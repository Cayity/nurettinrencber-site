import { clearCookie, handler } from '../../lib/auth.js';

export default handler( async ( req, res ) => {
	res.setHeader( 'Set-Cookie', clearCookie() );
	res.status( 200 ).json( { ok: true } );
} );
