<?php
/**
 * Pre-submission check, step 2: on a clean site with WP_DEBUG, render the
 * blocks and the shortcode, open the settings page, look at an admin screen
 * that isn't the plugin's, deactivate, uninstall, and count PHP notices.
 * Appends to /sp/runtime-<php>.txt.
 *
 * Calls hooks directly instead of firing init again: a second init floods the
 * log with notices that a real request never shows.
 *
 * Run by bin/presubmit/run.sh inside WordPress Playground; not part of the plugin.
 *
 * @package WPGenius\CalcWise
 */

// phpcs:disable WordPress.WP.AlternativeFunctions, WordPress.PHP.DevelopmentFunctions, WordPress.WP.GlobalVariablesOverride, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- A test script, not plugin code.

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';

$calcwise_lines  = array();
$calcwise_errors = array();
set_error_handler( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Counts notices.
	static function ( $number, $message, $file, $line ) use ( &$calcwise_errors ) {
		$calcwise_errors[] = ( false !== strpos( $file, '/wp-content/plugins/' ) ? 'PLUGIN ' : 'CORE   ' ) . "$number $message @ $file:$line";
		return false;
	}
);
wp_set_current_user( 1 );

$calcwise_lines[] = 'active=' . ( is_plugin_active( PRESUBMIT_PLUGIN ) ? 'yes' : 'no' )
	. ' settings_option=' . ( is_array( get_option( 'calcwise_settings' ) ) ? 'yes' : 'no' );

// Front end: every calculator as a block and as a shortcode.
$calcwise_types = array_keys( WPGenius\CalcWise\Calculator\Registry::all() );
$calcwise_html  = '';
foreach ( $calcwise_types as $calcwise_type ) {
	$calcwise_html .= do_blocks( '<!-- wp:calcwise/calculator {"type":"' . $calcwise_type . '","align":"wide"} /-->' )
		. do_shortcode( '[calcwise type="' . $calcwise_type . '"]' );
}
file_put_contents( '/sp/render-' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.html', $calcwise_html );
$calcwise_lines[] = 'render_bytes=' . strlen( $calcwise_html )
	. ' calculators=' . substr_count( $calcwise_html, 'class="calcwise-calculator ' ) . '/' . ( 2 * count( $calcwise_types ) )
	. ' scripts=' . implode(
		',',
		array_filter(
			wp_scripts()->queue,
			static function ( $handle ) {
				return 0 === strpos( $handle, 'calcwise' ); }
		)
	)
	. ' inline_style_or_script=' . ( preg_match( '/<(style|script)\b/i', $calcwise_html ) ? 'yes' : 'no' );

// Admin: the settings page, then the dashboard (not the plugin's screen).
// Outside a real admin request core's get_admin_page_title() may raise a
// deprecation on PHP 8.1+ (counted as core_notices, not the plugin's).
$GLOBALS['plugin_page'] = 'calcwise_settings'; // Admin\Settings::PAGE.
$GLOBALS['title']       = 'Settings';

$calcwise_settings = new WPGenius\CalcWise\Admin\Settings();
$calcwise_settings->register_settings();
ob_start();
$calcwise_settings->render_page();
$calcwise_lines[] = 'settings_page_bytes=' . strlen( (string) ob_get_clean() );

set_current_screen( 'dashboard' );
$calcwise_queued = array_merge( wp_scripts()->queue, wp_styles()->queue ); // The blocks above queued their styles.
do_action( 'admin_enqueue_scripts', 'index.php' );
$calcwise_assets = array();
foreach ( array( wp_scripts(), wp_styles() ) as $calcwise_deps ) {
	foreach ( array_diff( $calcwise_deps->queue, $calcwise_queued ) as $calcwise_handle ) {
		$calcwise_src = isset( $calcwise_deps->registered[ $calcwise_handle ] ) ? (string) $calcwise_deps->registered[ $calcwise_handle ]->src : '';
		if ( false !== strpos( $calcwise_src, '/plugins/' . dirname( PRESUBMIT_PLUGIN ) . '/' ) ) {
			$calcwise_assets[] = $calcwise_handle;
		}
	}
}
ob_start();
do_action( 'admin_notices' );
$calcwise_lines[] = 'dashboard_plugin_assets=' . ( $calcwise_assets ? implode( ',', $calcwise_assets ) : 'none' )
	. ' dashboard_notice_bytes=' . strlen( (string) ob_get_clean() );

// Deactivate: CalcWise schedules no events, so there is nothing to clear.
$calcwise_options = (array) get_option( 'calcwise_settings', array() );
ob_start();
deactivate_plugins( PRESUBMIT_PLUGIN );
$calcwise_lines[] = 'deactivate_output_bytes=' . strlen( (string) ob_get_clean() );
$calcwise_hooks   = array();
foreach ( (array) _get_cron_array() as $calcwise_events ) {
	$calcwise_hooks = array_merge( $calcwise_hooks, array_keys( (array) $calcwise_events ) );
}
$calcwise_lines[] = 'calcwise_cron_events=' . count( preg_grep( '/^calcwise/', $calcwise_hooks ) );

// Uninstall with "Delete data on uninstall" ticked.
$calcwise_options['delete_data'] = 1;
update_option( 'calcwise_settings', $calcwise_options );
ob_start();
uninstall_plugin( PRESUBMIT_PLUGIN );
$calcwise_uninstall_output = (string) ob_get_clean();
global $wpdb;
$calcwise_lines[] = 'uninstall_output_bytes=' . strlen( $calcwise_uninstall_output )
	. ' option_left=' . ( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s", 'calcwise_settings' ) ) ? 'yes' : 'no' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- get_option() would return the registered default.

$calcwise_plugin_notices = count( preg_grep( '/^PLUGIN /', $calcwise_errors ) );
$calcwise_lines[]        = 'plugin_notices=' . $calcwise_plugin_notices . ' core_notices=' . ( count( $calcwise_errors ) - $calcwise_plugin_notices );
$calcwise_lines          = array_merge( $calcwise_lines, $calcwise_errors );
file_put_contents( '/sp/runtime-' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.txt', implode( "\n", $calcwise_lines ) . "\n", FILE_APPEND );
