<?php

function tkgp_ajax_get_user() {
	if(is_admin()) {
		$users;
		if(isset($_POST['tkgp_ufilter']) || isset($_POST['tkgp_ugfilter'])) {
			//выводим отформатированный список найденных пользователей
			$pr = new TK_GProject($_POST['post_id']);
			$query = new WP_User_Query( array('exclude' => $pr->get_managers(), 'fields' => array('ID','display_name')) );
			
			if(!empty($query->results)) {
				$alt = 0;
				
				foreach ($query->results as $current) {
?>
				<tr class="alt<?php echo ($alt%2)+1; ?>">
					<td><?php echo $current->display_name; ?></td>
					<td><input type="checkbox" name="user" value="<?php echo $current->ID; ?>"></td>
				</tr>
<?php				$alt++;
				}
			}
		} else {
			//выводим форму
?>
	<div id="tkgp_modal_user"> 
      <span id="modal_close">x</span> 
      <input id="tkgp_search" type="text" placeholder="<?php echo _x('Search...', 'Project Settings', 'tkgp'); ?>">
      <hr>
	      <div class="container">
		      <table>
		      	<tr>
		      		<th> <?php echo _x('Users', 'Project Settings', 'tkgp'); ?></th>
		      		<th>v</th>
		      	</tr>
		      </table>
	      </div>
      </div>
	</div>
	<div id="tkgp_overlay"></div>
<?php	
		}
	}

	wp_die();
}

add_action('wp_ajax_tkgp_get_user', 'tkgp_ajax_get_user');

?>