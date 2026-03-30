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

	function applyTheme(theme) {
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

	function getThemeFromViewportPivot() {
		const viewportHeight = window.innerHeight;
		const pivotY = viewportHeight * 0.5;
		let nearestBlockTheme = null;
		let nearestDistance = Infinity;

		for (const block of blocksWithTheme) {
			const theme = block.getAttribute('data-theme');
			if (!theme || !themeConfigs[theme]) continue;

			const rect = block.getBoundingClientRect();

			// Preferred match: block that intersects viewport center.
			if (rect.top <= pivotY && rect.bottom >= pivotY) {
				return theme;
			}

			// Fallback: nearest block edge to center.
			const distanceToPivot = Math.min(
				Math.abs(rect.top - pivotY),
				Math.abs(rect.bottom - pivotY)
			);

			if (distanceToPivot < nearestDistance) {
				nearestDistance = distanceToPivot;
				nearestBlockTheme = theme;
			}
		}

		return nearestBlockTheme;
	}

	function syncThemeToViewport() {
		const activeTheme = getThemeFromViewportPivot();
		if (activeTheme) {
			applyTheme(activeTheme);
		}
	}

	ScrollTrigger.create({
		trigger: document.body,
		start: 'top top',
		end: 'bottom bottom',
		onUpdate: syncThemeToViewport,
		onRefresh: syncThemeToViewport
	});

	// Initial check on load
	syncThemeToViewport();
});
