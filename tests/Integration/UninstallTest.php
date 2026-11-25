<?php
/**
 * What uninstall.php keeps and deletes.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Integration;

use WPGenius\CalcWise\Api;
use WPGenius\CalcWise\Installer;
use WPGenius\CalcWise\PostTypes\Example;

/**
 * Uninstall.
 */
class UninstallTest extends \WP_UnitTestCase {

	/**
	 * Every status an item can be left in, including the two 'any' skips.
	 */
	const STATUSES = array( 'publish', 'draft', 'trash', 'auto-draft' );

	/**
	 * Activate, and create one item per status, one of them in a category.
	 */
	public function set_up() {
		parent::set_up();
		Installer::activate();

		foreach ( self::STATUSES as $status ) {
			$item = self::factory()->post->create(
				array(
					'post_type'   => Example::NAME,
					'post_status' => $status,
				)
			);
		}
		wp_set_object_terms( $item, 'News', Example::TAXONOMY );
	}

	/**
	 * Run uninstall.php for the current site. The file can be loaded only
	 * once (it declares a function), so later tests call that function.
	 */
	private function uninstall() {
		if ( function_exists( 'calcwise_uninstall_site' ) ) {
			calcwise_uninstall_site();
			return;
		}
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'calcwise/calcwise.php' );
		}
		require dirname( __DIR__, 2 ) . '/uninstall.php';
	}

	/**
	 * IDs of the items left, in any status.
	 *
	 * @return int[]
	 */
	private function items() {
		return get_posts(
			array(
				'post_type'   => Example::NAME,
				'post_status' => self::STATUSES,
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);
	}

	public function test_data_is_kept_unless_the_user_asked_to_delete_it() {
		$this->uninstall();

		$this->assertCount( 4, $this->items() );
		$this->assertIsArray( get_option( Api::OPTION ) );
		$this->assertCount( 1, get_terms( array( 'taxonomy' => Example::TAXONOMY, 'hide_empty' => false ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing -- Short.
	}

	public function test_everything_is_deleted_when_the_user_asked() {
		update_option( Api::OPTION, array_merge( Api::get_settings(), array( 'delete_data' => 1 ) ) );

		$this->uninstall();

		$this->assertSame( array(), $this->items(), 'Items in every status, including trash and auto-drafts.' );
		$this->assertSame( array(), get_terms( array( 'taxonomy' => Example::TAXONOMY, 'hide_empty' => false ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing -- Short.
		$this->assertFalse( get_option( Api::OPTION ) );
	}
}
