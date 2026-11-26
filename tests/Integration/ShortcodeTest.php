<?php
/**
 * The [calcwise] shortcode in a real WordPress.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Integration;

use WPGenius\CalcWise\Api;
use WPGenius\CalcWise\Assets;

/**
 * Shortcode.
 */
class ShortcodeTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		Assets::register();
	}

	public function test_each_calculator_renders_and_loads_only_its_own_script() {
		foreach ( array( 'sip', 'lumpsum', 'swp', 'goal', 'emi' ) as $type ) {
			$html = do_shortcode( '[calcwise type="' . $type . '"]' );

			$this->assertStringContainsString( 'data-calcwise-type="' . $type . '"', $html, $type );
			$this->assertTrue( wp_script_is( 'calcwise-' . $type, 'enqueued' ), "$type script enqueued" );
		}
	}

	public function test_attributes_override_the_starting_value() {
		$html = do_shortcode( '[calcwise type="sip" monthlyAmount="10000" years="15"]' );

		$this->assertStringContainsString( 'value="10000"', $html );
		$this->assertStringContainsString( 'value="15"', $html );
	}

	public function test_unknown_type_renders_nothing_for_visitors() {
		wp_set_current_user( 0 );

		$this->assertSame( '', do_shortcode( '[calcwise type="nope"]' ) );
	}

	public function test_escaped_output_keeps_the_settings_and_colours() {
		update_option( Api::OPTION, array_merge( Api::get_settings(), array( 'accent_color' => '#123456' ) ) );

		$html = do_shortcode( '[calcwise type="emi"]' );

		$this->assertStringContainsString( '--calcwise-accent: #123456', $html );
		$this->assertMatchesRegularExpression( '/data-calcwise-settings="([^"]+)"/', $html );
		preg_match( '/data-calcwise-settings="([^"]+)"/', $html, $match );
		$settings = json_decode( html_entity_decode( $match[1], ENT_QUOTES ), true );
		$this->assertArrayHasKey( 'principal', $settings['fields'] );
		$this->assertStringContainsString( 'role="img"', $html );
		$this->assertStringContainsString( 'aria-live="polite"', $html );
		$this->assertStringContainsString( 'tabindex="-1"', $html );
	}
}
