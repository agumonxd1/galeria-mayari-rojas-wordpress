<?php
/** One-time staging migration of legacy public artist documents. */
defined( 'ABSPATH' ) || exit( 1 );
$term = get_term_by( 'slug', 'elmar-rojas', 'gmr_artist' );
if ( ! $term ) { echo "Elmar term missing\n"; exit( 1 ); }
$ids = array_filter( array_map( 'absint', explode( ',', (string) get_term_meta( $term->term_id, 'gmr_artist_document_ids', true ) ) ) );
$results = array();
foreach ( $ids as $attachment_id ) {
	$result = GMR_Core_Documents::import_attachment( $attachment_id, 'artist', $term->term_id, true );
	$results[ $attachment_id ] = is_wp_error( $result ) ? $result->get_error_message() : $result;
}
if ( $ids && count( array_filter( $results, 'is_int' ) ) === count( $ids ) ) delete_term_meta( $term->term_id, 'gmr_artist_document_ids' );
echo wp_json_encode( array( 'vault'=>GMR_Core_Documents::vault_path(), 'results'=>$results, 'remaining_public_meta'=>get_term_meta( $term->term_id, 'gmr_artist_document_ids', true ) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
