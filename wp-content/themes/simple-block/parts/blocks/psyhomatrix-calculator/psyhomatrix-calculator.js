(function () {
	'use strict';

	let html2canvasLoader = null;

	/**
	 * Lazy-load locally bundled html2canvas on first Download click.
	 *
	 * @param {string} src Local URL to html2canvas.min.js.
	 * @return {Promise<Function>} Resolves with window.html2canvas.
	 */
	function loadHtml2Canvas(src) {
		if (typeof window.html2canvas === 'function') {
			return Promise.resolve(window.html2canvas);
		}
		if (html2canvasLoader) {
			return html2canvasLoader;
		}
		html2canvasLoader = new Promise(function (resolve, reject) {
			const script = document.createElement('script');
			script.src = src;
			script.async = true;
			script.onload = function () {
				if (typeof window.html2canvas === 'function') {
					resolve(window.html2canvas);
				} else {
					reject(new Error('html2canvas failed to initialize.'));
				}
			};
			script.onerror = function () {
				html2canvasLoader = null;
				reject(new Error('Failed to load html2canvas.'));
			};
			document.head.appendChild(script);
		});
		return html2canvasLoader;
	}

	/**
	 * Sum all digits of a non-negative integer or numeric string.
	 *
	 * @param {number|string} value Numeric value.
	 * @return {number} Sum of digits.
	 */
	function sumDigits(value) {
		return String(value)
			.replace(/\D/g, '')
			.split('')
			.reduce(function (sum, digit) {
				return sum + parseInt(digit, 10);
			}, 0);
	}

	/**
	 * Validate real calendar date (handles leap years and 30/31-day months).
	 *
	 * @param {number} day Day (1..31).
	 * @param {number} month Month (1..12).
	 * @param {number} year Year (1..9999).
	 * @return {boolean} True if valid calendar date.
	 */
	function isValidCalendarDate(day, month, year) {
		if (year < 1 || year > 9999 || month < 1 || month > 12 || day < 1 || day > 31) {
			return false;
		}
		const date = new Date(0);
		date.setFullYear(year, month - 1, day);
		return (
			date.getFullYear() === year &&
			date.getMonth() === month - 1 &&
			date.getDate() === day
		);
	}

	/**
	 * Copy text to clipboard with legacy fallback.
	 *
	 * @param {string} text Text to copy.
	 * @return {Promise<void>}
	 */
	function copyToClipboard(text) {
		if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
			return navigator.clipboard.writeText(text);
		}
		return new Promise(function (resolve, reject) {
			try {
				const textarea = document.createElement('textarea');
				textarea.value = text;
				textarea.setAttribute('readonly', '');
				textarea.style.position = 'fixed';
				textarea.style.opacity = '0';
				document.body.appendChild(textarea);
				textarea.select();
				const ok = document.execCommand('copy');
				document.body.removeChild(textarea);
				if (ok) {
					resolve();
				} else {
					reject(new Error('Copy command failed.'));
				}
			} catch (err) {
				reject(err);
			}
		});
	}

	/**
	 * Initialize a single Psyhomatrix Calculator block instance.
	 *
	 * @param {HTMLElement} blockRoot Root section element.
	 */
	function initCalculatorBlock(blockRoot) {
		if (blockRoot.dataset.psyhomatrixInit === '1') {
			return;
		}
		blockRoot.dataset.psyhomatrixInit = '1';

		const configEl = blockRoot.querySelector('.psyhomatrix-config');
		if (!configEl) {
			return;
		}

		let config = {};
		try {
			config = JSON.parse(configEl.textContent || '{}');
		} catch (e) {
			return;
		}

		const strings = config.strings || {};
		const fallbacks = config.fallbacks || {};
		const downloadCfg = config.download || {};
		const descriptions = config.descriptions || { goal: {}, base: {} };

		const input = blockRoot.querySelector('#birthdate');
		const calcBtn = blockRoot.querySelector('#calculate');
		const resetBtn = blockRoot.querySelector('#reset');
		const textMsg = blockRoot.querySelector('#text-message');
		const warningMsg = blockRoot.querySelector('#warning-message');
		const resultSection = blockRoot.querySelector('#calcresult');
		const matrixHeading = blockRoot.querySelector('.psyhomatrix-result-heading');
		const linkBlock = blockRoot.querySelector('#linkblock');
		const resultLinkInput = blockRoot.querySelector('#resultLink');
		const copyBtn = blockRoot.querySelector('#copyLinkBtn');
		const qrToggleBtn = blockRoot.querySelector('#qrcodeshow');
		const qrContainer = blockRoot.querySelector('#qrcode');
		const downloadBtn = blockRoot.querySelector('#downloadMatrix');
		const captureTarget = blockRoot.querySelector('.psychomatrix-capture');
		const previewEls = blockRoot.querySelectorAll('.bodyofprewiew, .headofprewiew');
		const dopNumbersEl = blockRoot.querySelector('.dop_numbers');
		const goalHeadEl = blockRoot.querySelector('.GoalHead .psyhomatrix-card-title');
		const baseHeadEl = blockRoot.querySelector('.BaseHead .psyhomatrix-card-title');
		const goalBodyEl = blockRoot.querySelector('.goaltrakt');
		const baseBodyEl = blockRoot.querySelector('.osnova');

		if (!input || !calcBtn || !resetBtn || !resultSection) {
			return;
		}

		const matrixCells = {
			'1': blockRoot.querySelector('.numbers_1'),
			'2': blockRoot.querySelector('.numbers_2'),
			'3': blockRoot.querySelector('.numbers_3'),
			'4': blockRoot.querySelector('.numbers_4'),
			'5': blockRoot.querySelector('.numbers_5'),
			'6': blockRoot.querySelector('.numbers_6'),
			'7': blockRoot.querySelector('.numbers_7'),
			'8': blockRoot.querySelector('.numbers_8'),
			'9': blockRoot.querySelector('.numbers_9'),
		};

		let currentFormattedDate = '';

		function showWarning(message) {
			if (warningMsg) {
				warningMsg.textContent = message || strings.invalidDate || '';
				warningMsg.hidden = false;
			}
			if (textMsg) {
				textMsg.hidden = true;
			}
		}

		function hideWarning() {
			if (warningMsg) {
				warningMsg.hidden = true;
			}
		}

		function closeQrPopup() {
			if (qrContainer) {
				qrContainer.hidden = true;
			}
			if (qrToggleBtn) {
				qrToggleBtn.setAttribute('aria-expanded', 'false');
			}
		}

		function formatAndValidateInput() {
			const digits = input.value.replace(/\D/g, '').slice(0, 8);
			const day = digits.substring(0, Math.min(2, digits.length));
			const month = digits.length > 2 ? digits.substring(2, Math.min(4, digits.length)) : '';
			const year = digits.length > 4 ? digits.substring(4, Math.min(8, digits.length)) : '';

			let formatted = day;
			if (day.length === 2 && (month || year)) {
				formatted += '.';
			}
			if (month) {
				formatted += month;
				if (month.length === 2 && year) {
					formatted += '.';
				}
			}
			if (year) {
				formatted += year;
			}

			input.value = formatted;

			const dayInt = day.length === 2 ? parseInt(day, 10) : null;
			const monthInt = month.length === 2 ? parseInt(month, 10) : null;
			const yearInt = year.length === 4 ? parseInt(year, 10) : null;

			let isValidDate = true;
			if (dayInt !== null && (dayInt < 1 || dayInt > 31)) {
				isValidDate = false;
			}
			if (monthInt !== null && (monthInt < 1 || monthInt > 12)) {
				isValidDate = false;
			}
			if (yearInt !== null && (yearInt < 1 || yearInt > 9999)) {
				isValidDate = false;
			}
			if (isValidDate && dayInt !== null && monthInt !== null && yearInt !== null) {
				if (!isValidCalendarDate(dayInt, monthInt, yearInt)) {
					isValidDate = false;
				}
			}

			input.classList.toggle('input-error', !isValidDate);

			const isComplete = day.length === 2 && month.length === 2 && year.length === 4;

			if (!isValidDate) {
				showWarning(strings.invalidDate);
				calcBtn.disabled = true;
			} else if (isComplete) {
				hideWarning();
				if (textMsg) {
					textMsg.hidden = true;
				}
				calcBtn.disabled = false;
			} else if (digits.length > 0) {
				hideWarning();
				if (textMsg) {
					textMsg.hidden = false;
				}
				calcBtn.disabled = true;
			} else {
				hideWarning();
				if (textMsg) {
					textMsg.hidden = true;
				}
				calcBtn.disabled = true;
			}
		}

		function countDigitsInCells(classNames) {
			let count = 0;
			classNames.forEach(function (cls) {
				const cell = blockRoot.querySelector('.' + cls);
				if (!cell) {
					return;
				}
				const matches = (cell.textContent || '').match(/\d/g);
				count += matches ? matches.length : 0;
			});
			return count;
		}

		function runCalculation(options) {
			const opts = options || {};
			const dateStr = input.value.trim();
			const match = /^(\d{2})\.(\d{2})\.(\d{4})$/.exec(dateStr);

			if (!match) {
				showWarning(strings.invalidFormat);
				return;
			}

			const dayStr = match[1];
			const monthStr = match[2];
			const yearStr = match[3];
			const day = parseInt(dayStr, 10);
			const month = parseInt(monthStr, 10);
			const year = parseInt(yearStr, 10);

			if (day < 1 || day > 31) {
				showWarning(strings.invalidDay);
				return;
			}
			if (month < 1 || month > 12) {
				showWarning(strings.invalidMonth);
				return;
			}
			if (!isValidCalendarDate(day, month, year)) {
				showWarning(strings.invalidDate);
				return;
			}

			hideWarning();
			if (textMsg) {
				textMsg.hidden = true;
			}

			currentFormattedDate = dayStr + '.' + monthStr + '.' + yearStr;
			input.value = currentFormattedDate;

			resultSection.hidden = false;
			previewEls.forEach(function (el) {
				el.hidden = true;
			});

			if (matrixHeading) {
				matrixHeading.textContent = (strings.matrixHeadingPrefix || 'Calculation results for date:') + ' ' + currentFormattedDate;
			}

			if (!opts.skipScroll) {
				resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}

			// Core numerology formulas (exact match to source script).
			const firstNumber = sumDigits('' + day + month + year);
			const secondNumber = firstNumber < 10 ? firstNumber : sumDigits(firstNumber);

			let originalThirdNumber;
			if (day) {
				originalThirdNumber = firstNumber - 2 * parseInt(String(day)[0] || '0', 10);
			} else if (month) {
				originalThirdNumber = firstNumber - 2 * parseInt(String(month)[0] || '0', 10);
			} else {
				originalThirdNumber = firstNumber - 2 * parseInt(String(year)[0] || '0', 10);
			}

			const thirdNumber = Math.abs(originalThirdNumber);
			const fourthNumber = sumDigits(thirdNumber);
			const displayThirdNumber = originalThirdNumber < 0 ? '(' + originalThirdNumber + ')' : String(originalThirdNumber);

			if (dopNumbersEl) {
				dopNumbersEl.innerHTML =
					'<span class="dop-pair">' + firstNumber + ' ' + secondNumber + '</span>' +
					'<span class="dop-sep" aria-hidden="true">&nbsp;&nbsp;</span>' +
					'<span class="dop-pair">' + displayThirdNumber + ' ' + fourthNumber + '</span>';
			}

			// Goal & Base descriptions.
			const goalKey = firstNumber + '|' + secondNumber;
			const baseKey = displayThirdNumber + '|' + fourthNumber;
			const goalEntry = descriptions.goal && descriptions.goal[goalKey] ? descriptions.goal[goalKey] : null;
			const baseEntry = descriptions.base && descriptions.base[baseKey] ? descriptions.base[baseKey] : null;

			if (goalHeadEl) {
				goalHeadEl.textContent =
					goalEntry && goalEntry.title
						? goalEntry.title
						: (strings.goalHeadingPrefix || 'Goal:') + ' ' + firstNumber + ' ' + secondNumber;
			}
			if (baseHeadEl) {
				baseHeadEl.textContent =
					baseEntry && baseEntry.title
						? baseEntry.title
						: (strings.baseHeadingPrefix || 'Base:') + ' ' + displayThirdNumber + ' ' + fourthNumber;
			}

			if (goalBodyEl) {
				goalBodyEl.innerHTML = goalEntry && goalEntry.html ? goalEntry.html : (fallbacks.goal || '');
			}
			if (baseBodyEl) {
				baseBodyEl.innerHTML = baseEntry && baseEntry.html ? baseEntry.html : (fallbacks.base || '');
			}

			// Share URL & history.replaceState sync.
			const currentUrl = window.location.origin + window.location.pathname;
			const resultUrl = currentUrl + '?date=' + currentFormattedDate;

			if (window.history && typeof window.history.replaceState === 'function') {
				window.history.replaceState(null, '', resultUrl);
			}

			if (resultLinkInput) {
				resultLinkInput.value = resultUrl;
			}
			if (linkBlock) {
				linkBlock.hidden = false;
			}
			if (copyBtn) {
				copyBtn.textContent = strings.copyLabel || 'Copy Link';
				copyBtn.classList.remove('is-copied');
			}

			// Regenerate QR code from local QRCode library.
			closeQrPopup();
			if (qrContainer && typeof window.QRCode === 'function') {
				qrContainer.innerHTML = '';
				new window.QRCode(qrContainer, {
					text: resultUrl,
					width: 120,
					height: 120,
				});
			}

			// Populate 3x3 matrix cells (1..9).
			const buckets = { '1': '', '2': '', '3': '', '4': '', '5': '', '6': '', '7': '', '8': '', '9': '' };
			[day, month, year, firstNumber, secondNumber, thirdNumber, fourthNumber].forEach(function (num) {
				String(num)
					.split('')
					.forEach(function (digit) {
						if (Object.prototype.hasOwnProperty.call(buckets, digit)) {
							buckets[digit] += digit;
						}
					});
			});

			Object.keys(matrixCells).forEach(function (digit) {
				const cell = matrixCells[digit];
				if (cell) {
					cell.textContent = buckets[digit].length > 0 ? buckets[digit] : '-';
				}
			});

			// Line counters.
			const lineMappings = {
				goal: ['numbers_1', 'numbers_4', 'numbers_7'],
				family: ['numbers_2', 'numbers_5', 'numbers_8'],
				habit: ['numbers_3', 'numbers_6', 'numbers_9'],
				selfrating: ['numbers_1', 'numbers_2', 'numbers_3'],
				life: ['numbers_4', 'numbers_5', 'numbers_6'],
				potential: ['numbers_7', 'numbers_8', 'numbers_9'],
				spiritual: ['numbers_1', 'numbers_5', 'numbers_9'],
				carnals: ['numbers_3', 'numbers_5', 'numbers_7'],
			};

			Object.keys(lineMappings).forEach(function (lineClass) {
				const el = blockRoot.querySelector('.' + lineClass);
				if (el) {
					el.textContent = String(countDigitsInCells(lineMappings[lineClass]));
				}
			});
		}

		// Input formatting & Enter key submit.
		input.addEventListener('input', formatAndValidateInput);
		input.addEventListener('keydown', function (event) {
			if (event.key === 'Enter' && !calcBtn.disabled) {
				event.preventDefault();
				runCalculation();
			}
		});
		input.addEventListener('focus', function () {
			if (/^\d{2}\.\d{2}\.\d{4}$/.test(input.value)) {
				input.select();
			}
		});

		calcBtn.addEventListener('click', function () {
			runCalculation();
		});

		// Reset handler.
		resetBtn.addEventListener('click', function () {
			input.value = '';
			input.classList.remove('input-error');
			currentFormattedDate = '';
			calcBtn.disabled = true;
			hideWarning();
			if (textMsg) {
				textMsg.hidden = true;
			}
			resultSection.hidden = true;
			if (linkBlock) {
				linkBlock.hidden = true;
			}
			if (resultLinkInput) {
				resultLinkInput.value = '';
			}
			if (qrContainer) {
				qrContainer.innerHTML = '';
			}
			closeQrPopup();
			previewEls.forEach(function (el) {
				el.hidden = false;
			});
			if (window.history && typeof window.history.replaceState === 'function') {
				window.history.replaceState(null, '', window.location.pathname);
			}
		});

		// Copy link button.
		if (copyBtn && resultLinkInput) {
			copyBtn.addEventListener('click', function () {
				copyToClipboard(resultLinkInput.value).then(function () {
					copyBtn.textContent = strings.copiedLabel || 'Copied';
					copyBtn.classList.add('is-copied');
				});
			});
		}

		// QR toggle & outside-click / Escape close.
		if (qrToggleBtn && qrContainer) {
			qrToggleBtn.addEventListener('click', function (event) {
				event.stopPropagation();
				const isHidden = qrContainer.hidden;
				qrContainer.hidden = !isHidden;
				qrToggleBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
			});

			qrContainer.addEventListener('click', function (event) {
				event.stopPropagation();
			});

			document.addEventListener('click', closeQrPopup);
			document.addEventListener('keydown', function (event) {
				if (event.key === 'Escape') {
					closeQrPopup();
				}
			});
		}

		// Download matrix as PNG (lazy-loads local html2canvas).
		if (downloadBtn && captureTarget) {
			downloadBtn.addEventListener('click', function () {
				if (!config.html2canvasUrl) {
					return;
				}
				downloadBtn.disabled = true;
				let signatureWrapper = null;

				loadHtml2Canvas(config.html2canvasUrl)
					.then(function (html2canvasFn) {
						if (downloadCfg.logoUrl || downloadCfg.signatureText) {
							signatureWrapper = document.createElement('div');
							signatureWrapper.className = 'psyhomatrix-download-signature';

							if (downloadCfg.logoUrl) {
								const logo = document.createElement('img');
								logo.src = downloadCfg.logoUrl;
								logo.alt = downloadCfg.logoAlt || '';
								logo.className = 'psyhomatrix-download-logo';
								signatureWrapper.appendChild(logo);
							}

							if (downloadCfg.signatureText) {
								const sigText = document.createElement('span');
								sigText.textContent = downloadCfg.signatureText;
								sigText.className = 'psyhomatrix-download-text';
								signatureWrapper.appendChild(sigText);
							}

							captureTarget.appendChild(signatureWrapper);
						}

						return html2canvasFn(captureTarget, {
							scale: 2,
							useCORS: true,
							onclone: function (clonedDoc) {
								// Normalize any CSS Color 4 functions (e.g. color(), oklch()) that html2canvas 1.4.x cannot parse.
								const probeCanvas = document.createElement('canvas');
								probeCanvas.width = 1;
								probeCanvas.height = 1;
								const ctx = probeCanvas.getContext('2d', { willReadFrequently: true });
								const unsupportedRe = /\b(?:color|oklch|oklab|lab|lch|color-mix)\(/i;
								const colorProps = ['color', 'backgroundColor', 'borderTopColor', 'borderRightColor', 'borderBottomColor', 'borderLeftColor', 'outlineColor'];

								function toRgba(cssColor) {
									if (!ctx) {
										return 'transparent';
									}
									ctx.clearRect(0, 0, 1, 1);
									ctx.fillStyle = cssColor;
									ctx.fillRect(0, 0, 1, 1);
									const d = ctx.getImageData(0, 0, 1, 1).data;
									return 'rgba(' + d[0] + ', ' + d[1] + ', ' + d[2] + ', ' + (d[3] / 255).toFixed(3) + ')';
								}

								const clonedRoot = clonedDoc.querySelector('.psychomatrix-capture');
								const targets = clonedRoot ? [clonedDoc.documentElement, clonedDoc.body, clonedRoot].concat(Array.from(clonedRoot.querySelectorAll('*'))) : [];
								targets.forEach(function (el) {
									if (!el || !el.style) {
										return;
									}
									const cs = clonedDoc.defaultView.getComputedStyle(el);
									colorProps.forEach(function (prop) {
										const val = cs[prop];
										if (val && unsupportedRe.test(val)) {
											el.style[prop] = toRgba(val);
										}
									});
									if (cs.boxShadow && unsupportedRe.test(cs.boxShadow)) {
										el.style.boxShadow = 'none';
									}
								});
							},
						}).then(function (canvas) {
							const fileDate = currentFormattedDate
								? currentFormattedDate.replace(/\./g, '-')
								: 'result';
							const link = document.createElement('a');
							link.download = 'psyhomatrix-' + fileDate + '.png';
							link.href = canvas.toDataURL('image/png');
							link.click();
						});
					})
					.finally(function () {
						if (signatureWrapper) {
							signatureWrapper.remove();
							signatureWrapper = null;
						}
						downloadBtn.disabled = false;
					});
			});
		}

		// Autoload from ?date=DD.MM.YYYY query parameter.
		const urlParams = new URLSearchParams(window.location.search);
		const dateParam = urlParams.get('date');
		if (dateParam && /^\d{2}\.\d{2}\.\d{4}$/.test(dateParam)) {
			input.value = dateParam;
			formatAndValidateInput();
			if (!calcBtn.disabled) {
				runCalculation({ skipScroll: true });
			}
		}
	}

	function initAllBlocks() {
		document.querySelectorAll('.psyhomatrix-calculator-block').forEach(initCalculatorBlock);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAllBlocks);
	} else {
		initAllBlocks();
	}
})();
