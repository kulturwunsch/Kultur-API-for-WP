<?php

use KA4WP\Adapters\AbstractAdapter;
use KA4WP\Adapters\AdapterRegistry;
use PHPUnit\Framework\TestCase;

class RegistryTestAdapter extends AbstractAdapter
{
	private $id;

	public function __construct($id)
	{
		$this->id = $id;
	}

	public function get_id(): string
	{
		return $this->id;
	}

	public function get_label(): string
	{
		return $this->id;
	}

	public function get_credential_fields(): array
	{
		return [];
	}

	public function get_endpoints(): array
	{
		return [];
	}

	protected function get_production_url(): string
	{
		return 'https://example.test';
	}

	protected function build_headers(array $credentials): array
	{
		return [];
	}
}

class AdapterRegistryTest extends TestCase
{
	protected function setUp(): void
	{
		$property = new ReflectionProperty(AdapterRegistry::class, 'instance');
		$property->setAccessible(true);
		$property->setValue(null, null);
		$GLOBALS['ka4wp_test_actions'] = [];
		$GLOBALS['ka4wp_test_options'] = [];
	}

	public function testLoadsAdaptersFromHookOnceAndPreservesFirstRegistration(): void
	{
		$registry = AdapterRegistry::instance();
		$registered = new RegistryTestAdapter('example');
		$duplicate = new RegistryTestAdapter('example');
		$hookCalls = 0;

		add_action('ka4wp/register_adapters', function ($loadedRegistry) use (&$hookCalls, $registered, $duplicate) {
			++$hookCalls;
			self::assertTrue($loadedRegistry->register($registered));
			self::assertFalse($loadedRegistry->register($duplicate));
		});

		self::assertSame($registered, $registry->get('example'));
		self::assertSame(['example' => $registered], $registry->all());
		self::assertSame(1, $hookCalls);
	}

	public function testReturnsActiveAdapterAndUsesDefaultWhenOptionIsEmpty(): void
	{
		$registry = AdapterRegistry::instance();
		$adapter = new RegistryTestAdapter(AdapterRegistry::DEFAULT_ADAPTER);
		$registry->register($adapter);

		self::assertSame($adapter, $registry->get_active());
		$GLOBALS['ka4wp_test_options'][AdapterRegistry::OPTION_CRM_TYPE] = '';
		self::assertSame($adapter, $registry->get_active());
		$GLOBALS['ka4wp_test_options'][AdapterRegistry::OPTION_CRM_TYPE] = 'missing';
		self::assertNull($registry->get_active());
	}
}
