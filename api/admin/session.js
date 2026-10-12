// Panel açılırken oturum ve kurulum durumunu bildirir (değerler asla gösterilmez, yalnızca eksik olanın adı).
import { isAdmin, handler } from '../../lib/auth.js';

export default handler( async ( req, res ) => {
	const missing = [];
	const pw = process.env.ADMIN_PASSWORD || '';
	if ( ! pw ) missing.push( 'ADMIN_PASSWORD tanımlı değil' );
	else if ( pw.length < 10 ) missing.push( 'ADMIN_PASSWORD en az 10 karakter olmalı' );
	if ( ! process.env.GITHUB_TOKEN ) missing.push( 'GITHUB_TOKEN tanımlı değil' );
	const configured = missing.length === 0;
	let admin = false;
	try { admin = configured && isAdmin( req ); } catch { admin = false; }
	res.status( 200 ).json( { configured, admin, missing } );
} );
