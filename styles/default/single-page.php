<?php
/*Template Name: Default TK Global Project Page
 *
 */

define('TKGP_STYLE_DIR', plugin_dir_path(__FILE__));
define('TKGP_STYLE_URL', plugin_dir_url(__FILE__));

wp_register_style('default.css', TKGP_STYLE_URL . 'css/default.css', array('tkgp_general'));
wp_register_script('default.js', TKGP_STYLE_URL . 'js/default.js', array('jquery', 'tkgp_js_general'));

wp_enqueue_style('default.css');
wp_enqueue_script('default.js');
wp_localize_script('default.js', 'tkl10n', array('you_supported' => TK_GProject::l10n('you_supported')));

global $post, $wp_query;

$project = new TK_GProject($post->ID);

if (!$project->userCanRead(get_current_user_id())) { //Check access for this Project
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}

$vote = $project->getVote();
$approval_percent = 100.0 * floatval($vote->approval_votes) / floatval($vote->target_votes);
$approval_percent = $approval_percent && $approval_percent < 0.75 ? '2px' : $approval_percent . '%';

$upload_dir = wp_upload_dir();
$logo_file = $upload_dir['basedir'] . "/projektoj/logo-{$project->internal_id}.jpg";
$logo_file = is_file($logo_file) ? $logo_file : TKGP_STYLE_DIR . 'images/default-logo.jpg';
$logo_url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $logo_file);

$button_captions = array(
    'approval_title' => TK_GProject::l10n('support_title'),
    'reset_text' => TK_GProject::l10n('you_supported'),
    'reset_title' => TK_GProject::l10n('supported_title') . ".\r\n" . TK_GProject::l10n('cancel_support_title')
);

get_header();
?>
<!--Start Logo-->
<div class="tk-logo" style="background: url(<?php echo $logo_url; ?>) no-repeat;">
    <div class="tk-logo-cell1">
        <div>
            <div>
                <div class="tk-title"><h2><?php echo $project->title; ?></h2></div>
            </div>
            <div>
                <div class="tk-social">#<?php echo $project->internal_id; ?></div>
            </div>
        </div>
    </div>
    <div class="tk-logo-cell2 tkgp_vote_block">
        <div>
            <div class="tk-buttons">
                <div>
                    <input type="hidden" class="tkgp_language_data"
                           data-vbtn-reset-text="<?php TK_GProject::the_l10n('you_supported'); ?>"
                           data-vbtn-reset-title="<?php echo $button_captions['reset_title']; ?>"
                           data-vbtn-approval-title="<?php TK_GProject::the_l10n('support_title'); ?>"/>
                    <?php
                    $button = $vote->getVoteButtonHtml(false, $button_captions);

                    if ($project->userCanVote(get_current_user_id())) {
                        if (!empty($button)) {
                            echo $button;
                        } else {
                            $button_title = TK_GProject::l10n('supported_title') . ".\r\n" . TK_GProject::l10n('cant_cancel_support_title');
                            ?>
                            <div class="tkgp_button tk-supported" title="<?php echo $button_title; ?>.">
                                <a><?php TK_GProject::the_l10n('you_supported'); ?></a></div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="tkgp_button tk-supported"
                             title="<?php TK_GProject::the_l10n('login_support_title'); ?>"><a
                                    href="<?php echo wp_login_url(get_permalink()); ?>"><?php TK_GProject::the_l10n('support'); ?></a>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="tk-users">
                <div></div>
            </div>
            <div class="tk-label">
                <div>
                    <span class="tk-approval-votes"><?php echo number_format($vote->approval_votes, 0, '.', ' '); ?></span>
                    <?php echo TK_GProject::l10n('supported'); ?></div>
            </div>
            <div class="tk-progress">
                <div>
                    <div class="tk-progress-bar">
                        <div class="tk-pb-approved" style="width:<?php echo $approval_percent ?>;"></div>
                    </div>
                </div>
            </div>
            <div class="tk-label">
                <div><?php echo TK_GProject::l10n('Needed'); ?>
                    <span class="tk-target-votes"><?php echo number_format($vote->target_votes, 0, '.', ' '); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<!--End Logo-->
<!--Start Hint-->
<div class="tk-hint">
    <h2><?php echo TK_GProject::l10n('hint'); ?></h2>
    <?php echo apply_filters("the_content", TK_GProject::l10n('hint_text')); ?>
</div>
<!--End Hint-->
<!--Start Target-->
<div class="tk-target">
    <h2><?php TK_GProject::the_l10n('target'); ?></h2>
    <?php echo apply_filters("the_content", $project->target); ?>
</div>
<!--End Target-->
<!--Start Parent Project-->
<?php
$parent_project = $project->getParentProject();

if (!empty($parent_project)) {
    ?>
    <div class="tk-parent-project">
        <h2><?php TK_GProject::the_l10n('parent_project'); ?></h2>
        <ul>
            <li><a href="<?php echo $parent_project->permalink; ?>"><?php echo $parent_project->title; ?></a></li>
        </ul>
    </div>
    <?php
}
?>
<!--End Parent Project-->
<!--Start Subprojects-->
<?php
$subprojects = $project->getChildProjects();

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
<!--End Subprojects-->
<!--Start Tabs-->
<div class="tk-tabs">
    <br id="tk-tab2"/><br id="tk-tab3"/><br id="tk-tab4"/><br id="tk-tab5"/>
    <a href="#tk-tab1"><?php TK_GProject::the_l10n('news'); ?></a><a
            href="#tk-tab2"><?php TK_GProject::the_l10n('description'); ?></a><a
            href="#tk-tab3"><?php TK_GProject::the_l10n('tasks'); ?></a><a
            href="#tk-tab4"><?php TK_GProject::the_l10n('answers'); ?></a><a
            href="#tk-tab5"><?php TK_GProject::the_l10n('team'); ?></a>
    <div></div>
    <div></div>
    <div><?php TK_GProject::the_l10n('no_tasks'); ?></div>
    <div><?php TK_GProject::the_l10n('no_answers'); ?></div>
    <div><?php TK_GProject::the_l10n('no_information'); ?></div>
</div>
<!--End Tabs-->
<?php
get_footer();
?>
