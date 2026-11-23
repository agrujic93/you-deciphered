# Psyhomatrix Calculator Block Plan (Aligned to Provided Script)

## Objective
Create a new block `simple-block/psyhomatrix-calculator` that reproduces the behavior from your provided script and reference page, then extends it with maintainable ACF-driven description overrides, result image download, and **bilingual English (default) + Serbian (`sr`) support for Polylang Free**, following all **Simple Block** theme rules (`README.md` & `DEVELOPER-STANDARDS.md`).

> **IMPORTANT — Translation & Polylang Free Requirement (English default + Serbian `sr`):**
> - **No Russian in production**: All user-facing strings, UI labels, placeholders, validation/helper messages, matrix cell labels (`data-termin`), result headings, CTA text, and all 49 Goal + 49 Base descriptions from Section 9 must be translated from Russian into **English (default)** and **Serbian (`sr`)**. Section 9 retains the original Russian source script solely as the reference for calculation logic and content meaning.
> - **Short UI strings**: Register all short UI strings in `simple_block_register_polylang_strings()` inside `functions.php` (via `pll_register_string( 'simple-block', $string, 'simple-block' )`) and retrieve them in PHP via `simple_block_pll__( '...' )` (which falls back to English when Polylang is inactive). Pass the resolved strings to JS in the block config.
> - **Long / hardcoded PHP & result texts (Polylang Free pattern)**: Detect the active language in `psyhomatrix-calculator.php` using `function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en'`. Use `if ( 'sr' === $lang ) { ... } else { ... }` for hardcoded PHP markup/fallback blocks and load the matching `'en'` or `'sr'` description dataset.
> - **ACF bilingual overrides**: Every ACF text/WYSIWYG override field (including Goal/Base repeaters) must include both English (`_en`) and Serbian (`_sr`) fields so admins can override either or both languages without Polylang Pro.

---

## 1) Exact script alignment requirements

Implementation must preserve the same core behavior as in your attached script:

1. **Single date input formatting (vanilla JS)**
   - Input field `#birthdate` with an accessible `<label>` (visually hidden if needed), `inputmode="numeric"`, and `autocomplete="bday"` so mobile devices open the numeric keypad automatically.
   - Support pressing `Enter` inside `#birthdate` to trigger calculation when the date is valid.
   - **Fix source conflict**: The reference page attaches two competing handlers (`data-slots` vanilla mask + jQuery `input` formatter). Use a **single vanilla JS** `input` handler (no jQuery dependency) that keeps only digits and formats as `DD.MM.YYYY` progressively.
   - Validate day (`1..31`), month (`1..12`), year (`1..9999`), plus calendar validity (e.g., Feb 29 leap year / 30-day months).
   - **Fix source `.input-error` bug**: The source script's sequential `if/else` blocks overwrite `.input-error` from earlier fields. Compute `isValidDate` across all parts first, then toggle `input-error` once.

2. **Calculate button state logic**
   - **Fix source partial-date contradiction**: The source script enables `#calculate` for 1-part 4-digit year or 2-part dates (showing `#text-message`), yet `#calculate` click strictly rejects anything not matching `^(\d{2})\.(\d{2})\.(\d{4})$`.
   - Recommended: Keep `#calculate` disabled until a full valid `DD.MM.YYYY` is entered (or explicitly implement partial-date calculation if desired), and use `aria-live="polite"` on `#warning-message` / `#text-message`.

3. **Calculate action**
   - On `#calculate` click:
     - strict regex parse `^(\d{2})\.(\d{2})\.(\d{4})$`
     - validate day/month again with error text
     - show `.psychomatrix`, hide preview blocks
     - update matrix heading with selected date
     - smooth-scroll to `#calcresult`

4. **Numerology formulas (must match script)**
   - `firstNumber` = sum of all digits in concatenated date values.
   - `secondNumber` = if `firstNumber < 10` then same value, else sum of digits of `firstNumber`.
   - `originalThirdNumber` = `firstNumber - 2 * first digit of day` (fallback month/year first digit if needed).
   - `thirdNumber` = `abs(originalThirdNumber)` for matrix distribution.
   - `fourthNumber` = sum of digits of `thirdNumber`.
   - Display third number with parentheses if negative (`displayThirdNumber`).

5. **Result header and URL/share behavior**
   - Additional numbers printed in the same order as script (`firstNumber`, `secondNumber`, `displayThirdNumber`, `fourthNumber`).
   - Goal heading: `"Goal: {firstNumber} {secondNumber}"` (label configurable via ACF).
   - Base heading: `"Base: {displayThirdNumber} {fourthNumber}"` (label configurable via ACF).
   - Matrix heading: `"Calculation results for date: DD.MM.YYYY"` (label configurable via ACF).
   - Result URL:
     - `currentUrl + ?date=DD.MM.YYYY` using zero-padded day/month.
     - Sync the browser address bar on calculation via `window.history.replaceState(null, '', resultUrl)` so refreshing or sharing from the address bar preserves the result.
   - Copy button (`#copyLinkBtn`):
     - reset text/color to default before new calculation
     - copy to clipboard (`navigator.clipboard.writeText` with `execCommand('copy')` fallback for non-HTTPS/older browsers)
     - set success text/color on success
   - QR code (`#qrcodeshow` + `#qrcode`):
     - Recreate QR code (`80x80`) from result URL each calculation, toggle visibility on `#qrcodeshow` click, and close on outside click (matching source page).

6. **Goal/Base description mapping**
   - Preserve all 49 Goal combinations (`0|0`, `1|1`..`48|12`) and 49 Base combinations (`0|0`, `1|1`..`48|12`) from the source script:
     - Goal branch keyed by `"{firstNumber}|{secondNumber}"`.
     - Base branch keyed by `"{displayThirdNumber}|{fourthNumber}"`.
   - **Add fallback handling**: Source script has no entries for negative `displayThirdNumber` (e.g., `09.01.2000` produces `(-6)|6`) or `firstNumber > 48` (e.g., `29.09.2999` produces `49|13`). Provide graceful default/ACF fallback text so `.goaltrakt` and `.osnova` are never blank.
   - Keep CTA/fallback content (`.ctamatrix`) where script uses it, editable via ACF.

7. **Matrix population and line counters**
   - Matrix cell mapping:
     - `1 -> .numbers_1`, ..., `9 -> .numbers_9`.
   - Distribute digits from:
     - day, month, year, firstNumber, secondNumber, thirdNumber, fourthNumber.
   - Empty cells rendered with `-`.
   - Compute and render counts for:
     - `.goal` (`1,4,7`)
     - `.family` (`2,5,8`)
     - `.habit` (`3,6,9`)
     - `.selfrating` (`1,2,3`)
     - `.life` (`4,5,6`)
     - `.potential` (`7,8,9`)
     - `.spiritual` (`1,5,9`)
     - `.carnals` (`3,5,7`)
   - Add UIkit tooltips (`data-uk-tooltip="title: ..."`) on the 9 matrix cells, 8 line totals, and Additional Numbers cell (using translated EN/SR descriptions via Polylang/ACF) so users can hover/tap to see what each metric means without cluttering the table.

8. **Reset behavior**
   - **Fix source `#reset` bug**: Source script clears `#day, #month, #year` (commented-out legacy inputs) and forgets `#birthdate`, `#linkblock`, `#qrcode`, `#text-message`, and `.input-error`.
   - `#reset` must clear `#birthdate`, remove `.input-error`, hide `.psychomatrix` and `#linkblock`, clear `#qrcode`, disable `#calculate`, hide `#warning-message` and `#text-message`, remove `?date=` from the URL via `window.history.replaceState(null, '', window.location.pathname)`, and restore preview blocks.

---

## 2) Block build structure (Simple Block standard)

Create in `wp-content/themes/simple-block/parts/blocks/psyhomatrix-calculator/`:

- `block.json` (metadata, namespace `simple-block/psyhomatrix-calculator`, `viewScript` / `viewStyle` references)
- `psyhomatrix-calculator.php` (block render template; reuse `parts/blocks/block-parts/block-general-logic.php` and `inc-buttons.php` where applicable; keep `.has-global-padding` and `alignwide` / `alignfull` / `none` alignment rules; Section 508 accessible markup)
- `psyhomatrix-calculator.scss` (`@use '../../assets/css/sass/custom/theme-variables' as *;`; `rem` units only with `1rem = 10px` (`62.5%`); use `var(--wp--preset--color--*)`, `var(--wp--preset--font-size--*)`, `var(--wp--preset--spacing--*)` from `theme.json` and `_theme-variables.scss`; no hardcoded hex/RGB or `px`; no `!important` unless overriding core/plugin styles with a comment; `@media screen and (max-width: ...)` media queries; compiled via `gulp watch` — never edit compiled `.css` directly)
- `psyhomatrix-calculator.js` (vanilla JS, zero jQuery; prefer UIkit `data-uk-*` attributes such as `data-uk-tooltip` and utility classes like `uk-flex`, `uk-grid`, `uk-button` for layout/toggles before custom CSS/JS)
- `vendor/qrcode.min.js` & `vendor/html2canvas.min.js` (bundled locally in the block or `assets/js/vendor/` — no third-party CDNs)
- `data/descriptions.en.json` & `data/descriptions.sr.json` (or `data/descriptions.json` with `en` and `sr` keys: default 49 Goal + 49 Base HTML descriptions translated from Russian into English and Serbian)
- `images/preview.png` (or `preview.png`, plus `.webp` assets where applicable)

Asset & PHP registration (`DEVELOPER-STANDARDS.md` Sections 12–13 & Polylang):
- Register `psyhomatrix-calculator` viewScript and local `qrcodejs` handle inside `cwp_register_block_script()` in `functions.php` hooked to `init`, versioned via `file_exists( $path ) ? filemtime( $path ) : '1.0.0'`.
- Pass the local `html2canvas` asset URL (with `filemtime()` version query) in block config and **lazy-load** `html2canvas` on demand when the user clicks Download (keeping initial page load fast).
- Register all calculator UI strings (buttons, labels, placeholders, validation messages, matrix `data-termin` labels, headings) in `simple_block_register_polylang_strings()` inside `functions.php`, and output/pass them via `simple_block_pll__( '...' )`.
- Wrap any new PHP helpers in `if ( ! function_exists( '...' ) )` with `simple_block_` / `ci_` / `cwp_` prefixes; keep `add_action`/`add_filter` outside function declarations.

---

## 3) ACF model & ACF JSON creation (for Dashboard Sync)

### 3.1 Create ACF JSON file directly for Dashboard Sync
Per `DEVELOPER-STANDARDS.md` Section 4 and `functions.php` (`my_acf_json_save_path` / `my_acf_json_load_path` / `my_acf_json_filename`):
- **Do NOT require manual field creation in WP Dashboard.** The implementing agent must generate the ACF Field Group JSON file at:
  - `wp-content/uploads/acf-json/block-psyhomatrix-calculator.json` (and copy to theme `acf-json/` if/when synced for deployment).
- Field Group title: `"Block - Psyhomatrix Calculator"` (slugified filename `block-psyhomatrix-calculator.json`).
- Location rule: `Block == simple-block/psyhomatrix-calculator`.
- After generation, the user can simply open **WP Dashboard → ACF → Field Groups → Sync available** and click **Sync**.

### 3.2 Required fields in `block-psyhomatrix-calculator.json` (Bilingual EN + SR)
Because the site uses **Polylang Free**, every overridable text/WYSIWYG field must provide **both English (`_en`) and Serbian (`_sr`) fields** (e.g., organized into `English (Default)` and `Serbian (SR)` ACF tabs). In PHP, check `pll_current_language( 'slug' ) === 'sr'` to pick the `_sr` field when present, falling back to `_en` or `simple_block_pll__( '...' )`:

#### A) UI text & label overrides (`_en` and `_sr` pairs)
- Block title / subtitle & matrix heading template (default EN: `"Calculation results for date:"`, SR via Polylang string / `_sr` override)
- Goal & Base heading prefixes (defaults EN: `"Goal:"`, `"Base:"`)
- Input `<label>` & placeholder (default: `"DD.MM.YYYY"`)
- Button labels: Calculate (`"Calculate"`), Reset (`"Reset"`), Copy Link (`"Copy Link"`), Copied (`"Copied ✔"`), QR Toggle (`"QR Code"`), Download (`"Download Matrix"`)
- Validation & helper messages (`#warning-message`, `#text-message`)
- Matrix cell labels (`data-termin` / captions for the 9 cells — Character, Energy, Interest, Health, Logic, Labor, Luck, Duty, Memory — plus the 8 line totals and Additional Numbers), registered in `simple_block_register_polylang_strings()` with optional ACF overrides
- Share link label (default: `"Link to your result:"`)
- CTA block (`.ctamatrix` WYSIWYG: `cta_content_en` / `cta_content_sr`) & fallback Goal/Base descriptions (`goal_fallback_en` / `goal_fallback_sr`, `base_fallback_en` / `base_fallback_sr`)
- Optional download signature: `download_logo` (Image Array, output using `['sizes']['large']` or appropriate registered size and `alt` attribute) + `download_signature_text_en` / `download_signature_text_sr`

#### B) Goal description overrides (repeater)
`goal_descriptions` rows (descriptive `"Add Row"` label, e.g., `"Add Goal Override"`):
- `first_number` (number)
- `second_number` (number)
- `title_en` (text, optional) & `title_sr` (text, optional)
- `content_en` (WYSIWYG/textarea) & `content_sr` (WYSIWYG/textarea)
- `is_active` (true/false, default `1`)

#### C) Base description overrides (repeater)
`base_descriptions` rows (descriptive `"Add Row"` label, e.g., `"Add Base Override"`):
- `third_number_display` (text; supports signed/parenthesized values like `(-6)` or `-6`)
- `fourth_number` (number)
- `title_en` (text, optional) & `title_sr` (text, optional)
- `content_en` (WYSIWYG/textarea) & `content_sr` (WYSIWYG/textarea)
- `is_active` (true/false, default `1`)

