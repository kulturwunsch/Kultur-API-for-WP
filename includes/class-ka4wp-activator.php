<?php

/**
 * Fired during plugin activation
 *
 * @link       https://kulturwunsch.de
 * @since      1.0.0
 *
 * @package    KA4WP
 * @subpackage KA4WP/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    KA4WP
 * @subpackage KA4WP/includes
 * @author     Kulturwunsch Wolfenbüttel e. V. <info@kulturwunsch.de>
 */
class KA4WP_Activator {

	/**
	 * Startup tasks after plugin activation
	 *
	 * Re-schedule all cron jobs and refresh settings.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// schedule cron to receive event categories
		$integrationOptions = get_option('ka4wp_settings_integrations');
		if(!empty($integrationOptions['api_receive_eventcategories']) && $integrationOptions['api_receive_eventcategories'] != '-1' && !wp_next_scheduled('ka4wp_cron_api_update_eventcategories'))
		{
			wp_schedule_event(time(), $integrationOptions['api_receive_eventcategories_duration'], 'ka4wp_cron_api_update_eventcategories');
		}
		
		if(!empty($integrationOptions['api_receive_impartingareas']) && $integrationOptions['api_receive_impartingareas'] != '-1' && !wp_next_scheduled('ka4wp_cron_api_update_impartingareas'))
		{
			wp_schedule_event(time(), $integrationOptions['api_receive_impartingareas_duration'], 'ka4wp_cron_api_update_impartingareas');
		}

	}

}
