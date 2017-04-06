/**
 * @author Sarvaritdinov Ravil
 */

var $j = jQuery.noConflict();
var tk_filter_state;

$j(document).ready(function ($j) {
    tk_filter_init();
});

function tk_filter_init() {
    tk_filter_state = {sort_by: 'priority', order_by: 'desc'};
    tk_filter_reset();
    $j('#tk-filter-order select').on('change', tk_select_handler);
}

function tk_select_handler() {
    if (tk_filter_is_updated()) {
        tk_show_filter_buttons();
    } else {
        tk_hide_filter_buttons();
    }
}

function tk_show_filter_buttons() {
    if (!$j('#tk-filter-buttons').length) {
        var code = "<div id='tk-filter-buttons'><div><div class='tk-button tk-filter-ok'>Apply</div></div>" +
            "<div><div class='tk-button tk-filter-cancel'>Cancel</div></div></div>";
        var pos = $j('.tk-filter-box').offset();
        var v_offset = $j('.tk-filter-box').outerHeight();
        var el_width = $j('.tk-filter-box').outerWidth();
        $j('body').append(code);

        var h_offset = el_width / 2 - $j('#tk-filter-buttons').outerWidth() / 2;
        $j('#tk-filter-buttons').offset({left: pos.left + h_offset, top: pos.top + v_offset + 5});
        $j('#tk-filter-buttons .tk-button').on('click', tk_filter_handler);
    } else {
        $j('#tk-filter-buttons').show();
    }
}

function tk_hide_filter_buttons() {
    $j('#tk-filter-buttons').hide();
}

function tk_filter_handler() {
    if($j(this).hasClass('tk-filter-ok')) {
        tk_show_modal_animete($j('.tk-projects-list'));
        tk_filter_state = tk_filters_state();
        tk_hide_filter_buttons();
        var sort = $j('#tk-filter-order select[name="sort_by"]');
        var order = $j('#tk-filter-order select[name="order_by"]');

        $j.ajax({
            url: tkgp_js_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'tkgp_get_projects',
                post_id: tkgp_js_vars.post_id,
                page_num: 1,
                sort_by: sort.find('option:selected').val(),
                order_by: order.find('option:selected').val()
            }
        })
            .done(function (data) {
                tk_update_list(data);
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
                tk_hide_modal_animate();
            });
    } else {
        tk_filter_reset();
        tk_hide_filter_buttons();
    }
}

function tk_update_list(data) {
    $j('.tk-projects-list').children().remove();
    $j('.tk-projects-list').append(data);

}

function tk_filter_reset() {
    $j('#tk-filter-order select[name="sort_by"] option')
        .removeAttr('selected')
        .parent('select')
        .find('option[value="' + tk_filter_state.sort_by + '"]')
        .attr('selected', 'selected');

    $j('#tk-filter-order select[name="order_by"] option')
        .removeAttr('selected')
        .parent('select')
        .find('option[value="' + tk_filter_state.order_by + '"]')
        .attr('selected', 'selected');

    tk_filter_state = tk_filters_state();
}

function tk_filters_state() {
    return {
        sort_by: $j('#tk-filter-order select[name="sort_by"] option:selected').val(),
        order_by: $j('#tk-filter-order select[name="order_by"] option:selected').val()
    };
}

function tk_filter_is_updated() {
    var cur_state = tk_filters_state();

    return tk_filter_state.sort_by != cur_state.sort_by
        || tk_filter_state.order_by != cur_state.order_by;
}