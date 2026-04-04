<?php
/**
 * Sticky Columns Block Template.
 *
 * @param array $block The block settings and attributes.
 */

if ( ! empty( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'sticky-columns-' . $block['id'];
}

$main_block_class = 'sticky-columns-block ci-block';

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

$left_column = get_field('left_column');
$right_column = get_field('right_column');
$sticky_column = get_field('sticky_column');
?>

<section data-theme="<?php echo esc_attr( $color_variant ); ?>" id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?>>
	<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>
	<div class="container" <?php echo $animation_data_attr; ?> <?php echo $animation_duration_style; ?>>
		<?php if ( $left_column || $right_column ) : ?>
			<div class="uk-grid uk-grid-large sticky-columns-grid" data-uk-grid>
				<div class="uk-width-1-2@m left-column animation-fade-item">
					<div class="sticky-column-content <?php echo ! $sticky_column ? 'is-sticky' : ''; ?>">
						<?php echo $left_column; ?>
					</div>
				</div>
				<div class="uk-width-1-2@m right-column animation-fade-item">
					<div class="sticky-column-content <?php echo $sticky_column ? 'is-sticky' : ''; ?>">
						<?php echo $right_column; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>