<?php
/**
 * Archive / search page header with the page's single h1.
 *
 * @package ZahratAlMaghrib
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$zam_label = '';
$zam_title = '';
$zam_desc  = '';
$zam_extra = '';
$zam_lang  = '';
$zam_total = (int) $wp_query->found_posts;

if ( is_category() ) {
	$zam_term  = get_queried_object();
	$zam_label = __( 'Section', 'zahrat-almaghrib' );
	$zam_title = single_cat_title( '', false );
	$zam_desc  = term_description();
	$zam_lang  = zam_term_lang( $zam_term );
	$zam_kids  = get_terms(
		array(
			'taxonomy'   => 'category',
			'parent'     => $zam_term->term_id,
			'hide_empty' => true,
		)
	);
	if ( $zam_kids && ! is_wp_error( $zam_kids ) ) {
		$zam_extra .= '<ul class="zam-chips" aria-label="' . esc_attr__( 'Subsections', 'zahrat-almaghrib' ) . '">';
		foreach ( $zam_kids as $zam_kid ) {
			$zam_extra .= '<li><a href="' . esc_url( get_category_link( $zam_kid ) ) . '" lang="' . esc_attr( zam_term_lang( $zam_kid ) ) . '">' . esc_html( $zam_kid->name ) . '</a></li>';
		}
		$zam_extra .= '</ul>';
	}
} elseif ( is_tag() ) {
	$zam_label = __( 'Tag', 'zahrat-almaghrib' );
	$zam_title = single_tag_title( '', false );
	$zam_desc  = term_description();
} elseif ( is_author() ) {
	$zam_author = (int) get_query_var( 'author' );
	$zam_label  = __( 'Author', 'zahrat-almaghrib' );
	$zam_title  = get_the_author_meta( 'display_name', $zam_author );
	$zam_bio    = trim( (string) get_the_author_meta( 'description', $zam_author ) );
	$zam_desc   = $zam_bio ? wpautop( $zam_bio ) : '';
	$zam_extra  = get_avatar( $zam_author, 96, '', '', array( 'class' => 'zam-archive-head__avatar' ) );
} elseif ( is_search() ) {
	$zam_label = __( 'Search results', 'zahrat-almaghrib' );
	/* translators: %s: search terms. */
	$zam_title = sprintf( __( '“%s”', 'zahrat-almaghrib' ), get_search_query( false ) );
	if ( '' === trim( get_search_query( false ) ) ) {
		$zam_title = __( 'Search', 'zahrat-almaghrib' );
	}
} elseif ( is_date() ) {
	$zam_label = __( 'Archive', 'zahrat-almaghrib' );
	$zam_title = get_the_archive_title();
} elseif ( is_archive() ) {
	$zam_label = __( 'Archive', 'zahrat-almaghrib' );
	$zam_title = get_the_archive_title();
	$zam_desc  = get_the_archive_description();
} else {
	return;
}

$zam_count = '';
if ( $zam_total > 0 ) {
	$zam_count = is_search()
		/* translators: %s: number of results. */
		? sprintf( _n( '%s result', '%s results', $zam_total, 'zahrat-almaghrib' ), number_format_i18n( $zam_total ) )
		/* translators: %s: number of articles. */
		: sprintf( _n( '%s article', '%s articles', $zam_total, 'zahrat-almaghrib' ), number_format_i18n( $zam_total ) );
}
$zam_page = zam_current_page();
?>
<header <?php echo zam_wrapper( array( 'class' => 'zam-archive-head' . ( is_author() ? ' zam-archive-head--author' : '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	if ( is_author() ) {
		echo $zam_extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>
	<div class="zam-archive-head__text">
		<p class="zam-archive-head__label"><?php echo zam_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $zam_label ); ?></span></p>
		<h1 class="zam-archive-head__title"<?php echo 'fr' === $zam_lang ? ' lang="fr" dir="ltr"' : ''; ?>>
			<?php echo esc_html( wp_strip_all_tags( $zam_title ) ); ?>
			<?php if ( $zam_page > 1 ) : ?>
				<span class="zam-archive-head__page">
					<?php
					/* translators: %s: page number. */
					echo esc_html( sprintf( __( 'Page %s', 'zahrat-almaghrib' ), number_format_i18n( $zam_page ) ) );
					?>
				</span>
			<?php endif; ?>
		</h1>
		<?php if ( $zam_desc && 1 === $zam_page ) : ?>
			<div class="zam-archive-head__desc" dir="auto"><?php echo wp_kses_post( $zam_desc ); ?></div>
		<?php endif; ?>
		<?php if ( $zam_count ) : ?>
			<p class="zam-archive-head__count"><?php echo esc_html( $zam_count ); ?></p>
		<?php endif; ?>
		<?php
		if ( is_category() ) {
			echo $zam_extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( is_search() ) {
			get_search_form( array( 'aria_label' => __( 'Search again', 'zahrat-almaghrib' ) ) );
		}
		?>
	</div>
</header>
