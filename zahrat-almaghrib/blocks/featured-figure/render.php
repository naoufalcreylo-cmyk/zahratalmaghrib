<?php
/**
 * Article featured image with caption and credit when the media library has them.
 *
 * @package ZahratAlMaghrib
 * @var WP_Block $block
 */

defined( 'ABSPATH' ) || exit;

$zam_post_id  = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
$zam_thumb_id = $zam_post_id ? get_post_thumbnail_id( $zam_post_id ) : 0;
if ( ! $zam_thumb_id || ! wp_attachment_is_image( $zam_thumb_id ) ) {
	return;
}

$zam_alt = trim( (string) get_post_meta( $zam_thumb_id, '_wp_attachment_image_alt', true ) );
$zam_img = wp_get_attachment_image(
	$zam_thumb_id,
	'large',
	false,
	array(
		'class'         => 'zam-figure__img',
		'alt'           => $zam_alt,
		'sizes'         => '(min-width: 1100px) 1040px, 100vw',
		'loading'       => 'eager',
		'fetchpriority' => 'high',
		'decoding'      => 'async',
	)
);
if ( ! $zam_img ) {
	return;
}

$zam_caption = trim( (string) wp_get_attachment_caption( $zam_thumb_id ) );
$zam_meta    = wp_get_attachment_metadata( $zam_thumb_id );
$zam_credit  = isset( $zam_meta['image_meta']['credit'] ) ? trim( (string) $zam_meta['image_meta']['credit'] ) : '';
$zam_credit  = (string) apply_filters( 'zam_image_credit', $zam_credit, $zam_thumb_id );
?>
<figure <?php echo zam_wrapper( array( 'class' => 'zam-figure' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo $zam_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php if ( $zam_caption || $zam_credit ) : ?>
		<figcaption>
			<?php if ( $zam_caption ) : ?>
				<span class="zam-figure__caption"><?php echo wp_kses_post( $zam_caption ); ?></span>
			<?php endif; ?>
			<?php if ( $zam_credit ) : ?>
				<span class="zam-figure__credit">
					<?php
					/* translators: %s: photographer or agency. */
					echo esc_html( sprintf( __( 'Photo: %s', 'zahrat-almaghrib' ), $zam_credit ) );
					?>
				</span>
			<?php endif; ?>
		</figcaption>
	<?php endif; ?>
</figure>
