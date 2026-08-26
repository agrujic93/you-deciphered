/**
 * File infinite-slider-block.js.
 *
 * Handles smooth infinite continuous autoplay image slider using Swiper.
 *
 * @package simple-block
 */

(function () {
	'use strict';

	function initInfiniteSlider(blockEl) {
		var isJquery = typeof jQuery !== 'undefined' && blockEl instanceof jQuery;
		var el = isJquery ? blockEl[0] : blockEl;
		if (!el) return;

		var swiperContainer = el.classList.contains('infinite-slider-swiper')
			? el
			: el.querySelector('.infinite-slider-swiper');

		if (!swiperContainer || typeof Swiper === 'undefined') return;

		// Clean up existing instance if block preview re-renders in editor
		if (swiperContainer.swiper) {
			try {
				swiperContainer.swiper.destroy(true, true);
			} catch (e) {
				// Silently handle if already destroyed
			}
		}

		var slides = swiperContainer.querySelectorAll('.swiper-slide');
		var slidesCount = slides.length;
		if (slidesCount === 0) return;

		new Swiper(swiperContainer, {
			slidesPerView: 'auto',
			spaceBetween: 24,
			loop: true,
			speed: 9000,
			autoplay: {
				delay: 0,
				disableOnInteraction: false,
				pauseOnMouseEnter: false,
				stopOnLastSlide: false,
			},
			allowTouchMove: false,
			simulateTouch: false,
			touchRatio: 0,
			grabCursor: false,
			watchSlidesProgress: true,
			breakpoints: {
				640: {
					spaceBetween: 24,
				},
				1024: {
					spaceBetween: 32,
				},
			},
		});
	}

	// Initialize on DOM ready.
	document.addEventListener('DOMContentLoaded', function () {
		var blocks = document.querySelectorAll('.ci-infinite-slider-block');
		blocks.forEach(function (block) {
			initInfiniteSlider(block);
		});
	});

	// Initialize in ACF block editor preview.
	if (window.acf) {
		window.acf.addAction(
			'render_block_preview/type=simple-block/infinite-slider-block',
			function ($block) {
				if ($block && $block[0]) {
					initInfiniteSlider($block[0]);
				}
			}
		);
	}
})();
