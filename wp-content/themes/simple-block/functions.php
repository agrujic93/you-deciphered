<?php
/**
 * Simple Block functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Simple Block
 * @since Simple Block 1.0
 */

/**
 * Include Block Generator
 */
add_action( 'after_setup_theme', function() {
	require get_template_directory() . '/inc/block-generator.php';
} );

/**
 * Register block styles.
 */

if ( ! function_exists( 'simple_block_block_styles' ) ) :
	/**
	 * Register custom block styles
	 *
	 * @since Simple Block 1.0
	 * @return void
	 */
	function simple_block_block_styles() {

		register_block_style(
			'core/details',
			array(
				'name'         => 'arrow-icon-details',
				'label'        => __( 'Arrow icon', 'simple-block' ),
				/*
				 * Styles for the custom Arrow icon style of the Details block
				 */
				'inline_style' => '
				.is-style-arrow-icon-details {
					padding-top: var(--wp--preset--spacing--10);
					padding-bottom: var(--wp--preset--spacing--10);
				}

				.is-style-arrow-icon-details summary {
					list-style-type: "\2193\00a0\00a0\00a0";
				}

				.is-style-arrow-icon-details[open]>summary {
					list-style-type: "\2192\00a0\00a0\00a0";
				}',
			)
		);
		register_block_style(
			'core/post-terms',
			array(
				'name'         => 'pill',
				'label'        => __( 'Pill', 'simple-block' ),
				/*
				 * Styles variation for post terms
				 * https://github.com/WordPress/gutenberg/issues/24956
				 */
				'inline_style' => '
				.is-style-pill a,
				.is-style-pill span:not([class], [data-rich-text-placeholder]) {
					display: inline-block;
					background-color: var(--wp--preset--color--base-2);
					padding: 0.375rem 0.875rem;
					border-radius: var(--wp--preset--spacing--20);
				}

				.is-style-pill a:hover {
					background-color: var(--wp--preset--color--contrast-3);
				}',
			)
		);
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'simple-block' ),
				/*
				 * Styles for the custom checkmark list block style
				 * https://github.com/WordPress/gutenberg/issues/51480
				 */
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;

add_action( 'init', 'simple_block_block_styles' );

/**
 * Register pattern categories.
 */

if ( ! function_exists( 'simple_block_pattern_categories' ) ) :
	/**
	 * Register pattern categories
	 *
	 * @since Simple Block 1.0
	 * @return void
	 */
	function simple_block_pattern_categories() {

		register_block_pattern_category(
			'simple_block_page',
			array(
				'label'       => _x( 'Pages', 'Block pattern category', 'simple-block' ),
				'description' => __( 'A collection of full page layouts.', 'simple-block' ),
			)
		);
	}
endif;

add_action( 'init', 'simple_block_pattern_categories' );

