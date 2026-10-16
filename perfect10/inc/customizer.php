<?php
/**
 * Customizer: one declarative schema defines every editable string, image and
 * toggle. The same schema feeds p10_option_defaults(), so a new control is
 * automatically available to templates through p10_option().
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The full Customizer schema.
 *
 * @return array
 */
function p10_customizer_schema() {
	$static = trailingslashit( home_url( '/' ) );

	$schema = array(

		'brand'     => array(
			'title'    => __( 'Brand & Contact', 'perfect10' ),
			'priority' => 20,
			'controls' => array(
				'brand_name'    => array( 'type' => 'text', 'label' => __( 'Brand name', 'perfect10' ), 'default' => 'Perfect 10' ),
				'brand_suffix'  => array( 'type' => 'text', 'label' => __( 'Brand suffix', 'perfect10' ), 'default' => 'LA · Orange County' ),
				'est_year'      => array( 'type' => 'text', 'label' => __( 'Established year', 'perfect10' ), 'default' => '2010' ),
				'phone_display' => array( 'type' => 'text', 'label' => __( 'Phone (display)', 'perfect10' ), 'default' => '+1 (951) 580-1742' ),
				'phone_raw'     => array( 'type' => 'text', 'label' => __( 'Phone (diallable, no spaces)', 'perfect10' ), 'default' => '+19515801742' ),
				'email'         => array( 'type' => 'text', 'label' => __( 'Contact email', 'perfect10' ), 'default' => 'booking@example.com' ),
				'contact_note'  => array( 'type' => 'textarea', 'label' => __( 'Contact note', 'perfect10' ), 'default' => 'Please do not call from a blocked number.' ),
				'currency'      => array( 'type' => 'select', 'label' => __( 'Default currency', 'perfect10' ), 'default' => 'USD', 'choices' => array(
					'USD' => 'USD $', 'EUR' => 'EUR €', 'GBP' => 'GBP £', 'CAD' => 'CAD $', 'AUD' => 'AUD $',
				) ),
				'address'       => array( 'type' => 'textarea', 'label' => __( 'Footer address', 'perfect10' ), 'default' => '' ),
			),
		),

		'hero'      => array(
			'title'    => __( 'Hero', 'perfect10' ),
			'priority' => 22,
			'controls' => array(
				'hero_image'         => array( 'type' => 'image', 'label' => __( 'Hero background image', 'perfect10' ), 'default' => 0 ),
				'hero_overlay'       => array( 'type' => 'range', 'label' => __( 'Hero overlay opacity (%)', 'perfect10' ), 'default' => 62, 'min' => 0, 'max' => 95 ),
				'hero_eyebrow'       => array( 'type' => 'text', 'label' => __( 'Eyebrow', 'perfect10' ), 'default' => 'Los Angeles · Orange County · Est. 2010' ),
				'hero_title_before'  => array( 'type' => 'text', 'label' => __( 'Title (before accent)', 'perfect10' ), 'default' => 'Discover Your' ),
				'hero_title_accent'  => array( 'type' => 'text', 'label' => __( 'Title (italic accent)', 'perfect10' ), 'default' => 'Perfect 10' ),
				'hero_tagline'       => array( 'type' => 'text', 'label' => __( 'Tagline', 'perfect10' ), 'default' => 'Independent ◆ Verified ◆ Discreet' ),
				'hero_copy'          => array( 'type' => 'textarea', 'label' => __( 'Intro copy', 'perfect10' ), 'default' => 'An 18+ referral network of independent companions across Los Angeles and Orange County. Review featured profiles and request a private referral through our boutique network.' ),
				'hero_cta_primary'   => array( 'type' => 'text', 'label' => __( 'Primary button label', 'perfect10' ), 'default' => 'Explore Profiles' ),
				'hero_cta_primary_url' => array( 'type' => 'text', 'label' => __( 'Primary button URL (blank = directory)', 'perfect10' ), 'default' => '' ),
				'hero_cta_secondary' => array( 'type' => 'text', 'label' => __( 'Secondary button label', 'perfect10' ), 'default' => 'Call' ),
				'hero_scroll_label'  => array( 'type' => 'text', 'label' => __( 'Scroll cue label', 'perfect10' ), 'default' => 'Begin' ),
				'hero_image_url'     => array( 'type' => 'text', 'label' => __( 'Or a remote hero image URL', 'perfect10' ), 'default' => '' ),
				'hero_motion'        => array( 'type' => 'checkbox', 'label' => __( 'Ken Burns image drift', 'perfect10' ), 'default' => 1 ),
			),
		),

		'marquee'   => array(
			'title'    => __( 'Ticker', 'perfect10' ),
			'priority' => 24,
			'controls' => array(
				'marquee_enabled' => array( 'type' => 'checkbox', 'label' => __( 'Show the scrolling ticker', 'perfect10' ), 'default' => 1 ),
				'marquee_items'   => array( 'type' => 'textarea', 'label' => __( 'Ticker items (comma separated)', 'perfect10' ), 'default' => 'Independent Companions,Since 2010,24/7 Booking Agent,18+ Referral Service,LA & Orange County,Discreet by Design' ),
				'marquee_speed'   => array( 'type' => 'range', 'label' => __( 'Ticker duration (seconds)', 'perfect10' ), 'default' => 38, 'min' => 12, 'max' => 120 ),
			),
		),

		'search'    => array(
			'title'    => __( 'Search Panel', 'perfect10' ),
			'priority' => 26,
			'controls' => array(
				'search_enabled' => array( 'type' => 'checkbox', 'label' => __( 'Show the search panel', 'perfect10' ), 'default' => 1 ),
				'search_kicker'  => array( 'type' => 'text', 'label' => __( 'Kicker', 'perfect10' ), 'default' => 'Find Your Companion' ),
				'search_title'   => array( 'type' => 'text', 'label' => __( 'Title (before accent)', 'perfect10' ), 'default' => 'Where are you' ),
				'search_accent'  => array( 'type' => 'text', 'label' => __( 'Title (italic accent)', 'perfect10' ), 'default' => 'looking?' ),
				'search_label'   => array( 'type' => 'text', 'label' => __( 'Caption line', 'perfect10' ), 'default' => 'Where are you looking?' ),
				'search_region'  => array( 'type' => 'text', 'label' => __( 'Region caption', 'perfect10' ), 'default' => 'Los Angeles & Orange County' ),
				'search_button'  => array( 'type' => 'text', 'label' => __( 'Button label', 'perfect10' ), 'default' => 'Search' ),
			),
		),

		'muses'     => array(
			'title'    => __( 'Featured Edit', 'perfect10' ),
			'priority' => 28,
			'controls' => array(
				'muses_enabled' => array( 'type' => 'checkbox', 'label' => __( 'Show this section', 'perfect10' ), 'default' => 1 ),
				'muses_number'  => array( 'type' => 'text', 'label' => __( 'Section number', 'perfect10' ), 'default' => '01' ),
				'muses_kicker'  => array( 'type' => 'text', 'label' => __( 'Kicker', 'perfect10' ), 'default' => 'The Featured Edit' ),
				'muses_title'   => array( 'type' => 'text', 'label' => __( 'Title (before accent)', 'perfect10' ), 'default' => "This Month's" ),
				'muses_accent'  => array( 'type' => 'text', 'label' => __( 'Title (italic accent)', 'perfect10' ), 'default' => 'Muses' ),
				'muses_action'  => array( 'type' => 'text', 'label' => __( 'Action link label', 'perfect10' ), 'default' => 'View the full directory' ),
				'muses_count'   => array( 'type' => 'range', 'label' => __( 'Profiles to show', 'perfect10' ), 'default' => 6, 'min' => 2, 'max' => 12 ),
			),
		),

		'premium'   => array(
			'title'    => __( 'Premium Collection', 'perfect10' ),
			'priority' => 30,
			'controls' => array(
				'premium_enabled' => array( 'type' => 'checkbox', 'label' => __( 'Show this section', 'perfect10' ), 'default' => 1 ),
				'premium_number'  => array( 'type' => 'text', 'label' => __( 'Section number', 'perfect10' ), 'default' => '02' ),
				'premium_kicker'  => array( 'type' => 'text', 'label' => __( 'Kicker', 'perfect10' ), 'default' => 'Exclusive Placement' ),
				'premium_title'   => array( 'type' => 'text', 'label' => __( 'Title (before accent)', 'perfect10' ), 'default' => 'The Premium' ),
				'premium_accent'  => array( 'type' => 'text', 'label' => __( 'Title (italic accent)', 'perfect10' ), 'default' => 'Collection' ),
				'premium_action'  => array( 'type' => 'text', 'label' => __( 'Action link label', 'perfect10' ), 'default' => 'Enter the collection' ),
				'premium_quote'   => array( 'type' => 'textarea', 'label' => __( 'Placement note', 'perfect10' ), 'default' => 'Top-featured placement for the most sought-after profiles.' ),
				'premium_cta'     => array( 'type' => 'text', 'label' => __( 'Packages button label', 'perfect10' ), 'default' => 'View Premium Packages' ),
				'premium_count'   => array( 'type' => 'range', 'label' => __( 'Profiles to show', 'perfect10' ), 'default' => 6, 'min' => 2, 'max' => 12 ),
			),
		),

		'types'     => array(
			'title'    => __( 'Profile Types', 'perfect10' ),
			'priority' => 32,
			'controls' => array(
				'types_enabled' => array( 'type' => 'checkbox', 'label' => __( 'Show this section', 'perfect10' ), 'default' => 1 ),
				'types_number'  => array( 'type' => 'text', 'label' => __( 'Section number', 'perfect10' ), 'default' => '03' ),
				'types_kicker'  => array( 'type' => 'text', 'label' => __( 'Kicker', 'perfect10' ), 'default' => "What You'll Find" ),
				'types_title'   => array( 'type' => 'text', 'label' => __( 'Title (before accent)', 'perfect10' ), 'default' => 'Browse by' ),
				'types_accent'  => array( 'type' => 'text', 'label' => __( 'Title (italic accent)', 'perfect10' ), 'default' => 'Profile Type' ),
				'types_action'  => array( 'type' => 'text', 'label' => __( 'Action link label', 'perfect10' ), 'default' => 'All profile types' ),
				'types_count'   => array( 'type' => 'range', 'label' => __( 'Tiles to show', 'perfect10' ), 'default' => 6, 'min' => 2, 'max' => 12 ),
			),
		),

		'cities'    => array(
			'title'    => __( 'Destinations', 'perfect10' ),
			'priority' => 34,
			'controls' => array(
				'cities_enabled' => array( 'type' => 'checkbox', 'label' => __( 'Show this section', 'perfect10' ), 'default' => 1 ),
				'cities_number'  => array( 'type' => 'text', 'label' => __( 'Section number', 'perfect10' ), 'default' => '04' ),
				'cities_kicker'  => array( 'type' => 'text', 'label' => __( 'Kicker', 'perfect10' ), 'default' => 'Destinations' ),
				'cities_title'   => array( 'type' => 'text', 'label' => __( 'Title (before accent)', 'perfect10' ), 'default' => 'Where the Night' ),
				'cities_accent'  => array( 'type' => 'text', 'label' => __( 'Title (italic accent)', 'perfect10' ), 'default' => 'Takes You' ),
				'cities_action'  => array( 'type' => 'text', 'label' => __( 'Action link label', 'perfect10' ), 'default' => 'Browse all cities' ),
				'cities_count'   => array( 'type' => 'range', 'label' => __( 'Cities to show', 'perfect10' ), 'default' => 9, 'min' => 3, 'max' => 18 ),
			),
		),

		'callband'  => array(
			'title'    => __( 'Call Band', 'perfect10' ),
			'priority' => 36,
			'controls' => array(
				'call_enabled' => array( 'type' => 'checkbox', 'label' => __( 'Show this section', 'perfect10' ), 'default' => 1 ),
				'call_kicker'  => array( 'type' => 'text', 'label' => __( 'Kicker', 'perfect10' ), 'default' => 'Book an Escort Today' ),
				'call_title'   => array( 'type' => 'text', 'label' => __( 'Title (before accent)', 'perfect10' ), 'default' => 'Requests within 24 hours?' ),
				'call_accent'  => array( 'type' => 'text', 'label' => __( 'Title (italic accent)', 'perfect10' ), 'default' => 'Call us.' ),
				'call_copy'    => array( 'type' => 'textarea', 'label' => __( 'Copy', 'perfect10' ), 'default' => 'For requests within the next 24 hours, calling is the fastest way to check current availability. For advance reservations, use our online form and a booking agent will contact you by phone to confirm.' ),
				'call_cta'     => array( 'type' => 'text', 'label' => __( 'Form button label', 'perfect10' ), 'default' => 'Reservation Form' ),
			),
		),

		'about'     => array(
			'title'    => __( 'About Blocks', 'perfect10' ),
			'priority' => 38,
			'controls' => array(
				'about_enabled'   => array( 'type' => 'checkbox', 'label' => __( 'Show the about blocks', 'perfect10' ), 'default' => 1 ),
				'about_1_number'  => array( 'type' => 'text', 'label' => __( 'Block 1 number', 'perfect10' ), 'default' => '01' ),
				'about_1_kicker'  => array( 'type' => 'text', 'label' => __( 'Block 1 kicker', 'perfect10' ), 'default' => 'Established Since 2010' ),
				'about_1_title'   => array( 'type' => 'text', 'label' => __( 'Block 1 title (before accent)', 'perfect10' ), 'default' => 'Refining the art of' ),
				'about_1_accent'  => array( 'type' => 'text', 'label' => __( 'Block 1 title (italic accent)', 'perfect10' ), 'default' => 'companionship' ),
				'about_1_copy'    => array( 'type' => 'textarea', 'label' => __( 'Block 1 copy', 'perfect10' ), 'default' => 'LA\'s Perfect 10\'s is more than a referral agency. It is a network built around trust, discretion, respect and professional presentation. Since 2010, we have connected clients with sought-after escorts in Los Angeles through a boutique-style referral approach.' ),
				'about_2_number'  => array( 'type' => 'text', 'label' => __( 'Block 2 number', 'perfect10' ), 'default' => '02' ),
				'about_2_kicker'  => array( 'type' => 'text', 'label' => __( 'Block 2 kicker', 'perfect10' ), 'default' => 'Respect · Consent · Compliance' ),
				'about_2_title'   => array( 'type' => 'text', 'label' => __( 'Block 2 title (before accent)', 'perfect10' ), 'default' => 'A more personal' ),
				'about_2_accent'  => array( 'type' => 'text', 'label' => __( 'Block 2 title (italic accent)', 'perfect10' ), 'default' => 'referral service' ),
				'about_2_copy'    => array( 'type' => 'textarea', 'label' => __( 'Block 2 copy', 'perfect10' ), 'default' => 'Each companion operates independently and retains control over her own availability, boundaries and decisions. Our role is limited to facilitating the referral process. Any private interaction is strictly between consenting adults and must comply with applicable law. You must be 18+ to request a referral.' ),
			),
		),

		'reviews'   => array(
			'title'    => __( 'Reviews', 'perfect10' ),
			'priority' => 40,
			'controls' => array(
				'reviews_enabled' => array( 'type' => 'checkbox', 'label' => __( 'Show this section', 'perfect10' ), 'default' => 1 ),
				'reviews_number'  => array( 'type' => 'text', 'label' => __( 'Section number', 'perfect10' ), 'default' => '05' ),
				'reviews_kicker'  => array( 'type' => 'text', 'label' => __( 'Kicker', 'perfect10' ), 'default' => 'From Our Guests' ),
				'reviews_title'   => array( 'type' => 'text', 'label' => __( 'Title (before accent)', 'perfect10' ), 'default' => 'In Their' ),
				'reviews_accent'  => array( 'type' => 'text', 'label' => __( 'Title (italic accent)', 'perfect10' ), 'default' => 'Own Words' ),
				'reviews_action'  => array( 'type' => 'text', 'label' => __( 'Action link label', 'perfect10' ), 'default' => 'Read all reviews' ),
				'reviews_count'   => array( 'type' => 'range', 'label' => __( 'Reviews to show', 'perfect10' ), 'default' => 3, 'min' => 1, 'max' => 9 ),
				'reviews_blurb'   => array( 'type' => 'textarea', 'label' => __( 'Reviews archive intro', 'perfect10' ), 'default' => '' ),
			),
		),

		'advertise' => array(
			'title'    => __( 'For Advertisers', 'perfect10' ),
			'priority' => 42,
			'controls' => array(
				'ads_enabled'  => array( 'type' => 'checkbox', 'label' => __( 'Show this section', 'perfect10' ), 'default' => 1 ),
				'ads_kicker'   => array( 'type' => 'text', 'label' => __( 'Kicker', 'perfect10' ), 'default' => 'For Advertisers' ),
				'ads_title'    => array( 'type' => 'text', 'label' => __( 'Title (before accent)', 'perfect10' ), 'default' => 'Get Found in' ),
				'ads_accent'   => array( 'type' => 'text', 'label' => __( 'Title (italic accent)', 'perfect10' ), 'default' => 'Local Searches' ),
				'ads_copy'     => array( 'type' => 'textarea', 'label' => __( 'Copy', 'perfect10' ), 'default' => 'Create a professional listing, select your Los Angeles County or Orange County city placement, and reach visitors searching by location across Southern California.' ),
				'ads_cta_1'    => array( 'type' => 'text', 'label' => __( 'Primary button', 'perfect10' ), 'default' => 'Create Independent Listing' ),
				'ads_cta_2'    => array( 'type' => 'text', 'label' => __( 'Secondary button', 'perfect10' ), 'default' => 'View Premium Packages' ),
				'ads_tags'     => array( 'type' => 'text', 'label' => __( 'Tag line', 'perfect10' ), 'default' => 'Independent · Agency · Trans · Couples · Massage' ),
				'ads_image'    => array( 'type' => 'image', 'label' => __( 'Section image', 'perfect10' ), 'default' => 0 ),
			),
		),

		'footer'    => array(
			'title'    => __( 'Footer', 'perfect10' ),
			'priority' => 44,
			'controls' => array(
				'footer_blurb'   => array( 'type' => 'textarea', 'label' => __( 'Footer blurb', 'perfect10' ), 'default' => 'An 18+ boutique referral network of independent companions across Los Angeles and Orange County.' ),
				'footer_tags'    => array( 'type' => 'text', 'label' => __( 'Footer tag line', 'perfect10' ), 'default' => 'Independent · Agency · Trans · Couples · Massage' ),
				'footer_legal'   => array( 'type' => 'textarea', 'label' => __( 'Legal / disclaimer', 'perfect10' ), 'default' => 'All persons listed are independent adults aged 18 or over. Perfect 10 is a referral and advertising service only and is not responsible for any private arrangement. Nothing on this site constitutes an offer to perform any act that is prohibited by law.' ),
				'footer_copyright' => array( 'type' => 'text', 'label' => __( 'Copyright line', 'perfect10' ), 'default' => '' ),
				'footer_columns' => array( 'type' => 'range', 'label' => __( 'Popular city links to list', 'perfect10' ), 'default' => 12, 'min' => 0, 'max' => 40 ),
				'footer_mark'    => array( 'type' => 'checkbox', 'label' => __( 'Show the large outlined brand watermark', 'perfect10' ), 'default' => 1 ),
				'footer_show_cities' => array( 'type' => 'checkbox', 'label' => __( 'Show Popular Cities column', 'perfect10' ), 'default' => 1 ),
				'footer_show_browse' => array( 'type' => 'checkbox', 'label' => __( 'Show Browse column', 'perfect10' ), 'default' => 1 ),
				'footer_show_explore' => array( 'type' => 'checkbox', 'label' => __( 'Show Explore column', 'perfect10' ), 'default' => 1 ),
				'footer_show_tags' => array( 'type' => 'checkbox', 'label' => __( 'Show footer tag line', 'perfect10' ), 'default' => 1 ),
				'footer_show_disclaimer' => array( 'type' => 'checkbox', 'label' => __( 'Show legal disclaimer', 'perfect10' ), 'default' => 1 ),
				'footer_bg_color' => array( 'type' => 'color', 'label' => __( 'Footer background', 'perfect10' ), 'default' => '#0b0a09' ),
				'footer_text_color' => array( 'type' => 'color', 'label' => __( 'Footer text', 'perfect10' ), 'default' => '#a9a49a' ),
				'footer_link_color' => array( 'type' => 'color', 'label' => __( 'Footer links', 'perfect10' ), 'default' => '#d7d1c5' ),
				'footer_accent_color' => array( 'type' => 'color', 'label' => __( 'Footer headings / accent', 'perfect10' ), 'default' => '#d4af37' ),
				'footer_top_padding' => array( 'type' => 'range', 'label' => __( 'Footer top spacing (base px at 1440px; fluid)', 'perfect10' ), 'default' => 48, 'min' => 16, 'max' => 120 ),
				'footer_bottom_padding' => array( 'type' => 'range', 'label' => __( 'Footer bottom spacing (base px at 1440px; fluid)', 'perfect10' ), 'default' => 24, 'min' => 8, 'max' => 80 ),
				'footer_mark_size' => array( 'type' => 'range', 'label' => __( 'Watermark size (vw)', 'perfect10' ), 'default' => 15, 'min' => 5, 'max' => 20 ),
			),
		),

		'features'  => array(
			'title'    => __( 'Features', 'perfect10' ),
			'priority' => 46,
			'controls' => array(
				'age_gate_enabled' => array( 'type' => 'checkbox', 'label' => __( 'Show the 18+ age gate', 'perfect10' ), 'default' => 1 ),
				'age_gate_title'   => array( 'type' => 'text', 'label' => __( 'Age gate title', 'perfect10' ), 'default' => 'Adults only' ),
				'age_gate_copy'    => array( 'type' => 'textarea', 'label' => __( 'Age gate copy', 'perfect10' ), 'default' => 'This is an 18+ referral directory. By entering you confirm that you are at least 18 years of age and consent to viewing adult content.' ),
				'age_gate_enter'   => array( 'type' => 'text', 'label' => __( 'Enter button', 'perfect10' ), 'default' => 'I am 18 or older — Enter' ),
				'age_gate_exit'    => array( 'type' => 'text', 'label' => __( 'Exit button', 'perfect10' ), 'default' => 'Exit' ),
				'age_gate_exit_url' => array( 'type' => 'text', 'label' => __( 'Exit URL', 'perfect10' ), 'default' => 'https://www.google.com' ),
				'concierge_enabled' => array( 'type' => 'checkbox', 'label' => __( 'Show the floating Message concierge', 'perfect10' ), 'default' => 1 ),
				'concierge_label'  => array( 'type' => 'text', 'label' => __( 'Concierge button label', 'perfect10' ), 'default' => 'Message' ),
				'concierge_note'   => array( 'type' => 'text', 'label' => __( 'Concierge note', 'perfect10' ), 'default' => 'AI concierge · 18+ referral service · Your details stay private' ),
				'reduced_motion'   => array( 'type' => 'checkbox', 'label' => __( 'Disable animations site-wide', 'perfect10' ), 'default' => 0 ),
				'sticky_header'    => array( 'type' => 'checkbox', 'label' => __( 'Sticky header', 'perfect10' ), 'default' => 1 ),
				'forward_profile_enquiries' => array( 'type' => 'checkbox', 'label' => __( 'Also email a profile’s own contact address', 'perfect10' ), 'default' => 1 ),
			),
		),

		'directory' => array(
			'title'    => __( 'Directory', 'perfect10' ),
			'priority' => 48,
			'controls' => array(
				'directory_page'     => array( 'type' => 'page', 'label' => __( 'Directory page', 'perfect10' ), 'default' => 0 ),
				'directory_title'    => array( 'type' => 'text', 'label' => __( 'Archive heading', 'perfect10' ), 'default' => 'Independent Companions' ),
				'directory_blurb'    => array( 'type' => 'textarea', 'label' => __( 'Archive intro', 'perfect10' ), 'default' => '' ),
				'profile_disclaimer' => array( 'type' => 'textarea', 'label' => __( 'Default profile disclaimer', 'perfect10' ), 'default' => '' ),
				'directory_per_page' => array( 'type' => 'range', 'label' => __( 'Profiles per page', 'perfect10' ), 'default' => 12, 'min' => 3, 'max' => 60 ),
				'directory_columns'  => array( 'type' => 'select', 'label' => __( 'Grid columns', 'perfect10' ), 'default' => '3', 'choices' => array(
					'2' => '2', '3' => '3', '4' => '4',
				) ),
				'directory_sidebar'  => array( 'type' => 'checkbox', 'label' => __( 'Show the filter sidebar', 'perfect10' ), 'default' => 1 ),
				'show_phone_on_card' => array( 'type' => 'checkbox', 'label' => __( 'Show phone on profile cards', 'perfect10' ), 'default' => 0 ),
				'blur_until_entered' => array( 'type' => 'checkbox', 'label' => __( 'Blur profile images until the age gate is accepted', 'perfect10' ), 'default' => 0 ),
			),
		),

		'stats'     => array(
			'title'    => __( 'Stat Strip', 'perfect10' ),
			'priority' => 50,
			'controls' => array(
				'stat_profiles'       => array( 'type' => 'range', 'label' => __( 'Profile count override (0 = live count)', 'perfect10' ), 'default' => 0, 'min' => 0, 'max' => 9999 ),
				'stat_profiles_label' => array( 'type' => 'text', 'label' => __( 'Profile stat label', 'perfect10' ), 'default' => 'Verified Profiles' ),
				'stat_cities'         => array( 'type' => 'range', 'label' => __( 'City count override (0 = live count)', 'perfect10' ), 'default' => 0, 'min' => 0, 'max' => 9999 ),
				'stat_cities_label'   => array( 'type' => 'text', 'label' => __( 'City stat label', 'perfect10' ), 'default' => 'Cities Covered' ),
				'stat_agent_value'    => array( 'type' => 'text', 'label' => __( 'Agent stat value', 'perfect10' ), 'default' => '24/7' ),
				'stat_agent_label'    => array( 'type' => 'text', 'label' => __( 'Agent stat label', 'perfect10' ), 'default' => 'Booking Agent' ),
				'stat_years_label'    => array( 'type' => 'text', 'label' => __( 'Years stat label', 'perfect10' ), 'default' => '16+ Years' ),
			),
		),

		'colors'    => array(
			'title'    => __( 'Colours', 'perfect10' ),
			'priority' => 52,
			'controls' => array(
				'color_gold'   => array( 'type' => 'color', 'label' => __( 'Gold accent', 'perfect10' ), 'default' => '#d4af37' ),
				'color_ink'    => array( 'type' => 'color', 'label' => __( 'Background ink', 'perfect10' ), 'default' => '#0b0a09' ),
				'color_cream'  => array( 'type' => 'color', 'label' => __( 'Text cream', 'perfect10' ), 'default' => '#f5f0e6' ),
				'color_panel'  => array( 'type' => 'color', 'label' => __( 'Panel surface', 'perfect10' ), 'default' => '#14120e' ),
			),
		),
	);

	unset( $static );

	return apply_filters( 'p10_customizer_schema', $schema );
}

