<?php
/**
 * Title: Header (masthead + sticky navigation)
 * Slug: zahrat-almaghrib/header
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: no
 *
 * @package ZahratAlMaghrib
 */

$zam_search = array(
	'label'          => __( 'Search', 'zahrat-almaghrib' ),
	'showLabel'      => false,
	'placeholder'    => __( 'Search the magazine…', 'zahrat-almaghrib' ),
	'buttonText'     => __( 'Search', 'zahrat-almaghrib' ),
	'buttonPosition' => 'button-only',
	'buttonUseIcon'  => true,
	'className'      => 'zam-search',
);
$zam_nav    = array(
	'overlayMenu' => 'mobile',
	'className'   => 'zam-nav',
	'ariaLabel'   => __( 'Main menu', 'zahrat-almaghrib' ),
	'layout'      => array(
		'type'           => 'flex',
		'justifyContent' => 'center',
		'flexWrap'       => 'wrap',
	),
);
$zam_json   = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
?>
<!-- wp:group {"anchor":"zam-top","align":"full","className":"zam-masthead","layout":{"type":"constrained"}} -->
<div id="zam-top" class="wp-block-group alignfull zam-masthead">
	<!-- wp:group {"align":"wide","className":"zam-topbar","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide zam-topbar">
		<!-- wp:site-tagline {"className":"zam-topbar__tagline"} /-->
		<!-- wp:social-links {"size":"has-small-icon-size","className":"is-style-logos-only zam-social","layout":{"type":"flex","flexWrap":"nowrap"}} -->
		<ul class="wp-block-social-links has-small-icon-size is-style-logos-only zam-social"></ul>
		<!-- /wp:social-links -->
	</div>
	<!-- /wp:group -->

	<!-- wp:zahrat-almaghrib/brand {"variant":"masthead"} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"zam-navbar","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull zam-navbar">
	<!-- wp:group {"align":"wide","className":"zam-navbar__row","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide zam-navbar__row">
		<!-- wp:navigation <?php echo wp_json_encode( $zam_nav, $zam_json ); ?> /-->

		<!-- wp:zahrat-almaghrib/brand {"variant":"compact"} /-->

		<!-- wp:search <?php echo wp_json_encode( $zam_search, $zam_json ); ?> /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
