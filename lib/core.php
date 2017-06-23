<?php
if (!defined('TKGP_ROOT') || !defined('TKGP_URL')) {
    exit;
}

require_once(TKGP_ROOT . 'lib/common.php');
require_once(TKGP_ROOT . 'lib/page.php');
require_once(TKGP_ROOT . 'lib/project.php');
require_once(TKGP_ROOT . 'lib/vote.php');
require_once(TKGP_ROOT . 'lib/news.php');
require_once(TKGP_ROOT . 'lib/task.php');
require_once(TKGP_ROOT . 'lib/tasks.php');

/**
 * Создание типа проектов
 */
function tkgp_create_type()
{
    register_post_type(TK_GProject::slug,
        array(
            'labels' => array(
                'name' => _x('Projects', 'tk_project', 'tkgp'),
                'singular_name' => _x('Projects', 'tk_project_singular', 'tkgp'),
                'add' => _x('Create new', 'tk_project', 'tkgp'),
                'add_new_item' => _x('Create new project', 'tk_project', 'tkgp'),
                'edit_item' => _x('Edit project', 'tk_project', 'tkgp'),
                'new_item' => _x('New project', 'tk_project', 'tkgp'),
                'view_item' => _x('View project', 'tk_project', 'tkgp'),
                'search_items' => _x('Search project', 'tk_project', 'tkgp'),
                'not_found' => _x('Project not found', 'tk_project', 'tkgp'),
                'not_found_in_trash' => _x('Project not found in Trash', 'tk_project', 'tkgp')
            ),
            'description' => _x('TehnoKom Global Project system type', 'tk_project', 'tkgp'),
            'public' => true,
            'exclude_from_search' => false,
            'menu_position' => 15,
            'hierarchical' => false,
            'supports' => array('title', 'editor', 'comments', 'thumbnail'),
            'taxonomies' => array('tkgp_tax'),
            'has_archive' => true
        )
    );
}

/**
 * Создание таксономии (категориий) для Проектов
 */
function tkgp_create_taxonomy()
{
    register_taxonomy('tkgp_tax',
        array(TK_GProject::slug),
        array(
            'labels' => array(
                'name' => _x('Categories of Projects', 'Taxonomy General Name', 'tkgp'),
                'singular_name' => _x('Categories of Projects', 'Taxonomy Singular Name', 'tkgp'),
                'menu_name' => __('Categories'),
                'all_items' => __('All Categories'),
                'edit_item' => __('Edit Category'),
                'view_item' => __('View Category'),
                'update_item' => __('Update Category'),
                'add_new_item' => __('Add New Category'),
                'new_item_name' => __('New Category Name'),
                'parent_item' => __('Parent Category'),
                'parent_item_colon' => __('Parent Category:'),
                'search_items' => __('Search Categories'),
                'popular_items' => __('Popular Tags'),
                'separate_items_with_commas' => __('Separate tags with commas'),
                'choose_from_most_used' => __('Choose from the most used tags'),
                'not_found' => __('No categories found.')
            ),
            'hierarchical' => true,
            'public' => true
        )
    );
}

/**
 * Метаданные для Проектов
 */
function tkgp_create_meta_box()
{
    global $post;

    if ($post->post_type != TK_GProject::slug) {
        return;
    }

    add_meta_box('tk_project_meta_settings',
        _x('Settings of Project', 'tk_meta', 'tkgp'),
        'tkgp_show_metabox_settings',
        null,
        'normal',
        'high');

    add_meta_box('tk_project_meta_images',
        _x('Logo and Avatar', 'tk_meta', 'tkgp'),
        'tkgp_show_metabox_images',
        null,
        'normal',
        'high');

    add_meta_box('tk_project_meta_steps',
        _x('Plane of Project', 'tk_meta', 'tkgp'),
        'tkgp_show_metabox_steps',
        null,
        'normal',
        'high');

    add_meta_box('tk_project_meta_votes',
        _x('Votes', 'tk_meta', 'tkgp'),
        'tkgp_show_metabox_votes',
        null,
        'normal',
        'high');
}

function tkgp_show_metabox_images()
{
    require_once (TKGP_ROOT . 'admin/logo-avatar.php');
}

