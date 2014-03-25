<?php
/**
 * Efront System Classes file
 *
 * @package eFront
 * @version 3.5.0
 */

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

/**
 * System exceptions
 *
 * @package eFront
 */
class EfrontSystemException extends Exception
{
	const INCOMPATIBLE_VERSIONS = 10;
	const ILLEGAL_CSV          = 11;
	const UNAUTHORIZED_ACCESS  = 12;
	const INVALID_VERSION_KEY  = 13;
	const ERROR_CONNECT_SERVER = 14;
	const VERSION_KEY_ERROR    = 15;
}

/**
 * System class
 *
 * This class incorporates system-wise static functions
 *
 * @since 3.5.0
 * @package eFront
 */
class EfrontSystem
{


	/**
	 * Backup system
	 *
	 * This function is used to backup the system. There are 2 types of backup, database only and full.
	 * In the first case (the default), only the database is backed up, while in the second case, files
	 * are backed up as well.
	 * <br/>Example:
	 * <code>
	 * $backupFile = EfrontSystem :: backup('13_3_2007');			//Backup database only
	 * </code>
	 *
	 * @param string $backupName The name of the backup
	 * @param int $backupType Can be either 0 or 1, where 0 siginifies database only backup and 1 is for including backup files as well
	 * @return EfrontFile The compressed file of the backup
	 * @since 3.5.0
	 * @access public
	 * @static
	 */
	public static function backup($backupName, $backupType = 0) {
		$tempDir     = G_BACKUPPATH.'temp/';
		if (is_dir($tempDir)) {
			$dir = new EfrontDirectory($tempDir);
			$dir -> delete();
		}
		mkdir($tempDir, 0755);
		mkdir($tempDir.'db_backup', 0755);
		$directory = new EfrontDirectory($tempDir);

		$tables    = $GLOBALS['db'] -> GetCol("show tables");                                              //Get the database tables

		foreach ($tables as $table) {
			if (!preg_match("/^\w+_view$/", $table)) {
				$data   = eF_getTableData($table, "count(*)");
				$unfold = 1000;
				$limit  = ceil($data[0]['count(*)'] / $unfold);
				for ($i = 0; $i < $limit; $i++) {
					$data = eF_getTableData($table, "*", "", "'' limit $unfold offset ".($i*$unfold));

					file_put_contents($tempDir.'db_backup/'.$table.'.'.$i, serialize($data), FILE_APPEND);
				}
				$result       = eF_ExecuteNew("show create table $table");
				$temp         = $result -> GetAll();
				$definition[] = "drop table ".$temp[0]['Table'];
				$definition[] = $temp[0]['Create Table'];
			}
		}

		/*
		 foreach ($tables as $table) {
			$data = eF_getTableData($table);
			file_put_contents($tempDir.'db_backup/'.$table, serialize($data));
			$result       = eF_ExecuteNew("show create table $table");
			$temp         = $result -> GetAll();
			$definition[] = "drop table ".$temp[0]['Table'];
			$definition[] = $temp[0]['Create Table'];
			}
			*/
		file_put_contents($tempDir.'db_backup/sql.txt', implode(";\n", $definition));
		file_put_contents($tempDir.'db_backup/version.txt', G_VERSION_NUM);

		if ($backupType == 1) {
			$lessonsDir = new EfrontDirectory(G_LESSONSPATH);
			$lessonsDir -> copy($tempDir.'lessons');
			$uploadsDir = new EfrontDirectory(G_UPLOADPATH);
			$uploadsDir -> copy($tempDir.'upload');
			$certificatesDir = new EfrontDirectory(G_ROOTPATH."www/certificate_templates/");
			$certificatesDir-> copy($tempDir.'certificate_templates');
			$editorTemplatesDir = new EfrontDirectory(G_ROOTPATH."www/content/editor_templates/");
			$editorTemplatesDir-> copy($tempDir.'editor_templates');
		} else if ($backupType == 2) {
			$rootDir = new EfrontDirectory(G_ROOTPATH);
			$rootDir -> copy($tempDir.'efront_root');
		}
		$compressedFile = $directory -> compress($backupName, false);
		//$directory -> delete();

		return $compressedFile;
	}

