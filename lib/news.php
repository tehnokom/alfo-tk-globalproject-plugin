<?php

/**
 * Class TK_GNews
 */
 class TK_GNews
 {
 	/**
     * WordPress Database Access Abstraction Object
     * @var
     */
    protected $wpdb;
	
	/**
	 * Internal options array
	 * @var array
	 */
	protected $opts;
	
	/**
	 * @var WP_Query object
	 */
	 protected $wp_query;
	
 	public function __construct($project_id)
	{
		global $wpdb;
		
		$this->wpdb = $wpdb;
		$post = get_post($project_id);
		
		if(is_object($post)) {
			$cat_id = $this->wpdb->get_var($this->wpdb->prepare("SELECT `news_id` FROM {$this->wpdb->prefix}tkgp_projects
																WHERE `post_id` = %d;", $post->ID));

			if(!empty($cat_id)) {
				file_put_contents(__FILE__.".log", $cat_id."\r\n", FILE_APPEND);
				$this->opts['cat_id'] = intval($cat_id);
				$this->opts['query']['cat'] = intval($cat_id);
				$this->opts['query']['post_type'] = 'post';
				$this->opts['query']['posts_per_page'] = 20;
				$this->opts['query']['orderby'] = 'date';
				$this->opts['query']['order'] = 'DESC';
				$this->wp_query = new WP_Query();
			}
		}
	}
	
	public function __get($name) {
		if(isset($this->opts[$name])) {
			return $this->opts[$name];
		}
		
		return null;
	}
	
	public function __isset($name) {
		return isset($this->opts[$name]);
	}
	
	/**
	 * @return bool
	 */
	public function isValid() {
		return isset($this->opts['cat_id']);
	}
	
	public function createPage($page_num = 1) {
		if(isset($this->wp_query)) {
			$this->wp_query->query($this->opts['query']);
		}
	}
	
	public function have_posts() {
		$res = isset($this->wp_query) && isset($this->wp_query->query_vars['cat']) ? $this->wp_query->have_posts() : false;
		return $res;
	}
	
	public function the_post() {
		if(isset($this->wp_query)) {
			$this->wp_query->the_post();
		}
	}
	
	public function next_post() {
		if(isset($this->wp_query)) {
			return $this->wp_query->next_post();
		}
		
		return null;
	}
	
	public function rewind_posts() {
		if(isset($this->wp_query)) {
			$this->wp_query->rewind_posts();
		}
	}
	
	public function post() {
		if(isset($this->wp_query)) {
			return $this->wp_query->post;
		}
	}
	
	public function seek($per_page, $offset) {
		$this->opts['query']['posts_per_page'] = $per_page;
		$this->opts['query']['offset'] = $offset;
	}
	
 };
?>