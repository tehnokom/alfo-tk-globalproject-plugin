<?php

/**
 * Class TK_GPage
 */
class TK_GPage
{

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
     * Last offset. This is needed for create next pages.
     * @var integer
     */
    protected $last_offset = 0;

    /**
     * A numerically indexed array of project row objects
     * @var array
     */
    protected $projects;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->wpdb->enable_nulls = true;
        $this->max_projects = 15;
        $this->max_links = 10;
    }

    /**
     * Creates a list of projects for the page
     *
     * @param $page_num integer Page Number
     * @param $ptype integer Page type
     */
    public function createPage($page_num = 1, $ptype = 3)
    {
        unset($this->projects);
        $offset = $this->max_projects - $page_num * $this->max_projects;
        $slug = TK_GProject::slug;
        $user_id = get_current_user_id();
        $prefix = $this->wpdb->prefix;

        $sql = "SELECT p.`id`
			FROM `{$prefix}posts` p,
				 `{$prefix}postmeta` pm1
			WHERE pm1.post_id = p.id
				AND p.`post_type` = '{$slug}'
				AND pm1.meta_key = 'ptype'
				AND pm1.meta_value = %d
				AND not exists(SELECT 1 FROM `{$prefix}tkgp_tasks_links`
					WHERE `child_type` = 0
					AND `child_id` = p.`id`)
			ORDER BY p.`post_date` DESC
			LIMIT %d OFFSET %d";

        while (count($this->projects) < $this->max_projects) {
            $res = $this->wpdb->get_results($this->wpdb->prepare($sql, $ptype, $this->max_projects, $offset), OBJECT);

            if (empty($res)) {
                $this->last_offset;
                break;
            }

            foreach ($res as $row) {
                $cur = new TK_GProject($row->id);
                ++$offset;

                if ($cur->userCanRead($user_id)) {
                    $this->projects[] = $cur;
                }
            }
        }
    }

    /**
     * Returns html code project page
     * @return string
     */
    public function getPageHtml()
    {
        $html = '';
        $l10n = TK_GProject::l10n('target');

        foreach ($this->projects as $project) {
            $pid = TK_GProject::userIsAdmin(get_current_user_id()) ? " #{$project->project_id}" : '';
            $title = apply_filters("the_title", $project->title);
            $target = apply_filters("the_content", $project->target);
            $html .= "<div style='display: block; width:98%; border: 1px solid rgba(204,204,204,0.9); margin: 0 auto 5px auto; padding: 5px; border-radius: 5px;'>
			<h3><a href='{$project->permalink}'>{$title}{$pid}</a></h3>
			<div><h5>{$l10n}</h5></div>
			<div>{$target}</div>";

            /*if(TK_GVote::exists($project->project_id)) {
                $user_id = get_current_user_id();
                $vote = new TK_GVote($project->project_id);
                $caps = $project->userCan($user_id);
                $html .= $vote->getResultVoteHtml($caps['vote'], false, !$caps['revote']);
            }*/

            $html .= "</div>";
        }

        return $html;
    }

    /**
     * Returns array of TK_GProject objects on current page
     *
     * @return array
     */
    public function getProjectsOnPage()
    {
        return $this->projects;
    }
}

;
?>