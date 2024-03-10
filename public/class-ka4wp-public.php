<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://kulturwunsch.de
 * @since      1.0.0
 *
 * @package    KA4WP
 * @subpackage KA4WP/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    KA4WP
 * @subpackage KA4WP/public
 * @author     Kulturwunsch Wolfenbüttel e. V. <info@kulturwunsch.de>
 */
class KA4WP_Public {

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
	 * @param      string    $ka4wp       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($ka4wp, $version) {

		$this->ka4wp = $ka4wp;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->ka4wp, plugin_dir_url( __FILE__ ) . 'css/ka4wp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->ka4wp, plugin_dir_url( __FILE__ ) . 'js/ka4wp-public.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * Load custom options for contact form 7 option fields
	 *
	 * @since     1.0.0
	 * @return    array
	 */
	public function ka4wP_load_cf7_custom_options($data, $options, $args) {
		$data = [];
		foreach ($options as $option) {
			if ($option === 'kulturapi_eventcategories') {
				
				$terms = get_terms([
								'taxonomy' => 'eventcategories',
								'hide_empty' => false,
							]);
				$data = array_merge($data, array_map('esc_attr', array_column($terms, 'name')));
			} elseif($option === 'kulturapi_impartingareas') {
				
				$terms = get_terms([
								'taxonomy' => 'impartingareas',
								'hide_empty' => false,
							]);
				$data = array_merge($data, array_map('esc_attr', array_column($terms, 'name')));
			}
		}
		return $data;
	}

}
