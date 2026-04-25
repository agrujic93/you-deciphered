<?php
/**
 * Zig Zag Boxes Block Template.
 *
 * @param array $block The block settings and attributes.
 */

$id = 'zig-zag-boxes-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

$main_block_class = 'zig-zag-boxes-block ci-block';
if ( ! empty( $block['className'] ) ) {
	$main_block_class .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$main_block_class .= ' align' . $block['align'];
}

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

include __DIR__ . '/../block-parts/block-general-logic.php';
?>

<section data-theme="<?php echo esc_attr( $color_variant ); ?>" id="<?php echo esc_attr( $id ); ?>" <?php echo $wrapper_attributes; ?>>
	<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>
	<div class="<?php echo esc_attr( $container_class ); ?> has-global-padding" <?php echo $animation_data_attr; ?> <?php echo $animation_duration_style; ?>>
		<?php if ( have_rows( 'zig_zag_boxes' ) ) : ?>
			<div class="zig-zag-container">
				<?php
				$row_count = 0;
				while ( have_rows( 'zig_zag_boxes' ) ) : the_row();
					$row_count++;
					$box_main_color    = get_sub_field( 'box_main_color' );
					$img_id            = get_sub_field( 'box_image' );
					$img_title         = get_sub_field( 'box_image_title' );
					$img_subtitle      = get_sub_field( 'box_image_subtitle' );
					$box_link          = get_sub_field( 'box_link' );
					$box_content       = get_sub_field( 'box_content' );

					// Determine alternating row alignment
					$is_even   = ( $row_count % 2 == 0 );
					$row_class = 'zig-zag-row uk-grid uk-flex-stretch uk-grid-match';
					if ( $is_even ) {
						$row_class .= ' is-even';
					}

					$has_link = ! empty( $box_link );
					?>

					<div class="zig-zag-box-wrapper" style="--box-hover-color: <?php echo esc_attr( $box_main_color ); ?>;">
						<?php if ( $has_link ) : ?>
							<a href="<?php echo esc_url( $box_link ); ?>" class="zig-zag-link-overlay aria-label-link" aria-label="<?php echo esc_attr( $img_title ?: simple_block_pll__( 'Box Link' ) ); ?>"></a>
						<?php endif; ?>

						<div class="<?php echo esc_attr( $row_class ); ?>" data-uk-grid>

							<!-- Image Column -->
							<div class="uk-width-1-2@m zig-zag-col-image <?php echo $is_even ? 'uk-flex-last@m' : ''; ?>">
								<div class="zig-zag-image-container uk-height-1-1">
									<?php if ( $img_id ) :
										$img_alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
										if ( ! $img_alt ) {
											$img_alt = $img_title ?: simple_block_pll__( 'Zig Zag Image' );
										}
										echo wp_get_attachment_image( $img_id, 'large', false, array(
											'class' => 'zig-zag-img',
											'alt'   => esc_attr( $img_alt ),
											'data-uk-parallax' => 'y: 0, 50;'
										) );
									endif; ?>

									<div class="zig-zag-image-titles">
										<?php if ( $img_title ) : ?>
											<h3 class="zig-zag-image-title h4 uk-margin-remove-bottom"><?php echo esc_html( $img_title ); ?></h3>
										<?php endif; ?>
										<?php if ( $img_subtitle ) : ?>
											<p class="zig-zag-image-subtitle uk-margin-remove-top"><?php echo esc_html( $img_subtitle ); ?></p>
										<?php endif; ?>
									</div>
								</div>
							</div>

							<!-- Content Column -->
							<div class="uk-width-1-2@m zig-zag-col-content">
								<div class="zig-zag-content-inner uk-height-1-1">

									<div class="zig-zag-labels">
										<?php if ( have_rows( 'box_labels' ) ) : ?>
											<ul class="uk-list uk-text-right@m uk-text-uppercase">
												<?php while ( have_rows( 'box_labels' ) ) : the_row();
													// Test common keys for repeater labels just in case
													$label_text = get_sub_field( 'label' ) ?: get_sub_field( 'title' ) ?: get_sub_field( 'text' ) ?: get_sub_field( 'name' );
													if ( $label_text ) :
												?>
													<li><?php echo esc_html( $label_text ); ?></li>
												<?php endif; endwhile; ?>
											</ul>
										<?php endif; ?>
									</div>

									<div class="zig-zag-content-body uk-margin-large-top">
										<div class="zig-zag-icon uk-margin-bottom">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="currentColor"/>
											</svg>
										</div>
										<div class="zig-zag-text">
											<?php echo wp_kses_post( $box_content ); ?>
										</div>
									</div>

								</div>
							</div>

						</div>
					</div>

				<?php endwhile; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
