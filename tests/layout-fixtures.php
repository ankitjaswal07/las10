<?php
/** Disposable-only layout fixtures. Does not overwrite any source profile or existing page. */
$root = getenv( 'P10_WP_ROOT' );
if ( ! $root || ! getenv( 'P10_DISPOSABLE_TEST' ) ) { exit( 'Disposable site required.' ); }
$_SERVER['HTTP_HOST'] = 'example.test'; $_SERVER['REQUEST_URI'] = '/'; $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1'; $_SERVER['REQUEST_METHOD'] = 'GET';
require $root . '/wp-load.php';
wp_set_current_user( 1 );
$body = '<h2>Responsive layout test</h2><p>Theme-authored text, fields and spacing should remain readable as the viewport changes.</p><p><a href="#">' . str_repeat( 'long-link-', 22 ) . '</a></p><figure class="alignfull"><img src="' . esc_url( P10_URI . 'assets/images/placeholder-hero.svg' ) . '" alt="Theme placeholder"></figure><table><tr>' . str_repeat( '<th>Wide table heading</th>', 10 ) . '</tr><tr>' . str_repeat( '<td>Table cells remain scrollable inside their content region.</td>', 10 ) . '</tr></table>';
$routes = array();
foreach ( array( 'standard' => '', 'booking' => 'booking', 'register' => 'register', 'directory' => 'directory', 'cities' => 'cities', 'packages' => 'packages', 'blank' => 'blank' ) as $label => $template ) {
	$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Layout QA ' . $label, 'post_name' => 'layout-qa-' . $label, 'post_content' => $body, 'meta_input' => array( '_wp_page_template' => $template ? 'page-templates/' . $template . '.php' : 'default' ) ) );
	$routes[ $label ] = '/?page_id=' . $id;
}
$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Layout QA home', 'post_name' => 'layout-qa-home', 'post_content' => '[p10_profiles limit="6"]' ) );
update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $id );
$routes['home'] = '/';
$profile = p10_source_find( 'p10_profile', p10_source_key( p10_source_manifest()['profiles'][0]['source_id'] ) );
$routes['profile'] = wp_parse_url( get_permalink( $profile ), PHP_URL_PATH );
$routes['city'] = '/?taxonomy=p10_city&term=los-angeles';
$routes['category'] = '/?taxonomy=p10_category&term=ultimate-la-vip-escorts';
// Theme test options only; no source profile flags, content, metadata or media changed.
set_theme_mod( 'p10_reduced_motion', 1 );
update_option( 'p10_chat_settings', array( 'provider' => 'local', 'followup' => 1 ) );
file_put_contents( dirname(__DIR__) . '/.local/layout-routes.json', wp_json_encode( $routes, JSON_PRETTY_PRINT ) );
echo "PASS: created disposable layout pages/routes; source profiles unchanged.\n";
