<?php
/**
 * Front-end and admin assets.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Fonts families used by the theme.
 *
 * @return array
 */
function p10_font_families() {
	return apply_filters( 'p10_font_families', array(
		'family' => array(
			'Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700',
			'Jost:wght@300;400;500;600',
			'Inter:wght@300;400;500;600;700',
		),
		'display' => 'swap',
	) );
}

/**
 * Google Fonts URL.
 *
 * @return string
 */
function p10_fonts_url() {
	$families = p10_font_families();
	if ( empty( $families['family'] ) ) {
		return '';
	}
	$query = array( 'display' => isset( $families['display'] ) ? $families['display'] : 'swap' );
	return add_query_arg(
		array_merge( $query, array( 'family' => implode( '&family=', $families['family'] ) ) ),
		'https://fonts.googleapis.com/css2'
	);
}

/**
 * Preconnect to the font origins.
 */
function p10_font_preconnect() {
	if ( apply_filters( 'p10_disable_google_fonts', false ) ) {
		return;
	}
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'p10_font_preconnect', 1 );

/**
 * Enqueue front-end assets.
 */
function p10_enqueue_assets() {
	if ( ! apply_filters( 'p10_disable_google_fonts', false ) ) {
		$fonts_url = p10_fonts_url();
		if ( $fonts_url ) {
			wp_enqueue_style( 'p10-fonts', $fonts_url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
	}

	wp_enqueue_style( 'perfect10', get_stylesheet_uri(), array(), P10_VERSION );
	wp_enqueue_style( 'p10-main', P10_URI . 'assets/css/main.css', array( 'perfect10' ), P10_VERSION );

	wp_enqueue_script( 'p10-main', P10_URI . 'assets/js/main.js', array(), P10_VERSION, true );

	wp_localize_script( 'p10-main', 'p10Config', p10_js_config() );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'p10_enqueue_assets' );

/**
 * Data handed to the front-end script.
 *
 * @return array
 */
function p10_js_config() {
	return apply_filters( 'p10_js_config', array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'restUrl'      => esc_url_raw( rest_url( 'perfect10/v1' ) ),
		'nonce'        => wp_create_nonce( 'p10_public' ),
		'restNonce'    => wp_create_nonce( 'wp_rest' ),
		'homeUrl'      => home_url( '/' ),
		'directoryUrl' => p10_directory_url(),
		'phoneHref'    => p10_phone_href(),
		'citiesByCounty' => p10_city_map_for_js(),
		'i18n'         => array(
			'loading'    => __( 'Loading…', 'perfect10' ),
			'allCities'  => __( 'All Cities', 'perfect10' ),
			'allCounties' => __( 'All Counties', 'perfect10' ),
			'noResults'  => __( 'No profiles match those filters.', 'perfect10' ),
			'error'      => __( 'Something went wrong. Please try again.', 'perfect10' ),
			'copied'     => __( 'Copied', 'perfect10' ),
		),
		'ageGate'      => (bool) p10_option( 'age_gate_enabled', 1 ),
		'blurUntilEntered' => (bool) p10_option( 'blur_until_entered', 0 ),
		'reducedMotion' => (bool) p10_option( 'reduced_motion', 0 ),
	) );
}

/**
 * Compact county → city map for the front end.
 *
 * @return array
 */
function p10_city_map_for_js() {
	$map = array();
	foreach ( p10_get_cities_by_county() as $county_slug => $group ) {
		$map[ $county_slug ] = array(
			'label'  => $group['label'],
			'cities' => array(),
		);
		foreach ( $group['cities'] as $city ) {
			$map[ $county_slug ]['cities'][] = array(
				'slug' => $city->slug,
				'name' => $city->name,
			);
		}
	}
	return $map;
}

/**
 * Inline the critical above-the-fold CSS so the hero paints immediately.
 */
function p10_critical_css() {
	$file = P10_DIR . 'assets/css/critical.css';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$css = wp_strip_all_tags( (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	echo '<style id="p10-critical">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- stripped CSS from a theme-owned file.
}
add_action( 'wp_head', 'p10_critical_css', 21 );

/**
 * Enqueue admin assets on the screens that need them.
 *
 * @param string $hook Current admin page.
 */
function p10_admin_assets( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	$needs = in_array( $hook, array( 'post.php', 'post-new.php' ), true )
		|| ( $screen && in_array( $screen->taxonomy, array( 'p10_county', 'p10_city', 'p10_category', 'p10_nationality' ), true ) );

	if ( ! $needs ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'p10-admin', P10_URI . 'assets/css/admin.css', array(), P10_VERSION );
	wp_enqueue_script( 'p10-admin', P10_URI . 'assets/js/admin.js', array( 'jquery' ), P10_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'p10_admin_assets' );

/**
 * Editor styling.
 */
function p10_editor_assets() {
	wp_enqueue_style( 'p10-editor', P10_URI . 'assets/css/editor.css', array(), P10_VERSION );
}
add_action( 'enqueue_block_editor_assets', 'p10_editor_assets' );

/**
 * Let SVG uploads through for administrators (used for brand marks and icons).
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function p10_allow_svg( $mimes ) {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	}
	return $mimes;
}
add_filter( 'upload_mimes', 'p10_allow_svg' );

/**
 * Add theme-specific TinyMCE formats for the editorial typography.
 *
 * @param array $init TinyMCE settings.
 * @return array
 */
function p10_tiny_mce_styles( $init ) {
	$styles = array(
		array(
			'title'   => __( 'Gold italic accent', 'perfect10' ),
			'selector' => 'em, i, span',
			'classes' => 'p10-accent',
		),
		array(
			'title'   => __( 'Section kicker', 'perfect10' ),
			'block'   => 'p',
			'classes' => 'p10-eyebrow',
		),
		array(
			'title'   => __( 'Gold button', 'perfect10' ),
			'selector' => 'a',
			'classes' => 'p10-btn p10-btn--gold',
		),
		array(
			'title'   => __( 'Ghost button', 'perfect10' ),
			'selector' => 'a',
			'classes' => 'p10-btn p10-btn--ghost',
		),
	);

	$existing = isset( $init['style_formats'] ) ? json_decode( $init['style_formats'], true ) : array();
	$init['style_formats'] = wp_json_encode( array_merge( (array) $existing, $styles ) );

	return $init;
}
add_filter( 'tiny_mce_before_init', 'p10_tiny_mce_styles' );

/**
 * Fallback favicon so the theme looks finished before a site icon is set.
 */
function p10_favicon() {
	if ( has_site_icon() ) {
		return;
	}

	printf(
		'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
		esc_url( P10_URI . 'assets/images/favicon.svg' )
	);
}
add_action( 'wp_head', 'p10_favicon', 1 );
