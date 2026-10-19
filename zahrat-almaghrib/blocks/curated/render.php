<?php
/**
 * Editors' selection: hand-picked stories only. Hidden on the site until stories are chosen.
 *
 * @package ZahratAlMaghrib
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

if ( is_paged() && ! zam_is_editor_preview() ) {
	return;
}

// Hand-picked stories always show: the editors chose them deliberately.
$zam_ids = array_slice( zam_clean_post_ids( $attributes['postIds'] ?? array() ), 0, 8 );
if ( ! $zam_ids ) {
	echo zam_editor_notice( __( 'Editors’ selection is hidden on the site until you pick stories in the block settings.', 'zahrat-almaghrib' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
zam_seen_add( $zam_ids );
_prime_post_caches( $zam_ids );

$zam_title = trim( (string) ( $attributes['title'] ?? '' ) );
if ( '' === $zam_title ) {
	$zam_title = __( 'Editors’ selection', 'zahrat-almaghrib' );
}
$zam_head_id = 'zam-curated-' . wp_unique_id();
$zam_cols    = zam_grid_cols( count( $zam_ids ) );
?>
<section <?php echo zam_wrapper( array( 'class' => 'zam-section zam-curated zam-tone--sand' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-labelledby="<?php echo esc_attr( $zam_head_id ); ?>">
	<div class="zam-section__inner">
		<?php echo zam_section_heading( $zam_title, '', 2, $zam_head_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="zam-grid zam-grid--<?php echo (int) $zam_cols; ?>">
			<?php
			foreach ( $zam_ids as $zam_id ) {
				echo zam_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$zam_id,
					array(
						'variant' => 'grid',
						'author'  => true,
						'date'    => false,
					)
				);
			}
			?>
		</div>
	</div>
</section>
