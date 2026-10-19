<?php
/**
 * Listing for the current archive or search, with pagination and helpful empty states.
 *
 * @package ZahratAlMaghrib
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$zam_layout = 'grid' === ( $attributes['layout'] ?? '' ) ? 'grid' : 'row';

if ( zam_is_editor_preview() ) {
	// The editor has no archive context: preview with the latest posts.
	$zam_posts = get_posts(
		array(
			'numberposts'      => 4,
			'suppress_filters' => false,
		)
	);
	$zam_pages = 1;
} else {
	$zam_posts = $wp_query->posts;
	$zam_pages = (int) $wp_query->max_num_pages;
}

if ( ! $zam_posts ) {
	$zam_cats = get_categories(
		array(
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => 8,
			'parent'     => 0,
			'hide_empty' => true,
		)
	);
	?>
	<div <?php echo zam_wrapper( array( 'class' => 'zam-empty' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php if ( is_search() ) : ?>
			<h2 class="zam-empty__title"><?php esc_html_e( 'No results found', 'zahrat-almaghrib' ); ?></h2>
			<p>
				<?php
				/* translators: %s: search terms. */
				echo esc_html( sprintf( __( 'We found no articles matching “%s”.', 'zahrat-almaghrib' ), get_search_query( false ) ) );
				?>
			</p>
			<ul class="zam-empty__tips">
				<li><?php esc_html_e( 'Check the spelling, or try writing the word with or without hamza (أ / ا) and ta marbuta (ة / ه).', 'zahrat-almaghrib' ); ?></li>
				<li><?php esc_html_e( 'Use fewer or more general words.', 'zahrat-almaghrib' ); ?></li>
				<li><?php esc_html_e( 'Browse one of the sections below.', 'zahrat-almaghrib' ); ?></li>
			</ul>
		<?php else : ?>
			<h2 class="zam-empty__title"><?php esc_html_e( 'No articles here yet', 'zahrat-almaghrib' ); ?></h2>
			<p><?php esc_html_e( 'There are no published articles on this page. Browse the latest news or another section.', 'zahrat-almaghrib' ); ?></p>
		<?php endif; ?>
		<?php if ( $zam_cats ) : ?>
			<ul class="zam-chips" aria-label="<?php esc_attr_e( 'Sections', 'zahrat-almaghrib' ); ?>">
				<?php foreach ( $zam_cats as $zam_cat ) : ?>
					<li><a href="<?php echo esc_url( get_category_link( $zam_cat ) ); ?>" lang="<?php echo esc_attr( zam_term_lang( $zam_cat ) ); ?>"><?php echo esc_html( $zam_cat->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<p><a class="zam-more" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the homepage', 'zahrat-almaghrib' ); ?><?php echo zam_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></p>
	</div>
	<?php
	return;
}
?>
<div <?php echo zam_wrapper( array( 'class' => 'zam-archive zam-archive--' . $zam_layout ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="<?php echo 'grid' === $zam_layout ? 'zam-grid zam-grid--3' : 'zam-rows'; ?>">
		<?php
		foreach ( $zam_posts as $zam_i => $zam_post ) {
			if ( 'post' !== $zam_post->post_type ) {
				// Pages in search results: text-only row.
				echo zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$zam_post,
					array(
						'variant' => 'row',
						'level'   => 2,
						'image'   => false,
						'kicker'  => false,
						'excerpt' => true,
						'date'    => false,
					)
				);
				continue;
			}
			echo zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$zam_post,
				array(
					'variant' => 'grid' === $zam_layout ? 'grid' : 'row',
					'level'   => 2,
					'excerpt' => true,
					'words'   => 30,
					'author'  => true,
					'size'    => 'medium_large',
					'sizes'    => '(min-width: 1024px) 320px, (min-width: 640px) 40vw, 100vw',
					'priority' => 0 === $zam_i, // First image is usually the largest above the fold.
				)
			);
		}
		?>
	</div>
	<?php echo zam_pagination( $zam_pages, zam_current_page() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
