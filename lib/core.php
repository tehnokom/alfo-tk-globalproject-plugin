<?php
    if(!defined('TKGP_ROOT') || !defined('TKGP_URL')) exit;
	
	require_once(TKGP_ROOT.'lib/project.php');
	require_once(TKGP_ROOT.'lib/vote.php');
	
/*Создание типа проектов*/	
	function create_tkgp_type() {
		register_post_type('tk_project',
			array(
				'labels' => array(
					'name' => _x( 'TK Projects', 'tk_project', 'tkgp' ),
					'singular_name' => _x( 'TK Projects', 'tk_project_singular', 'tkgp' ),
					'add' => _x('Create new', 'tk_project','tkgp'),
					'add_new_item' => _x('Create new project', 'tk_project','tkgp'),
					'edit_item' => _x('Edit project','tk_project','tkgp'),
					'new_item' => _x('New project','tk_project','tkgp'),
					'view_item' => _x('View project','tk_project','tkgp'),
					'search_items' => _x('Search project','tk_project','tkgp'),
					'not_found' => _x('Project not found','tk_project','tkgp'),
					'not_found_in_trash' => _x('Project not found in Trash','tk_project','tkgp') 
				),
				'description' => _x('TehnoKom Global Project system type','tk_project','tkgp'),
				'public' => true,
				'exclude_from_search' => false,
				'menu_position'	=> 15,
				'hierarchical' => false,
				'supports' => array( 'title', 'editor', 'comments', 'thumbnail' ),
				'taxonomies' => array( 'tkgp_tax' ),
				'has_archive' => true	
			)
		);
	}

/*Создание таксономии (категориий) для Проектов*/	
	function create_tkgp_taxonomy() {
		register_taxonomy('tkgp_tax', 
			array('tk_project'),
			array(
				'labels' => array(			
			    'name' => _x( 'Categories of Projects', 'Taxonomy General Name', 'tkgp' ),
			    'singular_name' => _x( 'Categories of Projects', 'Taxonomy Singular Name', 'tkgp' ),
			    'menu_name' => __( 'Categories' ),
			    'all_items' => __( 'All Categories' ),
			    'edit_item' => __( 'Edit Category' ),
			    'view_item' => __( 'View Category' ),
			    'update_item' => __( 'Update Category' ),
			    'add_new_item' => __( 'Add New Category' ),
			    'new_item_name' => __( 'New Category Name' ),
			    'parent_item' => __( 'Parent Category' ),
			    'parent_item_colon' => __( 'Parent Category:' ),
			    'search_items' => __('Search Categories'),
			    'popular_items' => __('Popular Tags'),
			    'separate_items_with_commas' => __('Separate tags with commas'),
			    'choose_from_most_used' => __( 'Choose from the most used tags'),
			    'not_found' => __('No categories found.')			
				),
				'hierarchical' => true,
            	'public' => true
            )
		);	
	}

/*Метаданные для Проектов*/
function create_tkgp_meta_box() {
	add_meta_box('tk_project_meta_settings', 
		_x( 'Settings of Project', 'tk_meta', 'tkgp' ), 
		'show_tkgp_metabox_settings',
		null,
		'normal',
		'high');
		
	add_meta_box('tk_project_meta_steps', 
		_x( 'Plane of Project', 'tk_meta', 'tkgp' ), 
		'show_tkgp_metabox_steps',
		null,
		'normal',
		'high');
}

/*Список полей для настройки Проекта*/
//'text|select|radio|date|select_user|select_group'
$tkgp_settings_fields = array (
	array(
		'label' => _x('Type','Project Settings','tkgp'),
		'desc' => _x('Type of this project.','Project Settings','tkgp'),
		'id' => 'ptype',
		'type' => 'radio',
		'options' => array(
		array('label' => _x('Private','Project Settings','tkgp'),
							  'value' => 0),
						array('label' => _x('Working','Project Settings Type','tkgp'),
							  'value' => 1),
					    array('label' => _x('Members only','Project Settings Type','tkgp'),
							  'value' => 2),
						array('label' => _x('Public','Project Settings Type','tkgp'),
							  'value' => 3)
					)
	),
	array(
		'label' => _x('Project Manager','Project Settings','tkgp'),
		'desc' => _x('Manager with full access to settings this Project.','Project Settings','tkgp'),
		'id' => 'manager',
		'type' => 'select_user',
		'options' => null
	),
	array(
		'label' => _x('Working group','Project Settings','tkgp'),
		'desc' => _x('Group to which the project was created.','Project Settings','tkgp'),
		'id' => 'group',
		'type' => 'select_group',
		'options' => null
	),
	array(
		'label' => _x('Visibility','Project Settings','tkgp'),
		'desc' => _x('Visibility for the categories of users.','Project Settings','tkgp'),
		'id' => 'visiblity',
		'type' => 'select',
		'options' => array(
						array('label' => _x('Public','Project Settings','tkgp'),
							  'value' => 0),
						array('label' => _x('Registered','Project Settings','tkgp'),
							  'value' => 1),
					    array('label' => _x('Members only','Project Settings','tkgp'),
							  'value' => 2),
						array('label' => _x('Private','Project Settings','tkgp'),
							  'value' => 3)
					)
	),
);

