<?php
/**
 * SEO: Schema.org JSON-LD, meta description, canonical and robots handling.
 *
 * Complements — never conflicts with — an SEO plugin. Everything here is
 * disabled automatically when Yoast or Rank Math is active.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is a third-party SEO plugin handling this already?
 *
 * @return bool
 */
function p10_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' );
}

/**
 * Print JSON-LD blocks.
 */
function p10_schema_output() {
	if ( p10_seo_plugin_active() || apply_filters( 'p10_disable_schema', false ) ) {
		return;
	}

	$graphs = array_filter( array(
		p10_schema_organization(),
		p10_schema_website(),
		p10_schema_breadcrumb(),
		p10_schema_profile(),
		p10_schema_collection(),
		p10_schema_reviews(),
	) );

	foreach ( $graphs as $graph ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'p10_schema_output', 40 );

/**
 * Organization / LocalBusiness graph.
 *
 * @return array
 */
function p10_schema_organization() {
	return array(
		'@context'  => 'https://schema.org',
		'@type'     => 'Organization',
		'@id'       => home_url( '/#organization' ),
		'name'      => (string) p10_option( 'brand_name', get_bloginfo( 'name' ) ),
		'url'       => home_url( '/' ),
		'description' => (string) p10_option( 'footer_blurb', get_bloginfo( 'description' ) ),
		'foundingDate' => (string) p10_option( 'est_year', '' ),
		'areaServed' => array( 'Los Angeles County', 'Orange County' ),
		'contactPoint' => array(
			'@type'       => 'ContactPoint',
			'telephone'   => p10_phone_raw(),
			'contactType' => 'reservations',
			'availableLanguage' => array( 'English' ),
		),
	);
}

/**
 * WebSite graph with the search action.
 *
 * @return array|null
 */
function p10_schema_website() {
	if ( ! is_front_page() && ! is_home() ) {
		return null;
	}

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => p10_directory_url() . '?q={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * BreadcrumbList graph.
 *
 * @return array|null
 */
function p10_schema_breadcrumb() {
	if ( is_front_page() ) {
		return null;
	}

	$items = array(
		array( 'name' => __( 'Home', 'perfect10' ), 'url' => home_url( '/' ) ),
	);

	if ( is_singular( 'p10_profile' ) ) {
		$post   = get_queried_object();
		$county = p10_get_primary_term( $post->ID, 'p10_county' );
		$city   = p10_get_primary_term( $post->ID, 'p10_city' );

		if ( $county ) {
			$items[] = array( 'name' => $county->name, 'url' => get_term_link( $county ) );
		}
		if ( $city ) {
			$items[] = array( 'name' => $city->name, 'url' => get_term_link( $city ) );
		}
		$items[] = array( 'name' => get_the_title( $post ), 'url' => get_permalink( $post ) );

	} elseif ( is_tax( 'p10_city' ) ) {
		$term   = get_queried_object();
		$county = get_term_by( 'slug', p10_city_county_slug( $term ), 'p10_county' );
		if ( $county ) {
			$items[] = array( 'name' => $county->name, 'url' => get_term_link( $county ) );
		}
		$items[] = array( 'name' => $term->name, 'url' => get_term_link( $term ) );

	} elseif ( is_tax() || is_post_type_archive() ) {
		$term = get_queried_object();
		$items[] = array(
			'name' => $term instanceof WP_Term ? $term->name : post_type_archive_title( '', false ),
			'url'  => $term instanceof WP_Term ? get_term_link( $term ) : p10_current_url(),
		);
	}

	if ( count( $items ) < 2 ) {
		return null;
	}

	$elements = array();
	foreach ( array_values( $items ) as $position => $item ) {
		$elements[] = array(
			'@type'    => 'ListItem',
			'position' => $position + 1,
			'name'     => $item['name'],
			'item'     => $item['url'],
		);
	}

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $elements,
	);
}

/**
 * Person + Service graph for a single profile.
 *
 * @return array|null
 */
function p10_schema_profile() {
	if ( ! is_singular( 'p10_profile' ) ) {
		return null;
	}

	$post     = get_queried_object();
	$currency = (string) p10_meta( $post, 'currency', p10_option( 'currency', 'USD' ) );
	$rate     = (float) p10_meta( $post, 'rate', 0 );
	$city     = p10_get_primary_term( $post->ID, 'p10_city' );
	$county   = p10_get_primary_term( $post->ID, 'p10_county' );
	$nationality = p10_get_primary_term( $post->ID, 'p10_nationality' );

	$person = array(
		'@type' => 'Person',
		'name'  => get_the_title( $post ),
		'url'   => get_permalink( $post ),
		'image' => p10_profile_image( $post, 'p10-card' ),
	);

	if ( $age = (int) p10_meta( $post, 'age' ) ) {
		$person['additionalProperty'] = array(
			array( '@type' => 'PropertyValue', 'name' => 'age', 'value' => $age ),
		);
	}
	if ( $nationality ) {
		$person['nationality'] = $nationality->name;
	}
	if ( $height = (int) p10_meta( $post, 'height_cm' ) ) {
		$person['height'] = array( '@type' => 'QuantitativeValue', 'value' => $height, 'unitCode' => 'CMT' );
	}

	$service = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'@id'         => get_permalink( $post ) . '#service',
		'name'        => get_the_title( $post ),
		'serviceType' => __( 'Companion referral', 'perfect10' ),
		'url'         => get_permalink( $post ),
		'description' => wp_strip_all_tags( get_the_excerpt( $post ) ),
		'provider'    => array( '@id' => home_url( '/#organization' ) ),
		'person'      => $person,
		'audience'    => array(
			'@type'    => 'PeopleAudience',
			'minAge'   => 18,
		),
	);

	if ( $rate ) {
		$service['offers'] = array(
			'@type'         => 'Offer',
			'price'         => number_format( $rate, 2, '.', '' ),
			'priceCurrency' => $currency,
			'url'           => get_permalink( $post ),
			'availability'  => p10_profile_is_available( $post )
				? 'https://schema.org/InStock'
				: 'https://schema.org/OutOfStock',
		);
	}

	$area = array();
	if ( $city ) {
		$area[] = $city->name;
	}
	if ( $county ) {
		$area[] = $county->name;
	}
	if ( $area ) {
		$service['areaServed'] = array_map( static function ( $name ) {
			return array( '@type' => 'Place', 'name' => $name );
		}, $area );
	}

	return $service;
}

