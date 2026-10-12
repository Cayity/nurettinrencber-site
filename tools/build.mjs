// Vercel derlemesi: data/site.json → preview/index.html (+ sitemap, robots, CSS/JS kopyaları).
// Kullanım: node tools/build.mjs

import { readFileSync, writeFileSync, copyFileSync, existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import { renderPage, youtubeId, LIVE, todayIstanbul } from './render.mjs';

const ROOT = path.resolve( path.dirname( fileURLToPath( import.meta.url ) ), '..' );
const OUT = path.join( ROOT, 'preview' );
const CACHE = path.join( ROOT, 'data', 'videos-cache.json' );

const site = JSON.parse( readFileSync( path.join( ROOT, 'data', 'site.json' ), 'utf8' ) );

/* YouTube kanalındaki en yeni videolar (API anahtarı gerekmez). Ulaşılamazsa son başarılı liste kullanılır. */
async function channelVideos() {
	const m = String( site.settings.links.youtube || '' ).match( /youtube\.com\/channel\/(UC[\w-]{22})/ );
	if ( ! m ) return [];
	try {
		const res = await fetch( `https://www.youtube.com/feeds/videos.xml?channel_id=${ m[ 1 ] }`, { signal: AbortSignal.timeout( 8000 ) } );
		if ( ! res.ok ) throw new Error( 'HTTP ' + res.status );
		const xml = await res.text();
		const list = [ ...xml.matchAll( /<entry>[\s\S]*?<yt:videoId>([\w-]{11})<\/yt:videoId>[\s\S]*?<title>([^<]*)<\/title>/g ) ]
			.map( ( x ) => ( { id: x[ 1 ], title: x[ 2 ].replace( /&amp;/g, '&' ).replace( /&quot;/g, '"' ).replace( /&#39;/g, "'" ) } ) );
		if ( list.length ) {
			writeFileSync( CACHE, JSON.stringify( list, null, 1 ) );
			return list;
		}
	} catch ( err ) {
		console.warn( 'YouTube akışı alınamadı, önbellek kullanılıyor:', err.message );
	}
	return existsSync( CACHE ) ? JSON.parse( readFileSync( CACHE, 'utf8' ) ) : [];
}

const v = site.settings.videos || {};
const videos = v.source === 'manual'
	? ( v.manual || [] ).map( ( x ) => ( { id: youtubeId( x.url || x ), title: x.title || '' } ) ).filter( ( x ) => x.id )
	: await channelVideos();

const today = todayIstanbul();
writeFileSync( path.join( OUT, 'index.html' ), renderPage( site, videos, today ) );

writeFileSync( path.join( OUT, 'robots.txt' ), `User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /api/\n\nSitemap: ${ LIVE }/sitemap.xml\n` );
writeFileSync( path.join( OUT, 'sitemap.xml' ),
	'<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' +
	`  <url><loc>${ LIVE }/</loc><lastmod>${ today }</lastmod><changefreq>weekly</changefreq></url>\n</urlset>\n` );

// Tasarım dosyaları tek kaynaktan: WordPress teması.
copyFileSync( path.join( ROOT, 'rencber', 'style.css' ), path.join( OUT, 'style.css' ) );
copyFileSync( path.join( ROOT, 'rencber', 'assets', 'js', 'main.js' ), path.join( OUT, 'main.js' ) );

console.log( `Derlendi: ${ site.events.length } konser (${ today } sonrası gösterilir), ${ site.releases.length } yayın, ${ videos.length } video, ${ site.gallery.length } fotoğraf.` );
