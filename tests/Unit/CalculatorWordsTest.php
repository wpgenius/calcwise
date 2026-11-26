<?php
/**
 * Tests for src/Calculator/Words.php.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Unit;

use Brain\Monkey\Functions;
use WPGenius\CalcWise\Calculator\Words;
use WPGenius\CalcWise\Tests\TestCase;

/**
 * Number words and short units.
 */
class CalculatorWordsTest extends TestCase {

	public function test_english_sends_nothing(): void {
		$this->assertNull( Words::number_words() );
		$this->assertNull( Words::units() );
	}

	public function test_translated_words_keep_the_library_shape(): void {
		$translations = array(
			'One'   => 'Ek',
			'Lakh'  => 'Laakh',
			'and'   => ' ',
			'Cr'    => 'Karod',
			'Forty' => 'Chaalis',
		);
		Functions\when( '_x' )->alias(
			static function ( $text ) use ( $translations ) {
				return isset( $translations[ $text ] ) ? $translations[ $text ] : $text;
			}
		);

		$words = Words::number_words();
		$this->assertIsArray( $words );
		$this->assertCount( 20, $words['ones'] );
		$this->assertCount( 10, $words['tens'] );
		$this->assertCount( count( Words::ENGLISH['international'] ), $words['international'] );
		$this->assertSame( 'Ek', $words['ones'][1] );
		$this->assertSame( 'Chaalis', $words['tens'][4] );
		$this->assertSame( 'Laakh', $words['indian']['lakh'] );
		$this->assertSame( '', $words['and'], 'A space means no word.' );
		$this->assertSame( 'Hundred', $words['hundred'] );

		$units = Words::units();
		$this->assertSame( 'Karod', $units['Cr'] );
		$this->assertSame( 'L', $units['L'] );
	}
}
