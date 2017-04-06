<?php
define('TKGP_STYLE_DIR', plugin_dir_path(__FILE__));
define('TKGP_STYLE_URL', plugin_dir_url(__FILE__));

wp_register_style('default-page-css', TKGP_STYLE_URL . 'css/default-page.css', array('tkgp_general','modal-windows-css'));
wp_register_style('modal-windows-css', TKGP_STYLE_URL . 'css/modal-windows.css');
wp_register_script('modal-windows-js', TKGP_STYLE_URL . 'js/modal-windows.js', array('jquery'));
wp_register_script('default-page-js', TKGP_STYLE_URL . 'js/default-page.js', array('jquery',
    'tkgp_js_general',
    'modal-windows-js'));

wp_enqueue_style('default-page-css');
wp_enqueue_script('default-page-js');
wp_localize_script('default-page-js', 'tkl10n', array('you_supported' => TK_GProject::l10n('you_supported')));

get_header();
?>
<div>
    <div class="tk-projects-list">
        <?php
        require_once(TKGP_STYLE_DIR . 'ajax-page.php');
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
<?php
get_footer();
?>