### 3.3 Language-aware priority logic
Detect active language in PHP: `$lang = ( function_exists( 'pll_current_language' ) && 'sr' === pll_current_language( 'slug' ) ) ? 'sr' : 'en';`
1. Use ACF override for the active language (`_sr` if `$lang === 'sr'` and non-empty, else `_en` if `$lang === 'en'`) when `is_active` is true and combination key matches.
2. Otherwise use the default translated map for `$lang` (`data/descriptions.sr.json` or `data/descriptions.en.json`).
3. Otherwise render the language-appropriate ACF/hardcoded PHP fallback (`<?php if ( 'sr' === $lang ) : ?> ... <?php else : ?> ... <?php endif; ?>`) so no result box is ever empty.

---

## 4) Data strategy for maintainability

Refactor giant `if` chains into structured maps per language (`data/descriptions.en.json` and `data/descriptions.sr.json`, or `data/descriptions.json` keyed by `en`/`sr`) + JS:

- `goalMap["{firstNumber}|{secondNumber}"] = { title, html }`
- `baseMap["{displayThirdNumber}|{fourthNumber}"] = { title, html }`

In `psyhomatrix-calculator.php`, load only the active language's default dataset (`en` or `sr`), resolve `simple_block_pll__( '...' )` UI strings and language-specific ACF overrides, and pass the active-language config to JS (e.g., via `wp_add_inline_script` or block wrapper `data-config`).

---

## 5) URL parameter and state restore

On page load:
- read `date` query parameter
- if valid format `DD.MM.YYYY`, set input and trigger calculate flow automatically.

On Calculate / Reset:
- Share link always generated from current pathname + normalized date.
- Sync the browser address bar with `window.history.replaceState(null, '', resultUrl)` on Calculate, and clear `?date=` on Reset.

---

## 6) Download psychomatrix as image

Action button:
- `#downloadMatrix` (or `#downloadPsychomatrix`)

Implementation:
- **Lazy-load** the locally bundled `html2canvas` script on first Download click (avoiding CDN requests and keeping initial page load fast).
- Capture matrix wrapper (`.raschetblock` or dedicated `.psychomatrix-capture`) via `html2canvas` with `{ scale: 2, useCORS: true }`.
- Optionally append a temporary branded signature/logo footer (from ACF) before capture and remove it immediately after, matching the reference page behavior.
- Export as PNG with meaningful filename:
  - `psyhomatrix-DD-MM-YYYY.png`

---

## 7) QA checklist

1. All UI strings, matrix labels, tooltips, validation messages, and 49+49 Goal/Base descriptions are translated from Russian into **English (default)** and **Serbian (`sr`)**, with Polylang Free (`simple_block_register_polylang_strings()`, `simple_block_pll__()`, and `pll_current_language( 'slug' ) === 'sr'`) switching cleanly between languages.
2. Date formatting, `inputmode="numeric"` mobile keypad, `Enter` key submit, calendar validation, and `.input-error` state work cleanly without jQuery or duplicate masks.
3. Four-number formula output matches source script for test dates (including negative `originalThirdNumber` cases like `09.01.2000`).
4. Goal/Base description shown for all 49+49 combinations from source, plus graceful fallback for unmapped keys.
5. Matrix cell counts, all 8 line totals, and UIkit `data-uk-tooltip` tooltips match source and display cleanly on hover/tap.
6. URL param (`?date=DD.MM.YYYY`) autoload, `history.replaceState` URL sync on Calculate/Reset, and share link generation work accurately.
7. Copy link (with fallback) + QR toggle/outside-click close + local `qrcodejs` regeneration work.
8. `wp-content/uploads/acf-json/block-psyhomatrix-calculator.json` syncs cleanly in WP Dashboard (`ACF → Field Groups → Sync available`), and ACF overrides supersede default text when `is_active` is enabled.
9. Download button lazy-loads local `html2canvas` and exports visible result image (`psyhomatrix-DD-MM-YYYY.png`) cleanly.
10. Reset clears `#birthdate`, `.input-error`, `#linkblock`, `#qrcode`, `#text-message`, `#warning-message`, clears `?date=` via `history.replaceState`, and restores initial state.
11. Accessibility & Theme Standards (`DEVELOPER-STANDARDS.md`): Section 508 (`<label>`, `aria-live="polite"`, meaningful `<img>` `alt` without auto-title fallback, clear link text / `aria-label` only when needed, heading hierarchy), `rem`-only SCSS with `_theme-variables.scss` and `var(--wp--preset--*)`, no `!important`, compiled via `gulp watch`.

---

## 8) Delivery notes

- Keep WordPress core untouched.
- Create `wp-content/uploads/acf-json/block-psyhomatrix-calculator.json` so it can be synced directly in the WP Dashboard (and synced to theme `acf-json/` for deployment).
- Compile SCSS via `gulp watch` from `wp-content/themes/simple-block/`; never commit `node_modules/` or `package-lock.json` and never edit compiled CSS directly.
- Set block preview image (`preview.png`), remove any commented-out/redundant code, and verify zero console errors.
- Reuse existing block template patterns (`block-general-logic.php`, `inc-buttons.php`) from this theme.
- Preserve existing UX/content intent while improving maintainability, English localization, and admin control.


---

## 9) Reference Script & Markup from Source Page

Extracted from `https://numerologaleksandrova.ru/numerology-kalkulyator/`.

### 9.1 URL Parameter Autoload & Input Mask Script

```html
<script>
//Получаем get параметры
function getParam(name) {
    const url = new URL(window.location.href);
    return url.searchParams.get(name);
}

document.addEventListener('DOMContentLoaded', function () {
    const dateParam = getParam('date');
    if (dateParam && /^\d{2}\.\d{2}\.\d{4}$/.test(dateParam)) {
        const input = document.getElementById('birthdate');
        input.value = dateParam;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        
        // Немного подождать, чтобы input успел отформатироваться и активировать кнопку
        setTimeout(() => {
            document.getElementById('calculate').click();
        }, 100); 
    }
});


//Основной код калькулятора
document.addEventListener('DOMContentLoaded', () => {
    for (const el of document.querySelectorAll("[placeholder][data-slots]")) {
        const pattern = el.getAttribute("placeholder"),
            slots = new Set(el.dataset.slots || "_"),
            prev = (j => Array.from(pattern, (c, i) => slots.has(c) ? j = i + 1 : j))(0),
            first = [...pattern].findIndex(c => slots.has(c)),
            accept = new RegExp(el.dataset.accept || "\\d", "g"),
            clean = input => {
                input = input.match(accept) || [];
                return Array.from(pattern, c =>
                    input[0] === c || slots.has(c) ? input.shift() || c : c
                );
            },
            format = () => {
                const [i, j] = [el.selectionStart, el.selectionEnd].map(pos => {
                    pos = clean(el.value.slice(0, pos)).findIndex(c => slots.has(c));
                    return pos < 0 ? prev.at(-1) : back ? prev[pos - 1] || first : pos;
                });
                el.value = clean(el.value).join("");
                el.setSelectionRange(i, j);
                back = false;
            };
        let back = false;
        el.addEventListener("keydown", (e) => back = e.key === "Backspace");
        el.addEventListener("input", format);
        // При фокусе, если дата уже введена полностью, выделяем весь текст
        el.addEventListener("focus", function() {
            if (/^\d{2}\.\d{2}\.\d{4}$/.test(this.value)) {
                this.select();
            }
            format();
        });
        el.addEventListener("blur", () => el.value === pattern && (el.value = ""));
    }
});
</script>
```

### 9.2 Main Calculator Logic, All Goal/Base Descriptions & Matrix Distribution

