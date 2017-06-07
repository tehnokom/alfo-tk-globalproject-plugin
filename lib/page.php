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

    /**
     * Current project index
     * @var int
     */
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
     * @param $project_type integer Page type
     */
    public function createPage($page_num = 1, $project_type = 3)
    {
        unset($this->projects);
        $this->cur_project_idx = 0;
        $offset = $page_num * $this->max_projects - $this->max_projects;
        $user_id = get_current_user_id();

        $sql = $this->compileSqlQuery();

        while (count($this->projects) < $this->max_projects) {
            $res = $this->wpdb->get_results($this->wpdb->prepare($sql, $project_type,
                $this->max_projects + 1, $offset), OBJECT);

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
     * @return bool
     */
    public function hasMore()
    {
        return (count($this->projects) > $this->max_projects);
    }

    /**
     * Allows you to specify query and field parameters
     */
    public function query($args)
    {
        if (is_array($args)) {
            foreach ($args as $key => $val) {
                if (is_array($val)) {
                    $this->parseQuery($key, $val);
                }
            }
        }
    }

    private function parseQuery($type, $args)
    {
        if ($type === 'sort_by') {
            foreach ($args as $val) {
                switch ($val) {
                    case 'priority':
                        unset($this->sql_src['order_by']);
                        $this->sql_src['tables'][] = "tkgp_projects pr";
                        $this->sql_src['tables_links'][] = "pr.`post_id` = p.`id`";
                        $this->sql_src['order_by'] = array("(100 - pr.`priority`) DESC", "p.`post_date` DESC");
                        break;

                    case 'popularity':
                        unset($this->sql_src['order_by']);
                        $this->sql_src['order_by'][] = "get_project_popularity(p.`id`,0) DESC";
                        break;

                    case 'date':
                        unset($this->sql_src['order_by']);
                        $this->sql_src['order_by'][] = "p.`post_date` DESC";
                        break;

                    case 'title':
                        unset($this->sql_src['order_by']);
                        $lang = function_exists(qtranxf_getLanguage) ? qtranxf_getLanguage() : '';
                        $this->sql_src['order_by'][] = !empty($lang) ? "get_wp_localize(p.`post_title`,'{$lang}') ASC"
                            : 'p.`post_title` ASC';
                        break;

                    default:
                        continue;
                        break;
                }
            }
        } else if ($type === 'order_by' && !empty($this->sql_src['order_by'])) {
            foreach ($args as $key => $val) {
                if (!empty($this->sql_src['order_by'][$key])) {
                    $this->sql_src['order_by'][$key] = preg_replace('/(ASC)|(DESC)/i',
                        $val, $this->sql_src['order_by'][$key]);
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
     * Returns TRUE when next Project is exists else false
     * @return bool
     */
    public function nextProject()
    {
        $max_projects = count($this->projects);
        $max_projects = $max_projects <= $this->max_projects ? $max_projects + 1 : $max_projects;
        if (++$this->cur_project_idx < $max_projects) {
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