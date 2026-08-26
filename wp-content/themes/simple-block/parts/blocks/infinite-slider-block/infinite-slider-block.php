<?php
/**
 * Block Name: Infinite Slider
 *
 * Displays a continuous infinite auto-scrolling image slider using Swiper.
 *
 * @package simple-block
 */

// Create id attribute for specific styling and anchor tag.
$id = wp_unique_id( 'ci-infinite-slider-' );
if ( ! empty( $block['anchor'] ) ) {
	$id = sanitize_title( (string) $block['anchor'] );
}

$classes = [ 'ci-infinite-slider-block', 'ci-block' ];
if ( ! empty( $block['className'] ) ) {
	$classes[] = $block['className'];
}

$inline_styles = [];

// Preview image in inserter.
if ( isset( $block['data']['preview_image_help'] ) ) :
	echo '<img src="' . esc_url( get_template_directory_uri() . $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';
else :
	require __DIR__ . '/../block-parts/block-options.php';

	$slides         = get_field( 'slider' );
	$intro          = get_field( 'intro' );
	$is_empty_block = empty( $slides ) && empty( $intro );

	if ( is_admin() && $is_empty_block ) {
		ci_render_empty_block_placeholder( __( 'Infinite Slider Block is empty. Click the block to edit and add images.', 'simple-block' ) );
		return;
	}
?>

	<section data-theme="<?php echo esc_attr( $color_variant ); ?>" id="<?php echo esc_attr( $id ); ?>" <?php echo $wrapper_attributes; ?>>

		<?php include __DIR__ . '/../block-parts/block-options-visuals.php'; ?>

		<?php if ( ! empty( $intro ) ) : ?>
			<div class="container" <?php echo $animation_attr; ?>>
				<div class="infinite-slider-intro rm-last-child-margin animation-fade-item" <?php echo $duration; ?>>
					<?php echo wp_kses_post( $intro ); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $slides ) && is_array( $slides ) ) :
			// Ensure sufficient slides for continuous infinite looping on wide viewports.
			$rendered_slides = $slides;
			if ( count( $slides ) < 10 && count( $slides ) > 0 ) {
				while ( count( $rendered_slides ) < 10 ) {
					$rendered_slides = array_merge( $rendered_slides, $slides );
				}
			}
		?>
			<div class="infinite-slider-track-wrap" <?php echo $animation_attr; ?>>
				<div class="swiper infinite-slider-swiper animation-fade-item" <?php echo $duration; ?>>
					<div class="swiper-wrapper">
						<?php foreach ( $rendered_slides as $slide_item ) :
							$image_id = is_array( $slide_item ) ? ( $slide_item['image'] ?? null ) : $slide_item;
							if ( ! $image_id ) {
								continue;
							}
							$image_alt = get_post_meta( (int) $image_id, '_wp_attachment_image_alt', true );
							if ( empty( $image_alt ) ) {
								$image_alt = get_the_title( (int) $image_id );
							}
						?>
							<div class="swiper-slide infinite-slider-slide">
								<div class="infinite-slider-image-holder">
									<?php echo wp_get_attachment_image(
										(int) $image_id,
										'full',
										false,
										[
											'class'   => 'infinite-slider-img',
											'alt'     => $image_alt,
											'loading' => 'lazy',
										]
									); ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</section>

<?php endif; ?>
