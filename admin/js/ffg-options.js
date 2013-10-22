/* - - - For the Facebook Feed Grabber Options page. - - - */

// Currently these names aren't necessarily the most descriptive.
// This is my first use of Backbone.js so it may be a bit rough.

// Object for storing collections in.
ffgOptions.panels = new Array();

// When the page is loaded do everything else.
jQuery( document ).ready(function( $ ) {

	// Get our data from the global namespace. 
	var ffg = window.ffgOptions;

	// The Model for our options page.
	ffgModel = Backbone.Model.extend({

		// Default args
		defaults : {},

		// The ajax URL
		url : ajaxurl,

		// Get our attributes ready
		toJSON : function() {
			var attrs = _.clone( this.attributes );
			attrs = attrs.json;
			return attrs;
		},

	});

	// Our collection of settings
	ffgCollection = Backbone.Collection.extend({
		model : ffgModel
	});

	// Initiate our view object
	var ffgViews = {};

	// General panel view manipulation
	// Currently this houses anything for the plugin options.
	ffgViews.panels = Backbone.View.extend({

		initialize : function (  ) {
			this.collection.each( this.render, this );
		},

		render : function ( model ) {
			var attr = model.attributes;
			var section = new ffgViews.section({
				model:model,
				el: attr.tagID
			});
			section.render();
		},

	});

	// Setting sections. 
	// This actually more akin to an individual setting.
	ffgViews.section = Backbone.View.extend({
		render : function (  ) {
			var item = new ffgViews.item({ model:this.model });
			this.$el.html( item.render().el );
		}
	});

	// The content to be displayed
	ffgViews.item = Backbone.View.extend({
		tagName : 'span',

		// Get the template from the DOM
		template : function () {
			var template = $(this.model.attributes.templateID).html();
			var data = this.model.toJSON();
			return _.template( template, data );
		},

		// Render the feed name
		render : function () {
			this.$el.html( this.template() );
			return this;
		},

	});

	// Initiate our collection
	var panels = new ffgCollection( ffg.panels );

	// Initiate our views.
	var panelViews = new ffgViews.panels({ collection:panels });

	// // 	The fields we'll be accessing.
	// var v_span = jQuery('#ffg-verify').siblings('span');
	// var verify = jQuery('#ffg-verify');
	// var select_feed = jQuery('#ffg-select-feed');
	// var sf_span = jQuery('#ffg-select-feed').parent();
	// var proxyDisabled = jQuery('#proxyDisabled');
	// var proxyEnabled = jQuery('#proxyEnabled');
	// var proxyURL = jQuery('#proxyEnabled input');
	
	// // The loading image.
	// var loading = '<img alt="" id="ajax-loading" class="ajax-loading icon" src="'+ ffg_options.wpurl +'/wp-admin/images/loading.gif" style="visibility: visible;" />';

	// // When they click the verify button
	// verify.click(function() {
	// 	// Disable the button
	// 	jQuery(this).attr('disabled', 'disable')
		
	// 	// Empty Description span and display loading icon
	// 	v_span
	// 		.empty()
	// 		.append(loading);
		
	// 	// The data to send to the server
	// 	var data = {
	// 		action: 'ffg_verif_app_cred',
	// 		secure: ffg_options.nonce_app_cred,
	// 		app_id: $('#ffg-app_id').val(),
	// 		secret: $('#ffg-secret').val()
	// 	};

	// 	// Make request.
	// 	jQuery.post(ajaxurl, data, function(response) {

	// 		// Display the results.
	// 		v_span
	// 			.empty()
	// 			.append(response);
				
	// 		// Enable the button
	// 		verify.removeAttr('disabled');
			
	// 	});
	// })

	// // When they click the verify button
	// select_feed.click(function( event ) {

	// 	event.preventDefault();
		
	// 	// Empty Description span and display loading icon
	// 	sf_span
	// 		.empty()
	// 		.append(loading);
		
	// 	// The data to send to the server
	// 	var data = {
	// 		action: 'ffg_select_feed',
	// 		secure: ffg_options.nonce_select_feed,
	// 	};

	// 	// Make request.
	// 	jQuery.post(ajaxurl, data, function(response) {

	// 		window.location.href = response;
	// 		return;
	// 		// Display the results.
	// 		sf_span
	// 			.empty()
	// 			.append(response);
			
	// 	});
	// })

	// // Select a feed that you'r an admin.

	// // If proxy is disabled then add an event to enable it.
	// if ( proxyURL.val() == '' ) {
	// 	proxyDisabled.children("a").click(function() {
	// 		proxyDisabled.hide('fast');
	// 		proxyEnabled.show('fast');
	// 	})
	// }

	
});