// Sitenin ana sayfasını data/site.json'dan üretir. Bağımlılık yok (Node 18+).
// Hem Vercel derlemesinde (tools/build.mjs) hem de yerelde kullanılır.

import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const ROOT = path.resolve( path.dirname( fileURLToPath( import.meta.url ) ), '..' );
export const LIVE = 'https://www.nurettinrencber.com.tr';

const AYLAR = [ 'Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara' ];
const GUNLER = [ 'Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt' ];
const STATUS = {
	EventScheduled: '',
	EventPostponed: 'Ertelendi',
	EventRescheduled: 'Yeni tarihe alındı',
	EventCancelled: 'İptal edildi',
};

/* Platformlar: anahtar → [etiket, ikon, müzik platformu mu] (temadaki rencber_platform_list ile aynı). */
export const PLATFORMS = {
	spotify: [ 'Spotify', 'spotify', true ],
	apple: [ 'Apple Music', 'apple', true ],
	youtube: [ 'YouTube', 'youtube', true ],
	youtube_music: [ 'YouTube Music', 'ytmusic', true ],
	deezer: [ 'Deezer', 'deezer', true ],
	instagram: [ 'Instagram', 'instagram', false ],
	facebook: [ 'Facebook', 'facebook', false ],
	x: [ 'X', 'x', false ],
	tiktok: [ 'TikTok', 'tiktok', false ],
};

export const e = ( s ) => String( s ?? '' )
	.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' )
	.replace( /"/g, '&quot;' ).replace( /'/g, '&#39;' );

/* İkon yolları temadaki helpers.php'den okunur (tek kaynak). */
let ICONS = null;
function icons() {
	if ( ICONS ) return ICONS;
	const src = readFileSync( path.join( ROOT, 'rencber/inc/helpers.php' ), 'utf8' );
	const start = src.indexOf( '$paths = array(' );
	const block = src.slice( start, src.indexOf( ');', start ) );
	ICONS = {};
	for ( const m of block.matchAll( /'([\w-]+)'\s*=>\s*'([^']*)'/g ) ) ICONS[ m[ 1 ] ] = m[ 2 ];
	return ICONS;
}
export function icon( name, size = 20 ) {
	const p = icons()[ name ];
	if ( ! p ) return '';
	return `<svg class="icon icon-${ name }" width="${ size }" height="${ size }" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">${ p }</svg>`;
}

export function youtubeId( url ) {
	const u = String( url || '' ).trim();
	if ( /^[\w-]{11}$/.test( u ) ) return u;
	const m = u.match( /(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/|v\/))([\w-]{11})/ );
	return m ? m[ 1 ] : '';
}
const spotifyArtist = ( url ) => ( String( url || '' ).match( /open\.spotify\.com\/(?:intl-[a-z]+\/)?artist\/([A-Za-z0-9]+)/ ) || [] )[ 1 ] || '';
const price = ( p, cur = 'TRY' ) => {
	if ( p === '' || p == null ) return '';
	const sym = { TRY: '₺', EUR: '€', USD: '$', GBP: '£' }[ cur ] ?? cur + ' ';
	const n = Number( String( p ).replace( ',', '.' ) );
	return sym + n.toLocaleString( 'tr-TR', { maximumFractionDigits: Number.isInteger( n ) ? 0 : 2 } );
};
const dparts = ( iso ) => {
	const [ y, m, d ] = iso.split( '-' ).map( Number );
	const wd = new Date( Date.UTC( y, m - 1, d ) ).getUTCDay();
	return { y, m, d, month: AYLAR[ m - 1 ], wd: GUNLER[ wd ] };
};
const abs = ( src ) => ( /^https?:/.test( src ) ? src : `${ LIVE }/${ String( src ).replace( /^\//, '' ) }` );
const paras = ( text ) => String( text || '' ).split( /\n\s*\n/ ).map( ( p ) => p.trim() ).filter( Boolean )
	.map( ( p ) => `<p>${ e( p ).replace( /\n/g, '<br>' ) }</p>` ).join( '' );

function sectionHead( eyebrow, title, link = '', linkText = '' ) {
	let out = '<header class="section-head">';
	if ( eyebrow ) out += `<span class="eyebrow">${ e( eyebrow ) }</span>`;
	out += `<h2 class="section-title">${ e( title ) }</h2>`;
	if ( link ) out += `<a class="section-link" href="${ e( link ) }"${ /^https?:/.test( link ) ? ' target="_blank" rel="noopener"' : '' }>${ e( linkText ) } ${ icon( 'arrow', 16 ) }</a>`;
	return out + '</header>';
}

