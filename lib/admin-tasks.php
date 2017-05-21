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
                    <h3><?php echo $task->title; ?></h3>
                </div>
                <div>
                    <?php echo $task->description; ?>
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
                                    <h4><?php echo $child->title; ?></h4>
                                </div>
                                <div>
                                    <?php echo $child->description; ?>
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