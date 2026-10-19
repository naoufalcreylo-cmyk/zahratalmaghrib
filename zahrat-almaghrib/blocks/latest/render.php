<?php
/**
 * Latest articles with real pagination. Skips stories already shown higher on the page.
 *
 * @package ZahratAlMaghrib
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$zam_page  = zam_current_page();
$zam_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => zam_home_per_page(),
		'paged'               => $zam_page,
		'post__not_in'        => zam_seen_ids(),
		'ignore_sticky_posts' => true,
	)
);

if ( ! $zam_query->have_posts() ) {
	echo zam_editor_notice( __( 'No more posts to show.', 'zahrat-almaghrib' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

zam_seen_add( wp_list_pluck( $zam_query->posts, 'ID' ) );

$zam_title = trim( (string) ( $attributes['title'] ?? '' ) );
if ( '' === $zam_title ) {
	$zam_title = __( 'Latest news', 'zahrat-almaghrib' );
}
$zam_is_paged = $zam_page > 1 && ! zam_is_editor_preview();
?>
<section <?php echo zam_wrapper( array( 'class' => 'zam-latest' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-labelledby="zam-latest-title">
	<?php if ( $zam_is_paged ) : ?>
		<header class="zam-section-head zam-section-head--page">
			<h1 class="zam-section-title" id="zam-latest-title"><?php echo zam_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>
				<?php
				/* translators: 1: section title, 2: page number. */
				echo esc_html( sprintf( __( '%1$s — page %2$s', 'zahrat-almaghrib' ), $zam_title, number_format_i18n( $zam_page ) ) );
				?>
			</span></h1>
		</header>
	<?php else : ?>
		<?php echo zam_section_heading( $zam_title, '', 2, 'zam-latest-title' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
	<div class="zam-grid zam-grid--3">
		<?php
		foreach ( $zam_query->posts as $zam_post ) {
			echo zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$zam_post,
				array(
					'variant' => 'grid',
					'level'   => $zam_is_paged ? 2 : 3,
					'excerpt' => ! empty( $attributes['excerpt'] ),
				)
			);
		}
		?>
	</div>
	<?php echo zam_pagination( $zam_query->max_num_pages, $zam_page ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</section>
