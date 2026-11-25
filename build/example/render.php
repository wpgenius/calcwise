<?php
/**
 * Front-end output of the "Latest items" block.
 *
 * Available variables: $attributes (block.json attributes), $content, $block.
 *
 * @package WPGenius\CalcWise
 */

use WPGenius\CalcWise\Api;
use WPGenius\CalcWise\PostTypes\Example;

defined( 'ABSPATH' ) || exit;

$calcwise_count = Example::per_page( isset( $attributes['count'] ) ? $attributes['count'] : 3 );
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes it. ?>>
	<?php
	// Same template as the [calcwise_items] shortcode, without "Load more".
	$calcwise_html = Api::render_template(
		'items-list.php',
		array(
			'query'     => Example::query( $calcwise_count ),
			'per_page'  => $calcwise_count,
			'layout'    => isset( $attributes['layout'] ) && 'list' === $attributes['layout'] ? 'list' : 'grid',
			'load_more' => false,
		)
	);
	echo $calcwise_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the template.
	?>
</div>
