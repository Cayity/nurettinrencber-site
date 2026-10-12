<?php
/**
 * YouTube: kanaldaki en yeni videoları RSS ile çeker (API anahtarı gerekmez), 6 saat önbellekler.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Kanal bağlantısından kanal ID'si (UC...).
 */
function rencber_youtube_channel_id( $url ) {
	if ( preg_match( '~youtube\.com/channel/(UC[\w-]{22})~', (string) $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * Gösterilecek videolar: [ ['id' => ..., 'title' => ...], ... ].
 */
function rencber_videos() {
	$limit = max( 1, (int) rencber_mod( 'videos_limit' ) );

	if ( 'manual' === rencber_mod( 'videos_source' ) ) {
		$out = array();
		foreach ( preg_split( '/\R/', (string) rencber_mod( 'videos_manual' ) ) as $line ) {
			$id = rencber_youtube_id( $line );
			if ( $id ) {
				$out[] = array( 'id' => $id, 'title' => '' );
			}
		}
		return array_slice( $out, 0, $limit );
	}

	$channel = rencber_youtube_channel_id( rencber_mod( 'url_youtube' ) );
	if ( ! $channel ) {
		return array();
	}

	$key    = 'rencber_yt_' . $channel;
	$videos = get_transient( $key );

	if ( false === $videos ) {
		$videos = array();
		$res    = wp_remote_get( 'https://www.youtube.com/feeds/videos.xml?channel_id=' . rawurlencode( $channel ), array( 'timeout' => 8 ) );
		if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) && function_exists( 'simplexml_load_string' ) ) {
			libxml_use_internal_errors( true );
			$xml = simplexml_load_string( wp_remote_retrieve_body( $res ) );
			if ( $xml ) {
				foreach ( $xml->entry as $entry ) {
					$yt       = $entry->children( 'http://www.youtube.com/xml/schemas/2015' );
					$videos[] = array(
						'id'    => (string) $yt->videoId, // phpcs:ignore WordPress.NamingConventions.ValidVariableName
						'title' => (string) $entry->title,
					);
				}
			}
		}
		// Hata durumunda 30 dk sonra tekrar dene.
		set_transient( $key, $videos, $videos ? 6 * HOUR_IN_SECONDS : 30 * MINUTE_IN_SECONDS );
	}

	return array_slice( $videos, 0, $limit );
}

/**
 * Ayar değişince önbelleği temizle.
 */
function rencber_flush_youtube_cache() {
	$channel = rencber_youtube_channel_id( rencber_mod( 'url_youtube' ) );
	if ( $channel ) {
		delete_transient( 'rencber_yt_' . $channel );
	}
}
add_action( 'customize_save_after', 'rencber_flush_youtube_cache' );
