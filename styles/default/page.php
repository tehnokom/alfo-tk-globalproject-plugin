<?php
/*Template Name: Default TK Global Project Page
 *
 */

define('TKGP_STYLE_DIR', plugin_dir_path(__FILE__));
define('TKGP_STYLE_URL', plugin_dir_url(__FILE__));

wp_register_style('default-page-css', TKGP_STYLE_URL . 'css/default-page.css', array('tkgp_general'));
wp_register_script('default-page-js', TKGP_STYLE_URL . 'js/default-page.js', array('jquery', 'tkgp_js_general'));

wp_enqueue_style('default-page-css');
wp_enqueue_script('default-page-js');
wp_localize_script('default-page-js', 'tkl10n', array('you_supported' => TK_GProject::l10n('you_supported')));

get_header();

require_once(TKGP_STYLE_DIR . 'ajax-page.php');

get_footer();
?>
