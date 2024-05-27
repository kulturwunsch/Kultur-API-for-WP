<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://kulturwunsch.de
 * @since      1.0.0
 *
 * @package    KA4WP
 * @subpackage KA4WP/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    KA4WP
 * @subpackage KA4WP/includes
 * @author     Kulturwunsch Wolfenbüttel e. V. <info@kulturwunsch.de>
 */
class KA4WP {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      KA4WP_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $ka4wp    The string used to uniquely identify this plugin.
	 */
	protected $ka4wp;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'KA4WP_VERSION' ) ) {
			$this->version = KA4WP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->ka4wp = 'ka4wp';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - KA4WP_Loader. Orchestrates the hooks of the plugin.
	 * - KA4WP_i18n. Defines internationalization functionality.
	 * - KA4WP_Admin. Defines all hooks for the admin area.
	 * - KA4WP_Cron. Orchestrates scheduling and un-scheduling cron jobs.
	 * - KA4WP_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ka4wp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ka4wp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ka4wp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ka4wp-public.php';

		$this->loader = new KA4WP_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the KA4WP_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new KA4WP_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		#$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new KA4WP_Admin($this->get_ka4wp(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_notices', $plugin_admin, 'ka4wp_verify_dependencies');
		$this->loader->add_action('init', $plugin_admin,'ka4wp_custom_post_type', 10);
		$this->loader->add_action('init', $plugin_admin,'ka4wp_register_eventcategories_taxonomy', 15);
		$this->loader->add_action('init', $plugin_admin,'ka4wp_register_impartingareas_taxonomy', 15);
		$this->loader->add_action('admin_init', $plugin_admin,'ka4wp_register_settings');
		$this->loader->add_action('save_post_ka4wp',$plugin_admin,'ka4wp_update_API_settings', 10, 3);
		$this->loader->add_action('admin_menu', $plugin_admin, 'ka4wp_register_submenu', 90);
		$this->loader->add_action('admin_init', $plugin_admin, 'ka4wp_update_plugin', 5);
		$this->loader->add_action('add_meta_boxes', $plugin_admin,'ka4wp_metabox');
		$this->loader->add_action('wpcf7_before_send_mail',$plugin_admin,'ka4wp_prepare_formdata_for_api');
		
		$this->loader->add_action('add_option_ka4wp_api_receive_eventcategories',$plugin_admin,'ka4wp_create_settings_api_receive_eventcategories', 10, 1);
		$this->loader->add_action('update_option_ka4wp_api_receive_eventcategories',$plugin_admin,'ka4wp_update_settings_api_receive_eventcategories', 10, 2);
		$this->loader->add_action('add_option_ka4wp_api_receive_eventcategories_recurrence',$plugin_admin,'ka4wp_create_settings_api_receive_eventcategories_recurrence', 10, 1);
		$this->loader->add_action('update_option_ka4wp_api_receive_eventcategories_recurrence',$plugin_admin,'ka4wp_update_settings_api_receive_eventcategories_recurrence', 10, 2);
		$this->loader->add_action('add_option_ka4wp_api_receive_impartingareas',$plugin_admin,'ka4wp_create_settings_api_receive_impartingareas', 10, 1);
		$this->loader->add_action('update_option_ka4wp_api_receive_impartingareas',$plugin_admin,'ka4wp_update_settings_api_receive_impartingareas', 10, 2);
		$this->loader->add_action('add_option_ka4wp_api_receive_impartingareas_recurrence',$plugin_admin,'ka4wp_create_settings_api_receive_impartingareas_recurrence', 10, 1);
		$this->loader->add_action('update_option_ka4wp_api_receive_impartingareas_recurrence',$plugin_admin,'ka4wp_update_settings_api_receive_impartingareas_recurrence', 10, 2);
		
		$this->loader->add_action('wp_ajax_ka4wp_get_selected_endpoint',$plugin_admin,'ka4wp_get_selected_endpoint',10);
		$this->loader->add_action('wp_ajax_ka4wp_get_predefined_mapping',$plugin_admin,'ka4wp_get_predefined_mapping',10);

		
		#CRON
		$this->loader->add_action('ka4wp_cron_api_update_eventcategories',$plugin_admin,'ka4wp_api_request_update_eventcategories', 5);
		$this->loader->add_action('ka4wp_cron_api_update_impartingareas',$plugin_admin,'ka4wp_api_request_update_impartingareas', 5);
		
		$this->loader->add_filter('plugin_action_links',$plugin_admin,'ka4wp_add_settings_link',10,2);
        $this->loader->add_filter('wpcf7_editor_panels',$plugin_admin,'ka4wp_cf7_add_api_integration', 1, 1); // adds another tab to contact form 7 screen
        $this->loader->add_filter('wpcf7_skip_mail',$plugin_admin,'ka4wp_check_skip_mail', 10, 2);
		$this->loader->add_action("wpcf7_save_contact_form",$plugin_admin,'save_contact_form_API_details', 10, 1); //save contact form api integrations
        $this->loader->add_filter("wpcf7_contact_form_properties",$plugin_admin,'add_contact_form_API_properties', 10, 2); // add contact form properties
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new KA4WP_Public( $this->get_ka4wp(), $this->get_version() );

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('wpcf7_form_tag_data_option', $plugin_public, 'ka4wP_load_cf7_custom_options', 10, 3); // load taxonomy for select/checkbox/radio elements

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_ka4wp() {
		return $this->ka4wp;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    KA4WP_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	/**
	 * Save debug logs
	 *
	 * @since     1.0.0
	 */
	public static function debug_log($content) {
		
		if(in_array(wp_get_development_mode(), ['plugin', 'all']) || WP_DEBUG_LOG == true || WP_DEBUG == true)
		{
			error_log('[KA4WP]: '.$content);
		}
	}
	
	/**
	 * Retrieve the all current Post API metadata
	 *
	 * @since     1.0.0
	 * @return    array
	 */
	public function ka4wp_get_api_options($post_id) {
		global $post;
		$post_id = $post->ID;
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
	
}
