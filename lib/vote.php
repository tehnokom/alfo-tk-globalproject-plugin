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
		            				   'step' => 100
										)
		        ),
		        array(
		            'label' => _x('Reset voting results', 'Project Settings', 'tkgp'),
		            'desc' => _x('Reset voting results.', 'Project Settings', 'tkgp'),
		            'id' => 'reset_vote',
		            'type' => 'checkbox'
				)
		    );	
		} 
    };
?>