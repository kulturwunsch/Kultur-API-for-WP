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
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ka4wp-loader.php';

		/**
		 * Autoloader for namespaced classes (KA4WP\), e.g. the CRM adapters.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ka4wp-autoloader.php';
		KA4WP_Autoloader::register();

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
	 *
	 * @return void
	 */
	private function set_locale() {

		$plugin_i18n = new KA4WP_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function define_admin_hooks() {

		$plugin_admin = new KA4WP_Admin($this->get_ka4wp(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_notices', $plugin_admin, 'ka4wp_verify_dependencies');
		$this->loader->add_action('admin_notices', $plugin_admin, 'ka4wp_migration_notices');
		$this->loader->add_action('admin_notices', $plugin_admin, 'ka4wp_legacy_cf7_notice');
		$this->loader->add_action('init', $plugin_admin,'ka4wp_custom_post_type', 10);
		$this->loader->add_action('init', $plugin_admin,'ka4wp_register_eventcategories_taxonomy', 15);
		$this->loader->add_action('init', $plugin_admin,'ka4wp_register_impartingareas_taxonomy', 15);
		$this->loader->add_action('init', $plugin_admin,'ka4wp_register_partners_taxonomy', 15);
		$this->loader->add_action('admin_init', $plugin_admin,'ka4wp_register_settings');
		$this->loader->add_action('wp_connectors_init', $plugin_admin,'ka4wp_connectors_init');
		$this->loader->add_action('ka4wp/register_adapters', $plugin_admin,'ka4wp_register_core_adapters');
		$this->loader->add_action('admin_menu', $plugin_admin, 'ka4wp_register_submenu', 90);
		$this->loader->add_action('admin_init', $plugin_admin, 'ka4wp_update_plugin', 5);
		$this->loader->add_filter('parent_file', $plugin_admin, 'ka4wp_taxonomy_parent_file');
		$this->loader->add_filter('submenu_file', $plugin_admin, 'ka4wp_taxonomy_submenu_file');
		$this->loader->add_action('created_partners',$plugin_admin,'ka4wp_save_custom_taxonomy_partners');
		$this->loader->add_action('edited_partners',$plugin_admin,'ka4wp_save_custom_taxonomy_partners');
		
		$this->loader->add_action('add_option_ka4wp_api_receive_eventcategories',$plugin_admin,'ka4wp_create_settings_api_receive_eventcategories', 10, 1);
		$this->loader->add_action('update_option_ka4wp_api_receive_eventcategories',$plugin_admin,'ka4wp_update_settings_api_receive_eventcategories', 10, 2);
		$this->loader->add_action('add_option_ka4wp_api_receive_eventcategories_recurrence',$plugin_admin,'ka4wp_create_settings_api_receive_eventcategories_recurrence', 10, 1);
		$this->loader->add_action('update_option_ka4wp_api_receive_eventcategories_recurrence',$plugin_admin,'ka4wp_update_settings_api_receive_eventcategories_recurrence', 10, 2);
		$this->loader->add_action('add_option_ka4wp_api_receive_impartingareas',$plugin_admin,'ka4wp_create_settings_api_receive_impartingareas', 10, 1);
		$this->loader->add_action('update_option_ka4wp_api_receive_impartingareas',$plugin_admin,'ka4wp_update_settings_api_receive_impartingareas', 10, 2);
		$this->loader->add_action('add_option_ka4wp_api_receive_impartingareas_recurrence',$plugin_admin,'ka4wp_create_settings_api_receive_impartingareas_recurrence', 10, 1);
		$this->loader->add_action('update_option_ka4wp_api_receive_impartingareas_recurrence',$plugin_admin,'ka4wp_update_settings_api_receive_impartingareas_recurrence', 10, 2);
		
		$this->loader->add_action('add_option_ka4wp_api_receive_partners',$plugin_admin,'ka4wp_create_settings_api_receive_partners', 10, 1);
		$this->loader->add_action('update_option_ka4wp_api_receive_partners',$plugin_admin,'ka4wp_update_settings_api_receive_partners', 10, 2);
		$this->loader->add_action('add_option_ka4wp_api_receive_partners_recurrence',$plugin_admin,'ka4wp_create_settings_api_receive_partners_recurrence', 10, 1);
		$this->loader->add_action('update_option_ka4wp_api_receive_partners_recurrence',$plugin_admin,'ka4wp_update_settings_api_receive_partners_recurrence', 10, 2);
		
		#Fluent Forms
		$this->loader->add_action('fluentform/loaded',$plugin_admin,'ka4wp_init_fluentforms',10);

		
		#CRON
		$this->loader->add_action('ka4wp_cron_api_update_eventcategories',$plugin_admin,'ka4wp_api_request_update_eventcategories', 5);
		$this->loader->add_action('ka4wp_cron_api_update_impartingareas',$plugin_admin,'ka4wp_api_request_update_impartingareas', 5);
		$this->loader->add_action('ka4wp_cron_api_update_partners',$plugin_admin,'ka4wp_api_request_update_partners', 5);
		
		$this->loader->add_filter('plugin_action_links',$plugin_admin,'ka4wp_add_settings_link',10,2);
        $this->loader->add_filter("partners_edit_form_fields",$plugin_admin,'ka4wp_edit_taxonomy_partners', 10, 2);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function define_public_hooks() {

		$plugin_public = new KA4WP_Public( $this->get_ka4wp(), $this->get_version() );

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		add_shortcode( 'ka4wp_partners', [$plugin_public, 'ka4wp_load_shortcode_partners'] );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 *
	 * @return void
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
	 *
	 * @return    KA4WP_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	/**
	 * Save debug logs
	 *
	 * @since     1.0.0
	 * @param string $content Value to be logged
	 *
	 * @return void
	 */
	public static function debug_log($content) {
		
		$debug = in_array(wp_get_development_mode(), ['plugin', 'all'], true)
			|| (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG);
		
		if($debug)
		{
			error_log('[KA4WP]: ' . (is_string($content) ? $content : wp_json_encode($content)));
		}
	}
	
}
