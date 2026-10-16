<?php
/** Elementor integration. No plugin files or saved documents are modified. */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

add_action(
	'init',
	function () {
		foreach ( array( 'page', 'p10_profile', 'p10_review', 'p10_package' ) as $type ) {
			add_post_type_support( $type, 'elementor' );
		}
	},
	20
);

/** Only documents actually built with Elementor replace PHP content. */
function p10_elementor_document( $id = 0 ) {
	$id = $id ? $id : get_queried_object_id();
	if ( ! did_action( 'elementor/loaded' ) || ! is_singular() || ! $id ) {
		return false; }
	$document = \Elementor\Plugin::$instance->documents->get( $id );
	return $document && $document->is_built_with_elementor();
}

add_action(
	'elementor/theme/register_locations',
	function ( $manager ) {
		$manager->register_all_core_location();
		// Custom locations work only with a provider supporting their conditions.
		$manager->register_location( 'search', array( 'label' => __( 'Search', 'perfect10' ) ) );
		$manager->register_location( '404', array( 'label' => __( '404', 'perfect10' ) ) );
	}
);

function p10_elementor_location( $location ) {
	return function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $location );
}

add_filter(
	'template_include',
	function ( $template ) {
		// Leave Elementor's native Canvas/Full Width templates alone.
		if ( is_singular() && in_array( get_page_template_slug(), array( 'elementor_canvas', 'elementor_header_footer' ), true ) ) {
			return $template;
		}
		$location = is_404() ? '404' : ( is_search() ? 'search' : ( is_singular() ? 'single' : ( is_archive() || is_home() ? 'archive' : '' ) ) );
		if ( $location && function_exists( 'elementor_theme_do_location' ) ) {
			$GLOBALS['p10_elementor_fallback']  = $template;
			$GLOBALS['p10_elementor_locations'] = 'search' === $location ? array( 'search', 'archive' ) : ( '404' === $location ? array( '404', 'single' ) : array( $location ) );
			return P10_DIR . 'page-templates/theme-builder.php';
		}
		if ( p10_elementor_document() ) {
			// A homepage is an assembled PHP layout. Replacement requires explicit template selection.
			if ( ! is_front_page() || 'page-templates/elementor-full-width.php' === get_page_template_slug() ) {
				return P10_DIR . 'page-templates/elementor-full-width.php';
			}
		}
		return $template;
	},
	99
);

add_action(
	'elementor/widgets/register',
	function ( $manager ) {
		foreach ( array( 'widget', 'profiles', 'facts', 'gallery' ) as $component ) {
			require_once P10_DIR . 'inc/elementor/class-p10-elementor-' . $component . '.php';
		}
		$manager->register( new P10_Elementor_Profiles() );
		$manager->register( new P10_Elementor_Facts() );
		$manager->register( new P10_Elementor_Gallery() );
	}
);
