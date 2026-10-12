// Admin oturumu: tek yönetici şifresi (Vercel ortam değişkeni ADMIN_PASSWORD) ve imzalı, HttpOnly çerez.
// Şifre değişince tüm oturumlar kendiliğinden geçersiz olur (imza anahtarı şifreden türetilir).

import { createHmac, createHash, timingSafeEqual } from 'node:crypto';

const COOKIE = 'rb_admin';
const MAX_AGE = 60 * 60 * 24 * 7; // 7 gün

function secretKey() {
	const pw = process.env.ADMIN_PASSWORD || '';
	if ( pw.length < 10 ) {
		throw Object.assign( new Error( 'ADMIN_PASSWORD ortam değişkeni ayarlanmamış (en az 10 karakter).' ), { status: 500 } );
	}
	return createHmac( 'sha256', pw ).update( 'rencber-admin-v1' ).digest();
}

const b64u = ( buf ) => Buffer.from( buf ).toString( 'base64url' );
const sign = ( data ) => b64u( createHmac( 'sha256', secretKey() ).update( data ).digest() );

function safeEqual( a, b ) {
	const ha = createHash( 'sha256' ).update( String( a ) ).digest();
	const hb = createHash( 'sha256' ).update( String( b ) ).digest();
	return timingSafeEqual( ha, hb );
}

export function checkPassword( given ) {
	secretKey(); // ayar yoksa anlaşılır hata ver
	return safeEqual( given || '', process.env.ADMIN_PASSWORD );
}

export function sessionCookie() {
	const payload = b64u( JSON.stringify( { exp: Math.floor( Date.now() / 1000 ) + MAX_AGE } ) );
	const token = `${ payload }.${ sign( payload ) }`;
	return `${ COOKIE }=${ token }; Path=/; HttpOnly; Secure; SameSite=Strict; Max-Age=${ MAX_AGE }`;
}

export const clearCookie = () => `${ COOKIE }=; Path=/; HttpOnly; Secure; SameSite=Strict; Max-Age=0`;

function readCookie( req ) {
	const raw = req.headers.cookie || '';
	for ( const part of raw.split( ';' ) ) {
		const [ k, ...v ] = part.trim().split( '=' );
		if ( k === COOKIE ) return v.join( '=' );
	}
	return '';
}

export function isAdmin( req ) {
	const token = readCookie( req );
	const [ payload, sig ] = token.split( '.' );
	if ( ! payload || ! sig ) return false;
	try {
		if ( ! safeEqual( sig, sign( payload ) ) ) return false;
		const { exp } = JSON.parse( Buffer.from( payload, 'base64url' ).toString() );
		return exp > Date.now() / 1000;
	} catch {
		return false;
	}
}

/**
 * Oturum + CSRF kontrolü. Değişiklik yapan isteklerde tarayıcının başka siteden
 * gönderemeyeceği özel bir başlık istenir (SameSite=Strict çereze ek koruma).
 */
export function requireAdmin( req, res ) {
	if ( ! isAdmin( req ) ) {
		res.status( 401 ).json( { error: 'Oturum açmanız gerekiyor.' } );
		return false;
	}
	if ( req.method !== 'GET' && req.headers[ 'x-rencber-admin' ] !== '1' ) {
		res.status( 403 ).json( { error: 'Geçersiz istek.' } );
		return false;
	}
	return true;
}

/* Hataları JSON olarak döndüren sarmalayıcı. */
export function handler( fn ) {
	return async ( req, res ) => {
		res.setHeader( 'Cache-Control', 'no-store' );
		res.setHeader( 'X-Robots-Tag', 'noindex' );
		try {
			await fn( req, res );
		} catch ( err ) {
			console.error( err );
			res.status( err.status || 500 ).json( { error: err.expose || err.status ? err.message : 'Sunucu hatası.' } );
		}
	};
}
