<?php
/*
Template Name: Contact Page
*/
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php
		$header        = do_blocks( '<!-- wp:template-part {"slug":"header","theme":"simple-block"} /-->' );
		$footer        = do_blocks( '<!-- wp:template-part {"slug":"footer","theme":"simple-block"} /-->' );
		$block_content = do_blocks( '
			<!-- wp:post-content {"layout":{"type":"constrained"}} /-->'
		);
	?>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div class="wp-site-blocks">
		<header class="wp-block-template-part site-header">
			<?php block_header_area(); ?>
		</header>
		<?php
			$lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';
			$lang = in_array( $lang, array( 'en', 'sr' ), true ) ? $lang : 'en';

			$headline_contact = get_field( 'headline_contact_' . $lang, 'option' );
			$email            = get_field( 'email_' . $lang, 'option' );
			$working_hours    = get_field( 'working_hours_' . $lang, 'option' );
			$headline_loc     = get_field( 'headline_location_' . $lang, 'option' );
			$address          = get_field( 'address_location_' . $lang, 'option' );
			$address_link     = get_field( 'address_link_' . $lang, 'option' );
			$address_iframe   = get_field( 'address_iframe_' . $lang, 'option' );
			$shortcode        = get_field( 'main_contact_form_shortcode_' . $lang, 'option' );
			$has_phones       = have_rows( 'phone_numbers_' . $lang, 'option' );
			$has_socials      = have_rows( 'social_networks_' . $lang, 'option' );
		?>
		<main class="wp-block-group site-main is-layout-flow wp-block-group-is-layout-flow" id="wp--skip-link--target">
			<?php echo $block_content; ?>
			<div class="entry-content has-global-padding">
				<section class="section-container ci-block info-cf7-map-section" data-uk-scrollspy="cls: uk-animation-slide-bottom-small; target: .animation-fade-item; delay: 300; repeat: false;">
					<div class="container">
						<div class="uk-grid uk-grid-large uk-margin-large-bottom" data-uk-grid>
							<div class="uk-width-2-5@l animation-fade-item">
								<?php if ( $has_phones || $headline_contact ) : ?>
									<div class="info-wrp phone-info-wrp rm-last-child-margin">
										<?php if ( $headline_contact ) : ?>
											<h3><?php echo esc_html( $headline_contact ); ?></h3>
										<?php endif; ?>
										<?php if ( $has_phones ) : ?>
											<?php while ( have_rows( 'phone_numbers_' . $lang, 'option' ) ) : the_row(); ?>
												<?php $phone = get_sub_field( 'phone_' . $lang, 'option' ); ?>
												<?php if ( $phone ) : ?>
													<p><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" aria-label="<?php echo esc_attr( sprintf( simple_block_pll__( 'Call %s' ), $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
												<?php endif; ?>
											<?php endwhile; ?>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<?php if ( $email ) : ?>
									<div class="info-wrp email-info-wrp rm-last-child-margin">
										<p><a href="mailto:<?php echo esc_attr( sanitize_email( $email ) ); ?>" aria-label="<?php echo esc_attr( sprintf( simple_block_pll__( 'Email %s' ), $email ) ); ?>"><?php echo esc_html( $email ); ?></a></p>
									</div>
								<?php endif; ?>

								<?php if ( $working_hours ) : ?>
									<div class="info-wrp work-info-wrp rm-last-child-margin">
										<?php echo wp_kses_post( $working_hours ); ?>
									</div>
								<?php endif; ?>

								<?php if ( $address || $headline_loc ) : ?>
									<div class="info-wrp location-info-wrp rm-last-child-margin">
										<?php if ( $headline_loc ) : ?>
											<h3><?php echo esc_html( $headline_loc ); ?></h3>
										<?php endif; ?>
										<?php if ( $address ) : ?>
											<p>
												<?php if ( is_array( $address_link ) && ! empty( $address_link['url'] ) ) : ?>
													<a href="<?php echo esc_url( $address_link['url'] ); ?>"<?php echo ! empty( $address_link['target'] ) ? ' target="' . esc_attr( $address_link['target'] ) . '" rel="noopener noreferrer"' : ''; ?> aria-label="<?php echo esc_attr( ! empty( $address_link['title'] ) ? $address_link['title'] : $address ); ?>">
														<?php echo esc_html( $address ); ?>
													</a>
												<?php else : ?>
													<?php echo esc_html( $address ); ?>
												<?php endif; ?>
											</p>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<?php if ( $has_socials ) : ?>
									<div class="info-wrp social-info-wrp">
										<h3><?php echo esc_html( simple_block_pll__( 'Social Networks' ) ); ?></h3>
										<div class="social-icons">
											<?php while ( have_rows( 'social_networks_' . $lang, 'option' ) ) : the_row(); ?>
												<?php
												$url          = get_sub_field( 'url_' . $lang, 'option' );
												$social_title = get_sub_field( 'social_network_title_' . $lang, 'option' );
												$icon         = get_sub_field( 'header_icon_' . $lang, 'option' );
												if ( empty( $icon ) ) {
													$icon = get_sub_field( 'footer_icon_' . $lang, 'option' );
												}
												$icon_alt = is_array( $icon ) && ! empty( $icon['alt'] ) ? $icon['alt'] : ( is_array( $icon ) && ! empty( $icon['title'] ) ? $icon['title'] : ( $social_title ?: 'Social Network' ) );
												$aria_lbl = $social_title ? $social_title : $icon_alt;
												?>
												<?php if ( $url ) : ?>
													<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $aria_lbl ); ?>">
														<?php if ( is_array( $icon ) && ! empty( $icon['url'] ) ) : ?>
															<img data-uk-svg alt="<?php echo esc_attr( $icon_alt ); ?>" src="<?php echo esc_url( $icon['url'] ); ?>">
														<?php elseif ( $social_title ) : ?>
															<span><?php echo esc_html( $social_title ); ?></span>
														<?php endif; ?>
													</a>
												<?php endif; ?>
											<?php endwhile; ?>
										</div>
									</div>
								<?php endif; ?>
							</div>

							<?php if ( $shortcode ) : ?>
								<div class="uk-width-3-5@l animation-fade-item">
									<?php echo do_shortcode( $shortcode ); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<?php if ( $address_iframe ) : ?>
						<div class="map-wrp animation-fade-item"><?php echo $address_iframe; ?></div>
					<?php endif; ?>
				</section>
			</div>
		</main>
		<footer class="wp-block-template-part site-footer">
			<?php block_footer_area(); ?>
		</footer>
	</div>
	<?php wp_footer(); ?>
</body>
</html>