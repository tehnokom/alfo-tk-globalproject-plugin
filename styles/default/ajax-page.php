<?php

$page = new TK_GPage();
$page->quiery(array('sort_by' => array('title'), 'order_by' => array('ASC')));
$page->createPage();
?>
<div>
    <div class="tk-projects-list">
        <?php
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
    </div>
    <div class="tk-panel">
        <div class="tk-filter-box">
            <div class="tk-title">
                <h4><?php echo _x('Filters', 'Default style', 'tk-style'); ?></h4>
            </div>
            <div id="tk-filter-order">
                <label for="tk-filter-order"><?php echo _x('Sorting', 'Default style', 'tk-style'); ?></label>
                <select name="sort_by">
                    <option value="priority"><?php echo _x('by proirity', 'Default style', 'tk-style'); ?></option>
                    <option value="popularity"><?php echo _x('by popularity', 'Default style', 'tk-style'); ?></option>
                    <option value="date"><?php echo _x('by date', 'Default style', 'tk-style'); ?></option>
                    <option value="title"><?php echo _x('by title', 'Default style', 'tk-style'); ?></option>
                </select>
                <select name="order_by">
                    <option value="desc"><?php echo _x('DESC', 'Default style', 'tk-style'); ?></option>
                    <option value="asc"><?php echo _x('ASC', 'Default style', 'tk-style'); ?></option>
                </select>
            </div>
        </div>
    </div>
</div>