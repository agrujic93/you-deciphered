# Developer Guide: Simple Block Theme

Hello Steve! This guide is designed for developers like you who are taking over or collaborating on the **Simple Block** theme. This theme is built as a **WordPress Block Theme** (based on Twenty Twenty-Four) but enhanced with **ACF Pro** for custom block development and **UIkit** for front-end components.

---

## 1. Block Generator (New!)
We've added a **Block Generator** to the WordPress dashboard to save you time.

1. Go to **Appearance > Block Generator**.
2. **Generate**: Enter a title and click "Generate" to create all files and register scripts.
3. **Delete**: Select an existing block from the dropdown and click "Delete". This safely removes the folder, the ACF JSON, and the script registration from `functions.php`.

---

## 2. How to Add Google Analytics
To update or add Google Analytics (GA4), you don't need a plugin. The code is handled directly in `functions.php`.

1. Open `functions.php` in the theme root.
2. Locate the function `add_google_analytics()`.
3. Replace the placeholder tag (e.g., `G-MQ7BGNLNX4`) with your own GA4 measurement ID.
4. Uncomment the script tags if they are commented out.
5. Save the file.

---

## 3. Main HTML Templates & Blocks
As a Block Theme, the main page structures are **HTML files** rather than PHP. You can find them in two locations:

*   **`templates/` folder**: Contains high-level structures like `page.html`, `single.html`, `archive-product.html` (equivalent to `archive.php`), and `index.html`.
*   **`parts/` folder**: Contains reusable parts like `header.html` and `footer.html`.

**Note for Steve**: These HTML files are almost empty — they simply "call" a specific block.
- The **Header** and **Footer** use custom **ACF blocks**.
- If you want to change the header or footer layout/logic, **do not edit the HTML files**. Instead, edit the custom blocks found in `parts/blocks/header-block/` or `parts/blocks/footer-block/`.

---

## 4. Theme Structure & Custom PHP Templates
This theme uses a hybrid approach.

### Root Folder & Custom PHP Templates
*   **Creating Templates**: You can easily create a new custom page template by duplicating `template-contact.php`.
*   **Naming & Overriding**:
    - **By Header**: Add a comment at the top: `/* Template Name: My Custom Template */`.
    - **By Slug**: Name the file `page-{slug}.php` to automatically override a specific page.
*   **How to Assign Templates (IMPORTANT)**: Since this is a Block Theme, custom PHP templates sometimes don't appear or work correctly in the main "Edit Page" sidebar.
    - **The Fix**: Go to the **Pages** list in the dashboard, hover over your page, and click **Quick Edit**.
    - You will find the **Template** selector there. This "old way" of selecting templates works reliably via Quick Edit, whereas the new Site Editor sidebar often ignores custom PHP files.
*   **functions.php**: Main logic, asset registration, and GA hooks.
*   **theme.json**: Global colors, fonts, and layout sizes.

### Block-Based Development
The entire website is built using **custom blocks**. You fill the page with these blocks in the WordPress editor.

**Folder: `parts/blocks/`**
Every custom block has its own isolated folder. For example, `parts/blocks/hero-block/` contains everything that makes the "Hero" block work.

**Standard Block Folder Structure:**
*   `{block-name}.php`: The HTML/PHP markup for the block.
*   `{block-name}.json`: Registration data (name, category, keywords, and asset handles).
*   `{block-name}.scss`: The styles for this block (Gulp compiles this to `.css`).
*   `{block-name}.js` *(Optional)*: Interactive logic (e.g., initializing a slider).
*   `preview.png`: The thumbnail you see in the WordPress block inserter.

---

## 5. How to Add CSS
There are two ways to add styles:

1.  **Dashboard (Quickest)**: Go to **Appearance > Editor > Styles > Additional CSS**. This is the best place for small tweaks or overrides that don't require access to the codebase.
2.  **Code (Professional)**: For new blocks or complex styles, add them to the block’s specific `.scss` file. Run `gulp watch` in the theme root to compile these changes into the final `style.css`.

---

## 6. How to Duplicate/Generate an ACF Block
If you need a new block that is similar to an existing one:

1.  **Copy Folder**: Copy an existing block folder in `parts/blocks/` and rename it.
2.  **Rename Files**: Rename all files inside to match the new folder name (e.g., `new-block.php`, `new-block.json`).
3.  **Update JSON**: Open `new-block.json` and change the `name`, `title`, and any asset handles inside.
4.  **ACF Dashboard**:
    - Go to **ACF > Field Groups** in WordPress.
    - Duplicate the field group belonging to the original block.
    - Change the "Location" rule to show this group if "Block" is equal to your "New Block".
5.  **ACF JSON & Git Tracking**:
    - This theme saves/loads ACF JSON from **`wp-content/uploads/acf-json/`**.
    - **Important**: Any change you make in the ACF dashboard automatically updates the corresponding `.json` file. We use these files to track all database field changes in **Git**.
    - **Steve**: If you modify any ACF fields, please let us know so we can push these JSON changes to the repository!

---

## 7. How to Change JavaScript & Sliders
Block-specific JS is stored inside the block's folder (e.g., `header.js`).

1.  **Modify**: Edit the `.js` file directly.
2.  **Registration**: New JS files must be registered in `functions.php` inside `cwp_register_block_script()` and referenced in the block’s `.json` file under the `"viewScript"` property.
3.  **Sliders (Swiper)**: We use **Swiper.js** for all carousels. Assets are pre-registered as `swiper` (JS) and `swiper-style` (CSS). If you create a new slider block, ensure your script lists `swiper` as a dependency in `wp_register_script`.

---

## 8. How to Inject Code into <head> or Footer
If you need to add custom scripts, tracking pixels, or meta tags to the whole site, the best way is using hooks in `functions.php`.

### Example: Adding to <head>
```php
add_action('wp_head', function() {
    ?>
    <!-- Your code here -->
    <meta name="custom-tag" content="value">
    <?php
});
```

### Example: Adding to Footer
```php
add_action('wp_footer', function() {
    ?>
    <script>
        console.log('Footer script loaded');
    </script>
    <?php
});
```

---

## 9. Important Tech Standards
*   **Units**: Always use `rem` instead of `px`. (1rem = 10px).
*   **UIkit**: This theme uses **UIkit**. Use UIkit’s data attributes (like `uk-modal` or `uk-grid`) for most layouts and interactions instead of writing custom JS/CSS.
*   **Variables**: Global colors/fonts are in `assets/css/sass/custom/_theme-variables.scss`. Always use these variables.
*   **Site Editor**: To save Editor changes (like footer/header edits) back to the theme logic, use the **Create Block Theme** plugin (Appearance > Save changes to theme).
