<?php
defined( 'ABSPATH' ) || exit;
if ( 'cli' !== PHP_SAPI ) exit( "CLI only\n" );

$types = array(
	5700 => 'exposicion',
	5711 => 'exposicion',
	5722 => 'actividad',
	5724 => 'exposicion',
	5727 => 'exposicion',
);
$report = array();
foreach ( $types as $id => $type ) {
	$post = get_post( $id );
	if ( ! $post || ! in_array( $post->post_type, array( 'ajde_events', 'gmr_event' ), true ) ) {
		$report[ $id ] = 'missing';
		continue;
	}
	$start = absint( get_post_meta( $id, '_unix_start_ev', true ) ?: get_post_meta( $id, 'evcal_srow', true ) );
	$end   = absint( get_post_meta( $id, '_unix_end_ev', true ) ?: get_post_meta( $id, 'evcal_erow', true ) );
	$timezone_name = get_post_meta( $id, '_evo_tz', true ) ?: 'America/Guatemala';
	try { $timezone = new DateTimeZone( $timezone_name ); } catch ( Exception $error ) { $timezone = new DateTimeZone( 'America/Guatemala' ); }
	$local_date = static fn( int $timestamp ): string => $timestamp ? ( new DateTimeImmutable( '@' . $timestamp ) )->setTimezone( $timezone )->format( 'Y-m-d\TH:i' ) : '';
	if ( 'gmr_event' !== $post->post_type ) wp_update_post( array( 'ID' => $id, 'post_type' => 'gmr_event' ) );
	update_post_meta( $id, 'gmr_event_start', $local_date( $start ) );
	update_post_meta( $id, 'gmr_event_end', $local_date( $end ) );
	update_post_meta( $id, 'gmr_event_all_day', false );
	update_post_meta( $id, 'gmr_event_venue', 'Galeria Mayari Rojas' );
	update_post_meta( $id, 'gmr_event_modality', 'presencial' );
	update_post_meta( $id, 'gmr_event_status', $end && $end < time() ? 'finished' : 'upcoming' );
	update_post_meta( $id, 'gmr_visibility', 'public' );
	update_post_meta( $id, 'gmr_legacy_source', 'eventon' );
	update_post_meta( $id, 'gmr_legacy_post_type', 'ajde_events' );
	wp_set_object_terms( $id, $type, 'gmr_event_type', false );
	$report[ $id ] = array( 'type' => $type, 'start' => get_post_meta( $id, 'gmr_event_start', true ), 'end' => get_post_meta( $id, 'gmr_event_end', true ), 'thumbnail' => get_post_thumbnail_id( $id ) );
}
flush_rewrite_rules( false );
echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . "\n";
