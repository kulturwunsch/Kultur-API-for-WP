<?php

use PHPUnit\Framework\TestCase;

class AdminTaxonomyMenuTest extends TestCase
{
	/** @var KA4WP_Admin */
	private $admin;

	protected function setUp(): void
	{
		$this->admin = new KA4WP_Admin('ka4wp', '2.0.0');
		$GLOBALS['ka4wp_test_current_screen'] = null;
	}

	public function testTaxonomyPagesSelectKulturApiParentAndCurrentSubmenu(): void
	{
		foreach (['eventcategories', 'impartingareas', 'partners'] as $taxonomy) {
			$GLOBALS['ka4wp_test_current_screen'] = (object) ['taxonomy' => $taxonomy];

			self::assertSame('ka4wp', $this->admin->ka4wp_taxonomy_parent_file('edit.php'));
			self::assertSame(
				'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=ka4wp',
				$this->admin->ka4wp_taxonomy_submenu_file(null)
			);
		}
	}

	public function testOtherAdminPagesKeepTheirExistingMenuSelection(): void
	{
		$GLOBALS['ka4wp_test_current_screen'] = (object) ['taxonomy' => 'category'];

		self::assertSame('edit.php', $this->admin->ka4wp_taxonomy_parent_file('edit.php'));
		self::assertSame('edit-tags.php', $this->admin->ka4wp_taxonomy_submenu_file('edit-tags.php'));
	}
}
