<?php
/**
 * Block Name: Hero
 *
 * This is the template that displays the Hero block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

// Create id attribute for specific styling and anchor tag.

if ( isset( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-hero-' . $block['id'];
}

$main_block_class = 'ci-hero-block ci-block';
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

	<?php
		$classes = [$main_block_class, $container_class];
		$inline_styles = [];

		if (get_field('hero_background_color')) {
			$inline_styles[] = 'background-color: ' . esc_attr(get_field('hero_background_color'));
		}

		if (get_field('hero_background_image') || get_field('hero_background_color')) {
			$classes[] = 'ci-has-background';
		}

		if (get_field('choose_hero_layout') == "hero_bgr_image_layout") {
			$classes[] = 'ci-hero-image';
		}

		if (get_field('choose_hero_layout') == "hero_text_layout") {
			if (!get_field('hero_background_color')) {
				$classes[] = 'ci-hero-text small-gap main-gradient ci-has-background';
			} else {
				$classes[] = 'ci-hero-text small-gap';
			}

		}

		if (get_field('choose_hero_layout') == "hero_slider_layout") {
			$classes[] = 'ci-hero-slider';
		}

		if (get_field('hero_text_color')) {
			$inline_styles[] = 'color: ' . esc_attr(get_field('hero_text_color'));
			$classes[] = 'has-text-color';
		}

		// Include universal logic (this will handle block_background_color, block_text_color, block_background_image, and color_variant)
		// It will also build its own $wrapper_attributes, so we need to be careful.
		// We'll rename our local $wrapper_attributes to $hero_wrapper_attributes if needed, 
		// but block-general-logic.php uses $main_block_class and $container_class to build its version.
		include __DIR__ . '/../block-parts/block-general-logic.php';
	?>

	<section data-theme="<?php echo esc_attr($color_variant); ?>" id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?>>
		
		<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>

		<div class="hero-content-wrp" <?php echo $animation_data_attr; ?> <?php echo $animation_duration_style; ?>>
			<?php if (get_field('choose_hero_layout') == "hero_text_layout"): ?>
				<div class="container">
					<?php if(get_field('hero_text')): ?>
						<div class="hero-wrp rm-last-child-margin animation-fade-item" <?php echo $duration; ?>>
							<?php echo (get_field('hero_text')); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php elseif (get_field('choose_hero_layout') == "hero_bgr_image_layout"): ?>
				<?php if (get_field( 'hero_background_image')):
					$image_alt = get_post_meta(get_field( 'hero_background_image'), '_wp_attachment_image_alt', true);
				?>
					<?php echo wp_get_attachment_image( get_field( 'hero_background_image'), 'full-hero-size', false, array( "class" => "hero-background-image",'alt' => $image_alt, "data-uk-parallax" => "scale: 1.3" ) ); ?>
				<?php endif; ?>
				<?php if (get_field('hero_background_image_overlay')): ?>
					<div style="background-color: <?php echo get_field('hero_background_image_overlay') ?>;" class="hero-image-overlay"></div>
				<?php endif ?>
				<div class="container">
					<?php if(get_field('hero_text')): ?>
						<div class="hero-wrp animation-fade-item rm-last-child-margin" <?php echo $duration; ?>>
							<div data-uk-parallax="y: -10vh; opacity: 0">
								<?php echo (get_field('hero_text')); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php elseif (get_field('choose_hero_layout') == "hero_slider_layout"): ?>
				<?php if ( have_rows( 'hero_slider' ) ) : ?>
					<div class="swiper hero-swiper">
						<div class="swiper-wrapper">
							<?php while ( have_rows( 'hero_slider' ) ) :the_row(); ?>
								<?php if (get_sub_field('slide_text_color')): ?>
									<div class="swiper-slide rm-last-child-margin has-text-color" style="color: <?php echo get_sub_field('slide_text_color'); ?>">
								<?php else: ?>
									<div class="swiper-slide rm-last-child-margin">
								<?php endif ?>
									<?php if (get_sub_field( 'slide_background_image')):
										$image_alt = get_post_meta(get_sub_field( 'slide_background_image'), '_wp_attachment_image_alt', true);
									?>
										<?php echo wp_get_attachment_image( get_sub_field( 'slide_background_image'), 'full-hero-size', false, array( "class" => "hero-slider-img",'alt' => $image_alt ) ); ?>
									<?php endif; ?>
									<?php if (get_sub_field('slide_background_overlay')): ?>
										<div style="background-color: <?php echo get_sub_field('slide_background_overlay') ?>;" class="hero-image-overlay"></div>
									<?php endif ?>
									<div <?php echo $duration; ?> class="hero-slide-content animation-fade-item">
										<div class="container" data-uk-parallax="y: -10vh; opacity: 0">
											<?php if(get_sub_field('slide_text')): ?>
												<?php echo (get_sub_field('slide_text')); ?>
											<?php endif; ?>
										</div>
									</div>
								</div>
							<?php endwhile; ?>
						</div>
						<div class="swiper-pagination"></div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>
