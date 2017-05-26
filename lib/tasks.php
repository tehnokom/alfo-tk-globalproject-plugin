<?php

class TK_GTasks
{
    protected $wpdb;

    protected $project;

    protected $max_tasks;

    protected $tasks;

    protected $cur_task;

    protected $cur_child;

    protected $status_list;

    public function __construct($project_id)
    {
        $this->project = new TK_GProject($project_id);

        if($this->project->isValid() && $this->project->userCanRead(get_current_user_id())) {
            global $wpdb;

            $this->wpdb = $wpdb;
            $this->wpdb->enable_nulls = true;
        } else {
            $this->project = null;
        }
    }

    public function isValid() {
        return !($this->project === null);
    }

    public function setStatuses($status = array()) {
        if(empty($status)) {
            $this->status_list = "BETWEEN 0 AND 9";
        } else if(is_array($status)) {
            $this->status_list = "IN (";
            foreach ($status as $val) {
                if(is_numeric($val)) {
                    $this->status_list .= "$val,";
                }
            }

            $this->status_list = trim($this->status_list,",");
            $this->status_list .= ")";
        } else if(is_numeric($status)) {
            $this->status_list = "= $status";
        }
    }

    public function createPage($page_num = 1) {
        if($this->isValid()) {
            $this->cur_task = 0;

            if(empty($this->status_list)) {
                $this->setStatuses();
            }

            $sql = $this->wpdb->prepare("SELECT * FROM (SELECT t.id, t.type, l.parent_id, l.parent_type, t.internal_id 
FROM `{$this->wpdb->prefix}tkgp_tasks` t 
LEFT JOIN `{$this->wpdb->prefix}tkgp_tasks_links` l ON (l.child_id = t.id AND l.child_type = t.type) 
WHERE t.post_id = %d
AND t.status {$this->status_list}
UNION
SELECT tt.id, tt.type, ll.parent_id, ll.parent_type, tt.internal_id 
FROM `{$this->wpdb->prefix}tkgp_tasks` tt 
INNER JOIN `{$this->wpdb->prefix}tkgp_tasks_links` ll ON (ll.child_id = tt.id AND ll.child_type = tt.type) 
WHERE tt.post_id = %d
AND tt.status {$this->status_list}) o
ORDER BY o.`internal_id` ASC;",
                intval($this->project->project_id),
                intval($this->project->project_id));

            $res = $this->wpdb->get_results($sql, ARRAY_A);

            if(!empty($res)) {
                $this->tasks = $this->buildTasksTree($res);
            }

            $this->cur_task = null;
            $this->cur_child = null;
        }
    }

    public function have_tasks() {
        return (is_array($this->tasks) && count($this->tasks));
    }

    public function next_task() {
        if(is_array($this->tasks)) {
            if(!isset($this->cur_task)) {
                $this->cur_task = 0;
            } else {
                $this->cur_task++;
            }

            if($this->cur_task < count($this->tasks)) {
                $this->cur_child = null;
                return true;
            }
        }

        $this->cur_task = null;
        $this->cur_child = null;
        return false;
    }

    public function get_task() {
        if($this->isValid() && isset($this->cur_task)) {
            $task = new TK_GTask(intval($this->tasks[$this->cur_task]['id']));
            if($task->isValid()) {
                return $task;
            }
        }

        return null;
    }

    protected function buildTasksTree(&$tasks, $max_level = 2, $cur_level = 1, $parent_id = 0) {
        $out = array();

        if($cur_level === 1) {
            foreach ($tasks as $key => $task) {
                if(empty($task['parent_id'])) {
                    $out[] = $task;
                    unset($tasks[$key]);

                    if($cur_level < $max_level) {
                        $childs = $this->buildTasksTree($tasks, $max_level, $cur_level + 1, intval($task['id']));

                        if(!empty($childs)) {
                            $out[count($out) - 1]['childs'] = $childs;
                        }
                    }
                }
            }
        } else {
            foreach ($tasks as $key => $task) {
                if(intval($task['parent_id']) === $parent_id) {
                    $out[] = $task;
                    unset($tasks[$key]);

                    if($cur_level < $max_level) {
                        $childs = $this->buildTasksTree($tasks, $max_level,$cur_level + 1, intval($task['id']));

                        if(!empty($childs)) {
                            $out[count($out) - 1]['childs'] = $childs;
                        }
                    }
                }
            }
        }

        return $out;
    }

    public function have_children() {
        if($this->isValid() && isset($this->cur_task) && !empty($this->tasks[$this->cur_task])) {
            return true;
        }

        return false;
    }

    public function next_child() {
        if($this->isValid() && $this->have_children()) {
            if(!isset($this->cur_child)) {
                $this->cur_child = 0;
            } else {
                $this->cur_child++;
            }

            if($this->cur_child < count($this->tasks[$this->cur_task]['childs'])) {
                return true;
            }
        }

        $this->cur_child = 0;
        return false;
    }

    public function get_child() {
        if($this->isValid() && isset($this->cur_child)) {
            $task = new TK_GTask($this->tasks[$this->cur_task]['childs'][$this->cur_child]);

            if($task->isValid()) {
                return $task;
            }
        }

        return null;
    }

}

;
?>