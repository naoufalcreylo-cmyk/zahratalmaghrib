<?php
/**
 * Register the theme's server-rendered blocks (no build step).
 *
 * @package ZahratAlMaghrib
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register every block folder in /blocks.
 */
function zam_register_blocks() {
	$dirs = glob( ZAM_DIR . '/blocks/*', GLOB_ONLYDIR );
	foreach ( (array) $dirs as $dir ) {
		if ( file_exists( $dir . '/block.json' ) ) {
			register_block_type( $dir );
		}
	}
}
add_action( 'init', 'zam_register_blocks' );

/**
 * Block category for the theme's blocks in the inserter.
 *
 * @param array[] $categories Categories.
 * @return array[]
 */
function zam_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'zahrat-almaghrib',
			'title' => __( 'Zahrat Al Maghrib', 'zahrat-almaghrib' ),
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'zam_block_category' );

/**
 * Whether a block is being rendered for the editor preview (ServerSideRender).
 *
 * @return bool
 */
function zam_is_editor_preview() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_GET['context'] ) && 'edit' === sanitize_key( wp_unslash( $_GET['context'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Notice shown only inside the editor when a block has nothing to display.
 *
 * @param string $message Message.
 * @return string
 */
function zam_editor_notice( $message ) {
	if ( ! zam_is_editor_preview() ) {
		return '';
	}
	return '<div class="zam-editor-notice">' . esc_html( $message ) . '</div>';
}

/**
 * Wrapper attributes for a dynamic block.
 *
 * @param array $extra Extra attributes.
 * @return string
 */
function zam_wrapper( $extra = array() ) {
	return get_block_wrapper_attributes( $extra );
}
