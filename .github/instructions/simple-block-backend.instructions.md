---
applyTo: "wp-content/themes/**/*.{php,json}"
description: "Use when editing Simple Block theme backend PHP/JSON: ACF blocks, registration, enqueueing, helpers, and performance/deployment backend standards."
---

# Simple Block Theme - Backend Rules

This is a WordPress block theme. Use block-theme/Site Editor patterns.

## ACF and Blocks
- ALWAYS keep ACF field groups synced to `acf-json/` (source is `/uploads/acf-json`, copied to theme).
- NEVER rely on dashboard-only ACF changes.
- ALWAYS keep block naming consistent in `parts/blocks/{name}/` files: `{name}.php|json|scss|js`.
- ALWAYS use namespace `simple-block/{name}`.
- ALWAYS check field existence before output/variable assignment (`if ( get_field() )`).
- ALWAYS set descriptive repeater row labels.
- ALWAYS use image array return format when possible and size-specific output.
- ALWAYS use alt fallback: `$image['alt'] ?: $image['title']`.
- IF image field returns ID, fetch alt from `_wp_attachment_image_alt`.

## PHP and Assets
- ALWAYS prefix functions/helpers: `simple_block_`, `ci_`, `cwp_`.
- ALWAYS keep hooks near declarations and use guards `if ( ! function_exists( '...' ) )`.
- NEVER use `register_nav_menus()` in this block theme.
- ALWAYS register block scripts/styles in `init` via `cwp_register_block_script()`.
- ALWAYS load block assets through `block.json` (`viewScript`/`viewStyle`) so they load only when block is present.
- ALWAYS enqueue global assets via `enqueue_block_assets`.
- ALWAYS enqueue editor-only assets via `enqueue_block_editor_assets`.
- ALWAYS declare `swiper` dependency for slider scripts; use pre-registered handles `swiper` and `swiper-style`.
- ALWAYS version assets with `filemtime` fallback: `file_exists( $path ) ? filemtime( $path ) : '1.0.0'`.
- NEVER assume WP default image sizes; verify project `add_image_size()` handles in `functions.php`.

## Build, Deploy, Performance
- ALWAYS run `gulp watch` from theme root.
- NEVER manually edit compiled CSS.
- NEVER commit `node_modules/`.
- Follow project lockfile policy (`package-lock.json` excluded by this standard).
- Keep backend logic in `INCLUDES` architecture.
- Deployment checks: remove unused blocks/ACF groups, set block previews and theme screenshot, disable Create Block Theme plugin, remove unused analytics logic.
- Performance checks: prefer `.webp`, remove dead/commented code, fix console errors.
- Recommended ops tools: LiteSpeed Cache and Index WP MySQL For Speed.
