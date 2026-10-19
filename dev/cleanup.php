<?php
// DEVELOPMENT ONLY: remove the WordPress default "Hello world!" post and sample page from the local Playground.
// Refuse to run anywhere except the local WordPress Playground mount.
if ( '/wordpress/zam-dev' !== __DIR__ ) {
	http_response_code( 404 );
	exit;
}
require dirname( __DIR__ ) . '/wp-load.php';
wp_delete_post( 1, true );
wp_delete_post( 2, true );
echo 'ok';
