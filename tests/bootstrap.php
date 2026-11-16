<?php

if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/wordpress/');
}

$GLOBALS['ka4wp_test_options'] = [];
$GLOBALS['ka4wp_test_post_meta'] = [];
$GLOBALS['ka4wp_test_posts'] = [];
$GLOBALS['ka4wp_test_cf7_forms'] = [];
$GLOBALS['ka4wp_test_actions'] = [];
$GLOBALS['ka4wp_test_http_handler'] = null;
$GLOBALS['ka4wp_test_http_response'] = null;
$GLOBALS['ka4wp_test_terms'] = [];
$GLOBALS['ka4wp_test_current_screen'] = null;

class WP_Error
{
	private $code;
	private $message;
	private $data;

	public function __construct($code = '', $message = '', $data = null)
	{
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}

	public function get_error_code()
	{
		return $this->code;
	}

	public function get_error_message()
	{
		return $this->message;
	}

	public function get_error_data()
	{
		return $this->data;
	}
}

class WP_Filesystem_Direct
{
	public function __construct($arg = false)
	{
	}

	public function delete($file, $recursive = false, $type = false)
	{
		return true;
	}
}

class KA4WP
{
	public static $test_logs = [];

	public static function debug_log($message)
	{
		self::$test_logs[] = $message;
	}
}

function __($text, $domain = null)
{
	return $text;
}

function get_option($name, $default = false)
{
	return array_key_exists($name, $GLOBALS['ka4wp_test_options'])
		? $GLOBALS['ka4wp_test_options'][$name]
		: $default;
}

function update_option($name, $value, $autoload = null)
{
	$GLOBALS['ka4wp_test_options'][$name] = $value;
	return true;
}

function add_option($name, $value = '', $deprecated = '', $autoload = 'yes')
{
	if (array_key_exists($name, $GLOBALS['ka4wp_test_options'])) {
		return false;
	}
	$GLOBALS['ka4wp_test_options'][$name] = $value;
	return true;
}

function get_posts($args = [])
{
	if (($args['post_type'] ?? '') === 'ka4wp') {
		return $GLOBALS['ka4wp_test_posts'];
	}
	if (($args['post_type'] ?? '') === 'wpcf7_contact_form') {
		return $GLOBALS['ka4wp_test_cf7_forms'];
	}
	return [];
}

function get_post_meta($postId, $key, $single = false)
{
	return $GLOBALS['ka4wp_test_post_meta'][$postId][$key] ?? '';
}

function wp_upload_dir()
{
	return ['basedir' => sys_get_temp_dir()];
}

function trailingslashit($value)
{
	return rtrim($value, '/\\') . DIRECTORY_SEPARATOR;
}

function absint($value)
{
	return abs((int) $value);
}

function do_action($hook, ...$args)
{
	foreach ($GLOBALS['ka4wp_test_actions'][$hook] ?? [] as $callback) {
		call_user_func_array($callback, $args);
	}
}

function add_action($hook, $callback)
{
	$GLOBALS['ka4wp_test_actions'][$hook][] = $callback;
}

function wp_get_development_mode()
{
	return $GLOBALS['ka4wp_test_development_mode'] ?? '';
}

function apply_filters($hook, $value, ...$args)
{
	return $value;
}

function untrailingslashit($value)
{
	return rtrim($value, '/\\');
}

function wp_remote_request($url, $args = [])
{
	if (is_callable($GLOBALS['ka4wp_test_http_handler'])) {
		return call_user_func($GLOBALS['ka4wp_test_http_handler'], $url, $args);
	}
	return $GLOBALS['ka4wp_test_http_response'];
}

function is_wp_error($thing)
{
	return $thing instanceof WP_Error;
}

function wp_remote_retrieve_response_code($response)
{
	return $response['response']['code'] ?? 0;
}

function wp_remote_retrieve_body($response)
{
	return $response['body'] ?? '';
}

function wp_json_encode($value)
{
	return json_encode($value);
}

function get_bloginfo($show = '')
{
	return 'test';
}

function home_url($path = '')
{
	return 'https://example.test' . $path;
}

function add_query_arg($args, $url)
{
	return $url . '?' . http_build_query($args);
}

function rawurlencode_deep($value)
{
	if (is_array($value)) {
		return array_map('rawurlencode_deep', $value);
	}
	return rawurlencode((string) $value);
}

function shortcode_atts($pairs, $atts, $shortcode = '')
{
	return array_merge($pairs, is_array($atts) ? $atts : []);
}

function get_terms($args = [])
{
	return $GLOBALS['ka4wp_test_terms'];
}

function get_current_screen()
{
	return $GLOBALS['ka4wp_test_current_screen'];
}

require_once dirname(__DIR__) . '/includes/Adapters/AdapterInterface.php';
require_once dirname(__DIR__) . '/includes/Adapters/AbstractAdapter.php';
require_once dirname(__DIR__) . '/includes/Adapters/AdapterRegistry.php';
require_once dirname(__DIR__) . '/includes/Adapters/WunschEventsAdapter.php';
require_once dirname(__DIR__) . '/includes/Migration/Migrator.php';
require_once dirname(__DIR__) . '/public/class-ka4wp-public.php';
require_once dirname(__DIR__) . '/admin/class-ka4wp-admin.php';
