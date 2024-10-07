<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://kulturwunsch.de
 * @since             1.0.0
 * @package           KA4WP
 *
 * @wordpress-plugin
 * Plugin Name:       Kultur-API for WP
 * Plugin URI:        https://github.com/kulturwunsch/Kultur-API-for-WP
 * Description:       Kultur-API for WP is an extension to digitize the entire process of a cultural impart organization.
 * Version:           1.1.1
 * Author:            Kulturwunsch Wolfenbüttel e. V.
 * Author URI:        https://kulturwunsch.de
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       kultur-api-for-wp
 * Domain Path:       /languages
 * Requires Plugins:  contact-form-7
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'KA4WP_VERSION', '1.1.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ka4wp-activator.php
 */
function ka4wp_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ka4wp-activator.php';
	KA4WP_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ka4wp-deactivator.php
 */
function ka4wp_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ka4wp-deactivator.php';
	KA4WP_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'ka4wp_activate' );
register_deactivation_hook( __FILE__, 'ka4wp_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ka4wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function ka4wp_run() {

	$plugin = new KA4WP();
	$plugin->run();

}
ka4wp_run();