/**
 * Feed the schema defaults into p10_option().
 *
 * @param array $defaults Existing defaults.
 * @return array
 */
function p10_schema_defaults( $defaults ) {
	foreach ( p10_customizer_schema() as $section ) {
		foreach ( $section['controls'] as $key => $control ) {
			if ( ! array_key_exists( $key, $defaults ) && array_key_exists( 'default', $control ) ) {
				$defaults[ $key ] = $control['default'];
			}
		}
	}
	return $defaults;
}
add_filter( 'p10_option_defaults', 'p10_schema_defaults', 5 );

/**
 * Register the Customizer panel, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function p10_customize_register( $wp_customize ) {
	$wp_customize->add_panel( 'p10_panel', array(
		'title'    => __( 'Perfect 10', 'perfect10' ),
		'priority' => 20,
	) );

	foreach ( p10_customizer_schema() as $section_key => $section ) {
		$wp_customize->add_section( 'p10_' . $section_key, array(
			'title'    => $section['title'],
			'panel'    => 'p10_panel',
			'priority' => isset( $section['priority'] ) ? $section['priority'] : 100,
		) );

		foreach ( $section['controls'] as $key => $control ) {
			$type = isset( $control['type'] ) ? $control['type'] : 'text';

			$wp_customize->add_setting( 'p10_' . $key, array(
				'default'           => isset( $control['default'] ) ? $control['default'] : '',
				'type'              => 'theme_mod',
				'transport'         => 'refresh',
				'sanitize_callback' => p10_sanitize_callback_for( $type ),
			) );

			$control_args = array(
				'label'    => $control['label'],
				'section'  => 'p10_' . $section_key,
				'settings' => 'p10_' . $key,
			);

			switch ( $type ) {
				case 'page':
					$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'p10_' . $key, array_merge( $control_args, array(
						'type'    => 'select',
						'choices' => p10_page_choices(),
					) ) ) );
					break;

				case 'checkbox':
					$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'p10_' . $key, array_merge( $control_args, array( 'type' => 'checkbox' ) ) ) );
					break;

				case 'textarea':
					$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'p10_' . $key, array_merge( $control_args, array( 'type' => 'textarea' ) ) ) );
					break;

				case 'select':
					$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'p10_' . $key, array_merge( $control_args, array(
						'type'    => 'select',
						'choices' => $control['choices'],
					) ) ) );
					break;

				case 'range':
					$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'p10_' . $key, array_merge( $control_args, array(
						'type'        => 'range',
						'input_attrs' => array(
							'min'  => isset( $control['min'] ) ? $control['min'] : 0,
							'max'  => isset( $control['max'] ) ? $control['max'] : 100,
							'step' => 1,
						),
					) ) ) );
					break;

				case 'color':
					$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'p10_' . $key, $control_args ) );
					break;

				case 'image':
					$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'p10_' . $key, $control_args ) );
					break;

				default:
					$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'p10_' . $key, array_merge( $control_args, array( 'type' => 'text' ) ) ) );
					break;
			}
		}
	}

	// Reposition the built-in identity controls into the brand section.
	if ( null !== $wp_customize->get_control( 'blogname' ) ) {
		$wp_customize->get_control( 'blogname' )->section  = 'p10_brand';
		$wp_customize->get_control( 'blogname' )->priority = 1;
	}
	if ( null !== $wp_customize->get_control( 'blogdescription' ) ) {
		$wp_customize->get_control( 'blogdescription' )->section  = 'p10_brand';
		$wp_customize->get_control( 'blogdescription' )->priority = 2;
	}
}
add_action( 'customize_register', 'p10_customize_register' );

/**
 * Pick a sanitiser for a control type.
 *
 * @param string $type Control type.
 * @return callable
 */
