<?php
/**
 * Block Name: Footer Block
 *
 * This is the template that displays the Footer block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package simple-block
 **/

if ( isset( $block['data']['preview_image_help'] ) ) :    /* rendering in inserter preview  */
	echo '<img src="' . esc_url( get_template_directory_uri() ) . esc_attr( $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';

else : /* Rendering in editor body. */
	$lang = pll_current_language( 'slug' );
	?>

	<div class="footer-inner">

		<?php /* ── Row 1: Logo ── */ ?>
		<?php if ( get_field( 'footer_logo_' . $lang, 'option' ) ) : ?>
			<div class="footer-logo-row">
				<a class="footer-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php bloginfo( 'name' ); ?>">
					<img
						src="<?php echo esc_url( get_field( 'footer_logo_' . $lang, 'option' )['sizes']['medium'] ); ?>"
						alt="<?php echo esc_attr( get_field( 'footer_logo_' . $lang, 'option' )['alt'] ); ?>"
					>
				</a>
			</div>
		<?php endif; ?>

		<?php /* ── Row 2: 4 columns ── */ ?>
		<div class="footer-columns uk-grid uk-grid-large" uk-grid>

			<?php /* Col 1: Newsletter subscribe form */ ?>
			<div class="footer-col footer-newsletter uk-width-1-2@m uk-width-expand@l">
				<div class="footer-col-inner">
					<?php if ( $lang === 'en' ) : ?>
						<?php echo do_shortcode( '[contact-form-7 id="f49eeaa" title="Newsletter En"]' ); ?>
					<?php else : ?>
						<?php echo do_shortcode( '[contact-form-7 id="0827c8f" title="Newsletter Sr"]' ); ?>
					<?php endif; ?>
				</div>
			</div>

			<?php /* Col 2: Events Calendar */ ?>
			<?php
			$events = tribe_get_events( [
				'posts_per_page' => 3,
				'start_date'     => 'now',
				] );

			if ( ! empty( $events ) ) : ?>
				<div class="footer-col footer-calendar uk-width-1-2@m uk-width-1-4@l">
					<div class="footer-col-inner">
					<?php if ( $lang === 'en' ) : ?>
						<h2 class="footer-calendar-title">Calendar</h3>
					<?php else : ?>
						<h2 class="footer-calendar-title">Kalendar</h3>
					<?php endif; ?>
						<ul class="footer-events-list">
							<?php foreach ( $events as $event ) : ?>
								<li class="footer-event-item">
									<a href="<?php echo esc_url( tribe_get_event_link( $event ) ); ?>" class="footer-event-link">
										<?php if ( $lang === 'en' ) : ?>
											<span class="footer-event-date"><?php echo tribe_get_start_date( $event, true, 'F j, Y' ); ?></span>
										<?php else : ?>
											<span class="footer-event-date"><?php echo tribe_get_start_date( $event, true, 'd.m.Y' ); ?></span>
										<?php endif; ?>
										<span class="footer-event-title"><?php echo esc_html( $event->post_title ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>

			<?php /* Col 3: Footer Menu */ ?>
			<div class="footer-col footer-nav uk-width-1-2@m uk-width-1-4@l">
				<div class="footer-col-inner">
					<?php if ( $lang === 'en' ) : ?>
						<h2 class="footer-nav-title">Useful Links</h2>
					<?php else : ?>
						<h2 class="footer-nav-title">Korisni Linkovi</h2>
					<?php endif; ?>
					<?php
					$footer_menu_title = ( $lang === 'en' ) ? 'Footer Menu EN' : 'Footer Menu SR';
					$footer_nav_posts  = get_posts( array(
						'post_type'   => 'wp_navigation',
						'title'       => $footer_menu_title,
						'post_status' => 'publish',
						'numberposts' => 1,
					) );

					if ( ! empty( $footer_nav_posts ) ) {
						echo do_blocks( '<!-- wp:navigation {"ref":' . $footer_nav_posts[0]->ID . ',"showSubmenuIcon":false,"overlayMenu":"never"} /-->' );
					}
					?>
				</div>
			</div>

			<?php /* Col 4: Social networks + phone + email */ ?>
			<div class="footer-col footer-contact uk-width-1-2@m uk-width-1-4@l">
				<div class="footer-col-inner">
					<?php if ( $lang === 'en' ) : ?>
						<h2 class="footer-contact-title">Contact</h2>
					<?php else : ?>
						<h2 class="footer-contact-title">Kontakt</h2>
					<?php endif; ?>
					<?php /* Social icons */ ?>
					<?php if ( have_rows( 'social_networks_' . $lang, 'option' ) ) : ?>
						<div class="footer-social-icons">
							<?php while ( have_rows( 'social_networks_' . $lang, 'option' ) ) : the_row(); ?>
								<?php
								$icon = get_sub_field( 'footer_icon_' . $lang, 'option' );
								$social_title = get_sub_field( 'social_network_title_' . $lang, 'option' );
								$url  = get_sub_field( 'url_' . $lang, 'option' );
								?>
								<?php if ( $url && $icon ) : ?>
									<a
										href="<?php echo esc_url( $url ); ?>"
										target="_blank"
										rel="noopener noreferrer"
										class="footer-social-link"
										aria-label="<?php echo esc_attr( $social_title ); ?>"
									>
										<?php if ( $social_title ) : ?>
											<span class="footer-social-title"><?php echo esc_html( $social_title ); ?></span>
										<?php endif; ?>
										<?php if ( $icon ) : ?>
											<img src="<?php echo esc_url( $icon['url'] ); ?>" alt="<?php echo esc_attr( $icon['alt'] ); ?>">
										<?php endif; ?>
									</a>
								<?php endif; ?>
							<?php endwhile; ?>
						</div>
					<?php endif; ?>

					<?php /* Phone numbers */ ?>
					<?php if ( have_rows( 'phone_numbers_' . $lang, 'option' ) ) : ?>
						<div class="footer-phones">
							<?php while ( have_rows( 'phone_numbers_' . $lang, 'option' ) ) : the_row(); ?>
								<?php $phone = get_sub_field( 'phone_' . $lang, 'option' ); ?>
								<?php if ( $phone ) : ?>
									<a class="footer-phone" href="tel:<?php echo esc_attr( $phone ); ?>">
										<?php echo esc_html( $phone ); ?>
									</a>
								<?php endif; ?>
							<?php endwhile; ?>
						</div>
					<?php endif; ?>

					<?php /* Email */ ?>
					<?php if ( get_field( 'email_' . $lang, 'option' ) ) : ?>
						<a class="footer-email" href="mailto:<?php echo esc_attr( get_field( 'email_' . $lang, 'option' ) ); ?>">
							<?php echo esc_html( get_field( 'email_' . $lang, 'option' ) ); ?>
						</a>
					<?php endif; ?>

				</div>
			</div>

		</div><!-- .footer-columns -->

		<?php /* ── Row 3: Bottom bar ── */ ?>
		<div class="footer-bottom">
			<p class="footer-copyright">
				<?php if ( $lang === 'en' ) : ?>
					&copy;<?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.
				<?php else : ?>
					&copy;<?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. Sva prava zadržana.
				<?php endif; ?>
			</p>
			<a class="footer-back-to-top" href="#top" uk-scroll aria-label="Back to top">
				<?php if ( $lang === 'en' ) : ?>
					Back to top ↑
				<?php else : ?>
					Na vrh ↑
				<?php endif; ?>
			</a>
		</div><!-- .footer-bottom -->

	</div><!-- .footer-inner -->

<?php endif; ?>
