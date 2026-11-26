<?php
/**
 * What the pre-submission runtime check exercises in CalcWise. The only file
 * in bin/presubmit/ to edit for this plugin; runtime.php does the rest.
 *
 * Every key is optional. Callbacks run on a clean site with WP_DEBUG, as the
 * administrator, after activation. Callbacks that return text add it to the
 * results line as "key=value" pairs.
 *
 * Run by bin/presubmit/run.sh inside WordPress Playground; not part of the plugin.
 */

$calcwise_types = array_keys( WPGenius\CalcWise\Calculator\Registry::all() );

return array(
	// Option, transient, cron hook and table names start with "calcwise".
	'prefix'           => 'calcwise',

	// Every calculator, as a block and as a shortcode.
	'render'           => array_merge(
		array_map(
			static function ( $type ) {
				return '<!-- wp:calcwise/calculator {"type":"' . $type . '","align":"wide"} /-->';
			},
			$calcwise_types
		),
		array_map(
			static function ( $type ) {
				return '[calcwise type="' . $type . '"]';
			},
			$calcwise_types
		)
	),

	// Each one rendered, and only the shown calculators' scripts queued.
	'check_render'     => static function ( $html ) use ( $calcwise_types ) {
		$scripts = array_filter(
			wp_scripts()->queue,
			static function ( $handle ) {
				return 0 === strpos( $handle, 'calcwise' );
			}
		);
		return 'calculators=' . substr_count( $html, 'class="calcwise-calculator ' ) . '/' . ( 2 * count( $calcwise_types ) )
			. ' scripts=' . implode( ',', $scripts );
	},

	// Settings → CalcWise.
	'settings_page'    => static function () {
		$GLOBALS['plugin_page'] = WPGenius\CalcWise\Admin\Settings::PAGE;
		$settings               = new WPGenius\CalcWise\Admin\Settings();
		$settings->register_settings();
		$settings->render_page();
	},

	// Before uninstall: tick "Delete data on uninstall".
	'before_uninstall' => static function () {
		$options                = (array) get_option( 'calcwise_settings', array() );
		$options['delete_data'] = 1;
		update_option( 'calcwise_settings', $options );
	},
);
