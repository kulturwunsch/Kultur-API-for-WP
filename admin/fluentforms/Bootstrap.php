<?php

namespace KA4WP;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use FluentForm\App\Http\Controllers\IntegrationManagerController;
use FluentForm\Framework\Foundation\Application;
use KA4WP\Adapters\AdapterInterface;
use KA4WP\Adapters\AdapterRegistry;

/**
 * Fluent Forms feed that sends submissions to the active CRM adapter.
 *
 * The credentials are managed by the adapter (WordPress Connectors), so the
 * integration has no global settings of its own.
 *
 * @since 2.0.0
 */
class FluentFormIntegration extends IntegrationManagerController
{
	public function __construct(Application $app)
	{
		parent::__construct(
			$app,
			'Kultur-API for WordPress',
			'ka4wp',
			'_fluentform_ka4wp_settings',
			'ka4wp_feed',
			1
		);

		$this->logo = plugin_dir_url(__FILE__) . 'fficon.png';
		$this->hasGlobalMenu = false;
		$this->disableGlobalSettings = 'yes';

		$this->registerAdminHooks();
		add_filter('fluentform/global_addons', [$this, 'addIntegrationDescription'], 2);
	}

	/**
	 * The integration is ready when the active adapter has its credentials.
	 *
	 * @return bool
	 */
	public function isConfigured()
	{
		$adapter = $this->getAdapter();

		return $adapter && $adapter->is_configured();
	}

	public function pushIntegration($integrations, $formId)
	{
		$integrations[$this->integrationKey] = [
			'title'                 => $this->title . ' Integration',
			'logo'                  => $this->logo,
			'is_active'             => $this->isConfigured(),
			'configure_title'       => __('Configuration required!', 'kultur-api-for-wp'),
			'global_configure_url'  => admin_url('options-connectors.php'),
			'configure_message'     => __('The CRM connection is not configured yet. Please enter your API key under Settings > Connectors first.', 'kultur-api-for-wp'),
			'configure_button_text' => __('Open connectors', 'kultur-api-for-wp'),
		];

		return $integrations;
	}

	/**
	 * Add a translated description to the global integrations overview.
	 *
	 * The base controller builds this overview from its global_addons filter;
	 * pushIntegration() only affects the per-form feed selector.
	 *
	 * @param array<string, mixed> $addons Registered integrations
	 *
	 * @return array<string, mixed>
	 */
	public function addIntegrationDescription(array $addons): array
	{
		if (isset($addons[$this->integrationKey])) {
			$addons[$this->integrationKey]['description'] = __('Kultur-API for WordPress connects your website easily with your cultureguest database.', 'kultur-api-for-wp');
		}

		return $addons;
	}

	public function getIntegrationDefaults($settings, $formId)
	{
		return [
			'name'              => '',
			'list_id'           => '',
			'merge_fields'      => (object) [],
			'additional_values' => [],
			'conditionals'      => [
				'conditions' => [],
				'status'     => false,
				'type'       => 'all',
			],
			'enabled'           => true,
		];
	}

	public function getSettingsFields($settings, $formId)
	{
		return [
			'fields' => [
				[
					'key'         => 'name',
					'label'       => __('Name', 'kultur-api-for-wp'),
					'required'    => true,
					'placeholder' => __('Your Feed Title', 'kultur-api-for-wp'),
					'component'   => 'text',
				],
				[
					'key'         => 'list_id',
					'label'       => __('Interface', 'kultur-api-for-wp'),
					'placeholder' => __('Select the CRM interface', 'kultur-api-for-wp'),
					'tips'        => __('Select the interface of your CRM that should receive the submitted data.', 'kultur-api-for-wp'),
					'component'   => 'list_ajax_options',
					'required'    => true,
					'options'     => $this->getEndpointOptions(),
				],
				[
					'key'                => 'merge_fields',
					'require_list'       => true,
					'label'              => __('Map fields', 'kultur-api-for-wp'),
					'tips'               => __('Select which form fields pair with the fields of the CRM. Required fields are marked with *.', 'kultur-api-for-wp'),
					'component'          => 'map_fields',
					'field_label_remote' => __('CRM field', 'kultur-api-for-wp'),
					'field_label_local'  => __('Form field', 'kultur-api-for-wp'),
					'primary_fileds'     => [],
				],
				[
					'key'                => 'additional_values',
					'require_list'       => true,
					'label'              => __('Additional values', 'kultur-api-for-wp'),
					'tips'               => __('Send fixed values or form variables to further CRM fields, e.g. the source of the submission. For uploaded files use the file variable of Fluent Forms, the CRM can fetch the file via its link.', 'kultur-api-for-wp'),
					'component'          => 'dropdown_many_fields',
					'field_label_remote' => __('CRM field', 'kultur-api-for-wp'),
					'field_label_local'  => __('Value', 'kultur-api-for-wp'),
					'options'            => $this->getAllFieldOptions(),
				],
				[
					'key'       => 'conditionals',
					'label'     => __('Conditional logic', 'kultur-api-for-wp'),
					'tips'      => __('Send the data to the CRM only if the conditions match.', 'kultur-api-for-wp'),
					'component' => 'conditional_block',
				],
				[
					'key'            => 'enabled',
					'label'          => __('Status', 'kultur-api-for-wp'),
					'component'      => 'checkbox-single',
					'checkbox_label' => __('Enable this feed', 'kultur-api-for-wp'),
				],
			],
			'button_require_list' => true,
			'integration_title'   => $this->title,
		];
	}

