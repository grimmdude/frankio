$(function() {
	// Enable input
	$('input').prop('disabled',false);
	
	// Array to save user inputs
	var user_input = [];
	var user_input_pointer;
	
	// Use up/down keys to cycle through past questions
	$('input').keydown(function(e) {
		// Up
		if (e.keyCode == 38) {
			if (user_input_pointer > 0) {
				user_input_pointer--;
				$(this).val(user_input[user_input_pointer]);
			}
			//console.log(user_input_pointer);
			return false;
		}
		// Down
		if (e.keyCode == 40) {
			if (user_input_pointer <= user_input.length-1 && user_input_pointer >= 0) {
				user_input_pointer++;
				$(this).val(user_input[user_input_pointer]);
			}
			//console.log(user_input_pointer);
			return false;
		}
	});
	
	
	$('input').ajaxStart(function() {
		$(this).prop('disabled',true);
	});
	$('input').ajaxStop(function() {
		$(this).prop('disabled',false);
	});
	$('input').change(function() {
		var input = jQuery(this).val();
		
		// If this is a calculation input use JS
		var calc_pattern = new RegExp("^([0-9\(\)]+[\-\/\+\*]?)+[0-9\(\)]+$");
		var number_pattern = new RegExp("^[0-9\(\)]+[\-\/\+\*]?$")
		if (number_pattern.test(input)) {
			$('#response').text('I can do calculations, try 4 + 4');
		}
		else if (calc_pattern.test(input)) {
			$('#response').text(input + '=' + eval(input));
		}
		else {
			$.ajax({
			  	url: 'index.php',
				type: 'POST',
				dataType: "json",
				data: { 
					action: "true",
					input: input,
					salt: jQuery('#salt').val()
					},
			  		success: function(data) {
						if (Object.prototype.toString.call( data.output ) === '[object Array]') {
							$('#response').html('<ul></ul>');
							for (var i = 0; i < data.output.length; i++) {
								$('#response ul').append('<li>' + data.output[i] + '</li>');
							}
						}
						else {
						$('#response').html(data.output);	
						}
						
						$('#response a').attr('target','_blank');
						if (data.type == 'wiki') {
							$('#response').css('font-size','14px');
						}
						else {
							$('#response').css('font-size','');
						}
						
						// Show options if available
						if (data.options) {
							$('#options').empty();
							$.each(data.options, function(index, value) {
								$('#options').append('<a href="javascript:;"><span class="label label-info" style="margin-right:7px;">'+value+'<span></a>');
							});
							
							$('#options a').click(function() {
								jQuery('#input').val(jQuery(this).find('span').text()).change();
							});
						}

						user_input.push(data.question);
						user_input_pointer = user_input.length - 1;
				  	}
			});	
		}
		$(this).val('');
	});	
});
