=== Kultur-API for WordPress ===
Contributors: kulturwunsch,juventiner
Donate link: https://kulturwunsch.de/spenden/
Tags: contact form, api, events, culture, multilingual
Requires at least: 6.3
Requires PHP: 7.4
Tested up to: 6.8
Stable tag: 1.3.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Demo URI: https://tastewp.com/new?pre-installed-plugin-slug=contact-form-7%2Ckultur-api-for-wp&redirect=edit.php%3Fpost_type%3Dka4wp%26page%3Dka4wp_settings&ni=true

Simple integration of your culture database into WordPress

== Description ==

Kultur-API is a comprehensive plugin for your website. It is aimed in particular at associations that provide free tickets for events. This plugin is intended to create added value and simplify processes.

It currently includes the following functions:

* Manage categories for events
* Manage imparting Areas
* Manage partners
* Include categories and areas in Contact Form 7 as dynamic fields
* Fetch all of them via API (at the moment from WUNSCH.events only)

Also some new features are planned:

* Custom API integration
* Submit new guests via Contact Form 7 into external database
* Submit new club members via Contact Form 7 into external database

If you have any requests or ideas for new functions, please let us know.

= Kultur-API for WordPress needs your support =

It is hard to continue development and support for this free plugin without contributions from users like you. If you enjoy using Kultur-API for WordPress and find it useful, please consider [making a donation](https://kulturwunsch.de/spenden/). Your donation will help encourage and support the plugin's continued development and better user support.

= Privacy notices =

With the standard configuration, this plugin does not store any personal data. Depending on your settings, transaction logs may contain personal information.

= Required plugins =

The following plugins are required for fully Kultur-API integration:

* [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) by Takayuki Miyoshi - With Contact Form 7 you are able to create smart and flexible contact forms.

= Translations =

You can translate Kultur-API for WordPress on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/kultur-api-for-wp).

== Installation ==

1. Upload the entire `kultur-api-for-wp` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **Plugins** screen (**Plugins > Installed Plugins**).

You will find **Kultur-API** menu in your WordPress admin screen.

For basic usage, have a look at the [plugin's website](https://kulturwunsch.de/).

== Screenshots ==

1. Manage settings in foer categories.

2. Manage API options in the contact form 7 configuration inside a new custom tab.

3. The admin menu bar.

4. Add new WUNSCH.events API.

5. Manage event categories, partners and imparting areas via custom taxonomy.

6. Display existing partners as grid via shortcode.

== Changelog ==

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
