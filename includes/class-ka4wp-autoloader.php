<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * PSR-4 style autoloader for the KA4WP\ namespace.
 *
 * KA4WP\Adapters\AdapterRegistry => includes/Adapters/AdapterRegistry.php
 *
 * @since      2.0.0
 * @package    KA4WP
 * @subpackage KA4WP/includes
 */
class KA4WP_Autoloader {

	private const PREFIX = 'KA4WP\\';

	/**
	 * Register the autoloader.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register([__CLASS__, 'load']);
	}

	/**
	 * Load a class file of the KA4WP namespace.
	 *
	 * @param string $class Fully qualified class name
	 *
	 * @return void
	 */
	public static function load($class) {
		if (strpos($class, self::PREFIX) !== 0) {
			return;
		}

		$relative = substr($class, strlen(self::PREFIX));
		$file = plugin_dir_path(__FILE__) . str_replace('\\', '/', $relative) . '.php';

		if (is_readable($file)) {
			require_once $file;
		}
	}
}
