<?php
/**
 * SEO: meta etiketleri ve schema.org yapılandırılmış verisi.
 *
 * - Sanatçı (Person + MusicGroup) ve sameAs ile Spotify / Apple Music / YouTube profilleri:
 *   Google bilgi panelinde platformların eşleşmesine yardım eder.
 * - Her konser için MusicEvent: Google aramada "Etkinlikler" listesine girmeye uygun hale getirir.
 * - Albümler için MusicAlbum.
 *
 * @package Rencber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bir SEO eklentisi meta etiketlerini zaten yazıyor mu?
 */
function rencber_seo_plugin_active() {
	return defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || class_exists( 'The_SEO_Framework\Load' );
}

/**
 * Sanatçı varlığının sabit kimliği.
 */
function rencber_artist_id() {
	return home_url( '/#sanatci' );
}

/**
 * Paylaşım görseli.
 */
function rencber_share_image() {
	$img = rencber_image_url( rencber_mod( 'seo_image' ), 'full' );
	if ( ! $img ) {
		$img = rencber_image_url( rencber_mod( 'hero_poster' ), 'full' );
	}
	if ( ! $img ) {
		$img = rencber_image_url( rencber_mod( 'about_image' ), 'full' );
	}
	return $img;
}

/**
 * Meta açıklama + Open Graph + Twitter (SEO eklentisi yoksa).
 */
function rencber_meta_tags() {
	if ( rencber_seo_plugin_active() ) {
		return;
	}

	$title = wp_get_document_title();
	$desc  = (string) rencber_mod( 'seo_description' );
	$image = rencber_share_image();
	$url   = home_url( add_query_arg( array() ) );
	$type  = 'website';

	if ( is_singular() && ! is_front_page() ) {
		$post = get_queried_object();
		$url  = get_permalink( $post );
		$type = 'article';
		if ( has_excerpt( $post ) ) {
			$desc = get_the_excerpt( $post );
		} elseif ( 'etkinlik' === $post->post_type ) {
			$e = rencber_event( $post );
			if ( $e['ts'] ) {
				$p    = rencber_date_parts( $e['ts'] );
				$desc = sprintf(
					/* translators: 1: sanatçı, 2: tarih, 3: saat, 4: mekân, 5: şehir */
					__( '%1$s %2$s %3$s saatinde %4$s, %5$s sahnesinde. Bilet ve konser bilgileri.', 'rencber' ),
					rencber_mod( 'artist_name' ),
					$p['long'],
					$e['time'],
					$e['venue'],
					$e['city']
				);
			}
		} elseif ( $post->post_content ) {
			$desc = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '…' );
		}
		if ( has_post_thumbnail( $post ) ) {
			$image = get_the_post_thumbnail_url( $post, 'full' );
		} elseif ( 'album' === $post->post_type ) {
			$image = rencber_album_cover( $post->ID, 'full' );
		}
	} elseif ( is_front_page() ) {
		$url = home_url( '/' );
	}

	$desc = trim( preg_replace( '/\s+/', ' ', $desc ) );

	echo "\n<!-- Rençber SEO -->\n";
	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( str_replace( '-', '_', get_bloginfo( 'language' ) ) ) );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	if ( $desc ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
	}
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
}
add_action( 'wp_head', 'rencber_meta_tags', 2 );

/**
 * WordPress çekirdeğinin kendi canonical etiketini, biz yazıyorsak kaldır (çift olmasın).
 */
function rencber_remove_core_canonical() {
	if ( ! rencber_seo_plugin_active() ) {
		remove_action( 'wp_head', 'rel_canonical' );
	}
}
add_action( 'wp', 'rencber_remove_core_canonical' );

/**
 * Sanatçı şeması.
 */
