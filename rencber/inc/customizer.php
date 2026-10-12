<?php
/**
 * Görünüm › Özelleştir: sitedeki her metin, görsel, video ve bağlantı buradan yönetilir.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Çoklu görsel seçici (galeri) kontrolü.
 */
function rencber_customizer_controls_class() {
	if ( ! class_exists( 'WP_Customize_Control' ) || class_exists( 'Rencber_Gallery_Control' ) ) {
		return;
	}

	class Rencber_Gallery_Control extends WP_Customize_Control { // phpcs:ignore Generic.Files.OneObjectStructurePerFile
		public $type = 'rencber_gallery';

		public function enqueue() {
			wp_enqueue_media();
			wp_enqueue_script( 'rencber-customizer-gallery', RENCBER_URI . '/assets/js/customizer-gallery.js', array( 'jquery', 'customize-controls' ), RENCBER_VERSION, true );
		}

		public function render_content() {
			$ids = array_filter( array_map( 'absint', explode( ',', (string) $this->value() ) ) );
			?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<div class="rencber-gallery-preview" style="display:grid;grid-template-columns:repeat(4,1fr);gap:4px;margin:8px 0">
				<?php foreach ( $ids as $id ) : ?>
					<?php echo wp_get_attachment_image( $id, 'thumbnail', false, array( 'style' => 'width:100%;height:auto;display:block' ) ); ?>
				<?php endforeach; ?>
			</div>
			<input type="hidden" class="rencber-gallery-input" <?php $this->link(); ?> value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
			<button type="button" class="button rencber-gallery-edit"><?php esc_html_e( 'Fotoğrafları seç / sırala', 'rencber' ); ?></button>
			<button type="button" class="button-link rencber-gallery-clear" style="margin-left:8px;color:#b32d2e"><?php esc_html_e( 'Temizle', 'rencber' ); ?></button>
			<?php
		}
	}
}
add_action( 'customize_register', 'rencber_customizer_controls_class', 1 );

/* Temizleyiciler. */
function rencber_sanitize_checkbox( $v ) {
	return (bool) $v;
}
function rencber_sanitize_ids( $v ) {
	return implode( ',', array_filter( array_map( 'absint', explode( ',', (string) $v ) ) ) );
}
function rencber_sanitize_select( $v, $setting ) {
	$choices = $setting->manager->get_control( $setting->id )->choices;
	return array_key_exists( $v, $choices ) ? $v : $setting->default;
}
function rencber_sanitize_int( $v ) {
	return absint( $v );
}
function rencber_sanitize_rich( $v ) {
	return wp_kses_post( $v );
}
function rencber_sanitize_shortcode( $v ) {
	return sanitize_text_field( $v );
}

/**
 * Ayarları kaydet.
 */
