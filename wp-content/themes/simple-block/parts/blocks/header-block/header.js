(function($) {
    "use strict";

    $(document).ready(function() {
        // Mobile Menu Toggle
        $('.open-menu').on('click', function() {
            $(this).toggleClass('is-active');
            $('.central-nav-pill').toggleClass('nav-active');
            $('body').toggleClass('nav-open');
        });

        // Sticky State Detection Helper
        // UIkit adds .uk-navbar-sticky to the element, but we can also use IntersectionObserver
        // for more robust detection on various resolutions if CSS classes feel laggy.

        const $header = $('.main-header');

        $(window).on('scroll', function() {
            var scrollPos = $(window).scrollTop();
            if (scrollPos > 100) {
                $header.addClass('is-sticky-manual');
            } else if (scrollPos <= 20) {
                $header.removeClass('is-sticky-manual');
            }
        });

        // Blob Navigation Logic
        const $navContainer = $('.central-nav-pill .wp-block-navigation__container, .central-nav-pill .wp-block-navigation-container');
        const isDesktop = () => window.innerWidth > 1024;

        if ($navContainer.length) {
            if (!window.gsap) {
                console.warn('GSAP is required for header blob navigation.');
                return;
            }

            // Ensure container is relative
            $navContainer.css('position', 'relative');

            const $blob = $('<div class="nav-blob"></div>').appendTo($navContainer);
            const $links = $navContainer.children('.wp-block-navigation-item').children('a');
            const navLinkEls = $links.toArray(); // raw DOM elements for fast comparison
            const navContainerEl = $navContainer[0];

            // Start fully hidden
            gsap.set($blob, { autoAlpha: 0 });

            // --- Rock-solid GSAP Implementation ---
            // 1. Pointer Events: immune to typical mouse event dropping on modern browsers.
            // 2. document.pointerleave: catches when the cursor leaves the browser entirely.
            // 3. killTweensOf: strictly controls GSAP overlap without relying on 'overwrite: "all"'.

            gsap.set($blob, { autoAlpha: 0 });

            let navTimeout = null;
            let isInsideNav = false;

            const getActiveLink = () => {
                return $navContainer.children('.current-menu-item, .current-menu-ancestor, .current_page_item, .current_page_ancestor').children('a').first();
            };

            const positionBlob = ($target, fast = false) => {
                gsap.killTweensOf($blob[0]);

                const targetOffset = $target.offset();
                const containerOffset = $navContainer.offset();

                gsap.to($blob[0], {
                    left: targetOffset.left - containerOffset.left,
                    top: targetOffset.top - containerOffset.top,
                    width: $target.outerWidth(),
                    height: $target.outerHeight(),
                    autoAlpha: 1,
                    duration: fast ? 0 : 0.35,
                    ease: "power3.out"
                });
            };

            const returnToActive = () => {
                $links.removeClass('nav-link-active');
                const $active = getActiveLink();

                if ($active.length) {
                    $active.addClass('nav-link-active');
                    positionBlob($active);
                } else {
                    gsap.killTweensOf($blob[0]);
                    gsap.to($blob[0], { autoAlpha: 0, duration: 0.2 });
                }
            };

            // Start condition
            const $activeLink = getActiveLink();
            if ($activeLink.length) {
                $activeLink.addClass('nav-link-active');
                positionBlob($activeLink, true);
            }

            // Hover over a specific link
            $links.on('pointerenter', function() {
                clearTimeout(navTimeout);
                isInsideNav = true;

                $links.removeClass('nav-link-active');
                $(this).addClass('nav-link-active');
                positionBlob($(this));
            });

            // Leave the entire container
            $navContainer.on('pointerleave', function(e) {
                isInsideNav = false;
                clearTimeout(navTimeout);
                
                // Short buffer lets the pointer enter a submenu or correct itself without hiding
                navTimeout = setTimeout(() => {
                    if (!isInsideNav) {
                        returnToActive();
                    }
                }, 50);
            });

            // Re-enter the container padding (if they are in gap)
            $navContainer.on('pointerenter', function() {
                clearTimeout(navTimeout);
                isInsideNav = true;
            });

            // If the pointer leaves the whole webpage (browser window / toolbar)
            $(document).on('pointerleave', function(e) {
                if (!e.relatedTarget || e.relatedTarget.nodeName === "HTML") {
                    isInsideNav = false;
                    clearTimeout(navTimeout);
                    returnToActive();
                }
            });

            // Recalculate on window resize
            $(window).on('resize', function() {
                if (!isDesktop()) {
                    gsap.killTweensOf($blob[0]);
                    gsap.set($blob[0], { autoAlpha: 0 });
                    $links.removeClass('nav-link-active');
                } else {
                    const $currentActive = getActiveLink();
                    if ($currentActive.length) {
                        $currentActive.addClass('nav-link-active');
                        positionBlob($currentActive, true);
                    } else {
                        gsap.killTweensOf($blob[0]);
                        gsap.set($blob[0], { autoAlpha: 0 });
                    }
                }
            });
        }
    });

})(jQuery);
