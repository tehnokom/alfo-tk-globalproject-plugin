var $j = jQuery.noConflict();
var tkgp_field_ajax_uploads = {};

function tkgp_ajax_url() {
    var ajax_url = ajaxurl !== undefined ? ajaxurl : tkgp_js_vars.ajax_url;
    return ajax_url;
}

function tkgp_current_pos_id() {
    var post_id = (typeof tkgp_url_vars !== undefined) ? tkgp_url_vars()['post'] : tkgp_js_vars.post_id;
    return post_id;
}

function tkgp_is_JSON(str) {
    try {
        $j.parseJSON(str);
    } catch (e) {
        return false;
    }
    return true;
}

function tkgp_enable_ajax_upload($field_filter, $button_filter) {
    if($button_filter === undefined) {
        $button_filter = $field_filter;
    }

    if(tkgp_field_ajax_uploads[$button_filter] === undefined) {
        tkgp_field_ajax_uploads[$button_filter] = [$field_filter];
    } else {
        tkgp_field_ajax_uploads[$button_filter].push($field_filter);
    }

    var action = ($button_filter !== $field_filter ? 'click' : 'change');
    $j($button_filter).on(action, null, {button: $button_filter}, tkgp_upload_files);
}

function tkgp_get_upload_nonce($file_type) {

}

function tkgp_upload_files(e) {
    var btn = e.data.button;
    var files;

    if(tkgp_field_ajax_uploads[btn] === undefined) {
        return;
    }

    $j(btn).trigger('tkgp_upload_files_check');

    e.stopPropagation();
    e.preventDefault();

    var data = false;

    $j.each(tkgp_field_ajax_uploads[btn], function(key, val) {
        var cur_field = $j(val);

        if(cur_field.val() === undefined || cur_field.val() === '') {
            return true; //continue
        }

        var files = cur_field.prop('files');
        var postfix = files.length > 1 ? '[]' : '';

        $j.each(files, function(key, val) {
            if(!tkgp_check_file_size(val, cur_field.attr('name'), true)
            || !tkgp_check_file_type(val, cur_field.attr('name'), true)) {
                cur_field.val('');
                return true;
            }

            if(!data) {
                data = new FormData();
            }

            data.append(cur_field.attr('name') + postfix, val);
        })
    });

    if(!data) {
        $j(btn).trigger('tkgp_upload_files_check_failed');
        return;
    }

    var nonce = $j('input[name="tkgp_images_nonce"]');
    if(nonce.length) {
        data.append(nonce.prop('name'), nonce.val());
    }

    data.append('action', 'tkgp_upload_image');
    data.append('post_id', tkgp_current_pos_id());

    $j(btn).trigger('tkgp_upload_files_request');

    $j.ajax({
        url: tkgp_ajax_url(),
        type: 'POST',
        async: true,
        data: data,
        cache: false,
        dataType: 'json',
        processData: false,
        contentType: false
    })
        .done(function (result) {
            if(result.status === 'ok') {
                alert('Success');
                $j(btn).trigger('tkgp_upload_files_success', [result.url]);
            } else {
                alert('Error: ' + result.msg + ": " + result.errors);
                console.log("Bad server response: " + result.msg + ": " + result.error);
                $j(btn).trigger('tkgp_upload_files_failed', [{title: result.msg, error_string: result.errors}]);
            }
        })
        .fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
            $j(btn).trigger('tkgp_upload_files_failed', [{title: "Request failed", error_string: textStatus}]);
        });
}

function tkgp_check_file_size(file, file_type, show_alert) {
    var fl_max_size;
    switch (file_type) {
        case 'tkgp_logo':
            fl_max_size = 300 * 1024;
            break;
        case 'tkgp_avatar':
            fl_max_size = 100 * 1024;
            break;

        default:
            return false;
    }

    if(file.size <= fl_max_size) {
        return true;
    }

    if(show_alert) {
        var str = tkgp_i18n.big_file;
        str = str.replace('%1%', file.name);
        alert(str + fl_max_size/1024 + tkgp_i18n.kb);
    }

    return false;
}

function tkgp_check_file_type(file, file_type, show_alert) {
    switch (file_type) {
        case 'tkgp_logo':
        case 'tkgp_avatar':
            if(file.type.indexOf('image') === 0) {
                return true;
            }

            if(show_alert) {
                var str = tkgp_i18n.not_img.replace('%1%', file.name);
                alert(str);
            }

        default:
            return false;
    }
}

function tkgp_delete_project_avatar() {
    tkgp_delete_project_image('avatar', this);
}

function tkgp_delete_project_logo() {
    tkgp_delete_project_image('logo', this);
}

function tkgp_delete_project_image(img_type, button) {
    if(img_type !== undefined) {

        $j(button).trigger('tkgp_delete_image_request');

        $j.ajax({
            url: tkgp_ajax_url(),
            type: 'POST',
            async: true,
            dataType: 'json',
            data: {
                action: 'tkgp_delete_image',
                post_id: tkgp_current_pos_id(),
                image: 'tkgp_' + img_type
            }
        })
            .done(function (result) {
                if(result.status === 'ok') {
                    $j(button).trigger('tkgp_delete_image_success', [result.url]);
                } else {
                    console.log("Bad server response: " + result.msg);
                    $j(button).trigger('tkgp_delete_image_failed',
                        [{title: 'Bad server response', error_string: result.msg}]);
                }
            })
            .fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
                $j(button).trigger('tkgp_delete_image_failed', [{title: 'Request failed', error_string: textStatus}]);
            });
    }
}