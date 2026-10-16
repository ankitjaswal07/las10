<?php
/**
 * Perfect 10 theme bootstrap.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'P10_VERSION', '1.0.0' );
define( 'P10_DIR', trailingslashit( get_template_directory() ) );
define( 'P10_URI', trailingslashit( get_template_directory_uri() ) );

/**
 * Load a theme include if it exists.
 *
 * @param string $file File name relative to /inc.
 */
function p10_require( $file ) {
	$path = P10_DIR . 'inc/' . $file;
	if ( is_readable( $path ) ) {
		require_once $path;
	}
}

p10_require( 'locations.php' );
p10_require( 'setup.php' );
p10_require( 'post-types.php' );
p10_require( 'meta.php' );
p10_require( 'template-tags.php' );
p10_require( 'nav.php' );
p10_require( 'customizer.php' );
p10_require( 'assets.php' );
p10_require( 'query.php' );
p10_require( 'forms.php' );
p10_require( 'enquiries.php' );
p10_require( 'age-gate.php' );
p10_require( 'seo.php' );
p10_require( 'shortcodes.php' );
p10_require( 'widgets.php' );
p10_require( 'demo-content.php' );

if ( is_admin() ) {
	p10_require( 'admin.php' );
}

/**
 * Fire an action once every Perfect 10 module has been loaded.
 */
do_action( 'p10_loaded' );
