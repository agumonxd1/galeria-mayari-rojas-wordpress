( function ( $ ) {
	'use strict';
	$( document ).on( 'click', '.gmr-select-media', function () {
		const field = $( this ).closest( '.gmr-media-field' );
		const frame = wp.media( { title: 'Seleccionar imagen', button: { text: 'Usar imagen' }, multiple: false, library: { type: 'image' } } );
		frame.on( 'select', function () {
			const image = frame.state().get( 'selection' ).first().toJSON();
			const url = image.sizes && image.sizes.thumbnail ? image.sizes.thumbnail.url : image.url;
			field.find( 'input[type="hidden"]' ).val( image.id );
			field.find( '.gmr-media-preview' ).html( '<img src="' + url + '" alt="">' );
			field.find( '.gmr-remove-media' ).prop( 'hidden', false );
		} );
		frame.open();
	} );
	$( document ).on( 'click', '.gmr-remove-media', function () {
		const field = $( this ).closest( '.gmr-media-field' );
		field.find( 'input[type="hidden"]' ).val( '' );
		field.find( '.gmr-media-preview' ).empty();
		$( this ).prop( 'hidden', true );
	} );
}( jQuery ) );
