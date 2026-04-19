<?php
/**
 * Blog block backend logic (AJAX + render helpers).
 *
 * @package Simple Block
 */

/**
 * Enqueue blog block script data
 *
 * @since Simple Block 1.0
 */
function ci_blog_block_enqueue_data() {
	// Output the script data inline in the footer
	add_action(
		'wp_footer',
		function() {
			?>
			<script type="text/javascript">
				var ciBlockData = {
					ajaxUrl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
					nonce: '<?php echo wp_create_nonce( 'ci_blog_block_nonce' ); ?>'
				};
			</script>
			<?php
		},
		1
	);
}
add_action( 'wp_enqueue_scripts', 'ci_blog_block_enqueue_data' );

/**
 * Extract blog display settings from AJAX request.
 *
 * @return array<string,string>
 */
function ci_blog_get_display_settings_from_request() {
	return array(
		'show_thumbnail'      => isset( $_POST['show_thumbnail'] ) ? sanitize_text_field( $_POST['show_thumbnail'] ) : '',
		'show_excerpt'        => isset( $_POST['show_excerpt'] ) ? sanitize_text_field( $_POST['show_excerpt'] ) : '',
		'show_categories'     => isset( $_POST['show_categories'] ) ? sanitize_text_field( $_POST['show_categories'] ) : '',
		'show_read_more_link' => isset( $_POST['show_read_more_link'] ) ? sanitize_text_field( $_POST['show_read_more_link'] ) : '',
		'show_date'           => isset( $_POST['show_date'] ) ? sanitize_text_field( $_POST['show_date'] ) : '',
		'show_author_name'    => isset( $_POST['show_author_name'] ) ? sanitize_text_field( $_POST['show_author_name'] ) : '',
	);
}

/**
 * Apply render globals for blog item output.
 *
 * @param array  $display_settings Display settings map.
 * @return void
 */
function ci_blog_set_render_context( $display_settings ) {
	global $ci_blog_display_settings;
	$ci_blog_display_settings = $display_settings;
}

/**
 * Build common post query args for blog ajax handlers.
 *
 * @param int $posts_per_page Posts per page.
 * @param int $paged Page index.
 * @param int $category Category term id.
 * @return array<string,mixed>
 */
function ci_blog_build_query_args( $posts_per_page, $paged, $category = 0 ) {
	$args = array(
		'post_type'      => 'post',
		'posts_per_page' => $posts_per_page,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'paged'          => $paged,
	);

	if ( $category ) {
		$args['cat'] = $category;
	}

	return $args;
}

/**
 * Send standardized blog query response payload.
 *
 * @param WP_Query $query WP_Query instance.
 * @param int      $paged Current page number.
 * @return void
 */
function ci_blog_send_query_response( $query, $paged ) {
	if ( $query->have_posts() ) {
		ob_start();
		while ( $query->have_posts() ) {
			$query->the_post();
			ci_render_blog_post_item();
		}
		wp_reset_postdata();
		$html = ob_get_clean();

		wp_send_json_success( array(
			'html'     => $html,
			'has_more' => $paged < $query->max_num_pages,
		) );
	}

	wp_send_json_success( array(
		'html'     => '',
		'has_more' => false,
	) );
}

/**
 * AJAX handler for loading more blog posts
 *
 * @since Simple Block 1.0
 */
function ci_blog_load_more() {
	check_ajax_referer( 'ci_blog_block_nonce', 'nonce' );

	$block_id         = isset( $_POST['block_id'] ) ? sanitize_text_field( $_POST['block_id'] ) : '';
	$paged            = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
	$category         = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
	$posts_per_page   = isset( $_POST['posts_per_page'] ) ? intval( $_POST['posts_per_page'] ) : 10;
	$display_settings = ci_blog_get_display_settings_from_request();

	ci_blog_set_render_context( $display_settings );

	if ( ! $block_id ) {
		wp_send_json_error( array( 'message' => 'Invalid block ID.' ) );
	}

	$args = ci_blog_build_query_args( $posts_per_page, $paged, $category );

	$query = new WP_Query( $args );

	ci_blog_send_query_response( $query, $paged );
}
add_action( 'wp_ajax_ci_blog_load_more', 'ci_blog_load_more' );
add_action( 'wp_ajax_nopriv_ci_blog_load_more', 'ci_blog_load_more' );

/**
 * AJAX handler for category filter on blog posts
 *
 * @since Simple Block 1.0
 */
function ci_blog_category_filter() {
	check_ajax_referer( 'ci_blog_block_nonce', 'nonce' );

	$block_id         = isset( $_POST['block_id'] ) ? sanitize_text_field( $_POST['block_id'] ) : '';
	$category         = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
	$paged            = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
	$posts_per_page   = isset( $_POST['posts_per_page'] ) ? intval( $_POST['posts_per_page'] ) : 10;
	$display_settings = ci_blog_get_display_settings_from_request();

	ci_blog_set_render_context( $display_settings );

	if ( ! $block_id ) {
		wp_send_json_error( array( 'message' => 'Invalid block ID.' ) );
	}

	$args = ci_blog_build_query_args( $posts_per_page, $paged, $category );

	$query = new WP_Query( $args );

	ci_blog_send_query_response( $query, $paged );
}
add_action( 'wp_ajax_ci_blog_category_filter', 'ci_blog_category_filter' );
add_action( 'wp_ajax_nopriv_ci_blog_category_filter', 'ci_blog_category_filter' );

/**
 * Render individual blog post item for AJAX
 *
 * @since Simple Block 1.0
 */
