<?php
/**
 * Table of contents, shown only when the article has enough headings.
 *
 * @package ZahratAlMaghrib
 * @var array    $attributes
 * @var WP_Block $block
 */

defined( 'ABSPATH' ) || exit;

$zam_post = get_post( isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID() );
if ( ! $zam_post ) {
	return;
}
$zam_headings = zam_post_headings( $zam_post );
$zam_min      = max( 2, (int) ( $attributes['min'] ?? 3 ) );
$zam_h2       = 0;
foreach ( $zam_headings as $zam_h ) {
	if ( 2 === $zam_h['level'] ) {
		++$zam_h2;
	}
}
if ( $zam_h2 < $zam_min ) {
	echo zam_editor_notice( __( 'The table of contents appears when the article has at least three level-2 headings.', 'zahrat-almaghrib' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
$zam_toc_id = 'zam-toc-' . wp_unique_id();
?>
<nav <?php echo zam_wrapper( array( 'class' => 'zam-toc' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-labelledby="<?php echo esc_attr( $zam_toc_id ); ?>">
	<details open>
		<summary id="<?php echo esc_attr( $zam_toc_id ); ?>"><?php esc_html_e( 'In this article', 'zahrat-almaghrib' ); ?></summary>
		<ol<?php echo zam_lang_attrs( $zam_post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php
			// Level-3 headings nest under the preceding level-2 heading.
			$zam_open_sub = false;
			$zam_open_li  = false;
			foreach ( $zam_headings as $zam_h ) {
				$zam_link = '<a href="#' . esc_attr( $zam_h['id'] ) . '">' . esc_html( $zam_h['text'] ) . '</a>';
				if ( 3 === $zam_h['level'] && $zam_open_li ) {
					if ( ! $zam_open_sub ) {
						echo '<ul>';
						$zam_open_sub = true;
					}
					echo '<li>' . $zam_link . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					continue;
				}
				if ( $zam_open_sub ) {
					echo '</ul>';
					$zam_open_sub = false;
				}
				if ( $zam_open_li ) {
					echo '</li>';
				}
				echo '<li>' . $zam_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$zam_open_li = true;
			}
			echo $zam_open_sub ? '</ul>' : '';
			echo $zam_open_li ? '</li>' : '';
			?>
		</ol>
	</details>
</nav>
