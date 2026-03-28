---
applyTo: "wp-content/themes/**/*.{scss,css,js}"
description: "Use when editing Simple Block theme frontend code: SCSS/CSS/JS, UIKit, Swiper, typography, spacing, and accessibility standards."
---

# Simple Block Theme - Frontend Rules

## CSS/SCSS
- ALWAYS use `rem` with `html { font-size: 62.5%; }` ($1rem = 10px$).
- NEVER use `px` except valid unit exceptions (`%`, `vh/vw`, `em`, `aspect-ratio`, `0`).
- NEVER hardcode colors, fonts, font sizes, or container widths.
- ALWAYS use `_theme-variables.scss` tokens and WP CSS vars from `theme.json`.
- ALWAYS re-check project slugs in `theme.json` (colors, spacing, typography are project-specific).
- ALWAYS keep global styles in `assets/css/sass/custom/` and block styles in `parts/blocks/{name}/{name}.scss`.
- ALWAYS import theme vars in block SCSS (`@use '../../assets/css/sass/custom/theme-variables' as *;`).
- NEVER use `!important` unless overriding deep WP/plugin styles; if used, add a short reason comment.
- ALWAYS format media queries as `@media screen and (max-width: {size}) { ... }`.

## Frameworks
- ALWAYS prefer UIKit components/attributes/utilities before custom CSS/JS.
- ALWAYS use `data-uk-` attribute prefix.
- NEVER modify UIKit source files; override in block SCSS or global custom variables (with caution).
- ALWAYS use Swiper for sliders/carousels.
- NEVER use UIKit slider/slideshow.
- ALWAYS use Swiper structure `.swiper > .swiper-wrapper > .swiper-slide` and style overrides in block SCSS.

## Layout, Accessibility, Performance
- ALWAYS include `alt` for images; ACF fallback: `$image['alt'] ?: $image['title']`.
- ALWAYS include meaningful `aria-label` on links.
- ALWAYS keep strict heading order (`h1 > h2 > h3 ...`).
- ALWAYS follow project layout rules: default padding, `.has-global-padding`, alignwide/alignfull behavior.
- ALWAYS use fluid typography/spacing tokens from `theme.json`.
- NEVER animate or delay above-the-fold hero content.
