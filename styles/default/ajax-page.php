<?php
$page = new TK_GPage();
$page->createPage();

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
?>