(function($) {
    "use strict";

    $(document).ready(function() {
        const $menuButton = $('.open-menu');
        const $mobileNav = $('.central-nav-pill');
        const $header = $('.main-header');
        const $headerActions = $header.find('.site-branding, .actions-pill');
        const $pageBackground = $header.closest('.site-header, header.wp-block-template-part').siblings()
            .add($header.siblings('.calculator-floating-link'))
            .add($headerActions);
        const mobileMenuQuery = window.matchMedia('(max-width: 1024px)');
        let $inertedBackground = $();

        $mobileNav.find('.wp-block-navigation-item.has-child').each(function(index) {
            const $item = $(this);
            const $link = $item.children('a').first();
            const $submenu = $item.children('.wp-block-navigation__submenu-container').first();
            if (!$link.length || !$submenu.length) {
                return;
            }

            const submenuId = $mobileNav.attr('id') + '-submenu-' + index;
            const linkText = $link.text().trim();
            const $toggle = $('<button>', {
                type: 'button',
                class: 'mobile-submenu-toggle',
                'aria-controls': submenuId,
                'aria-expanded': 'false',
                'aria-label': $mobileNav.attr('data-submenu-open-label').replace('%s', linkText)
            });

            $submenu.attr('id', submenuId);
            $link.after($toggle);
            $toggle.on('click', function() {
                const expanded = $toggle.attr('aria-expanded') === 'true';
                $toggle.attr({
                    'aria-expanded': String(!expanded),
                    'aria-label': $mobileNav.attr(expanded ? 'data-submenu-open-label' : 'data-submenu-close-label').replace('%s', linkText)
                });
                $item.toggleClass('submenu-open', !expanded);
            });
        });

        function closeMobileMenu(restoreFocus) {
            $menuButton.removeClass('is-active').attr({
                'aria-expanded': 'false',
                'aria-label': $menuButton.attr('data-open-label')
            });
            $mobileNav.removeClass('nav-active');
            $header.removeAttr('role aria-modal aria-label');
            $inertedBackground.removeAttr('inert');
            $inertedBackground = $();
            $('body').removeClass('nav-open');
            $mobileNav.find('.mobile-submenu-toggle[aria-expanded="true"]').trigger('click');
            if (restoreFocus) {
                $menuButton.trigger('focus');
            }
        }

        $menuButton.on('click', function() {
            if ($(this).attr('aria-expanded') === 'true') {
                closeMobileMenu(true);
                return;
            }

            $(this).addClass('is-active').attr({
                'aria-expanded': 'true',
                'aria-label': $(this).attr('data-close-label')
            });
            $mobileNav.addClass('nav-active');
            $header.attr({
                role: 'dialog',
                'aria-modal': 'true',
                'aria-label': $mobileNav.attr('data-menu-label')
            });
            $inertedBackground = $pageBackground.not('[inert]').attr('inert', '');
            $('body').addClass('nav-open');
            $mobileNav.find('.current-menu-item').parentsUntil($mobileNav, '.wp-block-navigation-item.has-child').each(function() {
                $(this).children('.mobile-submenu-toggle[aria-expanded="false"]').trigger('click');
            });
            $mobileNav.find('.wp-block-navigation-item > a:visible').first().trigger('focus');
        });

        $(document).on('keydown', function(event) {
            if ($menuButton.attr('aria-expanded') !== 'true') {
                return;
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                closeMobileMenu(true);
            } else if (event.key === 'Tab') {
                const focusable = $menuButton.add($mobileNav.find('a:visible, button:visible')).toArray();
                const index = focusable.indexOf(document.activeElement);
                if (event.shiftKey && index <= 0) {
                    event.preventDefault();
                    focusable[focusable.length - 1].focus();
                } else if (!event.shiftKey && (index === -1 || index === focusable.length - 1)) {
                    event.preventDefault();
                    focusable[0].focus();
                }
            }
        });

        $(window).on('resize', function() {
            if (!mobileMenuQuery.matches && $menuButton.attr('aria-expanded') === 'true') {
                closeMobileMenu(false);
            }
        });

        // Sticky State Detection Helper
        // UIkit adds .uk-navbar-sticky to the element, but we can also use IntersectionObserver
        // for more robust detection on various resolutions if CSS classes feel laggy.

        const $calculatorFloatingLink = $('.calculator-floating-link');

        function updateHeaderOnScroll() {
            var scrollPos = $(window).scrollTop();
            if (scrollPos > 100) {
                $header.addClass('is-sticky-manual');
            } else if (scrollPos <= 20) {
                $header.removeClass('is-sticky-manual');
            }
            $calculatorFloatingLink.prop('hidden', scrollPos > 200);
        }

        $(window).on('scroll', updateHeaderOnScroll);
        updateHeaderOnScroll();

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
