<?php
/**
 * Non-destructive migration preview tests.
 * Run on staging: wp eval-file tests/migration-preview.php
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit( 1 );

$year_single = GMR_Core_Migration_Preview::parse_year( '2016' );
$year_range  = GMR_Core_Migration_Preview::parse_year( '2003 - 2004' );
$dimensions  = GMR_Core_Migration_Preview::parse_dimensions( '14 x 23 x 14 cms' );
$diameter    = GMR_Core_Migration_Preview::parse_dimensions( '150 cms diámetro' );
$technical   = GMR_Core_Migration_Preview::parse_technical( 'Óleo-acrílico sobre tela y lámina de oro' );
$ids         = get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'draft' ), 'posts_per_page' => 1, 'fields' => 'ids' ) );
$plan        = $ids ? GMR_Core_Migration_Preview::plan_product( $ids[0] ) : array();

$checks = array(
	'class_loaded'         => class_exists( 'GMR_Core_Migration_Preview' ),
	'single_year'          => $year_single['parsed'] && 2016 === $year_single['start'] && null === $year_single['end'],
	'year_range'           => $year_range['parsed'] && 2003 === $year_range['start'] && 2004 === $year_range['end'],
	'three_dimensions'     => $dimensions['parsed'] && 14.0 === $dimensions['height'] && 23.0 === $dimensions['width'] && 14.0 === $dimensions['depth'],
	'diameter'             => $diameter['parsed'] && 150.0 === $diameter['diameter'],
	'technique_detection'  => in_array( 'oleo', $technical['techniques'], true ) && in_array( 'acrilico', $technical['techniques'], true ),
	'support_detection'    => in_array( 'tela', $technical['supports'], true ),
	'material_detection'   => in_array( 'oro', $technical['materials'], true ),
	'product_plan_created' => ! empty( $plan['id'] ) && array_key_exists( 'warnings', $plan ),
);

$failed = array_filter( $checks, static fn( $value ) => ! $value );
echo wp_json_encode( array( 'ok' => empty( $failed ), 'checks' => $checks ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;

if ( $failed ) {
	throw new RuntimeException( 'Migration preview test failed.' );
}
