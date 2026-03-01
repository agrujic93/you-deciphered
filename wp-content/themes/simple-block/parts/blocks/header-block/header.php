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
	<div class="top-menu">
		<div class="container">
			<div class="top-menu-wrp">
				<div class="contact-informations">
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
				</div>

				<?php if( have_rows('social_networks_'.$lang, 'option') ): ?>
					<div class="social-icons">
						<?php while( have_rows('social_networks_'.$lang, 'option') ): the_row(); ?>
							<a target="_blank" aria-label="Link do društvene mreže <?php echo get_sub_field('header_icon_'.$lang, 'option')['alt']; ?>" rel="noopener" href="<?php echo get_sub_field('url_'.$lang, 'option'); ?>">
								<img alt="<?php echo get_sub_field('header_icon_'.$lang, 'option')['alt']; ?>" src="<?php echo get_sub_field('header_icon_'.$lang, 'option')['url']; ?>">
							</a>
						<?php endwhile; ?>
					</div>
				<?php endif; ?>
				<?php pll_the_languages(array('dropdown'=>1, 'display_names_as'=>'slug', 'hide_if_no_translation'=>1));  ?>
			</div>
		</div>
	</div>
	<div class="bottom-menu" data-uk-sticky="cls-active: uk-navbar-sticky;">
		<div class="container">
			<div class="bottom-menu-wrp">
				<?php if( get_field('header_logo_'.$lang, 'option') ): ?>
					<div class="site-branding">
						<a aria-label="<?php if ($lang == 'en') echo 'Link to home page'; else echo 'Link do početne stranice'; ?>" class="header-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<img alt="<?php echo get_field('header_logo_'.$lang, 'option')['alt']; ?>" src="<?php echo get_field('header_logo_'.$lang, 'option')['sizes']['medium'] ?>">
						</a>
					</div><!-- .site-branding -->
				<?php endif; ?>

				<div class="navigation-wrp">
					<?php
					$menu_title = ( $lang === 'en' ) ? 'Main Menu EN' : 'Main Menu SR';
					$nav_posts  = get_posts( array(
						'post_type'   => 'wp_navigation',
						'title'       => $menu_title,
						'post_status' => 'publish',
						'numberposts' => 1,
					) );

					if ( ! empty( $nav_posts ) ) {
						echo do_blocks( '<!-- wp:navigation {"ref":' . $nav_posts[0]->ID . ',"showSubmenuIcon":false,"overlayMenu":"never"} /-->' );
					} else {
						// Fallback if the menu by title is not found
						echo do_blocks( '<!-- wp:navigation {"ref":4,"showSubmenuIcon":false,"overlayMenu":"never"} /-->' );
					}
					?>
					<div class="search-wrp">
						<a class="uk-navbar-toggle" data-uk-search-icon href="#"></a>
						<div class="uk-drop" uk-drop="mode: click; pos: left-center; offset: 0">
							<form class="uk-search uk-search-navbar uk-width-1-1" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
								<label for="search"></label>
								<input class="uk-search-input" type="text" name="s" id="search" value="<?php the_search_query(); ?>" autofocus />
								<input class="search-btn" type="submit" alt="Search"/>
							</form>
						</div>
					</div>

				</div>

				<div class="open-menu">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</div><!-- mobile-menu-trigger -->
			</div>
		</div>

	</div>

<?php endif; ?>
