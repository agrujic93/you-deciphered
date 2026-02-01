<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php
		$header = do_blocks('<!-- wp:template-part {"slug":"header","theme":"simple-block"} /-->');
		$footer = do_blocks('<!-- wp:template-part {"slug":"footer","theme":"simple-block"} /-->');
	?>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div class="wp-site-blocks">
		<header class="wp-block-template-part site-header">
			<?php block_header_area(); ?>
		</header>
		<main class="wp-block-group site-main has-global-padding" id="wp--skip-link--target" data-uk-scrollspy="cls: uk-animation-slide-bottom-small; target: .animation-fade-item; delay: 300; repeat: false;">
			<section class="section-container ci-hero-text small-gap main-gradient ci-has-background">
				<div class="hero-content-wrp">
					<div class="container">
						<div class="hero-wrp rm-last-child-margin">
							<h1 class="animation-fade-item" style="color: #fff;">Blog</h1>
						</div>
					</div>
				</div>
			</section>
			<section class="ci-query-posts-block ci-block section-container">
				<div class="container">
					<?php if ( have_posts() ) : ?>

						<div class="uk-grid" data-uk-grid>
							<?php
							/* Start the Loop */
							while ( have_posts() ) :
								the_post(); ?>

								<div class="uk-width-1-3@l uk-width-1-2@m animation-fade-item">
									<div class="query-posts-card rm-last-child-margin">
										<a aria-label="Link to the post" class="image-wrapper uk-margin-bottom" href="<?php echo esc_url( get_permalink() ); ?>">
											<?php if (get_the_post_thumbnail()): ?>
												<?php echo get_the_post_thumbnail(get_the_ID(), 'large' ); ?>
											<?php else: ?>
												<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/theme-default.jpeg" alt="thumbnail logo">
											<?php endif ?>
										</a>
										<a class="post-title uk-margin-small-bottom" aria-label="Link to the <?php echo esc_html( get_the_title() ); ?>" href="<?php echo esc_url( get_permalink() ); ?>">
											<h3><?php echo esc_html( get_the_title() ); ?></h3>
										</a>
										<?php if (get_post_type() == 'post'): ?>
											<p class="uk-margin-bottom"><?php echo esc_html( get_the_date() ); ?></p>
											<p class="uk-margin-bottom"><?php echo wp_html_excerpt( get_the_excerpt(), 180, '... ' ); ?></p>
										<?php endif ?>
										<div class="uk-margin-auto-top">
											<a class="btn" aria-label="Link to the <?php echo esc_html( get_the_title() ); ?>" href="<?php echo esc_url( get_permalink() ); ?>">Learn More</a>
										</div>
									</div>
								</div>
							<?php endwhile; ?>
						</div>

					<?php else :


					endif;

					the_posts_pagination( array(
						'mid_size'  => 2,
						'prev_text' => __( 'Prev', 'textdomain' ),
						'next_text' => __( 'Next', 'textdomain' ),
					) ); ?>
				</div>
			</section>

		</main>
		<footer class="wp-block-template-part site-footer">
			<?php block_footer_area(); ?>
		</footer>
	</div>
	<?php wp_footer(); ?>
</body>