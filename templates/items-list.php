<?php
/**
 * List of items with an optional "Load more" button.
 *
 * Override in your theme: copy to <theme>/calcwise/items-list.php.
 *
 * @package WPGenius\CalcWise
 *
 * @var WP_Query $query     Items to show.
 * @var int      $per_page  Items per page.
 * @var string   $layout    'grid' or 'list'.
 * @var bool     $load_more Whether to show the "Load more" button.
 */

use WPGenius\CalcWise\Api;

defined( 'ABSPATH' ) || exit;

if ( ! $query->have_posts() ) {
	echo '<p class="calcwise-empty">' . esc_html__( 'No items yet.', 'calcwise' ) . '</p>';
	return;
}
?>
<div class="calcwise-items">
	<div class="calcwise-items__list calcwise-items__list--<?php echo esc_attr( $layout ); ?>">
		<?php
		while ( $query->have_posts() ) {
			$query->the_post();
			echo Api::render_template( 'item.php', array( 'post' => get_post() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the template.
		}
		wp_reset_postdata();
		?>
	</div>

	<?php if ( $load_more && $query->max_num_pages > 1 ) : ?>
		<button type="button" class="calcwise-items__more" data-page="1" data-per-page="<?php echo esc_attr( (string) $per_page ); ?>">
			<?php esc_html_e( 'Load more', 'calcwise' ); ?>
		</button>
	<?php endif; ?>
</div>
