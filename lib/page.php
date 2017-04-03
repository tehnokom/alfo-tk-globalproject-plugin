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
    protected $cur_project = null;

    protected $cur_project_idx = 0;
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
     * Default SQL
     * @var string
     */
    private $sql_src = '';
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

        $this->sql_src = array(
            'fields' => array('p.`id`'),
            'tables' => array('posts p', 'postmeta pm1'),
            'tables_links' => array('pm1.`post_id` = p.`id`'),
            'where' => array("AND p.`post_type` =  '" . TK_GProject::slug . "'",
                "AND pm1.meta_key = 'ptype'",
                "AND pm1.meta_value = %d",
                "AND not exists(SELECT 1 FROM `{$this->wpdb->prefix}tkgp_tasks_links`
					WHERE `child_type` = 0
					AND `child_id` = p.`id`)"
            ),
            'order_by' => array('p.`post_date` DESC'),
            'limit' => '%d',
            'offset' => '%d'
        );
    }

    /**
     * Creates a list of projects for the page
     *
     * @param $page_num integer Page Number
     * @param $ptype integer Page type
     */
    public function createPage($page_num = 1, $project_type = 3)
    {
        unset($this->projects);
        $this->cur_project_idx = 0;
        $offset = $this->max_projects - $page_num * $this->max_projects;
        $user_id = get_current_user_id();

        $sql = $this->compileSqlQuery();

        while (count($this->projects) < $this->max_projects) {
            $res = $this->wpdb->get_results($this->wpdb->prepare($sql, $project_type,
                $this->max_projects, $offset), OBJECT);

            if (empty($res)) {
                $this->last_offset;
                break;
            }

            foreach ($res as $row) {
                $cur = new TK_GProject($row->id);
                ++$offset;

                if ($cur->userCanRead($user_id)) {
                    $this->projects[] = $cur->project_id;
                }
            }
        }
    }

    /**
     * Compiles the query based on the milestones of the specified parameters and returns its code
     * @return string
     */
    protected function compileSqlQuery()
    {
        $slug = TK_GProject::slug;
        $prefix = $this->wpdb->prefix;

        $sql = 'SELECT';

        foreach ($this->sql_src['fields'] as $field) {
            $sql .= " {$field},";
        }

        $sql = trim($sql, ",");
        $sql .= "\r\n   FROM";

        foreach ($this->sql_src['tables'] as $table) {
            $sql .= " {$this->wpdb->prefix}{$table},";
        }

        $sql = trim($sql, ",");
        $sql .= " \r\n   WHERE";

        foreach ($this->sql_src['tables_links'] as $link) {
            $sql .= substr($sql, -5) === 'WHERE' ? " {$link}" : "\r\n       AND {$link}";
        }

        foreach ($this->sql_src['where'] as $where) {
            $sql .= substr($sql, -5) === 'WHERE' ? " {$where}" : "\r\n      {$where}";
        }

        if (!empty($this->sql_src['order_by'])) {
            $sql .= "\r\nORDER BY";
            foreach ($this->sql_src['order_by'] as $order) {
                $sql .= " {$order},";
            }

            $sql = trim($sql, ",");
        }

        if (!empty($this->sql_src['limit'])) {
            $sql .= "\r\nLIMIT {$this->sql_src['limit']}";
            $sql .= !empty($this->sql_src['offset']) ? " OFFSET {$this->sql_src['offset']}" : '';
        }

        $sql .= ';';

        return $sql;
    }

    /**
     * Parses $_POST array and generates parameters for constructing the page in accordance with them
     * @return array
     */
    public function parseParamsFromPost()
    {
        return $this->parseParams($_POST);
    }

    /**
     * Parses $_GET  array and generates parameters for constructing the page in accordance with them
     * @return array
     */
    public function parseParamsFromGet()
    {
        return $this->parseParams($_GET);
    }

    /**
     * Parses the string or array and generates parameters for constructing the page in accordance with them
     * @param $args
     * @return array
     */
    public function parseParams($args)
    {
        $out = array();
        $correct = array();

        if (!is_array($args) && !empty($args)) {
            $out = explode(',', $args);
            $out = is_array($out) ? $out : array();
        } else {
            $out = $args;
        }

        foreach ($out as $key => $val) {
            switch ($key) {
                case 'sort_by':
                    $correct = array('priority', 'popularity', 'date', 'title');
                    break;

                case 'order_by':
                    $correct = array('asc', 'desc');
                    break;

                default:
                    unset($out[$key]);
                    continue;
                    break;
            }

            if (!self::checkParam($val, $correct)) {
                unset($out[$key]);
            }
        }

        return $out;
    }

    /**
     * Returns TRUE when $param correct, else returns FALSE
     * @param $param
     * @param $variants
     * @return mixed
     */
    private static function checkParam($param, $variants)
    {
        return array_search(strtolower($param), $variants);
    }

    /**
     * Returns TRUE when next Project is exists else false
     * @return bool
     */
    public function nextProject()
    {
        if (++$this->cur_project_idx <= count($this->projects)) {
            $project = new TK_GProject($this->projects[$this->cur_project_idx - 1]);
            if ($project->isValid()) {
                $this->cur_project = $project;
                return true;
            }
        }

        $this->cur_project_idx = 0;
        return false;
    }

    /**
     * Returns TK_Gproject object when project exists or NULL
     * @return object | null
     */
    public function project()
    {
        return is_object($this->cur_project) ? $this->cur_project : null;
    }
}

;
?>