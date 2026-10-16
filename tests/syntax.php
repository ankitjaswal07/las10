<?php
// PHP's own parser, not regex or a compatibility shim. Run on PHP 8.2+.
$root = dirname(__DIR__) . '/perfect10'; $count = 0;
foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root ) ) as $file ) {
	if ( 'php' !== $file->getExtension() ) { continue; }
	try { token_get_all( file_get_contents( $file ), TOKEN_PARSE ); $count++; }
	catch ( ParseError $error ) { fwrite( STDERR, $file . ': ' . $error->getMessage() . "\n" ); exit( 1 ); }
}
echo "PASS: PHP syntax parsed for $count theme files on " . PHP_VERSION . "\n";
