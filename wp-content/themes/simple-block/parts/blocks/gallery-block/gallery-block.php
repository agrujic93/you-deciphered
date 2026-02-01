<?php
/**
 * Block Name: Query Posts
 *
 * This is the template that displays the Query Posts Block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

// Create id attribute for specific styling and anchor tag.

if ( isset( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-gallery-block-' . $block['id'];
}

$main_block_class = 'ci-gallery-block ci-block';
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
if ( isset( $block['data']['preview_image_help'] ) ) :    /* rendering in inserter preview  */
	echo '<img src="' . esc_url( get_template_directory_uri() ) . esc_attr( $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';

else : /* rendering in editor body */
	?>

	<?php include __DIR__ . '/../block-parts/background-and-text-color-block.php'; ?>

	<section id="<?php echo esc_attr($block_id); ?>" <?php echo $wrapper_attributes; ?>>
		<?php if ( $bg_image_id ) : ?>
			<?php
			echo wp_get_attachment_image(
				$bg_image_id,
				'full-hero-size',
				false,
				array(
					'class' => 'section-background-image',
					'alt'   => $bg_image_alt,
				)
			);
			?>
		<?php endif; ?>

		<div class="section-img-overlay" style="background-color: <?php echo get_field('block_background_color'); ?>"></div>

		<div class="container" <?php include __DIR__ . '/../block-parts/animation-block.php'; ?>>
			<?php if (get_field('gallery_intro')): ?>
				<div class="uk-margin-medium-bottom rm-last-child-margin">
					<?php echo get_field('gallery_intro'); ?>
				</div>
			<?php endif ?>
			<?php if (get_field("gallery_images")):
				$first_image = get_field("gallery_images")[0]['image'];
				$first_image_alt = get_post_meta($first_image, '_wp_attachment_image_alt', TRUE);
				$first_image_url = wp_get_attachment_image_url($first_image, 'full-hero-size');
				$second_image = get_field("gallery_images")[1]['image'];
				$second_image_alt = get_post_meta($second_image, '_wp_attachment_image_alt', TRUE);
				$second_image_url = wp_get_attachment_image_url($second_image, 'full-hero-size');
				$third_image = get_field("gallery_images")[2]['image'];
				$third_image_alt = get_post_meta($third_image, '_wp_attachment_image_alt', TRUE);
				$third_image_url = wp_get_attachment_image_url($third_image, 'full-hero-size');
				$count = 0;
				$rows = count(get_field("gallery_images"));
				$rest = $rows - 3;
			?>
				<div class="gallery-wrp uk-grid uk-grid-small" data-uk-lightbox>
					<div class="uk-width-2-3@m uk-flex uk-flex-wrap gallery-column-1">
						<div class="gallery-item gallery-item-1">
							<div class="gallery-inner">
								<a href="<?php echo $first_image_url; ?>"></a>
								<?php
								echo wp_get_attachment_image(
									$first_image,
									'large',
									false,
									array(
										'class' => 'test',
										'alt'   => $first_image_alt,
									)
								);
								?>
								<span class="btn">View All</span>
							</div>
						</div>
					</div>
					<div class="uk-width-1-3@m uk-flex uk-flex-wrap gallery-column-2">
						<div class="gallery-item gallery-item-2 animation-fade-item">
							<div class="gallery-inner">
								<a href="<?php echo $second_image_url; ?>"></a>
								<?php
								echo wp_get_attachment_image(
									$second_image,
									'large',
									false,
									array(
										'class' => 'test',
										'alt'   => $second_image_alt,
									)
								);
								?>
							</div>
						</div>
						<div class="gallery-item gallery-item-3 animation-fade-item">
							<div class="gallery-inner">
								<a href="<?php echo $third_image_url; ?>"></a>
								<?php
								echo wp_get_attachment_image(
									$third_image,
									'large',
									false,
									array(
										'class' => 'test',
										'alt'   => $third_image_alt,
									)
								);
								?>
								<?php if ($rows > 3): ?>
									<span class="number h1 uk-text-center uk-margin-remove">+<?php echo $rest; ?></span>
								<?php endif ?>
							</div>
						</div>
					</div>


					<?php while ( have_rows( 'gallery_images' ) ) : the_row();
						$count++;
						$image_id = get_sub_field('image');
						$image_url = wp_get_attachment_image_url($image_id, 'full-hero-size');
					?>
						<?php if ($count > 3): ?>
							<div class="uk-hidden">
								<a href="<?php echo $image_url; ?>"></a>
							</div>
						<?php endif; ?>
					<?php endwhile; ?>
				</div><!-- gallery-wrp -->
			<?php endif ?>
		</div>
	</section>
<?php endif; ?>
