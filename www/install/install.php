<?php
/**
 * Installation file
 */
define("G_VERSIONTYPE_CODEBASE", "community");
define("G_VERSIONTYPE", "community");
define("G_BUILD", "18013");

session_cache_limiter('none');          //Initialize session
session_start();

Installation :: setErrorReporting();
//error_reporting( E_ALL );ini_set("display_errors", true); 		//Uncomment this to get a full list of errors

/*The inclusion directory*/
$path = "../../libraries/";

//Read current version from sample_config file
preg_match("/define\('G_VERSION_NUM', '(.*)'\);/", file_get_contents("sample_config.php"), $matches);
define("G_VERSION_NUM", $matches[1]);

define("PREPROCESSED", false);#cpp#ifdef COMMUNITY
#cpp#endif


$versionTypes = array('educational'  => 'Educational',
                      'enterprise'   => 'Enterprise',
                      'standard'     => 'Community++',
                      'community'    => 'Community');

/*Disable output buffering during installation for better error handling*/
define("NO_OUTPUT_BUFFERING", true);

//Set some ini properties we need
ini_set("display_errors", true);
ini_set('include_path', $path.'../PEAR/');
ini_get("max_execution_time") < 120 ? ini_set("max_execution_time", "120") : null;

//It is imperative that the smarty directory is writable in order to continue
if (!is_writable($path.'smarty/themes_cache')) {
	echo Installation :: printErrorMessage("Directory <b>".realpath($path.'smarty/themes_cache')."</b> must be writable by the server in order to continue");
	exit;
}

