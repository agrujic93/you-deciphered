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
	$block_id = 'ci-accordion-block-' . $block['id'];
}

$main_block_class = 'ci-accordion-block ci-block';
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

		<div class="section-img-overlay" style="background-color: <?php echo get_field('block_background_color'); ?>"></div>

		<div class="container" <?php include __DIR__ . '/../block-parts/animation-block.php'; ?>>

			<?php if (get_field('accordion_intro')): ?>
				<div class="uk-margin-medium-bottom rm-last-child-margin">
					<?php echo get_field('accordion_intro'); ?>
				</div>
			<?php endif ?>

			<?php if ( have_rows( 'accordion' ) ) : ?>
				<div class="accordion-wrp">
					<ul uk-accordion="multiple: true; active: 0;">
						<?php while ( have_rows( 'accordion' ) ) :the_row(); ?>
							<li>
								<?php if (get_sub_field('accordion_title')): ?>
									<a class="uk-accordion-title" href="#">
										<h3><?php echo get_sub_field('accordion_title'); ?></h3>
									</a>
								<?php endif; ?>
								<?php if (get_sub_field('accordion_content')): ?>
									<div class="uk-accordion-content">
										<?php echo get_sub_field('accordion_content'); ?>
									</div>
								<?php endif; ?>
							</li>
						<?php endwhile; ?>
					</ul>
				</div>
			<?php endif; ?>

		</div>
	</section>
<?php endif; ?>
