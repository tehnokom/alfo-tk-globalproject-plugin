<?php
/*Template Name: Default TK Global Project Page
 *
 */

define('TKGP_STYLE_DIR', plugin_dir_path(__FILE__));
define('TKGP_STYLE_URL', plugin_dir_url(__FILE__));

wp_register_style('default.css', TKGP_STYLE_URL . 'css/default.css', array('tkgp_general','modal-windows-css'));
wp_register_style('modal-windows-css', TKGP_STYLE_URL . 'css/modal-windows.css');
wp_register_script('modal-windows-js', TKGP_STYLE_URL . 'js/modal-windows.js', array('jquery'));
wp_register_script('tasks-tool-js', TKGP_STYLE_URL . 'js/tasks-tool.js', array('jquery-ui-sortable'));
wp_register_script('default.js', TKGP_STYLE_URL . 'js/default.js', array('jquery',
    'tkgp_js_general',
    'modal-windows-js',
    'tasks-tool-js'));

wp_enqueue_style('default.css');
wp_enqueue_script('default.js');
wp_localize_script('default.js', 'tkl10n', array('you_supported' => TK_GProject::l10n('you_supported')));

function tk_current_subpage($name) {
    if(get_query_var('tksubpage') === $name) {
        echo 'class="current selected"';
        return true;
    }

    return false;
}

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
                <div class="tk-target"><?php echo apply_filters("the_content", $project->target); ?></div>
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
    <div>
        <?php echo TK_GProject::l10n('hint_text'); ?>
    </div>
    <div><a class="tk-hide-hint" href="javascript:void(0);">X</a></div>
</div>
<!--End Hint-->
<!--Start Nav-->
<div class="tk-nav">
    <ul>
        <li <?php tk_current_subpage('') ?> >
            <a href="<?php echo $project->permalink; ?>"><?php echo _x('General', 'Default style', 'tk-style'); ?></a>
        </li>
        <li <?php tk_current_subpage('informo') ?> >
            <a href="<?php echo $project->permalink . '/informo'; ?>">
                <?php echo _x('Information', 'Default style', 'tk-style'); ?>
            </a>
        </li>
        <li <?php tk_current_subpage('taskoj') ?> >
            <a href="<?php echo $project->permalink . '/taskoj'; ?>">
                <?php echo _x('Tasks', 'Default style', 'tk-style'); ?>
            </a>
        </li>
        <li <?php tk_current_subpage('teamo') ?> >
            <a href="<?php echo $project->permalink . '/teamo'; ?>">
                <?php echo _x('Team', 'Default style', 'tk-style'); ?>
            </a>
        </li>
        <li <?php tk_current_subpage('statistiko') ?> >
            <a href="<?php echo $project->permalink . '/statistiko'; ?>">
                <?php echo _x('Statistics', 'Default style', 'tk-style'); ?>
            </a>
        </li>
        <?php if(tkgp_is_user_role('administrator') || tkgp_is_user_role('editor')) {
            ?>
        <li <?php tk_current_subpage('administrado') ?> >
            <a href="<?php echo $project->permalink . '/administrado'; ?>">
                <?php echo _x('Control', 'Default style', 'tk-style'); ?>
            </a>
        </li>
        <?php
        }
        ?>
    </ul>
</div>
<!--End Nav-->
<?php
$require_page = '';
switch (get_query_var('tksubpage')) {
    case 'informo':
        $require_page = TKGP_STYLE_DIR . 'info.php';
        break;

    case 'taskoj':
        $require_page = TKGP_STYLE_DIR . 'ajax-tasks.php';
        break;

    case 'teamo':
        $require_page = TKGP_STYLE_DIR . 'ajax-team.php';
        break;

    case 'statistiko':
    case 'administrado':
        $require_page = TKGP_STYLE_DIR . 'statistic.php';
        break;

    default:
        $require_page = TKGP_STYLE_DIR . 'general.php';
        break;
}
require_once ($require_page);

get_footer();
?>
