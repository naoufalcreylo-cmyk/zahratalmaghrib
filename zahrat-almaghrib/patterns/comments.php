<?php
/**
 * Title: Comments
 * Slug: zahrat-almaghrib/comments
 * Inserter: no
 *
 * Native WordPress comments; Settings → Discussion decides whether they show.
 *
 * @package ZahratAlMaghrib
 */

?>
<!-- wp:comments {"className":"zam-comments"} -->
<div class="wp-block-comments zam-comments">
	<!-- wp:comments-title {"level":2} /-->

	<!-- wp:comment-template -->
	<!-- wp:group {"className":"zam-comment","layout":{"type":"default"}} -->
	<div class="wp-block-group zam-comment">
		<!-- wp:group {"className":"zam-comment__head","layout":{"type":"flex","flexWrap":"nowrap"}} -->
		<div class="wp-block-group zam-comment__head">
			<!-- wp:avatar {"size":40} /-->

			<!-- wp:group {"layout":{"type":"default"}} -->
			<div class="wp-block-group">
				<!-- wp:comment-author-name /-->

				<!-- wp:comment-date /-->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:comment-content /-->

		<!-- wp:group {"layout":{"type":"flex"}} -->
		<div class="wp-block-group">
			<!-- wp:comment-reply-link /-->

			<!-- wp:comment-edit-link /-->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
	<!-- /wp:comment-template -->

	<!-- wp:comments-pagination -->
	<!-- wp:comments-pagination-previous /-->

	<!-- wp:comments-pagination-numbers /-->

	<!-- wp:comments-pagination-next /-->
	<!-- /wp:comments-pagination -->

	<!-- wp:post-comments-form /-->
</div>
<!-- /wp:comments -->
