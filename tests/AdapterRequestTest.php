<?php

use KA4WP\Adapters\AbstractAdapter;
use PHPUnit\Framework\TestCase;

class RequestTestAdapter extends AbstractAdapter
{
	public function get_id(): string
	{
		return 'request_test';
	}

	public function get_label(): string
	{
		return 'Request test';
	}

	public function get_credential_fields(): array
	{
		return [
			'api_key' => ['label' => 'API key', 'type' => 'password', 'required' => true],
		];
	}

	public function get_endpoints(): array
	{
		return [
			'submit' => ['path' => '/submit', 'method' => 'POST', 'type' => 'submit'],
			'load'   => ['path' => '/load', 'method' => 'GET', 'type' => 'load'],
		];
	}

	protected function get_production_url(): string
	{
		return 'https://api.example.test/v1';
	}

	protected function build_headers(array $credentials): array
	{
		return ['Authorization' => 'Bearer ' . $credentials['api_key']];
	}
}

class AdapterRequestTest extends TestCase
{
	protected function setUp(): void
	{
		$GLOBALS['ka4wp_test_options'] = ['ka4wp_request_test_api_key' => 'test-key'];
		$GLOBALS['ka4wp_test_http_handler'] = null;
		$GLOBALS['ka4wp_test_http_response'] = null;
		$GLOBALS['ka4wp_test_development_mode'] = '';
		KA4WP::$test_logs = [];
	}

	public function testSendsJsonRequestAndDecodesResponse(): void
	{
		$adapter = new RequestTestAdapter();
		$captured = null;
		$GLOBALS['ka4wp_test_http_handler'] = function ($url, $args) use (&$captured) {
			$captured = [$url, $args];
			return ['response' => ['code' => 201], 'body' => '{"id":42}'];
		};

		$result = $adapter->request('submit', ['name' => 'Test']);

		self::assertSame(['code' => 201, 'body' => ['id' => 42]], $result);
		self::assertSame('https://api.example.test/v1/submit', $captured[0]);
		self::assertSame('POST', $captured[1]['method']);
		self::assertSame('Bearer test-key', $captured[1]['headers']['Authorization']);
		self::assertSame('application/json', $captured[1]['headers']['Content-Type']);
		self::assertSame('{"name":"Test"}', $captured[1]['body']);
	}

	public function testReturnsErrorsForUnknownEndpointUnconfiguredCredentialsAndHttpFailure(): void
	{
		$adapter = new RequestTestAdapter();

		$unknown = $adapter->request('missing');
		self::assertInstanceOf(WP_Error::class, $unknown);
		self::assertSame('ka4wp_unknown_endpoint', $unknown->get_error_code());

		$GLOBALS['ka4wp_test_options']['ka4wp_request_test_api_key'] = '';
		$unconfigured = $adapter->request('submit');
		self::assertInstanceOf(WP_Error::class, $unconfigured);
		self::assertSame('ka4wp_not_configured', $unconfigured->get_error_code());

		$GLOBALS['ka4wp_test_options']['ka4wp_request_test_api_key'] = 'test-key';
		$GLOBALS['ka4wp_test_http_response'] = ['response' => ['code' => 503], 'body' => '{"error":"down"}'];
		$failed = $adapter->request('submit');
		self::assertInstanceOf(WP_Error::class, $failed);
		self::assertSame('ka4wp_http_503', $failed->get_error_code());
		self::assertSame(['code' => 503, 'body' => ['error' => 'down']], $failed->get_error_data());
	}

	public function testUsesQueryParametersForGetAndSurfacesTransportErrors(): void
	{
		$adapter = new RequestTestAdapter();
		$capturedUrl = '';
		$GLOBALS['ka4wp_test_http_handler'] = function ($url, $args) use (&$capturedUrl) {
			$capturedUrl = $url;
			return ['response' => ['code' => 200], 'body' => '[]'];
		};

		$result = $adapter->request('load', ['search' => 'term']);
		self::assertSame(['code' => 200, 'body' => []], $result);
		self::assertSame('https://api.example.test/v1/load?search=term', $capturedUrl);

		$GLOBALS['ka4wp_test_http_handler'] = static function () {
			return new WP_Error('network_error', 'Connection failed');
		};
		$transportError = $adapter->request('load');
		self::assertInstanceOf(WP_Error::class, $transportError);
		self::assertSame('network_error', $transportError->get_error_code());
		self::assertCount(1, KA4WP::$test_logs);
	}
}
