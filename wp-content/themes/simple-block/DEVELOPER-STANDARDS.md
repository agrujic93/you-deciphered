# Simple Block Theme — Developer Standards & Rules

> **For AI assistants**: Read this file before making any changes to this theme. All code suggestions must comply with every rule defined here.

---

## 1. CSS / SCSS Units

- **Always use `rem` instead of `px`** for all spacing, sizing, fonts, borders, and layout values.
- The base font size is set to `62.5%` on `html`, so **`1rem = 10px`**.
- This scales cleanly: `1.6rem = 16px`, `2rem = 20px`, `10rem = 100px`, etc.
- Exceptions (acceptable use of other units):
  - `%` for fluid/relative widths
  - `vh` / `vw` for viewport-relative sizing
  - `em` only when intentionally relative to the parent font size
  - `aspect-ratio` values (e.g., `16 / 9`)
  - Integer `0` (no unit needed)

**Wrong:**
```scss
padding: 20px;
font-size: 16px;
margin-bottom: 30px;
```

**Correct:**
```scss
padding: 2rem;
font-size: 1.6rem;
margin-bottom: 3rem;
```

---

## 2. SCSS Variables — Always Use Theme Variables

- Never hardcode color values — always use SCSS variables from `_theme-variables.scss`.
- Never hardcode font families — always use `$main-font` or `$heading-font`.
- Never hardcode font sizes — always use the predefined `$p-font-size`, `$h1-font-size`…`$h6-font-size` variables.
- Never hardcode container widths — always use `$content-size` or `$wide-size`.

### Available Variables (`_theme-variables.scss`):

```scss
/* Colors */
$base-color, $base-2-color
$contrast-color, $contrast-2-color, $contrast-3-color
$accent-color, $accent-2-color, $accent-3-color, $accent-4-color, $accent-5-color

/* Font Sizes */
$p-font-size
$h1-font-size … $h6-font-size

/* Font Families */
$main-font        // body text
$heading-font     // headings

/* Layout */
$content-size     // default container width
$wide-size        // wide container width
$g-padding-left   // global left padding
$g-padding-right  // global right padding
```

---

## 3. WordPress Color Palette — CSS Custom Properties

- Colors are defined in `Appearance > Editor > Styles > Colors > Palette`.
- Always refer to colors using WP CSS custom properties — **do not hardcode hex or RGB values**.
- Palette structure:
  - `--wp--preset--color--base` / `--wp--preset--color--base-2`
  - `--wp--preset--color--contrast` / `--wp--preset--color--contrast-2` / `--wp--preset--color--contrast-3`
  - `--wp--preset--color--accent` … `--wp--preset--color--accent-5`

> **⚠️ Project-specific:** The exact color slugs and palette structure may differ per project. Always check `theme.json` under `settings.color.palette` to confirm which slugs are defined before using them.

### Semantic Mappings:
- **Headings (h1–h6):** `var(--wp--preset--color--accent)`
- **Body text:** `var(--wp--preset--color--contrast)`
- **Background:** `var(--wp--preset--color--base)`

### CSS Class Pattern:
`.has-{slug}-color` (e.g., `.has-accent-3-color`)

---

## 4. ACF Field Groups — JSON Files

- **Never create ACF field groups manually through the WP Dashboard alone.**
- ACF field group definitions must be saved as **JSON files** in the `acf-json` folder.
- The `acf-json` folder lives in `/uploads/acf-json` and is **copied to the theme** during setup.
- After any change to a field group in the WP Dashboard, the corresponding `.json` file in `acf-json` must be synced/updated.
- When duplicating a block: delete the auto-generated `copy.json`, rename the Field Group, then save — WP will generate the correct `.json` file.

---

## 5. SCSS File Structure

```
assets/css/sass/custom/
├── _theme-variables.scss     ← All global variables (colors, fonts, sizes)
└── frontend-custom-style.scss ← Global styles, helpers, section paddings
```

- All custom block styles live in their own block folder:
  `parts/blocks/{block-name}/{block-name}.scss`
- Every block SCSS file must import theme variables:
  ```scss
  @use '../../assets/css/sass/custom/theme-variables' as *;
  ```

---

## 6. Build Process

- The theme uses **Gulp** as the task runner.
- Run `gulp watch` from the theme root during development.
- Never commit `node_modules/` or `package-lock.json` to the repository.
- Compiled CSS is output by Gulp — **never manually edit compiled CSS files**.

---

## 7. UIkit Framework

