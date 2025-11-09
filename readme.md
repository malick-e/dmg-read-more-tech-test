# DMG Read More

A lightweight WordPress plugin that provides:

- A **Gutenberg block** that lets editors search and select a post in the **Inspector Controls**, then renders a stylized “Read More” link in the editor and on the frontend.
- A **WP‑CLI command** for fast, date‑ranged searches of posts that contain this block — implemented with a single prepared SQL query for performance.

---

## Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Gutenberg Block Usage](#gutenberg-block-usage)
- [Rendered Markup](#rendered-markup)
- [WP‑CLI Command](#wp-cli-command)
	- [Usage](#usage)
	- [Options](#options)
	- [Examples](#examples)
	- [Notes on Performance](#notes-on-performance)
- [Development](#development)
	- [Scripts](#scripts)
	- [Folder Structure](#folder-structure)
	- [Linting](#linting)
	- [Build Artifacts / `main-built` Branch](#build-artifacts--main-built-branch)
- [Troubleshooting](#troubleshooting)
- [License](#license)

---

## Features

### Block: `dmg/read-more`
- Editors can search for posts **in the block’s Inspector Controls** using a search string.
- **Pagination** is supported (Next/Previous) for search results and for the default “Recent Articles” list.
- **Search by Post ID**: Paste a numeric ID to jump directly to that post.
- **Recent Articles** are shown by default when no search is entered.
- Selecting a post sets the `postId` attribute and immediately updates the preview.
- Server‑rendered on the frontend for safety; only **published** posts are rendered.

### WP‑CLI: `wp dmg-read-more search`
- Scans **published Posts** in a **date range** and outputs matching **Post IDs** (one per line).
- Uses a **single prepared SQL query** against `wp_posts` with `INSTR(post_content, '<!-- wp:dmg/read-more')` to detect the block marker.
- Defaults to the **last 30 days** if no dates are supplied.
- Logs a friendly message when no results are found or when errors occur.

---

## Requirements

- **WordPress**: 6.7+
- **PHP**: 8.1+
- A theme or site setup that supports the block editor (Gutenberg).

---

## Installation

1. Clone or download this repository into your WordPress `wp-content/plugins` directory.
2Activate **DMG Read More** from **Plugins** in the WP Admin.

> If you are installing from a CI‑built “main‑built” branch/zip, the plugin will include built assets and exclude dev files.

---

## Rendered Markup

Frontend rendering is server‑side (PHP) to ensure accurate post info.

---

## WP‑CLI Command

### Usage

```bash
wp dmg-read-more search [--date-after=<Y-m-d>|<Y-m-d H:i:s>] [--date-before=<Y-m-d>|<Y-m-d H:i:s>]
```

### Options

- `--date-after=<date>`
  Inclusive lower bound. Accepts `Y-m-d` (assumes `00:00:00`) or `Y-m-d H:i:s`.
  **Default:** 30 days ago at `00:00:00` (site timezone).

- `--date-before=<date>`
  Inclusive upper bound. Accepts `Y-m-d` or `Y-m-d H:i:s`.
  **Default:** Now (site timezone).

### Examples

```bash
# Last 30 days (default)
wp dmg-read-more search

# Specific range (inclusive)
wp dmg-read-more search --date-after=2025-01-01 --date-before="2025-02-01 23:59:59"

# Using a simple day for both bounds
wp dmg-read-more search --date-after=2025-05-01 --date-before=2025-05-31
```

**Output:** Matching **Post IDs** are written to STDOUT, one per line. When nothing matches, the command prints a friendly message instead of IDs.

### Notes on Performance

- The command uses a **single prepared SQL query** with conditions on:
	- `post_type = 'post'`
	- `post_status = 'publish'`
	- `post_date BETWEEN :after AND :before`
	- `INSTR(post_content, '<!-- wp:dmg/read-more') > 0`
- This avoids instantiating `WP_Post` objects and keeps memory usage low.
- For very large datasets, keep the requested **date window as tight as possible**; `post_content` is a `LONGTEXT` and is not indexed by default, so scanning fewer rows is critical.
- The query orders by `post_date DESC, ID DESC` so newer matches appear first.

---

## Development

This repo was scaffolded with `@wordpress/create-block`, so standard build tooling is already present.
It also includes a config file for using `@wordpress/wp-env` for local development if desired.

### Scripts

**Node (via `npm`):**
```bash
npm install
npm run start   # watch/build for development
npm run build   # production build
```

**Composer (optional, if present):**
```bash
composer install
composer run lint-php   # phpcs --standard=phpcs.xml
composer run fix-php    # phpcbf --standard=phpcs.xml
# PHPUnit setup is optional; see phpunit.xml(.dist) if included
```

### Folder Structure

```
/dmg
  dmg.php                     # plugin bootstrap (registers block, server render, CLI loader)
  /build                  # built assets
  /src                    # source JS/SCSS before build
  /commands
    class-dmg-read-more-cli.php
  readme.md
```

### Linting

- **PHP**: PHPCS / WPCS
- **JS**: ESLint

Run via `npm` or `composer` scripts mentioned above

### Build Artifacts / `main-built` Branch

Github Actions have been put in place to automatically generate a `main-built` branch that contains only the necessary runtime files for production installs.
1. On push to `main`, GitHub Actions:
	- Checks out `main`
	- Runs `npm ci && npm run build`
	- Copies only the **runtime plugin files** (e.g., `dmg.php`, `build/`, `commands/`, `readme.md`, `composer.json` if needed)
	- Commits to `main-built` (or packages a zip as an artifact)
2. Install from `main-built` or the generated zip on a WordPress site.

This keeps development files out of production installs.
