<?php
/**
 * Title: Homepage
 * Slug: zahrat-almaghrib/template-home
 * Inserter: no
 *
 * Section categories are referenced by slug, so the template works on any copy
 * of the site. A section whose category does not exist is simply not shown.
 *
 * @package ZahratAlMaghrib
 */

$zam_sections = array(
	'beauty'  => array( 'category' => 'جمال-واناقة', 'layout' => 'feature', 'count' => 4 ),
	'family'  => array( 'category' => 'أسرة-ومجتمع', 'layout' => 'grid', 'count' => 4 ),
	'stars'   => array( 'category' => 'نجوم-و-مشاهير', 'layout' => 'portrait', 'count' => 4, 'tone' => 'night', 'align' => 'full' ),
	'culture' => array( 'category' => 'آداب-و-فنون', 'layout' => 'list', 'count' => 4 ),
	'sport'   => array( 'category' => 'رياضة-و-رشاقة', 'layout' => 'list', 'count' => 4 ),
	'food'    => array( 'category' => 'شهيوات-زهرة', 'layout' => 'list', 'count' => 4 ),
	'opinion' => array( 'category' => 'مقالات-الرأي', 'layout' => 'text', 'count' => 3, 'tone' => 'sand', 'align' => 'full' ),
	'news'    => array( 'category' => 'أخبار-وقضايا', 'layout' => 'feature', 'count' => 5 ),
	'french'  => array( 'category' => 'en-francais', 'layout' => 'grid', 'count' => 4 ),
);
$zam_json  = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
$zam_block = static function ( $key, $in_column = false ) use ( $zam_sections, $zam_json ) {
	$attrs = $zam_sections[ $key ];
	if ( empty( $attrs['align'] ) && ! $in_column ) {
		$attrs['align'] = 'wide';
	}
	return '<!-- wp:zahrat-almaghrib/post-section ' . wp_json_encode( $attrs, $zam_json ) . ' /-->';
};
?>
<!-- wp:zahrat-almaghrib/lead-stories {"supporting":4,"align":"wide"} /-->

<!-- wp:zahrat-almaghrib/ad-slot {"format":"leaderboard","align":"wide"} -->
<!-- wp:html /-->
<!-- /wp:zahrat-almaghrib/ad-slot -->

<!-- wp:zahrat-almaghrib/latest {"align":"wide"} /-->

<?php echo $zam_block( 'beauty' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php echo $zam_block( 'family' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php echo $zam_block( 'stars' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<!-- wp:columns {"align":"wide","className":"zam-columns"} -->
<div class="wp-block-columns alignwide zam-columns">
	<!-- wp:column -->
	<div class="wp-block-column"><?php echo $zam_block( 'culture', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<!-- /wp:column -->
	<!-- wp:column -->
	<div class="wp-block-column"><?php echo $zam_block( 'sport', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<!-- /wp:column -->
	<!-- wp:column -->
	<div class="wp-block-column"><?php echo $zam_block( 'food', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:zahrat-almaghrib/curated {"align":"wide"} /-->

<?php echo $zam_block( 'opinion' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php echo $zam_block( 'news' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<!-- wp:zahrat-almaghrib/ad-slot {"format":"leaderboard","align":"wide"} -->
<!-- wp:html /-->
<!-- /wp:zahrat-almaghrib/ad-slot -->

<?php echo $zam_block( 'french' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
