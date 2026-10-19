<?php
/**
 * Category section in one of five layouts.
 *
 * @package ZahratAlMaghrib
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

if ( is_paged() && ! zam_is_editor_preview() ) {
	return;
}

$zam_cat = zam_category_by_slug( $attributes['category'] ?? '' );
if ( ! $zam_cat ) {
	echo zam_editor_notice( __( 'Choose an existing category for this section.', 'zahrat-almaghrib' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$zam_layout = in_array( $attributes['layout'] ?? '', array( 'feature', 'grid', 'list', 'portrait', 'text' ), true ) ? $attributes['layout'] : 'feature';
$zam_tone   = in_array( $attributes['tone'] ?? '', array( 'sand', 'night' ), true ) ? $attributes['tone'] : 'default';
$zam_count  = min( 8, max( 2, (int) ( $attributes['count'] ?? 4 ) ) );

$zam_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'cat'                 => $zam_cat->term_id,
		'posts_per_page'      => $zam_count,
		'post__not_in'        => zam_seen_ids(),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $zam_query->have_posts() ) {
	echo zam_editor_notice( __( 'This category has no posts to show yet.', 'zahrat-almaghrib' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

zam_seen_add( wp_list_pluck( $zam_query->posts, 'ID' ) );

$zam_title = trim( (string) ( $attributes['title'] ?? '' ) );
if ( '' === $zam_title ) {
	$zam_title = $zam_cat->name;
}
$zam_head_id = 'zam-sec-' . $zam_cat->term_id . '-' . wp_unique_id();
$zam_more    = ! empty( $attributes['showMore'] ) ? get_category_link( $zam_cat ) : '';
$zam_excerpt = ! empty( $attributes['excerpt'] );
$zam_posts   = $zam_query->posts;

$zam_classes = 'zam-section zam-section--' . $zam_layout . ' zam-tone--' . $zam_tone;
?>
<section <?php echo zam_wrapper( array( 'class' => $zam_classes ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-labelledby="<?php echo esc_attr( $zam_head_id ); ?>">
	<div class="zam-section__inner">
		<?php echo zam_section_heading( $zam_title, $zam_more, 2, $zam_head_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		switch ( $zam_layout ) {
			case 'feature':
				$zam_first = array_shift( $zam_posts );
				echo '<div class="zam-feature">';
				echo '<div class="zam-feature__main">' . zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$zam_first,
					array(
						'variant' => 'feature',
						'kicker'  => false,
						'excerpt' => true,
						'author'  => true,
						'size'    => 'large',
						'sizes'   => '(min-width: 1024px) 640px, 100vw',
					)
				) . '</div>';
				if ( $zam_posts ) {
					echo '<div class="zam-feature__list">';
					foreach ( $zam_posts as $zam_post ) {
						echo zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							$zam_post,
							array(
								'variant' => 'row',
								'kicker'  => false,
								'excerpt' => $zam_excerpt,
								'words'   => 18,
								'size'    => 'medium',
								'sizes'   => '(min-width: 1024px) 200px, 120px',
							)
						);
					}
					echo '</div>';
				}
				echo '</div>';
				break;

			case 'list':
				echo '<div class="zam-stack">';
				foreach ( $zam_posts as $zam_i => $zam_post ) {
					echo zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$zam_post,
						array(
							'variant' => 0 === $zam_i ? 'grid' : 'list',
							'kicker'  => false,
							'image'   => 0 === $zam_i,
							'excerpt' => 0 === $zam_i && $zam_excerpt,
							'sizes'   => '(min-width: 1024px) 600px, 100vw',
						)
					);
				}
				echo '</div>';
				break;

			case 'text':
				echo '<div class="zam-grid zam-grid--' . zam_grid_cols( count( $zam_posts ) ) . ' zam-grid--text">';
				foreach ( $zam_posts as $zam_post ) {
					echo zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$zam_post,
						array(
							'variant' => 'text',
							'kicker'  => false,
							'image'   => false,
							'author'  => true,
							'excerpt' => true,
							'words'   => 22,
						)
					);
				}
				echo '</div>';
				break;

			default: // grid, portrait.
				echo '<div class="zam-grid zam-grid--' . zam_grid_cols( count( $zam_posts ) ) . '">';
				foreach ( $zam_posts as $zam_post ) {
					echo zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$zam_post,
						array(
							'variant' => 'portrait' === $zam_layout ? 'portrait' : 'grid',
							'kicker'  => false,
							'excerpt' => $zam_excerpt,
							'sizes'   => '(min-width: 1024px) 300px, (min-width: 640px) 50vw, 100vw',
						)
					);
				}
				echo '</div>';
		}
		?>
	</div>
</section>
