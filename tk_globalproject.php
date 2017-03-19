<?php
/*
* Plugin Name: TehnoKom Global Project
* Plugin URI: https://github.com/tehnokom/alfo-tk-globalproject-plugin
* Text Domain: tkgp
* Description: Allows you to group projects with the possibility of voting.
* Version: 0.1a
* Author: Ravil Sarvaritdinov <ra9oaj@gmail.com>
* Author URI: http://github.com/RA9OAJ/
* License: GPLv2
* Text Domain: tkgp
*/
/*  Copyright 2016  Ravil Sarvaritdinov  (email : ra9oaj@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('TKGP_ROOT', plugin_dir_path(__FILE__));
define('TKGP_URL', plugin_dir_url(__FILE__));
define('TKGP_STYLES_DIR', TKGP_ROOT . 'styles/');
define('TKGP_STYLES_DIR', TKGP_URL . 'styles/');

require_once(TKGP_ROOT.'lib/plug_initial.php');
require_once(TKGP_ROOT.'lib/core.php');
require_once(TKGP_ROOT.'lib/ajax_functions.php');

register_activation_hook(__FILE__, 'tkgp_check_version');

function tkgp_localize_plugin() {
	load_plugin_textdomain( 'tkgp', false, dirname( plugin_basename( __FILE__ ) ) . '/locales/' );
}

function tkgp_css_registry() {
	wp_register_style('tkgp_general', TKGP_URL.'css/tkgp_general.css');
	wp_enqueue_style('tkgp_general');
}

function tkgp_js_registry() {
	wp_register_script('tkgp_js_general', TKGP_URL.'js/tkgp_general.js', array('jquery'));
	wp_enqueue_script('tkgp_js_general');
	wp_enqueue_media();
	wp_localize_script( 'tkgp_js_general', 'tkgp_js_vars',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ),
				   'plug_url' => TKGP_URL,
				   'post_id' => get_the_ID(),
				   'images' => array('load.gif','ok_status.png','err_status.png')));
				   
	wp_localize_script( 'tkgp_js_general', 'tkgp_i18n',
            array( 'loading' => __('Loading...','tkgp')));
}

function tkgp_admin_css_registry() {
	wp_register_style('tkgp_admin', TKGP_URL.'css/tkgp_admin.css');
	wp_register_style('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
  	wp_enqueue_style( 'jquery-ui' );
	wp_enqueue_style('tkgp_admin');
}

function tkgp_admin_js_registry() {
	wp_register_script('tkgp_js_admin', TKGP_URL.'js/tkgp_admin.js');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('tkgp_js_admin');
	wp_localize_script( 'tkgp_js_admin', 'tkgp_i18n',
            array( 'vote_reset' => __('You want to definitely reset the voting results?','tkgp'),
					'delete_manager' => __('You want to delete the manager?','tkgp'),
					'delete_single_manager' => __('You can not drop a single manager.','tkgp')
				 )
			);
}

function tkgp_create_news_cat() {
	$parent_cat_id = get_option('tkgp_news_cat_id');
	
	if(!$parent_cat_id) {
		require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
		$cat_args = array('cat_name' => 'News of projects',
						'category_nicename' => 'tkgp-news-projects',
						'category_parent' => '');
		
		$parent_cat_id = wp_create_category('News of Projects');
		
		if($parent_cat_id) {
			update_option('tkgp_news_cat_id', $parent_cat_id);
		}
	}
}

add_action('plugins_loaded', 'tkgp_check_version');
add_action('plugins_loaded', 'tkgp_localize_plugin');
add_action('plugins_loaded', 'tkgp_create_news_cat');
add_action('wp_enqueue_scripts', 'tkgp_css_registry');
add_action('wp_enqueue_scripts', 'tkgp_js_registry');
add_action('admin_enqueue_scripts', 'tkgp_admin_css_registry');
add_action('admin_enqueue_scripts', 'tkgp_admin_js_registry');

function tkgp_add_search_columns($columns) {
	$columns[] = 'display_name';
	return $columns;
}

add_filter('user_search_columns', 'tkgp_add_search_columns');
?>