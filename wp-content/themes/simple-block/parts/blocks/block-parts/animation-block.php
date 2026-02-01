<?php
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

// Get animation and duration from fields, using 'option' fallback if not set.
$animation = get_field('animation') ?: get_field('animation_option', 'option');
$duration_field = get_field('animation_duration') ?: get_field('animation_duration_option', 'option');

// Determine animation duration style.
$duration = get_animation_duration_style($duration_field);

// Output the animation data.
if ($animation) {
	if ($animation == 'default') {
		$animation = get_field('animation_option', 'option');
	}
	if ($animation == 'none') {
		$duration = "";
	}
	echo get_animation_data_attr($animation);
}
?>