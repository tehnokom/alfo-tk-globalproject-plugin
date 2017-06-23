/**
 * @author Sarvaritdinov Ravil
 */

$j = jQuery.noConflict();

function tk_show_modal_box(target_object) {
    if (typeof target_object === 'object') {
        var modal_div = "<div class=\"tk-modal-wall\"><div class=\"tk-modal-box\"><div class=\"tk-modal-container\">" +
            "</div></div></div>";

        $j(target_object).append(modal_div);
    }
}

function tk_hide_modal_box(target_object) {
    if (typeof target_object === 'object') {
        var modal = $j(target_object).find('.tk-modal-wall');

        if (modal.length > 0) {
            $j(target_object).find('.tk-modal-wall').remove();
        }
    }
}

function tk_modal_box_container(target_object) {
    if (typeof target_object === 'object' &&
        $j(target_object).find('.tk-modal-wall .tk-modal-container').length) {
        return $j(target_object).find('.tk-modal-wall .tk-modal-container');
    }

    return undefined;
}

function tk_show_modal_animete(target_object) {
    if (typeof target_object === 'object') {
        tk_show_modal_box(target_object);
        $j(target_object).find('.tk-modal-wall .tk-modal-container')
            .append("<center><img src=\"" + tkgp_js_vars.plug_url + '/images/load.gif' +
                "\" style=\"max-width: 32px;\"></center>");
    }
}

function tk_add_modal_window(id, content,target_object) {
    if(id !== undefined) {
        var target = typeof target_object === 'object' ? $j(target_object) : $j('body');
        var modal_div = "<div id=\"" + id +  "\" class=\"tk-modal-wall\">" +
            "<div class=\"tk-modal-box\"><div class=\"tk-modal-container\">" + (content === undefined ? '' : content) +
            "</div></div></div>";

        $j(target).append(modal_div);
        $j("#" + id).on('click', tk_modal_window_handler);
    }
}

function tk_modal_window_handler(event) {
    var target = $j(event.target);
    if(target.hasClass('tk-modal-wall') || target.hasClass('tk-modal-box')) {
        $j(this).hide();
    }
}

function tk_show_modal_window(id) {
    if(id !== undefined) {
        $j("#" + id).show();
    }
}

function tk_hide_modal_window(id) {
    if(id !== undefined) {
        $j("#" + id).hide();
    }
}

function tk_hide_modal_animate(target_object) {
    tk_hide_modal_box(target_object);
}

function tk_show_modal_animate_handler(event, obj) {
    tk_show_modal_animete(obj);
}

function tk_hide_modal_animate_handler(event, obj) {
    tk_hide_modal_animate(obj);
}

function tk_button_load_animate(obj) {
    $j(obj).html("<img src=\"" + tkgp_js_vars.plug_url + '/images/load.gif' +
        "\" style=\"max-width: 16px;\">");
}
