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

        $query = $this->wpdb->prepare("SELECT id, post_id, title, description, status, type, start_date, end_date, actual_end_date
        FROM {$this->wpdb->prefix}tkgp_tasks WHERE id = %d", $task_id);
        $result = $this->wpdb->get_results($query, ARRAY_A);
        if (!empty($result)) {
            $src = !empty($result[0]) ? $result[0] : $result;
            $this->opts['task_id'] = $src['id'];
            unset($src['id']);
            $this->opts = array_merge($this->opts, $src);
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

    public function setStatus($status)
    {
        if(!empty($status) && is_numeric($status)
            && intval($status) !== intval($this->status)) {

            $res = $this->wpdb->update("{$this->wpdb->prefix}tkgp_tasks",
                array('status' => $status),
                array('id' => $this->task_id),
                array('%d'),
                array('%d')
            );

            return boolval($res);
        }

        return false;
    }

    /**
     * Sets the specified fields to the specified values
     * @param array $args
     * @return bool
     */
    public function setFields($args)
    {
        $check_res = self::checkFields($args);

        if(!empty($check_res['fields'])) {
            unset($check_res['fields']['id']);
            $res = $this->wpdb->update("{$this->wpdb->prefix}tkgp_tasks",$check_res['fields'],
                array('id' => $this->task_id),
                $check_res['types'],
                array('%d'));

            return boolval($res);
        }

        return false;
    }

    /**
     * Checks the fields for the structure of the task data
     * @param $args
     * @return array
     */
    protected static function checkFields($args)
    {
       $fields = array();
       $types = array();

       if(is_array($args) && !empty($args)) {
           foreach ($args as $key => $value) {
               switch ($key) {
                   case 'title':
                       $types[] = '%s';
                       $fields[$key] = $value;
                       break;
                   case 'description':
                       $types[] = '%s';
                       $fields[$key] = $value;
                       break;
                   case 'type':
                       $types[] = '%s';
                       $fields[$key] = $value;
                       break;
                   case 'status':
                       //0 - черновик, 1 - опубликовано, 4 - удалено
                       $types[] = '%d';
                       $fields[$key] = $value;
                       break;
                   case 'start_date':
                       $types[] = '%s';
                       $fields[$key] = $value;
                       break;
                   case 'end_date':
                       $types[] = '%s';
                       $fields[$key] = $value;
                       break;
                   case 'actual_end_date':
                       $types[] = '%s';
                       $fields[$key] = $value;
                       break;

                   default:
                       continue;
               }
           }
       }

       return array('fields' => $fields, 'types' => $types);
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
            $check_res = self::checkFields($data);
            $fields = $check_res['fields'];
            $field_type = $check_res['types'];

            /*foreach ($data as $key => $value) {
                switch ($key) {
                    case 'title':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                        break;
                    case 'description':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                        break;
                    case 'type':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                        break;
                    case 'status':
                        //0 - черновик, 1 - опубликовано, 4 - удалено
                        $field_type[] = '%d';
                        $fields[$key] = $value;
                        break;
                    case 'start_date':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                        break;
                    case 'end_date':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                        break;
                    case 'actual_end_date':
                        $field_type[] = '%s';
                        $fields[$key] = $value;
                        break;

                    default:
                        continue;
                }
            }*/

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

                        $parent_task = new TK_GTask(intval($parent_id));

                        if ($parent_task->isValid()) {
                            $wpdb->insert("{$wpdb->prefix}tkgp_tasks_links",
                                array('parent_id' => $parent_id,
                                    'parent_type' => $parent_task->type,
                                    'child_id' => $task_id,
                                    'child_type' => $task->type),
                                array('%d', '%s', '%d', '%d')
                            );
                        }

                        return $task;
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
    public function have_children()
    {
        if($this->isValid()) {
            $query = $this->wpdb->prepare("SELECT 1 FROM {$this->wpdb->prefix}tkgp_tasks_links
WHERE parent_id = %d parent_type = %d", $this->task_id, $this->type);
            $res = $this->wpdb->get_var($query);
            return boolval($res);
        }

        return false;
    }

    /**
     * Returns array with ID of children tasks
     * @return array
     */
    public function get_children()
    {
        $sql = $this->wpdb->prepare("SELECT * FROM (SELECT t.id, t.type, l.parent_id, l.parent_type, t.internal_id 
FROM `{$this->wpdb->prefix}tkgp_tasks` t 
LEFT JOIN `{$this->wpdb->prefix}tkgp_tasks_links` l ON (l.child_id = t.id AND l.child_type = t.type) 
WHERE t.post_id = %d AND t.id <> %d
UNION
SELECT tt.id, tt.type, ll.parent_id, ll.parent_type, tt.internal_id 
FROM `{$this->wpdb->prefix}tkgp_tasks` tt 
INNER JOIN `{$this->wpdb->prefix}tkgp_tasks_links` ll ON (ll.child_id = tt.id AND ll.child_type = tt.type) 
WHERE tt.post_id = %d AND tt.id <> %d) o
ORDER BY o.`internal_id`;",
            $this->post_id,
            $this->task_id,
            $this->post_id,
            $this->task_id);

        $res = $this->wpdb->get_results($sql, ARRAY_A);
        return (!empty($res) ? $this->buildTreeTasks($res) : array());
    }

    protected function buildTreeTasks(&$data, $max_level = 7, $current_level = 1, $parent_id = 0)
    {
        $parent_id = !$parent_id ? $this->task_id : $parent_id;
        $out = array();

        if (is_array($data)) {
            foreach ($data as $key => $task) {
                if (intval($task['parent_id']) === $parent_id) {
                    $out['id_' . $task['id']] = $task;
                    unset($data[$key]);

                    if ($current_level < $max_level) {
                        $childs = $this->buildTreeTasks($data,
                            $max_level,
                            $current_level + 1,
                            intval($task['id']));

                        if (!empty($childs)) {
                            $out['id_' . $task['id']]['childs'] = $childs;
                        }
                    }
                }
            }

        }

        return $out;
    }
}

;
?>