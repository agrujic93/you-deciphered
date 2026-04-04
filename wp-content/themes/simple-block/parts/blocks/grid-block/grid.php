<?php
/**
 * Block Name: Grid Block
 *
 * This is the template that displays the Grid block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

if ( ! empty( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-grid-block-' . $block['id'];
}

$main_block_class = 'ci-grid-block ci-block';
$container_class  = 'section-full-width';
if ( 'wide' == $block['align'] ) {
	$container_class = 'section-container-wide';
} elseif ( '' == $block['align'] || 'center' == $block['align'] ) {
	$container_class = 'section-container';
} elseif ( 'left' == $block['align'] ) {
	$container_class = 'container-left';
} elseif ( 'right' == $block['align'] ) {
	$container_class = 'container-right';
}

$color_variant = ! empty( get_field( 'color_variant' ) ) ? 'dark' : 'light';

if ( isset( $block['data']['preview_image_help'] ) ) :    /* rendering in inserter preview  */
	echo '<img src="' . esc_url( get_template_directory_uri() ) . esc_attr( $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';
else : /* Rendering in editor body. */

	include __DIR__ . '/../block-parts/block-general-logic.php'; ?>

	<section data-theme="<?php echo esc_attr( $color_variant ); ?>" id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?>>
		<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>

		<div class="container" <?php echo $animation_data_attr; ?> <?php echo $animation_duration_style; ?>>

			<?php
			if ( ! function_exists('ci_build_column_styles') ) {
				function ci_build_column_styles( $col_key ) {
					$inline_styles = [];
					$classes       = ['rm-last-child-margin column-wrp'];

					if ( get_sub_field( "{$col_key}_text_color" ) && get_sub_field( $col_key ) ) {
						$inline_styles[] = 'color: ' . esc_attr( get_sub_field( "{$col_key}_text_color" ) );
						$classes[]       = 'ci-has-text-color';
					}

					if ( get_sub_field( "{$col_key}_background_color" ) && get_sub_field( $col_key ) ) {
						$inline_styles[] = 'background-color: ' . esc_attr( get_sub_field( "{$col_key}_background_color" ) );
						$classes[]       = 'ci-has-background';
					}

					$inline_style_string = ! empty( $inline_styles ) ? ' style="' . implode( '; ', $inline_styles ) . '"' : '';

					return [
						'styles'  => $inline_style_string,
						'classes' => implode( ' ', $classes ),
					];
				}
			}

			if ( ! function_exists('ci_render_column') ) {
				function ci_render_column( $col_key, $width_class, $duration, $column_data ) {
					if ( get_sub_field($col_key) ) {
						echo '<div class="' . esc_attr($width_class) . ' animation-fade-item" ' . $duration . '>';
						echo '<div class="' . esc_attr($column_data[$col_key]['classes']) . '"' . $column_data[$col_key]['styles'] . '>';
						echo get_sub_field($col_key);
						echo '</div></div>';
					}
				}
			}

			if ( have_rows( 'rows' ) ) :
				while ( have_rows( 'rows' ) ) : the_row();

					$columns = [
						'column_11',
						'column_12_1', 'column_12_2',
						'column_13_1', 'column_13_2', 'column_13_3',
						'column_23_1', 'column_23_2',
						'column_25_1', 'column_25_2',
						'column_35_1', 'column_35_2',
					];

					// Generate styles and classes for all columns
					$column_data = [];
					foreach ( $columns as $col ) {
						$column_data[$col] = ci_build_column_styles( $col );
					}

					$row_layout = get_sub_field('row_layout');
					$duration   = isset($duration) ? $duration : '';

					// Grid container
					?>
					<div class="uk-grid
						<?php if ( get_sub_field('column_gap') == 'big' ) echo ' uk-grid-large'; ?>
						<?php if ( get_sub_field('vertically_align_columns') == 'center' ) echo ' uk-flex-middle'; ?>
						<?php
							$horiz = get_sub_field('horizontally_align_columns');
							if ( $horiz === 'justify_center' ) echo ' uk-flex-center';
							elseif ( $horiz === 'justify_right' ) echo ' uk-flex-right';
						?>
					" data-uk-grid>
						<?php
						// Render columns by layout
						if ( $row_layout === 'one-column' ) {
							ci_render_column('column_11', 'uk-width-1-1', $duration, $column_data);
						} elseif ( $row_layout === 'two-columns-1-2-1-2' ) {
							ci_render_column('column_12_1', 'uk-width-1-2@l', $duration, $column_data);
							ci_render_column('column_12_2', 'uk-width-1-2@l', $duration, $column_data);
						} elseif ( $row_layout === 'two-columns-1-3-2-3' ) {
							ci_render_column('column_13_1', 'uk-width-1-3@l', $duration, $column_data);
							ci_render_column('column_23_2', 'uk-width-2-3@l', $duration, $column_data);
						} elseif ( $row_layout === 'two-columns-2-3-1-3' ) {
							ci_render_column('column_23_1', 'uk-width-2-3@l', $duration, $column_data);
							ci_render_column('column_13_2', 'uk-width-1-3@l', $duration, $column_data);
						} elseif ( $row_layout === 'two-columns-2-5-3-5' ) {
							ci_render_column('column_25_1', 'uk-width-2-5@l', $duration, $column_data);
							ci_render_column('column_35_2', 'uk-width-3-5@l', $duration, $column_data);
						} elseif ( $row_layout === 'two-columns-3-5-2-5' ) {
							ci_render_column('column_35_1', 'uk-width-3-5@l', $duration, $column_data);
							ci_render_column('column_25_2', 'uk-width-2-5@l', $duration, $column_data);
						} elseif ( $row_layout === 'three-columns-1-3-1-3-1-3' ) {
							ci_render_column('column_13_1', 'uk-width-1-3@l', $duration, $column_data);
							ci_render_column('column_13_2', 'uk-width-1-3@l', $duration, $column_data);
							ci_render_column('column_13_3', 'uk-width-1-3@l', $duration, $column_data);
						}
						?>
					</div>
				<?php endwhile; ?>
			<?php endif; ?>

		</div>
	</section>

<?php endif; ?>