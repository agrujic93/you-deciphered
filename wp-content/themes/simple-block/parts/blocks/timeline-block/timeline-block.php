<?php
/**
 * Block Name: Timeline
 *
 * Displays a chronological timeline of events with smooth scroll animations.
 *
 * @package simple-block
 */

// Create id attribute for specific styling and anchor tag.
$id = wp_unique_id( 'ci-timeline-' );
if ( ! empty( $block['anchor'] ) ) {
	$id = sanitize_title( (string) $block['anchor'] );
}

$classes = [ 'ci-timeline-block', 'ci-block' ];
if ( ! empty( $block['className'] ) ) {
	$classes[] = $block['className'];
}

$inline_styles = [];

// Preview image in inserter.
if ( isset( $block['data']['preview_image_help'] ) ) :
	echo '<img src="' . esc_url( get_template_directory_uri() . $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';
else :
	require __DIR__ . '/../block-parts/block-options.php';

	$timeline_items = get_field( 'timeline' );
	$is_empty_block = empty( $timeline_items ) || ! is_array( $timeline_items );

	if ( is_admin() && $is_empty_block ) {
		ci_render_empty_block_placeholder( __( 'Timeline Block is empty. Click the block to edit and add events.', 'ci-uikit' ) );
		return;
	}
?>

	<section data-theme="<?php echo esc_attr( $color_variant ); ?>" id="<?php echo esc_attr( $id ); ?>" <?php echo $wrapper_attributes; ?>>

		<?php include __DIR__ . '/../block-parts/block-options-visuals.php'; ?>

		<div class="container">
			<?php if ( ! empty( $timeline_items ) && is_array( $timeline_items ) ) : ?>
				<div class="ci-timeline-wrapper">
					<div class="ci-timeline-line" aria-hidden="true">
						<div class="ci-timeline-line-progress"></div>
					</div>

					<div class="ci-timeline-items">
						<?php foreach ( $timeline_items as $index => $item ) :
							$event_date        = ! empty( $item['event_date'] ) ? trim( (string) $item['event_date'] ) : '';
							$event_title       = ! empty( $item['event_title'] ) ? trim( (string) $item['event_title'] ) : '';
							$event_description = ! empty( $item['event_description'] ) ? trim( (string) $item['event_description'] ) : '';
							$is_odd            = ( $index % 2 === 0 ); // 0 = left, 1 = right, 2 = left, 3 = right
							$side_class        = $is_odd ? 'is-left' : 'is-right';
							$is_first          = ( $index === 0 );
						?>
							<div class="ci-timeline-item <?php echo esc_attr( $side_class ); ?> <?php echo $is_first ? 'is-first-item' : ''; ?>" data-item-index="<?php echo esc_attr( $index ); ?>">

								<div class="ci-timeline-node-wrap">
									<?php if ( $event_date ) : ?>
										<span class="ci-timeline-date"><?php echo esc_html( $event_date ); ?></span>
									<?php endif; ?>

									<div class="ci-timeline-marker">
										<?php if ( $is_first ) : ?>
											<svg class="ci-timeline-star" viewBox="0 0 24 24" width="2rem" height="2rem" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
												<line x1="12" y1="2" x2="12" y2="22"></line>
												<line x1="2" y1="12" x2="22" y2="12"></line>
												<line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
												<line x1="4.93" y1="19.07" x2="19.07" y2="4.93"></line>
											</svg>
										<?php else : ?>
											<span class="ci-timeline-dot"></span>
										<?php endif; ?>
									</div>
								</div>

								<div class="ci-timeline-content">
									<?php if ( $event_title ) : ?>
										<h2 class="ci-timeline-title h2"><?php echo esc_html( $event_title ); ?></h2>
									<?php endif; ?>

									<?php if ( $event_description ) : ?>
										<div class="ci-timeline-description">
											<?php echo wp_kses_post( wpautop( $event_description ) ); ?>
										</div>
									<?php endif; ?>
								</div>

							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>

<?php endif; ?>
