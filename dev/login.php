<?php
// DEVELOPMENT ONLY: log in as the local Playground admin (user 1). Never deploy.
// Refuse to run anywhere except the local WordPress Playground mount.
if ( '/wordpress/zam-dev' !== __DIR__ ) {
	http_response_code( 404 );
	exit;
}
require dirname( __DIR__ ) . '/wp-load.php';
wp_set_current_user( 1 );
wp_set_auth_cookie( 1, true );
wp_safe_redirect( isset( $_GET['to'] ) ? home_url( wp_unslash( $_GET['to'] ) ) : admin_url() );
exit;
