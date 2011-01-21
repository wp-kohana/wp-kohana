(function($){

	function check_pass_strength() {
		var pass1 = $('#pass1').val(), user = $('#user_login').val(), pass2 = $('#pass2').val(), strength;

		$('#pass-strength-result').removeClass('short bad good strong');
		if ( ! pass1 ) {
			$('#pass-strength-result').html( pwsL10n.empty );
			return;
		}

		strength = passwordStrength(pass1, user, pass2);

		switch ( strength ) {
			case 2:
				$('#pass-strength-result').addClass('bad').html( pwsL10n['bad'] );
				break;
			case 3:
				$('#pass-strength-result').addClass('good').html( pwsL10n['good'] );
				break;
			case 4:
				$('#pass-strength-result').addClass('strong').html( pwsL10n['strong'] );
				break;
			case 5:
				$('#pass-strength-result').addClass('short').html( pwsL10n['mismatch'] );
				break;
			default:
				$('#pass-strength-result').addClass('short').html( pwsL10n['short'] );
		}
	}

	$(document).ready( function() {
		$('#pass1').val('').keyup( check_pass_strength );
		$('#pass2').val('').keyup( check_pass_strength );
		$('.color-palette').click(function(){$(this).siblings('input[name=admin_color]').attr('checked', 'checked')});
		$('#nickname').blur(function(){
			var str = $(this).val() || $('#user_login').val();
			var select = $('#display_name');
			var sel = select.children('option:selected').attr('id');
			select.children('#display_nickname').remove();
			if ( ! select.children('option[value=' + str + ']').length )
				select.append('<option id="display_nickname" value="' + str + '">' + str + '</option>');
			$('#'+sel).attr('selected', 'selected');
		});
		$('#first_name, #last_name').blur(function(){
			var select = $('#display_name');
			var first = $('#first_name').val(), last = $('#last_name').val();
			var sel = select.children('option:selected').attr('id');
			$('#display_firstname, #display_lastname, #display_firstlast, #display_lastfirst').remove();
			if ( first && ! select.children('option[value=' + first + ']').length )
				select.append('<option id="display_firstname" value="' + first + '">' + first + '</option>');
			if ( last && ! select.children('option[value=' + last + ']').length )
				select.append('<option id="display_lastname" value="' + last + '">' + last + '</option>');
			if ( first && last ) {
				if ( ! select.children('option[value=' + first + ' ' + last + ']').length )
					select.append('<option id="display_firstlast" value="' + first + ' ' + last + '">' + first + ' ' + last + '</option>');
				if ( ! select.children('option[value=' + last + ' ' + first + ']').length )
					select.append('<option id="display_lastfirst" value="' + last + ' ' + first + '">' + last + ' ' + first + '</option>');
			}
			$('#'+sel).attr('selected', 'selected');
		});
    });

})(jQuery);
