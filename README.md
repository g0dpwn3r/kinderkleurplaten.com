# Kinderkleurplaten

Kinderkleurplaten is a WordPress-based children's coloring-page website. The repository contains the active WordPress theme, reusable WPCode snippets, and Python helper scripts for generating, uploading, and maintaining coloring pages.

## What is included

- A custom WordPress theme for Kinderkleurplaten.
- Custom post types for:
  - `kleurplaten`
  - `gastenboek_bericht`
- A taxonomy for coloring-page themes: `kleurplaat_thema`.
- Shortcodes for:
  - `[kk_ultieme_galerij]`
  - `[kk_gastenboek]`
- Python scripts for:
  - Generating AI coloring pages and publishing them to WordPress.
  - Replacing local SVG files with generated image wrappers.
  - Cleaning failed coloring-page posts from WordPress.
  - Testing Hugging Face connectivity.

## WordPress structure

Important files and folders:

```text
wp-content/themes/kinderkleurplaten/functions.php
wp-content/themes/kinderkleurplaten/front-page.php
wp-content/themes/kinderkleurplaten/archive-kleurplaten.php
wp-content/themes/kinderkleurplaten/single.php
snippets/
```

The theme registers post types, taxonomies, inline styles, and gallery/guestbook shortcodes.

## Python helper scripts

### `scraper-kleurplaten.py`

Generates coloring-page metadata and images, uploads the image to the WordPress media library, then creates a published `kleurplaten` post.

Requires these environment variables in `.env`:

```env
HF_TOKEN=
WORDPRESS_URL=
WORDPRESS_USERNAME=
WORDPRESS_APP_PASSWORD=
```

Example:

```bash
python scraper-kleurplaten.py --theme "Dieren op de boerderij" --count 20
```

### `local-svg-replacer.py`

Replaces SVG files in:

```text
wp-content/themes/kinderkleurplaten/assets/images/
```

It translates Dutch slugs to English prompts, generates a new image, and writes an SVG wrapper around the generated image.

Requires:

```env
HF_TOKEN=
FORCE_OVERWRITE=true
```

Example:

```bash
python local-svg-replacer.py
```

### `cleanup-failed-svgs.py`

Deletes published `kleurplaten` posts and their featured media through the WordPress REST API.

Requires:

```env
WORDPRESS_URL=
WORDPRESS_USERNAME=
WORDPRESS_APP_PASSWORD=
```

Example:

```bash
python cleanup-failed-svgs.py
```

### `test_connection.py`

Checks whether Hugging Face is reachable.

Example:

```bash
python test_connection.py
```

## Local setup

Create a virtual environment:

```bash
python3 -m venv .venv
source .venv/bin/activate
```

Install Python dependencies:

```bash
pip install python-dotenv requests huggingface_hub pillow deep-translator
```

Create a local `.env` file from your private configuration. Do not commit `.env` because it contains API keys and WordPress credentials.

## Snippets folder

The `snippets/` directory stores reusable WordPress snippets and styles:

```text
snippets/cpt-kleurplaten.php
snippets/shortcode-kleurplaten-grid.php
snippets/css-kleurplaten-grid.php
snippets/cpt-gastenboek.php
snippets/shortcode-gastenboek.php
snippets/css-gastenboek.css
```

These can be imported through WPCode or adapted into the theme.

## Security notes

- Keep `.env`, `wp-config.php`, API keys, and application passwords out of Git.
- Use a WordPress application password instead of your main WordPress password.
- Review generated content before publishing large batches.
- Use `cleanup-failed-svgs.py` carefully because it permanently deletes posts and media with `force=true`.
