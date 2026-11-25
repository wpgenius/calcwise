<?php
/**
 * One item in the list (also returned by the "Load more" AJAX call).
 *
 * Override in your theme: copy to <theme>/calcwise/item.php.
 *
 * @package WPGenius\CalcWise
 *
 * @var WP_Post $post Item.
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="calcwise-item">
	<?php if ( has_post_thumbnail( $post ) ) : ?>
		<a class="calcwise-item__image" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
			<?php echo get_the_post_thumbnail( $post, 'medium_large' ); ?>
		</a>
	<?php endif; ?>
	<h3 class="calcwise-item__title">
		<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
	</h3>
	<div class="calcwise-item__excerpt"><?php echo wp_kses_post( get_the_excerpt( $post ) ); ?></div>
</article>
