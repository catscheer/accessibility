jQuery(document).ready( function($) {

	$.ajax( {
			type: 'POST',
			url: clr_fontchanger.ajax_url,
			data: { 
				action : 'clr_stored_fontchange',
			},
			dataType: 'json',
			success: function(result) {
				$('#page').css({'color': result.data.fontcolor, 'font-size': result.data.fontsize });
			},
	});
			
	$('#font-form').on('submit', function(e) {

		e.preventDefault();
		var formData = $( '#font-form' ).serialize();
		$.ajax( {
			type: 'POST',
			url: clr_fontchanger.ajax_url,
			data: { 
				action : 'clr_submit_fontchange',
				value: formData,
			},
			dataType: 'json',
			success: function(result) {
				if ( result.success == true ) {
					$('#page').css({'color': result.data.fontcolor, 'font-size': result.data.fontsize });
				}
				else alert(result.data);
			},
		
		});

	});

})
