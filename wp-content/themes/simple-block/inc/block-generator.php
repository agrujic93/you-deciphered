<?php
/**
 * Simple Block Generator
 *
 * Allows creating new ACF blocks from the WP Dashboard.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the admin menu page.
 */
function sbg_register_menu_page() {
	add_theme_page(
		__( 'Block Generator', 'simple-block' ),
		__( 'Block Generator', 'simple-block' ),
		'manage_options',
		'block-generator',
		'sbg_render_generator_page'
	);
}
add_action( 'admin_menu', 'sbg_register_menu_page' );

/**
 * Render the generator page.
 */
function sbg_render_generator_page() {
	$message = '';
	$error   = '';

	if ( isset( $_POST['sbg_generate'] ) && check_admin_referer( 'sbg_generate_block', 'sbg_nonce' ) ) {
		$block_title = sanitize_text_field( $_POST['block_title'] );

		if ( empty( $block_title ) ) {
			$error = __( 'Please enter a block title.', 'simple-block' );
		} else {
			$result = sbg_create_block( $block_title );
			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$message = sprintf( __( 'Block "%s" generated successfully!', 'simple-block' ), $block_title );
			}
		}
	}

	if ( isset( $_POST['sbg_delete'] ) && check_admin_referer( 'sbg_delete_block', 'sbg_nonce' ) ) {
		$block_slug = sanitize_text_field( $_POST['delete_block_slug'] );

		if ( empty( $block_slug ) ) {
			$error = __( 'Please select a block to delete.', 'simple-block' );
		} else {
			$result = sbg_delete_block( $block_slug );
			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$message = sprintf( __( 'Block "%s" deleted successfully!', 'simple-block' ), $block_slug );
			}
		}
	}

	$custom_blocks = sbg_get_custom_blocks();

	?>
	<div class="wrap">
		<h1><?php _e( 'Simple Block Generator', 'simple-block' ); ?></h1>

		<?php if ( $message ) : ?>
			<div class="updated notice is-dismissible"><p><?php echo $message; ?></p></div>
		<?php endif; ?>

		<?php if ( $error ) : ?>
			<div class="error notice is-dismissible"><p><?php echo $error; ?></p></div>
		<?php endif; ?>

		<div style="display: flex; gap: 20px; align-items: flex-start; margin-top: 20px;">
			<!-- Generate Form -->
			<div class="card" style="max-width: 500px; flex: 1;">
				<h2><?php _e( 'Generate New Block', 'simple-block' ); ?></h2>
				<form method="post">
					<?php wp_nonce_field( 'sbg_generate_block', 'sbg_nonce' ); ?>
					<p>
						<label for="block_title"><strong><?php _e( 'Block Title:', 'simple-block' ); ?></strong></label><br>
						<input type="text" name="block_title" id="block_title" class="regular-text" placeholder="e.g. My New Block" required>
					</p>
					<p class="description">
						<?php _e( 'Creates folder, PHP, JSON, SCSS, CSS, and JS files, and registers the script in functions.php.', 'simple-block' ); ?>
					</p>
					<p>
						<input type="submit" name="sbg_generate" class="button button-primary" value="<?php _e( 'Generate Block', 'simple-block' ); ?>">
					</p>
				</form>
			</div>

			<!-- Delete Form -->
			<div class="card" style="max-width: 500px; flex: 1;">
				<h2><?php _e( 'Delete Custom Block', 'simple-block' ); ?></h2>
				<form method="post" onsubmit="return confirm('<?php _e( 'Are you sure? This will delete all files and the folder for this block!', 'simple-block' ); ?>');">
					<?php wp_nonce_field( 'sbg_delete_block', 'sbg_nonce' ); ?>
					<p>
						<label for="delete_block_slug"><strong><?php _e( 'Select Block:', 'simple-block' ); ?></strong></label><br>
						<select name="delete_block_slug" id="delete_block_slug" class="regular-text" required>
							<option value=""><?php _e( '-- Select Block --', 'simple-block' ); ?></option>
							<?php foreach ( $custom_blocks as $slug => $name ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?> (<?php echo esc_html( $slug ); ?>)</option>
							<?php endforeach; ?>
						</select>
					</p>
					<p class="description">
						<?php _e( 'Deletes the block folder, ACF JSON, and removes script registration from functions.php.', 'simple-block' ); ?>
					</p>
					<p>
						<input type="submit" name="sbg_delete" class="button button-link-delete" style="color: #d63638;" value="<?php _e( 'Delete Block', 'simple-block' ); ?>">
					</p>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Main block creation logic.
 */
function sbg_create_block( $title ) {
	$slug      = sanitize_title( $title );
	$theme_dir = get_template_directory();
	$block_dir = $theme_dir . '/parts/blocks/' . $slug;

	if ( is_dir( $block_dir ) ) {
		return new WP_Error( 'block_exists', __( 'Block folder already exists!', 'simple-block' ) );
	}

	// 1. Create Directory
	if ( ! wp_mkdir_p( $block_dir ) ) {
		return new WP_Error( 'mkdir_failed', __( 'Could not create block directory.', 'simple-block' ) );
	}

	// 2. Prepare Template Content
	$block_json = array(
		'name'        => 'acf/' . $slug,
		'title'       => $title,
		'description' => 'A custom ' . $title . ' block.',
		'style'       => array( 'file:./' . $slug . '.css' ),
		'viewScript'  => array( $slug . '-js' ),
		'category'    => 'custom-blocks',
		'icon'        => 'layout',
		'keywords'    => array( $slug ),
		'supports'    => array(
			'align'   => array( 'full', 'wide' ),
			'spacing' => array( 'margin' => true, 'padding' => true ),
			'anchor'  => true,
		),
		'acf'         => array(
			'mode'           => 'auto',
			'renderTemplate' => $slug . '.php',
		),
		'align'       => 'full',
	);

	$php_content = "<?php\n/**\n * {$title} Block Template.\n *\n * @param array \$block The block settings and attributes.\n */\n\n\$id = '{$slug}-' . \$block['id'];\nif ( ! empty( \$block['anchor'] ) ) {\n\t\$id = \$block['anchor'];\n}\n\n\$classes = '{$slug}-block ci-block';\nif ( ! empty( \$block['className'] ) ) {\n\t\$classes .= ' ' . \$block['className'];\n}\nif ( ! empty( \$block['align'] ) ) {\n\t\$classes .= ' align' . \$block['align'];\n}\n\n\$container_class = 'section-full-width';\nif ( 'wide' == \$block['align'] ) {\n\t\$container_class = 'section-container-wide';\n} elseif ( '' == \$block['align'] || 'center' == \$block['align'] ) {\n\t\$container_class = 'section-container';\n} elseif ( 'left' == \$block['align'] ) {\n\t\$container_class = 'container-left';\n} elseif ( 'right' == \$block['align'] ) {\n\t\$container_class = 'container-right';\n}\n?>\n\n<section id=\"<?php echo esc_attr( \$id ); ?>\" class=\"<?php echo esc_attr( \$classes ); ?>\">\n\t<div class=\"<?php echo esc_attr( \$container_class ); ?>\">\n\t\t<h2><?php echo esc_html( get_field( 'title' ) ?: '{$title}' ); ?></h2>\n\t</div>\n</section>\n";

	$scss_content = "@use 'assets/css/sass/custom/theme-variables' as *;\n\n.{$slug}-block {\n}\n";

	$css_content = "/* {$title} Block Styles */\n.{$slug}-block {\n}\n";

	$js_content = "/**\n * {$title} Script.\n */\njQuery(document).ready(function($) {\n\t// Your logic here\n\tconsole.log('{$title} block loaded');\n});\n";

	// 3. Write Files
	file_put_contents( $block_dir . '/block.json', json_encode( $block_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	file_put_contents( $block_dir . '/' . $slug . '.php', $php_content );
	file_put_contents( $block_dir . '/' . $slug . '.scss', $scss_content );
	file_put_contents( $block_dir . '/' . $slug . '.css', $css_content );
	file_put_contents( $block_dir . '/' . $slug . '.js', $js_content );

	// 4. Create preview image (placeholder)
	$hero_preview = $theme_dir . '/parts/blocks/hero-block/preview.png';
	if ( file_exists( $hero_preview ) ) {
		copy( $hero_preview, $block_dir . '/preview.png' );
	}

	// 5. Generate ACF JSON
	sbg_generate_acf_json( $title, $slug );

	// 6. Update functions.php JS registration
	sbg_update_functions_php( $slug );

	return true;
}

/**
 * Generate a blank ACF JSON file.
 */
function sbg_generate_acf_json( $title, $slug ) {
	$group_key = 'group_' . uniqid();
	$acf_json  = array(
		'key'      => $group_key,
		'title'    => 'Block: ' . $title,
		'fields'   => array(
			array(
				'key'               => 'field_' . uniqid(),
				'label'             => 'Title',
				'name'              => 'title',
				'type'              => 'text',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array( 'width' => '', 'class' => '', 'id' => '' ),
				'default_value'     => '',
				'placeholder'       => '',
				'prepend'           => '',
				'append'            => '',
				'maxlength'         => '',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'acf/' . $slug . '-block',
				),
			),
		),
		'active'   => true,
		'modified' => time(),
	);

	// The block name in location should be the same as in block.json
	$acf_json['location'][0][0]['value'] = 'acf/' . $slug;

	$upload_dir   = wp_upload_dir();
	$acf_json_dir = trailingslashit( $upload_dir['basedir'] ) . 'acf-json';

	if ( ! is_dir( $acf_json_dir ) ) {
		wp_mkdir_p( $acf_json_dir );
	}

	file_put_contents( $acf_json_dir . '/block-' . $slug . '.json', json_encode( $acf_json, JSON_PRETTY_PRINT ) );
}

/**
 * Programmatically update functions.php to register the new block JS.
 */
function sbg_update_functions_php( $slug ) {
	$functions_path = get_template_directory() . '/functions.php';
	$content        = file_get_contents( $functions_path );

	// Look for the cwp_register_block_script function
	$search = "function cwp_register_block_script() {";

	if ( strpos( $content, $search ) !== false ) {
		$clean_slug       = str_replace( '-', '_', $slug );
		$new_registration = "\n\t// Registered for block: {$slug}\n";
		$new_registration .= "\t\$js_path_{$clean_slug} = get_template_directory() . '/parts/blocks/{$slug}/{$slug}.js';\n";
		$new_registration .= "\t\$js_url_{$clean_slug}  = get_template_directory_uri() . '/parts/blocks/{$slug}/{$slug}.js';\n";
		$new_registration .= "\twp_register_script( '{$slug}-js', \$js_url_{$clean_slug}, array('jquery'), file_exists( \$js_path_{$clean_slug} ) ? filemtime( \$js_path_{$clean_slug} ) : '1.0.0' );\n";

		// Insert before the closing brace of cwp_register_block_script
		// This is a simple logic but might be risky if functions.php is complex.
		// We'll look for the next '}' after the function start.

		$start_pos = strpos( $content, $search );
		$end_pos   = strpos( $content, '}', $start_pos );

		if ( $end_pos !== false ) {
			$content = substr_replace( $content, $new_registration, $end_pos, 0 );
			file_put_contents( $functions_path, $content );
		}
	}
}

/**
 * Get all custom blocks from parts/blocks/
 */
function sbg_get_custom_blocks() {
	$theme_dir = get_template_directory();
	$blocks_dir = $theme_dir . '/parts/blocks';
	$blocks = array();

	if ( is_dir( $blocks_dir ) ) {
		$dirs = array_filter( glob( $blocks_dir . '/*' ), 'is_dir' );
		foreach ( $dirs as $dir ) {
			$slug = basename( $dir );
			$json_file = $dir . '/block.json';
			if ( file_exists( $json_file ) ) {
				$json = json_decode( file_get_contents( $json_file ), true );
				$name = ! empty( $json['title'] ) ? $json['title'] : $slug;
				$blocks[ $slug ] = $name;
			}
		}
	}
	return $blocks;
}

/**
 * Delete a block.
 */
function sbg_delete_block( $slug ) {
	$theme_dir = get_template_directory();
	$block_dir = $theme_dir . '/parts/blocks/' . $slug;

	// 1. Delete Block Folder
	if ( is_dir( $block_dir ) ) {
		sbg_delete_dir( $block_dir );
	}

	// 2. Delete ACF JSON
	$upload_dir   = wp_upload_dir();
	$acf_json_dir = trailingslashit( $upload_dir['basedir'] ) . 'acf-json';
	$acf_json_file = $acf_json_dir . '/block-' . $slug . '.json';
	if ( file_exists( $acf_json_file ) ) {
		unlink( $acf_json_file );
	}

	// 3. Remove script from functions.php
	sbg_remove_from_functions_php( $slug );

	return true;
}

/**
 * Helper to delete a directory recursively.
 */
function sbg_delete_dir( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$files = array_diff( scandir( $dir ), array( '.', '..' ) );
	foreach ( $files as $file ) {
		( is_dir( "$dir/$file" ) ) ? sbg_delete_dir( "$dir/$file" ) : unlink( "$dir/$file" );
	}
	return rmdir( $dir );
}

/**
 * Remove script registration from functions.php
 */
function sbg_remove_from_functions_php( $slug ) {
	$functions_path = get_template_directory() . '/functions.php';
	$content        = file_get_contents( $functions_path );

	$search = "// Registered for block: {$slug}";

	if ( strpos( $content, $search ) !== false ) {
		$lines = explode( "\n", $content );
		$new_lines = array();
		$skip_until = -1;

		for ( $i = 0; $i < count( $lines ); $i++ ) {
			if ( strpos( $lines[$i], $search ) !== false ) {
				// Skip the comment line and the next 3 lines (path, url, wp_register_script)
				$skip_until = $i + 4;
				continue;
			}

			if ( $i < $skip_until ) {
				continue;
			}

			$new_lines[] = $lines[$i];
		}

		file_put_contents( $functions_path, implode( "\n", $new_lines ) );
	}
}
