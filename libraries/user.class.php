<?php
/**
 * EfrontUser Class file
 *
 * @package eFront
 */

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

/**
 * User exceptions class
 *
 * @package eFront
 */
class EfrontUserException extends Exception
{
	const NO_ERROR		  = 0;
	const INVALID_LOGIN	 = 401;
	const USER_NOT_EXISTS   = 402;
	const INVALID_PARAMETER = 403;
	const USER_EXISTS	   = 404;
	const DATABASE_ERROR	= 405;
	const USER_FILESYSTEM_ERROR = 406;
	const INVALID_TYPE	  = 407;
	const ALREADY_IN		= 408;
	const INVALID_PASSWORD  = 409;
	const USER_NOT_HAVE_LESSON = 410;
	const WRONG_INPUT_TYPE  = 411;
	const USER_PENDING	  = 412;
	const TYPE_NOT_EXISTS   = 414;
	const MAXIMUM_REACHED   = 415;
	const RESTRICTED_USER_TYPE = 416;
	const USER_INACTIVE		= 417;
	const USER_NOT_LOGGED_IN = 418;
	const USER_NOT_HAVE_COURSE = 419;
	const GENERAL_ERROR	 = 499;
}


/**
 * Abstract class for users
 *
 * @package eFront
 * @abstract
 */
abstract class EfrontUser
{
	/**
	 * Percentage above which we notify the account holder for license reasons
	 */
	const NOTIFY_THRESHOLD = 0.8;
	
	/**
	 * A caching variable for user types
	 *
	 * @since 3.5.3
	 * @var array
	 * @access private
	 * @static
	 */
	private static $userRoles;

	/**
	 * The basic user types.
	 *
	 * @since 3.5.0
	 * @var array
	 * @access public
	 * @static
	 */
	public static $basicUserTypes = array('student', 'professor', 'administrator');

	/**
	 * The basic user types.
	 *
	 * @since 3.5.0
	 * @var array
	 * @access public
	 * @static
	 */
	public static $basicUserTypesTranslations = array('student' => _STUDENT, 'professor' => _PROFESSOR, 'administrator' => _ADMINISTRATOR);

	/**
	 * The user array.
	 *
	 * @since 3.5.0
	 * @var array
	 * @access public
	 */
	public $user = array();

	/**
	 * The user login.
	 *
	 * @since 3.5.0
	 * @var string
	 * @access public
	 */
	public $login = '';

	/**
	 * The user groups.
	 *
	 * @since 3.5.0
	 * @var string
	 * @access public
	 */
	public $groups = array();

	/**
	 * The user login.
	 *
	 * @since 3.5.0
	 * @var string
	 * @access public
	 */
	public $aspects = array();

	/**
	 * Whether this user authenitactes through LDAP.
	 *
	 * @since 3.5.0
	 * @var boolean
	 * @access public
	 */
	public $isLdapUser = false;

	/**
	 * The core_access sets where each user has access to
	 * @var array
	 * @since 3.5.0
	 * @access public
	 */
	public $core_access = array();

	/**
	 * Cache for modules
	 * @var array
	 * @since 3.6.1
	 * @access public
	 */
	protected static $cached_modules = false;

	/**
	 * Instantiate class
	 *
	 * This function instantiates a new EfrontUser sibling object based on the given
	 * login. If $password is set, then it verifies the given password against
	 * the stored one. Either the EfrontUserFactory may be used, or directly the
	 * EfrontX class.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');			//Use factory to instantiate user object with login 'jdoe'
	 * $user = EfrontUserFactory :: factory('jdoe', 'mypass');  //Use factory to instantiate user object with login 'jdoe' and perform password verification
	 * $user = new EfrontAdministrator('jdoe')				  //Instantiate administrator user object with login 'jdoe'
	 * </code>
	 *
	 * @param string $login The user login
	 * @param string $password An enrypted password to check for the user
	 * @since 3.5.0
	 * @access public
	 */
	function __construct($user, $password = false) {
		if (!eF_checkParameter($user['login'], 'login')) {
			throw new EfrontUserException(_INVALIDLOGIN.': '.$user['login'], EfrontUserException :: INVALID_LOGIN);
		} else if ($password !== false && $password != $user['password']) {
			throw new EfrontUserException(_INVALIDPASSWORD.': '.$user, EfrontUserException :: INVALID_PASSWORD);
		}

		$this -> user  = $user;
		$this -> login = $user['login'];

		$this -> user['directory'] = G_UPLOADPATH.$this -> user['login'];
		if (!is_dir($this -> user['directory'])) {
			$this -> createUserFolders();
		}
		$this -> user['password'] == 'ldap' ? $this -> isLdapUser = true : $this -> isLdapUser = false;

		//Initialize core access
		$this -> coreAccess = array();
	}

	/**
	 * Creates user folders
	 * @since 3.6.4
	 * @access private
	 */
	private function createUserFolders() {
		$user_dir = G_UPLOADPATH.$this -> user['login'].'/';
		mkdir($user_dir, 0755);
		mkdir($user_dir.'message_attachments/', 0755);
		mkdir($user_dir.'message_attachments/Incoming/', 0755);
		mkdir($user_dir.'message_attachments/Sent/', 0755);
		mkdir($user_dir.'message_attachments/Drafts/', 0755);
		mkdir($user_dir.'avatars/', 0755);

		try {
			//Create database representations for personal messages folders (it has nothing to do with filsystem database representation)
			eF_insertTableDataMultiple("f_folders", array(array('name' => 'Incoming', 'users_LOGIN' => $this -> user['login']),
			array('name' => 'Sent',	  'users_LOGIN' => $this -> user['login']),
			array('name' => 'Drafts', 'users_LOGIN' => $this -> user['login'])));
		} catch(Exception $e) {}

	}

	/**
	 * Get the user's upload directory
	 *
	 * This function returns the path to the user's upload directory. The path always has a trailing
	 * slash at the end.
	 * <br/>Example:
	 * <code>
	 * $path = $user -> getDirectory(); //returns something like /var/www/efront/upload/admin/
	 * </code>
	 *
	 * @return string The path to the user directory
	 * @since 3.6.0
	 * @access public
	 */
	public function getDirectory() {
		return $this -> user['directory'].'/';
	}

