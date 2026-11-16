<?php

use PHPUnit\Framework\TestCase;

class PartnerShortcodeTest extends TestCase
{
	public function testReturnsOnlyRenderedMarkupWithoutIncludeReturnValue(): void
	{
		$GLOBALS['ka4wp_test_terms'] = [];
		$public = new KA4WP_Public('ka4wp', '2.0.0');

		ob_start();
		$result = $public->ka4wp_load_shortcode_partners([]);
		$output = ob_get_clean();

		self::assertSame('', $output);
		self::assertSame('', $result);
	}
}
