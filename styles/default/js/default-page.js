/**
 * @author Sarvaritdinov Ravil
 */

var $j = jQuery.noConflict();
var tk_filter_state;

$j(document).ready(function ($j) {
    tk_page_init();
    tk_filter_init();
    tk_more_button_init();
});

function tk_page_init() {
    $j('.tk-mobile-button').css('opacity',1);
    $j('.tk-mobile-button').on('click', tk_mobile_filter_show);
}

function tk_filter_init() {
    tk_filter_state = {sort_by: 'priority', order_by: 'desc'};
    tk_filter_reset();
    $j('.tk-filter-order select').on('change', tk_select_handler);
}

function tk_more_button_init() {
    $j('#tk-page-more').on('click', tk_more_button_handler);
}

function tk_mobile_filter_show() {
    if(!$j('#tk-mobile-filters').length) {
        tk_add_modal_window('tk-mobile-filters');
        $j('.tk-panel .tk-filter-box').clone().appendTo($j('#tk-mobile-filters .tk-modal-container'));
        tk_filter_init();
    } else {
        tk_show_modal_window('tk-mobile-filters');
    }
}

function tk_mobile_filter_hide() {
    if($j('#tk-mobile-filters').length) {
        $j('#tk-mobile-filters').hide();
    }
}

function tk_more_button_handler() {
    tk_button_load_animate(this);
    var page_num = $j('input[name="tk_page_num"]').val();
    var sort = $j('.tk-filter-order select[name="sort_by"]');
    var order = $j('.tk-filter-order select[name="order_by"]');

    page_num = page_num === undefined ? 2 : parseInt(page_num) + 1;

    $j.ajax({
        url: tkgp_js_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'tkgp_get_projects',
            page: page_num,
            sort_by: sort.find('option:selected').val(),
            order_by: order.find('option:selected').val()
        }
    })
        .done(function (data) {
            tk_append_list(data);
        })
        .fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
            tk_hide_modal_animate();
        });
}

function tk_select_handler(event) {
    if (tk_filter_is_updated()) {
        tk_show_filter_buttons(event.target);
    } else {
        tk_hide_filter_buttons();
    }
}

function tk_show_filter_buttons(target) {
    var pos = $j(target).parents('.tk-filter-box').offset();
    var v_offset = $j(target).parents('.tk-filter-box').outerHeight();
    var el_width = $j(target).parents('.tk-filter-box').outerWidth();

    if (!$j('.tk-filter-buttons').length) {
        var code = "<div class='tk-filter-buttons'><div><div class='tk-button tk-filter-ok'>Apply</div></div>" +
            "<div><div class='tk-button tk-filter-cancel'>Cancel</div></div></div>";
        $j('body').append(code);
        var h_offset = el_width / 2 - $j('.tk-filter-buttons').outerWidth() / 2;

        $j('.tk-filter-buttons').offset({left: pos.left + h_offset, top: pos.top + v_offset + 5});
        $j('.tk-filter-buttons .tk-button').on('click', tk_filter_handler);
    } else {
        $j('.tk-filter-buttons').show();
        var h_offset = el_width / 2 - $j('.tk-filter-buttons').outerWidth() / 2;
        $j('.tk-filter-buttons').offset({left: pos.left + h_offset, top: pos.top + v_offset + 5});
    }
}

function tk_hide_filter_buttons() {
    $j('.tk-filter-buttons').hide();
}

function tk_filter_handler() {
    if($j(this).hasClass('tk-filter-ok')) {
        tk_show_modal_animete($j('.tk-projects-list'));
        tk_filter_state = tk_filters_state();
        tk_hide_filter_buttons();
        tk_filter_reset();
        tk_mobile_filter_hide();

        $j.ajax({
            url: tkgp_js_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'tkgp_get_projects',
                post_id: tkgp_js_vars.post_id,
                page: 1,
                sort_by: tk_filter_state.sort_by,
                order_by: tk_filter_state.order_by
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
        tk_filter_reset(this);
        tk_hide_filter_buttons(this);
    }
}

function tk_update_list(data) {
    $j('.tk-projects-list').children().remove();
    $j('.tk-projects-list').append(data);
    tk_more_button_init();
}

function tk_append_list(data) {
    $j('#tk-page-more').remove();
    var pg_input = $j('input[name="tk_page_num"]');

    if(pg_input.length) {
        pg_input.val(parseInt(pg_input.val()) + 1);
    } else {
        $j('.tk-projects-list').prepend("<input name='tk_page_num' type='hidden' value='2'>");
    }

    $j('.tk-projects-list').append(data);
    tk_more_button_init();
}

function tk_filter_reset() {
    $j('.tk-filter-order:visible select[name="sort_by"] option')
        .removeAttr('selected')
        .parent('select')
        .find('option[value="' + tk_filter_state.sort_by + '"]')
        .attr('selected', 'selected');

    $j('.tk-filter-order:visible select[name="order_by"] option')
        .removeAttr('selected')
        .parent('select')
        .find('option[value="' + tk_filter_state.order_by + '"]')
        .attr('selected', 'selected');

    tk_filter_state = tk_filters_state();
}

function tk_filters_state() {
    return {
        sort_by: $j('.tk-filter-order:visible select[name="sort_by"] option:selected').val(),
        order_by: $j('.tk-filter-order:visible select[name="order_by"] option:selected').val()
    };
}

function tk_filter_is_updated() {
    var cur_state = tk_filters_state();
    return tk_filter_state.sort_by != cur_state.sort_by
        || tk_filter_state.order_by != cur_state.order_by;
}