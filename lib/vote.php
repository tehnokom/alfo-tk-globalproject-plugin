<?php
/**
 * Class TK_GVote
 */
    class TK_GVote {
    	/**
		 *@var integer|null 
		 */
    	protected $vote_id;
    	
		public function __construct() {
			
		}
		
		public function __destruct() {
			
		}
		/**
		 * @param integer
		 * @return bool
		 */
		public function userCanVote($user_id) {
			return false;
		}
		/**
		 * @param mixed[]
		 * @return mixed[]
		 */
		public function getUsersVotes($arg) {
			return array();
		}
		/**
		 * @param bool
		 * @return mixed[]
		 */
		public function getVoteState($include_percents = false) {
			return array();
		}
		/**
		 * @param mixed[]
		 * @return mixed[]
		 */
		public function getVoteSettings($arg) {
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
		            'id' => 'target_votes',
		            'type' => 'number',
		            'options' => array('min' => '1',
		            				   'value' => 100,
		            				   'step' => 1
										)
		        ),
		        array(
		            'label' => _x('Reset voting results', 'Project Settings', 'tkgp'),
		            'desc' => _x('Reset voting results.', 'Project Settings', 'tkgp'),
		            'id' => 'reset_vote',
		            'type' => 'checkbox'
				),
				array(
		            'label' => _x('Start date', 'Project Settings', 'tkgp'),
		            'desc' => _x('The date of commencement of voting.', 'Project Settings', 'tkgp'),
		            'id' => 'start_date',
		            'type' => 'date',
		            'options' => array('required')
				),
				array(
		            'label' => _x('End date', 'Project Settings', 'tkgp'),
		            'desc' => _x('The data of the end of voting.', 'Project Settings', 'tkgp'),
		            'id' => 'end_date',
		            'type' => 'date'
				)
		    );	
		} 
    };
?>