	/**
	 * Restore system
	 *
	 * This function is used to restore a backup previously taken
	 * <br/>Example:
	 * <code>
	 * </code>
	 *
	 * @param EfrontFile $restoreFile The file restore from
	 * @param boolean $force Force restore even if versions are incompatible
	 * @since 3.5.2
	 * @access public
	 */
	public static function restore($restoreFile, $force = false) {
		if (!($restoreFile instanceof EfrontFile)) {
			$restoreFile = new EfrontFile($restoreFile);
		}

		$tempDir     = G_BACKUPPATH.'temp/';
		if (is_dir($tempDir)) {
			$dir = new EfrontDirectory($tempDir);
			$dir -> delete();
		}
		mkdir($tempDir, 0755);

		$restoreFile  = $restoreFile -> copy($tempDir.'/');
		$restoreFile -> uncompress(false);

		$filesystem  = new FileSystemTree($tempDir);

		$iterator    = new EfrontFileOnlyFilterIterator(new RecursiveIteratorIterator($filesystem -> tree, RecursiveIteratorIterator :: SELF_FIRST));
		foreach ($iterator as $key => $value) {
			if (strpos($key, 'version.txt') !== false) {
				$backupVersion = file_get_contents($key);
			}
		}

		if (version_compare($backupVersion, G_VERSION_NUM) != 0 && !$force) {
			throw new Exception (_INCOMPATIBLEVERSIONS.'<br/> '._BACKUPVERSION.':'.$backupVersion.' / '._CURRENTVERSION.': '.G_VERSION_NUM, EfrontSystemException::INCOMPATIBLE_VERSIONS);
		}

		$sql  = file_get_contents($tempDir.'db_backup/sql.txt');
		$sql  = explode(";\n", $sql);
		$node = $filesystem -> seekNode($tempDir.'db_backup');
		
		for ($i = 0; $i < sizeof($sql); $i+=2) {
			preg_match("/drop table (.+)/", $sql[$i], $matches);
			if ($matches[1]) {
				$temp[$matches[1]] = array($sql[$i], $sql[$i + 1]);
			}
		}
		$sql = $temp;
		
		//For each one of the tables that have backup data, recreate its table and import data
		$iterator = new EfrontFileOnlyFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($node), RecursiveIteratorIterator :: SELF_FIRST));
		$GLOBALS['db'] -> Execute("SET FOREIGN_KEY_CHECKS=0");
		foreach ($iterator as $file => $value) {
			$tableName = preg_replace("/\.\d+/", "", basename($file));
			if (isset($sql[$tableName])) {
				try {
					eF_executeNew($sql[$tableName][0]);
				} catch (Exception $e) {/*Don't halt for missing tables that can't be deleted*/}
				eF_executeNew($sql[$tableName][1]);
				unset($sql[$tableName]);
			}
			if (strpos($file, 'sql.txt') === false && strpos($file, 'version.txt') === false) {
				$data = unserialize(file_get_contents($file));
				$tableExists = false;
				try {
					$tableExists = eF_describeTable($tableName);
				} catch (Exception $e) {}
				if ($tableExists !== false && !preg_match("/^\w+_view$/", $tableName)) {
					eF_insertTableDataMultiple($tableName, $data);
				}
			}
		}
		$GLOBALS['db'] -> Execute("SET FOREIGN_KEY_CHECKS=1");

		//Turn off foreign key checks in order to be able to run "drop table" queries
		eF_executeNew("SET FOREIGN_KEY_CHECKS = 0;");
		//For each one of the tables that don't have backup data, simply recreate
		foreach ($sql as $tableName => $query) {
			try {
				eF_executeNew($query[0]);
			} catch (Exception $e) {/*Don't halt for missing tables that can't be deleted*/}
			eF_executeNew($query[1]);
		}
		eF_executeNew("SET FOREIGN_KEY_CHECKS = 1;");

		if (is_dir(G_BACKUPPATH.'temp/upload')) {
			$dir = new EfrontDirectory(G_BACKUPPATH.'temp/upload');
			$dir -> copy(G_ROOTPATH.'upload', true);
		}
		if (is_dir(G_BACKUPPATH.'temp/lessons')) {
			$dir = new EfrontDirectory(G_BACKUPPATH.'temp/lessons');
			$dir -> copy(G_CONTENTPATH.'lessons', true);
		}
		if (is_dir(G_BACKUPPATH.'temp/efront_root')) {
			$dir = new EfrontDirectory(G_BACKUPPATH.'temp/efront_root');
			$dir -> copy(G_ROOTPATH, true);
		}
		if (is_dir(G_BACKUPPATH.'temp/certificate_templates')) {
			$dir = new EfrontDirectory(G_BACKUPPATH.'temp/certificate_templates');
			$dir -> copy(G_ROOTPATH.'www/certificate_templates', true);
		}
		if (is_dir(G_BACKUPPATH.'temp/editor_templates')) {
			$dir = new EfrontDirectory(G_BACKUPPATH.'temp/editor_templates');
			$dir -> copy(G_ROOTPATH.'www/content/editor_templates', true);
		}

		$dir = new EfrontDirectory($tempDir);
		$dir -> delete();

		if (function_exists('apc_clear_cache')) {
			apc_clear_cache('user');
		}

		return true;
	}

	/**
	 * Import users
	 *
	 * This function is used to import users from the given CSV
	 * file.
	 * <br/>Example:
	 * <code>
	 * $file = new EfrontFile(/var/www/efront/upload/admin/temp/users.csv);
	 * EfrontSystem :: importUsers($file);
	 * </code>
	 *
	 * @param mixed $file The CVS file with the users, either an EfrontFile object or the full path to the file
	 * @param boolean $replaceUsers Whether to replace existing users having the same name as the ones imported
	 * @return array The imported users in an array of EfrontUser objects
	 * @since 3.5.0
	 * @access public
	 */
	public static function importUsers($file, $replaceUsers = false) {
		if (!($file instanceof EfrontFile)) {
			$file = new EfrontFile($file);
		}
		$usersTable  = eF_getTableData("users", "*", "");
		$tableFields = array_keys($usersTable[0]);

		// Get user types to check if they exist
		$userTypesTable = eF_getTableData("user_types", "*", "");
		// Set the userTypesTable to find in O(1) the existence or not of a user-type according to its name
		foreach($userTypesTable as $key => $userType) {
			$userTypesTable[$userType['name']] = $userType;
		}

		// If we work on the enterprise version we need to distinguish between users and module_hcd_employees tables fields
		//$userFields = array('login', 'password','email','languages_NAME','name','surname','active','comments','user_type','timestamp','avatar','pending','user_types_ID');
		$userFields = eF_getTableFields('users');

		$existingUsers = eF_getTableDataFlat("users", "login");
		$fileContents = file_get_contents($file['path']);
		$fileContents = explode("\n", trim($fileContents));
		$separator    = ";";
		//$fields       = explode($separator, trim($fileContents[0]));
		$fields       = str_getcsv(trim($fileContents[0]), $separator);
		if (sizeof($fields) == 1) {
			$separator = ",";
			//$fields    = explode($separator, $fileContents[0]);
			$fields    = str_getcsv(trim($fileContents[0]), $separator);
			if (sizeof($fields) == 1) {
				throw new Exception (_UNKNOWNSEPARATOR, EfrontSystemException::ILLEGAL_CSV);
			}
		}

		foreach ($fields as $key => $value) {
			if (empty($value)) {
				$unused = $key;
				unset($fields[$key]);
			}
		}
		$inserted = 0;
		$matched  = array_intersect($fields, $tableFields);

		$newUsers = array();
		$messages = array();
		// The check here is removed to offer interoperability between enterprise and educational versions
		// throw new Exception (_PLEASECHECKYOURCSVFILEFORMAT, EfrontSystemException::ILLEGAL_CSV);

		for ($i = 1; $i < sizeof($fileContents); $i++) {
			//$csvUser = explode($separator, $fileContents[$i]);
			$csvUser = str_getcsv($fileContents[$i], $separator);
			unset($csvUser[$unused]);

			if (sizeof($csvUser) != sizeof($fields)) {
				throw new Exception (_PLEASECHECKYOURCSVFILEFORMAT.': '._NUMBEROFFIELDSMUSTBE.' '.sizeof($fields).' '._BUTFOUND.' '.sizeof($csvUser), EfrontSystemException::ILLEGAL_CSV);
			}
			$csvUser = array_combine($fields, $csvUser);
			array_walk($csvUser, create_function('&$v, $k', '$v=trim($v);'));

			if (in_array($csvUser['login'], $existingUsers['login']) && $replaceUsers) {
				$existingUser  = EfrontUserFactory :: factory($csvUser['login']);
				$existingUser -> delete();
			}
			if (!in_array($csvUser['login'], $existingUsers['login']) || $replaceUsers) {

				if (!isset($csvUser['password']) || !$csvUser['password']) {
					$csvUser['password'] = $csvUser['login'];
				}

				// Check the user-type existence by name
				if ($csvUser['user_type_name'] != "" && isset($userTypesTable[$csvUser['user_type_name']])) {
					// If there is a mismatch between the imported custom type basic type and the current basic type
					// then set no custom type
					if ($userTypesTable[$csvUser['user_type_name']]['basic_user_type'] != $csvUser['user_type']) {
						$csvUser['user_types_ID'] = 0;
					} else {
						$csvUser['user_types_ID'] = $userTypesTable[$csvUser['user_type_name']]['id'];
					}

				} else {
					$csvUser['user_types_ID'] = 0;
				}
				unset($csvUser['user_type_name']);

				if (!$csvUser['user_type']) {
					$csvUser['user_type'] = 'student';
				}

				//If user type is not valid, don't insert that user
				if ($csvUser['user_type'] != "administrator" && $csvUser['user_type'] != "professor" && $csvUser['user_type'] != "student") {
					$messages[] = '&quot;'.$csvUser['login'].'&quot;: '._INVALIDUSERTYPE;
					unset($csvUser);continue;
				}

				// If we are not in enterprise version then $csvEmployeeProperties is used as a buffer
				// This is done to enable enterprise <-> Enteprise, educational <-> educational, enterprise <-> educational imports/exports
				$csvEmployeeProperties = $csvUser;
				if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
					// Copy all fields and remove the user ones -> leaving only employee related fields
					$csvEmployeeProperties['users_login'] = $csvUser['login'];
				} #cpp#endif

				// Delete and recreate $csvUser to keep only the fields in userFields
				unset($csvUser);
				foreach($userFields as $field) {
					if (isset($csvEmployeeProperties[$field])) {
						$csvUser[$field] = $csvEmployeeProperties[$field];
						if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
							unset($csvEmployeeProperties[$field]);
						} #cpp#endif
					}
				}

				try {
					if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
						$user =  EfrontUser :: createUser($csvUser);
						if (isset($csvEmployeeProperties['branch_name'])) {
							$result = eF_getTableData("module_hcd_branch", "branch_ID", "name='".$csvEmployeeProperties['branch_name']."'");
							if ($result[0]['branch_ID']) {
								$branchId = $result[0]['branch_ID'];
							}
							unset($csvEmployeeProperties['branch_name']);
						}

						if (isset($csvEmployeeProperties['job_name'])) {
							$result = eF_getTableData("module_hcd_job_description", "job_description_ID", "description='".$csvEmployeeProperties['job_name']."'");
							if ($result[0]['job_description_ID']) {
								$jobId = $result[0]['job_description_ID'];
							}
							unset($csvEmployeeProperties['job_name']);
						}
						if (isset($csvEmployeeProperties['job_role'])) {
							$csvEmployeeProperties['job_role'] ? $jobRole = 1 : $jobRole = 0;
							unset($csvEmployeeProperties['job_role']);
						}
						$user -> aspects['hcd'] = EfrontHcdUser::createUser($csvEmployeeProperties);
						if (isset($branchId) && isset($jobId) && isset($jobRole)) {
							$user -> aspects['hcd'] -> addJob($user, $jobId, $branchId, $jobRole);
						}

						$newUsers[] = $user;
					} else { #cpp#else
						$newUsers[] = EfrontUser :: createUser($csvUser);
					} #cpp#endif

				} catch (Exception $e) {
					$messages[] = '&quot;'.$csvUser['login'].'&quot;: '.$e -> getMessage().' ('.$e -> getCode().')';
				}
			}
		}

		return array($newUsers, $messages);
	}

	/**
	 * Export users
	 *
	 * This function is used to produce a CSV file with the system
	 * users.
	 * <br/>Example:
	 * <code>
	 * EfrontSystem :: exportUsers(";");		Create a semicolon-delimited CSV file with system users
	 * </code>
	 *
	 * @param string $separator The separator to use for the csv file
	 * @return EfrontFile The exported CSV file
	 * @since 3.5.0
	 * @access public
	 */
	public static function exportUsers($separator) {
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$users   = eF_getTableData("users LEFT OUTER JOIN user_types ON users.user_types_ID = user_types.id LEFT OUTER JOIN module_hcd_employees ON module_hcd_employees.users_login = users.login", "users.*, user_types.name as user_type_name, module_hcd_employees.*");
		} else { #cpp#else
			$users   = eF_getTableData("users LEFT OUTER JOIN user_types ON users.user_types_ID = user_types.id", "users.*, user_types.name as user_type_name");
		} #cpp#endif

		foreach ($users as $user) {
			unset($user['password']);
			unset($user['user_types_ID']);
			unset($user['additional_accounts']);
			if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
				unset($user['users_login']);
			} #cpp#endif
			$lines[] = implode($separator, $user);
		}

		array_unshift($lines, implode($separator, array_keys($user)));

		if (!is_dir($GLOBALS['currentUser'] -> user['directory']."/temp")) {
			mkdir($GLOBALS['currentUser'] -> user['directory']."/temp", 0755);
		}
		file_put_contents($GLOBALS['currentUser'] -> user['directory']."/temp/efront_users.csv", implode("\n", $lines));

		$file = new EfrontFile($GLOBALS['currentUser'] -> user['directory']."/temp/efront_users.csv");

		return $file;
	}

	/**
	 * Get system languages
	 *
	 * This function is used to get the languages installed to the system
	 * <br/>Example:
	 * <code>
	 * $languages = EfrontSystem :: getLanguages();	Returns a 2-dimensional array, with complete information on each language
	 * $languages = EfrontSystem :: getLanguages(true, true);	Returns a 1-dimensional array of active languages, with name => translation pairs
	 * </code>
	 *
	 * @param boolean $reduced Whether to return only active languages, in a single-dimensional array
	 * @return array The languages array
	 * @since 3.5.0
	 * @access public
	 */
	public static function getLanguages($reduced = false, $only_active = false) {
		$languages = EfrontCache::getInstance()->getCache('languages');
		if (!$languages) {
			$languages = array();
			$result = eF_getTableData("languages", "*", "", "translation asc");
			foreach ($result as $value) {
				if (is_file(G_ROOTPATH.'libraries/language/lang-'.$value['name'].'.php.inc')) {
					$value['file_path']        = G_ROOTPATH.'libraries/language/lang-'.$value['name'].'.php.inc';
					$languages[$value['name']] = $value;
				} else {
					eF_deleteTableData("languages", "name='".$value['name']."'");
				}
			}
				
			EfrontCache::getInstance()->setCache('languages', $languages);
		}

		if ($only_active) {
			foreach ($languages as $key=>$value) {
				if (!$value['active']) {
					unset($languages[$key]);
				}
			}
		}
		
		if ($reduced) {
			$reduced = array();
			foreach ($languages as $key => $value) {
				$value['translation'] ? $reduced[$key] = $value['translation'] : $reduced[$key] = $key;
			}
			return $reduced;
		} else {
			return $languages;
		}
	}

	public static function setFaviconFile($currentTheme) {
		$favicon = EfrontCache::getInstance()->getCache('favicon');
		if (!$favicon) {
			try {
				try {
					$faviconFile  = new EfrontFile($GLOBALS['configuration']['favicon']);
					$favicon = 'images/logo/'.$faviconFile['physical_name'];
				} catch (Exception $e) {
					$faviconFile  = new EfrontFile($currentTheme -> options['favicon']);
					$favicon = 'images/'.$faviconFile['physical_name'];
				}
			} catch (EfrontFileException $e) {
				$favicon = "images/favicon.png";
			}

			EfrontCache::getInstance()->setCache('favicon', $favicon);
		}
		return $favicon;		
	}
	
	public static function setLogoFile($currentTheme) {
		$logo = EfrontCache::getInstance()->getCache('logo');
		if (!$logo) {
			try {
				if ($GLOBALS['configuration']['use_logo'] == 2 && defined('G_BRANCH_URL') && G_BRANCH_URL && is_file(G_CURRENTTHEMEPATH.'images/logo/logo.png')) {
					$logo = 'images/logo/logo.png';
				} else if ($GLOBALS['configuration']['use_logo'] == 2 && is_file(G_CURRENTTHEMEPATH.'images/logo/logo.png')) {
					$logo = 'images/logo/logo.png';
				} else if ($GLOBALS['configuration']['use_logo'] > 0) {		//meaning that either we have 'use site logo' (1) or 'use theme logo' (2) but that does not exist
					$logoFile = new EfrontFile($GLOBALS['configuration']['site_logo']);
					$logo = 'themes/default/images/logo/'.$logoFile['physical_name'];
				} else {
					$logo = 'images/logo.png';
				}
			} catch (EfrontFileException $e) {
				$logo = "images/logo.png";
			}
			EfrontCache::getInstance()->setCache('logo', $logo);
		}		
		
		return $logo;
	}

	/**
	 * Get system admin
	 *
	 * This function is used to find and retrieve the system administrator
	 * If there are more than one, the system returns the older one.
	 * <br/>Example:
	 * <code>
	 * $admin = EfrontSystem :: getAdministrator();     //$admin is now an EfrontUser object with the user administrator
	 * </code>
	 *
	 * @return EfrontUser The administrator object
	 * @since 3.5.2
	 */
	public static function getAdministrator() {
		$admins = eF_getTableData("users", "*", "user_type = 'administrator' and user_types_ID = 0 and active=1 and archive=0", "timestamp");
		if (empty($admins)) {
			$admins = eF_getTableData("users", "*", "user_type = 'administrator' and active=1 and archive=0", "timestamp");
		}
		$admin  = EfrontUserFactory :: factory($admins[0]);

		return $admin;
	}

	/**
	 * Lock system
	 *
	 * This function locks down system, so that no users can login (for example when upgrading)
	 * If $logoutUsers is set, it logs out connected users as well
	 * <br/>Example:
	 * <code>
	 * EfrontSystem::lockSystem('The system is locked due to maintenance', 1);
	 * </code>
	 *
	 * @param string $message The message to display on the index page
	 * @param boolean $logoutUsers Whether to disconnect connected users
	 * @since 3.6.0
	 * @access public
	 * @static
	 */
	public static function lockSystem($message, $logoutUsers) {
		EfrontConfiguration::setValue('lock_message', $message);
		EfrontConfiguration::setValue('lock_down', 1);
		if ($logoutUsers) {
			$onlineUsers = EfrontUser :: getUsersOnline();
			foreach ($onlineUsers as $value) {
				if ($value['login'] != $_SESSION['s_login']) {
					$user = EfrontUserFactory :: factory($value['login']);
					$user -> logout();
				}
			}
		}
	}

	/**
	 * Unlock system
	 *
	 * This function unlocks the system, previously locked with EfrontSystem::lockSystem
	 * <br/>Example:
	 * <code>
	 * EfrontSystem :: unlockSystem();
	 * </code>
	 *
	 * @since 3.6.0
	 * @access public
	 * @static
	 */
	public static function unlockSystem() {
		EfrontConfiguration::setValue('lock_down', 0);
	}

	/**
	 * Check version key
	 *
	 * This function is used to check a version key for validity. It does so by communicating with
	 * the license server. Doing so also retrieves information about the current version
	 * <br/>Example:
	 * <code>
	 * $versionData = EfrontSystem :: checkVersionKey('version_key');
	 * </code>
	 *
	 * @param string $key The version key
	 * @return array The version components
	 * @since 3.6.0
	 * @access public
	 * @static
	 */
	public static function checkVersionKey($key) {
		if ($key) {
			$url = LICENSE_SERVER.'?key='.rawurlencode($key);

			try {
				$versionData = new SimpleXMLElement(file_get_contents(LICENSE_SERVER.'?key='.rawurlencode($key)));
			} catch (Exception $e) {
				throw new EfrontSystemException(_ERRORCONNECTINGTOLICENSESERVER, EfrontSystemException::ERROR_CONNECT_SERVER);
			}

			if ($versionData->status > 0) {
				throw new EfrontSystemException(_SETTINGKEYFAILEDWITHCODE.' '.$versionData->status.': '.$versionData->message, EfrontSystemException::INVALID_VERSION_KEY);
			}

			return (array)$versionData;
		} else {
			//throw new EfrontSystemException(_INVALIDVERSIONKEY.': '.$key, EfrontSystemException::INVALID_VERSION_KEY);
			return array();
		}
	}

	/**
	 * Set version key
	 *
	 * Sets the current version based on the key provided. The key is checked for
	 * validity using checkVersionKey()
	 * <br/>Example:
	 * <code>
	 * $key = 'version_key';
	 * $result = EfrontSystem :: setVersionKey($key);
	 * </code>
	 *
	 * @param string $key The version key
	 * @return boolean True if the key was successfully set, false otherwise
	 * @since 3.6.0
	 * @access public
	 * @static
	 */
	public static function setVersionKey($key) {
		if (!$key || !eF_checkParameter($key, 'alnum')) {
			throw new EfrontSystemException(_INVALIDVERSIONKEY.': '.$key, EfrontSystemException::INVALID_VERSION_KEY);
		}

		//$versionData = eF_checkVersionKey($key);
		$versionData = self :: checkVersionKey($key);

		if (G_VERSIONTYPE != $versionData['type']) {
			throw new EfrontSystemException(_KEYISNOTFORTHISEDITION, EfrontSystemException::INVALID_VERSION_KEY);
		}
		if ((!$versionData['users']  || !eF_checkParameter($versionData['users'], 'int')) ||
		(!$versionData['type']   || !isset($versionData['type'])) ||
		(!$versionData['serial'] || !eF_checkParameter($versionData['serial'], 'int'))) {
			throw new EfrontSystemException(_INVALIDVERSIONKEY.': '.$key, EfrontSystemException::INVALID_VERSION_KEY);
		}
		//debug();
		EfrontConfiguration :: setValue('version_key',    $key);
		EfrontConfiguration :: setValue('version_users',  $versionData['users']);
		EfrontConfiguration :: setValue('version_serial', $versionData['serial']);
		EfrontConfiguration :: setValue('version_type',   $versionData['type']);
		EfrontConfiguration :: setValue('version_activated', time());
		EfrontConfiguration :: setValue('version_upgrades',  $versionData['upgrades']);
		//EfrontConfiguration :: setValue('version_paypal', $versionData['paypal']);
		//EfrontConfiguration :: setValue('version_hcd',    $versionData['hcd']);

		// Going to educational version: check the existence of lesson and course skills
		if ($versionData['type'] == "educational") {
			eF_insertAutoLessonCourseSkills();
		}

		return true;
	}

	/**
	 * Delete version key
	 *
	 * This function deletes the currently stored version key, thus setting the version to
	 * "unregistered".
	 * <br/>Example:
	 * <code>
	 * EfrontSystem :: deleteVersionKey();
	 * </code>
	 *
	 * @since 3.6.0
	 * @access public
	 * @static
	 */
	public static function deleteVersionKey() {
		EfrontConfiguration :: setValue('version_key', '');
		EfrontConfiguration :: setValue('version_users', '');
		EfrontConfiguration :: setValue('version_serial', '');
		EfrontConfiguration :: setValue('version_type', '');
		//EfrontConfiguration :: setValue('version_paypal', '');
		//EfrontConfiguration :: setValue('version_hcd', '');
	}

	/**
	 * Print a default error message
	 *
	 * This function prints an error message. It is used for system errors, when any other chance of normally displaying an error is lost!
	 *
	 * @param string $message The error message
	 * @return string The HTML code of the formatted message
	 * @since 3.6.0
	 * @access public
	 * @static
	 */
	public static function printErrorMessage($message) {
		$str = '
	    <html>
	    <body>
	    <head>
	    <meta http-equiv = "Content-Type"     content = "text/html; charset = utf-8">
	    </head>
	    <style>
	    .singleMessage{width:100%;font-family:trebuchet ms;font-size:14px;border:1px solid red;background-color:#ffcccc;margin-top:10px}
	    .singleMessage td{padding:10px;}
	    .singleMessage td:first-child{width:1%}
	    </style>
	    <table class = "singleMessage">
	    	<tr><td><img src = "'.G_SERVERNAME.'/themes/default/images/32x32/warning.png" alt = "Failure" title = "Failure"></td>
	    		<td><div style = "font-size:16px;font-weight:bold">An error occured:</div><div>'.$message.'</div></tr>
	    </table>
	    </body>
	    </html>
	    ';

		return $str;
	}

	public static function exportToXls($data, $file = false, $alignments = array()) {
		require_once 'Spreadsheet/Excel/Writer.php';

		$workBook = new Spreadsheet_Excel_Writer($file);
		$workBook -> setTempDir(G_UPLOADPATH);
		$workBook -> setVersion(8);

		$workSheet = & $workBook -> addWorksheet('info');
		$workSheet -> setInputEncoding('utf-8');

		$columnIndex = 0;

		foreach (current($data) as $key => $value) {
			$maxColumnWidths[$columnIndex][] = mb_strlen($key);
			$alignments[$columnIndex] ? $align = $alignments[$columnIndex] : $align = 'left';
			$workSheet -> write(0, $columnIndex++, $key, $workBook -> addFormat(array('HAlign' => $align, 'Size' => 11, 'Bold' => 1)));
		}

		$rowIndex = 1;

		foreach ($data as $rowData) {
			$columnIndex = 0;
			foreach ($rowData as $cell) {
				$maxColumnWidths[$columnIndex][] = mb_strlen($cell);
				if ($alignments[$columnIndex]) {
					$align = $alignments[$columnIndex];
					$workSheet -> write($rowIndex, $columnIndex++, $cell, $workBook -> addFormat(array('HAlign' => $align)));
				} else {
					$workSheet -> write($rowIndex, $columnIndex++, $cell);
				}
			}
			$rowIndex++;
		}

		foreach ($maxColumnWidths as $columnIndex => $widths) {
			$workSheet -> setColumn($columnIndex, $columnIndex, max($widths) + 2);
		}
		$workBook -> close();

		if (!$file) {
			$workBook -> send('export.xls');
		}
	}

	public static function exportToCsv($data, $download = false, $name = "data.csv") {		
		$currentUser = EfrontUserFactory::factory($_SESSION['s_login']);
		$fp = fopen($currentUser->getDirectory().$name, 'w');
		foreach ($data as $fields) {
			fputcsv($fp, $fields);
		}
		fclose($fp);
		$file = new EfrontFile($currentUser->getDirectory().$name);
		if ($download) {
			$file -> sendFile(true);
		} else {
			return $file;
		}
	}
	
	/**
	 * Return system logo
	 *
	 * This function is used to return the system logo. If it does not exist, the default logo is returned
	 * @return EfrontFile The logo file object
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getSystemLogo() {
		try {
		    if ($GLOBALS['configuration']['use_logo'] == 2 && is_file(G_CURRENTTHEMEURL.'images/logo/logo.png')) {
		    	$logoFile = new EfrontFile(G_CURRENTTHEMEPATH.'images/logo/logo.png');
		    } else if ($GLOBALS['configuration']['use_logo'] > 0) {		//meaning that either we have 'use site logo' (1) or 'use theme logo' (2) but that does not exist
		    	$logoFile = new EfrontFile($GLOBALS['configuration']['logo']);   	
		    } else {
		    	$logoFile = new EfrontFile(G_DEFAULTIMAGESPATH."logo.png");   	
		    }
		} catch (EfrontFileException $e) {
			$logoFile = new EfrontFile(G_DEFAULTIMAGESPATH."logo.png");   	
		}	
		return $logoFile;
	}

	public static function switchLessonReportingMode($mode) {
		if ($GLOBALS['configuration']['time_reports'] != $mode && $mode == 1) {
			//step one: Read all times from the user_times table, per user,lesson and unit
			$data = $totals = array();
			$result = eF_getTableData("user_times", "users_LOGIN, entity_ID, lessons_ID, time", "entity = 'unit'");
			foreach ($result as $value) {
				if (isset($totals[$value['users_LOGIN']][$value['lessons_ID']][$value['entity_ID']])) {
					$totals[$value['users_LOGIN']][$value['lessons_ID']][$value['entity_ID']] += $value['time'];
				} else {
					$totals[$value['users_LOGIN']][$value['lessons_ID']][$value['entity_ID']] = $value['time'];
				}
			}

			//step 2: read all current time entries in the users_to_content table
			$result = eF_getTableData("users_to_content", "users_LOGIN, content_ID, lessons_ID, total_time");
			foreach ($result as $value) {
				$existing[$value['users_LOGIN']][$value['lessons_ID']][$value['content_ID']] = $value['total_time'];
			}
			
			//step 3: Populate the users_to_content table with the data from the user_times table, or update if a value already exist (overwriting it).			
			foreach ($totals as $user=>$lesson) {
				foreach ($lesson as $lessonId => $content) {
					foreach ($content as $contentId=>$seconds) {
						if (isset($existing[$user][$lessonId][$contentId])) {
							eF_updateTableData("users_to_content", array("total_time" => $seconds), "users_LOGIN='$user' and content_ID=$contentId and lessons_ID=$lessonId");
						} else {
							$data[] = array("users_LOGIN" => $user, "content_ID" => $contentId, "lessons_ID" => $lessonId, "total_time" => $seconds);
						}
					}
				}
			}
			eF_insertTableDataMultiple("users_to_content", $data);

			//step 4: Read the lesson (but not unit) times from the user_times table
			$data = $totals = array();
			$result = eF_getTableData("user_times", "users_LOGIN, entity_ID, time", "entity = 'lesson'");
			foreach ($result as $value) {
				if (isset($totals[$value['users_LOGIN']][$value['entity_ID']])) {
					$totals[$value['users_LOGIN']][$value['entity_ID']] += $value['time'];
				} else {
					$totals[$value['users_LOGIN']][$value['entity_ID']] = $value['time'];
				}
			}
			//step 5: Populate the users_to_content table with the plain lesson times, using null as a contentId
			
			foreach ($totals as $user=>$lesson) {
				foreach ($lesson as $lessonId => $seconds) {
					$data[] = array("users_LOGIN" => $user, "content_ID" => null, "lessons_ID" => $lessonId, "total_time" => $seconds);
				}
			}			
			
			eF_deleteTableData("users_to_content", "content_ID is null or content_ID=0");	//empty previous entries
			eF_insertTableDataMultiple("users_to_content", $data);
			
		}
	}
	
	public static function getSpaceUsage() {
		$total_size = $total_files= 0;
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(G_ROOTPATH), RecursiveIteratorIterator::SELF_FIRST);
    	foreach($objects as $name => $object){
    		if ($object->isFile()) {
    			$total_size +=$object->getSize();
    			$total_files ++;
    		}
    	}
		return array(round($total_size/(1024*1024)), $total_files);
	}
}


