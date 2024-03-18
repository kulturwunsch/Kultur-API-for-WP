(function( $ ) {
	'use strict';
$(document).ready(function(){
	/* change view of api configuration options */
	$("#ka4wp_api_type").on('change', function() {

		$('.ka4wp_detailed_api_settings').css("display", "none");
		
		switch($(this).find(":selected").val()) {
			case 'wunsch.events':
				$('#ka4wp_api_settings_wunschevents').css("display", "");
			break;
			case 'other':
				$('#ka4wp_api_settings_other').css("display", "");
			break;
		}
	}).trigger("change");
	
	/* change view of api configuration options */
	$("#wpcf7-sf-select-apiendpoint").on('change', function() {
		
		if($(this).find(":selected").val() == "")
		{
			$('#cf7-ka4wp-no-api-definition').css("display", "");
			$('#cf7-ka4wp-api-definition').css("display", "none");
		} else {
			$('#cf7-ka4wp-no-api-definition').css("display", "none");
			$('#cf7-ka4wp-api-definition').css("display", "");
			
			var post_id = $(this).val();
			var post_id = $('#post_ID').val();
			var data = {
				'form_id': form_id,
				'post_id': post_id,
				'ka4wp-nonce': ajax_object.nonce,
	            'action': 'cf7_to_any_api_get_form_field'
			};

			var ka4wp_response = jQuery.ajax({
									type: "POST",
									url: ajax_object.ajax_url,
									data: cf7anyapi_data,
								});

			ka4wp_response.done(function(result){
				var json_obj = JSON.parse(result);
                $('#cf7anyapi-form-fields').html(json_obj);
				
				switch($(this).find(":selected").val()) {
					case 'wunsch.events':
						$('#ka4wp_api_settings_wunschevents').css("display", "");
					break;
					case 'other':
						$('#ka4wp_api_settings_other').css("display", "");
					break;
				}
				
			});
		}

	}).trigger("change");

});

})( jQuery );
