/* Rençber teması — etkileşimler. Kütüphane gerektirmez. */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* Başlık: kaydırınca koyulaşır. */
	var header = document.querySelector( '[data-header]' );
	if ( header ) {
		var onScroll = function () {
			header.classList.toggle( 'is-solid', window.scrollY > 40 );
		};
		onScroll();
		window.addEventListener( 'scroll', onScroll, { passive: true } );
	}

	/* Başa dön düğmesi: bir ekran aşağı inince görünür. */
	var toTop = document.querySelector( '[data-to-top]' );
	if ( toTop ) {
		var onScrollTop = function () {
			toTop.classList.toggle( 'is-visible', window.scrollY > window.innerHeight * 0.8 );
		};
		onScrollTop();
		window.addEventListener( 'scroll', onScrollTop, { passive: true } );
		toTop.addEventListener( 'click', function () {
			window.scrollTo( { top: 0, behavior: reduceMotion ? 'auto' : 'smooth' } );
			var skip = document.querySelector( '.brand' );
			if ( skip ) { skip.focus( { preventScroll: true } ); }
		} );
	}

	/* Mobil menü. */
	var toggle = document.querySelector( '[data-nav-toggle]' );
	if ( toggle ) {
		var setOpen = function ( open ) {
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			document.body.classList.toggle( 'nav-open', open );
		};
		toggle.addEventListener( 'click', function () {
			setOpen( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );
		document.querySelectorAll( '#primary-nav a' ).forEach( function ( a ) {
			a.addEventListener( 'click', function () { setOpen( false ); } );
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) { setOpen( false ); }
		} );
	}

	/* Açılış videosu. */
	var hero = document.querySelector( '[data-hero]' );
	if ( hero ) {
		var btn = hero.querySelector( '[data-hero-toggle]' );
		var video = hero.querySelector( '[data-hero-video]' );
		var ytBox = hero.querySelector( '[data-yt]' );
		var iframe = null;
		var paused = false;
		// Videonun başındaki/sonundaki yazıları atlamak için başlangıç ve bitiş saniyesi (panelden).
		var clipStart = parseFloat( hero.getAttribute( 'data-start' ) ) || 0;
		var clipEnd = parseFloat( hero.getAttribute( 'data-end' ) ) || 0;

		var setPaused = function ( p ) {
			paused = p;
			if ( btn ) {
				btn.setAttribute( 'aria-pressed', p ? 'true' : 'false' );
				btn.setAttribute( 'aria-label', p ? 'Arka plan videosunu oynat' : 'Arka plan videosunu durdur' );
			}
			hero.classList.toggle( 'is-paused', p );
			if ( video ) { p ? video.pause() : video.play().catch( function () {} ); }
			if ( iframe && iframe.contentWindow ) {
				iframe.contentWindow.postMessage( JSON.stringify( { event: 'command', func: p ? 'pauseVideo' : 'playVideo', args: '' } ), '*' );
			}
		};

		var reveal = function () { hero.classList.add( 'is-playing' ); };

		// "Hareketi azalt" açıksa video oynamaz; yalnızca o durumda kapak fotoğrafı gösterilir.
		if ( reduceMotion ) {
			hero.classList.add( 'is-static' );
			var still = hero.querySelector( '.hero-poster[data-src]' );
			if ( still ) { still.src = still.getAttribute( 'data-src' ); }
		}

		if ( video ) {
			video.addEventListener( 'playing', reveal );
			var toStart = function () { try { video.currentTime = clipStart; } catch ( err ) {} };
			if ( clipStart ) {
				video.loop = false;
				video.readyState >= 1 ? toStart() : video.addEventListener( 'loadedmetadata', toStart );
			}
			// Kendi döngümüz: bitişte (ya da bitiş saniyesinde) başlangıç saniyesine dön.
			video.addEventListener( 'timeupdate', function () {
				if ( clipEnd && video.currentTime >= clipEnd ) { toStart(); }
			} );
			video.addEventListener( 'ended', function () { toStart(); video.play().catch( function () {} ); } );
			if ( reduceMotion ) { setPaused( true ); }
		}

		if ( ytBox && ! reduceMotion ) {
			// Sayfanın tamamen yüklenmesi beklenmez: oynatıcı hemen yüklenmeye başlar.
			var id = ytBox.getAttribute( 'data-yt' );
			iframe = document.createElement( 'iframe' );
			iframe.src = 'https://www.youtube-nocookie.com/embed/' + id +
				// loop parametresi her turda 0. saniyeden başlatır; döngü aşağıda elle yapılır.
				'?autoplay=1&mute=1&start=' + Math.floor( clipStart ) + ( clipEnd ? '&end=' + Math.floor( clipEnd ) : '' ) +
				'&controls=0&disablekb=1&playsinline=1&rel=0&modestbranding=1&iv_load_policy=3&enablejsapi=1' +
				'&origin=' + encodeURIComponent( location.origin );
			iframe.title = 'Arka plan videosu';
			iframe.allow = 'autoplay; encrypted-media';
			iframe.setAttribute( 'tabindex', '-1' );
			iframe.setAttribute( 'aria-hidden', 'true' );

			var send = function ( func, args ) {
				iframe.contentWindow.postMessage( JSON.stringify( { event: 'command', func: func, args: args || [], id: 'hero', channel: 'widget' } ), '*' );
			};

			var connected = false;
			var handshake = null;

			// Video gerçekten oynamaya başlayınca göster (YouTube'un yükleme ekranı görünmesin).
			window.addEventListener( 'message', function ( e ) {
				if ( ! /youtube/.test( e.origin ) || typeof e.data !== 'string' ) { return; }
				try {
					var d = JSON.parse( e.data );
					if ( ! connected ) {
						connected = true;
						clearInterval( handshake );
					}
					var state = d.info && typeof d.info.playerState === 'number' ? d.info.playerState : null;
					// Oynatıcı hazır ama başlamadıysa (bazı tarayıcılar autoplay'i yok sayar): sessize alıp açıkça başlat.
					if ( ! paused && ( d.event === 'onReady' || state === -1 || state === 5 ) ) {
						send( 'mute' );
						send( 'playVideo' );
					}
					if ( state === 1 ) {
						setTimeout( reveal, 300 );
					}
					// Video bitti: yazılı girişe dönmeden başlangıç saniyesinden tekrar oynat.
					if ( state === 0 ) {
						send( 'seekTo', [ clipStart, true ] );
						send( 'playVideo' );
					}
				} catch ( err ) {}
			} );
			iframe.addEventListener( 'load', function () {
				// Oynatıcı içindeki betik hazır olana kadar bağlantı isteğini tekrarla (YouTube API'sinin yaptığı gibi).
				var tries = 0;
				handshake = setInterval( function () {
					if ( connected || ++tries > 40 ) { clearInterval( handshake ); return; }
					iframe.contentWindow.postMessage( JSON.stringify( { event: 'listening', id: 'hero', channel: 'widget' } ), '*' );
				}, 250 );
				// Telefon otomatik oynatmayı tamamen engellerse (ör. düşük güç modu) siyah ekran kalmasın.
				setTimeout( function () {
					if ( ! hero.classList.contains( 'is-playing' ) ) {
						hero.classList.add( 'is-static' );
						var still = hero.querySelector( '.hero-poster[data-src]' );
						if ( still && ! still.src ) { still.src = still.getAttribute( 'data-src' ); }
					}
				}, 4000 );
			} );
			ytBox.appendChild( iframe );
		} else if ( ytBox ) {
			setPaused( true );
		}

		if ( btn ) {
			btn.addEventListener( 'click', function () { setPaused( ! paused ); } );
		}
	}

	/* Hafif YouTube oynatıcı: tıklayınca gerçek oynatıcıyı yükle. */
	document.querySelectorAll( '[data-yt-lite]' ).forEach( function ( el ) {
		// Yüksek çözünürlüklü kapak yoksa YouTube 120px gri yer tutucu döndürür: standart kapağa geç.
		var thumb = el.querySelector( 'img' );
		var fixThumb = function () {
			if ( thumb.naturalWidth && thumb.naturalWidth <= 120 && thumb.src.indexOf( 'maxresdefault' ) > -1 ) {
				thumb.src = thumb.src.replace( 'maxresdefault', 'hqdefault' );
			}
		};
		if ( thumb ) {
			thumb.complete ? fixThumb() : thumb.addEventListener( 'load', fixThumb );
		}

		el.addEventListener( 'click', function () {
			var id = el.getAttribute( 'data-yt-lite' );
			var f = document.createElement( 'iframe' );
			f.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1&rel=0';
			f.title = el.getAttribute( 'aria-label' ) || 'YouTube';
			f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
			f.allowFullscreen = true;
			el.replaceWith( f );
		} );
	} );

	/* Galeri ışık kutusu. */
	var links = Array.prototype.slice.call( document.querySelectorAll( '[data-lightbox]' ) );
	if ( links.length ) {
		var box = document.createElement( 'dialog' );
		box.className = 'lightbox';
		box.setAttribute( 'aria-label', 'Fotoğraf' );
		box.innerHTML =
			'<img alt="">' +
			'<button class="lb-btn lb-close" type="button" aria-label="Kapat"><svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4-6.3-6.3-6.3 6.3-1.4-1.4L9.2 12 2.9 5.7l1.4-1.4 6.3 6.3 6.3-6.3z"/></svg></button>' +
			'<button class="lb-btn lb-prev" type="button" aria-label="Önceki"><svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="m15.4 7.4-1.4-1.4-6 6 6 6 1.4-1.4L10.8 12z"/></svg></button>' +
			'<button class="lb-btn lb-next" type="button" aria-label="Sonraki"><svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M8.6 16.6 10 18l6-6-6-6-1.4 1.4 4.6 4.6z"/></svg></button>' +
			'<p class="lb-count"></p>';
		document.body.appendChild( box );

		var img = box.querySelector( 'img' );
		var count = box.querySelector( '.lb-count' );
		var index = 0;
		var show = function ( i ) {
			index = ( i + links.length ) % links.length;
			img.src = links[ index ].href;
			var thumb = links[ index ].querySelector( 'img' );
			img.alt = thumb ? thumb.alt : '';
			count.textContent = ( index + 1 ) + ' / ' + links.length;
		};

		links.forEach( function ( a, i ) {
			a.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				show( i );
				box.showModal();
			} );
		} );
		box.querySelector( '.lb-close' ).addEventListener( 'click', function () { box.close(); } );
		box.querySelector( '.lb-prev' ).addEventListener( 'click', function () { show( index - 1 ); } );
		box.querySelector( '.lb-next' ).addEventListener( 'click', function () { show( index + 1 ); } );
		box.addEventListener( 'click', function ( e ) { if ( e.target === box ) { box.close(); } } );
		box.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowLeft' ) { show( index - 1 ); }
			if ( e.key === 'ArrowRight' ) { show( index + 1 ); }
		} );
		var startX = null;
		box.addEventListener( 'touchstart', function ( e ) { startX = e.touches[ 0 ].clientX; }, { passive: true } );
		box.addEventListener( 'touchend', function ( e ) {
			if ( startX === null ) { return; }
			var dx = e.changedTouches[ 0 ].clientX - startX;
			if ( Math.abs( dx ) > 40 ) { show( index + ( dx < 0 ? 1 : -1 ) ); }
			startX = null;
		} );
	}

	/* Bölümler görünür olunca yumuşak giriş. */
	if ( ! reduceMotion && 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( en ) {
				if ( en.isIntersecting ) {
					en.target.classList.add( 'is-in' );
					io.unobserve( en.target );
				}
			} );
		}, { rootMargin: '0px 0px -10% 0px' } );
		document.querySelectorAll( '.section' ).forEach( function ( s ) {
			s.classList.add( 'will-reveal' );
			io.observe( s );
		} );
	}
} )();
