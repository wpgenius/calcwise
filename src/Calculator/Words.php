<?php
/**
 * Translated number words and short units for the calculator scripts.
 *
 * The financial-calculators library writes amounts in words ("Twenty Five
 * Lakh") and short chart labels ("2.5L") in English. These give it the site
 * language instead. Each method returns null while nothing is translated, so
 * English pages don't carry the extra settings.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Calculator;

defined( 'ABSPATH' ) || exit;

/**
 * Number words for amountInWords() and units for short numbers.
 */
class Words {

	/**
	 * The library's English words, in its `words` option shape.
	 */
	const ENGLISH = array(
		'ones'          => array( '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen' ),
		'tens'          => array( '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety' ),
		'hundred'       => 'Hundred',
		'and'           => 'and',
		'zero'          => 'Zero',
		'minus'         => 'Minus',
		'indian'        => array(
			'thousand' => 'Thousand',
			'lakh'     => 'Lakh',
			'crore'    => 'Crore',
		),
		'international' => array( '', 'Thousand', 'Million', 'Billion', 'Trillion', 'Quadrillion', 'Quintillion', 'Sextillion', 'Septillion', 'Octillion', 'Nonillion', 'Decillion' ),
	);

	/**
	 * The library's English short units, keyed by unit.
	 */
	const ENGLISH_UNITS = array(
		'K'  => 'K',
		'L'  => 'L',
		'Cr' => 'Cr',
		'M'  => 'M',
		'B'  => 'B',
		'T'  => 'T',
	);

	/**
	 * Number words in the site language, for amountInWords( value, { words } ).
	 *
	 * @return array<string, mixed>|null Null when they are still English.
	 */
	public static function number_words() {
		$words = array(
			'ones'          => array(
				'',
				_x( 'One', 'number in words', 'calcwise' ),
				_x( 'Two', 'number in words', 'calcwise' ),
				_x( 'Three', 'number in words', 'calcwise' ),
				_x( 'Four', 'number in words', 'calcwise' ),
				_x( 'Five', 'number in words', 'calcwise' ),
				_x( 'Six', 'number in words', 'calcwise' ),
				_x( 'Seven', 'number in words', 'calcwise' ),
				_x( 'Eight', 'number in words', 'calcwise' ),
				_x( 'Nine', 'number in words', 'calcwise' ),
				_x( 'Ten', 'number in words', 'calcwise' ),
				_x( 'Eleven', 'number in words', 'calcwise' ),
				_x( 'Twelve', 'number in words', 'calcwise' ),
				_x( 'Thirteen', 'number in words', 'calcwise' ),
				_x( 'Fourteen', 'number in words', 'calcwise' ),
				_x( 'Fifteen', 'number in words', 'calcwise' ),
				_x( 'Sixteen', 'number in words', 'calcwise' ),
				_x( 'Seventeen', 'number in words', 'calcwise' ),
				_x( 'Eighteen', 'number in words', 'calcwise' ),
				_x( 'Nineteen', 'number in words', 'calcwise' ),
			),
			'tens'          => array(
				'',
				'',
				_x( 'Twenty', 'number in words', 'calcwise' ),
				_x( 'Thirty', 'number in words', 'calcwise' ),
				_x( 'Forty', 'number in words', 'calcwise' ),
				_x( 'Fifty', 'number in words', 'calcwise' ),
				_x( 'Sixty', 'number in words', 'calcwise' ),
				_x( 'Seventy', 'number in words', 'calcwise' ),
				_x( 'Eighty', 'number in words', 'calcwise' ),
				_x( 'Ninety', 'number in words', 'calcwise' ),
			),
			'hundred'       => _x( 'Hundred', 'number in words', 'calcwise' ),
			/* translators: Joins hundreds and the rest, as in "One Hundred and Five". Translate as a single space if your language has no such word. */
			'and'           => _x( 'and', 'number in words', 'calcwise' ),
			'zero'          => _x( 'Zero', 'number in words', 'calcwise' ),
			'minus'         => _x( 'Minus', 'number in words', 'calcwise' ),
			'indian'        => array(
				'thousand' => _x( 'Thousand', 'number in words', 'calcwise' ),
				'lakh'     => _x( 'Lakh', 'number in words, 1,00,000', 'calcwise' ),
				'crore'    => _x( 'Crore', 'number in words, 1,00,00,000', 'calcwise' ),
			),
			'international' => array(
				'',
				_x( 'Thousand', 'number in words', 'calcwise' ),
				_x( 'Million', 'number in words', 'calcwise' ),
				_x( 'Billion', 'number in words', 'calcwise' ),
				_x( 'Trillion', 'number in words', 'calcwise' ),
				_x( 'Quadrillion', 'number in words', 'calcwise' ),
				_x( 'Quintillion', 'number in words', 'calcwise' ),
				_x( 'Sextillion', 'number in words', 'calcwise' ),
				_x( 'Septillion', 'number in words', 'calcwise' ),
				_x( 'Octillion', 'number in words', 'calcwise' ),
				_x( 'Nonillion', 'number in words', 'calcwise' ),
				_x( 'Decillion', 'number in words', 'calcwise' ),
			),
		);

		// A word translated as a space means "no word here".
		array_walk_recursive(
			$words,
			static function ( &$word ) {
				$word = trim( $word );
			}
		);

		return self::ENGLISH === $words ? null : $words;
	}

	/**
	 * Short units after a number on the chart axis ("2.5L", "1.2M").
	 *
	 * @return array<string, string>|null Null when they are still English.
	 */
	public static function units() {
		$units = array(
			'K'  => _x( 'K', 'short for thousand, after a number', 'calcwise' ),
			'L'  => _x( 'L', 'short for lakh, after a number', 'calcwise' ),
			'Cr' => _x( 'Cr', 'short for crore, after a number', 'calcwise' ),
			'M'  => _x( 'M', 'short for million, after a number', 'calcwise' ),
			'B'  => _x( 'B', 'short for billion, after a number', 'calcwise' ),
			'T'  => _x( 'T', 'short for trillion, after a number', 'calcwise' ),
		);

		return self::ENGLISH_UNITS === $units ? null : $units;
	}
}
