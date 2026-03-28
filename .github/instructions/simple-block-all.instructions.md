---
applyTo: "wp-content/themes/**/*.{php,json,scss,css,js}"
description: "Use when doing full-stack Simple Block theme work and you want one combined backend plus frontend rule set in a single file."
---

# Simple Block Theme - Combined Rules

This is a WordPress block theme. Use block-theme patterns and Site Editor workflows.

## Backend (PHP/JSON)
- ALWAYS keep ACF groups synced to `acf-json/`; NEVER rely on dashboard-only ACF edits.
- ALWAYS keep block structure/naming consistent in `parts/blocks/{name}/` and namespace `simple-block/{name}`.
- ALWAYS check field existence before output (`if ( get_field() )`).
- ALWAYS use image array output with size selection and alt fallback.
- ALWAYS prefix helpers/functions with `simple_block_`, `ci_`, `cwp_`.
- NEVER use `register_nav_menus()` in this block theme.
- ALWAYS register block assets in `init` and attach via `block.json` (`viewScript`/`viewStyle`).
- ALWAYS declare `swiper` dependency for sliders.
- ALWAYS version assets with `filemtime` fallback.
- NEVER assume default image sizes; verify project handles in `functions.php`.
- ALWAYS use `gulp watch`; NEVER edit compiled CSS; NEVER commit `node_modules/`.

## Frontend (SCSS/CSS/JS)
- ALWAYS use `rem` with `html { font-size: 62.5%; }`; avoid `px` except valid exceptions.
- NEVER hardcode theme colors/fonts/sizes; use SCSS variables and `theme.json` WP preset vars.
- ALWAYS re-check project-specific slugs in `theme.json`.
- ALWAYS prefer UIKit first; ALWAYS use `data-uk-` attributes.
- ALWAYS use Swiper for sliders; NEVER use UIKit slider/slideshow.
- NEVER use `!important` unless unavoidable for deep core/plugin overrides, and then add reason comment.
- ALWAYS enforce accessibility basics: image `alt`, meaningful link `aria-label`, proper heading order.
- NEVER animate/delay above-the-fold hero content.

## WordPress Core Safety
- Treat core as read-only.
- NEVER edit `wp-admin/**` or `wp-includes/**` unless explicitly requested.
- NEVER edit root core files (`wp-*.php`, `xmlrpc.php`, `index.php`, `license.txt`) unless explicitly requested.
- Prefer hooks/plugins/theme extensions over core patches.
- If core edit appears required, ask for confirmation first and note update overwrite risk.
