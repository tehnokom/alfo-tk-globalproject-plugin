<?php

/**
 * Class TK_GProject
 */
class TK_GProject
{
    /**
     * @var integer|null
     */
    protected $project_id;

    /**
     * TK_GProject constructor.
     * @param null $post_id
     */
    public function __construct($post_id = null)
    {
        if (isset($post_id)) {
            $res = get_post($post_id);

            if (is_object($res)) {
                $this->project_id = $res->ID;
            }
        } else {
            $this->project_id = null;
        }
    }

    /**
     *
     */
    public function __destruct()
    {

    }

    /**
     * @param bool $show_display_name
     * @return array|null
     */
    public function getManagers($show_display_name = false)
    {
        if (get_post_type($this->project_id) != 'tk_project') {
            return null;
        }

        $post = get_post($this->project_id);
        $managers = get_post_meta($this->project_id, 'manager', true);
		
        if (count($managers) == 0) {
            $managers[] = $post->post_author;
        }

        if ($show_display_name) {
            for ($i = 0; $i < count($managers); ++$i) {
                $managers[$i] = array(
                    'id' => $managers[$i],
                    'display_name' => get_user_by('ID', $current_val)->display_name
                );
            }
        }

        return $managers;
    }
	
	/**
     * @param bool $show_display_name
     * @return array|null
     */
	public function getMembers($show_display_name = false) {
		if (get_post_type($this->project_id) != 'tk_project') {
            return null;
        }
		
		$post = get_post($this->project_id);
		
		$members = get_post_meta($this->project_id, 'member', true);
		
		if ($show_display_name) {
            for ($i = 0; $i < count($members); ++$i) {
                $members[$i] = array(
                    'id' => $members[$i],
                    'display_name' => get_user_by('ID', $current_val)->display_name
                );
            }
        }
		
		return $members;
	}
	
    /**
     * @param bool $show_display_name
     * @return bool
     */
    public function getGroups($show_display_name = false)
    {
        return false;
    }

    /**
     * Update links Projects and Managers
     */
    public function updateProjectLinks()
    {

    }
	
	/**
	 * Return HTML code of Project Archive page
	 * 
	 * @return string 
	 */
	public static function getPageHtml() 
	{
		$html = '';
				
		return $html;
	}
	
	/**
	 * Return HTML code of Project Post
	 * 
	 * @return string
	 */
	public function getProjectContent()
	{
		$html = '';
		
		if($this->isValid()) {
			$post = get_post($this->project_id);
			$html .= $post->post_content;
			
			if(TK_GVote::exists($post->ID)) {
				$vote = new TK_GVote($post->ID);
			
				$user_id = get_current_user_id();
				$caps = $this->userCan($user_id);
				$html .= $vote->getResultVoteHtml($caps['vote'], !is_single($post->ID), !$caps['revote']);	
			}
		}
		
		return $html;
	}
	
	/**
	 * Check user Capabilities for this Project. Return an empty array when the user has no rights.
	 * 
	 * @param int $user_id
	 * @param array $caps Array('cap_name1', 'cap_name2', ...)
	 * @return array
	 */
	public function userCan($user_id, $caps = array()) {
		$out_caps = array();
		
		if($this->isValid()) {
			if(empty($caps)) {
				$caps = array('read','edit','work','vote','revote');
			}
			
			$p_type = intval(get_post_meta($this->project_id, 'ptype'));
			$p_visiblity = intval(get_post_meta($this->project_id, 'visiblity'));
			
			foreach ($caps as $cap) {
				$access = false;
				
				switch ($cap) {
					case 'read':
						if($p_visiblity === 0) { //Public
							$access = true;
						} elseif ($p_visiblity === 1) { //Registered
							$access = (wp_get_current_user()->ID === $user_id);
						} elseif ($p_visiblity === 2 || $p_visiblity === 3) { //Members only and Privete
							$members = $this->getManagers();
							$access = array_search($user_id, $members, true) === false ? false : true;
						} else { $access = false; }
						break;
						
					case 'edit':
						$post = get_post($this->project_id);
						$access = (array_search($user_id, $members, true) !== false 
									|| $user_id === $post->post_author
									|| is_super_admin($user_id)) ? true : false;
						break;
					
					case 'work':
						$can_edit = $this->userCan($user_id, array('edit'));
						$can_edit = $can_edit['edit'];
						$access = $can_edit || array_search($user_id, $this->getMembers()) !== false ? true : false;
						break;
						
					case 'vote':
						if($p_type === 0) {
							$access = false;
						} elseif ($p_type === 1) {
							$managers = $this->getManagers();
							$members = $this->getMembers();
							$all_members = !empty($members) ? array_merge($managers, $members) : $managers;
							
							$access = (array_search($user_id, $all_members) !== false) ? true : false;
						}
						break;
						
					case 'revote':
						$can_vote = $this->userCan($user_id, array('vote'));
						$can_vote = $can_vote['vote'];
						$vote = new TK_GVote($this->project_id);
						
						$access = ($can_vote && !$vote->userCanVote($user_id)) ? true : false;
						break;
						
					default:
						break;
				}
				
				$out_caps[$cap] = $access;
			}
		}
		
		return $out_caps;
	}
	
	/**
	 * Return TRUE where Project exist, else FALSE
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		return isset($this->project_id);	
	}
	
	/**
	 * Return array with Project Settings fields 
	 *
	 * @return array
	 */
	public static function getProjectFields()
	{
		return array(
        array(
            'label' => _x('Type', 'Project Settings', 'tkgp'),
            'desc' => _x('Type of this project.', 'Project Settings', 'tkgp'),
            'id' => 'ptype',
            'type' => 'radio',
            'options' => array(
                array(
                    'label' => _x('Private', 'Project Settings', 'tkgp'),
                    'value' => 0
                ),
                array(
                    'label' => _x('Working', 'Project Settings Type', 'tkgp'),
                    'value' => 1
                ),
                array(
                    'label' => _x('Members only', 'Project Settings Type', 'tkgp'),
                    'value' => 2
                ),
                array(
                    'label' => _x('Public', 'Project Settings Type', 'tkgp'),
                    'value' => 3
                )
            )
        ),
        array(
            'label' => _x('Project Manager', 'Project Settings', 'tkgp'),
            'desc' => _x('Manager with full access to settings this Project.', 'Project Settings', 'tkgp'),
            'id' => 'manager',
            'type' => 'select_user',
            'options' => null
        ),
        array(
            'label' => _x('Working group', 'Project Settings', 'tkgp'),
            'desc' => _x('Group to which the project was created.', 'Project Settings', 'tkgp'),
            'id' => 'group',
            'type' => 'select_group',
            'options' => null
        ),
        array(
            'label' => _x('Visibility', 'Project Settings', 'tkgp'),
            'desc' => _x('Visibility for the categories of users.', 'Project Settings', 'tkgp'),
            'id' => 'visiblity',
            'type' => 'select',
            'properties' => array('size' => '1'),
            'options' => array(
	                array(
	                    'label' => _x('Public', 'Project Settings', 'tkgp'),
	                    'value' => 0
	                ),
	                array(
	                    'label' => _x('Registered', 'Project Settings', 'tkgp'),
	                    'value' => 1
	                ),
	                array(
	                    'label' => _x('Members only', 'Project Settings', 'tkgp'),
	                    'value' => 2
	                ),
	                array(
	                    'label' => _x('Private', 'Project Settings', 'tkgp'),
	                    'value' => 3
	                )
            	)
        	),
    	);
	}
};

?>