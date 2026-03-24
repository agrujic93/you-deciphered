<?php
/**
 * Block Name: Icon - Text Features Block
 *
 * This is the template that displays the Grid block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

// Create id attribute for specific styling and anchor tag.

if ( isset( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-icon-text-features-block-' . $block['id'];
}

$main_block_class = 'ci-icon-text-features-block ci-block';
$container_class  = 'section-full-width';
if ( 'wide' == $block['align'] ) {
	$container_class = 'section-container-wide';
} elseif ( '' == $block['align'] || 'center' == $block['align'] ) {
	$container_class = 'section-container';
} elseif ( 'left' == $block['align'] ) {
	$container_class = 'container-left';
} elseif ( 'right' == $block['align'] ) {
	$container_class = 'container-right';
}
if ( isset( $block['data']['preview_image_help'] ) ) :    /* rendering in inserter preview  */
	echo '<img src="' . esc_url( get_template_directory_uri() ) . esc_attr( $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';

else : /* Rendering in editor body. */
	?>

	<?php include __DIR__ . '/../block-parts/block-general-logic.php'; ?>

	<section data-theme="<?php echo esc_attr($color_variant); ?>" id="<?php echo esc_attr($block_id); ?>" <?php echo $wrapper_attributes; ?>>

		<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>

		<div class="container" <?php echo $animation_data_attr; ?> <?php echo $animation_duration_style; ?>>
			<?php if (get_field( 'features_intro' )): ?>
				<div class="rm-last-child-margin uk-margin-medium-bottom animation-fade-item" <?php echo $duration; ?>>
					<?php echo get_field('features_intro'); ?>
				</div>
			<?php endif ?>

			<?php if (get_field('features_per_row') == "one") {
				$col_num = "uk-child-width-1-1";
			} elseif (get_field('features_per_row') == "two") {
				$col_num = "uk-child-width-1-2@m";
			} elseif (get_field('features_per_row') == "three") {
				$col_num = "uk-child-width-1-3@l uk-child-width-1-2@s";
			} elseif (get_field('features_per_row') == "four") {
				$col_num = "uk-child-width-1-4@l uk-child-width-1-2@s";
			} elseif (get_field('features_per_row') == "five") {
				$col_num = "uk-child-width-1-5@l uk-child-width-1-3@m uk-child-width-1-2@s";
			} elseif (get_field('features_per_row') == "six") {
				$col_num = "uk-child-width-1-6@l uk-child-width-1-3@m uk-child-width-1-2@s";
			} else {
				$col_num = "uk-child-width-1-3@m";
			} ?>

			<?php if ( have_rows( 'features' ) ) : ?>
				<div class="features-wrp uk-flex-center uk-grid <?php echo $col_num; ?>" data-uk-grid>
					<?php while ( have_rows( 'features' ) ) : the_row(); ?>
						<div class="feature-col">
							<div class="feature-wrp uk-text-center animation-fade-item" <?php echo $duration; ?>>
								<?php if (get_sub_field('feature_icon')): ?>
									<div class="feature-icon">
										<img src="<?php echo get_sub_field('feature_icon')['sizes']['medium_large']; ?>" alt="<?php echo get_sub_field('feature_icon')['alt']; ?>">
									</div>
								<?php endif ?>
								<?php if (get_sub_field('feature_link')): ?>
									<a class="feature-link" href="<?php echo get_sub_field('feature_link') ?>"></a>
								<?php endif ?>
								<?php if (get_sub_field('text')): ?>
									<div class="feature-text rm-last-child-margin">
										<?php echo get_sub_field('text'); ?>
									</div>
								<?php endif ?>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>
