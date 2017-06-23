<?php
$project = new TK_GProject(get_the_ID());
$project_logo = $project->getLogoUrl(array('path' => TKGP_STYLES_DIR,
    'url' => TKGP_STYLES_URL, 'subdir' => 'default/images', 'name' => 'default-logo.jpg'));
$project_avatar = $project->getAvatarUrl(array('path' => TKGP_STYLES_DIR,
    'url' => TKGP_STYLES_URL, 'subdir' => 'default/images', 'name' => 'default-avatar.jpg'));
$images_nonce = 'tkgp_upload_images_' . get_the_ID() . '_' . get_current_user_id();

if ($project->isValid()) {
    wp_nonce_field($images_nonce, 'tkgp_images_nonce');
    ?>
    <table class="form-table tkgp_logo_avatar">
        <tr>
            <th><label for="tkgp_logo"><?php echo _x('Logo', '', 'tkgp') ?></label></th>
            <td><input name="tkgp_logo" type="file"></td>
            <td><img src="<?php echo $project_logo; ?>" style="max-width: 350px; border-radius: 5px;"></td>
            <td>
                <div id="tkgp_del_logo" class="tkgp_button button"><a><?php echo __('Delete'); ?></a></div>
            </td>
        </tr>
        <tr>
            <th><label for="tkgp_avatar"><?php echo _x('Avatar', '', 'tkgp') ?></label></th>
            <td><input name="tkgp_avatar" type="file"></td>
            <td>
                <img src="<?php echo $project_avatar; ?>"
                     style="max-width:75px; max-height:75px; border-radius:50%;box-shadow:0 0 5px rgba(0, 0, 0, 0.64);">
            </td>
            <td>
                <div id="tkgp_del_avatar" class="tkgp_button button"><a><?php echo __('Delete'); ?></a></div>
            </td>
        </tr>
    </table>
    <?php
}
?>