/* Bugünün tarihi (İstanbul) — geçmiş konserler sayfaya hiç yazılmaz. */
export function todayIstanbul() {
	return new Date().toLocaleDateString( 'sv-SE', { timeZone: 'Europe/Istanbul' } );
}

export function upcomingEvents( site, today = todayIstanbul() ) {
	return ( site.events || [] )
		.filter( ( ev ) => ev.date && ev.date >= today )
		.sort( ( a, b ) => ( a.date + a.time ).localeCompare( b.date + b.time ) );
}

/* ---------- Bölümler ---------- */

function eventRow( ev ) {
	const p = dparts( ev.date );
	const off = [ 'EventCancelled', 'EventPostponed' ].includes( ev.status );
	const status = STATUS[ ev.status ] || '';
	let buy = '';
	if ( ev.soldout ) buy += '<span class="event-price is-soldout">Tükendi</span>';
	else if ( ev.price !== '' && ev.price != null ) buy += `<span class="event-price"><b>${ e( price( ev.price, ev.currency ) ) }</b> ile başlayan</span>`;
	if ( ev.ticket_url && ! ev.soldout && ! off ) {
		buy += `<a class="btn btn-solid btn-sm" href="${ e( ev.ticket_url ) }" target="_blank" rel="noopener">Bilet al</a>`;
	} else {
		buy += `<a class="btn btn-line btn-sm" href="https://www.google.com/maps/search/?api=1&amp;query=${ encodeURIComponent( `${ ev.venue } ${ ev.address || '' } ${ ev.city }`.trim() ) }" target="_blank" rel="noopener">Yol tarifi</a>`;
	}
	const anchor = ev.ticket_url && ! off ? ev.ticket_url : '#konserler';
	return `
<li class="event${ off ? ' is-off' : '' }" id="konser-${ e( ev.id ) }">
	<time class="event-date" datetime="${ e( ev.date ) }${ ev.time ? 'T' + e( ev.time ) : '' }"><span class="event-month">${ p.month }</span><span class="event-day">${ p.d }</span></time>
	<div class="event-info">
		<h3 class="event-city"><a href="${ e( anchor ) }"${ /^https?:/.test( anchor ) ? ' target="_blank" rel="noopener"' : '' }>${ e( ev.city ) }</a></h3>
		<p class="event-meta"><span>${ p.wd }${ ev.time ? ' ' + e( ev.time ) : '' }</span><span>${ e( ev.venue ) }</span>${ status ? `<span class="event-status">${ e( status ) }</span>` : '' }</p>
	</div>
	<div class="event-buy">${ buy }</div>
</li>`;
}

function releaseCard( a ) {
	const href = a.apple || a.spotify || a.youtube || '#dinle';
	const credit = a.credit ? `<span class="album-credit">${ e( a.credit ) } ile</span>` : '';
	return `
<li class="album"><a href="${ e( href ) }"${ /^https?:/.test( href ) ? ' target="_blank" rel="noopener"' : '' }>
	<span class="album-cover">${ a.cover ? `<img src="${ e( a.cover ) }" alt="${ e( a.title ) } kapak" loading="lazy" decoding="async" width="600" height="600">` : '' }</span>
	<span class="album-year">${ e( a.year ) }</span><span class="album-title">${ e( a.title ) }</span>${ credit }
</a></li>`;
}

function homeRow( h ) {
	const p = h.date ? dparts( h.date ) : null;
	const href = h.apple || h.spotify || h.youtube || '#dinle';
	return `<li><a href="${ e( href ) }" target="_blank" rel="noopener">${ h.cover ? `<img src="${ e( h.cover ) }" alt="" loading="lazy" width="56" height="56">` : '' }
<span class="home-name">${ e( String( h.title ).replace( /\s*\(Home Concert\)\s*/i, ' ' ).trim() ) }</span><span class="home-year">${ p ? `${ p.month } ${ p.y }` : e( h.year ) }</span></a></li>`;
}

/* ---------- schema.org ---------- */

