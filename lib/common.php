<?php

/**
 * Looking at all the keys in the array pattern returns an array with the pairs 'key' => 'value' of all the found keys.
 *
 * @param string Template string
 * @param array $target_array Array for search
 * @return array
 */
function tkgp_array_search($key_template, $target_array)
{
    $out = array();

    foreach ($target_array as $key => $value) {
        if (preg_match($key_template, $key)) {
            $out[$key] = $value;
        }
    }

    return $out;
}

/**
 * Verifies that the role matches the user
 *
 * @param string $role
 * @param int|null(default) $user_id
 * @return bool
 */
function tkgp_is_user_role($role, $user_id = null)
{
    $user = is_numeric($user_id) ? get_userdata($user_id) : wp_get_current_user();

    if (!$user) {
        return false;
    }

    return in_array($role, (array)$user->roles);
}

function tkgp_get_total_project_count()
{
    $count = wp_cache_get('tkgp_total_project_count');

    if (!$count) {
        $count = TK_GPage::getTotalProjectCount();
        wp_cache_add('tkgp_total_project_count', $count);
    }

    return $count;
}

function tkgp_check_img($form, $file, &$error)
{
    $status = false;
    $fl_max_size = -1; //file size in kilobytes
    $img_size = array();

    switch ($form) {
        case 'tkgp_logo':
            $fl_max_size = 300;
            $img_size = array('min-width' => 820, 'min-height' => 200, 'max-width' => 820, 'max-height' => 200);
        case 'tkgp_avatar':
            $img_size = empty($img_size) ?
                $img_size = array('min-width' => 50, 'min-height' => 50, 'max-width' => 100, 'max-height' => 100)
                : $img_size;
            //Checking MIME-type
            $status = (stripos(mime_content_type($file['tmp_name']), 'image') === 0);

            if(!$status) {
                $error = _x('The file is not an image.', 'Upload control', 'tkgp');
                return $status;
            }

            $fl_max_size = $fl_max_size < 0 ? 100: $fl_max_size;
            $img_types = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000, IMAGETYPE_PNG);
            $img_info = @getimagesize($file['tmp_name']);

            //Checking Image-type
            $status = in_array($img_info[2], $img_types);

            if(!$status) {
                $error = _x('Invalid image format. Acceptable: JPEG, PNG, GIF.', 'Upload control', 'tkgp');
                return $status;
            }

            //Checking file size
            $status = (filesize($file['tmp_name']) <= $fl_max_size * 1024);

            if(!$status) {
                $error = _x('The file is too big.', 'Upload control', 'tkgp');
            }

            //Checking Image size
            $status = ( $img_info[0] >= $img_size['min-width'] && $img_info[1] >= $img_size['min-height']
                && $img_info[0] <= $img_size['max-width'] && $img_info[1] <= $img_size['max-height']);

            if(!$status) {
                $error = _x('The image size does not meet the requirements.', 'Upload control', 'tkgp');
            }

            break;

        default:
            break;
    }

    return $status;
}