import { checkPassword, sessionCookie, handler } from '../../lib/auth.js';

// Basit kaba kuvvet freni: hatalı denemede yanıt gecikir.
const sleep = ( ms ) => new Promise( ( r ) => setTimeout( r, ms ) );

export default handler( async ( req, res ) => {
	if ( req.method !== 'POST' ) return res.status( 405 ).json( { error: 'POST gerekli.' } );
	const { password } = req.body || {};
	if ( ! checkPassword( password ) ) {
		await sleep( 1200 );
		return res.status( 401 ).json( { error: 'Şifre yanlış.' } );
	}
	res.setHeader( 'Set-Cookie', sessionCookie() );
	res.status( 200 ).json( { ok: true } );
} );
