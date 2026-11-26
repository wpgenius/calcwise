<?php
/**
 * Activation, deactivation and multisite setup.
 *
 * - activate():   saves default settings.
 * - deactivate(): nothing to clean up yet (data is kept).
 * - Multisite:    a network activation or deactivation runs on every site, and
 *                 a site added later is set up when the plugin is network active.
 *
 * Deleting data happens in uninstall.php, not here.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise;

defined( 'ABSPATH' ) || exit;


/**
 * Installer.
 */
class Installer extends Api {

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'wp_initialize_site', array( __CLASS__, 'new_site' ), 900 ); // After core created the site's tables.
	}

	/**
	 * Activation hook. On a network activation, runs for every site.
	 *
	 * @param bool $network_wide Whether the plugin is network activated.
	 * @return void
	 */
	public static function activate( $network_wide = false ) {
		if ( is_multisite() && $network_wide ) {
			$site_ids = get_sites(
				array(
					'fields' => 'ids',
					'number' => 0,
				)
			);
			foreach ( $site_ids as $site_id ) {
				switch_to_blog( $site_id );
				self::activate_site();
				restore_current_blog();
			}
			return;
		}
		self::activate_site();
	}

	/**
	 * Deactivation hook. On a network deactivation, runs for every site.
	 *
	 * @param bool $network_wide Whether the plugin is network deactivated.
	 * @return void
	 */
	public static function deactivate( $network_wide = false ) {
		if ( is_multisite() && $network_wide ) {
			$site_ids = get_sites(
				array(
					'fields' => 'ids',
					'number' => 0,
				)
			);
			foreach ( $site_ids as $site_id ) {
				switch_to_blog( $site_id );
				self::deactivate_site();
				restore_current_blog();
			}
			return;
		}
		self::deactivate_site();
	}

	/**
	 * Multisite: set up a site created while the plugin is network active.
	 *
	 * @param \WP_Site $site The new site.
	 * @return void
	 */
	public static function new_site( $site ) {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! is_plugin_active_for_network( plugin_basename( CALCWISE_FILE ) ) ) {
			return;
		}
		switch_to_blog( (int) $site->blog_id );
		self::activate_site();
		restore_current_blog();
	}

	/**
	 * Clean up one site (its data is kept).
	 *
	 * @return void
	 */
	private static function deactivate_site() {
		// Nothing to clean up yet: CalcWise adds no rewrite rules or scheduled jobs.
	}

	/**
	 * Set up one site.
	 *
	 * @return void
	 */
	private static function activate_site() {
		add_option( self::OPTION, Admin\Settings::defaults() ); // Does nothing if settings exist.
	}
}
