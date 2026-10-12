// Vercel Cron (günde bir): küçük bir damga dosyası commit edilir, Vercel siteyi yeniden derler.
// Böylece tarihi geçen konserler sayfadan ve Google verisinden düşer, YouTube videoları tazelenir.
// Gün içinde ikinci çağrı hiçbir şey yapmaz; yani dışarıdan tetiklense bile en fazla günde bir derleme olur.
import { getFile, putFile } from '../lib/github.js';

export default async function handler( req, res ) {
	try {
		const today = new Date().toLocaleDateString( 'sv-SE', { timeZone: 'Europe/Istanbul' } );
		const file = await getFile( 'data/son-yenileme.txt' );
		if ( file && file.content.trim() === today ) {
			return res.status( 200 ).json( { ok: true, skipped: true } );
		}
		await putFile( 'data/son-yenileme.txt', Buffer.from( today + '\n' ).toString( 'base64' ), `Günlük yenileme ${ today }`, file?.sha );
		res.status( 200 ).json( { ok: true, today } );
	} catch ( err ) {
		console.error( err );
		res.status( 500 ).json( { error: 'Yenileme başarısız.' } );
	}
}
