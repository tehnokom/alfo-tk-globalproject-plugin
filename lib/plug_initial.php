<?php
	function tkgp_check_version() {
		$cur_version = '0.15';
		$installed_version = tkgp_prepare_version(get_option('tkgp_db_version'));
		
		if(empty($installed_version)) {
			tkgp_db_install($cur_version);
		} elseif (floatval($cur_version) > floatval($installed_version)) {
			tkgp_upgrade_log("Start upgrading DB from {$installed_version} to {$cur_version}");
			tkgp_db_update($installed_version,$cur_version);
		}
	}
	
	function tkgp_prepare_version($ver) {
		return (preg_replace('/([a-zA-Z]+)/', '', $ver));
	}
	
	function tkgp_upgrade_log($msg, $type = 'i') {
		$prefix = date("[Y-m-d H:i:s]:");
		$type_str = 'Info';
		
		switch (variable) {
			case 'w':
				$type_str = 'Warning';
				break;
				
			case 'e':
				$type_str = 'Error';
				break;
				
			default:
				break;
		}
		
		file_put_contents(TKGP_ROOT . 'upgrade.log', "{$prefix} {$type_str}: {$msg}\r\n", FILE_APPEND);
	}
	
    function tkgp_db_install($cur_version) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'tkgp_votes';
		$charset_collate = " DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";
		
		if($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
			$sql = "CREATE TABLE {$table_name} (
				  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `post_id` bigint(20) unsigned NOT NULL,
				  `enabled` bit(1) DEFAULT NULL,
				  `start_date` datetime(6) NOT NULL,
				  `end_date` datetime(6) DEFAULT NULL,
				  `target_votes` bigint(20) unsigned NOT NULL,
				  `allow_revote` bit(1) DEFAULT NULL,
				  `allow_against` bit(1) DEFAULT NULL,
				  `consider_against` bit(1) DEFAULT NULL,
				  PRIMARY KEY (`id`),
				  KEY `post_index` (`post_id`)
				){$charset_collate};";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			dbDelta($sql);
		}
		
		$table_name = $wpdb->prefix . 'tkgp_votevariant';
		if($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
			$sql = "CREATE TABLE {$table_name} (
				  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `vote_id` bigint(20) unsigned NOT NULL,
				  `variant_id` tinyint(2) unsigned NOT NULL,
				  `variant` varchar(255) NOT NULL,
				  `votes_count` bigint(20) unsigned NOT NULL DEFAULT '0',
				  `approval_flag` bit(1) DEFAULT NULL,
				  `last_update` timestamp DEFAULT NOW(),
				  `date_create` timestamp DEFAULT NOW(),
				  PRIMARY KEY (`id`),
				  INDEX `vote_id` (`vote_id`),
				  UNIQUE KEY `variant_UNIQUE` (`vote_id`,`variant`),
				  UNIQUE KEY `variant_id` (`vote_id`,`variant_id`),
				  UNIQUE KEY `approval_UNIQUE` (`vote_id`,`approval_flag`)
				){$charset_collate};";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			dbDelta($sql);
		}
		
		$table_name = $wpdb->prefix . 'tkgp_usersvotes';
		if($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
			$sql = "CREATE TABLE {$table_name} (
				  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `vote_id` bigint(20) unsigned NOT NULL,
				  `user_id` bigint(20) unsigned NOT NULL,
				  `variant_id` tinyint(3) NOT NULL,
				  `create_date` timestamp DEFAULT NOW(),
				  PRIMARY KEY (`id`),
				  INDEX `votes` (`vote_id`),
				  INDEX `users` (`user_id`),
				  UNIQUE KEY `user_vote_unique` (`vote_id`,`user_id`)
				){$charset_collate};";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				
			dbDelta($sql);
		}
		
		$table_name = $wpdb->prefix . 'tkgp_tasks_links';
		if($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
			$sql = "CREATE TABLE {$table_name} (
				  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `parent_id` bigint(20) unsigned NOT NULL,
				  `parent_type` tinyint(3) NOT NULL,
				  `child_id` bigint(20) unsigned NOT NULL,
				  `child_type` tinyint(3) NOT NULL,
				  `create_date` timestamp DEFAULT NOW(),
				  PRIMARY KEY (`id`),
				  INDEX `parent_id` (`parent_id`),
				  INDEX `child_id` (`child_id`),
				  UNIQUE KEY `child_unique` (`child_id`,`child_type`),
				  UNIQUE KEY `link_unique` (`parent_id`,`parent_type`,`child_id`,`child_type`)
				){$charset_collate};";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				
			dbDelta($sql);
		}
		
		$table_name = $wpdb->prefix . 'tkgp_projects';
		if($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
			$sql = "CREATE TABLE {$table_name} (
				  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `post_id` bigint(20) unsigned NOT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `post_id` (`post_id`)
				){$charset_collate};";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				
			dbDelta($sql);
		}
		
		update_option('tkgp_db_version', $cur_version);
    }
    
    function tkgp_db_update($installed_version, $cur_version) {
    	global $wpdb;
    	
    	$slug = TK_GProject::slug;
    	
    	$patches = array(
	    	'0.1' => array(
	    					'sql' => array("ALTER TABLE `{$wpdb->prefix}tkgp_votes` 
									ADD COLUMN `allow_against` BIT(1) NULL DEFAULT NULL AFTER `allow_revote`,
									ADD COLUMN `consider_against` BIT(1) NULL DEFAULT NULL AFTER `allow_against`;",
										   "ALTER TABLE `{$wpdb->prefix}tkgp_usersvotes` 
									DROP INDEX `user_vote_unique` ,
									ADD UNIQUE INDEX `user_vote_unique` (`vote_id` ASC, `user_id` ASC);"
								 ),
							'ver_after' => '0.11'
						  ),
			'0.11' => array(
							'sql' => array("UPDATE `{$wpdb->prefix}posts` SET `post_type` = 'project', 
									`guid` = REPLACE(`guid`,'tk_project','project')
									WHERE `post_type` = 'tk_project';",
								 ),
							'ver_after' => '0.12'),
			'0.12' => array(
							'sql' => array("UPDATE `{$wpdb->prefix}posts` SET `post_type` = 'projektoj', 
									`guid` = REPLACE(`guid`,'project','projektoj')
									WHERE `post_type` = 'project';",
								 ),
							'ver_after' => '0.13'),
			'0.13' => array(
							'sql' => array("CREATE TABLE `{$wpdb->prefix}tkgp_tasks_links` (
									`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
									`parent_id` bigint(20) unsigned NOT NULL,
									`parent_type` tinyint(3) NOT NULL,
									`child_id` bigint(20) unsigned NOT NULL,
									`child_type` tinyint(3) NOT NULL,
									`create_date` timestamp DEFAULT NOW(),
									PRIMARY KEY (`id`),
									INDEX `parent_id` (`parent_id`),
									INDEX `child_id` (`child_id`),
									UNIQUE KEY `child_unique` (`child_id`,`child_type`),
									UNIQUE KEY `link_unique` (`parent_id`,`parent_type`,`child_id`,`child_type`)
									) DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate};",
								),
							'ver_after' => '0.14'),
			'0.14' => array(
							'sql' => array("CREATE TABLE `{$wpdb->prefix}tkgp_projects` (
				  					`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  					`post_id` bigint(20) unsigned NOT NULL,
				  					PRIMARY KEY (`id`),
				  					UNIQUE KEY `post_id` (`post_id`)
									) DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate};",
											"INSERT INTO `{$wpdb->prefix}tkgp_projects` (`post_id`)
												SELECT `id` FROM `{$wpdb->prefix}posts` 
												WHERE `post_type` = '{$slug}' ORDER BY `post_date` ASC;"
								 ),
							'ver_after' => '0.15'),
		);
    	
	 	if(!empty($patches[$installed_version])) {
	 		tkgp_upgrade_log("	Patching DB {$installed_version} => {$patches[$installed_version]['ver_after']}");
	 		
	 		if($patches[$installed_version]['sql'] == 'none') {
	 			update_option('tkgp_db_version', $patches[$installed_version]['ver_after']);
	 		} else {
	 			$result = false;

		 		foreach ($patches[$installed_version]['sql'] as $path) {
					tkgp_upgrade_log("		SQL: {$path}");
					
					$result = $wpdb->query($path);
		 			
		 			if(!$result) {
						if(!empty($wpdb->last_error)) {
							//ошибка - не прошел патч SQL
							tkgp_upgrade_log("Error during patch installation!",'e');
							tkgp_upgrade_log("SQL messages text: {$wpdb->last_error}", 'e');
							return;	
						}
						
						$result = true; //не критичная ошибка
						tkgp_upgrade_log("The patch is not changed. Maybe there is nothing to fix or fixes have been made earlier.", 'w');
		 			} else {
		 				tkgp_upgrade_log("		SQL: ОК");
		 			}
		 		}
		 		
		 		if($result) {
		 			tkgp_upgrade_log("	End patching DB {$installed_version} => {$cur_version}");
		 			update_option('tkgp_db_version', $patches[$installed_version]['ver_after']); //обновились до следующей версии
		 			$new_version = tkgp_prepare_version(get_option('tkgp_db_version'));
					
					if(floatval($new_version) < floatval($cur_version)) {
						tkgp_db_update($new_version, $cur_version);
					} else {
						tkgp_upgrade_log("End upgrading DB from {$installed_version} to {$cur_version}");
					}
		 		}
			}
	 	} else {
	 		tkgp_upgrade_log("You can not upgrade from version {$installed_version}!", 'e');
	 	} 	
    }
?>