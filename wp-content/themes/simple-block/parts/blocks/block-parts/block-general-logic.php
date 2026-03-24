<?php
/**
 * Block General Logic
 * 
 * This file handles the logic for "Blocks: Global Options" field group.
 * It's included in individual block templates to avoid code repetition.
 */

// 1. Color Variant Logic
$color_variant = ! empty( get_field( 'color_variant' ) ) ? 'dark' : 'light';

// 2. Background and Text Color Logic (re-using or refining the flow)
$main_block_class = isset($main_block_class) ? $main_block_class : 'ci-block';
$container_class  = isset($container_class) ? $container_class : '';

$classes = isset($classes) ? (is_array($classes) ? $classes : explode(' ', $classes)) : [$main_block_class, $container_class];
$inline_styles = isset($inline_styles) ? $inline_styles : [];
$bg_image_id = '';

if (get_field('block_background_color')) {
    $inline_styles[] = 'background-color: ' . esc_attr(get_field('block_background_color'));
}

if (get_field('block_background_image')) {
    $bg_image_id = get_field('block_background_image');
    $bg_image_alt = get_post_meta($bg_image_id, '_wp_attachment_image_alt', TRUE);
}

if (get_field('block_background_image') || get_field('block_background_color')) {
    $classes[] = 'ci-has-background';
}

if (get_field('block_background_image') && get_field('block_background_color')) {
    $classes[] = 'ci-has-image-overlay';
}

if (get_field('block_text_color')) {
    $inline_styles[] = 'color: ' . esc_attr(get_field('block_text_color'));
    $classes[] = 'ci-has-text-color';
}

// Generate wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => implode(' ', array_map('trim', $classes)),
    'style' => !empty($inline_styles) ? implode('; ', $inline_styles) . ';' : '',
]);

// 3. Animation Logic functions (ensure they exist)
if (!function_exists('get_animation_data_attr')) {
	function get_animation_data_attr($animation) {
		switch ($animation) {
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

if (!function_exists('get_animation_duration_style')) {
	function get_animation_duration_style($duration_field, $default_duration = 600) {
		return 'style="animation-duration:' . ($duration_field ? $duration_field : $default_duration) . 'ms;"';
	}
}

// Get animation and duration
$animation = get_field('animation') ?: get_field('animation_option', 'option');
$duration_field = get_field('animation_duration') ?: get_field('animation_duration_option', 'option');
$animation_duration_style = get_animation_duration_style($duration_field);

// We'll use a variable for the animation data attribute instead of echoing it immediately
$animation_data_attr = '';
if ($animation) {
	if ($animation == 'default') {
		$animation = get_field('animation_option', 'option');
	}
	if ($animation == 'none') {
		$animation_duration_style = "";
	}
	$animation_data_attr = get_animation_data_attr($animation);
}
