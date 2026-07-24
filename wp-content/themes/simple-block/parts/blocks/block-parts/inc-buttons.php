<?php
/**
 * Reusable ACF Buttons repeater renderer.
 *
 * Expected repeater field name: ci-clone-buttons
 * Sub-fields:
 * - button (link)
 * - button_style (select)
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

			$aria_label = $link_title;
			if ( '_blank' === $link_target ) {
				$aria_label = sprintf(
					/* translators: %s: link text. */
					__( '%s (opens in a new tab)', 'simple-block' ),
					$link_title
				);
			}

			$button_style = get_sub_field( 'button_style' );
			$button_class = 'btn';

			if ( ! empty( $button_style ) && 'btn' !== $button_style ) {
				$button_class .= ' ' . sanitize_html_class( $button_style );
			}
			?>
			<a class="<?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>" <?php echo ! empty( $link_rel ) ? 'rel="' . esc_attr( $link_rel ) . '"' : ''; ?>><span class="inline-text"><?php echo esc_html( $link_title ); ?></span></a>
		<?php endwhile; ?>
	</div>
<?php endif; ?>
