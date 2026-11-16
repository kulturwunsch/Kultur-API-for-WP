<?php

namespace KA4WP\Adapters;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Base class for CRM adapters with a generic JSON request implementation.
 *
 * Subclasses define id, label, credentials, endpoints and urls and may
 * override build_headers() for their authentication scheme.
 *
 * @since 2.0.0
 */
abstract class AbstractAdapter implements AdapterInterface {

	/**
	 * Production base url (without trailing slash).
	 *
	 * @return string
	 */
	abstract protected function get_production_url(): string;

	/**
	 * Test base url, used when the WordPress development mode is "plugin" or "all".
	 *
	 * @return string
	 */
	protected function get_test_url(): string {
		return $this->get_production_url();
	}

	/**
	 * Request headers including authentication.
	 *
	 * @param array<string, string> $credentials Stored credential values
	 *
	 * @return array<string, string>
	 */
	abstract protected function build_headers(array $credentials): array;

	/**
	 * {@inheritdoc}
	 */
	public function get_base_url(): string {
		$isTest = in_array(wp_get_development_mode(), ['plugin', 'all'], true);
		$url = $isTest ? $this->get_test_url() : $this->get_production_url();

		return untrailingslashit((string) apply_filters('ka4wp/adapter_base_url', $url, $this->get_id(), $isTest));
	}

	/**
	 * Stored credential values of this adapter.
	 *
	 * The api_key is resolved like in the WordPress Connectors API:
	 * environment variable, PHP constant, database.
	 *
	 * @return array<string, string>
	 */
	public function get_credentials(): array {
		$credentials = [];
		foreach ($this->get_credential_keys() as $key) {
			$value = '';
			if ($key === 'api_key') {
				$name = $this->get_api_key_constant_name();
				$env = getenv($name);
				if ($env !== false && $env !== '') {
					$value = (string) $env;
				} elseif (defined($name) && constant($name) !== '') {
					$value = (string) constant($name);
				}
			}
			if ($value === '') {
				$value = (string) get_option($this->get_setting_name($key), '');
			}
			$credentials[$key] = $value;
		}

		return $credentials;
	}

	/**
	 * Option name of a credential field.
	 *
	 * @param string $field Credential field key
	 *
	 * @return string
	 */
	public function get_setting_name(string $field): string {
		return 'ka4wp_' . $this->get_id() . '_' . $field;
	}

	/**
	 * Name of the environment variable / PHP constant for the api key.
	 *
	 * @return string
	 */
	public function get_api_key_constant_name(): string {
		return strtoupper('KA4WP_' . $this->get_id() . '_API_KEY');
	}

	/**
	 * Url where users can obtain their credentials.
	 *
	 * @return string
	 */
	public function get_credentials_url(): string {
		return '';
	}

	/**
	 * Logo shown on the connector card.
	 *
	 * Uses admin/img/adapters/<adapter id>.svg or .png of the plugin if present.
	 * Adapters from other plugins override this method.
	 *
	 * @return string
	 */
	public function get_logo_url(): string {
		$root = dirname(__DIR__, 2);
		foreach (['svg', 'png'] as $extension) {
			$relative = 'admin/img/adapters/' . $this->get_id() . '.' . $extension;
			if (file_exists($root . '/' . $relative)) {
				return plugins_url($relative, $root . '/kultur-api-for-wp.php');
			}
		}

		return '';
	}

	/**
	 * Definition for the WordPress Connectors API.
	 *
	 * The Connectors screen only supports a single api key. Adapters with other
	 * credentials return null and need their own settings page.
	 *
	 * @return array<string, mixed>|null
	 */
	public function get_connector_definition(): ?array {
		if (array_keys($this->get_credential_fields()) !== ['api_key']) {
			return null;
		}

		$definition = [
			'name'           => $this->get_label(),
			'description'    => sprintf(
				/* translators: %s: CRM name */
				__('Connect your WordPress with %s.', 'kultur-api-for-wp'),
				$this->get_label()
			),
			'type'           => 'api',
			'authentication' => [
				'method'          => 'api_key',
				'credentials_url' => $this->get_credentials_url(),
				'setting_name'    => $this->get_setting_name('api_key'),
				'env_var_name'    => $this->get_api_key_constant_name(),
				'constant_name'   => $this->get_api_key_constant_name(),
			],
			'plugin'         => ['file' => 'kultur-api-for-wp/kultur-api-for-wp.php'],
		];

		if ($this->get_logo_url() !== '') {
			$definition['logo_url'] = $this->get_logo_url();
		}

		return $definition;
	}

