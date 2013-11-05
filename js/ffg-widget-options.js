/* - - - For the Facebook Feed Grabber Widget Admin. - - - */

function ffg_setOnClick () {
	jQuery('.fbfeed-editoption').each(function(){
		jQuery(this).click(function(event){
			event.preventDefault();
			
			jQuery(this).parent().siblings('.option').show();
			jQuery(this).parent().hide();
			
		})
	})
}
