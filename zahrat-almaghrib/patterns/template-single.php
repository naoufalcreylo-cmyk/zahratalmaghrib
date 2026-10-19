<?php
/**
 * Title: Article
 * Slug: zahrat-almaghrib/template-single
 * Inserter: no
 *
 * @package ZahratAlMaghrib
 */

?>
<!-- wp:group {"tagName":"article","align":"full","className":"zam-article","layout":{"type":"constrained","contentSize":"720px","wideSize":"1040px"}} -->
<article class="wp-block-group alignfull zam-article">
	<!-- wp:group {"tagName":"header","align":"wide","className":"zam-article__head","layout":{"type":"constrained","contentSize":"880px"}} -->
	<header class="wp-block-group alignwide zam-article__head">
		<!-- wp:zahrat-almaghrib/breadcrumbs /-->

		<!-- wp:post-terms {"term":"category","separator":" ","className":"zam-kickers"} /-->

		<!-- wp:post-title {"level":1,"className":"zam-article__title"} /-->

		<!-- wp:post-excerpt {"className":"zam-standfirst"} /-->

		<!-- wp:group {"className":"zam-article__meta","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
		<div class="wp-block-group zam-article__meta">
			<!-- wp:zahrat-almaghrib/byline /-->

			<!-- wp:zahrat-almaghrib/share {"className":"is-compact"} /-->
		</div>
		<!-- /wp:group -->
	</header>
	<!-- /wp:group -->

	<!-- wp:zahrat-almaghrib/featured-figure {"align":"wide"} /-->

	<!-- wp:zahrat-almaghrib/toc /-->

	<!-- wp:post-content {"className":"zam-prose","layout":{"type":"constrained"}} /-->

	<!-- wp:zahrat-almaghrib/ad-slot {"format":"inline"} -->
	<!-- wp:html /-->
	<!-- /wp:zahrat-almaghrib/ad-slot -->

	<!-- wp:group {"tagName":"footer","className":"zam-article__foot","layout":{"type":"default"}} -->
	<footer class="wp-block-group zam-article__foot">
		<!-- wp:post-terms <?php echo wp_json_encode( array( 'term' => 'post_tag', 'prefix' => __( 'Tags:', 'zahrat-almaghrib' ) . ' ', 'separator' => ' ', 'className' => 'zam-tags' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?> /-->

		<!-- wp:zahrat-almaghrib/share /-->

		<!-- wp:zahrat-almaghrib/author-box /-->
	</footer>
	<!-- /wp:group -->

	<!-- wp:pattern {"slug":"zahrat-almaghrib/comments"} /-->

	<!-- wp:zahrat-almaghrib/related {"count":3,"align":"wide"} /-->
</article>
<!-- /wp:group -->
