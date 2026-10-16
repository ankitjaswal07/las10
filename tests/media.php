<?php
/** Synthetic media fixtures only; run AFTER runtime.php against the same disposable site. */
$root = getenv( 'P10_WP_ROOT' );
if ( ! $root || ! getenv( 'P10_DISPOSABLE_TEST' ) ) { exit( 'Disposable site required.' ); }
$_SERVER['HTTP_HOST']='example.test'; $_SERVER['REQUEST_URI']='/'; $_SERVER['SERVER_PROTOCOL']='HTTP/1.1'; $_SERVER['REQUEST_METHOD']='GET';
require $root . '/wp-load.php';
function verify_media( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( 'FAIL: ' . $label ); } echo "PASS: $label\n"; }
wp_set_current_user( 1 ); $manifest = p10_source_manifest(); $state = get_option( 'p10_source_state' );
// Capture the missing-source-assets report BEFORE adding synthetic fixture images.
file_put_contents( dirname(__DIR__) . '/docs/DISPOSABLE-IMPORT-REPORT.json', wp_json_encode( array( 'environment' => 'DISPOSABLE WordPress 6.8.3 / PHP 8.2.33 WebAssembly / SQLite. No source-media binaries. Not a production import or verified source authorization.', 'report' => $state ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n" );
$profile = $manifest['profiles'][0]; $ordered = $profile['media_ids'];
verify_media( count( $ordered ) > 1, 'Source gallery has ordered image identifiers' );
$ids = array();
foreach ( array_slice( $ordered, 0, 3 ) as $source_id ) {
	foreach ( $manifest['media'] as $row ) {
		if ( $row['source_id'] !== $source_id ) { continue; }
		$result = p10_source_media( $row, $manifest, $state ); $state['media'][$source_id] = $result;
		if ( empty( $result['destination_id'] ) ) { echo wp_json_encode( $result ) . "\n"; }
		verify_media( ! empty( $result['destination_id'] ), 'Synthetic local fixture sideloaded for source ID ' . $source_id );
		$ids[] = $result['destination_id'];
	}
}
verify_media( $ids[0] === $ids[1] && $ids[1] !== $ids[2], 'Identical binary deduplicated by SHA-256; distinct image remains separate' );
$id = $state['profiles'][$profile['source_id']]['destination_id'];
// Restore the fixture baseline if a previous disposable test run stopped midway.
set_post_thumbnail( $id, $ids[0] ); update_post_meta( $id, 'p10_gallery', implode( ',', array_slice( array_values( array_unique( $ids ) ), 1 ) ) );
p10_source_link_galleries( $manifest, $state );
verify_media( array_values( array_unique( $ids ) ) === p10_profile_gallery( get_post( $id ) ), 'Media Library gallery uses source ordering and checksum deduplication' );
$other = $ids[2]; // An actual Media Library image, not an attachment without a file.
set_post_thumbnail( $id, $other );
p10_source_link_galleries( $manifest, $state );
verify_media( (int) get_post_thumbnail_id( $id ) === $other, 'Editor-changed gallery is not overwritten' );
$profile_conflict = array_merge( $profile, array( 'source_id' => 999999 ) );
verify_media( 'conflict_unrelated_slug_manual_review' === p10_source_profile( $profile_conflict )['outcome'], 'Unrelated source with same slug is not overwritten' );
// Restore original gallery after assertion in disposable site only.
set_post_thumbnail( $id, $ids[0] );
echo "MEDIA TESTS FINISHED: SYNTHETIC colored JPEGs only, not source profile photos.\n";