	/**
	 * Whether all required credentials are filled in.
	 *
	 * @return bool
	 */
	public function is_configured(): bool {
		$credentials = $this->get_credentials();
		foreach ($this->get_required_credential_keys() as $key) {
			if (($credentials[$key] ?? '') === '') {
				return false;
			}
		}

		return true;
	}

	/**
	 * Keys of the credential fields.
	 *
	 * Runs before the init action (Fluent Forms checks the configuration while
	 * loading), so adapters should override this without translated strings.
	 *
	 * @return array<int, string>
	 */
	protected function get_credential_keys(): array {
		return array_keys($this->get_credential_fields());
	}

	/**
	 * Keys of the required credential fields, see get_credential_keys().
	 *
	 * @return array<int, string>
	 */
	protected function get_required_credential_keys(): array {
		return array_keys(array_filter($this->get_credential_fields(), static function ($field) {
			return !empty($field['required']);
		}));
	}

	/**
	 * {@inheritdoc}
	 */
	public function request(string $endpoint_key, array $data = []) {
		$endpoints = $this->get_endpoints();
		if (!isset($endpoints[$endpoint_key])) {
			return new \WP_Error('ka4wp_unknown_endpoint', sprintf(
				/* translators: %s: endpoint key */
				__('Unknown endpoint "%s".', 'kultur-api-for-wp'),
				$endpoint_key
			));
		}

		if (!$this->is_configured()) {
			return new \WP_Error('ka4wp_not_configured', __('The CRM credentials are not configured yet.', 'kultur-api-for-wp'));
		}

		$endpoint = $endpoints[$endpoint_key];
		$method = strtoupper($endpoint['method'] ?? 'POST');
		$url = $this->get_base_url() . $endpoint['path'];

		$args = [
			'method'     => $method,
			'timeout'    => 10,
			'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
			'headers'    => $this->build_headers($this->get_credentials()),
		];

		if (!empty($data)) {
			if ($method === 'GET') {
				$url = add_query_arg(rawurlencode_deep($data), $url);
			} else {
				$args['headers']['Content-Type'] = 'application/json';
				$args['body'] = wp_json_encode($data);
			}
		}

		$response = wp_remote_request($url, $args);
		if (is_wp_error($response)) {
			\KA4WP::debug_log(sprintf('%s: %s %s failed: %s', $this->get_id(), $method, $endpoint['path'], $response->get_error_message()));
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code($response);
		$raw = wp_remote_retrieve_body($response);
		$body = json_decode($raw, true);
		$body = (json_last_error() === JSON_ERROR_NONE) ? $body : $raw;

		if ($code < 200 || $code > 299) {
			\KA4WP::debug_log(sprintf('%s: %s %s returned HTTP %d', $this->get_id(), $method, $endpoint['path'], $code));
			return new \WP_Error('ka4wp_http_' . $code, sprintf(
				/* translators: %d: HTTP status code */
				__('The CRM responded with HTTP status %d.', 'kultur-api-for-wp'),
				$code
			), ['code' => $code, 'body' => $body]);
		}

		return ['code' => $code, 'body' => $body];
	}

	/**
	 * {@inheritdoc}
	 */
	public function test_connection() {
		foreach ($this->get_endpoints() as $key => $endpoint) {
			if (($endpoint['type'] ?? '') === 'check') {
				$result = $this->request($key);
				return is_wp_error($result) ? $result : true;
			}
		}

		return new \WP_Error('ka4wp_no_check_endpoint', __('This adapter has no endpoint to check the connection.', 'kultur-api-for-wp'));
	}
}
