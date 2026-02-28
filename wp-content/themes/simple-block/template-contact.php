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
		$header = do_blocks('<!-- wp:template-part {"slug":"header","theme":"simple-block"} /-->');
		$footer = do_blocks('<!-- wp:template-part {"slug":"footer","theme":"simple-block"} /-->');
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
		<?php $lang = pll_current_language('slug'); ?>
		<main class="wp-block-group site-main is-layout-flow wp-block-group-is-layout-flow" id="wp--skip-link--target">
			<?php echo $block_content; ?>
			<div class="entry-content has-global-padding">
				<section class="section-container ci-block info-cf7-map-section" data-uk-scrollspy="cls: uk-animation-slide-bottom-small; target: .animation-fade-item; delay: 300; repeat: false;">
					<div class="container">
						<div class="uk-grid uk-grid-large uk-margin-large-bottom" data-uk-grid>
							<div class="uk-width-2-5@l animation-fade-item">
								<?php if ( have_rows( 'phone_numbers_'.$lang, 'option' ) ) : ?>
									<div class="info-wrp phone-info-wrp rm-last-child-margin">
										<?php while ( have_rows( 'phone_numbers_'.$lang, 'option' ) ) : the_row(); ?>
											<p><a href="tel:<?php echo get_sub_field('phone_'.$lang, 'option'); ?>"><?php echo get_sub_field('phone_'.$lang, 'option'); ?></a></p>
										<?php endwhile; ?>
									</div>
								<?php endif; ?>
								<?php if (get_field('email_'.$lang, 'option')): ?>
									<div class="info-wrp email-info-wrp rm-last-child-margin">
										<p><a href="mailto:<?php echo get_field('email_'.$lang, 'option'); ?>"><?php echo get_field('email_'.$lang, 'option'); ?></a></p>
									</div>
								<?php endif ?>
								<?php if (get_field('working_hours_'.$lang, 'option')): ?>
									<div class="info-wrp work-info-wrp rm-last-child-margin">
										<?php echo get_field('working_hours_'.$lang, 'option') ?>
									</div>
								<?php endif ?>
								<?php if (get_field('address_location_'.$lang, 'option')): ?>
									<div class="info-wrp location-info-wrp rm-last-child-margin">
										<p><?php echo get_field('address_location_'.$lang, 'option') ?></p>
									</div>
								<?php endif ?>
								<?php if ( have_rows( 'social_networks_'.$lang, 'option' ) ) : ?>
									<div class="info-wrp social-info-wrp">
										<h3>Social Networks</h3>
										<div class="social-icons">
											<?php while ( have_rows( 'social_networks_'.$lang, 'option' ) ) : the_row(); ?>
												<a href="<?php echo get_sub_field( 'url_'.$lang, 'option' ); ?>" target="_blank">
													<img data-uk-svg alt="<?php echo get_sub_field('header_icon_'.$lang, 'option')['alt']; ?>" src="<?php echo get_sub_field('header_icon_'.$lang, 'option')['url']; ?>">
												</a>
											<?php endwhile; ?>
										</div>
									</div>
								<?php endif; ?>
							</div>
							<?php if (get_field('main_contact_form_shortcode_'.$lang, 'option')):
								$shortcode = get_field('main_contact_form_shortcode_'.$lang, 'option');
							?>
								<div class="uk-width-3-5@l animation-fade-item">
									<?php echo do_shortcode( $shortcode ); ?>
								</div>
							<?php endif ?>
						</div>
					</div>
					<?php if (get_field('address_iframe_'.$lang, 'option')): ?>
						<div class="map-wrp animation-fade-item"><?php echo get_field('address_iframe_'.$lang, 'option'); ?></div>
					<?php endif ?>
				</section>
			</div>
		</main>
		<footer class="wp-block-template-part site-footer">
			<?php block_footer_area(); ?>
		</footer>
	</div>
	<?php wp_footer(); ?>
</body>