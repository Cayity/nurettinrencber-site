/* Rençber yönetim paneli — kütüphanesiz. Veri: /api/admin/data (GitHub'daki data/site.json). */
( function () {
	'use strict';

	var $ = function ( s, el ) { return ( el || document ).querySelector( s ); };
	var state = { site: null, sha: '', dirty: false, tab: 'events', filter: {} };
	var pendingImages = {}; // yeni yüklenen görseller yayına girene kadar yerel önizleme

	var AYLAR = [ 'Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara' ];
	var TYPES = { album: 'Albüm', single: 'Single', home: 'Home Concert' };
	var STATUSES = { EventScheduled: 'Planlandı', EventPostponed: 'Ertelendi', EventRescheduled: 'Yeni tarihe alındı', EventCancelled: 'İptal edildi' };
	var LINKS = [
		[ 'spotify', 'Spotify' ], [ 'apple', 'Apple Music' ], [ 'youtube', 'YouTube kanalı' ], [ 'youtube_music', 'YouTube Music' ],
		[ 'deezer', 'Deezer' ], [ 'instagram', 'Instagram' ], [ 'facebook', 'Facebook' ], [ 'x', 'X (Twitter)' ], [ 'tiktok', 'TikTok' ],
		[ 'wikipedia', 'Vikipedi (sitede görünmez, Google için)' ],
	];
	var SECTIONS = [ [ 'events', 'Konserler' ], [ 'listen', 'Dinle' ], [ 'albums', 'Albümler' ], [ 'about', 'Hakkında' ], [ 'videos', 'Videolar' ], [ 'gallery', 'Galeri' ], [ 'contact', 'İletişim' ] ];

	/* ---------- yardımcılar ---------- */

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}
	function today() {
		try { return new Date().toLocaleDateString( 'sv-SE', { timeZone: 'Europe/Istanbul' } ); } catch ( e ) { return new Date().toISOString().slice( 0, 10 ); }
	}
	function uid( p ) { return p + Date.now().toString( 36 ) + Math.random().toString( 36 ).slice( 2, 5 ); }
	function imgSrc( p ) {
		if ( ! p ) return '';
		if ( pendingImages[ p ] ) return pendingImages[ p ];
		return /^https?:/.test( p ) ? p : '/' + p;
	}
	function toast( msg, isError ) {
		var t = $( '#toast' );
		t.textContent = msg;
		t.className = 'toast show' + ( isError ? ' error' : '' );
		clearTimeout( toast.t );
		toast.t = setTimeout( function () { t.className = 'toast'; }, isError ? 6000 : 3500 );
	}
	function api( path, opts ) {
		opts = opts || {};
		var init = { method: opts.method || 'GET', credentials: 'same-origin', headers: { 'X-Rencber-Admin': '1' } };
		if ( opts.body ) {
			init.headers[ 'Content-Type' ] = 'application/json';
			init.body = JSON.stringify( opts.body );
		}
		return fetch( path, init ).then( function ( r ) {
			return r.json().catch( function () { return {}; } ).then( function ( j ) {
				if ( ! r.ok ) { var err = new Error( j.error || ( 'Hata: ' + r.status ) ); err.status = r.status; throw err; }
				return j;
			} );
		} );
	}
	function setDirty( v ) {
		state.dirty = v;
		$( '#publish' ).disabled = ! v;
		var s = $( '#save-state' );
		s.textContent = v ? 'Kaydedilmemiş değişiklikler var' : '';
		s.className = 'save-state' + ( v ? ' dirty' : '' );
	}
	function get( obj, path ) { return path.split( '.' ).reduce( function ( o, k ) { return o == null ? o : o[ k ]; }, obj ); }
	function set( obj, path, val ) {
		var keys = path.split( '.' );
		var last = keys.pop();
		var o = keys.reduce( function ( o, k ) { if ( o[ k ] == null ) o[ k ] = {}; return o[ k ]; }, obj );
		o[ last ] = val;
	}

	/* ---------- form alanları ---------- */

	// Alan HTML'i; data-bind ile nesneye bağlanır.
	function field( label, path, opts ) {
		opts = opts || {};
		var type = opts.type || 'text';
		var cls = 'field' + ( opts.wide ? ' wide' : '' );
		var hint = opts.hint ? '<span class="hint">' + opts.hint + '</span>' : '';
		var attrs = ' data-bind="' + path + '"' + ( opts.required ? ' required' : '' ) + ( opts.placeholder ? ' placeholder="' + esc( opts.placeholder ) + '"' : '' );
		if ( type === 'textarea' ) {
			return '<label class="' + cls + '">' + label + '<textarea' + attrs + ( opts.rows ? ' rows="' + opts.rows + '"' : '' ) + '></textarea>' + hint + '</label>';
		}
		if ( type === 'select' ) {
			var o = Object.keys( opts.options ).map( function ( k ) { return '<option value="' + esc( k ) + '">' + esc( opts.options[ k ] ) + '</option>'; } ).join( '' );
			return '<label class="' + cls + '">' + label + '<select' + attrs + '>' + o + '</select>' + hint + '</label>';
		}
		if ( type === 'checkbox' ) {
			return '<label class="field check' + ( opts.wide ? ' wide' : '' ) + '"><input type="checkbox"' + attrs + '> ' + label + '</label>';
		}
		if ( type === 'image' ) {
			return '<div class="' + cls + '">' + label + '<div class="image-field' + ( opts.landscape ? ' wide' : '' ) + '" data-image="' + path + '" data-max="' + ( opts.max || 1600 ) + '" data-format="' + ( opts.format || 'image/webp' ) + '">' +
				'<img alt="">' +
				'<div class="btns"><button type="button" class="btn small" data-pick>Görsel seç</button><button type="button" class="btn small ghost" data-clear>Kaldır</button></div>' +
				'<input type="file" accept="image/*" hidden></div>' + hint + '</div>';
		}
		return '<label class="' + cls + '">' + label + '<input type="' + type + '"' + attrs + ( opts.min != null ? ' min="' + opts.min + '"' : '' ) + ( opts.max != null ? ' max="' + opts.max + '"' : '' ) + ( opts.step ? ' step="' + opts.step + '"' : '' ) + '>' + hint + '</label>';
	}

	// Kapsayıcıdaki alanları nesneye bağla. onChange: her değişiklikte.
	function bind( root, obj, onChange ) {
		root.querySelectorAll( '[data-bind]' ).forEach( function ( el ) {
			var path = el.getAttribute( 'data-bind' );
			var v = get( obj, path );
			if ( el.type === 'checkbox' ) el.checked = Boolean( v );
			else if ( Array.isArray( v ) ) el.value = v.join( '\n' );
			else el.value = v == null ? '' : v;
			el.addEventListener( el.type === 'checkbox' || el.tagName === 'SELECT' ? 'change' : 'input', function () {
				var val = el.type === 'checkbox' ? el.checked : el.type === 'number' ? ( el.value === '' ? '' : Number( el.value ) ) : el.value;
				if ( el.getAttribute( 'data-list' ) === '1' ) val = el.value.split( '\n' ).map( function ( x ) { return x.trim(); } ).filter( Boolean );
				set( obj, path, val );
				if ( onChange ) onChange();
			} );
		} );
		root.querySelectorAll( '[data-image]' ).forEach( function ( box ) {
			var path = box.getAttribute( 'data-image' );
			var img = box.querySelector( 'img' );
			var input = box.querySelector( 'input[type=file]' );
			var show = function () { var p = get( obj, path ); img.src = imgSrc( p ); img.style.visibility = p ? 'visible' : 'hidden'; };
			show();
			box.querySelector( '[data-pick]' ).addEventListener( 'click', function () { input.click(); } );
			box.querySelector( '[data-clear]' ).addEventListener( 'click', function () { set( obj, path, '' ); show(); if ( onChange ) onChange(); } );
			input.addEventListener( 'change', function () {
				var file = input.files[ 0 ];
				if ( ! file ) return;
				box.classList.add( 'uploading' );
				uploadImage( file, Number( box.getAttribute( 'data-max' ) ), box.getAttribute( 'data-format' ) ).then( function ( r ) {
					set( obj, path, r.path );
					show();
					if ( onChange ) onChange();
					toast( 'Görsel yüklendi. Kaydet ve yayınla ile siteye eklenir.' );
				} ).catch( function ( e ) { toast( e.message, true ); } ).then( function () {
					box.classList.remove( 'uploading' );
					input.value = '';
				} );
			} );
		} );
	}

	/* ---------- görsel yükleme (tarayıcıda küçültme) ---------- */

	function resize( file, max, type ) {
		return new Promise( function ( resolve, reject ) {
			var url = URL.createObjectURL( file );
			var im = new Image();
			im.onload = function () {
				var scale = Math.min( 1, max / Math.max( im.naturalWidth, im.naturalHeight ) );
				var w = Math.round( im.naturalWidth * scale );
				var h = Math.round( im.naturalHeight * scale );
				var c = document.createElement( 'canvas' );
				c.width = w; c.height = h;
				c.getContext( '2d' ).drawImage( im, 0, 0, w, h );
				URL.revokeObjectURL( url );
				c.toBlob( function ( blob ) {
					if ( ! blob ) return reject( new Error( 'Görsel işlenemedi.' ) );
					// Safari eski sürümleri WebP üretemezse JPEG'e düşer.
					resolve( { blob: blob, w: w, h: h, type: blob.type || type } );
				}, type, 0.84 );
			};
			im.onerror = function () { URL.revokeObjectURL( url ); reject( new Error( 'Görsel okunamadı.' ) ); };
			im.src = url;
		} );
	}
	function toBase64( blob ) {
		return new Promise( function ( resolve ) {
			var r = new FileReader();
			r.onload = function () { resolve( String( r.result ).split( ',' )[ 1 ] ); };
			r.readAsDataURL( blob );
		} );
	}
	function uploadImage( file, max, type ) {
		return resize( file, max || 1600, type || 'image/webp' ).then( function ( out ) {
			return toBase64( out.blob ).then( function ( b64 ) {
				return api( '/api/admin/upload', { method: 'POST', body: { name: file.name, type: out.type, data: b64 } } ).then( function ( r ) {
					pendingImages[ r.path ] = URL.createObjectURL( out.blob );
					return { path: r.path, w: out.w, h: out.h };
				} );
			} );
		} );
	}

	/* ---------- modal ---------- */

	function openModal( title, html, obj, opts ) {
		opts = opts || {};
		var modal = $( '#modal' );
		var draft = JSON.parse( JSON.stringify( obj ) );
		$( '#modal-title' ).textContent = title;
		$( '#modal-body' ).innerHTML = html;
		$( '#modal-delete' ).hidden = ! opts.onDelete;
		bind( $( '#modal-body' ), draft );
		$( '#modal-delete' ).onclick = function () {
			if ( confirm( 'Bu kaydı silmek istediğinize emin misiniz?' ) ) { modal.close( 'cancel' ); opts.onDelete(); }
		};
		// "Tamam" (ve alanlardayken Enter) formu gönderir; doğrulama başarısızsa pencere açık kalır.
		$( '#modal-form' ).onsubmit = function ( ev ) {
			var err = opts.validate && opts.validate( draft );
			if ( err ) { ev.preventDefault(); toast( err, true ); return; }
			opts.onSave( draft ); // ardından form (method=dialog) pencereyi kapatır
		};
		modal.showModal();
	}

	/* ---------- sekmeler ---------- */

	var views = {};

	views.events = function ( el ) {
		var t = today();
		var f = state.filter.events || 'upcoming';
		var list = state.site.events.slice().sort( function ( a, b ) { return ( a.date + a.time ).localeCompare( b.date + b.time ); } );
		if ( f === 'upcoming' ) list = list.filter( function ( e ) { return e.date >= t; } );
		if ( f === 'past' ) list = list.filter( function ( e ) { return e.date < t; } ).reverse();
		el.innerHTML =
			'<div class="panel-head"><div><h2>Konserler</h2><p class="lead">Tarihi geçen konserler sitede kendiliğinden gizlenir. Google’daki “Etkinlikler” bilgisi de buradan beslenir; mümkünse açık adres ve bilet bağlantısı girin.</p></div>' +
			'<button class="btn primary" data-add>+ Yeni konser</button></div>' +
			'<div class="filters">' + [ [ 'upcoming', 'Yaklaşan' ], [ 'past', 'Geçmiş' ], [ 'all', 'Tümü' ] ].map( function ( x ) {
				return '<button class="chip' + ( f === x[ 0 ] ? ' is-active' : '' ) + '" data-filter="' + x[ 0 ] + '">' + x[ 1 ] + '</button>';
			} ).join( '' ) + '</div>' +
			( list.length ? '<ul class="list">' + list.map( function ( e ) {
				var d = e.date.split( '-' );
				var st = e.status && e.status !== 'EventScheduled' ? '<span class="badge warn">' + STATUSES[ e.status ] + '</span>' : '';
				var so = e.soldout ? '<span class="badge warn">Tükendi</span>' : '';
				return '<li class="' + ( e.date < t ? 'is-past' : '' ) + '"><span class="date-chip"><small>' + AYLAR[ Number( d[ 1 ] ) - 1 ] + '</small><b>' + Number( d[ 2 ] ) + '</b></span>' +
					'<div class="main"><div class="title">' + esc( e.city ) + st + so + '</div><div class="sub">' + esc( d[ 0 ] + ' · ' + ( e.time || '' ) + ' · ' + e.venue + ( e.price ? ' · ₺' + e.price : '' ) ) + '</div></div>' +
					'<button class="btn small" data-edit="' + esc( e.id ) + '">Düzenle</button></li>';
			} ).join( '' ) + '</ul>' : '<p class="empty">Bu listede konser yok.</p>' );

		el.querySelectorAll( '[data-filter]' ).forEach( function ( b ) {
			b.onclick = function () { state.filter.events = b.getAttribute( 'data-filter' ); render(); };
		} );
		el.querySelector( '[data-add]' ).onclick = function () { editEvent( null ); };
		el.querySelectorAll( '[data-edit]' ).forEach( function ( b ) {
			b.onclick = function () { editEvent( b.getAttribute( 'data-edit' ) ); };
		} );
	};

	function editEvent( id ) {
		var events = state.site.events;
		var idx = events.findIndex( function ( e ) { return e.id === id; } );
		var ev = idx > -1 ? events[ idx ] : { id: uid( 'e' ), date: '', time: '20:30', city: '', venue: '', address: '', ticket_url: '', vendor: '', price: '', currency: 'TRY', status: 'EventScheduled', soldout: false };
		var html = '<div class="grid">' +
			field( 'Tarih *', 'date', { type: 'date', required: true } ) +
			field( 'Saat', 'time', { type: 'time' } ) +
			field( 'Şehir / ilçe *', 'city', { required: true, placeholder: 'Gebze' } ) +
			field( 'Mekân *', 'venue', { required: true, placeholder: 'Osman Hamdi Bey Kültür Merkezi' } ) +
			field( 'Açık adres', 'address', { wide: true, placeholder: 'Mahalle, cadde, no, ilçe/il', hint: 'Google Etkinlikler için önerilir.' } ) +
			field( 'Bilet bağlantısı', 'ticket_url', { type: 'url', wide: true, placeholder: 'https://…', hint: 'Doluysa sitede “Bilet al” düğmesi çıkar.' } ) +
			field( 'Bilet satıcısı', 'vendor', { placeholder: 'Biletix, Bubilet, Passo…' } ) +
			field( 'Başlangıç fiyatı', 'price', { placeholder: '1159' } ) +
			field( 'Para birimi', 'currency', { type: 'select', options: { TRY: 'TRY (₺)', EUR: 'EUR (€)', USD: 'USD ($)', GBP: 'GBP (£)' } } ) +
			field( 'Durum', 'status', { type: 'select', options: STATUSES } ) +
			field( 'Biletler tükendi', 'soldout', { type: 'checkbox', wide: true } ) +
			'</div>';
		openModal( idx > -1 ? 'Konseri düzenle' : 'Yeni konser', html, ev, {
			validate: function ( d ) {
				if ( ! d.date || ! d.city.trim() || ! d.venue.trim() ) return 'Tarih, şehir ve mekân zorunlu.';
				if ( d.ticket_url && ! /^https?:\/\//.test( d.ticket_url ) ) return 'Bilet bağlantısı https:// ile başlamalı.';
			},
			onSave: function ( d ) {
				if ( idx > -1 ) events[ idx ] = d; else events.push( d );
				setDirty( true ); render();
			},
			onDelete: idx > -1 ? function () { events.splice( idx, 1 ); setDirty( true ); render(); } : null,
		} );
	}

	views.releases = function ( el ) {
		var f = state.filter.releases || 'all';
		var list = state.site.releases.slice().sort( function ( a, b ) { return String( b.date ).localeCompare( String( a.date ) ); } );
		if ( f !== 'all' ) list = list.filter( function ( r ) { return r.type === f; } );
		el.innerHTML =
			'<div class="panel-head"><div><h2>Diskografi</h2><p class="lead">Albümler, single’lar ve Home Concert kayıtları. Kapağa tıklayınca açılacak bağlantı: Apple Music, yoksa Spotify, yoksa YouTube.</p></div>' +
			'<button class="btn primary" data-add>+ Yeni yayın</button></div>' +
			'<div class="filters">' + [ [ 'all', 'Tümü' ], [ 'single', 'Single' ], [ 'album', 'Albüm' ], [ 'home', 'Home Concert' ] ].map( function ( x ) {
				return '<button class="chip' + ( f === x[ 0 ] ? ' is-active' : '' ) + '" data-filter="' + x[ 0 ] + '">' + x[ 1 ] + '</button>';
			} ).join( '' ) + '</div>' +
			( list.length ? '<ul class="list">' + list.map( function ( r ) {
				return '<li><img class="thumb" src="' + esc( imgSrc( r.cover ) ) + '" alt="" loading="lazy">' +
					'<div class="main"><div class="title">' + esc( r.title ) + '<span class="badge">' + TYPES[ r.type ] + '</span></div><div class="sub">' + esc( r.year + ( r.credit ? ' · ' + r.credit + ' ile' : '' ) + ( r.label ? ' · ' + r.label : '' ) ) + '</div></div>' +
					'<button class="btn small" data-edit="' + esc( r.id ) + '">Düzenle</button></li>';
			} ).join( '' ) + '</ul>' : '<p class="empty">Kayıt yok.</p>' );
		el.querySelectorAll( '[data-filter]' ).forEach( function ( b ) {
			b.onclick = function () { state.filter.releases = b.getAttribute( 'data-filter' ); render(); };
		} );
		el.querySelector( '[data-add]' ).onclick = function () { editRelease( null ); };
		el.querySelectorAll( '[data-edit]' ).forEach( function ( b ) {
			b.onclick = function () { editRelease( b.getAttribute( 'data-edit' ) ); };
		} );
	};

	function editRelease( id ) {
		var list = state.site.releases;
		var idx = list.findIndex( function ( r ) { return r.id === id; } );
		var r = idx > -1 ? list[ idx ] : { id: uid( 'r' ), title: '', type: 'single', date: today(), year: Number( today().slice( 0, 4 ) ), credit: '', label: '', apple: '', spotify: '', youtube: '', cover: '', tracks: [] };
		var html = '<div class="grid">' +
			field( 'Ad *', 'title', { wide: true, required: true } ) +
			field( 'Tür', 'type', { type: 'select', options: TYPES } ) +
			field( 'Çıkış tarihi', 'date', { type: 'date', hint: 'Sıralama için kullanılır.' } ) +
			field( 'Birlikte söyleyen', 'credit', { placeholder: 'ör. Ceren Tügen Akdeniz' } ) +
			field( 'Yapım şirketi', 'label' ) +
			field( 'Apple Music bağlantısı', 'apple', { type: 'url', wide: true } ) +
			field( 'Spotify bağlantısı', 'spotify', { type: 'url', wide: true } ) +
			field( 'YouTube bağlantısı', 'youtube', { type: 'url', wide: true } ) +
			field( 'Kapak', 'cover', { type: 'image', wide: true, max: 800, hint: 'Kare görsel önerilir; otomatik küçültülür.' } ) +
			field( 'Şarkı listesi (her satıra bir şarkı)', 'tracks', { type: 'textarea', wide: true, rows: 6 } ) +
			'</div>';
		openModal( idx > -1 ? 'Yayını düzenle' : 'Yeni yayın', html, r, {
			validate: function ( d ) { if ( ! String( d.title ).trim() ) return 'Ad zorunlu.'; },
			onSave: function ( d ) {
				if ( d.date ) d.year = Number( String( d.date ).slice( 0, 4 ) );
				if ( typeof d.tracks === 'string' ) d.tracks = d.tracks.split( '\n' ).map( function ( x ) { return x.trim(); } ).filter( Boolean );
				if ( idx > -1 ) list[ idx ] = d; else list.push( d );
				setDirty( true ); render();
			},
			onDelete: idx > -1 ? function () { list.splice( idx, 1 ); setDirty( true ); render(); } : null,
		} );
		$( '#modal-body [data-bind="tracks"]' ).setAttribute( 'data-list', '1' );
	}

	function settingsView( el, title, lead, body ) {
		el.innerHTML = '<h2>' + title + '</h2><p class="lead">' + lead + '</p>' + body;
		bind( el, state.site.settings, function () { setDirty( true ); } );
	}

	views.hero = function ( el ) {
		settingsView( el, 'Açılış videosu', 'Sitenin en üstünde, sessiz ve döngüde oynayan video ve üzerindeki yazılar.',
			'<div class="card"><h3>Video</h3><div class="grid">' +
			field( 'YouTube video bağlantısı', 'hero.youtube', { type: 'url', wide: true, hint: 'Boş bırakılırsa aşağıdaki kapak fotoğrafı gösterilir.' } ) +
			field( 'Kaçıncı saniyeden başlasın', 'hero.start', { type: 'number', min: 0, hint: 'Videonun başındaki yazı/jenerik görünmesin diye.' } ) +
			field( 'Kaçıncı saniyede bitsin (0 = sonuna kadar)', 'hero.end', { type: 'number', min: 0 } ) +
			field( 'Karartma (%)', 'hero.overlay', { type: 'number', min: 0, max: 90, step: 5, hint: 'Yazıların okunması için videonun üzerine koyu katman. 50–65 önerilir.' } ) +
			field( 'Yedek kapak fotoğrafı', 'hero.poster', { type: 'image', landscape: true, max: 2000, hint: 'Yalnızca video oynatılamadığında görünür.' } ) +
			'</div></div>' +
			'<div class="card"><h3>Yazılar ve düğmeler</h3><div class="grid">' +
			field( 'Üst küçük yazı', 'hero.kicker' ) +
			field( 'Büyük başlık', 'hero.title' ) +
			field( 'Açıklama', 'hero.text', { type: 'textarea', wide: true, rows: 3 } ) +
			field( '1. düğme yazısı', 'hero.btn1_text' ) + field( '1. düğme bağlantısı', 'hero.btn1_url', { hint: '#konserler gibi bölüm adresi ya da https://…' } ) +
			field( '2. düğme yazısı', 'hero.btn2_text' ) + field( '2. düğme bağlantısı', 'hero.btn2_url' ) +
			'</div></div>' );
	};

	views.texts = function ( el ) {
		settingsView( el, 'Metinler ve bölümler', 'Menüde ve bölüm başlıklarında görünen adlar. Bir bölüm başlığını boş bırakırsanız o bölüm sitede gizlenir.',
			'<div class="card"><h3>Genel</h3><div class="grid">' +
			field( 'Sanatçı adı', 'artist_name' ) +
			field( 'Alt bilgi yazısı', 'footer_text', { hint: '{yil} yazarsanız bu yıl otomatik yazılır.' } ) +
			'</div></div>' +
			'<div class="card"><h3>Bölüm başlıkları</h3><div class="grid">' +
			SECTIONS.map( function ( s ) { return field( s[ 1 ] + ' bölümü', 'sections.' + s[ 0 ] ); } ).join( '' ) +
			'</div></div>' +
			'<div class="card"><h3>Bölüm yazıları</h3><div class="grid">' +
			field( 'Konser yokken görünen yazı', 'events_empty', { wide: true } ) +
			field( 'Dinle bölümü açıklaması', 'listen_text', { wide: true } ) +
			'</div></div>' );
	};

	views.about = function ( el ) {
		settingsView( el, 'Hakkında', 'Biyografi bölümü.',
			'<div class="card"><div class="grid">' +
			field( 'Fotoğraf', 'about.image', { type: 'image', wide: true, max: 1400 } ) +
			field( 'Öne çıkan söz (isteğe bağlı)', 'about.quote', { type: 'textarea', wide: true, rows: 2 } ) +
			field( 'Biyografi', 'about.text', { type: 'textarea', wide: true, rows: 14, hint: 'Paragrafları boş bir satırla ayırın.' } ) +
			'</div></div>' );
	};

	views.links = function ( el ) {
		settingsView( el, 'Bağlantılar', 'Doldurulan platformlar sitenin başlığında, “Dinle” bölümünde ve alt bilgide görünür. Boş olanlar gizlenir. Bu bağlantılar Google’ın sanatçıyı Spotify, Apple Music ve YouTube profilleriyle eşleştirmesine de yardımcı olur.',
			'<div class="card"><div class="grid">' + LINKS.map( function ( l ) { return field( l[ 1 ], 'links.' + l[ 0 ], { type: 'url', wide: true } ); } ).join( '' ) + '</div></div>' );
	};

	views.contact = function ( el ) {
		settingsView( el, 'İletişim', 'Sayfanın sonundaki iletişim bölümü.',
			'<div class="card"><div class="grid">' +
			field( 'Açıklama', 'contact.text', { type: 'textarea', wide: true, rows: 3 } ) +
			field( 'Menajer adı', 'contact.manager' ) +
			field( 'Telefon', 'contact.phone' ) +
			field( 'E-posta', 'contact.email', { type: 'email', wide: true } ) +
			'</div></div>' );
	};

	views.seo = function ( el ) {
		settingsView( el, 'SEO ve paylaşım', 'Google arama sonucunda ve WhatsApp, Facebook gibi paylaşımlarda görünen bilgiler.',
			'<div class="card"><div class="grid">' +
			field( 'Site açıklaması', 'seo_description', { type: 'textarea', wide: true, rows: 3, hint: 'Google’da adın altında görünen yazı (150–160 karakter).' } ) +
			field( 'Paylaşım görseli', 'seo.share_image', { type: 'image', landscape: true, wide: true, max: 1200, format: 'image/jpeg', hint: '1200×630 yatay görsel önerilir.' } ) +
			field( 'Müzik türü', 'seo.genre' ) + field( 'Doğum yılı', 'seo.birth_date' ) + field( 'Doğum yeri', 'seo.birth_place' ) +
			'</div></div>' );
	};

	views.videos = function ( el ) {
		var v = state.site.settings.videos;
		if ( ! Array.isArray( v.manual ) ) v.manual = [];
		var manualText = v.manual.map( function ( x ) { return x.url; } ).join( '\n' );
		el.innerHTML = '<h2>Videolar</h2><p class="lead">Varsayılan olarak YouTube kanalındaki en yeni videolar her gün otomatik gelir.</p>' +
			'<div class="card"><div class="grid">' +
			field( 'Videolar nereden gelsin?', 'source', { type: 'select', options: { channel: 'YouTube kanalındaki en yeni videolar (otomatik)', manual: 'Aşağıdaki listeden' }, wide: true } ) +
			field( 'Kaç video gösterilsin', 'limit', { type: 'number', min: 1, max: 15 } ) +
			'<label class="field wide">Video listesi (her satıra bir YouTube bağlantısı)<textarea id="manual-videos" rows="8">' + esc( manualText ) + '</textarea><span class="hint">Yalnızca “Aşağıdaki listeden” seçiliyse kullanılır.</span></label>' +
			'</div></div>';
		bind( el, v, function () { setDirty( true ); } );
		$( '#manual-videos' ).addEventListener( 'input', function () {
			v.manual = this.value.split( '\n' ).map( function ( x ) { return x.trim(); } ).filter( Boolean ).map( function ( u ) { return { url: u, title: '' }; } );
			setDirty( true );
		} );
	};

	views.gallery = function ( el ) {
		var g = state.site.gallery;
		el.innerHTML = '<h2>Galeri</h2><p class="lead">Fotoğraflar sitede bu sırayla görünür. Okları kullanarak sıralayabilirsiniz; yeni yüklenenler en başa eklenir.</p>' +
			'<div class="dropzone" id="drop">Fotoğrafları buraya sürükleyin ya da <b>seçmek için tıklayın</b><br><small>Birden fazla seçebilirsiniz. Otomatik küçültülür.</small><input type="file" accept="image/*" multiple hidden></div>' +
			( g.length ? '<div class="gallery-admin">' + g.map( function ( x, i ) {
				return '<figure><img src="' + esc( imgSrc( x.src ) ) + '" alt="" loading="lazy"><div class="tools">' +
					'<button type="button" data-move="' + i + ',-1" aria-label="Öne al">←</button>' +
					'<button type="button" class="del" data-del="' + i + '" aria-label="Sil">✕</button>' +
					'<button type="button" data-move="' + i + ',1" aria-label="Geriye al">→</button></div></figure>';
			} ).join( '' ) + '</div>' : '<p class="empty">Galeride fotoğraf yok.</p>' );

		var drop = $( '#drop' );
		var input = drop.querySelector( 'input' );
		drop.onclick = function () { input.click(); };
		drop.ondragover = function ( e ) { e.preventDefault(); drop.classList.add( 'is-over' ); };
		drop.ondragleave = function () { drop.classList.remove( 'is-over' ); };
		drop.ondrop = function ( e ) { e.preventDefault(); drop.classList.remove( 'is-over' ); addPhotos( e.dataTransfer.files ); };
		input.onchange = function () { addPhotos( input.files ); input.value = ''; };

		el.querySelectorAll( '[data-move]' ).forEach( function ( b ) {
			b.onclick = function () {
				var p = b.getAttribute( 'data-move' ).split( ',' ).map( Number );
				var j = p[ 0 ] + p[ 1 ];
				if ( j < 0 || j >= g.length ) return;
				var t = g[ p[ 0 ] ]; g[ p[ 0 ] ] = g[ j ]; g[ j ] = t;
				setDirty( true ); render();
			};
		} );
		el.querySelectorAll( '[data-del]' ).forEach( function ( b ) {
			b.onclick = function () {
				if ( ! confirm( 'Bu fotoğraf galeriden kaldırılsın mı?' ) ) return;
				g.splice( Number( b.getAttribute( 'data-del' ) ), 1 );
				setDirty( true ); render();
			};
		} );
	};

	function addPhotos( files ) {
		files = Array.prototype.filter.call( files || [], function ( f ) { return /^image\//.test( f.type ); } );
		if ( ! files.length ) return;
		var drop = $( '#drop' );
		var done = 0;
		drop.classList.add( 'uploading' );
		var chain = Promise.resolve();
		files.forEach( function ( file ) {
			chain = chain.then( function () {
				drop.firstChild.textContent = 'Yükleniyor… (' + ( done + 1 ) + '/' + files.length + ') ';
				// Galeri için iki boyut: liste (1000px) ve büyük görünüm (2000px).
				return uploadImage( file, 1000 ).then( function ( small ) {
					return uploadImage( file, 2000 ).then( function ( big ) {
						state.site.gallery.unshift( { src: small.path, full: big.path, alt: state.site.settings.artist_name, w: small.w, h: small.h } );
						done++;
					} );
				} );
			} );
		} );
		chain.catch( function ( e ) { toast( e.message, true ); } ).then( function () {
			if ( done ) { setDirty( true ); toast( done + ' fotoğraf eklendi. Kaydet ve yayınla ile siteye eklenir.' ); }
			render();
		} );
	}

	/* ---------- çizim ---------- */

	function render() {
		document.querySelectorAll( '#tabs button' ).forEach( function ( b ) {
			b.classList.toggle( 'is-active', b.getAttribute( 'data-tab' ) === state.tab );
		} );
		var panel = $( '#panel' );
		views[ state.tab ]( panel );
	}

	/* ---------- akış ---------- */

	function showOnly( id ) {
		[ 'login', 'setup', 'app' ].forEach( function ( x ) { $( '#' + x ).hidden = x !== id; } );
	}

	function load() {
		return api( '/api/admin/data' ).then( function ( r ) {
			state.site = r.site;
			state.sha = r.sha;
			[ 'events', 'releases', 'gallery' ].forEach( function ( k ) { if ( ! Array.isArray( state.site[ k ] ) ) state.site[ k ] = []; } );
			setDirty( false );
			showOnly( 'app' );
			render();
		} );
	}

	function publish() {
		var btn = $( '#publish' );
		btn.disabled = true;
		btn.textContent = 'Kaydediliyor…';
		api( '/api/admin/data', { method: 'PUT', body: { site: state.site, sha: state.sha, note: 'içerik güncellendi' } } ).then( function ( r ) {
			state.sha = r.sha;
			state.site = r.site;
			setDirty( false );
			var s = $( '#save-state' );
			s.textContent = 'Kaydedildi · yaklaşık 1 dakikada yayında';
			s.className = 'save-state ok';
			toast( 'Kaydedildi. Site yaklaşık 1 dakika içinde güncellenir.' );
			render();
		} ).catch( function ( e ) {
			toast( e.message, true );
			setDirty( true );
		} ).then( function () {
			btn.textContent = 'Kaydet ve yayınla';
		} );
	}

	$( '#login-form' ).addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		var btn = this.querySelector( 'button' );
		var errEl = $( '#login-error' );
		btn.disabled = true;
		errEl.textContent = '';
		api( '/api/admin/login', { method: 'POST', body: { password: this.password.value } } ).then( function () {
			return load();
		} ).catch( function ( err ) {
			errEl.textContent = err.message;
		} ).then( function () { btn.disabled = false; } );
	} );

	$( '#publish' ).addEventListener( 'click', publish );
	$( '#logout' ).addEventListener( 'click', function () {
		if ( state.dirty && ! confirm( 'Kaydedilmemiş değişiklikler kaybolacak. Çıkılsın mı?' ) ) return;
		api( '/api/admin/logout', { method: 'POST' } ).then( function () { location.reload(); } );
	} );
	$( '#tabs' ).addEventListener( 'click', function ( e ) {
		var b = e.target.closest( 'button[data-tab]' );
		if ( ! b ) return;
		state.tab = b.getAttribute( 'data-tab' );
		render();
		window.scrollTo( 0, 0 );
	} );
	window.addEventListener( 'beforeunload', function ( e ) {
		if ( state.dirty ) { e.preventDefault(); e.returnValue = ''; }
	} );
	document.addEventListener( 'keydown', function ( e ) {
		if ( ( e.ctrlKey || e.metaKey ) && e.key === 's' ) { e.preventDefault(); if ( state.dirty ) publish(); }
	} );

	fetch( '/api/admin/session', { credentials: 'same-origin' } ).then( function ( r ) { return r.json(); } ).then( function ( s ) {
		if ( ! s.configured ) {
			$( '#setup-missing' ).innerHTML = ( s.missing || [] ).map( function ( m ) { return '<li>' + esc( m ) + '</li>'; } ).join( '' );
			return showOnly( 'setup' );
		}
		if ( s.admin ) return load().catch( function ( e ) { showOnly( 'login' ); toast( e.message, true ); } );
		showOnly( 'login' );
	} ).catch( function () {
		showOnly( 'login' );
	} );
} )();
