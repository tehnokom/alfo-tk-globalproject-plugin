<?php
/*Template Name: Default TK Global Project Page
 *
 */

define('TKGP_STYLE_DIR', plugin_dir_path(__FILE__));
define('TKGP_STYLE_URL', plugin_dir_url(__FILE__));


wp_register_style('default.css', TKGP_STYLE_URL . 'css/default.css', array(tkgp_general));
wp_register_script('default.js', TKGP_STYLE_URL . 'js/default.js', array('jquery', 'tkgp_js_general'));

wp_enqueue_style('default.css');
wp_enqueue_script('default.js');

$project = new TK_GProject($post->ID);

if(!$project->userCanRead(get_current_user_id())) { //Check access for this Project
	$wp_query->set_404();
	status_header( 404 );
	get_template_part( 404 );
	exit();
}

$vote = $project->getVote();
$approval_percent = 100.0 * floatval($vote->approval_votes) / floatval($vote->target_votes);

get_header();
?>
<!--Start Logo-->
	<div class="tk-logo">
		<div class="tk-logo-cell1">
			<div>
				<div>
					<div class="tk-title"><h2><?php echo $project->title; ?></h2></div>
				</div>
				<div>
					<div class="tk-social"></div>
				</div>
			</div>
		</div>
		<div class="tk-logo-cell2 tkgp_vote_block">
			<div>
				<div class="tk-buttons">
					<div>
<?php 
$button = $vote->getVoteButtonHtml();

if($project->userCanVote(get_current_user_id())) {
	if(!empty($button)) {
		echo $button;
	} else {
?>
						<div class="tkgp_button tk-supported"><a><?php echo TK_GProject::l10n('you_supported'); ?></a></div>
<?php 
	}
} 
?>
					</div>
				</div>
				<div class="tk-users">
					<div></div>
				</div>
				<div class="tk-label">
					<div><span class="tk-approval-votes"><?php echo number_format($vote->approval_votes, 0, '.', ' '); ?></span> 
					<?php echo TK_GProject::l10n('supported'); ?></div>
				</div>
				<div  class="tk-progress">
					<div>
						<div class="tk-progress-bar">
							<div class="tk-pb-approved" style="width:<?php echo $approval_percent ?>%;"></div>
						</div>
					</div>
				</div>
				<div class="tk-label">
					<div><?php echo TK_GProject::l10n('Needed'); ?>
					<span class="tk-target-votes"><?php echo number_format($vote->target_votes, 0, '.', ' '); ?></span></div>
				</div>
			</div>
		</div>
	</div>
<!--End Logo-->
<!--Start Target-->
	<div class="tk-target">
		<h2><?php echo TK_GProject::l10n('target'); ?></h2>
		<?php echo wpautop($project->target); ?>
	</div>
<!--End Target-->
<!--Start Subprojects-->
	<div class="tk-subprojects">
		<h2><?php echo TK_GProject::l10n('subprojects'); ?></h2>
<?php
$subprojects = $project->getChildProjects();

if(!empty($subprojects))
{
?>
	<ul>
<?php
	foreach ($subprojects as $cur) {
		$li = "<li><a href=\"{$cur->permalink}\">{$cur->title}</a></li>";
		echo $li;
	}
?>
	</ul>
<?php
} else {
	echo wpautop(TK_GProject::l10n('subprojects_not_exists'));
}
?>
	</div>
<!--End Subprojects-->
<!--Start Tabs-->
	<div class="tk-tabs">
		<br id="tk-tab2" /><br id="tk-tab3" /><br id="tk-tab4" /><br id="tk-tab5" />
		<a href="#tk-tab1"><?php echo TK_GProject::l10n('news'); ?></a><a href="#tk-tab2"><?php echo TK_GProject::l10n('description'); ?></a><a href="#tk-tab3"><?php echo TK_GProject::l10n('tasks'); ?></a><a href="#tk-tab4"><?php echo TK_GProject::l10n('answers'); ?></a><a href="#tk-tab5"><?php echo TK_GProject::l10n('team'); ?></a>
		<div><?php echo TK_GProject::l10n('no_news'); ?></div>
		<div><?php echo apply_filters("the_content", $project->description); ?></div>
		<div><?php echo TK_GProject::l10n('no_tasks'); ?></div>
		<div><?php echo TK_GProject::l10n('no_answers'); ?></div>
		<div><?php echo TK_GProject::l10n('no_information'); ?></div>
	</div>
<!--End Tabs-->
<?php
get_footer();
?>
