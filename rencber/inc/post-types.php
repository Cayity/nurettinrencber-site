<?php
/**
 * Özel içerik türleri: Konserler (etkinlik) ve Albümler.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

/**
 * İçerik türlerini kaydet.
 */
function rencber_register_post_types() {
	register_post_type( 'etkinlik', array(
		'labels'        => array(
			'name'               => __( 'Konserler', 'rencber' ),
			'singular_name'      => __( 'Konser', 'rencber' ),
			'add_new'            => __( 'Yeni konser ekle', 'rencber' ),
			'add_new_item'       => __( 'Yeni konser ekle', 'rencber' ),
			'edit_item'          => __( 'Konseri düzenle', 'rencber' ),
			'all_items'          => __( 'Tüm konserler', 'rencber' ),
			'search_items'       => __( 'Konser ara', 'rencber' ),
			'not_found'          => __( 'Konser bulunamadı', 'rencber' ),
			'not_found_in_trash' => __( 'Çöpte konser yok', 'rencber' ),
		),
		'public'        => true,
		'has_archive'   => 'konserler',
		'rewrite'       => array( 'slug' => 'konser', 'with_front' => false ),
		'menu_icon'     => 'dashicons-tickets-alt',
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'album', array(
		'labels'        => array(
			'name'          => __( 'Albümler', 'rencber' ),
			'singular_name' => __( 'Albüm', 'rencber' ),
			'add_new'       => __( 'Yeni albüm ekle', 'rencber' ),
			'add_new_item'  => __( 'Yeni albüm ekle', 'rencber' ),
			'edit_item'     => __( 'Albümü düzenle', 'rencber' ),
			'all_items'     => __( 'Tüm albümler', 'rencber' ),
			'not_found'     => __( 'Albüm bulunamadı', 'rencber' ),
		),
		'public'        => true,
		'has_archive'   => 'albumler',
		'rewrite'       => array( 'slug' => 'album', 'with_front' => false ),
		'menu_icon'     => 'dashicons-album',
		'menu_position' => 6,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest'  => true,
	) );
}
add_action( 'init', 'rencber_register_post_types' );

/**
 * Tema etkinleştirildiğinde kalıcı bağlantıları yenile.
 */
function rencber_flush_rewrites() {
	rencber_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'rencber_flush_rewrites' );

/**
 * Alan tanımları (tek kaynak: kaydetme ve formu bunlar yönetir).
 */
function rencber_event_fields() {
	return array(
		'date'       => array( 'label' => __( 'Tarih', 'rencber' ), 'type' => 'date', 'required' => true ),
		'time'       => array( 'label' => __( 'Saat', 'rencber' ), 'type' => 'time', 'placeholder' => '20:30' ),
		'end_time'   => array( 'label' => __( 'Bitiş saati (isteğe bağlı)', 'rencber' ), 'type' => 'time' ),
		'city'       => array( 'label' => __( 'Şehir / ilçe', 'rencber' ), 'type' => 'text', 'placeholder' => 'Gebze', 'required' => true ),
		'venue'      => array( 'label' => __( 'Mekân', 'rencber' ), 'type' => 'text', 'placeholder' => 'Osman Hamdi Bey Kültür Merkezi', 'required' => true ),
		'address'    => array( 'label' => __( 'Açık adres', 'rencber' ), 'type' => 'text', 'placeholder' => 'Mahalle, cadde, no, ilçe/il' ),
		'country'    => array( 'label' => __( 'Ülke kodu', 'rencber' ), 'type' => 'text', 'placeholder' => 'TR' ),
		'ticket_url' => array( 'label' => __( 'Bilet bağlantısı', 'rencber' ), 'type' => 'url', 'placeholder' => 'https://...' ),
		'vendor'     => array( 'label' => __( 'Bilet satıcısı', 'rencber' ), 'type' => 'text', 'placeholder' => 'Biletix, Bubilet, Passo...' ),
		'price'      => array( 'label' => __( 'Başlangıç fiyatı', 'rencber' ), 'type' => 'text', 'placeholder' => '1159' ),
		'currency'   => array( 'label' => __( 'Para birimi', 'rencber' ), 'type' => 'select', 'options' => array( 'TRY' => 'TRY (₺)', 'EUR' => 'EUR (€)', 'USD' => 'USD ($)', 'GBP' => 'GBP (£)' ) ),
		'status'     => array( 'label' => __( 'Durum', 'rencber' ), 'type' => 'select', 'options' => rencber_event_statuses() ),
		'soldout'    => array( 'label' => __( 'Biletler tükendi', 'rencber' ), 'type' => 'checkbox' ),
	);
}

function rencber_album_fields() {
	return array(
		'type'      => array( 'label' => __( 'Tür', 'rencber' ), 'type' => 'select', 'options' => rencber_release_types() ),
		'year'      => array( 'label' => __( 'Çıkış yılı', 'rencber' ), 'type' => 'number', 'required' => true ),
		'credit'    => array( 'label' => __( 'Birlikte söyleyen (isteğe bağlı)', 'rencber' ), 'type' => 'text', 'placeholder' => 'Ceren Tügen Akdeniz' ),
		'label'     => array( 'label' => __( 'Yapım şirketi', 'rencber' ), 'type' => 'text' ),
		'spotify'   => array( 'label' => __( 'Spotify bağlantısı', 'rencber' ), 'type' => 'url' ),
		'apple'     => array( 'label' => __( 'Apple Music bağlantısı', 'rencber' ), 'type' => 'url' ),
		'youtube'   => array( 'label' => __( 'YouTube bağlantısı', 'rencber' ), 'type' => 'url' ),
		'tracks'    => array( 'label' => __( 'Şarkı listesi (her satıra bir şarkı)', 'rencber' ), 'type' => 'textarea' ),
		'cover_url' => array( 'label' => __( 'Kapak görseli URL (öne çıkan görsel yoksa)', 'rencber' ), 'type' => 'url' ),
	);
}

/**
 * Meta kutuları.
 */
function rencber_add_meta_boxes() {
	add_meta_box( 'rencber_event', __( 'Konser bilgileri', 'rencber' ), 'rencber_render_event_box', 'etkinlik', 'normal', 'high' );
	add_meta_box( 'rencber_album', __( 'Albüm bilgileri', 'rencber' ), 'rencber_render_album_box', 'album', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'rencber_add_meta_boxes' );

function rencber_render_fields( $post, $fields, $prefix ) {
	wp_nonce_field( 'rencber_meta', 'rencber_meta_nonce' );
	echo '<div class="rencber-fields">';
	foreach ( $fields as $key => $f ) {
		$name  = $prefix . $key;
		$value = get_post_meta( $post->ID, $name, true );
		$req   = ! empty( $f['required'] ) ? ' <span style="color:#b32d2e">*</span>' : '';
		$ph    = isset( $f['placeholder'] ) ? $f['placeholder'] : '';
		$wide  = in_array( $f['type'], array( 'textarea', 'url' ), true ) || 'address' === $key ? ' rf-wide' : '';

		echo '<p class="rf' . esc_attr( $wide ) . '"><label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $f['label'] ) . '</strong>' . $req . '</label><br>'; // phpcs:ignore WordPress.Security.EscapeOutput

		switch ( $f['type'] ) {
			case 'textarea':
				printf( '<textarea class="widefat" rows="8" id="%1$s" name="%1$s">%2$s</textarea>', esc_attr( $name ), esc_textarea( $value ) );
				break;
			case 'select':
				printf( '<select id="%1$s" name="%1$s">', esc_attr( $name ) );
				foreach ( $f['options'] as $ov => $ol ) {
					printf( '<option value="%s"%s>%s</option>', esc_attr( $ov ), selected( $value, $ov, false ), esc_html( $ol ) );
				}
				echo '</select>';
				break;
			case 'checkbox':
				printf( '<label><input type="checkbox" id="%1$s" name="%1$s" value="1"%2$s> %3$s</label>', esc_attr( $name ), checked( $value, '1', false ), esc_html__( 'Evet', 'rencber' ) );
				break;
			default:
				printf(
					'<input class="widefat" type="%1$s" id="%2$s" name="%2$s" value="%3$s" placeholder="%4$s">',
					esc_attr( $f['type'] ),
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $ph )
				);
		}
		echo '</p>';
	}
	echo '</div>';
	echo '<style>.rencber-fields{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:4px 20px}.rencber-fields .rf-wide{grid-column:1/-1}.rencber-fields select{min-width:200px}</style>';
}

function rencber_render_event_box( $post ) {
	echo '<p class="description">' . esc_html__( 'Bu bilgiler sitede ve Google arama sonuçlarında (Etkinlikler) kullanılır. Tarih, şehir ve mekân zorunludur. Geçmiş tarihli konserler sitede otomatik gizlenir.', 'rencber' ) . '</p>';
	rencber_render_fields( $post, rencber_event_fields(), '_ev_' );
}

function rencber_render_album_box( $post ) {
	echo '<p class="description">' . esc_html__( 'Kapak için sağdaki “Öne çıkan görsel” alanını kullanın (kare, en az 600×600 önerilir).', 'rencber' ) . '</p>';
	rencber_render_fields( $post, rencber_album_fields(), '_al_' );
}

/**
 * Kaydetme.
 */
function rencber_save_meta( $post_id, $post ) {
	if ( ! isset( $_POST['rencber_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['rencber_meta_nonce'] ), 'rencber_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'etkinlik' === $post->post_type ) {
		$fields = rencber_event_fields();
		$prefix = '_ev_';
	} elseif ( 'album' === $post->post_type ) {
		$fields = rencber_album_fields();
		$prefix = '_al_';
	} else {
		return;
	}

	foreach ( $fields as $key => $f ) {
		$name = $prefix . $key;
		$raw  = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		switch ( $f['type'] ) {
			case 'url':
				$val = esc_url_raw( $raw );
				break;
			case 'textarea':
				$val = sanitize_textarea_field( $raw );
				break;
			case 'checkbox':
				$val = $raw ? '1' : '';
				break;
			case 'number':
				$val = '' === $raw ? '' : (string) absint( $raw );
				break;
			case 'select':
				$val = array_key_exists( $raw, $f['options'] ) ? $raw : '';
				break;
			default:
				$val = sanitize_text_field( $raw );
		}
		if ( '' === $val ) {
			delete_post_meta( $post_id, $name );
		} else {
			update_post_meta( $post_id, $name, $val );
		}
	}
}
add_action( 'save_post', 'rencber_save_meta', 10, 2 );

/**
 * Başlık boş bırakılırsa konser başlığını otomatik üret: "Nurettin Rençber Gebze Konseri".
 */
function rencber_auto_event_title( $data, $postarr ) {
	if ( 'etkinlik' !== $data['post_type'] || '' !== trim( $data['post_title'] ) ) {
		return $data;
	}
	$city = isset( $_POST['_ev_city'] ) ? sanitize_text_field( wp_unslash( $_POST['_ev_city'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( $city ) {
		/* translators: 1: sanatçı adı, 2: şehir */
		$data['post_title'] = sprintf( __( '%1$s %2$s Konseri', 'rencber' ), rencber_mod( 'artist_name' ), $city );
		$data['post_name']  = '';
	}
	return $data;
}
add_filter( 'wp_insert_post_data', 'rencber_auto_event_title', 10, 2 );

/**
 * Yönetim listesinde sütunlar.
 */
function rencber_event_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		if ( 'date' === $k ) {
			$new['ev_date']  = __( 'Konser tarihi', 'rencber' );
			$new['ev_place'] = __( 'Yer', 'rencber' );
			$new['ev_price'] = __( 'Fiyat', 'rencber' );
			continue;
		}
		$new[ $k ] = $v;
	}
	return $new;
}
add_filter( 'manage_etkinlik_posts_columns', 'rencber_event_columns' );

function rencber_event_column_content( $col, $post_id ) {
	$e = rencber_event( $post_id );
	if ( 'ev_date' === $col && $e['ts'] ) {
		$past = $e['date'] < wp_date( 'Y-m-d' );
		$p    = rencber_date_parts( $e['ts'] );
		echo '<span style="' . ( $past ? 'opacity:.5' : '' ) . '">' . esc_html( $p['long'] . ( $e['time'] ? ' · ' . $e['time'] : '' ) ) . '</span>';
		if ( $past ) {
			echo '<br><em>' . esc_html__( 'geçmiş', 'rencber' ) . '</em>';
		}
		if ( 'EventScheduled' !== $e['status'] ) {
			$s = rencber_event_statuses();
			echo '<br><strong style="color:#b32d2e">' . esc_html( $s[ $e['status'] ] ) . '</strong>';
		}
	}
	if ( 'ev_place' === $col ) {
		echo esc_html( trim( $e['city'] . ' · ' . $e['venue'], ' ·' ) );
	}
	if ( 'ev_price' === $col ) {
		echo esc_html( $e['soldout'] ? __( 'Tükendi', 'rencber' ) : rencber_price( $e['price'], $e['currency'] ) );
	}
}
add_action( 'manage_etkinlik_posts_custom_column', 'rencber_event_column_content', 10, 2 );

function rencber_event_sortable( $cols ) {
	$cols['ev_date'] = 'ev_date';
	return $cols;
}
add_filter( 'manage_edit-etkinlik_sortable_columns', 'rencber_event_sortable' );

/**
 * Yönetimde konserler varsayılan olarak konser tarihine göre (yeniden eskiye) sıralı.
 */
function rencber_admin_event_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'etkinlik' !== $query->get( 'post_type' ) ) {
		return;
	}
	$orderby = $query->get( 'orderby' );
	if ( ! $orderby || 'ev_date' === $orderby ) {
		$query->set( 'meta_key', '_ev_date' );
		$query->set( 'orderby', 'meta_value' );
		if ( ! $query->get( 'order' ) || ! isset( $_GET['order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$query->set( 'order', 'DESC' );
		}
	}
}
add_action( 'pre_get_posts', 'rencber_admin_event_order' );

function rencber_album_columns( $cols ) {
	$cols = array_slice( $cols, 0, 1, true ) + array( 'al_cover' => '' ) + array_slice( $cols, 1, null, true );
	$cols['al_year'] = __( 'Yıl · tür', 'rencber' );
	unset( $cols['date'] );
	return $cols;
}
add_filter( 'manage_album_posts_columns', 'rencber_album_columns' );

function rencber_album_column_content( $col, $post_id ) {
	if ( 'al_year' === $col ) {
		$types = rencber_release_types();
		$type  = get_post_meta( $post_id, '_al_type', true );
		echo esc_html( get_post_meta( $post_id, '_al_year', true ) . ' · ' . ( isset( $types[ $type ] ) ? $types[ $type ] : $types['album'] ) );
	}
	if ( 'al_cover' === $col ) {
		$src = rencber_album_cover( $post_id, 'thumbnail' );
		if ( $src ) {
			echo '<img src="' . esc_url( $src ) . '" width="48" height="48" style="object-fit:cover;border-radius:3px" alt="">';
		}
	}
}
add_action( 'manage_album_posts_custom_column', 'rencber_album_column_content', 10, 2 );
