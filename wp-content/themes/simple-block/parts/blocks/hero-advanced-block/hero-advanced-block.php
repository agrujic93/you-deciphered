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

// Support custom "anchor" values.
$anchor = '';
if ( ! empty( $block['anchor'] ) ) {
	$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'hero-advanced-block';
if ( ! empty( $block['className'] ) ) {
	$class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$class_name .= ' align' . $block['align'];
}
if ( $is_preview ) {
	$class_name .= ' is-admin';
}

$headline  = get_field( 'headline' ) ?: 'ADVANCED HERO BLOCK.';
$has_video = get_field( 'has_video' );
$mp4_video = get_field( 'mp4_video' );
$image_1   = get_field( 'image_1' );
$image_2   = get_field( 'image_2' );

$wrapper_classes = $class_name . ( $has_video && $mp4_video ? ' has-video' : ' no-video' );

?>
<div <?php echo $anchor; ?>class="<?php echo esc_attr( $wrapper_classes ); ?>">

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
	<div class="hero-advanced-content-wrapper uk-container uk-container-expand uk-position-relative uk-flex uk-flex-bottom<?php echo $height_class; ?>">

		<?php if ( $image_1 ) : ?>
			<img src="<?php echo esc_url( $image_1['sizes']['large'] ); ?>" alt="<?php echo esc_attr( $image_1['alt'] ?: $image_1['title'] ); ?>" class="floating-image image-1 uk-position-absolute">
		<?php endif; ?>

		<?php if ( $image_2 ) : ?>
			<img src="<?php echo esc_url( $image_2['sizes']['large'] ); ?>" alt="<?php echo esc_attr( $image_2['alt'] ?: $image_2['title'] ); ?>" class="floating-image image-2 uk-position-absolute">
		<?php endif; ?>

		<div class="hero-text-container uk-width-1-1">
			<?php if ( $headline ) : ?>
				<h1 class="hero-advanced-headline"><?php echo wp_kses_post( $headline ); ?></h1>
			<?php endif; ?>
		</div>
	</div>

</div>
