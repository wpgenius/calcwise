<?php
/**
 * Runs when the plugin is deleted from the Plugins screen.
 *
 * Settings and items (and the example log table) are deleted only when "Delete
 * data on uninstall" is ticked on the settings page. Scheduled jobs are always
 * removed.
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

	// Item categories (PostTypes\Example::TAXONOMY), with their links to items.
	$term_ids = get_terms(
		array(
			'taxonomy'   => 'calcwise_item_category',
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);
	if ( is_array( $term_ids ) ) {
		foreach ( $term_ids as $term_id ) {
			wp_delete_term( (int) $term_id, 'calcwise_item_category' );
		}
	}

	// Items (PostTypes\Example::NAME) with their meta and revisions.
	// 'any' alone skips the trash and auto-drafts, so they are named too.
	$item_ids = get_posts(
		array(
			'post_type'   => 'calcwise_item',
			'post_status' => array( 'any', 'trash', 'auto-draft' ),
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);
	foreach ( $item_ids as $item_id ) {
		wp_delete_post( $item_id, true );
	}

	delete_option( 'calcwise_settings' );
}

// The plugin is not loaded, so its taxonomy is unknown to WordPress here;
// register it so the terms can be found and deleted.
register_taxonomy( 'calcwise_item_category', 'calcwise_item' );

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
