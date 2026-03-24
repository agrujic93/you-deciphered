/**
 * File main.js.
 *
 * @package ci-uikit
 */

document.addEventListener('DOMContentLoaded', function() {
	// Add loaded class to body
	document.body.classList.add('loaded');

	// Stop multiple submits (Contact Form 7)
	const cf7Submits = document.querySelectorAll('.wpcf7-submit');
	cf7Submits.forEach(submit => {
		submit.addEventListener('click', function() {
			this.style.pointerEvents = 'none';
		});
	});

	// Reset submit button state on CF7 submit event
	document.addEventListener('wpcf7submit', function() {
		const cf7SubmitsToReset = document.querySelectorAll('.wpcf7-submit');
		cf7SubmitsToReset.forEach(submit => {
			submit.style.pointerEvents = '';
		});
	}, false);

	// Global scroll-triggered background color theme
	const blocksWithTheme = document.querySelectorAll('[data-theme]');
	if (!blocksWithTheme.length) return;

	// Check if GSAP is available
	if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
		console.warn('GSAP or ScrollTrigger not loaded for global theme swapper.');
		return;
	}

	gsap.registerPlugin(ScrollTrigger);

	const themeConfigs = {
		'dark': {
			'--current-theme-bg': '#00022e',
			'--current-theme-text': '#ffffff',
			'--current-theme-accent': '#1cc8ff'
		},
		'light': {
			'--current-theme-bg': '#f9f9f9',
			'--current-theme-text': '#00022e',
			'--current-theme-accent': '#1cc8ff'
		},
		'accent': {
			'--current-theme-bg': '#f9f9f9',
			'--current-theme-text': '#00022e',
			'--current-theme-accent': '#1cc8ff'
		}
	};

	let lastAppliedTheme = null;

	function applyTheme(theme, direction) {
		if (theme === lastAppliedTheme) return;
		
		const config = themeConfigs[theme];
		if (!config) return;

		lastAppliedTheme = theme;

		// Update Body Class
		document.body.className = document.body.className.replace(/\btheme-\S+/g, '').trim();
		document.body.classList.add(`theme-${theme}`);

		// Smoothly transition CSS variables
		// We use a duration instead of scrub to avoid "flashes" caused by overlapping triggers
		// that seek from different initial states.
		gsap.to(document.documentElement, {
			...config,
			duration: 0.6,
			ease: 'power2.out',
			overwrite: true
		});
	}

	blocksWithTheme.forEach((block, index) => {
		const theme = block.getAttribute('data-theme');
		
		ScrollTrigger.create({
			trigger: block,
			start: 'top 60%', // Trigger slightly earlier for better feeling
			end: 'bottom 40%',
			onEnter: () => applyTheme(theme, 1),
			onEnterBack: () => applyTheme(theme, -1),
			onLeave: () => {
				// Detect if we are moving to next block
				if (index < blocksWithTheme.length - 1) {
					// We don't do anything here, let the next block's onEnter handle it
				}
			},
			onLeaveBack: () => {
				if (index > 0) {
					const prevTheme = blocksWithTheme[index - 1].getAttribute('data-theme');
					applyTheme(prevTheme, -1);
				} else {
					// Past the first block going up - revert to original state if needed
					// For now, keep the first theme's variables but maybe remove class
					document.body.className = document.body.className.replace(/\btheme-\S+/g, '').trim();
				}
			}
		});
	});

	// Initial check on load
	const firstBlock = blocksWithTheme[0];
	if (firstBlock) {
		const theme = firstBlock.getAttribute('data-theme');
		applyTheme(theme, 1);
	}
});
