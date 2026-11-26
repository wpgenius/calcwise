<?php
/**
 * Runs when the plugin is deleted from the Plugins screen.
 *
 * Settings are deleted only when "Delete data on uninstall" is ticked on the
 * settings page; on a network, site by site. CalcWise stores nothing else
 * (no tables, transients, user meta or scheduled events).
 *
 * The plugin is NOT loaded here, so its classes and constants are not available;
 * keep the names below in sync with src/.
 *
 * @package WPGenius\CalcWise
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Remove the plugin's data from the current site.
 *
 * @return void
 */
function calcwise_uninstall_site() {
	$settings = get_option( 'calcwise_settings', array() ); // Api::OPTION.
	if ( empty( $settings['delete_data'] ) ) {
		return;
	}

	delete_option( 'calcwise_settings' );
}

/**
 * Remove the plugin's data from every site that asked for it. On a network,
 * each site's own "Delete data on uninstall" setting decides for that site.
 *
 * @return void
 */
function calcwise_uninstall() {
	if ( ! is_multisite() ) {
		calcwise_uninstall_site();
		return;
	}

	$site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $site_ids as $site_id ) {
		switch_to_blog( (int) $site_id );
		calcwise_uninstall_site();
		restore_current_blog();
	}
}

calcwise_uninstall();
