<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://kulturwunsch.de
 * @since      1.0.0
 *
 * @package    KA4WP
 * @subpackage KA4WP/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    KA4WP
 * @subpackage KA4WP/admin
 * @author     Kulturwunsch Wolfenbüttel e. V. <info@kulturwunsch.de>
 */
class KA4WP_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $ka4wp    The ID of this plugin.
	 */
	private $ka4wp;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $ka4wp       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($ka4wp, $version) {

		$this->ka4wp = $ka4wp;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->ka4wp, plugin_dir_url( __FILE__ ) . 'css/ka4wp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$data = array(
	        'site_url' => site_url(),
	        'ajax_url' => admin_url('admin-ajax.php'),
	    );
		wp_enqueue_script( $this->ka4wp, plugin_dir_url( __FILE__ ) . 'js/ka4wp-admin.js', array( 'jquery' ), $this->version, false );
		#wp_localize_script($this->plugin_name, 'ajax_object', $data);
	}
	
	/**
	 * Run after settings "integrations" are added.
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_create_settings_integrations_postprocess($newValue) {
		
		$this->ka4wp_update_settings_integrations_postprocess('', $newValue);
	}
	
	/**
	 * Run after settings section "integrations" are modified
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_update_settings_integrations_postprocess($oldValue, $newValue) {
		
		if(!empty($newValue['api_receive_eventcategories']) && $newValue['api_receive_eventcategories'] != "-1" && empty(wp_next_scheduled('ka4wp_cron_api_update_eventcategories')))
		{
			$nr = wp_schedule_event(time(), $newValue['ka4wp_cron_api_update_eventcategories_recurrence'] ?: 'daily', 'ka4wp_cron_api_update_eventcategories');
			
			if(is_bool($nr) && $nr == true) { 
				error_log( 'Next run planned: '.wp_next_scheduled('ka4wp_cron_api_update_eventcategories')); 
			} else { 
				error_log('Fehler bei Planung: '.$nr->get_error_message()); 
			}

		} elseif(empty($newValue['api_receive_eventcategories']) || $newValue['api_receive_eventcategories'] == "-1") {
			
			if(wp_next_scheduled('ka4wp_cron_api_update_eventcategories'))
			{
				$nr = wp_clear_scheduled_hook('ka4wp_cron_api_update_eventcategories');
				
				if(is_int($nr)) { 
					error_log( 'Jobs unplanned: '.$nr); 
				} else { 
					error_log('Fehler bei unplanning: '.$nr->get_error_message()); 
				}
			}
		}
		
		if(!empty($newValue['api_receive_impartingareas']) && $newValue['api_receive_impartingareas'] != "-1" && empty(wp_next_scheduled('ka4wp_cron_api_update_impartingareas')))
		{
			$nr = wp_schedule_event(time(), $newValue['api_receive_impartingareas_recurrence'] ?: 'daily', 'ka4wp_cron_api_update_impartingareas');
			
			if(is_bool($nr) && $nr == true) { 
				error_log( 'Next run planned: '.wp_next_scheduled('ka4wp_cron_api_update_impartingareas')); 
			} else { 
				error_log('Fehler bei Planung: '.$nr->get_error_message()); 
			}

		} elseif(empty($newValue['api_receive_impartingareas']) || $newValue['api_receive_impartingareas'] == "-1") {
			
			if(wp_next_scheduled('ka4wp_cron_api_update_impartingareas'))
			{
				$nr = wp_clear_scheduled_hook('ka4wp_cron_api_update_impartingareas');
				
				if(is_int($nr)) { 
					error_log( 'Jobs unplanned: '.$nr); 
				} else { 
					error_log('Fehler bei unplanning: '.$nr->get_error_message()); 
				}
			}
		}
	}
	
	/**
	 * Saves the API response event categories in taxonomy database
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_api_request_update_eventcategories() {
		
		$options = get_option('ka4wp_settings_integrations');

		if(empty($options['api_receive_eventcategories']) || $options['api_receive_eventcategories'] == '-1')
		{
			return;
		}
		
		$response = self::ka4wp_send_lead($options['api_receive_eventcategories'], 'load_eventcategories');

		#TODO: error_log( 'API Response: '.$response['response']['code'].'; Message:'. $response['body']);

		if(!empty($response['success']) && $response['response']['code'] == 200)
		{
			if(!empty($response['body']))
			{
				self::ka4wp_api_response_update_eventcategories($response['body']);
			}
		} else {
			return;
		}
	}
	
	/**
	 * Saves the API response imparting areas in taxonomy database
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_api_request_update_impartingareas() {
		
		$options = get_option('ka4wp_settings_integrations');

		if(empty($options['api_receive_impartingareas']) || $options['api_receive_impartingareas'] == '-1')
		{
			return;
		}
		
		$response = self::ka4wp_send_lead($options['api_receive_impartingareas'], 'load_impartingareas');

		#TODO: error_log( 'API Response: '.$response['response']['code'].'; Message:'. $response['body']);

		if(!empty($response['success']) && $response['response']['code'] == 200)
		{
			if(!empty($response['body']))
			{
				self::ka4wp_api_response_update_impartingareas($response['body']);
			}
		} else {
			return;
		}
	}
	
	/**
	 * Saves the API response eventcategories in taxonomy database
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_api_response_update_eventcategories($categories) {
		
		foreach($categories as $category)
		{
			$term = term_exists($category['name'], 'eventcategories');
			if(!empty($term['term_id']))
			{
				update_term_meta($term['term_id'], 'api_managed', 1);
				update_term_meta($term['term_id'], 'enabled', $category['enabled']);
				update_term_meta($term['term_id'], 'timestamp', $category['timestamp']);
				update_term_meta($term['term_id'], 'shortcut', $category['shortcut']);
				update_term_meta($term['term_id'], 'databse_id', $category['id']);
				update_term_meta($term['term_id'], 'description', $category['description']);
			} else {
				$term = wp_insert_term($category['name'], 'eventcategories', array('description'=> $category['description']));
				if(!is_wp_error($term))
				{
					add_term_meta($term['term_id'], 'api_managed', 1);
					add_term_meta($term['term_id'], 'enabled', $category['enabled']);
					add_term_meta($term['term_id'], 'timestamp', $category['timestamp']);
					add_term_meta($term['term_id'], 'shortcut', $category['shortcut']);
					add_term_meta($term['term_id'], 'databse_id', $category['id']);
				}
			}
		}
	}
	
	/**
	 * Saves the API response imparting areas in taxonomy database
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_api_response_update_impartingareas($areas) {
		
		foreach($areas as $area)
		{
			$term = term_exists($area['name'], 'impartingareas');
			if(!empty($term['term_id']))
			{
				update_term_meta($term['term_id'], 'api_managed', 1);
				update_term_meta($term['term_id'], 'enabled', $area['enabled']);
				update_term_meta($term['term_id'], 'timestamp', $area['timestamp']);
				update_term_meta($term['term_id'], 'databse_id', $area['id']);
				update_term_meta($term['term_id'], 'description', $area['description']);
			} else {
				$term = wp_insert_term($area['name'], 'impartingareas', array('description'=> $area['description']));
				if(!is_wp_error($term))
				{
					add_term_meta($term['term_id'], 'api_managed', 1);
					add_term_meta($term['term_id'], 'enabled', $area['enabled']);
					add_term_meta($term['term_id'], 'timestamp', $area['timestamp']);
					add_term_meta($term['term_id'], 'databse_id', $area['id']);
				}
			}
		}
	}
	
	/**
	 * Check Plugin Dependencies
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_verify_dependencies(){
		if(is_multisite()){
			if(!is_plugin_active_for_network('contact-form-7/wp-contact-form-7.php')){
				echo '<div class="notice notice-warning is-dismissible">
	            	 <p>'.__( 'Kultur-API for Wordpress integrations requires CONTACT FORM 7 Plugin to be installed and active.', 'kultur-api-for-wp' ).'</p>
	         	</div>';
			}
		}else{
			if(!is_plugin_active('contact-form-7/wp-contact-form-7.php')){
      			echo '<div class="notice notice-warning is-dismissible">
	            	 <p>'.__( 'Kultur-API for Wordpress integrations requires CONTACT FORM 7 Plugin to be installed and active.', 'kultur-api-for-wp' ).'</p>
	         	</div>';
    		}
    	}
	}
	
	/**
	 * Register the Custom Post Type
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_custom_post_type(){
		$supports = array(
			'title', // Custom Post Type Title
		);
		$labels = array(
			'name' => _x('Kultur-API', 'plural', 'kultur-api-for-wp'),
			'singular_name' => _x('Kultur-api', 'singular', 'kultur-api-for-wp'),
			'menu_name' => _x('Kultur-API', 'admin menu', 'kultur-api-for-wp'),
			'name_admin_bar' => _x('Kultur-API for WP', 'admin bar', 'kultur-api-for-wp'),
			'add_new' => _x('Add New Kultur-API', 'add new', 'kultur-api-for-wp'),
			'add_new_item' => __('Add New Kultur-API', 'kultur-api-for-wp'),
			'new_item' => __('New Kultur-API', 'kultur-api-for-wp'),
			'edit_item' => __('Edit Kultur-API', 'kultur-api-for-wp'),
			'view_item' => __('View Kultur-API', 'kultur-api-for-wp'),
			'all_items' => __('All Kultur-API', 'kultur-api-for-wp'),
			'not_found' => __('No Kultur-API found.', 'kultur-api-for-wp'),
			'register_meta_box_cb' => 'aps_metabox',
		);
		$args = array(
			'supports' => $supports,
			'labels' => $labels,
			'description' => '',
			'hierarchical' => false,
			'public' => false,  // it's not public, it shouldn't have it's own permalink, and so on
			'publicly_queryable' => false,  // you should be able to query it
			'show_ui' => true,  // you should be able to edit it in wp-admin
			'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
			'has_archive' => false,  // it shouldn't have archive page
			'rewrite' => false,  // it shouldn't have rewrite rules
			'menu_icon'           => 'dashicons-tickets',
		);
		register_post_type('ka4wp', $args);
		flush_rewrite_rules(); 
	}
	
	/**
	 * Register the event categories taxonomy
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_register_eventcategories_taxonomy(){

		$labels = array(
			'name' => _x('Event categories', 'plural', 'kultur-api-for-wp'),
			'singular_name' => _x('Event category', 'singular', 'kultur-api-for-wp'),
			'menu_name' => _x('Event categories', 'admin menu', 'kultur-api-for-wp'),
			'name_admin_bar' => _x('Event categories', 'admin bar', 'kultur-api-for-wp'),
			'add_new' => _x('Add New Event category', 'add new', 'kultur-api-for-wp'),
			'add_new_item' => __('Add New Event category', 'kultur-api-for-wp'),
			'new_item' => __('New Event category', 'kultur-api-for-wp'),
			'edit_item' => __('Edit Event category', 'kultur-api-for-wp'),
			'view_item' => __('View Event category', 'kultur-api-for-wp'),
			'all_items' => __('All Event categories', 'kultur-api-for-wp'),
			'not_found' => __('No event category found.', 'kultur-api-for-wp'),
			'name_field_description' => __('Event category display name', 'kultur-api-for-wp'),
			'slug_field_description' => __('This field currently has no effect. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'kultur-api-for-wp'),
			'desc_field_description' => __('Description of the event category, if necessary. The description is currently not visible.', 'kultur-api-for-wp'),
		);
		
		$args = array(
			'labels' => $labels,
			'description' => __('Existing event categories, e.g. for registering new guests.', 'kultur-api-for-wp'),
			'public' => false,
			'show_ui' => true,
			'show_tagcloud' => false,
			'hierarchical' => false,
			'meta_box_cb' => false
		);
		
		register_taxonomy('eventcategories', 'ka4wp', $args);
		flush_rewrite_rules(); 
	}
	
	/**
	 * Register the event categories taxonomy
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_register_impartingareas_taxonomy(){

		$labels = array(
			'name' => _x('Imparting areas', 'plural', 'kultur-api-for-wp'),
			'singular_name' => _x('Imparting area', 'singular', 'kultur-api-for-wp'),
			'menu_name' => _x('Imparting areas', 'admin menu', 'kultur-api-for-wp'),
			'name_admin_bar' => _x('Imparting areas', 'admin bar', 'kultur-api-for-wp'),
			'add_new' => _x('Add New Imparting area', 'add new', 'kultur-api-for-wp'),
			'add_new_item' => __('Add New Imparting area', 'kultur-api-for-wp'),
			'new_item' => __('New Imparting area', 'kultur-api-for-wp'),
			'edit_item' => __('Edit Imparting area', 'kultur-api-for-wp'),
			'view_item' => __('View Imparting area', 'kultur-api-for-wp'),
			'all_items' => __('All Imparting areas', 'kultur-api-for-wp'),
			'not_found' => __('No imparting areas found.', 'kultur-api-for-wp'),
			'name_field_description' => __('Imparting area display name', 'kultur-api-for-wp'),
			'slug_field_description' => __('This field currently has no effect. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'kultur-api-for-wp'),
			'desc_field_description' => __('Description of the imparting area, if necessary. The description is currently not visible.', 'kultur-api-for-wp'),
		);
		
		$args = array(
			'labels' => $labels,
			'description' => __('Existing imparting areas, e.g. for registering new guests.', 'kultur-api-for-wp'),
			'public' => false,
			'show_ui' => true,
			'show_tagcloud' => false,
			'hierarchical' => false,
			'meta_box_cb' => false
		);
		
		register_taxonomy('impartingareas', 'ka4wp', $args);
		flush_rewrite_rules(); 
	}
	
	/**
	 * Register the Custom Meta Boxes
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_metabox(){
	    add_meta_box(
	        'cf7anyapi-setting',
	        __( 'Kultur-API Setting', 'kultur-api-for-wp' ),
	        array($this,'ka4wp_api_settings'),
	        'ka4wp'
	    );
	}
	
	/**
	 * Add settings links to plugin overview
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_add_settings_link($links, $file){
		if($file === 'kultur-api-for-wp/kultur-api-for-wp.php' && current_user_can('manage_options')){
			$url = admin_url('edit.php?post_type=ka4wp&page=ka4wp_settings');
			$documentation = admin_url('edit.php?post_type=ka4wp&page=ka4wp_docs');
			$links = (array) $links;
			$links[] = sprintf('<a href="%s">%s</a>', $url, __('Settings','kultur-api-for-wp'));
			$links[] = sprintf('<a href="%s">%s</a>', $documentation, __('Documentation','kultur-api-for-wp'));
		}
		return $links;
	}
	
	/**
	 * Register the Submenu
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_register_submenu(){

		add_submenu_page(
	        'edit.php?post_type=ka4wp',
	        __('Settings', 'kultur-api-for-wp'),
	        __('Settings', 'kultur-api-for-wp'),
	        'manage_options',
	        'ka4wp_settings',
	        array(&$this,'ka4wp_submenu_settings_callback')
	    );

	    add_submenu_page(
	        'edit.php?post_type=ka4wp',
	        __('Documentation', 'kultur-api-for-wp'),
	        __('Documentation', 'kultur-api-for-wp'),
	        'manage_options',
	        'ka4wp_docs',
	        array(&$this,'ka4wp_submenu_docs_callback')
	    );
	}
	
	/**
	 * Register the Submenu page "Settings"
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_submenu_settings_callback(){
		include dirname(__FILE__).'/partials/ka4wp-admin-options.php';
	}
	
	/**
	 * Register the Submenu page "Documentation"
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_submenu_docs_callback(){
		include dirname(__FILE__).'/partials/placeholder.php';
	}
	
	/**
	 * Register the CF7 API Integrations Tab
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_cf7_add_api_integration($panels){
		$integration_panel = array(
            'title' => __('Kultur-API', 'kultur-api-for-wp'),
            'callback' => array($this, 'ka4wp_api_settings_callback')
        );

        $panels["wpcf7-ka4wp"] = $integration_panel;
        return $panels;
	}
	
	/**
	 * Display the CF7-API integration page
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_api_settings_callback($post){
		include dirname(__FILE__).'/partials/ka4wp-form-options.php';
	}
	
	/**
	 * Registered Metaboxes Fields
	 *
	 * @since    1.0.0
	 */
	public static function ka4wp_api_settings() {
		include dirname(__FILE__).'/partials/ka4wp-admin-display.php';
	}
	
	/**
	 * Register settings for options page.
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_register_settings() {
		
		register_setting('ka4wp_settings_general', 'ka4wp_settings_general', 'ka4wp_settings_validate_general');
		register_setting('ka4wp_settings_logging', 'ka4wp_settings_logging', 'ka4wp_settings_validate_logging');
		register_setting('ka4wp_settings_integrations', 'ka4wp_settings_integrations', 'ka4wp_settings_validate_integrations');
		register_setting('ka4wp_settings_miscellaneous', 'ka4wp_settings_miscellaneous', 'ka4wp_settings_validate_miscellaneous');
		
		add_settings_section(
				'ka4wp_settings_section_integrations', // section ID
				esc_html__('Integration settings', 'kultur-api-for-wp'), // title (optional)
				'', // callback function to display the section (optional) f.e. description
				'ka4wp_settings_integrations'
			);
			
		add_settings_field(
				'api_receive_eventcategories',
				esc_html__('Retrive event categories', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_publish_api'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'api_receive_eventcategories',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'api_receive_eventcategories',
				]
			);
			
		add_settings_field(
				'api_receive_eventcategories_recurrence',
				esc_html__('Recurrence of the API', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_cron_recurrence'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'api_receive_eventcategories_recurrence',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'api_receive_eventcategories_recurrence',
				]
			);
			
		add_settings_field(
				'api_receive_impartingareas',
				esc_html__('Retrive imparting areas', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_publish_api'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'api_receive_impartingareas',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'api_receive_impartingareas',
				]
			);
			
		add_settings_field(
				'api_receive_impartingareas_recurrence',
				esc_html__('Recurrence of the API', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_cron_recurrence'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'api_receive_impartingareas_recurrence',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'api_receive_impartingareas_recurrence',
				]
			);
		
		add_settings_section(
				'ka4wp_settings_section_miscellaneous', // section ID
				esc_html__('Miscellaneous settings', 'kultur-api-for-wp'),
				function(){ esc_html_e('Settings that otherwise have no place.','kultur-api-for-wp'); },
				'ka4wp_settings_miscellaneous'
			);
			
		add_settings_field(
				'my_option_1',
				esc_html__( 'My Option 1', 'kultur-api-for-wp' ),
				array($this, 'ka4wp_settings_render_miscellaneous'),
				'ka4wp_settings_miscellaneous',
				'ka4wp_settings_section_miscellaneous',
				[
					'label_for' => 'my_option_1',
				]
			);
	}
	
	/**
	 * Render possible posttypes from APIs
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_settings_render_publish_api($args) {
		
		$options = get_option($args['option_group']);
		$field = $options[$args['name']];
	
		$posts = get_posts([
						'post_type' => 'ka4wp',
						'post_status' => 'publish',
						'numberposts' => -1,
						'order'    => 'ASC'
					]);
					
		$output = '<select id="ka4wp-settings-'.esc_html($args['name']).'" name="'.esc_html($args['option_group']).'['.esc_html($args['name']).']">';
		$output .= '<option value="-1" '.selected('-1', $field, false).'>'.esc_html__('DISABLED', 'kultur-api-for-wp').'</option>';
			foreach($posts as $post)
			{
				$output .= '<option value="'.esc_attr($post->ID).'" '.selected($post->ID, $field, false).'>'.esc_attr($post->post_title).'</option>';
			}
		$output .= '</select>';
		
		echo $output;
	}
	
	/**
	 * Render possible cron recurrence settings
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_settings_render_cron_recurrence($args) {
		
		$options = get_option($args['option_group']);
		$field = $options[$args['name']];
		
		$output = '<select id="ka4wp-settings-'.esc_html($args['name']).'" name="'.esc_html($args['option_group']).'['.esc_html($args['name']).']">';
		$output .= '<option value="hourly" '.selected('hourly', $field, false).'>'.esc_html__('Stündlich', 'kultur-api-for-wp').'</option>';
		$output .= '<option value="twicedaily’" '.selected('twicedaily’', $field, false).'>'.esc_html__('Zweimal täglich', 'kultur-api-for-wp').'</option>';
		$output .= '<option value="daily’" '.selected('daily’', $field, false).'>'.esc_html__('Einmal täglich', 'kultur-api-for-wp').'</option>';
		$output .= '<option value="weekly" '.selected('weekly', $field, false).'>'.esc_html__('Wöchentlich', 'kultur-api-for-wp').'</option>';
		$output .= '</select>';
		
		echo $output;
	}
	
	/**
	 * Render the edited settings "miscellaneous"
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_settings_render_miscellaneous() {
		
		#$options = get_option( 'my_option_1' );
		$options = get_option( 'ka4wp_settings_miscellaneous' );
		$field = $options['my_option_1'];
		echo "<input id='miscellaneous-settings-field' name='ka4wp_settings_miscellaneous[my_option_1]' type='text' value='" . esc_attr( $field ) . "' />";
	}
	
	/**
	 * Validate the edited settings "general"
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_settings_validate_general($input) {

		
		return $input;
	}
	
	/**
	 * Validate the edited settings "integrations"
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_settings_validate_integrations($input) {

		#TODO: Check in later releases if posts stil exist
		$input['api_receive_eventcategories'] = sanitize_text_field($input['api_receive_eventcategories']);
		$input['api_receive_eventcategories_recurrence'] = sanitize_text_field($input['api_receive_eventcategories_recurrence']);
		$input['api_receive_impartingareas'] = sanitize_text_field($input['api_receive_impartingareas']);
		$input['api_receive_impartingareas_recurrence'] = sanitize_text_field($input['api_receive_impartingareas_recurrence']);
		
		return $input;
	}
	
	/**
	 * Validate the edited settings "miscellaneous"
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_settings_validate_miscellaneous($input) {
		$input['my_option_1'] = sanitize_text_field($input['my_option_1']);
		return $input;
	}
	
	/**
	 * Validate the edited settings "logging"
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_settings_validate_logging($input) {

		return $input;
	}
	
	/**
	 * Retrieve the all current Post API metadata
	 *
	 * @since     1.0.0
	 * @return    array
	 */
	public function ka4wp_get_api_options($post_id) {
		
		$options = [];
		if(!empty($post_id))
		{
			$options['ka4wp_api_type'] = get_post_meta($post_id,'ka4wp_api_type',true);
			$options['ka4wp_api_key'] = get_post_meta($post_id,'ka4wp_api_key',true);
			$options['ka4wp_base_url'] = get_post_meta($post_id,'ka4wp_base_url',true);
			$options['ka4wp_basic_auth'] = get_post_meta($post_id,'ka4wp_basic_auth',true);
			$options['ka4wp_bearer_auth'] = get_post_meta($post_id,'ka4wp_bearer_auth',true);
			$options['ka4wp_input_type'] = get_post_meta($post_id,'ka4wp_input_type',true);
			$options['ka4wp_method'] = get_post_meta($post_id,'ka4wp_method',true);
			$options['ka4wp_form_field'] = get_post_meta($post_id,'ka4wp_form_field',true);
			$options['ka4wp_header_request'] = get_post_meta($post_id,'ka4wp_header_request',true);
			
			#$options = get_post_meta($post->ID);
		}

		return $options;
	}
	
	/**
	 * Update the Metaboxes value on Post Save
	 *
	 * @since    1.0.0
	 */
	public static function ka4wp_update_API_settings($post_id, $post, $update){
		if($post->post_type == 'ka4wp'){
			$status = 'false';
			if(isset($_POST['ka4wp_cpt_nonce']) && wp_verify_nonce($_POST['ka4wp_cpt_nonce'], 'ka4wp_cpt_nonce')){
				
				switch($_POST['ka4wp_api_type'])
				{
					default:
						$options['ka4wp_api_key'] = sanitize_text_field($_POST['ka4wp_api_key']) ?: 'error';
					break;
					case 'wunsch.events':
						$options['ka4wp_api_key'] = sanitize_text_field($_POST['ka4wp_api_key']) ?: 'fail';
					break;
					case 'other':
						$options['ka4wp_base_url'] = sanitize_url($_POST['ka4wp_base_url']);
						$options['ka4wp_input_type'] = sanitize_text_field($_POST['ka4wp_input_type']) ?: 'json';
						$options['ka4wp_method'] = sanitize_text_field($_POST['ka4wp_method']) ?: 'POST';
						$options['ka4wp_header_request'] = sanitize_text_field($_POST['ka4wp_header_request']);
					break;
				}
				
				$options['ka4wp_api_type'] = sanitize_text_field($_POST['ka4wp_api_type']);
				
				#TODO: validate fields after submit
				
				foreach($options as $options_key => $options_value){
					$response = update_post_meta($post_id, $options_key, $options_value );
    			}
				if($response){
					$status = 'true';
				}
				
				//check if api is available
				if($_POST['ka4wp_api_type'] === 'wunsch.events' && $post->post_status == 'publish')
				{
					
					$response = self::ka4wp_send_lead($post_id, 'check_api');
					
					if(!empty($response['success']) && $response['response']['code'] == 200)
					{
						update_post_meta($post_id, 'check_result', 1);
						update_post_meta($post_id, 'check_result_message', $response['body']);
					} else {
						update_post_meta($post_id, 'check_result', 0);
						update_post_meta($post_id, 'check_result_message', $response['error']);
					}
					#add_action('admin_notices','filbr_invalid_id_error' );
					#TODO: Fehlermeldung anzeigen wenn nicht erfolgreich
				}
			}
		}
	}
	
	/**
	 * Saves the API settings from the CF7 API Integrations Tab
	 *
	 * @since    1.0.0
	 */
	public function add_contact_form_API_properties($properties, $contact_form) {

		$properties["ka4wp_api_integrations"] = get_post_meta($contact_form->id(), '_ka4wp_api_integrations', true);
		return $properties;
	}
	
	/**
	 * Saves the API settings from the CF7 API Integrations Tab
	 *
	 * @since    1.0.0
	 */
	public function save_contact_form_API_details($contact_form) {
		$properties = $contact_form->get_properties();
        $properties['ka4wp_api_integrations'] = $_POST['wpcf7-ka4wp'];
        $contact_form->set_properties($properties);
	}
	
	/**
	 * On Form Submit Selected Form Data send to API
	 *
	 * @since    1.0.0
	 */
	public static function ka4wp_send_data_to_api($WPCF7_ContactForm){
		
		$wpcf7_data = $WPCF7_ContactForm->prop('ka4wp_api_integrations');
		
		if(!empty($wpcf7_data['send_to_api']))
		{
			$submission = WPCF7_Submission::get_instance();
			$posted_data = $submission->get_posted_data();
			$cf7files = $submission->uploaded_files();
			
		}
		
		// skip sending mail if enable on form settings
		if(!empty($wpcf7_data['stop_email']))
		{
			$WPCF7_ContactForm->skip_mail = true;
			#add_filter('wpcf7_skip_mail', '__return_true');
		}
	}
		
	/**
	 * Load WUNSCH.events default API values
	 *
	 * @since    1.0.0
	 */
	public static function ka4wp_api_options_wunschevents($api_action, $options) {
		$api_options = array(
				'url'     		=> in_array(wp_get_development_mode(), ['plugin', 'all']) ? 'https://api.testserver.wunschevents.de/v1' : 'https://api.wunsch.events/v1',
				'input_type'	=> 'JSON',
				'http_method'	=> 'GET',
				'headers'		=> [],
				#'basic_auth'	=> $options['ka4wp_basic_auth'], #TODO: Anpassen
				'basic_auth'	=> 'WUNSCH.events-test',
			);
			
		switch($api_action)
		{
			default:
				
			break;
			case 'check_api':
				$api_options['url'] .= '/check';
			break;
			case 'load_eventcategories':
				$api_options['url'] .= '/eventcategories/get';
			case 'load_impartingareas':
				$api_options['url'] .= '/impartingareas/get';
			break;
		}
		
		return $api_options;
	}
	
	/**
	 * Child Fuction of specific form data send to the API
	 *
	 * @since    1.0.0
	 */
	public static function ka4wp_send_lead($post_id, $api_action='', $data = [], $posted_data = []){
		
		
		if('publish' !== get_post_status($post_id)){
			return ['success' => false, 'error' => esc_html__('The selected API is not yet published.', 'kultur-api-for-wp')];
		}
		$post = get_post($post_id);
		$options = get_post_meta($post->ID);
		
		//define default args
		$args = array(
				'timeout'     => 5,
				'redirection' => 5,
				'httpversion' => '1.0',
				'user-agent'  => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
				'blocking'    => true,
				'headers'     => array(),
				'cookies'     => array(),
				'body'        => null,
				'compress'    => false,
				'decompress'  => true,
				'sslverify'   => false, # TODO: Fix needed
				'stream'      => false,
				'filename'    => null
			);
		
		$api_options = [];
		switch($options['ka4wp_api_type']) {
			
			case 'other':
				$api_options['http_method'] = $options['ka4wp_http_method'] ?: 'POST';
				$api_options['url'] = $options['ka4wp_url'] ?: '';				
				$api_options['input_type'] = $options['ka4wp_input_type'] ?: '';				
				$api_options['header_request'] = $options['ka4wp_header_request'] ?: '';				
			break;
			default:
			case 'wunsch.events':
				$api_options = self::ka4wp_api_options_wunschevents($api_action, $options);
				$args['headers'] = array_merge($args['headers'], $api_options['headers']);
			break;
		}
		
		if(!empty($api_options['header_request'])){
      		$args['headers'] = $header_request;
      	}
		
		if(!empty($api_options['basic_auth'])){
        	$args['headers']['Authorization'] = 'Basic ' . base64_encode($api_options['basic_auth']);
      	}
      
      	if(!empty($api_options['bearer_auth'])){
    		$args['headers']['Authorization'] = 'Bearer ' . $bearer_auth;
      	}

		if($api_options['http_method'] == 'GET'){
			
			if($api_options['input_type'] == 'params'){
				
				if(!empty($data)) {
					$data_string = http_build_query($data);

					$api_options['url'] .= stripos($api_options['url'],'?') !== false ? '&'.$data_string : '?'.$data_string;
				}
			}
			else{
				$args['headers']['Content-Type'] = 'application/json';
				if(!empty($data)) {
					$json = json_encode($data);

					if(is_wp_error($json)){
						return ['success' => false, 'error' => $json->get_error_message()];
					} else {
						$args['body'] = $json;
					}
				}
			}
			
			$result = wp_remote_get($api_options['url'], $args);			
		} else {

			if($api_options['input_type'] == "json"){

        		$args['headers']['Content-Type'] = 'application/json';
        		$json = json_encode($data);
        	
        		if(is_wp_error($json)){
          			return ['success' => false, 'error' => $json->get_error_message()];
        		} else{
          			$args['body'] = $json;
    			}
      		} else {
				$args['body'] = $data;
			}
			
      		$result = wp_remote_post($api_options['url'], $args);      		
		}
		
		return self::ka4wp_api_handle_result($result);
	}
	
	/**
	 * Child function to convert API response into correct format
	 *
	 * @since    1.0.0
	 */
	public static function ka4wp_api_handle_result($response) {
		
		if(!is_wp_error($response))
		{
			if(strpos($response['headers']['Content-Type'], 'application/json') !== false)
			{
				$response['body'] = json_decode(wp_remote_retrieve_body($response), true);
			} else {
				$response['body'] = wp_remote_retrieve_body($response);
			}
			$response['success'] = true;
			
			return $response;
		} else {
			$result['error'] = $response->get_error_message();
			$result['success'] = false;
			
			return $result;
		}
	}

}
