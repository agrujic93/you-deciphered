<?php
/**
 * Block Name: Highlighted Text
 *
 * Displays text that progressively highlights word-by-word on scroll using GSAP.
 *
 * @package simple-block
 */

// Create id attribute for specific styling and anchor tag.

$id = wp_unique_id( 'ci-highlighted-text-' );
if ( ! empty( $block['anchor'] ) ) {
	$id = sanitize_title( (string) $block['anchor'] );
}

$classes = [ 'ci-highlighted-text-block', 'ci-block' ];
if ( ! empty( $block['className'] ) ) {
	$classes[] = $block['className'];
}

$inline_styles = [];

// Preview image in inserter.
if ( isset( $block['data']['preview_image_help'] ) ) :
	echo '<img src="' . esc_url( get_template_directory_uri() . $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';
else :
	require __DIR__ . '/../block-parts/block-options.php';

	$is_empty_block = ! get_field( 'highlighted_text' );

	if ( is_admin() && $is_empty_block ) {
		ci_render_empty_block_placeholder( __( 'Highlighted Text Block is empty. Click the block to edit and add text.', 'ci-uikit' ) );
		return;
	}
?>

	<section data-theme="<?php echo esc_attr( $color_variant ); ?>" id="<?php echo esc_attr( $id ); ?>" <?php echo $wrapper_attributes; ?>>

		<?php include __DIR__ . '/../block-parts/block-options-visuals.php'; ?>

		<div class="container" <?php echo $animation_attr; ?>>
			<?php if ( get_field( 'highlighted_text' ) ) :
				$text       = (string) get_field( 'highlighted_text' );
				$text_html  = trim( (string) wpautop( $text ) );
				$paragraphs = array();

				if ( preg_match_all( '/<p[^>]*>(.*?)<\/p>/is', $text_html, $matches ) ) {
					foreach ( $matches[1] as $paragraph_html ) {
						$paragraph_text = trim(
							wp_strip_all_tags(
								str_replace(
									array( '<br />', '<br/>', '<br>' ),
									' ',
									$paragraph_html
								)
							)
						);

						if ( '' !== $paragraph_text ) {
							$paragraphs[] = $paragraph_text;
						}
					}
				}

				if ( empty( $paragraphs ) ) {
					$fallback_text = trim( wp_strip_all_tags( $text ) );
					if ( '' !== $fallback_text ) {
						$paragraphs[] = $fallback_text;
					}
				}

				foreach ( $paragraphs as $paragraph ) :
					$words = preg_split( '/\s+/', $paragraph, -1, PREG_SPLIT_NO_EMPTY );
					if ( empty( $words ) ) {
						continue;
					}
					?>
					<p class="highlighted-text-content animation-fade-item h1" <?php echo $duration; ?>>
						<?php foreach ( $words as $word ) : ?>
							<span class="ht-word"><?php echo esc_html( $word ); ?></span>
						<?php endforeach; ?>
					</p>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</section>

<?php endif; ?>
