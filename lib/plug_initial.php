<?php
function tkgp_check_version()
{
    $cur_version = '0.17';
    $installed_version = tkgp_prepare_version(get_option('tkgp_db_version'));

    if (empty($installed_version)) {
        tkgp_db_install($cur_version);
    } elseif (floatval($cur_version) > floatval($installed_version)) {
        tkgp_upgrade_log("Start upgrading DB from {$installed_version} to {$cur_version}");
        tkgp_db_update($installed_version, $cur_version);
    }
}

function tkgp_prepare_version($ver)
{
    return (preg_replace('/([a-zA-Z]+)/', '', $ver));
}

function tkgp_upgrade_log($msg, $type = 'i')
{
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

function tkgp_db_install($cur_version)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'tkgp_votes';
    $charset_collate = " DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";

    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
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
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
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
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
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
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
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
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        $sql = "CREATE TABLE {$table_name} (
				  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `post_id` bigint(20) unsigned NOT NULL,
				  `news_id` bigint(20) unsigned NULL DEFAULT NULL,
				  `priority` TINYINT unsigned DEFAULT 50,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `post_id` (`post_id`)
				){$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }

    if ($wpdb->get_var("SHOW FUNCTION STATUS LIKE 'get_wp_localize'") != 'get_wp_localize') {
        $sql = "CREATE FUNCTION `get_wp_localize`(`wp_data` TEXT CHARSET utf8, `wp_lang` VARCHAR(4) CHARSET utf8) 
                    RETURNS TEXT CHARSET utf8 NOT DETERMINISTIC READS SQL DATA SQL SECURITY INVOKER 
                BEGIN 
                    DECLARE tag varchar(8) DEFAULT '';
                    DECLARE start_pos int;
                    DECLARE end_pos int;

                    IF wp_lang != '' THEN
                        SET tag = CONCAT('[:', wp_lang, ']');
                        IF INSTR(wp_data, tag) THEN
                            SET start_pos = INSTR(wp_data, tag) + LENGTH(tag);
                            SET end_pos = LOCATE('[:]', wp_data, start_pos);
                            IF end_pos THEN
                                SET end_pos = end_pos;
                                RETURN SUBSTR(wp_data, start_pos, end_pos - start_pos);
                            ELSE
                                RETURN SUBSTR(wp_data, start_post);
                            END IF;
                        END IF;
                    END IF;    
                    RETURN wp_data;
                END;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }

    if ($wpdb->get_var("SHOW FUNCTION STATUS LIKE 'get_project_popularity'") != 'get_project_popularity') {
        $sql = "CREATE FUNCTION `get_project_popularity`(`post_id` BIGINT(20) UNSIGNED, `approval_only` TINYINT(1) UNSIGNED)
                    RETURNS BIGINT UNSIGNED NOT DETERMINISTIC READS SQL DATA SQL SECURITY INVOKER 
                BEGIN 
                    DECLARE v_id BIGINT(20) UNSIGNED DEFAULT 0; 
                    DECLARE v_count BIGINT(20) UNSIGNED DEFAULT 0; 
                    SELECT v.id 
                        INTO v_id 
                    FROM {$wpdb->prefix}tkgp_votes v 
                    WHERE v.post_id = post_id; 
                    SELECT count(*) 
                        INTO v_count 
                    FROM {$wpdb->prefix}tkgp_usersvotes uv 
                    WHERE uv.vote_id = v_id AND (approval_only = 0 OR uv.variant_id = -1); 
                    RETURN v_count; 
                    END;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }

    update_option('tkgp_db_version', $cur_version);
}