function schema( site, events ) {
	const s = site.settings;
	const sameAs = Object.keys( PLATFORMS ).map( ( k ) => s.links[ k ] ).filter( Boolean );
	if ( s.links.wikipedia ) sameAs.push( s.links.wikipedia );
	const artistRef = { '@type': [ 'Person', 'MusicGroup' ], '@id': `${ LIVE }/#sanatci`, name: s.artist_name };
	const share = abs( s.seo.share_image || s.about.image );
	const graph = [ {
		...artistRef,
		url: `${ LIVE }/`,
		image: abs( s.about.image || s.seo.share_image ),
		description: s.seo_description,
		genre: s.seo.genre || undefined,
		jobTitle: 'Müzisyen',
		birthDate: s.seo.birth_date || undefined,
		birthPlace: s.seo.birth_place ? { '@type': 'Place', name: s.seo.birth_place } : undefined,
		sameAs,
		contactPoint: s.contact.email || s.contact.phone ? {
			'@type': 'ContactPoint', contactType: 'booking', name: s.contact.manager || undefined,
			email: s.contact.email || undefined, telephone: ( s.contact.phone || '' ).replace( /[^\d+]/g, '' ) || undefined,
		} : undefined,
		album: ( site.releases || [] ).filter( ( r ) => r.type === 'album' )
			.map( ( r ) => ( { '@type': 'MusicAlbum', name: r.title, datePublished: String( r.year ), url: r.apple || undefined } ) ),
	}, {
		'@type': 'WebSite', '@id': `${ LIVE }/#site`, url: `${ LIVE }/`, name: s.artist_name, inLanguage: 'tr', publisher: { '@id': `${ LIVE }/#sanatci` },
	} ];
	for ( const ev of events ) {
		const node = {
			'@type': 'MusicEvent',
			'@id': `${ LIVE }/#konser-${ ev.id }`,
			name: `${ s.artist_name } ${ ev.city } Konseri`,
			url: `${ LIVE }/#konser-${ ev.id }`,
			startDate: `${ ev.date }T${ ev.time || '20:00' }:00+03:00`,
			eventStatus: `https://schema.org/${ ev.status || 'EventScheduled' }`,
			eventAttendanceMode: 'https://schema.org/OfflineEventAttendanceMode',
			location: { '@type': 'Place', name: ev.venue, address: { '@type': 'PostalAddress', streetAddress: ev.address || undefined, addressLocality: ev.city, addressCountry: 'TR' } },
			image: [ share ],
			description: `${ s.artist_name }, ${ ev.venue } (${ ev.city }) konseri.`,
			performer: artistRef,
			organizer: { '@type': 'Organization', name: s.artist_name, url: `${ LIVE }/` },
		};
		if ( ev.ticket_url || ( ev.price !== '' && ev.price != null ) ) {
			node.offers = {
				'@type': 'Offer',
				url: ev.ticket_url || `${ LIVE }/#konserler`,
				availability: `https://schema.org/${ ev.soldout ? 'SoldOut' : 'InStock' }`,
				price: ev.price !== '' && ev.price != null ? String( Number( String( ev.price ).replace( ',', '.' ) ) ) : undefined,
				priceCurrency: ev.currency || 'TRY',
				seller: ev.vendor ? { '@type': 'Organization', name: ev.vendor } : undefined,
			};
		}
		graph.push( node );
	}
	return JSON.stringify( { '@context': 'https://schema.org', '@graph': graph } ).replace( /</g, '\\u003c' );
}

/* ---------- Sayfa ---------- */

