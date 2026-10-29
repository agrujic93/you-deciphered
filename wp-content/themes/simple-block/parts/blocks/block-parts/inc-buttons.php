<?php
/**
 * Reusable ACF Buttons repeater renderer.
 *
 * Expected repeater field name: ci-clone-buttons
 * Sub-fields:
 * - button (link)
 * - button_style (select)
 *
 * Optional: $ci_button_context adds context to the accessible link name.
 *
 * @package simple-block
 */

if ( get_field('buttons_position') == 'center' ) :
	$buttons_position = ' uk-flex-center';
else:
	$buttons_position = '';
endif;

if ( have_rows( 'ci-clone-buttons' ) ) :
	?>
	<div class="ci-buttons-wrp uk-flex uk-flex-wrap<?php echo esc_attr( $buttons_position ); ?>">
		<?php
		while ( have_rows( 'ci-clone-buttons' ) ) :
			the_row();

			$link = get_sub_field( 'button' );
			if ( empty( $link['url'] ) || empty( $link['title'] ) ) {
				continue;
			}

			$link_url    = $link['url'];
			$link_title  = $link['title'];
			$link_target = ! empty( $link['target'] ) ? $link['target'] : '_self';
			$link_rel    = '_blank' === $link_target ? 'noopener noreferrer' : '';

			$aria_label = ! empty( $ci_button_context )
				? sprintf(
					/* translators: 1: button text, 2: related content title. */
					__( '%1$s: %2$s', 'simple-block' ),
					$link_title,
					$ci_button_context
				)
				: $link_title;
			if ( '_blank' === $link_target ) {
				$aria_label = sprintf(
					/* translators: %s: link text. */
					__( '%s (opens in a new tab)', 'simple-block' ),
					$aria_label
				);
			}

			$button_style = get_sub_field( 'button_style' );
			$is_expand = 'btn-expand' === $button_style;
			if ( $is_expand ) {
				$button_class = 'btn-expand';
			} elseif ( 'btn-secondary' === $button_style ) {
				$button_class = 'btn btn-secondary';
			} else {
				$button_class = 'btn';
				if ( ! empty( $button_style ) && 'btn' !== $button_style ) {
					$button_class .= ' ' . sanitize_html_class( $button_style );
				}
			}
			?>
			<a class="<?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>" <?php echo ! empty( $link_rel ) ? 'rel="' . esc_attr( $link_rel ) . '"' : ''; ?>>
				<?php if ( $is_expand ) : ?>
					<span class="btn-expand__icon" aria-hidden="true">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
							<line stroke="currentColor" x1="3.47" y1="10" x2="15.47" y2="10"></line>
							<polyline stroke="currentColor" points="11.98 13.84 15.82 10 11.98 6.16"></polyline>
						</svg>
					</span>
					<span class="btn-expand__text"><?php echo esc_html( $link_title ); ?></span>
				<?php else : ?>
					<span class="inline-text"><?php echo esc_html( $link_title ); ?></span>
				<?php endif; ?>
			</a>
		<?php endwhile; ?>
	</div>
<?php endif; ?>
