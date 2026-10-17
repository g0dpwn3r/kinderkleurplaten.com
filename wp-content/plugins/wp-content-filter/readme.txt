=== Content Censor ===
Contributors: dgwyer, wpgoplugins
Tags: content filter, profanity filter, comment filter, moderation, censor
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 3.2.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Filter configured words from selected public-facing parts of a WordPress site without changing the stored source content.

== Description ==

Content Censor replaces configured words when WordPress displays supported
content. Your original posts, pages, and comments remain unchanged in the
database, so authorised editors can continue to work with the source text.

Use the settings page to:

* define the words to filter;
* choose whole-word or embedded matching;
* select case-sensitive or case-insensitive matching;
* choose a replacement character and masking style;
* enable filtering for content, excerpts, titles, comments, author names, tags,
  and tag clouds; and
* exclude selected post or page IDs.

Content Censor is a presentation-layer filter. It does not replace appropriate
permissions, moderation, reporting, or security controls.

Upgrade to Content Censor Pro for batched site scanning, browser-title and
BuddyPress filtering, custom hook support, automatic updates, and priority
support: https://wpgoplugins.com/plugins/content-censor/

== Installation ==

1. Install and activate the plugin.
2. Open **Content Censor > Settings**. The Home page provides a quick start,
   documentation links, and a support summary; New Features lists recent changes.
3. Replace the example word list and choose the areas to filter.
4. Save the settings and review representative public content.

When Pro is activated over the WordPress.org plugin, existing `pccf_options`
settings are copied into the commercial settings record. The source record is
left intact for rollback.

== Frequently Asked Questions ==

= Does the plugin edit my posts or comments? =

No. Filtering happens when supported content is displayed. Stored content is
not rewritten.

= Can I prevent filtering on a specific page? =

Yes. Add the post or page ID to the comma-separated exclusion list.

= Why are one- and two-character entries ignored? =

Very short entries produce many accidental matches. Content Censor retains the
historic minimum of three characters per configured word.

= Does it replace a moderation system? =

No. It is one display-layer control and should be combined with the moderation
and security processes appropriate to the site.

== Upgrade Notice ==

= 3.2.3 =
Adds Home and New Features pages, reset confirmation, and a privacy-safe support
summary. Pro scans now work through large comment threads in bounded batches.

== Changelog ==

= 3.2.3 =
* Added Home and New Features alongside Settings in the Content Censor menu.
  Existing Settings bookmarks redirect to the current Settings page.
* Added confirmation before resetting settings and a copyable support summary
  that excludes saved rules, content, site addresses, and licence details.
* Updated the quick-start screenshot to show the current filtering controls.
* Pro: improved scanning of comment-heavy content with bounded comment batches
  and no duplicate matching posts in the results.
* Pro: load bundled translations at the correct point in WordPress startup.

= 3.2.1 =
* Aligned the translation domain with the historic `wp-content-filter` directory slug.

= 3.2.0 =
* Unified the public 3.1.0 and Freemius Pro 1.0.0 upgrade paths.
* Added a WordPress.org-compliant Freemius Free/Pro basename handoff.
* Preserved commercial settings and migrated WordPress.org settings safely.
* Restored Pro browser-title and custom-hook filtering.
* Added Unicode-aware masking and robust literal regular-expression handling.
* Replaced fragile HTML matching with WordPress HTML splitting.
* Replaced the unbounded scanner with capability- and nonce-protected batches.
* Restricted saved settings to supported controls and protected settings resets.

= 3.1.0 =
* Escaped and sanitized settings output in the WordPress.org plugin.

= 1.0.0 (Pro) =
* Moved commercial licensing and updates to Freemius.
* Preserved the existing `content_censor_plugin_options` record.
