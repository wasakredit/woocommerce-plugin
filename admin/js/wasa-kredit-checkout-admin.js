(function( $ ) {
	'use strict';

	$(document).ready(function () {

		var $lang_selector = $('#wasa_kreditwasa_kredit_countries');

		if ($lang_selector.length > 0 && typeof(jQuery().select2) === "function") {
			// Make multi option selector beautyful with select2
			$lang_selector.select2({
				placeholder: 'Choose countries'
			});
		}
	});

})( jQuery );
