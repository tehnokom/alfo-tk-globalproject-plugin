<?php
$page = new TK_GPage();

if (!empty($_POST['sort_by'])) {
    $filters = array('sort_by' => explode(',', $_POST['sort_by']));
    if (!empty($_POST['order_by'])) {
        $filters['order_by'] = explode(',', $_POST['order_by']);
    }

    $page->quiery($filters);
} else {
    $page->quiery(array('sort_by' => array('priority'), 'order_by' => array('desc')));
}

$page_num = empty($_POST['page']) ? 1 : intval($_POST['page']);
$page->createPage($page_num);

while ($page->nextProject()) {
    $project = $page->project();
    ?>
    <div class="tk-page-unit">
        <div class="tk-page-title">
            <div>
                <div><h2>
                        <a href="<?php echo $project->permalink; ?>"><?php echo apply_filters("the_title", $project->title); ?></a>
                    </h2></div>
            </div>
            <div>#<?php echo $project->internal_id; ?></div>
        </div>
        <div class="tk-page-target">
            <div><?php echo apply_filters("the_content", $project->target); ?></div>
        </div>
        <?php
        $subprojects = $project->getChildProjects();

        if (count($subprojects)) {
            ?>
            <div class="tk-page-sub">
                <div>
                    <?php echo TK_GProject::l10n('subprojects'); ?>:
                    <?php
                    foreach ($subprojects as $subproject) {
                        ?>
                        <span><a href="<?php echo $subproject->permalink; ?>"><?php echo apply_filters("the_title", $subproject->title); ?></a></span>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

if ($page->hasMore()) {
    ?>
    <div id="tk-page-more" class="tk-button"><?php echo _x('More', 'Default style','tk-style'); ?></div>
    <?php
}
?>