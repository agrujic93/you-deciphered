<?php
/**
 * Block Name: Featured Posts
 *
 * This is the template that displays the Featured Posts Block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

// Create id attribute for specific styling and anchor tag.

if ( isset( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-featured-posts-' . $block['id'];
}

$main_block_class = 'ci-featured-posts-block ci-block';
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

	<?php include __DIR__ . '/../block-parts/block-general-logic.php'; ?>

	<section data-theme="<?php echo esc_attr($color_variant); ?>" id="<?php echo esc_attr($block_id); ?>" <?php echo $wrapper_attributes; ?>>

		<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>

		<div class="container" <?php echo $animation_data_attr; ?> <?php echo $animation_duration_style; ?>>
			<?php if(get_field('intro')): ?>
				<div class="animation-fade-item uk-margin-medium-bottom block-intro" <?php echo $duration; ?>>
					<?php echo get_field('intro'); ?>
				</div>
			<?php endif; ?>

			<div class="uk-grid" data-uk-grid>
				<?php
				if( get_field('choose_posts') ):
					$featured_posts = get_field('choose_posts');
				?>
					<?php foreach( $featured_posts as $featured_post ):
						$permalink = get_permalink( $featured_post->ID );
						$title = get_the_title( $featured_post->ID );
						$categories = get_the_category( $featured_post->ID );
						?>
						<div class="uk-width-1-3@l uk-width-1-2@s animation-fade-item" <?php echo $duration; ?>>
							<?php if (get_field('block_background_color') == "rgb(246,247,253)"): ?>
								<div class="white-card query-posts-card rm-last-child-margin">
							<?php else: ?>
								<div class="card-wrp rm-last-child-margin">
							<?php endif; ?>

								<a aria-label="Link to the post" class="card-link" href="<?php echo esc_url( $permalink ); ?>">
								</a>
									<?php if (get_the_post_thumbnail($featured_post->ID)): ?>
										<div class="img-wrp">
											<?php echo get_the_post_thumbnail($featured_post->ID, 'large'); ?>
										</div>
									<?php else: ?>
										<div class="img-wrp">
											<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/featured-default.jpeg" alt="thumbnail logo">
										</div>
									<?php endif ?>
									<div class="card-content rm-last-child-margin">
										<div class="ci-post-categories">
											<?php
											$display_category = '';
											if ( ! empty( $categories ) ) {
												foreach ( $categories as $cat ) {
													if ( 'Uncategorized' !== $cat->name ) {
														$display_category = $cat->name;
														break;
													}
												}
											}
											if ( ! empty( $display_category ) ) :
												?>
												<span class="category-name"><?php echo esc_html( $display_category ); ?></span>
											<?php endif; ?>
										</div>
										<h3><?php echo esc_html( $title ); ?></h3>
									</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
