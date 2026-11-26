<?php
/**
 * Registration of the built scripts and styles.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Integration;

use WPGenius\CalcWise\Assets;

/**
 * Assets.
 */
class AssetsTest extends \WP_UnitTestCase {

	public function test_runtime_is_registered_with_the_plugin_version() {
		Assets::register();

		$this->assertTrue( wp_script_is( Assets::RUNTIME, 'registered' ) );
		$script = wp_scripts()->registered[ Assets::RUNTIME ];
		$this->assertStringStartsWith( CALCWISE_VERSION . '-', (string) $script->ver );
		$this->assertStringContainsString( 'build/runtime/index.js', $script->src );
		$this->assertTrue( wp_style_is( Assets::RUNTIME, 'registered' ), 'Runtime styles are registered too.' );
	}

	public function test_unknown_calculator_is_not_enqueued() {
		Assets::register();

		$this->assertFalse( Assets::enqueue( 'does-not-exist' ) );
		$this->assertFalse( wp_script_is( Assets::RUNTIME, 'enqueued' ) );
	}
}
