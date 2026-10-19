<?php
/**
 * Story card markup shared by every listing.
 *
 * @package ZahratAlMaghrib
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render one story.
 *
 * @param WP_Post $post Post.
 * @param array   $args {
 *     @type string $variant      lead | grid | row | list | compact | portrait | text.
 *     @type int    $level        Heading level for the headline (2–4).
 *     @type bool   $image        Show the image column.
 *     @type bool   $excerpt      Show the excerpt.
 *     @type int    $words        Excerpt length in words.
 *     @type bool   $author       Show the author's name.
 *     @type bool   $date         Show the date.
 *     @type bool   $kicker       Show the category label.
 *     @type string $size         Image size.
 *     @type string $sizes        `sizes` attribute.
 *     @type bool   $priority     Main above-the-fold image.
 * }
 * @return string
 */
function zam_card( $post, $args = array() ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$args = wp_parse_args(
		$args,
		array(
			'variant'  => 'grid',
			'level'    => 3,
			'image'    => true,
			'excerpt'  => false,
			'words'    => 26,
			'author'   => false,
			'date'     => true,
			'kicker'   => true,
			'size'     => 'medium_large',
			'sizes'    => '(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw',
			'priority' => false,
		)
	);

	$level     = min( 4, max( 2, (int) $args['level'] ) );
	$permalink = get_permalink( $post );
	$title     = get_the_title( $post );
	if ( '' === trim( wp_strip_all_tags( $title ) ) ) {
		$title = __( '(Untitled)', 'zahrat-almaghrib' );
	}

	$classes = array( 'zam-card', 'zam-card--' . sanitize_html_class( $args['variant'] ) );
	if ( ! $args['image'] ) {
		$classes[] = 'zam-card--no-media';
	} elseif ( ! has_post_thumbnail( $post ) ) {
		$classes[] = 'zam-card--placeholder';
	}

	$html = '<article class="' . esc_attr( implode( ' ', $classes ) ) . '"' . zam_lang_attrs( $post ) . '>';

	if ( $args['image'] ) {
		// The headline link carries the accessible name; the image link is a duplicate target.
		$html .= '<a class="zam-card__media" href="' . esc_url( $permalink ) . '" tabindex="-1" aria-hidden="true">';
		$html .= zam_post_image( $post, $args['size'], $args['sizes'], $args['priority'] );
		$html .= '</a>';
	}

	$html .= '<div class="zam-card__body">';

	if ( $args['kicker'] ) {
		$cat = zam_primary_category( $post );
		if ( $cat ) {
			// Category labels stay in the site language even on French cards.
			$html .= '<a class="zam-kicker" href="' . esc_url( get_category_link( $cat ) ) . '" lang="' . esc_attr( zam_term_lang( $cat ) ) . '">' . esc_html( $cat->name ) . '</a>';
		}
	}

	$html .= sprintf(
		'<h%1$d class="zam-card__title"><a href="%2$s">%3$s</a></h%1$d>',
		$level,
		esc_url( $permalink ),
		wp_kses( $title, array( 'em' => array(), 'strong' => array(), 'br' => array() ) )
	);

	if ( $args['excerpt'] ) {
		$excerpt = zam_excerpt( $post, (int) $args['words'] );
		if ( $excerpt ) {
			$html .= '<p class="zam-card__excerpt">' . esc_html( $excerpt ) . '</p>';
		}
	}

	$html .= zam_card_meta( $post, (bool) $args['author'], (bool) $args['date'] );
	$html .= '</div></article>';

	return $html;
}

/**
 * Discreet author / date line.
 *
 * @param WP_Post $post   Post.
 * @param bool    $author Include the author.
 * @param bool    $date   Include the date.
 * @return string
 */
function zam_card_meta( $post, $author = false, $date = true ) {
	$parts = array();
	if ( $author ) {
		$name = get_the_author_meta( 'display_name', $post->post_author );
		if ( $name ) {
			$parts[] = '<span class="zam-meta__author" dir="auto">' . esc_html( $name ) . '</span>';
		}
	}
	if ( $date ) {
		$parts[] = sprintf(
			'<time datetime="%s" dir="auto">%s</time>',
			esc_attr( get_the_date( DATE_W3C, $post ) ),
			esc_html( get_the_date( zam_date_format(), $post ) )
		);
	}
	if ( ! $parts ) {
		return '';
	}
	return '<p class="zam-meta" lang="' . esc_attr( zam_site_lang() ) . '">' . implode( '<span class="zam-meta__sep" aria-hidden="true">·</span>', $parts ) . '</p>';
}