	/**
	 * Create new user
	 *
	 * This function is used to create a new user in the system
	 * The user is created based on a a properties array, in which
	 * the user login, name, surname and email must be present, otherwise
	 * an EfrontUserException is thrown. Apart from these, all the other
	 * user elements are optional, and defaults will be used if they are left
	 * blank.
	 * Once the database representation is created, the constructor tries to create the
	 * user directories, G_UPLOADPATH.'login/' and message attachments subfolders. Finally
	 * it assigns a default avatar to the user. The function instantiates the user based on
	 * its type.
	 * <br/>Example:
	 * <code>
	 * $properties = array('login' => 'jdoe', 'name' => 'john', 'surname' => 'doe', 'email' => 'jdoe@example.com');
	 * $user = EfrontUser :: createUser($properties);
	 * </code>
	 *
	 * @param array $userProperties The new user properties
	 * @param array $users The list of existing users, with logins and active properties, in the form array($login => $active). It is handy to specify when creating massively users
	 * @return array with new user settings if the new user was successfully created
	 * @since 3.5.0
	 * @access public
	 */
	public static function createUser($userProperties, $users = array(), $addToDefaultGroup = true) {
		$result = eF_getTableData("users", "count(id) as total", "active=1");
		$activatedUsers = $result[0]['total'];
		
		if (!isset($userProperties['login']) || !eF_checkParameter($userProperties['login'], 'login')) {
			throw new EfrontUserException(_INVALIDLOGIN.': '.$userProperties['login'], EfrontUserException :: INVALID_LOGIN);
		}
		
		$result = eF_getTableData("users", "login, archive", "login='{$userProperties['login']}'");	//collation is by default utf8_general_ci, meaning that this search is case-insensitive
		if (sizeof($result) > 0) {
			if ($result[0]['archive']) {
				throw new EfrontUserException(_USERALREADYEXISTSARCHIVED.': '.$userProperties['login'], EfrontUserException :: USER_EXISTS);
			} else {
				throw new EfrontUserException(_USERALREADYEXISTS.': '.$userProperties['login'], EfrontUserException :: USER_EXISTS);
			}
		}
		
/*		
		$archived_keys = array_combine(array_keys($archived),array_keys($archived));  
		if (isset($archived_keys[mb_strtolower($userProperties['login'])])) {
		//if (in_array(mb_strtolower($userProperties['login']), array_keys($archived), true) !== false) {	
			throw new EfrontUserException(_USERALREADYEXISTSARCHIVED.': '.$userProperties['login'], EfrontUserException :: USER_EXISTS);
		}	
		
		$user_keys = array_combine(array_keys($users),array_keys($users));  
		if (isset($user_keys[mb_strtolower($userProperties['login'])])) { 
		//if (in_array(mb_strtolower($userProperties['login']), array_keys($users), true) !== false) {
			throw new EfrontUserException(_USERALREADYEXISTS.': '.$userProperties['login'], EfrontUserException :: USER_EXISTS);
		}
*/
		
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
			//pr($activatedUsers);
				if (isset($GLOBALS['configuration']['version_users']) && $activatedUsers > $GLOBALS['configuration']['version_users'] && $GLOBALS['configuration']['version_users'] > 0) {
					throw new EfrontUserException(_MAXIMUMUSERSNUMBERREACHED.' ('.$GLOBALS['configuration']['version_users'].'): '.$userProperties['login'], EfrontUserException :: MAXIMUM_REACHED);
				}
			} #cpp#endif
		} #cpp#endif
		
		
		if ($userProperties['email'] && !eF_checkParameter($userProperties['email'], 'email')) {
			throw new EfrontUserException(_INVALIDEMAIL.': '.$userProperties['email'], EfrontUserException :: INVALID_PARAMETER);
		}
		if (!isset($userProperties['name'])) {
			throw new EfrontUserException(_INVALIDNAME.': '.$userProperties['name'], EfrontUserException :: INVALID_PARAMETER);
		}
		if (!isset($userProperties['surname'])) {
			throw new EfrontUserException(_INVALIDSURNAME.': '.$userProperties['login'], EfrontUserException :: INVALID_PARAMETER);
		}
		$roles = EfrontUser::getRoles();
		$rolesTypes = EfrontUser::getRoles(true);
		foreach (EfrontUser::getRoles(true) as $key => $value) {
			$rolesTypes[$key] = mb_strtolower($value);
		}
		
		//If a user type is not specified, by default make the new user student
		if (!isset($userProperties['user_type'])) {	
			$userProperties['user_type'] = 'student';
		} else {
			if (in_array(mb_strtolower($userProperties['user_type']), $roles)) {
				$userProperties['user_type'] = mb_strtolower($userProperties['user_type']);
			} else if ($k=array_search(mb_strtolower($userProperties['user_type']), $rolesTypes)) {
				$userProperties['user_types_ID'] = $k;
				$userProperties['user_type'] = $roles[$k];
			} else {
				$userProperties['user_type'] = 'student';
			}
		}
		if (!in_array($userProperties['user_type'], EFrontUser::$basicUserTypes)) {
			$userProperties['user_type'] = 'student';
			$userProperties['user_types_ID'] = 0;
		}
		//!isset($userProperties['user_type']) || !in_array($userProperties['user_type'], EfrontUser::getRoles())	  ? $userProperties['user_type']	  = 'student'									 : null;
		isset($userProperties['password']) && $userProperties['password'] != ''		? $passwordNonTransformed		   = $userProperties['password'] : $passwordNonTransformed = $userProperties['login'];
		if ($userProperties['password'] != 'ldap') {
			!isset($userProperties['password']) || $userProperties['password'] == ''  ? $userProperties['password']	   = EfrontUser::createPassword($userProperties['login'])		: $userProperties['password'] = self :: createPassword($userProperties['password']);
			if ($GLOBALS['configuration']['force_change_password']) {
				$userProperties['need_pwd_change'] = 1;
			}
		}
		!isset($userProperties['email'])		  										? $userProperties['email']		  = ''											: null;										   // 0 means not pending, 1 means pending
		!isset($userProperties['languages_NAME']) 										? $userProperties['languages_NAME'] = $GLOBALS['configuration']['default_language'] : null;										  //If language is not specified, use default language
		!isset($userProperties['active']) ||  $userProperties['active'] == ""	   	? $userProperties['active']		 = 0											 : null;										   // 0 means inactive, 1 means active
		!isset($userProperties['pending'])												? $userProperties['pending']		= 0											 : null;										   // 0 means not pending, 1 means pending
		!isset($userProperties['timestamp']) ||  $userProperties['timestamp'] == ""	 ? $userProperties['timestamp']	  = time()										: null;
		!isset($userProperties['user_types_ID'])  										? $userProperties['user_types_ID']  = 0											 : null;
		
		$languages = EfrontSystem :: getLanguages();
		if (in_array($userProperties['languages_NAME'], array_keys($languages)) === false) {
			$userProperties['languages_NAME'] = $GLOBALS['configuration']['default_language'];
		}
		
		if ($userProperties['archive']) {
			$userProperties['archive'] 	= time();
			$userProperties['active'] 	= 0;
		}
		
		!isset($userProperties['timezone']) || $userProperties['timezone'] == ''  ? $userProperties['timezone'] = $GLOBALS['configuration']['time_zone'] :null;
		
		$userProfile = eF_getTableData("user_profile", "name,options", "active=1 AND type='select'");
		foreach ($userProfile as $field) {
			if(isset($userProperties[$field['name']])) {
				$options = unserialize($field['options']);
				$userProperties[$field['name']] = array_search($userProperties[$field['name']], $options);
			}
		}
				
		eF_insertTableData("users", $userProperties);

		// Assign to the new user all skillgap tests that should be automatically assigned to every new student
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				if ($userProperties['user_type'] == 'student') {
					$tests = EfrontTest :: getAutoAssignedTests();
					foreach ($tests as $test) {
						eF_insertTableData("users_to_skillgap_tests", array("users_LOGIN" => $userProperties['login'], "tests_ID" => $test));
					}
				}
			} #cpp#endif
		} #cpp#endif

		$newUser = EfrontUserFactory :: factory($userProperties['login']);
		//$newUser -> user['password'] = $passwordNonTransformed;	//commented out because it was not needed any more, and created problems. Will be removed in next pass

		global $currentUser;  // this is for running eF_loadAllModules ..needs to go somewhere else
		if (!$currentUser) {
			$currentUser = $newUser;
		}
		EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_JOIN, "users_LOGIN" => $newUser -> user['login'], "users_name" => $newUser -> user['name'], "users_surname" => $newUser -> user['surname'], "entity_name" => $passwordNonTransformed));
		EfrontEvent::triggerEvent(array("type" => (-1) * EfrontEvent::SYSTEM_VISITED, "users_LOGIN" =>$newUser -> user['login'], "users_name" => $newUser -> user['name'], "users_surname" => $newUser -> user['surname']));
	        	
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				if ($addToDefaultGroup) {
					EfrontGroup::addToDefaultGroup($newUser, $newUser -> user['user_types_ID'] ? $newUser -> user['user_types_ID'] : $newUser -> user['user_type']);
				}
			} #cpp#endif
		} #cpp#endif


		///MODULES1 - Module user add events
		// Get all modules (NOT only the ones that have to do with the user type)
		if (!self::$cached_modules) {
			self::$cached_modules = eF_loadAllModules();
		}
		// Trigger all necessary events. If the function has not been re-defined in the derived module class, nothing will happen
		foreach (self::$cached_modules as $module) {
			$module -> onNewUser($userProperties['login']);
		}

		EfrontCache::getInstance()->deleteCache('usernames');

		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				$threshold = self::NOTIFY_THRESHOLD * $GLOBALS['configuration']['version_users'];
		
				if (isset($GLOBALS['configuration']['version_users']) && $GLOBALS['configuration']['version_users'] > 0 && $activatedUsers < $threshold && $activatedUsers+1 > $threshold) {
					$admin = EfrontSystem::getAdministrator();
					eF_mail($GLOBALS['configuration']['system_email'], $admin->user['email'], _YOUAREREACHINGYOURSUBSCRIPTIONLIMIT, str_replace(array('%w', '%x', '%y', '%z'), array($admin->user['name'], self::NOTIFY_THRESHOLD*100, $GLOBALS['configuration']['site_name'], G_SERVERNAME), _YOUAREREACHINGYOURSUBSCRIPTIONLIMITBODY));
				}
			} #cpp#endif
		} #cpp#endif
		
		return $newUser;

	}


	/**
	 * This function parses an array of users and verifies that they are
	 * correct and converts it to an array if it's a single entry
	 *
	 * @param mixed $users The users to verify
	 * @return array The array of verified users
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function verifyUsersList($users) {
		if (!is_array($users)) {
			$users = array($users);
		}
		foreach ($users as $key => $value) {
			if ($value instanceOf EfrontUser) {
				$users[$key] = $value -> user['login'];
			} elseif (is_array($value) && isset($value['login'])) {
				$users[$key] = $value['login'];
			} elseif (is_array($value) && isset($value['users_LOGIN'])) {
				$users[$key] = $value['users_LOGIN'];
			} elseif (!eF_checkParameter($value, 'login')) {
				unset($users[$key]);
			}
		}
		return array_values(array_unique($users));			//array_values() to reindex array
	}


	/**
	 * This function parses an array of roles and verifies that they are
	 * correct, converts it to an array if it's a single entry and
	 * pads the array with extra values, if its length is less than the
	 * desired
	 *
	 * @param mixed $roles The roles to verify
	 * @param int $length The desired length of the roles array
	 * @return array The array of verified roles
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function verifyRolesList($roles, $length) {
		if (!is_array($roles)) {
			$roles = array($roles);
		}
		if (sizeof($roles) < $length) {
			$roles = array_pad($roles, $length, $roles[0]);
		}

		return array_values($roles); 			//array_values() to reindex array
	}

	/**
	 * Check whether the specified role is of type 'student'
	 *
	 * @param mixed $role The role to check
	 * @return boolean Whether it's a 'student' role
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function isStudentRole($role) {
		$courseRoles = EfrontLessonUser :: getLessonsRoles();
		if ($courseRoles[$role] == 'student') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check whether the specified role is of type 'professor'
	 *
	 * @param mixed $role The role to check
	 * @return boolean Whether it's a 'professor' role
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function isProfessorRole($role) {
		$courseRoles = EfrontLessonUser :: getLessonsRoles();
		if ($courseRoles[$role] == 'professor') {
			return true;
		} else {
			return false;
		}
	}

	public static function checkUserAccess($type = false, $forceType = false) {
		if ($GLOBALS['configuration']['webserver_auth']) {
			$user = EfrontUser :: checkWebserverAuthentication();
		} else if (isset($_SESSION['s_login']) && $_SESSION['s_password']) {
			$user = EfrontUserFactory :: factory($_SESSION['s_login'], false, $forceType);
		} else {
			throw new EfrontUserException(_RESOURCEREQUESTEDREQUIRESLOGIN, EfrontUserException::USER_NOT_LOGGED_IN);
		}

		if (!$user -> isLoggedIn(session_id())) {
			throw new EfrontUserException(_RESOURCEREQUESTEDREQUIRESLOGIN, EfrontUserException::USER_NOT_LOGGED_IN);
		}
		if ($user -> user['timezone']) {
			date_default_timezone_set($user -> user['timezone']);
		}
		
		$user -> applyRoleOptions($user -> user['user_types_ID']);                //Initialize user's role options for this lesson
		if ($type && $user -> user['user_type'] != $type) {
			throw new Exception(_YOUCANNOTACCESSTHISPAGE, EfrontUserException::INVALID_TYPE);
		}
		return $user;
	}

	public static function checkWebserverAuthentication() {
		try {
			eval('$usernameVar='.$GLOBALS['configuration']['username_variable'].';');
			if (!$usernameVar) {
				eF_redirect(G_SERVERNAME.$GLOBALS['configuration']['error_page'], true, 'top', true);
				exit;
			} else {
				try {
					$user = EfrontUserFactory :: factory($usernameVar);
					if (!$_SESSION['s_login'] || $usernameVar != $_SESSION['s_login']) {
						$user -> login($user -> user['password'], true);
					}
				} catch (EfrontUserException $e) {
					if ($e -> getCode() == EfrontUserException::USER_NOT_EXISTS && $GLOBALS['configuration']['webserver_registration']) {
						try {
							include($GLOBALS['configuration']['registration_file']);
							$user = EfrontUserFactory :: factory($usernameVar);
							if (!$_SESSION['s_login'] || $usernameVar != $_SESSION['s_login']) {
								$user -> login($user -> user['password'], true);
							}
						} catch (Exception $e) {
							eF_redirect(G_SERVERNAME.$GLOBALS['configuration']['unauthorized_page'], true, 'top', true);
							exit;
						}
					} else {
						eF_redirect(G_SERVERNAME.$GLOBALS['configuration']['unauthorized_page'], true, 'top', true);
						exit;
					}
				}
			}
		} catch (Exception $e) {
			eF_redirect(G_SERVERNAME.$GLOBALS['configuration']['unauthorized_page'], true, 'top', true);
			//header("location:".G_SERVERNAME.$GLOBALS['configuration']['unauthorized_page']);
		}
		return $user;
	}
	
	
	public static function countUsers($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);
	
		$from = "users u";
		list($where, $limit, $orderby) = EfrontUser :: convertUserConstraintsToSqlParameters($constraints);
		$result  = eF_countTableData($from, "u.login", implode(" and ", $where));
	
		return $result[0]['count'];
	}
	
	
	public function getUsers($constraints = array()) {
	
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);
	
		list($where, $limit, $orderby) = EfrontUser :: convertUserConstraintsToSqlParameters($constraints);
		$select  = "u.login,u.user_type,u.user_types_ID,u.active,u.timestamp,u.archive, u.last_login,u.balance";
		$from    = "users u";
	
		$result  = eF_getTableData($from, $select, implode(" and ", $where), $orderby, $groupby, $limit);
	
		return EfrontUser :: convertDatabaseResultToUserArray($result);
	
	}	



	/**
	 * Add user profile field
	 */
	public static function addUserField() {}

	/**
	 * Remove user profile field
	 */
	public static function removeUserField() {}

	/**
	 * Get user type
	 *
	 * This function returns the user basic type, one of 'administrator', 'professor',
	 * 'student'
	 * <br/>Example:
	 * <code>
	 *	  $user = EfrontUserFactory :: factory('admin');
	 *	  echo $user -> getType();			//Returns 'administrator'
	 * </code>
	 *
	 * @return string The user type
	 * @since 3.5.0
	 * @access public
	 */
	public function getType() {
		return $this -> user['user_type'];
	}


	/**
	 * Set user password
	 *
	 * This function is used to change the user password to something
	 * new.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> setPassword('somepass');
	 * </code>
	 *
	 * @param string $password The new password
	 * @return boolean true if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function setPassword($password) {
		$password_encrypted = EfrontUser::createPassword($password);
		if (eF_updateTableData("users", array("password" => $password_encrypted), "login='".$this -> user['login']."'")) {
			$this -> user['password'] = $password;
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Get user password
	 *
	 * This function returns the user password (MD5 encrypted)
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * echo $user -> getPassword();			 //echos something like '36f49e43c662986b838258ab099d0d5a'
	 * </code>
	 *
	 * @return string The user password (encrypted)
	 * @since 3.5.0
	 * @access public
	 */
	public function getPassword() {
		return $this -> user['password'];
	}

	/**
	 * Set login type
	 *
	 * This function is used to set the login type for the user. Currently this
	 * can be either 'normal' (default) or 'ldap'. Setting the login type to 'ldap'
	 * erases the user password and forces authentication through ldap server
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> setLoginType('ldap');			   //Set login type to 'ldap'
	 * $user -> setLoginType('normal', 'testpass'); //Set login type to 'normal' using password 'testpass'
	 * $user -> setLoginType();					 //Set login type to 'normal' and use default password (the user's login)
	 * </code>
	 * If the user was an ldap user and is reverted back to normal, the password is either specified
	 * or created by default to match the user's login
	 *
	 * @param string $loginType The new login type, one of 'ldap' or 'normal'
	 * @param string $password The new password, only used when converting ldap to normal accounts
	 * @return boolean True if everything is ok.
	 * @since 3.5.0
	 * @access public
	 */
	public function setLoginType($loginType = 'normal', $password = '') {
		//The user login type is specified by the password. If the password is 'ldap', the the login type is also ldap. There is no chance to mistaken normal users for ldap users, since all normal users have passwords stored in md5 format, which can never be 'ldap' (or anything like it)
		if ($loginType == 'ldap' && $this -> user['password'] != 'ldap') {
			eF_updateTableData("users", array("password" => 'ldap'), "login='".$this -> user['login']."'");
			$this -> user['password'] = 'ldap';
		} elseif ($loginType == 'normal' && $this -> user['password'] == 'ldap') {
			!$password ? $password = EfrontUser::createPassword($this -> user['login']) : null;							//If a password is not specified, use the user's login name
			eF_updateTableData("users", array("password" => $password), "login='".$this -> user['login']."'");
			$this -> user['password'] = $password;
		}
		return true;
	}

	/**
	 * Get the login type
	 *
	 * This function is used to check whether the user's login type
	 * is 'normal' or 'ldap'
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> getLoginType();					 //Returns either 'normal' or 'ldap'
	 * </code>
	 *
	 * @return string Either 'normal' or 'ldap'
	 * @since 3.5.0
	 * @access public
	 */
	public function getLoginType() {
		if ($this -> user['password'] == 'ldap') {
			return 'ldap';
		} else {
			return 'normal';
		}
	}

	/**
	 * Activate user
	 *
	 * This function is used to activate the user
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> activate();
	 * </code>
	 *
	 * @return boolean True if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function activate() {
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				$users = eF_countTableData("users", "*", "active=1 and archive=0");
				$versionUsers = (int) $GLOBALS['configuration']['version_users'];
				if (isset($versionUsers) && $users[0]['count'] > $versionUsers && $versionUsers > 0) {
					throw new EfrontUserException(_MAXIMUMUSERSNUMBERREACHED.' ('.$GLOBALS['configuration']['version_users'].'): '.$this -> user['login'], EfrontUserException :: MAXIMUM_REACHED);
				}
			} #cpp#endif
		} #cpp#endif

		$this -> user['active']  = 1;
		$this -> user['pending'] = 0;
		$this -> persist();
		return true;
	}

	/**
	 * Deactivate user
	 *
	 * This function is used to deactivate the user
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> deactivate();
	 * </code>
	 *
	 * @return boolean True if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function deactivate() {
		$this -> user['active']  = 0;
		$this -> persist();
		EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_USER_DEACTIVATE, "users_LOGIN" => $this -> user['login'], "users_name" => $this -> user['name'], "users_surname" => $this -> user['surname']));
		return true;
	}

	/**
	 * Set avatar image
	 *
	 * This function is used to set the user's avatar image.
	 * <br/>Example:
	 * <code>
	 * $file = new EfrontFile(32);											 //This is a file uploaded -for example- in the filesystem.
	 * $user -> setAvatar($file);
	 * </code>
	 *
	 * @param EfrontFile $file The file that will be used as avatar
	 * @return boolean True if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function setAvatar($file) {
		if (eF_updateTableData("users", array("avatar" => $file['id']), "login = '".$this -> user['login']."'")) {
			$this -> user['avatar'] = $file['id'];
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get avatar image
	 *
	 * This function returns the file object corresponding to the user avatar
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> getAvatar();	//Returns an EfrontFile object
	 * </code>
	 *
	 * @return EfrontFile The avatar's file object
	 * @since 3.6.0
	 * @access public
	 */
	public function getAvatar() {
		if ($this -> user['avatar']) {
			$avatar = new EfrontFile($this -> user['avatar']);
		} else {
			$avatar = new EfrontFile(G_SYSTEMAVATARSURL.'unknown_small.png');
		}
		return $avatar;
	}

	/**
	 * Set user status
	 *
	 * This function is used to set the user's status.
	 * <br/>Example:
	 * <code>
	 * $user -> setStatus("Carpe Diem!");
	 * </code>
	 *
	 * @param string to be set as the new status - could be ""
	 * @return boolean True if everything is ok
	 * @since 3.6.0
	 * @access public
	 */
	public function setStatus($status) {
		//echo $status;
		if ($_SESSION['facebook_user'] && $_SESSION['facebook_details']['status']['message'] != $status) {
			$path = "../libraries/";
			require_once $path . "external/facebook/facebook.php";
			$facebook   = new Facebook(array(
					'appId'  => $GLOBALS['configuration']['facebook_api_key'],
					'secret' => $GLOBALS['configuration']['facebook_secret'],
					'cookie' => true
			));
			//$access_token = $facebook -> getAccessToken();			
			
			$fql = "SELECT publish_stream FROM permissions WHERE uid =".$_SESSION['facebook_user'];
			$publish_info = $facebook->api(array(
					'method' => 'fql.query',
					'query' => $fql,
			));
			
			$canPublish = 0;
			if (!empty($publish_info)) {
				$canPublish = $publish_info[0]['publish_stream'];
			}

			if (!$canPublish) {			
				$dialog_url = "https://www.facebook.com/dialog/oauth?client_id=". $GLOBALS['configuration']['facebook_api_key']."&redirect_uri=".G_SERVERNAME.$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&scope=read_stream,status_update";
				echo json_encode(array('redirect' => $dialog_url)); 
				exit;
			}		
			
			//$perms = json_decode(file_get_contents('https://graph.facebook.com/me/permissions?access_token=' . $access_token));
			//$_SESSION['facebook_can_update'] = $perms->data[0]->status_update;		
			
			$facebook->api ( array(
					'method' => 'users.setStatus',
					 'status' => $status,
					 'uid'    => $_SESSION['facebook_user'],
					 'status_includes_verb' => true
			) );
			
			
			//	$facebook->api_client->call_method("facebook.users.setStatus", array("status" => $status, "status_includes_verb" => true));
			//	$temp = $facebook->api_client->fql_query("SELECT status FROM user WHERE uid = " . $_SESSION['facebook_user']);
			$_SESSION['facebook_details']['status'] = $status;

		}
		
		eF_updateTableData("users", array("status" => $status), "login = '".$this -> user['login']."'");
		$this -> user['status'] = $status;
		EfrontEvent::triggerEvent(array("type" => EfrontEvent::STATUS_CHANGE, "users_LOGIN" => $this -> user['login'], "users_name" => $this->user['name'], "users_surname" => $this->user['surname'], "entity_name" => $status));
		
		return true;
	}
	/**
	 * Logs out user
	 *
	 * To log out a user, the function deletes the session information and updates the database
	 * tables.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> logout();
	 * </code>
	 *
	 * @param $sessionId Which session to logout user from
	 * @return boolean True if the user was logged out succesfully
	 * @since 3.5.0
	 * @access public
	 */
	public function logout($sessionId = false) {
		// Delete FB-connect related cookies - without this code the "Session key invalid problem" appears
		if (isset($GLOBALS['configuration']['facebook_api_key']) && $GLOBALS['configuration']['facebook_api_key'] && $_COOKIE['fbsr_'.$GLOBALS['configuration']['facebook_api_key']]) {
			foreach ($_COOKIE as $cookie_key => $cookie) {
				if (strpos($cookie_key, $GLOBALS['configuration']['facebook_api_key']) !== false) {
					unset($_COOKIE[$cookie_key]);					
				}

			}
/*			$path = "../libraries/";
			require_once $path . "external/facebook/facebook.php";
			$facebook   = new Facebook(array(
					'appId'  => $GLOBALS['configuration']['facebook_api_key'],
					'secret' => $GLOBALS['configuration']['facebook_secret'],
					'cookie' => true
			));
			
			$facebook->destroySession();
			
			setcookie('fbsr_' . $GLOBALS['configuration']['facebook_api_key'], $_COOKIE['fbsr_' . $GLOBALS['configuration']['facebook_api_key']], time() - 3600, '/', '.'.$_SERVER['SERVER_NAME']);
			setcookie('fbsr_' . $GLOBALS['configuration']['facebook_api_key'], $_COOKIE['fbsr_' . $GLOBALS['configuration']['facebook_api_key']], time() - 3600, '/', $_SERVER['SERVER_NAME']);
*/			
		}

		if ($sessionId) {
			//Logout user on a specific session
			eF_updateTableData("user_times", array("session_expired" => 1), "session_expired = 0 and users_LOGIN='".$this -> user['login']."' and session_id='$sessionId' ");
		} else {
			//Logout every user logged under this login
			eF_updateTableData("user_times", array("session_expired" => 1), "session_expired = 0 and users_LOGIN='".$this -> user['login']."'");
		}

		$result = eF_getTableData("user_times", "id", "session_expired=0 and users_LOGIN='".$this -> user['login']."'");
		//If this was the last user logged in under this login, create logout entry
		if (empty($result)) {
			$fields_insert = array('users_LOGIN' => $this -> user['login'],
								   'timestamp'   => time(),
								   'action'	     => 'logout',
								   'comments'	 => 0,
								   'session_ip'  => eF_encodeIP($_SERVER['REMOTE_ADDR']));
			eF_insertTableData("logs", $fields_insert);
		}

		if ((!$_SESSION['s_login'] || $this -> user['login'] == $_SESSION['s_login']) && ($sessionId == session_id() || !$sessionId)) {
			if (isset($_COOKIE['c_request'])) {
				setcookie('c_request', '', time() - 86400);
				unset($_COOKIE['c_request']);
			}

			setcookie ("cookie_login", "", time() - 3600);
			setcookie ("cookie_password", "", time() - 3600);
			unset($_COOKIE['cookie_login']);						//These 2 lines are necessary, so that index.php does not think they are set
			unset($_COOKIE['cookie_password']);


			//Empty session without destroying it
			foreach ($_SESSION as $key => $value) {
				if ($key != 's_current_branch') {
					unset($_SESSION[$key]);
				}
			}
			//session_destroy();
		}

		return true;

	}

	/**
	 * Login user
	 *
	 * This function logs the user in the system, using the specified password
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> login('mypass');
	 * </code>
	 *
	 * @param string $password The password to login with
	 * @param boolean $encrypted Whether the password is already encrypted
	 * @return boolean True if the user logged in successfully
	 * @since 3.5.0
	 * @access public
	 */
	public function login($password, $encrypted = false) {

		//If the user is already logged in, log him out
		if ($this -> isLoggedIn()) {
			//If the user is logged in right now on the same pc with the same session, return true (nothing to do)
			if ($this -> isLoggedIn(session_id())) {
				if (!$encrypted && EfrontUser::createPassword($password) != $this -> user['password']) {
					throw new EfrontUserException(_INVALIDPASSWORD, EfrontUserException :: INVALID_PASSWORD);
				} else if ($encrypted && $password != $this -> user['password']) {
					throw new EfrontUserException(_INVALIDPASSWORD, EfrontUserException :: INVALID_PASSWORD);
				}				
				return true;
			} elseif (!$this -> allowMultipleLogin()) {
				$this -> logout();
			}
		}

		//If we are logged in as another user, log him out
		if (isset($_SESSION['s_login']) && $_SESSION['s_login'] != $this -> user['login']) {
			try {
				EfrontUserFactory :: factory($_SESSION['s_login']) -> logout(session_id());
			} catch (Exception $e) {}
		}

		//Empty session without destroying it
		foreach ($_SESSION as $key => $value) {
			if ($key != 'login_mode' && strpos($key, "facebook") === false) {					//'login_mode' is used to facilitate lesson registrations
				unset($_SESSION[$key]);
			}
		}

		if ($this -> user['pending']) {
			throw new EfrontUserException(_USERPENDING, EfrontUserException :: USER_PENDING);
		}
		if (!$this -> user['active']) {
			throw new EfrontUserException(_USERINACTIVE, EfrontUserException :: USER_INACTIVE);
		}

		if ($this -> isLdapUser) {									//Authenticate LDAP user
			if (!eF_checkUserLdap($this -> user['login'], $password)) {
				throw new EfrontUserException(_INVALIDPASSWORD, EfrontUserException :: INVALID_PASSWORD);
			}
		} else {		
			if (!$encrypted) {
				$password = EfrontUser::createPassword($password);
			}
			if ($password != $this -> user['password']) {
				throw new EfrontUserException(_INVALIDPASSWORD, EfrontUserException :: INVALID_PASSWORD);
			}
		}

		//if user language is deactivated or deleted, login user with system default language
		if ($GLOBALS['configuration']['onelanguage']) {
			$loginLanguage = $GLOBALS['configuration']['default_language'];
		} else {
			$activeLanguages = array_keys(EfrontSystem::getLanguages(true, true));
			if (in_array($this -> user['languages_NAME'], $activeLanguages)) {
				$loginLanguage = $this -> user['languages_NAME'];
			} else {
				$loginLanguage = $GLOBALS['configuration']['default_language'];
			}
		}

		//Assign session variables
		$_SESSION['s_login']	= $this -> user['login'];
		$_SESSION['s_password'] = $this -> user['password'];
		$_SESSION['s_type']		= $this -> user['user_type'];
		$_SESSION['s_language'] = $loginLanguage;
		$_SESSION['s_custom_identifier'] = sha1(microtime().$this -> user['login']);
		$_SESSION['s_time_target'] = array(0 => 'system');		//'s_time_target' is used to signify which of the system's area the user is currently accessing. It is a id => entity pair 
		//$_SESSION['last_action_timestamp'] = time();	//Initialize first action

		//Insert log entry
		$fields_insert = array('users_LOGIN' => $this -> user['login'],
							   'timestamp'   => time(),
							   'action'	 	 => 'login',
							   'comments'	 => session_id(),
							   'session_ip'  => eF_encodeIP($_SERVER['REMOTE_ADDR']));
		eF_insertTableData("logs", $fields_insert);
		eF_updateTableData("users", array('last_login' => time()), "login='{$this -> user['login']}'");
		if ($GLOBALS['configuration']['ban_failed_logins']) {
			eF_deleteTableData("logs","users_LOGIN='".$this -> user['login']."' and action='failed_login'");
		}

		//Insert user times entry
		$fields = array("session_timestamp" => time(),
						"session_id"		=> session_id(),
						"session_custom_identifier" => $_SESSION['s_custom_identifier'],
						"session_expired"	=> 0,
						"users_LOGIN" 		=> $_SESSION['s_login'],
						"timestamp_now"		=> time(),
						"time" 				=> 0,
						"entity" 			=> 'system',
						"entity_id" 		=> 0);
		eF_insertTableData("user_times", $fields);

		return true;
	}


	/**
	 * Check if this user is allowed to multiple logins
	 *
	 * This function checks the current system settings and returns true
	 * if the current user is allowed to be logged in to the system more than once
	 *
	 * @return boolean true if the user is allowed to loggin more than once
	 * @since 3.5.2
	 * @access private
	 */
	private function allowMultipleLogin() {
		$multipleLogins = unserialize($GLOBALS['configuration']['multiple_logins']);
		if ($multipleLogins) {
			if (in_array($this -> user['login'], $multipleLogins['users']) ||
				in_array($this -> user['user_type'], $multipleLogins['user_types']) ||
				in_array($this -> user['user_types_ID'], $multipleLogins['user_types']) ||
				array_intersect(array_keys($this -> getGroups()), $multipleLogins['groups'])) {

				if ($multipleLogins['global']) {			//If global allowance is set to "true", it means that the above clause, which matches the exceptions, translates to "multiple logins are prohibited for this user"
					return false;
				} else {
					return true;
				}
			} else {
				if ($multipleLogins['global']) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}


	/**
	 * Get the list of users that are currently online
	 *
	 * This function is used to get a list of the users that are currently online
	 * In addition, it logs out any inactive users, based on global setting
	 * <br>Example:
	 * <code>
	 * $online = EfrontUser :: getUsersOnline();
	 * </code>
	 *
	 * @param boolean $userType Return only users of the basic type $user_type
	 * @param int $interval The idle interval above which a user is logged out. If it's not specified, no logging out takes place
	 * @return array The list of online users
	 * @since 3.5.0
	 * @access public
	 */
	public static function getUsersOnline($interval = false) {
		$usersOnline = array();

		//A user may have multiple active entries on the user_times table, one for system, one for unit etc. Pick the most recent
		$result  = eF_getTableData("user_times,users", "users.login, users.name, users.surname, users.user_type, timestamp_now, session_timestamp, session_id", "users.login=user_times.users_LOGIN and session_expired=0", "timestamp_now desc");
		foreach ($result as $value) {
			if (!isset($parsedUsers[$value['login']])) {
				if ((time() - $value['timestamp_now'] < $interval) || !$interval) {
					$usersOnline[] = array('login' 		   => $value['login'],
										   //'name'		   => $value['name'],
										   //'surname'	   => $value['surname'],
										   'formattedLogin'=> formatLogin($value['login'], $value),
										   'user_type'	 	   => $value['user_type'],
										   'timestamp_now' 	   => $value['timestamp_now'],
										   'session_timestamp' => $value['session_timestamp'],
										   'time'		   	   => EfrontTimes::formatTimeForReporting(time() - $value['session_timestamp']));
				} else {
					//pr($result);
					//pr("interval: $interval, time: ".time().", timestamp_now:".$value['timestamp_now']);
					EfrontUserFactory :: factory($value['login']) -> logout($value['session_id']);
					//exit;
				}
				$parsedUsers[$value['login']] = true;
			}
		}
		
		
		$online_users = sizeof($result);
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				$threshold = $GLOBALS['configuration']['max_online_users_threshold'];
			
				if ($threshold > 0 && $online_users > $threshold && time() > $GLOBALS['configuration']['max_online_users_threshold_timestamp'] + 24*60*60) {
					$admin = EfrontSystem::getAdministrator();
					eF_mail($GLOBALS['configuration']['system_email'], $admin->user['email'], _ONLINEUSERSMAIL, str_replace(array('%w', '%x', '%y', '%z'), array($admin->user['name'], $threshold, $GLOBALS['configuration']['site_name'], G_SERVERNAME), _ONLINEUSERSMAILBODY));
					EfrontConfiguration::setValue('max_online_users_threshold_timestamp', time());
				}
			} #cpp#endif
		} #cpp#endif
		if ($GLOBALS['configuration']['max_online_users'] < $online_users) {
			EfrontConfiguration::setValue('max_online_users', $online_users);
			EfrontConfiguration::setValue('max_online_users_timestamp', time());
			
		}
		if (G_VERSIONTYPE == 'enterprise' && defined("G_BRANCH_URL") && G_BRANCH_URL && $_SESSION['s_current_branch']) {	
			$branch = new EfrontBranch($_SESSION['s_current_branch']);
			$branchUsers = $branch -> getBranchTreeUsers();
			foreach ($usersOnline as $key => $value) {	
				if (!isset($branchUsers[$value['login']]) && $value['user_type'] != 'administrator'){
					unset($usersOnline[$key]);
				}
			}
		}
		
		return $usersOnline;
	}

	/**
	 * Check if the user is already logged in
	 *
	 * This function examines the system logs to decide whether the user is still logged in
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> isLoggedIn();							   //Returns true if the user is logged in
	 * </code>
	 *
	 * @param string $sessionId Check if the user is logged in with this session id
	 * @return mixed Boolean True if the user is not logged in
	 * @since 3.5.0
	 * @access public
	 */
	public function isLoggedIn($sessionId = false) {
		
		if ($sessionId) {
			$result = eF_getTableData('user_times', 'users_LOGIN, session_id', "session_expired=0 and session_id = '$sessionId' and users_LOGIN='".$this -> user['login']."'");
		} else {
			$result = eF_getTableData('user_times', 'users_LOGIN, session_id', "session_expired=0 and users_LOGIN='".$this -> user['login']."'");
		}
		if (!empty($result)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete user
	 *
	 * This function is used to delete a user from the system.
	 * The user cannot be deleted if he is the last system administrator.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> delete();
	 * </code>
	 *
	 * @return boolean True if the user was deleted successfully
	 * @since 3.5.0
	 * @access public
	 */
	public function delete() {
		$this -> logout();
		///MODULES2 - Module user delete events - Before anything else
		// Get all modules (NOT only the ones that have to do with the user type)
		$modules = eF_loadAllModules();
		// Trigger all necessary events. If the function has not been re-defined in the derived module class, nothing will happen
		
		foreach ($modules as $module) {
			$module -> onDeleteUser($this -> user['login']);
		}

		try {
			$directory = new EfrontDirectory($this -> user['directory']);
			$directory -> delete();
		} catch (EfrontFileException $e) {
			$message = _USERDIRECTORYCOULDNOTBEDELETED.': '.$e -> getMessage().' ('.$e -> getCode().')';	//This does nothing at the moment
		}

		foreach ($this -> aspects as $aspect) {
			$aspect -> delete();
		}

		calendar::deleteUserCalendarEvents($this -> user['login']);

		eF_updateTableData("f_forums",	 array("users_LOGIN" => ''), "users_LOGIN='".$this -> user['login']."'");
		eF_updateTableData("f_messages",   array("users_LOGIN" => ''), "users_LOGIN='".$this -> user['login']."'");
		eF_updateTableData("f_topics",	 array("users_LOGIN" => ''), "users_LOGIN='".$this -> user['login']."'");
		eF_updateTableData("f_poll",	   array("users_LOGIN" => ''), "users_LOGIN='".$this -> user['login']."'");
		eF_updateTableData("news",		 array("users_LOGIN" => ''), "users_LOGIN='".$this -> user['login']."'");
		eF_updateTableData("files",		array("users_LOGIN" => ''), "users_LOGIN='".$this -> user['login']."'");

		eF_deleteTableData("f_folders", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("f_personal_messages", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("bookmarks", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("comments", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("f_users_to_polls", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("logs", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("rules", "users_LOGIN='".$this -> user['login']."'");
		//eF_deleteTableData("users_online", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("user_times", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("users_to_surveys", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("users_to_done_surveys", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("survey_questions_done", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("lessons_timeline_topics_data", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("events", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("profile_comments", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("users_to_content", "users_LOGIN='".$this -> user['login']."'");
		
		eF_deleteTableData("users_to_lessons", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("users_to_courses", "users_LOGIN='".$this -> user['login']."'");
		
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			eF_deleteTableData("payments", "users_LOGIN='".$this -> user['login']."'");
			eF_deleteTableData("facebook_connect", "users_LOGIN='".$this -> user['login']."'");
			eF_deleteTableData("users_to_skillgap_tests", "users_LOGIN='".$this -> user['login']."'");

		} #cpp#endif



		//This line was in EfrontProfessor and EfrontStudent without an obvious reason. Admins may also be members of groups
		eF_deleteTableData("users_to_groups", "users_LOGIN='".$this -> user['login']."'");

		//Changing order of these lines because of #4318, where system removal notification was set (user triggering the event)  
		eF_deleteTableData("notifications", "recipient='".$this -> user['login']."'");
		EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_REMOVAL, "users_LOGIN" => $this -> user['login'], "users_name" => $this -> user['name'], "users_surname" => $this -> user['surname']));
		
		eF_deleteTableData("users", "login='".$this -> user['login']."'");


		return true;
	}

	/**
	 * Set user type
	 *
	 * This function is used to change the basic user type
	 * @param string The new user type
	 * @since 3.5.0
	 * @access public
	 */
	public function changeType($userType) {
		if (!in_array($userType, EfrontUser :: $basicUserTypes)) {
			throw new EfrontUserException(_INVALIDUSERTYPE.': '.$userType, EfrontUser :: INVALID_TYPE);
		}

		switch ($userType) {
			case 'student':
				eF_updateTableData("users", array("user_type" => "student"), "login='".$this -> user['login']."'");
				break;
			case 'professor':
				eF_updateTableData("users", array("user_type" => "professor"), "login='".$this -> user['login']."'");
				break;
			case 'administrator':
				eF_updateTableData("users", array("user_type" => "administrator"), "login='".$this -> user['login']."'");
				break;
			default: break;
		}
	}

	/**
	 * Persist user values
	 *
	 * This function is used to store user's changed values to the database.
	 * <br/>Example:
	 * <code>
	 * $user -> surname = 'doe';							//Change object's surname
	 * $user -> persist();								  //Persist changed value
	 * </code>
	 *
	 * @return boolean True if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function persist() {
		$fields = array('password'	   => $this -> user['password'],
						'email'		  => $this -> user['email'],
						'languages_NAME' => $this -> user['languages_NAME'],
						'name'		   => $this -> user['name'],
						'surname'		=> $this -> user['surname'],
						'active'		 => $this -> user['active'],
						'comments'	   => $this -> user['comments'],
						'user_type'	  => $this -> user['user_type'],
						'timestamp'	  => $this -> user['timestamp'],
						'avatar'		 => $this -> user['avatar'],
						'pending'		=> $this -> user['pending'],
						'user_types_ID'  => $this -> user['user_types_ID'],
						'status'		 => $this -> user['status'],
						'balance'		=> $this -> user['balance'],
						'archive'		=> $this -> user['archive'],
						'timezone'		=> $this -> user['timezone'],
						'need_pwd_change' => $this -> user['need_pwd_change'] ? 1 : 0,
						'additional_accounts' => $this -> user['additional_accounts'],
						'short_description'   => $this -> user['short_description'],
						'autologin'   => $this -> user['autologin']);
		
		if ($GLOBALS['configuration']['reset_license_note_always']) {
			$fields['viewed_license'] =  0;
		} else {
			$fields['viewed_license'] =  $this -> user['viewed_license'];
		}
		
		$userProfile = eF_getTableData("user_profile", "*", "active=1 AND type <> 'branchinfo' AND type <> 'groupinfo'");
		foreach ($userProfile as $value) {
			$fields[$value['name']] = $this->user[$value['name']];
		}

		eF_updateTableData("users", $fields, "login='".$this -> user['login']."'");

		EfrontCache::getInstance()->deleteCache('usernames');

		///MODULES1 - Module user add events
		// Get all modules (NOT only the ones that have to do with the user type)
		if (!self::$cached_modules) {
			self::$cached_modules = eF_loadAllModules();
		}
		// Trigger all necessary events. If the function has not been re-defined in the derived module class, nothing will happen
		foreach (self::$cached_modules as $module) {
			$module -> onUpdateUser($this->user['login']);
		}
		
		return true;
	}



	/**
	 * Get the user groups list
	 *
	 * <br/>Example:
	 * <code>
	 * $groupsList	= $user -> getGroups();						 //Returns an array with pairs [groups id] => [employee specification for this group]
	 * </code>
	 *
	 * @return array An array of [group id] => [group ID] pairs, or an array of group objects
	 * @since 3.5.0
	 * @access public
	 */
	public function getGroups() {
		if (! $this -> groups ) {
			$result = eF_getTableData("users_to_groups ug, groups g", "g.*", "ug.users_LOGIN = '".$this -> login."' and g.id=ug.groups_ID and g.active=1");
			foreach ($result as $group) {
				$this -> groups[$group['id']] = $group;
			}

		}
		return $this -> groups;
	}


	/**
	 * Assign a group to user.
	 *
	 * This function can be used to assign a group to a user
	 * <br/>Example:
	 * <code>
	 * $user = EfrontHcdUserFactory :: factory('jdoe');
	 * $user -> addGroups(23);						 //Add a single group with id 23
	 * $user -> addGroups(array(23,24,25));			//Add multiple groups using an array
	 * </code>
	 *
	 * @return int The array of lesson ids.
	 * @since 3.5.0
	 * @access public
	 * @todo auto_projects
	 */
	public function addGroups($groupIds) {
		$this -> groups OR $this -> getGroups();		//Populate $this -> groups if it is not already filled in

		if (!is_array($groupIds)) {
			$groupIds = array($groupIds);
		}

		foreach ($groupIds as $key => $groupId) {
			if (eF_checkParameter($groupId, 'id') && !isset($this -> groups[$groupId])) {
				$group = new EfrontGroup($groupId);
				$group -> addUsers($this -> user['login'], $this -> user['user_types_ID'] ? $this -> user['user_types_ID'] : $this -> user['user_type']);
				$this  -> groups[$groupId] = $groupId;

				// Register group assignment into the event log - event log only available in HCD
				if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
					EfrontEvent::triggerEvent(array("type" => EfrontEvent::NEW_ASSIGNMENT_TO_GROUP, "users_LOGIN" => $this -> user['login'], "users_name" => $this -> user['name'], "users_surname" => $this -> user['surname'] , "entity_ID" => $groupId, "entity_name" => $this -> groups[$groupId]['name']));
				} #cpp#endif
			}
		}

		return $this -> groups;
	}

	/**
	 * Remove groups from employee.
	 *
	 * This function can be used to remove a group from the current employee.
	 * <br/>Example:
	 * <code>
	 * $employee = EfrontHcdUserFactory :: factory('jdoe');
	 * $employee -> removeGroups(23);						  //Remove a signle group with id 23
	 * $employee -> removeGroups(array(23,24,25));			 //Remove multiple groups using an array
	 * </code>
	 *
	 * @param int $groupIds Either a single group id, or an array if ids
	 * @return int The array of group ids.
	 * @since 3.5.0
	 * @access public
	 */
	public function removeGroups($groupIds) {
		$this -> groups OR $this -> getGroups();		//Populate $this -> groups if it is not already filled in

		if (!is_array($groupIds)) {
			$groupIds = array($groupIds);
		}

		foreach ($groupIds as $key => $groupId) {
			if (eF_checkParameter($groupId, 'id') && isset($this -> groups[$groupId])) {
				$group = new EfrontGroup($groupId);
				$group -> removeUsers($this -> user['login']);
				unset($this -> groups[$key]);										//Remove groups from cache array."

				// Register group assignment into the event log - event log only available in HCD
				if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
					EfrontEvent::triggerEvent(array("type" => EfrontEvent::REMOVAL_FROM_GROUP, "users_LOGIN" => $this -> user['login'], "users_name" => $this -> user['name'], "users_surname" => $this -> user['surname'] , "entity_ID" => $id, "entity_name" => $group_names[$id]));
				} #cpp#endif
			}
		}

		return $this -> groups;
	}


	///MODULE3
	/**
	 * Get modules for this user (according to the user type).
	 *
	 * This function can is used to get the modules for the user
	 * <br/>Example:
	 * <code>
	 * $currentUser = EfrontUserFactory :: factory('jdoe');
	 * $modules = $currentUser -> getModules();
	 * </code>
	 *
	 * @param no parameter
	 * @return int The array of modules for the user type of this user.
	 * @since 3.5.0
	 * @access public
	 */
	public function getModules() {
		if (!isset($this -> coreAccess['module_itself']) || $this -> coreAccess['module_itself'] != 'hidden') {
			$modulesDB = eF_getTableData("modules","*","active = 1");
			$modules   = array();
			isset($_SESSION['s_lesson_user_type']) && $_SESSION['s_lesson_user_type'] ? $user_type = $_SESSION['s_lesson_user_type'] : $user_type = $this -> getType();
			// Get all modules enabled for this user type
			foreach ($modulesDB as $module) {
				$folder = $module['position'];
				$className = $module['className'];
	
				// If a module is to be updated then its class should not be loaded now
				if (!($this -> getType() == "administrator" && isset($_GET['ctg']) && $_GET['ctg'] == "control_panel" && isset($_GET['op']) && $_GET['op'] == "modules" && $_GET['upgrade'] == $className)) {
	
					if(is_dir(G_MODULESPATH.$folder) && is_file(G_MODULESPATH.$folder."/".$className.".class.php")) {
						
						require_once G_MODULESPATH.$folder."/".$className.".class.php";
	
						if (class_exists($className)) {
							$modules[$className] = new $className($user_type.".php?ctg=module&op=".$className, $folder);
	
							// Got to check if this is a lesson module so as to change the moduleBasePath
							if ($modules[$className] -> isLessonModule() && isset($GLOBALS['currentLesson'])) {
								$modules[$className] -> moduleBaseUrl = $this -> getRole($GLOBALS['currentLesson']) .".php?ctg=module&op=".$className;
							}
							
							if (!in_array($user_type, $modules[$className] -> getPermittedRoles())) {
								unset($modules[$className]);
							}
						} else {
							$message	  = '"'.$className .'" '. _MODULECLASSNOTEXISTSIN . ' ' .G_MODULESPATH.$folder.'/'.$className.'.class.php';
							$message_type = 'failure';
						}
	
					} else {
						eF_deleteTableData("modules","className = '".$className."'");
	
						$message = _ERRORLOADINGMODULE . " " . $className . " " . _MODULEDELETED;
						$message_type = "failure";
					}
				}
			}
			return $modules;
		}
		return array();
	}

	/**
	 * Get the login time for on e or all users in the specified time interval
	 *
	 * This function returns the login time for the specified user in the specified interval
	 * <br/>Example:
	 * <code>
	 *	  $interval['from'] = "00000000";
	 *	  $interval['to']   = time();
	 *	  $time  = EfrontUser :: getLoginTime('jdoe', $interval); //$time['jdoe'] now holds his times
	 *	  $times = EfrontUser :: getLoginTime($interval); //$times now holds an array of times for all users
	 * </code>
	 *
	 * @param mixed $login The user to calulate times for, or false for all users
	 * @param mixed An array of the form (from =>'', to=>'') or false (return the total login time)
	 * @return the total login time as an array of hours, minutes, seconds
	 * @since 3.5.0
	 * @access public
	 */
	public static function getLoginTime($login = false, $interval = array()) {
		$times = new EfrontTimes($interval);
		if ($login) {
			$result = $times -> getUserTotalSessionTime($login);
			return $times -> formatTimeForReporting($result);
		} else {
			foreach ($times -> getSystemSessionTimesForUsers() as $login => $result) {
				$userTimes[$login] = $times -> formatTimeForReporting($result);
				return $userTimes;
			}
		}
	}


	/**
	 * Archive user
	 *
	 * This function is used to archive the user object, by setting its active status to 0 and its
	 * archive status to 1
	 * <br/>Example:
	 * <code>
	 * $user -> archive();	//Archives the user object
	 * $user -> unarchive();	//Archives the user object and activates it as well
	 * </code>
	 *
	 * @since 3.6.0
	 * @access public
	 */
	public function archive() {
		$this -> user['archive'] = time();
		$this -> persist();
		$this -> deactivate();
	}

	/**
	 * Unarchive user
	 *
	 * This function is used to unarchive the user object, by setting its active status to 1 and its
	 * archive status to 0
	 * <br/>Example:
	 * <code>
	 * $user -> archive();	//Archives the user object
	 * $user -> unarchive();	//Archives the user object and activates it as well
	 * </code>
	 *
	 * @since 3.6.0
	 * @access public
	 */
	public function unarchive() {
		$this -> activate();
		$this -> user['archive'] = 0;
		$this -> persist();
	}

	/**
	 * Apply role options to object
	 *
	 * This function is used to apply role options, using the specified role
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> applyRoleOptions(4);						//Apply the role options for user type with id 4 to the $user object
	 * </code>
	 *
	 * @param int $role The role id to apply options for
	 * @since 3.5.0
	 * @access public
	 */
	public function applyRoleOptions($role = false) {
		if (!$role) {
			$role = $this -> user['user_types_ID'];
		}

		if ($role) {
			$result = eF_getTableData("user_types", "*", "id='".$role."'");
			unserialize($result[0]['core_access'])	? $this -> coreAccess	= unserialize($result[0]['core_access'])	: $this -> coreAccess = null;
			unserialize($result[0]['modules_access']) ? $this -> modulesAccess = unserialize($result[0]['modules_access']) : $this -> modulesAccess = null;
		}
	}

	/**
	 * Get system roles
	 *
	 * This function is used to get all the roles in the system
	 * It returns an array where keys are the role ids and values are:
	 * - Either the role basic user types, if $getNames is false (the default)
	 * - or the role Names if $getNames is true
	 * The array is prepended with the 3 main roles, 'administrator', 'professor' and 'student'
	 * <br/>Example:
	 * <code>
	 * $roles = EfrontUser :: getRoles();
	 * </code>
	 *
	 * @param boolean $getNames Whether to return id/basic user type pairs or id/name pairs
	 * @return array The system roles
	 * @since 3.5.0
	 * @access public
	 * @static
	 */
	public static function getRoles($getNames = false) {
		//Cache results in self :: $userRoles
		if (is_null(self :: $userRoles)) {
			$roles	  = eF_getTableDataFlat("user_types", "*", "active=1");	//Get available roles
			self :: $userRoles = $roles;
		} else {
			$roles = self :: $userRoles;
		}
		if (sizeof($roles) > 0) {
			$getNames ? $roles = self :: $basicUserTypesTranslations + array_combine($roles['id'], $roles['name']) : $roles = array_combine(self :: $basicUserTypes, self :: $basicUserTypes) + array_combine($roles['id'], $roles['basic_user_type']);
		} else {
			$getNames ? $roles = self :: $basicUserTypesTranslations : $roles = array_combine(self :: $basicUserTypes, self :: $basicUserTypes);
		}

		return $roles;
	}


	/**
	 * Get the user profile's comments list
	 *
	 * <br/>Example:
	 * <code>
	 * $commentsList	= $user -> getProfileComments();						 //Returns an array with pairs [groups id] => [employee specification for this group]
	 * </code>
	 *
	 * @return array A sorted according to timestamp array of [comment id] => [timestamp, authors_LOGIN, authors_name, authors_surname, data] pairs, or an array of comments
	 * @since 3.6.0
	 * @access public
	 */
	public function getProfileComments() {
		if (EfrontUser::isOptionVisible('func_comments')) {
			$result = eF_getTableData("profile_comments JOIN users ON authors_LOGIN = users.login",  "profile_comments.id, profile_comments.timestamp, authors_LOGIN, users.name, users.surname, users.avatar, data", "users_LOGIN = '".$this -> user['login']."'", "timestamp DESC");
			$comments = array();
			foreach ($result as $comment) {
				$comments[$comment['id']] = $comment;
			}
			return $comments;
		} else {
			return array();
		}
	}


	/**
	 *
	 * @param $pwd
	 * @return unknown_type
	 */
	public static function createPassword($pwd, $mode = 'efront') {
		if ($mode == 'efront') {
			$encrypted = md5($pwd.G_MD5KEY);
		} else {
			$encrypted = $pwd;
		}
		return $encrypted;
	}


	/**
	 * Convert the user argument to a user login
	 *
	 * @param mixed $login The argument to convert
	 * @return string The user's login
	 * @since 3.6.3
	 * @access public
	 * @static
	 */
	public static function convertArgumentToUserLogin($login) {
		if ($login instanceof EfrontUser) {
			$login = $login -> user['login'];
		} else if (!eF_checkParameter($login, 'login')) {
			throw new EfrontUserException(_INVALIDLOGIN, EfrontUserException::INVALID_LOGIN);
		}

		return $login;
	}

	public static function convertUserObjectsToArrays($userObjects) {
		foreach ($userObjects as $key => $value) {
			if ($value instanceOf EfrontUser) {
				$userObjects[$key] = $value -> user;
			}
		}
		return $userObjects;
	}


	public static function convertUserConstraintsToSqlParameters($constraints) {
		$where = EfrontUser::addWhereConditionToUserConstraints($constraints);
		$limit = EfrontUser::addLimitConditionToConstraints($constraints);
		$order = EfrontUser::addSortOrderConditionToConstraints($constraints);

		return array($where, $limit, $order);
	}
	public static function addWhereConditionToUserConstraints($constraints) {
		$where = array();
		if (isset($constraints['archive'])) {
			$constraints['archive'] ? $where[] = 'u.archive!=0' : $where[] = 'u.archive=0';
		}
		if (isset($constraints['active'])) {
			$constraints['active'] ? $where[] = 'u.active=1' : $where[] = 'u.active=0';
		}
		if (isset($constraints['filter']) && $constraints['filter']) {
			$result 	 = eF_describeTable("users");
			$tableFields = array();
			foreach ($result as $value) {
				if ($value['Field'] != 'password' && $value['Field'] != 'timestamp') {
					$tableFields[] = "u.".$value['Field'].' like "%'.$constraints['filter'].'%"';
				}
			}
			$where[] = "(".implode(" OR ", $tableFields).")";
		}
		if (isset($constraints['condition'])) {
			$where[] = $constraints['condition'];
		}
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			if (isset($constraints['branch']) && is_numeric($constraints['branch'])) {
				$where[] = "module_hcd_employee_works_at_branch.branch_ID = '".$constraints['branch']."' AND module_hcd_employee_works_at_branch.assigned = 1";
			} else if (isset($constraints['branch']) && is_array($constraints['branch']) && !empty($constraints['branch'])) {
				$where[] = "module_hcd_employee_works_at_branch.branch_ID in ('".implode("','", $constraints['branch'])."') AND module_hcd_employee_works_at_branch.assigned = 1";
			}
			if (isset($constraints['jobs']) && $constraints['jobs'] != _ALLJOBS && $constraints['jobs'] != '') {
				$where[] = "module_hcd_job_description.description = '".$constraints['jobs']."'";
			}
		} #cpp#endif

		if (isset($constraints['table_filters'])) {
			foreach ($constraints['table_filters'] as $constraint) {
				$where[] = $constraint['condition'];
			}
		}

		return $where;
	}
	private static function addLimitConditionToConstraints($constraints) {
		$limit = '';
		if (isset($constraints['limit']) && eF_checkParameter($constraints['limit'], 'int') && $constraints['limit'] > 0) {
			$limit = $constraints['limit'];
		}
		if ($limit && isset($constraints['offset']) && eF_checkParameter($constraints['offset'], 'int') && $constraints['offset'] >= 0) {
			$limit = $constraints['offset'].','.$limit;
		}
		return $limit;
	}
	private static function addSortOrderConditionToConstraints($constraints) {
		$order = '';
		if (isset($constraints['sort']) && eF_checkParameter($constraints['sort'], 'alnum_with_spaces')) {
			$order = $constraints['sort'];
			if (isset($constraints['order']) && in_array($constraints['order'], array('asc', 'desc'))) {
				$order .= ' '.$constraints['order'];
			}
		}
		return $order;
	}

	public static function convertDatabaseResultToUserObjects($result) {
		$roles = EfrontLessonUser::getRoles();
		$userObjects = array();
		foreach ($result as $value) {
			$userObjects[$value['login']] = EfrontUserFactory::factory($value, false, ($value['role'] ? $roles[$value['role']] : false));
		}
		return $userObjects;
	}
	public static function convertDatabaseResultToUserArray($result) {
		$userArray = array();
		foreach ($result as $value) {
			$userArray[$value['login']] = $value;
		}
		return $userArray;
	}
	
	public static function isOptionVisible($option, $checkLessonMode = true) {	
		$disableMode = $GLOBALS['configuration']['mode_'.$option];		//this option is not disabled (0)
		if (is_null($disableMode) || $disableMode) { //in case it is NULL or 1
			$disableMode = true;
		}
		//$simpleMode = !$GLOBALS['configuration']['simple_mode'] || $GLOBALS['configuration']['mode_'.$option] == 1;	//either we're not in simple mode, or this option is allowed in simple mode		
		if (isset($_SESSION['s_login']) && isset($GLOBALS['currentUser']) && ($GLOBALS['currentUser'] instanceOf EfrontUser)) {
			$simpleMode = !$GLOBALS['currentUser']->user['simple_mode'] || !isset($GLOBALS['configuration']['mode_'.$option]) || $GLOBALS['configuration']['mode_'.$option] == 1;	//either we're not in simple mode, or this option is allowed in simple mode
		}
		if ($_SESSION['s_type'] == 'student') { //student is considered as in complete mode
			$simpleMode = true;
		}
		
		if (isset($_SESSION['s_login']) && isset($GLOBALS['currentUser']) && ($GLOBALS['currentUser'] instanceOf EfrontUser)) {
			$coreAccessMode = !isset($GLOBALS['currentUser']->coreAccess[$option]) || $GLOBALS['currentUser']->coreAccess[$option] != 'hidden';
		} else {
			$coreAccessMode = true;
		}
		if ($checkLessonMode) {
			if (isset($_SESSION['s_lessons_ID']) && isset($GLOBALS['currentLesson']) && ($GLOBALS['currentLesson'] instanceOf EfrontLesson) && $_SESSION['s_type'] != 'administrator' && isset($GLOBALS['currentLesson'] -> options[$option])) {
				$lessonMode = $GLOBALS['currentLesson'] -> options[$option];
				if (is_null($lessonMode) || $lessonMode) { //in case it is NULL or 1
					$lessonMode = true;
				}
			} else {
				$lessonMode = true;
			}	
		} else {
			$lessonMode = true;
		}	
				
		$mode = $simpleMode && $disableMode && $coreAccessMode && $lessonMode;
		return $mode;
	}
}

/**
 * Class for administrator users
 *
 * @package eFront
 */
class EfrontAdministrator extends EfrontUser
{
	/**
	 * Get user information
	 *
	 * This function returns the user information in an array
	 *
	 *
	 * <br/>Example:
	 * <code>
	 * $info = $user -> getInformation();		 //Get lesson information
	 * </code>
	 *
	 * @param string $user The user login to customize lesson information for
	 * @return array The user information
	 * @since 3.5.0
	 * @access public
	 */
	public function getInformation() {
		$languages   = EfrontSystem :: getLanguages(true);
		$info		= array();
		$info['login']			 = $this -> user['login'];
		$info['name']			  = $this -> user['name'];
		$info['surname']		   = $this -> user['surname'];
		$info['fullname']		  = $this -> user['name'] . " " . $this -> user['surname'];
		$info['user_type']		 = $this -> user['user_type'];
		$info['user_types_ID']	 = $this -> user['user_types_ID'];
		$info['student_lessons']   = array();
		$info['professor_lessons'] = array();
		$info['total_lessons']	 = 0;
		$info['total_login_time']  = self :: getLoginTime($this -> user['login']);
		$info['language']		  = $languages[$this -> user['languages_NAME']];
		$info['active']			= $this -> user['active'];
		$info['active_str']		= $this -> user['active'] ? _YES : _NO;
		$info['joined']			= $this -> user['timestamp'];
		$info['joined_str']		= formatTimestamp($this -> user['timestamp'], 'time');
		$info['avatar']			= $this -> user['avatar'];

		return $info;
	}

	public function getRole() {
		return "administrator";
	}

	/*
	 * Social eFront function
	 *
	 * For administrators it should return all users
	 */
	public function getRelatedUsers() {
		$all_users = EfrontUser::getUsers();
		foreach($all_users as $value) {
			if ($value['login'] == $this -> user['login']) {
				unset($all_users[$key]);
				break;
			}
		}
		return $all_users;
	}

	/**
	 *
	 * @return unknown_type
	 */
	public function getLessons() {
		return array();
	}

	public function getIssuedCertificates() {
		return array();
	}

}

/**
 * Class for users that may have lessons
 *
 * @package eFront
 * @abstract
 */
abstract class EfrontLessonUser extends EfrontUser
{
	/**
	 * A caching variable for user types
	 *
	 * @since 3.5.3
	 * @var array
	 * @access private
	 * @static
	 */
	private static $lessonRoles;

	/**
	 * The user lessons array.
	 *
	 * @since 3.5.0
	 * @var array
	 * @access public
	 */
	public $lessons = false;


	/**
	 * Assign lessons to user.
	 *
	 * This function can be used to assign a lesson to the current user. If $userTypes
	 * is specified, then the user is assigned to the lesson using the specified type.
	 * By default, the user basic type is used.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> addLessons(23);						 //Add a signle lesson with id 23
	 * $user -> addLessons(23, 'professor');			//Add a signle lesson with id 23 and set the user type to 'professor'
	 * $user -> addLessons(array(23,24,25));			//Add multiple lessons using an array
	 * $user -> addLessons(array(23,24,25), array('professor', 'student', 'professor'));			//Add multiple lessons using an array for lesson ids and another for corresponding user types
	 * </code>
	 *
	 * @param mixed $lessonIds Either a single lesson id, or an array if ids
	 * @param mixed $userTypes The corresponding user types for the specified lessons
	 * @param boolean $activate Lessons will be set as active or not
	 * @return mixed The array of lesson ids or false if the lesson already exists.
	 * @since 3.5.0
	 * @access public
	 */
	public function addLessons($lessonIds, $userTypes, $activate = 1) {
		if (sizeof($this -> lessons) == 0) {
			$this -> getLessons();
		}
		if (!is_array($lessonIds)) {
			$lessonIds = array($lessonIds);
		}
		if (!is_array($userTypes)) {
			$userTypes = array($userTypes);
		}
		if (sizeof($userTypes) < sizeof($lessonIds)) {
			 $userTypes = array_pad($userTypes, sizeof($lessonIds), $userTypes[0]);
		}
		if (sizeof($lessonIds) > 0) {
			$lessons = eF_getTableData("lessons", "*", "id in (".implode(",", $lessonIds).")");

			foreach ($lessons as $key => $lesson) {
				$lesson = new EfrontLesson($lesson);
				$lesson -> addUsers($this -> user['login'], $userTypes[$key], $activate);
			}

			$this -> lessons = false;	//Reset lessons information
		}
		return $this -> getLessons();
	}


	/**
	 * Confirm user's lessons
	 *
	 * This function can be used to set the "active" flag of a user's lesson to "true", so that
	 * he can access the corresponding lessons.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> confirmLessons(23);						 //Confirms the lesson with id 23
	 * $user -> addLessons(array(23,24,25));			//Confirms multiple lessons using an array
	 * </code>
	 *
	 * @param mixed $lessonIds Either a single lesson id, or an array if ids
	 * @return array The array of lesson ids
	 * @since 3.6.0
	 * @access public
	 */
	public function confirmLessons($lessonIds) {
		if (sizeof($this -> lessons) == 0) {
			$this -> getLessons();
		}
		if (!is_array($lessonIds)) {
			$lessonIds = array($lessonIds);
		}

		$lessons = eF_getTableData("lessons", "*", "id in (".implode(",", $lessonIds).")");
		foreach ($lessons as $key => $lesson) {
			$lesson = new EfrontLesson($lesson);
			$lesson -> confirm($this -> user['login']);
		}

		$this -> lessons = false;	//Reset lessons information

		return $this -> getLessons();
	}


	/**
	 * Remove lessons from user.
	 *
	 * This function can be used to remove a lesson from the current user.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> removeLessons(23);						  //Remove a signle lesson with id 23
	 * $user -> removeLessons(array(23,24,25));			 //Remove multiple lessons using an array
	 * </code>
	 *
	 * @param int $lessonIds Either a single lesson id, or an array if ids
	 * @return int The array of lesson ids.
	 * @since 3.5.0
	 * @access public
	 */
	public function removeLessons($lessonIds) {
		if (!is_array($lessonIds)) {
			$lessonIds = array($lessonIds);
		}

		foreach ($lessonIds as $key => $lessonID) {
			if (!eF_checkParameter($lessonID, 'id')) {
				unset($lessonIds[$key]);										//Remove illegal vaues from lessons array.
			}
		}

		eF_deleteTableData("users_to_lessons", "users_LOGIN = '".$this -> user['login']."' and lessons_ID in (".implode(",", $lessonIds).")");	//delete lessons from list
		foreach ($lessonIds as $lessonId) {
			$cacheKey = "user_lesson_status:lesson:".$lessonId."user:".$this -> user['login'];
			EfrontCache::getInstance()->deleteCache($cacheKey);			
		}

		//Timelines event
		EfrontEvent::triggerEvent(array("type" => EfrontEvent::LESSON_REMOVAL, "users_LOGIN" => $this -> user['login'], "lessons_ID" => $lessonIds));

		$userLessons = eF_getTableDataFlat("users_to_lessons", "lessons_ID, user_type", "users_LOGIN = '".$this -> user['login']."'");
		$this -> lessons = array_combine($userLessons['lessons_ID'], $userLessons['user_type']);

		return $this -> lessons;
	}

	/**
	 * Reset the user's progress in the specified lesson
	 *
	 * @param mixed $lesson The lesson to reset
	 * @since 3.6.3
	 * @access public
	 */
	public function resetProgressInLesson($lesson) {
		if (!($lesson instanceOf EfrontLesson)) {
			$lesson = new EfrontLesson($lesson);
		}

		$tracking_info = array("done_content"	    => "",
							   "issued_certificate" => "",
							   "from_timestamp"		=> time(),
							   "to_timestamp"		=> null,
							   "comments"		    => "",
							   "completed"		    => 0,
							   "current_unit"	    => 0,
							   "score"			    => 0);
		eF_updateTableData("users_to_lessons", $tracking_info, "users_LOGIN='".$this -> user['login']."' and lessons_ID = ".$lesson -> lesson['id']);

		eF_deleteTableData("completed_tests", "users_LOGIN = '".$this -> user['login']."' and tests_ID in (select id from tests where lessons_ID='".$lesson -> lesson['id']."')");
		eF_deleteTableData("scorm_data", "users_LOGIN = '".$this -> user['login']."' and content_ID in (select id from content where lessons_ID='".$lesson -> lesson['id']."')");
		eF_deleteTableData("users_to_content", "users_LOGIN = '".$this -> user['login']."' and content_ID in (select id from content where lessons_ID='".$lesson -> lesson['id']."')");
		eF_deleteTableData("user_times", "users_LOGIN = '".$this -> user['login']."' and lessons_ID=".$lesson -> lesson['id']);
		
		$event = array(	"type"				=> EfrontEvent::LESSON_PROGRESS_RESET,
				"users_LOGIN"		=> $this -> user['login'],
				"lessons_ID"		=> $lesson -> lesson['id'],
				"lessons_name" 		=> $lesson -> lesson['name']);
	
		EfrontEvent::triggerEvent($event);
	}
	
	public function changeProgressInLesson($lesson, $timestamp = false) {
		if (!($lesson instanceOf EfrontLesson)) {
			$lesson = new EfrontLesson($lesson);
		}
		$info = eF_getTableData("users_to_lessons", "completed,score,to_timestamp,comments", "users_LOGIN='".$this -> user['login']."' and lessons_ID = ".$lesson -> lesson['id']);					
		if ($info[0]['completed'] == 0) {
			$this -> completeLesson($lesson -> lesson['id'], 100, '', $timestamp);
			exit;
		} elseif ($timestamp !== false) {
			$new_info = array("to_timestamp"		=> $timestamp);
		} elseif ($timestamp === false){
			$new_info = array("issued_certificate" => "",
							   "to_timestamp"		=> null,
							   "comments"		    => "",
							   "score"				=> 0,
							   "completed"		    => 0);
		}
		eF_updateTableData("users_to_lessons", $new_info, "users_LOGIN='".$this -> user['login']."' and lessons_ID = ".$lesson -> lesson['id']);
	}

	public function resetProgressInAllLessons() {
		$tracking_info = array("done_content"	    => "",
							   "issued_certificate" => "",
							   "from_timestamp"		=> time(),
							   "to_timestamp"		=> null,
							   "comments"		    => "",
							   "completed"		    => 0,
							   "current_unit"	    => 0,
							   "score"			    => 0);
		eF_updateTableData("users_to_lessons", $tracking_info, "users_LOGIN='".$this -> user['login']."'");

		eF_deleteTableData("completed_tests", "users_LOGIN = '".$this -> user['login']."'");
		eF_deleteTableData("scorm_data", "users_LOGIN = '".$this -> user['login']."'");
		eF_deleteTableData("users_to_content", "users_LOGIN = '".$this -> user['login']."'");
		eF_deleteTableData("user_times", "users_LOGIN = '".$this -> user['login']."'");
	
	}

	/**
	 * Reset the user's progress in the specified course
	 *
	 * @param mixed $course The course to reset
	 * @param boolean $resetLessons whether to reset lesson progress as well
	 * @since 3.6.3
	 * @access public
	 */
	public function resetProgressInCourse($course, $resetLessons = false, $keep_certificate = false) {
		if (!($course instanceOf EfrontCourse)) {
			$course = new EfrontCourse($course);
		}
		$tracking_info = array("issued_certificate" => "",
							   "comments"		    => "",
							   "from_timestamp"		=> time(),
							   "to_timestamp"		=> 0,
							   "completed"		    => 0,
							   "score"			    => 0);
		if ($keep_certificate) {
			unset($tracking_info["issued_certificate"]);
		}
		
		eF_updateTableData("users_to_courses", $tracking_info, "users_LOGIN='".$this -> user['login']."' and courses_ID = ".$course -> course['id']);

		if ($resetLessons) {
			foreach ($course -> getCourseLessons() as $lesson) {
				$this -> resetProgressInLesson($lesson);
			}
		}
		
		$event = array(	"type"				=> EfrontEvent::COURSE_PROGRESS_RESET,
						"users_LOGIN"		=> $this -> user['login'],
						"lessons_ID"		=> $course -> course['id'],
						"lessons_name" 		=> $course -> course['name']);
		EfrontEvent::triggerEvent($event);

		foreach (eF_loadAllModules(true, true) as $key => $module) {
			$module -> onResetProgressInCourse($course -> course['id'], $this -> user['login']);
		}

	}

	public function resetProgressInAllCourses($keep_certificate = false) {		
		
		$tracking_info = array("issued_certificate" => "",
							   "comments"		    => "",
							   "from_timestamp"		=> time(),
							   "to_timestamp"		=> 0,
							   "completed"		    => 0,
							   "score"			    => 0);
		if ($keep_certificate) {
			unset($tracking_info["issued_certificate"]);
		}
		
		eF_updateTableData("users_to_courses", $tracking_info, "users_LOGIN='".$this -> user['login']."'");

		foreach (eF_loadAllModules(true, true) as $key => $module) {
			$module -> onResetProgressInAllCourses($this -> user['login']);
		}

	}
	/**
	 * Get the users's lessons list
	 *
	 * This function is used to get a list of ids with the users's lessons.
	 * If $returnObjects is set and true, then An array of lesson objects is returned
	 * The list is returned using the object's cache (unless $returnObjects is true).
	 * <br/>Example:
	 * <code>
	 * $lessonsList	= $user -> getLessons();						 //Returns an array with pairs [lessons id] => [user type]
	 * $lessonsObjects = $user -> getLessons(true);					 //Returns an array of lesson objects
	 * </code>
	 * If $returnObjects is specified, then each lesson in the lessons array will
	 * contain an additional field holding information on the user's lesson status
	 *
	 * @param boolean $returnObjects Whether to return lesson objects
	 * @param string $basicType If set, then return only lessons that the user has the specific basic role in them
	 * @return array An array of [lesson id] => [user type] pairs, or an array of lesson objects
	 * @since 3.5.0
	 * @access public
	 */
	public function getLessons($returnObjects = false, $basicType = false) {
		if ($this -> lessons && !$returnObjects) {
			$userLessons = $this -> lessons;
		} else {
			if ($returnObjects) {
				$userLessons = array();
				//Assign all lessons to an array, this way avoiding looping queries
				$result	 = eF_getTableData("lessons l, users_to_lessons ul", "l.*", "l.archive=0 and l.id=ul.lessons_ID and ul.archive = 0 and ul.users_LOGIN = '".$this -> user['login']."'", "l.name");
				foreach ($result as $value) {
					$lessons[$value['id']] = $value;
				}
				$courseLessons	= array();
				$nonCourseLessons = array();
				$result	  = eF_getTableData("users u,users_to_lessons ul, lessons l", "ul.*, u.user_type as basic_user_type, u.user_types_ID", "l.archive=0 and l.id = ul.lessons_ID and ul.archive=0 and ul.users_LOGIN = u.login and ul.users_LOGIN = '".$this -> user['login']."' and ul.lessons_ID != 0", "l.name");

				foreach ($result as $value) {
					try {
						$lesson = new EfrontLesson($lessons[$value['lessons_ID']]);
						$lesson -> userStatus = $value;
						if ($lesson -> lesson['course_only']) {
							$courseLessons[$value['lessons_ID']] = $lesson;
						} else {
							$nonCourseLessons[$value['lessons_ID']] = $lesson;
						}
					} catch (Exception $e) {}	//Do nothing in case of exception, simply do not take into account this lesson
				}
				$userLessons = $courseLessons + $nonCourseLessons;

			} else {
				$result = eF_getTableDataFlat("users_to_lessons ul, lessons l", "ul.lessons_ID, ul.user_type", "l.archive=0 and ul.archive=0 and ul.lessons_ID=l.id and ul.users_LOGIN = '".$this -> user['login']."'", "l.name");
				if (sizeof($result) > 0) {
					$this -> lessons = array_combine($result['lessons_ID'], $result['user_type']);
				} else {
					$this -> lessons = array();
				}
				foreach ($this -> lessons as $lessonId => $userType) {
					if (!$userType) {													//For some reason, the user type is not set in the database. so set it now
						$userType = $this -> user['user_type'];
						$this -> lessons[$lessonId] = $userType;
						eF_updateTableData("users_to_lessons", array("user_type" => $userType), "lessons_ID=$lessonId and users_LOGIN='".$this -> user['login']."'");
						$cacheKey = "user_lesson_status:lesson:".$lessonId."user:".$this -> user['login'];
						EfrontCache::getInstance()->deleteCache($cacheKey);
					}
				}
				unset($userType);
				$userLessons = $this -> lessons;
			}
		}

		if ($basicType) {
			$roles = EfrontLessonUser :: getLessonsRoles();
			foreach ($userLessons as $id => $role) {
				if ($role instanceof EfrontLesson) {								//$returnObjects is true
					if ($roles[$role -> userStatus['user_type']] != $basicType) {
						unset($userLessons[$id]);
					}
				} else {
					if ($roles[$role] != $basicType) {
						unset($userLessons[$id]);
					}
				}
			}
		}

		return $userLessons;
	}


	//@TODO: REPLACE getLessons
	public function getUserLessons($constraints = array()) {
		//if ($this -> lessons === false) {			//COMMENT-IN WHEN IT REPLACES getLessons()
		$this -> initializeLessons();
		//}
		$lessons = array();

		foreach ($this -> lessons as $key => $lesson) {
			if (!isset($constraints['return_objects']) || $constraints['return_objects']) {
				$lessons[$key] = new EfrontLesson($lesson);
			} else {
				$lessons[$key] = $lesson;
			}
		}

		return $lessons;
	}

	/**
	 * Initialize user lessons
	 *
	 * @since 3.6.1
	 * @access protected
	 */
	private function initializeLessons() {
		$result = eF_getTableData("users_to_lessons ul, lessons l",
								  "ul.*, ul.to_timestamp as timestamp_completed, ul.from_timestamp as active_in_lesson, l.id, l.name, l.directions_ID, l.course_only, l.instance_source, l.duration,l.options,l.to_timestamp,l.from_timestamp, l.active, 1 as has_lesson, l.access_limit",
								  "l.archive = 0 and ul.archive = 0 and l.id=ul.lessons_ID and ul.users_LOGIN='".$this -> user['login']."'","l.name");

		if (empty($result)) {
			$this -> lessons = array();
		} else {
			foreach ($result as $value) {
				$this -> lessons[$value['id']] = $value;
			}
		}

	}

	public function getUserAutonomousLessons($constraints = array()) {
		$lessons = $this -> getUserLessons($constraints);
		foreach ($lessons as $key => $lesson) {
			if ($lesson -> lesson['instance_source']) {
				unset($lessons[$key]);
			}
		}
		return $lessons;
	}

	/**
	 * Get user's eligible lessons
	 *
	 * This function is used to filter the user's lessons, excluding all the lessons
	 * that he is enrolled to, but cannot access for some reason (rules, schedule, active, etc)
	 *
	 * <br/>Example:
	 * <code>
	 * $eligibleLessons = $user -> getEligibleLessons();						 //Returns an array of EfrontLesson objects
	 * </code>
	 *
	 * @return array An array of lesson objects
	 * @since 3.6.0
	 * @access public
	 * @see libraries/EfrontLessonUser#getLessons($returnObjects, $basicType)
	 */
	public function getEligibleLessons() {
		
		$userCourses = $this -> getUserCourses();
		$userLessons = $this -> getUserStatusInLessons(false, true);

		$roles	     = self :: getLessonsRoles();
		$roleNames   = self :: getLessonsRoles(true);

		$constraints = array('archive' => false, 'active' => true, 'return_objects' => false);
		foreach ($userCourses as $course) {
			$courseLessons = array();
			foreach ($course->getCourseLessons($constraints) as $id => $lesson) {
				$courseLessons[$id] = $userLessons[$id];
			}
			
			$eligible = $course -> checkRules($this -> user['login'], $courseLessons);

			foreach ($eligible as $lessonId => $value) {
				if (!$value) {
					unset($userLessons[$lessonId]);
				}
			}
		}

		$eligibleLessons = array();
		foreach ($userLessons as $lesson) {
			if ($lesson -> lesson['active_in_lesson'] && (!isset($lesson -> lesson['eligible']) || (isset($lesson -> lesson['eligible']) && $lesson -> lesson['eligible']))) {
				$eligibleLessons[$lesson -> lesson['id']] = $lesson;
			}
		}

		return $eligibleLessons;

	}

	/**
	 * Get user potential lessons
	 *
	 * This function returns a list with the lessons that the user
	 * may take, but doesn't have. The list may be either a list of ids
	 * (faster) or a list of EfrontLesson objects.
	 * <br/>Example:
	 * <code>
	 * $user -> getNonLessons();			//Returns a list with potential lessons ids
	 * $user -> getNonLessons(true);		//Returns a list of EfrontLesson objects
	 * </code>
	 *
	 * @param boolean $returnObjects Whether to return a list of objects
	 * @return array The list of ids or objects
	 * @since 3.5.0
	 * @access public
	 */
	public function getNonLessons($returnObjects = false) {
		$userLessons = eF_getTableDataFlat("users_to_lessons", "lessons_ID", "archive=0 and users_LOGIN = '".$this -> user['login']."'");
		//sizeof($userLessons) > 0 ? $sql = "and id not in (".implode(",", $userLessons['lessons_ID']).")" : $sql = '';
		sizeof($userLessons) > 0 ? $sql = "active = 1 and id not in (".implode(",", $userLessons['lessons_ID']).")" : $sql = 'active = 1';

		if ($returnObjects) {
			$nonUserLessons = array();
			//$lessons		= eF_getTableData("lessons", "*", "languages_NAME='".$this -> user['languages_NAME']."'".$sql);
			$lessons		= eF_getTableData("lessons", "*", $sql);
			foreach ($lessons as $value) {
				$nonUserLessons[$value['id']]  = new EfrontLesson($value['id']);
			}
			return $nonUserLessons;
		} else {
			//$lessons = eF_getTableDataFlat("lessons", "*", "languages_NAME='".$this -> user['languages_NAME']."'".$sql);
			$lessons = eF_getTableDataFlat("lessons", "*", $sql);
			return $lessons['id'];
		}
	}

	/**
	 * Return only non lessons that can be selected by the student
	 *
	 * This function is similar to getNonLessons, the only difference being that it excludes lessons
	 * that can't be directly assigned, for example inactive, unpublished etc
	 *
	 * @return array The eligible lessons
	 * @since 3.6.0
	 * @access public
	 * @see EfrontLessonUser :: getNonLessons()
	 */
	public function getEligibleNonLessons() {
		$lessons = $this -> getNonLessons(true);
		foreach ($lessons as $key => $lesson) {
			if (!$lesson -> lesson['active'] || !$lesson -> lesson['publish'] || !$lesson -> lesson['show_catalog']) {
				unset($lessons[$key]);
			}
		}
		return $lessons;
	}



	public function getUserCourses($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);

		$select['main'] 	 	 = 'c.id, uc.users_LOGIN,uc.courses_ID,uc.completed,uc.score,uc.user_type,uc.issued_certificate,uc.from_timestamp as active_in_course, uc.to_timestamp, 1 as has_course';
		$select['has_instances'] = "(select count( * ) from courses c1, users_to_courses uc1 where c1.instance_source=c.id and uc1.courses_ID=c1.id and uc.users_LOGIN='".$this -> user['login']."') as has_instances";
		$select['num_lessons']   = "(select count( * ) from lessons_to_courses cl, lessons l where cl.courses_ID=c.id and l.archive=0 and l.id=cl.lessons_ID) as num_lessons";
		$select['num_students']  = "(select count( * ) from users_to_courses uc, users u where uc.courses_ID=c.id and u.archive=0 and u.login=uc.users_LOGIN and u.user_type='student') as num_students";
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$select['num_skills'] = "(select count( * ) from module_hcd_course_offers_skill s where courses_ID=c.id) as num_skills";
			$select['location']   = "(select b.name from module_hcd_branch b, module_hcd_course_to_branch cb where cb.branches_ID=b.branch_ID and cb.courses_ID=c.id limit 1) as location";
		} #cpp#endif

		$select = EfrontCourse :: convertCourseConstraintsToRequiredFields($constraints, $select);
		list($where, $limit, $orderby) = EfrontCourse :: convertCourseConstraintsToSqlParameters($constraints);
		$where[] = "c.id=uc.courses_ID and uc.users_LOGIN='".$this -> user['login']."' and uc.archive=0";
		//$result  = eF_getTableData("courses c, users_to_courses uc", $select, implode(" and ", $where), $orderby, false, $limit);
		$sql	= prepareGetTableData("courses c, users_to_courses uc", implode(",", $select), implode(" and ", $where), $orderby, false, $limit);
		$result = eF_getTableData("courses, ($sql) t", "courses.*, t.*", "courses.id=t.id", "name");

		if (!isset($constraints['return_objects']) || $constraints['return_objects'] == true) {
			return EfrontCourse :: convertDatabaseResultToCourseObjects($result);
		} else {
			return EfrontCourse :: convertDatabaseResultToCourseArray($result);
		}
	}

	public function countUserCourses($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);
		list($where, $limit, $orderby) = EfrontCourse :: convertCourseConstraintsToSqlParameters($constraints);
		$where[] = "c.id=uc.courses_ID and uc.users_LOGIN='".$this -> user['login']."' and uc.archive=0";
		$result  = eF_countTableData("courses c, users_to_courses uc", "c.id", implode(" and ", $where));
		return $result[0]['count'];
	}


	public function getUserCoursesIncludingUnassigned($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);

		$select['main'] = "c.id, r.courses_ID is not null as has_course, r.completed,r.score, r.from_timestamp as active_in_course";
		$select['user_type'] = "(select user_type from users_to_courses uc1 where users_login='".$this -> user['login']."' and uc1.courses_ID=c.id) as user_type";
		$select['has_instances'] = "(select count( * ) from courses l where instance_source=c.id) as has_instances";
		$select['num_lessons'] = "(select count( * ) from lessons_to_courses cl, lessons l where cl.courses_ID=c.id and l.archive=0 and l.id=cl.lessons_ID) as num_lessons";
		$select['num_students'] =  "(select count( * ) from users_to_courses uc, users u where uc.courses_ID=c.id and u.archive=0 and u.login=uc.users_LOGIN and u.user_type='student') as num_students";

		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$select['num_skills'] = "(select count( * ) from module_hcd_course_offers_skill s where courses_ID=c.id) as num_skills";
			$select['location']   = "(select b.name from module_hcd_branch b, module_hcd_course_to_branch cb where cb.branches_ID=b.branch_ID and cb.courses_ID=c.id limit 1) as location";
		} #cpp#endif
		$select = EfrontCourse :: convertCourseConstraintsToRequiredFields($constraints, $select);
		list($where, $limit, $orderby) = EfrontCourse :: convertCourseConstraintsToSqlParameters($constraints);

		//$result  = eF_getTableData("courses c left outer join (select completed,score,courses_ID, from_timestamp,archive from users_to_courses where users_login='".$this -> user['login']."' and archive=0) r on c.id=r.courses_ID ", $select, implode(" and ", $where), $orderby, "", $limit);
		$sql 	= prepareGetTableData("courses c left outer join (select completed,score,courses_ID, from_timestamp,archive from users_to_courses where users_login='".$this -> user['login']."' and archive=0) r on c.id=r.courses_ID ", implode(",", $select), implode(" and ", $where), $orderby, "", $limit);
		$result = eF_getTableData("courses, ($sql) t", "courses.*, t.*", "courses.id=t.id");

		if (!isset($constraints['return_objects']) || $constraints['return_objects'] == true) {
			return EfrontCourse :: convertDatabaseResultToCourseObjects($result);
		} else {
			return EfrontCourse :: convertDatabaseResultToCourseArray($result);
		}
	}

	public function countUserCoursesIncludingUnassigned($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);
		list($where, $limit, $orderby) = EfrontCourse :: convertCourseConstraintsToSqlParameters($constraints);
		//$where[] = "d.id=c.directions_ID";
		$result  = eF_countTableData("courses c left outer join (select completed,score,courses_ID, from_timestamp from users_to_courses where users_login='".$this -> user['login']."' and archive=0) r on c.id=r.courses_ID ", "c.id",
		implode(" and ", $where));

		return $result[0]['count'];
	}


	/**
	 * Get all courses, signifying those that the user already has, and aggregate instance results
	 *
	 * @param array $constraints The constraints for the query
	 * @return array An array of EfrontCourse objects
	 * @since 3.6.2
	 * @access public
	 */
	public function getUserCoursesAggregatingResultsIncludingUnassigned($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);

		if (isset($constraints['active']) && $constraints['active']) {
			$activeSql = 'and c1.active=1';
		} else if (isset($constraints['active']) && !$constraints['active']) {
			$activeSql = 'and c1.active=0';
		} else {
			$activeSql = '';
		}

		$select['main'] 	 	 = 'c.id';
		$select['user_type']	 = "(select user_type from users_to_courses uc1 where users_login='".$this -> user['login']."' and uc1.courses_ID=c.id) as user_type";
		$select['score']		 = "(select max(score) 	 from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 $activeSql and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID) as score";
		$select['completed']	 = "(select max(completed) from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 $activeSql and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID) as completed";
		$select['to_timestamp']  = "(select max(to_timestamp) from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 $activeSql and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID) as to_timestamp";
		$select['active_in_course']  = "(select max(from_timestamp) from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 $activeSql and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID) as active_in_course";
		$select['has_course']    = "(select count(*) > 0   from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 $activeSql and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID) as has_course";
		$select['num_lessons']   = "(select count( * ) from lessons_to_courses cl, lessons l where cl.courses_ID=c.id and l.archive=0 and l.id=cl.lessons_ID) as num_lessons";
		$select['num_students']  = "(select count( * ) from users_to_courses uc, users u where uc.courses_ID=c.id and u.archive=0 and u.login=uc.users_LOGIN and u.user_type='student') as num_students";
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$select['num_skills'] = "(select count( * ) from module_hcd_course_offers_skill s where courses_ID=c.id) as num_skills";
			$select['location']   = "(select b.name from module_hcd_branch b, module_hcd_course_to_branch cb where cb.branches_ID=b.branch_ID and cb.courses_ID=c.id limit 1) as location";
		} #cpp#endif

		$select = EfrontCourse :: convertCourseConstraintsToRequiredFields($constraints, $select);
		list($where, $limit, $orderby) = EfrontCourse :: convertCourseConstraintsToSqlParameters($constraints);
		
		$from = "courses c left outer join (select id from courses) r on c.id=r.id";
		if (isset($constraints['branch_url']) && $_SESSION['s_current_branch']) {
			$from.= ' LEFT OUTER JOIN module_hcd_course_to_branch cb on cb.courses_ID=c.id';
		} 
		//WITH THIS NEW QUERY, WE GET THE SLOW 'has_instances' PROPERTY AFTER FILTERING
		$sql	= prepareGetTableData($from, implode(",", $select), implode(" and ", $where), $orderby, false, $limit);
		$result = eF_getTableData(
					"courses, ($sql) t",
					"courses.*, (select count(id) from courses c1 where c1.instance_source=courses.id ) as has_instances, t.*",
					"courses.id=t.id");

		//THIS WAS THE OLD QUERY, MUCH SLOWER
		//$result  = eF_getTableData("courses c left outer join (select id from courses) r on c.id=r.id", $select, implode(" and ", $where), $orderby, false, $limit);

		if (!isset($constraints['return_objects']) || $constraints['return_objects'] == true) {
			return EfrontCourse :: convertDatabaseResultToCourseObjects($result);
		} else {
			return EfrontCourse :: convertDatabaseResultToCourseArray($result);
		}
	}

	public function countUserCoursesAggregatingResultsIncludingUnassigned($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);
		list($where, $limit, $orderby) = EfrontCourse :: convertCourseConstraintsToSqlParameters($constraints);
		//$where[] = "d.id=c.directions_ID";
		$from = "courses c left outer join (select id from courses) r on c.id=r.id";
		if (isset($constraints['branch_url']) && $_SESSION['s_current_branch']) {
			$from.= ' LEFT OUTER JOIN module_hcd_course_to_branch cb on cb.courses_ID=c.id';
		}
		
		$result  = eF_countTableData($from, "c.id",
		implode(" and ", $where));

		return $result[0]['count'];
	}


	/**
	 * The same as self::getUserCoursesAggregatingResultsIncludingUnassigned, only it has an addition "where" condition
	 * @param array $constraints
	 * @return array
	 * @since 3.6.2
	 */
	public function getUserCoursesAggregatingResults($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);

		$select['main'] 	 	 = 'c.id';
		$select['user_type']	 = "(select user_type from users_to_courses uc1 where users_login='".$this -> user['login']."' and uc1.courses_ID=c.id) as user_type";
		$select['score']		 = "(select max(score) 	 from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID) as score";
		$select['completed']	 = "(select max(completed) from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID) as completed";
		$select['to_timestamp']  = "(select max(to_timestamp) from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID) as to_timestamp";
		$select['active_in_course']  = "(select max(from_timestamp) from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID) as active_in_course";
		$select['has_course']    = "(select count(*) > 0   from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID) as has_course";
		$select['num_lessons']   = "(select count( * ) from lessons_to_courses cl, lessons l where cl.courses_ID=c.id and l.archive=0 and l.id=cl.lessons_ID) as num_lessons";
		$select['num_students']  = "(select count( * ) from users_to_courses uc, users u where uc.courses_ID=c.id and u.archive=0 and u.login=uc.users_LOGIN and u.user_type='student') as num_students";
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$select['num_skills'] = "(select count( * ) from module_hcd_course_offers_skill s where courses_ID=c.id) as num_skills";
			$select['location']   = "(select b.name from module_hcd_branch b, module_hcd_course_to_branch cb where cb.branches_ID=b.branch_ID and cb.courses_ID=c.id limit 1) as location";
		} #cpp#endif

		$select = EfrontCourse :: convertCourseConstraintsToRequiredFields($constraints, $select);
		list($where, $limit, $orderby) = EfrontCourse :: convertCourseConstraintsToSqlParameters($constraints);

		if (isset($constraints['active']) && $constraints['active']) {
			$activeSql = 'and c1.active=1';
		} else if (isset($constraints['active']) && !$constraints['active']) {
			$activeSql = 'and c1.active=0';
		} else {
			$activeSql = '';
		}
		$where[] = "(select count(*) > 0 from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 $activeSql and c1.archive = 0 and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID)=1";

		//WITH THIS NEW QUERY, WE GET THE SLOW 'has_instances' PROPERTY AFTER FILTERING
		$sql	= prepareGetTableData("courses c left outer join (select id from courses) r on c.id=r.id", implode(",", $select), implode(" and ", $where), $orderby, false, $limit);
		$result = eF_getTableData(
					"courses, ($sql) t",
					"courses.*, (select count(id) from courses c1 where c1.instance_source=courses.id ) as has_instances, t.*",
					"courses.id=t.id");

		//THIS WAS THE OLD QUERY, MUCH SLOWER
		//$result  = eF_getTableData("courses c left outer join (select id from courses) r on c.id=r.id", $select, implode(" and ", $where), $orderby, false, $limit);

		if (!isset($constraints['return_objects']) || $constraints['return_objects'] == true) {
			return EfrontCourse :: convertDatabaseResultToCourseObjects($result);
		} else {
			return EfrontCourse :: convertDatabaseResultToCourseArray($result);
		}

	}

	public function countUserCoursesAggregatingResults($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);
		list($where, $limit, $orderby) = EfrontCourse :: convertCourseConstraintsToSqlParameters($constraints);
		$where[] = "d.id=c.directions_ID";
		if (isset($constraints['active']) && $constraints['active']) {
			$activeSql = 'and c1.active=1';
		} else if (isset($constraints['active']) && !$constraints['active']) {
			$activeSql = 'and c1.active=0';
		} else {
			$activeSql = '';
		}
		$where[] = "(select count(*) > 0 from users_to_courses uc1, courses c1 where uc1.users_login='".$this -> user['login']."' and uc1.archive=0 $activeSql and c1.archive = 0 and (c1.instance_source=c.id or c1.id=c.id) and c1.id=uc1.courses_ID)=1";

		$result  = eF_countTableData("directions d,courses c left outer join (select id from courses) r on c.id=r.id", "c.id",
		implode(" and ", $where));

		return $result[0]['count'];
	}

	public function filterCoursesBasedOnInstance($courses, $instanceSource) {
		foreach ($courses as $key => $course) {
			if ($course -> course['instance_source'] != $instanceSource && $course -> course['id'] != $instanceSource) {
				unset($courses[$key]);
			} else {
				$courses[$key] -> course['num_lessons'] = $course -> countCourseLessons();
			}
		}

		return $courses;
	}

	/**
	 * Return only regular courses, not instances.
	 * Assign the completion and highest instance score to the parent course, from its instances.
	 *
	 */
	public function filterCoursesWithInstanceStatus($courses) {

		foreach ($courses as $key => $course) {
			if ($course -> course['instance_source']) {
				$instanceSource = $course -> course['instance_source'];

				if ($course -> course['completed']) {
					$courses[$instanceSource] -> course['completed'] = 1;
					if ($course -> course['score'] > $courses[$instanceSource] -> course['score']) {
						$courses[$instanceSource] -> course['score'] = $course -> course['score'];
					}
				}

				unset($courses[$key]);
			} else {
				$courses[$key] -> course['num_lessons'] = $course -> countCourseLessons();
			}
		}

		return $courses;
	}



	public function getUserStatusInIndependentLessons($onlyContent = false) {
		$result = eF_getTableDataFlat("users_to_lessons ul, lessons l", "lessons_ID", "l.archive=0 and ul.archive=0 and ul.lessons_ID=l.id and ul.users_LOGIN='".$this->user['login']."'");
		
		$userLessons = $this -> getUserStatusInLessons($result['lessons_ID'], $onlyContent);
		foreach ($userLessons as $key => $lesson) {
			if ($lesson -> lesson['course_only']) {
				unset($userLessons[$key]);
			}
		}

		return $userLessons;
	}

	public function getUserStatusInCourseLessons($course, $onlyContent = false) {
		$lessons = array();
		$courseLessons = $course -> getCourseLessons();		
		$userLessons   = $this   -> getUserStatusInLessons(array_keys($courseLessons), $onlyContent);		
		foreach ($courseLessons as $key => $lesson) {			
			if (isset($userLessons[$key])) {
				$lessons[$key] 							= $userLessons[$key];
				$lessons[$key]-> lesson['start_date'] 	= $lesson -> lesson['start_date'];
				$lessons[$key]-> lesson['end_date'] 	= $lesson -> lesson['end_date'];
			}
		}
		return $lessons;
	}

	public function getUserStatusInLessons($lessons = false, $onlyContent = false) {
		$userLessons = $this -> getUserLessons();

		if ($lessons !== false) {
			$lessonIds = $this -> verifyLessonsList($lessons);
			foreach ($lessonIds as $id) {
				if (in_array($id, array_keys($userLessons))) {
					$temp[$id] = $userLessons[$id];
				}
			}
			$userLessons = $temp;
		}

		if (!$onlyContent) {
			$activeTimes = $this->getLessonsActiveTimeForUser();
		}
		foreach ($userLessons as $key => $lesson) {
			$lesson = $this -> checkUserAccessToLessonBasedOnDuration($lesson);			
			if ((!$this -> user['user_types_ID'] && $lesson -> lesson['user_type'] != $this -> user['user_type']) || ($this -> user['user_types_ID'] && $lesson -> lesson['user_type'] != $this -> user['user_types_ID'])) {
				$lesson -> lesson['different_role'] = 1;
			}
			$userLessons[$key] -> lesson['overall_progress'] = $this -> getUserOverallProgressInLesson($lesson);
			if (!$onlyContent) {
				$userLessons[$key] -> lesson['project_status']   = $this -> getUserProjectsStatusInLesson($lesson);
				$userLessons[$key] -> lesson['test_status']	     = $this -> getUserTestsStatusInLesson($lesson);
				$userLessons[$key] -> lesson['time_in_lesson']   = $this -> getUserTimeInLesson($lesson);
				$userLessons[$key] -> lesson['active_time_in_lesson']   = EfrontTimes::formatTimeForReporting($activeTimes[$key]);
			}
		}
		

		return $userLessons;
	}

	private function checkUserAccessToLessonBasedOnDuration($lesson) {
		//pr($lesson);
		if ($lesson -> lesson['duration'] && $lesson -> lesson['active_in_lesson']) {
			$lesson -> lesson['remaining'] = $lesson -> lesson['active_in_lesson'] + $lesson -> lesson['duration']*3600*24 - time();
		} else {
			$lesson -> lesson['remaining'] = null;
		}
		//Check whether the lesson registration is expired. If so, set $value['from_timestamp'] to false, so that the effect is to appear disabled
		if (EfrontUser::isStudentRole($lesson -> lesson['user_type']) && $lesson -> lesson['duration'] && $lesson -> lesson['active_in_lesson'] && $lesson -> lesson['duration'] * 3600 * 24 + $lesson -> lesson['active_in_lesson'] < time()) {
			$lesson -> archiveLessonUsers($lesson -> lesson['users_LOGIN']);
		}
		return $lesson;
	}

	public function archiveUserCourses($courses) {
		$courses = $this -> verifyCoursesList($courses);

		foreach ($courses as $course) {
			$course = new EfrontCourse($course);
			$course -> archiveCourseUsers($this);
		}
		$this -> courses = false;					//Reset users cache
		return $this -> getUserCourses();
	}

	private function verifyCoursesList($courses) {
		if (!is_array($courses)) {
			$courses = array($courses);
		}
		foreach ($courses as $key => $value) {
			if ($value instanceOf EfrontCourse) {
				$courses[$key] = $value -> course['id'];
			} elseif (!eF_checkParameter($value, 'id')) {
				unset($courses[$key]);
			}
		}
		return array_values(array_unique($courses));			//array_values() to reindex array
	}

	private function sendNotificationsRemoveUserCourses($courses) {
		foreach ($courses as $key => $course) {
			$courseIds[] = $key;
		}
		EfrontEvent::triggerEvent(array("type" 		   => EfrontEvent::COURSE_REMOVAL,
										"users_LOGIN"  => $this -> user['login'],
										"lessons_ID"   => $courseIds));
	}

	public function archiveUserLessons($lessons) {
		$lessons = $this -> verifyLessonsList($lessons);
		$this -> sendNotificationsRemoveUserLessons($lessons);

		foreach ($lessons as $lesson) {
			eF_updateTableData("users_to_lessons", array("archive" => time()), "users_LOGIN='".$this -> user['login']."' and lessons_ID=$lesson");
			$cacheKey = "user_lesson_status:lesson:".$lesson."user:".$this -> user['login'];
			EfrontCache::getInstance()->deleteCache($cacheKey);
		}

		$this -> lessons = false;					//Reset users cache
		return $this -> getLessons();
	}

	private function verifyLessonsList($lessons) {
		if (!is_array($lessons)) {
			$lessons = array($lessons);
		}
		foreach ($lessons as $key => $value) {
			if ($value instanceOf EfrontLesson) {
				$lessons[$key] = $value -> lesson['id'];
			} elseif (!eF_checkParameter($value, 'id')) {
				unset($lessons[$key]);
			}
		}
		return array_values(array_unique($lessons));			//array_values() to reindex array
	}

	private function verifyLessonObjectsList($lessons) {
		if (!is_array($lessons)) {
			$lessons = array($lessons);
		}
		$lessonsList = array();
		foreach ($lessons as $value) {
			if (!($value instanceOf EfrontLesson)) {
				$value = new EfrontLesson($value);
				$lessonsList[$value -> lesson['id']] = $value;
			}
		}
		return $lessonsList;
	}

	private function sendNotificationsRemoveUserLessons($lessons) {
		foreach ($lessons as $key => $lesson) {
			$lessonIds[] = $key;
		}
		EfrontEvent::triggerEvent(array("type" 		   => EfrontEvent::LESSON_REMOVAL,
										"users_LOGIN"  => $this -> user['login'],
										"lessons_ID"   => $lessonIds));
	}

	private function getUserTimeInLesson($lesson) {
		$timeReport = new EfrontTimes();
		$userTimes = $timeReport -> getUserSessionTimeInLesson($this -> user['login'], $lesson -> lesson['id']);
		$userTimes = $timeReport -> formatTimeForReporting($userTimes);
		return $userTimes;

	}
	
	private function getActiveUserTimeInLesson($lesson) {
		//$timeReport = new EfrontTimes();
		$userTimes = EfrontLesson::getUserActiveTimeInLesson($this -> user['login'], $lesson -> lesson['id']);
		$userTimes = EfrontTimes::formatTimeForReporting($userTimes);
		return $userTimes;

	}
	
	public function getLessonsActiveTimeForUser() {
		$userLessons = array();
		foreach ($this->getUserLessons(array('return_objects' => false)) as $key=>$value) {
			$userLessons[$key] = 0;
		}
		$result = eF_getTableData("users_to_content", "lessons_ID, sum(total_time) as total_time", "users_LOGIN='".$this->user['login']."'", "", "lessons_ID");
		foreach ($result as $value) {
			if (isset($userLessons[$value['lessons_ID']])) {
				$userLessons[$value['lessons_ID']] = $value['total_time'];
			}
		}
		//Calculate SCORM times, as these are not counted by the system
		$result  = eF_getTableData("scorm_data sd, content c", "c.lessons_ID, sum(total_time) as total_time", "c.id=sd.content_ID and users_LOGIN='".$this->user['login']."'", "", "lessons_ID");
		foreach($result as $value) {
			if (isset($userLessons[$value['lessons_ID']])) {
				$userLessons[$value['lessons_ID']] += $value['total_time'];
			}			
		}

		$result = eF_getTableData("completed_tests ct, tests t", "t.lessons_ID, sum(ct.time_spent) as total_time", "t.id=ct.tests_ID and ct.status!='deleted' and ct.users_LOGIN='".$this->user['login']."'", "", "lessons_ID");
		foreach ($result as $value) {
			if (isset($userLessons[$value['lessons_ID']])) {
				$userLessons[$value['lessons_ID']] += $value['total_time'];
			}
		}
		
		return $userLessons;
	}	

	private function getUserOverallProgressInLesson($lesson) {
		$totalUnits = $completedUnits = 0;

		$contentTree	= new EfrontContentTree($lesson);
		$validUnits		= array();
		foreach ($iterator = new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($contentTree -> tree), RecursiveIteratorIterator :: SELF_FIRST))) as $key => $value) {
			$totalUnits++;
			$validUnits[$key] = $key;
		}
		if ($doneContent = unserialize($lesson -> lesson['done_content'])) {
			$doneContent 	= array_intersect($doneContent,$validUnits);  // to avoid counting deleted content makriria fix
			$completedUnits = sizeof($doneContent);
		}

		if ($totalUnits) {
			$completedUnitsPercentage = round(100 * $completedUnits/$totalUnits, 2);
			return array('total' 	  => $totalUnits,
						 'completed'  => $completedUnits,
						 'percentage' => $completedUnitsPercentage);
		} else {
			return array('total' 	  => 0,
						 'completed'  => 0,
						 'percentage' => 0);
		}

	}

	private function getUserTestsStatusInLesson($lesson) {
		$completedTests = $meanTestScore = 0;

		$tests 		= $lesson -> getTests(true, true);
		$totalTests = sizeof($tests);
//		$result 	= eF_getTableData("completed_tests ct, tests t", "ct.tests_ID, ct.score", "t.id=ct.tests_ID and ct.users_LOGIN='".$this -> user['login']."' and ct.archive=0 and t.lessons_ID=".$lesson -> lesson['id']);
//		pr($result);
		$result 	= eF_getTableData("completed_tests ct, tests t", "ct.archive, ct.tests_ID, ct.score, t.keep_best", "t.id=ct.tests_ID and ct.users_LOGIN='".$this -> user['login']."' and t.lessons_ID=".$lesson -> lesson['id']);
//		pr($result);exit;

		foreach ($result as $value) {
			if (in_array($value['tests_ID'], array_keys($tests))) {
				if ($value['keep_best']) {
					isset($scores[$value['tests_ID']]) OR $scores[$value['tests_ID']] = $value['score'];
					$scores[$value['tests_ID']] = max($scores[$value['tests_ID']], $value['score']);
				} else if ($value['archive'] == 0) {
					$scores[$value['tests_ID']] = $value['score'];
				}
			}
		}
		
/*		
		foreach ($result as $value) {
			if (in_array($value['tests_ID'], array_keys($tests))) {
				$meanTestScore += $value['score'];
				$completedTests++;
			}
		}
*/
		$meanTestScore  = array_sum($scores);
		$completedTests = sizeof($scores);
		
		$scormTests = $this -> getUserScormTestsStatusInLesson($lesson);
		$totalTests += sizeof($scormTests);
		foreach ($scormTests as $value) {
			$meanTestScore += $value;
			$completedTests++;
		}

		if ($totalTests) {
			$completedTestsPercentage = round(100 * $completedTests/$totalTests, 2);
			$meanTestScore 			  = round($meanTestScore/$completedTests, 2);

			return array('total' 	  => $totalTests,
						 'completed'  => $completedTests,
						 'percentage' => $completedTestsPercentage,
						 'mean_score' => $meanTestScore);
		} else {
			return array();
		}
	}

	private function getUserScormTestsStatusInLesson($lesson) {
		$usersDoneScormTests = eF_getTableData("scorm_data sd left outer join content c on c.id=sd.content_ID",
											   "c.id, c.ctg_type, sd.users_LOGIN, sd.masteryscore, sd.lesson_status, sd.score, sd.minscore, sd.maxscore",
											   "c.ctg_type = 'scorm_test' and sd.users_LOGIN = '".$this -> user['login']."' and c.lessons_ID = ".$lesson -> lesson['id']);

		$tests = array();
		foreach ($usersDoneScormTests as $doneScormTest) {
			if (is_numeric($doneScormTest['minscore']) && is_numeric($doneScormTest['maxscore'])) {
				$doneScormTest['score'] = 100 * $doneScormTest['score'] / ($doneScormTest['minscore'] + $doneScormTest['maxscore']);
			} else {
				$doneScormTest['score'] = $doneScormTest['score'];
			}

			$tests[$doneScormTest['id']] = $doneScormTest['score'];
		}

		return $tests;
	}

	private function getUserProjectsStatusInLesson($lesson) {
		$completedProjects = $meanProjectScore = 0;

		$projects 	   = $lesson -> getProjects(true, $this);
		$totalProjects = sizeof($projects);
		foreach ($projects as $project) {
			if ($project -> project['grade'] || $project -> project['grade'] === 0) {
				$completedProjects++;
				$meanProjectScore += $project -> project['grade'];
			}
		}
		if ($totalProjects) {
			$completedProjectsPercentage = round(100 * $completedProjects/$totalProjects, 2);
			$meanProjectScore 			 = round($meanProjectScore/$completedProjects, 2);
			return array('total' 	  => $totalProjects,
						 'completed'  => $completedProjects,
						 'percentage' => $completedProjectsPercentage,
						 'mean_score' => $meanProjectScore);
		} else {
			return array();
		}
	}



	/**
	 * Get user certificates
	 *
	 * This function gets all certificates that have been issued for the user
	 * <br/>Example:
	 * <code>
	 * $user -> getIssuedCertificates();	   //Get an array with the information on the certificates
	 * </code>
	 *
	 * @return an array of the format [] => [course name, certificate key, date issued, date expire, issuing authority]
	 * @since 3.6.1
	 * @access public
	 */
	public function getIssuedCertificates() {
		$constraints  = array('archive' => false, 'active' => true, 'condition' => 'issued_certificate != 0 or issued_certificate is not null');
		$constraints['return_objects'] = false;
		$courses 	  = $this -> getUserCourses($constraints);
		$certificates = array();
		foreach ($courses as $course) {
			if ($certificateInfo = unserialize($course['issued_certificate'])) {
				$certificateInfo = unserialize($course['issued_certificate']);
				$courseOptions = unserialize($course['options']);
				if ($course['certificate_expiration']) {
					$expirationArray				= convertTimeToDays($course['certificate_expiration']);
					$expire_certificateTimestamp 	= getCertificateExpirationTimestamp($certificateInfo['date'], $expirationArray);	
				}
				$certificates[] = array("courses_ID"	 => $course['id'],
										"course_name"	 => $course['name'],
										"serial_number"  => $certificateInfo['serial_number'],
										"grade" 		 => $certificateInfo['grade'],
										"issue_date"  	 => $certificateInfo['date'],
										"active"		 => $course['active'],
										"export_method" => $courseOptions['certificate_export_method'],
										"expiration_date"=> ($course['certificate_expiration']) ? $expire_certificateTimestamp : _NEVER);
			}
		}
		return $certificates;
	}


	/**
	 * Assign courses to user.
	 *
	 * This function can be used to assign a course to the current user. If $userTypes
	 * is specified, then the user is assigned to the course using the specified type.
	 * By default, the user asic type is used.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> addCourses(23);						 //Add a signle course with id 23
	 * $user -> addCourses(23, 'professor');			//Add a signle course with id 23 and set the user type to 'professor'
	 * $user -> addCourses(array(23,24,25));			//Add multiple courses using an array
	 * $user -> addCourses(array(23,24,25), array('professor', 'student', 'professor'));			//Add multiple courses using an array for course ids and another for corresponding user types
	 * </code>
	 *
	 * @param mixed $courseIds Either a single course id, or an array if ids
	 * @param mixed $userTypes The corresponding user types for the specified courses
	 * @param boolean $activeate Courses will be set as active or not
	 * @return mixed The array of course ids or false if the course already exists.
	 * @since 3.5.0
	 * @access public
	 * @todo auto_projects
	 */
	public function addCourses($courses, $roles = 'student', $confirmed = true) {
		$courses = $this -> verifyCoursesList($courses);
		$roles   = EfrontUser::verifyRolesList($roles, sizeof($courses));

		if (sizeof($courses) > 0) {
			$courses = eF_getTableData("courses", "*", "id in (".implode(",", $courses).")");
			foreach ($courses as $key => $course) {
				$course = new EfrontCourse($course);
				$course -> addUsers($this -> user['login'], $roles[$key], $confirmed);
			}
			$this -> courses = false;	//Reset courses information
		}

		return $this -> getUserCourses();
	}

	/**
	 * Confirm user's lessons
	 *
	 * This function can be used to set the "active" flag of a user's lesson to "true", so that
	 * he can access the corresponding lessons.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> addCourses(23);						 //Confirm a signle course with id 23
	 * $user -> addCourses(array(23,24,25));			//Confirm multiple courses using an array
	 * </code>
	 *
	 * @param mixed $courseIds Either a single course id, or an array if ids
	 * @return array The array of course ids
	 * @since 3.6.0
	 * @access public
	 */
	public function confirmCourses($courses) {
		$courses = $this -> verifyCoursesList($courses);
		foreach ($courses as $key => $course) {
			$course = new EfrontCourse($course);
			$course -> confirm($this);
		}
		$this -> courses = false;	//Reset courses information
		return $this -> getUserCourses();
	}


	/**
	 * Remove courses from user.
	 *
	 * This function can be used to remove a course from the current user.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> removeCourses(23);						  //Remove a signle course with id 23
	 * $user -> removeCourses(array(23,24,25));			 //Remove multiple courses using an array
	 * </code>
	 *
	 * @param int $courseIds Either a single course id, or an array if ids
	 * @return true.
	 * @since 3.5.0
	 * @access public
	 */
	public function removeCourses($courses) {
		$courseIds = $this -> verifyCoursesList($courses);

		$result = eF_getTableData("lessons_to_courses lc, users_to_courses uc", "lc.*", "lc.courses_ID=uc.courses_ID and uc.users_LOGIN = '".$this -> user['login']."'");
		foreach ($result as $value) {
			$lessonsToCourses[$value['lessons_ID']][] = $value['courses_ID'];
			$coursesToLessons[$value['courses_ID']][] = $value['lessons_ID'];
		}
		if (!empty($courseIds)) {
			$userLessonsThroughCourse = eF_getTableDataFlat("lessons_to_courses lc, users_to_courses uc", "lc.lessons_ID", "lc.courses_ID=uc.courses_ID and uc.courses_ID in (".implode(",", $courseIds).") and uc.users_LOGIN = '".$this -> user['login']."'");
			$userLessonsThroughCourse = $userLessonsThroughCourse['lessons_ID'];
		}
		eF_deleteTableData("users_to_courses", "users_LOGIN = '".$this -> user['login']."' and courses_ID in (".implode(",", $courseIds).")");	//delete courses from list
		foreach ($courseIds as $id) {
			$cacheKey = "user_course_status:course:".$id."user:".$this -> user['login'];
			EfrontCache::getInstance()->deleteCache($cacheKey);
		}

		EfrontEvent::triggerEvent(array("type" => EfrontEvent::COURSE_REMOVAL, "users_LOGIN" => $this -> user['login'], "lessons_ID" => $courseIds));

		foreach ($userLessonsThroughCourse as $lesson) {
			if (sizeof($lessonsToCourses[$lesson]) == 1) {
				$this -> removeLessons($lesson);
			} else if (sizeof(array_diff($lessonsToCourses[$lesson], $courseIds)) == 0) {
				$this -> removeLessons($lesson);
			}
		}

		return $true;
	}



	/**
	 * Set user role
	 *
	 * This function is used to set the specific role of this user.
	 * <br/>Example:
	 * <code>
	 * $user -> setRole(23, 'simpleUser');		  //Set this user's role to 'simpleUser' for lesson with id 23
	 * $user -> setRole(23);						//Set this user's role to the same as its basic type (for example 'student') for lesson with id 23
	 * $user -> setRole(false, 'simpleUser');	   //Set this user's role to 'simpleUser' for all lessons
	 * $user -> setRole();						  //Set this user's role to the same as its basic type (for example 'student') for all lessons
	 * </code>
	 *
	 * @param int $lessonId The lesson id
	 * @param string $userRole The new user role
	 * @return boolean true if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function setRole($lessonId = false, $userRole = false) {
		if ($userRole) {
			$fields = array("user_type" => $userRole);
		} else {
			$fields = array("user_type" => $this -> user['user_type']);
		}

		if ($lessonId && eF_checkParameter($lessonId, 'id')) {
			eF_updateTableData("users_to_lessons", $fields, "users_LOGIN='".$this -> user['login']."' and lessons_ID=$lessonId");
			$cacheKey = "user_lesson_status:lesson:".$lessonId."user:".$this -> user['login'];
			EfrontCache::getInstance()->deleteCache($cacheKey);
		} else {
			eF_updateTableData("users_to_lessons", $fields, "users_LOGIN='".$this -> user['login']."'");
		}
	}

	/**
	 * Get the user's role
	 *
	 * This function returns the user role for the specified lesson
	 * <br/>Example:
	 * <code>
	 * $this -> getRole(4);								 //Get the role for lesson with id 4
	 * </code>
	 *
	 * @param int $lessonId The lesson id to get the role for
	 * @return string The user role for the lesson
	 * @since 3.5.0
	 * @access public
	 */
	public function getRole($lessonId) {
		$roles = EfrontLessonUser :: getLessonsRoles();
		if ($lessonId instanceof EfrontLesson) {
			$lessonId = $lessonId -> lesson['id'];
		}
		if (in_array($lessonId, array_keys($this -> getLessons()))) {
			$result = eF_getTableData("users_to_lessons", "user_type", "users_LOGIN='".$this -> user['login']."' and lessons_ID=".$lessonId);
			return $roles[$result[0]['user_type']];
		} else {
			return false;
		}
	}

	/**
	 * Get roles applicable to lessons
	 *
	 * This function is used to get the roles in the system, that derive from professor and student
	 * It returns an array where keys are the role ids and values are:
	 * - Either the role basic user types, if $getNames is false (the default)
	 * - or the role Names if $getNames is true
	 * The array is prepended with the 2 main roles, 'professor' and 'student'
	 * <br/>Example:
	 * <code>
	 * $roles = EfrontLessonUser :: getLessonsRoles();
	 * </code>
	 *
	 * @param boolean $getNames Whether to return id/basic user type pairs or id/name pairs
	 * @return array The lesson-oriented roles
	 * @since 3.5.0
	 * @access public
	 * @static
	 */
	public static function getLessonsRoles($getNames = false) {
		//Cache results in self :: $lessonRoles
		if (is_null(self :: $lessonRoles)) {
			$roles	  = eF_getTableDataFlat("user_types", "*", "active=1 AND basic_user_type!='administrator'");	//Get available roles
			self :: $lessonRoles = $roles;
		} else {
			$roles = self :: $lessonRoles;
		}
		if (sizeof($roles) > 0) {
			$getNames ? $roles = array('student' => _STUDENT, 'professor' => _PROFESSOR) + array_combine($roles['id'], $roles['name']) : $roles = array('student' => 'student', 'professor' => 'professor') + array_combine($roles['id'], $roles['basic_user_type']);
		} else {
			$getNames ? $roles = array('student' => _STUDENT, 'professor' => _PROFESSOR) : $roles = array('student' => 'student', 'professor' => 'professor');
		}

		return $roles;
	}

	/**
	* Get the user's role on a course
	*
	* This function returns the user role for the specified course
	*
	* @param int $courseId The course id to get the role for
	* @return string The user role for the course
	* @since 3.6.8
	* @access public
	*/
	public function getCourseRoles($courseId) {//PROTONC
		$roles = EfrontLessonUser::getAvailableRoles();//roles are roles - course or lesson does not matter
		if ($courseId instanceof EfrontCourse) {
			$courseId = $courseId -> course['id'];
		}

		$result = eF_getTableData("users_to_courses", "user_type", "users_LOGIN='".$this -> user['login']."' and courses_ID=".$courseId);
		if (count($result) > 0) {
			return $roles[$result[0]['user_type']];
		} else {
			return false;
		}
	}

	/**
	* Get roles applicable to lessons
	*
	* This function is used to get the roles in the system, that derive from professor and student
	*
	* @param boolean $getNames Whether to return id/basic user type pairs or id/name pairs
	* @return array The lesson-oriented roles
	* @since 3.6.8
	* @access public
	* @static
	*/
	public static function getAvailableRoles($getNames = false) {//PROTONC
		$roles = eF_getTableDataFlat("user_types", "*", "active=1 AND basic_user_type!='administrator'"); //Get available roles
		if (sizeof($roles) > 0) {
			$getNames ? $roles = array('student' => _STUDENT, 'professor' => _PROFESSOR) + array_combine($roles['id'], $roles['name']) : $roles = array('student' => 'student', 'professor' => 'professor') + array_combine($roles['id'], $roles['basic_user_type']);
		} else {
			$getNames ? $roles = array('student' => _STUDENT, 'professor' => _PROFESSOR) : $roles = array('student' => 'student', 'professor' => 'professor');
		}
		return $roles;
	}

	/**
	 * Get student roles
	 *
	 * This function returns an array with student roles, like EfrontLessonUser::getLessonsRoles
	 *
	 * @param boolean $getNames Whether to return id/basic user type pairs or id/name pairs
	 * @return array The lesson-oriented roles
	 * @since 3.6.7
	 * @access public
	 * @static
	 * @see EfrontLessonUser::getLessonsRoles
	 */
	public static function getStudentRoles($getNames = false) {
		$roles 	   = self::getLessonsRoles();
		$roleNames = self::getLessonsRoles(true);
		foreach ($roles as $key => $value) {
			if ($value != 'student') {
				unset($roles[$key]);
				unset($roleNames[$key]);
			}
		}

		if ($getNames) {
			return $roleNames;
		} else {
			return $roles;
		}
	}

	/**
	 * Get professor roles
	 *
	 * This function returns an array with professor roles, like EfrontLessonUser::getLessonsRoles
	 *
	 * @param boolean $getNames Whether to return id/basic user type pairs or id/name pairs
	 * @return array The lesson-oriented roles
	 * @since 3.6.7
	 * @access public
	 * @static
	 * @see EfrontLessonUser::getLessonsRoles
	 */
	public static function getProfessorRoles($getNames = false) {
		$roles 	   = self::getLessonsRoles();
		$roleNames = self::getLessonsRoles(true);
		foreach ($roles as $key => $value) {
			if ($value != 'professor') {
				unset($roles[$key]);
				unset($roleNames[$key]);
			}
		}

		if ($getNames) {
			return $roleNames;
		} else {
			return $roles;
		}
	}
	
	/**
	 * Get lesson users
	 *
	 * This function returns a list with the students of all the lessons in which the current user has a professor role
	 * <br/>Example:
	 * <code>
	 *	  $user = EfrontUserFactory :: factory('professor');
	 *	  $students = $user -> getProfessorStudents();
	 * </code>
	 *
	 * @return array A list of user logins
	 * @since 3.5.0
	 * @access public
	 */
	public function getProfessorStudents() {
		$users = array();
		$result = eF_getTableDataFlat("users_to_lessons", "lessons_ID", "archive=0 and users_LOGIN='".$this->user['login']."' and user_type in ('".implode("','", array_keys(EfrontLessonUser::getProfessorRoles()))."')");
		if (!empty($result['lessons_ID'])) {
			$result = eF_getTableDataFlat("users_to_lessons as ul, users as u", "distinct ul.users_LOGIN", "ul.users_LOGIN=u.login and u.active=1 and ul.archive=0 and ul.user_type in ('".implode("','", array_keys(EfrontLessonUser::getStudentRoles()))."') and ul.lessons_ID in (".implode(",", $result['lessons_ID']).")");
			$users  = array_unique($result['users_LOGIN']);
		}

		return $users;
	}



	/**
	 * Get user information
	 *
	 * This function returns the user information in an array
	 *
	 *
	 * <br/>Example:
	 * <code>
	 * $info = $user -> getInformation();		 //Get lesson information
	 * </code>
	 *
	 * @param string $user The user login to customize lesson information for
	 * @return array The user information
	 * @since 3.5.0
	 * @access public
	 */
	public function getInformation() {
		$languages   = EfrontSystem :: getLanguages(true);
		$info		= array();
		$info['login']			 = $this -> user['login'];
		$info['name']			  = $this -> user['name'];
		$info['surname']		   = $this -> user['surname'];
		$info['fullname']		  = $this -> user['name'] . " " . $this -> user['surname'];
		$info['user_type']		 = $this -> user['user_type'];
		$info['user_types_ID']	 = $this -> user['user_types_ID'];
		$info['student_lessons']   = $this -> getLessons(true, 'student');
		$info['professor_lessons'] = $this -> getLessons(true, 'professor');
		$info['total_lessons']	 = sizeof($this -> getUserLessons());
		$info['total_courses']	 = sizeof($this -> getUserCourses(array('active' => true, 'return_objects' => false)));
		$info['total_login_time']  = self :: getLoginTime($this -> user['login']);
		$info['language']		  = $languages[$this -> user['languages_NAME']];
		$info['active']			= $this -> user['active'];
		$info['active_str']		= $this -> user['active'] ? _YES : _NO;
		$info['joined']			= $this -> user['timestamp'];
		$info['joined_str']		= formatTimestamp($this -> user['timestamp'], 'time');
		$info['avatar']			= $this -> user['avatar'];

		return $info;
	}

	/**
	 * Get user related users
	 *
	 * This function returns all users that related to this user
	 * The relation depends on common lessons
	 *
	 * <br/>Example:
	 * <code>
	 * $myRelatedUsers = $user -> getRelatedUsers();		 //Get related users
	 * </code>
	 *
	 * @return array Of related users logins
	 * @since 3.6.0
	 * @access public
	 */
	public function getRelatedUsers() {

		$myLessons = $this ->getLessons();
		$other_users = eF_getTableDataFlat("users_to_lessons ul, users u", "distinct users_LOGIN" , "u.archive=0 and u.active=1 and ul.users_LOGIN=u.login and ul.archive=0 and lessons_ID IN ('" . implode("','", array_keys($myLessons)) . "') AND users_LOGIN <> '" . $this -> user['login'] . "'");
		$users = $other_users['users_LOGIN'];
		return $users;
	}

	/**
	 * Get the common lessons with a particular user
	 *getUsers(
	 * <br/>Example:
	 * <code>
	 * $common_lessons	= $user -> getCommonLessons('joe'); // find the common lessons between this user and 'joe'
	 * </code>
	 *
	 * @return array with pairs [lessons_id] => [lessons_id, lessons_name] referring to the common lessons of this object's user and user with login=$login
	 * @since 3.6.0
	 * @access public
	 */
	public function getCommonLessons($login) {
		$result = eF_getTableData("users_to_lessons as ul1 JOIN users_to_lessons as ul2 ON ul1.lessons_ID = ul2.lessons_ID JOIN lessons ON ul1.lessons_ID = lessons.id", "lessons.id, lessons.name", "ul1.archive=0 and ul2.archive=0 and ul1.users_LOGIN = '".$this -> user['login']."' AND ul2.users_LOGIN = '".$login."'");
		$common_lessons = array();
		foreach ($result as $common_lesson) {
			$common_lessons[$common_lesson['id']] = $common_lesson;
		}
		return $common_lessons;
	}

	/**
	 * Get skillgap tests to do
	 *
	 * This function returns an array with all skill gap tests assigned to the student
	 * <br/>Example:
	 * <code>
	 * $user -> getSkillgapTests();						   //Set the unit with id 32 in lesson 2 as seen
	 * </code>
	 *
	 * @param No parameters
	 * @return Array of tests in the form [test_id] => [id, test_name]
	 * @since 3.5.2
	 * @access public
	 */
	public function getSkillgapTests() {
		$skillgap_tests = array();
		$result = eF_getTableData("users_to_skillgap_tests JOIN tests ON tests_ID = id", "*", "users_LOGIN = '".$this -> user['login']."' AND publish = 1");
		foreach ($result as $res) {
			$skillgap_tests[$res['id']] = array('id' => $res['id'], 'name' => $res['name'], 'solved' => $res['solved']);
		}
		return $skillgap_tests;
	}



	public function getUserStatusInCourses() {

	}

	public function hasCourse($course) {
		if ($course instanceOf EfrontCourse) {
			$course = $course -> course['id'];
		} elseif (!eF_checkParameter($course, 'id')) {
			throw new EfrontCourseException(_INVALIDID.": $course", EfrontCourseException :: INVALID_ID);
		}
		$result = eF_getTableData("users_to_courses", "courses_ID", "courses_ID=$course and users_LOGIN='".$this -> user['login']."' and archive=0");
		return sizeof($result) > 0;
	}

	public function getUserTypeInCourse($course) {
		if ($course instanceOf EfrontCourse) {
			$course = $course -> course['id'];
		} elseif (!eF_checkParameter($course, 'id')) {
			throw new EfrontCourseException(_INVALIDID.": $course", EfrontCourseException :: INVALID_ID);
		}
		$result = eF_getTableData("users_to_courses", "user_type", "courses_ID=$course and users_LOGIN='".$this -> user['login']."' and archive=0");
		if (!empty($result)) {
			return $result[0]['user_type'];
		} else {
			return false;
		}

	}


	public function hasLesson($lesson) {
		if ($lesson instanceOf EfrontLesson) {
			$lesson = $lesson -> lesson['id'];
		} elseif (!eF_checkParameter($lesson, 'id')) {
			throw new EfrontLessonException(_INVALIDID.": $lesson", EfrontLessonException :: INVALID_ID);
		}
		$result = eF_getTableData("users_to_lessons", "lessons_ID", "lessons_ID=$lesson and users_LOGIN='".$this -> user['login']."' and archive=0");
		return sizeof($result) > 0;
	}

	public function getUserTypeInLesson($lesson) {
		if ($lesson instanceOf EfrontLesson) {
			$lesson = $lesson -> lesson['id'];
		} elseif (!eF_checkParameter($lesson, 'id')) {
			throw new EfrontLessonException(_INVALIDID.": $lesson", EfrontLessonException :: INVALID_ID);
		}
		$result = eF_getTableData("users_to_lessons", "user_type", "lessons_ID=$lesson and users_LOGIN='".$this -> user['login']."' and archive=0");
		if (!empty($result)) {
			return $result[0]['user_type'];
		} else {
			return false;
		}

	}
	
	public function getUserBalance() {
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				$currentEmployee = $this->aspects['hcd'];
				pr($currentEmployee ->getBranches());
			} #cpp#endif
		} #cpp#endif
				
	}
}

