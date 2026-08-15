<?php
defined( 'ABSPATH' ) || exit;
$events = get_posts( array( 'post_type' => 'gmr_event', 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
$report = array( 'count' => count( $events ), 'complete' => 0, 'events' => array() );
foreach ( $events as $event ) {
	$row = array(
		'id' => $event->ID,
		'title' => $event->post_title,
		'start' => get_post_meta( $event->ID, 'gmr_event_start', true ),
		'end' => get_post_meta( $event->ID, 'gmr_event_end', true ),
		'type' => wp_get_post_terms( $event->ID, 'gmr_event_type', array( 'fields' => 'slugs' ) ),
		'thumbnail' => get_post_thumbnail_id( $event->ID ),
		'legacy' => get_post_meta( $event->ID, 'gmr_legacy_source', true ),
	);
	if ( $row['start'] && $row['end'] && $row['type'] && $row['thumbnail'] && 'eventon' === $row['legacy'] ) $report['complete']++;
	$report['events'][] = $row;
}
echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . "\n";
