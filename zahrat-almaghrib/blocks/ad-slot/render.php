<?php
/**
 * Labelled advertising slot. Prints nothing until ad code is added inside it.
 *
 * Ad code goes in a Custom HTML block inside this block, so WordPress's own
 * `unfiltered_html` capability decides who may add scripts.
 *
 * @package ZahratAlMaghrib
 * @var array  $attributes
 * @var string $content
 */

defined( 'ABSPATH' ) || exit;

$zam_code     = trim( (string) $content );
$zam_has_code = '' !== trim( wp_strip_all_tags( $zam_code, true ) ) || preg_match( '/<(ins|script|iframe|img|amp-ad)\b/i', $zam_code );
if ( ! $zam_has_code ) {
	echo zam_editor_notice( __( 'Advertising slot: hidden on the site until ad code is added.', 'zahrat-almaghrib' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$zam_format = in_array( $attributes['format'] ?? '', array( 'leaderboard', 'rectangle', 'inline' ), true ) ? $attributes['format'] : 'leaderboard';
?>
<aside <?php echo zam_wrapper( array( 'class' => 'zam-ad zam-ad--' . $zam_format ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php esc_attr_e( 'Advertisement', 'zahrat-almaghrib' ); ?>">
	<p class="zam-ad__label"><?php echo esc_html_x( 'Advertisement', 'ad label', 'zahrat-almaghrib' ); ?></p>
	<div class="zam-ad__slot"><?php echo $zam_code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- saved through core's unfiltered_html rules. ?></div>
</aside>
