<?php
/**
 * Tests for src/Shortcode.php.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Unit;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPGenius\CalcWise\Calculator\Registry;
use WPGenius\CalcWise\Shortcode;
use WPGenius\CalcWise\Tests\TestCase;

/**
 * Shortcode tests.
 */
class ShortcodeTest extends TestCase {

	const FIELDS = array(
		'monthlyAmount' => array(
			'min'     => 500,
			'max'     => 1000000,
			'step'    => 500,
			'default' => 25000,
		),
		'years'         => array(
			'min'     => 1,
			'max'     => 40,
			'step'    => 1,
			'default' => 10,
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
	}

	protected function tearDown(): void {
		Registry::reset();
		parent::tearDown();
	}

	public function test_attributes_become_field_overrides_case_insensitively() {
		$overrides = Shortcode::field_overrides(
			self::FIELDS,
			array(
				'monthlyamount' => '10000',
				'years_max'     => '25',
				'YEARS'         => '15',
				'years_step'    => 'two',
				'unknown'       => '5',
			)
		);

		$this->assertSame(
			array(
				'monthlyAmount' => array( 'default' => 10000.0 ),
				'years'         => array(
					'default' => 15.0,
					'max'     => 25.0,
				),
			),
			$overrides
		);
	}

	public function test_unknown_type_is_hidden_from_visitors() {
		Filters\expectApplied( 'calcwise_calculators' )->andReturn( array() );
		Functions\when( 'current_user_can' )->justReturn( false );

		$this->assertSame( '', Shortcode::render( array( 'type' => 'nope' ) ) );
		$this->assertSame( '', Shortcode::render( '' ) );
	}

	public function test_unknown_type_explains_itself_to_editors() {
		Filters\expectApplied( 'calcwise_calculators' )->andReturn(
			array( 'sip' => array( 'fields' => self::FIELDS ) )
		);
		Functions\when( 'current_user_can' )->justReturn( true );

		$html = Shortcode::render( array( 'type' => 'nope<script>' ) );

		$this->assertStringContainsString( 'calcwise-notice', $html );
		$this->assertStringContainsString( 'nopescript', $html, 'The type is sanitised before it is shown.' );
		$this->assertStringContainsString( 'sip', $html );
		$this->assertStringNotContainsString( '<script>', $html );
	}
}
