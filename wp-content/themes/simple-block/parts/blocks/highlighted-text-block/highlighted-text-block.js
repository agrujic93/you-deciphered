(function () {
	var ciHighlightedTextSeenIds = {};
	var ciHighlightedTextBlocks = [];
	var ciHighlightedUpdaterStarted = false;
	var ciHighlightedTickScheduled = false;

	function ensureUniqueBlockId(block) {
		if (!block) return;

		var baseId = (block.id || '').trim();
		if (!baseId) {
			baseId = 'ci-highlighted-text-' + Math.random().toString(36).slice(2, 10);
		}

		if (!ciHighlightedTextSeenIds[baseId]) {
			ciHighlightedTextSeenIds[baseId] = 1;
			block.id = baseId;
			return;
		}

		ciHighlightedTextSeenIds[baseId] += 1;
		block.id = baseId + '--' + ciHighlightedTextSeenIds[baseId];
	}

	function clamp(value, min, max) {
		return Math.max(min, Math.min(max, value));
	}

	function smoothstep(value) {
		var x = clamp(value, 0, 1);
		return x * x * (3 - 2 * x);
	}

	function getBlockProgress(block) {
		if (!block) return 0;

		var rect = block.getBoundingClientRect();
		var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
		var startY = viewportHeight * 0.85;
		var endY = viewportHeight * 0.45;
		var totalDistance = rect.height + (startY - endY);

		if (totalDistance <= 0) return 1;

		var distance = startY - rect.top;
		return clamp(distance / totalDistance, 0, 1);
	}

	function applyWordsProgress(wordsArray, progress) {
		var totalWords = wordsArray.length;
		if (!totalWords) return;

		var revealPosition = progress * totalWords;
		var transitionWindow = 1.4;

		wordsArray.forEach(function (word, index) {
			var localProgress = (revealPosition - index) / transitionWindow;
			var easedProgress = smoothstep(localProgress);
			var opacity = 0.15;

			if (localProgress >= 1) {
				opacity = 1;
			} else if (localProgress > 0) {
				opacity = 0.15 + 0.85 * easedProgress;
			}

			gsap.set(word, { opacity: opacity });
		});
	}

	function updateAllHighlightedTextBlocks() {
		for (var i = ciHighlightedTextBlocks.length - 1; i >= 0; i--) {
			var item = ciHighlightedTextBlocks[i];
			if (!item || !item.block || !document.body.contains(item.block)) {
				ciHighlightedTextBlocks.splice(i, 1);
				continue;
			}

			var progress = getBlockProgress(item.block);
			if (Math.abs(progress - item.lastProgress) > 0.001) {
				item.lastProgress = progress;
				applyWordsProgress(item.wordsArray, progress);
			}
		}
	}

	function scheduleHighlightedTextUpdate() {
		if (ciHighlightedTickScheduled) return;
		ciHighlightedTickScheduled = true;

		window.requestAnimationFrame(function () {
			ciHighlightedTickScheduled = false;
			updateAllHighlightedTextBlocks();
		});
	}

	function startHighlightedTextUpdater() {
		if (ciHighlightedUpdaterStarted) return;
		ciHighlightedUpdaterStarted = true;

		window.addEventListener('scroll', scheduleHighlightedTextUpdate, { passive: true });
		window.addEventListener('resize', scheduleHighlightedTextUpdate);
		window.addEventListener('orientationchange', scheduleHighlightedTextUpdate);
		window.addEventListener('load', scheduleHighlightedTextUpdate);

		// Safety net for environments where native scroll listeners are unreliable.
		window.setInterval(updateAllHighlightedTextBlocks, 120);
	}

	/**
	 * Initialize Highlighted Text Block
	 *
	 * Progressively highlights words (via opacity) as the user scrolls.
	 */
	function initHighlightedTextBlock(blockEl) {
		// Get the actual DOM element if jQuery object is passed.
		var isJqueryObject = typeof jQuery !== 'undefined' && blockEl instanceof jQuery;
		var el = isJqueryObject ? blockEl[0] : blockEl;
		if (!el) return;

		// Find the block container.
		var block = el.classList.contains('ci-highlighted-text-block')
			? el
			: el.querySelector('.ci-highlighted-text-block');

		if (!block) return;

		ensureUniqueBlockId(block);

		var words = block.querySelectorAll('.ht-word');
		if (!words.length) return;
		var wordsArray = Array.prototype.slice.call(words);

		// Check for GSAP.
		if (typeof gsap === 'undefined') {
			// Fallback: just highlight all words.
			block.querySelectorAll('.ht-word').forEach(function (word) {
				word.classList.add('is-highlighted');
			});
			return;
		}

		// Reset any previous state before (re)initializing.
		wordsArray.forEach(function (word) {
			gsap.set(word, { opacity: 0.15 });
		});

		for (var i = ciHighlightedTextBlocks.length - 1; i >= 0; i--) {
			if (ciHighlightedTextBlocks[i].block === block) {
				ciHighlightedTextBlocks.splice(i, 1);
			}
		}

		var initialProgress = getBlockProgress(block);
		ciHighlightedTextBlocks.push({
			block: block,
			wordsArray: wordsArray,
			lastProgress: initialProgress,
		});

		applyWordsProgress(wordsArray, initialProgress);
		startHighlightedTextUpdater();
		scheduleHighlightedTextUpdate();
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
