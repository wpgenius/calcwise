<?php
/**
 * The [calcwise] shortcode.
 *
 *     [calcwise type="sip"]
 *     [calcwise type="emi" principal="2500000" years="15" years_max="25"]
 *
 * type: sip, lumpsum, swp, goal or emi.
 * Any field of that calculator can be set: `<field>` for its starting value,
 * `<field>_min`, `<field>_max` and `<field>_step` for its limits. Field names
 * are case-insensitive (WordPress lowercases shortcode attributes), e.g.
 * monthlyamount="10000" for the SIP's monthly investment. Values are
 * validated and clamped by the renderer; anything else is ignored.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise;

use WPGenius\CalcWise\Calculator\Registry;
use WPGenius\CalcWise\Calculator\Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode.
 */
class Shortcode extends Api {

	/**
	 * Shortcode tag.
	 */
	const TAG = 'calcwise';

	/**
	 * Register the shortcode.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register [calcwise].
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( self::TAG, array( __CLASS__, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes (a string when there are none).
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = is_array( $atts ) ? $atts : array();
		$type = isset( $atts['type'] ) ? sanitize_key( $atts['type'] ) : '';

		$definition = '' === $type ? null : Registry::get( $type );
		if ( null === $definition ) {
			return self::unknown_type( $type );
		}

		return Renderer::render( $type, array( 'fields' => self::field_overrides( $definition['fields'], $atts ) ) );
	}

	/**
	 * Turn shortcode attributes into field overrides for the renderer.
	 *
	 * @param array<string, array<string, mixed>> $fields Calculator fields.
	 * @param array<string, mixed>                $atts   Shortcode attributes (lowercased keys).
	 * @return array<string, array<string, float>>
	 */
	public static function field_overrides( array $fields, array $atts ) {
		$atts      = array_change_key_case( $atts, CASE_LOWER );
		$overrides = array();
		foreach ( array_keys( $fields ) as $key ) {
			$name = strtolower( $key );
			foreach ( array(
				''      => 'default',
				'_min'  => 'min',
				'_max'  => 'max',
				'_step' => 'step',
			) as $suffix => $prop ) {
				if ( isset( $atts[ $name . $suffix ] ) && is_numeric( $atts[ $name . $suffix ] ) ) {
					$overrides[ $key ][ $prop ] = (float) $atts[ $name . $suffix ];
				}
			}
		}
		return $overrides;
	}

	/**
	 * What to show for a missing or unknown type: nothing for visitors, a short
	 * note for people who can edit the page.
	 *
	 * @param string $type Requested type.
	 * @return string
	 */
	private static function unknown_type( $type ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}
		$types = implode( ', ', array_keys( Registry::all() ) );
		$text  = '' === $type
			/* translators: %s: list of calculator types, e.g. "sip, lumpsum, emi". */
			? sprintf( __( 'CalcWise: add a calculator type, e.g. [calcwise type="sip"]. Types: %s.', 'calcwise' ), $types )
			/* translators: 1: the type that was asked for, 2: list of calculator types. */
			: sprintf( __( 'CalcWise: there is no "%1$s" calculator. Types: %2$s.', 'calcwise' ), $type, $types );

		return '<p class="calcwise-notice">' . esc_html( $text ) . '</p>';
	}
}
