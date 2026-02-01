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
