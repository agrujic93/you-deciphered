<?php
/**
 * Block Name: Price List Block
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
	$block_id = 'price-list-block-' . $block['id'];
}

$main_block_class = 'price-list-block ci-block';
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
			<?php if (get_field( 'price_list_intro' )): ?>
				<div class="rm-last-child-margin uk-margin-medium-bottom animation-fade-item" <?php echo $duration; ?>>
					<?php echo get_field('price_list_intro'); ?>
				</div>
			<?php endif ?>
			<?php if ( have_rows( 'price_list_groups' ) ) : ?>
				<div class="uk-overflow-auto">
					<table class="uk-table uk-table-striped animation-fade-item">
						<thead>
							<tr>
								<th class="table-number-th">No.</th>
								<th class="uk-table-expand">Service Name</th>
								<th class="table-price-th">Price</th>
							</tr>
						</thead>
						<tbody>
							<?php while ( have_rows('price_list_groups') ) : the_row(); ?>
								<?php if (get_sub_field('group_title')): ?>
									<tr class="price-group-title">
										<td><?php the_sub_field('group_title'); ?></td>
										<td></td>
										<td></td>
									</tr>
								<?php endif ?>
								<?php if( have_rows('services') ):
									$i = 0;
								?>
									<?php while ( have_rows('services') ) : the_row();
										$i++;
									?>
										<tr>
											<td data-label="No."><?php echo $i; ?>.</td>
											<td data-label="Service Name"><?php the_sub_field('service_name'); ?></td>
											<td data-label="Price"><?php the_sub_field('service_price'); ?></td>
										</tr>
									<?php endwhile; ?>
								<?php endif; ?>
							<?php endwhile; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>
