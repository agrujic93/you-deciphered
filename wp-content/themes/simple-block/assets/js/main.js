/**
 * File main.js.
 *
 * @package ci-uikit
 */

jQuery( document ).ready(
	function ($) {

		$('body').addClass('loaded');

		//hamburger-menu
		$('.open-menu').click(function(e){
			e.preventDefault();
			if($(this).hasClass('open')) {
				$(this).removeClass('open');
				$('.navigation-wrp').removeClass('open');
				$('body').removeClass('uk-overflow-hidden');
			} else {
				$(this).addClass('open');
				$('.navigation-wrp').addClass('open');
				$('body').addClass('uk-overflow-hidden');
			}
		});

		// Stop many submits.
		$( '.wpcf7-submit' ).on(
			'click',
			function () {
				$( this ).css( 'pointer-events','none' );
			}
		);
		document.addEventListener(
			'wpcf7submit',
			function ( event ) {
				$( '.wpcf7-submit' ).css( 'pointer-events','' );
			},
			false
		);

	}
);

document.addEventListener('DOMContentLoaded', function() {
	// Global scroll-triggered background color theme
	const blocksWithTheme = document.querySelectorAll('[data-theme]');
	if (!blocksWithTheme.length) return;

	const observerOptions = {
		root: null,
		rootMargin: '-50% 0px -50% 0px', // Triggers when the block crosses the middle of the viewport
		threshold: 0
	};

	const themeObserver = new IntersectionObserver((entries) => {
		entries.forEach(entry => {
			if (entry.isIntersecting) {
				const theme = entry.target.getAttribute('data-theme');

				// Optional: Strip any existing theme classes start with 'theme-' from body
				document.body.className = document.body.className.replace(/\btheme-\S+/g, '').trim();

				// Add new theme class
				if (theme) {
					document.body.classList.add(`theme-${theme}`);
				}
			}
		});
	}, observerOptions);

	blocksWithTheme.forEach(block => themeObserver.observe(block));
});