```html
			<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>


<script>

// Обработчик ввода в единое поле даты
$("#birthdate").on("input", function() {
    var value = $(this).val();
    // Убираем всё, кроме цифр
    var digits = value.replace(/\D/g, "");
    
    // Извлекаем части даты из строки цифр
    var day = digits.substring(0, Math.min(2, digits.length));
    var month = digits.length > 2 ? digits.substring(2, Math.min(4, digits.length)) : "";
    var year = digits.length > 4 ? digits.substring(4, Math.min(8, digits.length)) : "";
    
    // Формируем отформатированную дату: точки вставляем только после 2 цифр в дне и месяце
    var formatted = day;
    if(day.length === 2 && (month || year)) {
       formatted += ".";
    }
    if(month) {
       formatted += month;
       if(month.length === 2 && year) {
           formatted += ".";
       }
    }
    if(year) {
       formatted += year;
    }
    
    // Обновляем значение поля
    $(this).val(formatted);
    
    // Преобразуем части в числа (если достаточно цифр, иначе оставляем null)
    var dayInt = (day.length === 2) ? parseInt(day, 10) : null;
    var monthInt = (month.length === 2) ? parseInt(month, 10) : null;
    var yearInt = (year.length === 4) ? parseInt(year, 10) : null;
    
    var isValidDate = true;
    
    // Валидация дня
    if(dayInt !== null && (dayInt < 1 || dayInt > 31)) {
       isValidDate = false;
       $(this).addClass("input-error");
    } else {
       $(this).removeClass("input-error");
    }
    
    // Валидация месяца
    if(monthInt !== null && (monthInt < 1 || monthInt > 12)) {
       isValidDate = false;
       $(this).addClass("input-error");
    } else {
       $(this).removeClass("input-error");
    }
    
    // Валидация года
    if(yearInt !== null && (yearInt < 1 || yearInt > 9999)) {
       isValidDate = false;
       $(this).addClass("input-error");
    } else {
       $(this).removeClass("input-error");
    }
    
    // Подсчитываем количество заполненных частей (если хоть что-то введено)
    var filledFields = 0;
    if(day !== "") filledFields++;
    if(month !== "") filledFields++;
    if(year !== "") filledFields++;
    
    // Логика управления кнопкой "Рассчитать" и выводом сообщений
    if(!isValidDate) {
       $("#warning-message").show();
       $("#text-message").hide();
       $("#calculate").prop("disabled", true);
    } else if (filledFields === 1 && year.length === 4) {
       $("#warning-message").hide();
       $("#text-message").show();
       $("#calculate").prop("disabled", false);
    } else if (filledFields === 2) {
       $("#warning-message").hide();
       $("#text-message").show();
       $("#calculate").prop("disabled", false);
    } else if (filledFields === 3) {
       $("#text-message, #warning-message").hide();
       $("#calculate").prop("disabled", false);
    } else {
       $("#text-message, #warning-message").hide();
       $("#calculate").prop("disabled", true);
    }
});
    
// Функция для расчёта при нажатии на кнопку "Рассчитать"
$("#calculate").click(function() {
    // Получаем значение из поля
    var dateStr = $("#birthdate").val();
    
    // Проверяем, что введённое значение соответствует формату "ДД.ММ.ГГГГ" (только цифры)
    var re = /^(\d{2})\.(\d{2})\.(\d{4})$/;
    var match = re.exec(dateStr);
    
    if (!match) {
        $("#warning-message")
            .text("Пожалуйста, введите дату полностью в формате дд.мм.гггг (только цифры).")
            .show();
        return;
    }
    
    // Извлекаем и преобразуем части даты
    var day = match[1];
    var month = match[2];
    var year = match[3];
    
    // Дополнительная проверка диапазонов для дня и месяца
    if (parseInt(day, 10) < 1 || parseInt(day, 10) > 31) {
        $("#warning-message")
            .text("День должен быть от 01 до 31.")
            .show();
        return;
    }
    if (parseInt(month, 10) < 1 || parseInt(month, 10) > 12) {
        $("#warning-message")
            .text("Месяц должен быть от 01 до 12.")
            .show();
        return;
    }
    
    // Если всё корректно, скрываем сообщение об ошибке
    $("#warning-message").hide();
    
    // Скрываем или показываем нужные блоки
    $(".psychomatrix").show();
    $(".headofprewiew").hide();
    $(".bodyofprewiew").hide();
    
    // Обновляем значение поля с отформатированной датой
    $("#birthdate").val(day + "." + month + "." + year);
    
    // Выводим дату в заголовок перед матрицей
    $(".matrixhead .elementor-heading-title").html("Результаты расчёта для даты: " + day + "." + month + "." + year);
    
    document.getElementById("calcresult").scrollIntoView({behavior: "smooth"});
    
    $(".whitebottom").hide(); // Скрываем белое поле снизу
    
    // Преобразуем значения в числа для дальнейших расчётов
    day = parseInt(day, 10);
    month = parseInt(month, 10);
    year = parseInt(year, 10);
    
    if(isNaN(day)) day = 0;
    if(isNaN(month)) month = 0;
    if(isNaN(year)) year = 0;
    
    // Сумма всех цифр даты
    var firstNumber = ("" + day + month + year)
        .split('')
        .reduce(function(sum, digit) {
            return sum + parseInt(digit, 10);
        }, 0);
    
    // Второе число: если первая сумма меньше 10, то оно же, иначе – сумма его цифр
    var secondNumber = firstNumber < 10 ? firstNumber :
        ("" + firstNumber).split('').reduce(function(sum, digit) {
            return sum + parseInt(digit, 10);
        }, 0);
    
    // Третье число: вычитаем 2 * первую цифру дня (если день задан), иначе берём месяц или год
    var thirdNumber;
    var originalThirdNumber;
    if(day) {
       originalThirdNumber = firstNumber - 2 * parseInt(("" + day)[0] || 0, 10);
    } else if(month) {
       originalThirdNumber = firstNumber - 2 * parseInt(("" + month)[0] || 0, 10);
    } else {
       originalThirdNumber = firstNumber - 2 * parseInt(("" + year)[0] || 0, 10);
    }
    
    // Берём модуль результата для дальнейших расчётов
    thirdNumber = Math.abs(originalThirdNumber);
    
    // Сумма цифр третьего числа
    var fourthNumber = ("" + thirdNumber).split('').reduce(function(sum, digit) {
        return sum + parseInt(digit, 10);
    }, 0);
    
    // Формируем строку для отображения третьего числа (если отрицательное – в скобках)
    var displayThirdNumber = originalThirdNumber < 0 ? "(" + originalThirdNumber + ")" : originalThirdNumber;
    
    // Заполнение числовых значений в блоке .dop_numbers
    $(".dop_numbers").html(firstNumber + " &nbsp&nbsp;" + secondNumber + " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + displayThirdNumber + " &nbsp;&nbsp;" + fourthNumber + "<br>");
    
    // Обновление заголовков для цели и основы
    $(".GoalHead .elementor-heading-title").html("Цель: " + firstNumber + " " + secondNumber);
    $(".BaseHead .elementor-heading-title").html("Основа: " + displayThirdNumber + " " + fourthNumber);







// Выводим ссылку на результат 
const currentUrl = window.location.origin + window.location.pathname;
const formatTwoDigits = n => n.toString().padStart(2, '0');
const resultUrl = `${currentUrl}?date=${formatTwoDigits(day)}.${formatTwoDigits(month)}.${year}`;

$("#resultLink").val(resultUrl);
$("#linkblock").css("display", "flex");

// Сбросить кнопку, если уже нажимали ранее
$("#copyLinkBtn")
    .text("Скопировать")
    .css("background-color", "#57308b");

// Обработчик кнопки копирования
$("#copyLinkBtn").off("click").on("click", function () {
    navigator.clipboard.writeText($("#resultLink").val()).then(() => {
        $(this)
            .text("Скопировано ✔")
            .css("background-color", "#28a745"); // зелёный
    });
});

// Удалить старый QR, если есть
document.getElementById("qrcode").innerHTML = '';

// Сгенерировать новый QR-код
new QRCode(document.getElementById("qrcode"), {
    text: resultUrl,
    width: 80,
    height: 80
});





			
			// Заполнение цели	

		if (firstNumber == 0 && secondNumber == 0) {
			$(".goaltrakt").html("<p>Понять, в чем твой смысл жизни — это важная задача, с которой ты можешь справиться! 💪 Используй свои лучшие качества, чтобы открыть для себя новый путь.</p><p>🔺 Начни с того, чтобы найти свою уникальность. Обрати внимание, как ты взаимодействуешь с миром: что тебе интересно, что получается лучше всего?</p><p>🔺 Позволь себе исследовать! Делись своими находками с другими. Это сделает процесс еще увлекательнее.</p><p>Эти шаги помогут тебе раскрыть новые горизонты и приблизиться к пониманию своей миссии.</p>");
		}

		if (firstNumber == 1 && secondNumber == 1) {
			$(".goaltrakt").html("<p>Когда начинаешь что-то новое, обрати внимание на свои сильные стороны. Что у тебя получается лучше всего? 🔍 Разбираясь с этим и развивая свои таланты, ты укрепишь свою силу воли и самодисциплину 💪 — ключевые вещи для успеха!</p><p>Понимание своих сильных сторон поможет преодолеть сомнения и стать более решительным. Это даст тебе уверенность двигаться вперед к целям и справляться с любыми трудностями на пути 😉.</p>");
		}

		if (firstNumber == 2 && secondNumber == 2) {
			$(".goaltrakt").html("<p>Чтобы чувствовать себя энергичным и полным сил, важно правильно использовать свои внутренние ресурсы. Узнай, что у тебя есть в запасе, и развивай это! 💪</p><p>Вот несколько идей, как это сделать:</p><ul>  <li>🔵 Делись своими знаниями — взаимодействие с другими наполняет тебя новыми силами.</li> <li>🔵 Будь открыт для тех, кто нуждается в твоей поддержке — помогая другим, ты помогаешь и себе.</li></ul><p>Следуя этим простым шагам, ты сможешь сохранять высокий уровень энергии и получать удовольствие от жизни! 😊</p>");
		}

		if (firstNumber == 3 && secondNumber == 3) {
			$(".goaltrakt").html("<p>Ты на верном пути к тому, чтобы найти дело, которое по-настоящему увлекает, и сделать его полезным как для себя, так и для других. Достигнув этой цели, ты сможешь испытать глубокое удовлетворение и реализовать свой потенциал. Вот несколько советов, которые помогут тебе в этом:</p><p>✨ Подходи к своим задачам с дисциплиной и структурой. Сосредоточься на последовательных действиях, которые ведут к твоей цели, и не забывай о деталях.</p><p>✨ Избегай чрезмерного перфекционизма. Не превращай процесс в рутину — оставляй место для творчества и гибкости.</p><p>✨ Уважай свою уникальность! Каждый шаг на этом пути — это возможность узнать себя лучше.</p> <p>Начинай действовать, и пусть твое увлечение приведет к чему-то удивительному!</p>");
		}

		if (firstNumber == 4 && secondNumber == 4) {
			$(".goaltrakt").html("<p>Твой путь к здоровью — это не только забота о себе, но и шанс вдохновить других заботиться о своем физическом и ментальном состоянии. Вот как ты можешь помочь себе и другим ⤵</p><p>✔ <strong>Создай поддерживающее окружение</strong><br>Окружи себя людьми, которые мотивируют тебя расти. Это поможет укрепить твоё здоровье и даст возможность делиться полезными знаниями.</p><p>✔ <strong>Используй свои внутренние ресурсы</strong><br>Научись понимать потребности окружающих. Это откроет новые горизонты и позволит тебе стать источником вдохновения для других!</p><p>Давай вместе сделаем этот мир здоровее!🌟</p>");
		}

		if (firstNumber == 5 && secondNumber == 5) {
			$(".goaltrakt").html("<p>Когда берёшься за любое дело, даже за маленькое, попробуй взглянуть на него через призму анализа и планирования. 📝</p><p>Если сосредоточишься на этом, ты сможешь лучше управлять проектами, принимать обоснованные решения и достигать классных результатов! 🤩</p><p>⭐ <strong>Учись мыслить логически.</strong><br>⭐ <strong>Создавай стратегию для своих задач.</strong></p><p>Эти навыки помогут тебе не только в планировании, но и в понимании текущих ситуаций. Начни прямо сейчас! 💪✨</p>");
		}

		if (firstNumber == 6 && secondNumber == 6) {
			$(".goaltrakt").html("<p>Ты знаешь, что умение трудиться – это твой главный ключ к мастерству? 💪 Это качество поможет тебе справляться с любой рутиной и расти в профессии. Двигаясь в этом направлении, ты не только научишься полезным навыкам, но и получишь финансовую стабильность. 💰</p><p>Вот несколько простых советов:</p><p>🔺 <strong>Используй свои таланты на полную катушку!</strong><br>🔺 <strong>Работай над дисциплиной и настойчивостью.</strong></p><p>Шаг за шагом ты сможешь совершенствоваться и строить свою финансовую независимость. Давай сделаем это вместе! 🌟</p>");
		}

		if (firstNumber == 7 && secondNumber == 7) {
			$(".goaltrakt").html("<p>Привлечение радости и удачи в твою жизнь — это не просто желание, а настоящая цель. Для тебя это очень важно, ведь именно они помогут тебе раскрыть свой творческий потенциал. Вот несколько простых действий, которые могут помочь: </p><ul><li>⚡ Тренируй интуицию, замечай знаки судьбы.</li><li>⚡ Оценивай приятные моменты в повседневной жизни.</li><li>⚡ Работай с удовольствием — позитивный настрой открывает новые двери!</li><p>Двигайся вперед и создавай свой мир!</p>");
		}
		
		if (firstNumber == 8 && secondNumber == 8) {
			$(".goaltrakt").html("<p>Ты стремишься понять настоящую правду жизни. Важно проявлять доброту и стремиться понять других, развивая чувство справедливости. Это поможет тебе лучше осознать мир вокруг 🌏</p><p>🟠 Подходи к каждому делу добросовестно, проявляй терпение и смотри на ситуации с разных сторон. Так ты сможешь достичь гармонии и обрести ту самую правду, которую ищешь.</p><p>Помни, что правда не всегда на поверхности. Но действуя честно и заботясь о других, ты обязательно найдёшь её.</p>");
		}

		if (firstNumber == 9 && secondNumber == 9) {
			$(".goaltrakt").html("<p>Чтобы достичь своих целей и добиться значимых результатов, важно расширять знания и опыт.</p><p>🎯 <strong>Инвестируй время в самообразование.</strong><br>🎯 <strong>Изучай новые направления и совершенствуй свои навыки.</strong></p><p>Помни, обучение — это не разовая акция, а постоянный процесс. Чем больше ты знаешь, тем больше можешь поделиться с другими и помочь им на их пути к успеху. Это и есть настоящая цель!</p>");
		}

		if (firstNumber == 10 && secondNumber == 1) {
			$(".goaltrakt").html("<p>Все, что вы делаете, помогает вам проявлять свою уникальность и с ее помощью развивать силу воли, преодолевать внутренние сомнения и становиться решительным.</p><p>Начиная что-то новое, обращайте внимание на то, что получается у вас лучше всего, какие свои сильные стороны вы можете развить. Они помогут вам двигаться вперед, укрепляя самодисциплину и способность принимать волевые решения 😉</p>");
		}

		if (firstNumber == 11 && secondNumber == 2) {
			$(".goaltrakt").html("<p>Ваша сильная сторона – способность найти подход к любому человеку и решить свои задачи. Раскрыть эти качества поможет упорство и ответственность 👉 В каждом начинании старайтесь:</p><p>🚩 <strong>проявлять силу воли</strong><br> 🚩 <strong>укреплять свою решимость</strong><br>🚩 <strong>избегать гордыни и стремления к власти.</strong></p><p>Эти качества помогут вам действовать с легкостью, выстраивать гармоничные отношения и быстрее продвигаться к своей цели.</p>");
		}

		if (firstNumber == 12 && secondNumber == 3) {
			$(".goaltrakt").html("<p>Направляйте все свои действия на развитие силы воли и навыков общения. Эти качества помогут вам реализовать свои интересы и выработать самодисциплину.</p><ul><li>🚩 Занимайтесь тем, что вам действительно интересно.</li><li>🚩 Не бойтесь брать на себя ответственность начиная что-то новое.</li><li>🚩 Действуйте с легкостью, чтобы добиться успехов в реализации своих интересов и укрепить самоконтроль.</li></ul><p> Именно он создаст прочную основу для личного и профессионального роста и поможет улучшить качество вашей жизни.</p>");
		}

		if (firstNumber == 13 && secondNumber == 4) {
			$(".goaltrakt").html("<p>Начните развивать силу воли и интерес к окружающим. Эти качества помогут вам реализовать себя в обществе и найти свое место среди людей. Именно эта способность и есть ваша сильная сторона.</p><p>Научиться уверенно проявлять себя и свои таланты вам помогут:</p><p>🔸 <strong>самодисциплина и ответственность за свои поступки и решения</strong><br>🔸 <strong>умение искренне слушать и понимать других людей.</strong></p><p>Развивая эти навыки вы не только раскроете свой потенциал и выстроите гармоничные отношения с окружающими, а также обретете значимость и уважение в обществе.</p>");
		}

		if (firstNumber == 14 && secondNumber == 5) {
			$(".goaltrakt").html("<p>Чтобы достичь своих целей, важно развить два ключевых навыка — силу воли и умение понимать людей. С их помощью ты сможешь анализировать ситуацию, строить уверенные планы и принимать правильные решения.</p><p>Вот три совета, которые помогут на этом пути:</p><p>🎯 <strong>Ставь перед собой небольшие задачи и выполняй их последовательно.</strong> Проявляй решимость и настойчивость. Доводи начатое до конца — это разовьет твою силу воли.</p><p>🎯 <strong>Внимательно слушай, о чем говорят другие.</strong> Пытайся понять, что они думают и чувствуют, как принимают решения и что их мотивирует.</p><p>🎯 <strong>Принимай решения самостоятельно и не бойся брать ответственность за свои действия.</strong> Каждое новое дело — это отличная возможность попрактиковаться в этом.</p><p>Не старайся охватить все сразу. Делай маленькие шаги каждый день, и ты заметишь, как новые навыки изменят твою жизнь к лучшему.</p>");
		}

		if (firstNumber == 15 && secondNumber == 6) {
			$(".goaltrakt").html("<p>🎯 <strong>Твоя цель — создать успешную карьеру.</strong> Основу для нее формируют привычные действия, которые развивают важные навыки: планирование, анализ и силу воли.</p><p>Чтобы усилить эти навыки, всегда начинай новое дело с размышлений о том, чего хочешь достичь в будущем и что можешь сделать уже сейчас. Когда уяснишь это, принимай решения и действуй. Делай это самостоятельно, не перекладывая ответственность на окружающих.</p><p>Такой подход поможет тебе проявить себя в карьере и достичь того, чего ты действительно заслуживаешь.</p>");
		}

		if (firstNumber == 16 && secondNumber == 7) {
			$(".goaltrakt").html("<p>В любом начинании ты можешь добиться большего, чем сам ожидаешь. Вот что поможет тебе в этом:</p><p>🔺 <strong>Сила воли</strong><br>Старайся быть настойчивым в каждом деле. Выполняй задачи, которые требуют терпения, и обрати внимание на то, как ты справляешься с трудностями. Делай выводы и улучшай свои навыки.</p><p>🔺 <strong>Умение справляться с рутиной</strong><br>Не бойся монотонной работы. Найди в ней плюсы. Выбери небольшие ежедневные задачи и выполняй их с вниманием и усердием.</p><p>Развивая эти качества, ты привлечешь к себе успех и возможности.</p>");
		}

		if (firstNumber == 17 && secondNumber == 8) {
			$(".goaltrakt").html("<p>Чтобы стать более уверенным в себе, делай полезные дела и прикладывай усилия для их выполнения. Когда ты помогаешь другим, понимаешь, что вселенная работает по своим правилам, и твои добрые поступки обязательно принесут плоды.</p><p>💫 <strong>Прими решение действовать с позитивом.</strong><br>💫 <strong>Делай что-то, что приносит радость не только тебе, но и тем, кто тебя окружает.</strong></p><p>Каждый шаг, который ты делаешь ради улучшения мира вокруг, ведет к определенным результатам. Ты откроешь для себя важную истину: мир устроен так, что отдавая, мы получаем в ответ.</p><p>Следуя этому пути, ты обретешь внутреннюю гармонию и почувствуешь удовлетворение от своих действий.</p>");
		}

		if (firstNumber == 18 && secondNumber == 9) {
			$(".goaltrakt").html("<p>Каждый день старайся узнавать что-то новое и полезное 📚 Не бойся брать на себя ответственность за свои поступки и решения. Помни о своих обязанностях перед собой и окружающими.</p><p>Это поможет тебе развить терпение, научит справляться с трудностями и накопить ценный опыт, который станет в помощь тебе и твоим близким 👨👩👧👧</p><p>Когда ты делишься знаниями с другими, ты укрепляешь связи и становишься мудрым наставником. Это приносит пользу не только тебе, но и обществу в целом.</p>");
		}

		if (firstNumber == 19 && secondNumber == 10) {
			$(".goaltrakt").html("<p>Стремись развивать свою волю и учиться новому. Эти качества помогут тебе раскрыть свой истинный потенциал и стать настоящим лидером.</p><p>Когда начинаешь новое дело, прояви упорство. Это может быть настойчивость в сложных ситуациях или дисциплина на пути к цели.</p><p>Извлекай уроки из каждого опыта, даже если он кажется мелочью. Со временем это приведет тебя к лидерству, основанному на понимании себя и своего пути. Твоя сила воли и опыт станут основой для значительных успехов и уважения в обществе.</p>");
		}

		if (firstNumber == 20 && secondNumber == 2) {
			$(".goaltrakt").html("<p>Собирай свою истинную энергию и силы, они сделают твой путь к целям более уверенным и легким. Найди свой главный внутренний ресурс — то, что дает тебе силу, и работай над его раскрытием. Это увеличит твою продуктивность и подарит чувство счастья.</p><p>Чтобы максимально использовать свой потенциал:</p><p>🎯 <strong>Учись правильно взаимодействовать с окружающими.</strong> У тебя есть талант объединять людей вокруг общих идей и целей. Используй его, чтобы создать атмосферу взаимопонимания и поддержки.</p><p>🎯 <strong>Умей ясно и убедительно доносить свои мысли.</strong> Развивай навыки общения, чтобы окружающие точно понимали, что ты хочешь сказать и каков твой ожидаемый результат.</p><p>Следуя этим простым советам, ты сможешь зарядиться энергией и поддерживать высокий уровень продуктивности, что значительно улучшит твою жизнь.</p>");
		}

		if (firstNumber == 21 && secondNumber == 3) {
			$(".goaltrakt").html("<p>Найти и раскрыть свои истинные интересы легко, если ты целеустремлён и готов действовать.</p><p>💥 <strong>Ставь перед собой конкретные цели.</strong> Двигаясь к ним шаг за шагом, ты начнешь лучше понимать, что действительно важно и интересно именно для тебя. Это поможет тебе развиваться в нужном направлении.</p><p>💥 <strong>Верь в свои силы и возможности.</strong> Укрепление уверенности в себе поможет преодолевать препятствия и оставаться верным своим интересам.</p><p>Поддерживай веру в себя и регулярно действуй. Каждый шаг приближает тебя к пониманию того, что вызывает у тебя истинный энтузиазм.</p>");
		}

		if (firstNumber == 22 && secondNumber == 4) {
			$(".goaltrakt").html("<p>🎯 <strong>Твоя главная цель в жизни – познать себя и окружающих.</strong></p><p>Вот что может помочь тебе в этом:</p><p>1⃣ <strong>Активно взаимодействуй с миром.</strong></p><p>2⃣ <strong>Налаживай отношения с людьми вокруг.</strong></p><p>3⃣ <strong>Направляй свою энергию на самопознание.</strong></p><p>Общаясь и сотрудничая с другими, ты откроешь новые грани своей личности. Увидишь, как твои мысли и действия влияют на мир и людей рядом.</p><p>Стараясь понять свои внутренние качества и поддерживая других в раскрытии их потенциала, ты не только укрепишь связи, но и поймешь, что действительно важно для тебя.</p>");
		}

		if (firstNumber == 23 && secondNumber == 5) {
			$(".goaltrakt").html("<p>Развивая умение действовать и сохраняя интерес к своим увлечениям, ты постепенно учишься планировать и принимать обоснованные решения. Эти два аспекта — активные действия и увлеченность — помогают научиться думать наперед и предсказывать исход событий.</p><p>Когда начинаешь новое дело, помни:</p><p>💎 <strong>активно вовлекайся в процесс</strong><br>💎 <strong>не бойся действовать</strong><br>💎 <strong>поддерживай интерес к тому, чем занимаешься</strong></p><p>Это поможет тебе лучше анализировать ситуацию и принимать более точные решения, что приведет к большим успехам в твоих начинаниях.</p>");
		}

		if (firstNumber == 24 && secondNumber == 6) {
			$(".goaltrakt").html("<p>Выполняя привычные дела, обрати внимание на свою энергию. Учись направлять свои силы так, чтобы достигать лучших результатов и зарабатывать больше 💰.</p><p>🟡 <strong>Следи за тем, куда уходит твоя энергия.</strong> На что ты тратишь свои усилия?</p><p>Когда овладеешь этим навыком, даже самые монотонные задачи станут для тебя легкими. Это поможет тебе создать финансовую независимость и открыть новые возможности в жизни.</p>");
		}

		if (firstNumber == 25 && secondNumber == 7) {
			$(".goaltrakt").html("<p>Направляй свою энергию на планирование и анализ — это ключ к успеху в любом начинании 📄. Такой подход поможет тебе использовать информацию, которую ты получаешь от мира, и принимать более обоснованные решения 👌.</p> <p>🔮 <strong>Смотри на ситуации, информацию и действия других людей с разных углов.</strong><br>🔮 <strong>Будь внимательным, уловив то, что другие могут не заметить.</strong></p><p>Когда ты научишься интерпретировать знаки, которые посылает тебе Вселенная, они перестанут быть загадкой. Это умение откроет перед тобой новые возможности и поможет сделать правильный выбор в жизни.</p>");
		}

		if (firstNumber == 26 && secondNumber == 8) {
			$(".goaltrakt").html("<p>Трудись и направляй свою энергию на развитие. Усилия, которые ты вложишь в работу над собой, помогут тебе ощутить справедливость в жизни.</p><p>Обрати внимание, как жизнь всегда поддерживает баланс:<br>⚖ <strong>если ты стараешься развиваться и хорошо выполняешь свои задачи, справедливые результаты и щедрые награды обязательно придут.</strong></p><p>Поэтому фокусируйся на продуктивной деятельности и самосовершенствовании. Это поможет укрепить твое внутреннее чувство справедливости и создать гармонию вокруг тебя.</p>");
		}

		if (firstNumber == 27 && secondNumber == 9) {
			$(".goaltrakt").html("<p>Творческие задачи и повседневные дела, которые ты выполняешь с увлечением, помогают тебе накапливать жизненный опыт и знания. Они обогащают твою жизнь:</p><p>🌍 <strong>открывают горизонты и позволяют увидеть мир шире</strong><br>🌍 <strong>помогают находить нестандартные решения</strong><br>🌍 <strong>способствуют развитию в разных направлениях</strong></p><p>Направляй свою энергию на то, чтобы в каждое дело вплетать элементы творчества 🎨.</p><p>Подходя так к любым задачам, ты начнешь извлекать полезный опыт и новые знания из каждого действия. Эта привычка не только сделает твою работу ярче и интереснее, но и принесет множество открытий!</p>");
		}

		if (firstNumber == 28 && secondNumber == 10) {
			$(".goaltrakt").html("<p>Старайся действовать справедливо во всем, что делаешь. Это действительно развивает и укрепляет твои лидерские качества.</p><p>👉 <strong>Когда ты поступаешь честно и беспристрастно</strong>, люди начинают доверять тебе и видеть в тебе человека, способного взять на себя ответственность и вести за собой 🕺</p><p>Стремление к справедливости станет твоей привычкой. С течением времени ты заметишь, как твои лидерские качества раскрываются естественным образом. Честность не только усиливает твой авторитет, но и помогает строить крепкие отношения с окружающими.</p>");
		}

		if (firstNumber == 29 && secondNumber == 11) {
			$(".goaltrakt").html("<p>Каждое твое действие — это шанс понять, в чем твои сильные стороны и чем ты отличаешься от других. Как же использовать эти действия, чтобы получить важные ответы? ⤵</p><p>💥 <strong>Направь свои силы и энергию на получение и передачу опыта.</strong></p><p>Постоянное обучение и обмен знаниями помогут тебе стать настоящим экспертом и занять лидирующие позиции в любой области. Развивайся и делись своим опытом, и ты увидишь, как открываются новые возможности!</p>");
		}

		if (firstNumber == 30 && secondNumber == 3) {
			$(".goaltrakt").html("<p>В жизни ты ищешь занятие, которое будет тебя вдохновлять и приносить радость. Чтобы раскрыть свои истинные интересы и реализовать потенциал, обрати внимание на следующие качества:</p><p>🔸 <strong>Сообразительность</strong><br>Научись быстро находить решения и адаптироваться к неожиданным ситуациям. Это поможет тебе уверенно двигаться вперед.</p><p>🔸 <strong>Методичность</strong><br>Работай над задачами постепенно и по порядку. Так ты сможешь сосредоточиться на главном и избежать путаницы, которая мешает достигать целей.</p><p>🔸 <strong>Дисциплинированность</strong><br>Старайся регулярно выполнять даже маленькие шаги в сторону своей цели. Это создаст полезную привычку и подтолкнет тебя к успеху.</p><p>🔸 <strong>Концентрация</strong><br>Сосредоточься на том, что действительно важно для твоего роста. Избегай отвлекающих факторов, которые могут сбить с пути. Направляй свою энергию и внимание на цели, которые имеют значение.</p><p>Развивая эти качества, ты сможешь не только найти свое призвание, но и раскрыть свой потенциал в выбранной сфере.</p>");
		}

		if (firstNumber == 31 && secondNumber == 4) {
			$(".goaltrakt").html("<p>В стремлении найти свою уникальность и стать яркой личностью важно помнить, что самопознание и общение с другими людьми неразрывно связаны.</p><p>🔹 <strong>Обрати внимание на себя, но не забывай проявлять искренний интерес к окружающим.</strong></p><p>Общение с другими обогащает твое восприятие себя, открывает новые грани твоей личности и помогает находить ответы на сложные вопросы. Заботясь о себе, ты становишься более внимательным и учишься поддерживать других. 🤗</p><p>Самопознание и помощь другим людям создают мощную связь, которая усиливает твою уникальность. Когда ты разбираешься в себе, ты становишься более осознанным и значимым для окружающих.</p><p>Развивай свои личные качества и поддерживай других в раскрытии их сильных сторон. Такой подход сделает твой путь к самореализации более ярким и гармоничным. Каждое доброе дело и каждый момент поддержки — это шаг к более полноценной жизни.</p>");
		}

		if (firstNumber == 32 && secondNumber == 5) {
			$(".goaltrakt").html("<p>В течение жизни ты постоянно пытаешься реализовать свои планы и интересы. В этом тебе помогает взаимодействие с миром и окружающими. Правильная коммуникация развивает твою способность анализировать ситуацию и достигать задуманного.</p><p>Чтобы твои планы сбывались, стоит:</p><p>🎯 <strong>Правильно распределять свое время и усилия.</strong><br>🎯 <strong>Менять подход, если это необходимо.</strong><br>🎯 <strong>Строить отношения с нужными людьми.</strong><br>🎯 <strong>Быть собранным и действовать по плану, не отвлекаясь на ненужное.</strong></p><p>Умение действовать эффективно — ключ к успеху в любых начинаниях.</p>");
		}

		if (firstNumber == 33 && secondNumber == 6) {
			$(".goaltrakt").html("<p>🎯 <strong>Стремление стать настоящим профессионалом и хорошо зарабатывать — это крутая цель.</strong> Чтобы ее достичь, важно развивать глубокий интерес к своему делу и дисциплину.</p><p>🟣 <strong>Интерес</strong> будет подталкивать тебя к постоянному развитию и поможет сохранять мотивацию даже в трудные времена.<br>🟣 <strong>А дисциплина и трудолюбие</strong> позволят управлять своим временем, достигать промежуточных целей и продвигаться по карьерной лестнице.</p><p>Направляй свою энергию на развитие этих качеств. Сосредоточься на том, что действительно важно для твоего успеха!</p>");
		}

		if (firstNumber == 34 && secondNumber == 7) {
			$(".goaltrakt").html("<p>Искренний интерес к людям и бескорыстное отношение к ним открывают перед тобой двери к удаче, радости и позитивным переменам в жизни. 😉</p> <p>Чтобы быстрее привлечь счастье и получить доступ к знаниям высшего порядка, обрати внимание на эти советы ⤵</p><p>✔ <strong>Будь внимателен к окружающим и учитывай их потребности.</strong><br>✔ <strong>Заботься о своем теле и здоровье.</strong></p><p>Физическое и ментальное благополучие придаст тебе энергии и поможет достигать достойных результатов во всех твоих делах. Действуй!</p>");
		}

		if (firstNumber == 35 && secondNumber == 8) {
			$(".goaltrakt").html("<p>Стремление добиваться справедливых результатов за свои усилия – это твоя жизненная цель. Когда ты регулярно делаешь шаги к её достижению, ты открываешь для себя глубокие истины о жизни и вселенной. Вот что может помочь тебе на этом пути ⤵</p><p>👌 <strong>методичный подход к каждому делу</strong><br>👌 <strong>самодисциплина в повседневной жизни</strong><br>👌 <strong>анализ ситуации и тех, кто тебя окружает</strong><br>👌 <strong>логический подход к решению задач</strong></p><p>Эти качества откроют для тебя новые горизонты и помогут увидеть более широкий смысл всего, что происходит в твоей жизни.</p>");
		}

		if (firstNumber == 36 && secondNumber == 9) {
			$(".goaltrakt").html("<p>Стремишься получать новые знания и опыт? Это здорово! Накопив их, ты можешь щедро делиться с окружающими. Это и есть твоя глобальная цель. 🎯</p><p>Каждая задача, даже самая привычная, может научить тебя чему-то новому. Так что постарайся извлечь из нее максимум – твоя копилка знаний станет только больше! 🧳</p><p>Вот несколько простых рекомендаций, которые помогут тебе:</p><p>🔺 <strong>Подходи к каждой задаче с максимальной отдачей.</strong> Постарайся выполнить её наилучшим образом. Это позволит тебе глубже погрузиться в процесс и понять всё лучше.</p><p>🔺 <strong>Будь последовательным в своей работе.</strong> Чем больше знаний ты накопишь, тем легче будет их использовать в будущем.</p><p>Следуя этим рекомендациям, ты сможешь развивать свои навыки, делиться опытом с другими и стать экспертом в своей сфере.</p>");
		}

		if (firstNumber == 37 && secondNumber == 10) {
			$(".goaltrakt").html("<p>Глобальная цель твоей жизни — стать истинным лидером. Чтобы раскрыть свои лидерские качества и уникальность, обрати внимание на:</p><p>💎 <strong>Творческие интересы</strong> — они вдохновят тебя и помогут развиться.<br>💎 <strong>Знания высшего порядка</strong> — учись понимать подсказки от Вселенной.<br>💎 <strong>Желание делать что-то полезное для других</strong> — это придаст смысл твоим действиям.</p><p>Старайся вносить положительные изменения в мир. Такой подход укрепит твое ощущение уникальности и поможет стать лидером.</p>");
		}

		if (firstNumber == 38 && secondNumber == 11) {
			$(".goaltrakt").html("<p>Твоя цель — стать лучшим среди равных 😌. Чтобы достичь высокого уровня мастерства и получить признание в своей области, важно развивать следующие качества:</p><p>🔹 <strong>дисциплину по отношению к себе</strong><br>🔹 <strong>терпение и исполнительность</strong><br>🔹 <strong>справедливость, долг и доброту</strong></p><p>Используя эти рекомендации, ты сможешь укрепить свои позиции и стать авторитетом среди коллег и специалистов.</p>");
		}

		if (firstNumber == 39 && secondNumber == 12) {
			$(".goaltrakt").html("<p>Направь свои усилия на то, чтобы стать уверенным оратором и достичь лидерских позиций в общении. Здесь тебе поможет интерес к получению и передаче знаний.</p><p>🟡 <strong>Создавай возможности для обучения:</strong> читай книги, посещай лекции и вебинары по своей теме и тем направлениям, которые тебя увлекают.</p><p>🟡 <strong>Обрати внимание на навыки коммуникации.</strong> Умение эффективно делиться информацией сделает тебя более убедительным и успешным оратором.</p><p>Следуя этим рекомендациям, ты сможешь развить свои таланты и стать настоящим мастером общения.</p>");
		}

		if (firstNumber == 40 && secondNumber == 4) {
			$(".goaltrakt").html("<p>Истинное здоровье — это не только крепкое тело, но и способность делиться своими знаниями о том, как его сохранить. Это твоя глобальная цель в жизни! 🎯</p><p>🟡 <strong>Окружай себя поддерживающими людьми.</strong> В компании таких людей твоё здоровье укрепляется, ты достигаешь впечатляющих результатов и готов делиться ими с окружающими.</p><p>🟡 <strong>Стремись к совершенству в уходе за собой.</strong> Когда ты научишься правильно заботиться о своем теле и душе, ты заметишь, насколько улучшится твое самочувствие и жизнь в целом.</p><p><strong>Делитесь своими знаниями и опытом!</strong> Каждый из нас может внести свой вклад в создание более гармоничного общества. Когда ты помогаешь другим, ты не только обогащаешь их жизнь, но и сам становишься частью чего-то большего. Создавай вокруг себя атмосферу здоровья, как физического, так и духовного. Ведь именно такие усилия делают мир вокруг нас красивее и лучше! 🌟</p>");
		}

		if (firstNumber == 41 && secondNumber == 5) {
			$(".goaltrakt").html("<p>Каждый день ты думаешь о том, как успешно воплотить свои планы в жизнь. На этом пути тебе поможет интерес к людям и способность принимать разумные решения ⚖</p><p>🔵 <strong>Используй свою харизму, чтобы вдохновлять и объединять людей вокруг себя.</strong><br>🔵 <strong>Строй доверительные отношения с окружающими</strong> — их поддержка поможет тебе в достижении целей.<br>🔵 <strong>Прежде чем что-то сделать, тщательно обдумывай свои действия.</strong></p><p>Следуя этим принципам, ты сможешь реализовать все свои замыслы! 🌟</p>");
		}

		if (firstNumber == 42 && secondNumber == 6) {
			$(".goaltrakt").html("<p>Для тебя важно добиться успеха в карьере и проявить себя как профессионал. Твое здоровье, выносливость и умение грамотно распределять энергию играют в этом ключевую роль. Эти качества помогут тебе подниматься по карьерной лестнице и достигать финансового благополучия 💰.</p><p>Вот несколько простых советов, которые помогут укрепить твои профессиональные позиции:</p><p>🔸 <strong>Используй свое обаяние, чтобы привлекать нужных людей.</strong> Это создаст позитивную атмосферу на работе, и коллеги охотнее будут поддерживать тебя.</p><p>🔸 <strong>Направь свою энергию на рутинные задачи!</strong> Это не просто необходимость, а отличная возможность использовать свою мотивацию для себя и окружающих. Когда ты сосредотачиваешься на том, что нужно сделать, ты не только выполняешь работу, но и создаёшь позитивный пример для других.</p><p>Это поможет тебе уверенно двигаться вперёд и открывает путь к финансовому благополучию. Каждый шаг, даже самый небольшой, приближает тебя к успеху!</p>");
		}

		if (firstNumber == 43 && secondNumber == 7) {
			$(".goaltrakt").html("<p>Методичность и дисциплина — это твои лучшие друзья! Если ты сочетал их с интересом к тому, что делаешь, и к окружающему миру, удача не заставит себя ждать. Ты сможешь испытать радость и глубже понять законы Вселенной 🔮</p><p>💥 <strong>Не бойся проявить себя и стать заметным.</strong><br>💥 <strong>Искренние увлечения соединяют нас с другими</strong> — ищи свои общие интересы.<br>💥 <strong>Развивай свои хобби, будь собранным и дисциплинированным в этом.</strong></p><p>Такой подход поможет наслаждаться каждым шагом на твоем пути и тем успехом, который он принесет 😉</p>");
		}

		if (firstNumber == 44 && secondNumber == 8) {
			$(".goaltrakt").html("<p>Стремление быть среди людей помогает развивать в себе доброту, честность и справедливость. Эти качества делают тебя ценным для окружающих, и благодаря их теплому отношению ты испытываешь внутренний покой.</p><p><strong>Чтобы приблизиться к гармонии,</strong> используй свою физическую силу и выносливость, чтобы помогать другим. Это не только укрепит твои отношения с людьми, но и принесет внутренний покой и терпение.</p>");
		}

		if (firstNumber == 45 && secondNumber == 9) {
			$(".goaltrakt").html("<p>Используй свои внутренние ресурсы, чтобы реализовать свои планы. Это поможет тебе приблизиться к главной цели в жизни – накопить ценный опыт и знания, которые ты сможешь поделиться с другими.</p><p>Когда работаешь в команде или общаешься с людьми, старайся строить совместные планы. Это не только укрепит командный дух, но и даст тебе новые навыки.</p><p>Такой подход увеличит твои шансы достичь целей и сделает тебя востребованным членом любой команды.</p>");
		}

		if (firstNumber == 46 && secondNumber == 10) {
			$(".goaltrakt").html("<p>Чтобы стать настоящим лидером, которому будут доверять и прислушиваться, обрати внимание на несколько важных моментов:</p><p>🟣 <strong>Научись сопереживать.</strong> Важно не просто слышать людей, но и понимать их эмоции и потребности. Это поможет тебе завоевать доверие и расположение окружающих.</p><p>🟣 <strong>Используй свои внутренние ресурсы для профессионального роста.</strong> Не ограничивайся мечтами — действуй! Занимайся своим развитием, и ты сможешь занять свое достойное место в своей сфере.</p><p>Следуя этим простым рекомендациям, ты укрепишь свои позиции лидера, и к твоим словам начнут прислушиваться.</p>");
		}

		if (firstNumber == 47 && secondNumber == 11) {
			$(".goaltrakt").html("<p>Внутренние ресурсы, творческий подход и желание помогать другим делают тебя настоящим лидером среди единомышленников.</p><p>Чтобы раскрыть свой лидерский потенциал, обрати внимание на несколько важных моментов:</p><p>💎 <strong>Учись слушать и чувствовать свой организм.</strong><br>💎 <strong>Следи за его состоянием и самочувствием.</strong><br>💎 <strong>Ищи связи между тем, что ты делал, чем питался и о чем думал, и своими ощущениями</strong> — это поможет понять, что влияет на твое состояние.</p><p>Внимание к своему телу откроет новые горизонты!</p>");
		}

		if (firstNumber == 48 && secondNumber == 12) {
			$(".goaltrakt").html("<p>Работа с людьми помогает тебе развивать чувство долга и ответственности. Эти качества делают тебя настоящим лидером, за которым идут другие. Чтобы раскрыть свой потенциал, делай следующее:</p><p>🟣 <strong>Учись слышать себя.</strong> Понимай, в чем ты нуждаешься, чего хочешь и к чему стремишься.<br>🟣 <strong>Обращай внимание на желания окружающих.</strong> Это поможет наладить контакт.<br>🟣 <strong>Делись своей точкой зрения.</strong> Объясняй, почему ты думаешь именно так. Опирайся на свои внутренние убеждения и глубокое понимание жизни.</p><p>Эти навыки помогут тебе стать не только уважаемым лидером, но и человеком, которому доверяют и следуют.</p>");
		}

	
// Заполнение Основа

		if (displayThirdNumber == 0 && fourthNumber == 0) {
			$(".osnova").html("<p>Видеть истинную суть вещей — это твой особенный дар. Он позволяет понимать жизнь такой, какая она есть, без искажений и ложных представлений. Пользуйся этим качеством, чтобы распознавать настоящий смысл всего, что тебя окружает.</p><p>💥 <strong>Пора поразмышлять о том, в чем ты действительно силен.</strong> Что делает тебя уникальным? Это не просто способ поразмыслить о себе, а мощный инструмент для твоего роста и развития. Познавая себя лучше, ты сможешь использовать свои сильные стороны на полную катушку!</p><p><strong>Осознание своей уникальности</strong> — это ключевой момент на пути к пониманию себя и твоего места в мире. В матрице ты сможешь увидеть подсказки, которые помогут тебе раскрыть, что именно делает тебя особенным. Каждая цифра откроет новые грани твоего характера и покажет, как твои черты выделяют тебя среди других. Погрузись в это путешествие самопознания и узнай, какие уникальные качества присущи именно тебе!</p>");
		}
	
		if (displayThirdNumber == 1 && fourthNumber == 1) {
			$(".osnova").html("<p>Сила характера и воля — это качества, которые помогут тебе стать настоящим лидером. Чтобы развить эти стороны, важно осознать свою уникальность. Найди свои сильные стороны — подсказка к этому есть в твоей матрице 🔢.</p> <p>Понимание своих особенностей поможет тебе создать важный ресурс — умение брать ответственность за свои действия. Этот навык отличает настоящего лидера от остальных. Он приведет тебя к личным успехам и вдохновит других следовать за тобой.</p><p>Этот навык выделяет настоящего лидера среди остальных. Он поможет тебе достигать успехов и станет источником вдохновения для окружающих, которые захотят следовать за тобой. 💫</p>");
		}

			if (displayThirdNumber == 2 && fourthNumber == 2) {
			$(".osnova").html("<p>Твоя сильная сторона – это умение увеличивать свою энергию, когда ты направляешь ее в нужное русло. 🛤 Используй эту особенность на благо себе и другим. Вот несколько советов: ⬇</p><p>💎 <strong>Следи за своим эмоциональным состоянием.</strong><br>💎 <strong>Правильно используй свои силы и энергию.</strong><br>💎 <strong>Избегай истощения и выгорания</strong> – сохраняй внутренний баланс. ⚖</p><p>Заботясь о своем эмоциональном здоровье, ты повышаешь продуктивность, лучше справляешься с нагрузками и остаешься в гармонии с собой. Это сделает тебя более эффективным.</p>");
		}

			if (displayThirdNumber == 3 && fourthNumber == 3) {
			$(".osnova").html("<p>Качество, заложенное в тебе, — это интерес. 🧐 Начни развивать его и углублять! Вот несколько простых шагов, как это сделать:</p><p>🟣 <strong>Обращай внимание на дела, которые действительно важны и интересны для тебя.</strong><br>🟣 <strong>Сосредоточься на том, что помогает раскрыть твои таланты и способности.</strong><br>🟣 <strong>Избегай пустых разговоров, сплетен и ненужной информации.</strong><br>🟣 <strong>Проявляй дисциплину и структурированный подход к решению задач.</strong></p><p>Выбирай направления, которые помогут тебе расти и развиваться. Способности, заложенные в тебе, ждут своего часа!</p>");
		}

		if (displayThirdNumber == 4 && fourthNumber == 4) {
			$(".osnova").html("<p>Здоровье, выносливость и внутренняя сила — это потрясающие качества, которые позволяют тебе глубже чувствовать других и строить с ними настоящие отношения. Чтобы эти качества действительно работали на тебя и твоих близких, вот несколько простых советов:</p><p>💪 <strong>Используй свою физическую энергию, чтобы вдохновлять и поддерживать других.</strong><br>💪 <strong>Научись контролировать свои ощущения и эмоции.</strong> Избегай излишней самовлюбленности и ненужной агрессии.<br>💪 <strong>Открывайся окружающим и показывай свои чувства.</strong> Это поможет создать более крепкие связи.</p><p>Эти шаги помогут тебе не только расти как личности, но и делать мир вокруг лучше.</p>");
		}
	
		if (displayThirdNumber == 5 && fourthNumber == 5) {
			$(".osnova").html("<p>Твое логическое мышление и умение планировать — это настоящие суперсилы! Они помогают тебе строить долгосрочные стратегии и достигать больших целей 🎯</p><p>Чтобы раск открыть свой потенциал на максимум, просто следуй этим советам:</p><p>🟡 Применяй свои аналитические навыки для создания полезных и эффективных планов.</p><p>🟡 Старайся избегать мелких конфликтов и бытовых проблем.</p><p>Сфокусируйся на своих крупных целях и задача. Не позволяй мелочам отвлекать тебя от главного!</p>");
		}
		
		if (displayThirdNumber == 6 && fourthNumber == 6) {
			$(".osnova").html("<p>Ты обладаешь удивительной способностью вкладывать свои силы в работу, и эта работа может стать настоящим смыслом твоей жизни. Вот несколько советов, чтобы твои усилия приносили удовлетворение и радость:</p><p>🟢 Не зацикливайся только на заработке. Ищи занятия, которые действительно интересуют тебя и приносят пользу другим.</p><p>🟢 Избегай манипуляций. Они могут привести к разочарованию и разрушить твои планы.</p><p>Сосредоточься на том, как применять свои навыки для достижения долгосрочных и значимых целей. Это сделает твой путь вдохновляющим и насыщенным!</p>");
		}

		if (displayThirdNumber == 7 && fourthNumber == 7) {
			$(".osnova").html("<p>Ты всегда на шаг ближе к удаче и успеху, если понимаешь, как работают законы Вселенной 🌏</p><p>Вот несколько простых шагов, чтобы развить этот навык ⤵</p><p>🟡 <strong>Работай над раскрытием своего творческого потенциала.</strong> У каждого из нас есть уникальные таланты — найди свои!<br>🟡 <strong>Будь активным в самовыражении.</strong> Не упускай возможности, которые предоставляет жизнь, будь смелым!<br>🟡 <strong>Прислушивайся к знакам от Вселенной.</strong> Эти подсказки помогут тебе выбрать верный путь и избежать ненужных ошибок.</p><p>Доверяя своей интуиции и работая над собой, ты сможешь достичь больших высот!</p>");
		}
	
		if (displayThirdNumber == 8 && fourthNumber == 8) {
			$(".osnova").html("<p>Ты обладаешь уникальной способностью видеть истинную суть вещей. Это дар, который помогает тебе понимать мир вокруг и других людей. Чтобы раскрыть эту силу, тебе помогут три важных качества: чувство долга, ответственность и терпимость. Используй их, чтобы сделать жизнь лучше для тех, кто тебя окружает.</p><p>🟣 <strong>Помогай тем, кто нуждается в твоей поддержке.</strong> Твоя доброта и забота могут многое значить для других.<br>🟣 <strong>Не теряйся в бесконечном поиске правды или страдания.</strong></p><p>Помогая другим, ты сам познаешь, что такое настоящая правда и смысл жизни.</p>");
		}
	
		if (displayThirdNumber == 9 && fourthNumber == 9) {
			$(".osnova").html("<p>Твои врожденные знания — это настоящий клад, которым стоит пользоваться и развивать. Они помогают тебе накапливать ценный опыт, который ты можешь передать другим.</p><p>🔺 <strong>Собирай полезную информацию и открывай для себя новые горизонты.</strong><br>🔺 <strong>Превращай свои знания в практические навыки,</strong> чтобы применять их в жизни.<br>🔺 <strong>Делись своим опытом</strong> — это не только поможет другим, но и даст тебе новые идеи.</p><p>Важно помнить, что лишние эмоции, такие как обиды или зависть, только мешают твоему развитию. Очищай свой внутренний мир от негативных воспоминаний и сосредоточься на позитиве.</p>");
		}

			if (displayThirdNumber == 10 && fourthNumber == 1) {
			$(".osnova").html("<p>Твое главное качество — это умение принимать решения и нести ответственность за свои поступки. Чтобы развить его, важно осознать свою уникальность.</p><p><strong>Задумайся, в чем ты действительно лучший.</strong> Что отличает тебя от других? Это и есть твоя особенность. Когда поймешь её, научишься брать ответственность за свои действия. Это станет твоим сильным ресурсом в принятии важных решений.</p><p>Подсказка о том, как найти свою уникальность, ждет тебя в матрице 🔢.</p>");
		}

			if (displayThirdNumber == 11 && fourthNumber == 2) {
			$(".osnova").html("<p><strong>Действуй решительно и уверенно</strong> — это твой ключ к энергии и успеху. Чтобы её получить, начни брать ответственность за свои поступки. Сила воли поможет тебе двигаться вперёд, преодолевать трудности и достигать новых высот 🏃♀.</p><p>Помни, сила воли не должна превращаться в контроль. Используй её как источник вдохновения и поддержки для себя и окружающих 😌.</p>");
		}

			if (displayThirdNumber == 12 && fourthNumber == 3) {
			$(".osnova").html("<p><strong>Сила воли и умение общаться</strong> — это важные навыки, которые помогут тебе развивать самодисциплину и искренний интерес к жизни 😊.</p><p>Важно не дать этому интересу стать поверхностным. Чтобы этого не произошло:<br>🔸 развивай силу воли и желание общаться с людьми<br>🔸 не позволяй гордыне управлять тобой<br>🔸 избегай давления на других.</p><p>Эти простые шаги помогут укрепить твою самодисциплину и сохранить настоящую увлеченность жизнью, которая будет вдохновлять тебя двигаться вперед 💫.</p>");
		}

			if (displayThirdNumber == 13 && fourthNumber == 4) {
			$(".osnova").html("<p><strong>Развивай свою силу воли</strong> и ищи то, что тебе действительно интересно. Это поможет тебе проявить свою уникальность и лучше понимать окружающих.</p><p>Раскрой свои таланты в позитивном свете. Вот несколько советов:<br>🔸 Учись принимать решения самостоятельно.<br>🔸 Избегай гордыни и пустых увлечений.<br>🔸 Развивайся в том направлении, которое тебе по-настоящему интересно.</p><p>Следуя этим рекомендациям, ты сможешь наладить гармоничные отношения с окружающими и добиться личностного роста.</p>");
		}

			if (displayThirdNumber == 14 && fourthNumber == 5) {
			$(".osnova").html("<p>У тебя классно получается планировать и анализировать – это твои главные сильные стороны. Чтобы использовать эти способности на полную катушку, прислушайся к этим советам:</p><p>💥 Чаще принимай решения, даже если они кажутся незначительными. Это поможет тебе стать более уверенным и укрепит твою волю.</p><p>💥 Обращай внимание на людей вокруг: учись понимать их мотивы и поведение. Это улучшит твои отношения с теми, кто важен для тебя, и поможет принимать более точные решения в различных ситуациях.</p><p>Помни, твои способности – это твое преимущество!</p>");
		}

			if (displayThirdNumber == 15 && fourthNumber == 6) {
			$(".osnova").html("<p>Ты знаешь, что твой главный ресурс – это реализация в карьере? Успех в этой сфере может стать твоим мощным источником энергии и удовлетворения. Чтобы раскрыть свой профессиональный потенциал, стоит развивать навыки планирования и анализа.</p><p>🟢 Начни с простых шагов: составь план на день и старайся следовать ему. Когда это станет привычкой, переходи к планам на неделю или месяц. Не забывай отмечать свой прогресс и время от времени проверять его. Это поможет тебе увидеть, как ты движешься к своим целям!</p><p>🟢 Завершил проект или задачу? Отлично! Теперь удели немного времени на анализ. Подумай, что сработало, а что можно улучшить. Этот простой подход поможет тебе расти и избежать ошибок в будущем.</p><p>Планирование и анализ — это ключ к твоему профессиональному росту. Если освоишь эти навыки, сможешь эффективнее двигаться к своим целям. А это, в свою очередь, придаст тебе мотивации и энергии.</p>");
		}

			if (displayThirdNumber == 16 && fourthNumber == 7) {
			$(".osnova").html("<p>Ты обладаешь уникальной способностью превращать любой процесс в настоящее творчество. Чтобы полностью раскрыть свой потенциал, тебе нужно развивать силу воли и учиться усердно трудиться.</p><p>👉 Начни с осознанной тренировки своей воли. Ставь перед собой небольшие, но регулярные задачи, которые требуют сосредоточенности и терпения.</p><p>👉 Даже выполняя монотонные дела, старайся добавлять в них элементы творчества. Пробуй новые методы и подходы. Постепенно ты начнешь видеть любую работу под новым углом и находить в ней вдохновение.</p><p>Развивая свою силу воли и учась справляться с рутинными задачами, ты сможешь не только эффективно выполнять свои обязанности, но и подходить к ним с творческим энтузиазмом. Это поможет тебе получать удовольствие от работы и сделает тебя более счастливым человеком 😊.</p>");
		}

			if (displayThirdNumber == 17 && fourthNumber == 8) {
			$(".osnova").html("<p>🔋 Твои главные ресурсы — это доброта, терпение и умение видеть правду жизни. Именно они помогут сделать твою жизнь более счастливой и успешной. Настолько важно учиться опираться на свою силу воли и творческий подход. Эти качества превратят тебя в настоящего мастера своего дела.</p><p>⭐ Погружайся глубже в изучение того, что тебе действительно интересно.<br>⭐ Находи креативные решения даже в самых привычных ситуациях.</p><p>Когда ты соединяешь своё стремление с творчеством, мир вокруг становится понятнее, а люди — ближе.</p>");
		}

			if (displayThirdNumber == 18 && fourthNumber == 9) {
			$(".osnova").html("<p>Чтобы добиться успеха и стать мудрее, нужно развивать силу воли и терпение. Ставь перед собой небольшие, но важные задачи каждый день и старайся их выполнять, даже если это не всегда просто.</p><p>💪 Терпение и решительность помогут тебе преодолевать трудности и не останавливаться на полпути. Ты научишься использовать свои знания и жизненный опыт как главный ресурс. Увидишь, что способен справляться с любыми вызовами, и это сделает тебя успешным и счастливым.</p>");
		}

			if (displayThirdNumber == 19 && fourthNumber == 10) {
			$(".osnova").html("<p>Главное качество, которое выделит тебя среди остальных – это лидерство. Чтобы стать настоящим лидером и проявить свою уникальность, нужно:</p><ul>  <li>✔ Укреплять свою волю и целеустремленность. Эти качества помогут тебе преодолевать трудности и набираться жизненного опыта, даже если путь будет нелегким.</li> <li>✔ Стремиться к знаниям. Каждый новый навык и информация расширят твой кругозор, прибавят уверенности и приблизят к твоей цели.</li></ul><p>Соединив силу воли с жаждой знаний, ты сможешь раскрыть свой потенциал и добиться многого!</p>");
		}

			if (displayThirdNumber == 20 && fourthNumber == 2) {
			$(".osnova").html("<p>Твой источник сил и вдохновения – это настоящая энергия ⚡. Направь ее в нужное русло, чтобы двигаться к своим целям и преодолевать преграды. Вот несколько советов, как это сделать:</p><ul>  <li>⭐ Учись общаться с окружающими. Развивай умение делиться своими мыслями и идеями с другими. Это требует времени и практики, но вскоре ты сможешь легко находить поддержку и сотрудничать с окружающими в своих начинаниях.</li>  <li>⭐ Обращай внимание на свои эмоции. Эмоции – это мощный ресурс, который может как поддерживать тебя, так и тормозить. Научись понимать свои чувства и управлять ими, чтобы они работали на тебя, а не против.</li></ul><p>Сосредоточившись на этих аспектах, ты сможешь лучше использовать свою внутреннюю энергию и достигать большего!</p>");
		}

			if (displayThirdNumber == 21 && fourthNumber == 3) {
			$(".osnova").html("<p>Найди дело, которое тебя вдохновляет и вызывает настоящий интерес — именно оно станет твоим главным внутренним ресурсом. Удели ему максимум внимания и усилий, и ты сможешь достичь заметных результатов.</p><p>🔺 Сосредоточь свои силы на том, что действительно важно и интересно именно тебе.</p><p>Когда ты осознаешь, что действительно интересно, и направляешь свои усилия на важные для себя цели, появляется ощущение радости от того, что делаешь. Развиваться в этом направлении станет проще и увлекательнее. Ты почувствуешь, как каждый шаг приближает тебя к желаемому результату.</p>");
		}

			if (displayThirdNumber == 22 && fourthNumber == 4) {
			$(".osnova").html("<p>Направляй свою энергию на общение и взаимодействие с другими. Это поможет тебе лучше понять людей и заглянуть в себя, открывая новые стороны своей личности.</p><p>Ты начнешь замечать, что тебе интересно, какие качества ты ценишь в окружающих и как общение влияет на твое восприятие. Улучши свои отношения — это поможет разобраться в своих желаниях, мотивах и целях.</p><p>Время, проведенное с другими, станет для тебя источником вдохновения и самопознания, открывая перед тобой новые возможности.</p>");
		}

			if (displayThirdNumber == 23 && fourthNumber == 5) {
			$(".osnova").html("<p>Сосредоточься на том, что тебе действительно интересно. Постарайся сделать так, чтобы твои действия и увлечения были связаны между собой.</p><p>Направь свою энергию на развитие того дела, которое вызывает у тебя искренний интерес. Погружаясь в любимое занятие, ты сможешь укрепить свои навыки планирования и анализа — это станет твоим основным ресурсом.</p><p>Чем больше ты будешь развивать эти навыки, тем увереннее и успешнее двигаться к своим целям! 🎯</p>");
		}

			if (displayThirdNumber == 24 && fourthNumber == 6) {
			$(".osnova").html("<p>🧘 Энергия твоего тела — это главный ресурс, который определяет твою работоспособность. То, как ты ее используешь, влияет на то, сможешь ли ты справляться с задачами и достигать своих целей.</p><p>➖ Направляй свою энергию на выполнение важных дел 👉 Особенно тех, которые требуют сосредоточенности и терпения.</p><p>Это поможет тебе стать более профессиональным, усидчивым и успешно справляться даже с рутинными задачами. Развивая эти качества, ты сможешь поддерживать свою продуктивность и добиваться желаемого!</p>");
		}

			if (displayThirdNumber == 25 && fourthNumber == 7) {
			$(".osnova").html("<p>🔮 Умение замечать знаки, которые посылает тебе Вселенная, – это твоя особенность.</p><p>Чтобы развить этот навык, нужно научиться анализировать все, что происходит вокруг. Собирая и обрабатывая информацию, ты сможешь действовать исходя из своих наблюдений. Это поможет распознавать и понимать знаки, которые приходят к тебе сверху 💥</p><p>Так ты сможешь принимать более осознанные решения и двигаться по жизни с уверенностью, зная, что идешь правильным путем 😎</p>");
		}

			if (displayThirdNumber == 26 && fourthNumber == 8) {
			$(".osnova").html("<p>Энергия, которую ты вкладываешь в свою работу, формирует твой основной ресурс — способность понимать правду и справедливость 🧐</p><p>Чтобы развить это качество, важно научиться управлять своей энергией в повседневных задачах и работе 👉 Начни регулярно практиковать это и откроешь для себя возможность:</p><ul>  <li>➖ распознавать правду в любых ситуациях</li>  <li>➖ занимать справедливую позицию.</li></ul><p>Это станет твоим надежным ориентиром, поможет принимать взвешенные решения и строить гармоничные отношения с окружающими.</p>");
		}

			if (displayThirdNumber == 27 && fourthNumber == 9) {
			$(".osnova").html("<p>Энергия, которую ты вкладываешь в понимание знаков, которые посылает тебе Вселенная, помогает развивать важное качество — умение использовать свой опыт. Когда ты начинаешь замечать эти знаки, приходит осознание:</p><p>➡ какой урок можно извлечь из происходящего и как его применить в своей жизни 🧐</p><p>🟡 Направь свою энергию на осознанное восприятие этих сигналов. Со временем накопление знаний и опыта станет для тебя естественным процессом.</p><p>Это откроет перед тобой путь к взвешенным решениям и поможет использовать свои ресурсы максимально эффективно.</p>");
		}

			if (displayThirdNumber == 28 && fourthNumber == 10) {
			$(".osnova").html("<p>Ты обладаешь энергией, которую направляешь на добрые и честные поступки. Это помогает развивать твои лидерские качества 😎</p><p>Стремление к справедливости раскрывает твою уникальность и превращает её в ценный ресурс. Используя этот ресурс, ты сможешь открыть для себя новые возможности и уверенно двигаться к своим целям 🎯</p>");
		}

			if (displayThirdNumber == 29 && fourthNumber == 11) {
			$(".osnova").html("<p>Чтобы стать лучшим из лучших, фокусируйся на получении и передаче опыта. Обмен знаниями и постоянное обучение помогут тебе не только развить навыки, но и осознать свои сильные стороны.</p><p>Вкладывай свои силы в опыт и делись им с другими. Это даст тебе возможность:</p><ul>  <li>✔ постоянно совершенствоваться</li>  <li>✔ понять свою уникальность и развить лидерские качества</li>  <li>✔ стать признанным экспертом и достигать выдающихся результатов</li></ul>");
		}

			if (displayThirdNumber == 30 && fourthNumber == 3) {
			$(".osnova").html("<p>Важно уделять внимание тому, что действительно имеет значение для тебя. Это поможет раскрыть твой ключевой талант – интерес. Именно он подскажет, в каком направлении двигаться и как реализовать свой потенциал.</p><ul>  <li>✔ Сосредоточься на том, что важно именно для тебя.</li>  <li>✔ Прежде чем что-то сделать, задавай себе вопрос: ➡ Это приблизит меня к той жизни, о которой я мечтаю?</li>  <li>✔ Избегай пустых разговоров и лишней информации, которая отвлекает от твоих настоящих целей.</li>  <li>✔ Развивай дисциплину и следи за своими приоритетами.</li></ul><p>Эти рекомендации помогут тебе лучше понять свои истинные интересы и уверенно двигаться вперед. Разберись в своих желаниях и увлечениях, чтобы направить свою энергию в нужное русло. Начни исследовать, что действительно важно для тебя, и используй это знание, чтобы достичь своих целей. Позволь себе открыться новым возможностям и не бойся следовать за своими мечтами.</p>");
		}

			if (displayThirdNumber == 31 && fourthNumber == 4) {
			$(".osnova").html("<p>Понять, какая ты личность, и показать себя с интересной стороны поможет интерес как к себе, так и к другим.</p><ul>  <li>💎 <strong>Интерес к себе</strong> – это первый шаг к саморазвитию. Он подскажет, о чем ты мечтаешь, к чему стремишься и какие возможности у тебя уже есть. Исследуя свои желания и сильные стороны, ты сможешь направить свою жизнь в нужное тебе русло.</li>  <li>💎 <strong>Интерес к другим людям</strong>. Когда ты искренне интересуешься их мыслями, чувствами и поступками, ты открываешь для себя новые горизонты. Это поможет установить глубокие связи и понять, что движет другими.</li></ul><p>Проявляй интерес ко всем аспектам жизни — как к своим внутренним переживаниям, так и к тому, что происходит вокруг. Это поможет тебе лучше понять себя и сделает тебя более увлекательным и чутким собеседником для окружающих. Исследуя свои мысли и чувства, ты открываешь новые грани своего «Я» и глубже понимаешь мир вокруг.</p>");
		}

			if (displayThirdNumber == 32 && fourthNumber == 5) {
			$(".osnova").html("<p>Твоё главное качество — это умение строить дальновидные планы и заниматься аналитикой. 🧐</p><p>Вот несколько ключей, которые помогут тебе развить свои аналитические способности:</p><ul>  <li>🟣 интересуйся новыми знаниями и необычными идеями.</li>  <li>🟣 учись расставлять приоритеты и правильно распределять свои усилия.</li></ul><p>Помни, что для достижения успеха важно действовать методично и последовательно. Не перескакивай с одной задачи на другую. Сосредоточься на том, что важно и интересно именно тебе, и продолжай развивать свои аналитические навыки.</p>");
		}

			if (displayThirdNumber == 33 && fourthNumber == 6) {
			$(".osnova").html("<p>Твоя сильная сторона — это удивительное трудолюбие и умение легко справляться с профессиональными задачами. Чтобы развить это качество, важно искренне интересоваться тем, чем ты занимаешься. Поддерживай своё увлечение и работай над такими аспектами, как:</p><ul>  <li>🟡 дисциплина</li>  <li>🟡 методичность</li>  <li>🟡 виртуозность</li></ul><p>Это поможет тебе раскрыться в работе, творчестве и профессиональной сфере.</p><p>Не забывай заниматься не только своими интересами, но и делать что-то полезное для других. Это даст тебе больше удовлетворения и поможет стать еще успешнее.</p>");

		}
			if (displayThirdNumber == 34 && fourthNumber == 7) {
			$(".osnova").html("<p>Развивая интерес к людям и обществу, ты открываешь для себя целый мир возможностей. Это как ключ к тайнам Вселенной, который позволяет глубже понять, что происходит вокруг. 🔮</p><p>Когда ты искренне интересуешься другими, ты:</p><ul>  <li>🔺 лучше понимаешь их потребности и желания</li>  <li>🔺 начинаешь видеть мир с новой, более мудрой перспективы.</li></ul><p>Это не только делает тебя счастливее и успешнее, но и позволяет стать источником гармонии и радости для людей вокруг.</p>");
			
		}
			if (displayThirdNumber == 35 && fourthNumber == 8) {
			$(".osnova").html("<p>Твоя сильная сторона – стремление найти истину и справедливость. Ты умеешь чувствовать суть происходящего вокруг, и это качество будет развиваться, если ты научишься:</p><ul>  <li>💥 проявлять интерес к жизни во всех ее проявлениях</li>  <li>💥 последовательно подходить к решению задач</li>  <li>💥 мыслить логически.</li></ul><p>Сосредоточившись на этих навыках, ты сможешь лучше воспринимать и анализировать то, что случается. Это не только расширит твои горизонты, но и сделает тебя более уверенным в принятии решений.</p>");
		}

			if (displayThirdNumber == 36 && fourthNumber == 9) {
			$(".osnova").html("<p>Интерес к работе и правильная стратегия выполнения задач помогут тебе добиться успеха в карьере. Стремясь к росту, ты будешь накапливать опыт и знания, а став настоящим профессионалом, сможешь делиться ими с другими 👩💻.</p><p><strong>Что сделает твою работу еще более эффективной?</strong></p><ul>  <li>🟣 Методичный подход к любому делу.</li>  <li>🟣 Дисциплинированность.</li>  <li>🟣 Собранность.</li></ul><p>Эти качества позволят тебе максимально использовать свой опыт и уверенно справляться с новыми вызовами. Регулярный анализ своих действий поможет понять, что работает, а что нет, и скорректировать свой путь к успеху.</p>");
		}		

			if (displayThirdNumber == 37 && fourthNumber == 10) {
			$(".osnova").html("<p>Обладает интересом к окружающему миру и открыт ко всему новому? Это здорово, ведь именно так можно развивать свои лидерские качества. Вот несколько советов, которые помогут тебе стать настоящим лидером:</p><ul>  <li>🔵 Стремись постоянно узнавать что-то новое.</li>  <li>🔵 Ищи и реализовывай свои увлечения.</li>  <li>🔵 Будь открыт к мнению других.</li></ul><p>Важно учитывать, что думают и чувствуют окружающие. Подумай, насколько твои интересы могут быть полезны для них. Такой подход поможет тебе стать лидером, к мнению которого будут прислушиваться.</p>");
		}
	
				if (displayThirdNumber == 38 && fourthNumber == 11) {
			$(".osnova").html("<p>Ты обладаешь природными качествами, которые могут сделать тебя настоящим лидером. Интерес, чувство долга, терпение и стремление к справедливости — это твои сильные стороны, которые помогут тебе заслужить уважение и авторитет среди людей.</p><p>Чтобы развить свои лидерские качества, обрати внимание на несколько важных моментов:</p><ul>  <li>🟢 <strong>Развивай самодисциплину.</strong> Работая над собой и контролируя свои действия, ты сможешь стать более эффективным и уверенным в своих поступках.</li>  <li>🟢 <strong>Будь терпеливым.</strong> Понять и принять окружающих — значит укрепить свои лидерские способности и завоевать доверие.</li></ul><p>С каждым днем ты становишься ближе к тому, чтобы стать лидером, который вдохновляет других.</p>");
		}

				if (displayThirdNumber == 39 && fourthNumber == 12) {
			$(".osnova").html("<p>Твое главное качество – смелость в действиях 😎. Это свойственно тем, кто уверен в себе и ведет за собой других. Как стать таким человеком? Вот несколько ключевых моментов ⤵</p><ul>  <li>➖ <strong>Стремись к новым знаниям.</strong> Узнавай что-то новое каждый день.</li>  <li>➖ <strong>Глубоко погружайся в темы, которые тебе интересны.</strong> Не бойся исследовать!</li>  <li>➖ <strong>Накапливай опыт и делись им с окружающими.</strong></li></ul><p>Все это поможет тебе стать более уверенным и уважаемым человеком, а также защитит от высокомерия и гордыни. Оставайся открытым для свежих идей и конструктивной критики – это важно!</p>");
		}

				if (displayThirdNumber == 40 && fourthNumber == 4) {
			$(".osnova").html("<p>Ваше здоровье, выносливость и внутренняя сила позволяют вам чувствовать окружающих и помогать им, исцеляя их физические и душевные раны. Используйте эти качества во благо, ведь эгоистичное применение своей силы может негативно сказаться на вашем развитии.</p><ul>  <li>💥 <strong>Направляйте свои физические и внутренние ресурсы на пользу себе и другим.</strong> Старайтесь позитивно влиять на людей вокруг, помогайте им справляться с проблемами и преодолевать трудности.</li>  <li>💥 <strong>Избавьтесь от самолюбования</strong> и помните, что настоящая сила — это умение поддерживать других.</li></ul><p>Следуя этим принципам, вы сможете расти и развиваться, используя свои сильные стороны для блага как себя, так и людей вокруг. Будьте внимательны к своим ресурсам, направляйте их на помощь другим и на свой собственный рост. Это – путь к настоящей силе и гармонии с собой и окружающим миром.</p>");
		}

				if (displayThirdNumber == 41 && fourthNumber == 5) {
			$(".osnova").html("<p>Интересуясь жизнью и целями других, ты начинаешь включать их в свои планы и строить совместные амбициозные проекты. Этот подход открывает перед тобой множество возможностей, но важно действовать осознанно и учитывать желания окружающих.</p><ul>  <li>🔺 <strong>Узнай, готовы ли люди стать частью твоих планов.</strong> Это поможет избежать разочарований и неоправданных ожиданий.</li>  <li>🔺 <strong>Учись планировать свои действия с учетом мнений и целей других.</strong> Так ты сможешь построить более гармоничные и продуктивные отношения.</li></ul>");
		}

				if (displayThirdNumber == 42 && fourthNumber == 6) {
			$(".osnova").html("<p>Ты обладаешь такими качествами, как выносливость, внутренняя сила и хорошее здоровье. Эти характеристики играют важную роль в твоей жизни, ведь они помогают тебе эффективно распределять свою энергию при взаимодействии с другими. А это, в свою очередь, способствует твоему успеху в карьере 💫</p><p>Чтобы избежать эмоционального и физического истощения ⤵</p><ul>  <li>🟣 <strong>Учись грамотно распределять свою энергию.</strong> Это поможет поддерживать баланс между работой и отдыхом, а также достигать профессиональных целей без вреда для здоровья.</li>  <li>🟣 <strong>Обрати внимание на свое здоровье.</strong> Заботься о физическом и эмоциональном состоянии, чтобы использовать свою энергию с максимальной пользой и избегать усталости.</li></ul><p>Соблюдая эти советы, ты сможешь успешно справляться с рабочими задачами и при этом оставаться полным сил и энергии 💪.</p>");
		}
				if (displayThirdNumber == 43 && fourthNumber == 7) {
			$(".osnova").html("<p>Твоя уникальность заключается в понимании законов Вселенной, а также в везении и удаче 💫. Чтобы раскрыть это и воспользоваться открывающимися возможностями ⤵</p><ul>  <li>🔷 <strong>Используй свой физический потенциал.</strong></li>  <li>🔷 <strong>Проявляй интерес к людям и обществу.</strong></li>  <li>🔷 <strong>Будь методичным и структурированным даже в мелочах.</strong></li></ul><p>Сосредоточив внимание на этих советах, ты сможешь добиться успеха, лучше понимать происходящее вокруг и обрести внутреннюю гармонию 🧘.</p>");
		}

				if (displayThirdNumber == 44 && fourthNumber == 8) {
			$(".osnova").html("<p>Ты обладаешь невероятным талантом – уметь управлять своим телом и мыслями. 🧐 Это позволяет тебе не только лучше презентовать себя, но и открывает двери к новым возможностям. Ты можешь приносить пользу другим и улучшать их жизнь!</p><ul>  <li>👉 <strong>Делись своими физическими способностями с окружающими.</strong> Учись быть наставником и помогай другим освоить управление своим телом — это принесет пользу всем.</li>  <li>👉 <strong>Проявляй доброту, терпение и ответственность в процессе обучения.</strong> Таким образом, ты не только поможешь другим, но и сам станешь лучше!</li></ul>");

		}
				if (displayThirdNumber == 45 && fourthNumber == 9) {
			$(".osnova").html("<p>Ваше главное качество — это умение получать, накапливать и делиться знаниями. На протяжении жизни ты постоянно ищешь новые источники информации и опыта. Вот пара советов, которые помогут тебе в этом ⬇</p><ul>  <li>🟡 <strong>Направь свои внутренние силы и энергию на реализацию своих планов.</strong> Это не только поможет достичь целей, но и откроет новые горизонты для роста и развития.</li></ul><p>Кроме того, ты умеешь грамотно распределять ресурсы. Это приведет тебя к успеху и позволит стать настоящим экспертом в своей области.</p>");
		}

				if (displayThirdNumber == 46 && fourthNumber == 10) {
			$(".osnova").html("<p>Используя свои внутренние ресурсы, ты можешь добиться успеха в карьере и стать настоящим лидером, способным преодолевать любые преграды на пути к цели 🎯</p><p>Важно помнить, что если ты будешь направлять все свои силы только на работу, рискуешь выгореть и потерять интерес к тому, чем занимаешься. Чтобы сохранить свой потенциал, учись делегировать задачи. Настоящее лидерство — это не только умение вести за собой, но и способность грамотно распределять свои идеи и замыслы 🤔</p>");
		}

				if (displayThirdNumber == 47 && fourthNumber == 11) {
			$(".osnova").html("<p>Твоя сила и уникальность в том, что ты способен стать лучшим из лучших 😎. Ты умеешь выдерживать конкуренцию и занимать лидерские позиции благодаря следующим качествам:</p><ul>  <li>🔺 Искусству управлять своими мыслями и телом. Это делает тебя не только талантливым, но и творческим человеком. Именно поэтому ты выделяешься среди остальных и добиваешься успеха в любых условиях.</li>  <li>🔺 Желанию помогать другим. Поддерживая людей вокруг, ты укрепляешь свою связь с миром и получаешь подтверждение, что законы вселенной действительно работают. Протянув руку помощи в трудную минуту, ты создаешь настоящие связи и зарабатываешь уважение.</li></ul>");
		}
		
				if (displayThirdNumber == 48 && fourthNumber == 12) {
			$(".osnova").html("<p>Ты обладаешь уникальным даром — умеешь эффективно управлять своими мыслями и телом. Это помогает тебе раскрывать свои лидерские качества. Ты стремишься проявлять ответственность, терпение и чувство долга, и именно за это тебя уважают. Люди готовы следовать за тобой и позволяют тебе занимать лидирующие позиции.</p><p>Стать лучшим среди лучших не так сложно, как кажется. Вот несколько простых шагов, которые помогут тебе на этом пути:</p><ul>  <li>✔ Развивай самодисциплину. Даже когда лень, старайся заниматься физической активностью. Используй каждый момент, чтобы укрепить силу воли — делай маленькие шаги к своей цели.</li>  <li>✔ Будь терпеливым и усердным в работе над собой. Это поможет раскрыть твои сильные стороны и завоевать уважение окружающих. Начни с небольших изменений, и ты удивишься, как много можешь достичь!</li></ul>");
		}
		
		$(".ctamatrix").html("<p><b>Обратите внимание:</b> расчёт психоматрицы очень индивидуален и зависит от множества нюансов. В данном результате представлены краткие значения <b>дополнительных чисел</b>. </p></br><p>Чтобы получить полный психологический портрет, понять свои сильные и слабые стороны, предназначение и многое другое рекомендуем пройти полную расшифровку. Это можно сделать на <b>подробной консультации со специалистом нашей школы</b> или или присоединившись к <b>курсу «Профессионал»</b>, где освоите цифровой психоанализ с нуля до профессионального уровня.</p></br></br><div class='ctamatrix_btns'><a class='elementor-button buttononmatrix' href='#elementor-action%3Aaction%3Dpopup%3Aopen%26settings%3DeyJpZCI6Ijc4NCIsInRvZ2dsZSI6ZmFsc2V9'>Записаться на платную консультацию</a><a style='float:right' class='elementor-button buttononmatrix' href='/professional/'>Ознакомиться с курсом «Профессионал»</a><div>");

                // Разносим цифры всех расчетных чисел в таблицу
                var matrix = {
                    '1': $(".numbers_1"),
                    '2': $(".numbers_2"),
                    '3': $(".numbers_3"),
                    '4': $(".numbers_4"),
                    '5': $(".numbers_5"),
                    '6': $(".numbers_6"),
                    '7': $(".numbers_7"),
                    '8': $(".numbers_8"),
                    '9': $(".numbers_9")
                };

               
               // Функция для распределения цифр по ячейкам
                function distributeDigits(number) {
                    var digits = ("" + number).split('');
                    digits.forEach(function(digit) {
                        if (matrix[digit]) {
                            if (matrix[digit].text().length === 0) {
                                matrix[digit].text(digit);
                            } else {
                                matrix[digit].html(matrix[digit].text() + digit);
                            }
                        }
                    })
                }
                // Очищаем ячейки перед новым расчетом
                for (var key in matrix) {
                    matrix[key].text("");
                }

                // Вписываем цифры всех чисел в соответствующие ячейки
                distributeDigits(day);
                distributeDigits(month);
                distributeDigits(year);
                distributeDigits(firstNumber);
                distributeDigits(secondNumber);
                distributeDigits(thirdNumber);
                distributeDigits(fourthNumber);
                
                $.each(matrix, function(key, $element) {
  // Получаем текстовое содержимое и убираем лишние пробелы
  var currentText = $element.text().trim();
  
  if (currentText.length > 0) {
    // Если содержимое уже есть, добавляем перенос строки в конец текущего HTML-содержимого
    $element.html($element.html() + "<br>");
  } else {
    // Если содержимого нет, вставляем дефис и перенос строки
    $element.html("-<br>");
  }
});


                // Подсчет для каждой линии и вывод результата (если прочерк, то не считать)
                function countDigits(classes) {
                    var count = 0;
                    classes.forEach(function(className) {
                        var text = $("." + className).text();
                        var digits = text.match(/\d/g); // Найти все цифры
                        count += digits ? digits.length : 0; // Посчитать их количество
                    });
                    return count;
                }

                // Подсчет для каждой линии и вывод результата
                $(".goal").html(countDigits(['numbers_1', 'numbers_4', 'numbers_7']) + "<br>");      // Линия целеустремленности
                $(".family").html(countDigits(['numbers_2', 'numbers_5', 'numbers_8']) + "<br>");    // Линия семейности
                $(".habit").html(countDigits(['numbers_3', 'numbers_6', 'numbers_9']) + "<br>");     // Линия стабильности
                $(".selfrating").html(countDigits(['numbers_1', 'numbers_2', 'numbers_3']) + "<br>"); // Линия самооценки
                $(".life").html(countDigits(['numbers_4', 'numbers_5', 'numbers_6']) + "<br>");      // Линия быта
                $(".potential").html(countDigits(['numbers_7', 'numbers_8', 'numbers_9']) + "<br>"); // Линия потенциала
                $(".spiritual").html(countDigits(['numbers_1', 'numbers_5', 'numbers_9']) + "<br>"); // Линия духовности
                $(".carnals").html(countDigits(['numbers_3', 'numbers_5', 'numbers_7']) + "<br>");   // Линия плотского
            });

            // Функция сброса при нажатии на кнопку "Отменить"
            $("#reset").click(function() {
                $("#day, #month, #year").val("");  // Очищаем поля ввода
                $(".psychomatrix").hide();         // Скрываем таблицу
                $("#calculate").prop("disabled", true);  // Деактивируем кнопку расчета
                $("#warning-message").hide();  // Скрываем предупреждение
                $(".headofprewiew").show();  // Показываем заголовок превью
                $(".bodyofprewiew").show();  // Показываем само превью с видео
            });

        </script>		</div>
```

