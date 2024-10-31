function ValidateRateThisSite() {
	
	// Variable declaration
	var error = false;
	
	var options = document.forms["frontrts"]["rts_options"].value;
	var email = document.forms["frontrts"]["rts_emails"].value;
	
	// Form field validation
	if (typeof options !== 'undefined') {
		if(options.length == 0){
			var error = true;
			jQuery('#options_error').fadeIn(500);
			return false;
		}else{
			jQuery('#options_error').fadeOut(500);
		}
	}
	
	if(typeof email !== 'undefined') {
		var atpos = email.indexOf("@");
		var dotpos = email.lastIndexOf(".");
		if(email.length == 0) {
			var error = true;
			jQuery('#email_error').fadeIn(500);
			return false;
		} else if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length) {
			var error = true;
			jQuery('#email_error').text('Not a valid e-mail address.');
			jQuery('#email_error').fadeIn(500);
			return false;
		} else {
			jQuery('#email_error').fadeOut(500);
		}
	}			


}
