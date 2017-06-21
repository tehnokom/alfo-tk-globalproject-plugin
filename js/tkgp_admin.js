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

        if ($j(".tkgp_radio li input[type='radio'][name='ptype']:checked").length === 0) {
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
        }); //сохранение изменений из визуального редактора в textarea

        tkgp_target_move(); //перемещаем поле Цели
        tkgp_tasks_init(); //инициализируем задачи
        //обработчики на кнопки задач - пока вынес из tkgp_task_init
        $j('.tkgp_button.tkgp_task_ok').on('click', tkgp_task_save_handler);
        $j('.tkgp_button.tkgp_task_cancel').on('click', tkgp_task_editor_hide);

        if (window.location.hash.length) { //перематываем к задачам после добавления задачи
            setTimeout(function () {
                window.location.hash = window.location.hash
            }, 1000);
        }

        tkgp_enable_ajax_upload('input[name="tkgp_logo"]');
        tkgp_enable_ajax_upload('input[name="tkgp_avatar"]');
        $j('#tkgp_del_logo').on('click', tkgp_delete_project_logo);
        $j('#tkgp_del_avatar').on('click', tkgp_delete_project_avatar);
    }
);

function tkgp_target_move() {
    var row = $j("label[for='ptarget']").parent("th").parent("tr");
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
        if (pair[i] !== '') {
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
        if ($j(this).val().length === 0) {
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
        var output = '<div class="button tkgp_user"><a id="tkgp_user">' + display_name +
            '</a><input type="hidden" name="manager' + (i + offset) + '" value="' + cur.value + '"></div>';
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
    if ($j('#tkgp_modal_user').length === 0) {
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
    }
}

function tkgp_tasks_init() {
    $j('.tkgp_tasks').sortable({
        items: "li:not(.tkgp_tasks_tool)",
        connectWith: ".tkgp_tasks",
        dropOnEmpty: true,
        update: tkgp_task_sort_change,
        placeholder: "tkgp_task_empty"
    });

    tkgp_tasks_add_buttons();
}

function tkgp_tasks_add_buttons(obj) {
    obj = obj === undefined ? $j('.tkgp_tasks').parent() : obj;

    if (typeof obj === 'object') {
        /*$j(obj).children('ul.tkgp_tasks').append('<li class="tkgp_tasks_tool"><div>' +
         '<div class="tkgp_circle_button tkgp_task_add_btn">+</div>' +
         '</div></li>');*/

        $j('.tkgp_task_add_btn').on('click', tkgp_task_create_handler);
        $j('.tkgp_task_edit_btn').on('click', tkgp_task_edit_handler);
        $j('.tkgp_task_del_btn').on('click', tkgp_task_delete_handler);
    }
}

function tkgp_task_create_handler(e) {
    tkgp_task_editor_hide();
    var task_edit_form = $j('#tkgp_tasks_editor_form');
    var parent_id = $j($j(this).parents('li')[1]).children('input[name^="tkgp_task_id"]').val();

    task_edit_form.children('input, textarea').val('');
    task_edit_form.children('input[name="tkgp_parent_task_id"]').val(parent_id);
    tinyMCE.get('tkgp_task_editor').setContent('');

    task_edit_form.show();
}

function tkgp_task_sort_change(e, ui) {

}

function tkgp_task_save_handler() {
    var task_id = $j('#tkgp_tasks_editor_form').children('input[name="tkgp_task_id"]').val();
    var parent_id = $j('#tkgp_tasks_editor_form').children('input[name="tkgp_parent_task_id"]').val();
    var title = tkgp_task_field_compile('tkgp_task_title');
    var desc = tkgp_task_field_compile('tkgp_task_editor');
    var type = $j('select[name="tkgp_task_type"] :selected').val();

    $j.post(ajaxurl, {
            action: 'tkgp_task_save',
            post_id: tkgp_url_vars()['post'],
            task_id: task_id,
            parent_id: parent_id,
            title: title,
            desc: desc,
            type: type
        },
        function (resp) {
            var res = $j.parseJSON(resp);
            if (res.status === 'ok') {
                tkgp_task_editor_hide();
                window.location.hash = "#tkgp_task_anchor";
                window.location.reload();
            } else {
                alert('Task creation ERROR!');
            }
        }
    );
}

function tkgp_task_field_compile(name) {
    var out = {};
    var cur_lang = $j('li.qtranxs-lang-switch.active').attr('lang');
    var qtrans_tags = $j('input[name^="qtranslate-fields[' + name + ']"]');

    if (qtrans_tags.length) { //обнаружены поля qtranslate-x
        qtrans_tags.each( //собираем все языки в массив
            function (i, o) {
                var obj = $j(o);
                var regexp = new RegExp('(qtranslate-fields\\[' + name + '\\])', 'g');
                var key = obj.attr('name').replace(regexp, '').replace(/[\[\]]/g, '');

                if (key === 'qtranslate-separator') {
                    return;
                }

                var cur_content = '';
                if (key === cur_lang) { //если текущий редактируемый язык
                    //берем из самого поля ввода/редактора
                    cur_content = $j('*[name="' + name + '"]').prop('tagName') === 'TEXTAREA' ?
                        tinyMCE.get(name).getContent({format: 'text'})
                        : $j('*[name="' + name + '"]').val();
                } else {
                    cur_content = obj.val();
                }

                if (!cur_content.length) {
                    return;
                }

                out[key] = cur_content;
            }
        );

    } else { //если не обнаружены поля qtranslate-x
        var field = $j('*[name="' + name + '"]');
        out = field.prop('tagName') === 'TEXAREA' ?
            field.text()
            : field.val();
    }

    return JSON.stringify(out);
}

function tkgp_task_editor_hide() {
    $j('#tkgp_tasks_editor_form').hide();
}

function tkgp_task_edit_handler() {
    var task_id = $j($j(this).parents('li')[0]).children('input[name^="tkgp_task_id"]').val();

    $j.post(ajaxurl, {
            action: 'tkgp_get_task_data',
            post_id: tkgp_url_vars()['post'],
            task_id: task_id
        },
        function (resp) {
            var res = $j.parseJSON(resp);
            if (res.status === 'ok') {
                var task_edit_form = $j('#tkgp_tasks_editor_form');
                task_edit_form.children('input[name="tkgp_task_id"]').val(task_id);
                task_edit_form.children('select[name="tkgp_task_type"]').val(res.data.type);
                tkgp_task_editor_hide();
                tkgp_set_qtranslatex_fields('tkgp_task_title', res.data.title);
                tkgp_set_qtranslatex_fields('tkgp_task_editor', res.data.desc);
                task_edit_form.show();
            } else {
                alert(res.msg);
            }
        }
    );
}

function tkgp_task_delete_handler() {
    var task_id = $j($j(this).parents('li')[1]).children('input[name^="tkgp_task_id"]').val();

    $j.post(ajaxurl, {
            action: 'tkgp_task_delete',
            post_id: tkgp_url_vars()['post'],
            task_id: task_id
        },
        function (resp) {
            var res = $j.parseJSON(resp);
            if (res.status === 'ok') {
                var task_edit_form = $j('#tkgp_tasks_editor_form');
                tkgp_task_editor_hide();
                window.location.hash = "#tkgp_task_anchor";
                window.location.reload();
            } else {
                alert(res.msg);
            }
        }
    );
}

function tkgp_set_qtranslatex_fields(name, qtransx_string) {
    var langs = tkgp_parse_qtranslatex(qtransx_string);
    var cur_lang = $j('li.qtranxs-lang-switch.active').attr('lang');

    var lang_fields = $j('input[name^="qtranslate-fields[' + name + ']"]');
    lang_fields.each(function(index,field){
        var cur_field = $j(field);
        var lang_tag = cur_field.attr('name').replace('qtranslate-fields[' + name + ']','')
            .replace(/[\[\]]/g,'');

        if(lang_tag !== 'qtranslate-separator') {
            var content = langs.hasOwnProperty(lang_tag) ? langs[lang_tag] : '';
            cur_field.val(content);

            if (cur_lang === lang_tag) {
                var input = $j('*[name="' + name + '"]');

                if (input.prop('tagName') === 'TEXTAREA') {
                    tinyMCE.get(name).setContent(langs[lang_tag].replace(/\r?\n/g,'<br>'));
                } else {
                    input.val(langs[lang_tag]);
                }
            }
        }
    });
}

function tkgp_parse_qtranslatex(string) {
    var out = {};
    string = string.replace('[:]', '');

    while (string.search(/\[:[a-z]{2}\]/g) !== -1) {
        var lang = string.substr(0, 5).replace(/[\[:\]]/g, '');
        string = string.substr(5);
        var end = string.search(/\[:[a-z]{2}\]/g);
        end = end === -1 ? undefined : end - 1;
        out[lang] = string.substr(0, end);
        string = end === undefined ? '' : string.substr(end + 1);
    }

    return out;
}