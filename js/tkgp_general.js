var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
    tkgp_connect_edit_buttons();
    tkgp_connect_vote_buttons();
    tkgp_image_preload();
});

function tkgp_image_preload() {
    tkgp_js_vars.images.forEach(function (item) {
        (new Image()).src = tkgp_js_vars.plug_url + '/images/' + item;
    });
}

function tkgp_connect_edit_buttons() {
    $j('.tkgp_edit_button').on('click', tkgp_handler_edit_project);
}

function tkgp_connect_vote_buttons() {
    $j('.tkgp_vote_buttons div.tkgp_button_vote').on('click', tkgp_handler_vote);
    $j('.tkgp_vote_buttons div.tkgp_button_reset').on('click', tkgp_handler_reset_vote);
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
    $j(document).trigger('tkgp_send_vote_request');

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
            $j(document).trigger('tkgp_success_vote_request');
            tkgp_handler_vote_result(result, {vote_id: vote_id, vote_nonce: vote_nonce, post_id: post_id});
        })
        .fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
            $j(document).trigger('tkgp_failed_vote_request');
        });
}

function tkgp_handler_reset_vote() {
    var vote_id = $j(this).parents().find('input[name="tkgp_vote_id"]').val();
    var vote_nonce = $j(this).parents().find('input[name="tkgp_vote_nonce"]').val();
    var post_id = $j(this).parents().find('input[name="tkgp_post_id"]').val();
    $j(document).trigger('tkgp_send_reset_vote_request');

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
            $j(document).trigger('tkgp_success_reset_vote_request');
            tkgp_handler_vote_result(result, {vote_id: vote_id, vote_nonce: vote_nonce, post_id: post_id});
        })
        .fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
            $j(document).trigger('tkgp_failed_reset_vote_request');
        });
}

function tkgp_handler_vote_result(result, args) {
    $j(document).trigger('tkgp_send_vote_status_request');
    $j.ajax({
        url: tkgp_js_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'tkgp_get_vote_status',
            post_id: args.post_id,
            vote_id: args.vote_id,
            vote_nonce: args.vote_nonce,
            approval_text: $j('.tkgp_language_data[data-vbtn-approval-text]').attr('data-vbtn-approval-text'),
            approval_title: $j('.tkgp_language_data[data-vbtn-approval-title]').attr('data-vbtn-approval-title'),
            reproval_text: $j('.tkgp_language_data[data-vbtn-reproval-text]').attr('data-vbtn-reproval-text'),
            reproval_title: $j('.tkgp_language_data[data-vbtn-reproval-title]').attr('data-vbtn-reproval-title'),
            reset_text: $j('.tkgp_language_data[data-vbtn-reset-text]').attr('data-vbtn-reset-text'),
            reset_title: $j('.tkgp_language_data[data-vbtn-reset-title]').attr('data-vbtn-reset-title')
        }
    })
        .done(function (new_html) {
            $j(document).trigger('tkgp_success_vote_status_request', [result]);
            tkgp_update_vote(args.vote_id, new_html, result);
        })
        .fail(function (jqXHR, textStatus) {
            $j(document).trigger('tkgp_failed_vote_status_request');
            console.log("Request failed: " + textStatus);
            location.reload();
        });
}

function tkgp_update_vote(vote_id, result, message) {
    var res = $j.parseJSON(result);
    var mes = $j.parseJSON(message);
    var vr = $j('input[name="tkgp_vote_id"][value="' + vote_id + '"]').parents().find('.tkgp_vote_block');

    if (res.status === false) {
        location.reload();
    } else {
            vr.trigger('tkgp_vote_updated', [res, mes]); //event generated when vote status updated
    }
}


function tkgp_handler_edit_project() {
    var edit_nonce = $j(this).find('input[name="tkgp_access_nonce"]').val();
    var post_id = $j(this).find('input[name="tkgp_post_id"]').val();
    var wait_obj = $j('body');
    //tkgp_wait_animate(wait_obj);

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
    if (tkgp_is_JSON(data)) { //если результат JSON, значит проблемы с доступом
        var res = $j.parseJSON(data);
        var modal_box = $j('#tkgp_modal_box');

        $j(modal_box).find('#tkgp_icon').attr('src', tkgp_js_vars.plug_url + '/images/' + 'err_status.png');
        $j(modal_box).find('#tkgp_message').text(res.message)
            .css('color', '#f00');

        setTimeout(tkgp_hide_wait_animate, 2000);
    } else { //иначе выводим полученную форму
        //tkgp_hide_wait_animate();
        $j('body').append(data);
        $j('#tkgp_modal_user, #tkgp_overlay').css('display', 'block');
    }
}

function tkgp_ajax_get_news(handler) {
    var args = arguments;

    $j.ajax({
        url: tkgp_js_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'tkgp_get_project_news',
            post_id: tkgp_js_vars.post_id,
            page_num: 1
        }
    })
        .done(function (html) {
            if (typeof handler === "function") {
                if (args !== undefined) {
                    handler(html, args[1]);
                } else {
                    handler(news_html);
                }
            }
        })
        .fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });
}

function tkgp_ajax_get_target(handler) {
    var args = arguments[1];

    $j.ajax({
        url: tkgp_js_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'tkgp_get_project_target',
            post_id: tkgp_js_vars.post_id
        }
    })
        .done(function (html) {
            if (typeof handler === 'function') {
                if (args !== undefined) {
                    handler(html, args);
                } else {
                    handler(html);
                }
            }
        })
        .fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });
}

function tkgp_ajax_get_tasks(handler) {
    var args = arguments[1];

    $j.ajax({
        url: tkgp_js_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'tkgp_get_project_tasks',
            post_id: tkgp_js_vars.post_id,
        }
    })
        .done(function (html) {
            if (typeof handler === 'function') {
                if (args !== undefined) {
                    handler(html, args);
                } else {
                    handler(html);
                }
            }
        })
        .fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });
}

function tkgp_handler_save_project_data() {

}

function tkgp_hide_wait_animate() {
    $j(document).trigger('tkgp_hide_modal_animate');
}
