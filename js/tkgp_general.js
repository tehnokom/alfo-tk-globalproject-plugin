var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
	tkgp_js_init();
});

function tkgp_js_init() {
	$j('#tkgp_vote_buttons div.tkgp_button').on('click', tkgp_handler_vote);
	$j('div.tkgp_button_reset').on('click', tkgp_handler_reset_vote);
}

function tkgp_handler_vote() {
	var vote = $j(this).children('input[name="user_vote"]').val();
	var vote_id = $j(this).parents().find('input[name="tkgp_vote_id"]').val();
	var vote_nonce = $j(this).parents().find('input[name="tkgp_vote_nonce"]').val();
	var post_id = $j(this).parents().find('input[name="tkgp_post_id"]').val();

	$j.ajax({
                url: tkgp_js_vars.ajax_url,
                type: 'POST',
                async: true,
                data: {
                    action: 'tkgp_user_vote',
                    vote_id: vote_id,
                    post_id: post_id,
                    vote_variant: vote,
                    vote_nonce: vote_nonce
                }
            })
            .done(function (result) {
                tkgp_handler_vote_result(result, vote_id, vote_nonce, post_id);
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            });
}

function tkgp_handler_reset_vote() {
	var vote_id = $j(this).parents().find('input[name="tkgp_vote_id"]').val();
	var vote_nonce = $j(this).parents().find('input[name="tkgp_vote_nonce"]').val();
	var post_id = $j(this).parents().find('input[name="tkgp_post_id"]').val();

	$j.ajax({
                url: tkgp_js_vars.ajax_url,
                type: 'POST',
                async: true,
                data: {
                    action: 'tkgp_reset_user_vote',
                    post_id: post_id,
                    vote_id: vote_id,
                    vote_nonce: vote_nonce
                }
            })
            .done(function (result) {
                tkgp_handler_vote_result(result, vote_id, vote_nonce, post_id);
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            });
}

function tkgp_handler_vote_result(result, vote_id, vote_nonce, post_id) {
	alert(result);
	
	$j.ajax({
                url: tkgp_js_vars.ajax_url,
                type: 'POST',
                async: true,
                data: {
                    action: 'tkgp_get_vote_status',
                    post_id: post_id,
                    vote_id: vote_id,
                    vote_nonce: vote_nonce
                }
            })
            .done(function (result) {
                tkgp_update_vote(vote_id, result);
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            });
}

function tkgp_update_vote(vote_id, result) {
	var res = $j.parseJSON(result);
	if(res.status === false) {
		location.reload();
	} else {
		$j('input[name="tkgp_vote_id"][value="' + vote_id + '"]').parents().find('#tkgp_vote_result').replaceWith(res.new_content);
		tkgp_js_init();
	}
		
}
