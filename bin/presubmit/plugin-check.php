<?php
/**
 * Pre-submission check: Plugin Check with every static check, experimental
 * ones included, on the packaged plugin. Writes /sp/plugin-check.txt.
 *
 * Run by bin/presubmit/run.sh inside WordPress Playground; not part of the plugin.
 *
 * @package WPGenius\CalcWise
 */

// phpcs:disable WordPress.WP.AlternativeFunctions -- Writes its results to the mounted folder.

require_once '/wordpress/wp-load.php';

$calcwise_lines = array();
try {
	$calcwise_repository = 'WordPress\Plugin_Check\Checker\Check_Repository';
	$calcwise_checks     = ( new WordPress\Plugin_Check\Checker\Default_Check_Repository() )->get_checks(
		constant( $calcwise_repository . '::TYPE_STATIC' ) | constant( $calcwise_repository . '::INCLUDE_EXPERIMENTAL' )
	);
	$calcwise_context    = new WordPress\Plugin_Check\Checker\Check_Context( WP_PLUGIN_DIR . '/' . PRESUBMIT_PLUGIN );
	$calcwise_result     = new WordPress\Plugin_Check\Checker\Check_Result( $calcwise_context );

	$calcwise_lines[] = 'checks: ' . implode( ', ', array_keys( iterator_to_array( $calcwise_checks ) ) );
	foreach ( $calcwise_checks as $calcwise_slug => $calcwise_check ) {
		try {
			$calcwise_check->run( $calcwise_result );
		} catch ( Throwable $calcwise_e ) {
			$calcwise_lines[] = "EXCEPTION $calcwise_slug: " . $calcwise_e->getMessage();
		}
	}

	$calcwise_found = array(
		'ERROR'   => $calcwise_result->get_errors(),
		'WARNING' => $calcwise_result->get_warnings(),
	);
	foreach ( $calcwise_found as $calcwise_type => $calcwise_files ) {
		foreach ( $calcwise_files as $calcwise_file => $calcwise_rows ) {
			foreach ( $calcwise_rows as $calcwise_line => $calcwise_columns ) {
				foreach ( $calcwise_columns as $calcwise_messages ) {
					foreach ( $calcwise_messages as $calcwise_message ) {
						$calcwise_lines[] = "$calcwise_type $calcwise_file:$calcwise_line {$calcwise_message['code']} " . wp_strip_all_tags( $calcwise_message['message'] );
					}
				}
			}
		}
	}
	$calcwise_lines[] = 'errors=' . $calcwise_result->get_error_count() . ' warnings=' . $calcwise_result->get_warning_count();
} catch ( Throwable $calcwise_e ) {
	$calcwise_lines[] = 'FATAL ' . get_class( $calcwise_e ) . ': ' . $calcwise_e->getMessage() . ' @ ' . $calcwise_e->getFile() . ':' . $calcwise_e->getLine();
}

file_put_contents( '/sp/plugin-check.txt', implode( "\n", $calcwise_lines ) . "\n" );
