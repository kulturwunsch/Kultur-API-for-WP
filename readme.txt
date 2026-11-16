=== Kultur-API for WordPress ===
Contributors: kulturwunsch,juventiner
Donate link: https://kulturwunsch.de/spenden/
Tags: fluent forms, crm, api, events, culture
Requires at least: 7.0
Requires PHP: 7.4
Tested up to: 7.1
Stable tag: 2.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Demo URI: https://tastewp.com/new?pre-installed-plugin-slug=fluentform%2Ckultur-api-for-wp&redirect=admin.php%3Fpage%3Dka4wp_settings&ni=true

Simple integration of your culture database into WordPress

== Description ==

Kultur-API is a comprehensive plugin for your website. It is aimed in particular at associations that provide free tickets for events. This plugin is intended to create added value and simplify processes.

It currently includes the following functions:

* Manage categories for events
* Manage imparting Areas
* Manage partners
* Connect a CRM via the WordPress Connectors screen (WUNSCH.events included, further CRMs via adapters)
* Send Fluent Forms submissions to your CRM with a field mapping per form
* Fetch categories, areas and partners via API

Also some new features are planned:

* More CRM adapters

If you have any requests or ideas for new functions, please let us know.

= Kultur-API for WordPress needs your support =

It is hard to continue development and support for this free plugin without contributions from users like you. If you enjoy using Kultur-API for WordPress and find it useful, please consider [making a donation](https://kulturwunsch.de/spenden/). Your donation will help encourage and support the plugin's continued development and better user support.

= Privacy notices =

With the standard configuration, this plugin does not store any personal data. Depending on your settings, transaction logs may contain personal information.

= Required plugins =

The following plugins are required for fully Kultur-API integration:

* [Fluent Forms](https://wordpress.org/plugins/fluentform/) - required to send form submissions to your CRM.

Contact Form 7 integration has been removed. Existing Contact Form 7 forms no longer send data to the CRM; recreate them as Fluent Forms feeds. Fluent Forms replacements for the former event-category and imparting-area datasets are planned for the next release.

= Translations =

You can translate Kultur-API for WordPress on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/kultur-api-for-wp).

== Installation ==

1. Upload the entire `kultur-api-for-wp` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **Plugins** screen (**Plugins > Installed Plugins**).

You will find **Kultur-API** menu in your WordPress admin screen.

For basic usage, have a look at the [plugin's website](https://kulturwunsch.de/).

== Screenshots ==

1. Manage plugin settings.

2. Manage event categories.

3. Display existing partners as grid via shortcode.

4. Enable Kultur-API for WordPress as Fluent Forms integration

5. Configure a CRM connection for the installed Kultur-API adapters

6. Manage a Fluent Forms form feed for the Kultur-API

7. Enabled Integration inside the forms integrations list

== Changelog ==

= 2.0.0 =

- BREAKING: requires WordPress 7.0 or later
- BREAKING: Contact Form 7 integration is removed; recreate your forms as Fluent Forms feeds (an admin notice lists affected forms)
- BREAKING: legacy CRM interface management is removed from the admin menu; API credentials are managed in WordPress Connectors
- new: CRM adapters; the API key is managed in Settings > Connectors (or via the constant KA4WP_WUNSCH_EVENTS_API_KEY); one account per CRM
- new: Fluent Forms feed with endpoint selection, field mapping, required fields and additional values
- change: sync of categories, areas and partners is a simple on/off setting and uses the selected CRM
- change: existing settings and API keys are migrated automatically; additional different keys are reported in an admin notice
- fix: cron jobs were scheduled although the sync was disabled
- fix: all pending database updates are executed on update

= 1.3.3 =

- Change compatible WordPress Version to current
- remove Contact Form 7 as required plugin

= 1.3.2 =

- temp files upload Folder will be deleted on uninstallation
- temp file while CF7 processing will be stored correctly; and full deleted after finish processing
- CF7 nonce was removed because it's never been successful (Server side method call)
- improve WUNSCH.events api definitions
- fix some minor php bugs

= 1.3.1 =
- minor code improvments; non functional releas

= 1.3.0 =

- translations for languag strings in admin-js are added
- The Plugin is now registered for WP Consent Api usage
- adding more Options to configure Partner grid (take at look at our Github documentation)
- some code improvements
- change: mapping for external entries intaxoomies changed vom displayname to external_id
- fix: some Translation issues were fixed
- fix: the partner api was not correct planned on (de)activation

= 1.2.0 =

- fix: Event categories will now correct synched
- new: Support for sync and manage partners

= 1.1.1 =

- fix: some php warning with PHP 8.1 and Above
- fix: some language strings were not translated
- change: add Contact Form 7 as required dependency

= 1.1.0 =

- fix: contact form 7 meta data will be deleted on uninstall this plugin
- fix: missing translations
- new: basic auth fully implemented
- new: taxonomy terms will be deleted if they are missing in api responses

= 1.0.0 =

Initial release


== Upgrade Notice ==

= 2.0.0 =
Major update: requires WordPress 7.0, switches API keys to Settings > Connectors and removes Contact Form 7 integration. Existing CF7 forms will stop sending submissions; recreate them as Fluent Forms feeds. Back up your site before updating.
