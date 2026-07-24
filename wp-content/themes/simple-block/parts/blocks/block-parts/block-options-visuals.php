<?php
/**
 * Block General Visuals
 * 
 * This file handles the rendering of background image and color overlay for "Blocks: Global Options".
 */

// Background Image
if ($bg_image_id) :
    echo wp_get_attachment_image(
        $bg_image_id,
        'full-hero-size',
        false,
        array(
            'class' => 'section-background-image',
            'alt'   => $bg_image_alt,
            'data-uk-parallax' => 'y: -10%'
        )
    );
endif;

// Color Overlay (only when both background image and color are set)
if (get_field('block_background_image') && get_field('block_background_color')) : ?>
    <div class="section-img-overlay" style="background-color: <?php echo get_field('block_background_color'); ?>"></div>
<?php endif; ?>
