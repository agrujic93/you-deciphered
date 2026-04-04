<?php
/**
 * Services Slider Block Template.
 *
 * @param array $block The block settings and attributes.
 */

$block_id = 'services-slider-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
}

$main_block_class = 'services-slider-block ci-block';
if ( ! empty( $block['className'] ) ) {
	$main_block_class .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$main_block_class .= ' align' . $block['align'];
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

include __DIR__ . '/../block-parts/block-general-logic.php';

$services_slider_intro = '';
if ( get_field( 'services_slider_intro' ) ) {
	$services_slider_intro = get_field( 'services_slider_intro' );
}

$services = array();
if ( get_field( 'services' ) ) {
	$services = get_field( 'services' );
}

$cards_count = count( $services );
$has_slider  = $cards_count > 3;

if ( function_exists( 'wp_enqueue_style' ) ) {
	$services_slider_css_path = get_template_directory() . '/parts/blocks/services-slider/services-slider.css';
	$services_slider_css_url  = get_template_directory_uri() . '/parts/blocks/services-slider/services-slider.css';
	wp_enqueue_style( 'services-slider-style', $services_slider_css_url, array(), file_exists( $services_slider_css_path ) ? filemtime( $services_slider_css_path ) : '1.0.0' );
}

if ( $has_slider && function_exists( 'wp_enqueue_style' ) ) {
	wp_enqueue_style( 'swiper-style' );
}
?>

<section data-theme="<?php echo esc_attr( $color_variant ); ?>" id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?>>
	<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>
	<div class="container" <?php echo $animation_data_attr; ?> <?php echo $animation_duration_style; ?>>

		<?php if ( $services_slider_intro ) : ?>
			<div class="services-slider-intro animation-fade-item">
				<?php echo wp_kses_post( $services_slider_intro ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $services ) ) : ?>
			<?php if ( $has_slider ) : ?>
				<div class="services-slider-sticky-region animation-fade-item">
					<div class="services-slider-sticky-inner">
						<div class="swiper services-slider-swiper" data-cards-count="<?php echo esc_attr( $cards_count ); ?>">
							<div class="swiper-wrapper">
								<?php foreach ( $services as $service ) : ?>
									<?php
									$service_name        = ! empty( $service['service_name'] ) ? $service['service_name'] : '';
									$service_label       = ! empty( $service['service_label'] ) ? $service['service_label'] : '';
									$service_description = ! empty( $service['service_description'] ) ? $service['service_description'] : '';
									$service_image_id    = ! empty( $service['service_image'] ) ? absint( $service['service_image'] ) : 0;

									$service_image_url   = '';
									$service_image_alt   = '';
									$service_image_title = '';

									if ( $service_image_id ) {
										$service_image_data = wp_get_attachment_image_src( $service_image_id, 'large' );
										$service_image_url  = ! empty( $service_image_data[0] ) ? $service_image_data[0] : '';
										$service_image_alt  = get_post_meta( $service_image_id, '_wp_attachment_image_alt', true );
										$service_image_title = get_the_title( $service_image_id );
									}

									$service_image_alt = $service_image_alt ? $service_image_alt : $service_image_title;

									$service_link = ! empty( $service['service_button'] ) && is_array( $service['service_button'] ) ? $service['service_button'] : array();
									$service_link_url = ! empty( $service_link['url'] ) ? $service_link['url'] : '';
									$service_link_title = ! empty( $service_link['title'] ) ? $service_link['title'] : __( 'Learn more', 'simple-block' );
									$service_link_target = ! empty( $service_link['target'] ) ? $service_link['target'] : '_self';
									$service_link_label = $service_name ? sprintf( __( 'Learn more about %s', 'simple-block' ), $service_name ) : $service_link_title;
									?>
									<div class="swiper-slide">
										<article class="service-card">
											<?php if ( $service_image_url ) : ?>
												<div class="service-card__bg" style="background-image: url('<?php echo esc_url( $service_image_url ); ?>');" role="img" aria-label="<?php echo esc_attr( $service_image_alt ); ?>"></div>
											<?php endif; ?>

											<div class="service-card__overlay"></div>

											<div class="service-card__content">
												<?php if ( $service_label ) : ?>
													<p class="service-card__label"><?php echo esc_html( $service_label ); ?></p>
												<?php endif; ?>

												<?php if ( $service_name ) : ?>
													<h3 class="service-card__title h4"><?php echo esc_html( $service_name ); ?></h3>
												<?php endif; ?>

												<?php if ( $service_description ) : ?>
													<div class="service-card__description">
														<?php echo wp_kses_post( wpautop( $service_description ) ); ?>
													</div>
												<?php endif; ?>
											</div>

											<?php if ( $service_link_url ) : ?>
												<a class="service-card__cta" href="<?php echo esc_url( $service_link_url ); ?>" target="<?php echo esc_attr( $service_link_target ); ?>" <?php echo '_blank' === $service_link_target ? 'rel="noopener noreferrer"' : ''; ?> aria-label="<?php echo esc_attr( $service_link_label ); ?>">
													<span class="service-card__cta-icon" aria-hidden="true">
														<span data-uk-icon="icon: arrow-right; ratio: 1"></span>
													</span>
													<span class="service-card__cta-text"><?php echo esc_html( $service_link_title ); ?></span>
												</a>
											<?php endif; ?>
										</article>
									</div>
								<?php endforeach; ?>
							</div>

							<div class="services-slider-nav uk-hidden@l">
								<div class="swiper-button-prev ci-slider-button" aria-label="<?php esc_attr_e( 'Previous service', 'simple-block' ); ?>">
									<span data-uk-icon="icon: arrow-left; ratio: 1.1"></span>
								</div>
								<div class="swiper-button-next ci-slider-button" aria-label="<?php esc_attr_e( 'Next service', 'simple-block' ); ?>">
									<span data-uk-icon="icon: arrow-right; ratio: 1.1"></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php else : ?>
				<div class="services-slider-grid animation-fade-item" data-uk-grid>
					<?php foreach ( $services as $service ) : ?>
						<?php
						$service_name        = ! empty( $service['service_name'] ) ? $service['service_name'] : '';
						$service_label       = ! empty( $service['service_label'] ) ? $service['service_label'] : '';
						$service_description = ! empty( $service['service_description'] ) ? $service['service_description'] : '';
						$service_image_id    = ! empty( $service['service_image'] ) ? absint( $service['service_image'] ) : 0;

						$service_image_url   = '';
						$service_image_alt   = '';
						$service_image_title = '';

						if ( $service_image_id ) {
							$service_image_data = wp_get_attachment_image_src( $service_image_id, 'large' );
							$service_image_url  = ! empty( $service_image_data[0] ) ? $service_image_data[0] : '';
							$service_image_alt  = get_post_meta( $service_image_id, '_wp_attachment_image_alt', true );
							$service_image_title = get_the_title( $service_image_id );
						}

						$service_image_alt = $service_image_alt ? $service_image_alt : $service_image_title;

						$service_link = ! empty( $service['service_button'] ) && is_array( $service['service_button'] ) ? $service['service_button'] : array();
						$service_link_url = ! empty( $service_link['url'] ) ? $service_link['url'] : '';
						$service_link_title = ! empty( $service_link['title'] ) ? $service_link['title'] : __( 'Learn more', 'simple-block' );
						$service_link_target = ! empty( $service_link['target'] ) ? $service_link['target'] : '_self';
						$service_link_label = $service_name ? sprintf( __( 'Learn more about %s', 'simple-block' ), $service_name ) : $service_link_title;
						?>
						<article class="service-card">
							<?php if ( $service_image_url ) : ?>
								<div class="service-card__bg" style="background-image: url('<?php echo esc_url( $service_image_url ); ?>');" role="img" aria-label="<?php echo esc_attr( $service_image_alt ); ?>"></div>
							<?php endif; ?>

							<div class="service-card__overlay"></div>

							<div class="service-card__content">
								<?php if ( $service_label ) : ?>
									<p class="service-card__label"><?php echo esc_html( $service_label ); ?></p>
								<?php endif; ?>

								<?php if ( $service_name ) : ?>
									<h3 class="service-card__title h4"><?php echo esc_html( $service_name ); ?></h3>
								<?php endif; ?>

								<?php if ( $service_description ) : ?>
									<div class="service-card__description">
										<?php echo wp_kses_post( wpautop( $service_description ) ); ?>
									</div>
								<?php endif; ?>
							</div>

							<?php if ( $service_link_url ) : ?>
								<a class="service-card__cta" href="<?php echo esc_url( $service_link_url ); ?>" target="<?php echo esc_attr( $service_link_target ); ?>" <?php echo '_blank' === $service_link_target ? 'rel="noopener noreferrer"' : ''; ?> aria-label="<?php echo esc_attr( $service_link_label ); ?>">
									<span class="service-card__cta-icon" aria-hidden="true">
										<span data-uk-icon="icon: arrow-right; ratio: 1"></span>
									</span>
									<span class="service-card__cta-text"><?php echo esc_html( $service_link_title ); ?></span>
								</a>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>
