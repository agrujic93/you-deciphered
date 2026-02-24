<?php
/**
 * Block Name: Slider Images
 *
 * This is the template that displays the Slider Images Block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

// Create id attribute for specific styling and anchor tag.

if ( isset( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-slider-images-block-' . $block['id'];
}

$main_block_class = 'ci-slider-images-block ci-block';
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
			<?php if (get_field('gallery_intro')): ?>
				<div class="uk-margin-medium-bottom rm-last-child-margin">
					<?php echo get_field('gallery_intro'); ?>
				</div>
			<?php endif ?>

			<?php
			$layout_type = get_field('slides_per_view');
			$autoplay    = get_field('autoplay') ? 'autoplay: true;' : 'autoplay: false;'; ?>

			<?php if ( have_rows('gallery_images') ) :?>
				<?php if ( $layout_type === 'one_slide' ) : ?>
					<div class="gallery-wrp uk-position-relative uk-visible-toggle" data-uk-lightbox tabindex="-1" uk-slideshow="animation: fade; ratio: 7:3; <?php echo esc_attr($autoplay); ?>">
						<ul class="uk-slideshow-nav uk-dotnav uk-flex-center"></ul>
						<div class="uk-slideshow-items">
							<?php while ( have_rows('gallery_images') ) : the_row(); ?>
								<?php
									$first_image = get_sub_field("image");
									$first_image_alt = get_post_meta($first_image, '_wp_attachment_image_alt', TRUE);
									$first_image_url = wp_get_attachment_image_url($first_image, 'full');
								 ?>
								<div class="gallery-inner">
									<a href="<?php echo $first_image_url; ?>"></a>
									<?php echo wp_get_attachment_image( $first_image, 'large', false,
										array( 'class' => 'uk-cover', 'alt'   => $first_image_alt,));
									?>
								</div>
							<?php endwhile; ?>
						</div>
						<a class="uk-position-center-left uk-position-small" href uk-slidenav-previous uk-slideshow-item="previous"></a>
						<a class="uk-position-center-right uk-position-small" href uk-slidenav-next uk-slideshow-item="next"></a>
					</div>
				<?php else: ?>
					<?php switch ( $layout_type ) {
						case 'two_slides':
							$width_class = 'uk-child-width-1-2@s';
							break;
						case 'three_slides':
							$width_class = 'uk-child-width-1-3@s';
							break;
						case 'four_slides':
							$width_class = 'uk-child-width-1-4@s';
							break;
					} ?>
					<div class="uk-position-relative gallery-slider-wrp" data-uk-slider="<?php echo esc_attr($autoplay); ?>" data-uk-lightbox tabindex="-1">
						<div class="uk-slider-items uk-grid uk-grid-small <?php echo esc_attr($width_class); ?>">
							<?php while ( have_rows('gallery_images') ) : the_row(); ?>
								<?php
									$first_image = get_sub_field("image");
									$first_image_alt = get_post_meta($first_image, '_wp_attachment_image_alt', TRUE);
									$first_image_url = wp_get_attachment_image_url($first_image, 'full');
								 ?>
							<div class="ci-slide">
								<div class="gallery-inner">
									<a href="<?php echo $first_image_url; ?>"></a>
									<?php echo wp_get_attachment_image( $first_image, 'large', false,
										array( 'class' => 'uk-cover', 'alt'   => $first_image_alt,));
									?>
								</div>
							</div>
							<?php endwhile; ?>
						</div>
						<a class="uk-position-center-left uk-position-small uk-hidden-hover" href uk-slidenav-previous uk-slider-item="previous"></a>
						 <a class="uk-position-center-right uk-position-small uk-hidden-hover" href uk-slidenav-next uk-slider-item="next"></a>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>
