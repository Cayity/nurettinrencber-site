// Panel açılırken oturum ve kurulum durumunu bildirir.
import { isAdmin, handler } from '../../lib/auth.js';

export default handler( async ( req, res ) => {
	const configured = ( process.env.ADMIN_PASSWORD || '' ).length >= 10 && Boolean( process.env.GITHUB_TOKEN );
	let admin = false;
	try { admin = configured && isAdmin( req ); } catch { admin = false; }
	res.status( 200 ).json( { configured, admin } );
} );
