<?php
/**
 * Plugin Name: Zahrat Al Maghrib — dev helpers (DEVELOPMENT ONLY)
 * Description: Fills the Editors' selection with seeded picks and lets ?zam_nologo=1 preview the wordmark fallback.
 */

add_filter(
	'render_block_data',
	static function ( $block ) {
		if ( 'zahrat-almaghrib/curated' === $block['blockName'] && empty( $block['attrs']['postIds'] ) ) {
			$block['attrs']['postIds'] = array_map( 'intval', (array) get_option( 'zam_dev_picks', array() ) );
		}
		return $block;
	}
);

if ( isset( $_GET['zam_nologo'] ) ) {
	add_filter( 'theme_mod_custom_logo', '__return_false' );
}

// Log PHP warnings/notices raised by theme files so they can be reviewed from the host.
set_error_handler(
	static function ( $no, $str, $file, $line ) {
		if ( false !== strpos( (string) $file, '/themes/zahrat-almaghrib/' ) ) {
			file_put_contents( '/wordpress/zam-dev/php-errors.log', gmdate( 'c' ) . " [$no] $str in $file:$line\n", FILE_APPEND );
		}
		return false;
	}
);
