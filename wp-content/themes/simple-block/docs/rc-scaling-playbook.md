# RC Scaling Migration Playbook (Simple Block)

Use this document when you want Copilot to re-apply the **runtime `--rc-unit` scaling system** in this theme.

---

## Goal

Replace the old `html { font-size: ... }` scaling approach with:

- `html { font-size: 100%; }`
- runtime scale variable `--rc-unit`
- helper functions (`rc`, `rc-var`) for SCSS
- dashboard values (WP preset vars) kept as source of truth
- optional derived `ci-*` vars for scaled typography/layout

---

## Scope

Apply only inside:

- `wp-content/themes/simple-block/theme.json`
- `wp-content/themes/simple-block/assets/css/sass/custom/_theme-variables.scss`
- `wp-content/themes/simple-block/assets/css/sass/custom/frontend-custom-style.scss`

Do **not** edit WordPress core files.

---

## Re-apply Steps

### 1) Add/keep helpers in `_theme-variables.scss`

Add SCSS helpers:

- `@function rc($value)`
	- supports `rc(10)` and `rc(100px)`
	- resolves to `calc(... * var(--rc-unit, 0.625) * 1rem)`
- `@function rc-var($custom-property)`
	- resolves to `calc(var(--wp-var) * var(--rc-unit, 0.625))`

Keep container tokens as:

- `$content-size: var(--ci-content-size, var(--wp--style--global--content-size));`
- `$wide-size: var(--ci-wide-size, var(--wp--style--global--wide-size));`

For font-size tokens, use derived vars with fallback:

- `$h2-font-size: var(--ci-font-size-heading-2, var(--wp--preset--font-size--heading-2));`

(same pattern for paragraph + heading 1..6)

### 2) Update base runtime vars in `frontend-custom-style.scss`

Ensure:

- `html { font-size: 100%; }`

In `:root`, define:

- `--rc-unit: 0.625`
- at `min-width: 1920px` -> `--rc-unit: 0.78`
- at `min-width: 2560px` -> `--rc-unit: 0.85`

### 3) Add derived vars (do NOT override WP vars directly)

In `:root` add:

- `--ci-content-size: var(--wp--style--global--content-size);`
- `--ci-wide-size: var(--wp--style--global--wide-size);`
- `--ci-font-size-paragraph: calc(var(--wp--preset--font-size--paragraph) * var(--rc-unit, 0.625));`
- `--ci-font-size-heading-1..6: calc(var(--wp--preset--font-size--heading-X) * var(--rc-unit, 0.625));`

At breakpoints, scale content/wide vars:

- `1920+`: multiply by `1.248` (=`0.78 / 0.625`)
- `2560+`: multiply by `1.36` (=`0.85 / 0.625`)

### 4) Keep `theme.json` dashboard-editable

Prefer direct values for layout in `theme.json` (e.g. `1300px`, `1500px`) so users can edit in Site Editor safely.

If parity tuning is needed, adjust derived `--ci-*` vars in SCSS, not core WP preset variable definitions.

---

## Important Rules

- Never self-reference WP vars like:
	- `--wp--preset--font-size--heading-2: calc(var(--wp--preset--font-size--heading-2) * ...)`
	- This creates a variable cycle.
- Keep WP vars as source of truth.
- Scale through `ci-*` derived aliases.

---

## Quick Prompt to Reuse Later

Use this exact request with Copilot:

> “Apply the RC Scaling Migration Playbook from `wp-content/themes/simple-block/docs/rc-scaling-playbook.md` to this repo. Keep dashboard variables as source of truth, use derived `ci-*` vars, set html to 100%, and set rc breakpoints to 0.625 / 0.78 / 0.85.”

---

## Verification Checklist

- [ ] SCSS compiles with no errors
- [ ] `html` font-size is `100%`
- [ ] `--rc-unit` present with 3 values: `0.625 / 0.78 / 0.85`
- [ ] No self-referencing `--wp--preset--*` assignments
- [ ] Container widths still respond to dashboard values
- [ ] H2/H3/etc. visually match expected baseline at ~1440px
- [ ] Typography and spacing visibly enlarge at 1920px and 2560px

