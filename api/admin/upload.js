// Görsel yükleme: tarayıcıda küçültülmüş WebP/JPEG doğrudan depoya (preview/uploads/…) yazılır.
import { randomBytes } from 'node:crypto';
import { requireAdmin, handler } from '../../lib/auth.js';
import { putFile } from '../../lib/github.js';

const MAX = 3 * 1024 * 1024; // 3 MB (görseller tarayıcıda zaten küçültülüyor)
const TYPES = { 'image/webp': 'webp', 'image/jpeg': 'jpg', 'image/png': 'png' };

const slug = ( s ) => String( s || 'gorsel' ).toLocaleLowerCase( 'tr' )
	.replace( /ç/g, 'c' ).replace( /ğ/g, 'g' ).replace( /ı/g, 'i' ).replace( /ö/g, 'o' ).replace( /ş/g, 's' ).replace( /ü/g, 'u' )
	.replace( /\.[a-z0-9]+$/, '' ).replace( /[^a-z0-9]+/g, '-' ).replace( /^-|-$/g, '' ).slice( 0, 40 ) || 'gorsel';


export default handler( async ( req, res ) => {
	if ( ! requireAdmin( req, res ) ) return;
	if ( req.method !== 'POST' ) return res.status( 405 ).json( { error: 'POST gerekli.' } );

	const { name, type, data } = req.body || {};
	const ext = TYPES[ type ];
	if ( ! ext || typeof data !== 'string' ) return res.status( 400 ).json( { error: 'Yalnızca WebP, JPEG veya PNG yüklenebilir.' } );
	const buf = Buffer.from( data, 'base64' );
	if ( ! buf.length || buf.length > MAX ) return res.status( 413 ).json( { error: 'Görsel 3 MB’tan büyük olamaz.' } );

	// Dosya imzası kontrolü (uzantı/başlık taklidine karşı).
	const sig = buf.subarray( 0, 12 );
	const ok = ( ext === 'webp' && sig.toString( 'ascii', 0, 4 ) === 'RIFF' && sig.toString( 'ascii', 8, 12 ) === 'WEBP' )
		|| ( ext === 'jpg' && sig[ 0 ] === 0xff && sig[ 1 ] === 0xd8 )
		|| ( ext === 'png' && sig[ 0 ] === 0x89 && sig.toString( 'ascii', 1, 4 ) === 'PNG' );
	if ( ! ok ) return res.status( 400 ).json( { error: 'Dosya geçerli bir görsel değil.' } );

	const now = new Date();
	const rel = `uploads/${ now.getUTCFullYear() }/${ String( now.getUTCMonth() + 1 ).padStart( 2, '0' ) }/${ slug( name ) }-${ randomBytes( 3 ).toString( 'hex' ) }.${ ext }`;
	await putFile( `preview/${ rel }`, buf.toString( 'base64' ), `Panel: görsel yüklendi (${ rel })` );
	res.status( 200 ).json( { ok: true, path: rel } );
} );
