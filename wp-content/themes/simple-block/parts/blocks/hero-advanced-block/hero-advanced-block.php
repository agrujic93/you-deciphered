<?php
/**
 * Hero Advanced Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or it's parent block.
 */

if ( ! empty( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'hero-advanced-block-' . $block['id'];
}

// Create class attribute allowing for custom "className" and "align" values.
$main_block_class = 'hero-advanced-block';
if ( ! empty( $block['className'] ) ) {
	$main_block_class .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$main_block_class .= ' align' . $block['align'];
}
if ( $is_preview ) {
	$main_block_class .= ' is-admin';
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

$headline  = get_field( 'headline' ) ?: 'ADVANCED HERO BLOCK.';
$has_video = get_field( 'has_video' );
$mp4_video = get_field( 'mp4_video' );
$image_1   = get_field( 'image_1' );
$image_2   = get_field( 'image_2' );

if ( $has_video && $mp4_video ) {
	$main_block_class .= ' has-video';
} else {
	$main_block_class .= ' no-video';
}

include __DIR__ . '/../block-parts/block-general-logic.php';

?>
<section id="<?php echo esc_attr( $block_id ); ?>" data-theme="<?php echo esc_attr( $color_variant ); ?>" <?php echo $wrapper_attributes; ?>>
	<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>

	<?php if ( $has_video && $mp4_video ) : ?>
		<div class="video-background-container uk-position-cover">
			<video autoplay loop muted playsinline class="uk-cover" data-uk-cover>
				<source src="<?php echo esc_url( $mp4_video['url'] ); ?>" type="video/mp4">
			</video>
		</div>
		<div class="video-overlay uk-position-cover"></div>
	<?php endif; ?>

	<?php
	$height_class = ( $has_video && $mp4_video ) ? ' uk-height-100vh' : '';
	?>
	<div class="hero-advanced-content-wrapper uk-position-relative uk-flex uk-flex-bottom<?php echo $height_class; ?>">

		<?php if ( $image_1 ) : ?>
			<img src="<?php echo esc_url( $image_1['sizes']['large'] ); ?>" alt="<?php echo esc_attr( $image_1['alt'] ?: $image_1['title'] ); ?>" class="floating-image image-1 uk-position-absolute animation-fade-item">
		<?php endif; ?>

		<?php if ( $image_2 ) : ?>
			<img src="<?php echo esc_url( $image_2['sizes']['large'] ); ?>" alt="<?php echo esc_attr( $image_2['alt'] ?: $image_2['title'] ); ?>" class="floating-image image-2 uk-position-absolute animation-fade-item">
		<?php endif; ?>

		<div class="hero-text-container uk-width-1-1">
			<?php if ( $headline ) : ?>
				<h1 class="hero-advanced-headline"><?php echo wp_kses_post( $headline ); ?></h1>
			<?php endif; ?>
		</div>
	</div>

</section>
