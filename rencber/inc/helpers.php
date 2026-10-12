<?php
/**
 * Yardımcı fonksiyonlar ve varsayılan ayarlar.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tüm özelleştirici ayarlarının varsayılanları (tek kaynak).
 */
function rencber_defaults() {
	return array(
		// Genel.
		'artist_name'        => 'Nurettin Rençber',
		'accent_color'       => '#d49a4a',

		// Hero.
		'hero_video_type'    => 'youtube',
		'hero_youtube'       => 'https://www.youtube.com/watch?v=5KOIfMAcb-Q',
		'hero_mp4'           => '',
		'hero_poster'        => '',
		'hero_kicker'        => 'Türkü · Ezgi · Söz',
		'hero_title'         => 'Nurettin Rençber',
		'hero_text'          => 'Dağ türkülerinden deniz şarkılarına; otuz yılı aşkın bir yolculuk.',
		'hero_btn1_text'     => 'Konser takvimi',
		'hero_btn1_url'      => '#konserler',
		'hero_btn2_text'     => 'Şimdi dinle',
		'hero_btn2_url'      => '#dinle',
		'hero_overlay'       => 55,

		// Platformlar.
		'url_spotify'        => 'https://open.spotify.com/artist/0eZircNzjxnRUtRBVOsth2',
		'url_apple'          => 'https://music.apple.com/tr/artist/nurettin-ren%C3%A7ber/256846459',
		'url_youtube'        => 'https://www.youtube.com/channel/UCZI7HYnOxzdgq-bTqLHdUDQ',
		'url_youtube_music'  => '',
		'url_deezer'         => '',
		'url_instagram'      => 'https://www.instagram.com/nurettinrencber.official/',
		'url_facebook'       => 'https://www.facebook.com/nurettinrencberresmi/',
		'url_x'              => '',
		'url_tiktok'         => '',
		'url_wikipedia'      => 'https://tr.wikipedia.org/wiki/Nurettin_Ren%C3%A7ber',

		// Bölüm görünürlükleri ve başlıkları.
		'show_events'        => true,
		'title_events'       => 'Konserler',
		'events_limit'       => 8,
		'events_empty'       => 'Yeni konser tarihleri çok yakında burada.',
		'show_listen'        => true,
		'title_listen'       => 'Dinle',
		'listen_text'        => 'Bütün albümler ve şarkılar dijital platformlarda.',
		'spotify_embed'      => true,
		'show_albums'        => true,
		'title_albums'       => 'Albümler',
		'show_about'         => true,
		'title_about'        => 'Hakkında',
		'about_image'        => '',
		'about_quote'        => '',
		'about_text'         => "Rençber, Adıyaman’dan göçen bir ailenin çocuğu olarak 1960’ta Mersin’de doğdu. Müzikle tanışması ilk olarak 1977’de bağlama ile oldu. Çocukluğunun çoğu Mersin’de geçti. Liseyi bitirdikten sonra babasının bekçiliğini yaptığı bez fabrikasında çalıştı. 1981’de girdiği Ankara Üniversitesi Dil ve Tarih-Coğrafya Fakültesi’nden 1985’te felsefe öğretmeni olarak mezun oldu.\n\nİlk öğretmenlik deneyimini 1987’de Trabzon’un Tonya ilçesinde yaşadı. Dört sene burada öğretmenlik yaptıktan sonra Eskişehir, Çorum, Kırşehir ve Ankara’da görev yaptı. 2000 yılında öğretmenliği bıraktı.\n\nNurettin Rençber müzik yaşamına öğretmenlik görevini sürdürürken başladı. İlk albümü Dağ Türküleri ve Deniz Şarkıları’nı 1991’de yayınladı.",
		'show_videos'        => true,
		'title_videos'       => 'Videolar',
		'videos_source'      => 'channel',
		'videos_limit'       => 6,
		'videos_manual'      => '',
		'show_gallery'       => true,
		'title_gallery'      => 'Galeri',
		'gallery_ids'        => '',
		'show_contact'       => true,
		'title_contact'      => 'İletişim',
		'contact_text'       => 'Konser, organizasyon ve basın talepleri için menajerlik ile iletişime geçebilirsiniz.',
		'manager_name'       => 'Ali Yeygel',
		'manager_phone'      => '+90 507 343 30 29',
		'manager_email'      => 'aliyeygel@gmail.com',
		'contact_form'       => '',

		// SEO.
		'seo_description'    => 'Nurettin Rençber resmi web sitesi: konser takvimi, albümler, şarkılar, videolar ve iletişim bilgileri.',
		'seo_image'          => '',
		'seo_genre'          => 'Türk halk müziği',
		'seo_birth_date'     => '1960',
		'seo_birth_place'    => 'Mersin',

		// Alt bilgi.
		'footer_text'        => '© {yil} Nurettin Rençber. Tüm hakları saklıdır.',
	);
}

