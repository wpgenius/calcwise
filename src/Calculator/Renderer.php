<?php
/**
 * Renders a calculator. Blocks, shortcodes and widgets all call this, so every
 * calculator has the same markup wherever it is placed.
 *
 * The markup is complete without JavaScript (labels, inputs, limits, result
 * labels); the calculator's script fills in the results, the chart and the
 * table. Each instance carries its own settings in data-calcwise-settings, so
 * several calculators work on one page.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Calculator;

use WPGenius\CalcWise\Api;
use WPGenius\CalcWise\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Calculator markup.
 */
class Renderer {

	/**
	 * Instances rendered on this request, for unique IDs.
	 *
	 * @var int
	 */
	private static $count = 0;

	/**
	 * Render a calculator.
	 *
	 * @param string               $type Calculator type, e.g. 'sip'.
	 * @param array<string, mixed> $args {
	 *     Optional. Per-instance overrides.
	 *
	 *     @type array $fields Field key => array of 'default', 'min', 'max', 'step'.
	 * }
	 * @return string Empty for an unknown or unbuilt calculator.
	 */
	public static function render( $type, array $args = array() ) {
		$type       = sanitize_key( $type );
		$definition = Registry::get( $type );
		if ( null === $definition || ! Assets::enqueue( $type ) ) {
			return '';
		}

		$fields   = self::fields( $definition['fields'], isset( $args['fields'] ) ? (array) $args['fields'] : array() );
		$format   = self::format();
		$id       = 'calcwise-' . ( ++self::$count );
		$settings = array(
			'fields' => array_map(
				static function ( $field ) {
					return array_intersect_key( $field, array_flip( array( 'min', 'max', 'step', 'default' ) ) );
				},
				$fields
			),
			'format' => $format,
			'series' => $definition['chart']['series'],
			'i18n'   => array(
				'year'  => __( 'Year', 'calcwise' ),
				'error' => __( 'Please check the values entered.', 'calcwise' ),
			),
		);

		$style = '';
		$color = sanitize_hex_color( (string) Api::get_setting( 'accent_color', '' ) );
		if ( $color ) {
			$style = ' style="--calcwise-accent: ' . esc_attr( $color ) . ';"';
		}

		$html  = '<div class="calcwise-calculator calcwise-calculator--' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '"';
		$html .= ' data-calcwise-type="' . esc_attr( $type ) . '"';
		$html .= ' data-calcwise-settings="' . esc_attr( (string) wp_json_encode( $settings ) ) . '"' . $style . '>';

		$html .= '<div class="calcwise-calculator__body">';
		$html .= '<div class="calcwise-calculator__inputs">';
		foreach ( $fields as $key => $field ) {
			$html .= self::field( $id, $key, $field, $format );
		}
		$html .= '</div>';

		$html .= '<div class="calcwise-calculator__output">';
		$html .= self::results( $definition['results'], $format );
		$html .= '<p class="calcwise-message" role="alert" hidden></p>';
		$html .= self::chart( $id, $definition['chart']['label'] );
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<p class="calcwise-sr-only" aria-live="polite" data-calcwise-status></p>';

		$disclaimer = (string) Api::get_setting( 'disclaimer', __( 'Results are estimates based on the values you enter. They are not financial advice.', 'calcwise' ) );
		if ( '' !== trim( $disclaimer ) ) {
			$html .= '<p class="calcwise-disclaimer">' . esc_html( $disclaimer ) . '</p>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Apply per-instance overrides to the defined fields. Every value is
	 * numeric, min < max, step > 0 and the default stays between min and max.
	 *
	 * @param array<string, array<string, mixed>> $fields    Defined fields.
	 * @param array<string, mixed>                $overrides Field key => overrides.
	 * @return array<string, array<string, mixed>>
	 */
	public static function fields( array $fields, array $overrides ) {
		foreach ( $fields as $key => $field ) {
			$override = isset( $overrides[ $key ] ) ? (array) $overrides[ $key ] : array();
			foreach ( array( 'min', 'max', 'step', 'default' ) as $prop ) {
				if ( isset( $override[ $prop ] ) && is_numeric( $override[ $prop ] ) ) {
					$field[ $prop ] = (float) $override[ $prop ];
				}
			}
			if ( $field['max'] <= $field['min'] ) {
				$field['min'] = $fields[ $key ]['min'];
				$field['max'] = $fields[ $key ]['max'];
			}
			if ( $field['step'] <= 0 ) {
				$field['step'] = $fields[ $key ]['step'];
			}
			$field['default'] = min( max( $field['default'], $field['min'] ), $field['max'] );
			$fields[ $key ]   = $field;
		}
		return $fields;
	}

	/**
	 * Number format from the settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function format() {
		$system = Api::get_setting( 'number_system', 'indian' );
		return array(
			'system'   => 'international' === $system ? 'international' : 'indian',
			'symbol'   => (string) Api::get_setting( 'currency_symbol', '₹' ),
			'position' => 'after' === Api::get_setting( 'currency_position', 'before' ) ? 'after' : 'before',
			'words'    => (bool) Api::get_setting( 'amount_in_words', 1 ),
		);
	}

	/**
	 * One input: a number box and a slider for the same value.
	 *
	 * @param string               $id     Instance ID.
	 * @param string               $key    Field key.
	 * @param array<string, mixed> $field  Field.
	 * @param array<string, mixed> $format Number format.
	 * @return string
	 */
	private static function field( $id, $key, array $field, array $format ) {
		$input_id = $id . '-' . $key;
		$prefix   = '';
		$suffix   = '';
		switch ( $field['unit'] ) {
			case 'money':
				if ( 'after' === $format['position'] ) {
					$suffix = $format['symbol'];
				} else {
					$prefix = $format['symbol'];
				}
				break;
			case 'percent':
				$suffix = '%';
				break;
			case 'years':
				$suffix = _x( 'yr', 'years, short unit after a number', 'calcwise' );
				break;
			case 'months':
				$suffix = _x( 'mo', 'months, short unit after a number', 'calcwise' );
				break;
		}

		$attrs = sprintf(
			'min="%s" max="%s" step="%s" value="%s"',
			esc_attr( self::number( $field['min'] ) ),
			esc_attr( self::number( $field['max'] ) ),
			esc_attr( self::number( $field['step'] ) ),
			esc_attr( self::number( $field['default'] ) )
		);

		$html  = '<div class="calcwise-field calcwise-field--' . esc_attr( $field['unit'] ) . '" data-field="' . esc_attr( $key ) . '">';
		$html .= '<label class="calcwise-field__label" for="' . esc_attr( $input_id ) . '">' . esc_html( $field['label'] ) . '</label>';
		$html .= '<div class="calcwise-field__box">';
		if ( '' !== $prefix ) {
			$html .= '<span class="calcwise-field__unit" aria-hidden="true">' . esc_html( $prefix ) . '</span>';
		}
		$html .= '<input class="calcwise-field__number" type="number" inputmode="decimal" id="' . esc_attr( $input_id ) . '" ' . $attrs . ' data-default="' . esc_attr( self::number( $field['default'] ) ) . '">';
		if ( '' !== $suffix ) {
			$html .= '<span class="calcwise-field__unit" aria-hidden="true">' . esc_html( $suffix ) . '</span>';
		}
		$html .= '</div>';
		$html .= '<input class="calcwise-field__range" type="range" ' . $attrs . ' tabindex="-1" aria-hidden="true">';
		$html .= '<div class="calcwise-field__limits" aria-hidden="true"><span data-limit="min">' . esc_html( self::limit( $field['min'], $field['unit'], $prefix, $suffix ) ) . '</span><span data-limit="max">' . esc_html( self::limit( $field['max'], $field['unit'], $prefix, $suffix ) ) . '</span></div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Result labels with empty values for the script to fill.
	 *
	 * @param array<string, array<string, mixed>> $results Result definitions.
	 * @param array<string, mixed>                $format  Number format.
	 * @return string
	 */
	private static function results( array $results, array $format ) {
		$html = '<dl class="calcwise-results">';
		foreach ( $results as $key => $result ) {
			$class = 'calcwise-result' . ( $result['highlight'] ? ' calcwise-result--highlight' : '' );
			$html .= '<div class="' . esc_attr( $class ) . '" data-result="' . esc_attr( $key ) . '" data-format="' . esc_attr( $result['format'] ) . '"' . ( $result['highlight'] ? ' data-highlight' : '' ) . '>';
			$html .= '<dt class="calcwise-result__label">' . esc_html( $result['label'] ) . '</dt>';
			$html .= '<dd class="calcwise-result__value">&ndash;</dd>';
			if ( $result['words'] && $format['words'] ) {
				$html .= '<dd class="calcwise-result__words"></dd>';
			}
			$html .= '</div>';
		}
		$html .= '</dl>';
		return $html;
	}

	/**
	 * Chart canvas plus a table of the same values as a text alternative.
	 *
	 * @param string $id    Instance ID.
	 * @param string $label What the chart shows.
	 * @return string
	 */
	private static function chart( $id, $label ) {
		$html  = '<figure class="calcwise-chart">';
		$html .= '<div class="calcwise-chart__canvas"><canvas role="img" aria-label="' . esc_attr( $label ) . '" aria-describedby="' . esc_attr( $id ) . '-table"></canvas></div>';
		$html .= '<details class="calcwise-table">';
		$html .= '<summary>' . esc_html__( 'Show the values as a table', 'calcwise' ) . '</summary>';
		$html .= '<div class="calcwise-table__scroll"><table id="' . esc_attr( $id ) . '-table"><caption>' . esc_html( $label ) . '</caption><thead></thead><tbody></tbody></table></div>';
		$html .= '</details>';
		$html .= '</figure>';
		return $html;
	}

	/**
	 * A number as an HTML attribute value (no trailing zeros, no exponent).
	 *
	 * @param float $value Number.
	 * @return string
	 */
	private static function number( $value ) {
		$text = rtrim( rtrim( number_format( (float) $value, 6, '.', '' ), '0' ), '.' );
		return '' === $text || '-0' === $text ? '0' : $text;
	}

	/**
	 * A min/max label under a slider.
	 *
	 * @param float  $value  Number.
	 * @param string $unit   Field unit.
	 * @param string $prefix Unit before the number.
	 * @param string $suffix Unit after the number.
	 * @return string
	 */
	private static function limit( $value, $unit, $prefix, $suffix ) {
		$text = 'money' === $unit ? number_format_i18n( (float) $value ) : self::number( $value );
		return $prefix . $text . ( '' !== $suffix ? ' ' . $suffix : '' );
	}
}