/**
 * Plain-text excerpt: the manual excerpt when written, otherwise trimmed content.
 *
 * @param WP_Post $post  Post.
 * @param int     $words Word limit.
 * @return string
 */
function zam_excerpt( $post, $words = 26 ) {
	$text = has_excerpt( $post ) ? $post->post_excerpt : strip_shortcodes( $post->post_content );
	$text = wp_strip_all_tags( excerpt_remove_blocks( $text ) );
	$text = html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) );
	return trim( wp_trim_words( $text, max( 8, $words ), '…' ) );
}

/**
 * Site language as a BCP 47 tag ("ar", "fr-FR").
 *
 * @return string
 */
function zam_site_lang() {
	return str_replace( '_', '-', get_bloginfo( 'language' ) ? get_bloginfo( 'language' ) : 'ar' );
}

/**
 * Language of a category name, so French category names are pronounced correctly.
 *
 * @param WP_Term $term Term.
 * @return string
 */
function zam_term_lang( $term ) {
	return in_array( (int) $term->term_id, zam_french_term_ids(), true ) ? 'fr' : zam_site_lang();
}

/**
 * A section heading with the small star ornament and an optional "more" link.
 *
 * @param string $title    Heading text.
 * @param string $more_url Archive URL.
 * @param int    $level    Heading level.
 * @param string $id       Heading id, used for aria-labelledby.
 * @return string
 */
function zam_section_heading( $title, $more_url = '', $level = 2, $id = '' ) {
	if ( '' === trim( $title ) ) {
		return '';
	}
	$level = min( 4, max( 2, (int) $level ) );
	$html  = '<header class="zam-section-head">';
	$html .= sprintf(
		'<h%1$d class="zam-section-title"%2$s>%3$s<span>%4$s</span></h%1$d>',
		$level,
		$id ? ' id="' . esc_attr( $id ) . '"' : '',
		zam_icon( 'star' ),
		esc_html( $title )
	);
	if ( $more_url ) {
		$html .= sprintf(
			'<a class="zam-more" href="%1$s">%2$s<span class="screen-reader-text"> — %3$s</span>%4$s</a>',
			esc_url( $more_url ),
			esc_html__( 'More', 'zahrat-almaghrib' ),
			esc_html( $title ),
			zam_icon( 'chevron' )
		);
	}
	return $html . '</header>';
}

/**
 * Numbered pagination with "Previous" / "Next" links that are real, crawlable URLs.
 *
 * @param int $total   Total pages.
 * @param int $current Current page.
 * @return string
 */
function zam_pagination( $total, $current ) {
	$total = (int) $total;
	if ( $total < 2 ) {
		return '';
	}
	$links = paginate_links(
		array(
			'total'     => $total,
			'current'   => max( 1, (int) $current ),
			'mid_size'  => 1,
			'end_size'  => 1,
			'type'      => 'array',
			'prev_text' => zam_icon( 'chevron' ) . '<span>' . esc_html__( 'Previous', 'zahrat-almaghrib' ) . '</span>',
			'next_text' => '<span>' . esc_html__( 'Next', 'zahrat-almaghrib' ) . '</span>' . zam_icon( 'chevron' ),
			'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'zahrat-almaghrib' ) . ' </span>',
		)
	);
	if ( ! $links ) {
		return '';
	}
	$html = '<nav class="zam-pagination" aria-label="' . esc_attr__( 'Pagination', 'zahrat-almaghrib' ) . '"><ul>';
	foreach ( $links as $link ) {
		$html .= '<li>' . $link . '</li>';
	}
	return $html . '</ul></nav>';
}

/**
 * Current page number for archives, the posts index and paged static pages.
 *
 * @return int
 */
function zam_current_page() {
	return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
}

/**
 * Date format for theme displays. Keeps the site setting, except that WordPress's
 * English default "F j, Y" reads badly in Arabic ("أكتوبر 19, 2026"), so it becomes "j F Y".
 *
 * @return string
 */
function zam_date_format() {
	$format = (string) get_option( 'date_format' );
	if ( ( '' === $format || 'F j, Y' === $format ) && is_rtl() ) {
		$format = 'j F Y';
	}
	return (string) apply_filters( 'zam_date_format', $format );
}

/**
 * Column count for a grid of $n stories: 2, 3 or 4, never leaving a lonely row.
 *
 * @param int $n Number of stories.
 * @return int
 */
function zam_grid_cols( $n ) {
	$n = (int) $n;
	if ( $n <= 2 ) {
		return 2;
	}
	if ( 0 === $n % 3 && 0 !== $n % 4 ) {
		return 3;
	}
	return 4 === $n || 8 === $n || 7 === $n ? 4 : 3;
}
