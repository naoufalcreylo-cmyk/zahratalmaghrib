<?php
// DEVELOPMENT ONLY: syntax-check every theme PHP file without executing it.
// Refuse to run anywhere except the local WordPress Playground mount.
if ( '/wordpress/zam-dev' !== __DIR__ ) {
	http_response_code( 404 );
	exit;
}
header( 'Content-Type: text/plain; charset=utf-8' );
$root  = dirname( __DIR__ ) . '/wp-content/themes/zahrat-almaghrib';
$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
$n     = 0;
foreach ( $files as $file ) {
	if ( 'php' !== $file->getExtension() ) {
		continue;
	}
	$n++;
	try {
		token_get_all( file_get_contents( $file->getPathname() ), TOKEN_PARSE );
	} catch ( ParseError $e ) {
		echo 'PARSE ERROR ' . $file->getPathname() . ':' . $e->getLine() . ' ' . $e->getMessage() . "\n";
	}
}
echo "checked $n files, PHP " . PHP_VERSION . "\n";