// Adds theme support for post formats.
if ( ! function_exists( 'simple_block_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 */
	function simple_block_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'simple_block_post_format_setup' );

// Registers block binding sources.
if ( ! function_exists( 'simple_block_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 */
	function simple_block_register_block_bindings() {
		register_block_bindings_source(
			'simple-block/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'simple-block' ),
				'get_value_callback' => 'simple_block_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'simple_block_register_block_bindings' );

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'simple_block_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function simple_block_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;

// Custom Body Class
function custom_body_class( $classes ) {

	$classes[] = 'animation-fade-container';

	return $classes;
}
add_filter( 'body_class', 'custom_body_class' );

// Include Scripts
function ag_uikit_scripts() {

	$uikit_js_path = get_stylesheet_directory() . '/assets/js/uikit.min.js';

	wp_enqueue_script(
		'uikit-js',
		get_stylesheet_directory_uri() . '/assets/js/uikit.min.js',
		array(),
		file_exists( $uikit_js_path ) ? filemtime( $uikit_js_path ) : '3.21.11'
	);

	$uikit_icons_js_path = get_stylesheet_directory() . '/assets/js/uikit-icons.min.js';

	wp_enqueue_script(
		'uikit-icons-js',
		get_stylesheet_directory_uri() . '/assets/js/uikit-icons.min.js',
		array(),
		file_exists( $uikit_icons_js_path ) ? filemtime( $uikit_icons_js_path ) : '3.21.11',
		array( 'strategy' => 'defer' )
	);

	$main_js_path = get_stylesheet_directory() . '/assets/js/main.js';

	wp_enqueue_script(
		'main-js',
		get_stylesheet_directory_uri() . '/assets/js/main.js',
		array( 'gsap', 'gsap-scrolltrigger' ),
		file_exists( $main_js_path ) ? filemtime( $main_js_path ) : '1.0.0',
		array( 'strategy' => 'defer' )
	);
}
add_action( 'enqueue_block_assets', 'ag_uikit_scripts' );

// Include UIkit CSS
function ag_uikit_frontend() {
	// Path to your uikit-style.css file
	$css_file_path = get_stylesheet_directory() . '/assets/css/uikit-style.css';

	// Enqueue the UIkit style with file modification time as the version number
	wp_enqueue_style(
		'uikit-style', // Handle for the stylesheet
		get_stylesheet_directory_uri() . '/assets/css/uikit-style.css', // URL to the CSS file
		array(), // Dependencies, if any
		file_exists( $css_file_path ) ? filemtime( $css_file_path ) : '1.0.0' // Version based on file modification time
	);
}
add_action( 'init', 'ag_uikit_frontend', 1 );

// Include Custom CSS on frontend
function mytheme_enqueue_block_assets() {
	// Path to your frontend-custom-style.css file
	$css_file_path = get_template_directory() . '/assets/css/frontend-custom-style.css';

	// Enqueue the frontend custom style with file modification time as the version number
	wp_enqueue_style(
		'frontend-custom-style', // Handle for the stylesheet
		get_template_directory_uri() . '/assets/css/frontend-custom-style.css', // URL to the CSS file
		array(), // Dependencies, if any
		file_exists( $css_file_path ) ? filemtime( $css_file_path ) : '1.0.0' // Version based on file modification time
	);
}
add_action( 'enqueue_block_assets', 'mytheme_enqueue_block_assets' );

// Include Custom CSS on editor
function mytheme_enqueue_block_editor_assets() {
	// Absolute path to the CSS file for filemtime
	$css_file_path = get_template_directory() . '/assets/css/backend-custom-style.css';

	// URL to the CSS file for wp_enqueue_style
	$css_file_url = get_template_directory_uri() . '/assets/css/backend-custom-style.css';

	// Enqueue the backend custom style with file modification time as the version number
	wp_enqueue_style(
		'backend-custom-style', // Handle for the stylesheet
		$css_file_url, // URL to the CSS file
		array(), // Dependencies, if any
		file_exists( $css_file_path ) ? filemtime( $css_file_path ) : '1.0.0' // Version based on file modification time
	);
}
add_action( 'enqueue_block_editor_assets', 'mytheme_enqueue_block_editor_assets' );

/**
 * Register block styles.
 */
function ci_register_blocks_styles() {
	// Absolute path to the CSS file for filemtime
	$css_file_path = get_template_directory() . '/assets/swiper/swiper-bundle.min.css';

	// URL to the CSS file for wp_enqueue_style
	$css_file_url = get_template_directory_uri() . '/assets/swiper/swiper-bundle.min.css';

	wp_register_style(
		'swiper-style', // Handle for the stylesheet
		$css_file_url, // URL to the CSS file
		array(), // Dependencies, if any
		file_exists( $css_file_path ) ? filemtime( $css_file_path ) : '1.0.0' // Version based on file modification time
	);
}
add_action( 'init', 'ci_register_blocks_styles' );

/**
 * Register block scripts.
 */
function cwp_register_block_script() {
	// Absolute path to the swiper file for filemtime
	$swiper_file_path = get_template_directory() . '/assets/swiper/swiper-bundle.min.js';

	// URL to the swiper file
	$swiper_file_url = get_template_directory_uri() . '/assets/swiper/swiper-bundle.min.js';

	wp_register_script(
		'swiper', // Handle for the stylesheet
		$swiper_file_url, // URL to the CSS file
		array('jquery'), // Dependencies, if any
		file_exists( $swiper_file_path ) ? filemtime( $swiper_file_path ) : '1.0.0' // Version based on file modification time
	);

	// Absolute path to the swiper file for filemtime
	$hero_slider_path = get_template_directory() . '/parts/blocks/hero-block/hero-slider.js';

	// URL to the swiper file
	$hero_slider_url = get_template_directory_uri() . '/parts/blocks/hero-block/hero-slider.js';

	wp_register_script(
		'hero-slider-js',
		$hero_slider_url, // URL to the file
		array('swiper', 'acf'), // Dependencies, if any
		file_exists( $hero_slider_path ) ? filemtime( $hero_slider_path ) : '1.0.0' // Version based on file modification time
	);

	// Absolute path to the swiper file for filemtime
	$testimonials_slider_path = get_template_directory() . '/parts/blocks/testimonials-block/testimonials-slider.js';

	// URL to the swiper file
	$testimonials_slider_url = get_template_directory_uri() . '/parts/blocks/testimonials-block/testimonials-slider.js';

	wp_register_script(
		'testimonials-slider-js',
		$testimonials_slider_url, // URL to the file
		array('swiper', 'acf'), // Dependencies, if any
		file_exists( $testimonials_slider_path ) ? filemtime( $testimonials_slider_path ) : '1.0.0' // Version based on file modification time
	);

	// Absolute path to the swiper file for filemtime
	$partners_slider_path = get_template_directory() . '/parts/blocks/partners-block/partners-slider.js';

	// URL to the swiper file
	$partners_slider_url = get_template_directory_uri() . '/parts/blocks/partners-block/partners-slider.js';

	wp_register_script(
		'partners-slider-js',
		$partners_slider_url, // URL to the file
		array('swiper', 'acf'), // Dependencies, if any
		file_exists( $partners_slider_path ) ? filemtime( $partners_slider_path ) : '1.0.0' // Version based on file modification time
	);

	// Absolute path to the header file for filemtime
	$header_js_path = get_template_directory() . '/parts/blocks/header-block/header.js';

	// URL to the header file
	$header_js_url = get_template_directory_uri() . '/parts/blocks/header-block/header.js';

	wp_register_script(
		'header-js',
		$header_js_url, // URL to the file
		array('jquery', 'gsap'), // Dependencies, if any
		file_exists( $header_js_path ) ? filemtime( $header_js_path ) : '1.0.0' // Version based on file modification time
	);

	// Absolute path to gsap for filemtime
	$gsap_path = get_template_directory() . '/assets/js/gsap.min.js';
	$gsap_url = get_template_directory_uri() . '/assets/js/gsap.min.js';
	wp_register_script( 'gsap', $gsap_url, array(), file_exists( $gsap_path ) ? filemtime( $gsap_path ) : '3.14.2' );

	// Absolute path to ScrollTrigger for filemtime
	$scrolltrigger_path = get_template_directory() . '/assets/js/ScrollTrigger.min.js';
	$scrolltrigger_url = get_template_directory_uri() . '/assets/js/ScrollTrigger.min.js';
	wp_register_script( 'gsap-scrolltrigger', $scrolltrigger_url, array('gsap'), file_exists( $scrolltrigger_path ) ? filemtime( $scrolltrigger_path ) : '3.14.2' );


	// Absolute path to the hero advanced file for filemtime
	$hero_advanced_js_path = get_template_directory() . '/parts/blocks/hero-advanced-block/hero-advanced-block.js';
	$hero_advanced_js_url = get_template_directory_uri() . '/parts/blocks/hero-advanced-block/hero-advanced-block.js';
	wp_register_script( 'hero-advanced-js', $hero_advanced_js_url, array('gsap', 'gsap-scrolltrigger', 'jquery', 'acf'), file_exists( $hero_advanced_js_path ) ? filemtime( $hero_advanced_js_path ) : '1.0.0' );

	// Absolute path to the highlighted text file for filemtime
	$highlighted_text_js_path = get_template_directory() . '/parts/blocks/highlighted-text-block/highlighted-text-block.js';
	$highlighted_text_js_url = get_template_directory_uri() . '/parts/blocks/highlighted-text-block/highlighted-text-block.js';
	wp_register_script( 'highlighted-text-js', $highlighted_text_js_url, array('gsap', 'gsap-scrolltrigger', 'acf'), file_exists( $highlighted_text_js_path ) ? filemtime( $highlighted_text_js_path ) : '1.0.0' );


	// Registered for block: steps-slider
	$js_path_steps_slider = get_template_directory() . '/parts/blocks/steps-slider/steps-slider.js';
	$js_url_steps_slider  = get_template_directory_uri() . '/parts/blocks/steps-slider/steps-slider.js';
	wp_register_script( 'steps-slider-js', $js_url_steps_slider, array('jquery', 'gsap', 'gsap-scrolltrigger', 'swiper'), file_exists( $js_path_steps_slider ) ? filemtime( $js_path_steps_slider ) : '1.0.0' );

	// Registered for block: zig-zag-boxes
	$js_path_zig_zag_boxes = get_template_directory() . '/parts/blocks/zig-zag-boxes/zig-zag-boxes.js';
	$js_url_zig_zag_boxes  = get_template_directory_uri() . '/parts/blocks/zig-zag-boxes/zig-zag-boxes.js';
	wp_register_script( 'zig-zag-boxes-js', $js_url_zig_zag_boxes, array('jquery'), file_exists( $js_path_zig_zag_boxes ) ? filemtime( $js_path_zig_zag_boxes ) : '1.0.0' );

	// Registered for block: services-slider
	$js_path_services_slider = get_template_directory() . '/parts/blocks/services-slider/services-slider.js';
	$js_url_services_slider  = get_template_directory_uri() . '/parts/blocks/services-slider/services-slider.js';
	wp_register_script( 'services-slider-js', $js_url_services_slider, array('jquery', 'swiper'), file_exists( $js_path_services_slider ) ? filemtime( $js_path_services_slider ) : '1.0.0' );

	// Registered for block: stats
	$js_path_stats = get_template_directory() . '/parts/blocks/stats/stats.js';
	$js_url_stats  = get_template_directory_uri() . '/parts/blocks/stats/stats.js';
	wp_register_script( 'stats-js', $js_url_stats, array('jquery'), file_exists( $js_path_stats ) ? filemtime( $js_path_stats ) : '1.0.0' );

}
add_action( 'init', 'cwp_register_block_script' );

/**
 * Create Blocks.
 */
function create_custom_blocks() {
	// get an array of all of the block.json files in my blocks directory
	$block_json_files = glob( get_template_directory() . '/parts/blocks/**/block.json' );
	// auto register all blocks that were found.
	foreach ( $block_json_files as $block_json_file ) {
		register_block_type( $block_json_file );
	}
}

add_action( 'init', 'create_custom_blocks' );

/**
 * Filter block categories when post is provided.
 *
 * @param array        $block_categories The list of block categories.
 * @param WP_Post|null $editor_context   The post object or null if not available.
 * @return array The modified list of block categories.
 */
function filter_block_categories_when_post_provided( $block_categories, $editor_context ) {
	$custom_category_one = array();
	if ( ! empty( $editor_context->post ) ) {
		$custom_category_one = array(
			'slug'  => 'custom-blocks',
			'title' => __( 'Custom Blocks', 'custom-plugin' ),
			'icon'  => null,
		);
		array_unshift( $block_categories, $custom_category_one );
	}
	return $block_categories;
}

add_filter( 'block_categories_all', 'filter_block_categories_when_post_provided', 10, 2 );

/**
 * ACF Local JSON to /uploads/acf-json with slugged filenames
 * - Saves & loads from uploads
 * - Renames files to a slug from the group title (dashes)
 * - Avoids slug collisions by appending -{group_key} when needed
 * - Cleans up old group_*.json and duplicate slug files
 */

/**
 * Ensure uploads/acf-json exists and return its path.
 */
function my_acf_json_dir_path(): string {
	$upload_dir   = wp_upload_dir();
	$acf_json_dir = trailingslashit( $upload_dir['basedir'] ) . 'acf-json';

	// Create the directory if it doesn't exist.
	if ( ! is_dir( $acf_json_dir ) ) {
		wp_mkdir_p( $acf_json_dir );
	}

	return $acf_json_dir;
}

/**
 * Save all ACF JSON to uploads/acf-json.
 */
function my_acf_json_save_path( $path ) {
	$dir = my_acf_json_dir_path();

	// As a safety net, only override if the directory is writable.
	if ( is_dir( $dir ) && is_writable( $dir ) ) {
		return $dir;
	}

	// Fall back to original path if something's off.
	return $path;
}
add_filter( 'acf/settings/save_json', 'my_acf_json_save_path' );

/**
 * Load ACF JSON from uploads/acf-json (smart: append if exists).
 */
function my_acf_json_load_path( $paths ) {
	$dir = my_acf_json_dir_path();

	// Optionally remove the default theme path (uncomment if you want uploads-only)
	// unset( $paths[0] );

	if ( is_dir( $dir ) ) {
		$paths[] = $dir;
	}

	return $paths;
}
add_filter( 'acf/settings/load_json', 'my_acf_json_load_path' );

/**
 * Build a slugged filename for the JSON.
 * NOTE: Correct filter is acf/json/save_file_name (ACF ≥ 6.2).
 */
function my_acf_json_filename( $filename, $post, $load_path ) {
	// Defensive checks.
	if ( empty( $post['title'] ) ) {
		return $filename;
	}

	// Slug from the group title (uses WP's sanitize_title for accents & safety).
	$slug = sanitize_title( $post['title'] );

	$dir       = my_acf_json_dir_path();
	$group_key = isset( $post['key'] ) ? $post['key'] : '';

	// Preferred filename.
	$preferred = $slug . '.json';
	$preferred_path = trailingslashit( $dir ) . $preferred;

	// If a file with the slug already exists but belongs to a *different* group,
	// use a disambiguated filename that includes the group key.
	if ( file_exists( $preferred_path ) && $group_key ) {
		// If the existing slug.json belongs to *this* group, keep it.
		$belongs_to_slug = false;
		$json = @file_get_contents( $preferred_path );
		if ( $json ) {
			$data = json_decode( $json, true );
			if ( isset( $data['key'] ) && $data['key'] === $group_key ) {
				$belongs_to_slug = true;
			}
		}

		if ( ! $belongs_to_slug ) {
			return $slug . '-' . $group_key . '.json';
		}
	}

	// Default to plain slug.json
	return $preferred;
}
add_filter( 'acf/json/save_file_name', 'my_acf_json_filename', 10, 3 );

/**
 * Cleanup after saving a field group:
 * - Remove legacy group_{key}.json
 * - Remove any duplicate slug* files that do not match this group's key
 */
function my_acf_json_cleanup( $post ) {
	if ( empty( $post['key'] ) || empty( $post['title'] ) ) {
		return;
	}

	$dir       = my_acf_json_dir_path();
	$group_key = $post['key'];
	$slug      = sanitize_title( $post['title'] );

	// 1) Remove legacy default-named file: group_{key}.json
	$legacy = trailingslashit( $dir ) . $group_key . '.json';
	if ( file_exists( $legacy ) ) {
		@unlink( $legacy );
	}

	// 2) Remove any duplicate slug files that belong to another group.
	$pattern = trailingslashit( $dir ) . $slug . '*.json';
	foreach ( glob( $pattern ) as $file ) {
		// Keep the current group's own file(s)
		$json = @file_get_contents( $file );
		if ( ! $json ) {
			continue;
		}
		$data = json_decode( $json, true );
		if ( empty( $data['key'] ) ) {
			// If file is malformed or has no key, treat as duplicate & remove.
			@unlink( $file );
			continue;
		}
		if ( $data['key'] !== $group_key ) {
			@unlink( $file );
		}
	}
}
add_action( 'acf/update_field_group', 'my_acf_json_cleanup', 20 );

/**
 * Add custom image sizes
 */
if ( function_exists( 'add_image_size' ) ) {
	add_image_size( 'blog-thumb', 410, 410, true );
	add_image_size( 'half-content', 860, 860 );
	add_image_size( 'full-hero-size', 1920 );
}

/**
 * Modify the TinyMCE editor options to include default block palette colors.
 *
 * @param array $init An array of TinyMCE editor settings.
 * @return array The modified array of TinyMCE editor settings.
 */
function my_mce4_options( $init ) {

	// Get the default block palette colors from theme.json or WordPress defaults.
	$default_palette = wp_get_global_settings( array( 'color', 'palette', 'theme' ) );

	// Initialize an empty string for TinyMCE colors.
	$default_colours = '';

	// Loop through the palette and create the color list.
	if ( ! empty( $default_palette ) && is_array( $default_palette ) ) {
		foreach ( $default_palette as $color ) {
			// Remove the leading '#' from the color if it exists.
			$hex_color        = ltrim( $color['color'], '#' );
			$default_colours .= '"' . esc_attr( $hex_color ) . '", "' . esc_attr( $color['name'] ) . '",';
		}

		// Remove the trailing comma.
		$default_colours = rtrim( $default_colours, ',' );
	}

	// Build colour grid with the default block palette colors.
	$init['textcolor_map'] = '[' . $default_colours . ']';

	// Change the number of rows in the grid if the number of colors changes.
	// 8 swatches per row.
	$init['textcolor_rows'] = ceil( count( $default_palette ) / 8 );

	return $init;
}
add_filter( 'tiny_mce_before_init', 'my_mce4_options' );


/**
 * Add custom Formats button
 */
if ( ! function_exists( 'wysiwyg_styleselect_button' ) ) {
	function wysiwyg_styleselect_button( $buttons ) {
		array_unshift( $buttons, 'styleselect' );
		// array_unshift( $buttons, 'fontselect' );
		return $buttons;
	}
	add_filter( 'mce_buttons_2', 'wysiwyg_styleselect_button' );
}

if ( ! function_exists( 'wysiwyg_style_formats' ) ) {
	function wysiwyg_style_formats( $settings ) {
		$settings['theme_advanced_blockformats'] = 'p,a,div,span,h1,h2,h3,h4,h5,h6,tr,';
		$style_formats = [
			array(
				'title'	=> __( 'Custom Elements', 'text_domain' ),
				'items'	=> [
					[
						'title'		=> __( 'Button', 'text_domain' ),
						'selector'	=> 'a',
						'classes'	=> 'btn'
					],
					[
						'title'		=> __( 'Label', 'text_domain' ),
						'selector'	=> 'p,a,h1,h2,h3,h4,h5,h6',
						'classes'	=> 'ci-label'
					],
				],
			),
			array(
				'title'	=> __( 'Font Sizes', 'text_domain' ),
				'items'	=> [
					[
						'title'		=> __( 'Heading 1', 'text_domain' ),
						'selector'	=> 'p,a,h1,h2,h3,h4,h5,h6',
						'classes'	=> 'h1'
					],
					[
						'title'		=> __( 'Heading 2', 'text_domain' ),
						'selector'	=> 'p,a,h1,h2,h3,h4,h5,h6',
						'classes'	=> 'h2'
					],
					[
						'title'		=> __( 'Heading 3', 'text_domain' ),
						'selector'	=> 'p,a,h1,h2,h3,h4,h5,h6',
						'classes'	=> 'h3'
					],
					[
						'title'		=> __( 'Heading 4', 'text_domain' ),
						'selector'	=> 'p,a,h1,h2,h3,h4,h5,h6',
						'classes'	=> 'h4'
					],
					[
						'title'		=> __( 'Heading 5', 'text_domain' ),
						'selector'	=> 'p,a,h1,h2,h3,h4,h5,h6',
						'classes'	=> 'h5'
					],
					[
						'title'		=> __( 'Heading 6', 'text_domain' ),
						'selector'	=> 'p,a,h1,h2,h3,h4,h5,h6',
						'classes'	=> 'h6'
					],
				],
			),
		];
		$settings['style_formats'] = json_encode( $style_formats );
		return $settings;
	}
	add_filter( 'tiny_mce_before_init', 'wysiwyg_style_formats' );
}

/**
 * Add default block palette colors in ACF color picker
 */
function klf_acf_input_admin_footer() {
	// Get the default block palette colors from theme.json or WordPress defaults.
	$default_palette = wp_get_global_settings( array( 'color', 'palette', 'theme' ) );

	// Initialize an array to hold the hex codes of the colors.
	$palette_colors = array();

	// Loop through the palette and add each color to the array.
	if ( ! empty( $default_palette ) && is_array( $default_palette ) ) {
		foreach ( $default_palette as $color ) {
			// Add the color to the palette array.
			$palette_colors[] = esc_js( $color['color'] );
		}
	}

	// Convert the palette array into a JavaScript-friendly format.
	$js_palette = json_encode( $palette_colors );
	?>

	<script type="text/javascript">
		(function($) {
			acf.add_filter('color_picker_args', function( args, $field ){
				// Use the dynamically generated palette colors
				args.palettes = <?php echo $js_palette; ?>;
				// Return the colors
				return args;
			});
		})(jQuery);
	</script>

	<?php
}
add_action( 'acf/input/admin_footer', 'klf_acf_input_admin_footer' );

add_theme_support( 'title-tag' );

function add_google_analytics() { ?>
	<!-- Google tag (gtag.js) -->
	<!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-MQ7BGNLNX4"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-MQ7BGNLNX4');
	</script> -->
	<?php
}
add_action('wp_head', 'add_google_analytics');

/**
 * Add WooCommerce Support
 */
function simple_block_add_woocommerce_support() {
	// Basic WooCommerce support
	add_theme_support( 'woocommerce' );

	// Enable WooCommerce product gallery features
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}

add_action( 'after_setup_theme', 'simple_block_add_woocommerce_support' );

/**
 * Register translatable theme strings for Polylang.
 *
 * @return void
 */
function simple_block_register_polylang_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	$strings = array(
		'All',
		'Load More',
		'Read More',
		'Read More about %s',
		'By %s',
		'Invalid block ID.',
		'Click to edit Blog block',
		'Link to home page',
		'Small logo',
		'Search...',
		'Calendar',
		'Useful Links',
		'Contact',
		'All rights reserved.',
		'Back to top',
		'Back to top ↑',
		'Link to the post',
		'Link to the %s',
		'Learn More',
		'Learn More about %s',
		'thumbnail logo',
		'View All',
		'No.',
		'Service Name',
		'Price',
		'Box Link',
		'Zig Zag Image',
	);

	foreach ( $strings as $string ) {
		pll_register_string( 'simple-block', $string, 'simple-block' );
	}
}
add_action( 'init', 'simple_block_register_polylang_strings' );

/**
 * Safe Polylang translator helper with fallback.
 *
 * @param string $string String to translate.
 * @return string
 */
function simple_block_pll__( $string ) {
	if ( function_exists( 'pll__' ) ) {
		return pll__( $string );
	}

	return $string;
}

/**
 * Include blog block backend logic from block folder.
 */
require_once get_template_directory() . '/parts/blocks/blog-block/blog-block-functions.php';