/**
 * Class for professor users
 *
 * @package eFront
 */
class EfrontProfessor extends EfrontLessonUser
{

	/**
	 * Delete user
	 *
	 * This function is used to delete a user from the system.
	 * The user cannot be deleted if he is the last system administrator.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> delete();
	 * </code>
	 *
	 * @return boolean True if the user was deleted successfully
	 * @since 3.5.0
	 * @access public
	 */
	public function delete() {
		parent :: delete();

		eF_deleteTableData("users_to_lessons", "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("users_to_courses", "users_LOGIN='".$this -> user['login']."'");
/*
		foreach ($this -> getCourses() as $id => $value) {
			$cacheKey = "user_course_status:course:".$id."user:".$this -> user['login'];
			EfrontCache::getInstance()->deleteCache($cacheKey);
		}
*/
	}
	
	public function getNextLesson($lesson, $course = false, $assumeCurrentLessonCompleted = false) {
		return false;
	}
	
	public function setSeenUnit($unit, $lesson, $seen) {
		return false;
	}
}

/**
 * Class for student users
 *
 * @package eFront
 */
class EfrontStudent extends EfrontLessonUser
{
	/**
	 * Delete user
	 *
	 * This function is used to delete a user from the system.
	 * The user cannot be deleted if he is the last system administrator.
	 * <br/>Example:
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');
	 * $user -> delete();
	 * </code>
	 *
	 * @return boolean True if the user was deleted successfully
	 * @since 3.5.0
	 * @access public
	 */
	public function delete() {
		parent :: delete();
		$userDoneTests = eF_getTableData("done_tests", "id", "users_LOGIN='".$this -> user['login']."'");
		if (sizeof($userDoneTests) > 0) {
			eF_deleteTableData("done_questions",	"done_tests_ID IN (".implode(",", $userDoneTests['id']).")");
			eF_deleteTableData("done_tests",		"users_LOGIN='".$this -> user['login']."'");
		}

		eF_deleteTableData("users_to_lessons",	  "users_LOGIN='".$this -> user['login']."'");
		eF_deleteTableData("users_to_courses",	  "users_LOGIN='".$this -> user['login']."'");
/*
		foreach ($this -> getCourses() as $id => $value) {
			$cacheKey = "user_course_status:course:".$id."user:".$this -> user['login'];
			EfrontCache::getInstance()->deleteCache($cacheKey);
		}
*/
		eF_deleteTableData("users_to_projects",	 "users_LOGIN='".$this -> user['login']."'");
		//eF_deleteTableData("users_to_done_tests",   "users_LOGIN='".$this -> user['login']."'");
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				eF_deleteTableData("users_to_skillgap_tests",   "users_LOGIN='".$this -> user['login']."'");
			} #cpp#endif
		} #cpp#endif
		eF_deleteTableData("completed_tests",   "users_LOGIN='".$this -> user['login']."'");
	}

	/**
	 * Complete lesson
	 *
	 * This function is used to set the designated lesson's status
	 * to 'completed' for the current user.
	 * <br/>Example:
	 * <code>
	 * $user -> completeLesson(5, 87, 'Very good progress');									  //Complete lesson with id 5
	 * </code>
	 *
	 * @param mixed $lesson Either the lesson id, or an EfrontLesson object
	 * @param array $fields Extra fields containing the user score and any comments
	 * @return boolean true if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function completeLesson($lesson, $score = 100, $comments = '', $timestamp = '') {
		if (!($lesson instanceof EfrontLesson)) {
			$lesson = new EfrontLesson($lesson);
		}
		if (in_array($lesson -> lesson['id'], array_keys($this -> getLessons()))) {
			$fields = array('completed'	   => 1,
							'to_timestamp' => $timestamp ? $timestamp : time(),
							'score'		   => str_replace(',','.', $score),
							'comments'	   => $comments);
			eF_updateTableData("users_to_lessons", $fields, "users_LOGIN = '".$this -> user['login']."' and lessons_ID=".$lesson -> lesson['id']);
			//$cacheKey = "user_lesson_status:lesson:".$lesson -> lesson['id']."user:".$this -> user['login'];
			//EfrontCache::getInstance()->deleteCache($cacheKey);

			// Timelines event
			EfrontEvent::triggerEvent(array("type" => EfrontEvent::LESSON_COMPLETION, "users_LOGIN" => $this -> user['login'], "lessons_ID" => $lesson -> lesson['id'], "lessons_name" => $lesson -> lesson['name']));

			if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
				if (!$this -> aspects['hcd']) {
					$this -> aspects['hcd'] = EfrontEmployeeFactory :: factory($this -> user['login']);
				}
				$employee = $this -> aspects['hcd'];

				$newSkills = eF_getTableDataFlat("module_hcd_lesson_offers_skill","skill_ID, specification","lesson_ID = '".$lesson -> lesson['id']."'");
				// The lesson associated skills will *complement* the existing ones - last argument = true
				$employee -> addSkills( $newSkills['skill_ID'],  $newSkills['specification'], array_fill(0, sizeof($newSkills['skill_ID']), $score), true);
			} #cpp#endif


			//Get results in lessons
			$userLessons = array();
			$result = eF_getTableData("users_to_lessons", "lessons_ID,completed,score", "users_LOGIN='".$this -> user['login']."'");
			foreach ($result as $value) {
				if ($userLessons[$value['lessons_ID']] = $value);
			}
			$lessonCourses = $lesson -> getCourses(true);											//Get the courses that this lesson is part of. This way, we can auto complete a course, if it should be auto completed

			//Filter out courses that the student doesn't have
			$result 	   = eF_getTableDataFlat("users_to_courses", "courses_ID", "users_LOGIN='".$this -> user['login']."'");
			$userCourses   = $result['courses_ID'];
			foreach ($lessonCourses as $id => $course) {
				if (!in_array($id, $userCourses)) {
					unset($lessonCourses[$id]);
				}
			}

			//$userStatus = EfrontStats :: getUsersCourseStatus(array_keys($courses), $this -> user['login']);
			foreach ($lessonCourses as $course) {
				if ($course -> options['auto_complete']) {
					$constraints   = array('archive' => false, 'active' => true, 'return_objects' => false);
					$courseLessons = $course -> getCourseLessons($constraints);

					$completed 	   = $score	= array();
					foreach ($courseLessons as $lessonId => $value) {
						$userLessons[$lessonId]['completed'] ? $completed[] = 1 : $completed[] = 0;
						$score[] = $userLessons[$lessonId]['score'];
					}

					if (array_sum($completed) == sizeof($completed)) {									//If all the course's lessons are completed, then auto complete the course, using the mean lessons score
						$this -> completeCourse($course -> course['id'], round(array_sum($score) / sizeof($score)), _AUTOCOMPLETEDCOURSE);
					}
				}
			}

			$modules = eF_loadAllModules();
			foreach ($modules as $module) {
				$module -> onCompleteLesson($lesson -> lesson['id'],$this -> user['login']);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Complete course
	 *
	 * This function is used to set the course status to completed for
	 * the current user. If the course is set to automatically issue a
	 * certificate, the certificate is issued.
	 * <br/>Example:
	 * <code>
	 * $user -> completeCourse(5, 87, 'Very good progress');									  //Complete course with id 5
	 * </code>
	 *
	 * @param Efrontmixed $course Either an EfrontCourse object or a course id
	 * @param int $score The course score
	 * @param string $comments Comments for the course completion
	 * @return boolean True if everything is ok
	 */
	public function completeCourse($course, $score, $comments, $time = '') {
		$time ? $timestamp = $time : $timestamp = time();
		if (!($course instanceof EfrontCourse)) {
			$course = new EfrontCourse($course);
		}
		
		$constraints = array('archive' => false, 'active' => true, 'return_objects' => false);
		$userCourses = $this -> getUserCourses($constraints);
		if (in_array($course -> course['id'], array_keys($userCourses))) {
			//keep completed date when it is set (when only score changed for example)
			$checkCompleted	= $userCourses[$course -> course['id']]['to_timestamp'];
			$fields = array('completed'    => 1,
							'to_timestamp' => $timestamp,
							'score'		   => str_replace(',','.', $score),
							'comments'	   => $comments);
			$where  = "users_LOGIN = '".$this -> user['login']."' and courses_ID=".$course -> course['id'];
			EfrontCourse::persistCourseUsers($fields, $where, $course -> course['id'], $this -> user['login']);

			if (!self::$cached_modules) {
				self::$cached_modules = eF_loadAllModules();
			}
			// Trigger all necessary events. If the function has not been re-defined in the derived module class, nothing will happen
			foreach (self::$cached_modules as $module) {
				$module -> onCompleteCourse($course->course['id'], $this->user['login']);
			}
						
			if ($course -> options['auto_certificate']) {
				$certificate = $course -> prepareCertificate($this -> user['login'], $time);
				$course -> issueCertificate($this -> user['login'], $certificate);
			}
		
			$event = array("type" => EfrontEvent::COURSE_COMPLETION, 
							"users_LOGIN" => $this -> user['login'], 
							"lessons_ID" => $course -> course['id'], 
							"lessons_name" => $course -> course['name'],
							"replace" => true);
			EfrontEvent::triggerEvent($event);

			// Assign the related course skills to the employee
			if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
				if (!$this -> aspects['hcd']) {
					$this -> aspects['hcd'] = EfrontEmployeeFactory :: factory($this -> user['login']);
				}
				$employee = $this -> aspects['hcd'];
				$newSkills = eF_getTableDataFlat("module_hcd_course_offers_skill","skill_ID, specification","courses_ID = '".$course -> course['id']."'");

				// The course associated skills will *complement* the existing ones - last argument = true
				$employee -> addSkills( $newSkills['skill_ID'],  $newSkills['specification'], array_fill(0, sizeof($newSkills['skill_ID']), $score), true);
			} #cpp#endif

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set seen unit
	 *
	 * This function is used to set the designated unit as seen or not seen,
	 * according to $seen parameter. It also sets current unit to be the seen
	 * unit, if we are setting a unit as seen. Otherwise, the current unit is
	 * either leaved unchanged, or, if it matches the unset unit, it points
	 * to another seen unit.
	 * <br/>Example:
	 * <code>
	 * $user -> setSeenUnit(32, 2, true);						   //Set the unit with id 32 in lesson 2 as seen
	 * $user -> setSeenUnit(32, 2, false);						  //Set the unit with id 32 in lesson 2 as not seen
	 * </code>
	 * From version 3.5.2 and above, this function also sets the lesson as completed, if the conditions are met
	 *
	 * @param mixed $unit The unit to set status for, can be an id or an EfrontUnit object
	 * @param mixed $lesson The lesson that the unit belongs to, can be an id or an EfrontLesson object
	 * @param boolean $seen Whether to set the unit as seen or not
	 * @return boolean true if the lesson was completed as well
	 * @since 3.5.0
	 * @access public
	 */
	public function setSeenUnit($unit, $lesson, $seen) {
		if (isset($this -> coreAccess['content']) && $this -> coreAccess['content'] != 'change') {	//If user type is not plain 'student' and is not set to 'change' mode, do nothing
			return true;
		}
		if ($unit instanceof EfrontUnit) {											//Check validity of $unit
			$unit = $unit['id'];
		} elseif (!eF_checkParameter($unit, 'id')) {
			throw new EfrontContentException(_INVALIDID.": $unit", EfrontContentException :: INVALID_ID);
		}
		if ($lesson instanceof EfrontLesson) {										//Check validity of $lesson
			$lesson = $lesson -> lesson['id'];
		} elseif (!eF_checkParameter($lesson, 'id')) {
			throw new EfrontLessonException(_INVALIDID.": $lesson", EfrontLessonException :: INVALID_ID);
		}

		$lessons = $this -> getLessons();
		if (!in_array($lesson, array_keys($lessons))) {								//Check if the user is actually registered in this lesson
			throw new EfrontUserException(_USERDOESNOTHAVETHISLESSON.": ".$lesson, EfrontUserException :: USER_NOT_HAVE_LESSON);
		}

		$result = eF_getTableData("users_to_lessons", "done_content, current_unit", "users_LOGIN='".$this -> user['login']."' and lessons_ID=".$lesson);
		sizeof($result) > 0 ? $doneContent = unserialize($result[0]['done_content']) : $doneContent = array();

		$current_unit = 0;
		if ($seen) {
			$doneContent[$unit] = $unit;
			$current_unit	   = $unit;
		} else {
			if (isset($doneContent[$unit])) { //Because of Fatal error: Cannot unset string offsets error
				unset($doneContent[$unit]); 
			}
			if ($unit == $result[0]['current_unit']) {
				sizeof($doneContent) ? $current_unit = end($doneContent) : $current_unit = 0;
			}
		}
		sizeof($doneContent) ? $doneContent = serialize($doneContent) : $doneContent = null;

		eF_updateTableData("users_to_lessons", array('done_content' => $doneContent, 'current_unit' => $current_unit), "users_LOGIN='".$this -> user['login']."' and lessons_ID=".$lesson);
//		$cacheKey = "user_lesson_status:lesson:".$lesson."user:".$this -> user['login'];
//		EfrontCache::getInstance()->deleteCache($cacheKey);

		if ($current_unit && $seen) {
			EfrontEvent::triggerEvent(array("type" => EfrontEvent::CONTENT_COMPLETION, "users_LOGIN" => $this -> user['login'], "lessons_ID" => $lesson, "entity_ID" => $current_unit));
		}

		//Set the lesson as complete, if it can be.
		$completedLesson = false;
		$userProgress = EfrontStats :: getUsersLessonStatus($lesson, $this -> user['login']);
		$userProgress = $userProgress[$lesson][$this -> user['login']];
		//eF_updateTableData("users_to_lessons", array('progress' => $userProgress['overall_progress']), "users_LOGIN='".$this -> user['login']."' and lessons_ID=".$lesson);
		if ($seen) {
			if ($userProgress['lesson_passed'] && !$userProgress['completed']) {
				$lesson = new EfrontLesson($lesson);
				if ($lesson -> options['auto_complete']) {
					$userProgress['tests_avg_score'] ? $avgScore = $userProgress['tests_avg_score'] : $avgScore = 100;
					$timestamp = _AUTOCOMPLETEDAT.': '.date("Y/m/d, H:i:s");
					$this -> completeLesson($lesson, $avgScore, $timestamp);
					$completedLesson = true;
				}
			}
			
			if (!self::$cached_modules) {
				self::$cached_modules = eF_loadAllModules();
			}
			// Trigger all necessary events. If the function has not been re-defined in the derived module class, nothing will happen
			foreach (self::$cached_modules as $module) {
				$module -> onCompleteUnit($unit, $this->user['login']);
			}				

		}
		return $completedLesson;
	}



	/**
	 * Get the next lesson in row, or in the course, if specified
	 *
	 * @param EfrontLesson $lesson The lesson to account
	 * @param mixed $course The course to regard, or false
	 * @param boolean $assumeCurrentLessonCompleted When calculating the next lesson, we may want to find which one will be the next after we finish the current lesson. Setting this to true will bring that one 
	 * @return int The id of the next lesson in row
	 * @since 3.6.3
	 * @access public
	 */
	public function getNextLesson($lesson, $course = false, $assumeCurrentLessonCompleted = false) {
		$nextLesson = false;
		if ($course) {
			($course instanceOf EfrontCourse) OR $course = new EfrontCourse($course);
			//$courseLessons = $this -> getUserStatusInCourseLessons($course);			
			$result = eF_getTableData("users_to_lessons ul join lessons_to_courses lc on ul.lessons_ID=lc.lessons_ID", "ul.lessons_ID as id,completed", "users_LOGIN='{$this->user['login']}' AND courses_ID={$course->course['id']}");
			$courseLessons = array();
			if ($assumeCurrentLessonCompleted) {
				foreach ($result as $value) {
					$courseLessons[$value['id']] = $value;
					if ($value['id'] == $lesson->lesson['id']) {
						$courseLessons[$value['id']]['completed'] = true;
					}
				}
			}
			
			$eligibility = new ArrayIterator($course -> checkRules($_SESSION['s_login'], $courseLessons));
			while ($eligibility -> valid() && ($key = $eligibility -> key()) != $lesson -> lesson['id']) {
				$eligibility -> next();
			}
			$eligibility -> next();
			
			if ($eligibility -> valid() && $eligibility -> key() && $eligibility -> current()) {
				$nextLesson = $eligibility -> key();
			}
		} else {
			$directionsTree = new EfrontDirectionsTree();
			$userLessons = new ArrayIterator($directionsTree -> getLessonsList($this -> getUserLessons()));
			while ($userLessons -> valid() && ($key = $userLessons -> current()) != $lesson -> lesson['id']) {
				$userLessons -> next();
			}
			$userLessons -> next();

			if ($userLessons -> valid() && $userLessons -> current()) {
				$nextLesson = $userLessons -> current();
			}
		}
		return $nextLesson;
	}

	/**
	 * Get the previous lesson in row, or in the course, if specified
	 *
	 * @param EfrontLesson $lesson The lesson to account
	 * @param mixed $course The course to regard, or false
	 * @return int The id of the previous lesson in row
	 * @since 3.6.3
	 * @access public
	 */
	public function getPreviousLesson($lesson, $course = false) {
		$previousLesson = false;
		if ($course) {
			($course instanceOf EfrontCourse) OR $course = new EfrontCourse($course);
			$eligibility = new ArrayIterator($course -> checkRules($_SESSION['s_login']));

			while ($eligibility -> valid() && ($key = $eligibility -> key()) != $lesson -> lesson['id']) {
				$previous = $key;
				$eligibility -> next();
			}
			
			if (isset($previous) && $previous) {
				$previousLesson = $previous;
			}
		} else {
			$directionsTree = new EfrontDirectionsTree();
			$userLessons = new ArrayIterator($directionsTree -> getLessonsList($this -> getUserLessons()));
			while ($userLessons -> valid() && ($key = $userLessons -> current()) != $lesson -> lesson['id']) {
				$previous = $key;
				$userLessons -> next();
			}

			if (isset($previous) && $previous) {
				$previousLesson = $previous;
			}
		}
		return $previousLesson;
	}

}



