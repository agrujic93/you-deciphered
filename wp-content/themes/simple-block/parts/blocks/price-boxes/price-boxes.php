<?php

/**
 * Price Boxes Block Template.
 *
 * @param array $block The block settings and attributes.
 */

if (isset($block['data']['preview_image_help'])) {
	echo '<img src="' . esc_url(get_template_directory_uri()) . esc_attr($block['data']['preview_image_help']) . '" style="width:100%; height:auto;" alt="">';
	return;
}

$id = ! empty($block['anchor']) ? $block['anchor'] : 'price-boxes-' . $block['id'];
$main_block_class = 'price-boxes-block ci-block';
$alignment = $block['align'] ?? '';
$container_class = 'section-full-width';

if ('wide' === $alignment) {
	$container_class = 'section-container-wide';
} elseif ('' === $alignment || 'center' === $alignment) {
	$container_class = 'section-container';
} elseif ('left' === $alignment) {
	$container_class = 'container-left';
} elseif ('right' === $alignment) {
	$container_class = 'container-right';
}

include __DIR__ . '/../block-parts/block-general-logic.php';

$intro = get_field('intro');
?>

<section data-theme="<?php echo esc_attr($color_variant); ?>" id="<?php echo esc_attr($id); ?>" <?php echo $wrapper_attributes; ?>>
	<?php include __DIR__ . '/../block-parts/block-options-visuals.php'; ?>
	<div class="container" <?php echo $animation_data_attr; ?>>
		<?php if ($intro) : ?>
			<div class="price-boxes__intro rm-last-child-margin animation-fade-item" <?php echo $animation_duration_style; ?>>
				<?php echo wp_kses_post($intro); ?>
			</div>
		<?php endif; ?>

		<?php if (have_rows('price_boxes')) : ?>
			<div class="price-boxes__grid uk-grid uk-grid-small uk-child-width-1-2@m uk-child-width-1-3@l" data-uk-grid>
				<?php while (have_rows('price_boxes')) : the_row(); ?>
					<?php
					$title       = get_sub_field('title');
					$subtitle    = get_sub_field('subtitle');
					$price       = get_sub_field('price');
					$description = get_sub_field('description');
					?>
					<div class="price-boxes__column animation-fade-item" <?php echo $animation_duration_style; ?>>
						<article class="price-box">
							<div class="price-box__content">
								<?php if ($title) : ?>
									<h3 class="price-box__title"><?php echo esc_html($title); ?></h3>
								<?php endif; ?>
								<?php if ($subtitle) : ?>
									<p class="price-box__subtitle"><?php echo esc_html($subtitle); ?></p>
								<?php endif; ?>
								<?php if (false !== $price && null !== $price && '' !== $price) : ?>
									<p class="price-box__price"><?php echo esc_html($price); ?></p>
								<?php endif; ?>
								<?php if ($description) : ?>
									<div class="price-box__description rm-last-child-margin">
										<?php echo wp_kses_post($description); ?>
									</div>
								<?php endif; ?>
							</div>

							<?php if (have_rows('ci-clone-buttons')) : ?>
								<div class="price-box__buttons">
									<?php $ci_button_context = $title; ?>
									<?php include __DIR__ . '/../block-parts/inc-buttons.php'; ?>
									<?php unset( $ci_button_context ); ?>
								</div>
							<?php endif; ?>
						</article>
					</div>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>
	</div>
</section>