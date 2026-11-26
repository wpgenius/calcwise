<?php
/**
 * Multisite: new sites and uninstall across the network.
 *
 * Skipped on a single site. Run with: npm run test:integration:multisite
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Integration;

use WPGenius\CalcWise\Api;
use WPGenius\CalcWise\Installer;

/**
 * Multisite behaviour.
 *
 * @group multisite
 */
class MultisiteTest extends \WP_UnitTestCase {

	use RunsUninstall;

	/**
	 * Skip the whole class on a single site.
	 */
	public function set_up() {
		parent::set_up();
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite only.' );
		}
	}

	/**
	 * Mark the plugin as network active, or not.
	 *
	 * @param bool $active Whether it is network active.
	 */
	private function network_active( $active ) {
		update_site_option( 'active_sitewide_plugins', $active ? array( plugin_basename( CALCWISE_FILE ) => time() ) : array() );
	}

	public function test_a_new_site_is_set_up_when_the_plugin_is_network_active() {
		$this->network_active( true );
		$site = self::factory()->blog->create();

		switch_to_blog( $site );
		$settings = get_option( Api::OPTION );
		restore_current_blog();

		$this->assertIsArray( $settings );
	}

	public function test_a_new_site_is_left_alone_when_the_plugin_is_not_network_active() {
		$this->network_active( false );
		$site = self::factory()->blog->create();

		switch_to_blog( $site );
		$settings = get_option( Api::OPTION );
		restore_current_blog();

		$this->assertFalse( $settings );
	}

	public function test_uninstall_deletes_settings_only_on_sites_that_asked() {
		$keep   = self::factory()->blog->create();
		$delete = self::factory()->blog->create();
		foreach ( array(
			$keep   => 0,
			$delete => 1,
		) as $site => $delete_data ) {
			switch_to_blog( $site );
			update_option( Api::OPTION, array_merge( Api::get_settings(), array( 'delete_data' => $delete_data ) ) );
			restore_current_blog();
		}

		$this->uninstall();

		$this->assertIsArray( get_blog_option( $keep, Api::OPTION ) );
		$this->assertFalse( get_blog_option( $delete, Api::OPTION ) );
		$this->assertSame( get_current_blog_id(), get_main_site_id(), 'Back on the main site afterwards.' );
	}
}
