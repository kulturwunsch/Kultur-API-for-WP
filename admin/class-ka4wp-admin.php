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
		
		#if(in_array(get_post_type( get_the_ID() ), ['ka4wp', 'wpcf7']))
		#{
			$data = array(
				'site_url' => site_url(),
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ka4wp-ajax-nonce'),
			);
			wp_enqueue_script( $this->ka4wp, plugin_dir_url( __FILE__ ) . 'js/ka4wp-admin.js', array( 'jquery' ), $this->version, false );
			wp_localize_script($this->ka4wp, 'ajax_object', $data);
		#}
	}
	
	/**
	 * Update and install plugin database sources
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_update_plugin() {
		$oldVersion = get_option( 'ka4wp_plugin_version', '0.9' );
		
		if ( !(version_compare( $oldVersion, $this->version ) < 0) ) {
			return;
		}
		
		$this->ka4wp_install_db();
		
		update_option( 'ka4wp_plugin_version', $this->version );
	}
	
	/**
     * Created Custom Database Table for logging
     *
     * On plugin load, create or update database table
     *
     * @since    1.0.0
     */
    private function ka4wp_install_db() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $table_name_logs = $wpdb->prefix.'ka4wp_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name_logs (
            id int(11) NOT NULL AUTO_INCREMENT,
            form_id int(11) NOT NULL,
            post_id int(11) NOT NULL,
            form_data text NOT NULL,
            api_request_header text NOT NULL,
            api_request_body text NOT NULL,
            api_response_header text NOT NULL,
            api_response_body text NOT NULL,
            log text NOT NULL,
            created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_try_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            success_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            count_requests TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        dbDelta( $sql );
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
			$nr = wp_schedule_event(time(), sanitize_text_field($newValue['api_receive_eventcategories_recurrence']) ?: 'daily', 'ka4wp_cron_api_update_eventcategories');
			
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
			$nr = wp_schedule_event(time(), sanitize_text_field($newValue['api_receive_impartingareas_recurrence']) ?: 'daily', 'ka4wp_cron_api_update_impartingareas');
			
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
			$term = term_exists(sanitize_text_field($category['name']), 'eventcategories');
			if(!empty($term['term_id']))
			{
				update_term_meta($term['term_id'], 'api_managed', 1);
				update_term_meta($term['term_id'], 'enabled', sanitize_text_field($category['enabled']));
				update_term_meta($term['term_id'], 'timestamp', sanitize_text_field($category['timestamp']));
				update_term_meta($term['term_id'], 'shortcut', sanitize_text_field($category['shortcut']));
				update_term_meta($term['term_id'], 'databse_id', sanitize_text_field($category['id']));
				update_term_meta($term['term_id'], 'description', sanitize_text_field($category['description']));
			} else {
				$term = wp_insert_term(sanitize_text_field($category['name']), 'eventcategories', array('description'=> sanitize_text_field($category['description'])));
				if(!is_wp_error($term))
				{
					add_term_meta($term['term_id'], 'api_managed', 1);
					add_term_meta($term['term_id'], 'enabled', sanitize_text_field($category['enabled']));
					add_term_meta($term['term_id'], 'timestamp', sanitize_text_field($category['timestamp']));
					add_term_meta($term['term_id'], 'shortcut', sanitize_text_field($category['shortcut']));
					add_term_meta($term['term_id'], 'databse_id', sanitize_text_field($category['id']));
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
			$term = term_exists(sanitize_text_field($area['name']), 'impartingareas');
			if(!empty($term['term_id']))
			{
				update_term_meta($term['term_id'], 'api_managed', 1);
				update_term_meta($term['term_id'], 'enabled', sanitize_text_field($area['enabled']));
				update_term_meta($term['term_id'], 'timestamp', sanitize_text_field($area['timestamp']));
				update_term_meta($term['term_id'], 'databse_id', sanitize_text_field($area['id']));
				update_term_meta($term['term_id'], 'description', sanitize_text_field($area['description']));
			} else {
				$term = wp_insert_term(sanitize_text_field($area['name']), 'impartingareas', array('description'=> sanitize_text_field($area['description'])));
				if(!is_wp_error($term))
				{
					add_term_meta($term['term_id'], 'api_managed', 1);
					add_term_meta($term['term_id'], 'enabled', sanitize_text_field($area['enabled']));
					add_term_meta($term['term_id'], 'timestamp', sanitize_text_field($area['timestamp']));
					add_term_meta($term['term_id'], 'databse_id', sanitize_text_field($area['id']));
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
	            	 <p>'.esc_html__( 'Kultur-API for Wordpress integrations requires CONTACT FORM 7 Plugin to be installed and active.', 'kultur-api-for-wp' ).'</p>
	         	</div>';
			}
		}else{
			if(!is_plugin_active('contact-form-7/wp-contact-form-7.php')){
      			echo '<div class="notice notice-warning is-dismissible">
	            	 <p>'.esc_html__( 'Kultur-API for Wordpress integrations requires CONTACT FORM 7 Plugin to be installed and active.', 'kultur-api-for-wp' ).'</p>
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
				'ka4wp_settings_section_general', // section ID
				esc_html__('General settings', 'kultur-api-for-wp'),
				function(){ esc_html_e('Settings that otherwise have no place.','kultur-api-for-wp'); },
				'ka4wp_settings_general'
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
		
		echo wp_kses($output, ['select', 'option']);
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
		
		echo wp_kses($output, ['select', 'option']);
	}
	
	/**
	 * Render the edited settings "miscellaneous"
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_settings_render_miscellaneous($input) {
		
		
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
		$input['api_receive_eventcategories'] = ('publish' !== get_post_status(sanitize_text_field($input['api_receive_eventcategories']))) ? '-1' : sanitize_text_field($input['api_receive_eventcategories']);
		
		$input['api_receive_eventcategories_recurrence'] = in_array($input['api_receive_eventcategories_recurrence'], ['hourly', 'twicedaily', 'daily', 'weekly']) ? sanitize_text_field($input['api_receive_eventcategories_recurrence']) : 'daily';
		
		$input['api_receive_impartingareas'] = ('publish' !== get_post_status(sanitize_text_field($input['api_receive_impartingareas']))) ? '-1' : sanitize_text_field($input['api_receive_impartingareas']);
		
		$input['api_receive_impartingareas_recurrence'] = in_array($input['api_receive_impartingareas_recurrence'], ['hourly', 'twicedaily', 'daily', 'weekly']) ? sanitize_text_field($input['api_receive_impartingareas_recurrence']) : 'daily';
		
		#$input['api_receive_eventcategories'] = sanitize_text_field($input['api_receive_eventcategories']);
		#$input['api_receive_eventcategories_recurrence'] = sanitize_text_field($input['api_receive_eventcategories_recurrence']);
		#$input['api_receive_impartingareas'] = sanitize_text_field($input['api_receive_impartingareas']);
		#$input['api_receive_impartingareas_recurrence'] = sanitize_text_field($input['api_receive_impartingareas_recurrence']);
		
		// prepare success message
		add_settings_error('ka4wp_settings', 'ka4wp_settings_integrations', esc_html__('Your settings will be saved.', 'kultur-api-for-wp'), 'success');
		#add_settings_error( string $setting, string $code, string $message, string $type = ‘error’ ) #TODO: FERTIGSTELLEN
		
		return $input;
	}
	
	/**
	 * Validate the edited settings "miscellaneous"
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_settings_validate_miscellaneous($input) {
		
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
			$postid = sanitize_text_field($post_id);
			$options['ka4wp_api_type'] = get_post_meta($postid,'ka4wp_api_type',true);
			$options['ka4wp_api_key'] = get_post_meta($postid,'ka4wp_api_key',true);
			$options['ka4wp_base_url'] = get_post_meta($postid,'ka4wp_base_url',true);
			$options['ka4wp_basic_auth'] = get_post_meta($postid,'ka4wp_basic_auth',true);
			$options['ka4wp_bearer_auth'] = get_post_meta($postid,'ka4wp_bearer_auth',true);
			$options['ka4wp_input_type'] = get_post_meta($postid,'ka4wp_input_type',true);
			$options['ka4wp_method'] = get_post_meta($postid,'ka4wp_method',true);
			$options['ka4wp_form_field'] = get_post_meta($postid,'ka4wp_form_field',true);
			$options['ka4wp_header_request'] = get_post_meta($postid,'ka4wp_header_request',true);
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
			if(isset($_POST['ka4wp_cpt_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ka4wp_cpt_nonce'])), 'ka4wp_cpt_nonce')){
				
				switch(sanitize_text_field($_POST['ka4wp_api_type']))
				{
					default:
						$options['ka4wp_api_key'] = sanitize_text_field($_POST['ka4wp_api_key']) ?? '';
					break;
					case 'wunsch.events':
						$options['ka4wp_api_key'] = sanitize_text_field($_POST['ka4wp_api_key']) ?? '';
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
					$response = update_post_meta(sanitize_text_field($post_id), $options_key, $options_value );
    			}
				if($response){
					$status = 'true';
				}
				
				//check if api is available
				if(sanitize_text_field($_POST['ka4wp_api_type']) === 'wunsch.events' && $post->post_status == 'publish')
				{
					
					$response = self::ka4wp_send_lead($post_id, 'check_api');
					
					if(!empty($response['success']) && $response['response']['code'] == 200)
					{
						update_post_meta($post_id, 'check_result', 1);
						update_post_meta($post_id, 'check_result_message', sanitize_text_field($response['body']));
					} else {
						update_post_meta($post_id, 'check_result', 0);
						update_post_meta($post_id, 'check_result_message', !empty($response['error']) ? sanitize_text_field($response['error']) : sanitize_text_field($response['body']));
					}
					#add_action('admin_notices','filbr_invalid_id_error' );
					#TODO: Fehlermeldung anzeigen wenn nicht erfolgreich
					#add_settings_error( string $setting, string $code, string $message, string $type = ‘error’ )
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

		$properties["ka4wp_api_integrations"] = get_post_meta(sanitize_text_field($contact_form->id()), '_ka4wp_api_integrations', true);
		return $properties;
	}
	
	/**
	 * Saves the API settings from the CF7 API Integrations Tab
	 *
	 * @since    1.0.0
	 */
	public function save_contact_form_API_details($contact_form) {
		#if(isset($_POST['_wpnonce']) && wpcf7_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])))){ #TODO: TESTEN

			if(is_array($_POST['wpcf7-ka4wp']))
			{
				$properties = $contact_form->get_properties();
				
				$options = [];
				foreach($_POST['wpcf7-ka4wp'] as $key => $val)
				{
					$options[sanitize_text_field($key)] = sanitize_text_field($val);
				}
				$properties['ka4wp_api_integrations'] = $options;
				$contact_form->set_properties($properties);
			}
		#}
	}
	
	/**
	 * Ajax Endpoint to load specific API details
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_get_selected_endpoint()
	{	
		$defaultsOptions = self::ka4wp_get_endpoint_defaults(sanitize_text_field($_POST['post_id']));
		
		$predefinedMappings = [];
		foreach($defaultsOptions as $typeKey => $typeValues)
		{
			if(!empty($typeValues['options']))
			{
				$predefinedMappings[] = ['name' => esc_attr($typeValues['name']), 'value' => esc_attr($typeKey)];
			}
		}
		
		wp_send_json(['mappings' => $predefinedMappings, 'api_type' => get_post_meta(sanitize_text_field($_POST['post_id']), 'ka4wp_api_type', true) ?: 'none', 'predefined' => $typeValues['options'] ? 1 : 0]);
	}
	
	/**
	 * API Endpoint to load selected predefined mapping
	 *
	 * @since    1.0.0
	 */
	public static function ka4wp_get_predefined_mapping()
	{	
		$defaultsOptions = self::ka4wp_get_endpoint_defaults(sanitize_text_field($_POST['post_id']));
		
		$predefinedMappings = [];
		if(!empty($defaultsOptions[sanitize_text_field($_POST['mapping_key'])]['options']))
		{
			foreach($defaultsOptions[sanitize_text_field($_POST['mapping_key'])]['options'] as $typeKey => $typeValues)
			{
				$predefinedMappings[] = ['name' => esc_attr($typeValues['name']), 'value' => esc_attr($typeValues['value'])];
			}
		}
		
		wp_send_json(['mappings' => $predefinedMappings ?? []]);
	}
	
	/**
	 * API Endpoint to load selected predefined mapping
	 *
	 * @since    1.0.0
	 */
	public static function ka4wp_get_endpoint_defaults($post_id = null)
	{	
		$post_id ?? 0;
		
		$options = [];
		switch(get_post_meta($post_id, 'ka4wp_api_type', true))
		{
			default:
			case 'other':
				$options = [];
					#apply_filters('ka4wp_endpoint_defaults');
			break;
			case 'wunsch.events':
				$options = [
					'submit_cultureguest' => [
							'name' => esc_html__('Registration of new culture guests', 'kultur-api-for-wp'),
							'description' => esc_html__('Interface for creating new cultural guests.', 'kultur-api-for-wp'), 
							'endpoint_path' => '/cultureguest/create', 
							'options' => [
								['name' => esc_html__('Firstname', 'kultur-api-for-wp'), 'value' => 'firstname'],
								['name' => esc_html__('Lastname', 'kultur-api-for-wp'), 'value' => 'lastname'],
								['name' => esc_html__('Gender', 'kultur-api-for-wp'), 'value' => 'gender'],
								['name' => esc_html__('Street', 'kultur-api-for-wp'), 'value' => 'street'],
								['name' => esc_html__('Street number', 'kultur-api-for-wp'), 'value' => 'streetnumber'],
								['name' => esc_html__('Zip code', 'kultur-api-for-wp'), 'value' => 'zipcode'],
								['name' => esc_html__('City', 'kultur-api-for-wp'), 'value' => 'city'],
								['name' => esc_html__('Fixed line number', 'kultur-api-for-wp'), 'value' => 'phone'],
								['name' => esc_html__('Mobile number', 'kultur-api-for-wp'), 'value' => 'mobilephone'],
								['name' => esc_html__('Date of birth', 'kultur-api-for-wp'), 'value' => 'birthdate'],
							]
						],
					'submit_cultureguestgroup' => [
							'name' => esc_html__('Registration of new culture guests as a group (for organizations)', 'kultur-api-for-wp'), 
							'description' => esc_html__('Interface for creating new cultural guests as group.', 'kultur-api-for-wp'),  
							'endpoint_path' => '/culturegroup/create', 
							'options' => [
								['name' => 'Vorname', 'value' => 'firstname'],
								['name' => 'Nachname', 'value' => 'lastname'],
							]
						],
					'submit_organisationmember' => [
							'name' => esc_html__('Register as a organization member', 'kultur-api-for-wp'), 
							'description' => esc_html__('Interface for creating new members in the organization.', 'kultur-api-for-wp'),  
							'endpoint_path' => '/culturegroup/create',  
							'endpoint_path' => '/organizationmember/create', 
							'options' => [
								['name' => 'Vorname', 'value' => 'firstname'],
								['name' => 'Nachname', 'value' => 'lastname'],
							]
						],
					'check_api' => [
							'name' => esc_html__('Check the API endpoint health', 'kultur-api-for-wp'), 
							'description' => esc_html__('Interface to check whether the setup was successful and the other system responds correctly.', 'kultur-api-for-wp'), 
							'endpoint_path' => '/check', 
							'options' => []
						],
					'load_eventcategories' => [
							'name' => esc_html__('Load event categories ', 'kultur-api-for-wp'), 
							'description' => esc_html__('Interface for querying the event categories.', 'kultur-api-for-wp'), 
							'endpoint_path' => '/eventcategories/get', 
							'options' => []
						],
					'load_impartingareas' => [
							'name' => esc_html__('Load imparting areas', 'kultur-api-for-wp'), 
							'description' => esc_html__('Interface for querying the imparting areas.', 'kultur-api-for-wp'), 
							'endpoint_path' => '/impartingareas/get', 
							'options' => []
						],
					];
			break;
		}
		return $options;
	}
	
	/**
	 * Prepare formdata for API submit
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_prepare_formdata_for_api($WPCF7_ContactForm) {
		
		$processLogger = [];
		error_log('API-Data: Verarbeitung gestartet');
		
		//prepare upload directory
		$uploadDir = wp_upload_dir();
		$pluginUploadDir = trailingslashit($uploadDir['basedir']).'ka4wp_temp';
		if (!is_dir($pluginUploadDir))
		{
			wp_mkdir_p($pluginUploadDir);
		}
		
		//prepare form fields
		$CF7Submission = WPCF7_Submission::get_instance();
		$posted_data = $CF7Submission->get_posted_data();
		$uploaded_files = $CF7Submission->uploaded_files();
		$ContactForm = $CF7Submission->get_contact_form();
		
		//prepare form meta information
		$formInformation = [
					'timestamp' => $CF7Submission->get_meta('timestamp'),
					'remote_ip' => $CF7Submission->get_meta('remote_ip'),
					'remote_port' => $CF7Submission->get_meta('remote_port'),
					'user_agent' => $CF7Submission->get_meta('user_agent'),
					'url' => $CF7Submission->get_meta('url'),
					'current_user_id' => $CF7Submission->get_meta('current_user_id'),
					'form_id' => $CF7Submission->get_meta('container_post_id'),
				];
		$posted_data['form_information'] = $formInformation;
		
		//handle uploaded files
		if(!empty($uploaded_files))
		{
			foreach($uploaded_files as $key => $file) {
				$fileExtension = pathinfo($file[0], PATHINFO_EXTENSION);
				$originalFilename = pathinfo($file[0], PATHINFO_FILENAME);
				$fileName = 'ka4wp-'.time().'.'.$fileExtension;
				copy($file[0], $pluginUploadDir.'/'.$fileName);	
				$posted_data['files'][$key] = ['filename' => $fileName, 'originalFilename' => $originalFilename, 'extension' => $fileExtension];
			}
		}

		
		#$post_id = $CF7Submission->get_meta('container_post_id');
		#$posted_data['submitted_from'] = $post_id;
		#$posted_data['submit_time'] = $CF7Submission->get_meta('timestamp');
		#$posted_data['User_IP'] = $CF7Submission->get_meta('remote_ip');		
		#self::cf7anyapi_save_form_submit_data($form_id,$posted_data);
		

		//prepare form details
		$form_properties = $ContactForm->get_properties();
		$form_fields = $ContactForm->scan_form_tags();
		$api_settings = $form_properties['ka4wp_api_integrations'] ?? [];
		
		//save logs when enabled
		if(!empty($api_settings["logging"]))
		{
			error_log('API-Data: Logging aktiviert.');
			#$log_id = $this->ka4wp_api_create_log($CF7Submission->get_meta('container_post_id'), $api_settings["send_to_api"], $log, $formInformation, 1); TODO: aktivieren
		}
		
		//stop processing when api is disabled
		if(empty($api_settings["send_to_api"]))
		{
			error_log('API-Data: API deaktiviert.');
			return;#TODO: Fehlerhandling fehlt
		}
		
		if('publish' !== get_post_status($api_settings["apiendpoint"]))
		{
			error_log('API-Data: Schnittstelle nicht öffentlicht.');
			return; #TODO: Fehlerhandling fehlt
		}
		$posted_data['post_id'] = $api_settings["apiendpoint"];
	
		//prepare uploads for api transfer
		if(!empty($uploaded_files)){
			foreach($uploaded_files as $key => $file) {
				$posted_data['files'][$key]['file'] = base64_encode(file_get_contents($file[0]));
			}
		}
		
		$api_values = [];
		foreach($form_fields as $form_fields_value){
			if($form_fields_value->basetype != 'submit' && !empty($api_settings['mapping-'.$form_fields_value->raw_name]))
			{	
				if(!empty($uploaded_files[$form_fields_value->raw_name]))
				{
					$api_values[$api_settings['mapping-'.$form_fields_value->raw_name]] = $posted_data['files'][$key] ?? [];
				} else {
					$api_values[$api_settings['mapping-'.$form_fields_value->raw_name]] = $posted_data[$form_fields_value->raw_name] ?? '';
				}
			}
		}
		
		if(empty($api_values))
		{
			error_log('API-Data: Keine Werte vorhanden.');
			return; #TODO: Fehlermeldung + Log ergänzen
		}
		
		self::ka4wp_send_lead($wpcf7_api_data["apiendpoint"], $wpcf7_api_data["predefined-mapping"] ?? '', $api_values, $posted_data = []);
		
		// delete uploaded files
		if(!empty($uploaded_files)){
			foreach($uploaded_files as $key => $file) {
				wp_delete_file( $pluginUploadDir.'/'.$posted_data['files'][$key]['filename'] );
			}
		}
		
		error_log('API-Data: '.wp_json_encode($api_values));
		
		#wp_reset_postdata();
	}
	
	/**
	 * Checks if email should skipped
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_check_skip_mail($skip_mail, $contact_form) {
		
		$form_properties = $contact_form->get_properties();
		$api_settings = $form_properties['ka4wp_api_integrations'] ?? [];
		
		if(!empty($api_settings['stop_email']) && !empty($api_settings["send_to_api"]))
		{
			$skip_mail = true;
		}
		return $skip_mail;
	}
		
	/**
	 * Load WUNSCH.events default API values
	 *
	 * @since    1.0.0
	 */
	public static function ka4wp_api_options_wunschevents($api_action, $postid) {
		$api_options = array(
				'url'     		=> in_array(wp_get_development_mode(), ['plugin', 'all']) ? 'https://api.testserver.wunschevents.de/v1' : 'https://api.wunsch.events/v1',
				'input_type'	=> 'JSON',
				'http_method'	=> 'GET',
				'headers'		=> [],
				'basic_auth'	=> get_post_meta($postid, 'ka4wp_api_key', true),
			);
			
		$defaults = self::ka4wp_get_endpoint_defaults($postid);
		$api_options['url'] .= $defaults[$api_action]['endpoint_path'];
		
		return $api_options;
	}
	
	/**
	 * Child Fuction of specific form data send to the API
	 *
	 * @since    1.0.0
	 */
	public static function ka4wp_send_lead($post_id, $api_action='', $data = []){
		
		$postid = sanitize_text_field($post_id);
		if('publish' !== get_post_status($postid)){
			return ['success' => false, 'error' => esc_html__('The selected API is not yet published.', 'kultur-api-for-wp')];
		}

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
				'sslverify'   => true,
				'stream'      => false,
				'filename'    => null
			);
		
		$api_options = [];
		switch(get_post_meta($postid, 'ka4wp_api_type', true)) {
			default:
			case 'other':
				$api_options['http_method'] = get_post_meta($postid, 'ka4wp_http_method', true) ?: 'POST';			
				$api_options['url'] = get_post_meta($postid, 'ka4wp_url', true) ?: '';							
				$api_options['input_type'] = get_post_meta($postid, 'ka4wp_input_type', true) ?: 'JSON';							
				$api_options['header_request'] = json_decode(get_post_meta($postid, 'ka4wp_api_type', true)) ?: [];				
			break;
			case 'wunsch.events':
				$api_options = self::ka4wp_api_options_wunschevents($api_action, $postid);
				$api_options['header_request'] = array_merge($args['headers'], $api_options['headers']);
			break;
		}
		
		if(!empty($api_options['header_request'])){
      		$args['headers'] = $api_options['header_request'];
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
					$json = wp_json_encode($data);

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
        		$json = wp_json_encode($data);
        	
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
	public static function ka4wp_api_handle_result($response, $log_id = 0) {
		
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
	
	/**
	 * Create base log entry with default data
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_api_log_create($form_id, $post_id, $log=[], $meta_info=[], $gdpr=0)
	{
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix.'ka4wp_logs',
			array(
				'form_id' => $form_id,
				'post_id' => $post_id,
				'meta_info' => wp_json_encode($meta_info),
				'log' => wp_json_encode($log),
				'gdpr' => $gdpr,
			),
			array('%s')
		);
		
		$log_id = $wpdb->insert_id;
		
		return $log_id;
	}
	
	/**
	 * Create base log entry with default data
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_api_log_update($form_id, $post_id, $log=[], $meta_info=[], $gdpr=0)
	{
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix.'ka4wp_logs',
			array(
				'form_id' => $form_id,
				'post_id' => $post_id,
				'meta_info' => wp_json_encode($meta_info),
				'log' => wp_json_encode($log),
				'gdpr' => $gdpr,
			),
			array('%s')
		);
		
		$log_id = $wpdb->insert_id;
		
		return $log_id;
	}
	
	/**
	 * Create base log entry with default data
	 *
	 * @since    1.0.0
	 */
	public function ka4wp_api_log_add_action($log_id, $action, $request, $response)
	{
		global $wpdb;
		
		if(empty($log_id))
		{
			return;
		}

		$wpdb->insert(
			$wpdb->prefix.'ka4wp_apilog_actions',
			array(
				'form_id' => $form_id,
				'post_id' => $post_id,
				'meta_info' => wp_json_encode($meta_info),
				'log' => wp_json_encode($log),
				'gdpr' => $gdpr,
			),
			array('%s')
		);
		
		$log_id = $wpdb->insert_id;
		
		return $log_id;
	}

}
