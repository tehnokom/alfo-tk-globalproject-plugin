<?php
/*
Plugin Name: TehnoKom Global Project
Plugin URI: http://rikkimongoose.ru/projects/rikkis-wp-social-icons/
Description: Allows you to group projects with the possibility of voting.
Version: 0.1a
Author: Ravil Sarvaritdinov <ra9oaj@gmail.com>
Author URI: http://github.com/RA9OAJ/
License: GPLv2
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

require_once(TKGP_ROOT.'lib/core.php');
require_once(TKGP_ROOT.'lib/ajax_functions.php');

function tkgp_css_registry() {
	wp_register_style('tkgp_general', TKGP_URL.'css/tkgp_general.css');
	wp_enqueue_style('tkgp_general');
}

function tkgp_admin_css_registry() {
	wp_register_style('tkgp_admin', TKGP_URL.'css/tkgp_admin.css');
	wp_enqueue_style('tkgp_admin');
}

function tkgp_admin_js_registry() {
	wp_register_script('tkgp_js_general', TKGP_URL.'js/tkgp_general.js');
	wp_enqueue_script('jquery');
	wp_enqueue_script('tkgp_js_general');
}

add_action('wp_enqueue_scripts', 'tkgp_admin_css_registry');
add_action('admin_enqueue_scripts', 'tkgp_admin_css_registry');
add_action('admin_enqueue_scripts', 'tkgp_admin_js_registry');

function tkgp_add_search_columns($columns) {
	$columns[] = 'display_name';
	return $columns;
}

add_filter('user_search_columns', 'tkgp_add_search_columns');	
?>