function show_tkgp_metabox_settings() {
	global $tkgp_settings_fields;
	global $post;

echo '<input type="hidden" name="tkgp_meta_settings_nonce" value="'.wp_create_nonce(basename(__FILE__).'_settings').'" />
<table class="form-table">';

	foreach ($tkgp_settings_fields as $field) {
		echo '<tr>
	<th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
	<td>';
		switch ($field['type']) {
			case 'radio': //Переключатель
				echo '<ul class="tkgp_radio">';
				$current_val = get_post_meta($post->ID,$field['id'],true);
				$current_val = $current_val == '' ? '0' : $current_val;
				
				foreach ($field['options'] as $option) {
					echo '<li><input type="radio" name="'.$field['id'].'" '.($current_val == $option['value'] ? 'checked="true"' : '').' value="'.$option['value'].'">'.$option['label'].'</li>';	
				}
				
				echo '</ul>';
				break;
			
			case 'select': //Выпадающее меню
				echo '<select name="'.$field['id'].'" seze="1">';
				$current_val = get_post_meta($post->ID,$field['id'],true);
				$current_val = $current_val == '' ? '1' : $current_val;
				
				foreach ($field['options'] as $option) {
					if(is_array($current_val) == true)
						echo '<option '.(array_search($option['value'], $current_val) != false ? 'selected' : '').' value="'.$option['value'].'">'.$option['label'].'</option>';
					else echo '<option '.($current_val == $option['value'] ? 'selected' : '').' value="'.$option['value'].'">'.$option['label'].'</option>'; 					
				}
				echo '</select>';
				break;
			
			case 'select_user':
				$current_val = get_post_meta($post->ID, $field['id'], true);
				$current_val = $current_val == '' ? wp_get_current_user()->ID : $current_val;
				
				if(is_array($current_val) == true) { //если несколько менеджеров
					
				}
				/*elseif($current_val == '') { //если не назначен менеджер
					echo '<input type="text" placeholder= value="'.$current_val.'">';
				}*/
				elseif($current_val != '') {
					echo '<div class="tkgp_user button">
						'.get_user_by('ID',$current_val)->display_name.'
						<input type="hidden" name="'.$field['id'].'" value="'.$current_val.'">
					</div>';
				}

				echo '<div class="tkgp_btn tkgp_user_add button">
					'._x('Add','Project Settings','tkgp').'
					</div>';
				break;
			
			case 'select_group':
				if(!defined( 'BP_PLUGIN_DIR' )) //зависимость от BuddyPress
					echo _x('Groups are not supported.','Project Settings','tkgp');
				else {
					$current_val = get_post_meta($post->ID, $field['id'], true);
					//$current_val = 1;
					
					if(is_array($current_val) == true) {
						
					}
					/*elseif($current_val == '') {
						echo '<input type="text" value="'.$current_val.'">';
					}*/
					elseif($current_val != '') {
						echo '<div class="tkgp_user button">
							'.groups_get_group(array('group_id' => $current_val))->name.'
							<input type="hidden" name="'.$field['id'].'" value="'.$current_val.'">
						</div>';
					}
				}
				
				echo '<div class="tkgp_btn tkgp_user_add button">
						'._x('Add','Project Settings','tkgp').'
						</div>';
				
				break;
				
			default:
				echo '<input type="text">';
				break;
			
			echo '</td>';
		}
		echo '</tr>';		
	}

echo '</table>';
}

function show_tkgp_metabox_steps() {
?>
<input type="hidden" name="tkgp_meta_settings_nonce" value="<?php echo wp_create_nonce(basename(__FILE__).'_steps'); ?>" />

<?php
}

	add_action( 'init', 'create_tkgp_type' );
	add_action( 'init', 'create_tkgp_taxonomy');
	add_action( 'add_meta_boxes', 'create_tkgp_meta_box');
?>