<?php
/**
 * Multisite: new sites, network deactivation and deleted sites.
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
}
