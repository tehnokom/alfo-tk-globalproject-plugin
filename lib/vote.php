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
													 $this->project_id)
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
			if(isset($this->vote_id) && isset($user_id)) {
				global $wpdb;
				
				$res = $wpdb->get_var($wpdb->prepare("SELECT count(uv.id)
										FROM {$wpdb->prefix}tkgp_usersvotes uv, {$wpdb->prefix}tkgp_votes vv
										WHERE vv.id = uv.vote_id
										AND vv.enabled = 1
										AND uv.vote_id = %d
										AND uv.user_id = %d;", 
										$this->vote_id, $user_id)
										);
				if(intvar($res) == 0)
					return true;
			}
			return false;
		}
		
		/**
		 * @param mixed[] $arg
		 * @return mixed[]
		 */
		public function getUsersVotes($arg) {
			if(isset($this->vote_id) && isset($arg)) {
				global $wpdb;
				
				$where_in = '';
				foreach ($arg as $user) {
					if($where_in == '')
						$where_in = $user;
					else $where_i = $where_in . ',' . $user; 
				}
				
				$query = $wpdb->prepare("SELECT * 
										FROM {$wpdb->prefix}tkgp_usersvotes 
										WHERE vote_id = %d 
										AND user_id IN ({$where_in});",
										$this->vote_id);
				
				return $wpdb->get_results($query, ARRAY_A);	
			}
			return array();
		}
		
		/**
		 * @param bool $include_percents
		 * @return mixed[]
		 */
		public function getVoteState($include_percents = false) {
			if(isset($this->vote_id)) {
				global $wpdb;
				
				$query = '';
				
				if($this->variantExists()) {
					$query = $wpdb->prepare("SELECT uv.variant_id AS id, vv.variant AS variant, count(uv.id) AS cnt 
											FROM {$wpdb->prefix}tkgp_usersvotes uv, {$wpdb->prefix}tkgp_votevariant vv
											WHERE vv.vote_id = uv.vote_id
											AND uv.vote_id = %d
											GROUP BY uv.variant_id, vv.variant"
										, $this->vote_id);
				} else {
					$query = $wpdb->prepare("SELECT uv.variant_id AS id, count(uv.id) AS cnt 
											FROM {$wpdb->prefix}tkgp_usersvotes uv
											WHERE uv.vote_id = %d
											GROUP BY uv.variant_id
											ORDER BY uv.variant_id DESC"
										, $this->vote_id);
				}
				
				return $wpdb->get_results($query, ARRAY_A);
			}
			return array();
		}
		
		/**
		 * @return bool
		 */
		public function variantExists () {
			if(isset($this->vote_id)) {
				global $wpdb;
				
				$res = $wpdb->get_var($wpdb->prepare("SELECT count(id) 
												FROM {$wpdb->prefix}tkgp_votevariant 
												WHERE vote_id = %d", 
												$this->vote_id)
												);				
				if(intval($res))
					return true;
			}
			return false;
		}
		
		/**
		 * @param mixed[] $arg
		 * @return mixed[]|null
		 */
		public function getVoteSettings($arg = array()) {
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
							else $columns = $columns . ', ' . $columns;
							break;
						
						default:
							continue;
					}	
				}
				
				global $wpdb;
				
				return $wpdb->get_row($wpdb->prepare("SELECT {$columns} FROM {$wpdb->prefix}tkgp_votes WHERE id = %d;", $this->vote_id), ARRAY_A);
				
			}			
			return array();
		}
		
		/**
		 * @param string $key
		 * @param mixed $val
		 * @return bool
		 */
		public function setVoteSetting($key, $val) {
			if(isset($this->vote_id)) {
				global $wpdb;
				
				$format = array();
				
				switch ($key) {
					case 'start_date':
					case 'end_date':
						$format[] = '%s';
						break;
					
					default:
						$format[] = '%d';
						break;
				}
				
				$res = $wpdb->update($wpdb->prefix . 'tkgp_votes', 
							  array($key => $val), 
							  array('id' => $this->vote_id),
							  $format,
					
							  array('%d'));
				if($res !== false)
					return true;
			}
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
				
				$data = array('vote_id' => $this->vote_id,
							  'user_id' => $user_id,
							  'variant_id' => $variant_id
							  );
				
				$format = array('%d',
								'%d',
								'%d');
				
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
			if(isset($this->vote_id) && isset($user_id)) {
				global $wpdb;
				
				$res = $wpdb->query($wpdb->prepare("
					DELETE FROM {$wpdb->prefix}tkgp_usersvotes 
					WHERE vote_id = %d 
					AND user_id = %d;
					", 
					$this->vote_id, $user_id)
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
				$data = array('post_id' => $this->project_id,
							  'enabled' => 1,
							  'start_date' => $this->val($arg['tkgp_start_date'], date('YmdHis')),
							  'end_date' => $this->val($arg['tkgp_end_date'], NULL),
							  'target_votes' => $this->val($arg['tkgp_target_votes'], 100));
							  
				$format = array('%d',
								'%d',
								'%s',
								'%s',
								'%d'
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
													WHERE post_id = %d;",
													array($post_id))
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
						
					$data = array('vote_id' => $this->vote_id,
								  'variant_id' => $i,
								  'variant' => $arg['tkgp_var'.$i],
								  'approval_flab' => intval($arg['tkgp_appr_id']) == $i ? 1 : 0
								 );
					
					$format = array(
									'%d',
									'%d',
									'%s',
									'%d'
								   );
									
					
					$wpdb->insert($wpdb->prefix.'tkgp_votevariant', $data, $format);
				}

				if($wpdb->insert_id)
					return true;
			}
			return false;
		}

		/**
		 * @return mixed[]
		 */
		public function getVoteVariants() {
			if(isset($this->vote_id)) {
				global $wpdb;
				
				$quote = $wpdb->prepare("SELECT variant_id, variant 
										FROM {$wpdb->prefix}tkgp_votevariant
										WHERE vote_id = %d
										",
										$this->vote_id); 
				
				return $wpdb->get_results($query, ARRAY_A);
			}
			
			return array();
		}
		
		/**
		 * @return string
		 */
		public function getResultVoteHtml() {
			$form = '';
			
			if(isset($this->vote_id)) {
				$target_votes = $this->getVoteSettings(array('target_votes'));
				$target_votes = floatval($target_votes['target_votes']);
				$votes = $this->getVoteState();	
				$form .= '<dev id="tkgp_vote_result">';
				
				if($this->variantExists()) {

				} else {
					$approval = 100.0 * floatval(isset($votes[0]) ? $votes[0]['cnt'] : 0) / $target_votes;
					$reproval = 100.0 * floatval(isset($votes[1]) ? $votes[1]['cnt'] : 0) / $target_votes;
								
					$form .= '<dev id="tkgp_approval_status">';								
					$form .= '<div id="tkgp_approval" style="display: inline; width: '. $approval .'%; background: #FF0000;"></div>
					<div id="tkgp_reproval" style="display: inline; width: '. $reproval .'%; background: #EEE;"></div>';
					$forum .= '	</dev>';
				}
										
				$form .= '</dev>';
			}
			return $form;
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