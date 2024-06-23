<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://kulturwunsch.de
 * @since      1.0.0
 *
 * @package    KA4WP
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

	if(empty(get_option('ka4wp_prevent_deletion')))
	{
		//define settings
		$settingOptions = array('ka4wp_plugin_version', 'ka4wp_installation_id', 'ka4wp_api_receive_eventcategories', 'ka4wp_api_receive_eventcategories_recurrence', 'ka4wp_api_keep_deleted_eventcategories', 'ka4wp_api_receive_impartingareas', 'ka4wp_api_receive_impartingareas_recurrence', 'ka4wp_api_keep_deleted_impartingareas', 'ka4wp_prevent_deletion');

		// Clear up our settings
		foreach ($settingOptions as $settingName) {
			delete_option($settingName);
			delete_site_option($settingName);
		}

		// drop a custom database table
		global $wpdb;
		#$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ka4wp_logs" );

		//delete taxonomy terms
		register_taxonomy('eventcategories', 'ka4wp');
		$terms = get_terms(['taxonomy' => 'eventcategories', 'hide_empty' => false]);
		if(!empty($terms))
		{
			foreach($terms as $category)
			{
				wp_delete_term($category->term_id, 'eventcategories');
			}
		}
		
		register_taxonomy('impartingareas', 'ka4wp');
		$terms = get_terms(['taxonomy' => 'impartingareas', 'hide_empty' => false]);
		if(!empty($terms))
		{
			foreach($terms as $category)
			{
				wp_delete_term($category->term_id, 'impartingareas');
			}
		}

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
		
		//delete all custom posts
		$pluginPosts = get_posts( array( 'post_type' => 'ka4wp', 'numberposts' => -1) );
		foreach ( $pluginPosts as $singlePost ) {
			wp_delete_post( $singlePost->ID, true); // Set to False if you want to send them to Trash.
		}
		
		//delete cf7 postmeta
		delete_post_meta_by_key('_ka4wp_api_integrations');
	}