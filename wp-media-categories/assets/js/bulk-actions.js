( function( $ ) {
	'use strict';

	$( function() {
		var settings = window.wpMediaCategoriesBulkActions || {};

		if ( ! settings.taxonomy || ! settings.actions ) {
			return;
		}

		$( '#posts-filter' ).prepend(
			$( '<input>', {
				type: 'hidden',
				id: 'bulk_tax_cat',
				name: 'bulk_tax_cat',
				value: settings.taxonomy
			} ),
			$( '<input>', {
				type: 'hidden',
				id: 'bulk_tax_id',
				name: 'bulk_tax_id',
				value: ''
			} )
		);

		$( '#bulk-action-selector-top, #bulk-action-selector-bottom' ).on( 'change', function() {
			$( '#bulk_tax_id' ).val( $( this ).find( 'option:selected' ).attr( 'option_slug' ) );
		} );

		$.each( settings.actions, function( termId, label ) {
			$( '<option>', {
				value: 'bulk_toggle',
				text: label
			} ).attr( 'option_slug', termId ).appendTo( "select[name='action'], select[name='action2']" );
		} );
	} );
}( jQuery ) );
