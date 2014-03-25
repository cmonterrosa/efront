<?php
$failed_queries = array();

// Added this in order to avoid non-existing table erros when upgrading to different editions
if (strcmp($GLOBALS['configuration']['version_type'], G_VERSIONTYPE_CODEBASE) != 0) {
	try {	
		$GLOBALS['db'] -> Execute("set foreign_key_checks=0");
		foreach (explode(";\n", str_replace("\r\n", "\n", file_get_contents(G_VERSIONTYPE.'_nodrops.sql'))) as $command) {
			if (trim($command)) {		
				$GLOBALS['db'] -> execute(trim($command));
			}
		}
		$GLOBALS['db'] -> Execute("set foreign_key_checks=1");
	} catch (Exception $e) {
		if ($e ->getCode() != 1050) {
			$failed_queries[] = $e->getMessage();
		}
	}
}


//3.6.12 queries
if (version_compare($dbVersion, '3.6.12') == -1) {
	try {
		$db -> Execute("alter table users add last_login int(10) unsigned default NULL");
		$db->Execute("update users u set last_login=(select max(timestamp) from logs where users_LOGIN=u.login and action='login')");
	} catch (Exception $e) {
		if ($e ->getCode() != 1060) {
			$failed_queries[] = $e->getMessage();
		}
	}
	try {
		$db->Execute("alter table lessons add access_limit int(10) default 0");
	} catch (Exception $e) {
		if ($e ->getCode() != 1060) {
			$failed_queries[] = $e->getMessage();
		}
	}
	try {
		$db->Execute("alter table users_to_lessons add access_counter int(10) default 0");
	} catch (Exception $e) {
		if ($e ->getCode() != 1060) {
			$failed_queries[] = $e->getMessage();
		}
	}
	try {
		$db->Execute("alter table user_profile add field_order int(10) default null");
	} catch (Exception $e) {
		if ($e ->getCode() != 1060) {
			$failed_queries[] = $e->getMessage();
		}
	}
	try {
		$db->Execute("alter table completed_tests engine=innodb");
		$db->Execute("
	CREATE TABLE IF NOT EXISTS `completed_tests_blob` (
	  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	  `completed_tests_ID` mediumint(8) unsigned NOT NULL,
	  `test` longblob,
	  PRIMARY KEY (`id`),
	  KEY `ibfk_completed_tests_blob_1` (`completed_tests_ID`),
	  CONSTRAINT `ibfk_completed_tests_blob_1` FOREIGN KEY (`completed_tests_ID`) REFERENCES `completed_tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");	
		$db->Execute("insert into completed_tests_blob (completed_tests_ID, test) select id, test from completed_tests");
		$db->Execute("alter table completed_tests drop test");
	} catch (Exception $e) {
		if ($e ->getCode() != 1054) {
			$failed_queries[] = $e->getMessage();
		}
	}
}

//3.6.13 queries
if (version_compare($dbVersion, '3.6.13') == -1) {
	try {
		$db->Execute("alter table `lessons_to_courses` ADD start_period int(10) UNSIGNED default NULL");
		$db->Execute("alter table `lessons_to_courses` ADD end_period int(10) UNSIGNED default NULL");
	} catch (Exception $e) {
		if ($e ->getCode() != 1060) {
			$failed_queries[] = $e->getMessage();
		}
	}

	try {
		$db -> Execute("alter table user_profile add rule varchar(255) default null");
	} catch (Exception $e) {
		if ($e ->getCode() != 1060) {
			$failed_queries[] = $e->getMessage();
		}
	}

	try {
		$db->Execute("alter table content add linked_to mediumint(8) unsigned default null");
		$db->Execute("alter table questions add linked_to mediumint(8) unsigned default null");
	} catch (Exception $e) {
		if ($e ->getCode() != 1060) {
			$failed_queries[] = $e->getMessage();
		}
	}

	try {
		$db->Execute("alter table users ADD simple_mode tinyint(1) default 0");
	} catch (Exception $e) {
		if ($e ->getCode() != 1060) {
			$failed_queries[] = $e->getMessage();
		}
	}
	
	try {
		//change all tables' engine to innodb, except for these containing a fulltext index, which don't support innodb
		$result = $db->getCol("SELECT CONCAT('ALTER TABLE `',table_schema,'`.`',table_name,'` ENGINE=InnoDB;') FROM information_schema.tables WHERE engine='MyISAM' AND table_schema='{$db->database}' AND  table_name not in (select table_name FROM information_schema.statistics WHERE index_type='FULLTEXT' and table_schema='{$db->database}')");
		foreach ($result as $value) {
			$db->Execute($value);
		}
	} catch (Exception $e) {
		$failed_queries[] = $e->getMessage();
	}
	
	
	try {
		if (is_file('tincan_queries.txt')) {
			$GLOBALS['db'] -> Execute("set foreign_key_checks=0");
			foreach (explode(";\n", file_get_contents('tincan_queries.txt')) as $command) {
				if (trim($command)) {
					$GLOBALS['db'] -> execute(trim($command));
				}
			}
			$GLOBALS['db'] -> Execute("set foreign_key_checks=1");
		}
	} catch (Exception $e) {
		$failed_queries[] = $e->getMessage();
	}
}

if (version_compare($dbVersion, '3.6.13') == 0) {
	try {
		$db->Execute("alter table `users` change `last_login` `last_login` int(10) unsigned NOT NULL DEFAULT 0");
		//$db->Execute("alter table `users_to_lessons` add `progress` float default 0");
	} catch (Exception $e) {
		if ($e ->getCode() != 1265) {  //Error Code: 1265. Data truncated for column
			$failed_queries[] = $e->getMessage();
		}
	}
	
	try {
		if (is_file('tincan_queries.txt')) {
			$GLOBALS['db'] -> Execute("set foreign_key_checks=0");
			foreach (explode(";\n", file_get_contents('tincan_queries.txt')) as $command) {
				if (trim($command)) {
					$GLOBALS['db'] -> execute(trim($command));
				}
			}
			$GLOBALS['db'] -> Execute("set foreign_key_checks=1");
		}
	} catch (Exception $e) {
		$failed_queries[] = $e->getMessage();
	}
				
}

//3.6.14 queries
if (version_compare($dbVersion, '3.6.14') == -1) {

	try{
		$db->Execute("ALTER TABLE `users_to_projects` ADD `professor_upload_filename` VARCHAR( 255) NULL DEFAULT NULL");
		$db->Execute("ALTER TABLE `users_to_projects` ADD `text_grade` VARCHAR( 100 ) NULL DEFAULT NULL");
	} catch (Exception $e) {
		if ($e ->getCode() != 1060) {
			$failed_queries[] = $e->getMessage();
		}
	}
	
	try{
		$db->Execute("create index pm_index ON f_personal_messages (users_LOGIN)");
	} catch (Exception $e) {
			if ($e ->getCode() != 1061) {
			$failed_queries[] = $e->getMessage();
		}
	}
	
	try{
		$db->Execute("create index users_LOGIN ON scorm_data(users_LOGIN)");
	} catch (Exception $e) {
		$failed_queries[] = $e->getMessage();
	}
		
}		







if (!empty($failed_queries)) {
	throw new Exception(implode('<br/>', $failed_queries));
}

