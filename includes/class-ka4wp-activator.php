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
	 *
	 * @return void
	 */
	public static function activate() {

		// schedule cron to receive event categories
		if(!empty(get_option('ka4wp_api_receive_eventcategories', '')) && !wp_next_scheduled('ka4wp_cron_api_update_eventcategories'))
		{
			wp_schedule_event(time(), get_option('ka4wp_api_receive_eventcategories_recurrence', 'daily') ?: 'daily', 'ka4wp_cron_api_update_eventcategories');
		}
		
		// schedule cron to receive imparting areas
		if(!empty(get_option('ka4wp_api_receive_impartingareas', '')) && !wp_next_scheduled('ka4wp_cron_api_update_impartingareas'))
		{
			wp_schedule_event(time(), get_option('ka4wp_api_receive_impartingareas_recurrence', 'daily') ?: 'daily', 'ka4wp_cron_api_update_impartingareas');
		}
		
		// schedule cron to receive partners
		if(!empty(get_option('ka4wp_api_receive_partners', '')) && !wp_next_scheduled('ka4wp_cron_api_update_partners'))
		{
			wp_schedule_event(time(), get_option('ka4wp_api_receive_partners_recurrence', 'daily') ?: 'daily', 'ka4wp_cron_api_update_partners');
		}
		
		//set installation id if empty
		if(get_option( 'ka4wp_installation_id', '0' ) == '0')
		{
			add_option( 'ka4wp_installation_id', md5(uniqid('ka4wp_', true)) );
		}
	}

}
