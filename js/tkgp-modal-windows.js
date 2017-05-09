/**
 * @author Sarvaritdinov Ravil
 */

$j = jQuery.noConflict();

function tkgp_show_modal_box(target_object) {
    if (typeof target_object === 'object') {
        var modal_div = "<div class=\"tkgp_modal_wall\"><div class=\"tkgp_modal_box\"><div class=\"tk_modal_container\">" +
            "</div></div></div>";

        $j(target_object).append(modal_div);
    }
}

function tkgp_hide_modal_box(target_object) {
    if (typeof target_object === 'object') {
        var modal = $j(target_object).find('.tkgp_modal_wall');

        if (modal.length > 0) {
            $j(target_object).find('.tkgp_modal_wall').remove();
        }
    }
}

function tkgp_modal_box_container(target_object) {
    if (typeof target_object === 'object' &&
        $j(target_object).find('.tkgp_modal_wall .tkgp_modal_container').length) {
        return $j(target_object).find('.tkgp_modal_wall .tkgp_modal_container');
    }

    return undefined;
}

function tkgp_show_modal_animete(target_object) {
    if (typeof target_object === 'object') {
        tkgp_show_modal_box(target_object);
        $j(target_object).find('.tkgp_modal_wall .tkgp_modal_container')
            .append("<center><img src=\"" + tkgp_js_vars.plug_url + '/images/load.gif' +
                "\" style=\"max-width: 32px;\"></center>");
    }
}

function tkgp_add_modal_window(id, content,target_object) {
    if(id !== undefined) {
        var target = typeof target_object === 'object' ? $j(target_object) : $j('body');
        var modal_div = "<div id=\"" + id +  "\" class=\"tkgp_modal_wall\">" +
            "<div class=\"tkgp_modal_box\"><div class=\"tkgp_modal_container\">" + (content === undefined ? '' : content) +
            "</div></div></div>";

        $j(target).append(modal_div);
        $j("#" + id).on('click', tkgp_modal_window_handler);
    }
}

function tkgp_modal_window_handler(event) {
    var target = $j(event.target);
    if(target.hasClass('tkgp_modal_wall') || target.hasClass('tkgp_modal_box')) {
        $j(this).hide();
    }
}

function tkgp_show_modal_window(id) {
    if(id !== undefined) {
        $j("#" + id).show();
    }
}

function tkgp_hide_modal_window(id) {
    if(id !== undefined) {
        $j("#" + id).hide();
    }
}

function tkgp_hide_modal_animate(target_object) {
    tkgp_hide_modal_box(target_object);
}

function tkgp_show_modal_animate_handler(event, obj) {
    tkgp_show_modal_animete(obj);
}

function tkgp_hide_modal_animate_handler(event, obj) {
    tkgp_hide_modal_animate(obj);
}

function tkgp_button_load_animate(obj) {
    $j(obj).html("<img src=\"" + tkgp_js_vars.plug_url + '/images/load.gif' +
        "\" style=\"max-width: 16px;\">");
}