function p10_sanitize_callback_for( $type ) {
	switch ( $type ) {
		case 'checkbox':
			return 'p10_sanitize_checkbox';
		case 'color':
			return 'sanitize_hex_color';
		case 'image':
			return 'absint';
		case 'range':
			return 'absint';
		case 'textarea':
			return 'sanitize_textarea_field';
		case 'select':
		case 'page':
			return 'absint';
		default:
			return 'sanitize_text_field';
	}
}

/**
 * Published pages keyed by ID, for the page dropdown control.
 *
 * @return array
 */
function p10_page_choices() {
	$choices = array( 0 => __( '— Automatic —', 'perfect10' ) );

	$pages = get_pages( array( 'sort_column' => 'post_title', 'sort_order' => 'ASC' ) );
	foreach ( (array) $pages as $page ) {
		$choices[ (int) $page->ID ] = $page->post_title;
	}

	return $choices;
}

/**
 * Checkbox sanitiser.
 *
 * @param mixed $value Value.
 * @return int
 */
function p10_sanitize_checkbox( $value ) {
	return $value ? 1 : 0;
}

/**
 * Output the colour customisations as CSS variables.
 */
function p10_custom_colors() {
	$colors = array(
		'--p10-gold'  => p10_option( 'color_gold', '#d4af37' ),
		'--p10-ink'   => p10_option( 'color_ink', '#0b0a09' ),
		'--p10-cream' => p10_option( 'color_cream', '#f5f0e6' ),
		'--p10-panel' => p10_option( 'color_panel', '#14120e' ),
		'--p10-footer-bg' => p10_option( 'footer_bg_color', '#0b0a09' ),
		'--p10-footer-text' => p10_option( 'footer_text_color', '#a9a49a' ),
		'--p10-footer-link' => p10_option( 'footer_link_color', '#d7d1c5' ),
		'--p10-footer-accent' => p10_option( 'footer_accent_color', '#d4af37' ),
	);

	$overlay = absint( p10_option( 'hero_overlay', 62 ) );
	$speed   = absint( p10_option( 'marquee_speed', 38 ) );
	$columns = absint( p10_option( 'directory_columns', 3 ) );
	$footer_top = absint( p10_option( 'footer_top_padding', 48 ) );
	$footer_bottom = absint( p10_option( 'footer_bottom_padding', 24 ) );
	$footer_mark_size = absint( p10_option( 'footer_mark_size', 15 ) );

	echo '<style id="p10-custom-colors">:root{';
	foreach ( $colors as $var => $value ) {
		$value = sanitize_hex_color( $value );
		if ( $value ) {
			echo esc_attr( $var ) . ':' . esc_attr( $value ) . ';';
		}
	}
	echo '--p10-hero-overlay:' . esc_attr( $overlay / 100 ) . ';';
	echo '--p10-marquee-duration:' . esc_attr( $speed ) . 's;';
	echo '--p10-grid-columns:' . esc_attr( $columns ? $columns : 3 ) . ';';
	echo '--p10-footer-top-padding:calc(var(--p10-fluid-space) * ' . esc_attr( max( 16, min( 120, $footer_top ) ) / 16 ) . ');';
	echo '--p10-footer-bottom-padding:calc(var(--p10-fluid-space) * ' . esc_attr( max( 8, min( 80, $footer_bottom ) ) / 16 ) . ');';
	echo '--p10-footer-mark-size:' . esc_attr( max( 5, min( 20, $footer_mark_size ) ) ) . 'vw;';
	echo '}</style>' . "\n";
}
add_action( 'wp_head', 'p10_custom_colors', 20 );

/**
 * Live preview bindings.
 */
function p10_customize_preview_js() {
	wp_enqueue_script(
		'p10-customizer',
		P10_URI . 'assets/js/customizer.js',
		array( 'customize-preview' ),
		P10_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'p10_customize_preview_js' );
