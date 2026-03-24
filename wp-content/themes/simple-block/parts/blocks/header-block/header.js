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

        if ($navContainer.length && isDesktop()) {
            if (!window.gsap) {
                console.warn('GSAP is required for header blob navigation.');
                return;
            }

            // Ensure container is relative
            $navContainer.css('position', 'relative');

            const $blob = $('<div class="nav-blob"></div>').appendTo($navContainer);
            const $links = $navContainer.children('.wp-block-navigation-item').children('a');
            
            const moveBlob = (element, fast = false) => {
                const $target = $(element);
                
                // Remove active class from all links
                $links.removeClass('nav-link-active');

                if (!$target.length) {
                    gsap.to($blob, { autoAlpha: 0, duration: 0.3 });
                    return;
                }

                // Add active class to the targeted link
                $target.addClass('nav-link-active');

                const targetOffset = $target.offset();
                const containerOffset = $navContainer.offset();

                gsap.to($blob, {
                    left: targetOffset.left - containerOffset.left,
                    top: targetOffset.top - containerOffset.top,
                    width: $target.outerWidth(),
                    height: $target.outerHeight(),
                    autoAlpha: 1,
                    duration: fast ? 0 : 0.45,
                    ease: "power3.out",
                    overwrite: "all"
                });
            };

            const getActiveLink = () => {
                return $navContainer.children('.current-menu-item, .current-menu-ancestor, .current_page_item, .current_page_ancestor').children('a').first();
            };

            // Initial Position
            const $activeLink = getActiveLink();
            if ($activeLink.length) {
                moveBlob($activeLink, true);
            }

            $links.on('mouseenter', function() {
                moveBlob($(this));
            });

            $navContainer.on('mouseleave', function() {
                moveBlob(getActiveLink());
            });
            
            // Recalculate on window resize
            $(window).on('resize', function() {
                if (!isDesktop()) {
                    gsap.to($blob, { autoAlpha: 0, scale: 0, duration: 0 });
                    $links.removeClass('nav-link-active');
                } else {
                    const $currentActive = getActiveLink();
                    if ($currentActive.length) moveBlob($currentActive, true);
                }
            });
        }
    });

})(jQuery);