function tkgp_show_metabox_settings()
{
    global $post;

    if ($post->post_type != TK_GProject::slug) {
        return;
    }

    echo '<input type="hidden" name="tkgp_meta_settings_nonce" value="' . wp_create_nonce(basename(__FILE__) . '_settings') . '" />
<table class="form-table">';

    $project = new TK_GProject($post->ID);

    foreach (TK_GProject::getProjectFields() as $field) {
        $current_val = '';

        echo '<tr>
	<th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
	<td>';
        switch ($field['type']) {
            case 'radio': //Переключатель
                $current_val = get_post_meta($post->ID, $field['id'], true);
                $current_val = $current_val == '' ? '1' : $current_val;
                break;

            case 'number':
                $current_val = $project->priority;
                break;

            case 'select': //Выпадающее меню
                $current_val = get_post_meta($post->ID, $field['id'], true);
                $current_val = $current_val == '' ? '1' : $current_val;
                break;

            case 'select_user':
                if ($project->isValid()) {
                    $current_val = $project->getManagers();
                }
                break;

            case 'text':
                if ($project->isValid()) {
                    $current_val = $project->getParentProject();
                    $current_val = is_object($current_val) ? $current_val->internal_id : '';
                }
                break;

            default:
                $current_val = get_post_meta($post->ID, $field['id'], true);
                break;


        }
        tkgp_display_options_field($field, $current_val);
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

function tkgp_show_metabox_steps()
{
    ?>
    <div id="tkgp_task_frame">
        <a name="tkgp_task_anchor"></a>
        <?php require_once(TKGP_ROOT . 'lib/admin-tasks.php'); ?>
    </div>
    <div id="tkgp_tasks_editor_form"
         style="padding:5px;margin-top:20px;border:1px solid #ccc;background: #ccc;" hidden="hidden">
        <input type="hidden" name="tkgp_task_id" val=""/>
        <input type="hidden" name="tkgp_parent_task_id" val=""/>
        <select name="tkgp_task_type" style="display: block;">
            <option value="2"><?php echo _x('Stage', 'Project Tasks', 'tkgp'); ?></option>
            <option value="3"><?php echo _x('Task', 'Project Tasks', 'tkgp'); ?></option>
        </select>
        <input type="text" name="tkgp_task_title" class="tkgp_task_mlang"/>
        <?php
        wp_editor('', 'tkgp_task_editor', array(
            'editor_class' => 'requiredField',
            'textarea_rows' => '6',
            'media_buttons' => false,
            'teeny' => true));
        ?>
        <div style="text-align:right;margin-top: 5px;">
            <div class="tkgp_button button tkgp_task_ok"><a><?php echo __('Apply'); ?></a></div>
            <div class="tkgp_button button tkgp_task_cancel"><a><?php echo __('Cancel'); ?></a></div>
        </div>
    </div>
    <?php
}

function tkgp_show_metabox_votes()
{
    global $post;

    $vote = new TK_GVote($post->ID);
    ?>
    <input type="hidden" name="tkgp_meta_votes_nonce"
           value="<?php echo wp_create_nonce(basename(__FILE__) . '_votes'); ?>"/>
    <table class="form-table">
        <?php
        foreach (TK_GVote::getVotesFields() as $field) {
            $cur_id = str_replace('tkgp_vote_', '', $field['id']);
            $current_val = $vote->$cur_id;
            ?>
            <tr>
                <th><label for="<?php echo $field['id']; ?>"/><?php echo $field['label']; ?></th>
                <td>
                    <?php
                    switch ($field['type']) {
                        case 'radio':
                            $current_val = $current_val == '' ? '1' : $current_val;
                            tkgp_display_options_field($field, $current_val);
                            break;

                        case 'number':
                            tkgp_display_options_field($field, $current_val);
                            break;

                        case 'date':
                            $current_val = !empty($current_val) ? $current_val = date('d-m-Y',
                                strtotime($current_val)) : $current_val;
                            tkgp_display_options_field($field, $current_val);
                            break;

                        default:
                            tkgp_display_options_field($field, $current_val);
                            break;
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
}


function tkgp_save_post_meta($post_id)
{
    if (!wp_verify_nonce($_POST['tkgp_meta_settings_nonce'], basename(__FILE__) . '_settings')
        || $_POST['post_type'] != TK_GProject::slug
        || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || !current_user_can('edit_page', $post_id) //проверка на доступ пользователя, потом нужно будет доработать
    ) {
        return $post_id;
    }

    $project = new TK_GProject($post_id);
    $vote_updates = array();
    $fields = array_merge(TK_GProject::getProjectFields(), TK_GVote::getVotesFields());

    foreach ($fields as $field) {
        if (!isset($_POST[$field['id']]) && $field['exclude'] != 1) {
            delete_post_meta($post_id, $field['id']);
            continue;
        }

        $old = get_post_meta($post_id, $field['id'], true);
        $new = array();

        switch ($field['id']) {
            case 'manager':
            case 'group':
                $pattern = '/^(' . $field['id'] . '){1}[0-9]*$/';
                $list = tkgp_array_search($pattern, $_POST);

                foreach ($list as $key => $val) {
                    if (is_numeric($val)) {
                        $new[] = $val;

                        if ($field['id'] == 'manager') {
                            //тут же добавляем текущий проект к выбранному пользователю
                            update_user_meta($_POST[$key], 'tkgp_projects', $post_id);
                        }
                    }
                }

                if ($old != $new) {
                    update_post_meta($post_id, 'tkgp_' . $field['id'], $new);
                }

                break;

            case 'tkgp_vote_enabled':
                $vote_updates['enabled'] = intval($_POST[$field['id']]);
                break;

            case 'tkgp_vote_target_votes':
                $vote_updates['target_votes'] = intval($_POST[$field['id']]);
                break;

            case 'tkgp_vote_start_date':
                $vote_updates['start_date'] = DateTime::createFromFormat('d-m-Y H:i:s',
                    $_POST[$field['id']] . ' 00:00:00')->format('YmdHis');
                break;

            case 'tkgp_vote_end_date':
                if (empty($_POST[$field['id']])) {
                    $vote_updates['end_date'] = null;
                } else {
                    $vote_updates['end_date'] = DateTime::createFromFormat('d-m-Y H:i:s',
                        $_POST[$field['id']] . ' 00:00:00')->format('YmdHis');
                }
                break;

            case 'tkgp_vote_allow_against':
                $vote_updates['allow_against'] = (empty($_POST[$field['id']]) ? 0 : 1);
                break;

            case 'tkgp_vote_allow_revote':
                $vote_updates['allow_revote'] = (empty($_POST[$field['id']]) ? 0 : 1);
                break;

            case 'tkgp_vote_reset':
                if (!empty($_POST[$field['id']]) && $_POST[$field['id']] == 1) {
                    $vote_updates['reset'] = true;
                }
                break;

            case 'tkgp_parent_id':
                $old = $project->getParentProject();

                if (!empty($_POST[$field['id']])) {
                    $prnt = preg_replace('/[^\d]+/', '', $_POST[$field['id']]);
                    $prnt = TK_GProject::idToPost($prnt);
                    $prnt = new TK_GProject($prnt);
                    $new = $prnt->isValid() ? $prnt : null;

                    if (is_object($new)) {
                        if (is_object($old)) {
                            if ($new->project_id != $old->project_id) {
                                $old->deleteChildLink($project->project_id);
                                $new->createChildLink($project->project_id);
                            }
                        } else {
                            $new->createChildLink($project->project_id);
                        }
                    }
                } else if (is_object($old)) {
                    $old->deleteChildLink($project->project_id);
                }

                break;

            case 'tkgp_priority':
                if (!empty($_POST['tkgp_priority'])) {
                    $old = $project->priority;
                    $new = $_POST['tkgp_priority'];

                    if ($old != $new) {
                        $project->setProjectPriority($new);
                    }
                }
                break;

            default:
                $new = $_POST[$field['id']];

                if ($old != $new) {
                    update_post_meta($post_id, $field['id'], $new);
                }
                break;
        }
    }

    if (!empty($vote_updates)) {
        $vote = new TK_GVote($post_id);

        if (!$vote->voteExists() && $vote_updates['enabled'] == 1) {
            $vote->createVote();
        }

        if (!empty($vote_updates['reset'])) {
            $vote->resetVote();
            unset($vote_updates['reset']);
        }

        $vote->updateVoteSettings($vote_updates);
    }

    return $post_id;
}

/**
 * Create plugin options page on admin panel
 */
function tkgp_create_plugin_options()
{
    add_options_page(_x('TK Projects', 'tk_project', 'tkgp'),
        _x('TK Projects Settings', 'tk_project', 'tkgp'),
        8,
        __FILE__,
        'tkgp_option_page');
}

function tkgp_option_page()
{
    add_settings_section('tkgp_opt_section_1', 'Section 1', '', 'tkgp_global');

    add_settings_section('tkgp_opt_section_2', 'Section 2', '', 'tkgp_global');
}

/**
 * Displays input field of plugin settings.
 *
 * @param array $args
 * @param mixed $default_val
 */
function tkgp_display_options_field($args, $default_val = '')
{
    if ($args['type'] == 'editor') {
        $settings = $args['properties'];
        $value = empty($default_val) ? $args['value'] : $default_val;

        wp_editor($value, $args['id'], $settings);
    } else {
        echo tkgp_field_html($args, $default_val);
    }
}

/**
 * Return HTML code of input fields for web-forms.
 *
 * @param array $args
 * @param mixed|array $default_val
 * @return string
 */
function tkgp_field_html($args, $default_val = '')
{
    $html = '';

    $properties = '';

    if (!empty($args['properties'])) {
        foreach ($args['properties'] as $prop => $val) {
            $properties .= ' ' . $prop . (isset($val) ? ('="' . $val . '"') : '');
        }
    }

    switch ($args['type']) {
        case 'radio':
            $html .= '<ul class="tkgp_radio">';

            foreach ($args['options'] as $option) {
                $html .= '<li><input type="radio" name="' . $args['id'] . '" ' . ($option['value'] == $default_val ? 'checked="true"' : '')
                    . ' value="' . $option['value'] . $properties . '">' . $option['label'] . '</li>';
            }

            $html .= '</ul>';
            break;

        case 'number':
            $html .= '<input type="number" name="' . $args['id'] . '" value="' . (empty($default_val) ? $args['value'] : $default_val) . '"' . $properties . '/>';
            break;

        case 'select':
            $html .= '<select class="tkgp_select tkgp_group_select" name="' . $args['id'] . '"' . $properties . '>';

            foreach ($args['options'] as $option) {
                if (is_array($default_val) == true) {
                    $html .= '<option ' . (array_search($option['value'],
                            $default_val) != false ? 'selected' : '') . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                } else {
                    $html .= '<option ' . ($default_val == $option['value'] ? 'selected' : '') . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                }
            }
            $html .= '</select>';
            break;

        case 'select_user':
            if (count($default_val)) {
                $uidx = 0;

                foreach ($default_val as $user) {
                    $html .= '<div class="button tkgp_user">
					<a id="tkgp_user">' . get_user_by('ID', $user)->display_name . '</a>
					<input type="hidden" name="' . $args['id'] . ($uidx == 0 ? '' : $uidx) . '" value="' . $user . '">
					</div>';
                    ++$uidx;
                }
            }

            $html .= '<div class="button tkgp_btn tkgp_user_add">
			<a id="tkgp_user_add">' . _x('Add', 'Project Settings', 'tkgp') . '</a>
			</div>';
            break;

        case 'select_group':
            if (!defined('BP_PLUGIN_DIR')) //зависимость от BuddyPress
            {
                $html .= _x('Groups are not supported.', 'Project Settings', 'tkgp');
            } else {
                if (is_array($default_val) == true) {

                } elseif ($default_val != '') {
                    $html .= '<div class="tkgp_group button">
					<a id="tkgp_group" data-permalink="">' . groups_get_group(array('group_id' => $default_val))->name . '</a>
					<input type="hidden" name="' . $args['id'] . '" value="' . $default_val . '">
					</div>';
                }
            }

            $html .= '<div class="tkgp_btn tkgp_group_add button">
			<a id="tkgp_group_add">' . _x('Add', 'Project Settings', 'tkgp') . '</a>
			</div>';
            break;

        case 'date':
            $html .= '<input type="text' /*. $args['type']*/ . '" name="' . $args['id'] . '" value="' . (!empty($default_val) ? $default_val : $args['value']) . '" ' . $properties . ' class="tkgp_datepicker" />';
            break;

        case 'checkbox':
            $properties .= empty($default_val) && strpos($properties, 'checked') === false ? '' : 'checked';
            $html .= '<input type="' . $args['type'] . '" name="' . $args['id'] . '" value="' . $args['value'] . '" ' . $properties . ' />';
            break;

        case 'textarea':
            $html .= '<textarea name="' . $args['id'] . '" ' . $properties . '>' . $args['value'] . '</textarea>';
            break;

        default:
            $html .= '<input type="' . $args['type'] . '" name="' . $args['id'] . '"' . $properties . ' value="' . $default_val . '" />';
            break;
    }

    return $html;
}

function tkgp_include_templates($template_path)
{
    $post_type = get_post_type();
    //$news_parent_cat_id = get_option('tkgp_news_cat_id');

    if ($post_type == TK_GProject::slug) {
        if (!is_single()) {
            $template_path = TKGP_ROOT . 'styles/default/page.php';
        } else {
            $template_path = TKGP_ROOT . 'styles/default/single-page.php';
        }
    } /*else if($post_type == 'post' && $news_parent_cat_id) {
		if(!is_single()) {
			
		} else {
			
		}
	}*/

    return $template_path;
}

function tkgp_exclude_categories($args, $taxonomies)
{
    $root_cat_id = get_option('tkgp_news_cat_id');

    if (!is_admin() && !empty($root_cat_id)) {
        if (array_search('category', $taxonomies) !== false && !empty($args['child_of'])) {
            if (array_search($root_cat_id, $args['exclude_tree']) === false) {
                $args['exclude_tree'][] = $root_cat_id;
            }
        }
    }
    return $args;
}

function tkgp_check_subpages()
{
    global $wp, $wp_query, $post;

    if (!empty($wp->query_vars['tksubpage']) && !empty($post)) {
        switch ($wp->query_vars['tksubpage']) {
            case 'informo':
            case 'statistiko':
            case 'taskoj':
            case 'administrado':
            case 'teamo':
                break;
            default:
                //если подстраница неверна, перенаправляем на 404
                $wp_query->set_404();
                status_header(404);
                get_template_part(404);
                exit();
                break;
        }
    }
}

function tkgp_subpages_rewrite()
{
    global $wp_rewrite;
    $slug = TK_GProject::slug;

    add_rewrite_tag('%tksubpage%', '([^&]+)');
    add_rewrite_rule('^' . $slug . '/([^/]+)/([^/]+)/?',
        'index.php?post_type=' . $slug . '&name=$matches[1]&tksubpage=$matches[2]',
        'top');

    $wp_rewrite->flush_rules();
}

function tkgp_project_reset_cache()
{
    wp_cache_delete('tkgp_total_project_count');
}

function tkgp_upload_dir($uploads)
{
    $project = new TK_GProject($_POST['post_id']);

    $subdir = '/' . TK_GProject::slug . '/' . $project->internal_id;

    $uploads['subdir'] = $subdir;
    $uploads['path'] = $uploads['basedir'] . $subdir;
    $uploads['url'] = $uploads['baseurl'] . $subdir;

    return $uploads;
}

add_action('init', 'tkgp_create_type');
add_action('init', 'tkgp_create_taxonomy');
add_action('init', 'tkgp_subpages_rewrite');
add_action('template_redirect', 'tkgp_check_subpages');
add_action('admin_menu', 'tkgp_create_plugin_options');
add_action('add_meta_boxes', 'tkgp_create_meta_box');
add_action('save_post', 'tkgp_save_post_meta', 0);
add_action('template_include', 'tkgp_include_templates');
add_action('publish_post', 'tkgp_project_reset_cache');
add_filter('get_terms_args', 'tkgp_exclude_categories', 10, 2);
?>