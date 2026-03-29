(function ($) {
	/**
	 * initializeBlock
	 *
	 * Adds custom JS to the block HTML.
	 *
	 * @param   object $block The block jQuery element.
	 * @param   object attributes The block attributes (only available when editing).
	 * @return  void
	 */
	var initializeBlock = function ($block) {
		const $hero = $block.hasClass('hero-advanced-block') ? $block : $block.find('.hero-advanced-block');

		if (!$hero.length || $hero.hasClass('is-admin')) {
			return;
		}

		const $headline = $hero.find('.hero-advanced-headline');
		if ($headline.length) {
			const text = $headline.text().trim();
			const words = text.split(/\s+/).map(word => {
				const chars = word.split('').map(char => {
					return `<span class="char" style="display:inline-block">${char}</span>`;
				}).join('');
				return `<span class="word" style="display:inline-block">${chars}</span>`;
			}).join(' ');
			$headline.html(words);

			const $chars = $headline.find('.char');
			const $words = $headline.find('.word');

			// Check if GSAP is available
			if (typeof gsap !== 'undefined') {
				// GSAP Load Animation
				gsap.to($chars, {
					duration: 1.2,
					opacity: 1,
					y: 0,
					ease: 'power4.out',
					stagger: 0.04,
					delay: 0.1
				});

				// GSAP Scroll Animation
				if (typeof ScrollTrigger !== 'undefined') {
					gsap.registerPlugin(ScrollTrigger);

					// Calculate center dynamically per word depending on viewport
					gsap.to($words, {
						x: function(index, target) {
							const rect = target.getBoundingClientRect();
							const charCenter = rect.left + (rect.width / 2);
							return (charCenter - window.innerWidth / 2) * 0.8;
						},
						ease: 'none',
						scrollTrigger: {
							trigger: $hero[0],
							start: 'top top',
							end: 'bottom top',
							// Lower scrub so the word movement reacts sooner as scrolling begins.
							scrub: 0.4
						}
					});
				}

				// Mouse Parallax Effect for Images
				const $image1 = $hero.find('.image-1');
				const $image2 = $hero.find('.image-2');

				if ($image1.length || $image2.length) {
					$hero.on('mousemove', function(e) {
						const xPos = (e.clientX / window.innerWidth) - 0.5;
						const yPos = (e.clientY / window.innerHeight) - 0.5;

						if ($image1.length) {
							gsap.to($image1[0], {
								duration: 1,
								x: xPos * 60,
								y: yPos * 60,
								ease: 'power2.out'
							});
						}

						if ($image2.length) {
							gsap.to($image2[0], {
								duration: 1,
								x: xPos * -80,
								y: yPos * -80,
								ease: 'power2.out'
							});
						}
					});
				}

			} else {
				// Fallback if GSAP failed to load
				$chars.css({ 'opacity': 1, 'transform': 'none' });
			}
		}
	};

	// Initialize each block on page load (front end).
	$(document).ready(function () {
		$('.hero-advanced-block').each(function () {
			initializeBlock($(this));
		});
	});

	// Initialize dynamic block preview (editor).
	if (window.acf) {
		window.acf.addAction(
			'render_block_preview/type=simple-block/hero-advanced',
			initializeBlock
		);
	}
})(jQuery);
