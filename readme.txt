=== IS-WP Connector ===
Contributors: colinhahn
Donate link:
Tags:
Stable tag: 2.1
Requires at least: 3.9.0
Tested up to: 3.9.1
License: GPLv2 or higher
License URI: http://www.gnu.org/licenses/gpl-2.0.html

IS-WP connection tool

== Description ==

IS-WP connection tool creates an endpoint to enable CRM systems to interface with WordPress through HTTP POST requests. Requests to the API endpoint are ignored unless the user sets an API key through the plugin settings page, and outbound (WordPress to CRM) connections are only triggered if set up by the user. There is no central server to process these requests - you must set up this plugin with your own API keys.

== Installation ==

1. Upload plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What kind of support is provided? =

This plugin is completely unsupported.

== Changelog ==

= 2.1 =
* Limits usernames to A-Za-z0-9 to match multisite limitations.
* Adds short code for api page

= 2.0 =
* First version submitted to WordPress repository.

== Upgrade Notice ==

= 2.1 =
Multisite compatibility (partial)

= 2.0 =
Upgrade to receive future update notices automatically via the WordPress Repository.

