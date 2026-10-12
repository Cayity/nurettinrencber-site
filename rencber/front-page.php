<?php
/**
 * Ana sayfa: tek sayfa düzeni. Bölümler Özelleştir panelinden açılıp kapatılır.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'parts/hero' );

$sections = array(
	'show_events'  => 'events',
	'show_listen'  => 'listen',
	'show_albums'  => 'albums',
	'show_about'   => 'about',
	'show_videos'  => 'videos',
	'show_gallery' => 'gallery',
	'show_contact' => 'contact',
);
foreach ( $sections as $flag => $part ) {
	if ( rencber_mod( $flag ) ) {
		get_template_part( 'parts/' . $part );
	}
}

get_footer();
