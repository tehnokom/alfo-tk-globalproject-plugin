var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
	tkgp_js_init();
	tkgp_image_preload();
});

function tkgp_image_preload() {
	tkgp_js_vars.images.forEach(function (item) {
		(new Image()).src = tkgp_js_vars.plug_url + '/images/' + item;
	});
}

function tkgp_js_init() {
	$j('#tkgp_vote_buttons div.tkgp_button').on('click', tkgp_handler_vote);
	$j('div.tkgp_button_reset').on('click', tkgp_handler_reset_vote);
	$j('.tkgp_edit_button').on('click',tkgp_handler_edit_project);
}

function tkgp_is_JSON(str) {
	try {
		$j.parseJSON(str);
	} catch (e) {
		return false;
	}
	return true;
}

function tkgp_handler_vote() {
	var vote = $j(this).children('input[name="user_vote"]').val();
	var vote_id = $j(this).parents().find('input[name="tkgp_vote_id"]').val();
	var vote_nonce = $j(this).parents().find('input[name="tkgp_vote_nonce"]').val();
	var post_id = $j(this).parents().find('input[name="tkgp_post_id"]').val();
	var wait_obj = $j(this).parents().find('#tkgp_vote_result');
	tkgp_wait_animate(wait_obj);
	
	$j.ajax({
                url: tkgp_js_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'tkgp_user_vote',
                    vote_id: vote_id,
                    post_id: post_id,
                    vote_variant: vote,
                    vote_nonce: vote_nonce
                }
            })
            .done(function (result) {
                tkgp_handler_vote_result(result, {vote_id: vote_id, vote_nonce: vote_nonce, post_id: post_id});
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            });
}

function tkgp_handler_reset_vote() {
	var vote_id = $j(this).parents().find('input[name="tkgp_vote_id"]').val();
	var vote_nonce = $j(this).parents().find('input[name="tkgp_vote_nonce"]').val();
	var post_id = $j(this).parents().find('input[name="tkgp_post_id"]').val();
	var wait_obj = $j(this).parents().find('#tkgp_vote_result');
	tkgp_wait_animate(wait_obj);

	$j.ajax({
                url: tkgp_js_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'tkgp_reset_user_vote',
                    post_id: post_id,
                    vote_id: vote_id,
                    vote_nonce: vote_nonce
                }
            })
            .done(function (result) {
                tkgp_handler_vote_result(result, {vote_id: vote_id, vote_nonce: vote_nonce, post_id: post_id});
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
                location.reload();
            });
}

function tkgp_handler_vote_result(result, args) {
	$j.ajax({
                url: tkgp_js_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'tkgp_get_vote_status',
                    post_id: args.post_id,
                    vote_id: args.vote_id,
                    vote_nonce: args.vote_nonce
                }
            })
            .done(function (new_html) {
                tkgp_update_vote(args.vote_id, new_html, result);
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
                location.reload();
            });
}

function tkgp_update_vote(vote_id, result, message, stage) {
	var res = $j.parseJSON(result);
	var mes = $j.parseJSON(message);
	
	if(stage === undefined) {
		var vr = $j('input[name="tkgp_vote_id"][value="' + vote_id + '"]').parents().find('#tkgp_vote_result');
		var color = mes.status === false ? '#f00' : '#22ff22' ;
		var img = mes.status === false ? 'err_status.png' : 'ok_status.png';
		$j(vr).find('#tkgp_message').text(mes.message)
									.css('color', color);
		
		$j(vr).find('#tkgp_icon').attr('src', tkgp_js_vars.plug_url + '/images/' + img);
		setTimeout(tkgp_update_vote, 2000, vote_id, result, '',1);
	} else if(stage === 1) {
		if(res.status === false) {
			location.reload();
		} else {
			$j('input[name="tkgp_vote_id"][value="' + vote_id + '"]').parents().find('#tkgp_vote_result').replaceWith(res.new_content);
		tkgp_js_init();
		}	
	}	
}

function tkgp_wait_animate(obj) {
	if(typeof obj == 'object') {
		$j(obj).prepend('<dev id="tkgp_modal_box" style="position: absolute; width: 99%; height: 96%;z-index: 999; background: rgba(45,45,45,0.6);box-sizing: border-box;">'
						+ '<img id="tkgp_icon" src="' + tkgp_js_vars.plug_url + '/images/load.gif" width="32px" style="margin-left: 48%; margin-top: 10%;"/>'
						+ '<div id="tkgp_message" style="display: inline-block; width: 100%; margin-top: 5px; color: #FFF; text-align: center;">' + tkgp_i18n.loading + '</div>'
						+ '</dev>'
						);
	}
}

function tkgp_handler_edit_project() {
	var edit_nonce = $j(this).find('input[name="tkgp_access_nonce"]').val();
	var post_id = $j(this).find('input[name="tkgp_post_id"]').val();
	var wait_obj = $j('body');
	tkgp_wait_animate(wait_obj);
	
	$j.ajax({
                url: tkgp_js_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'tkgp_get_project_editor',
                    post_id: post_id,
                    access_nonce: edit_nonce
                }
            })
            .done(function (data) {
                tkgp_show_project_editor(data, this);
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            });
}

function tkgp_show_project_editor(data, button) {
	if(tkgp_is_JSON(data)) { //если результат JSON, значит проблемы с доступом
		var res = $j.parseJSON(data);
		var modal_box = $j('#tkgp_modal_box');
		
		$j(modal_box).find('#tkgp_icon').attr('src', tkgp_js_vars.plug_url + '/images/' + 'err_status.png');
		$j(modal_box).find('#tkgp_message').text(res.message)
										   .css('color','#f00');
										   		
		setTimeout(tkgp_hide_wait_animate, 2000);
	} else { //иначе выводим полученную форму
		//tkgp_hide_wait_animate();

	}
}

function tkgp_handler_save_project_data() {
	
}

function tkgp_hide_wait_animate() {
	$j('#tkgp_modal_box').remove();
}
