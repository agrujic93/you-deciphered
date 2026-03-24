<?php
/**
 * Block Name: Team
 *
 * This is the template that displays the Team block.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

// Create id attribute for specific styling and anchor tag.

if ( isset( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-partners-block-' . $block['id'];
}

$main_block_class = 'ci-partners-block ci-block';
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

<?php include __DIR__ . '/../block-parts/block-general-logic.php'; ?>

<section data-theme="<?php echo esc_attr($color_variant); ?>" id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?> <?php echo $animation_data_attr; ?> <?php echo $animation_duration_style; ?>>
	<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>
	<div class="container">
		<?php if (get_field('partners_intro')): ?>
			<div class="uk-margin-medium-bottom rm-last-child-margin">
				<?php echo get_field('partners_intro'); ?>
			</div>
		<?php endif ?>

		<?php if ( have_rows( 'partners' ) ) : ?>
			<div class="swiper partners-swiper">
				<div class="swiper-wrapper">
					<?php while ( have_rows( 'partners' ) ) : the_row(); ?>
						<div class="swiper-slide">
							<?php if (get_sub_field( 'partner_logo')):
								$image_alt = get_post_meta(get_sub_field( 'partner_logo'), '_wp_attachment_image_alt', true);
							?>
								<?php echo wp_get_attachment_image( get_sub_field( 'partner_logo'), 'medium', false, array( "class" => "partners-logo",'alt' => $image_alt ) ); ?>
							<?php endif; ?>
							<?php if (get_sub_field('partner_link')): ?>
								<a target="_blank" href="<?php echo get_sub_field('partner_link'); ?>"></a>
							<?php endif ?>
						</div>
					<?php endwhile; ?>
				</div>
			</div>
		<?php endif; ?>

	</div><!-- .container -->
</section>
<?php endif; ?>
