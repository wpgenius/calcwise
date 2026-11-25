<?php
/**
 * Front-end output of the "Items with live filter" block.
 *
 * The HTML is complete without JavaScript (every item is listed); view.js
 * then makes the search box filter it, using the data-wp-* directives below.
 *
 * Available variables: $attributes (block.json attributes), $content, $block.
 *
 * @package WPGenius\CalcWise
 */

use WPGenius\CalcWise\PostTypes\Example;

defined( 'ABSPATH' ) || exit;

$calcwise_posts  = Example::query( isset( $attributes['count'] ) ? (int) $attributes['count'] : 8 )->posts;
$calcwise_titles = array();

// Initial values of the derived state, so the HTML is right before view.js
// runs (and without JavaScript): with an empty search every item is visible.
wp_interactivity_state(
	'calcwise/interactive',
	array(
		'isVisible' => true,
		'isEmpty'   => false,
	)
);

foreach ( $calcwise_posts as $calcwise_post ) {
	$calcwise_titles[ $calcwise_post->ID ] = function_exists( 'mb_strtolower' ) ? mb_strtolower( get_the_title( $calcwise_post ) ) : strtolower( get_the_title( $calcwise_post ) );
}
?>
<div
	<?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes it. ?>
	data-wp-interactive="calcwise/interactive"
	<?php
	echo wp_interactivity_data_wp_context( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes it.
		array(
			'search' => '',
			'titles' => array_values( $calcwise_titles ),
		)
	);
	?>
>
	<?php if ( ! $calcwise_posts ) : ?>
		<p><?php esc_html_e( 'No items yet.', 'calcwise' ); ?></p>
	<?php else : ?>
		<label class="calcwise-filter__label">
			<?php esc_html_e( 'Filter items', 'calcwise' ); ?>
			<input type="search" class="calcwise-filter__input" data-wp-on--input="actions.updateSearch" placeholder="<?php esc_attr_e( 'Type to filter…', 'calcwise' ); ?>">
		</label>

		<ul class="calcwise-filter__list">
			<?php foreach ( $calcwise_posts as $calcwise_post ) : ?>
				<li
					<?php echo wp_interactivity_data_wp_context( array( 'title' => $calcwise_titles[ $calcwise_post->ID ] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes it. ?>
					data-wp-bind--hidden="!state.isVisible"
				>
					<a href="<?php echo esc_url( get_permalink( $calcwise_post ) ); ?>"><?php echo esc_html( get_the_title( $calcwise_post ) ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>

		<p class="calcwise-filter__empty" data-wp-bind--hidden="!state.isEmpty">
			<?php esc_html_e( 'No items match.', 'calcwise' ); ?>
		</p>
	<?php endif; ?>
</div>