export function renderPage( site, videos = [], today = todayIstanbul() ) {
	const s = site.settings;
	const sec = s.sections;
	const h = s.hero;
	const ytId = youtubeId( h.youtube );
	const events = upcomingEvents( site, today );
	const filled = Object.entries( PLATFORMS ).filter( ( [ k ] ) => s.links[ k ] );
	const music = filled.filter( ( [ , p ] ) => p[ 2 ] );
	const linkIcon = ( size, rel = 'noopener' ) => ( [ k, p ] ) => `<a href="${ e( s.links[ k ] ) }" target="_blank" rel="${ rel }" aria-label="${ e( p[ 0 ] ) }">${ icon( p[ 1 ], size ) }</a>`;
	const spotify = spotifyArtist( s.links.spotify );

	const releases = [ ...( site.releases || [] ) ].sort( ( a, b ) => ( b.date || String( b.year ) ).localeCompare( a.date || String( a.year ) ) );
	const singles = releases.filter( ( r ) => r.type === 'single' );
	const albums = releases.filter( ( r ) => r.type === 'album' );
	const homes = releases.filter( ( r ) => r.type === 'home' );

	const next = events[ 0 ];
	const nextHtml = next
		? `<a class="hero-next" href="#konserler"><span class="hero-next-label">Sıradaki konser</span><span class="hero-next-body"><b>${ dparts( next.date ).d } ${ dparts( next.date ).month }</b> · ${ e( next.city ) } · ${ e( next.venue ) }</span></a>`
		: '<span></span>';

	const menu = [
		[ 'konserler', sec.events ], [ 'dinle', sec.listen ], [ 'albumler', sec.albums ], [ 'hakkinda', sec.about ],
		[ 'videolar', sec.videos ], [ 'galeri', sec.gallery ], [ 'iletisim', sec.contact ],
	].filter( ( [ , t ] ) => t ).map( ( [ id, t ] ) => `<li><a href="#${ id }">${ e( t ) }</a></li>` ).join( '\n\t\t\t\t' );

	const nameParts = String( s.artist_name ).trim().split( ' ' );
	const last = nameParts.pop();

	const videoItems = videos.slice( 0, Math.max( 1, Number( s.videos.limit ) || 6 ) ).map( ( v, i ) => {
		const thumb = `https://i.ytimg.com/vi/${ v.id }/${ i === 0 ? 'maxresdefault' : 'hqdefault' }.jpg`;
		const clean = String( v.title || '' ).replace( s.artist_name, '' ).replace( /^[\s-]+/, '' ).trim();
		return `
<li class="video${ i === 0 ? ' is-featured' : '' }">
	<button class="video-lite" type="button" data-yt-lite="${ e( v.id ) }" aria-label="${ e( v.title || 'Videoyu oynat' ) }"><img src="${ thumb }" alt="" loading="lazy" decoding="async"><span class="video-play">${ icon( 'play', 28 ) }</span></button>
	${ clean ? `<p class="video-title">${ e( clean ) }</p>` : '' }
</li>`;
	} ).join( '' );

	const gallery = ( site.gallery || [] ).map( ( g ) => `<li><a href="${ e( g.full || g.src ) }" data-lightbox><img src="${ e( g.src ) }" alt="${ e( g.alt || s.artist_name ) }" loading="lazy" decoding="async"${ g.w ? ` width="${ g.w }" height="${ g.h }"` : '' }></a></li>` ).join( '' );

	const contact = s.contact;
	const share = abs( s.seo.share_image || s.about.image );
	const year = today.slice( 0, 4 );

	return `<!doctype html>
<html lang="tr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
	<title>${ e( s.artist_name ) } — Resmi Web Sitesi</title>
	<meta name="robots" content="index, follow, max-image-preview:large">
	<link rel="canonical" href="${ LIVE }/">
	<meta name="description" content="${ e( s.seo_description ) }">
	<meta name="google-site-verification" content="QuOWnppoDTATHLx4YbSR5X2xe5wefEDSdI4ZLtEAQ70">
	<meta property="og:locale" content="tr_TR">
	<meta property="og:type" content="website">
	<meta property="og:site_name" content="${ e( s.artist_name ) }">
	<meta property="og:url" content="${ LIVE }/">
	<meta property="og:title" content="${ e( s.artist_name ) } — Resmi Web Sitesi">
	<meta property="og:description" content="${ e( s.seo_description ) }">
	<meta property="og:image" content="${ e( share ) }">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:image" content="${ e( share ) }">
	<link rel="icon" href="/favicon.ico" sizes="any">
	<link rel="icon" href="/favicon.svg" type="image/svg+xml">
	<link rel="icon" href="/favicon-48.png" sizes="48x48" type="image/png">
	<link rel="icon" href="/icon-192.png" sizes="192x192" type="image/png">
	<link rel="apple-touch-icon" href="/apple-touch-icon.png">
	<link rel="manifest" href="/site.webmanifest">
	<meta name="theme-color" content="#0f0d0b">
	<link rel="preconnect" href="https://www.youtube-nocookie.com">
	<link rel="preconnect" href="https://www.google.com">
	<link rel="preconnect" href="https://i.ytimg.com">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Manrope:wght@400;500;600;700&display=swap">
	<link rel="stylesheet" href="/style.css">
	<script type="application/ld+json">${ schema( site, events ) }</script>
</head>
<body class="home">
<a class="skip-link" href="#icerik">İçeriğe geç</a>

<header class="site-header" data-header>
	<div class="wrap header-inner">
		<a class="brand" href="/" rel="home"><span class="brand-text">${ e( nameParts.join( ' ' ) ) } <b>${ e( last ) }</b></span></a>
		<nav class="primary-nav" id="primary-nav" aria-label="Ana menü">
			<ul class="menu">
				${ menu }
			</ul>
			<div class="nav-platforms">${ filled.map( linkIcon( 18, 'noopener me' ) ).join( '' ) }</div>
		</nav>
		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" data-nav-toggle>
			<span class="screen-reader-text">Menü</span><span class="nav-toggle-bar"></span>
		</button>
	</div>
</header>

<main id="icerik">
<section class="hero" data-hero="${ ytId ? 'youtube' : 'image' }" data-start="${ Number( h.start ) || 0 }" data-end="${ Number( h.end ) || 0 }" aria-label="${ e( h.title ) }">
	<div class="hero-media">
		${ ytId ? `<img class="hero-poster" data-src="${ e( h.poster ) }" alt="" decoding="async">\n\t\t<div class="hero-yt" data-yt="${ ytId }"></div>` : `<img class="hero-poster" src="${ e( h.poster ) }" alt="" fetchpriority="high" decoding="async">` }
	</div>
	<div class="hero-shade" style="--overlay:${ Math.min( 90, Math.max( 0, Number( h.overlay ) || 0 ) ) / 100 }"></div>
	<div class="wrap hero-content">
		${ h.kicker ? `<p class="hero-kicker">${ e( h.kicker ) }</p>` : '' }
		<h1 class="hero-title">${ e( h.title ) }</h1>
		${ h.text ? `<p class="hero-text">${ e( h.text ) }</p>` : '' }
		<div class="hero-actions">
			${ h.btn1_text ? `<a class="btn btn-solid" href="${ e( h.btn1_url ) }">${ e( h.btn1_text ) }</a>` : '' }
			${ h.btn2_text ? `<a class="btn btn-ghost" href="${ e( h.btn2_url ) }">${ icon( 'play', 16 ) }${ e( h.btn2_text ) }</a>` : '' }
		</div>
	</div>
	<div class="wrap hero-foot">
		${ nextHtml }
		<div class="hero-platforms">
			${ music.map( linkIcon( 20 ) ).join( '' ) }
			${ ytId ? `<button class="hero-toggle" type="button" data-hero-toggle aria-pressed="false" aria-label="Arka plan videosunu durdur">${ icon( 'pause', 18 ) }${ icon( 'play', 18 ) }</button>` : '' }
		</div>
	</div>
</section>
${ sec.events ? `
<section class="section section-events" id="konserler">
	<div class="wrap">
		${ sectionHead( 'Takvim', sec.events ) }
		${ events.length ? `<ol class="event-list">${ events.map( eventRow ).join( '' ) }</ol>` : '' }
		<p class="empty-note" data-events-empty${ events.length ? ' hidden' : '' }>${ e( s.events_empty ) }</p>
	</div>
</section>` : '' }
${ sec.listen ? `
<section class="section section-listen" id="dinle">
	<div class="wrap listen-grid${ spotify ? '' : ' no-embed' }">
		<div class="listen-copy">
			${ sectionHead( 'Dijital platformlar', sec.listen ) }
			${ s.listen_text ? `<p class="lead">${ e( s.listen_text ) }</p>` : '' }
			<ul class="platform-list">${ music.map( ( [ k, p ] ) => `<li><a class="platform platform-${ k }" href="${ e( s.links[ k ] ) }" target="_blank" rel="noopener">${ icon( p[ 1 ], 24 ) }<span class="platform-name">${ e( p[ 0 ] ) }</span><span class="platform-go">${ k === 'youtube' ? 'İzle' : 'Dinle' } ${ icon( 'arrow', 14 ) }</span></a></li>` ).join( '' ) }</ul>
		</div>
		${ spotify ? `<div class="listen-embed">
			<iframe title="Spotify oynatıcısı" src="https://open.spotify.com/embed/artist/${ spotify }?utm_source=generator&amp;theme=0" width="100%" height="460" loading="lazy" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
		</div>` : '' }
	</div>
</section>` : '' }
${ sec.albums && releases.length ? `
<section class="section section-albums" id="albumler">
	<div class="wrap">
		${ sectionHead( `Diskografi · ${ releases.length } yayın`, sec.albums ) }
		${ singles.length ? `<div class="disco-group"><h3 class="disco-title">Single’lar <span>${ singles.length }</span></h3><ul class="album-grid is-singles">${ singles.map( releaseCard ).join( '' ) }</ul></div>` : '' }
		${ albums.length ? `<div class="disco-group"><h3 class="disco-title">Stüdyo albümleri <span>${ albums.length }</span></h3><ul class="album-grid">${ albums.map( releaseCard ).join( '' ) }</ul></div>` : '' }
		${ homes.length ? `<div class="disco-group"><h3 class="disco-title">Home Concert serisi <span>${ homes.length }</span></h3><ul class="home-list">${ homes.map( homeRow ).join( '' ) }</ul></div>` : '' }
	</div>
</section>` : '' }
${ sec.about ? `
<section class="section section-about" id="hakkinda">
	<div class="wrap about-grid${ s.about.image ? '' : ' no-image' }">
		${ s.about.image ? `<figure class="about-media"><img src="${ e( s.about.image ) }" alt="${ e( s.artist_name ) }" loading="lazy"></figure>` : '' }
		<div class="about-copy">
			${ sectionHead( 'Biyografi', sec.about ) }
			${ s.about.quote ? `<blockquote class="about-quote">${ e( s.about.quote ) }</blockquote>` : '' }
			<div class="prose">${ paras( s.about.text ) }</div>
		</div>
	</div>
</section>` : '' }
${ sec.videos && videoItems ? `
<section class="section section-videos" id="videolar">
	<div class="wrap">
		${ sectionHead( 'YouTube', sec.videos, s.links.youtube, 'Kanala abone ol' ) }
		<ul class="video-grid">${ videoItems }</ul>
	</div>
</section>` : '' }
${ sec.gallery && gallery ? `
<section class="section section-gallery" id="galeri">
	<div class="wrap">
		${ sectionHead( 'Fotoğraflar', sec.gallery ) }
		<ul class="gallery-grid" data-gallery>${ gallery }</ul>
	</div>
</section>` : '' }
${ sec.contact ? `
<section class="section section-contact" id="iletisim">
	<div class="wrap contact-grid no-form">
		<div class="contact-copy">
			${ sectionHead( 'Konser ve organizasyon', sec.contact ) }
			${ contact.text ? `<p class="lead">${ e( contact.text ) }</p>` : '' }
			<div class="contact-card">
				${ contact.manager ? `<p class="contact-role">Menajer</p><p class="contact-name">${ e( contact.manager ) }</p>` : '' }
				${ contact.phone ? `<a class="contact-line" href="tel:${ e( contact.phone.replace( /[^\d+]/g, '' ) ) }">${ icon( 'phone', 18 ) }${ e( contact.phone ) }</a>` : '' }
				${ contact.email ? `<a class="contact-line" href="mailto:${ e( contact.email ) }">${ icon( 'mail', 18 ) }${ e( contact.email ) }</a>` : '' }
			</div>
		</div>
	</div>
</section>` : '' }
</main>

<footer class="site-footer">
	<div class="wrap footer-inner">
		<p class="footer-name">${ e( s.artist_name ) }</p>
		<ul class="footer-platforms">${ filled.map( ( [ k, p ] ) => `<li><a href="${ e( s.links[ k ] ) }" target="_blank" rel="noopener me">${ icon( p[ 1 ], 18 ) }<span>${ e( p[ 0 ] ) }</span></a></li>` ).join( '' ) }</ul>
		<div class="footer-bottom"><p>${ e( String( s.footer_text || '' ).replace( '{yil}', year ) ) }</p></div>
	</div>
</footer>

<button class="to-top" type="button" data-to-top aria-label="Sayfanın başına dön">
	<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 5.2 4.6 12.6l1.4 1.4L11 9V20h2V9l5 5 1.4-1.4z"/></svg>
</button>

<script src="/main.js" defer></script>
</body>
</html>
`;
}
