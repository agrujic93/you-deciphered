<?php
/**
 * Block Name: Blog
 *
 * This is the template that displays the Query Posts Block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

if ( ! empty( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-blog-block-' . $block['id'];
}

$main_block_class = 'ci-blog-block ci-block';
$container_class  = 'section-full-width';
if ( 'wide' == $block['align'] ) {
	$container_class = 'section-container-wide';
} elseif ( '' == $block['align'] || 'center' == $block['align'] ) {
	$container_class = 'section-container';
} elseif ( 'left' == $block['align'] ) {
	$container_class = 'container-left';
} elseif ( 'right' == $block['align'] ) {
	$container_class = 'container-right';
}

if ( isset( $block['data']['preview_image_help'] ) ) : /* rendering in inserter preview */
	echo '<img src="' . esc_url( get_template_directory_uri() ) . esc_attr( $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';

else : /* rendering in editor body */
	?>

	<?php include __DIR__ . '/../block-parts/block-general-logic.php'; ?>

	<section data-theme="<?php echo esc_attr($color_variant); ?>" id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?>>

		<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>

		<?php
		if ( is_admin() && ! is_singular() ) {
			echo '<div style="padding: 20px; border: 1px dashed #ccc; text-align: center;">';
			echo '<h3>Click to edit Blog block</h3>';
			echo '</div>';
			return;
		}
		?>

		<div class="container" <?php echo $animation_data_attr; ?>>
			<?php if ( get_field( 'intro' ) ) : ?>
				<div class="animation-fade-item uk-margin-medium-bottom rm-last-child-margin" <?php echo $animation_duration_style; ?>>
					<?php echo get_field( 'intro' ); ?>
				</div>
			<?php endif; ?>

			<?php
			$selected_categories = get_field( 'categories' );
			$excluded_categories = get_field( 'exclude_categories' );
			?>

			<?php
			// Display category pills if both selected and excluded categories are empty
			if ( empty( $selected_categories ) && empty( $excluded_categories ) ) :
				$categories_with_children = get_categories( array(
					'hierarchical' => 1,
					'hide_empty'   => false,
				) );

				// Filter to get only categories with children
				$parent_categories = array_filter(
					$categories_with_children,
					function( $cat ) {
						return $cat->category_parent === 0; // Only parent categories
					}
				);

				if ( ! empty( $parent_categories ) ) :
					?>
					<div class="ci-category-filter-pills uk-margin-medium-bottom">
						<div class="uk-flex uk-flex-wrap" data-uk-margin>
							<button class="ci-category-pill ci-label-pill ci-label-pill-big is-active " data-category="" data-all-categories="true">
								<?php echo esc_html( simple_block_pll__( 'All' ) ); ?>
							</button>
							<?php foreach ( $parent_categories as $category ) : ?>
								<button class="ci-category-pill ci-label-pill ci-label-pill-big" data-category="<?php echo esc_attr( $category->term_id ); ?>">
									<?php echo esc_html( $category->name ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
					<?php
				endif;
			endif;
			?>

			<?php
			$number_field = get_field( 'posts_per_page' );
			if ( $number_field === 'all' ) {
				$number_of_posts = -1;
			} elseif ( $number_field ) {
				$number_of_posts = $number_field;
			} else {
				$number_of_posts = 10;
			}
			?>

			<?php
			$show_thumbnail_setting      = get_field( 'show_thumbnail' );
			$show_excerpt_setting        = get_field( 'show_excerpt' );
			$show_categories_setting     = get_field( 'show_categories' );
			$show_read_more_link_setting = get_field( 'show_read_more_link' );
			$show_date_setting           = get_field( 'show_date' );
			$show_author_name_setting    = get_field( 'show_author_name' );

			if ( '' === $show_thumbnail_setting || null === $show_thumbnail_setting ) {
				$show_thumbnail_setting = true;
			}
			?>

			<div class="posts-wrp" data-page="1" data-category="" data-posts-per-page="<?php echo esc_attr( $number_of_posts ); ?>" data-show-thumbnail="<?php echo esc_attr( $show_thumbnail_setting ? '1' : '0' ); ?>" data-show-excerpt="<?php echo esc_attr( $show_excerpt_setting ? '1' : '0' ); ?>" data-show-categories="<?php echo esc_attr( $show_categories_setting ? '1' : '0' ); ?>" data-show-read-more-link="<?php echo esc_attr( $show_read_more_link_setting ? '1' : '0' ); ?>" data-show-date="<?php echo esc_attr( $show_date_setting ? '1' : '0' ); ?>" data-show-author-name="<?php echo esc_attr( $show_author_name_setting ? '1' : '0' ); ?>">
				<div class="uk-grid uk-grid-small blog-grid-view" data-uk-grid>

				<?php
				// Pagination setup
				$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

				$args = array(
					'post_type'      => 'post',
					'posts_per_page' => $number_of_posts,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'paged'          => $paged,
				);

				if ( ! empty( $selected_categories ) ) {
					$args['category__in'] = $selected_categories;
				}

				if ( ! empty( $excluded_categories ) ) {
					$args['category__not_in'] = $excluded_categories;
				}

				$selected_tags = get_field('tag'); // change field name to yours

				if ( ! empty( $selected_tags ) ) {
					$args['tag__in'] = $selected_tags;
				}

				$query = new WP_Query( $args );

				ci_blog_set_render_context(
					array(
						'show_thumbnail'      => $show_thumbnail_setting,
						'show_excerpt'        => $show_excerpt_setting,
						'show_categories'     => $show_categories_setting,
						'show_read_more_link' => $show_read_more_link_setting,
						'show_date'           => $show_date_setting,
						'show_author_name'    => $show_author_name_setting,
					)
				);

				if ( $query->have_posts() ) :
					while ( $query->have_posts() ) :
						$query->the_post();
						ci_render_blog_post_item();
					endwhile;
				endif;

				wp_reset_postdata();
				?>
				</div>

				<!-- Load More Button -->
				<?php if ( get_field( 'show_pagination' ) && $query->max_num_pages > 1 ) : ?>
					<div class="uk-margin-large-top uk-text-center ci-load-more-wrp">
						<button class="ci-load-more-btn btn btn-secondary" data-has-more="1"><?php echo esc_html( simple_block_pll__( 'Load More' ) ); ?></button>
					</div>
				<?php endif; ?>

				<div class="ci-posts-loader" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="ci-spinner lds-ripple" style="background: none;">
						<circle cx="50" cy="50" r="8.28987" fill="none" stroke="#4a84ff" stroke-width="2">
							<animate attributeName="r" calcMode="spline" values="0;40" keyTimes="0;1" dur="1" keySplines="0 0.2 0.8 1" begin="-0.5s" repeatCount="indefinite"></animate>
							<animate attributeName="opacity" calcMode="spline" values="1;0" keyTimes="0;1" dur="1" keySplines="0.2 0 0.8 1" begin="-0.5s" repeatCount="indefinite"></animate>
						</circle>
						<circle cx="50" cy="50" r="29.5877" fill="none" stroke="#4a84ff" stroke-width="2">
							<animate attributeName="r" calcMode="spline" values="0;40" keyTimes="0;1" dur="1" keySplines="0 0.2 0.8 1" begin="0s" repeatCount="indefinite"></animate>
							<animate attributeName="opacity" calcMode="spline" values="1;0" keyTimes="0;1" dur="1" keySplines="0.2 0 0.8 1" begin="0s" repeatCount="indefinite"></animate>
						</circle>
					</svg>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>