	/**
	 * Fields of the selected interface, offered for the mapping.
	 *
	 * @param mixed $list   Initial filter value
	 * @param mixed $listId Selected endpoint key
	 * @param mixed $formId Id of the form
	 *
	 * @return array<string, string> Field key => label, required fields are marked with an asterisk
	 */
	public function getMergeFields($list, $listId, $formId)
	{
		$endpoints = $this->getSubmitEndpoints();
		$key = (string) ($listId ?: $list);

		if (!isset($endpoints[$key])) {
			return [];
		}

		$fields = [];
		foreach ($endpoints[$key]['fields'] ?? [] as $field) {
			$fields[$field['value']] = $field['name'] . (!empty($field['required']) ? ' *' : '');
		}

		return $fields;
	}

	/**
	 * Send the submission to the CRM.
	 *
	 * @param array<string, mixed> $feed     Feed with processed values
	 * @param array<string, mixed> $formData Submitted form data
	 * @param object               $entry    Fluent Forms entry
	 * @param object               $form     Fluent Forms form
	 *
	 * @return bool
	 */
	public function notify($feed, $formData, $entry, $form)
	{
		$values = $feed['processedValues'];
		$endpointKey = (string) ($values['list_id'] ?? '');
		$adapter = $this->getAdapter();
		$endpoints = $this->getSubmitEndpoints();

		if (!$adapter || !isset($endpoints[$endpointKey])) {
			$this->logResult($feed, 'failed', __('The selected CRM interface is not available.', 'kultur-api-for-wp'));
			return false;
		}

		$data = [];
		foreach ((array) ($values['additional_values'] ?? []) as $item) {
			if (!empty($item['label']) && isset($item['item_value']) && $item['item_value'] !== '') {
				$data[$item['label']] = $item['item_value'];
			}
		}
		foreach ((array) ($values['merge_fields'] ?? []) as $key => $value) {
			if ($value !== '' && $value !== null) {
				$data[$key] = $value;
			}
		}

		$missing = [];
		foreach ($endpoints[$endpointKey]['fields'] ?? [] as $field) {
			if (!empty($field['required']) && (!isset($data[$field['value']]) || $data[$field['value']] === '')) {
				$missing[] = $field['name'];
			}
		}
		if (!empty($missing)) {
			$this->logResult($feed, 'failed', sprintf(
				/* translators: %s: list of field names */
				__('Skipped because required fields are empty: %s', 'kultur-api-for-wp'),
				implode(', ', $missing)
			));
			return false;
		}

		/**
		 * Filters the data sent to the CRM.
		 *
		 * @param array<string, mixed> $data        Data to send
		 * @param string               $endpointKey Adapter endpoint key
		 * @param array<string, mixed> $feed        Fluent Forms feed
		 * @param object               $entry       Fluent Forms entry
		 * @param object               $form        Fluent Forms form
		 */
		$data = apply_filters('ka4wp/fluentforms_payload', $data, $endpointKey, $feed, $entry, $form);

		$result = $adapter->request($endpointKey, $data);

		/**
		 * Fires after the CRM answered.
		 *
		 * @param array<string, mixed>|\WP_Error $result      Response or error
		 * @param string                         $endpointKey Adapter endpoint key
		 * @param AdapterInterface               $adapter     Used adapter
		 */
		do_action('ka4wp/adapter_response', $result, $endpointKey, $adapter);

		if (is_wp_error($result)) {
			$this->logResult($feed, 'failed', $result->get_error_message());
			return false;
		}

		$this->logResult($feed, 'success', sprintf(
			/* translators: %s: CRM name */
			__('The data was successfully sent to %s.', 'kultur-api-for-wp'),
			$adapter->get_label()
		));

		return true;
	}

	private function logResult($feed, string $status, string $message): void
	{
		do_action('fluentform/integration_action_result', $feed, $status, $message);
	}

	private function getAdapter(): ?AdapterInterface
	{
		return AdapterRegistry::instance()->get_active();
	}

	/**
	 * Endpoints of the active adapter that receive form data.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function getSubmitEndpoints(): array
	{
		$adapter = $this->getAdapter();
		if (!$adapter) {
			return [];
		}

		return array_filter($adapter->get_endpoints(), static function ($endpoint) {
			return ($endpoint['type'] ?? '') === 'submit';
		});
	}

	/**
	 * @return array<string, string>
	 */
	private function getEndpointOptions(): array
	{
		$options = [];
		foreach ($this->getSubmitEndpoints() as $key => $endpoint) {
			$options[$key] = $endpoint['name'];
		}

		return $options;
	}

	/**
	 * All mapping targets of all interfaces for the free "additional values" list.
	 *
	 * @return array<string, string>
	 */
	private function getAllFieldOptions(): array
	{
		$options = [];
		foreach ($this->getSubmitEndpoints() as $endpoint) {
			foreach ($endpoint['fields'] ?? [] as $field) {
				$options[$field['value']] = $field['name'];
			}
		}

		return $options;
	}
}
