<?php


function tkgp_ajax_get_user()
{
    if (is_admin()) {
//        $users;
        if (!empty($_POST['tkgp_ufilter']) || !empty($_POST['tkgp_ugfilter'])) {
            //выводим отформатированный список найденных пользователей
            $pr = new TK_GProject($_POST['post_id']);
            $query = new WP_User_Query(array(
                'exclude' => $pr->getManagers(),
                'fields' => array('ID', 'display_name'),
                'orderby' => 'display_name',
                'search' => '*' . $_POST['tkgp_ufilter'] . '*',
                'search_columns' => 'display_name',
                'order' => 'ASC'
            ));

            if (!empty($query->results)) {
                echo tkgp_print_results($query->results);
            }
        } else {
            //выводим форму
            tkgp_print_form('users');
        }
    }

    wp_die();
}

/**
 * @param string $type
 * @return string
 */
function tkgp_print_form($type)
{
    echo '<div id="tkgp_modal_user">
        <div id="tkgp_modal_header">
            <span id="modal_close">x</span>';

    switch ($type) {
        case 'users':
            echo '<input id="tkgp_search" type="text" '
                . 'placeholder="' . _x('Search...', 'Project Settings', 'tkgp') . '">'
                . '</div>
        		<div class="container">
					<table>
                		<tr>
                    		<th>' . _x('Users', 'Project Settings', 'tkgp') . '</th>
                    		<th>v</th>
                		</tr>
            		</table>
        		</div>
        	<div id="tkgp_modal_footer">
        		<input type="button" id="tkgp_add_selected" class="button" value="'
                . _x('Add', 'Project Settings', 'tkgp') . '">
        	</div>';
            break;

        case 'settings':
            echo '</div>';

            $post = get_post($_POST['post_id'], OBJECT, 'edit');

            wp_editor($post->post_content, 'editpost');
            \_WP_Editors::enqueue_scripts();
            print_footer_scripts();
            \_WP_Editors::editor_js();
            break;

        default:

            break;
    }

    echo '</div>
    <div id="tkgp_overlay"></div>';
}

/**
 * @param $results
 * @return string
 */
function tkgp_print_results($results)
{
    $html = '';
    if ($results) {
        $alt = 0;

        foreach ($results as $current) {
            $html .= '<tr class="alt' . (($alt % 2) + 1) . '">'
                . '<td>' . $current->display_name . '</td>'
                . '<td><input type="checkbox" name="user" value="' . $current->ID . '"></td>'
                . '</tr>';
            $alt++;
        }
    }

    return $html;
}

function tkgp_ajax_policy(&$message)
{
    $res = false;

    if (empty($_POST['post_id'])
        || empty($_POST['vote_id'])
        || !is_user_logged_in()
    ) {
        //проблемы безопасности
        $message = _x('Operation is not allowed', 'Project Vote', 'tkgp');
        return $res;
    }

    $project = new TK_GProject(intval($_POST['post_id']));
    $vote = $project->getVote();
    $user_id = get_current_user_id();

    if (!$vote->voteExists() || $vote->vote_id != $_POST['vote_id']) {
        //не существует такого голосования
        $message = _x('Voting does not exist or is hidden', 'Project Vote', 'tkgp');
    } else {
        $res = true;
    }

    return $res;
}

function tkgp_ajax_user_vote()
{
    $res = false;
    $message = '';

    if (tkgp_ajax_policy($message)
        && wp_verify_nonce($_POST['vote_nonce'], 'tkgp_user_vote')
    ) {
        $project = new TK_GProject(intval($_POST['post_id']));
        $vote = $project->getVote();
        $user_id = get_current_user_id();

        if (!$vote->enabled) {
            //голосование выключено
            $message = _x('Voting was disabled', 'Project Vote', 'tkgp');
        } else if (!$project->userCanVote($user_id)) {
            //пользователь не имеет права голосовать
            $message = _x('You have no rights to vote', 'Project Vote', 'tkgp');
        } else {
            $res = $vote->addUserVote($user_id, intval($_POST['vote_variant']));
            $message = ($res ? _x('Your vote has been counted', 'Project Vote', 'tkgp')
                : _x('Your vote was not counted', 'Project Vote', 'tkgp'));
        }
    }

    echo json_encode(array(
        'status' => $res,
        'message' => $message
    ));

    wp_die();
}

