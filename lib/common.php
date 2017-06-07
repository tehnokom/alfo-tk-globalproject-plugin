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
function tkgp_is_user_role($role, $user_id = null) {
    $user = is_numeric($user_id) ? get_userdata($user_id) : wp_get_current_user();

    if(!$user) {
        return false;
    }

    return in_array($role, (array) $user->roles);
}