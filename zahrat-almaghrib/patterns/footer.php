<?php
/**
 * Title: Footer
 * Slug: zahrat-almaghrib/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 *
 * @package ZahratAlMaghrib
 */

?>
<!-- wp:group {"align":"full","className":"zam-footer","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull zam-footer">
	<!-- wp:group {"align":"wide","className":"zam-footer__grid","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide zam-footer__grid">
		<!-- wp:group {"className":"zam-footer__about","layout":{"type":"default"}} -->
		<div class="wp-block-group zam-footer__about">
			<!-- wp:zahrat-almaghrib/brand {"variant":"compact","showTagline":true} /-->

			<!-- wp:paragraph {"className":"zam-footer__desc"} -->
			<p class="zam-footer__desc"><?php echo esc_html__( 'Zahrat Al Maghrib is a comprehensive women’s online magazine in Arabic and French, covering women, family and society, as well as news, culture, the arts and sport.', 'zahrat-almaghrib' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:social-links {"size":"has-small-icon-size","className":"is-style-logos-only zam-social","layout":{"type":"flex","flexWrap":"nowrap"}} -->
			<ul class="wp-block-social-links has-small-icon-size is-style-logos-only zam-social"></ul>
			<!-- /wp:social-links -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"zam-footer__col","layout":{"type":"default"}} -->
		<div class="wp-block-group zam-footer__col">
			<!-- wp:heading {"level":2,"className":"zam-footer__title"} -->
			<h2 class="wp-block-heading zam-footer__title"><?php echo esc_html__( 'The magazine', 'zahrat-almaghrib' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:zahrat-almaghrib/menu {"location":"footer"} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"zam-footer__col zam-footer__col--sections","layout":{"type":"default"}} -->
		<div class="wp-block-group zam-footer__col zam-footer__col--sections">
			<!-- wp:heading {"level":2,"className":"zam-footer__title"} -->
			<h2 class="wp-block-heading zam-footer__title"><?php echo esc_html__( 'Sections', 'zahrat-almaghrib' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:categories {"showOnlyTopLevel":true,"className":"zam-footer__cats"} /-->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"wide","className":"zam-footer__bottom","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide zam-footer__bottom">
		<!-- wp:zahrat-almaghrib/copyright /-->

		<!-- wp:paragraph {"className":"zam-footer__top"} -->
		<p class="zam-footer__top"><a href="#zam-top"><?php echo esc_html__( 'Back to top', 'zahrat-almaghrib' ); ?></a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
