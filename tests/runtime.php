<?php
/** Run only against a DISPOSABLE WordPress install with this theme active.
 * P10_WP_ROOT=/path/to/disposable/wp php tests/runtime.php
 * Mutates posts/options in that install. Never run on production.
 */
$root = getenv( 'P10_WP_ROOT' );
if ( ! $root || ! is_file( $root . '/wp-load.php' ) || ! getenv( 'P10_DISPOSABLE_TEST' ) ) { exit( "Set P10_WP_ROOT and P10_DISPOSABLE_TEST=1 for a disposable site only.\n" ); }
$_SERVER['HTTP_HOST'] = 'example.test'; $_SERVER['SERVER_NAME'] = 'example.test'; $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1'; $_SERVER['REQUEST_METHOD'] = 'GET'; $_SERVER['REQUEST_URI'] = '/'; $_SERVER['REMOTE_ADDR'] = '192.0.2.123';
require $root . '/wp-load.php';
function check( $condition, $label ) { if ( ! $condition ) { throw new RuntimeException( 'FAIL: ' . $label ); } echo "PASS: $label\n"; }
wp_set_current_user( 1 );
check( current_user_can( 'manage_options' ), 'Test administrator available' );
check( function_exists( 'p10_source_batch' ), 'Updated theme loaded' );
$manifest = p10_source_manifest();
check( 44 === count( $manifest['profiles'] ) && 240 === count( $manifest['media'] ), 'Manifest counts verified' );
$sentinel = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Unrelated sentinel', 'post_content' => 'DO NOT OVERWRITE' ) );
for ( $i = 0; $i < 40; $i++ ) { $state = p10_source_batch(); if ( 240 === $state['media_cursor'] ) { break; } }
$ids = get_posts( array( 'post_type' => 'p10_profile', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) );
check( 42 === count( $ids ), '42 published source profiles imported' );
check( 2 === count( array_filter( $state['profiles'], function ( $r ) { return 'excluded_draft' === $r['outcome']; } ) ), 'Both drafts excluded' );
check( 'DO NOT OVERWRITE' === get_post_field( 'post_content', $sentinel ), 'Unrelated page preserved' );
check( ! array_filter( $state['media'], function ( $r ) { return ! empty( $r['destination_id'] ); } ), 'No attachment URL counted as imported media' );
foreach ( $ids as $id ) {
	check( ! get_post_meta( $id, 'verification_admin_notes', true ), 'Private source notes excluded for profile ' . $id );
	check( wp_get_object_terms( $id, 'p10_city' ) && wp_get_object_terms( $id, 'p10_county' ) && wp_get_object_terms( $id, 'p10_category' ), 'Taxonomy relationships present for ' . $id );
	check( 'unknown' === get_post_meta( $id, 'p10_available', true ), 'No invented live availability for ' . $id );
}
$before_count = count( $ids );
p10_source_batch( true );
for ( $i = 0; $i < 40; $i++ ) { $state = p10_source_batch(); if ( 240 === $state['media_cursor'] ) { break; } }
check( $before_count === count( get_posts( array( 'post_type' => 'p10_profile', 'post_status' => 'publish', 'numberposts' => -1 ) ) ), 'Re-import creates no duplicate profiles' );
check( ! array_filter( $state['profiles'], function ( $r ) { return 'failed' === $r['outcome']; } ), 'No failed profile records' );
// Add poison records and metadata; public retrieval must never reveal them.
$draft = wp_insert_post( array( 'post_type' => 'p10_profile', 'post_status' => 'draft', 'post_title' => 'SECRETPOISON Brandy', 'post_content' => 'DRAFT_SECRET_POISON' ) );
$private = wp_insert_post( array( 'post_type' => 'p10_enquiry', 'post_status' => 'private', 'post_title' => 'Brandy', 'post_content' => 'ENQUIRY_SECRET_POISON' ) );
$password = wp_insert_post( array( 'post_type' => 'p10_profile', 'post_status' => 'publish', 'post_password' => 'testpass', 'post_title' => 'Brandy locked', 'post_content' => 'PASSWORD_SECRET_POISON' ) );
$profile_id = p10_source_find( 'p10_profile', p10_source_key( $manifest['profiles'][0]['source_id'] ) );
update_post_meta( $profile_id, 'verification_admin_notes', 'ADMIN_SECRET_POISON' );
$knowledge = wp_json_encode( p10_chat_knowledge( 'Brandy' ) );
check( false === strpos( $knowledge, 'SECRET_POISON' ), 'Retrieval excludes drafts, enquiries, passwords and private metadata' );
check( false !== strpos( $knowledge, 'Brandy' ), 'Published profile is retrievable' );
check( count( p10_chat_knowledge( 'los angeles' ) ) > 0, 'Location retrieval returns public term/profile links' );
$editor = wp_create_user( 'testeditor', 'disposable-test', 'editor@example.test' );
( new WP_User( $editor ) )->set_role( 'editor' ); wp_set_current_user( $editor );
check( ! current_user_can( 'edit_post', $private ) && ! current_user_can( 'read_post', $private ), 'Editor cannot read or edit enquiries' );
try { p10_source_batch(); check( false, 'Importer denies editor' ); } catch ( RuntimeException $e ) { check( true, 'Importer denies editor' ); }
wp_set_current_user( 0 );
$req = new WP_REST_Request( 'POST', '/p10/v1/assistant' );
$req->set_param( 'message', 'Brandy' );
$res = rest_do_request( $req ); check( 403 === $res->get_status(), 'Public endpoint rejects missing nonce' );
$nonce = wp_create_nonce( 'wp_rest' );
$req->set_header( 'X-WP-Nonce', $nonce ); $req->set_header( 'Origin', 'https://evil.example' );
check( 403 === rest_do_request( $req )->get_status(), 'Public endpoint rejects foreign origin' );
$req->set_header( 'Origin', home_url() );
$req->set_param( 'message', array( 'not-a-string' ) ); check( 400 === rest_do_request( $req )->get_status(), 'Chat rejects array input' );
$req->set_param( 'message', str_repeat( 'a', 601 ) ); check( 400 === rest_do_request( $req )->get_status(), 'Chat rejects oversized input' );
$req->set_param( 'message', 'Brandy' ); $res = rest_do_request( $req );
check( 200 === $res->get_status() && ! empty( $res->get_data()['sources'] ), 'Anonymous nonce-protected text chat works' );
$dispatched = apply_filters( 'rest_post_dispatch', $res, rest_get_server(), $req );
check( 'no-store, private' === $dispatched->get_headers()['Cache-Control'], 'Assistant responses cannot be cached' );
wp_set_current_user( 1 );
$policy = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Privacy policy', 'post_content' => 'Disposable test policy.' ) );
update_option( 'wp_page_for_privacy_policy', $policy ); update_option( 'p10_chat_settings', array( 'followup' => 1 ) );
wp_set_current_user( 0 );
$req = new WP_REST_Request( 'POST', '/p10/v1/assistant/followup' ); $req->set_header( 'X-WP-Nonce', $nonce ); $req->set_header( 'Origin', home_url() );
foreach ( array( 'name' => 'Test enquiry', 'email' => 'followup@example.test', 'message' => 'Test inquiry only.', 'notice' => P10_CHAT_NOTICE ) as $key => $value ) { $req->set_param( $key, $value ); }
check( 400 === rest_do_request( $req )->get_status(), 'Follow-up without consent rejected' );
$req->set_param( 'consent', 'true' ); check( 400 === rest_do_request( $req )->get_status(), 'String consent rejected (must be boolean)' );
$req->set_param( 'consent', true ); $req->set_param( 'notice', 'old' ); check( 400 === rest_do_request( $req )->get_status(), 'Old notice version rejected' );
$req->set_param( 'notice', P10_CHAT_NOTICE );
add_filter( 'pre_wp_mail', function () { return false; } );
$res = rest_do_request( $req );
check( 200 === $res->get_status() && 'transport_failed' === $res->get_data()['notification'], 'Enquiry saves while notification failure is accurately reported' );
$enquiries = p10_chat_privacy_ids( 'followup@example.test', 1 ); check( 1 === count( $enquiries ), 'Only consented enquiry persisted' );
$consent = get_post_meta( $enquiries[0], '_p10_chat_consent', true );
check( true === $consent['consent'] && P10_CHAT_NOTICE === $consent['notice_version'] && ! empty( $consent['timestamp'] ), 'Consent status, timestamp and notice version stored' );
$erasers = apply_filters( 'wp_privacy_personal_data_erasers', array() );
$erased = call_user_func( $erasers['p10-chat']['callback'], 'followup@example.test', 1 );
check( $erased['items_removed'] && ! p10_chat_privacy_ids( 'followup@example.test', 1 ), 'Privacy eraser deletes consented inquiry' );
for ( $i = 0; $i < 25; $i++ ) { $rate = rest_do_request( $req ); if ( 429 === $rate->get_status() ) { break; } }
check( 429 === $rate->get_status(), 'Rate limit returns HTTP 429' );
wp_set_current_user( 1 );
// Remove only the poison fixture records. Source records remain for the separate media test.
foreach ( array( $draft, $private, $password ) as $id ) { wp_delete_post( $id, true ); }
echo "RUNTIME TESTS FINISHED (disposable SQLite database, not production).\n";
