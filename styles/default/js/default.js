/**
 * @author Sarvaritdinov Ravil
 */

var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
    $j('.tkgp_vote_block').on('tkgp_vote_updated', tk_vote_update);
    $j(document).on('tkgp_send_vote_request', tk_animate_start);
    $j(document).on('tkgp_send_reset_vote_request', tk_animate_start);

    $j('.tk-hide-hint').on('click', tk_hide_hint);
});

function tk_hide_hint() {
    $j('.tk-hint').hide();
}

function tk_animate_start() {
    tk_show_modal_animete($j('.tk-logo-cell2'));
}

function tk_animate_stop() {
    tk_hide_modal_animate($j('.tk-logo-cell2'));
}

function tk_vote_update(event, res, message) {
    if(message === undefined) {
        tk_animate_stop();
        var percent = 100.0 * res.approval_votes / res.target_votes;
        var new_content = res.new_content.length != 0 ? res.new_content : '<div class="tkgp_button tk-supported"><a>' + tkl10n.you_supported + '</a></div>';

        if (percent && percent < 0.75) {
            percent = '2px';
        } else if (percent > 100) {
            percent = 100 + '%';
        } else {
            percent += '%';
        }

        $j(event).find('.tkgp_vote_buttons').replaceWith(new_content);
        $j(event).find('.tk-approval-votes').text(tk_number_format(res.approval_votes));
        $j(event).find('.tk-target-votes').text(tk_number_format(res.target_votes));
        $j(event).find('.tk-pb-approved').css('width', percent);
        tkgp_connect_vote_buttons();
    } else {
        var color = message.status ? '#028709' : '#ff3a00';
        var img_url = tkgp_js_vars.plug_url + (message.status ? '/images/ok_status.png'
                : '/images/err_status.png');
        tk_modal_box_container($j('.tk-logo-cell2'))
            .replaceWith("<div><center><img src=\"" + img_url + "\"></center></div>" +
                "<div style='color: " + color + "; text-shadow: 0 0 5px #fff;'>" + message.message + "</div>");
        setTimeout(tk_vote_update, 2000, this, res);
    }
}

function tk_number_format(src, decimal, separator) {
    decimal = decimal === undefined ? 3 : parseInt(decimal);
    separator = separator === undefined ? ' ' : separator;

    var out = '';
    var dec = src.toString().split('.');
    var ost = dec[0].length % decimal;
    var steps = parseInt(dec[0].length / decimal) + (ost ? 1 : 0);

    for (var i = 0; i < steps; i++) {
        out = out.length === 0 ? out : separator + out;
        var start = -1 * decimal * (i + 1);
        var len = i + 1 === steps && ost ? dec[0].length % decimal : decimal;

        out = dec[0].substr(start, len) + out;
    }

    out = dec.length > 1 ? out + '.' + dec[1] : out;

    return out;
}
