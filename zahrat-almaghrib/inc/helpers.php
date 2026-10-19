<?php
/**
 * Shared helpers: shown-post registry, language detection, terms, images, icons.
 *
 * @package ZahratAlMaghrib
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remember post IDs already rendered on this request so later sections skip them.
 *
 * @param int[] $ids Post IDs.
 */
function zam_seen_add( $ids ) {
	$GLOBALS['zam_seen_posts'] = array_values( array_unique( array_merge( zam_seen_ids(), array_map( 'absint', (array) $ids ) ) ) );
}

/**
 * Post IDs already rendered on this request.
 *
 * @return int[]
 */
function zam_seen_ids() {
	return isset( $GLOBALS['zam_seen_posts'] ) ? (array) $GLOBALS['zam_seen_posts'] : array();
}

/**
 * Number of posts per page on the posts index. Used by the main query and the
 * "Latest articles" block so both agree on which /page/N/ URLs exist.
 *
 * @return int
 */
function zam_home_per_page() {
	return max( 3, absint( apply_filters( 'zam_home_per_page', 9 ) ) );
}

/**
 * IDs of the "En français" category and its descendants.
 *
 * @return int[]
 */
function zam_french_term_ids() {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}
	$ids   = array();
	$slugs = (array) apply_filters( 'zam_french_category_slugs', array( 'en-francais' ) );
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', sanitize_title( $slug ), 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			$ids[] = (int) $term->term_id;
			$ids   = array_merge( $ids, array_map( 'intval', get_term_children( $term->term_id, 'category' ) ) );
		}
	}
	return $ids;
}

/**
 * Content language of a post when it differs from the site language.
 *
 * Order: the `zam_lang` custom field ("fr" / "ar"), then membership of the
 * French category tree, then a title written only in Latin script.
 *
 * @param int|WP_Post|null $post Post.
 * @return string Language code ("fr") or empty string for the site language.
 */
function zam_post_lang( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	static $cache = array();
	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ];
	}

	$lang     = '';
	$override = get_post_meta( $post->ID, 'zam_lang', true );
	if ( in_array( $override, array( 'fr', 'ar' ), true ) ) {
		$lang = 'fr' === $override ? 'fr' : '';
	} elseif ( 'post' === $post->post_type ) {
		$french = zam_french_term_ids();
		if ( $french && has_category( $french, $post ) ) {
			$lang = 'fr';
		} else {
			$title = wp_strip_all_tags( $post->post_title );
			if ( '' !== $title && ! preg_match( '/\p{Arabic}/u', $title ) && preg_match_all( '/\p{Latin}/u', $title ) >= 8 ) {
				$lang = 'fr';
			}
		}
	}

	$cache[ $post->ID ] = (string) apply_filters( 'zam_post_language', $lang, $post );
	return $cache[ $post->ID ];
}

/**
 * HTML attributes for a post written in a left-to-right language.
 *
 * @param int|WP_Post|null $post Post.
 * @return string Leading-space attribute string, already escaped.
 */
function zam_lang_attrs( $post = null ) {
	$lang = zam_post_lang( $post );
	if ( '' === $lang ) {
		return '';
	}
	return sprintf( ' lang="%s" dir="ltr"', esc_attr( $lang ) );
}

/**
 * The category shown as a post's label.
 *
 * Honours a primary category saved by Yoast SEO or AIOSEO when present.
 *
 * @param int|WP_Post|null $post Post.
 * @return WP_Term|null
 */
function zam_primary_category( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}

	$primary = (int) get_post_meta( $post->ID, '_yoast_wpseo_primary_category', true );
	if ( ! $primary && function_exists( 'aioseo' ) ) {
		$aioseo_post = class_exists( '\AIOSEO\Plugin\Common\Models\Post' ) ? \AIOSEO\Plugin\Common\Models\Post::getPost( $post->ID ) : null;
		if ( $aioseo_post && ! empty( $aioseo_post->primary_term ) ) {
			$terms   = is_string( $aioseo_post->primary_term ) ? json_decode( $aioseo_post->primary_term, true ) : (array) $aioseo_post->primary_term;
			$primary = isset( $terms['category'] ) ? (int) $terms['category'] : 0;
		}
	}
	if ( $primary ) {
		$term = get_term( $primary, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		}
	}

	$cats = get_the_category( $post->ID );
	return $cats ? $cats[0] : null;
}

