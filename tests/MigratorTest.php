<?php

use KA4WP\Migration\Migrator;
use PHPUnit\Framework\TestCase;

class MigratorTest extends TestCase
{
	protected function setUp(): void
	{
		$GLOBALS['ka4wp_test_options'] = [];
		$GLOBALS['ka4wp_test_posts'] = [];
		$GLOBALS['ka4wp_test_post_meta'] = [];
		$GLOBALS['ka4wp_test_cf7_forms'] = [];
	}

	public function testMigratesLegacyCredentialsAndFlagsWithoutOverwritingExistingCredentials(): void
	{
		$GLOBALS['ka4wp_test_posts'] = [
			(object) ['ID' => 10, 'post_title' => 'Primary'],
			(object) ['ID' => 11, 'post_title' => 'Other account'],
			(object) ['ID' => 12, 'post_title' => 'Ignored CRM'],
			(object) ['ID' => 13, 'post_title' => 'Empty key'],
		];
		$GLOBALS['ka4wp_test_post_meta'] = [
			10 => ['ka4wp_api_type' => 'wunsch.events', 'ka4wp_api_key' => ' primary-key '],
			11 => ['ka4wp_api_type' => 'wunsch.events', 'ka4wp_api_key' => 'other-key'],
			12 => ['ka4wp_api_type' => 'other', 'ka4wp_api_key' => 'ignored-key'],
			13 => ['ka4wp_api_type' => 'wunsch.events', 'ka4wp_api_key' => '  '],
		];
		$GLOBALS['ka4wp_test_cf7_forms'] = [21, 22];
		$GLOBALS['ka4wp_test_options'] = [
			'ka4wp_api_receive_eventcategories' => '0',
			'ka4wp_api_receive_impartingareas' => '456',
			'ka4wp_api_receive_partners' => '1',
		];

		Migrator::run('1.3.2', '2.0.0');

		self::assertSame('primary-key', get_option('ka4wp_wunsch_events_api_key'));
		self::assertSame('wunsch_events', get_option('ka4wp_crm_type'));
		self::assertSame(
			[['id' => 11, 'title' => 'Other account']],
			get_option(Migrator::OPTION_EXTRA_ACCOUNTS)
		);
		self::assertSame('', get_option('ka4wp_api_receive_eventcategories'));
		self::assertSame('1', get_option('ka4wp_api_receive_impartingareas'));
		self::assertSame('1', get_option('ka4wp_api_receive_partners'));
		self::assertSame([21, 22], get_option(Migrator::OPTION_LEGACY_CF7_FORMS));
	}

	public function testDoesNotOverwriteConnectorKeyAndDoesNotRunFutureSteps(): void
	{
		$GLOBALS['ka4wp_test_posts'] = [
			(object) ['ID' => 10, 'post_title' => 'Primary'],
		];
		$GLOBALS['ka4wp_test_post_meta'] = [
			10 => ['ka4wp_api_type' => 'wunsch.events', 'ka4wp_api_key' => 'legacy-key'],
		];
		$GLOBALS['ka4wp_test_options']['ka4wp_wunsch_events_api_key'] = 'connector-key';

		Migrator::run('2.0.0', '2.0.0');
		self::assertSame('connector-key', get_option('ka4wp_wunsch_events_api_key'));
		self::assertFalse(array_key_exists(Migrator::OPTION_LEGACY_CF7_FORMS, $GLOBALS['ka4wp_test_options']));

		Migrator::run('1.3.2', '2.0.0');
		self::assertSame('connector-key', get_option('ka4wp_wunsch_events_api_key'));
	}
}
