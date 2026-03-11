(function () {
	/**
	 * Initialize Highlighted Text Block
	 *
	 * Uses GSAP ScrollTrigger to progressively highlight words (via opacity) as the user scrolls.
	 */
	function initHighlightedTextBlock(blockEl) {
		// Get the actual DOM element if jQuery object is passed.
		var el = blockEl instanceof jQuery ? blockEl[0] : blockEl;
		if (!el) return;

		// Find the block container.
		var block = el.classList.contains('ci-highlighted-text-block')
			? el
			: el.querySelector('.ci-highlighted-text-block');

		if (!block) return;

		var words = block.querySelectorAll('.ht-word');
		if (!words.length) return;

		// Check for GSAP and ScrollTrigger.
		if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
			// Fallback: just highlight all words.
			words.forEach(function (word) {
				word.classList.add('is-highlighted');
			});
			return;
		}

		gsap.registerPlugin(ScrollTrigger);

		// Animate each word's opacity from muted (0.2) to full (1) on scroll.
		gsap.to(words, {
			opacity: 1,
			stagger: 0.1,
			scrollTrigger: {
				trigger: block,
				start: 'top 85%',
				end: 'bottom 45%',
				scrub: true,
			},
		});
	}

	// Initialize on DOM ready.
	document.addEventListener('DOMContentLoaded', function () {
		var blocks = document.querySelectorAll('.ci-highlighted-text-block');
		blocks.forEach(function (block) {
			initHighlightedTextBlock(block);
		});
	});

	// Initialize in ACF block editor preview.
	if (window.acf) {
		window.acf.addAction(
			'render_block_preview/type=simple-block/highlighted-text-block',
			function ($block) {
				initHighlightedTextBlock($block[0]);
			}
		);
	}
})();
