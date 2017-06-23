<!--Start Central-->
<div id="projektoj-centro">
    <!--Start Parent Project-->
    <?php
    $parent_project = $project->getParentProject();
    $subprojects = $project->getChildProjects();

    if (!empty($parent_project) || !empty($subprojects)) {
        ?>
        <div class="tk-block">
            <?php
            if (!empty($parent_project)) {
                ?>
                <div class="tk-parent-project">
                    <h2><?php TK_GProject::the_l10n('parent_project'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $parent_project->permalink; ?>"><?php echo $parent_project->title; ?></a>
                        </li>
                    </ul>
                </div>
                <?php
            }
            ?>
            <!--End Parent Project-->
            <!--Start Subprojects-->
            <?php
            if (!empty($subprojects)) {
                ?>
                <div class="tk-subprojects">
                    <h2><?php TK_GProject::the_l10n('subprojects'); ?></h2>
                    <ul>
                        <?php
                        foreach ($subprojects as $cur) {
                            $li = "<li><a href=\"{$cur->permalink}\">{$cur->title}</a></li>";
                            echo $li;
                        }
                        ?>
                    </ul>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    } ?>
    <!--End Subprojects-->
    <!--News Start-->
    <div class="tk-block">
        <h2 style="font-size: 16px;">
            <?php TK_GProject::the_l10n('news'); ?>
        </h2>
    </div>
    <?php require_once(TKGP_STYLE_DIR . 'ajax-news-page.php'); ?>
    <!--News End-->
</div>
<!--End Central-->
<!--Satrt Widgets-->
<div id="projektoj-dekstre">
    <div id="projektoj-dekstre-1">
        <?php dynamic_sidebar('projektoj-dekstre-1'); ?>
    </div>
</div>
<!--End Widgets-->