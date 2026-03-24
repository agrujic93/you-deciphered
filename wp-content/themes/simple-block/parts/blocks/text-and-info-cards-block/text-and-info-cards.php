<?php
/**
 * Block Name: Text and Info Cards Block
 *
 * This is the template that displays the Text and Info Cards.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ci-uikit
 **/

// Create id attribute for specific styling and anchor tag.

if ( isset( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-text-and-info-cards-' . $block['id'];
}

$main_block_class = 'ci-text-and-info-cards-block ci-block';
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

				<div class="uk-grid uk-grid-large uk-flex-middle uk-position-relative" data-uk-grid>
					<?php if (get_field('text')): ?>
						<div class="uk-width-expand rm-last-child-margin left-col">
							<?php echo get_field( 'text' ); ?>
						</div>
					<?php endif ?>
					<?php if ( have_rows( 'info_cards' ) ) : ?>
						<div class="uk-width-1-2@l cards-col">
							<div class="uk-grid uk-margin-bottom inner-grid" data-uk-grid>
								<?php while ( have_rows( 'info_cards' ) ) : the_row(); ?>
									<div class="uk-width-1-2 card">
										<?php if (get_sub_field('card_background_color') == "dark_blue") {
											$card_class = "card-dark-blue";
										} elseif (get_sub_field('card_background_color') == "light_blue") {
											$card_class = "card-light-blue";
										} elseif (get_sub_field('card_background_color') == "green") {
											$card_class = "card-green";
										} elseif (get_sub_field('card_background_color') == "orange") {
											$card_class = "card-orange";
										} ?>

										<div class="card-wrp rm-last-child-margin <?php echo $card_class; ?>">
											<?php if (get_sub_field('card_link')): ?>
												<a class="card-link" href="<?php echo get_sub_field('card_link'); ?>"></a>
											<?php endif ?>
											<?php if (get_sub_field('card_title')): ?>
												<h3><?php echo get_sub_field('card_title'); ?></h3>
											<?php endif ?>
											<?php if (get_sub_field('card_description')): ?>
												<p><?php echo get_sub_field('card_description'); ?></p>
											<?php endif ?>
										</div>
									</div>
								<?php endwhile; ?>
							</div>
							<?php
							if( get_field('info_cards_cta') ):
								$link = get_field('info_cards_cta');
								$link_url = $link['url'];
								$link_title = $link['title'];
								$link_target = $link['target'] ? $link['target'] : '_self';
								?>
									<a class="ci-read-more-link" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

		</div>
	</section>
<?php endif; ?>
