<?php
    class TK_GProject {
    		
    	/*function get_managers($post_id, $show_display_name = false) {
    		if(get_post_type($post_id) != 'tk_project')
				return null;
			
			$managers = array();
			$post = get_post($post_id);
			
			array_push(get_post_meta($post_id, 'manager', true), $managers);
			if(count($managers) == 0)
				$managers[] = $post->post_author;
			
			if($show_display_name) {
				for($i = 0; $i< count($managers); ++$i) {
					$managers[$i] = array('id' => $managers[$i],
										  'display_name' => get_user_by('ID',$current_val)->display_name;
										 );
				}
			}
			
			return $managers;
    	}*/
    };
?>