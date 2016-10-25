<?php

function tkgp_ajax_get_user() {
	if(is_admin()) {
		$users;
		if(isset($_POST['tkgp_ufilter']) || isset($_POST['tkgp_ugfilter'])) {
			//выводим отформатированный список найденных пользователей
			echo "Ураааа!!!";
		} else {
			//выводим форму
?>
	<div id="tkgp_modal_user"> 
      <span id="modal_close">x</span> 
      <input id="tkgp_search" type="text" placeholder="<?php echo _x('Search...', 'Project Settings', 'tkgp'); ?>">
      <hr>
      <table>
      	<tr>
      		<th> <?php echo _x('Users', 'Project Settings', 'tkgp'); ?></th>
      		<th>v</th>
      	</tr>
      </table>
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