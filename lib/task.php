<?php

class TK_GTask
{
    protected $wpdb;

    protected $opts;

    public function __construct($task_id)
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->wpdb->enable_nulls = true;

        $query = $this->wpdb->prepare("SELECT post_id, status, type, start_date, end_date, actual_end_date
        FROM {$this->wpdb->prefix}tkgp_tasks WHERE id = %d", $task_id);
        $result = $this->wpdb->get_results($query, ARRAY_A);

        if (!empty($result)) {
            $this->opts['task_id'] = $task_id;
            $this->opts = array_merge($this->opts, $result);
        }
    }

    /**
     * Magic method. It's Ma-a-a-gic :)
     * @param string $name
     * @return mixed | null
     */
    public function __get($name)
    {
        if (isset($this->opts[$name])) {
            return $this->opts[$name];
        }

        return null;
    }

    /**
     * Magic method. It's Ma-a-a-gic :)
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->opts[$name]);
    }

    /**
     * Returns TRUE when Task is exists, or FALSE
     * @return bool
     */
    public function isValid()
    {
        return isset($this->opts['task_id']);
    }

    /**
     * Creates a new Task for a Project
     * @param int $project_id
     * @param array $data
     * @param null|int $parent_id
     * @return null|TK_GTask
     */
    public static function createTask($project_id, $data, $parent_id = null)
    {
        $project = new TK_GProject($project_id);

        if ($project->isValid() && is_array($data)) {
            $fields = array();
            $field_type = array();

            foreach ($data as $key => $value) {
                switch ($key) {
                    case 'title':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                    case 'description':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                    case 'type':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                    case 'status':
                        $field_type[] = '%d';
                        $fields[$key] = $value;
                    case 'start_date':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                    case 'end_date':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                    case 'actual_end_date':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                        break;

                    default:
                        continue;
                }

                if (!empty($fields['title'])) {
                    global $wpdb;
                    $wpdb->enable_nulls = true;

                    $fields['post_id'] = $project_id;
                    $field_type[] = '%d';
                    $res = $wpdb->insert("{$wpdb->prefix}tkgp_tasks", $fields, $field_type);

                    if ($res) {
                        $query = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}tkgp_tasks 
WHERE post_id = %d AND title = %s", $project_id, $fields['title']);

                        $task_id = $wpdb->get_var($query);
                        $task = new TK_GTask($task_id);
                        if ($task->isValid()) {

                            $parent_task = new TK_GTask($parent_id);
                            if($parent_task->isValid()) {
                                $wpdb->insert("{$wpdb->prefix}tkgp_tasks_links",
                                    array('parent_id' => $parent_id,
                                        'parent_type' => $parent_task->type,
                                        'child_id' => $task_id,
                                        'child_type' => $task->type),
                                    array('%d','%s','%d','%d'));
                            }

                            return $task;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Returns TRUE when this Task has children tasks
     * @return bool
     */
    public function hasChildren()
    {
        if($this->isValid()) {
            $query = $this->wpdb->prepare("SELECT 1 FROM {$this->wpdb->prefix}tkgp_tasks_links
WHERE parent_id = %d parent_type = %d", $this->task_id, $this->type);
            $res = $this->wpdb->get_var($query);
            return boolval($res);
        }

        return false;
    }

    public function getChildTasks()
    {

    }
}

;
?>