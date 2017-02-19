<?php

/**
 * Class TK_GPage
 */
class TK_GPage {
	
	/**
     * WordPress Database Access Abstraction Object
     * @var object
     */
	protected $wpdb;
	
	/**
	 * Current project index
	 * @var integer
	 */
	protected $cur_project = 0;
	
	/**
	 * Current page index
	 * @var integer
	 */
	protected $cur_page = 0;
	
	/**
	 * Max count projects on page
	 * @var integer
	 */
	protected $max_projects = 0;
	
	/**
	 * Max count links on page
	 * @var integer
	 */
	protected $max_links = 0;
	
	/**
	 * A numerically indexed array of project row objects
	 * @var array
	 */
	protected $projects;
	
	public function __construnct() {
		global $wpdb;
		
		$this->wpdb = $wpdb;
		$this->max_projects = 15;
		$this->max_links = 10;
	}
	
	/**
	 * Creates a list of projects for the page
	 * @param $page_num integer
	 * @param $ptype integer
	 */
	public function createPage($page_num = 1, $ptype = 3) {
		$offsrt = $page_num * $this->max_projects;
		
		$sql = "SELECT p.`ID`,
					p.`post_author`,
					p.`post_date`,
					p.`post_title`,
					p.`guid`,
					pm1.meta_value as ptype,
					pm2.meta_value as ptarget
			FROM `{$this->wpdb->prefix}wp_posts` p,
				 `{$this->wpdb->prefix}wp_postmeta` pm1,
				 `{$this->wpdb->prefix}wp_postmeta` pm2
			WHERE pm1.post_id = p.id
				AND pm2.post_id = p.id
				AND p.`post_type` = 'tk_project'
				AND pm1.meta_key = 'ptype'
				AND pm1.meta_value = %d
				AND pm2.meta_key = 'ptarget'
			ORDER BY p.`post_date` DESC
			LIMIT %d OFFSET %d";
			
		$this->projects = $this->wpdb->get_results($this->wpdb->prepare($sql, 
													  $ptype, 
													  $this->cur_project, 
													  $offset));
		
		/*foreach ($this->projects as $row) {
			file_put_contents(__FILE__.".log", serialize($row)."\r\n", FILE_APPEND);
		}
		file_put_contents(__FILE__.".log", "=====================================\r\n", FILE_APPEND);*/
	}

	    	
};
?>