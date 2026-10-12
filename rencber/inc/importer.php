<?php
/**
 * İlk kurulum: eski siteden albümleri, galeriyi, fotoğrafları ve bilinen konserleri aktarır.
 * Görseller zaten ortam kütüphanesinde olduğu için yeniden indirilmez, mevcut kayıtlar bağlanır.
 *
 * Görünüm › Rençber kurulum sayfasından tekrar çalıştırılabilir; var olan kayıtları çoğaltmaz.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Eski sitedeki içerik.
 */
function rencber_seed_data() {
	$up = content_url( '/uploads/' );
	return array(
		'gallery' => array(
			'2026/01/r01.jpg', '2026/01/r05.jpeg', '2026/01/r06.jpeg',
			'2025/01/0001.jpeg', '2025/01/0002.jpg', '2025/01/0003.jpg', '2025/01/0004.jpg',
			'2025/01/0005.jpg', '2025/01/0006.jpg', '2025/01/0007.jpg', '2025/01/0008.jpg',
			'2025/01/0009.jpg', '2025/01/0010.jpg', '2025/01/0011.jpg', '2025/01/0012.jpg',
			'2025/01/n0022.jpeg', '2025/01/n0021.jpeg', '2025/01/n0020.jpeg',
		),
		'about'   => '2025/01/hakkimda.jpeg',
		'poster'  => '2025/01/s001.jpg',
		'share'   => '2025/01/0008.jpg',
		'events'  => array(
			// Google arama sonucundaki "Etkinlikler" listesinden (Ekim 2026). Lütfen kontrol edin.
			array( '2026-10-16', '20:00', 'Mardin', 'Cemal Yay Kongre ve Konferans Salonu', '1226', 'Bubilet' ),
			array( '2026-12-11', '20:30', 'Gebze', 'Kocaeli Osman Hamdi Bey Kültür Merkezi', '1159', '' ),
			array( '2026-12-11', '20:30', 'Darıca', 'Adnan Menderes Kültür Merkezi', '', '' ),
			array( '2026-12-21', '20:30', 'İstanbul', 'Mall of İstanbul', '959', '' ),
		),
		'base'    => $up,
	);
}

/**
 * Yükleme yolundan ek ID'si (bulunamazsa 0).
 */
function rencber_find_attachment( $relative ) {
	$id = attachment_url_to_postid( content_url( '/uploads/' . $relative ) );
	return $id ? (int) $id : 0;
}

/**
 * Aktarımı çalıştır. Döndürür: yapılanların özeti.
 */
