<?php
$post = get_post($_POST['post_id']);
$tasks = new TK_GTasks($post->ID);

if($tasks->isValid() && $post->post_status !== "auto-draft") {
    $tasks->setStatuses(array(0,1,2,3));
    $tasks->createPage();
    ?>
    <div style="max-height: 600px; overflow-y: auto;">
    <ul class="tkgp_tasks">
        <?php
        while ($tasks->next_task()) {
            $task = $tasks->get_task();
            ?>
            <li data-tkgp-task-type="<?php echo $task->type; ?>">
                <input type="hidden" name="tkgp_task_id_<?php echo $task->task_id; ?>"
                       value="<?php echo $task->task_id; ?>" />
                <div style="width: 70%;display:inline-block;">
                    <h3><?php echo apply_filters('the_title',$task->title); ?></h3>
                </div>
                <div style="width:26.5%;display:inline-block;text-align: right;">
                    <div class="tkgp_circle_button tkgp_task_edit_btn">E</div>
                </div>
                <div>
                    <?php echo apply_filters('the_content',$task->description); ?>
                </div>
                <ul class="tkgp_tasks">
                    <?php
                    if($tasks->have_children()) {
                        while ($tasks->next_child()) {
                            $child = $tasks->get_child();
                            ?>
                            <li data-tkgp-task-type="<?php echo $child->type; ?>">
                                <input type="hidden" name="tkgp_task_id_<?php echo $child->task_id; ?>"
                                       value="<?php echo $child->task_id; ?>" />
                                <input type="hidden" name="tkgp_task_parent_<?php echo $child->task_id; ?>"
                                       value="<?php echo $task->task_id; ?>" />
                                <div style="width: 70%;display:inline-block;">
                                    <h4><?php echo apply_filters('the_title',$child->title); ?></h4>
                                </div>
                                <div style="width:26.5%;display:inline-block;text-align: right;">
                                    <div class="tkgp_circle_button tkgp_task_edit_btn">E</div>
                                </div>
                                <div>
                                    <?php echo apply_filters('the_content',$child->description); ?>
                                </div>
                                <ul class="tkgp_tasks">
                                    <li class="tkgp_tasks_tool">
                                        <div>
                                         <div class="tkgp_circle_button tkgp_task_del_btn">-</div>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <?php
                        }
                    }
                    ?>
                    <li class="tkgp_tasks_tool">
                        <div>
                            <div class="tkgp_circle_button tkgp_task_add_btn">+</div>
                            <div class="tkgp_circle_button tkgp_task_del_btn">-</div>
                        </div>
                    </li>
                </ul>
            </li>
            <?php
        }
        ?>
        <li class="tkgp_tasks_tool">
            <div>
                <div class="tkgp_circle_button tkgp_task_add_btn">+</div>
            </div>
        </li>
    </ul>
    </div>
<?php
} else {
    echo _x('Functionality will be available after saving the current project.', 'Project Settings', 'tkgp');
}
?>