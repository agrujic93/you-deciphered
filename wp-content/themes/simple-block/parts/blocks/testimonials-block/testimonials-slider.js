/**
 * File testimonials-slider.js.
 *
 * @package ci-uikit
 */

jQuery( document ).ready(
	function ($) {
		var testimonialsSwiper;
		if ($( '.testimonials-swiper' ).length > 0) {
			testimonialsSwiper = new Swiper(
				".testimonials-swiper",
				{
					effect: "slide",
					centeredSlides: true,
					spaceBetween: 40,
					speed: 2000,
					loop: true,
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
