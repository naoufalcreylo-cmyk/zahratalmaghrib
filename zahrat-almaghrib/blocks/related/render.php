<?php
/**
 * Related articles: shared tags first, then shared categories. Never the current post.
 *
 * @package ZahratAlMaghrib
 * @var array    $attributes
 * @var WP_Block $block
 */

defined( 'ABSPATH' ) || exit;

$zam_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
if ( ! $zam_post_id || 'post' !== get_post_type( $zam_post_id ) ) {
	return;
}

$zam_count   = min( 6, max( 2, (int) ( $attributes['count'] ?? 3 ) ) );
$zam_exclude = array_merge( array( $zam_post_id ), zam_seen_ids() );
$zam_ids     = array();

$zam_tags = wp_get_post_tags( $zam_post_id, array( 'fields' => 'ids' ) );
if ( $zam_tags ) {
	$zam_q   = new WP_Query(
		array(
			'tag__in'             => $zam_tags,
			'post__not_in'        => $zam_exclude,
			'posts_per_page'      => $zam_count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'fields'              => 'ids',
		)
	);
	$zam_ids = array_map( 'intval', $zam_q->posts );
}

if ( count( $zam_ids ) < $zam_count ) {
	$zam_cats = wp_get_post_categories( $zam_post_id );
	if ( $zam_cats ) {
		$zam_q   = new WP_Query(
			array(
				'category__in'        => $zam_cats,
				'post__not_in'        => array_merge( $zam_exclude, $zam_ids ),
				'posts_per_page'      => $zam_count - count( $zam_ids ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'fields'              => 'ids',
			)
		);
		$zam_ids = array_merge( $zam_ids, array_map( 'intval', $zam_q->posts ) );
	}
}

if ( ! $zam_ids ) {
	return;
}
_prime_post_caches( $zam_ids );
$zam_head_id = 'zam-related-' . wp_unique_id();
?>
<section <?php echo zam_wrapper( array( 'class' => 'zam-related' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-labelledby="<?php echo esc_attr( $zam_head_id ); ?>">
	<?php echo zam_section_heading( __( 'Related articles', 'zahrat-almaghrib' ), '', 2, $zam_head_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<div class="zam-grid zam-grid--<?php echo (int) zam_grid_cols( count( $zam_ids ) ); ?>">
		<?php
		foreach ( $zam_ids as $zam_id ) {
			echo zam_card( $zam_id, array( 'variant' => 'grid' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</div>
</section>
