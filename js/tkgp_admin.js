var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
        $j('.tkgp_datepicker[name="tkgp_vote_start_date"]').datepicker({
            dateFormat: 'dd-mm-yy'
        });

        $j('.tkgp_datepicker[name="tkgp_vote_end_date"]').datepicker({
            dateFormat: 'dd-mm-yy',
            minDate: 'today' //$j('.tkgp_datepicker[name="start_date"]').val()
        });

        $j('input[name="tkgp_vote_reset"]').on('click', tkgp_vote_reset);

        $j(".tkgp_radio li input[type='radio']")
            .addClass('tkgp_radio_hidden')
            .on('click', tkgp_handler_radio) //обработчик для переключателей
            .on('click', tkgp_handler_select_radio);

        $j(".tkgp_user_add").on('click', tkgp_handler_add_user);
        $j(".tkgp_user").on('click', tkgp_handler_del_user);

        if ($j(".tkgp_radio li input[type='radio'][name='ptype']:checked").length == 0) {
            $j(".tkgp_radio li input[type='radio'][name='ptype'][checked='true']")
                .addClass('tkgp_radio_checked')
                .trigger('click');
        }
        else {
            $j(".tkgp_radio li input[type='radio'][name='ptype']:checked")
                .addClass('tkgp_radio_checked')
                .trigger('click');
        }

        $j(".required_field").attr('required', 'required'); //обязательность поля "Цель проекта"
        $j("input[type='submit']").on('mousedown', function () {
            tinyMCE.triggerSave();
        }); //сохранение изменени из визуального редактора в textarea

        tkgp_target_move();
    }
);

function tkgp_target_move() {
    row = $j("label[for='ptarget']").parent("th").parent("tr");
    $j("#wp-content-wrap").before('<h3 id="wp-ptarget-header"></h3>');
    $j("label[for='ptarget']").append(':').detach().appendTo("#wp-ptarget-header");
    $j("#wp-ptarget-wrap").detach().insertBefore("#wp-content-wrap");
    row.detach();
    row = null;
}

function tkgp_vote_reset() {
    if ($j(this).is(':checked') && confirm(tkgp_i18n.vote_reset)) {
        $j(this).prop('checked', true);
    } else $j(this).prop('checked', false);
}

function tkgp_url_vars() {
    var pair = window.location.href.slice(window.location.href.indexOf('?')).split(/[&?]{1}/);
    var out = {};

    for (var i = 0; i < pair.length; ++i) {
        if (pair[i] != '') {
            out[pair[i].split('=')[0]] = pair[i].split('=')[1];
        }
    }

    return out;
}

function tkgp_handler_radio() {
    var radio_name = $j(this).attr('name');
    $j(".tkgp_radio li input[type='radio'][name='" + radio_name + "']").removeClass('tkgp_radio_checked')
        .removeAttr('checked');
    $j(this)
        .addClass('tkgp_radio_checked')
        .attr('checked', 'true');

}

function tkgp_handler_select_radio() {
    if (this.value === '3') {
        $j('.tkgp_group_select option').attr('disabled', '');
        $j('.tkgp_group_select option[selected=""]').removeAttr('selected');
        $j('.tkgp_group_select [value="0"]').attr('selected', '').removeAttr('disabled');
    }
    else $j('.tkgp_group_select option').removeAttr('disabled');
}

function tkgp_handler_search(e) {
    if (/*$j(this).val().length >= 2 &&*/ e.keyCode !== 27) {
        if ($j(this).val().length == 0) {
            tkgp_show_search_result('');
            return;
        }

        $j
            .ajax({
                url: ajaxurl,
                type: 'POST',
                async: true,
                data: {
                    action: 'tkgp_get_user',
                    tkgp_ufilter: $j(this).val(),
                    post_id: tkgp_url_vars()['post']
                }
            })
            .done(function (result) {
                tkgp_show_search_result(result);
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            });
    }
}

function tkgp_handler_add_selected() {
    var selected = $j('#tkgp_modal_user input[type="checkbox"]:checked');
    var offset = $j('.tkgp_user').length;

    for (var i = 0; i < selected.length; i++) {
        var cur = selected[i];
        var display_name = ($j(cur).parents('tr').find('td:first-child')).text();
        var output = '<div class="button tkgp_user"><a id="tkgp_user">' + display_name + '</a><input type="hidden" name="manager' + (i + offset) + '" value="' + cur.value + '"></div>';
        $j('.tkgp_user_add').before(output);
    }
    $j(".tkgp_user").off('click', tkgp_handler_del_user);
    $j(".tkgp_user").on('click', tkgp_handler_del_user);
    tkgp_hide_user_modal();

}

function tkgp_show_search_result(resp) {
    $j('tr.alt1, tr.alt2').remove();
    $j('#tkgp_modal_user table tbody').append(resp);
}

function tkgp_show_user_modal() {
    $j('#tkgp_modal_user, #tkgp_overlay').css('display', 'block');
}

function tkgp_hide_user_modal() {
    $j('#tkgp_modal_user, #tkgp_overlay').removeAttr('style');
    $j('#tkgp_search').attr('value', '');
    tkgp_show_search_result();
}

function tkgp_handler_add_user() {
    if ($j('#tkgp_modal_user').length == 0) {
        $j.post(ajaxurl, {action: 'tkgp_get_user', post_id: tkgp_url_vars()['post']},
            function (resp) {
                var $j = jQuery.noConflict();
                $j('body').append(resp);
                $j('#tkgp_add_selected').on('click', tkgp_handler_add_selected);
                $j('#tkgp_modal_user #modal_close, #tkgp_overlay').on('click', function () {
                    tkgp_hide_user_modal();
                });
                $j('#tkgp_search').keypress(tkgp_handler_search);
                tkgp_show_user_modal();
            }
        );
    } else {
        tkgp_show_user_modal();
    }
}

function tkgp_handler_del_user() {
    var select_name = $j(this).find('input[name^="manager"]').attr('name');
    var parent = $j(this).parents('td');
    var mgr_cnt = parseInt($j(parent).find('input[name^="manager"]').length);

    if (mgr_cnt > 1) {
        if (!confirm(tkgp_i18n.delete_manager)) {
            return;
        }

        var select_idx = parseInt(select_name.replace('manager', ''));

        if (isNaN(select_idx)) {
            select_idx = 0;
        }

        $j(this).remove();

        for (var i = select_idx + 1; i < mgr_cnt; ++i) {
            $j(parent).find('input[name="manager' + i + '"]').attr('name', 'manager' + (i - 1 ? i - 1 : ''));
        }

        if (mgr_cnt - 1 > 1) {
            $j(parent).find('input[name="mgr_cnt"]').val(mgr_cnt - 1);
        } else {
            $j(parent).find('input[name="mgr_cnt"]').remove();
        }
    } else { //нельзя удалять единственного
        alert(tkgp_i18n.delete_single_manager);
        return;
    }

    //$j(parent).remove('#manager-1');

}
