<?php
global $post;
$tasks = new TK_GTasks($post->ID);

if($tasks->isValid() && $post->post_status !== "auto-draft") {
    $tasks->createPage();
    ?>
    <div style="max-height: 600px; overflow-y: auto;">
    <ul class="tkgp_tasks">
        <?php
        while ($tasks->next_task()) {
            $task = $tasks->get_task();
            ?>
            <li data-tkgp-task-type="<?php echo $task->type; ?>">
                <input type="hidden" name="tkgp_task_id" value="<?php echo $task->task_id; ?>" />
                <div style="width: 100%;">
                    <input class="tkgp_task_mlang" type="text" name="tkgp_task_title_<?php echo $task->task_id; ?>"
                        value="<?php echo $task->title; ?>">
                </div>
                <div>
                    <textarea class="tkgp_task_mlang" name="tkgp_task_desc_<?php echo $task->task_id; ?>">
                        <?php echo $task->description; ?>
                    </textarea>
                </div>
                <ul class="tkgp_tasks">
                    <?php
                    if($tasks->have_children()) {
                        while ($tasks->next_child()) {
                            $child = $tasks->get_child();
                            ?>
                            <li data-tkgp-task-type="<?php echo $child->type; ?>">
                                <input type="hidden" name="tkgp_task_id" value="<?php echo $child->task_id; ?>" />
                                <input type="hidden" name="tkgp_task_parent" value="<?php echo $task->task_id; ?>" />
                                <div style="width: 100%;">
                                    <input class="tkgp_task_mlang" type="text"
                                           name="tkgp_task_title_<?php echo $child->task_id; ?>"
                                           value="<?php echo $child->title; ?>" />
                                </div>
                                <div>
                                    <textarea class="tkgp_task_mlang" name="tkgp_task_desc_<?php echo $child->task_id; ?>">
                                        <?php echo $child->description; ?>
                                    </textarea>
                                </div>
                                <ul class="tkgp_tasks">
                                </ul>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </li>
            <?php
        }
        ?>
    </ul>
    </div>
<?php
} else {
    echo _x('Functionality will be available after saving the current project.', 'Project Settings', 'tkgp');
}
?>