$(function() {
	// Array to save user inputs
	var user_input = [];
	var user_input_pointer;
	
	$('#show_help').click(function() {
		$('#help').toggle(0, function(){
			if ($(this).is(':visible')) {
				$('#show_help').text('Hide Help');
			}
			else {
				$('#show_help').text('Show Help');
			}
		});
	});
	// Enable input
	$('input').prop('disabled',false)
		// Use up/down keys to cycle through past questions
		.keydown(function(e) {
			// Up
			if (e.keyCode == 38) {
				if (user_input_pointer > 0) {
					user_input_pointer--;
					$(this)
						.val(user_input[user_input_pointer])
						.trigger('change');
				}
				//console.log(user_input_pointer);
				return false;
			}
			// Down
			if (e.keyCode == 40) {
				if (user_input_pointer <= user_input.length-1 && user_input_pointer >= 0) {
					user_input_pointer++;
					$(this)
						.val(user_input[user_input_pointer])
						.trigger('change');
				}
				//console.log(user_input_pointer);
				return false;
			}
		})
		.ajaxStart(function() {
			$(this).prop('disabled',true);
		})
		.ajaxStop(function() {
			$(this).prop('disabled',false);
		})
		.change(function() {
			var input = jQuery(this).val();
		
			// If this is a calculation input use JS
			var calc_pattern = new RegExp("^([0-9\(\)\.]+[\-\/\+\*%]?)+[0-9\(\)\.]+$");
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
							$('#response').html(data.response.output);	
							
							/*
							// Show options if available
							if (data.options) {
								$('#options').empty();
								$.each(data.options, function(index, value) {
									$('#options').append('<a href="javascript:;"><span class="label label-info" style="margin-right:7px;">'+value+'<span></a>');
								});
							
								$('#options a').click(function() {
									$('#input').val(jQuery(this).find('span').text()).change();
								});
							}
							*/

							user_input.push(data.input);
							user_input_pointer = user_input.length - 1;
							$('#command_history').append('<p>'+user_input[user_input.length - 1]+'</p>');
					  	},
						error: function(e) {
							$('#response').text("Couldn't talk to Frank IO.");
						}
				});	
			}
			$(this).val('');
		});	
});
