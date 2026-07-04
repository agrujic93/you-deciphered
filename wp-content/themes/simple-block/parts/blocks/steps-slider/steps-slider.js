/**
 * Steps Slider Script.
 */
document.addEventListener('DOMContentLoaded', () => {
    const mobileQuery = window.matchMedia('(max-width: 1024px)');

    // 1. Desktop GSAP
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);

        const blocks = document.querySelectorAll('.steps-slider-block');

        let mm = gsap.matchMedia();

        mm.add("(min-width: 1025px)", () => {
            blocks.forEach(block => {
                const pinnedArea = block.querySelector('.steps-slider-desktop .steps-slider-pinned-area');
                if (!pinnedArea) return;

                const contentItems = block.querySelectorAll('.steps-slider-desktop .step-content-item');
                const imageItems = block.querySelectorAll('.steps-slider-desktop .step-image-item');

                if (contentItems.length <= 1) return;

                const totalSlides = contentItems.length;

                // Create main tl
                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: pinnedArea,
                        start: "center center",
                        end: () => `+=${totalSlides * 100}%`,
                        pin: true,
                        scrub: 1,
                    }
                });

                // Set initial states
                gsap.set(contentItems, { autoAlpha: 0, y: 30 });
                gsap.set(imageItems, { autoAlpha: 0, scale: 0.95 });

                // First slide is visible
                gsap.set(contentItems[0], { autoAlpha: 1, y: 0 });
                gsap.set(imageItems[0], { autoAlpha: 1, scale: 1 });

                // Add sequence
                for (let i = 0; i < totalSlides; i++) {
                    if (i > 0) {
                        // Fade OUT PREVIOUS (Fast)
                        tl.to(contentItems[i - 1], {
                            autoAlpha: 0,
                            y: -30,
                            duration: 0.3,
                            ease: "power2.inOut"
                        }, `slide${i}`);

                        tl.to(imageItems[i - 1], {
                            autoAlpha: 0,
                            scale: 0.95,
                            duration: 0.3,
                            ease: "power2.inOut"
                        }, `slide${i}`);

                        // Fade IN CURRENT (Normal/Slower, happens slightly after out starts)
                        tl.to(contentItems[i], {
                            autoAlpha: 1,
                            y: 0,
                            duration: 1,
                            ease: "power2.out"
                        }, `slide${i}+=0.2`);

                        tl.to(imageItems[i], {
                            autoAlpha: 1,
                            scale: 1,
                            duration: 1,
                            ease: "power2.out"
                        }, `slide${i}+=0.2`);
                    }

                    // Pause reading time (controls how long the pin stays without animating on scrub)
                    tl.to({}, {duration: 0.8});
                }
            });
        });
    }

    // 2. Mobile Swiper
    if (typeof Swiper !== 'undefined') {
        const swiperInstances = new WeakMap();

        const syncMobileSwipers = () => {
            const swipers = document.querySelectorAll('.steps-swiper');

            swipers.forEach(swiperEl => {
                const existingInstance = swiperInstances.get(swiperEl);

                if (mobileQuery.matches) {
                    if (!existingInstance) {
                        const instance = new Swiper(swiperEl, {
                            slidesPerView: 1,
                            spaceBetween: 30,
                            autoHeight: true,
                            pagination: {
                                el: swiperEl.querySelector('.swiper-pagination'),
                                clickable: true,
                            },
                            navigation: {
                                nextEl: swiperEl.querySelector('.swiper-button-next'),
                                prevEl: swiperEl.querySelector('.swiper-button-prev'),
                            },
                            grabCursor: true,
                        });

                        swiperInstances.set(swiperEl, instance);
                    }
                } else if (existingInstance) {
                    existingInstance.destroy(true, true);
                    swiperInstances.delete(swiperEl);
                }
            });
        };

        syncMobileSwipers();

        if (typeof mobileQuery.addEventListener === 'function') {
            mobileQuery.addEventListener('change', syncMobileSwipers);
        } else if (typeof mobileQuery.addListener === 'function') {
            mobileQuery.addListener(syncMobileSwipers);
        }
    }
});