function tkgp_db_update($installed_version, $cur_version)
{
    global $wpdb;

    $slug = TK_GProject::slug;

    $patches = array(
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
        '0.15' => array(
            'sql' => array("ALTER TABLE `{$wpdb->prefix}tkgp_projects` 
									ADD COLUMN `news_id` bigint(20) unsigned NULL DEFAULT NULL AFTER `post_id`;"
            ),
            'ver_after' => '0.16'),
        '0.16' => array(
            'sql' => array("CREATE FUNCTION `get_wp_localize`(`wp_data` TEXT CHARSET utf8, `wp_lang` VARCHAR(4) CHARSET utf8) 
                                RETURNS TEXT CHARSET utf8 NOT DETERMINISTIC READS SQL DATA SQL SECURITY INVOKER 
                            BEGIN 
                                DECLARE tag varchar(8) DEFAULT '';
                                DECLARE start_pos int;
                                DECLARE end_pos int;

                                IF wp_lang != '' THEN
                                    SET tag = CONCAT('[:', wp_lang, ']');
                                    IF INSTR(wp_data, tag) THEN
                                        SET start_pos = INSTR(wp_data, tag) + LENGTH(tag);
                                        SET end_pos = LOCATE('[:]', wp_data, start_pos);
                                        IF end_pos THEN
                                            SET end_pos = end_pos;
                                            RETURN SUBSTR(wp_data, start_pos, end_pos - start_pos);
                                        ELSE
                                            RETURN SUBSTR(wp_data, start_post);
                                        END IF;
                                    END IF;
                                END IF;    
                                RETURN wp_data;
                            END;",
                            "CREATE FUNCTION `get_project_popularity`(`post_id` BIGINT(20) UNSIGNED, `approval_only` TINYINT(1) UNSIGNED)
                                RETURNS BIGINT UNSIGNED NOT DETERMINISTIC READS SQL DATA SQL SECURITY INVOKER 
                             BEGIN 
                                DECLARE v_id BIGINT(20) UNSIGNED DEFAULT 0; 
                                DECLARE v_count BIGINT(20) UNSIGNED DEFAULT 0; 
                                SELECT v.id 
                                    INTO v_id 
                                FROM {$wpdb->prefix}tkgp_votes v 
                                WHERE v.post_id = post_id; 
                                SELECT count(*) 
                                    INTO v_count 
                                FROM {$wpdb->prefix}tkgp_usersvotes uv 
                                WHERE uv.vote_id = v_id AND (approval_only = 0 OR uv.variant_id = -1); 
                                RETURN v_count; 
                             END;",
                            "ALTER TABLE `{$wpdb->prefix}tkgp_projects` 
									ADD COLUMN `priority` TINYINT unsigned DEFAULT 50 AFTER `news_id`;"
            ),
            'ver_after' => '0.17'
        )
    );

    if (!empty($patches[$installed_version])) {
        tkgp_upgrade_log("	Patching DB {$installed_version} => {$patches[$installed_version]['ver_after']}");

        if ($patches[$installed_version]['sql'] == 'none') {
            update_option('tkgp_db_version', $patches[$installed_version]['ver_after']);
        } else {
            $result = false;

            foreach ($patches[$installed_version]['sql'] as $path) {
                tkgp_upgrade_log("		SQL: {$path}");

                $result = $wpdb->query($path);

                if (!$result) {
                    if (!empty($wpdb->last_error)) {
                        //ошибка - не прошел патч SQL
                        tkgp_upgrade_log("Error during patch installation!", 'e');
                        tkgp_upgrade_log("SQL messages text: {$wpdb->last_error}", 'e');
                        return;
                    }

                    $result = true; //не критичная ошибка
                    tkgp_upgrade_log("The patch is not changed. Maybe there is nothing to fix or fixes have been made earlier.", 'w');
                } else {
                    tkgp_upgrade_log("		SQL: ОК");
                }
            }

            if ($result) {
                tkgp_upgrade_log("	End patching DB {$installed_version} => {$cur_version}");
                update_option('tkgp_db_version', $patches[$installed_version]['ver_after']); //обновились до следующей версии
                $new_version = tkgp_prepare_version(get_option('tkgp_db_version'));

                if (floatval($new_version) < floatval($cur_version)) {
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