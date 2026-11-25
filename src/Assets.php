<?php
/**
 * Registers the built scripts and styles.
 *
 * - calcwise-runtime: shared runtime (Chart.js and the calculator registry),
 *   from build/runtime/index.js.
 * - calcwise-<type>:  one per calculator, from build/calculators/<type>/view.js
 *   (+ view.css), depending on calcwise-runtime.
 *
 * Nothing is enqueued here: a calculator's render function calls
 * Assets::enqueue( $type ), so a page loads only the calculators it shows.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise;

defined( 'ABSPATH' ) || exit;

/**
 * Asset registration.
 */
class Assets extends Api {

	/**
	 * Handle of the shared runtime.
	 */
	const RUNTIME = 'calcwise-runtime';

	/**
	 * Register on init, so blocks, shortcodes and widgets can use the handles.
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register the runtime and every built calculator.
	 *
	 * @return void
	 */
	public static function register() {
		self::register_entry( self::RUNTIME, 'runtime/index' );

		foreach ( self::calculator_types() as $type ) {
			self::register_entry( self::handle( $type ), 'calculators/' . $type . '/view', array( self::RUNTIME ) );
		}
	}

	/**
	 * Enqueue one calculator's script and style (and the runtime they need).
	 *
	 * @param string $type Calculator type, e.g. 'sip'.
	 * @return bool False if that calculator isn't built.
	 */
	public static function enqueue( $type ) {
		$handle = self::handle( $type );
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			return false;
		}

		wp_enqueue_script( $handle );
		if ( wp_style_is( self::RUNTIME, 'registered' ) ) {
			wp_enqueue_style( self::RUNTIME );
		}
		if ( wp_style_is( $handle, 'registered' ) ) {
			wp_enqueue_style( $handle );
		}
		return true;
	}

	/**
	 * Handle of a calculator's script and style.
	 *
	 * @param string $type Calculator type.
	 * @return string
	 */
	public static function handle( $type ) {
		return 'calcwise-' . sanitize_key( $type );
	}

	/**
	 * Calculator types that have a built script: build/calculators/<type>/view.asset.php.
	 *
	 * @return string[]
	 */
	public static function calculator_types() {
		$files = glob( CALCWISE_DIR_PATH . 'build/calculators/*/view.asset.php' );
		$types = array();
		foreach ( $files ? $files : array() as $file ) {
			$type = sanitize_key( basename( dirname( $file ) ) );
			if ( '' !== $type ) {
				$types[] = $type;
			}
		}
		return $types;
	}

	/**
	 * Register a built entry's script, and its style when there is one.
	 *
	 * @param string   $handle Script and style handle.
	 * @param string   $entry  Entry path inside build/, without extension.
	 * @param string[] $deps   Extra script dependencies.
	 * @return void
	 */
	private static function register_entry( $handle, $entry, array $deps = array() ) {
		$asset_file = CALCWISE_DIR_PATH . 'build/' . $entry . '.asset.php';
		if ( ! is_readable( $asset_file ) ) {
			return;
		}

		$asset   = require $asset_file;
		$deps    = array_merge( isset( $asset['dependencies'] ) ? (array) $asset['dependencies'] : array(), $deps );
		$version = CALCWISE_VERSION . ( isset( $asset['version'] ) ? '-' . substr( (string) $asset['version'], 0, 8 ) : '' );

		wp_register_script(
			$handle,
			self::asset_url( 'build/' . $entry . '.js' ),
			$deps,
			$version,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		if ( is_readable( CALCWISE_DIR_PATH . 'build/' . $entry . '.css' ) ) {
			wp_register_style( $handle, self::asset_url( 'build/' . $entry . '.css' ), array(), $version );
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}
}