function rencber_customize_register( $wp_customize ) {
	$d = rencber_defaults();

	// Kısayol: ayar + kontrol ekle.
	$add = function ( $id, $section, $label, $type = 'text', $args = array() ) use ( $wp_customize, $d ) {
		$sanitize = array(
			'text'      => 'sanitize_text_field',
			'textarea'  => 'rencber_sanitize_rich',
			'url'       => 'esc_url_raw',
			'email'     => 'sanitize_email',
			'checkbox'  => 'rencber_sanitize_checkbox',
			'number'    => 'rencber_sanitize_int',
			'range'     => 'rencber_sanitize_int',
			'select'    => 'rencber_sanitize_select',
			'radio'     => 'rencber_sanitize_select',
			'media'     => 'absint',
			'color'     => 'sanitize_hex_color',
			'gallery'   => 'rencber_sanitize_ids',
			'shortcode' => 'rencber_sanitize_shortcode',
		);
		$wp_customize->add_setting( $id, array(
			'default'           => isset( $d[ $id ] ) ? $d[ $id ] : '',
			'sanitize_callback' => isset( $args['sanitize'] ) ? $args['sanitize'] : $sanitize[ $type ],
			'transport'         => 'refresh',
		) );

		$base = array(
			'label'       => $label,
			'section'     => $section,
			'description' => isset( $args['description'] ) ? $args['description'] : '',
		);

		if ( 'media' === $type ) {
			$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $id, $base + array(
				'mime_type' => isset( $args['mime'] ) ? $args['mime'] : 'image',
			) ) );
			return;
		}
		if ( 'color' === $type ) {
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, $base ) );
			return;
		}
		if ( 'gallery' === $type ) {
			$wp_customize->add_control( new Rencber_Gallery_Control( $wp_customize, $id, $base ) );
			return;
		}
		$control = $base + array( 'type' => 'shortcode' === $type ? 'text' : $type );
		if ( isset( $args['choices'] ) ) {
			$control['choices'] = $args['choices'];
		}
		if ( isset( $args['input_attrs'] ) ) {
			$control['input_attrs'] = $args['input_attrs'];
		}
		$wp_customize->add_control( $id, $control );
	};

	$wp_customize->add_panel( 'rencber', array(
		'title'       => __( 'Rençber Site Ayarları', 'rencber' ),
		'description' => __( 'Sitedeki tüm metinler, video, bağlantılar ve bölümler. Konserler ve albümler sol menüdeki kendi bölümlerinden yönetilir.', 'rencber' ),
		'priority'    => 20,
	) );

	$sections = array(
		'rencber_general'  => __( 'Genel ve renk', 'rencber' ),
		'rencber_hero'     => __( 'Açılış ve arka plan videosu', 'rencber' ),
		'rencber_links'    => __( 'Spotify, Apple Music, YouTube ve sosyal medya', 'rencber' ),
		'rencber_events'   => __( 'Konserler bölümü', 'rencber' ),
		'rencber_listen'   => __( 'Dinle bölümü', 'rencber' ),
		'rencber_albums'   => __( 'Albümler bölümü', 'rencber' ),
		'rencber_about'    => __( 'Hakkında bölümü', 'rencber' ),
		'rencber_videos'   => __( 'Videolar bölümü', 'rencber' ),
		'rencber_gallery'  => __( 'Galeri bölümü', 'rencber' ),
		'rencber_contact'  => __( 'İletişim bölümü', 'rencber' ),
		'rencber_seo'      => __( 'SEO ve Google', 'rencber' ),
		'rencber_footer'   => __( 'Alt bilgi', 'rencber' ),
	);
	$p = 10;
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section( $id, array( 'title' => $title, 'panel' => 'rencber', 'priority' => $p ) );
		$p += 10;
	}

	// Genel.
	$add( 'artist_name', 'rencber_general', __( 'Sanatçı adı', 'rencber' ), 'text', array( 'description' => __( 'Logo yüklenmemişse başlıkta bu yazı görünür. Logo için: Site kimliği.', 'rencber' ) ) );
	$add( 'accent_color', 'rencber_general', __( 'Vurgu rengi', 'rencber' ), 'color' );

	// Hero.
	$add( 'hero_video_type', 'rencber_hero', __( 'Arka plan türü', 'rencber' ), 'radio', array(
		'choices' => array(
			'youtube' => __( 'YouTube videosu', 'rencber' ),
			'mp4'     => __( 'Yüklenen video (MP4 — en hızlısı)', 'rencber' ),
			'image'   => __( 'Yalnızca fotoğraf', 'rencber' ),
		),
	) );
	$add( 'hero_youtube', 'rencber_hero', __( 'YouTube video bağlantısı', 'rencber' ), 'url', array( 'description' => __( 'Ses kapalı ve döngüde oynar.', 'rencber' ) ) );
	$add( 'hero_mp4', 'rencber_hero', __( 'MP4 video', 'rencber' ), 'media', array( 'mime' => 'video', 'description' => __( '10–30 sn, 1080p, 8 MB altı önerilir.', 'rencber' ) ) );
	$add( 'hero_start', 'rencber_hero', __( 'Videoyu kaçıncı saniyeden başlat', 'rencber' ), 'number', array( 'input_attrs' => array( 'min' => 0, 'step' => 1 ), 'description' => __( 'Videonun başındaki yazı/jenerik görünmesin diye. Döngü de bu saniyeden döner.', 'rencber' ) ) );
	$add( 'hero_end', 'rencber_hero', __( 'Videoyu kaçıncı saniyede bitir (0 = sonuna kadar)', 'rencber' ), 'number', array( 'input_attrs' => array( 'min' => 0, 'step' => 1 ), 'description' => __( 'Sondaki yazıyı da atlamak için.', 'rencber' ) ) );
	$add( 'hero_poster', 'rencber_hero', __( 'Kapak fotoğrafı', 'rencber' ), 'media', array( 'description' => __( 'Video yüklenirken ve “yalnızca fotoğraf” seçiliyken görünür.', 'rencber' ) ) );
	$add( 'hero_overlay', 'rencber_hero', __( 'Karartma (%)', 'rencber' ), 'range', array( 'input_attrs' => array( 'min' => 0, 'max' => 90, 'step' => 5 ) ) );
	$add( 'hero_kicker', 'rencber_hero', __( 'Üst küçük yazı', 'rencber' ) );
	$add( 'hero_title', 'rencber_hero', __( 'Büyük başlık', 'rencber' ) );
	$add( 'hero_text', 'rencber_hero', __( 'Açıklama', 'rencber' ), 'textarea' );
	$add( 'hero_btn1_text', 'rencber_hero', __( '1. buton yazısı', 'rencber' ) );
	$add( 'hero_btn1_url', 'rencber_hero', __( '1. buton bağlantısı', 'rencber' ), 'text', array( 'sanitize' => 'esc_url_raw' ) );
	$add( 'hero_btn2_text', 'rencber_hero', __( '2. buton yazısı', 'rencber' ) );
	$add( 'hero_btn2_url', 'rencber_hero', __( '2. buton bağlantısı', 'rencber' ), 'text', array( 'sanitize' => 'esc_url_raw' ) );

	// Bağlantılar.
	foreach ( rencber_platform_list() as $key => $pl ) {
		$add( 'url_' . $key, 'rencber_links', $pl[0], 'url' );
	}
	$add( 'url_wikipedia', 'rencber_links', __( 'Vikipedi sayfası (yalnızca Google için)', 'rencber' ), 'url', array( 'description' => __( 'Sitede görünmez; Google bilgi paneline sanatçıyı tanıtmak için kullanılır.', 'rencber' ) ) );

	// Konserler.
	$add( 'show_events', 'rencber_events', __( 'Bu bölümü göster', 'rencber' ), 'checkbox' );
	$add( 'title_events', 'rencber_events', __( 'Bölüm başlığı', 'rencber' ) );
	$add( 'events_limit', 'rencber_events', __( 'Ana sayfada en fazla kaç konser', 'rencber' ), 'number', array( 'input_attrs' => array( 'min' => 1, 'max' => 30 ) ) );
	$add( 'events_empty', 'rencber_events', __( 'Konser yokken görünen yazı', 'rencber' ) );

	// Dinle.
	$add( 'show_listen', 'rencber_listen', __( 'Bu bölümü göster', 'rencber' ), 'checkbox' );
	$add( 'title_listen', 'rencber_listen', __( 'Bölüm başlığı', 'rencber' ) );
	$add( 'listen_text', 'rencber_listen', __( 'Açıklama', 'rencber' ), 'textarea' );
	$add( 'spotify_embed', 'rencber_listen', __( 'Spotify oynatıcısını göster', 'rencber' ), 'checkbox' );

	// Albümler.
	$add( 'show_albums', 'rencber_albums', __( 'Bu bölümü göster', 'rencber' ), 'checkbox', array( 'description' => __( 'Albümleri sol menüde “Albümler”den ekleyip düzenleyin.', 'rencber' ) ) );
	$add( 'title_albums', 'rencber_albums', __( 'Bölüm başlığı', 'rencber' ) );

	// Hakkında.
	$add( 'show_about', 'rencber_about', __( 'Bu bölümü göster', 'rencber' ), 'checkbox' );
	$add( 'title_about', 'rencber_about', __( 'Bölüm başlığı', 'rencber' ) );
	$add( 'about_image', 'rencber_about', __( 'Fotoğraf', 'rencber' ), 'media' );
	$add( 'about_quote', 'rencber_about', __( 'Öne çıkan söz (isteğe bağlı)', 'rencber' ), 'textarea' );
	$add( 'about_text', 'rencber_about', __( 'Biyografi', 'rencber' ), 'textarea', array( 'description' => __( 'Paragrafları boş satırla ayırın.', 'rencber' ) ) );

	// Videolar.
	$add( 'show_videos', 'rencber_videos', __( 'Bu bölümü göster', 'rencber' ), 'checkbox' );
	$add( 'title_videos', 'rencber_videos', __( 'Bölüm başlığı', 'rencber' ) );
	$add( 'videos_source', 'rencber_videos', __( 'Videolar nereden gelsin?', 'rencber' ), 'radio', array(
		'choices' => array(
			'channel' => __( 'YouTube kanalındaki en yeni videolar (otomatik)', 'rencber' ),
			'manual'  => __( 'Aşağıdaki listeden', 'rencber' ),
		),
	) );
	$add( 'videos_limit', 'rencber_videos', __( 'Kaç video', 'rencber' ), 'number', array( 'input_attrs' => array( 'min' => 1, 'max' => 15 ) ) );
	$add( 'videos_manual', 'rencber_videos', __( 'Video listesi', 'rencber' ), 'textarea', array( 'sanitize' => 'sanitize_textarea_field', 'description' => __( 'Her satıra bir YouTube bağlantısı.', 'rencber' ) ) );

	// Galeri.
	$add( 'show_gallery', 'rencber_gallery', __( 'Bu bölümü göster', 'rencber' ), 'checkbox' );
	$add( 'title_gallery', 'rencber_gallery', __( 'Bölüm başlığı', 'rencber' ) );
	$add( 'gallery_ids', 'rencber_gallery', __( 'Fotoğraflar', 'rencber' ), 'gallery', array( 'description' => __( 'Ortam kütüphanesinden seçip sürükleyerek sıralayabilirsiniz.', 'rencber' ) ) );

	// İletişim.
	$add( 'show_contact', 'rencber_contact', __( 'Bu bölümü göster', 'rencber' ), 'checkbox' );
	$add( 'title_contact', 'rencber_contact', __( 'Bölüm başlığı', 'rencber' ) );
	$add( 'contact_text', 'rencber_contact', __( 'Açıklama', 'rencber' ), 'textarea' );
	$add( 'manager_name', 'rencber_contact', __( 'Menajer adı', 'rencber' ) );
	$add( 'manager_phone', 'rencber_contact', __( 'Telefon', 'rencber' ) );
	$add( 'manager_email', 'rencber_contact', __( 'E-posta', 'rencber' ), 'email' );
	$add( 'contact_form', 'rencber_contact', __( 'Form kısa kodu', 'rencber' ), 'shortcode', array( 'description' => __( 'Contact Form 7 kısa kodu, ör. [contact-form-7 id="123"]. Boşsa form gösterilmez.', 'rencber' ) ) );

	// SEO.
	$add( 'seo_description', 'rencber_seo', __( 'Site açıklaması', 'rencber' ), 'textarea', array( 'sanitize' => 'sanitize_textarea_field', 'description' => __( 'Google’da adın altında görünen yazı (150–160 karakter). Rank Math/Yoast kuruluysa onun ayarı geçerlidir.', 'rencber' ) ) );
	$add( 'seo_image', 'rencber_seo', __( 'Paylaşım görseli', 'rencber' ), 'media', array( 'description' => __( 'WhatsApp, Facebook, X paylaşımlarında görünür. 1200×630 önerilir.', 'rencber' ) ) );
	$add( 'seo_genre', 'rencber_seo', __( 'Müzik türü', 'rencber' ) );
	$add( 'seo_birth_date', 'rencber_seo', __( 'Doğum yılı / tarihi', 'rencber' ) );
	$add( 'seo_birth_place', 'rencber_seo', __( 'Doğum yeri', 'rencber' ) );

	// Alt bilgi.
	$add( 'footer_text', 'rencber_footer', __( 'Telif yazısı', 'rencber' ), 'text', array( 'description' => __( '{yil} yazarsanız bu yıl otomatik yazılır.', 'rencber' ) ) );
}
add_action( 'customize_register', 'rencber_customize_register' );
