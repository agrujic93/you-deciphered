/**
 * File hero-slider.js.
 *
 * @package ci-uikit
 */

jQuery( document ).ready(
	function ($) {
		if ($( '.hero-swiper' ).length > 0) {
			heroSwiper = new Swiper(
				".hero-swiper",
				{
					effect: "creative",
					creativeEffect: {
						prev: {
							translate: ["-50%", 0, -1],
						},
						next: {
							translate: ["100%", 0, 0],
						},
					},
					speed: 2200,
					loop: true,
					slidesPerView: 1,
					pagination: {
						el: ".swiper-pagination",
						clickable: true,
					},
					autoplay: {
						delay: 7000,
					},
				}
			);
		}
	}
);
