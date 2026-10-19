<?php
/**
 * Breadcrumb trail. Structured data is left to the SEO plugin.
 *
 * @package ZahratAlMaghrib
 */

defined( 'ABSPATH' ) || exit;

if ( is_front_page() && ! is_paged() ) {
	return;
}

$zam_items = array(
	array(
		'url'   => home_url( '/' ),
		'label' => __( 'Home', 'zahrat-almaghrib' ),
	),
);

$zam_add_term = static function ( $term, $items ) {
	foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor_id ) {
		$ancestor = get_term( $ancestor_id, $term->taxonomy );
		if ( $ancestor && ! is_wp_error( $ancestor ) ) {
			$items[] = array(
				'url'   => get_term_link( $ancestor ),
				'label' => $ancestor->name,
				'lang'  => zam_term_lang( $ancestor ),
			);
		}
	}
	$items[] = array(
		'url'   => get_term_link( $term ),
		'label' => $term->name,
		'lang'  => zam_term_lang( $term ),
	);
	return $items;
};

if ( is_singular( 'post' ) ) {
	$zam_cat = zam_primary_category( get_queried_object_id() );
	if ( $zam_cat ) {
		$zam_items = $zam_add_term( $zam_cat, $zam_items );
	}
	$zam_items[] = array(
		'label' => get_the_title( get_queried_object_id() ),
		'lang'  => zam_post_lang( get_queried_object_id() ),
	);
} elseif ( is_page() ) {
	foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $zam_parent ) {
		$zam_items[] = array(
			'url'   => get_permalink( $zam_parent ),
			'label' => get_the_title( $zam_parent ),
		);
	}
	$zam_items[] = array( 'label' => get_the_title( get_queried_object_id() ) );
} elseif ( is_category() ) {
	$zam_items = $zam_add_term( get_queried_object(), $zam_items );
} elseif ( is_tag() ) {
	$zam_items[] = array(
		'url'   => get_term_link( get_queried_object() ),
		'label' => single_tag_title( '', false ),
	);
} elseif ( is_author() ) {
	$zam_items[] = array(
		'url'   => get_author_posts_url( (int) get_query_var( 'author' ) ),
		'label' => get_the_author_meta( 'display_name', (int) get_query_var( 'author' ) ),
	);
} elseif ( is_search() ) {
	$zam_items[] = array( 'label' => __( 'Search results', 'zahrat-almaghrib' ) );
} elseif ( is_date() ) {
	$zam_items[] = array( 'label' => get_the_archive_title() );
} elseif ( is_404() ) {
	$zam_items[] = array( 'label' => __( 'Page not found', 'zahrat-almaghrib' ) );
} elseif ( is_home() ) {
	$zam_items[] = array(
		'url'   => get_pagenum_link( 1 ),
		'label' => __( 'Latest news', 'zahrat-almaghrib' ),
	);
}

if ( is_paged() && ! is_search() ) {
	$zam_items[] = array(
		/* translators: %s: page number. */
		'label' => sprintf( __( 'Page %s', 'zahrat-almaghrib' ), number_format_i18n( zam_current_page() ) ),
	);
}

$zam_last_i = count( $zam_items ) - 1;
?>
<nav <?php echo zam_wrapper( array( 'class' => 'zam-breadcrumbs' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php esc_attr_e( 'Breadcrumb', 'zahrat-almaghrib' ); ?>">
	<ol>
		<?php foreach ( $zam_items as $zam_i => $zam_item ) : ?>
			<?php $zam_lang_attr = ( isset( $zam_item['lang'] ) && 'fr' === $zam_item['lang'] ) ? ' lang="fr" dir="ltr"' : ''; ?>
			<li>
				<?php if ( $zam_i < $zam_last_i && ! empty( $zam_item['url'] ) && ! is_wp_error( $zam_item['url'] ) ) : ?>
					<a href="<?php echo esc_url( $zam_item['url'] ); ?>"<?php echo $zam_lang_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $zam_item['label'] ); ?></a>
				<?php else : ?>
					<span aria-current="page"<?php echo $zam_lang_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( wp_strip_all_tags( $zam_item['label'] ) ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
