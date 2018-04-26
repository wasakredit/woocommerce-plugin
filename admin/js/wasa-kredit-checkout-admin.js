(function( $ ) {
	'use strict';

	$(document).ready(function () {
		// Sell Countries
		$( 'select#woocommerce_allowed_countries' ).change( function() {
			if ( 'specific' === $( this ).val() ) {
				$( this ).closest('tr').next( 'tr' ).hide();
				$( this ).closest('tr').next().next( 'tr' ).show();
			} else if ( 'all_except' === $( this ).val() ) {
				$( this ).closest('tr').next( 'tr' ).show();
				$( this ).closest('tr').next().next( 'tr' ).hide();
			} else {
				$( this ).closest('tr').next( 'tr' ).hide();
				$( this ).closest('tr').next().next( 'tr' ).hide();
			}
		}).change();

		$('#wasa_kreditwasa_kredit_enabled_for_countries').select2({
			placeholder: 'Choose countries'
		});
	});

})( jQuery );