- This theme includes **UIkit** — always use UIkit components, utilities, and JavaScript before writing custom CSS or JS.
- Avoid adding extra libraries or writing custom code for things UIkit already handles (modals, accordions, dropdowns, animations, grid, tooltips, etc.).
- Use UIkit **data attributes** for JS behavior (e.g., `uk-modal`, `uk-accordion`, `uk-scrollspy`) instead of custom JavaScript.
- Use UIkit **utility classes** for spacing, flex, grid, visibility, and animation where possible instead of writing custom SCSS.
- If a UIkit component needs visual adjustments, override it via SCSS in the block's own `.scss` file — never modify UIkit source files.
- **Exception — Sliders & Carousels:** Always use **Swiper** instead of UIkit slider/slideshow (see Section 8).
- **Global UIkit variable overrides:** If you need to change a UIkit variable globally (e.g., default border radius, font, color), do it in:
  `assets/css/sass/custom/_custom-variables.scss`

  > **⚠️ Caution:** Changes in this file affect the **entire website**. Be very careful — even a small change here can impact every UIkit component across all pages. Always test thoroughly after editing this file.


**Examples of UIkit usage over custom code:**

| Need | Use |
|---|---|
| Slider / Carousel | **Swiper** (not UIkit — see Section 8) |
| Modal / Lightbox | `uk-modal` or `uk-lightbox` |
| Accordion | `uk-accordion` |
| Dropdown | `uk-dropdown` |
| Scroll animation | `uk-scrollspy` |
| Sticky element | `uk-sticky` |
| Flex/Grid layout | `uk-grid`, `uk-flex` |
| Visibility helpers | `uk-visible@m`, `uk-hidden@s` |

---

## 8. Swiper — Sliders & Carousels