function rencber_run_import() {
	$data = rencber_seed_data();
	$log  = array();

	// Diskografi: inc/yayinlar.json (Apple Music kataloğundan derlendi + eski site kapakları).
	$n    = 0;
	$disc = json_decode( (string) file_get_contents( RENCBER_DIR . '/inc/yayinlar.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	foreach ( (array) $disc as $a ) {
		$exists = get_posts( array(
			'post_type'   => 'album',
			'title'       => $a['title'],
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
		) );
		if ( $exists ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'     => 'album',
			'post_title'    => $a['title'],
			'post_status'   => 'publish',
			'post_date'     => $a['date'] . ' 12:00:00',
			'post_date_gmt' => $a['date'] . ' 09:00:00',
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		$meta = array(
			'_al_type'   => $a['type'],
			'_al_year'   => (string) $a['year'],
			'_al_credit' => $a['credit'],
			'_al_label'  => $a['label'],
			'_al_apple'  => $a['apple'],
			'_al_tracks' => implode( "\n", $a['tracks'] ),
		);
		foreach ( array_filter( $meta ) as $k => $v ) {
			update_post_meta( $id, $k, $v );
		}
		$att = $a['old_cover'] ? rencber_find_attachment( $a['old_cover'] ) : 0;
		if ( $att ) {
			set_post_thumbnail( $id, $att );
		} elseif ( $a['artwork'] ) {
			// Apple'ın kapak sunucusu (600×600). İstenirse sonra öne çıkan görsel ile değiştirilebilir.
			update_post_meta( $id, '_al_cover_url', str_replace( '1000x1000bb', '600x600bb', $a['artwork'] ) );
		} elseif ( $a['old_cover'] ) {
			update_post_meta( $id, '_al_cover_url', $data['base'] . $a['old_cover'] );
		}
		++$n;
	}
	$log[] = sprintf( '%d albüm/single eklendi.', $n );

	// Konserler.
	$n = 0;
	foreach ( $data['events'] as $ev ) {
		list( $date, $time, $city, $venue, $price, $vendor ) = $ev;
		$exists = get_posts( array(
			'post_type'   => 'etkinlik',
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_query'  => array(
				array( 'key' => '_ev_date', 'value' => $date ),
				array( 'key' => '_ev_city', 'value' => $city ),
			),
		) );
		if ( $exists ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'   => 'etkinlik',
			'post_title'  => sprintf( '%s %s Konseri', rencber_mod( 'artist_name' ), $city ),
			'post_status' => 'publish',
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		$meta = array(
			'_ev_date'     => $date,
			'_ev_time'     => $time,
			'_ev_city'     => $city,
			'_ev_venue'    => $venue,
			'_ev_country'  => 'TR',
			'_ev_currency' => 'TRY',
			'_ev_status'   => 'EventScheduled',
			'_ev_price'    => $price,
			'_ev_vendor'   => $vendor,
		);
		foreach ( array_filter( $meta ) as $k => $v ) {
			update_post_meta( $id, $k, $v );
		}
		++$n;
	}
	$log[] = sprintf( '%d konser eklendi.', $n );

	// Galeri ve fotoğraflar (yalnızca boşsa doldur).
	if ( ! get_theme_mod( 'gallery_ids' ) ) {
		$ids = array_filter( array_map( 'rencber_find_attachment', $data['gallery'] ) );
		if ( $ids ) {
			set_theme_mod( 'gallery_ids', implode( ',', $ids ) );
		}
		$log[] = sprintf( 'Galeriye %d fotoğraf bağlandı.', count( $ids ) );
	}
	foreach ( array( 'about_image' => 'about', 'hero_poster' => 'poster', 'seo_image' => 'share' ) as $mod => $key ) {
		if ( ! get_theme_mod( $mod ) ) {
			$att = rencber_find_attachment( $data[ $key ] );
			if ( $att ) {
				set_theme_mod( $mod, $att );
			}
		}
	}

	// İletişim formu: Contact Form 7 kuruluysa ilk formu bağla.
	if ( ! get_theme_mod( 'contact_form' ) && post_type_exists( 'wpcf7_contact_form' ) ) {
		$form = get_posts( array( 'post_type' => 'wpcf7_contact_form', 'numberposts' => 1 ) );
		if ( $form ) {
			set_theme_mod( 'contact_form', sprintf( '[contact-form-7 id="%d"]', $form[0]->ID ) );
			$log[] = 'İletişim formu bağlandı.';
		}
	}

	update_option( 'rencber_imported', time() );
	return $log;
}

/**
 * İlk etkinleştirmede bir kez otomatik çalıştır.
 */
function rencber_import_on_activation() {
	if ( ! get_option( 'rencber_imported' ) ) {
		rencber_run_import();
	}
}
add_action( 'after_switch_theme', 'rencber_import_on_activation', 20 );

/**
 * Görünüm › Rençber kurulum.
 */
function rencber_import_menu() {
	add_theme_page( __( 'Rençber kurulum', 'rencber' ), __( 'Rençber kurulum', 'rencber' ), 'edit_theme_options', 'rencber-setup', 'rencber_import_page' );
}
add_action( 'admin_menu', 'rencber_import_menu' );

function rencber_import_page() {
	$log = array();
	if ( isset( $_POST['rencber_import'] ) && check_admin_referer( 'rencber_import' ) ) {
		$log = rencber_run_import();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Rençber tema kurulumu', 'rencber' ); ?></h1>
		<?php if ( $log ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( implode( ' ', $log ) ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Sitenin tüm ayarları tek yerde:', 'rencber' ); ?>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=rencber' ) ); ?>"><?php esc_html_e( 'Site ayarlarını aç', 'rencber' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=etkinlik' ) ); ?>"><?php esc_html_e( 'Konserler', 'rencber' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=album' ) ); ?>"><?php esc_html_e( 'Albümler', 'rencber' ); ?></a>
		</p>
		<h2><?php esc_html_e( 'Eski içerikleri aktar', 'rencber' ); ?></h2>
		<p><?php esc_html_e( 'Apple Music kataloğundan derlenen diskografiyi (11 stüdyo albümü şarkı listeleriyle, single’lar ve Home Concert kayıtları), galeri fotoğraflarını, biyografi fotoğrafını ve Google’da görünen konserleri ekler. Daha önce eklenenleri tekrar eklemez.', 'rencber' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'rencber_import' ); ?>
			<button class="button" name="rencber_import" value="1"><?php esc_html_e( 'Aktarımı çalıştır', 'rencber' ); ?></button>
		</form>
		<h2><?php esc_html_e( 'Google Etkinlikler için', 'rencber' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Her konseri Konserler › Yeni konser ekle ile girin: tarih, saat, şehir, mekân, mümkünse açık adres ve bilet bağlantısı.', 'rencber' ); ?></li>
			<li><?php esc_html_e( 'Tema her konser için Google’ın okuduğu MusicEvent verisini otomatik üretir; geçmiş konserler sitede gizlenir.', 'rencber' ); ?></li>
			<li><?php esc_html_e( 'Google Search Console’da konser sayfasını “URL denetimi” ile dizine eklenmesi için gönderin.', 'rencber' ); ?></li>
		</ol>
	</div>
	<?php
}
