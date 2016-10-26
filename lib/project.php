<?php
    class TK_GProject {
    	protected $project_id;
		
		public function __construct($post_id = null) {
			if(isset($post_id)) {
				$res = get_post($post_id);
				
				if(is_object($res)) 
					$this->project_id = $res->ID;
			}
			else $this->project_id = null;
		}
		
		public function __destruct() {
			
		}
			
    	public function get_managers($show_display_name = false) {
    		if(get_post_type($this->project_id) != 'tk_project')
				return null;
			
			$managers = array();
			$post = get_post($this->project_id);
			
			array_push(get_post_meta($this->project_id, 'manager', false), $managers);
			if(count($managers) == 0)
				$managers[] = $post->post_author;
			
			if($show_display_name) {
				for($i = 0; $i< count($managers); ++$i) {
					$managers[$i] = array('id' => $managers[$i],
										  'display_name' => get_user_by('ID',$current_val)->display_name
										 );
				}
			}
			
			return $managers;
    	}
		
		public function get_groups($show_display_name = false) {
			return false;
		}
			
		public function update_project_links() { //обновляем связи проекта с менеджерами и группами
			
		}
    };

?>