function rencber_artist_schema( $full = true ) {
	$same_as = array();
	foreach ( rencber_platform_list() as $key => $p ) {
		$u = rencber_mod( 'url_' . $key );
		if ( $u ) {
			$same_as[] = $u;
		}
	}
	if ( rencber_mod( 'url_wikipedia' ) ) {
		$same_as[] = rencber_mod( 'url_wikipedia' );
	}

	$artist = array(
		'@type' => array( 'Person', 'MusicGroup' ),
		'@id'   => rencber_artist_id(),
		'name'  => rencber_mod( 'artist_name' ),
		'url'   => home_url( '/' ),
	);
	if ( ! $full ) {
		return $artist;
	}

	$image = rencber_image_url( rencber_mod( 'about_image' ), 'full' );
	if ( ! $image ) {
		$image = rencber_share_image();
	}
	if ( $image ) {
		$artist['image'] = $image;
	}
	if ( rencber_mod( 'seo_description' ) ) {
		$artist['description'] = rencber_mod( 'seo_description' );
	}
	if ( rencber_mod( 'seo_genre' ) ) {
		$artist['genre'] = rencber_mod( 'seo_genre' );
	}
	$artist['jobTitle'] = __( 'Müzisyen', 'rencber' );
	if ( rencber_mod( 'seo_birth_date' ) ) {
		$artist['birthDate'] = rencber_mod( 'seo_birth_date' );
	}
	if ( rencber_mod( 'seo_birth_place' ) ) {
		$artist['birthPlace'] = array( '@type' => 'Place', 'name' => rencber_mod( 'seo_birth_place' ) );
	}
	if ( $same_as ) {
		$artist['sameAs'] = array_values( array_unique( $same_as ) );
	}
	if ( rencber_mod( 'manager_email' ) || rencber_mod( 'manager_phone' ) ) {
		$artist['contactPoint'] = array_filter( array(
			'@type'       => 'ContactPoint',
			'contactType' => 'booking',
			'name'        => rencber_mod( 'manager_name' ),
			'email'       => rencber_mod( 'manager_email' ),
			'telephone'   => preg_replace( '/[^\d+]/', '', (string) rencber_mod( 'manager_phone' ) ),
		) );
	}

	$albums = array();
	foreach ( rencber_albums( 'album' ) as $a ) {
		$albums[] = array(
			'@type'         => 'MusicAlbum',
			'name'          => get_the_title( $a ),
			'url'           => get_permalink( $a ),
			'datePublished' => get_post_meta( $a->ID, '_al_year', true ),
		);
	}
	if ( $albums ) {
		$artist['album'] = $albums;
	}

	return $artist;
}

/**
 * Tek bir konserin MusicEvent şeması.
 */
