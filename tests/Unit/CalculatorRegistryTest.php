<?php
/**
 * Tests for src/Calculator/Registry.php.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Unit;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPGenius\CalcWise\Calculator\Registry;
use WPGenius\CalcWise\Tests\TestCase;

/**
 * Registry tests.
 */
class CalculatorRegistryTest extends TestCase {

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

	/**
	 * Provide definitions through the filter.
	 *
	 * @param array $definitions Definitions keyed by type.
	 */
	private function definitions( array $definitions ) {
		Filters\expectApplied( 'calcwise_calculators' )->andReturn( $definitions );
	}

	public function test_fields_are_normalised() {
		$this->definitions(
			array(
				'demo' => array(
					'title'  => 'Demo',
					'fields' => array(
						'amount'  => array(
							'label'   => 'Amount',
							'unit'    => 'money',
							'min'     => 100,
							'max'     => 1000,
							'step'    => 0,
							'default' => 5000,
						),
						'rate'    => array(
							'label' => 'Rate',
							'unit'  => 'weird',
							'min'   => 1,
							'max'   => 30,
						),
						'broken'  => array(
							'label' => 'No max',
							'min'   => 1,
						),
						'reverse' => array(
							'label' => 'Max below min',
							'min'   => 10,
							'max'   => 5,
						),
						'1bad'    => array(
							'label' => 'Bad key',
							'min'   => 1,
							'max'   => 2,
						),
					),
				),
			)
		);

		$demo = Registry::get( 'demo' );

		$this->assertSame( array( 'amount', 'rate' ), array_keys( $demo['fields'] ) );
		$this->assertSame( 1000.0, $demo['fields']['amount']['default'], 'Default is clamped to max.' );
		$this->assertSame( 1.0, $demo['fields']['amount']['step'], 'A zero step falls back to 1.' );
		$this->assertSame( 'number', $demo['fields']['rate']['unit'], 'Unknown unit falls back to number.' );
		$this->assertSame( 1.0, $demo['fields']['rate']['default'], 'Missing default falls back to min.' );
	}

	public function test_calculator_without_valid_fields_is_left_out() {
		$this->definitions(
			array(
				'empty' => array( 'title' => 'Nothing' ),
				'ok'    => array(
					'fields' => array(
						'x' => array(
							'min' => 0,
							'max' => 1,
						),
					),
				),
			)
		);

		$this->assertNull( Registry::get( 'empty' ) );
		$this->assertNotNull( Registry::get( 'ok' ) );
		$this->assertNull( Registry::get( 'missing' ) );
	}

	public function test_results_and_chart_get_defaults() {
		$this->definitions(
			array(
				'demo' => array(
					'fields'  => array(
						'x' => array(
							'min' => 0,
							'max' => 1,
						),
					),
					'results' => array(
						'total' => array(
							'label'     => 'Total',
							'words'     => 1,
							'highlight' => true,
						),
						'years' => array(
							'label'  => 'Years',
							'format' => 'number',
						),
						'odd'   => array( 'format' => 'html' ),
					),
					'chart'   => array( 'series' => array( 'total' => 'Total value' ) ),
				),
			)
		);

		$demo = Registry::get( 'demo' );

		$this->assertTrue( $demo['results']['total']['words'] );
		$this->assertTrue( $demo['results']['total']['highlight'] );
		$this->assertSame( 'number', $demo['results']['years']['format'] );
		$this->assertSame( 'money', $demo['results']['odd']['format'], 'Unknown formats fall back to money.' );
		$this->assertSame( array( 'total' => 'Total value' ), $demo['chart']['series'] );
		$this->assertSame( '', $demo['chart']['label'] );
	}
}
