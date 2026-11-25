<?php
/**
 * Class autoloader.
 *
 * Maps a class name to a file in src/, one class per file:
 *   WPGenius\CalcWise\Plugin          → src/Plugin.php
 *   WPGenius\CalcWise\Admin\Settings  → src/Admin/Settings.php
 *
 * @package WPGenius\CalcWise
 */

defined( 'ABSPATH' ) || exit;

spl_autoload_register(
	static function ( $class_name ) {
		$prefix = 'WPGenius\\CalcWise\\';

		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative = str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) );
		$file     = __DIR__ . '/' . $relative . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);
