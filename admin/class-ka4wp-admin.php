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
		
		if (version_compare($oldVersion, $this->version) >= 0) {
			return;
		}
		
		\KA4WP\Migration\Migrator::run($oldVersion, $this->version);
		
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
	 * Initialize the Fluent Forms integration
	 *
	 * @since    2.0.0
	 * @param mixed $app The current Fluent Forms Instance
	 *
	 * @return void
	 */
	public function ka4wp_init_fluentforms($app) {
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/fluentforms/Bootstrap.php';
		new \KA4WP\FluentFormIntegration($app);
	}
	
	/**
	 * Register the adapters shipped with the plugin
	 *
	 * @since    2.0.0
	 * @param \KA4WP\Adapters\AdapterRegistry $registry The adapter registry
	 *
	 * @return void
	 */
	public function ka4wp_register_core_adapters($registry) {
		
		$registry->register(new \KA4WP\Adapters\WunschEventsAdapter());
	}
	
	/**
	 * Register a connector for every adapter that supports the Connectors screen
	 *
	 * @since    2.0.0
	 * @param WP_Connector_Registry $registry The current connector registry instance
	 *
	 * @return void
	 */
	public function ka4wp_connectors_init($registry) {
		
		foreach (\KA4WP\Adapters\AdapterRegistry::instance()->all() as $adapter) {
			if (!$adapter instanceof \KA4WP\Adapters\AbstractAdapter) {
				continue;
			}
			
			$definition = $adapter->get_connector_definition();
			$connectorId = 'ka4wp_' . $adapter->get_id();
			if ($definition === null || $registry->is_registered($connectorId)) {
				continue;
			}
			
			$registry->register($connectorId, $definition);
		}
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
		
		if($oldValue != $newValue && !empty(get_option('ka4wp_api_receive_eventcategories', '')))
		{
			wp_clear_scheduled_hook('ka4wp_cron_api_update_eventcategories');
			wp_schedule_event(time(), $newValue ?: 'daily', 'ka4wp_cron_api_update_eventcategories');
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
		
		if($oldValue != $newValue && !empty(get_option('ka4wp_api_receive_impartingareas', '')))
		{
			wp_clear_scheduled_hook('ka4wp_cron_api_update_impartingareas');
			wp_schedule_event(time(), $newValue ?: 'daily', 'ka4wp_cron_api_update_impartingareas');
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
		
		if($oldValue != $newValue && !empty(get_option('ka4wp_api_receive_partners', '')))
		{
			wp_clear_scheduled_hook('ka4wp_cron_api_update_partners');
			wp_schedule_event(time(), $newValue ?: 'daily', 'ka4wp_cron_api_update_partners');
		}
	}
	
	/**
	 * Load data from the active CRM adapter if the sync of the given setting is enabled
	 *
	 * @since    2.0.0
	 * @param string $optionName Name of the enable setting
	 * @param string $endpointKey Adapter endpoint to load
	 *
	 * @return array<mixed>|null Response body or null if disabled or failed
	 */
	private function ka4wp_load_from_adapter(string $optionName, string $endpointKey): ?array {
		
		if(empty(get_option($optionName, ''))) {
			return null;
		}
		
		$adapter = \KA4WP\Adapters\AdapterRegistry::instance()->get_active();
		if(!$adapter) {
			return null;
		}
		
		$response = $adapter->request($endpointKey);
		if(is_wp_error($response) || empty($response['body']) || !is_array($response['body'])) {
			return null;
		}
		
		return $response['body'];
	}
	
	/**
	 * Saves the API response event categories in taxonomy database
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_api_request_update_eventcategories() {
		
		$body = $this->ka4wp_load_from_adapter('ka4wp_api_receive_eventcategories', 'load_eventcategories');
		if($body !== null) {
			self::ka4wp_api_response_update_eventcategories($body);
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
		
		$body = $this->ka4wp_load_from_adapter('ka4wp_api_receive_impartingareas', 'load_impartingareas');
		if($body !== null) {
			self::ka4wp_api_response_update_impartingareas($body);
		}
	}
	
	/**
	 * Saves the API response partners in taxonomy database
	 *
	 * @since    1.2.0
	 *
	 * @return void
	 */
	public function ka4wp_api_request_update_partners() {
		
		$body = $this->ka4wp_load_from_adapter('ka4wp_api_receive_partners', 'load_partners');
		if($body !== null) {
			self::ka4wp_api_response_update_partners($body);
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
	 * Show hints that need the attention of the admin after the migration
	 *
	 * @since    2.0.0
	 *
	 * @return void
	 */
	public function ka4wp_migration_notices() {
		
		if (!current_user_can('manage_options')) {
			return;
		}
		
		$optionName = \KA4WP\Migration\Migrator::OPTION_EXTRA_ACCOUNTS;
		
		if (isset($_GET['ka4wp_dismiss_migration']) && isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'ka4wp_dismiss_migration')) {
			delete_option($optionName);
			return;
		}
		
		$accounts = get_option($optionName, []);
		if (empty($accounts) || !is_array($accounts)) {
			return;
		}
		
		$titles = [];
		foreach ($accounts as $account) {
			$titles[] = $account['title'] ?: '#' . $account['id'];
		}
		
		$dismissUrl = wp_nonce_url(add_query_arg('ka4wp_dismiss_migration', '1'), 'ka4wp_dismiss_migration');
		
		echo '<div class="notice notice-warning"><p>' . esc_html__('Kultur-API for WordPress now supports one account per CRM. The following legacy API entries use a different API key and were not migrated:', 'kultur-api-for-wp') . ' <strong>' . esc_html(implode(', ', $titles)) . '</strong> <a href="' . esc_url($dismissUrl) . '">' . esc_html__('Dismiss', 'kultur-api-for-wp') . '</a></p></div>';
	}
	
	/**
	 * Warn about migrated Contact Form 7 forms with a legacy integration
	 *
	 * @since    2.0.0
	 *
	 * @return void
	 */
	public function ka4wp_legacy_cf7_notice() {
		
		if (!current_user_can('manage_options') || !class_exists('WPCF7_ContactForm')) {
			return;
		}
		
		$optionName = \KA4WP\Migration\Migrator::OPTION_LEGACY_CF7_FORMS;
		
		if (isset($_GET['ka4wp_dismiss_cf7']) && isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'ka4wp_dismiss_cf7')) {
			delete_option($optionName);
			return;
		}
		
		$formIds = get_option($optionName, []);
		if (empty($formIds) || !is_array($formIds)) {
			return;
		}
		
		$links = [];
		foreach ($formIds as $formId) {
			$title = get_the_title((int) $formId);
			if ($title === '') {
				continue; // form no longer exists
			}
			$links[] = '<a href="' . esc_url(admin_url('admin.php?page=wpcf7&post=' . (int) $formId . '&action=edit')) . '">' . esc_html($title) . '</a>';
		}
		
		if (empty($links)) {
			delete_option($optionName);
			return;
		}
		
		$dismissUrl = wp_nonce_url(add_query_arg('ka4wp_dismiss_cf7', '1'), 'ka4wp_dismiss_cf7');
		
		echo '<div class="notice notice-warning"><p><strong>' . esc_html__('Kultur-API for WordPress: Contact Form 7 submissions are no longer sent to the CRM.', 'kultur-api-for-wp') . '</strong> '
			. esc_html__('Please recreate the following forms as Fluent Forms feeds with the Kultur-API integration:', 'kultur-api-for-wp') . ' '
			. wp_kses(implode(', ', $links), ['a' => ['href' => []]])
			. ' <a href="' . esc_url($dismissUrl) . '">' . esc_html__('Dismiss', 'kultur-api-for-wp') . '</a></p></div>';
	}
	
	/**
	 * Check Plugin Dependencies
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_verify_dependencies(){
		if(!defined('FLUENTFORM')){
			echo '<div class="notice notice-warning is-dismissible"><p>'.esc_html__( 'Kultur-API for WordPress integrations requires the Fluent Forms plugin to be installed and active.', 'kultur-api-for-wp' ).'</p></div>';
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
			'show_ui' => false,
			'show_in_menu' => false,
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
			$url = admin_url('admin.php?page=ka4wp_settings');
			$documentation = admin_url('admin.php?page=ka4wp_docs');
			$links = (array) $links;
			$links[] = sprintf('<a href="%s">%s</a>', $url, __('Settings','kultur-api-for-wp'));
			$links[] = sprintf('<a href="%s">%s</a>', $documentation, __('Documentation','kultur-api-for-wp'));
		}
		return $links;
	}
	
	/**
	 * Register the plugin admin menu and its pages.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function ka4wp_register_submenu(){
		add_menu_page(
			__('Kultur-API', 'kultur-api-for-wp'),
			__('Kultur-API', 'kultur-api-for-wp'),
			'edit_posts',
			'ka4wp',
			[$this, 'ka4wp_menu_callback'],
			'dashicons-tickets'
		);

		add_submenu_page(
	        'ka4wp',
	        __('Settings', 'kultur-api-for-wp'),
	        __('Settings', 'kultur-api-for-wp'),
	        'manage_options',
	        'ka4wp_settings',
	        array(&$this,'ka4wp_submenu_settings_callback')
	    );

	    add_submenu_page(
	        'ka4wp',
	        __('Documentation', 'kultur-api-for-wp'),
	        __('Documentation', 'kultur-api-for-wp'),
	        'manage_options',
	        'ka4wp_docs',
	        array(&$this,'ka4wp_submenu_docs_callback')
	    );

		add_submenu_page(
			'ka4wp',
			__('Event categories', 'kultur-api-for-wp'),
			__('Event categories', 'kultur-api-for-wp'),
			'manage_categories',
			'edit-tags.php?taxonomy=eventcategories&post_type=ka4wp'
		);

		add_submenu_page(
			'ka4wp',
			__('Imparting areas', 'kultur-api-for-wp'),
			__('Imparting areas', 'kultur-api-for-wp'),
			'manage_categories',
			'edit-tags.php?taxonomy=impartingareas&post_type=ka4wp'
		);

		add_submenu_page(
			'ka4wp',
			__('Partners', 'kultur-api-for-wp'),
			__('Partners', 'kultur-api-for-wp'),
			'manage_categories',
			'edit-tags.php?taxonomy=partners&post_type=ka4wp'
		);
	}

	public function ka4wp_menu_callback(): void {
		echo '<div class="wrap"><h1>' . esc_html__('Kultur-API', 'kultur-api-for-wp') . '</h1>';
		echo '<p>' . esc_html__('CRM connections are managed in WordPress Connectors. Use the submenu to manage plugin settings and synchronized data.', 'kultur-api-for-wp') . '</p></div>';
	}

	/**
	 * Keep taxonomy pages under the Kultur-API menu although the legacy CPT editor is hidden.
	 *
	 * @param string $parentFile Current parent menu file
	 *
	 * @return string
	 */
	public function ka4wp_taxonomy_parent_file(string $parentFile): string {
		$screen = get_current_screen();
		$taxonomies = ['eventcategories', 'impartingareas', 'partners'];

		if ($screen && in_array($screen->taxonomy, $taxonomies, true)) {
			return 'ka4wp';
		}

		return $parentFile;
	}

	/**
	 * Highlight the currently open taxonomy submenu.
	 *
	 * @param string|null $submenuFile Current submenu file
	 *
	 * @return string|null
	 */
	public function ka4wp_taxonomy_submenu_file($submenuFile) {
		$screen = get_current_screen();
		$taxonomies = ['eventcategories', 'impartingareas', 'partners'];

		if (!$screen || !in_array($screen->taxonomy, $taxonomies, true)) {
			return $submenuFile;
		}

		return 'edit-tags.php?taxonomy=' . $screen->taxonomy . '&post_type=ka4wp';
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
		
		register_setting('ka4wp_settings_integrations', 'ka4wp_crm_type', ['default' => \KA4WP\Adapters\AdapterRegistry::DEFAULT_ADAPTER, 'type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_crm_type']]);
		
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_eventcategories', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_flag']]);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_eventcategories_recurrence', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_cron_recurrence']]);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_keep_deleted_eventcategories', ['default' => 0, 'type' => 'integer']);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_impartingareas', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_flag']]);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_impartingareas_recurrence', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_cron_recurrence']]);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_keep_deleted_impartingareas', ['default' => 0, 'type' => 'integer']);
		register_setting('ka4wp_settings_integrations', 'ka4wp_api_receive_partners', ['type' => 'string', 'sanitize_callback' => [$this, 'ka4wp_settings_validate_flag']]);
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
				'ka4wp_crm_type',
				esc_html__('CRM system', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_crm_type'),
				'ka4wp_settings_integrations',
				'ka4wp_settings_section_integrations',
				[
					'label_for' => 'ka4wp_crm_type',
					'option_group' => 'ka4wp_settings_integrations',
					'name' => 'ka4wp_crm_type',
				]
			);
			
		add_settings_field(
				'ka4wp_api_receive_eventcategories',
				esc_html__('Retrive event categories', 'kultur-api-for-wp'),
				array($this, 'ka4wp_settings_render_checkbox'),
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
				array($this, 'ka4wp_settings_render_checkbox'),
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
				array($this, 'ka4wp_settings_render_checkbox'),
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
	 * Render the CRM system selection
	 *
	 * @since    2.0.0
	 * @param array<string, string|int> $args Array with setting configuration data
	 *
	 * @return void
	 */
	public function ka4wp_settings_render_crm_type(array $args): void {
		
		$active = (string) get_option($args['name'], \KA4WP\Adapters\AdapterRegistry::DEFAULT_ADAPTER);
		
		echo '<select id="' . esc_attr($args['name']) . '" name="' . esc_attr($args['name']) . '">';
		foreach (\KA4WP\Adapters\AdapterRegistry::instance()->all() as $adapter) {
			echo '<option value="' . esc_attr($adapter->get_id()) . '" ' . selected($adapter->get_id(), $active, false) . '>' . esc_html($adapter->get_label()) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . wp_kses(
			sprintf(
				/* translators: %s: link to the connectors screen */
				__('Enter the API key under %s.', 'kultur-api-for-wp'),
				'<a href="' . esc_url(admin_url('options-connectors.php')) . '">' . esc_html__('Settings > Connectors', 'kultur-api-for-wp') . '</a>'
			),
			['a' => ['href' => []]]
		) . '</p>';
	}
	
	/**
	 * Only allow registered adapters as CRM type
	 *
	 * @since    2.0.0
	 * @param mixed $input Submitted value
	 *
	 * @return string
	 */
	public function ka4wp_settings_validate_crm_type($input): string {
		
		$input = sanitize_key((string) $input);
		
		return \KA4WP\Adapters\AdapterRegistry::instance()->get($input) ? $input : \KA4WP\Adapters\AdapterRegistry::DEFAULT_ADAPTER;
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
	 * Validate checkbox settings, returns "1" or an empty string
	 *
	 * @since    2.0.0
	 * @param mixed $input Setting value after changing
	 *
	 * @return string
	 */
	public function ka4wp_settings_validate_flag($input): string {

		return empty($input) ? '' : '1';
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
		if (get_post_meta($post_id, 'ka4wp_api_type', true) !== 'wunsch.events') {
			return $options;
		}

		// Deprecated since 2.0.0: legacy format built from the adapter definition
		$adapter = \KA4WP\Adapters\AdapterRegistry::instance()->get('wunsch_events');
		foreach ($adapter ? $adapter->get_endpoints() : [] as $key => $endpoint) {
			$options[$key] = [
				'name' => $endpoint['name'],
				'description' => $endpoint['description'] ?? '',
				'endpoint_path' => $endpoint['path'],
				'http_method' => $endpoint['method'] ?? 'GET',
				'options' => $endpoint['fields'] ?? [],
			];
		}

		return $options;
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
		_deprecated_function(__METHOD__, '2.0.0', 'KA4WP\Adapters\AdapterInterface');
		
		$adapter = \KA4WP\Adapters\AdapterRegistry::instance()->get('wunsch_events');
		$credentials = $adapter ? $adapter->get_credentials() : [];
		$endpoints = $adapter ? $adapter->get_endpoints() : [];
		
		return array(
			'url'         => ($adapter ? $adapter->get_base_url() : '') . ($endpoints[$api_action]['path'] ?? ''),
			'input_type'  => 'JSON',
			'headers'     => $adapter ? ['Authorization' => 'Basic ' . ($credentials['api_key'] ?? '')] : [],
			'auth_token'  => $credentials['api_key'] ?? '',
			'http_method' => $endpoints[$api_action]['method'] ?? 'GET',
		);
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

		if ('wunsch.events' !== get_post_meta($postid, 'ka4wp_api_type', true)) {
			return ['success' => false, 'error' => esc_html__('The selected API is not available.', 'kultur-api-for-wp')];
		}

		$adapter = \KA4WP\Adapters\AdapterRegistry::instance()->get('wunsch_events');
		if (!$adapter) {
			return ['success' => false, 'error' => esc_html__('The selected API is not available.', 'kultur-api-for-wp')];
		}
		
		$result = $adapter->request((string) $api_action, (array) $data);
		if (is_wp_error($result)) {
			$errorData = $result->get_error_data();
			return [
				'success' => false,
				'error'   => $result->get_error_message(),
				'body'    => is_array($errorData) ? ($errorData['body'] ?? null) : null,
			];
		}
		
		return ['success' => true, 'response' => ['code' => $result['code']], 'body' => $result['body']];
	}

}
