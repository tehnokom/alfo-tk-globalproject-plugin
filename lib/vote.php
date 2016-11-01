<?php
/**
 * Class TK_GVote
 */
    class TK_GVote {
    	/**
		 *@var integer|null 
		 */
    	protected $vote_id;
		
		/**
		 * @var integer|null
		 */
		protected $project_id;
		
    	/**
		 * @var null $post_id
		 */
		public function __construct($post_id = null) {
			if (isset($post_id)) {
	            $res = get_post($post_id);

    	        if (is_object($res)) {
    	        	global $wpdb;
					
        	        $this->project_id = $res->ID;
					$this->vote_id = $wpdb->get_var($wpdb->prepare("SELECT id 
													 FROM {$wpdb->prefix}tkgp_votes 
													 WHERE post_id = %d", 
													 esc_sql($this->project_id))
													);
				}
	        } else {
	            $this->project_id = null;
				$this->vote_id = null;
	        }
		}
		
		public function __destruct() {
			
		}
		
		/**
		 * @param integer $user_id
		 * @return bool
		 */
		public function userCanVote($user_id) {
			return false;
		}
		
		/**
		 * @param mixed[] $arg
		 * @return mixed[]
		 */
		public function getUsersVotes($arg) {
			return array();
		}
		
		/**
		 * @param bool $include_percents
		 * @return mixed[]
		 */
		public function getVoteState($include_percents = false) {
			
			return array();
		}
		
		/**
		 * @param mixed[] $arg
		 * @return mixed[]|null
		 */
		public function getVoteSettings($arg) {
			if(is_array($arg) && isset($this->vote_id))
			{
				$columns = '*';
				
				foreach ($arg as $cur) {
					switch ($cur) {
						case 'id':
						case 'ID':
						case 'post_id':
						case 'enabled':
						case 'start_date':
						case 'end_date':
						case 'target_votes':
							if($columns == '*')
								$columns = $cur;
							
							$columns = $columns . ', ' . $columns;
							break;
						
						default:
							continue;
					}	
				}
				
				global $wpdb;
				
				return $wpdb->get_row($wpdb->prepare("SELECT {$columns} FROM {$wpdb->prefix}tkgp_votes WHERE id = %d", array($this->vote_id)), ARRAY_A);
				
			}			
			return array();
		}
		
		/**
		 * @param string $key
		 * @param mixed $val
		 * @return bool
		 */
		public function setVoteSetting($key, $val) {
			return false;
		}
		
		/**
		 * @param int $user_id
		 * @param int $variant_id
		 * @return bool
		 */
		public function addUserVote($user_id, $variant_id) {
			if(isset($user_id) && isset($variant_id) && $this->userCanVote($user_id)) {
				global $wpdb;
				
				$data = array('vote_id' => '%d',
							  'user_id' => '%d',
							  'variant_id' => '%d'
							  );
				
				$format = array($this->vote_id,
								esc_sql($user_id),
								esc_sql($variant_id));
				
				$res = $wpdb->insert($wpdb->prefix.'tkgp_usersvotes', $data, $format);
				
				if($wpdb->insert_id)
					return true;
			}
			return false;
		}
		
		/**
		 * @param int $user_id
		 * @return bool
		 */
		public function deleteUserVote($user_id) {
			if(isset($this->vote_id)) {
				global $wpdb;
				
				$res = $wpdb->query($wpdb->prepare("
					DELETE FROM {$wpdb->prefix}tkgp_usersvotes 
					WHERE vote_id = %d 
					AND user_id = %d
					", 
					array($this->vote_id, $user_id))
				);
				
				if(intval($res) == 0)
					return true;
			}
			return false;
		}
		
		/**
		 * @param mixed[] $arg
		 * @return bool
		 */
		public function createVote($arg = array()) {
			if(isset($this->project_id) && !$this->voteExists()) {
				global $wpdb;
				
				$wpdb->enable_nulls = true;
				$data = array('post_id' => '%d',
							  'enabled' => '%d',
							  'start_date' => '%s',
							  'end_date' => '%s',
							  'target_votes' => '%d');
							  
				$format = array($this->project_id,
								1,
								esc_sql($this->val($arg['tkgp_start_date'], date('YmdHis'))),
								esc_sql($this->val($arg['tkgp_end_date'], 'NULL')),
								$this->val($arg['tkgp_target_votes'], 100)
								);
				
				$wpdb->insert($wpdb->prefix.'tkgp_votes',$data,$format);
				if($wpdb->insert_id) {
					$this->vote_id = $wpdb->insert_id;
					return true; 
				}
			}
			return false;
		}
		
		/**
		 * @param mixed[] $arg
		 * @return bool
		 */
		public function updateVote($arg) {
			return false;
		}
		
		/**
		 * @param integer $post_id
		 * @return bool
		 */
		public static function exists($post_id) {
			if(isset($post_id))
			{
				global $wpdb;
				$res = $wpdb->get_var($wpdb->prepare("
													SELECT count(id) 
													FROM {$wpdb->prefix}tkgp_votes 
													WHERE post_id = %d",
													esc_sql($post_id))
									 );
				if(intval($res) > 0)
					return true;
			}
			return false;
		}
		
		/**
		 * @return bool
		 */
		public function voteExists() {
			return $this->exists($this->project_id);
		}
		
		/**
		 * @return mixed[]
		 */
		public static function getVotesFields() {	
			return array(
		        array(
		            'label' => _x('Enable Vote', 'Project Settings', 'tkgp'),
		            'desc' => _x('Enable/Disable vote for this project.', 'Project Settings', 'tkgp'),
		            'id' => 'vote_enabled',
		            'type' => 'radio',
		            'options' => array(
		                array(
		                    'label' => _x('Enable', 'Project Settings', 'tkgp'),
		                    'value' => '1'
		                ),
		                array(
		                    'label' => _x('Disable', 'Project Settings Type', 'tkgp'),
		                    'value' => '0'
		                )
		            )
		        ),
		        array(
		            'label' => _x('Vote for approval', 'Project Settings', 'tkgp'),
		            'desc' => _x('Number of votes in which the project is considered approved.', 'Project Settings', 'tkgp'),
		            'id' => 'tkgp_target_votes',
		            'type' => 'number',
		            'options' => array('min' => '1',
		            				   'value' => 100,
		            				   'step' => 1
										)
		        ),
				array(
		            'label' => _x('Start date', 'Project Settings', 'tkgp'),
		            'desc' => _x('The date of commencement of voting.', 'Project Settings', 'tkgp'),
		            'id' => 'tkgp_start_date',
		            'type' => 'date',
		            'options' => array('required')
				),
				array(
		            'label' => _x('End date', 'Project Settings', 'tkgp'),
		            'desc' => _x('The data of the end of voting.', 'Project Settings', 'tkgp'),
		            'id' => 'tkgp_end_date',
		            'type' => 'date'
				),
		        array(
		            'label' => _x('Reset voting results', 'Project Settings', 'tkgp'),
		            'desc' => _x('Reset voting results.', 'Project Settings', 'tkgp'),
		            'id' => 'tkgp_reset_vote',
		            'type' => 'checkbox'
				)
		    );	
		}

		/**
		 * @param mixed[] $arg
		 * @return bool
		 */
		protected function createVoteVariants($arg) {
			if($this->voteExists() && is_array($arg) && array_key_exists('tkgp_var_cnt', $arg)) {
				global $wpdb;
								
				for($i = 1; $i <= intval($arg['tkgp_var_cnt']); $i++) {
					if(!isset($arg['tkgp_var_id'.$i]) || !isset($arg['tkgp_var'.$i]))
						continue;
						
					$data = array('vote_id' => '%d',
								  'variant_id' => '%d',
								  'variant' => '%s',
								  'approval_flab' => '%d'
								 );
					
					$format = array(
									$this->vote_id,
									$i,
									esc_sql($arg['tkgp_var'.$i]),
									intval($arg['tkgp_appr_id']) == $i ? 1 : 0
								   );
									
					
					$wpdb->insert($wpdb->prefix.'tkgp_votevariant', $data, $format);
				}

				if($wpdb->insert_id)
					return true;
			}
			return false;
		}
		/**
		 * @param mixed|mixed[] $val
		 * @param mixed|mixed[] $default
		 * @return mixed|mixed[]
		 */
		protected function val($val, $default) {
			return isset($val) ? $val : $default;
		} 
    };
?>