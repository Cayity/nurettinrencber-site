// Yerel test sunucusu: preview/ klasörünü ve /api uçlarını Vercel'e benzer şekilde çalıştırır.
// GitHub yerine yerel diske yazar. Kullanım:
//   ADMIN_PASSWORD=... node tools/dev-server.mjs   →  http://localhost:5174/admin
import http from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const ROOT = path.resolve( path.dirname( fileURLToPath( import.meta.url ) ), '..' );
process.env.GITHUB_LOCAL_DIR ||= ROOT;
process.env.GITHUB_TOKEN ||= 'yerel';
const PORT = Number( process.env.PORT || 5174 );
const MIME = { html: 'text/html; charset=utf-8', css: 'text/css', js: 'text/javascript', json: 'application/json', svg: 'image/svg+xml', webp: 'image/webp', jpg: 'image/jpeg', png: 'image/png', ico: 'image/x-icon', txt: 'text/plain', xml: 'application/xml' };

function decorate( res ) {
	res.status = ( c ) => { res.statusCode = c; return res; };
	res.json = ( o ) => { res.setHeader( 'Content-Type', 'application/json' ); res.end( JSON.stringify( o ) ); return res; };
	return res;
}

http.createServer( async ( req, res ) => {
	decorate( res );
	const url = new URL( req.url, 'http://localhost' );
	try {
		if ( url.pathname.startsWith( '/api/' ) ) {
			const file = path.join( ROOT, url.pathname + '.js' );
			const mod = await import( pathToFileURL( file ).href + '?t=' + Date.now() );
			let body = '';
			for await ( const chunk of req ) body += chunk;
			req.body = body && /json/.test( req.headers[ 'content-type' ] || '' ) ? JSON.parse( body ) : {};
			// Yerelde Secure çerez http'de çalışmaz: Secure bayrağını kaldır.
			const set = res.setHeader.bind( res );
			res.setHeader = ( k, v ) => set( k, k.toLowerCase() === 'set-cookie' ? String( v ).replace( '; Secure', '' ) : v );
			return await mod.default( req, res );
		}
		let p = decodeURIComponent( url.pathname );
		if ( p.endsWith( '/' ) ) p += 'index.html';
		let full = path.join( ROOT, 'preview', p );
		if ( ! path.extname( full ) ) full += ( await stat( full ).then( ( s ) => s.isDirectory() ).catch( () => false ) ) ? '/index.html' : '.html';
		const buf = await readFile( full );
		res.setHeader( 'Content-Type', MIME[ path.extname( full ).slice( 1 ) ] || 'application/octet-stream' );
		res.end( buf );
	} catch ( err ) {
		if ( err.code === 'ENOENT' ) { res.statusCode = 404; return res.end( 'Bulunamadı' ); }
		console.error( err );
		res.statusCode = 500; res.end( 'Hata' );
	}
} ).listen( PORT, () => console.log( `http://localhost:${ PORT }  (admin: /admin)` ) );
