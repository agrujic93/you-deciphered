<?php
/**
 * Block Name: Highlighted Text
 *
 * Displays text that progressively highlights word-by-word on scroll using GSAP.
 *
 * @package simple-block
 */

// Create id attribute for specific styling and anchor tag.
if ( isset( $block['anchor'] ) ) {
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

	$wrapper_attributes = get_block_wrapper_attributes([
		'class' => implode( ' ', [ $main_block_class, $container_class ] ),
	]);
	?>

	<section id="<?php echo esc_attr( $block_id ); ?>" <?php echo $wrapper_attributes; ?>>
		<div class="highlighted-text-container">
			<?php if ( get_field( 'highlighted_text' ) ) :
				$text = get_field( 'highlighted_text' );
				// Split text into words and wrap each in a span.
				$words = preg_split( '/\s+/', trim( $text ) );
				?>
				<p class="highlighted-text-content">
					<?php foreach ( $words as $word ) : ?>
						<span class="ht-word"><?php echo esc_html( $word ); ?></span>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		</div>
	</section>

<?php endif; ?>
