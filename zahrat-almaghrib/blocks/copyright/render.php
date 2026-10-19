<?php
/**
 * Copyright line with the current year. No legal text is added.
 *
 * @package ZahratAlMaghrib
 */

defined( 'ABSPATH' ) || exit;

printf(
	'<p %1$s>© <span>%2$s</span> %3$s</p>',
	zam_wrapper( array( 'class' => 'zam-copyright' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	esc_html( wp_date( 'Y' ) ),
	esc_html_x( 'زهرة المغرب', 'temporary Arabic wordmark', 'zahrat-almaghrib' )
);
