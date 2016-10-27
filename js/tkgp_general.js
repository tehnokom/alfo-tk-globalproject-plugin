var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
        $j(".tkgp_radio li input[type='radio']")
            .addClass('tkgp_radio_hidden')
            .on('click', tkgp_handler_radio) //обработчик для переключателей
            .on('click', tkgp_handler_select_radio);

        $j(".tkgp_user_add").click(tkgp_handler_add_user);

        if ($j(".tkgp_radio li input[type='radio']:checked").length == 0) {
            $j(".tkgp_radio li input[type='radio'][checked='true']")
                .addClass('tkgp_radio_checked')
                .trigger('click');
        }
        else {
            $j(".tkgp_radio li input[type='radio']:checked")
                .addClass('tkgp_radio_checked')
                .click();
        }
    }
);

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
    $j(".tkgp_radio li input[type='radio']").removeClass('tkgp_radio_checked');
    $j(".tkgp_radio li input[type='radio']").removeAttr('checked');
    $j(this).addClass('tkgp_radio_checked');
    $j(this).attr('checked', 'true');

}

function tkgp_handler_select_radio() {
    if (this.value === '3') {
        //alert($j('.tkgp_group_select').length);
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
	
	if($j('input[name="mgr_cnt"]').length == 0) {
		$j('.tkgp_user_add').before('<input name="mgr_cnt" value="1" type="hidden">');	
	}
	
	var offset = parseInt($j('input[name="mgr_cnt"]').val(),10);
	
	for(var i = 0; i < selected.length; i++) {
		var cur = selected[i];
		var display_name = ($j(cur).parents('tr').find('td:first-child')).text();
		var output = '<div class="button tkgp_user"><a id="tkgp_user">' + display_name + '</a><input type="hidden" name="manager' + (i + offset) + '" value="' + cur.value + '"></div>';
		$j('.tkgp_user_add').before(output);
	}
	
	$j('input[name="mgr_cnt"]').val(offset + selected.length);
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
                $j('#tkgp_add_selected').click(tkgp_handler_add_selected);
                $j('#tkgp_modal_user #modal_close, #tkgp_overlay').click(function () {
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