if ( ! function_exists( 'ci_render_blog_post_item' ) ) {
	if ( ! function_exists( 'ci_blog_field_enabled' ) ) {
		/**
		 * Normalize ACF true/false style values to strict booleans.
		 *
		 * @param mixed $value   Raw ACF value.
		 * @param bool  $default Default fallback when value is unset.
		 * @return bool
		 */
		function ci_blog_field_enabled( $value, $default = false ) {
			if ( '' === $value || null === $value ) {
				return (bool) $default;
			}

			if ( is_bool( $value ) ) {
				return $value;
			}

			return in_array( $value, array( 1, '1', 'true', 'on', 'yes' ), true );
		}
	}

	function ci_render_blog_post_item() {
		global $ci_blog_display_settings;

		$post_id        = get_the_ID();
		$permalink      = get_permalink();
		$title          = get_the_title();
		$date           = get_the_date();
		$author_id      = (int) get_post_field( 'post_author', $post_id );
		$author         = trim( (string) get_the_author() );
		if ( '' === $author ) {
			$author = trim( (string) get_the_author_meta( 'display_name', $author_id ) );
		}
		$categories     = get_the_category();
		$featured_image = get_the_post_thumbnail_url( $post_id, 'large' );
		$default_thumbnail_path = get_template_directory() . '/assets/images/thumbnail-default.jpeg';
		$default_thumbnail_url = file_exists( $default_thumbnail_path ) ? get_template_directory_uri() . '/assets/images/thumbnail-default.jpeg' : '';
		$post_thumbnail = $featured_image ? $featured_image : $default_thumbnail_url;

		$show_thumbnail_value = ( is_array( $ci_blog_display_settings ) && array_key_exists( 'show_thumbnail', $ci_blog_display_settings ) )
			? $ci_blog_display_settings['show_thumbnail']
			: get_field( 'show_thumbnail', $post_id );
		$show_categories_value = ( is_array( $ci_blog_display_settings ) && array_key_exists( 'show_categories', $ci_blog_display_settings ) )
			? $ci_blog_display_settings['show_categories']
			: get_field( 'show_categories', $post_id );
		$show_date_value = ( is_array( $ci_blog_display_settings ) && array_key_exists( 'show_date', $ci_blog_display_settings ) )
			? $ci_blog_display_settings['show_date']
			: get_field( 'show_date', $post_id );
		$show_author_name_value = ( is_array( $ci_blog_display_settings ) && array_key_exists( 'show_author_name', $ci_blog_display_settings ) )
			? $ci_blog_display_settings['show_author_name']
			: get_field( 'show_author_name', $post_id );
		$show_excerpt_value = ( is_array( $ci_blog_display_settings ) && array_key_exists( 'show_excerpt', $ci_blog_display_settings ) )
			? $ci_blog_display_settings['show_excerpt']
			: get_field( 'show_excerpt', $post_id );
		$show_read_more_link_value = ( is_array( $ci_blog_display_settings ) && array_key_exists( 'show_read_more_link', $ci_blog_display_settings ) )
			? $ci_blog_display_settings['show_read_more_link']
			: get_field( 'show_read_more_link', $post_id );

		$show_thumbnail = ci_blog_field_enabled( $show_thumbnail_value, true );
		$show_categories = ci_blog_field_enabled( $show_categories_value, false );
		$show_date = ci_blog_field_enabled( $show_date_value, false );
		$show_author_name = ci_blog_field_enabled( $show_author_name_value, false ) && '' !== $author;
		$show_excerpt = ci_blog_field_enabled( $show_excerpt_value, false );
		$show_read_more_link = ci_blog_field_enabled( $show_read_more_link_value, false );

		// Default to showing thumbnail if field not set
		if ( $show_thumbnail === '' || $show_thumbnail === null ) {
			$show_thumbnail = true;
		}
		?>
		<div class="single-blog-wrp animation-fade-item uk-width-1-3@m">
			<div class="grid-card">
				<?php if ( $show_thumbnail && $post_thumbnail ) : ?>
					<div class="uk-position-relative post-thumb uk-overflow-hidden uk-margin-small-bottom">
						<img class="wp-post-image" src="<?php echo esc_url( $post_thumbnail ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
						<?php if ( $show_categories && ! empty( $categories ) ) : ?>
							<p class="uk-text-small uk-margin-remove-bottom post-categories">
								<?php echo esc_html( implode( ', ', wp_list_pluck( $categories, 'name' ) ) ); ?>
							</p>
						<?php endif; ?>
					</div>
				<?php elseif ( $show_categories && ! empty( $categories ) ) : ?>
					<p class="uk-text-small uk-margin-small-bottom post-categories post-categories--no-thumb">
						<?php echo esc_html( implode( ', ', wp_list_pluck( $categories, 'name' ) ) ); ?>
					</p>
				<?php endif; ?>

				<h3 class="post-title h4 uk-margin-small-bottom">
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
				</h3>
				<div class="uk-flex uk-flex-between uk-flex-middle">
					<?php if ( $show_date || $show_author_name ) : ?>
						<div class="post-meta uk-margin-remove-bottom">
							<?php if ( $show_date ) : ?>
								<p class="uk-text-small uk-margin-remove-bottom"><?php echo esc_html( $date ); ?></p>
							<?php endif; ?>
							<?php if ( $show_author_name ) : ?>
								<p class="post-author uk-text-small uk-margin-remove-bottom">By <?php echo esc_html( $author ); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php if ( $show_read_more_link ) : ?>
						<div class="read-more-link-wrp">
							<a class="read-more-link uk-text-small" aria-label="Read More about <?php echo esc_html( $title ); ?>" href="<?php echo esc_url( $permalink ); ?>">Read More</a>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $show_excerpt ) : ?>
					<p class="uk-margin-small-top"><?php echo wp_html_excerpt( get_the_excerpt(), 150, '...' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
