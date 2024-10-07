=== Kultur-API for WordPress ===
Contributors: kulturwunsch,juventiner
Donate link: https://kulturwunsch.de/spenden/
Tags: contact form, api, events, culture, multilingual
Requires at least: 6.3
Requires PHP: 7.4
Tested up to: 6.6
Stable tag: 1.1.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Demo URI: https://tastewp.com/new?pre-installed-plugin-slug=contact-form-7%2Ckultur-api-for-wp&redirect=edit.php%3Fpost_type%3Dka4wp%26page%3Dka4wp_settings&ni=true

Simple integration of your culture database into WordPress

== Description ==

Kultur-API is a comprehensive plugin for your website. It is aimed in particular at associations that provide free tickets for events. This plugin is intended to create added value and simplify processes.

It currently includes the following functions:

* Manage categories for events
* Manage imparting areas
* Include both in Contact Form 7 as dynamic fields
* Fetch both via API (at the moment from WUNSCH.events only)

Also some new features are planned:

* Custom API integration
* Submit new guests via Contact Form 7 into external database
* Submit new club members via Contact Form 7 into external database

If you have any requests or ideas for new functions, please let us know.

= Kultur-API for WordPress needs your support =

It is hard to continue development and support for this free plugin without contributions from users like you. If you enjoy using Kultur-API for WordPress and find it useful, please consider [making a donation](https://kulturwunsch.de/spenden/). Your donation will help encourage and support the plugin's continued development and better user support.

= Privacy notices =

With the standard configuration, this plugin does not store any personal data. Depending on your settings, transaction logs may contain personal information.

= Recommended plugins =

The following plugins are recommended for fully Kultur-API integration:

* [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) by Takayuki Miyoshi - With Contact Form 7 you are able to create smart and flexible contact forms.

= Translations =

You can translate Kultur-API for WordPress on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/kultur-api-for-wp).

== Installation ==

1. Upload the entire `kultur-api-for-wp` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **Plugins** screen (**Plugins > Installed Plugins**).

You will find **Kultur-API** menu in your WordPress admin screen.

For basic usage, have a look at the [plugin's website](https://kulturwunsch.de/).

== Screenshots ==


== Changelog ==

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
