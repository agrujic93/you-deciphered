<?php
/**
 * Steps Slider Block Template.
 *
 * @param array $block The block settings and attributes.
 */

$id = 'steps-slider-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$classes = 'steps-slider-block ci-block';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$classes .= ' align' . $block['align'];
}

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
?>

<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
	<div class="<?php echo esc_attr( $container_class ); ?>">
		
		<?php if ( get_field( 'steps_slider_intro' ) ) : ?>
			<div class="steps-slider-intro uk-margin-large-bottom">
				<?php echo wp_kses_post( get_field( 'steps_slider_intro' ) ); ?>
			</div>
		<?php endif; ?>

		<?php
		// Ensure Swiper styles are loaded for this block
		if ( function_exists( 'wp_enqueue_style' ) ) {
			wp_enqueue_style( 'swiper-style' );
		}
		?>

		<?php if ( have_rows( 'slides' ) ) : ?>
			
			<!-- Desktop View -->
			<div class="steps-slider-desktop uk-visible@m">
				<div class="steps-slider-pinned-area">
					<div class="steps-slider-grid uk-grid uk-grid-large uk-child-width-1-2@m" data-uk-grid>
						
						<div class="steps-slider-content-col">
							<?php 
							$i = 1;
							while ( have_rows( 'slides' ) ) : the_row(); 
								$title = get_sub_field( 'slide_title' );
								$content = get_sub_field( 'slide_content' );
								?>
								<div class="step-content-item step-item-<?php echo esc_attr( $i ); ?> <?php echo $i === 1 ? 'is-active' : ''; ?>">
									<?php if ( get_field( 'display_slide_numbers' ) ) : ?>
										<div class="step-number h6"><?php echo sprintf( '%02d', $i ); ?></div>
									<?php endif; ?>
									<div class="step-text">
										<?php if ( $title ) : ?>
											<h3 class="step-title h2"><?php echo esc_html( $title ); ?></h3>
										<?php endif; ?>
										<?php if ( $content ) : ?>
											<div class="step-desc">
												<?php echo wp_kses_post( $content ); ?>
											</div>
										<?php endif; ?>
									</div>
								</div>
							<?php 
							$i++;
							endwhile; 
							?>
						</div>

						<div class="steps-slider-image-col">
							<?php 
							$i = 1;
							while ( have_rows( 'slides' ) ) : the_row(); 
								$image_id = get_sub_field( 'slide_image' );
								?>
								<div class="step-image-item step-item-<?php echo esc_attr( $i ); ?> <?php echo $i === 1 ? 'is-active' : ''; ?>">
									<?php 
									if ( $image_id ) {
										echo wp_get_attachment_image( $image_id, 'large' );
									}
									?>
								</div>
							<?php 
							$i++;
							endwhile; 
							?>
						</div>

					</div>
				</div>
			</div>

			<!-- Mobile View -->
			<div class="steps-slider-mobile uk-hidden@m">
				<div class="swiper steps-swiper">
					<div class="swiper-wrapper">
						<?php 
						$i = 1;
						while ( have_rows( 'slides' ) ) : the_row(); 
							$image_id = get_sub_field( 'slide_image' );
							$title = get_sub_field( 'slide_title' );
							$content = get_sub_field( 'slide_content' );
							?>
							<div class="swiper-slide step-mobile-slide">
								<div class="step-image-item uk-margin-bottom">
									<?php 
									if ( $image_id ) {
										echo wp_get_attachment_image( $image_id, 'large' );
									}
									?>
								</div>
								<div class="step-content-item">
									<?php if ( get_field( 'display_slide_numbers' ) ) : ?>
										<div class="step-number h6 uk-margin-bottom"><?php echo sprintf( '%02d', $i ); ?></div>
									<?php endif; ?>
									<div class="step-text">
										<?php if ( $title ) : ?>
											<h4 class="step-title h3"><?php echo esc_html( $title ); ?></h4>
										<?php endif; ?>
										<?php if ( $content ) : ?>
											<div class="step-desc">
												<?php echo wp_kses_post( $content ); ?>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php 
						$i++;
						endwhile; 
						?>
					</div>
					
					<div class="steps-slider-nav uk-flex uk-flex-middle uk-flex-between uk-margin-top">
						<div class="swiper-pagination"></div>
						<div class="swiper-nav-buttons uk-flex uk-flex-middle">
							<div class="swiper-button-prev ci-slider-button uk-margin-small-right">
								<span data-uk-icon="icon: arrow-left; ratio: 1.2"></span>
							</div>
							<div class="swiper-button-next ci-slider-button">
								<span data-uk-icon="icon: arrow-right; ratio: 1.2"></span>
							</div>
						</div>
					</div>

				</div>
			</div>
			
		<?php endif; ?>

	</div>
</section>
