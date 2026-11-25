<?php
/**
 * Tests for the shared helpers in src/Api.php.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Unit;

use Brain\Monkey\Functions;
use WPGenius\CalcWise\Api;
use WPGenius\CalcWise\Tests\TestCase;

/**
 * Api tests.
 */
class ApiTest extends TestCase {

	public function test_replace_placeholders_with_or_without_braces() {
		$text = Api::replace_placeholders(
			'Hi {name}, you have {count} new items.',
			array(
				'name'    => 'Asha',
				'{count}' => 3,
			)
		);

		$this->assertSame( 'Hi Asha, you have 3 new items.', $text );
	}

	public function test_replace_placeholders_leaves_unknown_ones() {
		$this->assertSame( 'Hello {unknown}', Api::replace_placeholders( 'Hello {unknown}', array( 'name' => 'x' ) ) );
	}

	public function test_get_setting_falls_back_to_defaults() {
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'get_option' )->justReturn( array( 'accent_color' => '#000000' ) );

		$this->assertSame( '#000000', Api::get_setting( 'accent_color' ) ); // Saved value.
		$this->assertSame( 0, Api::get_setting( 'delete_data' ) ); // Default.
		$this->assertSame( 'none', Api::get_setting( 'missing', 'none' ) ); // Unknown key.
	}
}
