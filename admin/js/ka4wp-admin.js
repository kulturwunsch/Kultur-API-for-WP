(function( $ ) {
	'use strict';
	
$(document).ready(function() {
	
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
	
	
	//$("#wpcf7-sf-select-apiendpoint").on('load', function() {
	
	$( document ).ready(function() {
		
		if($("#wpcf7-sf-select-apiendpoint").find(":selected").val() == "")
		{
			$('#cf7-ka4wp-no-api-definition').css("display", "");
			$('#cf7-ka4wp-api-definition').css("display", "none");
		} else {
			$('#cf7-ka4wp-no-api-definition').css("display", "none");
			$('#cf7-ka4wp-api-definition').css("display", "");
				
			if($('#wpcf7-sf-predefined-mapping option').length > 1)
			{
				$('#cf7-ka4wp-api-mapping-type').css("display", "");
				
				if($('#wpcf7-sf-predefined-mapping').find(":selected").val() == "")
				{
					$('#cf7-ka4wp-api-mapping-predefined').css("display", "none");
					$('#cf7-ka4wp-api-mapping-custom').css("display", "");
				} else {
					$('#cf7-ka4wp-api-mapping-predefined').css("display", "");
					$('#cf7-ka4wp-api-mapping-custom').css("display", "none");
				}
			} else {
				$('#cf7-ka4wp-api-mapping-type').css("display", "none");
				$('#cf7-ka4wp-api-mapping-predefined').css("display", "none");
				$('#cf7-ka4wp-api-mapping-custom').css("display", "");
			}
		}
	});
	
	/* change view of api configuration options */
	$("#wpcf7-sf-select-apiendpoint").on('change', function() {
		
		if($(this).find(":selected").val() == "")
		{
			$('#cf7-ka4wp-no-api-definition').css("display", "");
			$('#cf7-ka4wp-api-definition').css("display", "none");
		} else {

			var post_id = $(this).val();
			var data = {
				'post_id': post_id,
				'form_id': $("#post_ID").val(),
				'ka4wp-nonce': ajax_object.nonce,
	            'action': 'ka4wp_get_selected_endpoint'
			};
			
			var ka4wp_response = jQuery.ajax({
									type: "POST",
									url: ajax_object.ajax_url,
									data: data,
								});
			
			ka4wp_response.done(function(result){
				//var json_obj = JSON.parse(result);
				var json_obj = result;
				
				if(json_obj === undefined) {
					return;
				}
				
				if(json_obj === undefined || json_obj.mappings.length < 1)
				{
					$('#cf7-ka4wp-api-mapping-custom').css("display", "");
					$('#cf7-ka4wp-api-mapping-predefined').css("display", "none");
				}
				
				$('#wpcf7-sf-predefined-mapping option').each(function() {
					if ( $(this).val() != '' ) {
						$(this).remove();
					}
				});
				
				$.each(json_obj.mappings, function (i, item) {
					$("#wpcf7-sf-predefined-mapping").append(new Option(item.name, item.value, false, false));
				});
				
				if(json_obj.api_type == "wunsch.events" || json_obj.predefined == 1)
				{
					$('#cf7-ka4wp-api-mapping-custom').css("display", "none");
					$('#cf7-ka4wp-api-mapping-type').css("display", "");
					$('#cf7-ka4wp-api-mapping-predefined').css("display", "none");
				} else {
					$('#cf7-ka4wp-api-mapping-custom').css("display", "");
					$('#cf7-ka4wp-api-mapping-type').css("display", "none");
					$('#cf7-ka4wp-api-mapping-predefined').css("display", "none");
				}
				
				$('#cf7-ka4wp-no-api-definition').css("display", "none");
				$('#cf7-ka4wp-api-definition').css("display", "");
				$("#wpcf7-sf-predefined-mapping").trigger("change");
			});
		}

	});
	
	/* change view of api configuration options */
	$("#wpcf7-sf-predefined-mapping").on('change', function() {
		
		if($(this).find(":selected").val() == "")
		{
			$('#cf7-ka4wp-no-api-definition').css("display", "none");
			$('#cf7-ka4wp-api-definition').css("display", "");
			$('#cf7-ka4wp-api-mapping-custom').css("display", "");
			$('#cf7-ka4wp-api-mapping-predefined').css("display", "none");
		} else {
			$('#cf7-ka4wp-api-mapping-custom').css("display", "none");
			$('#cf7-ka4wp-no-api-definition').css("display", "none");
			$('#cf7-ka4wp-api-mapping-predefined').css("display", "");
			$('#cf7-ka4wp-api-definition').css("display", "");
			
			var mapping_key = $(this).val();
			var post_id = $("#wpcf7-sf-select-apiendpoint").find(":selected").val();
			var data = {
				'mapping_key': mapping_key,
				'post_id': post_id,
				'form_id': $("#post_ID").val(),
				'ka4wp-nonce': ajax_object.nonce,
	            'action': 'ka4wp_get_predefined_mapping'
			};

			var ka4wp_response = jQuery.ajax({
									type: "POST",
									url: ajax_object.ajax_url,
									data: data,
								});

			ka4wp_response.done(function(result){
				
				/*$('.predefined-field-mapping option').each(function() {
					if ( $(this).val() != '' ) {
						$(this).remove();
					}
				});*/
				
				//var json_obj = JSON.parse(result);
				var json_obj = result;
				
				if(json_obj === undefined || json_obj.mappings.length < 1)
				{
					$('#cf7-ka4wp-api-mapping-custom').css("display", "");
					$('#cf7-ka4wp-api-mapping-predefined').css("display", "none");
				}
				
				/*$.each(json_obj.mappings, function (i, item) {
					$(".predefined-field-mapping").append(new Option(item.name, item.value, false, false));
				});*/
				
				$(".predefined-field-mapping").each(function () {
					var that = $(this);
					var selectedValue = that.find(":selected").val();
					$(this).find("option").each(function() {
						if ( $(this).val() != '' ) {
							$(this).remove();
						}
					});
					$.each(json_obj.mappings, function (i, item) {
						let isSelected = false;
						if(selectedValue == item.value) { isSelected = true }
						that.append(new Option(item.name, item.value, false, isSelected));
						console.log('DEFAUT: '+selectedValue+'; OLD: '+item.value);
					});	
				});
			});
		}

	});
	
	function detectMappingChange()
	{
		if($("#wpcf7-sf-predefined-mapping").find(":selected").val() == "")
		{
			$('#cf7-ka4wp-no-api-definition').css("display", "none");
			$('#cf7-ka4wp-api-definition').css("display", "");
			$('#cf7-ka4wp-api-mapping-custom').css("display", "");
			$('#cf7-ka4wp-api-mapping-predefined').css("display", "none");
		} else {
			$('#cf7-ka4wp-api-mapping-custom').css("display", "none");
			$('#cf7-ka4wp-no-api-definition').css("display", "none");
			$('#cf7-ka4wp-api-mapping-predefined').css("display", "");
			$('#cf7-ka4wp-api-definition').css("display", "");
		}
	}

});

})( jQuery );
