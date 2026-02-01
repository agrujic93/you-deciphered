<?php
/**
 * Block Name: Query Posts
 *
 * This is the template that displays the Query Posts Block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

// Create id attribute for specific styling and anchor tag.

if ( isset( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-query-posts-' . $block['id'];
}

$main_block_class = 'ci-query-posts-block ci-block';
$container_class = 'section-full-width';
if ( 'wide' == $block['align'] ) {
	$container_class = 'section-container-wide';
} elseif ( '' == $block['align'] || 'center' == $block['align'] ) {
	$container_class = 'section-container';
} elseif ( 'left' == $block['align'] ) {
	$container_class = 'container-left';
} elseif ( 'right' == $block['align'] ) {
	$container_class = 'container-right';
}
if ( isset( $block['data']['preview_image_help'] ) ) :    /* rendering in inserter preview  */
	echo '<img src="' . esc_url( get_template_directory_uri() ) . esc_attr( $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';

else : /* rendering in editor body */
	?>

	<?php include __DIR__ . '/../block-parts/background-and-text-color-block.php'; ?>

	<section id="<?php echo esc_attr($block_id); ?>" <?php echo $wrapper_attributes; ?>>
		<?php if ( $bg_image_id ) : ?>
			<?php
			echo wp_get_attachment_image(
				$bg_image_id,
				'full-hero-size',
				false,
				array(
					'class' => 'section-background-image',
					'alt'   => $bg_image_alt,
				)
			);
			?>
		<?php endif; ?>

		<div class="section-img-overlay" style="background-color: <?php echo get_field('block_background_color'); ?>"></div>

		<div class="container" <?php include __DIR__ . '/../block-parts/animation-block.php'; ?>>
			<?php if(get_field('intro')): ?>
				<div class="animation-fade-item uk-margin-medium-bottom" <?php echo $duration; ?>>
					<?php echo get_field('intro'); ?>
				</div>
			<?php endif; ?>

			<?php if (get_field('posts_to_show') == "latest_posts"): ?>
				<div class="uk-grid" data-uk-grid>
				<?php
					$number_of_posts = get_field('number_of_latest_posts') ?? 3;
					$post_type = get_field('choose_posts_type') ?? 'post';

					$args = array(
						'post_type' => $post_type,
						'posts_per_page' => $number_of_posts,
						'orderby' => 'date',
						'order' => 'DESC',
					);
					$query = new WP_Query( $args );
					if ( $query->have_posts() ) :
						while ( $query->have_posts() ) : $query->the_post();
							$permalink = get_permalink();
							$title = get_the_title();
							$date = get_the_date();
						?>
							<div class="uk-width-1-3@l uk-width-1-2@m animation-fade-item" <?php echo $duration; ?>>
								<?php if (get_field('block_background_color') == "rgb(246,247,253)"): ?>
									<div class="white-card query-posts-card rm-last-child-margin">
								<?php else: ?>
									<div class="query-posts-card rm-last-child-margin">
								<?php endif; ?>

									<a aria-label="Link to the post" class="image-wrapper uk-margin-bottom" href="<?php echo esc_url( $permalink ); ?>">
										<?php if (get_the_post_thumbnail()): ?>
											<?php echo get_the_post_thumbnail(get_the_ID(), 'large' ); ?>
										<?php else: ?>
											<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/theme-default.jpeg" alt="thumbnail logo">
										<?php endif ?>
									</a>
									<a class="post-title uk-margin-small-bottom" aria-label="Link to the <?php echo esc_html( $title ); ?>" href="<?php echo esc_url( $permalink ); ?>">
										<h3><?php echo esc_html( $title ); ?></h3>
									</a>
									<?php if ($post_type == 'post'): ?>
										<p class="uk-margin-bottom"><?php echo esc_html( $date ); ?></p>
										<p class="uk-margin-bottom"><?php echo wp_html_excerpt( get_the_excerpt(), 180, '... ' ); ?></p>
									<?php endif ?>
									<div class="uk-margin-auto-top">
										<a class="btn" aria-label="Learn More about <?php echo esc_html( $title ); ?>" href="<?php echo esc_url( $permalink ); ?>">Learn More</a>
									</div>
								</div>
							</div>
						<?php endwhile;
					wp_reset_postdata();
					endif;
				?>
				</div>
			<?php else: ?>
				<div class="uk-grid" data-uk-grid>
					<?php
					if( get_field('choose_posts') ):
						$featured_posts = get_field('choose_posts');
					?>
						<?php foreach( $featured_posts as $featured_post ): ?>
							<div class="animation-fade-item uk-width-1-3@l uk-width-1-2@m" <?php echo $duration; ?>>
								<?php if (get_field('block_background_color') == "rgb(246,247,253)"): ?>
									<div class="white-card query-posts-card rm-last-child-margin">
								<?php else: ?>
									<div class="query-posts-card rm-last-child-margin">
								<?php endif; ?>
									<?php
										$permalink = get_permalink( $featured_post->ID );
										$title = get_the_title( $featured_post->ID );
										$date = get_the_date('', $featured_post->ID);
									?>
									<a class="image-wrapper uk-margin-bottom" href="<?php echo esc_url( $permalink ); ?>">
										<?php if (get_the_post_thumbnail($featured_post->ID)): ?>
											<?php echo get_the_post_thumbnail($featured_post->ID, 'large' ); ?>
										<?php else: ?>
											<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/theme-default.jpeg" alt="thumbnail logo">
										<?php endif ?>
									</a>
									<a class="post-title uk-margin-small-bottom" aria-label="Link to the <?php echo esc_html( $title ); ?>" href="<?php echo esc_url( $permalink ); ?>">
										<h3><?php echo esc_html( $title ); ?></h3>
									</a>
									<?php if (get_post_type($featured_post->ID) == 'post'): ?>
										<p class="uk-margin-bottom"><?php echo esc_html( $date ); ?></p>
										<p class="uk-margin-bottom"><?php echo wp_html_excerpt( get_the_excerpt($featured_post->ID), 180, '... ' ); ?></p>
									<?php endif ?>
									<div class="uk-margin-auto-top">
										<a class="btn" aria-label="Link to the <?php echo esc_html( $title ); ?>" href="<?php echo esc_url( $permalink ); ?>">Learn More</a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>
