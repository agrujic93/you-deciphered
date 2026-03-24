<?php
/**
 * Block Name: Testimonials
 *
 * This is the template that displays the Testimonials block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

// Create id attribute for specific styling and anchor tag.

if ( isset( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-testimonials-' . $block['id'];
}

$main_block_class = 'ci-testimonials-block ci-block ci-has-background';
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

	<section data-theme="<?php echo esc_attr($color_variant); ?>" id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?> <?php echo $animation_data_attr; ?> <?php echo $animation_duration_style; ?>>
		<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>

		<div class="container">
			<?php if (get_field( 'testimonials_intro' )): ?>
				<div class="rm-last-child-margin uk-margin-large-bottom animation-fade-item" <?php echo $duration; ?>>
					<?php echo get_field('testimonials_intro'); ?>
				</div>
			<?php endif ?>
		</div>

		<?php if ( have_rows( 'testimonials' ) ) : ?>
			<div class="swiper testimonials-swiper animation-fade-item" <?php echo $duration; ?>>
				<div class="swiper-wrapper">
					<?php while ( have_rows( 'testimonials' ) ) :the_row(); ?>
						<div class="swiper-slide rm-last-child-margin">
							<div class="uk-grid" data-uk-grid>
								<div class="uk-width-1-4@m">
									<div class="author-img-wrp">
										<?php if (get_sub_field( 'author_image')):
											$image_alt = get_post_meta(get_sub_field( 'author_image'), '_wp_attachment_image_alt', true);
										?>
											<?php echo wp_get_attachment_image( get_sub_field( 'author_image'), 'medium_large', false, array( "class" => "author-img",'alt' => $image_alt ) ); ?>
										<?php else: ?>
											<img class="quote-ico" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/quote-ico.svg" alt="Quote">
										<?php endif; ?>
									</div>
								</div>
								<div class="uk-width-expand">
									<?php if (get_sub_field('author_name')): ?>
										<h3 class="uk-margin-remove-bottom h2"><?php echo get_sub_field('author_name'); ?></h3>
									<?php endif ?>
									<?php if (get_sub_field('author_position')): ?>
										<span class="ci-label"><?php echo get_sub_field('author_position'); ?></span>
									<?php endif ?>
									<?php if (get_sub_field('testimonial')): ?>
										<p class="uk-margin-top h5"><?php echo get_sub_field('testimonial'); ?></p>
									<?php endif ?>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
				<div class="ci-swiper-nav">
					<div class="swiper-button-next">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g id="Arrow / Arrow_Right_LG">
								<path id="Vector" d="M21 12L3 12" stroke="CurrentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
								<path id="Vector_2" d="M8 17L3 12L8 7" stroke="CurrentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
							</g>
						</svg>
					</div>
					<div class="swiper-button-prev">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g id="Arrow / Arrow_Right_LG">
								<path id="Vector" d="M21 12L3 12" stroke="CurrentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
								<path id="Vector_2" d="M8 17L3 12L8 7" stroke="CurrentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
							</g>
						</svg>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</section>
<?php endif; ?>
