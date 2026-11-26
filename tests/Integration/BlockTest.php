<?php
/**
 * The CalcWise Calculator block in a real WordPress.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Integration;

use WPGenius\CalcWise\Assets;
use WPGenius\CalcWise\Blocks\Blocks;

/**
 * Block.
 */
class BlockTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		Assets::register();
	}

	public function test_block_is_registered_with_a_variation_per_calculator() {
		$block = \WP_Block_Type_Registry::get_instance()->get_registered( Blocks::CALCULATOR );

		$this->assertNotNull( $block );
		$names = wp_list_pluck( $block->get_variations(), 'name' );
		foreach ( array( 'sip', 'lumpsum', 'swp', 'goal', 'emi' ) as $type ) {
			$this->assertContains( $type, $names );
		}
	}

	public function test_block_renders_the_calculator_with_its_settings() {
		$html = do_blocks( '<!-- wp:calcwise/calculator {"type":"emi","fields":{"years":{"default":15}}} /-->' );

		$this->assertStringContainsString( 'wp-block-calcwise-calculator', $html );
		$this->assertStringContainsString( 'data-calcwise-type="emi"', $html );
		$this->assertStringContainsString( 'value="15"', $html );
	}

	public function test_block_without_a_type_renders_nothing() {
		$this->assertSame( '', trim( do_blocks( '<!-- wp:calcwise/calculator /-->' ) ) );
	}

	public function test_editor_gets_each_calculator_with_its_fields() {
		$calculators = Blocks::editor_calculators();

		$this->assertArrayHasKey( 'principal', $calculators['emi']['fields'] );
		$this->assertArrayHasKey( 'label', $calculators['emi']['fields']['principal'] );
	}
}
