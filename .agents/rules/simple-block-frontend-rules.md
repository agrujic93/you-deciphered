---
trigger: always_on
glob: "**/*.{scss,css,js}"
description: Frontend & UI Standards for Simple Block Theme.
---

# Simple Block Theme - Frontend Rules

## 1. CSS/SCSS (ALWAYS use `rem`, $1rem=10px$)
- **Units**: `rem` ONLY via `html { font-size: 62.5%; }`. No `px`.
- **Exceptions**: `%` (fluid), `vh/vw` (viewport), `em` (relative), `aspect-ratio`, `0` (no unit).
- **Variables**: NEVER hardcode values. Use `_theme-variables.scss` (`$main-font`, `$heading-font`, `$p-font-size`, `$h1-h6-font-size`, `$content-size`, `$wide-size`). Slugs vary by project.
- **WP Palette**: NEVER hardcode Hex/RGB. Use WP custom properties (`var(--wp--preset--color--{slug})`). Slugs vary by project, check `theme.json`.
- **Layout Vars**: Reference `theme.json` widths via custom properties (e.g., `var(--wp--style--global--content-size)`). Defaults vary by project.
- **Styles Structure**: Global styles in `assets/css/sass/custom/`. Per-block: `parts/blocks/{name}/{name}.scss`. ALWAYS import theme variables (`@use '../../assets/css/sass/custom/theme-variables' as *;`).
- **Specificity**: NEVER use `!important` unless overriding deep WP/plugin core styles. Increase specificity/nesting instead.
- **Media Queries**: Formatted as `@media screen and (max-width: {size}) { ... }`.

## 2. Frameworks
- **UIKit**: Use components (`uk-modal`, `uk-accordion`), attributes (`uk-scrollspy`, `uk-sticky`), and utility classes (`uk-flex`, `uk-grid`, `uk-visible@m`) FIRST before writing custom CSS/JS.
- **UIKit Attributes**: ALWAYS use `data-uk-` prefix (e.g., `data-uk-switcher`).
- **UIKit Visuals**: Override via block SCSS or globally in `assets/css/sass/custom/_custom-variables.scss` (extreme caution — affects whole site).
- **Swiper**: ALWAYS use Swiper for sliders/carousels. NEVER use UIKit sliders. Handle: `swiper`. CSS: `swiper-style`.
- **Swiper Structure**: `.swiper > .swiper-wrapper > .swiper-slide`. Setup pagination/navigation if needed. Style overrides go in block SCSS.

## 3. Layout, Typography & Accessibility
- **Accessibility (Section 508)**:
  - `<img>`: MUST have `alt`. Fallback in ACF: `$image['alt'] ?: $image['title']`.
  - `<a>`: MUST have `aria-label` describing destination/action (must contain visible text).
  - **Nesting**: Strict HTML heading hierarchy (`h1 > h2 > h3`).
- **Layout & Alignment**: Default padding `2rem`. `alignfull` uses negative global padding margins. `.has-global-padding` required on block groups. `alignwide` handles max-width via `$wide-size`.
- **Fluidity**: ALWAYS use `theme.json` fluid scales for typography (`var(--wp--preset--font-size--{slug})`) & spacing (`var(--wp--preset--spacing--{slug})`). Avoid hardcoding these.
- **Typography Helpers**: Heading utility classes (`.h1`-.h6) use heading font, `font-weight: 500`, `line-height: 1.4`.
- **Performance / UX**: NO animations or delays for "Above the Fold" / Hero sections. Disable fade-in for the first viewport (LCP/FCP optimization).
