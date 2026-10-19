<?php
/**
 * Title: Page not found
 * Slug: zahrat-almaghrib/template-404
 * Inserter: no
 *
 * @package ZahratAlMaghrib
 */

$zam_search = array(
	'label'          => __( 'Search the magazine', 'zahrat-almaghrib' ),
	'showLabel'      => true,
	'placeholder'    => __( 'Type a word or a name…', 'zahrat-almaghrib' ),
	'buttonText'     => __( 'Search', 'zahrat-almaghrib' ),
	'buttonPosition' => 'button-outside',
	'className'      => 'zam-404__search',
);
?>
<!-- wp:group {"className":"zam-404","layout":{"type":"constrained","contentSize":"640px"}} -->
<div class="wp-block-group zam-404">
	<!-- wp:paragraph {"className":"zam-404__code"} -->
	<p class="zam-404__code">404</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":1,"className":"zam-404__title"} -->
	<h1 class="wp-block-heading zam-404__title"><?php echo esc_html__( 'This page could not be found', 'zahrat-almaghrib' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p><?php echo esc_html__( 'The link may be broken, or the page may have been moved. Try searching, or go back to the homepage.', 'zahrat-almaghrib' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:search <?php echo wp_json_encode( $zam_search, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?> /-->

	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html__( 'Back to the homepage', 'zahrat-almaghrib' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
