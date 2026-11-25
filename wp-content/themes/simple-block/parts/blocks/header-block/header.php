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

	<?php
	$lang = pll_current_language('slug');
	$calculator_url = home_url( 'sr' === $lang ? '/sr/usluge/kalkulator-psihomatrice/' : '/services/psyhomatrix-calculator/' );
	$calculator_label = 'sr' === $lang ? 'Izračunajte svoju psihomatricu' : 'Calculate Your Psyhomatrix';
	$calculator_icon = get_template_directory_uri() . '/assets/images/calculator.svg';
	$navigation_id = wp_unique_id( 'header-navigation-' );
	$menu_open_label = 'sr' === $lang ? 'Otvori meni' : 'Open menu';
	$menu_close_label = 'sr' === $lang ? 'Zatvori meni' : 'Close menu';
	$submenu_open_label = 'sr' === $lang ? 'Otvori podmeni: %s' : 'Expand submenu: %s';
	$submenu_close_label = 'sr' === $lang ? 'Zatvori podmeni: %s' : 'Collapse submenu: %s';
	$header_logo = get_field( 'header_logo_' . $lang, 'option' );
	?>
	<header class="main-header">
		<div class="container">
			<div class="header-wrp">
				<!-- Branding Section -->
				<div class="site-branding">
					<a aria-label="<?php echo esc_attr( simple_block_pll__( 'Link to home page' ) ); ?>" class="header-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<?php if ( $header_logo ) : ?>
							<img class="main-logo" alt="<?php echo esc_attr( $header_logo['alt'] ?: $header_logo['title'] ); ?>" src="<?php echo esc_url( $header_logo['url'] ); ?>">
						<?php endif; ?>
					</a>
				</div>
				<!-- Navigation Section (Pill) -->
				<nav class="central-nav-pill" id="<?php echo esc_attr( $navigation_id ); ?>" data-submenu-open-label="<?php echo esc_attr( $submenu_open_label ); ?>" data-submenu-close-label="<?php echo esc_attr( $submenu_close_label ); ?>" data-menu-label="<?php echo esc_attr( 'sr' === $lang ? 'Glavni meni' : 'Main menu' ); ?>">
					<?php if ( $header_logo ) : ?>
						<a class="mobile-menu-logo" aria-label="<?php echo esc_attr( simple_block_pll__( 'Link to home page' ) ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<img alt="<?php echo esc_attr( $header_logo['alt'] ?: $header_logo['title'] ); ?>" src="<?php echo esc_url( $header_logo['url'] ); ?>">
						</a>
					<?php endif; ?>
					<a aria-label="<?php echo esc_attr( simple_block_pll__( 'Link to home page' ) ); ?>" class="uk-inline-block" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<img class="small-logo" alt="<?php echo esc_attr( simple_block_pll__( 'Small logo' ) ); ?>" src="<?php echo get_template_directory_uri(); ?>/assets/images/yd-small-logo-cropped-light-blue.svg">
					</a>
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
									<input class="uk-search-input" type="text" name="s" placeholder="<?php echo esc_attr( simple_block_pll__( 'Search...' ) ); ?>" autofocus />
								</form>
							</div>
						</div>
					</div>

					<div class="calculator-trigger">
						<a href="<?php echo esc_url( $calculator_url ); ?>" aria-label="<?php echo esc_attr( $calculator_label ); ?>">
							<img src="<?php echo esc_url( $calculator_icon ); ?>" alt="" width="24" height="24">
						</a>
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
				<button class="open-menu uk-hidden@m" type="button" aria-controls="<?php echo esc_attr( $navigation_id ); ?>" aria-expanded="false" aria-label="<?php echo esc_attr( $menu_open_label ); ?>" data-open-label="<?php echo esc_attr( $menu_open_label ); ?>" data-close-label="<?php echo esc_attr( $menu_close_label ); ?>">
					<span class="icon-bar" aria-hidden="true"></span>
					<span class="icon-bar" aria-hidden="true"></span>
					<span class="icon-bar" aria-hidden="true"></span>
				</button>
			</div>
		</div>
	</header>
	<a class="calculator-floating-link" href="<?php echo esc_url( $calculator_url ); ?>">
		<img src="<?php echo esc_url( $calculator_icon ); ?>" alt="" width="24" height="24">
		<span><?php echo esc_html( $calculator_label ); ?></span>
	</a>

<?php endif; ?>
