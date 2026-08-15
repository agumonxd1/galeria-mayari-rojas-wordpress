<?php
/** Read-only migration checkpoint inspection. @package MayariCore */
defined( 'ABSPATH' ) || exit( 1 );
$run = get_option( 'gmr_migration_run_catalog-v1', array() );
echo wp_json_encode( array(
	'products' => count( $run['products'] ?? array() ),
	'status' => $run['status'] ?? '',
	'serialized_bytes' => strlen( serialize( $run ) ),
	'entry_5877' => $run['products'][5877]['state'] ?? 'absent',
), JSON_PRETTY_PRINT ) . PHP_EOL;
