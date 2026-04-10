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
	$block_id = 'sticky-columns-' . $block['id'];
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

			<?php $view_type = get_field('choose_layout'); ?>

			<div class="posts-wrp" data-uk-lightbox="nav: thumbnav; animation: scale; toggle: .light">

				<?php if ($view_type === 'grid_view'): ?>
					<div class="uk-grid uk-grid-small blog-grid-view" uk-grid>
				<?php endif; ?>

				<?php
				$number_field = get_field( 'posts_per_page' );
				if ( $number_field === 'all' ) {
					$number_of_posts = -1;
				} elseif ( $number_field ) {
					$number_of_posts = $number_field;
				} else {
					$number_of_posts = 10;
				}

				// Pagination setup
				$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

				$selected_categories = get_field( 'categories' );

				$excluded_categories = get_field( 'exclude_categories' );

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

				if ( $query->have_posts() ) :
					while ( $query->have_posts() ) :
						$query->the_post();
						$permalink = get_permalink();
						$title     = get_the_title();
						$date      = get_the_date();
						$author    = get_the_author();
						$featured_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
						$featured_video = get_field('featured_video', get_the_ID());
						?>
						<div class="single-blog-wrp animation-fade-item <?php echo ($view_type === 'grid_view') ? 'uk-width-1-3@m' : ''; ?>" <?php echo $animation_duration_style; ?>>
							<?php if ($view_type === 'list_view'): ?>
								<div class="uk-grid uk-grid-small" data-uk-grid>
									<?php if ($featured_video): ?>
										<div class="uk-width-1-3@s">
											<div class="uk-position-relative post-thumb uk-overflow-hidden">
												<div class="single-video-100 blog-block"><?php echo $featured_video; ?></div>
											</div>
										</div>
									<?php elseif ( get_the_post_thumbnail() && get_field('show_thumbnail') ) : ?>
										<div class="uk-width-1-3@s">
											<div class="uk-position-relative post-thumb uk-overflow-hidden">
												<img class="wp-post-image" src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>">
												<div class="rollover-info">
													<div>
														<div class="uk-flex">
															<a class="links-wrp" aria-label="Link to the blog post" href="<?php echo esc_url( $permalink ); ?>"><div class="link-ico"></div></a>
															<a class="links-wrp light" data-caption="<?php echo esc_attr($title); ?>" data-type="image" href="<?php echo esc_url( $featured_image ); ?>"><div class="loop-ico"><img class="hidden-img" src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>"></div></a>
														</div>
														<h4 class="h6 uk-margin-remove-bottom uk-text-center"><a aria-label="Link to the blog post" href="<?php echo esc_url( $permalink ); ?>"><?php echo $title; ?></a></h4>
														<?php
														$categories = get_the_category();
														if ( ! empty( $categories ) && get_field('show_categories') ) {
															$first_category = $categories[0];
															$category_link = get_category_link( $first_category->term_id );
															echo '<span class="post-category"><a href="' . esc_url( $category_link ) . '">' . esc_html( $first_category->name ) . '</a></span>';
														}
														?>
													</div>
												</div>
											</div>
										</div>
									<?php endif; ?>
									<div class="uk-width-expand">
										<div class="single-info-wrp rm-last-child-margin">
											<div>
												<h3 class="post-title uk-display-inline-block h4 uk-margin-remove-bottom">
													<a aria-label="Link to the <?php echo esc_html( $title ); ?>" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
												</h3>
												<?php if (get_field('show_date') || get_field('show_author_name')): ?>
													<div class="uk-margin-small-top">
														<?php if (get_field('show_date')): ?>
															<p class="uk-text-small uk-margin-remove-bottom"><?php echo esc_html( $date ); ?></p>
														<?php endif ?>
														<?php if (get_field('show_author_name')): ?>
															<p class="post-author uk-text-small uk-margin-remove-bottom">By <?php echo esc_html( $author ); ?></p>
														<?php endif ?>
													</div>
												<?php endif ?>
											</div>
											<?php if (get_field('show_excerpt')): ?>
												<p class="uk-margin-remove-bottom post-excerpt"><?php echo wp_html_excerpt( get_the_excerpt(), 180, '... ' ); ?></p>
											<?php endif ?>
											<?php if (get_field('show_read_more_link')): ?>
												<div class="read-more-link-wrp">
													<a class="read-more-link uk-text-small" aria-label="Read More about <?php echo esc_html( $title ); ?>" href="<?php echo esc_url( $permalink ); ?>">Read More</a>
												</div>
											<?php endif ?>
										</div>
									</div>
								</div>
							<?php else: ?>
								<div class="grid-card">
									<?php if ($featured_video): ?>
										<div class="uk-position-relative post-thumb uk-overflow-hidden uk-margin-small-bottom">
											<div class="single-video-100 blog-block"><?php echo $featured_video; ?></div>
										</div>
									<?php elseif ($featured_image && get_field('show_thumbnail')): ?>
										<div class="uk-position-relative post-thumb uk-overflow-hidden uk-margin-small-bottom">
											<img class="wp-post-image" src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>">
											<div class="rollover-info">
												<div>
													<div class="uk-flex">
														<a class="links-wrp" aria-label="Link to the blog post" href="<?php echo esc_url( $permalink ); ?>"><div class="link-ico"></div></a>
														<a class="links-wrp light" data-caption="<?php echo esc_attr($title); ?>" data-type="image" href="<?php echo esc_url( $featured_image ); ?>"><div class="loop-ico"><img class="hidden-img" src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>"></div></a>
													</div>
													<h4 class="h6 uk-margin-remove-bottom uk-text-center"><a aria-label="Link to the blog post" href="<?php echo esc_url( $permalink ); ?>"><?php echo $title; ?></a></h4>
													<?php
													$categories = get_the_category();
													if ( ! empty( $categories ) && get_field('show_categories') ) {
														$first_category = $categories[0];
														$category_link = get_category_link( $first_category->term_id );
														echo '<span class="post-category"><a href="' . esc_url( $category_link ) . '">' . esc_html( $first_category->name ) . '</a></span>';
													}
													?>
												</div>
											</div>
										</div>
									<?php endif; ?>

									<h3 class="post-title h4 uk-margin-small-bottom">
										<a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
									</h3>
									<div class="uk-flex uk-flex-between uk-flex-middle">
									<?php if (get_field('show_date')): ?>
										<p class="uk-text-small uk-margin-remove-bottom"><?php echo esc_html($date); ?></p>
									<?php endif; ?>
									<?php if (get_field('show_read_more_link')): ?>
										<div class="read-more-link-wrp">
											<a class="read-more-link uk-text-small" aria-label="Read More about <?php echo esc_html( $title ); ?>" href="<?php echo esc_url( $permalink ); ?>">Read More</a>
										</div>
									<?php endif ?>
									</div>

									<?php if (get_field('show_excerpt')): ?>
										<p class="uk-margin-small-top"><?php echo wp_html_excerpt(get_the_excerpt(), 150, '...'); ?></p>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
						<?php
					endwhile;
					?>
			</div>

			<!-- Pagination -->
			<?php if (get_field('show_pagination')): ?>
				<div class="uk-margin-large-top uk-text-right ci-pagination-wrp">
					<?php
					echo paginate_links( array(
						'total'   => $query->max_num_pages,
						'current' => max( 1, $paged ),
						'prev_text' => 'Previous',
						'next_text' => 'Next',
					) );
					?>
				</div>
			<?php endif; ?>

			<?php
				wp_reset_postdata();
				endif;
			?>
			<?php if ($view_type === 'grid_view'): ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>