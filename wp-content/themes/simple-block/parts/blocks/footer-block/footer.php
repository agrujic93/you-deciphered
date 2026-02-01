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

	<div class="container">

		<div class="footer-top uk-grid uk-grid-large uk-flex-between">
			<?php if (get_field('footer_logo', 'option') ): ?>
				<div class="uk-width-auto@l uk-width-1-2@m uk-margin-medium-bottom">
					<a class="footer-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<img alt="<?php echo get_field('footer_logo', 'option')['alt']; ?>" src="<?php echo get_field('footer_logo', 'option')['sizes']['medium'] ?>">
					</a>
				</div>
			<?php endif; ?>

			<?php if(get_field('headline_location', 'option') || get_field('address_location', 'option') || get_field('address_link', 'option')): ?>

				<div class="uk-width-expand@l uk-width-1-2@m uk-margin-medium-bottom">
					<?php if( get_field('headline_location', 'option') ): ?>
						<h3><?php the_field('headline_location', 'option'); ?></h3>
					<?php endif; ?>
					<?php if( get_field('address_location', 'option') ): ?>
						<p class="address"><?php the_field('address_location', 'option'); ?></p>
					<?php endif; ?>
					<?php
					if( get_field('address_link', 'option') ):
						$link = get_field('address_link', 'option');
						$link_url = $link['url'];
						$link_title = $link['title'];
						$link_target = $link['target'] ? $link['target'] : '_self';
						?>
						<a rel="noopener" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?></a>
					<?php endif; ?>
				</div>

			<?php endif; ?> <!-- address-informations -->


			<?php if(get_field('headline_contact', 'option') || get_field('phone_numbers', 'option') || get_field('email', 'option') || get_field('social_networks', 'option')): ?>

				<div class="uk-width-expand@l uk-width-1-2@m uk-margin-medium-bottom">
					<?php if( get_field('headline_contact', 'option') ): ?>
						<h3><?php the_field('headline_contact', 'option'); ?></h3>
					<?php endif; ?>

					<?php
					if( have_rows('phone_numbers', 'option') ):
						while ( have_rows('phone_numbers', 'option') ) : the_row(); ?>
							<a class="phone" href="tel:<?php the_sub_field('phone', 'option'); ?>"><?php the_sub_field('phone', 'option'); ?></a>
						<?php endwhile;
					endif;
					?>

					<?php if( get_field('email', 'option') ): ?>
						<a class="email" href="mailto:<?php the_field('email', 'option'); ?>"><?php the_field('email', 'option'); ?></a>
					<?php endif; ?>

					<?php if( have_rows('social_networks', 'option') ): ?>
						<div class="social-icons">
							<?php while( have_rows('social_networks', 'option') ): the_row(); ?>
								<a target="_blank" rel="noopener" href="<?php echo get_sub_field('url', 'option'); ?>">
									<img alt="<?php echo get_sub_field('footer_icon', 'option')['alt']; ?>" src="<?php echo get_sub_field('footer_icon', 'option')['url']; ?>">
								</a>
							<?php endwhile; ?>
						</div>
					<?php endif; ?>

				</div>

			<?php endif; ?> <!-- contact-informations -->

			<?php if( get_field('working_hours', 'option') ): ?>
				<div class="uk-width-expand@l uk-width-1-2@m uk-margin-medium-bottom">
					<?php the_field('working_hours', 'option'); ?>
				</div>
			<?php endif; ?>

		</div><!-- footer-top -->

		<div class="site-info">
			<p style="margin-bottom: 1rem;">Website &copy;<?php echo date('Y'); ?>. All right reserved.</p>
		</div>

	</div><!-- container -->
<?php endif; ?>
