<?php
/**
 * Query adjustments and render-time content filters.
 *
 * @package ZahratAlMaghrib
 */

defined( 'ABSPATH' ) || exit;

/**
 * Optional per-post language override ("fr" / "ar") editable from the Custom Fields panel.
 */
function zam_register_meta() {
	register_post_meta(
		'post',
		'zam_lang',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => static function ( $value ) {
				return in_array( $value, array( 'fr', 'ar' ), true ) ? $value : '';
			},
			'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			},
		)
	);
}
add_action( 'init', 'zam_register_meta' );

/**
 * Keep the posts index page size in step with the "Latest articles" block.
 *
 * @param WP_Query $query Query.
 */
function zam_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_home() ) {
		$query->set( 'posts_per_page', zam_home_per_page() );
		$query->set( 'ignore_sticky_posts', true );
	}
}
add_action( 'pre_get_posts', 'zam_pre_get_posts' );

/**
 * Archive titles read "الرياض", not "وسم: الرياض"; the template adds its own label.
 */
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/**
 * Body classes for language-aware styling.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function zam_body_class( $classes ) {
	if ( is_singular( 'post' ) && 'fr' === zam_post_lang( get_queried_object_id() ) ) {
		$classes[] = 'zam-article-ltr';
	}
	return $classes;
}
add_filter( 'body_class', 'zam_body_class' );

/**
 * Mark a French post's title, excerpt and content as LTR without flipping the site.
 *
 * @param string   $content Block HTML.
 * @param array    $block   Parsed block.
 * @param WP_Block $instance Block instance.
 * @return string
 */
function zam_lang_on_post_blocks( $content, $block, $instance ) {
	if ( '' === trim( $content ) ) {
		return $content;
	}
	$post_id = isset( $instance->context['postId'] ) ? (int) $instance->context['postId'] : get_the_ID();
	if ( ! $post_id || '' === zam_post_lang( $post_id ) ) {
		return $content;
	}
	$tags = new WP_HTML_Tag_Processor( $content );
	if ( $tags->next_tag() ) {
		$tags->set_attribute( 'lang', zam_post_lang( $post_id ) );
		$tags->set_attribute( 'dir', 'ltr' );
		return $tags->get_updated_html();
	}
	return $content;
}
add_filter( 'render_block_core/post-title', 'zam_lang_on_post_blocks', 10, 3 );
add_filter( 'render_block_core/post-excerpt', 'zam_lang_on_post_blocks', 10, 3 );
add_filter( 'render_block_core/post-content', 'zam_lang_on_post_blocks', 10, 3 );

/**
 * Stable fragment id for a heading, derived from its text.
 *
 * @param string $text Heading text or HTML.
 * @return string
 */
function zam_heading_id( $text ) {
	$text = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES, 'UTF-8' ) ) );
	return 'h-' . substr( md5( $text ), 0, 8 );
}

/**
 * Give level 2–3 headings in the article body an id so the table of contents can link to them.
 *
 * @param string $content Rendered post content.
 * @return string
 */
function zam_add_heading_ids( $content ) {
	if ( ! is_singular( 'post' ) || false === stripos( $content, '<h' ) ) {
		return $content;
	}
	return preg_replace_callback(
		'#<h([23])(\s[^>]*)?>(.*?)</h\1>#is',
		static function ( $m ) {
			$attrs = isset( $m[2] ) ? $m[2] : '';
			if ( preg_match( '/\sid=/i', $attrs ) || '' === trim( wp_strip_all_tags( $m[3] ) ) ) {
				return $m[0];
			}
			return '<h' . $m[1] . ' id="' . esc_attr( zam_heading_id( $m[3] ) ) . '"' . $attrs . '>' . $m[3] . '</h' . $m[1] . '>';
		},
		$content
	);
}
add_filter( 'the_content', 'zam_add_heading_ids', 20 );

/**
 * Headings (level 2–3) of a post, read from its saved content.
 *
 * @param WP_Post $post Post.
 * @return array[] Each item: level, text, id.
 */
function zam_post_headings( $post ) {
	$html = preg_replace( '/<!--.*?-->/s', '', (string) $post->post_content );
	$out  = array();
	if ( preg_match_all( '#<h([23])(\s[^>]*)?>(.*?)</h\1>#is', $html, $matches, PREG_SET_ORDER ) ) {
		foreach ( $matches as $m ) {
			$text = trim( html_entity_decode( wp_strip_all_tags( $m[3] ), ENT_QUOTES, 'UTF-8' ) );
			if ( '' === $text ) {
				continue;
			}
			$id = '';
			if ( isset( $m[2] ) && preg_match( '/\sid=["\']([^"\']+)["\']/i', $m[2], $idm ) ) {
				$id = $idm[1];
			}
			$out[] = array(
				'level' => (int) $m[1],
				'text'  => $text,
				'id'    => $id ? $id : zam_heading_id( $m[3] ),
			);
		}
	}
	return $out;
}

/**
 * Plain "…" instead of "[…]" for automatic excerpts.
 *
 * @return string
 */
function zam_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'zam_excerpt_more' );

/**
 * The article standfirst shows only a hand-written excerpt, never an automatic one.
 *
 * @param string   $content  Block HTML.
 * @param array    $block    Parsed block.
 * @param WP_Block $instance Block instance.
 * @return string
 */
function zam_standfirst_only_manual( $content, $block, $instance ) {
	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( false === strpos( $class, 'zam-standfirst' ) ) {
		return $content;
	}
	$post_id = isset( $instance->context['postId'] ) ? (int) $instance->context['postId'] : get_the_ID();
	return ( $post_id && has_excerpt( $post_id ) ) ? $content : '';
}
add_filter( 'render_block_core/post-excerpt', 'zam_standfirst_only_manual', 5, 3 );