/**
 * User Factory class
 *
 * This clas is used as a factory for user objects
 * <br/>Example:
 * <code>
 * $user = EfrontUserFactory :: factory('jdoe');
 * </code>
 *
 * @package eFront
 * @version 3.5.0
 */
class EfrontUserFactory
{
	/**
	 * Construct user object
	 *
	 * This function is used to construct a user object, based on the user type.
	 * Specifically, it creates an EfrontStudent, EfrontProfessor, EfrontAdministrator etc
	 * An optional password verification may take place, if $password is specified
	 * If $user is a login name, the function queries database. Alternatively, it may
	 * use a prepared user array, which is mostly convenient when having to perform
	 * multiple initializations
	 * <br/>Example :
	 * <code>
	 * $user = EfrontUserFactory :: factory('jdoe');			//Use factory function to instantiate user object with login 'jdoe'
	 * $userData = eF_getTableData("users", "*", "login='jdoe'");
	 * $user = EfrontUserFactory :: factory($userData[0]);	  //Use factory function to instantiate user object using prepared data
	 * </code>
	 *
	 * @param mixed $user A user login or an array holding user data
	 * @param string $password An optional password to check against
	 * @param string $forceType Force the type to initialize the user, for example for when a professor accesses student.php as student
	 * @return EfrontUser an object of a class extending EfrontUser
	 * @since 3.5.0
	 * @access public
	 * @static
	 */
	public static function factory($user, $password = false, $forceType = false) {
		if ((is_string($user) || is_numeric($user)) && eF_checkParameter($user, 'login')) {
			$result = eF_getTableData("users", "*", "login='".$user."'");
			if (sizeof($result) == 0) {
				throw new EfrontUserException(_USERDOESNOTEXIST.': '.$user, EfrontUserException :: USER_NOT_EXISTS);
			} else if ($password !== false && $password != $result[0]['password']) {
				throw new EfrontUserException(_INVALIDPASSWORDFORUSER.': '.$user, EfrontUserException :: INVALID_PASSWORD);
			}
			/*
			if (strcmp($result[0]['login'], $user) !=0){
				throw new EfrontUserException(_USERDOESNOTEXIST.': '.$user, EfrontUserException :: USER_NOT_EXISTS);
			}
			*/
			$user = $result[0];
		} elseif (!is_array($user)) {
			throw new EfrontUserException(_INVALIDLOGIN.': '.$user, EfrontUserException :: INVALID_PARAMETER);
		}

		$forceType ? $userType = $forceType : $userType = $user['user_type'];

		switch ($userType) {
			case 'administrator' : $factory = new EfrontAdministrator($user, $password); break;
			case 'professor'	 : $factory = new EfrontProfessor($user, $password);	 break;
			case 'student'	   : $factory = new EfrontStudent($user, $password);   break;
			default: throw new EfrontUserException(_INVALIDUSERTYPE.': "'.$userType.'"', EfrontUserException :: INVALID_TYPE); break;
		}


		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$factory -> aspects['hcd'] = EfrontEmployeeFactory :: factory($factory);
		} #cpp#endif

		return $factory;
	}

}



