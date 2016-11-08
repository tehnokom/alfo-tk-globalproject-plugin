var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
	$j('#tkgp_vote_buttons div.tkgp_button').on('click', tkgp_handler_vote);
});

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
                tkgp_handler_vote_result(result);
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            });
}

function tkgp_handler_vote_result(result) {
	alert(result);
}
