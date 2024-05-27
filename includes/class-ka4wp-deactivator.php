<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://kulturwunsch.de
 * @since      1.0.0
 *
 * @package    KA4WP
 * @subpackage KA4WP/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    KA4WP
 * @subpackage KA4WP/includes
 * @author     Kulturwunsch Wolfenbüttel e. V. <info@kulturwunsch.de>
 */
class KA4WP_Deactivator {

	/**
	 * Run before plugin deactivation.
	 *
	 * Un-schedule running cron jobs and shutdown mainly functions
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
				
		// unscheduled load event categories		
		if(wp_next_scheduled('ka4wp_cron_api_update_eventcategories'))
		{
			wp_clear_scheduled_hook('ka4wp_cron_api_update_eventcategories');
		}
		
		// unscheduled load imparting areas		
		if(wp_next_scheduled('ka4wp_cron_api_update_impartingareas'))
		{
			wp_clear_scheduled_hook('ka4wp_cron_api_update_impartingareas');
		}

	}

}
