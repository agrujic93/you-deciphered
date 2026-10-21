# Psyhomatrix Calculator Block Plan (Aligned to Provided Script)

## Objective
Create a new block `simple-block/psyhomatrix-calculator` that reproduces the behavior from your provided script and reference page, then extends it with maintainable ACF-driven description overrides and result image download.

---

## 1) Exact script alignment requirements

Implementation must preserve the same core behavior as in your attached script:

1. **Single date input formatting**
   - Input field `#birthdate`.
   - On input: keep only digits, format as `DD.MM.YYYY` progressively.
   - Validate day/month/year ranges:
     - day: `1..31`
     - month: `1..12`
     - year: `1..9999`
   - Add/remove `input-error` class based on validity.

2. **Calculate button state logic**
   - Enable/disable `#calculate` and show/hide `#warning-message` + `#text-message` exactly as in script:
     - invalid data => warning, disable calculate
     - 1 filled part where year has 4 digits => allow calculate
     - 2 filled parts => allow calculate
     - all 3 filled => allow calculate
     - otherwise disable

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
   - Additional numbers printed in the same order as script.
   - Goal heading: `"Цель: {firstNumber} {secondNumber}"`.
   - Base heading: `"Основа: {displayThirdNumber} {fourthNumber}"`.
   - Result URL:
     - `currentUrl + ?date=DD.MM.YYYY` using zero-padded day/month.
   - Copy button:
     - reset text/color to default before new calculation
     - copy to clipboard
     - set success text/color on success
   - Recreate QR code from result URL each calculation.

6. **Goal/Base description mapping**
   - Preserve all combinations and texts from script:
     - Goal branch uses `if (firstNumber == X && secondNumber == Y)`.
     - Base branch uses `if (displayThirdNumber == X && fourthNumber == Y)`.
   - Keep CTA/fallback content where script uses it.

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

8. **Reset behavior**
   - `#reset` clears inputs, hides psychomatrix, disables calculate, hides warning, restores preview blocks.

---

## 2) Block build structure (Simple Block standard)

Create:

- `wp-content/themes/simple-block/parts/blocks/psyhomatrix-calculator/psyhomatrix-calculator.php`
- `wp-content/themes/simple-block/parts/blocks/psyhomatrix-calculator/psyhomatrix-calculator.json` (or project-equivalent block metadata file)
- `wp-content/themes/simple-block/parts/blocks/psyhomatrix-calculator/psyhomatrix-calculator.scss`
- `wp-content/themes/simple-block/parts/blocks/psyhomatrix-calculator/psyhomatrix-calculator.js`

Use namespace `simple-block/psyhomatrix-calculator` and block asset loading via block metadata.

---

## 3) ACF model for description overrides

## 3.1 Field group
Field group: `psyhomatrix_calculator_settings`

### A) UI text fields
- Titles/subtitles
- Input placeholder
- Button labels (calculate/reset/copy/download)
- Validation texts

### B) Goal description overrides (repeater)
`goal_descriptions` rows:
- `first_number` (number)
- `second_number` (number)
- `title` (text, optional)
- `content` (WYSIWYG/textarea, required)
- `is_active` (true/false)

### C) Base description overrides (repeater)
`base_descriptions` rows:
- `third_number_display` (number; signed value to match `displayThirdNumber`)
- `fourth_number` (number)
- `title` (text, optional)
- `content` (WYSIWYG/textarea, required)
- `is_active` (true/false)

## 3.2 Priority logic
1. Use ACF override when active and matching combination key.
2. Otherwise use default script-based text map.

---

## 4) Data strategy for maintainability

Refactor giant `if` chains into structured maps in JS:

- `goalMap["{firstNumber}|{secondNumber}"] = { title, html }`
- `baseMap["{displayThirdNumber}|{fourthNumber}"] = { title, html }`

Load defaults from script-migrated data and then apply ACF overrides injected from PHP into JS config.

---

## 5) URL parameter and state restore

On page load:
- read `date` query parameter
- if valid format `DD.MM.YYYY`, set input and trigger calculate flow automatically.

Share link always generated from current pathname + normalized date.

---

## 6) Download psychomatrix as image

Add new action button:
- `#downloadPsychomatrix`

Implementation:
- capture result wrapper (`#calcresult` or dedicated `.psychomatrix-capture`) via `html2canvas`
- export as PNG with meaningful filename, e.g.:
  - `psyhomatrix-DD-MM-YYYY.png`

This is an added requirement on top of source script behavior.

---

## 7) QA checklist

1. Date formatting and validation behavior matches source script.
2. Four-number formula output matches source script for test dates.
3. Goal/Base description shown for same combinations as source.
4. Matrix cell counts and line totals match source.
5. URL param autoload works and is shareable.
6. Copy link + QR generation works.
7. ACF override supersedes default text for matching combination.
8. Download button exports visible result image.
9. Reset restores initial state exactly.

---

## 8) Delivery notes

- Keep WordPress core untouched.
- Keep ACF JSON synced in theme `acf-json/`.
- Reuse existing block template patterns from this theme.
- Preserve existing UX/content intent while improving maintainability and admin control.