/**
 * ItemList graph for directory archives.
 *
 * @return array|null
 */
function p10_schema_collection() {
	global $wp_query;

	if ( ! p10_is_directory() || empty( $wp_query->posts ) ) {
		return null;
	}

	$items    = array();
	$position = 1;

	foreach ( $wp_query->posts as $post ) {
		if ( 'p10_profile' !== $post->post_type ) {
			continue;
		}
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'url'      => get_permalink( $post ),
			'name'     => get_the_title( $post ),
		);
	}

	if ( ! $items ) {
		return null;
	}

	$title = is_tax() || is_category()
		? single_term_title( '', false )
		: post_type_archive_title( '', false );

	return array(
		'@context'         => 'https://schema.org',
		'@type'            => 'ItemList',
		'name'             => $title,
		'numberOfItems'    => (int) $wp_query->found_posts,
		'itemListElement'  => $items,
	);
}

/**
 * Aggregate review graph on the reviews archive.
 *
 * @return array|null
 */
function p10_schema_reviews() {
	if ( ! is_post_type_archive( 'p10_review' ) && ! is_singular( 'p10_review' ) ) {
		return null;
	}

	$reviews = get_posts( array(
		'post_type'      => 'p10_review',
		'posts_per_page' => 20,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	) );

	if ( ! $reviews ) {
		return null;
	}

	$items = array();
	$total = 0;

	foreach ( $reviews as $review ) {
		$rating = (int) p10_get_meta( $review->ID, 'rating', 5 );
		$total += $rating;
		$items[] = array(
			'@type'         => 'Review',
			'author'        => array(
				'@type' => 'Person',
				'name'  => (string) p10_get_meta( $review->ID, 'guest_name', __( 'Verified Guest', 'perfect10' ) ),
			),
			'reviewBody'    => wp_strip_all_tags( get_the_excerpt( $review ) ),
			'reviewRating'  => array(
				'@type'       => 'Rating',
				'ratingValue' => $rating,
				'bestRating'  => 5,
				'worstRating' => 1,
			),
		);
	}

	return array(
		'@context' => 'https://schema.org',
		'@type'    => 'ItemList',
		'name'     => __( 'Guest reviews', 'perfect10' ),
		'itemListElement' => $items,
	);
}

/**
 * Meta description.
 */
