<?php

namespace KA4WP\Migration;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use KA4WP\Adapters\AdapterRegistry;
use KA4WP\Adapters\WunschEventsAdapter;

/**
 * Versioned, idempotent data migrations.
 *
 * A step runs once when the stored plugin version is lower than the step
 * version. Steps never delete legacy data, so an update can be repeated.
 *
 * @since 2.0.0
 */
class Migrator {

	public const OPTION_EXTRA_ACCOUNTS = 'ka4wp_migration_extra_accounts';
	public const OPTION_LEGACY_CF7_FORMS = 'ka4wp_legacy_cf7_forms';

	/** @var array<string, string> step version => method */
	private const STEPS = [
		'1.3.2' => 'migrate_1_3_2',
		'2.0.0' => 'migrate_2_0_0',
	];

	/**
	 * Run all pending steps up to the given version.
	 *
	 * @param string $from Previously stored plugin version
	 * @param string $to   Current plugin version
	 *
	 * @return void
	 */
	public static function run(string $from, string $to): void {
		foreach (self::STEPS as $version => $method) {
			if (version_compare($from, $version) < 0 && version_compare($version, $to) <= 0) {
				self::$method();
			}
		}
	}

	/**
	 * Delete all files in the temp folder.
	 *
	 * @return void
	 */
	private static function migrate_1_3_2(): void {
		if (!class_exists('WP_Filesystem_Direct')) {
			require ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
			require ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
		}

		$uploadDir = wp_upload_dir();
		$wpsf = new \WP_Filesystem_Direct(false);
		// @phpstan-ignore method.resultUnused (Return value intentionally ignored during migration.)
		$wpsf->delete(trailingslashit($uploadDir['basedir']) . 'ka4wp_temp', true, 'f');
	}

	/**
	 * Move the credentials of the "ka4wp" custom post type into the
	 * WUNSCH.events adapter and collect legacy Contact Form 7 forms.
	 *
	 * @return void
	 */
	private static function migrate_2_0_0(): void {
		$adapter = new WunschEventsAdapter();
		$settingName = $adapter->get_setting_name('api_key');

		$primaryKey = '';
		$extraAccounts = [];

		$posts = get_posts([
			'post_type'   => 'ka4wp',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'ID',
			'order'       => 'ASC',
		]);

		foreach ($posts as $post) {
			if (get_post_meta($post->ID, 'ka4wp_api_type', true) !== 'wunsch.events') {
				continue;
			}

			$key = trim((string) get_post_meta($post->ID, 'ka4wp_api_key', true));
			if ($key === '') {
				continue;
			}

			if ($primaryKey === '') {
				$primaryKey = $key;
			} elseif ($key !== $primaryKey) {
				$extraAccounts[] = ['id' => $post->ID, 'title' => $post->post_title];
			}
		}

		// never overwrite a key the user already entered in the connectors screen
		if ($primaryKey !== '' && (string) get_option($settingName, '') === '') {
			update_option($settingName, $primaryKey, false);
		}

		add_option(AdapterRegistry::OPTION_CRM_TYPE, AdapterRegistry::DEFAULT_ADAPTER);

		if (!empty($extraAccounts)) {
			update_option(self::OPTION_EXTRA_ACCOUNTS, $extraAccounts, false);
		}

		self::convert_receive_options();
		self::collect_legacy_cf7_forms();
	}

	/**
	 * The sync settings stored the id of a CPT entry, now they are plain flags
	 * because the CRM is selected globally.
	 *
	 * @return void
	 */
	private static function convert_receive_options(): void {
		foreach (['eventcategories', 'impartingareas', 'partners'] as $name) {
			$option = 'ka4wp_api_receive_' . $name;
			$value = get_option($option, '');
			if ($value === false || $value === '' || $value === '1') {
				continue;
			}

			// "0" meant disabled, any post id meant enabled
			update_option($option, in_array($value, ['0', 0], true) ? '' : '1');
		}
	}

	/**
	 * Remember Contact Form 7 forms that still have an API configuration.
	 *
	 * @return void
	 */
	private static function collect_legacy_cf7_forms(): void {
		$forms = get_posts([
			'post_type'   => 'wpcf7_contact_form',
			'post_status' => 'any',
			'numberposts' => -1,
			'meta_key'    => '_ka4wp_api_integrations',
			'fields'      => 'ids',
		]);

		if (!empty($forms)) {
			update_option(self::OPTION_LEGACY_CF7_FORMS, array_map('absint', $forms), false);
		}
	}
}