function rencber_event_schema( $post ) {
	$e = rencber_event( $post );
	if ( ! $e['date'] || ! $e['venue'] ) {
		return null;
	}

	$tz    = wp_timezone();
	$start = new DateTime( $e['date'] . ' ' . ( $e['time'] ? $e['time'] : '20:00' ), $tz );

	$image = get_the_post_thumbnail_url( $e['id'], 'full' );
	if ( ! $image ) {
		$image = rencber_share_image();
	}

	$desc = has_excerpt( $e['id'] ) ? get_the_excerpt( $e['id'] ) : sprintf(
		/* translators: 1: sanatçı, 2: mekân, 3: şehir */
		__( '%1$s, %2$s (%3$s) konseri.', 'rencber' ),
		rencber_mod( 'artist_name' ),
		$e['venue'],
		$e['city']
	);

	$schema = array(
		'@type'               => 'MusicEvent',
		'@id'                 => $e['url'] . '#etkinlik',
		'name'                => $e['title'],
		'url'                 => $e['url'],
		'description'         => wp_strip_all_tags( $desc ),
		'startDate'           => $start->format( 'c' ),
		'eventStatus'         => 'https://schema.org/' . $e['status'],
		'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
		'location'            => array(
			'@type'   => 'Place',
			'name'    => $e['venue'],
			'address' => array_filter( array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $e['address'],
				'addressLocality' => $e['city'],
				'addressCountry'  => $e['country'],
			) ),
		),
		'performer'           => rencber_artist_schema( false ),
		'organizer'           => array(
			'@type' => 'Organization',
			'name'  => rencber_mod( 'artist_name' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( $e['end_time'] ) {
		$end = new DateTime( $e['date'] . ' ' . $e['end_time'], $tz );
		if ( $end < $start ) {
			$end->modify( '+1 day' );
		}
		$schema['endDate'] = $end->format( 'c' );
	}
	if ( $image ) {
		$schema['image'] = array( $image );
	}
	if ( $e['ticket_url'] || '' !== $e['price'] ) {
		$offer = array(
			'@type'         => 'Offer',
			'url'           => $e['ticket_url'] ? $e['ticket_url'] : $e['url'],
			'availability'  => 'https://schema.org/' . ( $e['soldout'] ? 'SoldOut' : 'InStock' ),
			'priceCurrency' => $e['currency'],
			'validFrom'     => get_post_time( 'c', false, $e['id'] ),
		);
		if ( '' !== $e['price'] ) {
			$offer['price'] = (string) (float) str_replace( ',', '.', $e['price'] );
		}
		if ( $e['vendor'] ) {
			$offer['seller'] = array( '@type' => 'Organization', 'name' => $e['vendor'] );
		}
		$schema['offers'] = $offer;
	}

	return $schema;
}

/**
 * Albüm şeması.
 */
function rencber_album_schema( $post ) {
	$post   = get_post( $post );
	$tracks = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $post->ID, '_al_tracks', true ) ) ) );
	$schema = array(
		'@type'         => 'MusicAlbum',
		'@id'           => get_permalink( $post ) . '#album',
		'name'          => get_the_title( $post ),
		'url'           => get_permalink( $post ),
		'byArtist'      => rencber_artist_schema( false ),
		'datePublished' => get_post_meta( $post->ID, '_al_year', true ),
		'image'         => rencber_album_cover( $post->ID, 'full' ),
		'albumReleaseType' => 'https://schema.org/' . ( 'album' === ( get_post_meta( $post->ID, '_al_type', true ) ?: 'album' ) ? 'AlbumRelease' : 'SingleRelease' ),
	);
	$same = array_filter( array(
		get_post_meta( $post->ID, '_al_spotify', true ),
		get_post_meta( $post->ID, '_al_apple', true ),
		get_post_meta( $post->ID, '_al_youtube', true ),
	) );
	if ( $same ) {
		$schema['sameAs'] = array_values( $same );
	}
	if ( $tracks ) {
		$schema['numTracks'] = count( $tracks );
		$schema['track']     = array(
			'@type'           => 'ItemList',
			'numberOfItems'   => count( $tracks ),
			'itemListElement' => array_map( function ( $t, $i ) {
				return array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'item'     => array( '@type' => 'MusicRecording', 'name' => $t ),
				);
			}, array_values( $tracks ), array_keys( array_values( $tracks ) ) ),
		);
	}
	return array_filter( $schema );
}

/**
 * JSON-LD çıktısı.
 */
function rencber_output_schema() {
	$graph = array();

	if ( is_front_page() ) {
		$graph[] = rencber_artist_schema();
		$graph[] = array(
			'@type'     => 'WebSite',
			'@id'       => home_url( '/#site' ),
			'url'       => home_url( '/' ),
			'name'      => get_bloginfo( 'name' ),
			'publisher' => array( '@id' => rencber_artist_id() ),
			'inLanguage' => get_bloginfo( 'language' ),
		);
		foreach ( rencber_upcoming_events( 30 ) as $ev ) {
			$s = rencber_event_schema( $ev );
			if ( $s ) {
				$graph[] = $s;
			}
		}
	} elseif ( is_singular( 'etkinlik' ) ) {
		$s = rencber_event_schema( get_queried_object() );
		if ( $s ) {
			$graph[] = $s;
		}
	} elseif ( is_singular( 'album' ) ) {
		$graph[] = rencber_album_schema( get_queried_object() );
	} elseif ( is_post_type_archive( 'etkinlik' ) ) {
		foreach ( rencber_upcoming_events( 50 ) as $ev ) {
			$s = rencber_event_schema( $ev );
			if ( $s ) {
				$graph[] = $s;
			}
		}
	}

	if ( ! $graph ) {
		return;
	}

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	) . '</script>' . "\n";
}
add_action( 'wp_head', 'rencber_output_schema', 20 );
