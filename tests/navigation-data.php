<?php
/** Disposable-only check that responsive drawer retains primary submenu destinations. */
$root = getenv( 'P10_WP_ROOT' );
if ( ! $root || ! getenv( 'P10_DISPOSABLE_TEST' ) ) { exit( 'Disposable site required.' ); }
$_SERVER['HTTP_HOST']='example.test'; $_SERVER['REQUEST_URI']='/'; $_SERVER['SERVER_PROTOCOL']='HTTP/1.1'; $_SERVER['REQUEST_METHOD']='GET';
require $root . '/wp-load.php';
wp_set_current_user( 1 );
$old = get_theme_mod( 'nav_menu_locations', array() );
$menu = wp_create_nav_menu( 'Disposable responsive submenu fixture' );
try {
	$parent = wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Parent fixture', 'menu-item-url' => home_url( '/parent-fixture/' ), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish' ) );
	wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Child fixture', 'menu-item-url' => home_url( '/child-fixture/' ), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent ) );
	set_theme_mod( 'nav_menu_locations', array_merge( (array) $old, array( 'primary' => $menu ) ) );
	$items = p10_drawer_items();
	if ( count( $items ) !== 2 || $items[0]['label'] !== 'Parent fixture' || $items[1]['label'] !== 'Child fixture' || ! $items[1]['child'] ) { throw new RuntimeException( 'FAIL: responsive drawer lost submenu destination' ); }
	echo "PASS: responsive drawer retains parent and child destinations without rewriting the saved menu.\n";
} finally {
	set_theme_mod( 'nav_menu_locations', $old );
	wp_delete_nav_menu( $menu );
}
