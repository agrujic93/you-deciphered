---
trigger: always_on
description: Backend & Core Standards for Simple Block Theme.
---

# Simple Block Theme - Backend Rules

## 1. ACF & Blocks Structure
- **ACF JSON**: NEVER create field groups in WP manual alone. SYNC all groups to `acf-json/` (lives in `/uploads/acf-json/`, copied to theme).
- **Structure**: Uniform in `parts/blocks/{name}/` -> `{name}.php`, `{name}.json`, `{name}.scss`, `{name}.js`, `/images/`.
- **Naming**: Ensure `{name}` is consistent across ALL files and registration.
- **Namespace**: `simple-block/{name}` (e.g., `simple-block/hero-block`).
- **ACF Checks**: ALWAYS check `if(get_field())` before outputting or defining variables. Update Repeater "Add Row" labels to be descriptive.
- **Image Handling**:
  - ACF Recommended Return Format: **Image Array**. Specify sizes (e.g., `['sizes']['large']`).
  - Fallback `alt`: `$image['alt'] ?: $image['title']`.
  - ID-based: Fetch $image_alt via `_wp_attachment_image_alt` metadata (`$image_alt = get_post_meta($id, '_wp_attachment_image_alt', TRUE);`).

## 2. PHP Functions & Assets
- **Prefix**: Functions: `simple_block_`, Helpers: `ci_`, Asset/Script Helpers: `cwp_`.
- **Hooks**: Keep hooks near function declarations. Use guards: `if ( ! function_exists( '...' ) )`. `add_action/filter` outside function logic.
- **Menus**: DO NOT use `register_nav_menus()` (this is a block theme). Menus are managed in the Site Editor.
- **Registration**: Register block scripts/styles globally in `init` using `cwp_register_block_script()`.
- **Enqueuing**: Enqueue via block.json (`viewScript` / `viewStyle`). WP will only load it when used. Global assets via `enqueue_block_assets`.
- **Backend UI**: Enqueue editor styles via `enqueue_block_editor_assets`.
- **Dependencies**: Declare `'swiper'` as dependency for slider scripts. Swiper assets (JS/CSS handles: `swiper`, `swiper-style`) are pre-registered globally.
- **Versioning**: ALWAYS version assets using file modification time with a fallback string like `file_exists( $path ) ? filemtime( $path ) : '1.0.0'`.
- **Custom Image Sizes**: NEVER rely on WP defaults. Handles (`blog-thumb`, `half-content`, `full-hero-size`...) differ per project. Verify in `functions.php`.

## 3. Build Process, Deployment & Performance
- **Gulp Task Runner**: Run `gulp watch` from root. NEVER manually compile CSS inline or edit output. NEVER commit `node_modules/`. Include `INCLUDES` architecture for PHP logic.
- **Admin Users**: Developer user MUST be **`codeadmin`** (standard company password).
- **Deployment Checklist**: Remove `node_modules/`, disable Creative Block Theme plugin, set block preview images, remove unused blocks/ACF groups, conceal header/footer in Dashboard, set theme screenshot, remove Google Analytics logic.
- **Optimization Strategy**: Convert images to `.webp`. Remove all commented-out/redundant code and fix console errors.
- **LiteSpeed Cache**: Required installation for Image Opt, Minification, Combine, Browser Cache, Crawler, and WebP replacement.
- **Database**: Use the **Index WP MySQL For Speed** plugin to optimize DB queries.