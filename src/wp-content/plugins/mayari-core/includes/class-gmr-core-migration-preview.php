<?php
/**
 * Read-only migration planner for the legacy catalog.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Migration_Preview {

	private const DISCIPLINES = array( 'pintura', 'escultura', 'obra-grafica', 'joyeria' );
	private const ARTISTS = array(
		'elmar-rojas', 'irene-carlos', 'rodolfo-abularach', 'milton-bautista',
		'miguel-hernandez', 'ramon-avila', 'rudy-cotton', 'armando-lara',
		'hector-tadeo', 'bernard-dreyfus', 'ednard-dreyfus', 'elsie-wunderlich', 'juan-navipop',
	);
	private const COLLECTIONS = array(
		'coleccion-exclusiva-2015', 'coleccion-exclusiva-2016',
		'coleccion-exclusiva-gran-formato-2016', 'de-las-alegrias-poeticas',
		'de-las-doncellas', 'de-las-doncellas-del-campo', 'de-las-poesias', 'de-las-tradiciones',
	);

	public static function register_hooks(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'gmr migration-preview', array( self::class, 'command' ) );
		}
	}

	/**
	 * Preview the legacy catalog migration. This command never writes data.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : summary, table, json or csv. Default: summary.
	 *
	 * [--warnings-only]
	 * : Include only products that require review.
	 */
	public static function command( array $args, array $assoc_args ): void {
		$format = $assoc_args['format'] ?? 'summary';
		if ( ! in_array( $format, array( 'summary', 'table', 'json', 'csv' ), true ) ) {
			WP_CLI::error( 'Formato invalido. Use summary, table, json o csv.' );
		}

		$ids = get_posts( array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
		) );
		$plans = array_map( array( self::class, 'plan_product' ), $ids );
		if ( isset( $assoc_args['warnings-only'] ) ) {
			$plans = array_values( array_filter( $plans, static fn( $plan ) => ! empty( $plan['warnings'] ) ) );
		}

		if ( 'summary' === $format ) {
			WP_CLI::line( wp_json_encode( self::summarize( $plans, count( $ids ) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
			return;
		}

		$rows = array_map( array( self::class, 'flatten_plan' ), $plans );
		if ( 'json' === $format ) {
			WP_CLI::line( wp_json_encode( $plans, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
			return;
		}
		WP_CLI\Utils\format_items( $format, $rows, array_keys( self::flatten_plan( self::empty_plan() ) ) );
	}

	public static function plan_product( int $product_id ): array {
		$post = get_post( $product_id );
		if ( ! $post || 'product' !== $post->post_type ) {
			return self::empty_plan();
		}
		$categories  = wp_get_post_terms( $product_id, 'product_cat' );
		$by_slug     = array();
		foreach ( is_wp_error( $categories ) ? array() : $categories as $term ) {
			$by_slug[ $term->slug ] = $term->name;
		}
		$disciplines = array_values( array_intersect( array_keys( $by_slug ), self::DISCIPLINES ) );
		$artists     = array_values( array_intersect( array_keys( $by_slug ), self::ARTISTS ) );
		$collections = array_values( array_intersect( array_keys( $by_slug ), self::COLLECTIONS ) );
		$year_raw    = self::attribute_text( $product_id, 'pa_ano' );
		$measure_values = self::attribute_values( $product_id, 'pa_medidas' );
		$measure_raw = $measure_values ? (string) end( $measure_values ) : '';
		$tech_raw    = self::attribute_text( $product_id, 'pa_tecnica' );
		$year        = self::parse_year( $year_raw );
		$dimensions  = self::parse_dimensions( $measure_raw );
		$technical   = self::parse_technical( $tech_raw );
		$warnings    = array();
		$resolutions = array();

		if ( 0 === count( $artists ) ) {
			$artists = array( 'anonimo' );
			$resolutions[] = 'artist_missing_to_anonymous';
		} elseif ( count( $artists ) > 1 ) {
			$artists = array();
			$resolutions[] = 'artist_ambiguous_to_empty';
		}
		if ( 0 === count( $disciplines ) ) {
			$disciplines = array( 'sin-disciplina' );
			$resolutions[] = 'discipline_missing_to_none';
		} elseif ( count( $disciplines ) > 1 ) {
			$disciplines = array( 'joyeria' );
			$resolutions[] = 'discipline_ambiguous_to_jewelry';
		}
		if ( '' !== $year_raw && empty( $year['parsed'] ) ) $warnings[] = 'year_unparsed';
		if ( '' === $year_raw ) $resolutions[] = 'year_missing_to_undated';
		if ( '' !== $measure_raw && empty( $dimensions['parsed'] ) ) $warnings[] = 'dimensions_unparsed';
		if ( count( $measure_values ) > 1 ) $resolutions[] = 'multiple_dimensions_use_last';
		if ( '' === $measure_raw ) $resolutions[] = 'dimensions_missing_to_empty';
		if ( '' !== $tech_raw && empty( $technical['techniques'] ) && empty( $technical['materials'] ) ) {
			$technical['techniques'] = array( 'sin-tecnica' );
			$resolutions[] = 'technique_unclassified_to_none';
		}
		if ( '' === $tech_raw ) {
			$technical['techniques'] = array( 'sin-tecnica' );
			$resolutions[] = 'technique_missing_to_none';
		}
		if ( ! has_post_thumbnail( $product_id ) ) $resolutions[] = 'image_missing_to_attachment_2753';
		if ( '' === (string) get_post_meta( $product_id, '_sku', true ) ) $resolutions[] = 'sku_generated_gmr_legacy_' . $product_id;
		if ( '' === (string) get_post_meta( $product_id, '_price', true ) ) $resolutions[] = 'price_missing_to_consult';
		if ( has_term( 'variable', 'product_type', $product_id ) ) $resolutions[] = 'variable_product_preserved';

		return array(
			'id'          => $product_id,
			'title'       => $post->post_title,
			'status'      => $post->post_status,
			'artist'      => 1 === count( $artists ) ? $artists[0] : $artists,
			'discipline'  => 1 === count( $disciplines ) ? $disciplines[0] : $disciplines,
			'collections' => $collections,
			'year_raw'    => $year_raw,
			'year'        => $year,
			'measure_raw' => $measure_raw,
			'dimensions'  => $dimensions,
			'tech_raw'    => $tech_raw,
			'technical'   => $technical,
			'resolutions' => $resolutions,
			'warnings'    => array_values( array_unique( $warnings ) ),
		);
	}

	public static function parse_year( string $raw ): array {
		$result = array( 'parsed' => false, 'start' => null, 'end' => null );
		if ( preg_match_all( '/(?:19|20)\d{2}/', $raw, $matches ) && ! empty( $matches[0] ) ) {
			$result['parsed'] = true;
			$result['start']  = (int) $matches[0][0];
			$result['end']    = isset( $matches[0][1] ) ? (int) $matches[0][1] : null;
		}
		return $result;
	}

	public static function parse_dimensions( string $raw ): array {
		$result = array( 'parsed' => false, 'height' => null, 'width' => null, 'depth' => null, 'diameter' => null, 'unit' => 'cm', 'notes' => '' );
		$normalized = strtolower( remove_accents( str_replace( array( '×', ',' ), array( 'x', '.' ), $raw ) ) );
		if ( preg_match( '/(\d+(?:\.\d+)?)\s*(?:cm|cms)?\s*diametro/', $normalized, $match ) ) {
			$result['parsed']   = true;
			$result['diameter'] = (float) $match[1];
		} elseif ( preg_match_all( '/\d+(?:\.\d+)?/', $normalized, $matches ) && in_array( count( $matches[0] ), array( 2, 3 ), true ) ) {
			$result['parsed'] = true;
			$result['height'] = (float) $matches[0][0];
			$result['width']  = (float) $matches[0][1];
			$result['depth']  = isset( $matches[0][2] ) ? (float) $matches[0][2] : null;
		}
		if ( preg_match( '/coleccion|individual|incluye|aprox/', $normalized ) ) {
			$result['notes'] = $raw;
		}
		return $result;
	}

	public static function parse_technical( string $raw ): array {
		$text = strtolower( remove_accents( $raw ) );
		$dictionaries = array(
			'techniques' => array( 'oleo', 'acrilico', 'serigrafia', 'litografia', 'grabado', 'mezotinta', 'mixografia', 'piezografia', 'tecnica mixta', 'esgrafiado' ),
			'supports'   => array( 'papel fabriano', 'papel', 'lienzo', 'tela', 'madera' ),
			'materials'  => array( 'bronce', 'plata', 'jade', 'resina', 'piedra volcanica', 'oro', 'carboncillo', 'tinta', 'pastel', 'humo', 'marmol' ),
		);
		$result = array( 'techniques' => array(), 'supports' => array(), 'materials' => array(), 'original' => $raw );
		foreach ( $dictionaries as $group => $terms ) {
			foreach ( $terms as $term ) {
				if ( str_contains( $text, $term ) ) $result[ $group ][] = sanitize_title( $term );
			}
			$result[ $group ] = array_values( array_unique( $result[ $group ] ) );
		}
		if ( in_array( 'papel-fabriano', $result['supports'], true ) ) {
			$result['supports'] = array_values( array_diff( $result['supports'], array( 'papel' ) ) );
		}
		return $result;
	}

	private static function attribute_text( int $product_id, string $taxonomy ): string {
		return implode( ' | ', self::attribute_values( $product_id, $taxonomy ) );
	}

	private static function attribute_values( int $product_id, string $taxonomy ): array {
		$terms = wp_get_post_terms( $product_id, $taxonomy, array( 'fields' => 'names' ) );
		return is_wp_error( $terms ) ? array() : $terms;
	}

	private static function summarize( array $plans, int $catalog_total ): array {
		$warning_counts = array();
		$clean = 0;
		foreach ( $plans as $plan ) {
			if ( empty( $plan['warnings'] ) ) ++$clean;
			foreach ( $plan['warnings'] as $warning ) $warning_counts[ $warning ] = ( $warning_counts[ $warning ] ?? 0 ) + 1;
		}
		arsort( $warning_counts );
		return array( 'mode' => 'preview_read_only', 'catalog_total' => $catalog_total, 'reported_products' => count( $plans ), 'ready_without_warnings' => $clean, 'requires_review' => count( $plans ) - $clean, 'warning_counts' => $warning_counts );
	}

	private static function flatten_plan( array $plan ): array {
		return array(
			'id' => $plan['id'], 'title' => $plan['title'], 'status' => $plan['status'],
			'artist' => is_array( $plan['artist'] ) ? implode( '|', $plan['artist'] ) : $plan['artist'],
			'discipline' => is_array( $plan['discipline'] ) ? implode( '|', $plan['discipline'] ) : $plan['discipline'],
			'collections' => implode( '|', $plan['collections'] ),
			'year' => $plan['year']['parsed'] ? $plan['year']['start'] . ( $plan['year']['end'] ? '-' . $plan['year']['end'] : '' ) : '',
			'dimensions' => $plan['dimensions']['parsed'] ? $plan['measure_raw'] : '',
			'techniques' => implode( '|', $plan['technical']['techniques'] ),
			'supports' => implode( '|', $plan['technical']['supports'] ),
			'materials' => implode( '|', $plan['technical']['materials'] ),
			'resolutions' => implode( '|', $plan['resolutions'] ),
			'warnings' => implode( '|', $plan['warnings'] ),
		);
	}

	private static function empty_plan(): array {
		return array( 'id' => 0, 'title' => '', 'status' => '', 'artist' => '', 'discipline' => '', 'collections' => array(), 'year_raw' => '', 'year' => array( 'parsed' => false, 'start' => null, 'end' => null ), 'measure_raw' => '', 'dimensions' => array( 'parsed' => false ), 'tech_raw' => '', 'technical' => array( 'techniques' => array(), 'supports' => array(), 'materials' => array() ), 'resolutions' => array(), 'warnings' => array() );
	}
}
