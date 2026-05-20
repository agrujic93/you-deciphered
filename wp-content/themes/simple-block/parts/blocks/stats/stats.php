<?php
/**
 * Stats Block Template.
 *
 * @param array $block The block settings and attributes.
 */

if ( isset( $block['data']['preview_image_help'] ) ) {
	echo '<img src="' . esc_url( get_template_directory_uri() ) . esc_attr( $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';
	return;
}

$id = 'ci-stats-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$main_block_class = 'ci-stats-block ci-block';

$container_class = 'section-full-width';
if ( 'wide' == $block['align'] ) {
	$container_class = 'section-container-wide';
} elseif ( '' == $block['align'] || 'center' == $block['align'] ) {
	$container_class = 'section-container';
} elseif ( 'left' == $block['align'] ) {
	$container_class = 'container-left';
} elseif ( 'right' == $block['align'] ) {
	$container_class = 'container-right';
}

include __DIR__ . '/../block-parts/block-general-logic.php';

$intro      = get_field( 'intro' );
$statistics = get_field( 'statistics' );
?>

<section data-theme="<?php echo esc_attr( $color_variant ); ?>" id="<?php echo esc_attr( $id ); ?>" <?php echo $wrapper_attributes; ?>>
	<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>
	<div class="container" <?php echo $animation_data_attr; ?>>
		<?php if ( $intro ) : ?>
			<div class="stats-block__intro rm-last-child-margin animation-fade-item" <?php echo $animation_duration_style; ?>>
				<?php echo wp_kses_post( $intro ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $statistics ) ) : ?>
			<div class="stats-block__panel animation-fade-item" <?php echo $animation_duration_style; ?>>
				<div class="uk-grid uk-grid-small uk-child-width-1-2@s uk-child-width-1-4@l" data-uk-grid>
					<?php
					$total_stats   = count( $statistics );
					$display_items = array_slice( $statistics, 0, 3 ); // First 3 items

					if ( $total_stats > 3 ) {
						// Add 2 blank placeholders at positions 4-5
						$display_items[] = null;
						$display_items[] = null;
						// Add remaining items (from position 6 onwards)
						$display_items = array_merge( $display_items, array_slice( $statistics, 3 ) );
					}

					foreach ( $display_items as $stat ) :
						if ( is_null( $stat ) ) :
							?>
							<div class="stats-block__item-wrp">
								<div class="stats-block__item blank__item"></div>
							</div>
							<?php
						else :
							$raw_number = isset( $stat['number'] ) ? (string) $stat['number'] : '0';
							$number     = is_numeric( $raw_number ) ? (float) $raw_number : 0;
							$decimals   = 0;

							if ( false !== strpos( $raw_number, '.' ) ) {
								$decimals = strlen( substr( strrchr( $raw_number, '.' ), 1 ) );
							}

							$display_number = number_format_i18n( $number, $decimals );
							$suffix         = isset( $stat['number_sufix'] ) ? $stat['number_sufix'] : '';
							$title          = isset( $stat['title'] ) ? $stat['title'] : '';
							?>
							<div class="stats-block__item-wrp">
								<div class="stats-block__item">
									<div class="stats-block__number-wrap">
										<span
											class="stats-block__number h1 js-stats-counter uk-margin-remove"
											data-counter-target="<?php echo esc_attr( $number ); ?>"
											data-counter-decimals="<?php echo esc_attr( $decimals ); ?>"
										><?php echo esc_html( $display_number ); ?></span>
										<?php if ( '' !== $suffix ) : ?>
											<span class="stats-block__suffix h1 uk-margin-remove"><?php echo esc_html( $suffix ); ?></span>
										<?php endif; ?>
									</div>

									<?php if ( '' !== $title ) : ?>
										<p class="stats-block__title uk-margin-remove uk-text-small"><?php echo esc_html( $title ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php
						endif;
					endforeach;
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
