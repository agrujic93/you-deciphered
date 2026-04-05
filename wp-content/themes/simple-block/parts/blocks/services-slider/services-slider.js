/**
 * Services Slider Script.
 */

jQuery( document ).ready(
	function ($) {
		const initializedBlocks = new WeakSet();
		const desktopQuery = window.matchMedia( '(min-width: 1024px)' );

		const initServicesSlider = function (block) {
			if (!block || initializedBlocks.has(block)) {
				return;
			}

			const swiperEl = block.querySelector('.services-slider-swiper');
			if (!swiperEl) {
				initializedBlocks.add(block);
				return;
			}

			const navNext = block.querySelector('.services-slider-nav .swiper-button-next');
			const navPrev = block.querySelector('.services-slider-nav .swiper-button-prev');
			const slidesCount = swiperEl.querySelectorAll('.swiper-slide').length;
			if (slidesCount <= 3 || typeof Swiper === 'undefined') {
				initializedBlocks.add(block);
				return;
			}

			const stickyRegion = block.querySelector('.services-slider-sticky-region');
			const stickyInner = block.querySelector('.services-slider-sticky-inner');
			let scrollRaf = null;
			let easingRaf = null;
			let targetTranslate = 0;

			const swiper = new Swiper(swiperEl, {
				slidesPerView: 1,
				spaceBetween: 20,
				speed: 700,
				watchOverflow: true,
				grabCursor: true,
				allowTouchMove: true,
				simulateTouch: true,
				touchStartPreventDefault: false,
				threshold: 8,
				freeMode: {
					enabled: false,
					sticky: false,
					momentum: true,
					momentumRatio: 0.35,
					momentumVelocityRatio: 0.35,
				},
				mousewheel: {
					enabled: false,
					forceToAxis: false,
					releaseOnEdges: true,
					sensitivity: 0.8,
					thresholdDelta: 4,
				},
				navigation: {
					nextEl: navNext,
					prevEl: navPrev,
				},
				breakpoints: {
					768: {
						slidesPerView: 2,
						spaceBetween: 20,
						grabCursor: true,
						allowTouchMove: true,
						freeMode: {
							enabled: false,
						},
						mousewheel: {
							enabled: false,
						},
					},
					1024: {
						slidesPerView: 3,
						spaceBetween: 24,
						grabCursor: false,
						allowTouchMove: false,
						freeMode: {
							enabled: true,
							sticky: false,
							momentum: true,
							momentumRatio: 0.35,
							momentumVelocityRatio: 0.35,
						},
						mousewheel: {
							enabled: false,
							forceToAxis: false,
							releaseOnEdges: true,
							sensitivity: 0.8,
							thresholdDelta: 4,
						},
					},
					2500: {
						slidesPerView: 3.4,
						spaceBetween: 40,
					},
				},
			});

			const updateStickyMetrics = function () {
				if (!stickyRegion || !stickyInner) {
					return;
				}

				if (!desktopQuery.matches) {
					stickyRegion.style.removeProperty('--services-slider-region-height');
					stickyRegion.style.removeProperty('--services-slider-sticky-top');
					return;
				}

				const stickyHeight = stickyInner.offsetHeight;
				const maxTranslate = typeof swiper.maxTranslate === 'function' ? Math.abs( swiper.maxTranslate() ) : 0;
				const viewportHeight = window.innerHeight;
				const scrollDistance = maxTranslate > 0 ? maxTranslate : viewportHeight * 0.35;
				const regionHeight = Math.max( stickyHeight + scrollDistance, viewportHeight );
				const stickyTop = Math.max((viewportHeight - stickyHeight) / 2, 32);

				stickyRegion.style.setProperty('--services-slider-region-height', `${regionHeight}px`);
				stickyRegion.style.setProperty('--services-slider-sticky-top', `${stickyTop}px`);
			};

			const syncDesktopInteractionMode = function () {
				const isDesktop = desktopQuery.matches;

				swiper.params.freeMode.enabled = isDesktop;
				swiper.params.mousewheel.enabled = false;

				if (swiper.originalParams && swiper.originalParams.freeMode) {
					swiper.originalParams.freeMode.enabled = isDesktop;
				}

				swiper.params.grabCursor = !isDesktop;
				if (swiper.originalParams) {
					swiper.originalParams.grabCursor = !isDesktop;
				}

				swiper.params.allowTouchMove = !isDesktop;
				if (swiper.originalParams) {
					swiper.originalParams.allowTouchMove = !isDesktop;
				}

				if (swiper.originalParams && swiper.originalParams.mousewheel) {
					swiper.originalParams.mousewheel.enabled = false;
				}

				if (swiper.mousewheel) {
					swiper.mousewheel.disable();
				}

				swiper.allowTouchMove = !isDesktop;

				if (isDesktop && typeof swiper.unsetGrabCursor === 'function') {
					swiper.unsetGrabCursor();
				} else if (!isDesktop && typeof swiper.setGrabCursor === 'function') {
					swiper.setGrabCursor();
				}
			};

			const easeTranslate = function () {
				const currentTranslate = swiper.getTranslate();
				const difference = targetTranslate - currentTranslate;

				if (Math.abs(difference) < 0.4) {
					swiper.setTransition(0);
					swiper.setTranslate(targetTranslate);
					swiper.updateProgress(targetTranslate);
					swiper.updateActiveIndex();
					swiper.updateSlidesClasses();
					easingRaf = null;
					return;
				}

				const easedTranslate = currentTranslate + (difference * 0.14);
				swiper.setTransition(0);
				swiper.setTranslate(easedTranslate);
				swiper.updateProgress(easedTranslate);
				swiper.updateActiveIndex();
				swiper.updateSlidesClasses();

				easingRaf = window.requestAnimationFrame(easeTranslate);
			};

			const syncSwiperToScroll = function () {
				if (!desktopQuery.matches || !stickyRegion || !stickyInner) {
					scrollRaf = null;
					return;
				}

				const stickyTop = parseFloat( getComputedStyle( stickyRegion ).getPropertyValue( '--services-slider-sticky-top' ) ) || Math.max( ( window.innerHeight - stickyInner.offsetHeight ) / 2, 32 );
				const regionTop = stickyRegion.getBoundingClientRect().top + window.scrollY;
				const startScroll = regionTop - stickyTop;
				const endScroll = startScroll + Math.max( stickyRegion.offsetHeight - stickyInner.offsetHeight, 1 );
				const scrollProgress = Math.max( 0, Math.min( 1, ( window.scrollY - startScroll ) / Math.max( endScroll - startScroll, 1 ) ) );
				targetTranslate = swiper.minTranslate() + ( ( swiper.maxTranslate() - swiper.minTranslate() ) * scrollProgress );

				if (!easingRaf) {
					easingRaf = window.requestAnimationFrame(easeTranslate);
				}

				scrollRaf = null;
			};

			const handleWindowScroll = function () {
				if ( scrollRaf ) {
					return;
				}

				scrollRaf = window.requestAnimationFrame( syncSwiperToScroll );
			};

			const handleResize = function () {
				syncDesktopInteractionMode();
				swiper.update();
				updateStickyMetrics();
				handleWindowScroll();
			};

			swiper.on('init resize breakpoint afterInit setTranslate reachBeginning reachEnd fromEdge', updateStickyMetrics);
			swiper.on('init resize breakpoint afterInit', syncDesktopInteractionMode);
			syncDesktopInteractionMode();
			updateStickyMetrics();
			handleWindowScroll();
			window.addEventListener('scroll', handleWindowScroll, { passive: true });
			window.addEventListener('resize', handleResize);

			initializedBlocks.add(block);
		};

		document.querySelectorAll('.services-slider-block').forEach((block) => {
			initServicesSlider(block);
		});

		if (window.acf) {
			window.acf.addAction('render_block_preview/type=services-slider', function ($block) {
				if ($block && $block[0]) {
					initServicesSlider($block[0]);
				}
			});
		}
	}
);
