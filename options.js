/* - - - For the Facebook Feed Grabber Options page. - - - */

jQuery(document).ready(function($) {

	// 	The fields we'll be accessing.
	var v_span = jQuery('#ffg-verify').siblings('span');
	var verify = jQuery('#ffg-verify');

	// The loading image.
	var loading = '<img alt="" id="ajax-loading" class="ajax-loading icon" src="'+ ffg_options.wpurl +'/wp-admin/images/loading.gif" style="visibility: visible;" />';
	var no = '<img alt="" class="icon" src="'+ ffg_options.wpurl +'/wp-admin/images/no.png" />';
	var yes = '<img alt="" class="icon" src="'+ ffg_options.wpurl +'/wp-admin/images/yes.png" />';

	// When they click the verify button
	verify.click(function() {
		// Disable the button
		jQuery(this).attr('disabled', 'disable')
		
		// Empty Description span and display loading icon
		v_span
			.empty()
			.append(loading);
		
		// The data to send to the server
		var data = {
			action: 'ffg_verif_app_cred',
			secure: ffg_options.nonce,
			app_id: $('#ffg-app-id').val(),
			secret: $('#ffg-secret').val()
		};
		
		// Make request.
		jQuery.post(ajaxurl, data, function(response) {

			// if invalid 
			if ( response.search('/Fatal error/') != '-1' ) {
				
				v_span
					.empty()
					.append(no +'Invalid App Id or Secret');
				
			} else if ( response.substring(0,3).search('^e:[0-9]$') == '0' ) {
				
				v_span
					.empty()
					.append(no + response.substring(4));
				
			// if valid
			} else {
				
				v_span
					.empty()
					.append(yes +' '+ response);
				
			}
			
			// Enable the button
			verify.removeAttr('disabled');
			
		});
	})

});
