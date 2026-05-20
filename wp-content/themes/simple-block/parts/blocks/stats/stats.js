/**
 * Stats Script.
 */
jQuery(document).ready(function($) {
	var formatCounter = function(value, decimals) {
		var fixedValue = Number(value).toFixed(decimals);
		var parts = fixedValue.split('.');

		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

		if (decimals > 0) {
			return parts[0] + ',' + parts[1];
		}

		return parts[0];
	};

	var runCounter = function($counter) {
		if ($counter.data('counterAnimated')) {
			return;
		}

		var target = parseFloat($counter.attr('data-counter-target'));
		var decimals = parseInt($counter.attr('data-counter-decimals'), 10) || 0;

		if (isNaN(target)) {
			return;
		}

		$counter.data('counterAnimated', true);

		var duration = 4000;
		var startTime = null;

		var tick = function(currentTime) {
			if (!startTime) {
				startTime = currentTime;
			}

			var progress = Math.min((currentTime - startTime) / duration, 1);
			var eased = 1 - Math.pow(1 - progress, 3);
			var currentValue = target * eased;

			$counter.text(formatCounter(currentValue, decimals));

			if (progress < 1) {
				window.requestAnimationFrame(tick);
				return;
			}

			$counter.text(formatCounter(target, decimals));
		};

		window.requestAnimationFrame(tick);
	};

	var initStatsBlock = function($block) {
		var $counters = $block.find('.js-stats-counter');

		if (!$counters.length) {
			return;
		}

		var runAll = function() {
			$counters.each(function() {
				runCounter($(this));
			});
		};

		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					if (!entry.isIntersecting) {
						return;
					}

					runAll();
					observer.disconnect();
				});
			}, {
				threshold: 0.35
			});

			observer.observe($block.get(0));
			return;
		}

		runAll();
	};

	$('.ci-stats-block').each(function() {
		initStatsBlock($(this));
	});
});