### 9.3 Share Link, QR Toggle, Image Download & Matrix Table Markup

```html
<div id="linkblock" class="result-link-block" style="display: none;">
    <span class="result-link-label">Ссылка на ваш результат:</span>
    
    <input type="text" id="resultLink" readonly class="result-link-input">
    
    <button id="copyLinkBtn" class="result-link-button">Скопировать</button>
    
<button id="qrcodeshow" class="result-link-button">QR код</button>

<div id="qrcode" class="result-link-qrcode" style="display: none;"></div>

<script>
document.getElementById('qrcodeshow').addEventListener('click', function(event) {
    event.stopPropagation();
    
    const qrCodeDiv = document.getElementById('qrcode');
    
    if (qrCodeDiv.style.display === 'none' || qrCodeDiv.style.display === '') {
        qrCodeDiv.style.display = 'block';
    } else {
        qrCodeDiv.style.display = 'none';
    }
});

document.addEventListener('click', function() {
    document.getElementById('qrcode').style.display = 'none';
});
</script>



     <button id="downloadMatrix" class="result-link-button">Скачать матрицу <i class="fa fa-download" aria-hidden="true"></i></button>
</div>



<script>
document.getElementById("downloadMatrix").addEventListener("click", function () {
    const matrix = document.querySelector(".raschetblock");

// Создаём контейнер для логотипа + подписи
const signatureWrapper = document.createElement("div");
signatureWrapper.style.display = "flex";
signatureWrapper.style.flexWrap = "wrap"; // Позволяет перенос
signatureWrapper.style.alignItems = "center";
signatureWrapper.style.justifyContent = "center";
signatureWrapper.style.gap = "12px";
signatureWrapper.style.marginTop = "0px";
signatureWrapper.style.marginBottom = "30px";
signatureWrapper.style.maxWidth = "100%";
signatureWrapper.style.boxSizing = "border-box";
signatureWrapper.style.padding = "0 10px"; // немного отступов по бокам
signatureWrapper.style.textAlign = "center";

// Логотип
const logo = document.createElement("img");
logo.src = "https://numerologaleksandrova.ru/wp-content/uploads/2023/11/logo-2-scaled-optimized.png";
logo.alt = "Логотип";
logo.style.height = "40px";
logo.style.width = "auto";
logo.style.maxWidth = "100%";

// Текст подписи
const signatureText = document.createElement("div");
signatureText.textContent = "Матрица была рассчитана на сайте numerologaleksandrova.ru";
signatureText.style.fontSize = "18px"; // чуть меньше для мобильных
signatureText.style.fontFamily = "Montserrat, sans-serif";
signatureText.style.color = "#555";
signatureText.style.wordBreak = "break-word";
signatureText.style.flex = "1 1 auto"; // чтобы не выталкивало


    // Собираем всё вместе
    signatureWrapper.appendChild(logo);
    signatureWrapper.appendChild(signatureText);
    matrix.appendChild(signatureWrapper);

    // Делаем скриншот
    html2canvas(matrix, {
        scale: 2,
        useCORS: true
    }).then(canvas => {
        // Удаляем подпись из DOM после создания изображения
        signatureWrapper.remove();

        // Создаём ссылку для скачивания
        const link = document.createElement("a");
        link.download = "psychomatrix.png";
        link.href = canvas.toDataURL("image/png");
        link.click();
    });
});
</script>

		</div>
				</div>
		<div class="elementor-element elementor-element-79e9dfc raschetblock e-flex e-con-boxed e-con e-child" data-id="79e9dfc" data-element_type="container" data-settings="{&quot;background_background&quot;:&quot;classic&quot;,&quot;content_width&quot;:&quot;boxed&quot;}">
					<div class="e-con-inner">
				<div class="elementor-element elementor-element-1eb2a7e elementor-widget__width-inherit elementor-widget elementor-widget-text-editor" data-id="1eb2a7e" data-element_type="widget" data-widget_type="text-editor.default">
				<div class="elementor-widget-container">
			<style>/*! elementor - v3.17.0 - 08-11-2023 */
.elementor-widget-text-editor.elementor-drop-cap-view-stacked .elementor-drop-cap{background-color:#69727d;color:#fff}.elementor-widget-text-editor.elementor-drop-cap-view-framed .elementor-drop-cap{color:#69727d;border:3px solid;background-color:transparent}.elementor-widget-text-editor:not(.elementor-drop-cap-view-default) .elementor-drop-cap{margin-top:8px}.elementor-widget-text-editor:not(.elementor-drop-cap-view-default) .elementor-drop-cap-letter{width:1em;height:1em}.elementor-widget-text-editor .elementor-drop-cap{float:left;text-align:center;line-height:1;font-size:50px}.elementor-widget-text-editor .elementor-drop-cap-letter{display:inline-block}</style>				<table class="raschet">
<tbody>
<tr>
<td class="dop_numbers" colspan="3" data-termin="Дополнительные числа"></td>
<td class="carnals" data-termin="Плотская Д"></td>
</tr>
<tr>
<td class="numbers_1" data-termin="Характер"></td>
<td class="numbers_4" data-termin="Здоровье"></td>
<td class="numbers_7" data-termin="Удача"></td>
<td class="goal" data-termin="Цель"></td>
</tr>
<tr>
<td class="numbers_2" data-termin="Энергия"></td>
<td class="numbers_5" data-termin="Логика"></td>
<td class="numbers_8" data-termin="Долг"></td>
<td class="family" data-termin="Семья"></td>
</tr>
<tr>
<td class="numbers_3" data-termin="Интерес"></td>
<td class="numbers_6" data-termin="Труд"></td>
<td class="numbers_9" data-termin="Память"></td>
<td class="habit" data-termin="Привычки"></td>
</tr>
<tr>
<td class="selfrating" data-termin="Самооценка"></td>
<td class="life" data-termin="Быт"></td>
<td class="potential" data-termin="Потенциал"></td>
<td class="spiritual" data-termin="Духовная Д"></td>
</tr>
</tbody>
</table>						</div>
```
