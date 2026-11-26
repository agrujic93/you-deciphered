styles.scss in this folder combines uikit's default scss and user generated custom scss.

styles.scss should be compiled in style.css in theme's root folder.

The Events Calendar archive and single-event styles live in
[`custom/frontend-custom-style.scss`](./custom/frontend-custom-style.scss) and
use the theme's WordPress presets. From the theme directory, run
`npx gulp globalFrontCSS` to rebuild the frontend stylesheet and source map.
Single-event notice compatibility is handled separately in
[`../../../inc/tec-notices.php`](../../../inc/tec-notices.php).