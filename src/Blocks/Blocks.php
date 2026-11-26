<?php
/**
 * Registers every block built into build/ and wires up the CalcWise
 * Calculator block:
 *
 * - one variation per calculator, made from the calculator definitions, so
 *   each calculator can be found by name in the inserter;
 * - the calculator definitions for the block's sidebar (window.calcwiseBlock);
 * - the calculator scripts and styles inside the editor, so the preview is
 *   the working calculator.
 *
 * Block sources live in blocks/<name>/ (block.json, index.js, edit.js,
 * render.php). `npm run build` compiles them into build/<name>/.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Blocks;

use WPGenius\CalcWise\Api;
use WPGenius\CalcWise\Assets;
use WPGenius\CalcWise\Calculator\Registry;

defined( 'ABSPATH' ) || exit;

/**
 * Block loader.
 */
class Blocks extends Api {

	/**
	 * Name of the calculator block.
	 */
	const CALCULATOR = 'calcwise/calculator';

	/**
	 * Register blocks on init.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'get_block_type_variations', array( __CLASS__, 'variations' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'editor_data' ) );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'editor_preview_assets' ) );
	}

	/**
	 * Register each build/<name>/block.json.
	 *
	 * @return void
	 */
	public function register_blocks() {
		$block_files = glob( CALCWISE_DIR_PATH . 'build/*/block.json' );
		foreach ( $block_files ? $block_files : array() as $block_json ) {
			register_block_type( dirname( $block_json ) );
		}
	}

	/**
	 * One block variation per calculator.
	 *
	 * @param array<int, array<string, mixed>> $variations Existing variations.
	 * @param \WP_Block_Type                   $block_type Block type.
	 * @return array<int, array<string, mixed>>
	 */
	public static function variations( $variations, $block_type ) {
		if ( self::CALCULATOR !== $block_type->name ) {
			return $variations;
		}
		foreach ( Registry::all() as $type => $definition ) {
			$variations[] = array(
				'name'        => $type,
				'title'       => $definition['title'],
				'description' => $definition['description'],
				'icon'        => 'calculator',
				'attributes'  => array( 'type' => $type ),
				'isActive'    => array( 'type' ),
				'scope'       => array( 'inserter', 'transform' ),
				'keywords'    => array( $type ),
			);
		}
		return $variations;
	}

	/**
	 * Calculator definitions for the block's sidebar.
	 *
	 * @return void
	 */
	public static function editor_data() {
		$handle = generate_block_asset_handle( self::CALCULATOR, 'editorScript' );
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			return;
		}
		wp_add_inline_script( $handle, 'window.calcwiseBlock = ' . wp_json_encode( array( 'calculators' => self::editor_calculators() ) ) . ';', 'before' );
	}

	/**
	 * What the editor needs to know about each calculator.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function editor_calculators() {
		$calculators = array();
		foreach ( Registry::all() as $type => $definition ) {
			$calculators[ $type ] = array(
				'title'       => $definition['title'],
				'description' => $definition['description'],
				'fields'      => array_map(
					static function ( $field ) {
						return array_intersect_key( $field, array_flip( array( 'label', 'unit', 'min', 'max', 'step', 'default' ) ) );
					},
					$definition['fields']
				),
			);
		}
		return $calculators;
	}

	/**
	 * Load every calculator inside the editor (the canvas iframe gets what is
	 * enqueued on enqueue_block_assets), so the block preview works.
	 *
	 * @return void
	 */
	public static function editor_preview_assets() {
		if ( ! is_admin() ) {
			return;
		}
		foreach ( array_keys( Registry::all() ) as $type ) {
			Assets::enqueue( $type );
		}
	}
}
