// İçerik deposu olarak GitHub: panelden kaydedilen her şey bu depoya commit edilir,
// Vercel de her commit'te siteyi yeniden derleyip yayınlar.
// Gerekli ortam değişkeni: GITHUB_TOKEN (yalnızca bu depoya "Contents: Read and write" yetkili fine-grained token).

import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { createHash } from 'node:crypto';
import path from 'node:path';

const REPO = process.env.GITHUB_REPO || 'Cayity/nurettinrencber-site';
const BRANCH = process.env.GITHUB_BRANCH || 'main';
const API = `https://api.github.com/repos/${ REPO }/contents/`;

function headers() {
	const token = process.env.GITHUB_TOKEN;
	if ( ! token ) {
		throw Object.assign( new Error( 'GITHUB_TOKEN ortam değişkeni ayarlanmamış.' ), { status: 500 } );
	}
	return {
		Authorization: `Bearer ${ token }`,
		Accept: 'application/vnd.github+json',
		'X-GitHub-Api-Version': '2022-11-28',
		'User-Agent': 'rencber-admin',
	};
}

const encodePath = ( p ) => p.split( '/' ).map( encodeURIComponent ).join( '/' );

// Yerel geliştirme (tools/dev-server.mjs): GITHUB_LOCAL_DIR verilirse GitHub yerine diske yazılır.
const LOCAL = process.env.GITHUB_LOCAL_DIR;
const localSha = ( buf ) => createHash( 'sha1' ).update( buf ).digest( 'hex' );

export async function getFile( filePath ) {
	if ( LOCAL ) {
		try {
			const buf = await readFile( path.join( LOCAL, filePath ) );
			return { sha: localSha( buf ), content: buf.toString( 'utf8' ) };
		} catch { return null; }
	}
	const res = await fetch( `${ API }${ encodePath( filePath ) }?ref=${ BRANCH }`, { headers: headers() } );
	if ( res.status === 404 ) return null;
	if ( ! res.ok ) {
		throw Object.assign( new Error( `GitHub okuma hatası (${ res.status }).` ), { status: 502 } );
	}
	const json = await res.json();
	return { sha: json.sha, content: Buffer.from( json.content, 'base64' ).toString( 'utf8' ) };
}

/**
 * Dosya yaz (commit). `sha` verilirse GitHub, dosya o sürümden sonra değiştiyse 409 döner:
 * iki kişi aynı anda kaydederse biri diğerinin değişikliğini ezmez.
 */
export async function putFile( filePath, base64, message, sha ) {
	if ( LOCAL ) {
		const full = path.join( LOCAL, filePath );
		if ( sha ) {
			const cur = await getFile( filePath );
			if ( cur && cur.sha !== sha ) throw Object.assign( new Error( 'İçerik siz düzenlerken başka biri tarafından değiştirildi. Sayfayı yenileyip tekrar deneyin.' ), { status: 409 } );
		}
		const buf = Buffer.from( base64, 'base64' );
		await mkdir( path.dirname( full ), { recursive: true } );
		await writeFile( full, buf );
		console.log( '[yerel commit]', message );
		return { sha: localSha( buf ), commit: 'local' };
	}
	const body = {
		message,
		content: base64,
		branch: BRANCH,
	};
	if ( sha ) body.sha = sha;
	const res = await fetch( `${ API }${ encodePath( filePath ) }`, {
		method: 'PUT',
		headers: { ...headers(), 'Content-Type': 'application/json' },
		body: JSON.stringify( body ),
	} );
	if ( res.status === 409 || res.status === 422 ) {
		throw Object.assign( new Error( 'İçerik siz düzenlerken başka biri tarafından değiştirildi. Sayfayı yenileyip tekrar deneyin.' ), { status: 409 } );
	}
	if ( ! res.ok ) {
		throw Object.assign( new Error( `GitHub yazma hatası (${ res.status }).` ), { status: 502 } );
	}
	const json = await res.json();
	return { sha: json.content.sha, commit: json.commit.sha };
}
