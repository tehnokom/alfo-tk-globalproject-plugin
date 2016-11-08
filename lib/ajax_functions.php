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
            tkgp_print_form();
        }
    }

    wp_die();
}

/**
 * @return string
 */
function tkgp_print_form()
{
    ?>
    <div id="tkgp_modal_user">
        <div id="tkgp_modal_header">
            <span id="modal_close">x</span>
            <input id="tkgp_search" type="text"
                   placeholder="<?php echo _x('Search...', 'Project Settings', 'tkgp'); ?>">
        </div>
        <div class="container">
            <table>
                <tr>
                    <th> <?php echo _x('Users', 'Project Settings', 'tkgp'); ?></th>
                    <th>v</th>
                </tr>
            </table>
        </div>
        <div id="tkgp_modal_footer">
            <input type="button" id="tkgp_add_selected" class="button"
                   value="<?php echo _x('Add', 'Project Settings', 'tkgp'); ?>">
        </div>
    </div>
    <div id="tkgp_overlay"></div>
    <?php
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

function tkgp_ajax_user_vote() {
	if(empty($_POST['post_id']) 
		|| empty($_POST['vote_id'])
		|| !wp_verify_nonce($_POST['vote_nonce'], 'tkgp_user_vote')
		|| !is_user_logged_in()
		) {
			//проблемы безопасности
			wp_die();	
		}
		
		$vote = new TK_GVote(intval($_POST['post_id']));
		if(!$vote->voteExists() || $vote->getVoteId() != $_POST['vote_id']) {
			//не существует такого голосования
			wp_die();
		}
		
		$user_id = get_current_user_id();
		$res = $vote->addUserVote($user_id, intval($_POST['vote_variant']));
		
		echo json_encode(array('status' => $res, 
								'message' => ($res ? _x('Your vote has been counted', 'Project Vote', 'tkgp')
												   : _x('Your vote was not counted', 'Project Vote', 'tkgp' )
											 )
							  )
						);
		
		wp_die();
}

add_action('wp_ajax_tkgp_get_user', 'tkgp_ajax_get_user');
add_action('wp_ajax_tkgp_user_vote', 'tkgp_ajax_user_vote');
add_action('wp_ajax_nopriv_tkgp_user_vote', 'tkgp_ajax_user_vote');

?>