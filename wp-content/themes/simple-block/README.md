# Simple Block Theme - Project Setup Guide

This guide provides developers with the necessary steps to set up, configure, and maintain the **Simple Block** WordPress theme.

---

## 1. Initialize Git Repository

1. **Initialize or clone a new repository:**
   ```bash
   git init
   # OR
   git clone <repository-url>
   ```
2. Add the `.gitignore` file from our core repository.
3. Push the initial commit.

---

## 2. Install and Configure WordPress

1. **Download WordPress:**
   ```bash
   wp core download
   ```
2. **Configure DDEV:**
   ```bash
   ddev config
   ```
3. **Create and manage the database on `192.168.11.200`**
   * *Optional PHPMyAdmin:*
     ```bash
     ddev get ddev/ddev-phpmyadmin
     ddev restart
     ```
4. Add credentials and relevant info to Basecamp.
5. Install and activate the required plugins from our core repository (`create-block-theme`, `acf`).
6. Add the theme **"Simple Block"** from our core repository (exclude `node_modules` and `package-lock.json`).
7. Copy the `acf-json` folder from `/uploads` to the theme.
8. Remove unnecessary default themes and plugins (keep only one default theme).

---

## 3. Theme Dependencies

From `/wp-content/themes/simple-block`, run:

```bash
npm install -g gulp-cli
npm install
```

Then start Gulp:
```bash
gulp watch
```

---

## 4. WordPress Setup

1. Install and activate the **Create Block Theme** plugin.
2. Activate all required plugins.
3. Activate your custom theme.
4. Sync ACF field groups.
5. Add an option page **"Theme Settings"** via ACF and add initial content under "Theme Settings".
6. Create essential WP pages.
7. Check the following WordPress settings:
   - General
   - Reading
   - Discussion
   - Permalinks

---

## 5. Saving Theme Editor Changes

When you modify anything in the Site Editor, save those changes to your theme:

1. Go to **Create Block Theme** → **Save Changes to Theme** → **Save Changes**.
2. Do this every time you make adjustments in the WP editor. The `theme.json` file will be updated.

---

## 6. Color Guidelines

Define the color palette in:
`Appearance > Editor > Styles > Colors > Palette`

### Suggested Structure:
- **Base:** 1, 2
- **Contrast:** 1, 2, 3
- **Accent:** 1, 2, 3, 4, 5

### Mappings:
- **Headings (h1–h6):** `var(--wp--preset--color--accent)`
- **Body text:** `var(--wp--preset--color--contrast)`
- **Background:** `var(--wp--preset--color--base)`

### Examples of Default Vars and Classes:
- `var(--wp--preset--{$feature}--{$slug})` → `var(--wp--preset--color--base)`
- `.has-{$slug}-{$feature}` → `.has-accent-3-color`

---

## 7. Font Setup

1. Install fonts through the Site Editor.
2. Adjust font sizes in `theme.json` (change font size labels in `theme.json`) through the Site Editor (fluid sizes for paragraphs and headings):
   `Styles > Typography > Font Size Presets`
3. Adjust font styles for elements through the Site Editor.

---

## 8. Layout Configuration

Define layout sizes in the Editor:
- **Content:** Default container width
- **Wide:** Extended container
- **Padding:** Default horizontal padding (`2rem`)

---

## 9. Default Block Alignment Rules

- **alignfull:** Negative left/right margins equal to global padding.
- **alignwide:** Uses wide container max-width.
- **none:** Uses content-width container.
- Default block groups include the class `.has-global-padding`.

---

## 10. Custom Styles

### Theme Variables
Check the theme variables in:
`simple-block > assets > sass > custom > _theme-variables.scss`

Set up `$main-font` and `$heading-font`. These variables are included in every custom block.

### Frontend Custom Style
Check the variables, general styles, and section paddings in:
`simple-block > assets > sass > custom > _frontend-custom-style.scss`

---

## 11. How to Duplicate an ACF Custom Block

1. **Duplicate the existing block folder**
   Copy the entire block folder within your theme’s directory.
2. **Rename all block references**
   Update the block name in every file (PHP, JSON, and CSS/JS if applicable). Ensure the new block name matches across all references.
3. **Register and link custom scripts (if needed)**
   If the block uses a custom script, register it in `functions.php`. Add the script handle (title) to the block’s `.json` file under `viewScript`.
4. **Update preview image and assets**
   Replace the block’s preview image. Check the `images` folder inside the block to ensure all references are correct.
5. **Duplicate the ACF Field Group in WordPress**
   In the WP Dashboard, duplicate the Field Group related to the block. Delete the automatically created `copy.json` file from the `acf-json` folder. Rename the duplicated Field Group and save it — this will generate a new `.json` file with the correct name inside `acf-json`.

---

## 12. Final Steps

- Deactivate the **Create Block Theme** plugin after development is complete.
- Set up the preview images for custom blocks.
- Remove unnecessary custom blocks from the theme and ACF field groups from the dashboard and corresponding JSON files.
- Hide header and footer blocks in WP Dashboard.
- Remove or adjust the function `add_google_analytics` in `functions.php`.
- Set up the theme preview image.
- **Deploy the theme without the `node_modules` folder.**
