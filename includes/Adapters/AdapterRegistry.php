<?php

namespace KA4WP\Adapters;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Collects all available CRM adapters.
 *
 * Third parties register adapters:
 *
 *     add_action('ka4wp/register_adapters', function ($registry) {
 *         $registry->register(new My_Adapter());
 *     });
 *
 * @since 2.0.0
 */
class AdapterRegistry {

	public const DEFAULT_ADAPTER = 'wunsch_events';
	public const OPTION_CRM_TYPE = 'ka4wp_crm_type';

	/** @var AdapterRegistry|null */
	private static $instance = null;

	/** @var array<string, AdapterInterface> */
	private $adapters = [];

	/** @var bool */
	private $loaded = false;

	/**
	 * @return AdapterRegistry
	 */
	public static function instance(): AdapterRegistry {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register an adapter. An already registered id is not overwritten.
	 *
	 * @param AdapterInterface $adapter Adapter instance
	 *
	 * @return bool True if registered
	 */
	public function register(AdapterInterface $adapter): bool {
		$id = $adapter->get_id();
		if ($id === '' || isset($this->adapters[$id])) {
			return false;
		}
		$this->adapters[$id] = $adapter;

		return true;
	}

	/**
	 * @param string $id Adapter id
	 *
	 * @return AdapterInterface|null
	 */
	public function get(string $id): ?AdapterInterface {
		$this->load();

		return $this->adapters[$id] ?? null;
	}

	/**
	 * @return array<string, AdapterInterface>
	 */
	public function all(): array {
		$this->load();

		return $this->adapters;
	}

	/**
	 * The adapter selected in the settings, or null if it is not available.
	 *
	 * @return AdapterInterface|null
	 */
	public function get_active(): ?AdapterInterface {
		$id = (string) get_option(self::OPTION_CRM_TYPE, self::DEFAULT_ADAPTER);

		return $this->get($id ?: self::DEFAULT_ADAPTER);
	}

	/**
	 * Fire the registration hook once.
	 *
	 * @return void
	 */
	private function load(): void {
		if ($this->loaded) {
			return;
		}
		$this->loaded = true;

		/**
		 * Register CRM adapters.
		 *
		 * @since 2.0.0
		 * @param AdapterRegistry $registry The adapter registry
		 */
		do_action('ka4wp/register_adapters', $this);
	}
}
