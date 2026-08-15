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
	$( document ).on( 'click', '.gmr-select-multiple', function () {
		const field = $( this ).closest( '.gmr-multi-media-field' );
		const frame = wp.media( { title: 'Seleccionar y ordenar archivos', button: { text: 'Usar archivos' }, multiple: true } );
		frame.on( 'open', function () { field.find( 'input' ).val().split( ',' ).filter( Boolean ).forEach( id => frame.state().get( 'selection' ).add( wp.media.attachment( id ) ) ); } );
		frame.on( 'select', function () {
			const items = frame.state().get( 'selection' ).toJSON();
			field.find( 'input' ).val( items.map( item => item.id ).join( ',' ) );
			field.find( '.gmr-media-preview' ).html( items.map( item => '<span>' + ( item.sizes && item.sizes.thumbnail ? '<img src="' + item.sizes.thumbnail.url + '" alt="">' : item.filename ) + '</span>' ).join( '' ) );
		} );
		frame.open();
	} );
	$( document ).on( 'click', '.gmr-clear-multiple', function () { const field = $( this ).closest( '.gmr-multi-media-field' ); field.find( 'input' ).val( '' ); field.find( '.gmr-media-preview' ).empty(); } );
}( jQuery ) );
