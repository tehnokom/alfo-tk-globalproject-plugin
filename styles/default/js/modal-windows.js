/**
 * @author Sarvaritdinov Ravil
 */

$j = jQuery.noConflict();

function tk_show_modal_animete(target_object) {
    if (typeof target_object === 'object') {
        var modal_div = "<div class=\"tk-modal-wall\"><div class=\"tk-modal-box\"><div class=\"tk-modal-container\"><center><img src=\"" +
            tkgp_js_vars.plug_url + '/images/load.gif' +
            "\" style=\"max-width: 32px;\"></center></div></div></div>";

        $j(target_object).append(modal_div);
    }
}

function tk_hide_modal_animate(target_object) {
    if (typeof target_object) {
        var modal = $j(target_object).find('.tk-modal-wall');

        if (modal.length > 0) {
            $j(target_object).find('.tk-modal-wall').remove();
        }
    }
}

function tk_show_modal_animate_handler(event, obj) {
    tk_show_modal_animete(obj);
}

function tk_hide_modal_animate_handler(event, obj) {
    tk_hide_modal_animate(obj);
}