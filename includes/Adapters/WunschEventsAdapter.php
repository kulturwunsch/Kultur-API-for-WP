<?php

namespace KA4WP\Adapters;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Adapter for the WUNSCH.events CRM.
 *
 * @since 2.0.0
 */
class WunschEventsAdapter extends AbstractAdapter {

	/**
	 * {@inheritdoc}
	 */
	public function get_id(): string {
		return 'wunsch_events';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return 'WUNSCH.events';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_credential_fields(): array {
		return [
			'api_key' => [
				'label'    => __('API Key', 'kultur-api-for-wp'),
				'type'     => 'password',
				'required' => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_credential_keys(): array {
		return ['api_key'];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_credential_keys(): array {
		return ['api_key'];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_production_url(): string {
		return 'https://api.wunsch.events/v1';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_test_url(): string {
		return 'https://api.testserver.wunschevents.de/v1';
	}

	/**
	 * The API key is sent as the Basic authorization token.
	 *
	 * {@inheritdoc}
	 */
	protected function build_headers(array $credentials): array {
		return ['Authorization' => 'Basic ' . $credentials['api_key']];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_endpoints(): array {
		return [
			'submit_cultureguest' => [
				'name'        => __('Registration of new culture guests', 'kultur-api-for-wp'),
				'description' => __('Interface for creating new cultural guests.', 'kultur-api-for-wp'),
				'path'        => '/cultureguest/create',
				'method'      => 'POST',
				'type'        => 'submit',
				'fields'      => [
					['name' => __('Firstname', 'kultur-api-for-wp'), 'value' => 'firstname', 'required' => true],
					['name' => __('Lastname', 'kultur-api-for-wp'), 'value' => 'lastname', 'required' => true],
					['name' => __('Gender', 'kultur-api-for-wp'), 'value' => 'gender'],
					['name' => __('Street', 'kultur-api-for-wp'), 'value' => 'street'],
					['name' => __('Street number', 'kultur-api-for-wp'), 'value' => 'streetnumber'],
					['name' => __('Zip code', 'kultur-api-for-wp'), 'value' => 'zipcode'],
					['name' => __('City', 'kultur-api-for-wp'), 'value' => 'city'],
					['name' => __('Fixed line number', 'kultur-api-for-wp'), 'value' => 'phone'],
					['name' => __('Mobile number', 'kultur-api-for-wp'), 'value' => 'mobilephone'],
					['name' => __('Date of birth', 'kultur-api-for-wp'), 'value' => 'birthdate'],
				],
			],
			'submit_cultureguestgroup' => [
				'name'        => __('Registration of new culture guests as a group (for organizations)', 'kultur-api-for-wp'),
				'description' => __('Interface for creating new cultural guests as group.', 'kultur-api-for-wp'),
				'path'        => '/culturegroup/create',
				'method'      => 'POST',
				'type'        => 'submit',
				'fields'      => [
					['name' => __('Firstname', 'kultur-api-for-wp'), 'value' => 'firstname', 'required' => true],
					['name' => __('Lastname', 'kultur-api-for-wp'), 'value' => 'lastname', 'required' => true],
				],
			],
			'submit_organisationmember' => [
				'name'        => __('Register as an organization member', 'kultur-api-for-wp'),
				'description' => __('Interface for creating new members in the organization.', 'kultur-api-for-wp'),
				'path'        => '/organizationmember/create',
				'method'      => 'POST',
				'type'        => 'submit',
				'fields'      => [
					['name' => __('Company name', 'kultur-api-for-wp'), 'value' => 'organizationname'],
					['name' => __('Firstname', 'kultur-api-for-wp'), 'value' => 'firstname', 'required' => true],
					['name' => __('Lastname', 'kultur-api-for-wp'), 'value' => 'lastname', 'required' => true],
					['name' => __('Gender', 'kultur-api-for-wp'), 'value' => 'gender'],
					['name' => __('Email address', 'kultur-api-for-wp'), 'value' => 'email'],
					['name' => __('Street', 'kultur-api-for-wp'), 'value' => 'street'],
					['name' => __('Street number', 'kultur-api-for-wp'), 'value' => 'streetnumber'],
					['name' => __('Zip code', 'kultur-api-for-wp'), 'value' => 'zipcode'],
					['name' => __('City', 'kultur-api-for-wp'), 'value' => 'city'],
					['name' => __('Date of birth', 'kultur-api-for-wp'), 'value' => 'birthdate'],
					['name' => __('Fixed line number', 'kultur-api-for-wp'), 'value' => 'phone'],
					['name' => __('Mobile number', 'kultur-api-for-wp'), 'value' => 'mobilephone'],
					['name' => __('IBAN', 'kultur-api-for-wp'), 'value' => 'iban'],
					['name' => __('BIC', 'kultur-api-for-wp'), 'value' => 'bic'],
					['name' => __('Name of the Accountholder', 'kultur-api-for-wp'), 'value' => 'accountholder'],
					['name' => __('Bankname', 'kultur-api-for-wp'), 'value' => 'bankname'],
					['name' => __('Donation receipt', 'kultur-api-for-wp'), 'value' => 'donationreceipt'],
					['name' => __('Amount', 'kultur-api-for-wp'), 'value' => 'amount'],
					['name' => __('Maturity cycle', 'kultur-api-for-wp'), 'value' => 'maturitycycle'],
				],
			],
			'check_api' => [
				'name'        => __('Check the API endpoint health', 'kultur-api-for-wp'),
				'description' => __('Interface to check whether the setup was successful and the other system responds correctly.', 'kultur-api-for-wp'),
				'path'        => '/check',
				'method'      => 'GET',
				'type'        => 'check',
				'fields'      => [],
			],
			'load_eventcategories' => [
				'name'        => __('Load event categories', 'kultur-api-for-wp'),
				'description' => __('Interface for querying the event categories.', 'kultur-api-for-wp'),
				'path'        => '/eventcategories/get',
				'method'      => 'GET',
				'type'        => 'load',
				'fields'      => [],
			],
			'load_impartingareas' => [
				'name'        => __('Load imparting areas', 'kultur-api-for-wp'),
				'description' => __('Interface for querying the imparting areas.', 'kultur-api-for-wp'),
				'path'        => '/impartingareas/get',
				'method'      => 'GET',
				'type'        => 'load',
				'fields'      => [],
			],
			'load_partners' => [
				'name'        => __('Load partners', 'kultur-api-for-wp'),
				'description' => __('Interface for querying partners.', 'kultur-api-for-wp'),
				'path'        => '/partners/get',
				'method'      => 'GET',
				'type'        => 'load',
				'fields'      => [],
			],
		];
	}
}
