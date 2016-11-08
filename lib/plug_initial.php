<?php
    function tkgp_db_install() {
		$cur_version = '0.1a';

		if(get_option('tkgp_db_version') && get_option('tkgp_db_version') !== $cur_version) {
			tkgp_db_update();
			return;
		}		
		
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
				  UNIQUE KEY `user_vote_unique` (`vote_id`,`user_id`,`variant_id`)
				){$charset_collate};";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				
			dbDelta($sql);
		}
		
		update_option('tkgp_db_version', $cur_version);
    }
    
    function tkgp_db_update() {
    	return;
    }
?>