function p10_meta_description() {
	if ( p10_seo_plugin_active() ) {
		return;
	}

	$description = '';

	if ( is_front_page() ) {
		$description = (string) p10_option( 'hero_copy', '' );
	} elseif ( is_singular( 'p10_profile' ) ) {
		$post        = get_queried_object();
		$description = wp_strip_all_tags( get_the_excerpt( $post ) );
		if ( get_post_meta( $post->ID, '_p10_source_key', true ) && get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ) ) {
			$description = wp_strip_all_tags( get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ) );
		}
		if ( ! $description ) {
			$description = trim( get_the_title( $post ) . ' · ' . p10_profile_location_line( $post ) . ' · ' . p10_profile_stats_line( $post ) );
		}
	} elseif ( is_tax() ) {
		$term        = get_queried_object();
		$description = (string) p10_term_meta( $term, 'blurb', '' );
		if ( ! $description && ! get_term_meta( $term->term_id, '_p10_source_term', true ) ) {
			/* translators: 1: term name, 2: brand. */
			$description = sprintf( __( 'Browse verified companions in %1$s with %2$s.', 'perfect10' ), $term->name, p10_option( 'brand_name', '' ) );
		}
	} elseif ( is_post_type_archive() ) {
		$description = (string) p10_option( 'footer_blurb', '' );
	} elseif ( is_singular() ) {
		$description = wp_strip_all_tags( get_the_excerpt() );
	}

	$description = trim( preg_replace( '/\s+/', ' ', $description ) );

	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( wp_html_excerpt( $description, 160, '' ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'p10_meta_description', 5 );

/**
 * Open Graph and Twitter tags.
 */
function p10_social_meta() {
	if ( p10_seo_plugin_active() ) {
		return;
	}

	$title = wp_get_document_title();
	$url   = p10_current_url();
	$image = '';
	$type  = 'website';

	if ( is_singular( 'p10_profile' ) ) {
		$type  = 'profile';
		$image = p10_profile_image( get_queried_object(), 'p10-card' );
	} elseif ( is_tax() ) {
		$image = p10_term_image( get_queried_object(), 'p10-card-wide' );
	} elseif ( has_post_thumbnail() ) {
		$image = (string) get_the_post_thumbnail_url( null, 'p10-card-wide' );
	}
	if ( ! $image ) {
		$image = p10_hero_image();
	}

	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
	}
	echo '<meta name="rating" content="adult">' . "\n";
	echo '<meta name="rating-type" content="RTA-5042-1996-1400-1577-RTA">' . "\n";
}
add_action( 'wp_head', 'p10_social_meta', 6 );

/**
 * Keep filtered/paginated directory URLs out of the index.
 */
function p10_robots_meta() {
	if ( p10_seo_plugin_active() ) {
		return;
	}

	$filter_keys = array( 'premium', 'verified', 'available', 'max_rate', 'min_age', 'max_age', 'q', 'sort', 'type' );

	foreach ( $filter_keys as $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET[ $key ] ) && '' !== $_GET[ $key ] ) {
			echo '<meta name="robots" content="noindex,follow">' . "\n";
			return;
		}
	}
}
add_action( 'wp_head', 'p10_robots_meta', 7 );

/**
 * Document title pieces.
 *
 * @param array $title Title parts.
 * @return array
 */
function p10_document_title( $title ) {
	if ( is_front_page() ) {
		$title['title'] = sprintf(
			/* translators: 1: brand, 2: descriptor. */
			'%1$s · %2$s',
			p10_option( 'brand_name', get_bloginfo( 'name' ) ),
			__( 'Los Angeles Premier Companion Directory', 'perfect10' )
		);
		unset( $title['tagline'] );
	}
	return $title;
}
add_filter( 'document_title_parts', 'p10_document_title' );

/** Preserve exported plain-text SEO titles without competing with SEO plugins. */
function p10_source_document_title( $title ) {
	if ( ! p10_seo_plugin_active() && is_singular( 'p10_profile' ) && get_post_meta( get_queried_object_id(), '_p10_source_key', true ) ) {
		$source = get_post_meta( get_queried_object_id(), '_yoast_wpseo_title', true );
		// Leave plugin-specific variables to the plugin instead of outputting raw tokens.
		if ( $source && false === strpos( $source, '%%' ) ) {
			return wp_strip_all_tags( $source );
		}
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'p10_source_document_title', 20 );
