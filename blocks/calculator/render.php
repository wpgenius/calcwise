<?php
/**
 * Front end of the CalcWise Calculator block: the shared renderer inside the
 * block wrapper (alignment, margin, extra classes).
 *
 * @package WPGenius\CalcWise
 *
 * @var array<string, mixed> $attributes Block attributes: type, fields.
 */

defined( 'ABSPATH' ) || exit;

$calcwise_type = isset( $attributes['type'] ) ? sanitize_key( (string) $attributes['type'] ) : '';
$calcwise_html = '' === $calcwise_type ? '' : WPGenius\CalcWise\Calculator\Renderer::render(
	$calcwise_type,
	array( 'fields' => isset( $attributes['fields'] ) && is_array( $attributes['fields'] ) ? $attributes['fields'] : array() )
);

if ( '' !== $calcwise_html ) {
	echo wp_kses(
		'<div ' . get_block_wrapper_attributes() . '>' . $calcwise_html . '</div>',
		WPGenius\CalcWise\Calculator\Renderer::allowed_html()
	);
}
