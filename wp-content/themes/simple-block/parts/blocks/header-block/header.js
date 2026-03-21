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
    });

})(jQuery);
