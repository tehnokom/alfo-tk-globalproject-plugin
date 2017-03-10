<?php

/**
 * Class TK_GProject
 */
class TK_GProject
{
	/**
     * WordPress Database Access Abstraction Object
     * @var
     */
    protected $wpdb;
		
	/**
	 * @var bool
	 */
	protected $is_project = false;
	
	/**
	 * @var int
	 */
	protected $project_type;
	
	/**
	 * @var int
	 */
	protected $project_visibility;
	 
	 /**
	  * @var array
	  */
	protected $opts;

	/**
	 * @const string slug
	 */
	const slug = 'projektoj';
	
    /**
     * TK_GProject constructor.
     * @param null $post_id
     */
    public function __construct($post_id = null)
    { 	
        if (isset($post_id)) {
            $res = get_post($post_id);

            if (is_object($res)) {
                $this->opts['project_id'] = $res->ID;
				$this->is_project = $res->post_type == TK_GProject::slug;
				
				if($this->is_project) {
					global $wpdb;
		
    				$this->wpdb = $wpdb;
					$this->wpdb->enable_nulls = true;
    				
					$this->opts['target'] = get_post_meta($this->project_id, 'ptarget', true);
					$this->opts['guid'] = $res->guid;
					$this->opts['title'] = get_the_title($this->project_id);
					$this->opts['permalink'] = get_permalink($this->project_id);
					$this->project_type = intval(get_post_meta($this->project_id, 'ptype', true));
					$this->project_visibility = intval(get_post_meta($this->project_id, 'visiblity', true));
				}
            }
        } else {
            $this->project_id = null;
        }
    }

	/**
	 * Magic method. It's Ma-a-a-gic :)
	 * @param string $name
	 * @return mixed | null
	 */
	public function __get($name) {
		if($name == 'description') {
			$post = get_post($this->project_id);
			return $post->post_content;
		} else if(array_key_exists($name, $this->opts)) {
			return $this->opts[$name];
		}
		
		return null;
	}
	
	public function __isset($name) {
		return (isset($this->opts[$name]));
	}
	
