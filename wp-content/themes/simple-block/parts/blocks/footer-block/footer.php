<?php
/**
 * Block Name: Header Block
 *
 * This is the template that displays the Grid block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

if ( isset( $block['data']['preview_image_help'] ) ) :    /* rendering in inserter preview  */
	echo '<img src="' . esc_url( get_template_directory_uri() ) . esc_attr( $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';

else : /* Rendering in editor body. */
	?>
	<?php $lang = pll_current_language('slug'); ?>
	<div class="container">

		<div class="footer-top uk-grid uk-grid-large uk-flex-between">
			<?php if (get_field('footer_logo_'.$lang, 'option') ): ?>
				<div class="uk-width-auto@l uk-width-1-2@m uk-margin-medium-bottom">
					<a class="footer-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<img alt="<?php echo get_field('footer_logo_'.$lang, 'option')['alt']; ?>" src="<?php echo get_field('footer_logo_'.$lang, 'option')['sizes']['medium'] ?>">
					</a>
				</div>
			<?php endif; ?>

			<?php if(get_field('headline_location_'.$lang, 'option') || get_field('address_location_'.$lang, 'option') || get_field('address_link_'.$lang, 'option')): ?>

				<div class="uk-width-expand@l uk-width-1-2@m uk-margin-medium-bottom">
					<?php if( get_field('headline_location_'.$lang, 'option') ): ?>
						<h3><?php the_field('headline_location_'.$lang, 'option'); ?></h3>
					<?php endif; ?>
					<?php if( get_field('address_location_'.$lang, 'option') ): ?>
						<p class="address"><?php the_field('address_location_'.$lang, 'option'); ?></p>
					<?php endif; ?>
					<?php
					if( get_field('address_link_'.$lang, 'option') ):
						$link = get_field('address_link_'.$lang, 'option');
						$link_url = $link['url'];
						$link_title = $link['title'];
						$link_target = $link['target'] ? $link['target'] : '_self';
						?>
						<a rel="noopener" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?></a>
					<?php endif; ?>
				</div>

			<?php endif; ?> <!-- address-informations -->


			<?php if(get_field('headline_contact_'.$lang, 'option') || get_field('phone_numbers_'.$lang, 'option') || get_field('email_'.$lang, 'option') || get_field('social_networks_'.$lang, 'option')): ?>

				<div class="uk-width-expand@l uk-width-1-2@m uk-margin-medium-bottom">
					<?php if( get_field('headline_contact_'.$lang, 'option') ): ?>
						<h3><?php the_field('headline_contact_'.$lang, 'option'); ?></h3>
					<?php endif; ?>

					<?php
					if( have_rows('phone_numbers_'.$lang, 'option') ):
						while ( have_rows('phone_numbers_'.$lang, 'option') ) : the_row(); ?>
							<a class="phone" href="tel:<?php the_sub_field('phone_'.$lang, 'option'); ?>"><?php the_sub_field('phone_'.$lang, 'option'); ?></a>
						<?php endwhile;
					endif;
					?>

					<?php if( get_field('email_'.$lang, 'option') ): ?>
						<a class="email" href="mailto:<?php the_field('email_'.$lang, 'option'); ?>"><?php the_field('email_'.$lang, 'option'); ?></a>
					<?php endif; ?>

					<?php if( have_rows('social_networks_'.$lang, 'option') ): ?>
						<div class="social-icons">
							<?php while( have_rows('social_networks_'.$lang, 'option') ): the_row(); ?>
								<a target="_blank" rel="noopener" href="<?php echo get_sub_field('url_'.$lang, 'option'); ?>">
									<img alt="<?php echo get_sub_field('footer_icon_'.$lang, 'option')['alt']; ?>" src="<?php echo get_sub_field('footer_icon_'.$lang, 'option')['url']; ?>">
								</a>
							<?php endwhile; ?>
						</div>
					<?php endif; ?>

				</div>

			<?php endif; ?> <!-- contact-informations -->

			<?php if( get_field('working_hours_'.$lang, 'option') ): ?>
				<div class="uk-width-expand@l uk-width-1-2@m uk-margin-medium-bottom">
					<?php the_field('working_hours_'.$lang, 'option'); ?>
				</div>
			<?php endif; ?>

		</div><!-- footer-top -->

		<div class="site-info">
			<?php if ( $lang == 'en' ) : ?>
				<p style="margin-bottom: 1rem;">Website &copy;<?php echo date('Y'); ?>. All right reserved.</p>
			<?php else : ?>
				<p style="margin-bottom: 1rem;">Sajt &copy;<?php echo date('Y'); ?>. Sva prava zadržana.</p>
			<?php endif; ?>
		</div>

	</div><!-- container -->
<?php endif; ?>
