/**
 * @author Sarvaritdinov Ravil
 */

var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
    tk_filter_init();
    tk_filter_reset();

});

function tk_filter_init() {
    $j('#tk-filter-order select').on('change', tk_select_handler);
}

function tk_select_handler() {
    if(!tk_filter_is_default()) {
        //показать кнопки применения и сброса фильтра
        tk_show_filter_buttons();
    } else {
        hide_filter_buttons();
    }
}

function tk_show_filter_buttons() {
    if(!$j('#tk-filter-buttons').length) {
        var code = "<div id='tk-filter-buttons'><div><div class='tk-button tk-filter-ok'>Apply</div></div>" +
            "<div><div class='tk-button tk-filter-cancel'>Cancel</div></div></div>";
        var pos = $j('.tk-filter-box').offset();
        var v_offset = $j('.tk-filter-box').outerHeight();
        var el_width = $j('.tk-filter-box').outerWidth();
        $j('body').append(code);

        var h_offset = el_width / 2 - $j('#tk-filter-buttons').outerWidth() / 2;
        $j('#tk-filter-buttons').offset({left: pos.left + h_offset, top: pos.top + v_offset + 5});
    } else {
        $j('#tk-filter-buttons').css('display: init;');
    }
}

function tk_hide_filter_buttons() {

}

function tk_filter_handler() {

}

function tk_filter_reset() {
    $j('#tk-filter-order select[name="sort_by"] option')
        .removeAttr('selected')
        .parent('select')
        .find('[value="date"]')
        .attr('selected', 'selected');

    $j('#tk-filter-order select[name="order_by"] option')
        .removeAttr('selected')
        .parent('select')
        .find('option[value="desc"]')
        .attr('selected', 'selected');
}

function tk_filter_is_default() {
    return (
        $j('#tk-filter-order select[name="sort_by"] option:selected')
            .attr('value') === 'date'
        &&
        $j('#tk-filter-order select[name="order_by"] option:selected')
            .attr('value') === 'desc'
    );
}