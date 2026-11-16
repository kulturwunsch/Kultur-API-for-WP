<?php

namespace KA4WP\Adapters;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Contract for a CRM adapter.
 *
 * An adapter describes one CRM: its credentials, its endpoints and how
 * requests are sent. Third parties register adapters via the
 * "ka4wp/register_adapters" action.
 *
 * Endpoint definition format (array key = endpoint key):
 * [
 *   'name'        => string  Human readable name
 *   'description' => string  Short description
 *   'path'        => string  Path appended to the base url
 *   'method'      => string  HTTP method, default POST
 *   'type'        => string  "submit" (form data is sent), "load" (data is fetched) or "check"
 *   'fields'      => array   List of ['name' => string, 'value' => string, 'required' => bool (optional)] mapping targets
 * ]
 *
 * @since 2.0.0
 */
interface AdapterInterface {

	/**
	 * Unique adapter id, e.g. "wunsch_events".
	 *
	 * @return string
	 */
	public function get_id(): string;

	/**
	 * Human readable CRM name.
	 *
	 * @return string
	 */
	public function get_label(): string;

	/**
	 * Credential fields the user has to fill in.
	 *
	 * Format: key => ['label' => string, 'type' => 'text'|'password', 'required' => bool]
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_credential_fields(): array;

	/**
	 * Available endpoints of the CRM.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_endpoints(): array;

	/**
	 * Base url of the CRM API (production or test, depending on environment).
	 *
	 * @return string
	 */
	public function get_base_url(): string;

	/**
	 * Send a request to an endpoint.
	 *
	 * @param string               $endpoint_key Key of the endpoint definition
	 * @param array<string, mixed> $data         Data to send
	 *
	 * @return array<string, mixed>|\WP_Error Decoded response ['code' => int, 'body' => mixed] or error
	 */
	public function request(string $endpoint_key, array $data = []);

	/**
	 * Check whether the stored credentials work.
	 *
	 * @return true|\WP_Error
	 */
	public function test_connection();
}