    /**
     * @param bool $show_display_name
     * @return array|null
     */
    public function getManagers($show_display_name = false)
    {
        if (!$this->is_project) {
            return null;
        }

        $post = get_post($this->project_id);
        $managers = get_post_meta($this->project_id, 'manager', true);
		
		if(empty($managers)) {
			$managers[] = $post->post_author;
		}
		
		if(!is_array($managers)) {
			$managers[] = $managers;
		}

        if ($show_display_name) {
            for ($i = 0; $i < count($managers); ++$i) {
                $managers[$i] = array(
                    'id' => $managers[$i],
                    'display_name' => get_user_by('ID', $managers[$i])->display_name
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
		if (!$this->is_project) {
            return null;
        }
		
		$post = get_post($this->project_id);
		
		$members = get_post_meta($this->project_id, 'member', true);
		
		if(!is_array($members)) {
			$members[] = $members;
		}
		
		if ($show_display_name) {
            for ($i = 0; $i < count($members); ++$i) {
                $members[$i] = array(
                    'id' => $members[$i],
                    'display_name' => get_user_by('ID', $members[$i])->display_name
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
	
	protected function getEditPostHtml()
	{
		$html = '<p style="text-align:right;">
		<span class="tkgp_edit_button">';
		
		$html .= _x('Edit Project','Project Edit', 'tkgp');
		$html .= '<input type="hidden" name="tkgp_access_nonce" value="' . wp_create_nonce('tkgp_project_access') . '"/>
		<input type="hidden" name="tkgp_post_id" value="' . $this->project_id . '"/>
		</span>
		</p>';
		
		return $html;	
	}
	
	/**
	 * Return HTML code of Project Post
	 * 
	 * @param string $data
	 * @return string
	 */
	public function getProjectContent($data = '')
	{
		$html = $data;
		
		if($this->isValid()) {
			$user_id = get_current_user_id();
			$html = get_post_meta($this->project_id, 'ptarget', true);
			$html = wpautop($html);
			
			if(is_user_logged_in() && $this->userCanEdit($user_id)) {
				// код кнопки редактирования
				$html = $this->getEditPostHtml() . $html;
			}
			
			if(TK_GVote::exists($this->project_id)) {
				$vote = new TK_GVote($this->project_id);
				$caps = $this->userCan($user_id);
				$html .= $vote->getResultVoteHtml($caps['vote'], false, !$caps['revote']);	
			}

			$html .= $this->getTasksHtml();
			
			if(is_single($this->project_id)) {
				$html .= wpautop($data);
			}
		}
		
		return $html;
	}
	
	/**
	 * Return HTML code of project Tasks
	 * 
	 * @return strng
	 */
	public function getTasksHtml()
	{
		$html = '';
		
		if(!$this->is_project){
			return $html;
		}
		
		$html = '<div class="tkgp_tasks">
		<br id="tkgp_tasks_tab2">
		<br id="tkgp_tasks_tab3">
		<a href="#tkgp_tasks_tab1">' . _x('Tasks','Project Tasks','tkgp') . '</a><a href="#tkgp_tasks_tab2">' . 
		_x('Sub-projects','Project Tasks','tkgp') . '</a>
		<div>';
		
		$tree = $this->buildTasksTree($this->project_id, 0);
		$html .= (empty($tree) ? '<p>' . _x('The tasks are still undefined', 'Project Tasks','tkgp') . '</p>' : $tree) .
		'</div>';
		
		$html .= '<div>';
		
		$children = $this->getChildProjects();
		if(!empty($children)) {
			$html .= '<ul>';
			
			foreach ($children as $child) {
				$html .= "<li><a href=\"{$child->permalink}\">{$child->title}</a></li>";
			}
			$html .= '</ul>';
		} else {
			$html .= '<p>' . _x('No associated projects', 'Project Tasks', 'tkgp') . '</p>';
		}
		
		$html .= '</div>
		</div>';
		
		return $html;
	}
	
	/**
	 * Recursively builds a task tree
	 * 
	 * @param int @parent_id
	 * @param int @parent_type
	 * @param string @root_tag (ul | ol)
	 * @return string
	 */
	public function buildTasksTree($parent_id, $parent_type)
	{
		$html = '';
		
		$sql = "SELECT `child_id`, `child_type` FROM `{$this->wpdb->prefix}tkgp_tasks_links`
				WHERE `parent_id` = %d
				AND `parent_type` = %d
				AND `child_type` <> 0";
		
		$res = $this->wpdb->get_results($this->wpdb->prepare($sql, $parent_id, $parent_type), OBJECT);
		$root_tag = !$parent_type ? 'ul' : 'ol';
		$tag_class = 'tkgp_tasks_' . (!$parent_type ? 'list' : ($parent_id == 1 ? 'stage' : 'unit'));
		
		$html .= !empty($res) ? "<{$root_tag} class=\"{$tag_class}\">" : ''; 
		
		foreach ($res as $row) {
			$html .= '<li>';
			 
			switch ($row->child_type) {
				case 1: //Stage
					//Заглушка
					break;
				
				case 2: //Task
					//Заглушка
					break;
				
				default:
					break;
			}
			
			$html .= '</li>';
		}
		
		$html .= !empty($res) ? "</{$root_tag}>" : '';
		
		return $html;
	}
	
	/**
	 * Return array TK_GProject objects for children projects
	 * 
	 * @return array
	 */
	public function getChildProjects()
	{
		$out = array();
		
		$sql = "SELECT `child_id` FROM `{$this->wpdb->prefix}tkgp_tasks_links`
				WHERE `parent_id` = %d
				AND `parent_type` = 0
				AND `child_type` = 0";
		
		$res = $this->wpdb->get_results($this->wpdb->prepare($sql, $this->project_id), OBJECT);
		
		foreach ($res as $row) {
			$child = new TK_GProject($row->child_id);
			if($child->isValid()) {
				$out[] = $child;
			}
		}
		
		return $out;
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
						
			foreach ($caps as $cap) {
				$access = false;
				
				$method = 'userCan'.ucfirst($cap);
				if (method_exists($this, $method)) {
					$access = $this->$method($user_id);
				}
				
				$out_caps[$cap] = $access;
			}
		}
		
		return $out_caps;
	}
	
	/**
	 * Return TRUE when User by $user_id has Administrator, else FALSE.
	 * 
	 * @param int $user_id
	 * @return bool
	 */
	protected static function userIsAdmin($user_id) {
		$res = false;
		
		$user_data = get_user_by('ID',$user_id);
		
		if($user_data) {
			$roles_caps = $user_data->get_role_caps();
			$res = isset($roles_caps['administrator']) ? (boolean)$roles_caps['administrator'] : false;
		}
		
		return $res;
	}
	
	/**
	 * Return TK_GProject object when new project is created or null
	 * 
	 * @param array $data
	 * @return object | null 
	 */
	public static function createProject($data) {
		if(empty($data['target'])
			|| empty($data['post_title'])) {
			return null;
		}
		
		$user_id = get_current_user();
		$in = $data;
		$in['post_type'] = TK_GProject::slug;
		$in['post_status'] = 'publish';
		$in['comment_status'] = 'closed';
		
		$id = wp_insert_post($in);
		
		if($id && !is_object($id)) {
			update_post_meta($id, 'ptype', $data['type']);
			update_post_meta($id, 'ptarget', $data['target']);
			update_post_meta($id, 'manager', array($user_id));
			
			return new TK_GProject($id);
		}
		
		return null;
	}
	
	/**
	 * Return TRUE when user can read project else FALSE
	 * 
	 * @param int $user_id
	 */
	public function userCanRead($user_id) {
		$access = false;
		
		switch($this->project_visibility) {
			case 0: //Public
				$access = true;
				break;
			
			case 1: //Registered
				$user_data = get_user_by('ID',$user_id);
				$access = $user_data === false ? false : true;
				break;
			
			case 2: 
			case 3: //Members only and Privete
				$members = $this->getManagers();
				$access = array_search($user_id, $members) === false && !$this->userIsAdmin($user_id) ? false : true;
				break;
			
			default: 
				$access = false;
				break;
		}
		
		return $access;
	}
	
	/**
	 * Return TRUE when user can edit project else FALSE
	 * 
	 * @param int $user_id
	 */
	public function userCanEdit($user_id) {
		$access = false;
		$post = get_post($this->project_id);
		
		$access = (array_search($user_id, $this->getManagers()) !== false 
					|| $user_id === $post->post_author
					|| is_super_admin($user_id)) ? true : false;
		
		return $access;
	}
	
	/**
	 * Return TRUE when user can work project esle FALSE
	 * 
	 * @param int $user_id
	 */
	public function userCanWork($user_id) {
		$access = false;
		$can_edit = $this->userCanEdit($user_id);
		
		$access = $can_edit || array_search($user_id, $this->getMembers()) !== false ? true : false;
		
		return $access;
	}
	
	/**
	 * Return TRUE when user can vote project ELSE
	 * 
	 * @param int $user_id
	 */
	public function userCanVote($user_id) {
		$access = false;
		
		if($this->project_type === 0) { //Личный проект
			$access = false;
		} elseif ($this->project_type === 3) { //Общественный проект
			$access = (get_userdata($user_id) !== false) ? true : false;
		} else { // Рабочий и Групповой проекты
			$managers = $this->getManagers();
			$members = $this->getMembers();
			$all_members = !empty($members) ? array_merge($managers, $members) : $managers;
							
			$access = (array_search($user_id, $all_members) !== false) ? true : false;	
		}
		
		return $access;
	}
	
	/**
	 * Return TRUE when user can revote project
	 * 
	 * @param int $user_id
	 */
	public function userCanRevote($user_id) {
		$can_vote = $this->userCanVote($user_id);
		$vote = new TK_GVote($this->project_id);
					
		$access = ($can_vote && !$vote->userCanVote($user_id)) ? true : false;
		
		return $access;
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
        	'label' => _x('Target of the project', 'Project Settings Type', 'tkgp'),
        	'desc' => _x('Text description of the project target.', 'Project Settings Type', 'tkgp'),
        	'id' => 'ptarget',
        	'type' => 'editor',
        	'properties' => array( 
        						'editor_class'=>'requiredField', 
        						'textarea_rows'=>'6',
        						'media_buttons' => false,
        						'teeny' => true,
        						'editor_class' => 'required_field' //для пометки как требуемое поле
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

	static public function l10n($phrase_key, $default_phrase = '')
	{
		$out = '';
		switch ($phrase_key) {
			case 'target':
				$out = _x('Target of project','Project l10n', 'tkgp');
				break;
			
			case 'subprojects':
				$out = _x('Subprojects','Project l10n', 'tkgp');
				break;
			
			case 'subprojects_not_exists':
				$out = _x('Subprojects not exists','Project l10n', 'tkgp');
				break;
			
			case 'news':
				$out = _x('News','Project l10n', 'tkgp');
				break;
			
			case 'tasks':
				$out = _x('Tasks','Project l10n', 'tkgp');
				break;
				
			case 'description':
				$out = _x('Description','Project l10n', 'tkgp');
				break;
				
			case 'answers':
				$out = _x('Answers','Project l10n', 'tkgp');
				break;
			
			case 'team':
				$out = _x('Team','Project l10n', 'tkgp');
				break;
			
			case 'no_news':
				$out = _x('No News','Project l10n', 'tkgp');
				break;
				
			case 'no_tasks':
				$out = _x('No Tasks','Project l10n', 'tkgp');
				break;
			
			case 'no_answers':
				$out = _x('No Answers','Project l10n', 'tkgp');
				break;
			
			case 'no_information':
				$out = _x('No Information','Project l10n', 'tkgp');
				break;
			
			case 'Needed':
				$out = _x('Needed','Project l10n', 'tkgp');
				break;
			
			case 'Supported':
				$out = _x('Supported','Project l10n', 'tkgp');
				break;
				
			case 'needed':
				$out = _x('needed','Project l10n', 'tkgp');
				break;
			
			case 'supported':
				$out = _x('supported','Project l10n', 'tkgp');
				break;
			
			default:
				$out = $default_phrase;
				break;
		}
		
		return $out;
	}
};

?>