//Check whether we are on http or https
isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? $protocol = 'https' : $protocol = 'http';
//Set the servername
define("G_SERVERNAME", dirname(dirname($protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'])).'/');

//Check if smarty and language file exist, and halt program execution if it is not present
if (is_file($path."smarty/smarty_config.php") && is_file($path."language/lang-english.php.inc")) {
	/**The default theme path*/
	define("G_THEMESPATH", str_replace("\\", "/", dirname(dirname(dirname(__FILE__)))."/www/themes/"));
    /** The default theme path*/
    define("G_DEFAULTTHEMEPATH", G_THEMESPATH."default/");
    /** The default theme url*/
    define("G_DEFAULTTHEMEURL", "themes/default/");

    $currentTheme = "efront2013";
	/**The current theme path*/
	define("G_CURRENTTHEMEPATH", G_THEMESPATH.$currentTheme."/");
	/**The current theme url*/
	define("G_CURRENTTHEMEURL", "themes/".$currentTheme."/");
	/**The current theme css url*/
	define("G_CURRENTTHEMECSS", G_CURRENTTHEMEURL."css/css_global.css");
	/**The current theme cache path*/
	define("G_CURRENTTHEMECACHE", dirname(dirname(dirname(__FILE__)))."/libraries/smarty/themes_cache/".$currentTheme."/");

	/**The smarty libraries*/
	require_once $path."smarty/libs/Smarty.class.php";
	require_once $path."smarty/smarty_config.php";
	/**The default language file*/
	require_once $path."language/lang-english.php.inc";
} else {
	echo Installation :: printErrorMessage("Some files are missing, installation cannot continue");
	exit;
}

//If we asked for unattended installation, there must be a 2nd parameter with the configuration details or performing an upgrade
if (isset($_GET['unattended']) && !isset($_GET['upgrade']) && (!isset($_GET['config']) || !is_file(basename($_GET['config'])))) {
	unset($_GET['unattended']);
}

$smarty -> assign("T_VERSION_TYPE", $versionTypes[G_VERSIONTYPE]);
if (is_file($path."configuration.php")) {
	$smarty -> assign("T_INSTALLATION_OPTIONS", array(array('text' => 'Emergency restore', 'image' => "16x16/undo.png", 'href' => 'install/'.basename($_SERVER['PHP_SELF'])."?restore=1")));
	$smarty -> assign("T_CONFIGURATION_EXISTS", true);
}


if ((isset($_GET['step']) && $_GET['step'] == 1) || isset($_GET['unattended'])) {

	if (is_file('../php.ini') && !is_file('php.ini') && copy('../php.ini', 'php.ini') && !isset($_GET['unattended'])) {
		header("location:".$_SERVER['PHP_SELF']."?step=1".(isset($_GET['upgrade']) ? '&upgrade=1' : ''));
	}
	$exclude_normal = true;
	require_once $path."includes/check_status.php";
	Installation :: fix($settings_mandatory, 'local');
	if ($_GET['mode'] != 'none' && sizeof($settings_mandatory) > 0) {
		if (!$_GET['mode']) {
			Installation :: fix($settings_mandatory, 'local');
			header("location:".$_SERVER['PHP_SELF']."?step=1&mode=htaccess".(isset($_GET['upgrade']) ? '&upgrade=1' : ''));
		} else {
			Installation :: fix($settings_mandatory, 'htaccess');
			header("location:".$_SERVER['PHP_SELF']."?step=1&mode=none".(isset($_GET['upgrade']) ? '&upgrade=1' : ''));
		}
	} else if ($_GET['mode'] == 'none' && sizeof($settings_mandatory) > 0) {
		$message = 'The system tried to automatically fix the errors shown below by applying custom php.ini and .htaccess files, but was unable to. Please fix the following errors manually.';
	} else if ($_GET['mode'] == 'none') {
		$message = 'The system automatically changed some php parameters to meet installation prerequisites by applying a local php.ini and/or a .htaccess file';
		$message_type  = 'success';
	}

	if (!$install) {
		$smarty -> assign("T_MISSING_SETTINGS", true);
	}

}
if ((isset($_GET['step']) && $_GET['step'] == 2) || isset($_GET['unattended'])) {
	//unset session, if any
	//    unset($_SESSION);
	//    session_destroy();

	/** HTML_QuickForm Class*/
	require_once 'HTML/QuickForm.php';
	/** HTML_QuickForm Smarty renderer class*/
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	/**ADODB database abstraction class*/
	require_once($path.'adodb/adodb.inc.php');
	/**ADODB exceptions class*/
	require_once($path.'adodb/adodb-exceptions.inc.php');
	/**Various tools*/
	require_once($path.'tools.php');

	if ($_GET['upgrade']) {
		$currentVersion = Installation :: checkCurrentVersion();
		$smarty -> assign("T_CURRENT_VERSION", $currentVersion);
	}

	//Initialize ADODB
	$ADODB_CACHE_DIR  = $path."adodb/cache";
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	$form = new HTML_QuickForm("info_form", "post", $_SERVER['PHP_SELF']."?step=2".($_GET['upgrade'] ? '&upgrade=1': ''), "", "class = 'indexForm'", true);
	$form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');                   //Register this rule for checking user input with our function, eF_checkParameter
	
	$form -> addElement('select', 'db_type', null, array('mysql' => 'MySQL', 'mssql' => 'MSSQL'));
	$form -> addRule('db_type', 'Invalid database type', 'checkParameter', 'string');                //The database type can only be string and is mandatory
	$form -> setDefaults(array('db_type' => 'mysql'));
	$form -> freeze(array('db_type'));                                                               //Freeze this element, since it can't change for now

	$form -> addElement('text', 'db_host', null, 'class = "inputText"');
	$form -> addRule('db_host', 'The field "Database host" is mandatory', 'required', null, 'client');                //The database type can only be string and is mandatory

	$form -> addElement('text', 'db_user', null, 'class = "inputText"');
	$form -> addRule('db_user', 'The field "Database user" is mandatory', 'required', null, 'client');                //The database type can only be string and is mandatory
	$form -> addRule('db_user', 'Invalid database user', 'checkParameter', 'alnum');                //The database user can only be string

	$form -> addElement('password', 'db_password', null, 'class = "inputText"');

	$form -> addElement('text', 'db_name', null, 'class = "inputText"');
	$form -> addRule('db_name', 'The field "Database name" is mandatory', 'required', null, 'client');                //The database type can only be string and is mandatory
	$form -> addRule('db_name', 'Invalid database name', 'checkParameter', 'alnum');        //The database name can only be string

	$form -> addElement('text', 'db_prefix', null, 'class = "inputText"');
	$form -> addRule('db_prefix', 'Invalid database prefix', 'checkParameter', 'alnum');        //The database name can only be string

	if ($_GET['upgrade']) {
		$form -> addElement('text', 'old_db_name', null, 'class = "inputText"');
		$form -> addRule('old_db_name', 'The field "Upgrade from database" is mandatory', 'required', null, 'client');                //The database type can only be string and is mandatory
		$form -> addRule('old_db_name', 'Invalid database name', 'checkParameter', 'alnum_general');        //The database name can only be string

		$form -> addElement('checkbox', 'backup', null, null, 'style = "vertical-align:middle"');
	} else {
		$form -> addElement('text', 'admin_name', null, 'class = "inputText"');
		$form -> addRule('admin_name', 'The field "Administrator user name" is mandatory', 'required', null, 'client');
		$form -> addRule('admin_name', 'Invalid administrator user name', 'checkParameter', 'login');

		$form -> addElement('password', 'admin_password', null, 'class = "inputText"');
		$form -> addRule('admin_password', 'The field "Administrator password" is mandatory', 'required', null, 'client');

		$form -> addElement('text', 'admin_email', null, 'class = "inputText"');
		$form -> addRule('admin_email', 'The field "Administrator email" is mandatory', 'required', null, 'client');
		$form -> addRule('admin_email', 'Invalid administrator email', 'checkParameter', 'email');

	}

	$form -> addElement('submit', 'submit_form', $_GET['upgrade'] ? 'Upgrade' : 'Install', 'class = "flatButton"');
	$form -> addElement('submit', 'delete_form', 'Delete existing tables and retry', 'class = "flatButton"');

	$form -> setDefaults(array('db_host'     	=> 'localhost',    //$_SERVER['HTTP_HOST']
                               'db_user'     	=> 'efront',
                               'db_password' 	=> 'solaris',
                               'db_name'     	=> 'efront',
                               'db_prefix'	 	=> '',
                               'admin_name'  	=> 'admin',
    							'backup'		=> true,
                               'default_data'   => true));

	if ($_GET['upgrade']) {
		$form -> setDefaults($currentVersion);
		$form -> setDefaults(array('old_db_name' => $currentVersion['db_name']));
	}
	$form->disable_csrf = true;
	if (($form -> isSubmitted() && $form -> validate()) || isset($_GET['unattended'])) {

		try {
			if (function_exists('apc_clear_cache')) {
				apc_clear_cache('user');
			}

			$db = ADONewConnection($form -> exportValue('db_type'));                                    //Set Connection parameter to "mysql"
			if (isset($_GET['unattended'])) {
				if (isset($_GET['upgrade'])) {
					$values = $currentVersion;
					$values['old_db_name'] = $values['db_name'];
				} else {
					$contents = file(basename($_GET['config']));
					$values   = array();
					foreach ($contents as $value) {
						$value = explode("=", $value);
						$values[trim($value[0])] = trim($value[1]);
					}
				}
			} else {
				$values = $form -> exportValues();
			}
			
			if ($_GET['upgrade']) {
				
				$db -> NConnect($values['db_host'], $values['db_user'], $values['db_password'], $values['db_name']);
				$db -> Execute("SET NAMES 'UTF8'");
				try {
					$db -> Execute("truncate cache");
				} catch (Exception $e) {}    //If the table could not be emptied, it doesn't exist, which is ok

				$dbVersion = $db -> getCol("select value from configuration where name = 'database_version'");
				if (!empty($dbVersion)) {
					$dbVersion = $dbVersion[0];
				} else {
					$dbVersion = '3.5';
				}

				//Include old configuration file in order to perform the automatic backup, use database functions, etc
				require_once($path."configuration.php");
				
				if ($values['backup'] || isset($_GET['unattended'])) {
					$backupFile = EfrontSystem :: backup($values['db_name'].'_'.time().'.zip');    //Auto backup database
				}
				if (version_compare($dbVersion, '3.6.11') == -1) {
					Installation :: createTable('themes', $file_contents);
					
					
					//Get all the database tables, except for the temporary installation tables
					$result = $db -> Execute("show table status");                                              //Get the database tables
					while (!$result->EOF) {
						if (strpos($result -> fields['Name'], 'install_') !== 0) {
							$tables[] = $result -> fields['Name'];
						}
						$result -> MoveNext();
					}
					//We are upgrading onto the same database.
					if ($values['old_db_name'] == $values['db_name']) {
						$db -> NConnect($values['db_host'], $values['db_user'], $values['db_password'], $values['db_name']);
						$db -> Execute("SET NAMES 'UTF8'");
					
						//Delete old temporary installation tables
						foreach ($tables as $table) {
							try {
								$result = $db -> Execute("drop table install_$table");            //Delete temporary installation tables, if such exist
							} catch (Exception $e) {
							}    //If the table could not be deleted, it doesn't exist
						}
					
						//Create temporary tables with the 'install_' prefix
						foreach ($file_contents as $query) {
							//Apply the selected prefix to table names
							$matches = array();
							preg_match('/CREATE TABLE (\w+) .*/', $query, $matches);
							$queryCreate     = preg_replace('/CREATE TABLE (\w+) (.*)/', 'CREATE TABLE if not exists '.$values['db_prefix'].'$1 $2', $query);
							$queryCreateTemp = preg_replace('/(.*CREATE TABLE )(\w+)( .*)/', '$1 '.$values['db_prefix'].'install_$2 $3', $query);   //Use temporary installation tables
							try {
								$db -> Execute($queryCreateTemp);
								$db -> Execute($queryCreate);
								$newTables[] = $matches[1];
							} catch (Exception $e) {
								$failed_tables[] = $e -> msg;                                                 //Each failed query will not halt the execution, but will be recorded to this table
							}
						}
						if (isset($failed_tables)) {                                                        //If there were any errors, assign them to smarty to be displayed
							$smarty -> assign('T_FAILED_TABLES', 1);
							throw new Exception(implode(', ', $failed_tables));
						}
						$existingTables = array_diff($tables, $newTables);                                    //These are tables pre-existing in the database, which should remain intact
					
						$userProfile    = eF_getTableData("user_profile", "*");    //Get any additional user profile fields
					} else {
						//The inclusion of configuration.php triggered the creation of a new configuration file. Truncate it in the new database
						$result = $db -> Execute("truncate configuration");
					
						//Special handling of modules table
						$db -> NConnect($values['db_host'], $values['db_user'], $values['db_password'], $values['old_db_name']);
						$db -> Execute("SET NAMES 'UTF8'");
					
						$existingTables     = $db -> GetCol("show tables");
						$moduleTables       = array_diff($existingTables, $tables);
						$moduleTableQueries = array();
						foreach ($moduleTables as $table) {
							$result = $db -> execute("show create table ".$table);
							$moduleTableQueries[$table] = $result -> getAll();
							$tables[] = $table;
						}
						$userProfile    = eF_getTableData("user_profile", "*");    //Get any additional user profile fields
					
						$db -> NConnect($values['db_host'], $values['db_user'], $values['db_password'], $values['db_name']);
						$db -> Execute("SET NAMES 'UTF8'");
					
						//Delete old temporary installation tables
						foreach ($tables as $key => $table) {
							try {
								$result = $db -> Execute("drop table install_$table");            //Delete temporary installation tables, if such exist
							} catch (Exception $e) {
							}    //If the table could not be deleted, it doesn't exist
							if (preg_match("/^.*_view$/", $table)) {
								unset($tables[$key]);
							}
						}
					
						//Create missing tables in the target database
						foreach ($moduleTableQueries as $query) {
							if (isset($query[0]['Create Table'])) {
								$db -> Execute($query[0]['Create Table']);
							}
						}
						//For every table that already exists in the target database, we must empty otherwise we may end up with duplicate values
						$commonTables = array_intersect($existingTables, $tables);
						foreach ($commonTables as $table) {
							try {
								$db -> Execute("truncate table $table");
							} catch (Exception $e) {/*Do nothing, if for example it's views we are trying to truncate*/
							}
						}
					
					}
					
					for ($i = 0; $i < sizeof($userProfile); $i++) {
						$userProfile[$i]['mandatory']     ? $mandatory = "NOT NULL" : $mandatory = "NULL";
						$userProfile[$i]['default_value'] ? $default   = $userProfile[$i]['default_value'] : $default   = false;
						try {
							if ($values['old_db_name'] == $values['db_name']) {
								$db -> Execute("ALTER TABLE install_users ADD ".$userProfile[$i]['name']." varchar(255) ".$mandatory." DEFAULT '".$default."'");
							} else {
								$db -> Execute("ALTER TABLE users ADD ".$userProfile[$i]['name']." varchar(255) ".$mandatory." DEFAULT '".$default."'");
							}
						} catch (Exception $e) {
							$failed_updates[] = $e -> msg;
						}
					}
					
					
					unset($tables['userpage']); //deprecated table
					$upgradedTables = array();
					foreach ($tables as $table) {
						if ($values['old_db_name'] == $values['db_name']) {
							if (!in_array($table, $existingTables)) {
								//Installation :: updateDBTable($table, "install_".$table);
								if (Installation :: quickUpgrade($table)) {
									$upgradedTables[] = $table;
								}
							}
						} else {
							$oldDB = array('db_host' => $values['db_host'], 'db_user' => $values['db_user'], 'db_password' => $values['db_password'], 'db_name' => $values['old_db_name']);
							$newDB = array('db_host' => $values['db_host'], 'db_user' => $values['db_user'], 'db_password' => $values['db_password'], 'db_name' => $values['db_name']);
					
							Installation :: updateDBTable($table, $table, $oldDB, $newDB);
						}
					}
					
					//In any case, Restore connection to the normal database
					$GLOBALS['db'] -> NConnect($values['db_host'], $values['db_user'], $values['db_password'], $values['db_name']);
					$GLOBALS['db'] -> Execute("SET NAMES 'UTF8'");
					
					//The upgrade completed successfully, so delete old tables and rename temporary install_ tables to its original names
					if ($values['old_db_name'] == $values['db_name']) {
						foreach ($upgradedTables as $table) {
							if (!in_array($table, $existingTables)) {
								$db -> Execute("drop table $table");
								$db -> Execute("RENAME TABLE install_$table TO $table");
							}
						}
						foreach ($tables as $table) {
							if (!in_array($table, $existingTables)) {
								try {
									$db -> Execute("drop table install_$table");
								} catch (Exception $e) {
								}
							}
						}
					}
					
					if (version_compare($dbVersion, '3.6.7') == -1) {
						$courses = eF_getTableData("courses","*");
						foreach ($courses as $key => $value) {
							$options = unserialize($value['options']);
							if (!isset($options['certificate_export_method'])) {
								$options['certificate_export_method'] = 'rtf';
								eF_updateTableData('courses',array('options' => serialize($options)),'id='.$value['id']);
							}
						}
						EfrontTimes :: upgradeFromUsersOnline();
					}
					
					if (version_compare($dbVersion, '3.6.10') == -1) {
						$result = eF_getTableData("users_to_projects", "*");
						foreach ($result as $value) {
							if (isset($value['filename']) && $value['filename'] != '') {
								try {
									$file = new EfrontFile($value['filename']);
									if ($file['directory'] == G_UPLOADPATH.$value['users_LOGIN'].'/projects') {
										$projectDirectory = G_UPLOADPATH.$value['users_LOGIN'].'/projects/'.$value['projects_ID'].'/';
										if (!is_dir($projectDirectory)) {
											EfrontDirectory :: createDirectory($projectDirectory);
										}
										$file -> rename($projectDirectory.$file['physical_name']);
									}
								} catch (Exception $e) {
								}
									
							}
						}
							
						//change flv path with offset because of the tinymce 3.4.2
						$result = eF_getTableData("content", "*", "data like '%flvToPlay%'");
						foreach ($result as $value) {
							if (mb_strpos($value['data'], "flvToPlay=../../../../../") !== false) {
								$value['data'] = str_replace("flvToPlay=../../../../../", "flvToPlay=##EFRONTEDITOROFFSET##", $value['data']);
								eF_updateTableData("content", array('data' => $value['data']), "id=".$value['id']);
							}
						}
					}
					
					$options = EfrontConfiguration :: getValues();
					//This means that the version upgrading from is 3.5
					if ($dbVersion == '3.5') {
						//Try to restore custom blocks
						try {
							if ($options['custom_blocks']) {
								$basedir = G_EXTERNALPATH;
								if (!is_dir($basedir) && !mkdir($basedir, 0755)) {
									throw new EfrontFileException(_COULDNOTCREATEDIRECTORY.': '.$fullPath, EfrontFileException :: CANNOT_CREATE_DIR);
								}
					
								$blocks = unserialize($options['custom_blocks']);
								foreach ($blocks as $value) {
									$value['name'] = rand().time();                //Use a random name
									$block = array('name'   => $value['name'],
											'title'  => $value['title']);
									file_put_contents($basedir.$value['name'].'.tpl', $value['content']);
									isset($customBlocks) && sizeof($customBlocks) > 0 ? $customBlocks[] = $block : $customBlocks = array($block);
								}
								$currentSetTheme = new themes($GLOBALS['configuration']['theme']);
								$currentSetTheme -> layout['custom_blocks'] = $customBlocks;
								$currentSetTheme -> persist();
							}
						} catch (Exception $e) {
						}
						//Try to restore custom logo
						try {
							$logoFile = new EfrontFile($options['logo']);
							if (strpos($logoFile['path'], G_LOGOPATH) === false) {
								copy ($logoFile['path'], G_LOGOPATH.$logoFile['name']);
							}
						} catch (Exception $e) {
						}
						//Try to restore custom favicon
						try {
							if (strpos($faviconFile['path'], G_LOGOPATH) === false) {
								$faviconFile = new EfrontFile($options['logo']);
							}
							copy ($faviconFile['path'], G_LOGOPATH.$faviconFile['name']);
						} catch (Exception $e) {
						}
						//Try to restore paypalbusiness addres
						try {
							$result = eF_getTableData("paypal_configuration", "paypalbusiness");
							if (!empty($result)) {
								EfrontConfiguration :: setValue('paypalbusiness', $result[0]['paypalbusiness']);
							}
						} catch (Exception $e) {
						}
						//Reset certain version options
						try {
							if ($options['version_type'] == 'standard') {
								EfrontConfiguration :: setValue('version_type', 'community');
							}
						} catch (Exception $e) {
						}
					
						//Add default notifications to 3.5
						EfrontNotification::addDefaultNotifications();
					}
						
				}
				
				//Now upgrade to 3.6.12
				include ("upgrade.php");
				
				Installation :: createConfigurationFile($values, true);

				//the following lines remove some old editor files that prevent editor from loading in version 3.6
				$removedDir = array();
				$removedDir[] = G_ROOTPATH.'www/editor/tiny_mce/plugins/zoom';
				$removedDir[] = G_ROOTPATH.'www/editor/tiny_mce/plugins/flash';
				$removedDir[] = G_ROOTPATH.'www/editor/tiny_mce/plugins/devkit';
				$removedDir[] = G_ROOTPATH.'www/editor/tiny_mce/plugins/mathtype';
				$removedDir[] = G_ROOTPATH.'www/editor/tiny_mce/plugins/lessons_info';

				foreach ($removedDir as $key => $value) {
					if (is_dir($value)) {
						try {
							$directory = new EfrontDirectory($value);
							$directory -> delete();
						} catch (EfrontFileException $e) {}                                    //Don't stop on filesystem errors
					}
				}
				$fileSystemTree = new FileSystemTree(G_ROOTPATH.'www/editor/tiny_mce/plugins', true);
				foreach (new EfrontDirectoryOnlyFilterIterator($fileSystemTree -> tree) as $key => $value) {
					if (is_dir($key.'/jscripts')) {
						try {
							if ($value['name'] != 'preview' && $value['name'] != 'Jsvk') {
								$directory = new EfrontDirectory($key.'/jscripts');
								$directory -> delete();
							}
						} catch (EfrontFileException $e) {}                                    //Don't stop on filesystem errors
					}
				}
				try {
					$cacheTree = new FileSystemTree(G_THEMECACHE, true);
					foreach (new EfrontDirectoryOnlyFilterIterator($cacheTree -> tree) as $value) {
						$value -> delete();
					}
				} catch (Exception $e) {}

				EfrontConfiguration :: setValue('database_version', G_VERSION_NUM);

				if (!defined("PREPROCESSED") && $GLOBALS['configuration']['version_type'] != G_VERSIONTYPE) {
					EfrontConfiguration :: setValue('version_type', G_VERSIONTYPE);
					EfrontConfiguration :: setValue('version_users', '');
					EfrontConfiguration :: setValue('version_activated', '');
					EfrontConfiguration :: setValue('version_upgrades', '');
					EfrontConfiguration :: setValue('version_key', '');
				}
				EfrontConfiguration :: setValue('editor_type', 'tinymce_new');
				EfrontConfiguration :: setValue('phplivedocx_server', 'https://api.livedocx.com/1.2/mailmerge.asmx?WSDL');  //code for updating phplivedocx_server
				$defaultConfig = EfrontConfiguration :: getValues();
				$phplivedocxConfig = '<?php
define("PATH_ZF","'.G_ROOTPATH.'Zend/library/'.'");
define("USERNAME","'.$defaultConfig['phplivedocx_username'].'");
define("PASSWORD","'.$defaultConfig['phplivedocx_password'].'");
define("PHPLIVEDOCXAPI","'.$defaultConfig['phplivedocx_server'].'");
?>';
				try {
					if (!file_exists($path."phplivedocx_config.php") || is_writable($path."phplivedocx_config.php")) {
						file_put_contents($path."phplivedocx_config.php", $phplivedocxConfig);
					}
				} catch (Exception $e) {}

				foreach ($defaultConfig as $key => $value) {
					if (strpos($key, 'disable_') !== false) {
						$mode_key = str_replace("disable_", "mode_", $key);
						if (isset($defaultConfig[$mode_key])) {
							EfrontConfiguration :: setValue($mode_key, !$value);
							EfrontConfiguration::deleteValue($key);
						}
					}
					switch ($key) {
						case 'social_'.SOCIAL_FUNC_EVENTS: 
							EfrontConfiguration :: setValue("mode_social_events", $value);
							EfrontConfiguration::deleteValue($key);
						break;
						case 'social_'.SOCIAL_FUNC_SYSTEM_TIMELINES: 
							EfrontConfiguration :: setValue("mode_system_timeline", $value);
							EfrontConfiguration::deleteValue($key);
						break;
						case 'social_'.SOCIAL_FUNC_LESSON_TIMELINES: 
							EfrontConfiguration :: setValue("mode_lessons_timeline", $value);
							EfrontConfiguration::deleteValue($key);
						break;
						case 'social_'.SOCIAL_FUNC_PEOPLE: 
							EfrontConfiguration :: setValue("mode_func_people", $value);
							EfrontConfiguration::deleteValue($key);
						break;
						case 'social_'.SOCIAL_FUNC_COMMENTS: 
							EfrontConfiguration :: setValue("mode_func_comments", $value);
							EfrontConfiguration::deleteValue($key);
						break;
						case 'social_'.SOCIAL_FUNC_USERSTATUS: 
							EfrontConfiguration :: setValue("mode_func_userstatus", $value);
							EfrontConfiguration::deleteValue($key);
						break;
						case 'show_complete_org_chart':
							EfrontConfiguration :: setValue("mode_show_complete_org_chart", $value);
							EfrontConfiguration::deleteValue($key);
							break;
						case 'show_organization_chart':
							EfrontConfiguration :: setValue("mode_show_organization_chart", $value);
							EfrontConfiguration::deleteValue($key);
							break;
						case 'show_user_form':
							EfrontConfiguration :: setValue("mode_show_user_form", $value);
							EfrontConfiguration::deleteValue($key);
							break;
						case 'show_unassigned_users_to_supervisors':
							EfrontConfiguration :: setValue("mode_show_unassigned_users_to_supervisors", $value);
							EfrontConfiguration::deleteValue($key);
							break;
						case 'allow_users_to_delete_supervisor_files':
							EfrontConfiguration :: setValue("mode_allow_users_to_delete_supervisor_files", $value);
							EfrontConfiguration::deleteValue($key);
							break;
						case 'propagate_courses_to_branch_users':
							EfrontConfiguration :: setValue("mode_propagate_courses_to_branch_users", $value);
							EfrontConfiguration::deleteValue($key);
							break;
						case 'allow_direct_login':
							EfrontConfiguration :: setValue("mode_allow_direct_login", $value);
							EfrontConfiguration::deleteValue($key);
							break;
						case 'mod_rewrite_bypass':
							EfrontConfiguration :: setValue("mode_mod_rewrite_bypass", $value);
							EfrontConfiguration::deleteValue($key);
							break;							
						default: break;
					}
				}
				
				//Upgrade for 3.6.8's default/site/theme logo: If a logo is set, then set this as the 'site logo' and set 'use_logo' to 1 (which means 'use site logo')
				if ($GLOBALS['configuration']['logo'] && !$GLOBALS['configuration']['site_logo']) {
					EfrontConfiguration :: setValue('use_logo', 1);
					EfrontConfiguration :: setValue('site_logo', $GLOBALS['configuration']['logo']);
				}

				$defaultConfig['editor_type'] == 'tinymce_new' ? $editorDir = 'tiny_mce_new' : $editorDir = 'tiny_mce';
				try {
					$cacheEditor = new FileSystemTree(G_ROOTPATH.'www/editor/'.$editorDir, true);
					foreach (new EfrontFileOnlyFilterIterator($cacheEditor -> tree) as $key => $value) {
						if ($value['extension'] == 'gz') {
							$value -> delete();
						}
					}
				} catch (Exception $e) {}

				Installation :: addModules(true);
				EfrontStats :: createViews();

				if (!isset($_GET['unattended'])) {
					header("location:".$_SERVER['PHP_SELF']."?finish=1&upgrade=1");
					exit;
				}
			} else {

				//first, try to connect to the host
				$db -> NConnect($values['db_host'], $values['db_user'], $values['db_password']);
				//Check whether the db exists. If not, create it
				try {
					$db -> NConnect($values['db_host'], $values['db_user'], $values['db_password'], $values['db_name']);
					$db_exists = true;
				} catch (Exception $e) {
					$db -> Execute("create database ".$values['db_name']." DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");                          //Create the new database
					$db -> NConnect($values['db_host'], $values['db_user'], $values['db_password'], $values['db_name']);
				}
				if ($db_exists && $values['submit_form']) {					
					$smarty->assign("T_FAILED_TABLES", true);
					throw new Exception ('A database with this name already exists. If you continue, all of its tables will be deleted');
				}
				if ($values['db_type'] == 'mysql') {
					$db -> Execute("SET NAMES 'UTF8'");
				}
				
				$GLOBALS['db'] -> Execute("set foreign_key_checks=0");

				foreach (explode(";\n", str_replace("\r\n", "\n", file_get_contents(G_VERSIONTYPE.'.sql'))) as $command) {
					if (trim($command)) {
						$GLOBALS['db'] -> execute(trim($command));
					}
				}
				$GLOBALS['db'] -> Execute("truncate cache");
				$GLOBALS['db'] -> Execute("set foreign_key_checks=1");
				
				//Create the file libraries/configuration.php
				Installation :: createConfigurationFile($values);

				/**Include the file configuration.php*/
				require_once($path."configuration.php");
				if (stripos(php_uname(), 'windows') !== false) {
					EfrontConfiguration :: setValue('file_encoding', 'UTF7-IMAP');
				}

				EfrontConfiguration :: setValue('version_type', G_VERSIONTYPE);
				EfrontConfiguration :: setValue('version_users', '');
				EfrontConfiguration :: setValue('version_activated', '');
				EfrontConfiguration :: setValue('version_upgrades', '');
				EfrontConfiguration :: setValue('version_key', '');

				EfrontConfiguration :: setValue('time_zone', date_default_timezone_get());
				
				$defaultConfig = EfrontConfiguration :: getValues();
				$phplivedocxConfig = '<?php
define("PATH_ZF","'.G_ROOTPATH.'Zend/library/'.'");
define("USERNAME","'.$defaultConfig['phplivedocx_username'].'");
define("PASSWORD","'.$defaultConfig['phplivedocx_password'].'");
define("PHPLIVEDOCXAPI","'.$defaultConfig['phplivedocx_server'].'");
?>';
				file_put_contents($path."phplivedocx_config.php", $phplivedocxConfig);

				eF_updateTableData("users", array('email' => $values['admin_email'], 'password' => EfrontUser::createPassword($values['admin_password']), 'last_login' => '0'));
				eF_updateTableData("users", array('login' => $values['admin_name']), "id=1");
				eF_updateTableData("courses", array('created' => time()));
				eF_updateTableData("courses", array('created' => time(), 'creator_LOGIN' => $values['admin_name']));
				eF_updateTableData("lessons", array('created' => time(), 'creator_LOGIN' => $values['admin_name']));
				eF_updateTableData("users_to_courses", array('from_timestamp' => time()));
				eF_updateTableData("users_to_lessons", array('from_timestamp' => time()));
				eF_deleteTableData("logs", "");
				eF_deleteTableData("events", "");
				
				EfrontConfiguration::setValue("database_version", G_VERSION_NUM);
				EfrontConfiguration::setValue("system_Email", $values['admin_email']);
				
				$file   = new EfrontFile(EfrontDirectory :: normalize(getcwd()).'/lessons.zip');
				$newFile = $file->copy(G_LESSONSPATH, true);
				$newFile->uncompress();
				$newFile->delete();
				
				if (G_VERSIONTYPE == 'community') { #cpp#ifdef COMMUNITY
					$modulesToRemove[] = 'content_reports';
					$modulesToRemove[] = 'course_reports';
					$modulesToRemove[] = 'fuze_meetings';
					$modulesToRemove[] = 'training_reports';
				} #cpp#endif
				if (G_VERSIONTYPE != 'enterprise') { #cpp#ifndef ENTERPRISE
					$modulesToRemove[] = 'branch_reports';
					$modulesToRemove[] = 'jobs_manager';
				} #cpp#endif
				
				if (is_file('post_install.php')) {
					include('post_install.php');
					runPostInstallationFunctions();
				}
				EfrontStats::createViews();

				if (!isset($_GET['unattended'])) {
					header("location:".$_SERVER['PHP_SELF']."?finish=1");
					exit;
				}

			}


		} catch (Exception $e) {
			Installation::handleInstallationExceptions($e);
		}
	}

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

	$renderer->setRequiredTemplate(
       '{$html}{if $required}
            &nbsp;<span class = "formRequired">*</span>
        {/if}'
        );

        $form -> setJsWarnings('The following errors occured:', 'Please correct the above errors');
        $form -> setRequiredNote('* Denotes mandatory fields');
        $form -> accept($renderer);

        $smarty -> assign('T_DATABASE_FORM', $renderer -> toArray());

}
if (isset($_GET['finish']) || isset($_GET['unattended'])) {
	
	if (isset($_GET['unattended'])) {
		if ($_GET['ajax']) {
			echo json_encode(array('status' => 1, 'message' => 'Successfully completed Unattended upgrade'));
			EfrontSystem :: unlockSystem();
		} else {
			header("location:".G_SERVERNAME."index.php?delete_install=1");
		}
	} else {
		session_destroy();
		unset($_SESSION);
	}
}

if (isset($_GET['restore'])) {
	try {
		$path = "../../libraries/";
		try {
			if (is_file($path."configuration.php")) {
				require_once($path."configuration.php");
			} else {
				echo Installation :: printErrorMessage("You must have a valid configuration file for the emergency restore to work");
				exit;
			}
		} catch (Exception $e) {
			$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
			$message      = $e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
			$message_type = 'failure';
		}

		foreach($it = new DirectoryIterator(G_BACKUPPATH) as $key => $value) {
			if (!$value -> isDir() && pathinfo($value -> getFileName(), PATHINFO_EXTENSION) == 'zip') {
				$files[] = basename($value);
			}
		}
		$smarty -> assign("T_BACKUP_FILES", $files);

		if (isset($_GET['file']) && in_array($_GET['file'], $files)) {
			EfrontSystem :: restore(G_BACKUPPATH.$_GET['file']);    //Auto backup database
			$message      = "The restoring procedure completed successfully";
			$message_type = "success";
		}
	} catch (Exception $e) {
		Installation::handleInstallationExceptions($e);
	}
}



$loadScripts = array('EfrontScripts',
					 'scriptaculous/prototype',
					 'scriptaculous/scriptaculous',
					 'scriptaculous/effects',
                     'efront_ajax');

$smarty -> assign("T_HEADER_LOAD_SCRIPTS", implode(",", array_unique($loadScripts)));                    //array_unique, so it doesn't send duplicate entries
$smarty -> assign("T_MESSAGE", $message);
$smarty -> assign("T_MESSAGE_TYPE", $message_type);

$smarty -> load_filter('output', 'eF_template_applyImageMap');
$smarty -> load_filter('output', 'eF_template_applyThemeToImages');
$smarty -> display ("install/install.tpl");


/**
 *
 * @author user
 *
 */
class InstallationException extends Exception
{
	const DATABASE_NOT_EMPTY = 1;
}

/**
 *
 * @author user
 *
 */
class Installation
{

	/**
	 *
	 * @param $values
	 * @return unknown_type
	 */
	public static function createConfigurationFile($values, $upgrade = false) {

		$patterns = array("/(define\(['\"]G_DBTYPE['\"], ['\"]).*(['\"]\);)/",
	                      "/(define\(['\"]G_DBHOST['\"], ['\"]).*(['\"]\);)/",
	                      "/(define\(['\"]G_DBUSER['\"], ['\"]).*(['\"]\);)/",
	                      "/(define\(['\"]G_DBPASSWD['\"], ['\"]).*(['\"]\);)/",
	                      "/(define\(['\"]G_DBNAME['\"], ['\"]).*(['\"]\);)/",
	                      "/(define\(['\"]G_DBPREFIX['\"], ['\"]).*(['\"]\);)/",
	                      "/(define\(['\"]G_VERSION_NUM['\"], ['\"]).*(['\"]\);)/");

		dirname(dirname($_SERVER['PHP_SELF'])) != '.' ? $servername = dirname(dirname($_SERVER['PHP_SELF'])) : $servername = '';
		$servername = str_replace("\\", "/", $servername);

		$replacements = array('${1}'.$values['db_type'].'${2}',
	            			  '${1}'.$values['db_host'].'${2}',
	            			  '${1}'.$values['db_user'].'${2}',
	            			  '${1}'.$values['db_password'].'${2}',
	            			  '${1}'.$values['db_name'].'${2}',
	            			  '${1}'.$values['db_prefix'].'${2}',
	                          '${1}'.G_VERSION_NUM.'${2}');


		if ($upgrade) {
			$file_contents = file_get_contents($GLOBALS['path'].'configuration.php');                            //Load sample configuration file
			$new_file_contents = preg_replace("/(define\(['\"]G_SERVERNAME['\"], ['\"]).*(['\"]\);)/", "define('G_OFFSET', '".rtrim($servername, "/").'/'."');", $file_contents, -1, $count);
		} else {
			$file_contents = file_get_contents('sample_config.php');                            //Load sample configuration file
			$new_file_contents = preg_replace("/define\('G_OFFSET', ''\)/", "define('G_OFFSET', '".rtrim($servername, "/").'/'."')", $file_contents, -1, $count);
		}
		
		$new_file_contents = preg_replace($patterns, $replacements, $new_file_contents, -1, $count);    //Replace sample settings with current settings
		if (!file_put_contents($GLOBALS['path'].'configuration.php', $new_file_contents)) {
			throw new Exception("The configuration file could not be created");
		}
	}



	/**
	 *
	 * @return unknown_type
	 */
	public static function checkCurrentVersion() {
		if (is_file($GLOBALS['path'].'configuration.php')) {
			$file_contents = file_get_contents($GLOBALS['path'].'configuration.php');                            //Load existing configuration file

			preg_match('/define\(["\']G_DBTYPE["\'], ["\'](.*)["\']\);/',   $file_contents, $type);
			preg_match('/define\(["\']G_DBHOST["\'], ["\'](.*)["\']\);/',   $file_contents, $host);
			preg_match('/define\(["\']G_DBUSER["\'], ["\'](.*)["\']\);/',   $file_contents, $user);
			preg_match('/define\(["\']G_DBPASSWD["\'], ["\'](.*)["\']\);/', $file_contents, $password);
			preg_match('/define\(["\']G_DBNAME["\'], ["\'](.*)["\']\);/',   $file_contents, $name);
			//preg_match('/define\(["\']G_VERSION_NUM["\'], ["\'](.*)["\']\);/', $file_contents, $name);
			$currentVersion = array('db_type'	  => $type[1],
            						'db_host'     => $host[1],
                                    'db_user'     => $user[1],
                                    'db_password' => $password[1],
                                    'db_name'     => $name[1]);
		} else {

		}

		return $currentVersion;
	}

	/**
	 * Automatically fix PHP settings errors
	 *
	 * This function is used to automatically apply a suitable php.ini or .htaccess file to correct the most
	 * usual PHP settings errors: session.save_path, magic_quotes_gpc, register_globals.
	 *
	 * @param array $settings The settings that need to be fixed
	 * @param string $mode Can be 'local', which means that the fix will be in a local php.ini file, or 'htaccess', which means that the fix will be performed with a .htaccess file
	 * @since 3.6.0
	 * @access public
	 * @static
	 */
	public static function fix($settings, $mode = 'local') {
		$sessionSavePathDir = '';
		if ($settings['session.save_path'] && function_exists('sys_get_temp_dir') && is_writable(sys_get_temp_dir())) {        //If the session.save_path does not exist, set it to system's default temp dir
			$sessionSavePathDir = trim(sys_get_temp_dir(), '\\');
		} else if (!function_exists('sys_get_temp_dir') || (function_exists('sys_get_temp_dir') && !is_writable(sys_get_temp_dir()))) {
			$rootDir = dirname(dirname(dirname(__FILE__)));
			if (is_dir(dirname($rootDir).'/tmp') && is_writable(dirname($rootDir).'/tmp')) {
				$sessionSavePathDir = dirname($rootDir).'/tmp';
			} else if (is_writable($rootDir.'/upload')) {
				$sessionSavePathDir = $rootDir.'/upload';
			}
		}
		//When we need to apply a local php.ini file, even if session.save_path is correctly configured in the system, after applying
		//the php.ini file, for some reason the session.save_path goes away. So, whenever we are in this function, we *must*
		//include the session.save_path inside the local php.ini file
		elseif (!$settings['session.save_path']) {
			if (ini_get('session.save_path') && is_writable(ini_get('session.save_path'))) {
				$sessionSavePathDir = ini_get('session.save_path');
			} else if (function_exists('sys_get_temp_dir') && is_writable(sys_get_temp_dir())) {
				$sessionSavePathDir = trim(sys_get_temp_dir(), '\\');
			} else if (!function_exists('sys_get_temp_dir') || (function_exists('sys_get_temp_dir') && !is_writable(sys_get_temp_dir()))) {
				$rootDir = dirname(dirname(dirname(__FILE__)));
				if (is_dir(dirname($rootDir).'/tmp') && is_writable(dirname($rootDir).'/tmp')) {
					$sessionSavePathDir = dirname($rootDir).'/tmp';
				} else if (is_writable($rootDir.'/upload')) {
					$sessionSavePathDir = $rootDir.'/upload';
				}
			}
				
		}		
		
		//local mode: create and apply a local php.ini file
		if ($mode == 'local') {
			$localPhpIniString = "";
			if ($sessionSavePathDir) {
				$localPhpIniString .= "session.save_path = \"".$sessionSavePathDir."\"\n";
			}
			if ($settings['magic_quotes_gpc']) {
				$localPhpIniString .= "magic_quotes_gpc = Off\n";
			}
			if ($settings['register_globals']) {
				$localPhpIniString .= "register_globals = Off\n";
			}

			file_put_contents("../php.ini", "\n".$localPhpIniString, FILE_APPEND);
			file_put_contents("php.ini", "\n".$localPhpIniString, FILE_APPEND);
			file_put_contents("../editor/tiny_mce/php.ini", $localPhpIniString, FILE_APPEND);
		} else if ($mode == 'htaccess') {
			$localHtaccess = "<IfModule mod_php5.c>
php_value magic_quotes_gpc Off
php_value register_globals Off
".($sessionSavePathDir ? "php_value session.save_path \"".$sessionSavePathDir."\"" : "")."
</IfModule>";
			file_put_contents("../.htaccess", $localHtaccess);
		}
	}


	/**
	 *
	 * @param $table
	 * @param $newTable
	 * @param $oldDB
	 * @param $newDB
	 * @return unknown_type
	 */
	public static function updateDBTable($table, $newTable, $oldDB = false, $newDB = false) {
		if ($oldDB && $newDB) {
			$GLOBALS['db'] -> NConnect($oldDB['db_host'], $oldDB['db_user'], $oldDB['db_password'], $oldDB['db_name']);
			$GLOBALS['db'] -> Execute("SET NAMES 'UTF8'");
		}

		try {
			$data   = eF_getTableData($table, "count(*)");
		} catch (Exception $e) {
			$limit = 0;
		}
		$unfold = 2000;
		$limit  = ceil($data[0]['count(*)'] / $unfold);
		if ($table != $newTable) {
			try {
				$GLOBALS['db'] -> Execute("truncate table $newTable");
			} catch (Exception $e) {}
		}
		if ($table == 'f_folders') {  //because of  UNIQUE(name, users_LOGIN) added there, we have to remove possible duplicates
			$dbVersion = $GLOBALS['db'] -> getCol("select value from configuration where name = 'database_version'");
			if (!empty($dbVersion)) {
				$dbVersion = $dbVersion[0];
			} else {
				$dbVersion = '3.5';
			}
			if (version_compare($dbVersion, '3.6.4') == -1) {
				$upgrade_f_folders = eF_getTableData("f_folders","*");
				foreach ($upgrade_f_folders  as $key => $value) {
					$usersToFolders[$value['users_LOGIN']][$value['name']][] = $value['id'];
				}
				foreach ($usersToFolders  as $login => $folder) {
					foreach ($folder  as $name => $arrayId) {
						if (sizeof($arrayId) > 1) {
							$maxId	=	max(array_values($arrayId));
							$arrayCut = array_diff($arrayId, array($maxId));
							eF_deleteTableData("f_folders","id IN (".implode(",", $arrayCut).")");
							eF_updateTableData("f_personal_messages",array('f_folders_ID' => $maxId), "f_folders_ID IN (".implode(",", $arrayCut).")");
						}
					}
				}
			}
		}
		for ($i = 0; $i < $limit; $i++) {
			if ($oldDB && $newDB) {
				$GLOBALS['db'] -> NConnect($oldDB['db_host'], $oldDB['db_user'], $oldDB['db_password'], $oldDB['db_name']);
				$GLOBALS['db'] -> Execute("SET NAMES 'UTF8'");
			}
			$data = eF_getTableData($table, "*", "", "'' limit $unfold offset ".($i*$unfold));
			//Special handling for glossary table that changed name
			if ($table == 'glossary_words') {
				if ($newTable != $table) {
					$newTable = 'install_glossary';
				} else {
					$newTable = 'glossary';
				}
				$table = 'glossary';
			}
			//Get the old database descriptions, it is used in updateDBData(), which however must be called after any connection to the new DB
			//so this line MUST be called before the new connection
			$table_fields = $GLOBALS['db'] -> GetCol("describe $table");

			if ($oldDB && $newDB) {
				$GLOBALS['db'] -> NConnect($newDB['db_host'], $newDB['db_user'], $newDB['db_password'], $newDB['db_name']);
				$GLOBALS['db'] -> Execute("SET NAMES 'UTF8'");

				$result = $GLOBALS['db'] -> getAll("describe $table");

			} else {
				$result = $GLOBALS['db'] -> getAll("describe install_$table");

			}
			//$result contains the new database descriptions. So both result and this code must be executed AFTER the new DB connection
			$fieldTypes = array();
			foreach ($result as $key => $value) {
				$fieldTypes[$value['Field']] = $value['Type'];
			}

			$data = self :: updateDBData($table, $data, $table_fields, $fieldTypes);
			if (sizeof($data) > 0) {
				$data = array_values($data);            //Reindex array, in case some values where removed
			}

			eF_insertTableDataMultiple($newTable, $data, false);
		}

		$GLOBALS['db'] -> Execute("drop table if exists glossary_words");
		$GLOBALS['db'] -> Execute("drop table if exists install_glossary_words");
	}


	/**
	 * Update all tables by copying each one of them
	 *
	 * @param array $table The table to upgrade
	 * @param array $data The table content
	 * @return array The upgraded data
	 */
	public static function updateDBData($table, $data, $table_fields, $fieldTypes) {
		$table_size   = sizeof($data);
		//Copy old stupid 'site moto' to correct 'site motto' to avoid hilarious confusions
		if ($table == 'configuration') {
			foreach ($data as $k => $v) {
				if ($v['name'] == 'site_moto') {
					$sitemotoKey  = $k;
				} elseif ($v['name'] == 'site_motto') {
					$sitemottoKey = $k;
				}
			}
			if ($sitemotoKey) {
				if (isset($sitemottoKey)) {
					$data[$sitemottoKey]['value'] = $data[$sitemotoKey]['value'];
				} else {
					$data[] = array('name' => 'site_motto', 'value' => $data[$sitemotoKey]['value']);
				}
				unset($data[$sitemotoKey]);
			}
		}

		for ($i = 0; $i < $table_size; $i++) {

			if ($table == 'search_keywords') {
				$data = array();
			} else if ($table == 'f_forums' || $table == 'f_topics') {
				if ($data[$i]['status'] == 'invisible') {
					$data[$i]['status'] = 3;
				} elseif ($data[$i]['status'] == 'locked') {
					$data[$i]['status'] = 2;
				} else {
					$data[$i]['status'] = 1;        //public
				}
			} else if ($table == 'themes') {
				$GLOBALS['db'] -> Execute("truncate themes");
			} else if ($table == 'rules') {
				if ($data[$i]['rule_type'] == 'hasnot_passed') {
					unset($data[$i]);
				}
			} else if ($table == 'questions') {
				unset($data[$i]['code']);        //Obsolete field
			} else if ($table == 'bookmarks') {
				unset($data[$i]['users_USER_TYPE']);    //Obsolete field
			} else if ($table == 'payments') {
				unset($data[$i]['paypal_data_ID']);    //Obsolete field
			} else if ($table == 'courses') {
				$data[$i]['created'] != '' OR $data[$i]['created'] = time();	//Set a creation date to a course, if it doesn't have one

				//Convert old table properties to options
				$options = unserialize($data[$i]['options']) OR $options = array(); //initialize the course options for the below operations
				!$data[$i]['certificate'] OR $options['certificate'] = $data[$i]['certificate'];
				!$data[$i]['auto_certificate'] OR $options['auto_certificate'] = $data[$i]['auto_certificate'];
				!$data[$i]['auto_complete'] OR $options['auto_complete'] = $data[$i]['auto_complete'];

				//isset($options['certificate_export_method']) OR $options['certificate_export_method'] = 'rtf';

				!$data[$i]['duration'] OR $options['duration'] = $data[$i]['duration'];
				$data[$i]['options'] = serialize($options);

				//Unset old and deprecated fields
				unset($data[$i]['certificate']);
				unset($data[$i]['auto_certificate']);
				unset($data[$i]['auto_complete']);
				unset($data[$i]['certificate_tpl_id']);
				unset($data[$i]['duration']);
				unset($data[$i]['certificate_tpl']);
				unset($data[$i]['from_timestamp']);
				unset($data[$i]['to_timestamp']);
				unset($data[$i]['shift']);

			} else if ($table == 'calendar') {
				if (isset($data[$i]['lessons_ID'])) {
					if ($data[$i]['lessons_ID']) {
						$data[$i]['foreign_ID'] = $data[$i]['lessons_ID'];
						$data[$i]['type'] 		= 'lesson';
					} else {
						$data[$i]['foreign_ID'] = 0;
						$data[$i]['type'] 		= '';
					}
					unset($data[$i]['lessons_ID']);
				}
				if (isset($data[$i]['private'])) {
					if ($data[$i]['private']) {
						$data[$i]['type'] = 'private';
					}
					unset($data[$i]['private']);
				}
			}

			//Convert any '' values inside fields that are now integers, to 0 (for example timestamps that used to be varchars)
			if (isset($data[$i])) {
				foreach ($data[$i] as $key => $value) {
					if (strpos($fieldTypes[$key], "int(") !== false && $value === '') {
						$data[$i][$key] = 0;
					}
				}
			}

			//Remove missing fields (usually deprecated fields no longer existing in current version)
			isset($data[$i]) ? $keys = array_keys($data[$i]) : $keys = array();
			$obsolete_fields = array_diff($table_fields, $keys);

			foreach ($obsolete_fields as $value) {
				unset($data[$i][$value]);
			}
		}

		return $data;
	}


	public static function quickUpgrade($table) {
		$oldTable = $GLOBALS['db'] -> GetAll("describe $table");
		$newTable = $GLOBALS['db'] -> GetAll("describe install_$table");
		$changed = false;
		if (sizeof($newTable) != sizeof($oldTable)) {
			$changed = true;
		}
		foreach ($oldTable as $key => $value) {
			if (array_diff($oldTable[$key], $newTable[$key]) || array_diff($newTable[$key], $oldTable[$key])) {
				$changed = true;
			}
		}
		$oldTableIndexes = $GLOBALS['db'] -> GetAll("show indexes from $table");
		$newTableIndexes = $GLOBALS['db'] -> GetAll("show indexes from install_$table");
		if (sizeof($newTableIndexes) != sizeof($oldTableIndexes)) {
			$changed = true;
		}
				
		if ($changed) {
			Installation :: updateDBTable($table, "install_".$table);
		}
		return $changed;
	}

	/**
	 * Create a database table
	 *
	 * This function is used in order to read the sql table definitions and pick one
	 * specific table creatoin declaration to execute.
	 *
	 * @param string $table The table to create
	 * @param $contents The sql definitions
	 * @return boolean True if the table already exists or was successfully created
	 * @since 3.6.0
	 * @access public
	 */
	public static function createTable($table, $file_contents) {

		if (!in_array('themes', $GLOBALS['db'] -> GetCol("show tables"))) {
			preg_match('/.*(CREATE TABLE '.$table.'.*;).*/sU', implode(";", $file_contents), $matches);
			if (sizeof($matches) == 2) {
				$GLOBALS['db'] -> Execute($matches[1]);
				return true;
			} else {
				return false;
			}
		} else {
			//The table already exists
			return true;
		}
	}

	/**
	 * Set error reporting for installation
	 *
	 * @since 3.6.0
	 * @access public
	 * @static
	 */
	public static function setErrorReporting() {
		if (!isset($_SESSION['error_level']) && !isset($_GET['debug'])) {
			//Set error level to display all except for notices
			error_reporting(E_ERROR);
		} else if ($_SESSION['error_level'] == 'warning') {
			error_reporting(E_ALL ^ E_NOTICE);
			ini_set("display_errors", true);
		} else if ($_SESSION['error_level'] == 'all' || isset($_GET['debug'])) {
			error_reporting(E_ALL);
			ini_set("display_errors", true);
		}
		if (isset($_GET['set_error_level'])) {
			if (!isset($_SESSION['error_level'])) {
				$_SESSION['error_level'] = 'warning';
			} else if ($_SESSION['error_level'] == 'warning') {
				$_SESSION['error_level'] = 'all';
			} else {
				unset($_SESSION['error_level']);
			}
			echo $_SESSION['error_level'];
			exit;
		}
	}

	/**
	 * Print a default error message
	 *
	 * This function prints an error message.
	 *
	 * @param string $message The error message
	 * @return string The HTML code of the formatted message
	 * @since 3.6.0
	 * @access public
	 * @static
	 */
	public static function printErrorMessage($message) {
		$str = '
	    <style>
	    .singleMessage{width:100%;font-family:trebuchet ms;font-size:14px;border:1px solid red;background-color:#ffcccc;margin-top:10px}
	    .singleMessage td{padding:10px;}
	    .singleMessage td:first-child{width:1%}
	    </style>
	    <table class = "singleMessage">
	    	<tr><td><img src = "../themes/default/images/32x32/warning.png" alt = "Failure" title = "Failure"></td>
	    		<td><div style = "font-size:16px;font-weight:bold">An error occured:</div><div>'.$message.'</div></tr>
	    </table>
	    ';

		return $str;
	}

	public static function handleInstallationExceptions($e) {
		if ($_GET['unattended']) {
			handleAjaxExceptions($e);
		} else {
			handleNormalFlowExceptions($e);
		}
	}

	/**
	 * Install modules
	 *
	 * @since 3.6.0
	 * @access public
	 * @static
	 */
	public static function addModules($upgrade = false) {
		$modulesToInstall = array(
				"administrator_tools",
				"bbb",
				"billboard",
				"blogs",
				"bootstrap",
				"chat",
				"crossword",
				"export_unit",
				"faq",
				"flashcards",
				"gift_aiken",
				"gradebook",
				"idle_users",
				"info_kiosk",
				"journal",
				"links",
				"outlook_invitation",
				"quick_mails",
				"quote",
				"rss",
				"security",
				"workbook",
				"youtube",
		);
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			$modulesToInstall[] = 'content_reports';
			$modulesToInstall[] = 'course_reports';
			$modulesToInstall[] = 'fuze_meetings';
			$modulesToInstall[] = 'training_reports';
		} #cpp#endif
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$modulesToInstall[] = 'branch_reports';
			$modulesToInstall[] = 'jobs_manager';
		} #cpp#endif


		$modulesList = eF_getTableData("modules", "*");
		foreach ($modulesList as $module) {
			$existingModules[] = $module['className'];
		}

		$filesystem  = new FileSystemTree(G_MODULESPATH, true);
		foreach (new EfrontNodeFilterIterator($filesystem -> tree) as $moduleDirectory => $value) {
			try {
				if (in_array(str_replace("module_", "", basename($moduleDirectory)), $modulesToInstall) && is_file($moduleDirectory.'/module.xml')) {

					$xml           = simplexml_load_file($moduleDirectory.'/module.xml');
					$className     = (string)$xml -> className;
					$className     = str_replace(" ", "", $className);
					$database_file = (string)$xml -> database;

					if (is_file($moduleDirectory.'/'.$className. ".class.php")) {
						require_once $moduleDirectory."/".$className.".class.php";

						if (class_exists($className)) {
							$module = new $className("administrator.php?ctg=module&op=".$className, $className);

							// Check whether the roles defined are acceptable
							$roles = $module -> getPermittedRoles();
							$roles_failure = 0;
							if (sizeof($roles) == 0) {
								throw new Exception(_NOMODULEPERMITTEDROLESDEFINED);
							} else {
								foreach ($roles as $role) {
									if ($role != 'administrator' && $role != 'student' && $role != 'professor') {
										throw new Exception(_NOMODULEPERMITTEDROLESDEFINED);
									}
								}
							}

							$fields = array('className'   => $className,
									'db_file'     => $database_file,
									'name'        => $className,
									'active'      => 0,
									'title'       => ((string)$xml -> title)?(string)$xml -> title:" ",
									'author'      => (string)$xml -> author,
									'version'     => (string)$xml -> version,
									'description' => (string)$xml -> description,
									'position'    => basename($moduleDirectory),
									'permissions' => implode(",", $module -> getPermittedRoles()));

							// Install module database
							if ($upgrade && in_array($className, $existingModules)) {
								if ($module -> onUpgrade()) {
									eF_updateTableData("modules", $fields, "className ='".$_GET['upgrade']."'");
								} else {
									throw new Exception(_MODULEDBERRORONUPGRADECHECKUPGRADEFUNCTION);
								}
							} else {
								if ($module -> onInstall()) {
									if ($className == 'module_security') {
										$fields['active'] = 1;		//Since 3.6.10, security module starts as activated (instead of rss)
									}
									eF_insertTableData("modules", $fields);
								} else {
									throw new Exception(_MODULEDBERRORONINSTALL);
								}
							}

						} else {
							throw new Exception('"'.$className .'" '. _MODULECLASSNOTEXISTSIN . ' ' .$moduleDirectory.'/'.$className.'.class.php');
						}
					} else {
						throw new Exception(_NOMODULECLASSFOUND . ' "'. $className .'" : '.$moduleDirectory);
					}
				}
			} catch (Exception $e) {/*Don't install any failed modules*/
			}

		}


	}
}

?>
