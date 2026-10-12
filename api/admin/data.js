// GET: güncel site verisi (GitHub'daki son hali) · PUT: kaydet ve yayınla.
import { requireAdmin, handler } from '../../lib/auth.js';
import { getFile, putFile } from '../../lib/github.js';
import { cleanSite } from '../../lib/validate.js';

const FILE = 'data/site.json';

export default handler( async ( req, res ) => {
	if ( ! requireAdmin( req, res ) ) return;

	if ( req.method === 'GET' ) {
		const file = await getFile( FILE );
		if ( ! file ) return res.status( 404 ).json( { error: 'Veri dosyası bulunamadı.' } );
		return res.status( 200 ).json( { sha: file.sha, site: JSON.parse( file.content ) } );
	}

	if ( req.method === 'PUT' ) {
		const { site, sha, note } = req.body || {};
		if ( ! sha ) return res.status( 400 ).json( { error: 'Sürüm bilgisi eksik.' } );
		const clean = cleanSite( site );
		const json = JSON.stringify( clean, null, 1 ) + '\n';
		if ( json.length > 800000 ) return res.status( 413 ).json( { error: 'Veri çok büyük.' } );
		const msg = `Panel: ${ String( note || 'içerik güncellendi' ).slice( 0, 80 ) }`;
		const out = await putFile( FILE, Buffer.from( json ).toString( 'base64' ), msg, sha );
		return res.status( 200 ).json( { ok: true, sha: out.sha, site: clean } );
	}

	res.status( 405 ).json( { error: 'Desteklenmeyen istek.' } );
} );
