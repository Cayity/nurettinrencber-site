<?php
/**
 * Rençber teması — çekirdek kurulum.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

define( 'RENCBER_VERSION', '1.0.0' );
define( 'RENCBER_DIR', get_template_directory() );
define( 'RENCBER_URI', get_template_directory_uri() );

require RENCBER_DIR . '/inc/helpers.php';
require RENCBER_DIR . '/inc/post-types.php';
require RENCBER_DIR . '/inc/customizer.php';
require RENCBER_DIR . '/inc/schema.php';
require RENCBER_DIR . '/inc/youtube.php';
require RENCBER_DIR . '/inc/importer.php';

/**
 * Tema özellikleri.
 */
function rencber_setup() {
	load_theme_textdomain( 'rencber', RENCBER_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 520,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_image_size( 'rencber-cover', 600, 600, true );
	add_image_size( 'rencber-wide', 1600, 1000, false );

	register_nav_menus( array(
		'primary' => __( 'Ana menü', 'rencber' ),
		'footer'  => __( 'Alt menü', 'rencber' ),
	) );
}
add_action( 'after_setup_theme', 'rencber_setup' );

/**
 * Stil ve script dosyaları.
 */
function rencber_assets() {
	wp_enqueue_style(
		'rencber-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Manrope:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'rencber', get_stylesheet_uri(), array( 'rencber-fonts' ), RENCBER_VERSION );

	$accent = rencber_mod( 'accent_color' );
	if ( $accent ) {
		wp_add_inline_style( 'rencber', ':root{--accent:' . sanitize_hex_color( $accent ) . ';}' );
	}

	wp_enqueue_script( 'rencber', RENCBER_URI . '/assets/js/main.js', array(), RENCBER_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
}
add_action( 'wp_enqueue_scripts', 'rencber_assets' );

/**
 * Google Fonts için önbağlantı.
 */
function rencber_resource_hints( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
		$urls[] = 'https://fonts.googleapis.com';
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'rencber_resource_hints', 10, 2 );

/**
 * Eski Enfold sayfa oluşturucu kısa kodlarını (av_*) temizler; içerik metni kalır.
 * Tema değişince eski sayfalarda [av_section ...] gibi çıktılar görünmesin diye.
 */
function rencber_strip_enfold_shortcodes( $content ) {
	if ( false === strpos( $content, '[av_' ) ) {
		return $content;
	}
	return preg_replace( '/\[\/?av_[^\]]*\]/', '', $content );
}
add_filter( 'the_content', 'rencber_strip_enfold_shortcodes', 5 );

/**
 * Body sınıfları: ön sayfa dışındaki sayfalarda başlık hep koyu.
 */
function rencber_body_class( $classes ) {
	if ( ! is_front_page() ) {
		$classes[] = 'is-inner';
	}
	return $classes;
}
add_filter( 'body_class', 'rencber_body_class' );

/**
 * Konser arşivinde: yalnızca gelecek tarihli etkinlikler, tarihe göre sıralı.
 * Albüm arşivinde: yıla göre yeniden eskiye.
 */
function rencber_archive_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'etkinlik' ) ) {
		$past = isset( $_GET['gecmis'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$query->set( 'posts_per_page', 50 );
		$query->set( 'meta_key', '_ev_date' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', $past ? 'DESC' : 'ASC' );
		$query->set( 'meta_query', array(
			array(
				'key'     => '_ev_date',
				'value'   => wp_date( 'Y-m-d' ),
				'compare' => $past ? '<' : '>=',
				'type'    => 'DATE',
			),
		) );
	}
	if ( $query->is_post_type_archive( 'album' ) ) {
		$query->set( 'posts_per_page', 50 );
		$query->set( 'meta_key', '_al_year' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'DESC' );
	}
}
add_action( 'pre_get_posts', 'rencber_archive_queries' );
