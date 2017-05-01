<?php
$tasks = new TK_GTasks(intval($_POST['post_id']));
if ($tasks->isValid()) {
    $tasks->createPage();

    if ($tasks->have_tasks()) {
        ?>
        <ul class="tk-tasks-list">
            <?php
            while ($tasks->next_task()) {
                $task = $tasks->get_task();
                ?>
                <li class="tk-task-type-<?php echo $task->type; ?>">
                    <h3><?php echo $task->title; ?></h3>
                    <div class="tk-task-desc"><?php echo $task->description; ?></div>
                    <ul>
                    <?php
                    if ($tasks->have_children()) {
                        ?>
                            <?php
                            while ($tasks->next_child()) {
                                $child_task = $tasks->get_child();
                                ?>
                                <li class="tk-task-type-<?php echo $child_task->type; ?>">
                                    <h4><?php echo $child_task->title; ?></h4>
                                    <div class="tk-task-desc">
                                        <?php echo $child_task->description;?>
                                    </div>
                                    <ul>
                                    </ul>
                                </li>
                                <?php
                            }
                            ?>
                        <?php
                    }
                    ?>
                    </ul>
                </li>
                <?php
            }
            ?>
        </ul>
        <?php
    } else {
        TK_GProject::the_l10n('no_tasks');
    }
}
?>