/**
 * Find a category from a slug typed in Arabic or percent-encoded.
 *
 * @param string $slug Slug.
 * @return WP_Term|null
 */
function zam_category_by_slug( $slug ) {
	$slug = trim( (string) $slug );
	if ( '' === $slug ) {
		return null;
	}
	$term = get_term_by( 'slug', sanitize_title( rawurldecode( $slug ) ), 'category' );
	return ( $term && ! is_wp_error( $term ) ) ? $term : null;
}

/**
 * Featured image markup with explicit dimensions, or a quiet placeholder.
 *
 * @param WP_Post $post     Post.
 * @param string  $size     Registered image size.
 * @param string  $sizes    `sizes` attribute.
 * @param bool    $priority Whether this is the main above-the-fold image.
 * @return string
 */
function zam_post_image( $post, $size = 'medium_large', $sizes = '(min-width: 1024px) 33vw, 100vw', $priority = false ) {
	$thumb_id = get_post_thumbnail_id( $post );
	if ( $thumb_id && wp_attachment_is_image( $thumb_id ) ) {
		$attr = array(
			'sizes'    => $sizes,
			'alt'      => '',
			'decoding' => 'async',
			'class'    => 'zam-img',
		);
		if ( $priority ) {
			$attr['loading']       = 'eager';
			$attr['fetchpriority'] = 'high';
		} else {
			$attr['loading'] = 'lazy';
		}
		$html = wp_get_attachment_image( $thumb_id, $size, false, $attr );
		if ( $html ) {
			return $html;
		}
	}
	return zam_placeholder();
}

/**
 * Placeholder used when a post has no usable featured image.
 *
 * @return string
 */
function zam_placeholder() {
	return '<span class="zam-ph" aria-hidden="true">' . zam_icon( 'star' ) . '</span>';
}

/**
 * Inline SVG icons. All decorative: callers supply accessible text.
 *
 * @param string $name Icon name.
 * @return string
 */
function zam_icon( $name ) {
	$paths = array(
		'star'     => '<path d="M12 1.5l3 4.4 5.2-.9-.9 5.2 4.4 3-4.4 3 .9 5.2-5.2-.9-3 4.4-3-4.4-5.2.9.9-5.2L.3 13.2l4.4-3-.9-5.2 5.2.9z" fill="none" stroke="currentColor" stroke-width="1.1"/>',
		'whatsapp' => '<path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2c-1.5 0-3-.4-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.7.3-.2.3-.9.9-.9 2.2s.9 2.5 1 2.7c.1.2 1.8 2.8 4.4 3.9 1.6.7 2.3.8 3.1.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.5-.3-.6-.7-.7z"/>',
		'facebook' => '<path fill="currentColor" d="M13.5 22v-8h2.7l.4-3.2h-3.1v-2c0-.9.3-1.6 1.6-1.6h1.7V4.3c-.3 0-1.3-.1-2.5-.1-2.5 0-4.1 1.5-4.1 4.2v2.4H7.4V14h2.8v8z"/>',
		'link'     => '<path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1 1M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1-1"/>',
		'chevron'  => '<path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="zam-icon zam-icon--' . esc_attr( $name ) . '" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Validate a list of post IDs from block attributes: published posts only.
 *
 * @param mixed $ids Raw attribute.
 * @return int[]
 */
function zam_clean_post_ids( $ids ) {
	$ids = array_filter( array_map( 'absint', (array) $ids ) );
	$out = array();
	foreach ( $ids as $id ) {
		if ( 'publish' === get_post_status( $id ) && 'post' === get_post_type( $id ) ) {
			$out[] = $id;
		}
	}
	return array_values( array_unique( $out ) );
}
