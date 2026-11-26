<?php
/**
 * Shared helpers used by every part of the plugin.
 *
 * Other classes extend Api, so inside them you can write self::get_setting( 'key' ).
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise;

defined( 'ABSPATH' ) || exit;

/**
 * Common base class.
 */
class Api {

	/**
	 * Name of the one option that stores every setting (an array).
	 */
	const OPTION = 'calcwise_settings';

	/*
	 * ------------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------------
	 */

	/**
	 * All settings: the saved values on top of the defaults from Admin\Settings::fields().
	 *
	 * @return array<string, mixed>
	 */
	public static function get_settings() {
		$saved = get_option( self::OPTION, array() );
		return array_merge( Admin\Settings::defaults(), is_array( $saved ) ? $saved : array() );
	}

	/**
	 * One setting.
	 *
	 * @param string $key      Field key from Admin\Settings::fields().
	 * @param mixed  $fallback Returned when the key is unknown.
	 * @return mixed
	 */
	public static function get_setting( $key, $fallback = null ) {
		$settings = self::get_settings();
		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $fallback;
	}

	/*
	 * ------------------------------------------------------------------
	 * Assets
	 * ------------------------------------------------------------------
	 */

	/**
	 * URL of a file in the plugin, e.g. self::asset_url( 'assets/css/frontend.css' ).
	 *
	 * @param string $path Path relative to the plugin folder.
	 * @return string
	 */
	public static function asset_url( $path ) {
		return CALCWISE_DIR_URL . ltrim( $path, '/' );
	}

	/**
	 * Version string for cache busting: the file's modification time while
	 * WP_DEBUG is on (so edits show up at once), otherwise the plugin version.
	 *
	 * @param string $path Path relative to the plugin folder.
	 * @return string
	 */
	public static function asset_version( $path ) {
		$file = CALCWISE_DIR_PATH . ltrim( $path, '/' );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_readable( $file ) ) {
			return (string) filemtime( $file );
		}
		return CALCWISE_VERSION;
	}
}
