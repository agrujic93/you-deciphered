<?php
/**
 * Universal block options logic.
 * Handles background color, text color, background image, and animation fields from the clone source.
 * Requires variables: $classes, $inline_styles.
 */

$color_variant = ! empty( get_field( 'color_variant' ) ) ? 'dark' : 'light';

// Handle Block Alignment Class
if ( ! empty( $block['align'] ) ) {
	$classes[] = 'align' . $block['align'];
}

// Handle Container Width Class based on block alignment
$container_class = 'section-full-width';
if ( isset( $block['align'] ) ) {
	if ( 'wide' == $block['align'] ) {
		$container_class = 'section-container-wide';
	} elseif ( '' == $block['align'] || 'center' == $block['align'] ) {
		$container_class = 'section-container';
	} elseif ( 'left' == $block['align'] ) {
		$container_class = 'container-left';
	} elseif ( 'right' == $block['align'] ) {
		$container_class = 'container-right';
	}
}

if ( ! empty( $container_class ) ) {
	$classes[] = $container_class;
}

$block_background_color = trim( (string) get_field( 'block_background_color' ) );
$block_text_color       = trim( (string) get_field( 'block_text_color' ) );
$bg_image_id            = (int) get_field( 'block_background_image' );

// Handle Background Color
if ( '' !== $block_background_color ) {
	$inline_styles[] = 'background-color:' . $block_background_color;
}

// Handle Background Image

if ( ! empty( $bg_image_id ) ) {
	$bg_image_alt = get_post_meta( $bg_image_id, '_wp_attachment_image_alt', true );
}

if ( ! empty( $bg_image_id ) || '' !== $block_background_color ) {
	$classes[] = 'ci-has-background';
}

if ( ! empty( $bg_image_id ) && '' !== $block_background_color ) {
	$classes[] = 'ci-has-image-overlay';
}

// Handle Text Color
if ( '' !== $block_text_color ) {
	$inline_styles[] = 'color:' . $block_text_color;
	$classes[]       = 'ci-has-text-color';
}

// Handle Animation
if ( ! function_exists( 'get_animation_data_attr' ) ) {
	function get_animation_data_attr( $animation ) {
		switch ( $animation ) {
			case 'left':
				return 'data-uk-scrollspy="cls: uk-animation-slide-left-small; target: .animation-fade-item; delay: 300; repeat: false;"';
			case 'right':
				return 'data-uk-scrollspy="cls: uk-animation-slide-right-small; target: .animation-fade-item; delay: 300; repeat: false;"';
			case 'fade':
				return 'data-uk-scrollspy="cls: uk-animation-slide-bottom-small; target: .animation-fade-item; delay: 300; repeat: false;"';
			case 'none':
				return 'data-attr="not-animated"';
			default:
				return '';
		}
	}
}

if ( ! function_exists( 'get_animation_duration_style' ) ) {
	function get_animation_duration_style( $duration_field, $default_duration = 600 ) {
		return 'style="animation-duration:' . ( $duration_field ? $duration_field : $default_duration ) . 'ms;"';
	}
}

if ( ! function_exists( 'ci_render_empty_block_placeholder' ) ) {
	function ci_render_empty_block_placeholder( $message ) {
		echo '<div class="ci-empty-block-placeholder" style="min-height: 120px; padding: 24px; border: 1px dashed #8c8f94; border-radius: 4px; background: #f6f7f7; color: #50575e; display: flex; align-items: center; justify-content: center; text-align: center;">' . esc_html( $message ) . '</div>';
	}
}

$animation      = get_field( 'animation' ) ?: get_field( 'animation_option', 'option' );
$duration_field = get_field( 'animation_duration' ) ?: get_field( 'animation_duration_option', 'option' );
$duration       = get_animation_duration_style( $duration_field );

if ( $animation ) {
	if ( $animation == 'default' ) {
		$animation = get_field( 'animation_option', 'option' );
	}
	if ( $animation == 'none' ) {
		$duration = '';
	}
	$animation_attr = get_animation_data_attr( $animation );
} else {
	$animation_attr = '';
}

// Setup wrapper attributes
$wrapper_args = [
	'class' => implode( ' ', array_unique( array_map( 'trim', $classes ) ) )
];

// Do NOT pass style through get_block_wrapper_attributes() because WordPress's
// safecss_filter_attr() strips CSS values containing parentheses (rgb, rgba, hsl, etc.).
$wrapper_attributes = get_block_wrapper_attributes( $wrapper_args );

if ( count( $inline_styles ) > 0 ) {
	$inline_style_string = esc_attr( implode( '; ', array_filter( array_map( 'trim', $inline_styles ) ) ) . ';' );
	if ( str_contains( $wrapper_attributes, 'style="' ) ) {
		$wrapper_attributes = preg_replace( '/style="([^"]*)"/', 'style="$1 ' . $inline_style_string . '"', $wrapper_attributes );
	} else {
		$wrapper_attributes .= ' style="' . $inline_style_string . '"';
	}
}
?>