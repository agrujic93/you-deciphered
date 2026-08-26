(function () {
	'use strict';

	/**
	 * Initialize Timeline Block
	 *
	 * @param {HTMLElement|jQuery} blockEl
	 */
	function initTimelineBlock(blockEl) {
		var isJquery = typeof jQuery !== 'undefined' && blockEl instanceof jQuery;
		var root = isJquery ? blockEl[0] : blockEl;
		if (!root) return;

		var block = root.classList.contains('ci-timeline-block')
			? root
			: root.querySelector('.ci-timeline-block');

		if (!block) return;

		var items = block.querySelectorAll('.ci-timeline-item');
		var lineProgress = block.querySelector('.ci-timeline-line-progress');
		var wrapper = block.querySelector('.ci-timeline-wrapper');

		if (!items.length) return;

		// 1. GSAP + ScrollTrigger Animation
		if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
			gsap.registerPlugin(ScrollTrigger);

			// Animate vertical progress line
			if (lineProgress && wrapper) {
				gsap.fromTo(
					lineProgress,
					{ height: '0%' },
					{
						height: '100%',
						ease: 'none',
						scrollTrigger: {
							trigger: wrapper,
							start: 'top 70%',
							end: 'bottom 70%',
							scrub: 0.2,
						},
					}
				);
			}

			// Animate each timeline milestone item
			items.forEach(function (item) {
				var marker = item.querySelector('.ci-timeline-marker');
				var date = item.querySelector('.ci-timeline-date');
				var content = item.querySelector('.ci-timeline-content');
				var isLeft = item.classList.contains('is-left');

				var tl = gsap.timeline({
					scrollTrigger: {
						trigger: item,
						start: 'top 85%',
						toggleActions: 'play none none none',
						onEnter: function () {
							item.classList.add('is-inview');
						},
					},
				});

				if (marker) {
					tl.fromTo(
						marker,
						{ scale: 0, opacity: 0 },
						{ scale: 1, opacity: 1, duration: 0.5, ease: 'back.out(1.8)' }
					);
				}

				if (date) {
					tl.fromTo(
						date,
						{ opacity: 0, x: isLeft ? 15 : -15 },
						{ opacity: 1, x: 0, duration: 0.5, ease: 'power2.out' },
						'-=0.3'
					);
				}

				if (content) {
					tl.fromTo(
						content,
						{ opacity: 0, y: 30 },
						{ opacity: 1, y: 0, duration: 0.7, ease: 'power3.out' },
						'-=0.35'
					);
				}
			});

			return;
		}

		// 2. IntersectionObserver Fallback
		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							entry.target.classList.add('is-inview');
						}
					});
				},
				{ threshold: 0.2 }
			);

			items.forEach(function (item) {
				observer.observe(item);
			});
		} else {
			items.forEach(function (item) {
				item.classList.add('is-inview');
			});
		}
	}

	// Initialize on DOM Ready
	document.addEventListener('DOMContentLoaded', function () {
		var blocks = document.querySelectorAll('.ci-timeline-block');
		blocks.forEach(function (block) {
			initTimelineBlock(block);
		});
	});

	// Initialize in ACF block editor preview
	if (window.acf) {
		window.acf.addAction(
			'render_block_preview/type=simple-block/timeline-block',
			function ($block) {
				initTimelineBlock($block[0]);
			}
		);
	}
})();
