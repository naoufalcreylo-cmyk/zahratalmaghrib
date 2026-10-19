<?php
/**
 * Lead story + supporting stories.
 *
 * Selection order: hand-picked posts, then sticky posts, then the latest posts.
 * IDs are registered even on paginated views so "Latest articles" stays consistent.
 *
 * @package ZahratAlMaghrib
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$zam_supporting = min( 4, max( 2, (int) ( $attributes['supporting'] ?? 4 ) ) );
$zam_total      = 1 + $zam_supporting;
$zam_cat        = zam_category_by_slug( $attributes['category'] ?? '' );
$zam_ids        = array_slice( zam_clean_post_ids( $attributes['postIds'] ?? array() ), 0, $zam_total );

if ( count( $zam_ids ) < $zam_total && ! empty( $attributes['useSticky'] ) ) {
	$zam_sticky = zam_clean_post_ids( get_option( 'sticky_posts', array() ) );
	if ( $zam_sticky ) {
		$zam_sticky_q = new WP_Query(
			array(
				'post__in'            => $zam_sticky,
				'post__not_in'        => $zam_ids,
				'posts_per_page'      => $zam_total - count( $zam_ids ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'fields'              => 'ids',
				'cat'                 => $zam_cat ? $zam_cat->term_id : 0,
			)
		);
		$zam_ids = array_merge( $zam_ids, array_map( 'intval', $zam_sticky_q->posts ) );
	}
}

if ( count( $zam_ids ) < $zam_total ) {
	$zam_fill = new WP_Query(
		array(
			'post_type'           => 'post',
			'post__not_in'        => array_merge( $zam_ids, zam_seen_ids() ),
			'posts_per_page'      => $zam_total - count( $zam_ids ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'fields'              => 'ids',
			'cat'                 => $zam_cat ? $zam_cat->term_id : 0,
		)
	);
	$zam_ids = array_merge( $zam_ids, array_map( 'intval', $zam_fill->posts ) );
}

zam_seen_add( $zam_ids );

if ( is_paged() && ! zam_is_editor_preview() ) {
	return;
}
if ( ! $zam_ids ) {
	echo zam_editor_notice( __( 'No published posts yet.', 'zahrat-almaghrib' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

_prime_post_caches( $zam_ids );
$zam_lead = array_shift( $zam_ids );
?>
<section <?php echo zam_wrapper( array( 'class' => 'zam-lead' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-labelledby="zam-lead-title">
	<?php if ( is_front_page() ) : ?>
		<h1 id="zam-lead-title" class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: publication name. */ __( '%s — top stories', 'zahrat-almaghrib' ), _x( 'زهرة المغرب', 'temporary Arabic wordmark', 'zahrat-almaghrib' ) ) ); ?></h1>
	<?php else : ?>
		<h2 id="zam-lead-title" class="screen-reader-text"><?php esc_html_e( 'Top stories', 'zahrat-almaghrib' ); ?></h2>
	<?php endif; ?>
	<div class="zam-lead__main">
		<?php
		echo zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$zam_lead,
			array(
				'variant'  => 'lead',
				'level'    => 3,
				'excerpt'  => true,
				'words'    => 34,
				'author'   => true,
				'size'     => 'large',
				'sizes'    => '(min-width: 1240px) 720px, (min-width: 1024px) 58vw, 100vw',
				'priority' => true,
			)
		);
		?>
	</div>
	<?php if ( $zam_ids ) : ?>
		<div class="zam-lead__side">
			<?php
			foreach ( $zam_ids as $zam_id ) {
				echo zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$zam_id,
					array(
						'variant' => 'compact',
						'level'   => 3,
						'size'    => 'medium',
						'sizes'   => '(min-width: 1024px) 140px, 112px',
					)
				);
			}
			?>
		</div>
	<?php endif; ?>
</section>
