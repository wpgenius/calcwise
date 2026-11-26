<?php
/**
 * Every built-in calculator definition is valid.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Unit;

use Brain\Monkey\Functions;
use WPGenius\CalcWise\Calculator\Registry;
use WPGenius\CalcWise\Tests\TestCase;

/**
 * Definition tests.
 */
class CalculatorDefinitionsTest extends TestCase {

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
	 * Built-in calculators and the library inputs each must define.
	 *
	 * @return array<string, array{0: string, 1: string[], 2: string[]}>
	 */
	public static function calculators() {
		return array(
			'sip'     => array( 'sip', array( 'monthlyAmount', 'annualRate', 'years' ), array( 'invested', 'returns', 'total' ) ),
			'lumpsum' => array( 'lumpsum', array( 'amount', 'annualRate', 'years' ), array( 'invested', 'returns', 'total' ) ),
			'swp'     => array( 'swp', array( 'investment', 'monthlyWithdrawal', 'annualRate', 'years' ), array( 'investment', 'totalWithdrawn', 'finalBalance' ) ),
			'emi'     => array( 'emi', array( 'principal', 'annualRate', 'years' ), array( 'emi', 'principal', 'totalInterest', 'totalPayment' ) ),
			'goal'    => array( 'goal', array( 'presentCost', 'currentSavings', 'inflationRate', 'annualRate', 'years' ), array( 'targetYear', 'futureCost', 'savingsFutureValue', 'monthlyInvestment' ) ),
		);
	}

	/**
	 * Each definition has its fields, results, chart label and one highlight.
	 *
	 * @dataProvider calculators
	 *
	 * @param string   $type    Calculator type.
	 * @param string[] $fields  Expected field keys (library inputs).
	 * @param string[] $results Expected result keys.
	 */
	public function test_definition_is_complete( $type, array $fields, array $results ) {
		$definition = Registry::get( $type );

		$this->assertNotNull( $definition, "src/calculators/$type/definition.php loads." );
		$this->assertNotSame( '', $definition['title'] );
		$this->assertSame( $fields, array_keys( $definition['fields'] ) );
		$this->assertSame( $results, array_keys( $definition['results'] ) );
		$this->assertNotSame( '', $definition['chart']['label'] );

		foreach ( $definition['fields'] as $key => $field ) {
			$this->assertGreaterThanOrEqual( $field['min'], $field['default'], "$key default >= min" );
			$this->assertLessThanOrEqual( $field['max'], $field['default'], "$key default <= max" );
		}

		$highlighted = array_filter( array_column( $definition['results'], 'highlight' ) );
		$this->assertCount( 1, $highlighted, 'Exactly one highlighted result (announced to screen readers).' );
	}
}
