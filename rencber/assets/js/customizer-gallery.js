/* Özelleştiricide galeri seçici: WordPress ortam kütüphanesinin galeri düzenleyicisini açar. */
( function ( $ ) {
	$( document ).on( 'click', '.rencber-gallery-edit', function ( e ) {
		e.preventDefault();
		var $wrap = $( this ).closest( '.customize-control' );
		var $input = $wrap.find( '.rencber-gallery-input' );
		var $preview = $wrap.find( '.rencber-gallery-preview' );
		var ids = $input.val() ? $input.val().split( ',' ) : [];
		var shortcode = '[gallery ids="' + ids.join( ',' ) + '"]';

		var frame = ids.length
			? wp.media.gallery.edit( shortcode )
			: wp.media( { frame: 'post', state: 'gallery-library', multiple: true, library: { type: 'image' } } ).open();

		frame.state( 'gallery-edit' ).on( 'update', function ( selection ) {
			var newIds = selection.models.map( function ( m ) { return m.id; } );
			$preview.empty();
			selection.models.forEach( function ( m ) {
				var s = m.get( 'sizes' );
				var url = s && s.thumbnail ? s.thumbnail.url : m.get( 'url' );
				$preview.append( $( '<img>' ).attr( 'src', url ).css( { width: '100%', height: 'auto', display: 'block' } ) );
			} );
			$input.val( newIds.join( ',' ) ).trigger( 'change' );
		} );
	} );

	$( document ).on( 'click', '.rencber-gallery-clear', function ( e ) {
		e.preventDefault();
		var $wrap = $( this ).closest( '.customize-control' );
		$wrap.find( '.rencber-gallery-preview' ).empty();
		$wrap.find( '.rencber-gallery-input' ).val( '' ).trigger( 'change' );
	} );
} )( jQuery );