function tkgp_ajax_reset_user_vote()
{
    $res = false;
    $message = '';

    if (tkgp_ajax_policy($message)
        && wp_verify_nonce($_POST['vote_nonce'], 'tkgp_reset_user_vote')
    ) {
        $project = new TK_GProject(intval($_POST['post_id']));
        $vote = new TK_GVote(intval($_POST['post_id']));
        $user_id = get_current_user_id();

        if (!$vote->enabled) {
            //голосование выключено
            $message = _x('Voting was disabled', 'Project Vote', 'tkgp');
        } else if (!$project->userCanRevote($user_id)) {
            //пользователь не имеет права сбрасывать голос
            $message = _x('You can not cancel your vote', 'Project Vote', 'tkgp');
        } elseif ($vote->userCanVote($user_id)) {
            //пользователь не голосовал
            $message = _x('You may not vote', 'Project Vote', 'tkgp');
        } else {
            $res = $vote->deleteUserVote($user_id);
            $message = ($res ? _x('Your vote has been reset', 'Project Vote', 'tkgp')
                : _x('Your vote was not reset', 'Project Vote', 'tkgp'));
        }
    }

    echo json_encode(array(
        'status' => $res,
        'message' => $message,
    ));

    wp_die();
}

function tkgp_ajax_get_vote_status()
{
    $html = '';
    $res = false;

    if (tkgp_ajax_policy($html)
        && (wp_verify_nonce($_POST['vote_nonce'], 'tkgp_reset_user_vote')
            || wp_verify_nonce($_POST['vote_nonce'], 'tkgp_user_vote'))
    ) {
        $project = new TK_GProject($_POST['post_id']);
        $vote = $project->getVote();
        $user_id = get_current_user_id();

        if (!$project->userCanRead($user_id)) {
            //нет доступа к проекту
            $html = _x('You do not have access to the data of voting', 'Project Vote', 'tkgp');
        } else {
            $res = true;

            $btn_titles = array();
            foreach ($_POST as $key => $value) {
                if (preg_match('/^(\w)+_(title|text)$/', $key)) {
                    $btn_titles[$key] = $value;
                }
            }

            $html = $vote->getVoteButtonHtml(false, $btn_titles);
        }

    }

    if (!$res) {
        $html = '<p><center><h3>' . $html . '</h3></center></p>';
    }

    echo json_encode(array(
        'status' => true,
        'approval_votes' => $vote->approval_votes,
        'reproval_votes' => $vote->reproval_votes,
        'target_votes' => $vote->target_votes,
        'new_content' => $html
    ));

    wp_die();
}

function tkgp_ajax_get_project_editor()
{
    $message = _x('Operation is not allowed', 'Project Vote', 'tkgp');

    if (!empty($_POST['post_id'])
        && wp_verify_nonce($_POST['access_nonce'], 'tkgp_project_access')
    ) {
        $project = new TK_GProject($_POST['post_id']);
        $user_id = get_current_user_id();

        if ($project->userCanEdit($user_id)) { //есть права на редактирование проета
            tkgp_print_form('settings'); //выводим форму редактора
            wp_die();
        }

        $message = _x('You do not have permission to edit project', 'Project Edit', 'tkgp');

    }

    echo json_encode(array('status' => false, 'message' => $message));

    wp_die();
}

function tkgp_ajax_get_project_news()
{
    if (!empty($_POST['post_id'])) {
        $project = new TK_GProject($_POST['post_id']);
        $user_id = get_current_user_id();

        if ($project->isValid() && $project->userCanRead($user_id)) {
            require_once(TKGP_STYLES_DIR . 'default/ajax-news-page.php');
        }
    }

    wp_die();
}

function tkgp_get_project_target()
{
    if (!empty($_POST['post_id'])) {
        $project = new TK_GProject($_POST['post_id']);
        $user_id = get_current_user_id();

        if ($project->isValid() && $project->userCanRead($user_id)) {
            echo apply_filters('the_content', $project->description);
        }
    }

    wp_die();
}

function tkgp_get_projects()
{
    require_once(TKGP_STYLES_DIR . 'default/ajax-page.php');
    wp_die();
}

