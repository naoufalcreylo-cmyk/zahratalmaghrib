<?php
/**
 * Lightweight share links: no third-party scripts, no counters.
 *
 * @package ZahratAlMaghrib
 * @var WP_Block $block
 */

defined( 'ABSPATH' ) || exit;

$zam_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
if ( ! $zam_post_id ) {
	return;
}
$zam_url      = get_permalink( $zam_post_id );
$zam_title    = html_entity_decode( wp_strip_all_tags( get_the_title( $zam_post_id ) ), ENT_QUOTES, 'UTF-8' );
$zam_whatsapp = 'https://wa.me/?text=' . rawurlencode( $zam_title . ' ' . $zam_url );
$zam_facebook = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $zam_url );
$zam_label_id = 'zam-share-' . wp_unique_id();
$zam_new_tab  = '<span class="screen-reader-text"> ' . esc_html__( '(opens in a new tab)', 'zahrat-almaghrib' ) . '</span>';
?>
<div <?php echo zam_wrapper( array( 'class' => 'zam-share' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<p class="zam-share__label" id="<?php echo esc_attr( $zam_label_id ); ?>"><?php esc_html_e( 'Share', 'zahrat-almaghrib' ); ?></p>
	<ul aria-labelledby="<?php echo esc_attr( $zam_label_id ); ?>">
		<li><a class="zam-share__btn" href="<?php echo esc_url( $zam_whatsapp ); ?>" target="_blank" rel="noopener noreferrer"><?php echo zam_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'WhatsApp', 'zahrat-almaghrib' ); ?></span><?php echo $zam_new_tab; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
		<li><a class="zam-share__btn" href="<?php echo esc_url( $zam_facebook ); ?>" target="_blank" rel="noopener noreferrer"><?php echo zam_icon( 'facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Facebook', 'zahrat-almaghrib' ); ?></span><?php echo $zam_new_tab; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
		<li><button type="button" class="zam-share__btn" data-zam-copy="<?php echo esc_url( $zam_url ); ?>"><?php echo zam_icon( 'link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Copy link', 'zahrat-almaghrib' ); ?></span></button></li>
	</ul>
	<p class="zam-share__status" role="status" aria-live="polite"></p>
</div>
