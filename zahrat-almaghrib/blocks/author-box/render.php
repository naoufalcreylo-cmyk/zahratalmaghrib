<?php
/**
 * Author card. Shown only when the author has written a biography.
 *
 * @package ZahratAlMaghrib
 * @var WP_Block $block
 */

defined( 'ABSPATH' ) || exit;

$zam_post = get_post( isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID() );
if ( ! $zam_post ) {
	return;
}
$zam_author_id = (int) $zam_post->post_author;
$zam_bio       = trim( (string) get_the_author_meta( 'description', $zam_author_id ) );
if ( '' === $zam_bio ) {
	echo zam_editor_notice( __( 'The author card appears when the author has a biography in their profile.', 'zahrat-almaghrib' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
$zam_name = get_the_author_meta( 'display_name', $zam_author_id );
?>
<aside <?php echo zam_wrapper( array( 'class' => 'zam-author-box' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php esc_attr_e( 'About the author', 'zahrat-almaghrib' ); ?>">
	<?php echo get_avatar( $zam_author_id, 88, '', '', array( 'class' => 'zam-author-box__avatar', 'loading' => 'lazy' ) ); ?>
	<div>
		<p class="zam-author-box__name">
			<a href="<?php echo esc_url( get_author_posts_url( $zam_author_id ) ); ?>"><?php echo esc_html( $zam_name ); ?></a>
		</p>
		<div class="zam-author-box__bio" dir="auto"><?php echo wp_kses_post( wpautop( $zam_bio ) ); ?></div>
		<a class="zam-more" href="<?php echo esc_url( get_author_posts_url( $zam_author_id ) ); ?>">
			<?php
			/* translators: %s: author name. */
			echo esc_html( sprintf( __( 'All articles by %s', 'zahrat-almaghrib' ), $zam_name ) );
			echo zam_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</a>
	</div>
</aside>