function tkgp_get_tasks()
{
    if (!empty($_POST['post_id'])) {
        $project = new TK_GProject($_POST['post_id']);
        $user_id = get_current_user_id();

        if ($project->isValid() && $project->userCanRead($user_id)) {
            require_once(TKGP_STYLES_DIR . 'default/ajax-tasks.php');
        }
    }

    wp_die();
}

function tkgp_task_save()
{
    $out = array('status' => 'error');
    if (!empty($_POST['post_id']) && is_admin()) {
        $title = tkgp_qtranslatex_compile($_POST['title']);
        $desc = tkgp_qtranslatex_compile($_POST['desc']);

        if(empty($_POST['task_id'])) {
            $task = TK_GTask::createTask($_POST['post_id'],
                array('title' => $title,
                    'description' => $desc,
                    'type' => $_POST['type'],
                    'status' => 1,
                    ),
                $_POST['parent_id']);

            if($task) {
                $out['status'] = 'ok';
            }
        }
    }

    echo json_encode($out);

    wp_die();
}

/**
 * Returns string for qtranslate-x
 *
 * @param array $args
 * @return string
 */
function tkgp_qtranslatex_compile($args) {
    $out = '';
    $in = json_decode(str_replace('\"','"', $args), true);

    if(is_array($in) && !empty($in)) {
        foreach ($in as $key => $val) {
            $out .= "[:$key]$val";
        }

        $out .= strlen($out) > 5 ? '[:]' : '';
        $out = str_replace('\n',"\n", $out);
    }

    return $out;
}

function tkgp_task_delete() {
    $out = array('status' => 'error',
        'msg' => _x('Access denaided.', 'Project Tasks', 'tkgp'));

    if (!empty($_POST['task_id']) && is_admin()) {
        $task = new TK_GTask($_POST['task_id']);

        if($task && $task->post_id == $_POST['post_id']) {
            $task->setStatus(4);
            $out['status'] = 'ok';
            $out['msg'] = _x('Task marked as deleted.', 'Project Tasks', 'tkgp');
        } else {
            $out['msg'] = _x('Task not found.', 'Project Tasks', 'tkgp');
        }
    }

    echo json_encode($out);

    wp_die();
}

function tkgp_get_task_data()
{
    $out = array('status' => 'error',
        'msg' => 'Access denaided.',
        'data' => null);

    if (!empty($_POST['task_id']) && is_admin()) {
        $task = new TK_GTask($_POST['task_id']);

        if ($task) {
            $out['status'] = 'ok';
            unset($out['msk']);
            $out['data'] = array(
                    'title' => $task->title,
                    'desc' => $task->description,
                    'type' => $task->type,
                    'status' => $task->status
            );
        }

    }

    echo json_encode($out);

    wp_die();
}

add_action('wp_ajax_tkgp_get_user', 'tkgp_ajax_get_user');
add_action('wp_ajax_tkgp_user_vote', 'tkgp_ajax_user_vote');
add_action('wp_ajax_tkgp_get_vote_status', 'tkgp_ajax_get_vote_status');
add_action('wp_ajax_tkgp_reset_user_vote', 'tkgp_ajax_reset_user_vote');
add_action('wp_ajax_tkgp_get_project_editor', 'tkgp_ajax_get_project_editor');

add_action('wp_ajax_tkgp_get_project_news', 'tkgp_ajax_get_project_news');
add_action('wp_ajax_nopriv_tkgp_get_project_news', 'tkgp_ajax_get_project_news');
add_action('wp_ajax_tkgp_get_project_target', 'tkgp_get_project_target');
add_action('wp_ajax_nopriv_tkgp_get_project_target', 'tkgp_get_project_target');
add_action('wp_ajax_tkgp_get_projects', 'tkgp_get_projects');
add_action('wp_ajax_nopriv_tkgp_get_projects', 'tkgp_get_projects');
add_action('wp_ajax_tkgp_get_project_tasks', 'tkgp_get_tasks');
add_action('wp_ajax_nopriv_tkgp_get_project_tasks', 'tkgp_get_tasks');
add_action('wp_ajax_tkgp_task_save', 'tkgp_task_save');
add_action('wp_ajax_tkgp_get_task_data', 'tkgp_get_task_data');
add_action('wp_ajax_tkgp_task_delete', 'tkgp_task_delete');
?>