<?php
/*Template Name: Default TK Global Project Page
 *
 */

define('TKGP_STYLE_DIR', plugin_dir_path(__FILE__));
define('TKGP_STYLE_URL', plugin_dir_url(__FILE__));

wp_register_style('default-page-css', TKGP_STYLE_URL . 'css/default-page.css', array(tkgp_general));
wp_register_script('default-page-js', TKGP_STYLE_URL . 'js/default-page.js', array('jquery', 'tkgp_js_general'));

wp_enqueue_style('default-page-css');
wp_enqueue_script('default-page-js');
wp_localize_script('default-page-js', 'tkl10n', array('you_supported' => TK_GProject::l10n('you_supported')));

get_header();

$page = new TK_GPage();
$page->createPage();

foreach ($page->getProjectsOnPage() as $project) {
?>
	<div class="tk-page-unit">
		<div class="tk-page-title">
			<div>
				<div><h2><a href="<?php echo $project->permalink; ?>"><?php echo apply_filters("the_title",$project->title); ?></a></h2></div>
			</div>
			<div>#<?php echo $project->internal_id; ?></div>
		</div>
		<div class="tk-page-target">
			<div><?php echo apply_filters("the_content", $project->target); ?></div>
		</div>
<?php 
	$subprojects = $project->getChildProjects();
	
	if(count($subprojects)) {
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

get_footer();
?>
