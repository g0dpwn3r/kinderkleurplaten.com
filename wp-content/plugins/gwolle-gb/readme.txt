=== Gwolle Guestbook ===
Contributors: Gwolle, mpol, dbonovas, haraldreingruber, roots84, dedotombo, therab, robinnatter, ronr1999, slavum, chris0815, cleantalk, alexclassroom, rhialto
Tags: guestbook, guest book, livre d'or, Gästebuch, review
Requires at least: 4.1
Tested up to: 7.1
Stable tag: 5.1.2
License: GPLv2 or later
Requires PHP: 7.0

Gwolle Guestbook is the WordPress guestbook you've just been looking for. Beautiful and easy.


== Description ==

Gwolle Guestbook is the WordPress guestbook you've just been looking for. Beautiful and easy.
Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle Guestbook and have a real guestbook.


Current features include:

* Easy to use guestbook frontend with a simple form for visitors of your website.
* List of guestbook entries at the frontend with pagination or infinite scroll.
* Widget to display an excerpt of your last or your best entries.
* Simple and clean admin interface that integrates seamlessly into WordPress admin.
* Dashboard Widget to easily manage the latest entries from your Admin Dashboard.
* Easy Import from other guestbooks into Gwolle Guestbook.
* Notification by mail when a new entry has been posted.
* Moderation, so that you can check an entry before it is visible in your guestbook (optional).
* 7 anti-spam features, like Honeypot, Nonce, Form Timeout, Akismet, Stop Forum Spam and Custom Quiz Question.
* Simple Form Builder to select which form-fields you want to use.
* Simple Entry Builder with the parts of each entry that you want to show.
* Multiple guestbooks are possible.
* MultiSite is supported.
* Localization. Own languages can be added very easily through [GlotPress](https://translate.wordpress.org/projects/wp-plugins/gwolle-gb).
* Admins can add a reply to each entry.
* A log for each entry, so that you know which member of the staff released and edited a guestbook entry to the public and when.
* IP-address and host-logging with link to WHOIS query site.
* RSS Feed.
* BBcode, Emoji and Smiley integration (optional).
* Easy uninstall routine for complete removal of all database changes.

... and all that integrated in the stylish WordPress look.

= Import / Export =

You may have another guestbook installed. That's great, because Gwolle Guestbook enables you to import entries easily.
The importer does not delete any of your data, so you can go back to your previous setup without loss of data, if you want to.
Trying Gwolle Guestbook is as easy as 1-2-3.

Import is supported from:

* DMSGuestbook.
* WordPress comments from a specific post, page or just all comments.
* Gwolle Guestbook itself, with Export supported as well (CSV-file).

= Support =

If you have a problem or a feature request, please post it on the plugin's support forum on [wordpress.org](https://wordpress.org/support/plugin/gwolle-gb). I will do my best to respond as soon as possible.

If you send me an email, I will not reply. Please use the support forum.

= Translations =

Translations can be added very easily through [GlotPress](https://translate.wordpress.org/projects/wp-plugins/gwolle-gb).
You can start translating strings there for your locale. They need to be validated though, so if there's no validator yet, and you want to apply for being validator (PTE), please post it on the support forum.
I will make a request on make/polyglots to have you added as validator for this plugin/locale.

= Demo =

Check out the demo at [https://demo.zenoweb.nl](https://demo.zenoweb.nl/wordpress-plugins/gwolle-gb/).

= Add-On =

Gwolle Guestbook: The Add-On is the add-on for Gwolle Guestbook that gives extra functionality for your guestbook.

Current features include:

* Meta Fields. Add any field you want; company, phone number, you name it.
* Social Media Sharing (optional).
* Star Ratings, with voting and display and Rich Snippets for SEO (optional).
* Average star rating per guestbook.
* Like an entry and view likes for each entry.
* Preview for the frontend form.
* Preview for the admin editor form.
* Automatic Refresh of guestbook list with new entries.
* Admin reply on the frontend with AJAX.
* Edit content/author/origin of entry on the frontend with AJAX.
* Report Abuse.
* Easy String Replacement in the default text so you can make this guestbook into a review section or anything you want.
* Delete button in each entry for the moderator and author (optional).
* Permalink button in each entry for easy access (optional).
* Email button to contact each author (optional).
* Upload Images through the form. (Only for Author, Editor and Administrator with capability 'gwolle_gb_upload_files') (optional).
* Sitemap support for popular SEO/Sitemap plugins.
* Auto Anonymize timer (optional).
* Auto Delete timer (optional).

Since version 5.0.0 the Add-On is included in the main plugin.

= Demo with Add-On =

Check out the demo with the Add-On enabled at [https://demo.zenoweb.nl](https://demo.zenoweb.nl/wordpress-plugins/gwolle-guestbook-the-add-on/).

= Compatibility =

This plugin is compatible with [ClassicPress](https://www.classicpress.net).

= Contributions =

This plugin is also available in [Codeberg](https://codeberg.org/cyclotouriste/gwolle-gb).


== Installation ==

= Installation =

* Install the plugin through the admin page "Plugins".
* Alternatively, unpack and upload the contents of the zipfile to your '/wp-content/plugins/' directory.
* Activate the plugin through the 'Plugins' menu in WordPress.
* Place '[gwolle_gb]' in a page. That's it.

As an alternative for the shortcode, you can use the function `show_gwolle_gb();` to show the guestbook in your templates.
It couldn't be easier.

= License =

The plugin itself is released under the GNU General Public License. A copy of this license can be found at the license homepage or
in the gwolle-gb.php file at the top.

= Hooks: Actions and Filters =

There are many hooks available in this plugin. Documentation is included in the zip file in /docs/actions and /docs/filters. Examples are included. If you have a need for a hook, please request this in the support forum.

= Add an entry with PHP code =

It is not that hard to add an entry in PHP code.

	<?php
		$entry = new gwolle_gb_entry();

		// Set the data in the instance, returns true
		$set_data = $entry->set_data( $args );

		// Save entry, returns the id of the entry
		$save = $entry->save();
	?>

The Array $args can have the following key/values:

* id, int with the id, leave empty for a new entry.
* author_name, string with the name of the autor.
* author_id, id with the WordPress user ID of the author.
* author_email, string with the email address of the author.
* author_origin, string with the city of origin of the author.
* author_website, string with the website of the author.
* author_ip, string with the ipaddress of the author.
* author_host, string with the hostname of that ip.
* content, string with content of the message.
* datetime, timestamp of the entry.
* ischecked, bool if it is checked by a moderator.
* checkedby, int with the WordPress ID of that moderator.
* istrash, bool if it is in trash or not.
* isspam, bool if it is spam or not.
* admin_reply, string with content of the admin reply message.
* admin_reply_uid, id with the WordPress user ID of the author of the admin_reply.
* book_id, int with the Book ID of that entry, default is 1.


= Format for importing through CSV-file =

The importer expects a certain format of the CSV-file. If you need to import from a custom solution, your CSV needs to conform.
The header needs to look like this:

	<?php
	array(
		'id',
		'author_name',
		'author_email',
		'author_origin',
		'author_website',
		'author_ip',
		'author_host',
		'content',
		'datetime',
		'isspam',
		'ischecked',
		'istrash',
		'admin_reply',
		'book_id',
		'meta_fields'
	)
	?>

The next lines are made up of the content.

There are some gotchas:

* Date needs to be a UNIX timestamp. For manually creating a timestamp, look at the [timestamp generator](https://www.timestampgenerator.com/). When using a [formatted date](https://www.php.net/manual/en/datetime.formats.date.php), the plugin will try to read it correctly. If it fails it will use today's date.
* Use commas for field separators. If you use Office software like Excel (which is hell) or LibreOffice Calc, set this correctly.
* Use double quotes around each field. When no quotes are used the import process can break when having quotes or commas inside the content of the entry.
* The file should be encoded as UTF-8 without BOM to correctly enter special characters.
* Make sure you use UNIX line-endings. Any decent text-editor can transform a textdocument (CSV file) to UNIX line-endings.

With version 1.4.1 and older, the field datetime was called date.

You could make a test-entry, export that, and look to see what the importer expects from the CSV.
There is also an example CSV file included in the zipfile of the plugin under '/docs/import_example/'.

If you want to prepare a CSV file from other software, plaese be aware that Microsoft Excel is terrible in dealing with CSV files. You will not manage to create a working CSV file with this. Please use LibreOffice Calc for this.

== Frequently Asked Questions ==

= How do I get people to post messages in my guestbook? =

You could start by writing the first entry yourself, and invite people to leave a message.

= Which entries are visible on the Frontend? =

Starting with version 1.0, the following entries are listed on the Frontend:

* Checked
* Not marked as Spam
* Not in the Trash

Before that, in 0.9.7, all the 'checked' entries were visible.

= I have a lot of unchecked entries. What do I do? =

* For the entries that you consider spam, but were not automatically marked as spam, you can manually mark them as spam, and they will not be visible anymore.
* For entries that are not spam, but you still don't want them visible, you can move them to trash.
* The entries that you want visible, set them to checked.

= I want to translate this plugin =

Translations can be added very easily through [GlotPress](https://translate.wordpress.org/projects/wp-plugins/gwolle-gb).
You can start translating strings there for your locale.
They need to be validated though, so if there's no validator yet, and you want to apply for being validator (PTE), please post it on the support forum.
I will make a request on make/polyglots to have you added as validator for this plugin/locale.

= What about Spam? =

By default this plugin uses a Honeypot feature and a Timeout feature. If spambots try to post guestbook entries this should work sufficiently.

If you still have problems there are more options:

* Honeypot feature: Hidden input field that only spambots would fill in.
* Form Timeout: If the form was sent in too fast after loading the page, the entry will be marked as spam.
* Nonce: Will verify if you really loaded the page with the form first, before posting an entry. Spambots will just submit the form without having a Nonce.
* Akismet: Third party spamfilter by Automattic. Works really well, but not everybody likes to use a third party service.
* Stop Forum Spam: Third party spamfilter. Again, works really well, but not everybody likes to use a third party service.
* Custom Anti-Spam question: Use a simple quiz question to test if you are human.

= I already use WP-SpamShield =

WP-SpamShield is a general plugin for anti-spam that supports the general WordPress forms and many plugins.
Activating WP-SpamShield will disable the anti-spam features in Gwolle Guestbook and all anti-spam will be handled by WP-SpamShield.
If you don’t want to use WP-SpamShield’s protection for Gwolle Guestbook, then all you need to do is disable Anti-Spam for Miscellaneous Forms in WP-SpamShield settings.

= How can I use Multiple Guestbooks? =

You can add a parameter to the shortcode, like:

	[gwolle_gb book_id="2"]

This will make that page show all the entries in Book ID 2. In case you read this on wordpress.org, the quotes are supposed to be standard double quotes, no backticks.

If you use the template function, you can use it like this:

	show_gwolle_gb( array('book_id'=>2) );

= With multiple guestbooks, how do I keep track? =

There is no need to use id's that are incrementing.
If you have a lot of guestbooks on lots of pages, you can just use the id of the post as the id of the guestbook. That way you won't have double id's.
You can set the book_id automatically to the post_id with this shortcode:

	[gwolle_gb book_id="post_id"]

In case you read this on wordpress.org, the quotes are supposed to be standard double quotes, no backticks.

= I use Infinite Scroll with multiple guestbooks =

This is not really supported and might result in strange issues.

= I only want to show one entry. =

You can use a shortcode parameter for showing just one entry:

	[gwolle_gb_read entry_id="213"]

In case you read this on wordpress.org, the quotes are supposed to be standard double quotes, no backticks.

= I don't see the labels in the form. =

This plugin doesn't apply any CSS to the label elements. It is possible that your label elements have a white color on a white background.
You can check this with the Inspector in your browser. If that is the case, you have a theme or plugin that is applying that CSS to your
label elements. Please contact them.

= I don't get a notification email. =

First check your spambox in your mailaccount.

Second, on the settingspage you can change the From address for the email that is sent.
Sometimes there are problems sending it from the default address, so this is a good thing to change to a real address.

There are also several SMTP plugins, where you can configure a lot of settings for email.

If it still doesn't work, request the maillog at your hosting provider, or ask if they can take a look.

= I want to show the form and the list on different pages =

There are different shortcodes that you can use.
Instead of the '[gwolle_gb]' shortcode, you can use '[gwolle_gb_write]' for just the form, and '[gwolle_gb_read]' for the list of entries.

There is also a widget that can display the latest entries in a widget area, that has many options.
Alternatively you can use the shortcode '[gwolle_gb_widget]' to display the latest entries in widget layout. Parameters are:

* book_id, int with an ID.
* num_entries, int with the shown number of messages.
* num_words, int with the shown number of words per entry.

= I want to show the form immediately, without the button =

The shortcodes '[gwolle_gb]' and '[gwolle_gb_write]' have a parameter for the button.
You can use them as '[gwolle_gb button="false"]' or '[gwolle_gb_write button="true"]', to deviate from the default.

= Moderation is enabled, but my entry is marked as checked =

If a user with capability of 'gwolle_gb_moderate_comments' posts an entry, it will be marked as checked by default, because he can mark it as checked anyway.

= Moderation is disabled, but some entries are still unchecked =

There is validation of the length of words in the content and author name.
If the words are too long and it looks abusive, it will be marked as unchecked. A moderator will still be needed to manually edit and check these entries.

= When opening the RSS Feed, I get a Error 404 =

You can refresh your rewrite rules, by going to Settings / Permalinks, and save your permalinks again.
This will most likely add the rewrite rule for the RSS Feed.

= I use a caching plugin, and my entries are not visible after posting =

When you have moderation disabled, Gwolle Guestbook will try to refresh the cache.
If it doesn't on your setup, please let me know which caching plugin you use, and support for it might be added.

You can also refresh or delete your cache manually. Most caching plugins offer support for that.

= I use a Multi-Lingual plugin =

There are 2 settings that you need to pay attention to. If you saved the settings for the form tab, you should save an
empty header and notice text. It will fill in the default there after saving, but that is okay.
As long as you saved an empty option, or it is still not-saved, then it will show the translated text from your MO file.

Also, you will want to use the book_id parameter of the shortcode for multiple guestbooks.

= I use a theme with AJAX =

Using a theme with AJAX navigation can give issues. Only on the guestbook page is the JavaScript and CSS loaded.
So you would need to load it on every page to have it available for the guestbook. You can add the following code to functions.php of your theme:

	<?php
	add_action('wp_enqueue_scripts', 'gwolle_gb_enqueue', 20);
	?>

I don't have any experience myself with AJAX themes. If it doesn't work, please contact the theme author.

= My theme has no styling for form fields =

You could use this CSS. Please adapt the colors ;)
The CSS can be added in Appearance > Customizer > Custom CSS.

	html body div.gwolle-gb input[type="button"],
	html body div.gwolle-gb input[type="submit"] {
		color: #fff; /* white */
		background-color: #f00; /* red */
		border: 1px solid #0f0; /* green */
		border-radius: 5px;
	}

	html body div.gwolle-gb form.gwolle-gb-write div.input input[type="text"],
	html body div.gwolle-gb form.gwolle-gb-write div.input input[type="email"],
	html body div.gwolle-gb form.gwolle-gb-write div.input input[type="url"],
	html body div.gwolle-gb form.gwolle-gb-write div.input textarea,
	html body div.gwolle-gb form.gwolle-gb-write div.input select {
		border: 1px solid #0f0; /* green */
		border-radius: 3px;
	}

= I use the Autoptimize plugin =

The frontend scripts will only be loaded on the Guestbook page, so they won't be added to autoptimize.
You can add 'gwolle_gb_frontend' to both the comma-separated JS and CSS autoptimization exclusion list. That way it will still be loaded right.
On the autoptimize settings page, you might have to click on "show advanced settings"-button top-right first. More info on troubleshooting in AO's FAQ.

= What capabilities are needed? =

For moderating comments you need the capability 'gwolle_gb_moderate_comments'.

For managing options you need the capability 'manage_options'.

= Can I override a template? =

You can look at 'frontend/gwolle_gb-entry.php', and copy it to your theme folder. Then it will be loaded by the plugin.
Make sure you keep track of changes in the default templatefile though. It is often better to use filters, that way you are more forward-compatible.

= What hooks are available for customization? =

There are many hooks available in this plugin. Documentation is included in the zip file in /docs/actions and /docs/filters. Examples are included.
If you have a need for an additional hook, please request this in the support forum.

= I want to change the word Guestbook into something else. =

First, this plugin is a guestbook. If you want to use it for a different usecase, you will need to do that in code.
Take a look at the previous question about hooks.
You are probably wanting to use these two hooks:

* [gwolle_gb_write](https://plugins.trac.wordpress.org/browser/gwolle-gb/trunk/docs/filters/gwolle_gb_write.txt)
* [gwolle_gb_button](https://plugins.trac.wordpress.org/browser/gwolle-gb/trunk/docs/filters/gwolle_gb_button.txt)

Also, the add-on has options for text changes.

= I have a one-page design and want to use links with the right anchor. =

It should be possible by using a filter.
Have a look at this [example code](https://plugins.trac.wordpress.org/browser/gwolle-gb/trunk/docs/filters/gwolle_gb_get_permalink.txt). Make sure to use the correct anchor tag for your website.

= Should I really not use WordPress comments for a guestbook? =

Sure you can if you want to. In my personal opinion however it can be a good thing to keep comments and guestbook entries separated.
So if you already have a blog with comments, the guestbook entries might get lost in there, and keeping a separate guestbook can be good.
But if you don't use standard comments, you can just as easily use the comment section for a guestbook.

= Which SEO/Sitemap plugins are supported? =

* [WordPress Core Sitemaps since WP 5.5](https://wordpress.org/plugins/core-sitemaps/).
* [The SEO Framework](https://wordpress.org/plugins/autodescription/).
* [All In One SEO Pack](https://wordpress.org/plugins/all-in-one-seo-pack/).
* [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/).
* [XML Sitemap Generator for Google](https://wordpress.org/plugins/google-sitemap-generator/).
* [XML Sitemap & Google News](https://wordpress.org/plugins/xml-sitemap-feed/).

= The preview and submit do nothing. =

If you check in your console's tab for network, you might see a reply with `saved:null`. This can happen when you entered some strings in the string replacement settings that are also part of the html. For example, if you enter as old string `entry` and new string `review`, this error will occur.

Please be carefull which strings you want replaced.

= What about uploading files? =

Please be aware that you want uploading of images restricted to trusted users, that means users that are logged in and have the capability 'gwolle_gb_upload_files'. Most often these will only be Authors, Editors or Administrators. It won't be great when the website becomes a haven for image spam or worse, that is why this restriction in place.

For users that are not logged in, it is still advised to use the image option in the BBcode menu, where they can link to an image elsewhere on the web.


== Screenshots ==

1. Frontend View of the list of guestbook entries. On top the button that will show the form when clicked. Then pagination. Then the list of entries.
2. Widget with different options.
3. Main Admin Page with the overview panel, so that you easily can see what's the overall status.
4. List of guestbook entries. The icons display the status of an entry.
5. The Editor for a single entry. The Actions are using AJAX. There is a log of each entry what happened to this entry.
6. Settings Page. This is the first tab where you can select which parts of the form to show and use.
7. Dashboard Widget with new and unchecked entries.


== Changelog ==

= 5.1.2 =
* 2026-10-14
* Fix XSS security issue.
  Use esc_url instead of esc_attr for the permalink in the metabox.
  Only show this metabox line in PHP.
  Not adding this metabox line on preview or ajax for security reasons, do not accept permalink in $_POST.
  Thanks crow at wordfence.
* The same for Social Media sharing.

= 5.1.1 =
* 2026-09-22
* Fix meta fields on update (thanks @floenzens).

= 5.1.0 =
* 2026-08-21
* Rewrite template function gwolle_gb_entry_template for better translations.
* If you use a custom template, please merge the updates.
* Add filter gwolle_gb_upload_files_free_for_all.
* Fix entry edit on frontend when city/origin is disabled from view.

= 5.0.2 =
* 2026-06-23
* Fix update on older versions of WordPress.
* Add option to enable/disable sitemap.
* Always show starrating in starrating widget.
* Change order of postboxes on admin import page.
* List Rate It as third party library.
* Merge abuse-tab with misc-tab in Add-On settings.

= 5.0.1 =
* 2026-06-20
* Remove constants from Add-On.
* Use real slug for disabling Add-On, constant might not exist anymore.
* Do not define GWOLLE_GB_ADDON_VER constant twice.

= 5.0.0 =
* 2026-06-19
* Integrate version 2.10.2 of the Add-On.
* Rename all add-on functions to _v2 to avoid conflicts.
* Deactivate separate add-on plugin.
* Add checkbox to enable or disable double entry check.
* Fix warning in Sitemap for Yoast SEO.
* Use search element for search form in widget.
* Set default escape character for importing from CSV.

= 4.10.1 =
* 2026-02-06
* Remove dependency on jQuery on frontend.
* Remove gwolle_gb_ajax_callback jQuery callback.
* Escape key closes metabox.
* Fix undefined variable in main widget.
* Change a few class names.

= 4.10.0 =
* 2025-12-23
* Remove support for Cleantalk antispam service, merged in their plugin.
  (since v6.68 released on 13.11.2025).
* Rewrite most jQuery code into vanilla JavaScript.
* Use CSS transition for main button of form.
* Use CSS transition for metabox.
* Use CSS transition for readmore links.
* Use button to toggle metabox on frontend.
* Improve blocklist.
* Add function gwolle_gb_check_ip_on_blocklist().
* Always show ajax icon in metabox, even if not logged in.
* Change a few class names for gwolle-gb-messages.
* Better sanitizing of settings.
* Add function gwolle_gb_setting_array_sanitize.
* Add function gwolle_gb_markitup_replace for backwards compatibility.
* Use plugin_dir_path instead of WP_PLUGIN_DIR.
* Verify Nonces correctly.
* Remove upgrading old options that don't exist anymore.
* Set autoload explicitly for options.
* Reset character counter for textarea after submitting new entry.
* Remove deprecated gwolle_gb_scroll_callback (as jQuery.Callbacks() ).
* Cleanup old strings for Add-On.

= 4.9.3 =
* 2025-06-23
* Fix security issue in BBcode (thanks zer0gh0st (D.Sim) through Wordfence).
* Use esc_url and wp_kses on BBcode data through url and img.
* Use preg_replace_callback for a callback function for sanitizing.
* Add label to admin pages.
* Add screen-reader-text to main admin pages.
* Make log messages more consistent (thanks rhialto).

= 4.9.2 =
* 2025-06-08
* Add #id again to entries in main shortcode.
* Add link in widget to the corresponding entry.
* Fix 'readmore' functionality (broke in 4.9.0).

= 4.9.1 =
* 2025-05-30
* Add support for Cleantalk antispam service.
* Fix missing s in settingspage for cleantalk (thanks alexclassroom).
* Rewrite small part of jQuery JavaScript to Vanilla JavaScript.

= 4.9.0 =
* 2025-05-04
* Deprecate 'gwolle_gb_ajax_callback' as jQuery.Callbacks.
* Timing and loading order of JavaScript is impossible to manage.
* Add JavaScript function 'gwolle_gb_frontend_callback_function'.

= 4.8.1 =
* 2025-03-26
* Change default setting, disable Nonce spamfilter.
* Add option to also send notification email with spam entries.
* Fix heading and notice, show default when empty.

= 4.8.0 =
* 2025-02-18
* Add option for Youtube to bbcode.
* Add function gwolle_gb_bbcode_disabled.
* Deprecate function gwolle_gb_bbcode_img_disabled.
* Add filter gwolle_gb_bbcode_youtube_enabled.
* Add function 'gwolle_gb_get_field_id()'.
* Fix formfields to have an id attribute, for accessibility (thanks chris0815).
* Add CSS for loginform to have text label above input field.
* Take some hints from phpcs.
* Update strings for Add-On.

= 4.7.2 =
* 2025-01-22
* Fix XSS security issue (thanks Peter Thaleikis).
* Allow html in author_name through filter.
* Hide invisible elements for screenreaders.

= 4.7.1 =
* 2024-11-15
* Better check for parameters in the shortcode.

= 4.7.0 =
* 2024-10-02
* Always use utf8mb4 for database tables (requires MySQL 5.5 or higher).
* Better check for direct access of files.
* Remove return message about 'too fast', not needed really.
* Add function and action 'gwolle_gb_enqueue' so people can choose whether to load css or not.
* Add filter gwolle_gb_enqueue_frontend_css.
* Add filter gwolle_gb_bbcode_img_enabled.
* Add function gwolle_gb_bbcode_img_disabled.
* Also parse (or strip) BBcode in notification mails.
* Loading plugin translations should be delayed until init action.

= 4.6.2 =
* 2024-02-09
* Add direct link to moderation email for quick checking of an entry (thanks Paul Bergers).
* Add log entry entry-checked-by-email.
* Fixes for function 'gwolle_gb_array_flatten()'.

= 4.6.1 =
* 2023-09-29
* Make author email editable too.
* Change max-height for images from 500px to 700px.
* Add support for Litespeed Cache.
* Fix saving meta key for book_id.

= 4.6.0 =
* 2023-05-17
* Add parameter 'num_entries' to shortcode for more flexibility.
* For excerpt, add a Collapse link as well (thanks slavum).
* Fix double slash for sss.js enqueue.
* Small additions to spamfilters.
* Drop 'auto_detect_line_endings' for import, deprecated in PHP 8.1.
* Add small advertisement for a recommended plugin (free).
* Take more hints from phpcs.
* Update strings for Add-On.

= 4.5.0 =
* 2023-01-24
* Support print CSS.
* Use 'wp_required_field_indicator' function from WordPress Core 6.1 if available.
* Add filters 'gwolle_gb_entry_read_author_info_before' and 'gwolle_gb_entry_read_author_info_after'.
* Update emoji.

= 4.4.1 =
* 2022-11-19
* Quick fix for missing roles.

= 4.4.0 =
* 2022-11-18
* Use custom capabilities 'gwolle_gb_moderate_comments' and 'gwolle_gb_upload_files'.
* Refactor Admin Editor, saving new entry saves more options.

= 4.3.0 =
* 2022-09-01
* Dependency for jquery should be an array.
* Make sure setting for admin list is always shown correctly.
* Move setting for antispam security question to antispam settings tab.
* Set max-height for images as well.
* Add class to form if user is logged in or not.
* Add 'clear:both' to metabox lines.
* Support Siteground Cache plugin for clearing cache.
* Use html5 on all themes.
* Add filter 'gwolle_gb_header_text'.
* Add function 'widget_single_view()' to widget to avoid duplicate code.
* Only use 'gwolle_gb_ajax_callback' for callbacks.
* Update strings for add-on.

= 4.2.2 =
* 2022-03-16
* Change options for number of words in widget to more reasonable options.
* Add class 'gwolle-gb-ajax' to form and list if configured so it can be used more widely.
* Add class as well to '#gwolle-gb-total'.
* Remove '&raquo;' character from frontend.
* Place "clearboth" divs inside div for form field, compatibility with zass theme.
* Add filter 'gwolle_gb_formclass' for class of frontend form.
* Add filter 'gwolle_gb_button_text' for simpler changing the button text.
* Add function 'gwolle_gb_array_flatten()' and use it instead of 'print_r()'.
* Always add nonce to the upload form, so we can use it if desired.
* Remove old translation files from plugin, GlotPress should be used.

= 4.2.1 =
* 2021-12-09
* Fix pagination, always use 'int', not the 'float' from 'ceil()'.
* Fix editor checkboxes, use int, not string.

= 4.2.0 =
* 2021-11-18
* Security fix for CSRF, use 'sanitize_text_field' for user_email on author search.
* Thanks Erwan from wpscan.
* Use more 'esc_attr' when appropriate.
* Add option for character limit in content of entry.
* Add function 'gwolle_gb_check_maxlength'.
* Add function 'gwolle_gb_count_characters'.
* Load markitup.js in footer.
* Run update hook in 'init' instead of 'admin_init' to support background updates.
* No need to check if function 'current_user_can', 'user_can', 'is_multisite' and 'has_shortcode' exist.
* Drop support for WP 3.4 and using 'get_current_theme'.
* Some updates from phpcs and wpcs.

= 4.1.2 =
* 2021-05-05
* Add more classes to text elements, for better customization.
* Better explain how to add a new user to notifications.
* Fix PHP error when addon is set as active but not available in plugins folder.

= 4.1.1 =
* 2021-03-01
* Fix deprecated jQuery calls with WP 5.6 and jQuery 3.5.
* Fix form submission when using http on a https website or viceversa.

= 4.1.0 =
* 2021-01-27
* Add notification email on moderation.
* Support WP Rocket for clearing cache.
* Add span around "from" text in frontend.
* Add "required" parameter to search widget.
* Better test for empty string of email subscribers.
* Change 'intval()' to '(int)'.
* Change 'strval()' to '(string)'.

= 4.0.8 =
* 2020-10-21
* Security fix: always escape formdata in 'gwolle_gb_add_formdata()'.
* Thanks [Nicolas SURRIBAS](http://devloop.users.sourceforge.net/index.php?article217/one-crazy-month-of-web-vulnerability-scanning).

= 4.0.7 =
* 2020-10-06
* Remove placeholders in form, labels should be enough.
* Fix closing/opening of postboxes.
* Check for double entries is now a filter, so that it can be disabled.

= 4.0.6 =
* 2020-08-10
* Fix blocklist.

= 4.0.5 =
* 2020-07-21
* Add blocklist (taken from add-on).
* Also use blocklist from WordPress Core.
* Fix datetime in RSS to be RFC 822 compliant.
* Add support for user ID in import (based on email address).
* Support editing of author ID in Editor.
* Add class 'gwolle-gb-widget' to widget 'ul' element.
* Fix saving certain settings on some hosts.
* Update strings for add-on.

= 4.0.4 =
* 2020-06-21
* Fix svn issue.

= 4.0.3 =
* 2020-06-21
* Remove ':' from form labels.
* Remove part of the label for anti-spam question.
* Fix class of messages on error (red/green).
* Fixed a few actions for 'gwolle_gb_save_entry_admin'.
* Remove 'gwolle_gb_antispam_label' filter.
* Metabox opens now with a softer fade effect.
* Use standard From addres 'wordpress@domain.com' as default.
* Add function 'gwolle_gb_get_user_ip()' for CDN or proxy.
* Add filter 'gwolle_gb_include_proxy_ips' for using this with a CDN or proxy.
* Fix bugs in book_id functions.

= 4.0.2 =
* 2020-05-15
* Redo dismiss button for form in CSS.
* Improve CSS for dark themes.
* Set error for select element on 'div.input'.
* Add postbox on main admin page when add-on is active.
* Update strings for add-on.

= 4.0.1 =
* 2020-05-04
* Fix emoji in frontend form.
* Set timestamp in editor to hidden again.
* Make some CSS more specific.

= 4.0.0 =
* 2020-04-28
* Many CSS ids and classes have changed, please review your custom CSS.
* Plugin supports multiple forms on one page now.
* Fix serialized options.
* Add pause on hover for super-simple-slider.
* Fix updating of datetime on admin editor.
* Fix double class attributes in form.
* Run timeout function only once, set timout to 1 second.
* Remove separators from BBcode icons.
* Add loading="lazy" attribute to bbcode images.
* Add filters 'gwolle_gb_author_origin_prefill', 'gwolle_gb_author_content_prefill' and 'gwolle_gb_author_content_label'.
* Deprecate 'gwolle_gb_content_label' filter.
* Update strings for add-on.