/**
 * Ayar oku (varsayılanıyla).
 */
function rencber_mod( $key ) {
	$defaults = rencber_defaults();
	return get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/**
 * Platform listesi: anahtar => [etiket, ikon, müzik platformu mu].
 */
function rencber_platform_list() {
	return array(
		'spotify'       => array( 'Spotify', 'spotify', true ),
		'apple'         => array( 'Apple Music', 'apple', true ),
		'youtube'       => array( 'YouTube', 'youtube', true ),
		'youtube_music' => array( 'YouTube Music', 'ytmusic', true ),
		'deezer'        => array( 'Deezer', 'deezer', true ),
		'instagram'     => array( 'Instagram', 'instagram', false ),
		'facebook'      => array( 'Facebook', 'facebook', false ),
		'x'             => array( 'X', 'x', false ),
		'tiktok'        => array( 'TikTok', 'tiktok', false ),
	);
}

/**
 * Doldurulmuş platform bağlantıları.
 *
 * @param string $filter 'music' | 'social' | 'all'.
 */
function rencber_platforms( $filter = 'all' ) {
	$out = array();
	foreach ( rencber_platform_list() as $key => $p ) {
		$url = rencber_mod( 'url_' . $key );
		if ( ! $url ) {
			continue;
		}
		if ( 'music' === $filter && ! $p[2] ) {
			continue;
		}
		if ( 'social' === $filter && $p[2] && 'youtube' !== $key ) {
			continue;
		}
		$out[ $key ] = array(
			'label' => $p[0],
			'icon'  => $p[1],
			'url'   => $url,
		);
	}
	return $out;
}

/**
 * Satır içi SVG ikonları.
 */
function rencber_icon( $name, $size = 20 ) {
	$paths = array(
		'spotify'   => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm4.6 14.4a.62.62 0 0 1-.86.2c-2.35-1.43-5.3-1.76-8.79-.96a.62.62 0 1 1-.28-1.21c3.81-.87 7.08-.5 9.72 1.11.3.18.39.57.2.86Zm1.22-2.72a.78.78 0 0 1-1.07.26c-2.69-1.65-6.8-2.13-9.98-1.17a.78.78 0 1 1-.45-1.49c3.64-1.1 8.16-.57 11.24 1.33.37.22.48.7.26 1.07Zm.1-2.83C14.7 8.94 9.37 8.76 6.3 9.7a.94.94 0 1 1-.54-1.8c3.53-1.07 9.4-.86 13.1 1.34a.94.94 0 0 1-.96 1.61Z"/>',
		'apple'     => '<path d="M16.37 2c.1 1.18-.33 2.35-1.03 3.2-.72.86-1.86 1.52-2.98 1.44-.13-1.15.42-2.36 1.08-3.11.74-.85 2-1.49 2.93-1.53ZM20.5 17.1c-.55 1.27-.82 1.84-1.53 2.96-.99 1.56-2.4 3.5-4.13 3.51-1.54.02-1.94-1-4.03-.99-2.09.01-2.53 1.01-4.07.99-1.74-.02-3.07-1.77-4.06-3.33C-.1 15.88-.4 10.85 1.37 8.2c1.25-1.89 3.23-2.99 5.09-2.99 1.89 0 3.08 1.04 4.65 1.04 1.52 0 2.45-1.04 4.64-1.04 1.65 0 3.4.9 4.65 2.45-4.09 2.24-3.42 8.07.1 9.44Z"/>',
		'youtube'   => '<path d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .5 12a31 31 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8ZM9.7 15.1V8.9l5.8 3.1-5.8 3.1Z"/>',
		'ytmusic'   => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 15.3a5.3 5.3 0 1 1 0-10.6 5.3 5.3 0 0 1 0 10.6Zm0-11.6a6.3 6.3 0 1 0 0 12.6 6.3 6.3 0 0 0 0-12.6ZM10 9v6l5-3-5-3Z"/>',
		'deezer'    => '<path d="M18.8 4.5h4.7v2.8h-4.7zM18.8 8.4h4.7v2.8h-4.7zM18.8 12.3h4.7v2.8h-4.7zM.5 16.2h4.7V19H.5zM6.6 16.2h4.7V19H6.6zM12.7 16.2h4.7V19h-4.7zM18.8 16.2h4.7V19h-4.7zM12.7 12.3h4.7v2.8h-4.7zM6.6 12.3h4.7v2.8H6.6zM12.7 8.4h4.7v2.8h-4.7z"/>',
		'instagram' => '<path d="M12 2.2c3.2 0 3.6 0 4.8.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.7 4.8-4.9 4.9-1.3.1-1.6.1-4.8.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 3.9 3.9 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2ZM12 0C8.7 0 8.3 0 7.1.1 2.7.3.3 2.7.1 7.1 0 8.3 0 8.7 0 12s0 3.7.1 4.9c.2 4.4 2.6 6.8 7 7 1.2.1 1.6.1 4.9.1s3.7 0 4.9-.1c4.4-.2 6.8-2.6 7-7 .1-1.2.1-1.6.1-4.9s0-3.7-.1-4.9c-.2-4.4-2.6-6.8-7-7C15.7 0 15.3 0 12 0Zm0 5.8a6.2 6.2 0 1 0 0 12.4 6.2 6.2 0 0 0 0-12.4ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.4-11.8a1.4 1.4 0 1 0 0 2.9 1.4 1.4 0 0 0 0-2.9Z"/>',
		'facebook'  => '<path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.7 4.53-4.7 1.31 0 2.69.24 2.69.24v2.97h-1.51c-1.5 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07Z"/>',
		'x'         => '<path d="M18.2 2.3h3.4l-7.4 8.4 8.7 11.5h-6.8l-5.3-7-6.1 7H1.3l7.9-9L.8 2.3h7l4.8 6.4 5.6-6.4Zm-1.2 17.9h1.9L7 4.2H5l12 16Z"/>',
		'tiktok'    => '<path d="M16.6 5.8A4.3 4.3 0 0 1 15.5 3h-3.1v12.4a2.6 2.6 0 1 1-2.6-2.6c.3 0 .5 0 .8.1V9.7a5.7 5.7 0 1 0 4.9 5.7V9a7.4 7.4 0 0 0 4.3 1.4V7.3a4.3 4.3 0 0 1-3.2-1.5Z"/>',
		'play'      => '<path d="M8 5v14l11-7z"/>',
		'pause'     => '<path d="M6 5h4v14H6zM14 5h4v14h-4z"/>',
		'arrow'     => '<path d="M13.2 5.3 19.9 12l-6.7 6.7-1.4-1.4 4.3-4.3H4v-2h12.1l-4.3-4.3z"/>',
		'pin'       => '<path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z"/>',
		'phone'     => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2Z"/>',
		'mail'      => '<path d="M20 4H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5v2Z"/>',
		'close'     => '<path d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7l1.4-1.4 6.3 6.3 6.3-6.3z"/>',
		'chev-l'    => '<path d="m15.4 7.4-1.4-1.4-6 6 6 6 1.4-1.4L10.8 12z"/>',
		'chev-r'    => '<path d="M8.6 16.6 10 18l6-6-6-6-1.4 1.4 4.6 4.6z"/>',
		'sound-on'  => '<path d="M3 9v6h4l5 5V4L7 9H3Zm13.5 3A4.5 4.5 0 0 0 14 8v8a4.5 4.5 0 0 0 2.5-4ZM14 3.2v2.1a7 7 0 0 1 0 13.4v2.1a9 9 0 0 0 0-17.6Z"/>',
		'sound-off' => '<path d="M16.5 12A4.5 4.5 0 0 0 14 8v2.2l2.5 2.5V12Zm2.5 0c0 .9-.2 1.8-.5 2.6l1.5 1.5A8.8 8.8 0 0 0 21 12a9 9 0 0 0-7-8.8v2.1a7 7 0 0 1 5 6.7ZM4.3 3 3 4.3 7.7 9H3v6h4l5 5v-6.7l4.3 4.3c-.7.5-1.4.9-2.3 1.2v2.1a9 9 0 0 0 3.7-1.8l2 2 1.3-1.3-9-9L4.3 3ZM12 4 9.9 6.1 12 8.2V4Z"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="icon icon-%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$paths[ $name ]
	);
}

/**
 * Bir YouTube bağlantısından (veya doğrudan ID'den) video ID'sini çıkarır.
 */
function rencber_youtube_id( $url ) {
	$url = trim( (string) $url );
	if ( preg_match( '/^[\w-]{11}$/', $url ) ) {
		return $url;
	}
	if ( preg_match( '~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/))([\w-]{11})~', $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * Spotify sanatçı bağlantısından sanatçı ID'si.
 */
function rencber_spotify_artist_id( $url ) {
	if ( preg_match( '~open\.spotify\.com/(?:intl-[a-z]+/)?artist/([A-Za-z0-9]+)~', (string) $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * Görsel ayarı: ek ID'si ya da URL olabilir.
 */
function rencber_image_url( $value, $size = 'large' ) {
	if ( is_numeric( $value ) && $value ) {
		$src = wp_get_attachment_image_url( (int) $value, $size );
		return $src ? $src : '';
	}
	return $value ? esc_url_raw( $value ) : '';
}

/**
 * Gelecek etkinlikler (bugün dahil).
 */
function rencber_upcoming_events( $limit = 8 ) {
	return get_posts( array(
		'post_type'      => 'etkinlik',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $limit,
		'meta_key'       => '_ev_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'     => '_ev_date',
				'value'   => wp_date( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
	) );
}

/**
 * Bir etkinliğin tüm alanları tek dizide.
 */
function rencber_event( $post ) {
	$post = get_post( $post );
	$id   = $post->ID;
	$get  = function ( $k ) use ( $id ) {
		return (string) get_post_meta( $id, '_ev_' . $k, true );
	};
	$date = $get( 'date' );
	$time = $get( 'time' );
	$ts   = $date ? strtotime( $date . ' ' . ( $time ? $time : '00:00' ) ) : 0;

	return array(
		'id'         => $id,
		'title'      => get_the_title( $id ),
		'url'        => get_permalink( $id ),
		'date'       => $date,
		'time'       => $time,
		'ts'         => $ts,
		'city'       => $get( 'city' ),
		'venue'      => $get( 'venue' ),
		'address'    => $get( 'address' ),
		'country'    => $get( 'country' ) ? $get( 'country' ) : 'TR',
		'ticket_url' => $get( 'ticket_url' ),
		'vendor'     => $get( 'vendor' ),
		'price'      => $get( 'price' ),
		'currency'   => $get( 'currency' ) ? $get( 'currency' ) : 'TRY',
		'status'     => $get( 'status' ) ? $get( 'status' ) : 'EventScheduled',
		'soldout'    => (bool) $get( 'soldout' ),
		'end_time'   => $get( 'end_time' ),
	);
}

/**
 * Türkçe ay kısaltması ve gün adı, sitenin diline göre.
 */
function rencber_date_parts( $ts ) {
	// Zaman damgası girilen yerel saatten UTC olarak üretildi; kaydırma olmasın diye UTC ile biçimlendir.
	$utc = new DateTimeZone( 'UTC' );
	return array(
		'day'     => wp_date( 'j', $ts, $utc ),
		'month'   => wp_date( 'M', $ts, $utc ),
		'year'    => wp_date( 'Y', $ts, $utc ),
		'weekday' => wp_date( 'l', $ts, $utc ),
		'short'   => wp_date( 'D', $ts, $utc ),
		'long'    => wp_date( 'j F Y, l', $ts, $utc ),
	);
}

/**
 * Fiyatı "₺1.159" biçiminde gösterir.
 */
function rencber_price( $price, $currency = 'TRY' ) {
	if ( '' === $price ) {
		return '';
	}
	$symbols = array( 'TRY' => '₺', 'EUR' => '€', 'USD' => '$', 'GBP' => '£' );
	$sym     = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : $currency . ' ';
	$num     = (float) str_replace( ',', '.', $price );
	$dec     = floor( $num ) == $num ? 0 : 2; // phpcs:ignore Universal.Operators.StrictComparisons
	return $sym . number_format_i18n( $num, $dec );
}

/**
 * Durum etiketleri.
 */
function rencber_event_statuses() {
	return array(
		'EventScheduled'   => __( 'Planlandı', 'rencber' ),
		'EventPostponed'   => __( 'Ertelendi', 'rencber' ),
		'EventRescheduled' => __( 'Yeni tarihe alındı', 'rencber' ),
		'EventCancelled'   => __( 'İptal edildi', 'rencber' ),
	);
}

/**
 * Albümler (yeniden eskiye).
 */
function rencber_release_types() {
	return array(
		'album'  => __( 'Albüm', 'rencber' ),
		'single' => __( 'Single', 'rencber' ),
		'home'   => __( 'Home Concert', 'rencber' ),
	);
}

function rencber_albums( $type = '', $limit = -1 ) {
	$meta = array(
		'year' => array( 'key' => '_al_year', 'type' => 'NUMERIC' ),
	);
	if ( in_array( $type, array( 'single', 'home' ), true ) ) {
		$meta[] = array( 'key' => '_al_type', 'value' => $type );
	} elseif ( 'album' === $type ) {
		// Tür seçilmemiş eski kayıtlar albüm sayılır.
		$meta[] = array(
			'relation' => 'OR',
			array( 'key' => '_al_type', 'value' => 'album' ),
			array( 'key' => '_al_type', 'compare' => 'NOT EXISTS' ),
		);
	}
	return get_posts( array(
		'post_type'      => 'album',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'meta_query'     => $meta,
		'orderby'        => array(
			'year' => 'DESC',
			'date' => 'DESC',
		),
	) );
}

/**
 * Albüm kapak görseli URL'si (öne çıkan görsel ya da yedek URL).
 */
function rencber_album_cover( $post_id, $size = 'rencber-cover' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail_url( $post_id, $size );
	}
	return (string) get_post_meta( $post_id, '_al_cover_url', true );
}

/**
 * Galeri ek ID'leri.
 */
function rencber_gallery_ids() {
	$raw = (string) rencber_mod( 'gallery_ids' );
	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}

/**
 * Bir bölüm başlığı.
 */
function rencber_section_head( $eyebrow, $title, $link = '', $link_text = '' ) {
	echo '<header class="section-head">';
	if ( $eyebrow ) {
		echo '<span class="eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	echo '<h2 class="section-title">' . esc_html( $title ) . '</h2>';
	if ( $link ) {
		echo '<a class="section-link" href="' . esc_url( $link ) . '">' . esc_html( $link_text ) . ' ' . rencber_icon( 'arrow', 16 ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</header>';
}

/**
 * Menü bulunmazsa kullanılan varsayılan bağlantılar.
 */
function rencber_default_menu() {
	$base  = is_front_page() ? '' : home_url( '/' );
	$items = array(
		'show_events'  => array( '#konserler', rencber_mod( 'title_events' ) ),
		'show_listen'  => array( '#dinle', rencber_mod( 'title_listen' ) ),
		'show_albums'  => array( '#albumler', rencber_mod( 'title_albums' ) ),
		'show_about'   => array( '#hakkinda', rencber_mod( 'title_about' ) ),
		'show_videos'  => array( '#videolar', rencber_mod( 'title_videos' ) ),
		'show_gallery' => array( '#galeri', rencber_mod( 'title_gallery' ) ),
		'show_contact' => array( '#iletisim', rencber_mod( 'title_contact' ) ),
	);
	echo '<ul class="menu">';
	foreach ( $items as $flag => $item ) {
		if ( ! rencber_mod( $flag ) ) {
			continue;
		}
		printf( '<li><a href="%s">%s</a></li>', esc_url( $base . $item[0] ), esc_html( $item[1] ) );
	}
	echo '</ul>';
}

/**
 * Alt bilgi metni ({yil} yer tutucusu ile).
 */
function rencber_footer_text() {
	return str_replace( '{yil}', wp_date( 'Y' ), (string) rencber_mod( 'footer_text' ) );
}
