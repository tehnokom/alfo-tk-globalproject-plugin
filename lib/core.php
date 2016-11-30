<?php
if (!defined('TKGP_ROOT') || !defined('TKGP_URL')) {
    exit;
}

require_once(TKGP_ROOT . 'lib/project.php');
require_once(TKGP_ROOT . 'lib/vote.php');

/**
 * Создание типа проектов
 */
function tkgp_create_type()
{
    register_post_type('tk_project',
        array(
            'labels' => array(
                'name' => _x('TK Projects', 'tk_project', 'tkgp'),
                'singular_name' => _x('TK Projects', 'tk_project_singular', 'tkgp'),
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
        array('tk_project'),
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

    if ($post->post_type != 'tk_project') {
        return;
    }

    add_meta_box('tk_project_meta_settings',
        _x('Settings of Project', 'tk_meta', 'tkgp'),
        'tkgp_show_metabox_settings',
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

function tkgp_show_metabox_settings()
{
    global $post;

    if ($post->post_type != 'tk_project') {
        return;
    }

    echo '<input type="hidden" name="tkgp_meta_settings_nonce" value="' . wp_create_nonce(basename(__FILE__) . '_settings') . '" />
<table class="form-table">';

    foreach (TK_GProject::getProjectFields() as $field) {
        echo '<tr>
	<th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
	<td>';
        switch ($field['type']) {
            case 'radio': //Переключатель
                $current_val = get_post_meta($post->ID, $field['id'], true);
                $current_val = $current_val == '' ? '1' : $current_val;				
				
				tkgp_display_options_field($field, $current_val);
                break;

            case 'select': //Выпадающее меню
                $current_val = get_post_meta($post->ID, $field['id'], true);
                $current_val = $current_val == '' ? '1' : $current_val;
				
				tkgp_display_options_field($field, $current_val);
                break;

            case 'select_user':
                $current_val = get_post_meta($post->ID, $field['id'], true);
                $current_val = $current_val == '' ? wp_get_current_user()->ID : $current_val;
				
				tkgp_display_options_field($field, $current_val);
                break;

            case 'select_group':
                tkgp_display_options_field($field, $current_val);
                break;

            default:
                echo '<input type="text">';
                break;

                echo '</td>';
        }
        echo '</tr>';
    }

    echo '</table>';
}

function tkgp_show_metabox_steps()
{
    ?>
    <input type="hidden" name="tkgp_meta_steps_nonce"
           value="<?php echo wp_create_nonce(basename(__FILE__) . '_steps'); ?>"/>

    <?php
}

function tkgp_show_metabox_votes()
{
    global $post;
	
	$vote = new TK_GVote($post->ID);
	$vote_settings = $vote->getVoteSettings();
    ?>
    <input type="hidden" name="tkgp_meta_votes_nonce"
           value="<?php echo wp_create_nonce(basename(__FILE__) . '_votes'); ?>"/>
    <table class="form-table">
        <?php
        foreach (TK_GVote::getVotesFields() as $field) {
        	$cur_id = str_replace('tkgp_vote_', '', $field['id']);
			$current_val = $vote_settings[ $cur_id ];
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
							$current_val = !empty($current_val) ? $current_val = date('d-m-Y',strtotime($current_val)) : $current_val;	
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
        || !wp_verify_nonce($_POST['tkgp_meta_steps_nonce'], basename(__FILE__) . '_steps')
        || $_POST['post_type'] != 'tk_project'
        || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || !current_user_can('edit_page', $post_id) //проверка на доступ пользователя, потом нужно будет доработать
    ) {
        return $post_id;
    }

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
				$pattern = '/^(' . $field['id'] .'){1}[0-9]*$/';
				$list = tkgp_array_search($pattern, $_POST);
				
                foreach($list as $key => $val) {
					if (is_numeric($val)) {
                    	$new[] = $val;

						if ($field['id'] == 'manager') {
							//тут же добавляем текущий проект к выбранному пользователю
							update_user_meta($_POST[$idx], 'tkgp_projects', $post_id);
						}
					}
                }

                if ($old != $new) {
                    update_post_meta($post_id, $field['id'], $new);
                }

                break;
				
			case 'tkgp_vote_enabled':
				$vote_updates['enabled'] = intval($_POST[$field['id']]);
				break;
			
			case 'tkgp_vote_target_votes':
				$vote_updates['target_votes'] = intval($_POST[$field['id']]);
				break;
			
			case 'tkgp_vote_start_date':
				$vote_updates['start_date'] = DateTime::createFromFormat('d-m-Y H:i:s',$_POST[$field['id']].' 00:00:00')->format('YmdHis');
				break;
			
			case 'tkgp_vote_end_date':
				if(empty($_POST[$field['id']])) { 
					$vote_updates['end_date'] = null;
				} else { 
					$vote_updates['end_date'] = DateTime::createFromFormat('d-m-Y H:i:s',$_POST[$field['id']].' 00:00:00')->format('YmdHis');
				}
				break;
				
			case 'tkgp_vote_allow_revote':
				$vote_updates['allow_revote'] = (empty($_POST['tkgp_vote_allow_revote']) ? null : 1);
				break;
				
			case 'tkgp_vote_reset':
				if(!empty($_POST['tkgp_vote_reset']) && $_POST['tkgp_vote_reset'] == 1) {
					$vote_updates['reset'] = true;
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

	if(!empty($vote_updates)) {
		global $post;
		$vote = new TK_GVote($post->ID);
		
		if(!$vote->voteExists() && $vote_updates['enabled'] == 1) {
			$vote->createVote();
		}
		
		if(!empty($vote_updates['reset'])) {
			$vote->resetVote();
			unset($vote_updates['reset']);
		}
		
		$vote->updateVoteSettings($vote_updates);
	}

    return $post_id;
}

/**
 * Looking at all the keys in the array pattern returns an array with the pairs 'key' => 'value' of all the found keys.
 * 
 * @param string Template string
 * @param array $target_array Array for search
 */
function tkgp_array_search($key_template, $target_array) {
	$out = array();
	
	foreach ($target_array as $key => $value) {
		if(preg_match($key_template, $key)) {
			$out[$key] = $value;
		}
	}
	
	return $out;
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
	add_settings_section('tkgp_opt_section_1','Section 1', '', 'tkgp_global');
		
	add_settings_section('tkgp_opt_section_2','Section 2', '', 'tkgp_global');
}

/**
 * Displays input field of plugin settings.
 * 
 * @param array $args
 * @param mixed $default_val
 */
function tkgp_display_options_field($args, $default_val = '')
{
	echo tkgp_field_html($args, $default_val);
}

/**
 * Return HTML code of input fields for web-forms.
 * 
 * @param array $args
 * @param mixed|array $default_val
 */
function tkgp_field_html($args, $default_val = '')
{
	$html = '';
	
	$properties = '';
	
	if(!empty($args['properties'])) {
		foreach ($args['properties'] as $prop => $val) {
			$properties .=  ' ' . $prop . (isset($val) ? ('="' . $val . '"') : '');
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
					$html .= '<option ' . (array_search($option['value'], $default_val) != false ? 'selected' : '') . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
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
			$html .= '<input type="text' /*. $args['type']*/ . '" name="' . $args['id'] . '" value="' . $default_val . '" ' . $properties . ' class="tkgp_datepicker" />';
			break;
		
		case 'checkbox':
			$properties .= empty($default_val) && strpos($properties, 'checked') === false ? '' : 'checked';
			$html .= '<input type="' . $args['type'] . '" name="' . $args['id'] . '" value="' . $args['value'] . '" ' . $properties . ' />';
			break;
		
		default:
			$html .= '<input type="' . $args['type'] . '" name="' . $args['id'] . '"' . $properties . ' value="' . $default_val . '">';
			break;
	}
		
	return $html; 
}


function tkgp_content($data)
{
    global $post;

    if ($post->post_type == 'tk_project') {
    	$project = new TK_GProject($post->ID);
		
		$data = $project->getProjectContent();
    }
    return $data;
}

add_action('init', 'tkgp_create_type');
add_action('init', 'tkgp_create_taxonomy');
add_action('admin_menu', 'tkgp_create_plugin_options');
add_action('add_meta_boxes', 'tkgp_create_meta_box');
add_filter('the_content', 'tkgp_content');
add_action('save_post', 'tkgp_save_post_meta', 0);
?>