<?php
/**
 * Block Name: Highlighted Text
 *
 * Displays text that progressively highlights word-by-word on scroll using GSAP.
 *
 * @package simple-block
 */

// Create id attribute for specific styling and anchor tag.

if ( ! empty( $block['anchor'] ) ) {
	$block_id = esc_attr( $block['anchor'] );
} else {
	$block_id = 'ci-highlighted-text-' . $block['id'];
}

$main_block_class = 'ci-highlighted-text-block ci-block';
$container_class  = 'section-full-width';
if ( 'wide' == $block['align'] ) {
	$container_class = 'section-container-wide';
} elseif ( '' == $block['align'] || 'center' == $block['align'] ) {
	$container_class = 'section-container';
}

// Preview image in inserter.
if ( isset( $block['data']['preview_image_help'] ) ) :
	echo '<img src="' . esc_url( get_template_directory_uri() ) . esc_attr( $block['data']['preview_image_help'] ) . '" style="width:100%; height:auto;">';
else :
	include __DIR__ . '/../block-parts/block-general-logic.php';
	?>

	<section data-theme="<?php echo esc_attr( $color_variant ); ?>" id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?>>

		<?php include __DIR__ . '/../block-parts/block-general-visuals.php'; ?>

		<div class="container" <?php echo $animation_data_attr; ?> <?php echo $animation_duration_style; ?>>
			<?php if ( get_field( 'highlighted_text' ) ) :
				$text = get_field( 'highlighted_text' );
				// Split text into words and wrap each in a span.
				$words = preg_split( '/\s+/', trim( $text ) );
				?>
				<p class="highlighted-text-content animation-fade-item h2">
					<?php foreach ( $words as $word ) : ?>
						<span class="ht-word"><?php echo esc_html( $word ); ?></span>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		</div>
	</section>

<?php endif; ?>
