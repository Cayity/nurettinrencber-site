// Panelden gelen site verisini temizler: beklenmeyen alanlar atılır, metinler kısaltılır,
// bağlantılar yalnızca http(s) olabilir. Böylece bozuk bir kayıt siteyi kıramaz.

const bad = ( msg ) => Object.assign( new Error( msg ), { status: 400, expose: true } );

const str = ( v, max = 300 ) => String( v ?? '' ).replace( /\u0000/g, '' ).trim().slice( 0, max );
const txt = ( v, max = 8000 ) => String( v ?? '' ).replace( /\r\n/g, '\n' ).replace( /\u0000/g, '' ).trim().slice( 0, max );
const url = ( v ) => {
	const s = str( v, 600 );
	if ( ! s ) return '';
	if ( s.startsWith( '#' ) ) return s;
	return /^https?:\/\/[^\s"<>]+$/i.test( s ) ? s : '';
};
// Site içi görsel yolu: img/… ya da uploads/… (dışarıdan https görsel de olabilir).
const img = ( v ) => {
	const s = str( v, 600 );
	if ( /^(img|uploads)\/[\w./-]+\.(webp|jpe?g|png|svg)$/i.test( s ) && ! s.includes( '..' ) ) return s;
	return /^https:\/\/[^\s"<>]+$/i.test( s ) ? s : '';
};
const int = ( v, min, max, def = 0 ) => {
	const n = parseInt( v, 10 );
	return Number.isFinite( n ) ? Math.min( max, Math.max( min, n ) ) : def;
};
const date = ( v ) => ( /^\d{4}-\d{2}-\d{2}$/.test( String( v ) ) ? String( v ) : '' );
const time = ( v ) => ( /^\d{2}:\d{2}$/.test( String( v ) ) ? String( v ) : '' );
const id = ( v, prefix ) => ( /^[\w-]{1,40}$/.test( String( v ) ) ? String( v ) : prefix + Math.random().toString( 36 ).slice( 2, 9 ) );
const oneOf = ( v, list, def ) => ( list.includes( v ) ? v : def );

const LINK_KEYS = [ 'spotify', 'apple', 'youtube', 'youtube_music', 'deezer', 'instagram', 'facebook', 'x', 'tiktok', 'wikipedia' ];
const SECTION_KEYS = [ 'events', 'listen', 'albums', 'about', 'videos', 'gallery', 'contact' ];
const STATUSES = [ 'EventScheduled', 'EventPostponed', 'EventRescheduled', 'EventCancelled' ];

export function cleanSite( input ) {
	if ( ! input || typeof input !== 'object' ) throw bad( 'Geçersiz veri.' );
	const s = input.settings || {};
	const h = s.hero || {};
	const about = s.about || {};
	const contact = s.contact || {};
	const seo = s.seo || {};
	const videos = s.videos || {};

	const events = ( Array.isArray( input.events ) ? input.events : [] ).slice( 0, 300 ).map( ( ev ) => {
		const out = {
			id: id( ev.id, 'e' ),
			date: date( ev.date ),
			time: time( ev.time ),
			city: str( ev.city, 80 ),
			venue: str( ev.venue, 160 ),
			address: str( ev.address, 240 ),
			ticket_url: url( ev.ticket_url ),
			vendor: str( ev.vendor, 60 ),
			price: str( ev.price, 12 ).replace( /[^\d.,]/g, '' ),
			currency: oneOf( ev.currency, [ 'TRY', 'EUR', 'USD', 'GBP' ], 'TRY' ),
			status: oneOf( ev.status, STATUSES, 'EventScheduled' ),
			soldout: Boolean( ev.soldout ),
		};
		if ( ! out.date || ! out.city || ! out.venue ) throw bad( 'Her konserde tarih, şehir ve mekân zorunludur.' );
		return out;
	} );

	const releases = ( Array.isArray( input.releases ) ? input.releases : [] ).slice( 0, 400 ).map( ( r ) => {
		const out = {
			id: id( r.id, 'r' ),
			title: str( r.title, 160 ),
			type: oneOf( r.type, [ 'album', 'single', 'home' ], 'single' ),
			date: date( r.date ),
			year: int( r.year || String( r.date ).slice( 0, 4 ), 1950, 2100, new Date().getFullYear() ),
			credit: str( r.credit, 120 ),
			label: str( r.label, 120 ),
			apple: url( r.apple ),
			spotify: url( r.spotify ),
			youtube: url( r.youtube ),
			cover: img( r.cover ),
			tracks: ( Array.isArray( r.tracks ) ? r.tracks : String( r.tracks || '' ).split( '\n' ) ).map( ( t ) => str( t, 160 ) ).filter( Boolean ).slice( 0, 60 ),
		};
		if ( ! out.title ) throw bad( 'Her yayının bir adı olmalı.' );
		if ( ! out.date ) out.date = `${ out.year }-01-01`;
		return out;
	} );

	const gallery = ( Array.isArray( input.gallery ) ? input.gallery : [] ).slice( 0, 200 ).map( ( g ) => ( {
		src: img( g.src ),
		full: img( g.full ) || img( g.src ),
		alt: str( g.alt, 160 ),
		w: int( g.w, 0, 10000 ),
		h: int( g.h, 0, 10000 ),
	} ) ).filter( ( g ) => g.src );

	const links = {};
	for ( const k of LINK_KEYS ) links[ k ] = url( ( s.links || {} )[ k ] );
	const sections = {};
	for ( const k of SECTION_KEYS ) sections[ k ] = str( ( s.sections || {} )[ k ], 40 ); // boş = bölüm gizli

	return {
		settings: {
			artist_name: str( s.artist_name, 80 ) || 'Nurettin Rençber',
			seo_description: str( s.seo_description, 300 ),
			hero: {
				youtube: url( h.youtube ),
				start: int( h.start, 0, 36000 ),
				end: int( h.end, 0, 36000 ),
				poster: img( h.poster ),
				overlay: int( h.overlay, 0, 90, 55 ),
				kicker: str( h.kicker, 80 ),
				title: str( h.title, 80 ),
				text: str( h.text, 300 ),
				btn1_text: str( h.btn1_text, 40 ),
				btn1_url: url( h.btn1_url ),
				btn2_text: str( h.btn2_text, 40 ),
				btn2_url: url( h.btn2_url ),
			},
			links,
			sections,
			events_empty: str( s.events_empty, 200 ),
			listen_text: str( s.listen_text, 300 ),
			about: { image: img( about.image ), quote: txt( about.quote, 400 ), text: txt( about.text, 12000 ) },
			videos: {
				source: oneOf( videos.source, [ 'channel', 'manual' ], 'channel' ),
				limit: int( videos.limit, 1, 15, 6 ),
				manual: ( Array.isArray( videos.manual ) ? videos.manual : [] ).slice( 0, 30 ).map( ( v ) => ( { url: url( v.url || v ), title: str( v.title, 160 ) } ) ).filter( ( v ) => v.url ),
			},
			contact: { text: str( contact.text, 400 ), manager: str( contact.manager, 80 ), phone: str( contact.phone, 40 ), email: str( contact.email, 120 ) },
			seo: { genre: str( seo.genre, 80 ), birth_date: str( seo.birth_date, 20 ), birth_place: str( seo.birth_place, 80 ), share_image: img( seo.share_image ) },
			footer_text: str( s.footer_text, 200 ),
		},
		events,
		releases,
		gallery,
	};
}
