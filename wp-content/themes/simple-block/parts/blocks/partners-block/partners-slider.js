/**
 * File partners-slider.js.
 *
 * @package ci-uikit
 */

jQuery( document ).ready(
	function ($) {
		var partnersSwiper;
		if ($( '.partners-swiper' ).length > 0) {
			partnersSwiper = new Swiper(
				".partners-swiper",
				{
					slidesPerView: 2,
					autoplay: {
						delay: 3500,
						pauseOnMouseEnter: true,
					},
					breakpoints: {
						640: {
							slidesPerView: 2,
						},
						768: {
							slidesPerView: 3,
						},
						1024: {
							slidesPerView: 5,
						},
					},
					speed: 2000,
					loop: true,
					spaceBetween: 0,
				}
			);
		}
	}
);
