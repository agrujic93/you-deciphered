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
	<header class="main-header">
		<div class="container">
			<div class="header-wrp">

				<!-- Branding Section -->
				<div class="site-branding">
					<a aria-label="<?php if ($lang == 'en') echo 'Link to home page'; else echo 'Link do početne stranice'; ?>" class="header-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<?php if( get_field('header_logo_'.$lang, 'option') ): ?>
							<img class="main-logo" alt="<?php echo get_field('header_logo_'.$lang, 'option')['alt']; ?>" src="<?php echo get_field('header_logo_'.$lang, 'option')['url'] ?>">
						<?php endif; ?>
					</a>
				</div>
				<!-- Navigation Section (Pill) -->
				<nav class="central-nav-pill">
					<img class="small-logo" alt="Small logo" src="<?php echo get_template_directory_uri(); ?>/assets/images/yd-small-logo-cropped.svg">
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
				</nav>

				<!-- Actions Section (Pill) -->
				<div class="actions-pill">
					<div class="lang-switcher">
						<?php
						// Using dropdown for Polylang
						pll_the_languages(array('dropdown'=>1, 'display_names_as'=>'slug', 'hide_if_no_translation'=>1));
						?>
					</div>

					<div class="search-trigger">
						<a class="uk-navbar-toggle" href="#" uk-search-icon></a>
						<div class="uk-drop" uk-drop="mode: click; pos: bottom-right; offset: 20">
							<div class="uk-card uk-card-default uk-card-body uk-card-small uk-border-rounded">
								<button class="uk-drop-close" type="button" uk-close></button>
								<form class="uk-search uk-search-navbar uk-width-1-1" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
									<input class="uk-search-input" type="text" name="s" placeholder="<?php echo ($lang == 'en') ? 'Search...' : 'Pretraži...'; ?>" autofocus />
								</form>
							</div>
						</div>
					</div>

					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
						<div class="cart-trigger">
							<a href="<?php echo wc_get_cart_url(); ?>" uk-icon="icon: cart">
								<span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
							</a>
						</div>
					<?php endif; ?>
				</div>

				<!-- Mobile Trigger -->
				<div class="open-menu uk-hidden@m">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</div>
			</div>
		</div>
	</header>

<?php endif; ?>
