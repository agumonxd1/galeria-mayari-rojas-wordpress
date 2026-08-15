<?php
/**
 * Reversible legacy catalog migration.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Migration {
	private const CONFIRM_APPLY = 'APPLY-STAGING';
	private const CONFIRM_ROLLBACK = 'ROLLBACK-STAGING';
	private const META_KEYS = array(
		'gmr_year_start', 'gmr_year_end', 'gmr_undated', 'gmr_height', 'gmr_width',
		'gmr_depth', 'gmr_diameter', 'gmr_dimension_unit', 'gmr_dimensions_notes',
		'gmr_technique_notes', 'gmr_visibility', 'gmr_price_visibility',
		'gmr_commercial_status', 'gmr_migration_run', '_sku', '_thumbnail_id',
	);
	private const TAXONOMIES = array( 'product_cat', 'gmr_artist', 'gmr_collection', 'gmr_technique', 'gmr_support', 'gmr_material' );

	public static function register_hooks(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'gmr migrate', array( self::class, 'apply_command' ) );
			WP_CLI::add_command( 'gmr migrate-rollback', array( self::class, 'rollback_command' ) );
			WP_CLI::add_command( 'gmr migrate-status', array( self::class, 'status_command' ) );
			WP_CLI::add_command( 'gmr migrate-repair', array( self::class, 'repair_command' ) );
		}
	}

	public static function apply_command( array $args, array $assoc_args ): void {
		if ( self::CONFIRM_APPLY !== ( $assoc_args['confirm'] ?? '' ) ) {
			WP_CLI::error( 'Confirmacion requerida: --confirm=' . self::CONFIRM_APPLY );
		}
		$run_id = sanitize_key( $assoc_args['run'] ?? gmdate( 'Ymd-His' ) );
		$option = self::option_name( $run_id );
		$run = get_option( $option, array() );
		if ( ! $run ) {
			$run = array( 'id' => $run_id, 'version' => GMR_CORE_VERSION, 'started_at' => gmdate( 'c' ), 'status' => 'running', 'products' => array(), 'created_terms' => array() );
			add_option( $option, $run, '', false );
		}

		$ids = get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ids', 'meta_query' => array( array( 'key' => 'gmr_migration_run', 'compare' => 'NOT EXISTS' ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$limit = isset( $assoc_args['limit'] ) ? max( 1, absint( $assoc_args['limit'] ) ) : count( $ids );
		$ids = array_slice( $ids, 0, $limit );
		wp_suspend_cache_invalidation( true );
		foreach ( $ids as $product_id ) {
			if ( ! isset( $run['products'][ $product_id ] ) ) {
				$run['products'][ $product_id ] = array( 'snapshot' => self::snapshot( $product_id ), 'state' => 'snapshot' );
				update_option( $option, $run, false );
			}
			self::migrate_product( $product_id, $run_id, $run );
			$run['products'][ $product_id ]['state'] = 'applied';
			update_option( $option, $run, false );
			WP_CLI::log( "Migrated {$product_id}" );
		}
		wp_suspend_cache_invalidation( false );
		$remaining = self::remaining_count();
		$run['status'] = 0 === $remaining ? 'complete' : 'partial';
		$run['finished_at'] = gmdate( 'c' );
		update_option( $option, $run, false );
		update_option( 'gmr_migration_latest_run', $run_id, false );
		WP_CLI::success( wp_json_encode( array( 'run' => $run_id, 'processed' => count( $ids ), 'remaining' => $remaining, 'status' => $run['status'] ) ) );
	}

	public static function rollback_command( array $args, array $assoc_args ): void {
		if ( self::CONFIRM_ROLLBACK !== ( $assoc_args['confirm'] ?? '' ) ) {
			WP_CLI::error( 'Confirmacion requerida: --confirm=' . self::CONFIRM_ROLLBACK );
		}
		$run_id = sanitize_key( $assoc_args['run'] ?? get_option( 'gmr_migration_latest_run', '' ) );
		$option = self::option_name( $run_id );
		$run = get_option( $option );
		if ( ! is_array( $run ) ) WP_CLI::error( 'Corrida no encontrada.' );
		wp_suspend_cache_invalidation( true );
		foreach ( array_reverse( $run['products'], true ) as $product_id => $entry ) {
			self::restore_snapshot( (int) $product_id, $entry['snapshot'] );
			$run['products'][ $product_id ]['state'] = 'rolled_back';
			update_option( $option, $run, false );
		}
		wp_suspend_cache_invalidation( false );
		$run['status'] = 'rolled_back';
		$run['rolled_back_at'] = gmdate( 'c' );
		update_option( $option, $run, false );
		WP_CLI::success( wp_json_encode( array( 'run' => $run_id, 'restored' => count( $run['products'] ) ) ) );
	}

	public static function status_command(): void {
		$latest = get_option( 'gmr_migration_latest_run', '' );
		$run = $latest ? get_option( self::option_name( $latest ), array() ) : array();
		WP_CLI::line( wp_json_encode( array( 'latest_run' => $latest, 'run_status' => $run['status'] ?? 'none', 'processed' => isset( $run['products'] ) ? count( $run['products'] ) : 0, 'remaining' => self::remaining_count() ), JSON_PRETTY_PRINT ) );
	}

	public static function repair_command( array $args, array $assoc_args ): void {
		if ( self::CONFIRM_APPLY !== ( $assoc_args['confirm'] ?? '' ) ) WP_CLI::error( 'Confirmacion requerida: --confirm=' . self::CONFIRM_APPLY );
		$run_id = sanitize_key( $assoc_args['run'] ?? 'catalog-v1' );
		$run = get_option( self::option_name( $run_id ), array() );
		if ( ! $run ) WP_CLI::error( 'Corrida no encontrada.' );
		$none_id = self::ensure_term( 'gmr_technique', 'sin-tecnica', 'Sin tecnica', $run );
		$repaired = 0;
		foreach ( array_keys( $run['products'] ) as $product_id ) {
			if ( ! has_term( '', 'gmr_technique', (int) $product_id ) ) {
				wp_set_object_terms( (int) $product_id, array( $none_id ), 'gmr_technique', false );
				++$repaired;
			}
		}
		$irene = self::ensure_term( 'gmr_artist', 'irene-carlos', 'Irene Carlos', $run );
		wp_set_object_terms( 5877, array( $irene ), 'gmr_artist', false );
		$run['repairs'] = array( 'at' => gmdate( 'c' ), 'technique_none' => $repaired, 'artist_5877' => 'irene-carlos' );
		update_option( self::option_name( $run_id ), $run, false );
		WP_CLI::success( wp_json_encode( $run['repairs'] ) );
	}

	private static function snapshot( int $product_id ): array {
		$snapshot = array( 'meta' => array(), 'terms' => array() );
		foreach ( self::META_KEYS as $key ) {
			$snapshot['meta'][ $key ] = array( 'exists' => metadata_exists( 'post', $product_id, $key ), 'value' => get_post_meta( $product_id, $key, true ) );
		}
		foreach ( self::TAXONOMIES as $taxonomy ) {
			$terms = wp_get_object_terms( $product_id, $taxonomy, array( 'fields' => 'ids' ) );
			$snapshot['terms'][ $taxonomy ] = is_wp_error( $terms ) ? array() : array_map( 'intval', $terms );
		}
		return $snapshot;
	}

	private static function restore_snapshot( int $product_id, array $snapshot ): void {
		foreach ( $snapshot['meta'] as $key => $entry ) {
			if ( $entry['exists'] ) self::direct_set_meta( $product_id, $key, $entry['value'] );
			else self::direct_delete_meta( $product_id, $key );
		}
		foreach ( $snapshot['terms'] as $taxonomy => $term_ids ) {
			wp_set_object_terms( $product_id, array_map( 'intval', $term_ids ), $taxonomy, false );
		}
		self::direct_set_lookup_sku( $product_id, (string) ( $snapshot['meta']['_sku']['value'] ?? '' ) );
	}

	private static function migrate_product( int $product_id, string $run_id, array &$run ): void {
		$plan = GMR_Core_Migration_Preview::plan_product( $product_id );
		WP_CLI::log( "Planning {$product_id}" );
		self::set_artist( $product_id, $plan['artist'], $run );
		WP_CLI::log( "Artist {$product_id}" );
		self::set_collections( $product_id, $plan['collections'], $run );
		WP_CLI::log( "Collections {$product_id}" );
		self::set_discipline( $product_id, (string) $plan['discipline'], $run );
		WP_CLI::log( "Discipline {$product_id}" );
		self::set_technical_terms( $product_id, $plan['technical'], $run );
		WP_CLI::log( "Technical {$product_id}" );
		self::set_dates_and_dimensions( $product_id, $plan );
		WP_CLI::log( "Metadata {$product_id}" );
		self::direct_set_meta( $product_id, 'gmr_technique_notes', $plan['tech_raw'] );
		self::set_default_meta( $product_id, 'gmr_visibility', 'public' );
		self::set_default_meta( $product_id, 'gmr_price_visibility', 'collectors' );
		self::set_default_meta( $product_id, 'gmr_commercial_status', 'available' );
		if ( '' === (string) get_post_meta( $product_id, '_sku', true ) ) {
			$sku = 'GMR-LEGACY-' . $product_id;
			self::direct_set_meta( $product_id, '_sku', $sku );
			self::direct_set_lookup_sku( $product_id, $sku );
		}
		if ( ! has_post_thumbnail( $product_id ) ) self::direct_set_meta( $product_id, '_thumbnail_id', 2753 );
		self::direct_set_meta( $product_id, 'gmr_migration_run', $run_id );
	}

	private static function set_artist( int $product_id, string|array $artist, array &$run ): void {
		if ( is_array( $artist ) && empty( $artist ) ) {
			wp_set_object_terms( $product_id, array(), 'gmr_artist', false );
			return;
		}
		$slug = is_array( $artist ) ? (string) reset( $artist ) : $artist;
		$names = array( 'anonimo' => 'Anonimo', 'elmar-rojas' => 'Elmar Rojas', 'irene-carlos' => 'Irene Carlos' );
		$term_id = self::ensure_term( 'gmr_artist', $slug, $names[ $slug ] ?? self::name_from_slug( $slug ), $run );
		wp_set_object_terms( $product_id, array( $term_id ), 'gmr_artist', false );
		if ( 'elmar-rojas' === $slug ) update_term_meta( $term_id, 'gmr_artist_special_template', 'elmar' );
	}

	private static function set_collections( int $product_id, array $slugs, array &$run ): void {
		$ids = array();
		foreach ( $slugs as $slug ) $ids[] = self::ensure_term( 'gmr_collection', $slug, self::name_from_slug( $slug ), $run );
		wp_set_object_terms( $product_id, $ids, 'gmr_collection', false );
	}

	private static function set_discipline( int $product_id, string $slug, array &$run ): void {
		$old = wp_get_post_terms( $product_id, 'product_cat' );
		$remove = array_merge( array( 'pintura', 'escultura', 'obra-grafica', 'joyeria' ), GMR_Core_Migration_Preview::legacy_artist_slugs(), GMR_Core_Migration_Preview::legacy_collection_slugs() );
		$ids = array();
		foreach ( is_wp_error( $old ) ? array() : $old as $term ) if ( ! in_array( $term->slug, $remove, true ) ) $ids[] = $term->term_id;
		$ids[] = self::ensure_term( 'product_cat', $slug, self::name_from_slug( $slug ), $run );
		wp_set_object_terms( $product_id, array_values( array_unique( $ids ) ), 'product_cat', false );
	}

	private static function set_technical_terms( int $product_id, array $technical, array &$run ): void {
		foreach ( array( 'techniques' => 'gmr_technique', 'supports' => 'gmr_support', 'materials' => 'gmr_material' ) as $group => $taxonomy ) {
			$ids = array();
			foreach ( $technical[ $group ] as $slug ) $ids[] = self::ensure_term( $taxonomy, $slug, self::name_from_slug( $slug ), $run );
			wp_set_object_terms( $product_id, $ids, $taxonomy, false );
		}
	}

	private static function set_dates_and_dimensions( int $product_id, array $plan ): void {
		if ( $plan['year']['parsed'] ) {
			self::direct_set_meta( $product_id, 'gmr_year_start', $plan['year']['start'] );
			if ( $plan['year']['end'] ) self::direct_set_meta( $product_id, 'gmr_year_end', $plan['year']['end'] ); else self::direct_delete_meta( $product_id, 'gmr_year_end' );
			self::direct_delete_meta( $product_id, 'gmr_undated' );
		} else {
			self::direct_delete_meta( $product_id, 'gmr_year_start' ); self::direct_delete_meta( $product_id, 'gmr_year_end' ); self::direct_set_meta( $product_id, 'gmr_undated', 1 );
		}
		foreach ( array( 'height', 'width', 'depth', 'diameter' ) as $field ) {
			$key = 'gmr_' . $field;
			$value = $plan['dimensions'][ $field ] ?? null;
			if ( null !== $value ) self::direct_set_meta( $product_id, $key, $value ); else self::direct_delete_meta( $product_id, $key );
		}
		self::direct_set_meta( $product_id, 'gmr_dimension_unit', 'cm' );
		self::direct_set_meta( $product_id, 'gmr_dimensions_notes', $plan['measure_raw'] );
	}

	private static function ensure_term( string $taxonomy, string $slug, string $name, array &$run ): int {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( $term ) return (int) $term->term_id;
		$created = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
		if ( is_wp_error( $created ) ) throw new RuntimeException( $created->get_error_message() );
		$run['created_terms'][] = array( 'taxonomy' => $taxonomy, 'term_id' => (int) $created['term_id'] );
		return (int) $created['term_id'];
	}

	private static function set_default_meta( int $product_id, string $key, string $value ): void {
		if ( ! metadata_exists( 'post', $product_id, $key ) ) self::direct_set_meta( $product_id, $key, $value );
	}

	private static function direct_set_meta( int $product_id, string $key, mixed $value ): void {
		global $wpdb;
		$serialized = maybe_serialize( $value );
		$meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key=%s ORDER BY meta_id LIMIT 1", $product_id, $key ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		if ( $meta_id ) {
			$wpdb->update( $wpdb->postmeta, array( 'meta_value' => $serialized ), array( 'meta_id' => (int) $meta_id ), array( '%s' ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		} else {
			$wpdb->insert( $wpdb->postmeta, array( 'post_id' => $product_id, 'meta_key' => $key, 'meta_value' => $serialized ), array( '%d', '%s', '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		}
		wp_cache_delete( $product_id, 'post_meta' );
	}

	private static function direct_delete_meta( int $product_id, string $key ): void {
		global $wpdb;
		$wpdb->delete( $wpdb->postmeta, array( 'post_id' => $product_id, 'meta_key' => $key ), array( '%d', '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		wp_cache_delete( $product_id, 'post_meta' );
	}

	private static function direct_set_lookup_sku( int $product_id, string $sku ): void {
		global $wpdb;
		$table = $wpdb->wc_product_meta_lookup;
		$wpdb->update( $table, array( 'sku' => $sku ), array( 'product_id' => $product_id ), array( '%s' ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	private static function name_from_slug( string $slug ): string {
		return mb_convert_case( str_replace( '-', ' ', $slug ), MB_CASE_TITLE, 'UTF-8' );
	}

	private static function remaining_count(): int {
		return count(
			get_posts(
				array(
					'post_type'      => 'product',
					'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array( array( 'key' => 'gmr_migration_run', 'compare' => 'NOT EXISTS' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				)
			)
		);
	}

	private static function option_name( string $run_id ): string {
		return 'gmr_migration_run_' . $run_id;
	}
}
