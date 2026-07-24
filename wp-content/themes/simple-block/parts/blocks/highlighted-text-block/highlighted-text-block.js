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
			block.querySelectorAll('.ht-word').forEach(function (word) {
				word.classList.add('is-highlighted');
			});
			return;
		}

		gsap.registerPlugin(ScrollTrigger);

		// Reset any previous state before (re)initializing.
		block.querySelectorAll('.ht-word').forEach(function (word) {
			gsap.set(word, { opacity: 0.15 });
		});

		ScrollTrigger.getAll().forEach(function (trigger) {
			if (trigger.trigger === block) {
				trigger.kill();
			}
		});

		// Keep the original feel: one continuous sequence across all words.
		gsap.to(words, {
			opacity: 1,
			stagger: 0.1,
			ease: 'none',
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
