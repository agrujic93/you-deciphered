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
			'--current-theme-bg': '#1a1a1a',
			'--current-theme-text': '#ffffff',
			'--current-theme-accent': '#1cc8ff'
		},
		'light': {
			'--current-theme-bg': '#ffffff',
			'--current-theme-text': '#00022e',
			'--current-theme-accent': '#4a84ff'
		},
		'accent': {
			'--current-theme-bg': '#024b6c',
			'--current-theme-text': '#ffffff',
			'--current-theme-accent': '#f8f279'
		}
	};

	blocksWithTheme.forEach((block) => {
		const theme = block.getAttribute('data-theme');
		const config = themeConfigs[theme];
		if (!config) return;

		// GSAP Scrubbing transition - Interpolates according to scroll speed
		gsap.to(document.documentElement, {
			...config,
			scrollTrigger: {
				trigger: block,
				start: 'top 80%', // Start transition as the block enters the lower part of viewport
				end: 'top 20%',   // Finalize as it reaches the upper part
				scrub: 1,         // Follows scroll speed with subtle lag for smooth feel
				overwrite: 'auto',
				onEnter: () => {
					document.body.className = document.body.className.replace(/\btheme-\S+/g, '').trim();
					document.body.classList.add(`theme-${theme}`);
				},
				onEnterBack: () => {
					document.body.className = document.body.className.replace(/\btheme-\S+/g, '').trim();
					document.body.classList.add(`theme-${theme}`);
				}
			}
		});
	});
});
