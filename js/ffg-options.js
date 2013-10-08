/* - - - For the Facebook Feed Grabber Options page. - - - */

jQuery(document).ready(function($) {

	// 	The fields we'll be accessing.
	var v_span = jQuery('#ffg-verify').siblings('span');
	var verify = jQuery('#ffg-verify');
	var proxyDisabled = jQuery('#proxyDisabled');
	var proxyEnabled = jQuery('#proxyEnabled');
	var proxyURL = jQuery('#proxyEnabled input');
	
	// The loading image.
	var loading = '<img alt="" id="ajax-loading" class="ajax-loading icon" src="'+ ffg_options.wpurl +'/wp-admin/images/loading.gif" style="visibility: visible;" />';

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

			// Display the results.
			v_span
				.empty()
				.append(response);
				
			// Enable the button
			verify.removeAttr('disabled');
			
		});
	})

	if ( proxyURL.val() == '' ) {
		proxyDisabled.children("a").click(function() {
			proxyDisabled.hide('fast');
			proxyEnabled.show('fast');
		})
	}
});
