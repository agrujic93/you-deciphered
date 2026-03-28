---
applyTo: "**/*"
description: "Use for all tasks in this repository to keep WordPress core untouched unless explicitly requested."
---

# WordPress Core Safety Rules

## 1. Protected Core Paths
- Treat WordPress core as read-only by default.
- Do not edit files under `wp-admin/**`.
- Do not edit files under `wp-includes/**`.
- Do not edit root core loader/auth files like `wp-*.php`, `xmlrpc.php`, `index.php`, and `license.txt` unless user explicitly asks.

## 2. Allowed Customization Areas
- Prefer theme work in `wp-content/themes/**`.
- Prefer plugin work in `wp-content/plugins/**`.
- Prefer custom config in environment-specific files only when explicitly requested.

## 3. Safe Agent Behavior
- If a requested change appears to require a core file edit, stop and ask for confirmation before modifying core.
- Prefer hooks, filters, child theme logic, or plugins instead of patching core.
- Never propose core edits as the first option when a non-core alternative exists.

## 4. Upgrade Safety
- Any direct core change will be overwritten during WordPress updates.
- Always call out update risk before touching protected core files.
