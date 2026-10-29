/**
 * Price Boxes Script.
 */
(function($) {
	function initPriceBoxes($block) {
		// Your logic here
		console.log('Price Boxes block initialized');
	}

	$(document).ready(function() {
		$('.price-boxes-block').each(function() {
			initPriceBoxes($(this));
		});
	});

	var acfObj = window.acf || (window.top && window.top.acf);
	if (acfObj) {
		acfObj.addAction('render_block_preview/type=price-boxes', function($block) {
			if ($block) {
				initPriceBoxes($block);
			}
		});
	}
})(jQuery);
