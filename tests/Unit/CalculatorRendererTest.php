<?php
/**
 * Tests for src/Calculator/Renderer.php.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Unit;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPGenius\CalcWise\Calculator\Registry;
use WPGenius\CalcWise\Calculator\Renderer;
use WPGenius\CalcWise\Tests\TestCase;

/**
 * Renderer tests.
 */
class CalculatorRendererTest extends TestCase {

	const FIELDS = array(
		'amount' => array(
			'label'   => 'Amount',
			'unit'    => 'money',
			'min'     => 500.0,
			'max'     => 100000.0,
			'step'    => 500.0,
			'default' => 25000.0,
		),
	);

	protected function setUp(): void {
		parent::setUp();
		Registry::reset();
		Functions\when( 'sanitize_key' )->alias(
			static function ( $key ) {
				return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
			}
		);
		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );
		Functions\when( 'sanitize_hex_color' )->returnArg();
		Functions\when( 'number_format_i18n' )->alias( 'number_format' );
		Functions\when( 'get_option' )->justReturn( array() );
	}

	protected function tearDown(): void {
		Registry::reset();
		parent::tearDown();
	}

	/**
	 * Register one demo calculator and say whether its script is built.
	 *
	 * @param bool $built Whether the calculator's script is registered.
	 */
	private function demo( $built = true ) {
		Filters\expectApplied( 'calcwise_calculators' )->andReturn(
			array(
				'demo' => array(
					'fields'  => self::FIELDS,
					'results' => array(
						'total' => array(
							'label'     => 'Total <b>',
							'highlight' => true,
							'words'     => true,
						),
					),
					'chart'   => array(
						'label'  => 'Growth',
						'series' => array( 'total' => 'Total value' ),
					),
				),
			)
		);
		Functions\when( 'wp_script_is' )->justReturn( $built );
		Functions\when( 'wp_style_is' )->justReturn( false );
		Functions\when( 'wp_enqueue_script' )->justReturn( null );
	}

	public function test_overrides_are_validated() {
		$fields = Renderer::fields(
			self::FIELDS,
			array(
				'amount' => array(
					'min'     => '1000',
					'max'     => 'lots',
					'step'    => -5,
					'default' => 999999,
				),
			)
		);

		$this->assertSame( 1000.0, $fields['amount']['min'] );
		$this->assertSame( 100000.0, $fields['amount']['max'], 'Non-numeric max is ignored.' );
		$this->assertSame( 500.0, $fields['amount']['step'], 'A negative step falls back to the defined one.' );
		$this->assertSame( 100000.0, $fields['amount']['default'], 'Default is clamped to max.' );
	}

	public function test_min_above_max_falls_back_to_the_defined_limits() {
		$fields = Renderer::fields( self::FIELDS, array( 'amount' => array( 'min' => 200000 ) ) );

		$this->assertSame( 500.0, $fields['amount']['min'] );
		$this->assertSame( 100000.0, $fields['amount']['max'] );
	}

	public function test_unknown_or_unbuilt_calculator_renders_nothing() {
		$this->demo( false );

		$this->assertSame( '', Renderer::render( 'demo' ) );
		$this->assertSame( '', Renderer::render( 'missing' ) );
	}

	public function test_markup_carries_its_own_settings_and_labels() {
		$this->demo();

		$html = Renderer::render( 'demo', array( 'fields' => array( 'amount' => array( 'default' => 5000 ) ) ) );

		$this->assertStringContainsString( 'data-calcwise-type="demo"', $html );
		$this->assertMatchesRegularExpression( '/data-calcwise-settings="([^"]+)"/', $html );
		preg_match( '/data-calcwise-settings="([^"]+)"/', $html, $match );
		$settings = json_decode( html_entity_decode( $match[1], ENT_QUOTES ), true );
		$this->assertSame( 5000, (int) $settings['fields']['amount']['default'] );
		$this->assertSame( 'indian', $settings['format']['system'] );
		$this->assertSame( array( 'total' => 'Total value' ), $settings['series'] );

		$this->assertStringContainsString( 'type="number"', $html );
		$this->assertStringContainsString( 'type="range"', $html );
		$this->assertStringContainsString( 'value="5000"', $html );
		$this->assertStringContainsString( 'Total &lt;b&gt;', $html, 'Labels are escaped.' );
		$this->assertStringContainsString( 'aria-live="polite"', $html );
		$this->assertStringContainsString( 'calcwise-result__words', $html );
		$this->assertStringContainsString( '<table', $html );
	}

	public function test_each_instance_gets_its_own_ids() {
		$this->demo();

		preg_match( '/id="(calcwise-\d+)"/', Renderer::render( 'demo' ), $first );
		preg_match( '/id="(calcwise-\d+)"/', Renderer::render( 'demo' ), $second );

		$this->assertNotSame( $first[1], $second[1] );
	}

	public function test_site_settings_apply_and_instances_can_still_override_them() {
		$this->demo();
		Functions\when( 'get_option' )->justReturn(
			array(
				'limits_demo'    => array( 'amount' => array( 'default' => 3000 ) ),
				'accent_color_2' => '#123456',
			)
		);

		$this->assertStringContainsString( 'value="3000"', Renderer::render( 'demo' ) );
		$this->assertStringContainsString( '--calcwise-accent-2: #123456', Renderer::render( 'demo' ) );
		$this->assertStringContainsString( 'value="7000"', Renderer::render( 'demo', array( 'fields' => array( 'amount' => array( 'default' => 7000 ) ) ) ) );
	}
}
