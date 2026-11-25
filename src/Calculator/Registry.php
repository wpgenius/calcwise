<?php
/**
 * The calculators CalcWise knows about.
 *
 * Each calculator lives in its own folder, src/calculators/<type>/:
 *
 * - definition.php  returns its fields, results and chart labels (below);
 * - view.js         registers it with the runtime (built to build/calculators/<type>/);
 * - style.css       its own styles, if any (imported by view.js).
 *
 * definition.php returns:
 *
 *     array(
 *         'title'       => __( 'SIP calculator', 'calcwise' ),
 *         'description' => __( 'What a monthly investment grows to.', 'calcwise' ),
 *         'fields'      => array(
 *             // Keys are the library's input names.
 *             'monthlyAmount' => array( 'label' => …, 'unit' => 'money', 'default' => 25000, 'min' => 500, 'max' => 1000000, 'step' => 500 ),
 *         ),
 *         'results'     => array(
 *             'total' => array( 'label' => …, 'words' => true, 'highlight' => true ),
 *         ),
 *         'chart'       => array(
 *             'label'  => __( 'Growth of the investment by year', 'calcwise' ),
 *             'series' => array( 'invested' => __( 'Amount invested', 'calcwise' ) ),
 *         ),
 *     )
 *
 * Units: money, percent, years, months, number.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Calculator;

defined( 'ABSPATH' ) || exit;

/**
 * Calculator definitions.
 */
class Registry {

	/**
	 * Allowed field units.
	 */
	const UNITS = array( 'money', 'percent', 'years', 'months', 'number' );

	/**
	 * Definitions loaded on this request, keyed by type.
	 *
	 * @var array<string, array<string, mixed>>|null
	 */
	private static $definitions = null;

	/**
	 * Every calculator, keyed by type.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function all() {
		if ( null === self::$definitions ) {
			$definitions = array();
			$files       = glob( CALCWISE_DIR_PATH . 'src/calculators/*/definition.php' );
			foreach ( $files ? $files : array() as $file ) {
				$type       = sanitize_key( basename( dirname( $file ) ) );
				$definition = include $file;
				if ( '' !== $type && is_array( $definition ) ) {
					$definitions[ $type ] = $definition;
				}
			}

			/**
			 * Filters the calculator definitions, keyed by type.
			 *
			 * @param array $definitions Definitions from src/calculators/<type>/definition.php.
			 */
			$definitions = apply_filters( 'calcwise_calculators', $definitions );

			self::$definitions = array();
			foreach ( (array) $definitions as $type => $definition ) {
				$definition = self::normalize( (array) $definition );
				if ( $definition['fields'] ) {
					self::$definitions[ sanitize_key( $type ) ] = $definition;
				}
			}
		}
		return self::$definitions;
	}

	/**
	 * One calculator's definition.
	 *
	 * @param string $type Calculator type.
	 * @return array<string, mixed>|null Null for an unknown type.
	 */
	public static function get( $type ) {
		$all  = self::all();
		$type = sanitize_key( $type );
		return isset( $all[ $type ] ) ? $all[ $type ] : null;
	}

	/**
	 * Forget the loaded definitions (tests, or after changing the filter).
	 *
	 * @return void
	 */
	public static function reset() {
		self::$definitions = null;
	}

	/**
	 * Fill in missing keys and drop invalid fields.
	 *
	 * @param array<string, mixed> $definition Raw definition.
	 * @return array<string, mixed>
	 */
	private static function normalize( array $definition ) {
		$fields = array();
		foreach ( isset( $definition['fields'] ) ? (array) $definition['fields'] : array() as $key => $field ) {
			$field = (array) $field;
			if ( ! preg_match( '/^[A-Za-z][A-Za-z0-9_]*$/', (string) $key ) || ! isset( $field['min'], $field['max'] ) ) {
				continue;
			}
			$min  = (float) $field['min'];
			$max  = (float) $field['max'];
			$step = isset( $field['step'] ) && (float) $field['step'] > 0 ? (float) $field['step'] : 1.0;
			if ( $max <= $min ) {
				continue;
			}
			$default = isset( $field['default'] ) ? (float) $field['default'] : $min;

			$fields[ $key ] = array(
				'label'   => isset( $field['label'] ) ? (string) $field['label'] : $key,
				'unit'    => isset( $field['unit'] ) && in_array( $field['unit'], self::UNITS, true ) ? $field['unit'] : 'number',
				'min'     => $min,
				'max'     => $max,
				'step'    => $step,
				'default' => min( max( $default, $min ), $max ),
			);
		}

		$results = array();
		foreach ( isset( $definition['results'] ) ? (array) $definition['results'] : array() as $key => $result ) {
			$result          = (array) $result;
			$results[ $key ] = array(
				'label'     => isset( $result['label'] ) ? (string) $result['label'] : (string) $key,
				'words'     => ! empty( $result['words'] ),
				'highlight' => ! empty( $result['highlight'] ),
				'format'    => isset( $result['format'] ) && in_array( $result['format'], array( 'money', 'number', 'text' ), true ) ? $result['format'] : 'money',
			);
		}

		$chart = isset( $definition['chart'] ) ? (array) $definition['chart'] : array();

		return array(
			'title'       => isset( $definition['title'] ) ? (string) $definition['title'] : '',
			'description' => isset( $definition['description'] ) ? (string) $definition['description'] : '',
			'fields'      => $fields,
			'results'     => $results,
			'chart'       => array(
				'label'  => isset( $chart['label'] ) ? (string) $chart['label'] : '',
				'series' => isset( $chart['series'] ) ? array_map( 'strval', (array) $chart['series'] ) : array(),
			),
		);
	}
}
