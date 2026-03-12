<?php
/**
 * Sticky Columns Block Template.
 *
 * @param array $block The block settings and attributes.
 */

$id = 'sticky-columns-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$classes = 'sticky-columns-block ci-block';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$classes .= ' align' . $block['align'];
}

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

$left_column = get_field('left_column');
$right_column = get_field('right_column');
$sticky_column = get_field('sticky_column');
?>

<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
	<div class="<?php echo esc_attr( $container_class ); ?>">
		<?php if ( $left_column || $right_column ) : ?>
			<div class="uk-grid uk-grid-large sticky-columns-grid" data-uk-grid>
				<div class="uk-width-1-2@m left-column">
					<div class="sticky-column-content <?php echo ! $sticky_column ? 'is-sticky' : ''; ?>">
						<?php echo $left_column; ?>
					</div>
				</div>
				<div class="uk-width-1-2@m right-column">
					<div class="sticky-column-content <?php echo $sticky_column ? 'is-sticky' : ''; ?>">
						<?php echo $right_column; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
