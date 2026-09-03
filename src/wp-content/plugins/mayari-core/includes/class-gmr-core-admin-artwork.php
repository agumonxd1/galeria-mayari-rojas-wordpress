<?php
/**
 * Artwork editing experience and product list helpers.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Admin_Artwork {

	private const NONCE_ACTION = 'gmr_save_artwork';
	private const NONCE_NAME   = 'gmr_artwork_nonce';

	public static function register_hooks(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'add_meta_boxes_product', array( self::class, 'add_meta_box' ) );
		add_action( 'save_post_product', array( self::class, 'save' ), 20, 2 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( self::class, 'incomplete_notice' ) );
		add_filter( 'manage_edit-product_columns', array( self::class, 'add_columns' ), 30 );
		add_action( 'manage_product_posts_custom_column', array( self::class, 'render_column' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( self::class, 'add_filters' ), 30, 2 );
		add_action( 'pre_get_posts', array( self::class, 'apply_filters' ), 30 );
	}

	public static function add_meta_box(): void {
		remove_meta_box( 'gmr_artistdiv', 'product', 'side' );
		remove_meta_box( 'tagsdiv-gmr_artist', 'product', 'side' );
		remove_meta_box( 'gmr_collectiondiv', 'product', 'side' );
		remove_meta_box( 'tagsdiv-gmr_collection', 'product', 'side' );
		remove_meta_box( 'tagsdiv-gmr_technique', 'product', 'side' );
		remove_meta_box( 'tagsdiv-gmr_support', 'product', 'side' );
		remove_meta_box( 'tagsdiv-gmr_material', 'product', 'side' );

		add_meta_box(
			'gmr-artwork-data',
			__( 'Ficha artistica Mayari', 'mayari-core' ),
			array( self::class, 'render_meta_box' ),
			'product',
			'normal',
			'high'
		);
	}

	public static function render_meta_box( WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		$meta = static fn( string $key, mixed $default = '' ) => get_post_meta( $post->ID, $key, true ) ?: $default;
		?>
		<div class="gmr-artwork-form">
			<p class="gmr-form-intro"><?php esc_html_e( 'Completa la informacion disponible. Los campos marcados con * forman la identificacion minima de la obra.', 'mayari-core' ); ?></p>

			<section class="gmr-form-section" data-section="identity">
				<h3><?php esc_html_e( '1. Identificacion', 'mayari-core' ); ?></h3>
				<div class="gmr-form-grid">
					<?php self::term_select( $post->ID, 'gmr_artist', 'gmr_artist', __( 'Artista *', 'mayari-core' ), false ); ?>
					<?php self::term_select( $post->ID, 'product_cat', 'gmr_discipline', __( 'Disciplina *', 'mayari-core' ), false, self::discipline_slugs() ); ?>
					<?php self::term_select( $post->ID, 'gmr_collection', 'gmr_collection', __( 'Coleccion o serie', 'mayari-core' ), true ); ?>
					<?php self::input( 'gmr_year_start', __( 'Año inicial', 'mayari-core' ), $meta( 'gmr_year_start' ), 'number', array( 'min' => '1000', 'max' => '2200' ) ); ?>
					<?php self::input( 'gmr_year_end', __( 'Año final', 'mayari-core' ), $meta( 'gmr_year_end' ), 'number', array( 'min' => '1000', 'max' => '2200' ) ); ?>
					<?php self::checkbox( 'gmr_undated', __( 'Sin fecha', 'mayari-core' ), (bool) $meta( 'gmr_undated' ) ); ?>
				</div>
			</section>

			<section class="gmr-form-section" data-section="technique">
				<h3><?php esc_html_e( '2. Tecnica y materiales', 'mayari-core' ); ?></h3>
				<div class="gmr-form-grid">
					<?php self::term_select( $post->ID, 'gmr_technique', 'gmr_technique', __( 'Tecnica', 'mayari-core' ), true ); ?>
					<?php self::term_select( $post->ID, 'gmr_support', 'gmr_support', __( 'Soporte', 'mayari-core' ), true ); ?>
					<?php self::term_select( $post->ID, 'gmr_material', 'gmr_material', __( 'Materiales', 'mayari-core' ), true ); ?>
					<?php self::textarea( 'gmr_technique_notes', __( 'Detalle tecnico', 'mayari-core' ), $meta( 'gmr_technique_notes' ) ); ?>
				</div>
			</section>

			<section class="gmr-form-section" data-section="dimensions">
				<h3><?php esc_html_e( '3. Dimensiones', 'mayari-core' ); ?></h3>
				<div class="gmr-form-grid gmr-form-grid--dimensions">
					<?php self::input( 'gmr_height', __( 'Alto', 'mayari-core' ), $meta( 'gmr_height' ), 'number', array( 'min' => '0', 'step' => '0.01' ) ); ?>
					<?php self::input( 'gmr_width', __( 'Ancho', 'mayari-core' ), $meta( 'gmr_width' ), 'number', array( 'min' => '0', 'step' => '0.01' ) ); ?>
					<div data-gmr-disciplines="escultura,joyeria">
						<?php self::input( 'gmr_depth', __( 'Profundidad', 'mayari-core' ), $meta( 'gmr_depth' ), 'number', array( 'min' => '0', 'step' => '0.01' ) ); ?>
					</div>
					<?php self::input( 'gmr_diameter', __( 'Diametro', 'mayari-core' ), $meta( 'gmr_diameter' ), 'number', array( 'min' => '0', 'step' => '0.01' ) ); ?>
					<?php self::select( 'gmr_dimension_unit', __( 'Unidad', 'mayari-core' ), $meta( 'gmr_dimension_unit', 'cm' ), array( 'cm' => 'cm' ) ); ?>
					<div data-gmr-disciplines="escultura,joyeria">
						<?php self::input( 'gmr_weight', __( 'Peso', 'mayari-core' ), $meta( 'gmr_weight' ), 'number', array( 'min' => '0', 'step' => '0.01' ) ); ?>
					</div>
					<div data-gmr-disciplines="escultura,joyeria">
						<?php self::select( 'gmr_weight_unit', __( 'Unidad de peso', 'mayari-core' ), $meta( 'gmr_weight_unit', 'kg' ), array( 'kg' => 'kg', 'g' => 'g' ) ); ?>
					</div>
					<?php self::textarea( 'gmr_dimensions_notes', __( 'Notas de dimensiones', 'mayari-core' ), $meta( 'gmr_dimensions_notes' ) ); ?>
				</div>
			</section>

			<section class="gmr-form-section" data-section="edition" data-gmr-disciplines="obra-grafica,escultura,joyeria">
				<h3><?php esc_html_e( '4. Edicion, firma y autenticidad', 'mayari-core' ); ?></h3>
				<div class="gmr-form-grid">
					<?php self::checkbox( 'gmr_unique_piece', __( 'Pieza unica', 'mayari-core' ), (bool) $meta( 'gmr_unique_piece' ) ); ?>
					<?php self::input( 'gmr_edition_number', __( 'Numero de edicion', 'mayari-core' ), $meta( 'gmr_edition_number' ) ); ?>
					<?php self::input( 'gmr_edition_size', __( 'Tamaño del tiraje', 'mayari-core' ), $meta( 'gmr_edition_size' ), 'number', array( 'min' => '1' ) ); ?>
					<?php self::select( 'gmr_signature_status', __( 'Firma', 'mayari-core' ), $meta( 'gmr_signature_status', 'unknown' ), self::signature_options() ); ?>
					<?php self::input( 'gmr_signature_location', __( 'Ubicacion de firma', 'mayari-core' ), $meta( 'gmr_signature_location' ) ); ?>
					<?php self::select( 'gmr_certificate_status', __( 'Certificado', 'mayari-core' ), $meta( 'gmr_certificate_status', 'unknown' ), self::certificate_options() ); ?>
				</div>
			</section>

			<section class="gmr-form-section" data-section="commercial">
				<h3><?php esc_html_e( '5. Estado y visibilidad', 'mayari-core' ); ?></h3>
				<div class="gmr-form-grid">
					<?php self::select( 'gmr_commercial_status', __( 'Estado comercial *', 'mayari-core' ), $meta( 'gmr_commercial_status', 'available' ), self::commercial_options() ); ?>
					<?php self::select( 'gmr_visibility', __( 'Visibilidad de la obra *', 'mayari-core' ), $meta( 'gmr_visibility', 'public' ), self::visibility_options() ); ?>
					<?php self::select( 'gmr_price_visibility', __( 'Visibilidad del precio *', 'mayari-core' ), $meta( 'gmr_price_visibility', 'collectors' ), self::price_visibility_options() ); ?>
					<?php self::input( 'gmr_price_label', __( 'Texto cuando el precio no es visible', 'mayari-core' ), $meta( 'gmr_price_label', 'Consultar' ) ); ?>
					<?php self::checkbox( 'gmr_price_negotiable', __( 'Precio negociable', 'mayari-core' ), (bool) $meta( 'gmr_price_negotiable' ) ); ?>
					<?php self::checkbox( 'gmr_featured', __( 'Obra destacada', 'mayari-core' ), (bool) $meta( 'gmr_featured' ) ); ?>
				</div>
			</section>

			<section class="gmr-form-section" data-section="internal">
				<h3><?php esc_html_e( '6. Informacion interna', 'mayari-core' ); ?></h3>
				<div class="gmr-form-grid">
					<?php self::input( 'gmr_physical_location', __( 'Ubicacion fisica', 'mayari-core' ), $meta( 'gmr_physical_location' ) ); ?>
					<?php self::input( 'gmr_consignor', __( 'Propietario o consignante', 'mayari-core' ), $meta( 'gmr_consignor' ) ); ?>
					<?php self::textarea( 'gmr_internal_notes', __( 'Observaciones internas', 'mayari-core' ), $meta( 'gmr_internal_notes' ) ); ?>
				</div>
			</section>
		</div>
		<?php
	}

	public static function save( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || 'product' !== $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'gmr_manage_artworks' ) && ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		self::save_terms( $post_id );
		self::save_fields( $post_id );
	}

	private static function save_terms( int $post_id ): void {
		$map = array(
			'gmr_artist'     => array( 'taxonomy' => 'gmr_artist', 'multiple' => false ),
			'gmr_discipline' => array( 'taxonomy' => 'product_cat', 'multiple' => false ),
			'gmr_collection' => array( 'taxonomy' => 'gmr_collection', 'multiple' => true ),
			'gmr_technique'  => array( 'taxonomy' => 'gmr_technique', 'multiple' => true ),
			'gmr_support'    => array( 'taxonomy' => 'gmr_support', 'multiple' => true ),
			'gmr_material'   => array( 'taxonomy' => 'gmr_material', 'multiple' => true ),
		);

		foreach ( $map as $field => $settings ) {
			$raw = $_POST[ $field ] ?? array();
			$ids = array_values( array_filter( array_map( 'absint', (array) wp_unslash( $raw ) ) ) );
			if ( ! $settings['multiple'] ) {
				$ids = array_slice( $ids, 0, 1 );
			}
			if ( 'product_cat' === $settings['taxonomy'] ) {
				$current = wp_get_object_terms( $post_id, 'product_cat' );
				foreach ( is_wp_error( $current ) ? array() : $current as $term ) {
					if ( ! in_array( $term->slug, self::discipline_slugs(), true ) ) {
						$ids[] = $term->term_id;
					}
				}
				$ids = array_values( array_unique( $ids ) );
			}
			wp_set_object_terms( $post_id, $ids, $settings['taxonomy'], false );
		}
	}

	private static function save_fields( int $post_id ): void {
		$text_fields = array( 'gmr_technique_notes', 'gmr_dimensions_notes', 'gmr_edition_number', 'gmr_signature_location', 'gmr_price_label', 'gmr_physical_location', 'gmr_consignor', 'gmr_internal_notes' );
		$number_fields = array( 'gmr_year_start', 'gmr_year_end', 'gmr_height', 'gmr_width', 'gmr_depth', 'gmr_diameter', 'gmr_weight', 'gmr_edition_size' );
		$boolean_fields = array( 'gmr_undated', 'gmr_unique_piece', 'gmr_price_negotiable', 'gmr_featured' );
		$enum_fields = array(
			'gmr_dimension_unit'     => array_keys( array( 'cm' => 'cm' ) ),
			'gmr_weight_unit'        => array_keys( array( 'kg' => 'kg', 'g' => 'g' ) ),
			'gmr_signature_status'   => array_keys( self::signature_options() ),
			'gmr_certificate_status' => array_keys( self::certificate_options() ),
			'gmr_commercial_status'  => array_keys( self::commercial_options() ),
			'gmr_visibility'         => array_keys( self::visibility_options() ),
			'gmr_price_visibility'   => array_keys( self::price_visibility_options() ),
		);

		foreach ( $text_fields as $key ) {
			self::update_or_delete( $post_id, $key, isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : '' );
		}

		foreach ( $number_fields as $key ) {
			$raw = isset( $_POST[ $key ] ) ? wc_format_decimal( wp_unslash( $_POST[ $key ] ) ) : '';
			self::update_or_delete( $post_id, $key, $raw );
		}

		foreach ( $boolean_fields as $key ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
		}

		foreach ( $enum_fields as $key => $allowed ) {
			$value = isset( $_POST[ $key ] ) ? sanitize_key( wp_unslash( $_POST[ $key ] ) ) : '';
			if ( in_array( $value, $allowed, true ) ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	private static function update_or_delete( int $post_id, string $key, string $value ): void {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}

	public static function enqueue_assets( string $hook ): void {
		$screen = get_current_screen();
		if ( ! $screen || 'product' !== $screen->post_type || ! in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
			return;
		}

		wp_enqueue_style( 'gmr-admin-artwork', plugins_url( 'assets/admin-artwork.css', GMR_CORE_FILE ), array(), GMR_CORE_VERSION );
		wp_enqueue_script( 'gmr-admin-artwork', plugins_url( 'assets/admin-artwork.js', GMR_CORE_FILE ), array(), GMR_CORE_VERSION, true );
	}

	public static function incomplete_notice(): void {
		$screen = get_current_screen();
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( ! $screen || 'product' !== $screen->post_type || ! $post_id ) {
			return;
		}

		$missing = array();
		if ( ! get_post_meta( $post_id, '_sku', true ) ) {
			$missing[] = 'SKU';
		}
		if ( ! has_term( '', 'gmr_artist', $post_id ) ) {
			$missing[] = 'Artista';
		}
		if ( ! self::has_discipline( $post_id ) ) {
			$missing[] = 'Disciplina';
		}

		if ( $missing ) {
			printf(
				'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
				esc_html__( 'Ficha artistica incompleta.', 'mayari-core' ),
				esc_html( sprintf( 'Falta completar: %s.', implode( ', ', $missing ) ) )
			);
		}
	}

	public static function add_columns( array $columns ): array {
		$custom = array(
			'gmr_artist'     => __( 'Artista', 'mayari-core' ),
			'gmr_discipline' => __( 'Disciplina', 'mayari-core' ),
			'gmr_status'     => __( 'Estado', 'mayari-core' ),
			'gmr_visibility' => __( 'Visibilidad', 'mayari-core' ),
		);
		$ordered = array();

		foreach ( array( 'cb', 'thumb', 'name', 'sku' ) as $key ) {
			if ( isset( $columns[ $key ] ) ) {
				$ordered[ $key ] = $columns[ $key ];
			}
		}

		$ordered = array_merge( $ordered, $custom );

		foreach ( array( 'is_in_stock', 'price', 'date' ) as $key ) {
			if ( isset( $columns[ $key ] ) ) {
				$ordered[ $key ] = $columns[ $key ];
			}
		}

		return $ordered;
	}

	public static function render_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'gmr_artist':
				echo esc_html( self::term_names( $post_id, 'gmr_artist' ) );
				break;
			case 'gmr_discipline':
				echo esc_html( self::term_names( $post_id, 'product_cat', self::discipline_slugs() ) );
				break;
			case 'gmr_status':
				$value = get_post_meta( $post_id, 'gmr_commercial_status', true ) ?: 'available';
				printf( '<span class="gmr-admin-badge gmr-admin-badge--%s">%s</span>', esc_attr( $value ), esc_html( self::commercial_options()[ $value ] ?? $value ) );
				break;
			case 'gmr_visibility':
				$value = get_post_meta( $post_id, 'gmr_visibility', true ) ?: 'public';
				printf( '<span class="gmr-admin-badge gmr-admin-badge--%s">%s</span>', esc_attr( $value ), esc_html( self::visibility_options()[ $value ] ?? $value ) );
				break;
		}
	}

	public static function add_filters( string $post_type, string $position ): void {
		if ( 'product' !== $post_type || 'top' !== $position ) {
			return;
		}

		self::taxonomy_filter( 'gmr_artist', 'gmr_filter_artist', __( 'Todos los artistas', 'mayari-core' ) );
		self::select_filter( 'gmr_filter_status', __( 'Todos los estados', 'mayari-core' ), self::commercial_options() );
		self::select_filter( 'gmr_filter_visibility', __( 'Toda visibilidad', 'mayari-core' ), self::visibility_options() );
	}

	public static function apply_filters( WP_Query $query ): void {
		global $pagenow;
		if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() || 'product' !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( isset( $_GET['gmr_filter_artist'] ) && absint( $_GET['gmr_filter_artist'] ) ) {
			$tax_query   = (array) $query->get( 'tax_query', array() );
			$tax_query[] = array(
				'taxonomy' => 'gmr_artist',
				'field'    => 'term_id',
				'terms'    => absint( $_GET['gmr_filter_artist'] ),
			);
			$query->set( 'tax_query', $tax_query );
		}

		$meta_query = (array) $query->get( 'meta_query', array() );
		$filters = array(
			'gmr_filter_status'     => 'gmr_commercial_status',
			'gmr_filter_visibility' => 'gmr_visibility',
		);
		foreach ( $filters as $request_key => $meta_key ) {
			if ( ! empty( $_GET[ $request_key ] ) ) {
				$meta_query[] = array(
					'key'   => $meta_key,
					'value' => sanitize_key( wp_unslash( $_GET[ $request_key ] ) ),
				);
			}
		}
		$query->set( 'meta_query', $meta_query );
	}

	private static function term_select( int $post_id, string $taxonomy, string $name, string $label, bool $multiple, array $allowed_slugs = array() ): void {
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		$selected = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
		?>
		<label class="gmr-field">
			<span><?php echo esc_html( $label ); ?></span>
			<select name="<?php echo esc_attr( $name ); ?><?php echo $multiple ? '[]' : ''; ?>" <?php echo $multiple ? 'multiple size="4"' : ''; ?> data-gmr-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
				<?php if ( ! $multiple ) : ?><option value=""><?php esc_html_e( 'Seleccionar', 'mayari-core' ); ?></option><?php endif; ?>
				<?php foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) : ?>
					<?php if ( $allowed_slugs && ! in_array( $term->slug, $allowed_slugs, true ) ) { continue; } ?>
					<option value="<?php echo esc_attr( (string) $term->term_id ); ?>" data-slug="<?php echo esc_attr( $term->slug ); ?>" <?php selected( in_array( $term->term_id, $selected, true ) ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php endforeach; ?>
			</select>
			<small><?php esc_html_e( 'Puedes crear y normalizar opciones desde el menu de Productos.', 'mayari-core' ); ?></small>
		</label>
		<?php
	}

	private static function input( string $name, string $label, mixed $value, string $type = 'text', array $attributes = array() ): void {
		$attribute_html = '';
		foreach ( $attributes as $key => $attribute_value ) {
			$attribute_html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $attribute_value ) );
		}
		printf(
			'<label class="gmr-field"><span>%s</span><input type="%s" name="%s" value="%s"%s></label>',
			esc_html( $label ),
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( (string) $value ),
			$attribute_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	private static function textarea( string $name, string $label, mixed $value ): void {
		printf( '<label class="gmr-field gmr-field--wide"><span>%s</span><textarea name="%s" rows="3">%s</textarea></label>', esc_html( $label ), esc_attr( $name ), esc_textarea( (string) $value ) );
	}

	private static function checkbox( string $name, string $label, bool $checked ): void {
		printf( '<label class="gmr-field gmr-field--check"><input type="checkbox" name="%s" value="1" %s><span>%s</span></label>', esc_attr( $name ), checked( $checked, true, false ), esc_html( $label ) );
	}

	private static function select( string $name, string $label, string $value, array $options ): void {
		echo '<label class="gmr-field"><span>' . esc_html( $label ) . '</span><select name="' . esc_attr( $name ) . '">';
		foreach ( $options as $option_value => $option_label ) {
			echo '<option value="' . esc_attr( $option_value ) . '" ' . selected( $value, $option_value, false ) . '>' . esc_html( $option_label ) . '</option>';
		}
		echo '</select></label>';
	}

	private static function taxonomy_filter( string $taxonomy, string $name, string $label ): void {
		wp_dropdown_categories(
			array(
				'taxonomy'        => $taxonomy,
				'name'            => $name,
				'show_option_all' => $label,
				'hide_empty'      => false,
				'hierarchical'    => false,
				'value_field'     => 'term_id',
				'selected'        => isset( $_GET[ $name ] ) ? absint( $_GET[ $name ] ) : 0,
			)
		);
	}

	private static function select_filter( string $name, string $label, array $options ): void {
		$current = isset( $_GET[ $name ] ) ? sanitize_key( wp_unslash( $_GET[ $name ] ) ) : '';
		echo '<select name="' . esc_attr( $name ) . '"><option value="">' . esc_html( $label ) . '</option>';
		foreach ( $options as $value => $option_label ) {
			echo '<option value="' . esc_attr( $value ) . '" ' . selected( $current, $value, false ) . '>' . esc_html( $option_label ) . '</option>';
		}
		echo '</select>';
	}

	private static function term_names( int $post_id, string $taxonomy, array $allowed_slugs = array() ): string {
		$terms = wp_get_object_terms( $post_id, $taxonomy );
		if ( is_wp_error( $terms ) ) {
			return '—';
		}
		$names = array();
		foreach ( $terms as $term ) {
			if ( ! $allowed_slugs || in_array( $term->slug, $allowed_slugs, true ) ) {
				$names[] = $term->name;
			}
		}
		return $names ? implode( ', ', $names ) : '—';
	}

	private static function has_discipline( int $post_id ): bool {
		$terms = wp_get_object_terms( $post_id, 'product_cat', array( 'fields' => 'slugs' ) );
		return ! is_wp_error( $terms ) && (bool) array_intersect( $terms, self::discipline_slugs() );
	}

	private static function discipline_slugs(): array {
		return array( 'pintura', 'escultura', 'obra-grafica', 'joyeria' );
	}

	private static function signature_options(): array {
		return array( 'unknown' => 'Desconocida', 'signed' => 'Firmada', 'unsigned' => 'No firmada', 'attributed' => 'Atribuida' );
	}

	private static function certificate_options(): array {
		return array( 'unknown' => 'Desconocido', 'included' => 'Incluido', 'available' => 'Disponible', 'not_available' => 'No disponible' );
	}

	private static function commercial_options(): array {
		return array( 'available' => 'Disponible', 'reserved' => 'Reservada', 'sold' => 'Vendida', 'not_available' => 'No disponible', 'on_exhibition' => 'En exposicion', 'archive' => 'Archivo historico' );
	}

	private static function visibility_options(): array {
		return array( 'public' => 'Publica', 'collectors' => 'Solo Coleccionistas', 'hidden' => 'Oculta' );
	}

	private static function price_visibility_options(): array {
		return array( 'collectors' => 'Coleccionistas y administracion', 'admins' => 'Solo administracion', 'public' => 'Publico' );
	}
}
