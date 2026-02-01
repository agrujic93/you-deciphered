<?php
	$classes = [$main_block_class, $container_class];
	$inline_styles = [];
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

	if (count($inline_styles) > 0) {
		$wrapper_attributes = get_block_wrapper_attributes([
			'class' => implode(' ', array_map('trim', $classes)),
			'style' => implode('; ', $inline_styles) . ';',
		]);
	} else {
		$wrapper_attributes = get_block_wrapper_attributes([
			'class' => implode(' ', array_map('trim', $classes)),
		]);
	}

?>