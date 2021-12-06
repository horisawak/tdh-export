jQuery( function( $ ) {
	$( document ).ready( function() {
		$( "#id_submit" ).click( function() {
			// Get the result of the validation
			let validation_flg = document.tdh_form.reportValidity();
			// Check for validation
			if ( validation_flg ) { // [OK] Success validation
				// Disable the submit button
				$(this).prop( "disabled", true );
				// Change the text of submit button
				$(this).prop( "value", "processing..." );
				// Display spinner
				$("#id_spinner").addClass("tdh-spinner-active");
				// Submit form
				document.tdh_form.submit();
			} else { // [ERR] validation failed
				; // [NOP] The html specification warning is displayed in the browser
			}
		});
	});
});
