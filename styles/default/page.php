<?php
/*Template Name: Default TK Global Project Page
 *
 */
get_header();

$page = new TK_GPage();
$page -> createPage();
echo $page -> getPageHtml();

get_footer();
?>