- **Always use [Swiper](https://swiperjs.com/)** for any slider or carousel implementation.
- Do **not** use UIkit `uk-slider` or `uk-slideshow` — Swiper is the preferred choice for this theme.
- Swiper provides better control, touch support, loop mode, and customisation than UIkit's slider.
- Swiper is already used across existing blocks — follow the same pattern as existing implementations.

**Basic usage pattern:**
```html
<div class="swiper">
    <div class="swiper-wrapper">
        <div class="swiper-slide">...</div>
        <div class="swiper-slide">...</div>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
</div>
```

```js
const swiper = new Swiper('.swiper', {
    loop: true,
    slidesPerView: 1,
    pagination: { el: '.swiper-pagination', clickable: true },
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
});
```

- Register the block JS in `functions.php` and reference it in the block `.json` under `"viewScript"`.
- Style Swiper overrides in the block's own `.scss` file — never edit Swiper source files.

---

## 9. Block Structure

Custom ACF blocks follow this folder structure inside `parts/blocks/`:


```
parts/blocks/{block-name}/
├── {block-name}.php      ← Block template (PHP)
├── {block-name}.json     ← Block registration file
├── {block-name}.scss     ← Block-specific SCSS
├── {block-name}.js       ← Block-specific JS (if needed)
└── images/               ← Block preview image and assets
```

- The block `name` must be consistent across **all** files (PHP, JSON, SCSS, JS).
- If a block uses a custom script, register it in `functions.php` and reference the handle in the block `.json` under `"viewScript"`.

---

## 9. Layout & Alignment Rules

- **Default horizontal padding:** `2rem`
- **`alignfull`:** Uses negative left/right margins equal to global padding (`$g-padding-left` / `$g-padding-right`).
- **`alignwide`:** Uses `$wide-size` as max-width.
- **`none` / default:** Uses `$content-size` as max-width.
- Default block groups include the class `.has-global-padding` — do not remove this.

---

## 10. Typography

- Font sizes must be **fluid** for paragraphs and headings (set in `theme.json` via Site Editor).
- Font families are installed through the Site Editor and referenced via WP preset variables.
- Heading utility classes (`.h1`…`.h6`) use `$heading-font`, `font-weight: 500`, and `line-height: 1.4`.

---

## 11. Avoid `!important`

- **Do not use `!important`** in SCSS or CSS.
- Instead, increase specificity by nesting more precisely or using a more specific selector.
- `!important` makes styles hard to override, debug, and maintain.

**Wrong:**
```scss
color: #fff !important;
margin-bottom: 0 !important;
```

**Correct:**
```scss
// Increase specificity instead
.parent-selector .child-element {
    color: #fff;
    margin-bottom: 0;
}
```

- **Exception:** `!important` is acceptable **only** when overriding deeply nested WordPress core or plugin styles that cannot be targeted any other way. If used, add a comment explaining why.

```scss
// Required: WP block editor adds inline style that cannot be targeted otherwise
.wp-block-group { margin-top: 0 !important; }
```

---

## 12. PHP Functions — Naming & Structure

- **All custom functions must use a prefix** to avoid conflicts with plugins or WP core.
  - Theme-level functions: `simple_block_`
  - Asset/UIkit helpers: `ci_`
  - Block/CI helpers: `ci_`
  - Script registration helpers: `cwp_`
- Always wrap functions in `if ( ! function_exists( '...' ) )` guards where applicable.
- Use `add_action` / `add_filter` outside of function declarations, at the bottom of each block.
- Follow the pattern: define function → hook it. Keep hooks close to their functions.

---

## 13. Script & Style Registration

- **Block-specific scripts and styles are registered globally, then enqueued by the block itself** — do not globally enqueue block scripts.
  - Register scripts in `cwp_register_block_script()` hooked to `init`
  - Reference the handle in the block's `block.json` under `"viewScript"` or `"viewStyle"` — WP will enqueue it only when the block is present on the page
- **Global assets** (UIkit, main.js, frontend CSS) are enqueued via `enqueue_block_assets` or `init`.
- **Always version assets using `filemtime()`** with a fallback string:
  ```php
  file_exists( $path ) ? filemtime( $path ) : '1.0.0'
  ```
  This ensures browsers always load the latest file after changes.
- **Swiper assets are pre-registered** with handles `swiper` and `swiper-style`.
  - Swiper files are located in `assets/swiper/`
  - Declare `'swiper'` as a dependency in your block's script registration — do not re-register it.
  ```php
  wp_register_script( 'my-slider-js', $url, array( 'swiper', 'acf' ), filemtime( $path ) );
  ```
- **Backend styles** (editor only) are enqueued via `enqueue_block_editor_assets`.

---

## 14. Custom Image Sizes

Three custom image sizes are registered in the theme. Always use these when outputting images — do not rely on WP defaults:

> **⚠️ Project-specific:** These handles and dimensions may differ per project. Always check `functions.php` for the current `add_image_size()` definitions before using them.

| Handle | Width | Height | Hard Crop |
|---|---|---|---|
| `blog-thumb` | 410px | 410px | Yes |
| `half-content` | 860px | 860px | No |
| `full-hero-size` | 1920px | — | No |

Use these in PHP via `get_the_post_thumbnail( $id, 'blog-thumb' )` or in ACF image fields by specifying the size.

---

## 15. theme.json — Layout, Spacing & Typography Reference

Do not hardcode these values — always reference them through WP CSS custom properties or `theme.json` slugs.

> **⚠️ All values in this section are project-specific.** The tables below reflect the current project's `theme.json`. Colors, sizes, fonts, and spacing **will differ from project to project**. Always open `theme.json` and verify the actual slugs and values before using them.

### Layout Sizes
> Check `settings.layout` in `theme.json` — values differ per project.

| Setting | Value | CSS Variable |
|---|---|---|
| Content width | `130rem` *(this project)* | `var(--wp--style--global--content-size)` |
| Wide width | `150rem` *(this project)* | `var(--wp--style--global--wide-size)` |

### Spacing Scale
> Check `settings.spacing.spacingSizes` in `theme.json` — slugs and values differ per project.

| Slug | Size |
|---|---|
| `10` | `1rem` |
| `20` | `min(1.5rem, 2vw)` |
| `30` | `min(2.5rem, 3vw)` |
| `40` | `min(4rem, 5vw)` |
| `50` | `min(6.5rem, 8vw)` |
| `60` | `min(10.5rem, 13vw)` |

Use as: `var(--wp--preset--spacing--40)`

### Font Family Slugs
> Check `settings.typography.fontFamilies` in `theme.json` — fonts and slugs differ per project.

| Role | Slug | CSS Variable |
|---|---|---|
| Heading font | `cal-sans` *(this project)* | `var(--wp--preset--font-family--cal-sans)` |
| Body font | `mona-sans` *(this project)* | `var(--wp--preset--font-family--mona-sans)` |

### Font Size Slugs
> Check `settings.typography.fontSizes` in `theme.json` — sizes and fluid ranges differ per project.

| Name | Slug | Size |
|---|---|---|
| Paragraph | `paragraph` | fluid `1.6–1.7rem` |
| Heading 1 | `heading-1` | fluid `3–5rem` |
| Heading 2 | `heading-2` | fluid `2.4–3.4rem` |
| Heading 3 | `heading-3` | fluid `2.2–2.6rem` |
| Heading 4 | `heading-4` | fluid `2–2.4rem` |
| Heading 5 | `heading-5` | fluid `1.9–2.2rem` |
| Heading 6 | `heading-6` | fluid `1.8–2rem` |

Use as: `var(--wp--preset--font-size--heading-2)`

### Block Namespace
All custom blocks are registered under the `simple-block/` namespace. Example: `simple-block/hero-block`.

---

## 16. Deployment Checklist (Do Not Skip)

- [ ] Remove `node_modules/` before deploying
- [ ] Deactivate the **Create Block Theme** plugin
- [ ] Set preview images for all custom blocks
- [ ] Remove unused custom blocks and ACF field groups
- [ ] Hide header and footer blocks in WP Dashboard
- [ ] Review / remove `add_google_analytics` in `functions.php`
- [ ] Set the theme screenshot/preview image

---

## 17. Adding New Rules

When new standards are agreed upon, add them here as new numbered sections. This file is the single source of truth for development conventions on this theme.
