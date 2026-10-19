<?php
/**
 * Logo, or the temporary Arabic wordmark when no logo is set.
 *
 * @package ZahratAlMaghrib
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$zam_variant = 'compact' === ( $attributes['variant'] ?? '' ) ? 'compact' : 'masthead';
$zam_name    = __( 'Zahrat Al Maghrib', 'zahrat-almaghrib' );
$zam_home    = home_url( '/' );
$zam_current = is_front_page() && ! is_paged();

$zam_inner = '';
if ( has_custom_logo() ) {
	$zam_logo_id = (int) get_theme_mod( 'custom_logo' );
	$zam_inner   = wp_get_attachment_image(
		$zam_logo_id,
		'full',
		false,
		array(
			'class'         => 'zam-brand__logo',
			'alt'           => $zam_name,
			'loading'       => 'eager',
			'decoding'      => 'async',
		)
	);
}
if ( ! $zam_inner ) {
	// Temporary wordmark — not an approved logo. Replace by setting a Site Logo.
	$zam_inner = '<span class="zam-wordmark" lang="ar">' . esc_html_x( 'زهرة المغرب', 'temporary Arabic wordmark', 'zahrat-almaghrib' ) . '</span>';
}

$zam_tagline = '';
if ( ! empty( $attributes['showTagline'] ) && get_bloginfo( 'description' ) ) {
	$zam_tagline = '<p class="zam-brand__tagline">' . esc_html( get_bloginfo( 'description' ) ) . '</p>';
}

printf(
	'<div %1$s><a class="zam-brand__link" href="%2$s" rel="home"%3$s>%4$s</a>%5$s</div>',
	zam_wrapper( array( 'class' => 'zam-brand zam-brand--' . $zam_variant ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	esc_url( $zam_home ),
	$zam_current ? ' aria-current="page"' : '',
	$zam_inner, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$zam_tagline // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
);
