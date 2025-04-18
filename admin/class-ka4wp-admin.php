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
	 *
	 * @return void
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->ka4wp, plugin_dir_url( __FILE__ ) . 'css/ka4wp-admin.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		
		if(!did_action('wp_enqueue_media'))
		{
			wp_enqueue_media();
		}
		
		$data = array(
			'site_url' => site_url(),
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ka4wp-ajax-nonce'),
		);
		wp_enqueue_script( $this->ka4wp, plugin_dir_url( __FILE__ ) . 'js/ka4wp-admin.min.js', array( 'jquery' ), $this->version, false );
		wp_localize_script($this->ka4wp, 'ajax_object', $data);
		
		$lang = array(
			'upload_image' => __('Upload image', 'kultur-api-for-wp'),
			'use_image' => __('Use this image', 'kultur-api-for-wp'),
			'insert_image' => __('Insert image', 'kultur-api-for-wp'),
		);
		wp_localize_script($this->ka4wp, 'lang', $lang);
	}
	
	/**
	 * Update and install plugin database sources
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_update_plugin() {
		$oldVersion = get_option( 'ka4wp_plugin_version', '0.9' );
		
		if (!(version_compare($oldVersion, $this->version) < 0) ) {
			return;
			
		} elseif(version_compare($oldVersion, '1.3.2') < 0) {
			// Fix in 1.3.2 to delete all files in temp folder
			if(!class_exists('WP_Filesystem_Direct'))
			{
				require ABSPATH.'/wp-admin/includes/class-wp-filesystem-base.php';
				require ABSPATH.'/wp-admin/includes/class-wp-filesystem-direct.php';
			}
		
			$uploadDir = wp_upload_dir();
			$wpsf = new WP_Filesystem_Direct(false);
			$wpsf->delete(trailingslashit($uploadDir['basedir']).'ka4wp_temp', true, 'f');
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
	 *
	 * @return void
     */
    private function ka4wp_install_db() {

    }
	
	/**
	 * Run after settings "api_receive_eventcategories" added.
	 *
	 * @since    1.0.0
	 * @param string $newValue The new setting value
	 *
	 * @return void
	 */
	public function ka4wp_create_settings_api_receive_eventcategories($newValue) {
		
		$this->ka4wp_update_settings_api_receive_eventcategories('', $newValue);
	}
	
	/**
	 * Run after settings section "api_receive_eventcategories" are modified
	 *
	 * @since    1.0.0
	 * @param string $oldValue The setting value before edit
	 * @param string $newValue The new changed setting value
	 *
	 * @return void
	 */
	public function ka4wp_update_settings_api_receive_eventcategories($oldValue, $newValue) {
		
		if(!empty($newValue))
		{
			if(empty(wp_next_scheduled('ka4wp_cron_api_update_eventcategories')))
			{
				wp_schedule_event(time(), get_option('ka4wp_api_receive_eventcategories_recurrence', 'daily') ?: 'daily', 'ka4wp_cron_api_update_eventcategories');
			}
		} else {
			wp_clear_scheduled_hook('ka4wp_cron_api_update_eventcategories');
		}
	}
	
	/**
	 * Run after settings "api_receive_eventcategories_recurrence" added.
	 *
	 * @since    1.0.0
	 * @param string $newValue The new added setting value
	 *
	 * @return void
	 */
	public function ka4wp_create_settings_api_receive_eventcategories_recurrence($newValue) {
		
		$this->ka4wp_update_settings_api_receive_eventcategories_recurrence('', $newValue);
	}
	
	/**
	 * Run after settings section "api_receive_eventcategories_recurrence" are modified
	 *
	 * @since    1.0.0
	 * @param string $oldValue The setting value before edit
	 * @param string $newValue The new changed setting value
	 *
	 * @return void
	 */
	public function ka4wp_update_settings_api_receive_eventcategories_recurrence($oldValue, $newValue) {
		
		$apiEnabled = get_option('ka4wp_api_receive_eventcategories_recurrence', '0') ?: '0';
		
		if($oldValue != $newValue && !empty($apiEnabled))
		{
			wp_schedule_event(time(), $newValue, 'ka4wp_cron_api_update_eventcategories');
		}
	}
	
	/**
	 * Run after settings "api_receive_impartingareas" added.
	 *
	 * @since    1.0.0
	 * @param string $newValue The new added setting value
	 *
	 * @return void
	 */
	public function ka4wp_create_settings_api_receive_impartingareas($newValue) {
		
		$this->ka4wp_update_settings_api_receive_impartingareas('', $newValue);
	}
	
	/**
	 * Run after settings section "api_receive_impartingareas" are modified
	 *
	 * @since    1.0.0
	 * @param string $oldValue The setting value before edit
	 * @param string $newValue The new changed setting value
	 *
	 * @return void
	 */
	public function ka4wp_update_settings_api_receive_impartingareas($oldValue, $newValue) {
		
		if(!empty($newValue))
		{
			if(empty(wp_next_scheduled('ka4wp_cron_api_update_impartingareas')))
			{
				wp_schedule_event(time(), get_option('ka4wp_api_receive_impartingareas_recurrence', 'daily') ?: 'daily', 'ka4wp_cron_api_update_impartingareas');
			}
		} else {
			wp_clear_scheduled_hook('ka4wp_cron_api_update_impartingareas');
		}
	}
	
	/**
	 * Run after settings "api_receive_impartingareas_recurrence" added.
	 *
	 * @since    1.0.0
	 * @param string $newValue The new added setting value
	 *
	 * @return void
	 */
	public function ka4wp_create_settings_api_receive_impartingareas_recurrence($newValue) {
		
		$this->ka4wp_update_settings_api_receive_impartingareas_recurrence('', $newValue);
	}
	
	/**
	 * Run after settings section "api_receive_impartingareas_recurrence" are modified
	 *
	 * @since    1.0.0
	 * @param string $oldValue The setting value before edit
	 * @param string $newValue The new changed setting value
	 *
	 * @return void
	 */
	public function ka4wp_update_settings_api_receive_impartingareas_recurrence($oldValue, $newValue) {
		
		$apiEnabled = get_option('ka4wp_api_receive_impartingareas', '0') ?: '0';
		
		if($oldValue != $newValue && !empty($apiEnabled))
		{
			wp_schedule_event(time(), $newValue, 'ka4wp_cron_api_update_impartingareas');
		}
	}
	
	/**
	 * Run after settings "api_receive_partners" added.
	 *
	 * @since    1.2.0
	 * @param string $newValue The new added setting value
	 *
	 * @return void
	 */
	public function ka4wp_create_settings_api_receive_partners($newValue) {
		
		$this->ka4wp_update_settings_api_receive_partners('', $newValue);
	}
	
	/**
	 * Run after settings section "api_receive_partners" are modified
	 *
	 * @since    1.2.0
	 * @param string $oldValue The setting value before edit
	 * @param string $newValue The new changed setting value
	 *
	 * @return void
	 */
	public function ka4wp_update_settings_api_receive_partners($oldValue, $newValue) {
		
		if(!empty($newValue))
		{
			if(empty(wp_next_scheduled('ka4wp_cron_api_update_partners')))
			{
				wp_schedule_event(time(), get_option('ka4wp_api_receive_partners_recurrence', 'daily') ?: 'daily', 'ka4wp_cron_api_update_partners');
			}
		} else {
			wp_clear_scheduled_hook('ka4wp_cron_api_update_partners');
		}
	}
	
	/**
	 * Run after settings "api_receive_partners_recurrence" added.
	 *
	 * @since    1.2.0
	 * @param string $newValue The new added setting value
	 *
	 * @return void
	 */
	public function ka4wp_create_settings_api_receive_partners_recurrence($newValue) {
		
		$this->ka4wp_update_settings_api_receive_partners_recurrence('', $newValue);
	}
	
	/**
	 * Run after settings section "api_receive_partners_recurrence" are modified
	 *
	 * @since    1.2.0
	 * @param string $oldValue The setting value before edit
	 * @param string $newValue The new changed setting value
	 *
	 * @return void
	 */
	public function ka4wp_update_settings_api_receive_partners_recurrence($oldValue, $newValue) {
		
		$apiEnabled = get_option('ka4wp_api_receive_partners', '0') ?: '0';
		
		if($oldValue != $newValue && !empty($apiEnabled))
		{
			wp_schedule_event(time(), $newValue, 'ka4wp_cron_api_update_partners');
		}
	}
	
	/**
	 * Saves the API response event categories in taxonomy database
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_api_request_update_eventcategories() {
		
		$selectedApi = get_option('ka4wp_api_receive_eventcategories', '0') ?: '0';

		if(empty($selectedApi))
		{
			return;
		}
		
		$response = self::ka4wp_send_lead($selectedApi, 'load_eventcategories');

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
	 *
	 * @return void
	 */
	public function ka4wp_api_request_update_impartingareas() {
		
		$selectedApi = get_option('ka4wp_api_receive_impartingareas', '0') ?: '0';

		if(empty($selectedApi))
		{
			return;
		}
		
		$response = self::ka4wp_send_lead($selectedApi, 'load_impartingareas');

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
	 * Saves the API response imparting areas in taxonomy database
	 *
	 * @since    1.2.0
	 *
	 * @return void
	 */
	public function ka4wp_api_request_update_partners() {
		
		$selectedApi = get_option('ka4wp_api_receive_partners', '0') ?: '0';

		if(empty($selectedApi))
		{
			return;
		}
		
		$response = self::ka4wp_send_lead($selectedApi, 'load_partners');

		if(!empty($response['success']) && $response['response']['code'] == 200)
		{
			if(!empty($response['body']))
			{
				self::ka4wp_api_response_update_partners($response['body']);
			}
		} else {
			return;
		}
	}
	
	/**
	 * Saves the API response eventcategories in taxonomy database
	 *
	 * @since    1.0.0
	 * @param array<string, string> $categories Event categories received from api call
	 *
	 * @return void
	 */
	public function ka4wp_api_response_update_eventcategories(array $categories): void {
		
		foreach($categories as $category)
		{
			if(empty($category['id']) || empty($category['name']))
			{
				continue;
			}
			
			$terms = get_terms(array(
				'taxonomy' => 'eventcategories',
				'hide_empty' => false,
				'meta_query' => array(
					array(
						'key'       => 'api_managed',
						'value'     => 1,
						'compare'   => '='
					),
					array(
						'key'       => 'external_id',
						'value'     => sanitize_text_field($category['id']),
						'compare'   => '='
					),
					'relation' => 'AND',
				)
			));
			
			if(!empty($terms))
			{
				foreach($terms as $term)
				{
					wp_update_term($term->term_id, 'eventcategories', array('name' => sanitize_text_field($category['name']), 'description'=> sanitize_text_field($category['description'] ?? '')));
					update_term_meta($term->term_id, 'enabled', sanitize_text_field($category['enabled'] ?? 1));
					update_term_meta($term->term_id, 'timestamp', sanitize_text_field($category['timestamp'] ?? current_time('U')));
					update_term_meta($term->term_id, 'shortcut', sanitize_text_field($category['shortcut'] ?? ''));
				}
			} else {
				$term = wp_insert_term(sanitize_text_field($category['name']), 'eventcategories', array('description'=> sanitize_text_field($category['description'] ?? '')));
				if(!is_wp_error($term))
				{
					add_term_meta($term['term_id'], 'api_managed', 1);
					add_term_meta($term['term_id'], 'enabled', sanitize_text_field($category['enabled'] ?? 1));
					add_term_meta($term['term_id'], 'timestamp', sanitize_text_field($category['timestamp'] ?? current_time('U')));
					add_term_meta($term['term_id'], 'shortcut', sanitize_text_field($category['shortcut'] ?? ''));
					add_term_meta($term['term_id'], 'external_id', sanitize_text_field($category['id']));
				}
			}
		}
		
		//cleanup deleted entries
		$this->ka4wp_cleanup_response_taxonomies('eventcategories', $categories);
	}
	
	/**
	 * Saves the API response imparting areas in taxonomy database
	 *
	 * @since    1.0.0
	 * @param array<string, string> $areas IMparting areas received from api call
	 *
	 * @return void
	 */
	public function ka4wp_api_response_update_impartingareas(array $areas): void {
		
		foreach($areas as $area)
		{
			if(empty($area['id']) || empty($area['name']))
			{
				continue;
			}
			
			$terms = get_terms(array(
				'taxonomy' => 'impartingareas',
				'hide_empty' => false,
				'meta_query' => array(
					array(
						'key'       => 'api_managed',
						'value'     => 1,
						'compare'   => '='
					),
					array(
						'key'       => 'external_id',
						'value'     => sanitize_text_field($area['id']),
						'compare'   => '='
					),
					'relation' => 'AND',
				)
			));
			
			if(!empty($terms))
			{
				foreach($terms as $term)
				{
					wp_update_term($term->term_id, 'impartingareas', array('name' => sanitize_text_field($area['name']), 'description'=> sanitize_text_field($area['description'] ?? '')));
					update_term_meta($term->term_id, 'enabled', sanitize_text_field($area['enabled'] ?? 1));
					update_term_meta($term->term_id, 'timestamp', sanitize_text_field($area['timestamp'] ?? current_time('U')));
				}
			} else {
				$term = wp_insert_term(sanitize_text_field($area['name']), 'impartingareas', array('description'=> sanitize_text_field($area['description'] ?? '')));
				if(!is_wp_error($term))
				{
					add_term_meta($term['term_id'], 'api_managed', 1);
					add_term_meta($term['term_id'], 'enabled', sanitize_text_field($area['enabled'] ?? 1));
					add_term_meta($term['term_id'], 'timestamp', sanitize_text_field($area['timestamp'] ?? current_time('U')));
					add_term_meta($term['term_id'], 'external_id', sanitize_text_field($area['id']));
				}
			}
		}
		
		//cleanup deleted entries
		$this->ka4wp_cleanup_response_taxonomies('impartingareas', $areas);
	}
	
	/**
	 * Saves the API response partners in taxonomy database
	 *
	 * @since    1.2.0
	 * @param array<string, string> $partners Partners received from api call
	 *
	 * @return void
	 */
	public function ka4wp_api_response_update_partners(array $partners): void {
		
		foreach($partners as $partner)
		{
			if(empty($partner['id']) || empty($partner['organizationname']))
			{
				continue;
			}
			
			$terms = get_terms(array(
				'taxonomy' => 'partners',
				'hide_empty' => false,
				'meta_query' => array(
					array(
						'key'       => 'api_managed',
						'value'     => 1,
						'compare'   => '='
					),
					array(
						'key'       => 'external_id',
						'value'     => sanitize_text_field($partner['id']),
						'compare'   => '='
					),
					'relation' => 'AND',
				)
			));
			
			if(!empty($terms))
			{
				foreach($terms as $term)
				{
					wp_update_term($term->term_id, 'partners', array(
							'name' => sanitize_text_field($partner['organizationname']),
						));
					update_term_meta($term->term_id, 'timestamp', sanitize_text_field($partner['timestamp'] ?? current_time('U')));
					update_term_meta($term->term_id, 'organizer', sanitize_text_field($partner['organizer'] ?? 0));
					update_term_meta($term->term_id, 'socialpartner', sanitize_text_field($partner['socialpartner'] ?? 0));
					update_term_meta($term->term_id, 'otherpartner', sanitize_text_field($partner['otherpartner'] ?? 0));
					update_term_meta($term->term_id, 'organisation_holder', sanitize_text_field($partner['organisation_holder'] ?? ''));
					update_term_meta($term->term_id, 'street', sanitize_text_field($partner['street'] ?? ''));
					update_term_meta($term->term_id, 'streetnumber', sanitize_text_field($partner['streetnumber'] ?? ''));
					update_term_meta($term->term_id, 'zipcode', sanitize_text_field($partner['zipcode'] ?? ''));
					update_term_meta($term->term_id, 'city', sanitize_text_field($partner['city'] ?? ''));
					update_term_meta($term->term_id, 'district', sanitize_text_field($partner['district'] ?? ''));
					update_term_meta($term->term_id, 'phonenumber', sanitize_text_field($partner['phonenumber'] ?? ''));
					update_term_meta($term->term_id, 'website', sanitize_text_field($partner['website'] ?? ''));
				}
			} else {
				$term = wp_insert_term(sanitize_text_field($partner['organizationname']), 'partners', array());
				if(!is_wp_error($term))
				{
					add_term_meta($term['term_id'], 'api_managed', 1);
					add_term_meta($term['term_id'], 'timestamp', sanitize_text_field($partner['timestamp'] ?? current_time('U')));
					add_term_meta($term['term_id'], 'external_id', sanitize_text_field($partner['id']));
					add_term_meta($term['term_id'], 'organizer', sanitize_text_field($partner['organizer'] ?? 0));
					add_term_meta($term['term_id'], 'socialpartner', sanitize_text_field($partner['socialpartner'] ?? 0));
					add_term_meta($term['term_id'], 'otherpartner', sanitize_text_field($partner['otherpartner'] ?? 0));
					add_term_meta($term['term_id'], 'organisation_holder', sanitize_text_field($partner['organisation_holder'] ?? ''));
					add_term_meta($term['term_id'], 'street', sanitize_text_field($partner['street'] ?? ''));
					add_term_meta($term['term_id'], 'streetnumber', sanitize_text_field($partner['streetnumber'] ?? ''));
					add_term_meta($term['term_id'], 'zipcode', sanitize_text_field($partner['zipcode'] ?? ''));
					add_term_meta($term['term_id'], 'city', sanitize_text_field($partner['city'] ?? ''));
					add_term_meta($term['term_id'], 'district', sanitize_text_field($partner['district'] ?? ''));
					add_term_meta($term['term_id'], 'phonenumber', sanitize_text_field($partner['phonenumber'] ?? ''));
					add_term_meta($term['term_id'], 'website', sanitize_text_field($partner['website'] ?? ''));
				}
			}
		}
		
		//cleanup deleted entries
		$this->ka4wp_cleanup_response_taxonomies('partners', $partners);
	}
	
	/**
	 * Saves the API response imparting areas in taxonomy database
	 *
	 * @since    1.1.0
	 * @param string $taxonomy The taxonomy to clean up
	 * @param array<string, string|int> $data The data received from the api call
	 *
	 * @return void
	 */
	private function ka4wp_cleanup_response_taxonomies(string $taxonomy, array $data): void
	{
		$terms = get_terms(array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
				'meta_query' => array(
					array(
						'key'       => 'api_managed',
						'value'     => 1,
						'compare'   => '='
					)
				)
			));
			
		$keepDeletedEntries = get_option('ka4wp_api_keep_deleted_'.$taxonomy, '0') ?? '0';
		
		if(!empty($terms) && (count(array_filter(array_column($data, 'id'))) > 0 || empty($data)))
		{
			foreach($terms as $term)
			{
				if(!in_array(get_term_meta($term->term_id, 'external_id', true) ?? '0', array_filter(array_column($data, 'id'))))
				{
					if($keepDeletedEntries != '1')
					{
						wp_delete_term($term->term_id, $taxonomy);
					} else {
						update_term_meta($term->term_id, 'api_managed', 0);
						delete_term_meta($term->term_id, 'external_id');
					}
				}
			}
		}
	}
	
	/**
	 * Check Plugin Dependencies
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_verify_dependencies(){
		if(is_multisite()){
			if(!is_plugin_active_for_network('contact-form-7/wp-contact-form-7.php')){
				echo '<div class="notice notice-warning is-dismissible">
	            	 <p>'.esc_html__( 'Kultur-API for WordPress integrations requires CONTACT FORM 7 Plugin to be installed and active.', 'kultur-api-for-wp' ).'</p>
	         	</div>';
			}
		}else{
			if(!is_plugin_active('contact-form-7/wp-contact-form-7.php')){
      			echo '<div class="notice notice-warning is-dismissible">
	            	 <p>'.esc_html__( 'Kultur-API for WordPress integrations requires CONTACT FORM 7 Plugin to be installed and active.', 'kultur-api-for-wp' ).'</p>
	         	</div>';
    		}
    	}
	}
	
	/**
	 * Register the Custom Post Type
	 *
	 * @since    1.0.0
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @return void
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
	 * Register the event categories taxonomy
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_register_partners_taxonomy(){

		$labels = array(
			'name' => _x('Partners', 'plural', 'kultur-api-for-wp'),
			'singular_name' => _x('Partner', 'singular', 'kultur-api-for-wp'),
			'menu_name' => _x('Partner', 'admin menu', 'kultur-api-for-wp'),
			'name_admin_bar' => _x('Partner', 'admin bar', 'kultur-api-for-wp'),
			'add_new' => _x('Add New Partner', 'add new', 'kultur-api-for-wp'),
			'add_new_item' => __('Add New Partner', 'kultur-api-for-wp'),
			'new_item' => __('New Partner', 'kultur-api-for-wp'),
			'edit_item' => __('Edit Partner', 'kultur-api-for-wp'),
			'view_item' => __('View Partner', 'kultur-api-for-wp'),
			'all_items' => __('All Partners', 'kultur-api-for-wp'),
			'not_found' => __('No partners found.', 'kultur-api-for-wp'),
			'name_field_description' => __('Partner organization name', 'kultur-api-for-wp'),
			'slug_field_description' => __('This field currently has no effect. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'kultur-api-for-wp'),
			'desc_field_description' => __('Description of the partner, if necessary. The description is currently not visible.', 'kultur-api-for-wp'),
		);
		
		$args = array(
			'labels' => $labels,
			'description' => __('Existing partners, e.g. for display in lists.', 'kultur-api-for-wp'),
			'public' => false,
			'show_ui' => true,
			'show_tagcloud' => false,
			'hierarchical' => false,
			'meta_box_cb' => false
		);
		
		register_taxonomy('partners', 'ka4wp', $args);
		flush_rewrite_rules(); 
	}
	
	/**
	 * Register the Custom Meta Boxes
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_metabox(){
	    add_meta_box(
	        'cf7anyapi-setting',
	        __( 'Settings for Kultur-API integration', 'kultur-api-for-wp' ),
	        array($this,'ka4wp_api_settings'),
	        'ka4wp'
	    );
	}
	
	/**
	 * Add settings links to plugin overview
	 *
	 * @since    1.0.0
	 * @param array<string> $links Array with existing plugin info links
	 * @param string $file Path of the plugin file
	 *
	 * @return array<string> $links Array with additional links
	 */
	public function ka4wp_add_settings_link(array $links, string $file): array {
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
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	public function ka4wp_submenu_settings_callback(){
		include dirname(__FILE__).'/partials/ka4wp-admin-options.php';
	}
	
	/**
	 * Register the Submenu page "Documentation"
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_submenu_docs_callback(){
		include dirname(__FILE__).'/partials/placeholder.php';
	}
	
	/**
	 * Register the CF7 API Integrations Tab
	 *
	 * @since    1.0.0
	 * @param array<string, string|array<string, string>> $panels Array with the existing CF7 panels
	 *
	 * @return array<string, string|array<string, string>> $panels The full panels for CF7 integration
	 */
	public function ka4wp_cf7_add_api_integration(array $panels): array {
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
	 * @param array $post Array with current Post Data
	 *
	 * @return void
	 */
	public function ka4wp_api_settings_callback($post){
		include dirname(__FILE__).'/partials/ka4wp-form-options.php';
	}
	
	/**
	 * Registered Metaboxes Fields
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public static function ka4wp_api_settings() {
		include dirname(__FILE__).'/partials/ka4wp-admin-display.php';
	}
		
	/**
	 * Render taxonomy partners editor
	 *
	 * @since    1.2.0
	 * @param WP_Term $term Current taxonomy term object
	 * @param string $taxonomy Current taxonomy slug
	 *
	 * @return void
	 */
	public function ka4wp_edit_taxonomy_partners($term, $taxonomy) {
		include dirname(__FILE__).'/partials/ka4wp-edit-taxonomy-partners.php';
	}
	
	/**
	 * Manage taxonomy partners fields
	 *
	 * @since    1.2.0
	 * @param int $term_id Id of the given term
	 *
	 * @return void
	 */
	function ka4wp_save_custom_taxonomy_partners($term_id)
	{
		update_term_meta($term_id, 'logo_image_id',	sanitize_text_field($_POST['ka4wp_logo_image_id'] ?? get_term_meta($term_id, 'logo_image_id', true) ?: 0));	
	}
	
	/**
	 * Register settings for options page.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_register_settings() {
		
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_eventcategories', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_post_published']]);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_eventcategories_recurrence', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_cron_recurrence']]);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_keep_deleted_eventcategories', ['default' => 0, 'type' => 'integer']);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_impartingareas', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_post_published']]);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_impartingareas_recurrence', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_cron_recurrence']]);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_keep_deleted_impartingareas', ['default' => 0, 'type' => 'integer']);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_partners', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_post_published']]);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_partners_recurrence', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_cron_recurrence']]);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_keep_deleted_partners', ['default' => 0, 'type' => 'integer']);
		register_setting('ka4wp_settings_miscellaneous', 'ka4wp_prevent_deletion', ['default' => 0, 'type' => 'integer']);
		register_setting('ka4wp_settings_miscellaneous', 'ka4wp_partnerlogo_default', ['default' => 0, 'type' => 'integer']);
		
		add_settings_section(
				'ka4wp_settings_section_integrations', // section ID
				esc_html__('Integration settings', 'kultur-api-for-wp'), // title (optional)
				function(){ esc_html_e('Settings for background APIs and integrations','kultur-api-for-wp'); }, // callback function to display the section (optional) f.e. description
				'ka4wp_settings_integrations'
			);
			
		add_settings_field(
				'ka4wp_api_receive_eventcategories',
				esc_html__('Retrive event categories', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_publish_api'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'ka4wp_api_receive_eventcategories',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'ka4wp_api_receive_eventcategories',
				]
			);
			
		add_settings_field(
				'ka4wp_api_receive_eventcategories_recurrence',
				esc_html__('Recurrence of the API', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_cron_recurrence'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'ka4wp_api_receive_eventcategories_recurrence',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'ka4wp_api_receive_eventcategories_recurrence',
				]
			);
			
		add_settings_field(
				'ka4wp_api_keep_deleted_eventcategories',
				esc_html__('Should entries that are missing in the API be retained?', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_checkbox'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'ka4wp_api_keep_deleted_eventcategories',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'ka4wp_api_keep_deleted_eventcategories',
				]
			);
			
		add_settings_field(
				'ka4wp_api_receive_impartingareas',
				esc_html__('Retrive imparting areas', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_publish_api'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'ka4wp_api_receive_impartingareas',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'ka4wp_api_receive_impartingareas',
				]
			);
			
		add_settings_field(
				'ka4wp_api_receive_impartingareas_recurrence',
				esc_html__('Recurrence of the API', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_cron_recurrence'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'ka4wp_api_receive_impartingareas_recurrence',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'ka4wp_api_receive_impartingareas_recurrence',
				]
			);
			
		add_settings_field(
				'ka4wp_api_keep_deleted_impartingareas',
				esc_html__('Should entries that are missing in the API be retained?', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_checkbox'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'ka4wp_api_keep_deleted_impartingareas',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'ka4wp_api_keep_deleted_impartingareas',
				]
			);
			
		add_settings_field(
				'ka4wp_api_receive_partners',
				esc_html__('Retrive partners', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_publish_api'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'ka4wp_api_receive_partners',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'ka4wp_api_receive_partners',
				]
			);
			
		add_settings_field(
				'ka4wp_api_receive_partners_recurrence',
				esc_html__('Recurrence of the API', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_cron_recurrence'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'ka4wp_api_receive_partners_recurrence',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'ka4wp_api_receive_partners_recurrence',
				]
			);
			
		add_settings_field(
				'ka4wp_api_keep_deleted_partners',
				esc_html__('Should entries that are missing in the API be retained?', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_checkbox'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'ka4wp_api_keep_deleted_partners',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'ka4wp_api_keep_deleted_partners',
				]
			);
		
		add_settings_section(
				'ka4wp_settings_section_general', // section ID
				esc_html__('General settings', 'kultur-api-for-wp'),
				function(){ esc_html_e('Settings that otherwise have no place.','kultur-api-for-wp'); },
				'ka4wp_settings_general'
			);
			
		add_settings_section(
				'ka4wp_settings_section_logging', // section ID
				esc_html__('Logging settings', 'kultur-api-for-wp'),
				function(){ esc_html_e('Global settings around the topic of logging.','kultur-api-for-wp'); },
				'ka4wp_settings_logging'
			);
		
		//miscellaneous settings group
		add_settings_section(
				'ka4wp_settings_section_miscellaneous', // section ID
				esc_html__('Miscellaneous settings', 'kultur-api-for-wp'),
				function(){ esc_html_e('Miscellaneous settings relating to the Kultur-API integration','kultur-api-for-wp'); },
				'ka4wp_settings_miscellaneous'
			);
			
		add_settings_field(
				'ka4wp_prevent_deletion',
				esc_html__('Prevent deletion when uninstalling', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_checkbox'),
				'ka4wp_settings_miscellaneous',
				'ka4wp_settings_section_miscellaneous',
				[
					'label_for' => 'ka4wp_prevent_deletion',
					'option_group' => 'ka4wp_settings_miscellaneous',
					'name' => 'ka4wp_prevent_deletion',
				]
			);
			
		add_settings_field(
				'ka4wp_partnerlogo_default',
				esc_html__('Default logo for partners without own one', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_image_gallery_select'),
				'ka4wp_settings_miscellaneous',
				'ka4wp_settings_section_miscellaneous',
				[
					'label_for' => 'ka4wp_partnerlogo_default',
					'option_group' => 'ka4wp_settings_miscellaneous',
					'name' => 'ka4wp_partnerlogo_default',
				]
			);
	}
	
	/**
	 * Render possible posttypes from APIs
	 *
	 * @since    1.0.0
	 * @param array<string, string|int> $args Array with setting configuration data
	 *
	 * @return void
	 */
	public function ka4wp_settings_render_publish_api(array $args): void {
		
		$option = get_option($args['name'], '0');
	
		$posts = get_posts([
						'post_type' => 'ka4wp',
						'post_status' => 'publish',
						'numberposts' => -1,
						'order'    => 'ASC'
					]);
					
		$output = '<select id="ka4wp-settings-'.esc_html($args['name']).'" name="'.esc_html($args['name']).'">';
		$output .= '<option value="0" '.selected('0', $option, false).'>'.esc_html__('DISABLED', 'kultur-api-for-wp').'</option>';
			foreach($posts as $post)
			{
				$output .= '<option value="'.$post->ID.'" '.selected($post->ID, $option, false).'>'.esc_attr($post->post_title).'</option>';
			}
		$output .= '</select>';
		
		echo wp_kses($output, ['select' => ['id' => [], 'name' => []], 'option' => ['value' => [], 'selected' => []]]);
	}
	
	/**
	 * Render possible cron recurrence settings
	 *
	 * @since    1.0.0
	 * @param array<string, string|int> $args Array with setting configuration data
	 *
	 * @return void
	 */
	public function ka4wp_settings_render_cron_recurrence(array $args): void {
		
		$option = get_option($args['name'], '0');
		
		$output = '<select id="ka4wp-settings-'.esc_html($args['name']).'" name="'.esc_html($args['name']).'">';
		$output .= '<option value="hourly" '.selected('hourly', $option, false).'>'.esc_html__('hourly', 'kultur-api-for-wp').'</option>';
		$output .= '<option value="twicedaily" '.selected('twicedaily', $option, false).'>'.esc_html__('twice daily', 'kultur-api-for-wp').'</option>';
		$output .= '<option value="daily" '.selected('daily', $option, false).'>'.esc_html__('daily', 'kultur-api-for-wp').'</option>';
		$output .= '<option value="weekly" '.selected('weekly', $option, false).'>'.esc_html__('weekly', 'kultur-api-for-wp').'</option>';
		$output .= '</select>';
		
		echo wp_kses($output, ['select' => ['id' => [], 'name' => []], 'option' => ['value' => [], 'selected' => []]]);
	}
	
	/**
	 * Render image gallery select settings
	 *
	 * @since    1.2.0
	 * @param array<string, string|int> $args Array with setting configuration data
	 *
	 * @return void
	 */
	public function ka4wp_settings_render_image_gallery_select(array $args): void {
		
		$option = get_option($args['name'], '0');
		$image = wp_get_attachment_image_url(get_option($args['name'], '0'), 'medium');
		
		if(!empty($option) && !empty($image)) {
			$output = '<a href="#" class="ka4wp-upload-file">';
				$output .= '<img src="'.esc_url($image).'" />';
			$output .= '</a>';
			$output .= '<a href="#" class="ka4wp-remove-file">'.__('Remove image','kultur-api-for-wp').'</a>';
			$output .= '<input type="hidden" id="ka4wp-settings-'.esc_html($args['name']).'" name="'.esc_html($args['name']).'" value="'.absint($option).'">';
		} else {
			$output = '<a href="#" class="button ka4wp-upload-file">'.__('Upload image','kultur-api-for-wp').'</a>';
			$output .= '<a href="#" class="ka4wp-remove-file d-none">'.__('Remove image','kultur-api-for-wp').'</a>';
			$output .= '<input type="hidden" id="ka4wp-settings-'.esc_html($args['name']).'" name="'.esc_html($args['name']).'" value="">';
		}
		
		echo wp_kses($output, ['a' => ['href' => true, 'class' => true], 'img' => ['src' => []], 'input' => ['type' => [], 'id' => [], 'name' => [], 'value' => []]]);
	}
	
	/**
	 * Render simple checkbox settings
	 *
	 * @since    1.0.0
	 * @param array<string, string|int> $args Array with setting configuration data
	 *
	 * @return void
	 */
	public function ka4wp_settings_render_checkbox(array $args): void {
		
		$option = get_option($args['name']);
		
		$output = '<input type="checkbox" id="ka4wp-settings-'.esc_html($args['name']).'" name="'.esc_html($args['name']).'" value="1" '.checked('1', $option, false).'>';
		
		echo wp_kses($output, ['input' => ['id' => [], 'type' => [], 'value' => [], 'checked' => [], 'name' => []]]);
	}
	
	/**
	 * Validate settings if selected post is published
	 *
	 * @since    1.2.0
	 * @param string $input Setting value after changing
	 *
	 * @return int
	 */
	public function ka4wp_settings_validate_post_published($input): int {

		return ('publish' !== get_post_status(absint($input))) ? 0 : absint($input);
	}
	
	/**
	 * Validate settings if the cron recurrence is valid
	 *
	 * @since    1.2.0
	 * @param string $input Setting value after changing
	 *
	 * @return string
	 */
	public function ka4wp_settings_validate_cron_recurrence($input) {

		return in_array($input, ['hourly', 'twicedaily', 'daily', 'weekly']) ? sanitize_text_field($input) : 'daily';
	}
	
	/**
	 * Retrieve the all current Post API metadata
	 *
	 * @since     1.0.0
	 * @param int $post_id Id of the selected Post
	 *
	 * @return    array<string, string|int>
	 */
	public function ka4wp_get_api_options($post_id) {
		
		$options = [];
		if(!empty($post_id))
		{
			$options['ka4wp_api_type'] = get_post_meta(absint($post_id),'ka4wp_api_type',true);
			$options['ka4wp_api_key'] = get_post_meta(absint($post_id),'ka4wp_api_key',true);
			$options['ka4wp_base_url'] = get_post_meta(absint($post_id),'ka4wp_base_url',true);
			$options['ka4wp_basic_auth'] = get_post_meta(absint($post_id),'ka4wp_basic_auth',true);
			$options['ka4wp_bearer_auth'] = get_post_meta(absint($post_id),'ka4wp_bearer_auth',true);
			$options['ka4wp_input_type'] = get_post_meta(absint($post_id),'ka4wp_input_type',true);
			$options['ka4wp_method'] = get_post_meta(absint($post_id),'ka4wp_method',true);
			$options['ka4wp_form_field'] = get_post_meta(absint($post_id),'ka4wp_form_field',true);
			$options['ka4wp_header_request'] = get_post_meta(absint($post_id),'ka4wp_header_request',true);
		}

		return $options;
	}
	
	/**
	 * Update the Metaboxes value on Post Save
	 *
	 * @since    1.0.0
	 * @param int $post_id Id of the selected Post
	 * @param WP_Post $post Post object
	 * @param bool $update Whether this is an existing post being updated
	 *
	 * @return void
	 */
	public static function ka4wp_update_API_settings($post_id, $post, $update){
		if($post->post_type == 'ka4wp'){
			$status = 'false';
			if(isset($_POST['ka4wp_cpt_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ka4wp_cpt_nonce'])), 'ka4wp_cpt_nonce')){
				
				switch(sanitize_text_field($_POST['ka4wp_api_type']))
				{
					default:
						$options['ka4wp_api_key'] = sanitize_text_field($_POST['ka4wp_api_key']) ?: '';
					break;
					case 'wunsch.events':
						$options['ka4wp_api_key'] = sanitize_text_field($_POST['ka4wp_api_key']) ?: '';
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
					$response = update_post_meta(absint($post_id), $options_key, $options_value );
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
				}
			}
		}
	}
	
	/**
	 * Saves the API settings from the CF7 API Integrations Tab
	 *
	 * @since    1.0.0
	 * @param array<string, string> $properties Given properties
	 * @param WPCF7_ContactForm $contact_form Current contact form object
	 *
	 * @return array<string, string>
	 */
	public function add_contact_form_API_properties(array $properties, $contact_form): array { //@phpstan-ignore class.notFound

		$properties["ka4wp_api_integrations"] = get_post_meta(absint($contact_form->id()), '_ka4wp_api_integrations', true); //@phpstan-ignore class.notFound
		return $properties;
	}
	
	/**
	 * Saves the API settings from the CF7 API Integrations Tab
	 *
	 * @since    1.0.0
	 * @param WPCF7_ContactForm $contact_form Current contact form object
	 *
	 * @return void
	 */
	public function save_contact_form_api_details($contact_form) { //@phpstan-ignore class.notFound
		#if(isset($_POST['_wpnonce']) && wpcf7_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])))) // @phpstan-ignore function.notFound
		#{
			if(is_array($_POST['wpcf7-ka4wp']))
			{
				$properties = $contact_form->get_properties(); //@phpstan-ignore class.notFound
				
				$options = [];
				foreach($_POST['wpcf7-ka4wp'] as $key => $val)
				{
					$options[sanitize_text_field($key)] = sanitize_text_field($val);
				}
				$properties['ka4wp_api_integrations'] = $options;
				$contact_form->set_properties($properties); //@phpstan-ignore class.notFound
			}
		#}
	}
	
	/**
	 * Ajax Endpoint to load specific API details
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_get_selected_endpoint()
	{	
		$defaultsOptions = self::ka4wp_get_endpoint_defaults(absint($_POST['post_id']));
		
		$predefinedMappings = [];
		foreach($defaultsOptions as $typeKey => $typeValues)
		{
			if(!empty($typeValues['options']))
			{
				$predefinedMappings[] = ['name' => esc_attr($typeValues['name']), 'value' => esc_attr($typeKey)];
			}
		}
		
		wp_send_json(['mappings' => $predefinedMappings, 'api_type' => get_post_meta(absint($_POST['post_id']), 'ka4wp_api_type', true) ?: 'none', 'predefined' => $predefinedMappings ? 1 : 0]);
	}
	
	/**
	 * API Endpoint to load selected predefined mapping
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public static function ka4wp_get_predefined_mapping()
	{	
		$defaultsOptions = self::ka4wp_get_endpoint_defaults(absint($_POST['post_id']));
		
		$predefinedMappings = [];
		if(!empty($defaultsOptions[sanitize_text_field($_POST['mapping_key'])]['options']))
		{
			foreach($defaultsOptions[sanitize_text_field($_POST['mapping_key'])]['options'] as $typeKey => $typeValues)
			{
				$predefinedMappings[] = ['name' => esc_attr($typeValues['name']), 'value' => esc_attr($typeValues['value'])];
			}
		}
		
		wp_send_json(['mappings' => $predefinedMappings]);
	}
	
	/**
	 * API Endpoint to load selected predefined mapping
	 *
	 * @since    1.0.0
	 * @param int $post_id Id of the post to fetch data
	 *
	 * @return array
	 */
	public static function ka4wp_get_endpoint_defaults($post_id = 0)
	{			
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
							'http_method' => 'POST',
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
							'http_method' => 'POST',
							'options' => [
								['name' => 'Vorname', 'value' => 'firstname'],
								['name' => 'Nachname', 'value' => 'lastname'],
							]
						],
					'submit_organisationmember' => [
							'name' => esc_html__('Register as an organization member', 'kultur-api-for-wp'), 
							'description' => esc_html__('Interface for creating new members in the organization.', 'kultur-api-for-wp'),   
							'endpoint_path' => '/organizationmember/create',
							'http_method' => 'POST',
							'options' => [
								['name' => esc_html__('Company name', 'kultur-api-for-wp'), 'value' => 'organizationname'],
								['name' => esc_html__('Firstname', 'kultur-api-for-wp'), 'value' => 'firstname'],
								['name' => esc_html__('Lastname', 'kultur-api-for-wp'), 'value' => 'lastname'],
								['name' => esc_html__('Gender', 'kultur-api-for-wp'), 'value' => 'gender'],
								['name' => esc_html__('Email address', 'kultur-api-for-wp'), 'value' => 'email'],
								['name' => esc_html__('Street', 'kultur-api-for-wp'), 'value' => 'street'],
								['name' => esc_html__('Street number', 'kultur-api-for-wp'), 'value' => 'streetnumber'],
								['name' => esc_html__('Zip code', 'kultur-api-for-wp'), 'value' => 'zipcode'],
								['name' => esc_html__('City', 'kultur-api-for-wp'), 'value' => 'city'],
								['name' => esc_html__('Date of birth', 'kultur-api-for-wp'), 'value' => 'birthdate'],
								['name' => esc_html__('Fixed line number', 'kultur-api-for-wp'), 'value' => 'phone'],
								['name' => esc_html__('Mobile number', 'kultur-api-for-wp'), 'value' => 'mobilephone'],
								['name' => esc_html__('IBAN', 'kultur-api-for-wp'), 'value' => 'iban'],
								['name' => esc_html__('BIC', 'kultur-api-for-wp'), 'value' => 'bic'],
								['name' => esc_html__('Name of the Accountholder', 'kultur-api-for-wp'), 'value' => 'accountholder'],
								['name' => esc_html__('Bankname', 'kultur-api-for-wp'), 'value' => 'bankname'],
								['name' => esc_html__('Donation receipt', 'kultur-api-for-wp'), 'value' => 'donationreceipt'],
								['name' => esc_html__('Amount', 'kultur-api-for-wp'), 'value' => 'amount'],
								['name' => esc_html__('Maturity cycle', 'kultur-api-for-wp'), 'value' => 'maturitycycle'],
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
					'load_partners' => [
							'name' => esc_html__('Load partners', 'kultur-api-for-wp'), 
							'description' => esc_html__('Interface for querying partners.', 'kultur-api-for-wp'), 
							'endpoint_path' => '/partners/get', 
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
	 *
	 * @return void
	 */
	public function ka4wp_prepare_formdata_for_api()
	{	
		//prepare upload directory
		$uploadDir = wp_upload_dir();
		$pluginUploadDir = trailingslashit($uploadDir['basedir']).'ka4wp_temp';
		if (!is_dir($pluginUploadDir))
		{
			wp_mkdir_p($pluginUploadDir);
		}
		
		//prepare form fields
		$CF7Submission = WPCF7_Submission::get_instance(); //@phpstan-ignore class.notFound
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

		//prepare form details
		$form_properties = $ContactForm->get_properties();
		$form_fields = $ContactForm->scan_form_tags();
		$api_settings = $form_properties['ka4wp_api_integrations'] ?? [];
		
		//stop processing when api is disabled
		if(empty($api_settings) || empty($api_settings["send_to_api"]))
		{
			error_log('API-Data: API deaktiviert.');
			return;#TODO: Fehlerhandling fehlt
		}
		
		//save logs when enabled
		if(!empty($api_settings["logging"]))
		{
			error_log('API-Data: Logging aktiviert.');
			#TODO: Implement logging
		}
		
		if('publish' !== get_post_status($api_settings["apiendpoint"]))
		{
			error_log('API-Data: Schnittstelle nicht öffentlicht.');
			return; #TODO: Fehlerhandling fehlt
		}
		$posted_data['post_id'] = $api_settings["apiendpoint"];
		
		//handle uploaded files and prepare uploads for api transfer
		if(!empty($uploaded_files))
		{
			foreach($uploaded_files as $key => $file) {
				$fileExtension = pathinfo($file[0], PATHINFO_EXTENSION);
				$originalFilename = pathinfo($file[0], PATHINFO_FILENAME);
				$fileName = uniqid('ka4wp-').'.'.$fileExtension;
				copy($file[0], $pluginUploadDir.'/'.$fileName);	
				$posted_data['files'][$key] = ['filename' => $fileName, 'originalFilename' => $originalFilename, 'extension' => $fileExtension, 'file' => base64_encode(file_get_contents($file[0]))];
			}
		}
		
		$api_values = [];
		foreach($form_fields as $form_fields_value){
			if($form_fields_value->basetype != 'submit' && !empty($api_settings['mapping-'.$form_fields_value->raw_name]))
			{	
				if($form_fields_value->basetype == 'file')
				{
					$api_values['files'][$api_settings['mapping-'.$form_fields_value->raw_name]] = $posted_data['files'][$form_fields_value->raw_name] ?? [];
				} else {
					$api_values['data'][$api_settings['mapping-'.$form_fields_value->raw_name]] = $posted_data[$form_fields_value->raw_name] ?? '';
				}
			}
		}
		
		if(empty($api_values))
		{
			error_log('API-Data: Keine Werte vorhanden.');
			return; #TODO: Implement logging
		}
		
		self::ka4wp_send_lead($api_settings["apiendpoint"], $api_settings["predefined-mapping"] ?? '', $api_values);
		
		// delete uploaded files
		if(!empty($uploaded_files)){
			foreach($uploaded_files as $key => $file) {
				wp_delete_file( $pluginUploadDir.'/'.$posted_data['files'][$key]['filename']);
			}
		}
		
		error_log('API-Data: '.wp_json_encode($api_values));
		
		wp_reset_postdata();
	}
	
	/**
	 * Checks if email should skipped
	 *
	 * @since    1.0.0
	 * @param bool $skip_mail If mail sending should skipped
	 * @param WPCF7_ContactForm $contact_form Current contact form object
	 *
	 * @return bool
	 */
	public function ka4wp_check_skip_mail($skip_mail, $contact_form) { //@phpstan-ignore class.notFound
		
		$form_properties = $contact_form->get_properties(); //@phpstan-ignore class.notFound
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
	 * @param string $api_action Action that should performed
	 * @param string|int $postid Id of the given post
	 *
	 * @return array
	 */
	public static function ka4wp_api_options_wunschevents($api_action, $postid) {
		$api_options = array(
				'url'     		=> in_array(wp_get_development_mode(), ['plugin', 'all']) ? 'https://api.testserver.wunschevents.de/v1' : 'https://api.wunsch.events/v1',
				'input_type'	=> 'JSON',
				'headers'		=> ['Authorization' => 'Basic '.get_post_meta(absint($postid), 'ka4wp_api_key', true)],
				'auth_token'	=> get_post_meta(absint($postid), 'ka4wp_api_key', true),
			);
			
		$defaults = self::ka4wp_get_endpoint_defaults(absint($postid));
		$api_options['url'] .= $defaults[$api_action]['endpoint_path'];
		$api_options['http_method'] = $defaults[$api_action]['http_method'] ?? 'GET';
		
		return $api_options;
	}
	
	/**
	 * Child Fuction of specific form data send to the API
	 *
	 * @since    1.0.0
	 * @param int $post_id Id of the given post
	 * @param string $api_action API action that should performed
	 * @param array<string, string|int> $data Data to send via API
	 *
	 * @return mixed
	 */
	public static function ka4wp_send_lead($post_id, $api_action='', $data = [])
	{	
		$postid = absint($post_id);
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
        	$args['headers']['Authorization'] = 'Basic ' . base64_encode($api_options['basic_auth_username'] ?: ''.':'.$api_options['basic_auth_password'] ?: '');
      	}
      
      	if(!empty($api_options['bearer_auth'])){
    		$args['headers']['Authorization'] = 'Bearer ' . $api_options['bearer_auth'];
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

					if(is_wp_error($json)){ //@phpstan-ignore function.impossibleType
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
        	
        		if(is_wp_error($json)){ //@phpstan-ignore function.impossibleType
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
	 * @param WP_Error|array<string, string|array<string, string|int>> $response Data to get from api call
	 * @param int $log_id The id of the operation to log results
	 *
	 * @return array<string, string|bool> 
	 */
	public static function ka4wp_api_handle_result($response, $log_id = 0) {
		
		if(!is_wp_error($response)) //@phpstan-ignore function.impossibleType
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
