/**
 * File testimonials-slider.js.
 *
 * @package ci-uikit
 */

jQuery( document ).ready(
	function ($) {
		var testimonialsSwiper;
		var $swiperContainer = $( '.testimonials-swiper' );

		if ( $swiperContainer.length > 0 ) {
			var $wrapper = $swiperContainer.find( '.swiper-wrapper' );
			var $slides = $wrapper.find( '.swiper-slide' );
			var slidesCount = $slides.length;
			var enableLoop = slidesCount > 1;

			// Swiper requires enough slides to loop properly (especially with slidesPerView > 1 and centeredSlides).
			// If we have few slides (but more than 1), duplicate them in DOM before init.
			if ( slidesCount > 1 && slidesCount < 6 ) {
				var clonesNeeded = Math.ceil( 6 / slidesCount ) - 1;
				for ( var i = 0; i < clonesNeeded; i++ ) {
					$slides.clone().appendTo( $wrapper );
				}
			}

			testimonialsSwiper = new Swiper(
				".testimonials-swiper",
				{
					effect: "slide",
					centeredSlides: true,
					spaceBetween: 40,
					speed: 2000,
					loop: enableLoop,
					slidesPerView: 1,
					autoplay: {
						delay: 10000,
						pauseOnMouseEnter: true,
					},
					navigation: {
						nextEl: ".swiper-button-next",
						prevEl: ".swiper-button-prev",
					},
					breakpoints: {
						1024: {
							slidesPerView: 1.2,
						},
						1180: {
							slidesPerView: 1.4,
						},
						1280: {
							slidesPerView: 1.6,
						},
						2560: {
							slidesPerView: 2,
							spaceBetween: 50
						}
					}
				}
			);
		}
	}
);
