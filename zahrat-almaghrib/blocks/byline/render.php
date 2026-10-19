<?php
/**
 * Author, publication date and — when edited a day or more later — the updated date.
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
$zam_name      = get_the_author_meta( 'display_name', $zam_author_id );
$zam_published = get_post_timestamp( $zam_post, 'date' );
$zam_modified  = get_post_timestamp( $zam_post, 'modified' );
$zam_updated   = $zam_published && $zam_modified && ( $zam_modified - $zam_published ) >= DAY_IN_SECONDS;
?>
<div <?php echo zam_wrapper( array( 'class' => 'zam-byline' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $zam_name ) : ?>
		<?php echo get_avatar( $zam_author_id, 44, '', '', array( 'class' => 'zam-byline__avatar', 'loading' => 'lazy' ) ); ?>
	<?php endif; ?>
	<div class="zam-byline__text">
		<?php if ( $zam_name ) : ?>
			<p class="zam-byline__author">
				<?php esc_html_e( 'By', 'zahrat-almaghrib' ); ?>
				<a href="<?php echo esc_url( get_author_posts_url( $zam_author_id ) ); ?>" rel="author"><?php echo esc_html( $zam_name ); ?></a>
			</p>
		<?php endif; ?>
		<p class="zam-byline__dates">
			<span>
				<span class="screen-reader-text"><?php esc_html_e( 'Published:', 'zahrat-almaghrib' ); ?> </span>
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $zam_post ) ); ?>"><?php echo esc_html( get_the_date( zam_date_format(), $zam_post ) ); ?></time>
			</span>
			<?php if ( $zam_updated ) : ?>
				<span class="zam-byline__updated">
					<?php esc_html_e( 'Updated:', 'zahrat-almaghrib' ); ?>
					<time datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C, $zam_post ) ); ?>"><?php echo esc_html( get_the_modified_date( zam_date_format(), $zam_post ) ); ?></time>
				</span>
			<?php endif; ?>
		</p>
	</div>
</div>
