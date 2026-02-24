<?php
/**
 * Block Name: Staff
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
	$block_id = 'ci-staff-block-' . $block['id'];
}

$main_block_class = 'ci-staff-block ci-block';
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

	<?php include __DIR__ . '/../block-parts/background-and-text-color-block.php'; ?>

	<section id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?>>


		<div class="container" <?php include __DIR__ . '/../block-parts/animation-block.php'; ?>>
			<?php if (get_field( 'staff_group_name' )): ?>
				<div class="rm-last-child-margin animation-fade-item staff-group-name-wrp" <?php echo $duration; ?>>
					<h2><?php echo get_field('staff_group_name'); ?></h2>
				</div>
			<?php endif ?>

			<?php if ( have_rows( 'staff' ) ) : ?>
				<div class="staff-wrp uk-grid uk-flex-middle" data-uk-grid>
					<?php while ( have_rows( 'staff' ) ) : the_row(); ?>
						<div class="staff-col uk-width-1-2@m animation-fade-item" <?php echo $duration; ?>>
							<div class="person-wrp">
								<?php if (get_sub_field( 'image' )):
									$image_alt = get_post_meta(get_sub_field( 'image' ), '_wp_attachment_image_alt', TRUE);
								?>
									<?php echo wp_get_attachment_image( get_sub_field( 'image' ), 'large', false, array( "class" => "person-img", 'alt' => $image_alt ) ); ?>
								<?php endif; ?>
								<div class="person-detail rm-last-child-margin">
									<?php if (get_sub_field('full_name')): ?>
										<h3><?php echo get_sub_field('full_name'); ?></h3>
									<?php endif ?>
									<?php if (get_sub_field('position')): ?>
										<p><?php echo get_sub_field('position'); ?></p>
									<?php endif ?>
									<?php
									if( get_sub_field('button') ):
										$link = get_sub_field('button');
										$link_url = $link['url'];
										$link_title = $link['title'];
										$link_target = $link['target'] ? $link['target'] : '_self';
										?>
										<a class="btn" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?></a>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>
