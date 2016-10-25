var $j = jQuery.noConflict();

$j(document).ready(function($j){
		$j(".tkgp_radio li input[type='radio']").addClass('tkgp_radio_hidden');
		$j(".tkgp_radio li input[type='radio']").click(handler_tkgp_radio); //обработчик для переключателей
		$j(".tkgp_radio li input[type='radio']").click(handler_tkgp_select_radio);
		$j(".tkgp_user_add").click(handler_tkgp_add_user);
		
		if($j(".tkgp_radio li input[type='radio']:checked").length == 0) {
			$j(".tkgp_radio li input[type='radio'][checked='true']").addClass('tkgp_radio_checked').click();}
		else {
			$j(".tkgp_radio li input[type='radio']:checked").addClass('tkgp_radio_checked').click();}
	}
);

function tkgpUrlVars() {
	var pair = window.location.href.slice(window.location.href.indexOf('?')).split(/[&?]{1}/);
	var out = {};
		
	for(var i = 0; i < pair.length; ++i) {
		if(pair[i] != '') {
			out[ pair[i].split('=')[0] ] = pair[i].split('=')[1];
		}	
	}
	
	return out;
}

function handler_tkgp_radio() {
	$j(".tkgp_radio li input[type='radio']").removeClass('tkgp_radio_checked');
	$j(".tkgp_radio li input[type='radio']").removeAttr('checked');
	$j(this).addClass('tkgp_radio_checked');
	$j(this).attr('checked','true');
		
}

function handler_tkgp_select_radio() {
	if(this.value === '3') {
		//alert($j('.tkgp_group_select').length);
		$j('.tkgp_group_select option').attr('disabled','');
		$j('.tkgp_group_select option[selected=""]').removeAttr('selected');
		$j('.tkgp_group_select [value="0"]').attr('selected','').removeAttr('disabled');
	}
	else $j('.tkgp_group_select option').removeAttr('disabled');
}

function handler_tkgp_search(e) {
	if($j(this).val().length >= 2 && e.keyCode !== 27) {
		$j.ajax({url: ajaxurl,
				 type: 'POST',
				 async: true,
				 data: {action: 'tkgp_get_user', 
				 		tkgp_ufilter: $j(this).val(),
				 		post_id: tkgpUrlVars()['post']
				 	   },
				 success: show_search_result
			});
	}
}

function show_search_result(resp) {
	alert(resp);
}

function handler_tkgp_add_user() {
	if($j('#tkgp_modal_user').length == 0) {
		$j.post(ajaxurl,{action:'tkgp_get_user', post_id: tkgpUrlVars()['post']}, 
				function(resp){
					var $j = jQuery.noConflict();
					$j('body').append(resp);
					$j('#tkgp_modal_user #modal_close, #tkgp_overlay').click(function(){
							$j('#tkgp_modal_user, #tkgp_overlay').removeAttr('style');
						});
					$j('#tkgp_search').keypress(handler_tkgp_search);
					$j('#tkgp_modal_user, #tkgp_overlay').css('display', 'block');
				}
		);
	} else {
		$j('#tkgp_modal_user, #tkgp_overlay').css('display', 'block');
	}
}
