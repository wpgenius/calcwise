<?php
/**
 * Runs when the plugin is deleted from the Plugins screen.
 *
 * Settings are deleted only when "Delete data on uninstall" is ticked on the
 * settings page.
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

if ( is_multisite() ) {
	$calcwise_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $calcwise_site_ids as $calcwise_site_id ) {
		switch_to_blog( $calcwise_site_id );
		calcwise_uninstall_site();
		restore_current_blog();
	}
} else {
	calcwise_uninstall_site();
}
