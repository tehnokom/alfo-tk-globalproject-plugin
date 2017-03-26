/**
 * @author Sarvaritdinov Ravil
 */

var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
    $j('.tkgp_vote_block').on('tkgp_vote_updated', tk_vote_update);
    $j('.tk-tabs > a[href^=#tk-tab]').on('click', tk_tab_handler);

    if (location.hash !== '') {
        var cur_tab = location.hash.match(/#tk-tab[\d]+/).toString().replace(/[^\d]+/, '');

        if (cur_tab) {
            $j('.tk-tabs > a[href=#tk-tab' + cur_tab + ']').click();
            tk_tab_handler(cur_tab);
        }
    } else {
        tk_tab_handler('1');
    }
});

function tk_tab_handler(tab_number) {
    var tab_num = typeof tab_number !== 'object' ?
        tab_number :
        $j(this).attr('href').replace(/[^\d]+/, '');

    var tab_object = $j('.tk-tabs > div:nth-of-type(' + tab_num + ')');

    if (!tab_object.find('input[name=tk-tab-status]').length) {
        tk_show_modal_animete($j('.tk-tabs > div:nth-of-type(' + tab_num + ')'));

        switch (tab_num) {
            case '1':
                tkgp_ajax_get_news(tk_update_tab, {tab_num: tab_num});
                break;

            case '2':
                tkgp_ajax_get_target(tk_update_tab, {tab_num: tab_num});
                break;

            default:
                setTimeout(tk_hide_modal_animate, 500, $j('.tk-tabs > div:nth-of-type(' + tab_num + ')'));
                break;
        }
    }
}

function tk_vote_update(event, res) {
    var percent = 100.0 * res.approval_votes / res.target_votes;
    var new_content = res.new_content.length != 0 ? res.new_content : '<div class="tkgp_button tk-supported"><a>' + tkl10n.you_supported + '</a></div>';

    if (percent && percent < 0.75) {
        percent = '2px';
    } else if (percent > 100) {
        percent = 100 + '%';
    } else {
        percent += '%';
    }

    $j(this).find('.tkgp_vote_buttons').replaceWith(new_content);
    $j(this).find('.tk-approval-votes').text(tk_number_format(res.approval_votes));
    $j(this).find('.tk-target-votes').text(tk_number_format(res.target_votes));
    $j(this).find('.tk-pb-approved').css('width', percent);
}

function tk_update_tab(html, args) {
    var a_selector = 'a[href*="' + location.pathname + '#tk-tab"]' +
        ', a[href*="' + location.hostname + '"][href*=#tk-tab], a[href^=#tk-tab]';

    $j('.tk-tabs > div:nth-of-type(' + args.tab_num + ')')
        .empty()
        .append('<input name="tk-tab-status" type="hidden" value="loaded"/>')
        .append(html)
        .find(a_selector).on('click', tk_tab_handler);
}

function tk_show_modal_animete(target_object) {
    if (typeof target_object === 'object') {
        var modal_div = "<div class=\"tk-modal-wall\"><div class=\"tk-modal-box\"><div class=\"tk-modal-container\"><center><img src=\"" +
            tkgp_js_vars.plug_url + '/images/load.gif' +
            "\" style=\"max-width: 32px;\"></center></div></div></div>";

        $j(target_object).append(modal_div);
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

function tk_hide_modal_animate(target_object) {
    if (typeof target_object) {
        var modal = $j(target_object).find('.tk-modal-wall');

        if (modal.length > 0) {
            $j(target_object).find('.tk-modal-wall').remove();
        }
    }
}
