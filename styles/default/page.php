<?php
/*Template Name: Default TK Global Project Page
 *
 */
get_header();
?>
<div style="padding: 10px 5px 5px 5px;">
<?php
$page = new TK_GPage();
$page->createPage();
echo $page->getPageHtml();
?>
</div>
<?php
get_footer();
?>
