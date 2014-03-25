<?php
/**
 * Platform index page
 *
 * This is the index page, allowing for logging in, registering new users,
 * contacting and resetting password
 *
 * @package eFront
 * @version 3.6.0
 */

session_cache_limiter('nocache');
session_start();    //This causes the double-login problem, where the user needs to login twice when already logged in with the same browser

if (!isset($_SESSION['s_login'])) {
	session_regenerate_id();
	setcookie("PHPSESSID", session_id(), 0, false, false, false, true);
}

$path = "../libraries/";
//Automatically redirect to installation page if configuration file is missing
if (!is_file($path."configuration.php")) {                        //If the configuration file does not exist, this is a fresh installation, so redirect to installation page
	is_file("install/index.php") ? header("location:install/index.php") : print('Failed locating configuration file <br/> Failed locating installation directory <br/> Please execute installation script manually <br/>');
	exit;
} else {
	/** Configuration file */
	require_once $path."configuration.php";
}

if ($GLOBALS['configuration']['webserver_auth']) {
	eval('$usernameVar='.$GLOBALS['configuration']['username_variable'].';');
	$currentUser = EfrontUser :: checkWebserverAuthentication();
	$currentUser -> login($currentUser -> user['password'], true);
}

//@todo:temporary here, should leave
$cacheId = null;

$message = $message_type = '';

$benchmark = new EfrontBenchmark($debug_TimeStart);
$benchmark -> set('init');

//Set headers in order to eliminate browser cache (especially IE's)
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("cache-control: no-transform");	//To prevent 3G carriers from compressing the site, which will break all grids

//Delete installation directory after install/upgrade
if (is_dir("install") && isset($_GET['delete_install'])) {
	try {
		$dir = new EfrontDirectory('install');
		$dir -> delete();
	} catch (Exception $e) {
		echo "The installation directory could not be deleted. Please delete it manually or your system security is at risk.";
	}
}

if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	try {	
		if (isset($_GET['logout']) && $GLOBALS['configuration']['facebook_api_key'] && $GLOBALS['configuration']['facebook_secret'] && EfrontFacebook::userConnected()) {
//probably never goes here with new way 			
				$path = "../libraries/";
				//require_once $path . "external/facebook-platform/php/facebook.php";
				require_once $path . "external/facebook/facebook.php";
					
				$facebook   = new Facebook(array(
						'appId'  => $GLOBALS['configuration']['facebook_api_key'],
						'secret' => $GLOBALS['configuration']['facebook_secret'],
						'cookie' => true
				));
				
				$smarty -> assign("T_OPEN_FACEBOOK_SESSION", 1);
				$smarty -> assign("T_FACEBOOK_API_KEY", $GLOBALS['configuration']['facebook_api_key']);
				$smarty -> assign("T_FACEBOOK_LOGOUT", 1);
				
			}
		
	} catch (Exception $e) {
		handleNormalFlowExceptions($e);
	}
} #cpp#endif

if (!$smarty -> is_cached('index.tpl', $cacheId) || !$GLOBALS['configuration']['smarty_caching']) {
	//Get available languages
	$languages = array();

    foreach (EfrontSystem :: getLanguages() as $key => $value) {
        if ($value['active']) {
            $languages[$key] = $value['translation'];
        }
    }
	//ksort($languages);
    $smarty -> assign("T_LANGUAGES", $languages);

    $debug_InitTime = microtime(true) - $debug_TimeStart;
    if ($configuration['cms_page'] != "" && sizeof($_GET) == 0  && file_exists(G_CURRENTTHEMEPATH."external/".$GLOBALS['configuration']['cms_page'].".php")){                                             //if there is cms page and no get parameter defined
        eF_redirect(G_SERVERNAME.G_CURRENTTHEMEURL."external/".$configuration['cms_page'].".php");
    }
    if (isset($_GET['logout']) && !isset($_POST['submit_login'])) {                                                       //If user wants to log out
        if (isset($_SESSION['s_login']) && $_SESSION['s_login']) {
        	try {
        		$user = EfrontUserFactory :: factory($_SESSION['s_login']);
                $user -> logout(session_id());
                if ($GLOBALS['configuration']['logout_redirect']) {
                    strpos($GLOBALS['configuration']['logout_redirect'], 'https://') === 0 || strpos($GLOBALS['configuration']['logout_redirect'], 'http://') === 0 ? header("location:".$GLOBALS['configuration']['logout_redirect']) : header("location:http://".$GLOBALS['configuration']['logout_redirect']);
                }
        	} catch (EfrontUserException $e) {
                unset($_SESSION);
                session_destroy();
                $message = $e -> getMessage();
            }
        }

        if (isset($_GET['reason']) && $_GET['reason']=='timeout') {
            $message      = _YOUHAVELOGGEDOUTBECAUSEYOURSESSIONHASTIMEDOUT;
            $message_type = 'failure';

        }

    }

	//Show information in the selected language
	if (isset($_GET['bypass_language']) && in_array($_GET['bypass_language'], array_keys($languages))) {
		$_SESSION['s_language'] = $_GET['bypass_language'];
	}
	//Keep persisted language across page calls
	if (isset($_SESSION['s_language'])) {
		$smarty -> assign("T_LANGUAGE", $_SESSION['s_language']);
	} else {
		$smarty -> assign("T_LANGUAGE", $GLOBALS['configuration']['default_language']);
	}
}

/*
 * Check if you should input the JS code to
 * trigger sending the next notificatoin emails
 * Since 3.6.0
 */
if (EfrontNotification::shouldSendNextNotifications()) {
	$smarty -> assign("T_TRIGGER_NEXT_NOTIFICATIONS_SEND", 1);
	$_SESSION['send_next_notifications_now'] = 0;	// the msg that triggered the immediate send should be sent now
}

//if there is cms page and no get parameter defined, redirect to the cms page
if ($configuration['cms_page'] != "" && sizeof($_GET) == 0 && file_exists(G_CURRENTTHEMEPATH."external/".$GLOBALS['configuration']['cms_page'].".php")) {   //check also if file exists to prevent from broken link
	//eF_redirect("".G_RELATIVEADMINLINK.$GLOBALS['configuration']['cms_page'].".php");
	eF_redirect("".G_SERVERNAME.G_CURRENTTHEMEURL."external/".$configuration['cms_page'].".php");
}
//The user logged out
if (isset($_GET['logout']) && !isset($_POST['submit_login'])) {
	//session_start();			//Isn't needed here if the head session_start() is in place
	if (isset($_SESSION['s_login']) && $_SESSION['s_login']) {
		try {
			$user = EfrontUserFactory :: factory($_SESSION['s_login']);
			$user -> logout(session_id());
			//Redirect user to another page, if such a configuration setting exists
			if ($GLOBALS['configuration']['logout_redirect']) {
				if ($GLOBALS['configuration']['logout_redirect'] == 'close') {
					echo "<script>window.close();</script>";
				} else {
					strpos($GLOBALS['configuration']['logout_redirect'], 'http://') === 0 ? eF_redirect("".$GLOBALS['configuration']['logout_redirect']) : header("location:http://".$GLOBALS['configuration']['logout_redirect']);
				}
			}
		} catch (EfrontUserException $e) {
			$message = $e -> getMessage();
			$message_type = 'failure';
		}
	}
}

if (!$smarty -> is_cached('index.tpl', $cacheId) || !$GLOBALS['configuration']['smarty_caching']) {
	$blocks = array('login'           => array('title' => _LOGINENTRANCE,   'image' => '32x32/keys.png'),
    				'online'          => array('title' => _USERSONLINE, 	'image' => '32x32/users.png'),
    				'lessons'         => array('title' => _COURSES, 		'image' => '32x32/theory.png'),
                	'selectedLessons' => array('title' => _SELECTEDCOURSES, 'image' => '32x32/shopping_basket.png'),
					'checker' 		  => array('title' => _OPTIONSCHECKER,  'image' => '32x32/success.png'),
    				'news'            => array('title' => _SYSTEMNEWS, 		'image' => '32x32/announcements.png'));
	if (!empty($GLOBALS['currentTheme'] -> layout['positions']['enabled'])) {
		$blocks['links'] =  array('title' => _LINKS, 			'image' => '32x32/generic.png');
	}

	//$customBlocks = unserialize($GLOBALS['configuration']['custom_blocks']);
	if (isset($currentTheme -> layout['custom_blocks']) && is_array($currentTheme -> layout['custom_blocks'])) {
	    $customBlocks = $currentTheme -> layout['custom_blocks'];
	} else {
	    $customBlocks = array();
	}
	//Uncomment the line below if you want custom blocks to be sorted alphabetically in Links block 
	//$customBlocks = eF_multiSort($customBlocks, 'title');

	foreach ($customBlocks as $key => $block) {
		$blocks[$key] = array('title' => $block['title'], 'image' => '32x32/generic.png');
	}
	if (!EfrontUser::isOptionVisible('online_users')) {
		unset($blocks['online']);
	}

	$smarty -> assign("T_CUSTOM_BLOCKS", $customBlocks);
	$smarty -> assign("T_BLOCKS", $blocks);
	$positions = $GLOBALS['currentTheme']->layout['positions'];
	$smarty -> assign("T_POSITIONS", $positions);	
	
	if ($GLOBALS['configuration']['lessons_directory'] == 1 && in_array('lessons', array_merge($positions['leftList'], $positions['rightList'], $positions['centerList']))) {
		if (isset($_SESSION['s_current_branch'])) {
			$branch = new EfrontBranch($_SESSION['s_current_branch']);
			$constraints = array('active' => true, 'archive' => false, 'instance' => false, 'sort' => 'name');
			$courses = $branch->getBranchCoursesIncludingParentBranches($constraints);

			$lessons = array();
		}
		$directionsTree = new EfrontDirectionsTree();
		
		$options		= array('lessons_link' => basename($_SERVER['PHP_SELF']).'?ctg=lesson_info&lessons_ID=',
				'courses_link' => basename($_SERVER['PHP_SELF']).'?ctg=lesson_info&courses_ID=',
				'search'	   => true,
				'catalog'	   => true,
				'url'		   => $_SERVER['PHP_SELF'],
				'collapse'	   => $GLOBALS['configuration']['collapse_catalog'],
				'buy_link'	   => true,
				'course_lessons' => false);
		include("directions_tree.php");
	}
}


/* -------------------------------------------------------Login part-------------------------------------------------------------------*/
if (isset($_GET['autologin']) && eF_checkParameter($_GET['autologin'], 'hex')) {
	try {
		$result 	= eF_getTableDataFlat("users", "login,autologin,password,user_type", "active=1 and autologin !=''");
		$autolinks 	= $result['autologin'];
		$key 		= array_search($_GET['autologin'], $autolinks);
		if ($key !==  false) {
			//pr($result['login'][$key]);
			$user 		= EfrontUserFactory :: factory($result['login'][$key]);
			$pattern 	= $user -> user['login']."_".$user -> user['timestamp'];
			$pattern 	= md5($pattern.G_MD5KEY);
			if (strcmp($pattern, $_GET['autologin']) == 0) {
				$user -> login($user -> user['password'], true);
				if (isset($_GET['lessons_ID']) && eF_checkParameter($_GET['lessons_ID'], 'id')) {
				//check for valid lesson
					setcookie('c_request', $user -> user['user_type'].'.php?lessons_ID='.$_GET['lessons_ID'], time() + 86400, false, false, false, true);
				}
				if (isset($_GET['view_unit']) && eF_checkParameter($_GET['view_unit'], 'id')) {
				//check for valid lesson
					setcookie('c_request', $user -> user['user_type'].'.php?view_unit='.$_GET['view_unit'], time() + 86400, false, false, false, true);
				}
				EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_VISITED, "users_LOGIN" => $user -> user['login'], "users_name" => $user -> user['name'], "users_surname" => $user -> user['surname']));
				loginRedirect($user -> user['user_type']);
				exit;
			}
		}
	} catch (EfrontUserException $e) {}
}

if (isset($_GET['ctg']) && $_GET['ctg'] == "expired") {
	if (isset($_SESSION['s_login'])) {
		$currentUser = EfrontUserFactory :: factory($_SESSION['s_login']);
		$currentUser -> logout(session_id());
	}
	eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=login&message=".urlencode(_YOURSESSIONHASEXPIREDPLEASELOGINAGAIN));
}
if (isset($_COOKIE['cookie_login']) && isset($_COOKIE['cookie_password']) && eF_checkParameter($_COOKIE['cookie_login'], 'login') && $_GET['ctg'] != 'agreement') {
	try {
		$user = EfrontUserFactory :: factory($_COOKIE['cookie_login']);
		$user -> login($_COOKIE['cookie_password'], true);
		if ($GLOBALS['configuration']['show_license_note'] && $user -> user['viewed_license'] == 0) {
			eF_redirect("index.php?ctg=agreement");
		} else {
			// Check if the mobile version of eFront is required - if so set a session variable accordingly
			//eF_setMobile();

            EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_VISITED, "users_LOGIN" => $user -> user['login'], "users_name" => $user -> user['name'], "users_surname" => $user -> user['surname']));
			loginRedirect($user -> user['user_type']);
		}
		exit;
	} catch (EfrontUserException $e) {}
}




/*
 * Make sure that if a user has registered lessons without being logged in,
 * after he logs in he will be redirected to the "complete registration" page
 * In addition, set "login_mode" to 1, meaning that the user pressed the "continue"
 * button in his cart, so the next step should be loging in
 */
if (isset($_GET['register_lessons'])) {
	if (!$_SESSION['s_login']) {
		$message  = _PLEASELOGINTOCOMPLETEREGISTRATION;
		if ($GLOBALS['configuration']['signup']) {
			$message .= _OTHERWISEPLEASEREGISTER;
		}
		$message_type = 'success';
	}
//	setcookie('c_request', 'index.php?register_lessons=1&checkout=1', time() + 300);
	$_SESSION['login_mode'] = '1';
} elseif (!isset($_GET['ctg']) || $_GET['ctg'] == 'lessons') {
//	setcookie('c_request', '', time() - 86400);
	$_SESSION['login_mode'] = '0';
}

isset($_GET['ctg']) && $_GET['ctg'] == 'login' ? $postTarget = basename($_SERVER['PHP_SELF'])."?ctg=login" : $postTarget = basename($_SERVER['PHP_SELF'])."?index_page";
//isset($_GET['ctg']) && $_GET['ctg'] == 'login' ? $postTarget = ($_SERVER['SCRIPT_NAME'])."?ctg=login" : $postTarget = ($_SERVER['SCRIPT_NAME'])."?index_page";

$form = new HTML_QuickForm("login_form", "post", $postTarget, "", "class = 'indexForm'", true);
$form -> removeAttribute('name');
$form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');           //Register this rule for checking user input with our function, eF_checkParameter


$form -> addElement('text', 'login', _LOGIN, 'class = "inputText" id = "login_box"');
$form -> addRule('login', _THEFIELD.' "'._LOGIN.'" '._ISMANDATORY, 'required', null, 'client');
$form -> addRule('login', _INVALIDLOGIN, 'checkParameter', 'login');

$form -> addElement('password', 'password', _PASSWORD, 'class = "inputText" tabindex = "0"');
$form -> addRule('password', _THEFIELD.' "'._PASSWORD.'" '._ISMANDATORY, 'required', null, 'client');

$form -> addElement('checkbox', 'remember', _KEEPMELOGGEDIN, null, 'class = "inputCheckbox" style = "vertical-align:middle"');
$form -> addElement('submit', 'submit_login', _ENTER, 'class = "flatButton"');
$form->disable_csrf = true;

if ($form -> isSubmitted() && $form -> validate()) {
	try {
		
		$user = EfrontUserFactory :: factory(trim($form -> exportValue('login')));

		if ($GLOBALS['configuration']['lock_down'] && $user -> user['user_type'] != 'administrator') {
			eF_redirect("index.php?message=".urlencode(_LOCKDOWNONLYADMINISTRATORSCANLOGIN)."&message_type=failure");
			exit;
		}

		if ($_SESSION['s_current_branch']) {
			$branch = new EfrontBranch($_SESSION['s_current_branch']);  
			$branchUsers = $branch -> getBranchTreeUsers();
			if ($user->user['user_type'] != 'administrator' && (empty($branchUsers) || in_array($user -> user['login'], array_keys($branchUsers)) === false)) {
				eF_redirect("index.php?message=".urlencode(_YOUARENOTAMEMBEROFTHISBRANCH));				
			}
		} else if ($user->user['user_type'] != 'administrator' && !$GLOBALS['configuration']['allow_direct_login']) {
			eF_redirect("index.php?message=".urlencode(_YOUCANONLYLOGINFROMYOURBRANCHURL));
		}
		
		$user -> login($form -> exportValue('password'));
		
		//Check whether there are any fields that must be filled in by the user
		$result = eF_getTableData("user_profile", "name,type", "active=1 and mandatory = 2");
		foreach ($result as $value) {
			if (($user -> user[$value['name']] == '' || is_null($user -> user[$value['name']])) && $value['type'] != 'branchinfo') {
				$_SESSION['missing_fields'] = 1;
			}
		}
		
		if ($form -> exportValue('remember')) {                                                             //The user asked to remember login (it is implemented with cookies)
			$expire = time() + 30 * 86400;                                                                  //1 month
			setcookie("cookie_login",    $_SESSION['s_login'],    $expire, false, false, false, true);
			setcookie("cookie_password", $_SESSION['s_password'], $expire, false, false, false, true);
		} else {
			setcookie("cookie_login",    '', time() - 3600);
			setcookie("cookie_password", '', time() - 3600);
		}

		// Check if the mobile version of eFront is required - if so set a session variable accordingly
		//eF_setMobile();

		if ($GLOBALS['configuration']['force_change_password'] && !$user -> isLdapUser && $user -> user['need_pwd_change']) {
			eF_redirect("index.php?ctg=password_change");
		} else if ($GLOBALS['configuration']['show_license_note'] && $user -> user['viewed_license'] == 0) {
			eF_redirect("index.php?ctg=agreement");
		} elseif ($_SESSION['login_mode']) {
		    eF_redirect("index.php?ctg=checkout&checkout=1");
		} else {
			EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_VISITED, "users_LOGIN" => $user -> user['login'], "users_name" => $user -> user['name'], "users_surname" => $user -> user['surname']));
			loginRedirect($user -> user['user_type']);
		}
		exit;
	} catch (EfrontUserException $e) {
		if ($GLOBALS['configuration']['activate_ldap']) {
			if (!extension_loaded('ldap')) {
				$message      = $e -> getMessage().'<br/>'._LDAPEXTENSIONNOTLOADED;
				$message_type = 'failure';
			} else {
				$result = eF_checkUserLdap($form -> exportValue('login'), $form -> exportValue('password'));
				if ($result) {                                                //The user exists in the LDAP server
					$_SESSION['ldap_user_pwd'] = $form -> exportValue('password');	//Keep the password temporarily in the session, it will be used in the next step
					eF_redirect("index.php?ctg=signup&ldap=1&login=".$form -> exportValue('login'));
				} else {
					$message      = _LOGINERRORPLEASEMAKESURECAPSLOCKISOFF;
					$message_type = 'failure';
				}
			}
		} elseif ($e -> getCode() == EfrontUserException :: USER_PENDING) {
			$message = $e -> getMessage();
			$message_type = 'failure';
		} elseif ($e -> getCode() == EfrontUserException :: USER_INACTIVE) {
			$message = $e -> getMessage();
			$message_type = 'failure';
		}
		else {
			if ($GLOBALS['configuration']['ban_failed_logins'] && $user -> user['login'] != '') {
				$fields_insert = array('users_LOGIN' => $user -> user['login'],
			  		'timestamp'   => time(),
			   		'action'	     => 'failed_login',
			   		'session_ip'  => eF_encodeIP($_SERVER['REMOTE_ADDR']));
				eF_insertTableData("logs", $fields_insert);
				$res_ban = eF_getTableData("logs", "count(id) as ct", "users_LOGIN='".$user -> user['login']."' and action='failed_login'");
				if ($res_ban[0]['ct'] >= 5 && $user -> user['user_type'] != 'administrator') {
					$user -> deactivate();	
				}
			}
			$message      = _LOGINERRORPLEASEMAKESURECAPSLOCKISOFF;
			$message_type = 'failure';
		}
		$form -> setConstants(array("login" => $values['login'], "password" => ""));
	} catch (Exception $e) {
	    $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
	    $message      = $e -> getMessage().' &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
	    $message_type = failure;
	}
}
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

$form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
$form -> setRequiredNote(_REQUIREDNOTE);
$form -> accept($renderer);

$smarty -> assign('T_LOGIN_FORM', $renderer -> toArray());

if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
    /** Facebook connect ***/
	if (($GLOBALS['configuration']['social_modules_activated'] & FB_FUNC_LOGGING)) {
		try {

			if ($GLOBALS['configuration']['facebook_api_key'] && $GLOBALS['configuration']['facebook_secret']) {
				$facebook_inteface = 1;
				
				$cookie = get_new_facebook_cookie($GLOBALS['configuration']['facebook_api_key'], $GLOBALS['configuration']['facebook_secret']);

				//$facebook_user = json_decode(file_get_contents('https://graph.facebook.com/me?access_token='.$cookie['access_token']));
				
				if (!isset($_GET['logout']) && !empty($cookie['user_id'])) {
					$smarty -> assign("T_NO_FACEBOOK_LOGIN", 1);
					$facebook_inteface = 0;
					
					
					try {
						new EfrontFacebook($cookie['user_id']);
						$eF_user = eF_getTableData("facebook_connect JOIN users ON users.login = facebook_connect.users_LOGIN", "users_LOGIN, password", "fb_uid='".$cookie['user_id']."'");
						//pr($eF_user);
						
						
						$user = EfrontUserFactory :: factory($eF_user[0]['users_LOGIN']);
						$user -> login($eF_user[0]['password'], true);

						if ($GLOBALS['configuration']['show_license_note'] && $user -> user['viewed_license'] == 0) {
							eF_redirect("index.php?ctg=agreement");
						} else {
							EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_VISITED, "users_LOGIN" => $user -> user['login'], "users_name" => $user -> user['name'], "users_surname" => $user -> user['surname']));
							loginRedirect($user -> user['user_type']);
						}
						exit;
					} catch (Exception $e) {
						if (!isset($_GET['ctg']) && ($e instanceof EfrontUserException || $e instanceof EfrontFacebookException)) {

							// User logged in through facebook but does not currently have an account in eFront - prompt to ask what to do
							$name = $_SESSION['facebook_details']['first_name'];
							$fb_form_existing = new HTML_QuickForm("facebook_login_form", "post", basename($_SERVER['PHP_SELF']), "_self", "class = 'indexForm'", true);
							$fb_form_existing -> registerRule('checkParameter', 'callback', 'eF_checkParameter');           //Register this rule for checking user input with our function, eF_checkParameter

							$fb_form_existing -> addElement('text', 'fb_existing_login', _EXISTINGLOGIN, 'class = "inputText"');
							//$fb_form_existing -> addRule('fb_existing_login', _THEFIELD.' "'._LOGIN.'" '._ISMANDATORY, 'required', null, 'client');
							$fb_form_existing -> addRule('fb_existing_login', _YOUCANNOTLOGINAS .'"'.$_POST['login'].'"', 'checkParameter', 'login');

							$fb_form_existing -> addElement('password', 'fb_existing_password', _PASSWORD, 'class = "inputText" tabindex = "0"');
							//$fb_form_existing -> addRule('fb_existing_password', _THEFIELD.' "'._PASSWORD.'" '._ISMANDATORY, 'required', null, 'client');

							$fb_form_existing -> addElement('submit', 'submit_login_existing', _ENTER, 'class = "flatButton"');

							if ($fb_form_existing -> isSubmitted() && $fb_form_existing -> validate()) {
								try {
									$user = EfrontUserFactory :: factory(trim($fb_form_existing -> exportValue('fb_existing_login')));
									if ($GLOBALS['configuration']['lock_down'] && $user -> user['user_type'] != 'administrator') {
										eF_redirect("index.php?message=".urlencode(_LOCKDOWNONLYADMINISTRATORSCANLOGIN)."&message_type=failure");
										exit;
									}
									$user -> login($fb_form_existing -> exportValue('fb_existing_password'));
									if ($fb_form_existing -> exportValue('remember')) {                                                             //The user asked to remeber login (it is implemented with cookies)
										$expire = time() + 30 * 86400;                                                                  //1 month
										setcookie("cookie_login",    $_SESSION['s_login'],    $expire, false, false, false, true);
										setcookie("cookie_password", $_SESSION['s_password'], $expire, false, false, false, true);
									} else {
										setcookie("cookie_login",    '', time() - 3600);
										setcookie("cookie_password", '', time() - 3600);
									}

									// Check if the mobile version of eFront is required - if so set a session variable accordingly
									//eF_setMobile();

									if ($GLOBALS['configuration']['show_license_note'] && $user -> user['viewed_license'] == 0) {
										eF_redirect("index.php?ctg=agreement");
									} else {
										EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_VISITED, "users_LOGIN" => $user -> user['login'], "users_name" => $user -> user['name'], "users_surname" => $user -> user['surname']));
									
										loginRedirect($user -> user['user_type']);
									}
									exit;
								} catch (EfrontUserException $e) {	
									if ($GLOBALS['configuration']['activate_ldap']) {
										if (!extension_loaded('ldap')) {
											$message      = $e -> getMessage().'<br/>'._LDAPEXTENSIONNOTLOADED;
											$message_type = 'failure';
										} else {
											$result = eF_checkUserLdap($fb_form_existing -> exportValue('login'), $fb_form_existing -> exportValue('password'));
											if ($result) {                                                //The user exists in the LDAP server
												eF_redirect("index.php?ctg=signup&ldap=1&login=".$fb_form_existing -> exportValue('login'));
											} else {
												$message      = _LOGINERRORPLEASEMAKESURECAPSLOCKISOFF;
												$message_type = 'failure';
											}
										}
									} elseif ($e -> getCode() == EfrontUserException :: USER_PENDING) {
										$message = $e -> getMessage();
										$message_type = 'failure';
									} elseif ($e -> getCode() == EfrontUserException :: USER_INACTIVE) {
										$message = $e -> getMessage();
										$message_type = 'failure';
									}
									else {
										$message      = _LOGINERRORPLEASEMAKESURECAPSLOCKISOFF;
										$message_type = 'failure';
									}
									$fb_form_existing -> setConstants(array("fb_existing_login" => $values['login'], "fb_existing_password" => ""));
								}
							}
							$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

							$fb_form_existing -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
							$fb_form_existing -> setRequiredNote(_REQUIREDNOTE);
							$fb_form_existing -> accept($renderer);

							$smarty -> assign('T_EXISTING_USER_LOGIN_FORM', $renderer -> toArray());

							// If external login option enabled
							if ($GLOBALS['configuration']['social_modules_activated'] & FB_FUNC_LOGGING) {

								$fb_form_new = new HTML_QuickForm("facebook_login_new_form", "post", basename($_SERVER['PHP_SELF']), "", "class = 'indexForm'", true);
								$fb_form_new -> registerRule('checkParameter', 'callback', 'eF_checkParameter');           //Register this rule for checking user input with our function, eF_checkParameter

								$fb_form_new -> addElement('text', 'fb_new_login', _NEWLOGIN, 'class = "inputText"');
								//$fb_form_new -> addRule('fb_new_login', _THEFIELD.' "'._LOGIN.'" '._ISMANDATORY, 'required', null, 'client');
								$fb_form_new -> addRule('fb_new_login', _YOUCANNOTLOGINAS .'"'.$_POST['login'].'"', 'checkParameter', 'login');

								$fb_form_new -> addElement('password', 'fb_new_password', _PASSWORD, 'class = "inputText" tabindex = "0"');
								//$fb_form_new -> addRule('fb_new_password', _THEFIELD.' "'._PASSWORD.'" '._ISMANDATORY, 'required', null, 'client');
								$fb_form_new -> addElement('password', 'fb_new_passrepeat', _REPEATPASSWORD, 'class = "inputText "');
								//$form -> addRule('passrepeat', _THEFIELD.' '._REPEATPASSWORD.' '._ISMANDATORY, 'required', null, 'client');
								$fb_form_new -> addRule(array('fb_new_password', 'fb_new_passrepeat'), _PASSWORDSDONOTMATCH, 'compare', null, 'client');
								$fb_form_new -> addElement('text', 'fb_new_email', _EMAILADDRESS, 'class = "inputText"');

								$fb_form_new -> addElement('submit', 'submit_login_new', _REGISTER, 'class = "flatButton"');

								if ($fb_form_new -> isSubmitted() && $fb_form_new -> validate()) {
									$values = $fb_form_new -> exportValues();

									// Create array from normal user data
									$users_content = array('login'          => $values['fb_new_login'],
			                                       'name'           => $_SESSION['facebook_details']['first_name'],
			                                       'surname'        => $_SESSION['facebook_details']['last_name'],
			                                       'active'         => 1,
			                                       'email'          => $values['fb_new_email'],
			                                       'password'       => $values['fb_new_password'],
			                                       'user_type'      => "student",
			                					   'timezone'		=> $GLOBALS['configuration']['time_zone'],
			                                       'user_types_ID'  => 0);

									// Insert the user into the database
									try {
										EfrontUser :: createUser($users_content);

										//exit;
										// Enter after logging new account
										try {
											$user = EfrontUserFactory :: factory(trim($values['fb_new_login']));

											if ($GLOBALS['configuration']['lock_down'] && $user -> user['user_type'] != 'administrator') {
												eF_redirect("index.php?message=".urlencode(_LOCKDOWNONLYADMINISTRATORSCANLOGIN)."&message_type=failure");
												exit;
											}
											$user -> login($values['fb_new_password']);

											setcookie("cookie_login",    '', time() - 3600);
											setcookie("cookie_password", '', time() - 3600);

											// Check if the mobile version of eFront is required - if so set a session variable accordingly
											//eF_setMobile();

											if ($GLOBALS['configuration']['show_license_note'] && $user -> user['viewed_license'] == 0) {
												eF_redirect("index.php?ctg=agreement");
											} else {
												EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_VISITED, "users_LOGIN" => $user -> user['login'], "users_name" => $user -> user['name'], "users_surname" => $user -> user['surname']));
												loginRedirect($user -> user['user_type']);
											}
											exit;
										} catch (EfrontUserException $e) {
											if ($GLOBALS['configuration']['activate_ldap']) {
												if (!extension_loaded('ldap')) {
													$message      = $e -> getMessage().'<br/>'._LDAPEXTENSIONNOTLOADED;
													$message_type = 'failure';
												} else {
													$result = eF_checkUserLdap($fb_form_new -> exportValue('login'), $fb_form_new -> exportValue('password'));
													if ($result) {                                                //The user exists in the LDAP server
														eF_redirect("index.php?ctg=signup&ldap=1&login=".$fb_form_new -> exportValue('login'));
													} else {
														$message      = _LOGINERRORPLEASEMAKESURECAPSLOCKISOFF;
														$message_type = 'failure';
													}
												}
											} elseif ($e -> getCode() == EfrontUserException :: USER_PENDING) {
												$message = $e -> getMessage();
												$message_type = 'failure';
											} elseif ($e -> getCode() == EfrontUserException :: USER_INACTIVE) {
												$message = $e -> getMessage();
												$message_type = 'failure';
											}
											else {
												$message      = _LOGINERRORPLEASEMAKESURECAPSLOCKISOFF;
												$message_type = 'failure';
											}
											$fb_form_new -> setConstants(array("fb_new_login" => $values['fb_new_login'], "fb_new_password" => ""));
										}
									} catch (Exception $e) {
										$message      = _USERCOULDNOTBECREATED.': '.$e -> getMessage().'('.$e -> getCode().')';
										$message_type = 'failure';
									}



									$fb_form_new -> setConstants(array("fb_new_login" => $values['login'], "fb_new_password" => ""));
								}

								$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

								$fb_form_new -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
								$fb_form_new -> setRequiredNote(_REQUIREDNOTE);
								$fb_form_new -> accept($renderer);

								$smarty -> assign('T_NEW_FACEBOOK_USER_LOGIN_FORM', $renderer -> toArray());

								$smarty -> assign("T_FACEBOOK_EXTERNAL_LOGIN",1);
								$smarty->assign("T_FACEBOOK_LOGIN_WELCOME", _WELCOME . " " . $name . "! " . _FACEBOOKLOGININFO);
							} else {
								$smarty->assign("T_FACEBOOK_LOGIN_WELCOME", _WELCOME . " " . $name . "! " . _FACEBOOKLOGININFOONLYMERGE);
							}


							$smarty -> assign("T_FACEBOOK_ACCOUNT_MERGE_POPUP", 1);

						}
					}

				} else {
					$smarty -> assign("T_OPEN_FACEBOOK_SESSION", 1);
					$smarty -> assign("T_FACEBOOK_API_KEY", $GLOBALS['configuration']['facebook_api_key']);
				}
			} else {
				$message = _FACEBOOKCONNECTENABLEDBUTNOAPPPROVIDED;
				$message_type = 'failure';
			}
		}catch (Exception $e) {
			handleNormalFlowExceptions($e);
		}
	}
} #cpp#endif

/* -----------------End of Login part-----------------------------*/
if (isset($_GET['ctg']) && $_GET['ctg'] == 'agreement' && $_SESSION['s_login']) { //Display license agreement

	try {
		$_SESSION['s_index_comply'] = 'agreement';
		$user = EfrontUserFactory :: factory($_SESSION['s_login']);
		$agreementForm = new HTML_QuickForm("agreement_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=agreement", "", "class = 'indexForm'", true);

		$agreementForm -> addElement('submit', 'submit_decline', _NOTACCEPTANDEXIT, 'class = "flatButton"');
		$agreementForm -> addElement('submit', 'submit_accept', _ACCEPTANDCONTINUE, 'class = "flatButton"');

		if ($agreementForm -> isSubmitted() && $agreementForm -> validate()) {
			$values = $agreementForm -> exportValues();
			if ($values['submit_accept']) {
				$user -> user['viewed_license'] = 1;
				$user -> persist();
				unset($_SESSION['s_index_comply']);

				EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_VISITED, "users_LOGIN" => $user -> user['login'], "users_name" => $user -> user['name'], "users_surname" => $user -> user['surname']));
				if ($_SESSION['login_mode']) {
					eF_redirect("index.php?ctg=checkout&checkout=1");
				}
				loginRedirect($user -> user['user_type']);
			} else {
				$user -> logout(session_id());
				eF_redirect("index.php");
			}
		}

		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
		$agreementForm -> accept($renderer);
		$smarty -> assign('T_AGREEMENT_FORM', $renderer -> toArray());
	} catch (Exception $e) {
		eF_redirect("index.php?message=".urlencode($e -> getMessage()." (".$e -> getCode().")")."&message_type=failure");
	}
} else if (isset($_GET['ctg']) && $_GET['ctg'] == 'password_change' && $_SESSION['s_login']) {
	try {
		$_SESSION['s_index_comply'] = 'password_change';
		
		$user = EfrontUserFactory :: factory($_SESSION['s_login']);
		$changePasswordForm = new HTML_QuickForm("change_password_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=password_change", "", "class = 'indexForm'", true);

		$changePasswordForm -> addElement('password', 'old_password', _OLDPASSWORD, 'class = "inputText"');
		$changePasswordForm -> addElement('password', 'password', _NEWPASSWORD, 'class = "inputText"');
		$changePasswordForm -> addElement('password', 'passrepeat', _REPEATPASSWORD, 'class = "inputText"');
		$changePasswordForm -> addRule('password', _THEFIELD.' '._PASSWORD.' '._ISMANDATORY, 'required', null, 'client');
		$changePasswordForm -> addRule('passrepeat', _THEFIELD.' '._REPEATPASSWORD.' '._ISMANDATORY, 'required', null, 'client');
		$changePasswordForm -> addRule(array('password', 'passrepeat'), _PASSWORDSDONOTMATCH, 'compare', null, 'client');
		$changePasswordForm -> addRule('passrepeat', str_replace("%x", $GLOBALS['configuration']['password_length'], _PASSWORDMUSTBE6CHARACTERS), 'minlength', $GLOBALS['configuration']['password_length'], 'client');

		$changePasswordForm -> addElement('submit', 'submit', _SUBMIT, 'class = "flatButton"');

		if ($changePasswordForm -> isSubmitted() && $changePasswordForm -> validate()) {
			$newPassword = $changePasswordForm -> exportValue('password');
			$newPassword = EfrontUser :: createPassword($newPassword);
			if ($user -> user['password'] != EfrontUser :: createPassword($changePasswordForm -> exportValue('old_password'))) {
				$message = _OLDPASSWORDWRONG;
				$message_type = 'failure';
			}else if ($user -> user['password'] == $newPassword) {
				$message = _PASSWORDISTHESAME;
				$message_type = 'failure';
			} else {
				$user -> user['password'] = $newPassword;
				$user -> user['need_pwd_change'] = 0;
				$user -> persist();
				unset($_SESSION['s_index_comply']);
				if ($GLOBALS['configuration']['show_license_note'] && $user -> user['viewed_license'] == 0) {
					eF_redirect("index.php?ctg=agreement");
				} else {
					EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_VISITED, "users_LOGIN" => $user -> user['login'], "users_name" => $user -> user['name'], "users_surname" => $user -> user['surname']));
					loginRedirect($user -> user['user_type']);
				}
			}
		}

		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
		$changePasswordForm  -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
		$changePasswordForm  -> setRequiredNote(_REQUIREDNOTE);
		$changePasswordForm -> accept($renderer);
		$smarty -> assign('T_CHANGE_PASSWORD_FORM', $changePasswordForm -> toArray());
	} catch (Exception $e) {
		eF_redirect("index.php?message=".urlencode($e -> getMessage()." (".$e -> getCode().")")."&message_type=failure");
	}
}

/* ---------------------------------------------------------Activation by email part--------------------------------------------------------- */

if (isset($_GET['account']) && isset($_GET['key']) && eF_checkParameter($_GET['account'], 'login') && eF_checkParameter($_GET['key'], 'timestamp')) {

	if (($configuration['activation'] == 0 && $configuration['mail_activation'] == 1) || $configuration['supervisor_mail_activation'] == 1) {
		$result = eF_getTableData("users", "timestamp, active", "login='".$_GET['account']."'");
		if ($result[0]['active'] == 0 && $result[0]['timestamp'] == $_GET['key']) {
			try {
				$user = EfrontUserFactory :: factory($_GET['account']);//new EfrontUser($_GET['login']);
				$user -> activate();
				if ($_GET['activatedBy']) {
					$message      = _EMPLOYEEACCOUNTSUCCESSFULLYACTIVATED;
				} else {
					$message      = _ACCOUNTSUCCESSFULLYACTIVATED;
				}
				$message_type = 'success';
				eF_redirect(''.basename($_SERVER['PHP_SELF']).'?message='.urlencode($message).'&message_type=success');
			} catch (EfrontException $e) {
				$message      = _PROBLEMACTIVATINGACCOUNT.': '.$e -> getMessage().' ('.$e -> getCode().')';
				$message_type = 'failure';
			}
		}
	} else {
		$message      = _YOUCANNOTACCESSTHISPAGE;
		eF_redirect(''.basename($_SERVER['PHP_SELF']).'?message='.urlencode($message).'&message_type=failure');
	}

}

/* ---------------------------------------------------------Reset Password part--------------------------------------------------------- */
if (isset($_GET['ctg']) && $_GET['ctg'] == 'reset_pwd' && $GLOBALS['configuration']['password_reminder'] && !$GLOBALS['configuration']['only_ldap']) {                                                         //The user asked to display the contact form
	if (eF_checkSpam() == true) {
		$message      = _SPAMDETECTION;
		$message_type = 'failure';
		eF_redirect(basename($_SERVER['PHP_SELF']).'?message='.urlencode($message).'&message_type='.$message_type);	
	}
	$smarty -> assign('T_CTG', 'reset_pwd');

	$form = new HTML_QuickForm("reset_password_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=reset_pwd", "", "class = 'indexForm'", true);
	$form -> removeAttribute('name');
	$form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');           //Register this rule for checking user input with our function, eF_checkParameter

	$form -> addElement('text', 'login_or_pwd', _LOGINOREMAIL, 'class = "inputText"');
	$form -> addRule('login_or_pwd', _THEFIELD.' '._ISMANDATORY, 'required', null, 'client');
	$form -> addRule('login_or_pwd', _INVALIDFIELDDATA, 'checkParameter', 'text');
	$form -> addElement('submit', 'submit_reset_password', _SUBMIT, 'class="flatButton"');


	if ($form -> isSubmitted() && $form -> validate()) {
		$input = $form -> exportValue("login_or_pwd");
		$fields_insert = array('users_LOGIN' => 'visitor',
					   'timestamp'   => time(),
					   'action'	     => 'forms',
					   'comments'	 => 'reset_password',
					   'session_ip'  => eF_encodeIP($_SERVER['REMOTE_ADDR']));
		eF_insertTableData("logs", $fields_insert);
		try {
			if (eF_checkParameter($input, 'email')) {                                               //The user entered an email address
				$result = eF_getTableData("users", "login", "email='".$input."'");                  //Get the user stored login
				if (sizeof($result) > 1) {
					$message 	  = _MORETHANONEUSERWITHSAMEMAILENTERLOGIN;
					$message_type = 'failure';
					eF_redirect(''.basename($_SERVER['PHP_SELF']).'?ctg=reset_pwd&message='.urlencode($message).'&message_type='.$message_type);
					exit;
				} else {
					$user = EfrontUserFactory :: factory($result[0]['login']);
				}
			} elseif (eF_checkParameter($input, 'login')) {                                         //The user entered his login name
				$user = EfrontUserFactory :: factory($input);
			}
			if ($user -> isLdapUser) {
				eF_redirect(''.basename($_SERVER['PHP_SELF']).'?message='.urlencode(_LDAPUSERMUSTCONTACTADMIN.$GLOBALS['configuration']['system_email']).'&message_type=failure');
			} else {
            	EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_FORGOTTEN_PASSWORD, "users_LOGIN" => $user->user['login'], "users_name" => $user->user['name'], "users_surname" => $user->user['surname']));
				$message 	  = _ANEMAILHASBEENSENT;
				$message_type = 'success';
				if ($_SESSION['login_mode'] != 1) {
					eF_redirect(''.basename($_SERVER['PHP_SELF']).'?message='.urlencode($message).'&message_type='.$message_type);
				}
			}
		} catch (Exception $e) {
			$message      = _NONEXISTINGMAIL;
			$message_type = 'failure';
			eF_redirect(''.basename($_SERVER['PHP_SELF']).'?ctg=reset_pwd&message='.urlencode($message).'&message_type='.$message_type);
		}
	} elseif (isset($_GET['id']) && isset($_GET['login'])) {                             //Second stage, user received the email and clicked on the link
		$login = $_GET['login'];
		if (!eF_checkParameter($login, 'login')) {                                      //Possible hacking attempt: malformed user
			$message      = _INVALIDUSER;
			$message_type = 'failure';
		} else {
			$user = eF_getTableData("users", "email, name", "login='".$login."'");
			if (strcmp($_GET['id'], EfrontUser::createPassword($login)) == 0 && sizeof($user) > 0) {
				$password           = implode("", array_map(create_function('$v', 'return chr($v);'), array_rand(array_flip(array_merge(range(48, 57), range(64, 90), range(97, 122))), 10)));
				$password_encrypted = EfrontUser::createPassword($password);
				eF_updateTableData("users", array('password' => $password_encrypted), "login='$login'");

                EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_NEW_PASSWORD_REQUEST, "users_LOGIN" => $login, "entity_name" => $password));
                $message      = _EMAILWITHPASSWORDSENT;
                eF_redirect(''.basename($_SERVER['PHP_SELF']).'?message='.urlencode($message).'&message_type=success');
			} else {
				$message      = _INVALIDUSER;
				$message_type = 'failure';
			}
		}
	}

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

	$renderer -> setRequiredTemplate(
       '{$html}{if $required}
            &nbsp;<span class = "formRequired">*</span>
        {/if}');

	$form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
	$form -> setRequiredNote(_REQUIREDNOTE);
	$form -> accept($renderer);

	$smarty -> assign('T_RESET_PASSWORD_FORM', $renderer -> toArray());

}
/* -------------------------------------------------------End of Reset Password part--------------------------------------------------------- */

/* -----------------------------------------------------Sign up part--------------------------------------------------------- */
if (isset($_GET['ctg']) && ($_GET['ctg'] == "signup") && $configuration['signup']) {
	if (eF_checkSpam() == true) {
		$message      = _SPAMDETECTION;
		$message_type = 'failure';
		eF_redirect(basename($_SERVER['PHP_SELF']).'?message='.urlencode($message).'&message_type='.$message_type);	
	}
	if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
		if (isset($_GET['postAjaxRequest'])) {
			try {
				$currentBranch = new EfrontBranch($_GET['branch']);
		    	if (isset($_GET['getJobSelect'])) {
			        $ar= $currentBranch -> createJobDescriptionsSelect($attributes);
		    	} else if (isset($_GET['getSupervisorsSelect'])) {
		    		$ar= $currentBranch -> createSupervisorsSelect($_GET['only_existing']);
		    	}
		        foreach ($ar as $val=>$element) {
		            echo $val."<option>".$element."<option>";
		        }

		        exit;
			} catch (Exception $e) {

			}
		}

	} #cpp#endif

	$users = eF_countTableData("users", "login", "active=1 and archive=0");
	if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	    if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
	    	$versionUsers = (int) $GLOBALS['configuration']['version_users'];
	        if ($users[0]['count'] >= $versionUsers && $versionUsers > 0) {
	            eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=contact&limit_reached=1&message=".urlencode(_MAXIMUMUSERSLIMITREACHEDCONTACTUS)."&message_type=failure");
	        }
	    } #cpp#endif
	} #cpp#endif

	$smarty -> assign("T_CTG", "signup");

	$form = new HTML_QuickForm("signup_register_personal_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=signup".(isset($_GET['ldap']) ? '&ldap=1' : ''), "", "class = 'indexForm'", true);
	$form -> removeAttribute('name');
	$form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');           //Register this rule for checking user input with our function, eF_checkParameter
	$form -> registerRule('checkNotExist', 'callback', 'eF_checkNotExist');             //This rule is using our function, eF_checkNotExist, to ensure that no duplicate values are inserted in unique fields, such as login and email
	$form -> registerRule('checkRule', 'callback', 'eF_checkRule');           //Register this rule for checking user input with our function, eF_checkParameter
	
    $form -> addElement('text', 'login', _LOGIN, (isset($_GET['ldap']) ? 'class = "inputText inactiveElement" readonly' : 'class = "inputText"'));
    $form -> addRule('login', _THEFIELD.' '._LOGIN.' '._ISMANDATORY, 'required', null, 'client');
    $form -> addRule('login', _THEFIELD.' "'._LOGIN.'" '._MUSTBESMALLERTHAN.' 50 '.mb_strtolower(_CHARACTERS), 'maxlength', 50, 'client');
    $form -> addRule('login', _THEFIELD.' '._LOGIN.' '._HASINVALIDCHARACTERS.'. '._ONLYALLOWEDCHARACTERSLOGIN, 'checkParameter', 'login');
    $form -> addRule('login', _THELOGIN.' &quot;'.($form -> exportValue('login')).'&quot; '._ALREADYEXISTS, 'checkNotExist', 'login');

	$form -> addElement(isset($_GET['ldap']) ? 'text' : 'password', 'password', _PASSWORD, 'class = "inputText"');
	$form -> addElement(isset($_GET['ldap']) ? 'text' : 'password', 'passrepeat', _REPEATPASSWORD, 'class = "inputText"');
	$form -> addRule('password', _THEFIELD.' '._PASSWORD.' '._ISMANDATORY, 'required', null, 'client');
	$form -> addRule('passrepeat', _THEFIELD.' '._REPEATPASSWORD.' '._ISMANDATORY, 'required', null, 'client');
	$form -> addRule(array('password', 'passrepeat'), _PASSWORDSDONOTMATCH, 'compare', null, 'client');
	if (!$_GET['ldap']) {		//For LDAP registrations, this rule does not hold true
		$form -> addRule('passrepeat', str_replace("%x", $GLOBALS['configuration']['password_length'], _PASSWORDMUSTBE6CHARACTERS), 'minlength', $GLOBALS['configuration']['password_length'], 'client');
	}
    $form -> addElement('text', 'firstName', _FIRSTNAME, 'class = "inputText"');
    $form -> addRule('firstName', _THEFIELD.' '._FIRSTNAME.' '._ISMANDATORY, 'required', null, 'client');
    $form -> addRule('firstName', _THEFIELD.' "'._FIRSTNAME.'" '._MUSTBESMALLERTHAN.' 50 '.mb_strtolower(_CHARACTERS), 'maxlength', 50, 'client');
    $form -> addRule('firstName', _THEFIELD.' '._FIRSTNAME.' '._HASINVALIDCHARACTERS.'. '._ONLYALLOWEDCHARACTERSTEXT, 'checkParameter', 'text');

	$form -> addElement('text', 'lastName', _LASTNAME, 'class = "inputText"');
    $form -> addRule('lastName', _THEFIELD.' '._LASTNAME.' '._ISMANDATORY, 'required', null, 'client');
    $form -> addRule('lastName', _THEFIELD.' "'._LASTNAME.'" '._MUSTBESMALLERTHAN.' 50 '.mb_strtolower(_CHARACTERS), 'maxlength', 50, 'client');
    $form -> addRule('lastName', _THEFIELD.' '._LASTNAME.' '._HASINVALIDCHARACTERS.'. '._ONLYALLOWEDCHARACTERSTEXT, 'checkParameter', 'text');

	$form -> addElement('text', 'email', _EMAILADDRESS, 'class = "inputText "');
    $form -> addRule('email', _THEFIELD.' '._EMAILADDRESS.' '._ISMANDATORY, 'required', null, 'client');
    $form -> addRule('email', _THEFIELD.' '._EMAILADDRESS.' '._HASINVALIDCHARACTERS.'. '._ONLYALLOWEDCHARACTERSTEXT, 'email', null, 'client');
    //$form -> addRule('email', _THEEMAIL.' &quot;'.($form -> exportValue('email')).'&quot; '._ALREADYEXISTS, 'checkNotExist', 'email');

	$languages = array();
	foreach (EfrontSystem :: getLanguages() as $key => $value) {
		if ($value['active']) {
			$languages[$key] = $value['translation'];
		}
	}
	$form -> addElement('select', 'languages_NAME', _LANGUAGE, $languages, 'class = "inputSelect" onchange = "location = \'index.php?ctg=signup&bypass_language=\'+this.options[this.selectedIndex].value"');     //A select drop down for languages
	if ($_SESSION['s_language']) {
		$form -> setDefaults(array('languages_NAME' => $_SESSION['s_language']));                                       //The default language is also the selected one
	} else {
		$form -> setDefaults(array('languages_NAME' => $GLOBALS['configuration']['default_language']));                                       //The default language is also the selected one
	}
	if ($GLOBALS['configuration']['onelanguage']) {
		$form -> freeze(array('languages_NAME'));
	}

	if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE

			require_once "../libraries/module_hcd_tools.php";
		} #cpp#endif

	    $user_profile      = eF_getTableData("user_profile", "*", "active=1 and visible=1", "field_order");    //Get admin-defined form fields for user registration
	    $userProfileFields = array();
	    foreach ($user_profile as $field) {                             //Add custom fields, defined in user_profile database table

	    	if ($field['type'] != 'branchinfo' && $field['type'] != 'groupinfo') {
	        	$userProfileFields[] = $field['name'];
	    		switch ($field['type']) {
					case 'select':
						$form -> addElement("select", $field['name'], $field['description'], unserialize($field['options']), 'class = " inputSelect"');
						break;
					case 'checkbox':
						$form -> addElement("advcheckbox", $field['name'], $field['description'], null, 'class = "inputCheckbox"', array(0,1));
						break;
					case 'text':
						$form -> addElement("text", $field['name'], $field['description'], 'class = "inputText"');
						break;
					case 'textarea':
						$element = $form -> addElement("textarea", $field['name'], $field['description'], 'class = "inputText"');
						$element -> setCols(40);
						$element -> setRows(2);						
						break;
					case 'date':
						$options = unserialize($field['options']);
						$form -> addElement(EfrontEntity::createDateElement($form, $field['name'], $field['description'], array('minYear' => $options['year_range_from'], 'maxYear' => $options['year_range_to'], 'include_time' => $options['include_time'])));
						break;
					default: break;
				}
				if ($field['mandatory']) {
					$form -> addRule($field['name'], _THEFIELD.' "'.$field['description'].'" '._ISMANDATORY, 'required', null, 'client');
				}
				if ($field['rule']) {
					$form -> addRule($field['name'], _INVALIDFIELDDATA, 'checkRule', $field['rule']);
				}
				if ($field['type'] == 'date' && $field['default_value'] == 0) {
					$form -> setDefaults(array($field['name'] => time()));
				} else {
					$form -> setDefaults(array($field['name'] => $field['default_value']));
				}
	    	} elseif ($field['type'] == 'branchinfo') {
	    		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
	    			$loadScripts[] = 'includes/index';

	    			$options = unserialize($field['options']);

		            //$branches1 = eF_getTableData("module_hcd_branch", "branch_ID, name, father_branch_ID","","father_branch_ID ASC,branch_ID ASC");
	            	//$branches = eF_createBranchesTreeSelect($branches1, 0);
	            	
	            	$branchesTree = new EfrontBranchesTree();
	            	$pathStrings = $branchesTree -> toPathStringShortened();
	            	if (!empty($pathStrings)) {
	            		$branches = array(0=>'')+$pathStrings;
	            	} else {
	            		$branches = array(0=>'');
	            	}
	            	
			        /** Create the branches selects according to this employee's data **/
			        if (!empty($branches)) {
			        	$userProfileFields[] = $field['name'].'_branches';
			        	$form -> addElement('select', $field['name'].'_branches' , $options['name'], $branches ,'class = "inputSelect" id="'.$field['name'].'_branches" name="'.$field['name'].'_branches"  onchange="javascript:change_branch(\''.$field['name'].'_branches\', \''.$field['name'].'_jobs\');change_supervisors(\''.$field['name'].'_branches\',\''.$field['name'].'_supervisors\');"');

			        	if ($field['mandatory']) {
				            $form -> addRule($field['name'].'_branches', _THEFIELD.' '.$options['name'].' '._ISMANDATORY, 'required', null, 'client');
				        }

			        	//IE does not support options.disabled.... Special treatment needed
			        	if ($options['job']['description'] != "") {
				        	$userProfileFields[] = $field['name'].'_jobs';

				        	if ($options['job']['description'] != "") {
				        		if ($options['job']['hidden'] == 1) {
				        			$hidden_job = ' style="display:none" ';
				        			$job_description_field = "";
				        		} else {
				        			$hidden_job = ' ';
				        			$job_description_field = $options['job']['description'];
				        		}
					            //if (MSIE_BROWSER) {
					            //    $form -> addElement('select', $field['name'].'_jobs' , $job_description_field, array("" => _PLEASESELECT . " " .$options['name']), 'class="inputSelect" id= "'. $field['name'].'_jobs" onFocus = "this.selIndex = this.selectedIndex;" onChange="restoreSelection(this);" id= "jobs_main"  ' . $hidden_job);
					            //} else {
					                $form -> addElement('select', $field['name'].'_jobs' , $job_description_field, array("" => _PLEASESELECT . " " .$options['name']), 'class="inputSelect" id= "'. $field['name'].'_jobs"  ' . $hidden_job);
					            //}
				        	}
			        	}

			            if ($configuration['supervisor_mail_activation'] == 1 && $options['supervisor']['description'] != "") {
			            	$userProfileFields[] = $field['name'].'_supervisors';
			            	if ($options['supervisor']['hidden'] == 1) {
				        		$form -> addElement('select', $field['name'].'_supervisors' , "", array("" => _PLEASESELECT . " " .$options['name'] ), 'class="inputSelect" id= "'.$field['name'].'_supervisors" style="display:none"');
				        	} else {
				        		$form -> addElement('select', $field['name'].'_supervisors' , $options['supervisor']['description'], array("" => _PLEASESELECT . " " .$options['name'] ), 'class="inputSelect" id= "'.$field['name'].'_supervisors" ');
				        	}

			            }
			        }
	    		} #cpp#endif
	    	} else {
	    		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
	    			$options   = unserialize($field['options']);
	    			$allGroups = EfrontGroup::getGroups(false);
	    			$groups	   = array();
	    			foreach ($allGroups as $value) {
	    				if ($value['self_enroll']) {
	    					$groups[$value['id']] = $value['name'];
	    				}
	    			}
			        if (!empty($groups)) {
			        	$groups = array(0=>'') + $groups;
			        	$userProfileFields[] = $field['name'].'_groups';
			        	$form -> addElement('select', $field['name'].'_groups' , $options['name'], $groups,'class = "inputSelect"');

			        	if ($field['mandatory']) {
				            $form -> addRule($field['name'].'_groups', _THEFIELD.' '.$options['name'].' '._ISMANDATORY, 'required', null, 'client');
				        }
			        }
	    		} #cpp#endif	    		
	    	}
	    }
	    
		//if you want to display user profile fields with a specific order, fill in the array with the "Field name" (only those you are interested in)
		//$profileFieldsOrder = array("Field_one", "Field_two"); 
		//$userProfileFields = array_unique(array_merge($profileFieldsOrder, $userProfileFields));

	    $smarty -> assign("T_USER_PROFILE_FIELDS", $userProfileFields);
	} #cpp#endif

	$element = $form -> addElement('textarea', 'comments', _COMMENTS, 'class = "inputText" id = "comments"');
	$element -> setCols(40);
	$element -> setRows(2);
	$form -> addElement('submit', 'submit_register', _REGISTER, 'class = "flatButton"');

	if (isset($_GET['ldap'])) {
		$result = eF_getLdapValues($GLOBALS['configuration']['ldap_uid'].'='.$_GET['login'], array($GLOBALS['configuration']['ldap_preferredlanguage'],
		$GLOBALS['configuration']['ldap_mail'],
		$GLOBALS['configuration']['ldap_cn'],
		$GLOBALS['configuration']['ldap_uid']));
		$name_parts = explode(" ", $result[0]['cn'][0]);
		$first_name = array_shift($name_parts);
		sizeof($name_parts) == 0 ? $last_name = $first_name : $last_name = implode(" ", $name_parts);
		$form -> setDefaults(array("login"      => $_GET['login'],
                                   "password"   => _LDAPACCOUNTPASSWORD,
                                   "passrepeat" => _LDAPACCOUNTPASSWORD,
                                   "email"      => $result[0]['mail'][0],
                                   "firstName"  => $first_name,
                                   "lastName"   => $last_name));
		//$form -> freeze(array('login', 'password', 'passrepeat'));
		$smarty -> assign("T_LDAP_USER", true);
		if (!$form -> isSubmitted()) {
			$message 	  = _VERIFYFOLLOWINGINFOISCORRECT;
			$message_type = 'success';
		}
	} elseif ($configuration['only_ldap']) {
		$message      = _ONLYLDAPREGISTRATIONPERMITTED;
		$message_type = 'failure';
		eF_redirect(basename($_SERVER['PHP_SELF'])."?message=".urlencode($message)."&message_type=$message_type");
	}

	if ($form -> isSubmitted()) {
		$fields_insert = array('users_LOGIN' => 'visitor',
					   'timestamp'   => time(),
					   'action'	     => 'forms',
					   'comments'	 => 'signup',
					   'session_ip'  => eF_encodeIP($_SERVER['REMOTE_ADDR']));
		eF_insertTableData("logs", $fields_insert);
		if ($form -> validate()) {
            try {
				if (isset($_SESSION['s_login'])) {                                          //A logged-in user wants to signup: Log him out first
					$user = EfrontUserFactory :: factory($_SESSION['s_login']);
					$user -> logout(session_id());
				}
	
				$values = $form -> exportValues();                                          //Get the form values
	
				//Check the user_type. If it's an id, it means that it's not one of the basic user types; so derive the basic user type and populate the user_types_ID field
				$defaultUserType = $GLOBALS['configuration']['default_type'];
	
				if (is_numeric($defaultUserType)) {
	                $result = eF_getTableData("user_types", "id, basic_user_type", "id=".$defaultUserType);
	                if (sizeof($result) > 0) {
	                    $values['user_type']     = $result[0]['basic_user_type'];
	                    $values['user_types_ID'] = $result[0]['id'];
	                } else {
	                    $values['user_type'] = 'student';
	                }
	            } else {
					$values['user_type'] = $defaultUserType;
	                $values['user_types_ID'] = 0;
	            }
	
				$user_data = array("login"          => $values['login'],
	                               "password"       => isset($_GET['ldap']) ? 'ldap' : $values['password'],
	                               "name"           => $values['firstName'],
	                               "surname"        => $values['lastName'],
	                               "email"          => $values['email'],
	                               "comments"       => $values['comments'],
	                               "pending"		=> ($configuration['activation']) ? 0 : 1,
	                               "active"			=> $configuration['activation'],
	                               "languages_NAME" => $values['languages_NAME'],
								   "user_type"      => $values['user_type'],
								   "user_types_ID" => $values['user_types_ID']);
	
				if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
					$self_registered_jobs = array();
				} #cpp#endif
	
	            foreach ($user_profile as $field) {                                         //Get the custom fields values
	            	if ($field['type'] == 'date') {
	            		$user_data[$field['name']] = mktime($values[$field['name']]['H'], $values[$field['name']]['i'], $values[$field['name']]['s'], $values[$field['name']]['M'], $values[$field['name']]['d'], $values[$field['name']]['Y']);            		
	            	} else if ($field['type'] == 'branchinfo') {
	            		$self_registered_jobs[] = array("branch_ID" => $values[$field['name']. "_branches"], "job_description" => $_POST[$field['name']. "_jobs"], "supervisor" => $_POST[$field['name']. "_supervisors"], "mandatory" => $field['mandatory']);
	            	} else if ($field['type'] == 'groupinfo') {
	            		if ($values[$field['name']."_groups"]) {
	            			$groupToAdd = new EfrontGroup($values[$field['name']."_groups"]);
	            		}
	            	} else {
	            		$user_data[$field['name']] = $values[$field['name']];
	            	}
	            }

	        	$newUser = EfrontUser :: createUser($user_data);
	        	$encrypted = true;	//needed for autologin
	        	EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_REGISTER, "users_LOGIN" => $user_data['login'], "users_name" => $user_data['name'], "users_surname" => $user_data['surname'], "entity_name" => $user_data['password']));
	        	
	        	// send not-visited notifications for the newly registered user
	        	//EfrontEvent::triggerEvent(array("type" => (-1) * EfrontEvent::SYSTEM_VISITED, "users_LOGIN" => $user_data['login'], "users_name" => $user_data['name'], "users_surname" => $user_data['surname']));
	        	//pr($self_registered_jobs);

                if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE

                	try {
	                	if (isset($groupToAdd)) {
	            			$groupToAdd -> addUsers($values['login']);
	            		}
	                    $new_employees_content['users_login'] = $values['login'];
	                    EfrontHcdUser :: createUser($new_employees_content);
	                    foreach ($self_registered_jobs as $job_assigned) {
							if ($job_assigned['branch_ID'] != 0 || $job_assigned['mandatory']) {
								if (!$job_assigned['job_description']) {
									$job_assigned['job_description'] = _NOSPECIFICJOB;
								}
		                        // If the selected pair of branch-job description doesn't exist then insert it
		                        $new_job_description_ID = eF_getJobDescriptionId($job_assigned['job_description'], $job_assigned['branch_ID']);

		                        // Only assigned as employees
		                        $employee = new EfrontEmployee($new_employees_content, array());

		                        $newUser = EfrontUserFactory :: factory ($values['login']);
		                        //$encrypted = false; //the factory above changed the user password mode
		                        if ($configuration['supervisor_mail_activation'] == 1 && $job_assigned['supervisor'] != "") {
		                        	$activating_supervisor_found = true;

		                          	$employee -> addJob ($newUser, $new_job_description_ID, $job_assigned['branch_ID'], 0, false, array("manager" => $job_assigned['supervisor'], "timestamp" => $newUser -> user['timestamp']));
		                        } else {
		                          	$employee -> addJob ($newUser, $new_job_description_ID, $job_assigned['branch_ID'], 0);
		                        }
							}

	                    }
	                    if ($_SESSION['s_current_branch']) {
	                    	$employee = new EfrontEmployee($new_employees_content, array());
	                    	if (!in_array($_SESSION['s_current_branch'], $employee->getBranches(true))) {
		                    	$new_job_description_ID = eF_getJobDescriptionId(_NOSPECIFICJOB, $_SESSION['s_current_branch']);
		                    	$newUser = EfrontUserFactory :: factory ($values['login']);
		                    	$employee -> addJob ($newUser, $new_job_description_ID, $_SESSION['s_current_branch'], 0);
	                    	}
	                    }
                	} catch (Exception $e) {
                		$newUser -> delete();
                		eF_redirect(basename($_SERVER['PHP_SELF']).'?ctg=signup&message='.urlencode($e->getMessage()).'&message_type=failure');
                	}

                } #cpp#endif

				if ($configuration['activation'] == 0) {
					if ($configuration['mail_activation'] == 1){

						$tmp            = eF_getTableData("users","timestamp, login, name,surname","login='".$user_data['login']."'");
						$timestamp      = $tmp[0]["timestamp"];

						EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_ON_EMAIL_ACTIVATION, "users_LOGIN" => $tmp[0]['login'], "users_name" => $tmp[0]['name'], "users_surname" => $tmp[0]['surname'], "timestamp" => $timestamp, "entity_name" => $timestamp));

						$message = _YOUWILLRECEIVEMAILFORACCOUNTACTIVATION;
					#cpp#ifdef ENTEPRRISE
					} else if (G_VERSIONTYPE == 'enterprise' && $configuration['supervisor_mail_activation'] == 1 && $activating_supervisor_found) {
						$message = _YOURSUPERVISORWILLACTIVATEYOURACCOUNT;
					#cpp#endif
					} else {
						$message = _ADMINISTRATORWILLACTIVATEYOURACCOUNT;
					}
					eF_redirect(basename($_SERVER['PHP_SELF']).'?message='.urlencode($message).'&message_type=success');
					exit;
				} else {
					$message 	  = _SUCCESSREGISTER;
					$message_type = 'success';
					
					//Automatic registration trigers login as well, unless login_mode is enabled
					if ($_GET['ldap']) {
						$newUser -> login($_SESSION['ldap_user_pwd'], $encrypted);
						unset($_SESSION['ldap_user_pwd']);
					} else {
						$newUser -> login($newUser->user['password'], $encrypted);
					}

					if ($GLOBALS['configuration']['force_change_password'] && !$newUser -> isLdapUser && $newUser -> user['need_pwd_change']) {
						eF_redirect("index.php?ctg=password_change");
					} else if ($GLOBALS['configuration']['show_license_note'] && $newUser -> user['viewed_license'] == 0) {
						eF_redirect("index.php?ctg=agreement&message=".urlencode($message)."&message_type=".$message_type);
					} else if ($_SESSION['login_mode']) {
					    eF_redirect("index.php?ctg=checkout&checkout=1&message=".urlencode($message)."&message_type=".$message_type);
					} else {
						EfrontEvent::triggerEvent(array("type" => EfrontEvent::SYSTEM_VISITED, "users_LOGIN" => $newUser -> user['login'], "users_name" => $newUser -> user['name'], "users_surname" => $newUser -> user['surname']));
						loginRedirect($newUser -> user['user_type'], urlencode($message), $message_type);
					}
				}


			} catch (Exception $e) {
				$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
				$message      = $e -> getMessage().' &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
				$message_type = failure;
			}
		}
	}

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

	$renderer -> setRequiredTemplate(
       '{$html}{if $required}
            &nbsp;<span class = "formRequired">*</span>
        {/if}'
        );

        $form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
        $form -> setRequiredNote(_REQUIREDNOTE);
        $form -> accept($renderer);

        $smarty -> assign('T_PERSONAL_INFO_FORM', $renderer -> toArray());
}
/* --------------------------------------------------- End of Sign up part--------------------------------------------------- */

/* -------------------------------------------------------Contact part--------------------------------------------------------- */
if (isset($_GET['ctg']) && $_GET['ctg'] == 'contact') {                                                         //The user asked to display the contact form
	if (eF_checkSpam() == true) {
		$message      = _SPAMDETECTION;
		$message_type = 'failure';
		eF_redirect(basename($_SERVER['PHP_SELF']).'?message='.urlencode($message).'&message_type='.$message_type);	
	}	
	$smarty -> assign('T_CTG', 'contact');

	$form = new HTML_QuickForm("contact_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=contact", "", "class = 'indexForm'", true);
	$form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');           //Register this rule for checking user input with our function, eF_checkParameter

	$form -> addElement('text', 'email', _YOUREMAIL, 'class = "inputText"');
	$form -> addRule('email', _THEFIELD.' "'._EMAIL.'" '._ISMANDATORY, 'required');
	$form -> addRule('email', _INVALIDFIELDDATA, 'checkParameter', 'email');

	$form -> addElement('text', 'message_subject', _MESSAGESUBJECT, 'class = "inputText"');
	//$form -> addRule('message_subject', _INVALIDFIELDDATA, 'checkParameter', 'text');

	$form -> addElement('textarea', 'message_body', _TEXT, 'class = "inputText" id = "contact"');
	$form -> addElement('submit', 'submit_contact', _SUBMIT, 'class = "flatButton"');

	if ($_GET['limit_reached']) {
		$form->setDefaults(array('message_subject' => _IWANTTOSIGNUPBUTMAXIMUMUSERSLIMITREACHED, 'message_body' => _IWANTTOSIGNUPBUTMAXIMUMUSERSLIMITREACHEDBODY));
	}
	if ($_SESSION['s_login']) {
		$form->setDefaults(array('email' => $currentUser->user['email']));
	}
	
	if ($form -> isSubmitted()) {
		$fields_insert = array('users_LOGIN' => 'visitor',
					   'timestamp'   => time(),
					   'action'	     => 'forms',
					   'comments'	 => 'contact',
					   'session_ip'  => eF_encodeIP($_SERVER['REMOTE_ADDR']));
		eF_insertTableData("logs", $fields_insert);
		if ($form -> validate()) {
			$to      = $form -> exportValue("email");
			$subject = $form -> exportValue("message_subject");
			$body    = $form -> exportValue("message_body")."\r\n\r\n(".$subject." ["._FROM.": ".$to."])";		
			if (eF_mail($to, $GLOBALS['configuration']['system_email'], $subject." ["._FROM.": ".$to."]", $body, false, true)) {
				$copied_body = _THANKYOUFORCONTACTINGUSBODY."<br/><hr/><br/>".$form -> exportValue("message_body");
				eF_mail($GLOBALS['configuration']['system_email'], $to, _THANKYOUFORCONTACTINGUS, $copied_body, false, false);
				$message      = _SENDSUCCESS;
				$message_type = 'success';
				eF_redirect(basename($_SERVER['PHP_SELF']).'?message='.urlencode($message).'&message_type='.$message_type);
				
			} else {
				$message      = _SENDFAILURE;
				$message_type = 'failure';
			}
		}
	}

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

	$renderer -> setRequiredTemplate(
       '{$html}{if $required}
            &nbsp;<span class = "formRequired">*</span>
        {/if}');

	$form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
	$form -> setRequiredNote(_REQUIREDNOTE);
	$form -> accept($renderer);

	$smarty -> assign('T_CONTACT_FORM', $renderer -> toArray());
}

/* -------------------------------------------------------End of Contact part--------------------------------------------------------- */
/* -------------------------------------------------------Lesson information part--------------------------------------------------------- */
if (isset($_GET['ctg']) && $_GET['ctg'] == 'lesson_info') {                                                    //The user asked to display information on a lesson
	//session_start();			//Isn't needed here if the head session_start() is in place

	if (!$smarty -> is_cached('index.tpl', $cacheId) || !$GLOBALS['configuration']['smarty_caching']) {
		include("directions_tree.php");
		try {
			if (isset($_GET['lessons_ID'])) {
				if (isset($lessons[$_GET['lessons_ID']]) && ($lessons[$_GET['lessons_ID']] instanceOf EfrontLesson)) {
					$smarty -> assign("T_HAS_LESSON", $lessons[$_GET['lessons_ID']] -> lesson['has_lesson']);
				}
				$lesson     = new EfrontLesson($_GET['lessons_ID']);
				$lesson -> lesson['price_string'] = formatPrice($lesson -> lesson['price'], array($lesson -> options['recurring'], $lesson -> options['recurring_duration']), true);
				$lesson -> lesson['num_students']    = sizeof($lesson -> getStudentUsers());
				$lesson -> lesson['seats_remaining'] = $lesson -> lesson['max_users'] - $lesson -> lesson['num_students'];
				$lesson -> lesson['seats_remaining'] >= 0 OR $lesson -> lesson['seats_remaining'] = 0;
				$smarty -> assign("T_LESSON", $lesson);

				$lessonInformation = $lesson -> getInformation();
				$content    = new EfrontContentTree($lesson);
				if (sizeof($content -> tree) > 0) {
					$smarty -> assign("T_CONTENT_TREE", $content -> toHTML(false, 'dhtml_content_tree', array('noclick' => 1)));
				}
				$lessonInfo = new LearningObjectInformation(unserialize($lesson -> lesson['info']));
				$smarty -> assign("T_LESSON_INFO", $lessonInfo);
				$additionalInfo = $lesson -> getInformation();
				$smarty -> assign("T_ADDITIONAL_LESSON_INFO", $additionalInfo);

				if ($lesson -> lesson['course_only']) {
					$smarty -> assign("T_LESSON_COURSES", $lesson -> getCourses());
					if (isset($_GET['from_course']) && $_GET['from_course']) {
						$course = new EfrontCourse($_GET['from_course']);
						$smarty -> assign ("T_COURSE", $course);
						$smarty -> assign("T_HAS_COURSE", in_array($course -> course['id'], array_keys($userCourses)));
					} elseif (isset($_GET['course']) && $_GET['course']) {
						$course = new EfrontCourse($_GET['course']);
						$smarty -> assign ("T_COURSE", $course);
						$smarty -> assign("T_HAS_COURSE", in_array($course -> course['id'], array_keys($userCourses)));
					}

				}
			} else if ($_GET['courses_ID']) {				
				if (isset($courses[$_GET['courses_ID']]) && ($courses[$_GET['courses_ID']] instanceOf EfrontCourse)) {
					$smarty -> assign("T_HAS_COURSE", $courses[$_GET['courses_ID']] -> course['has_course']);
				}
				$course     = new EfrontCourse($_GET['courses_ID']);
				$course -> course['num_students']    = sizeof($course -> getStudentUsers());
				$course -> course['seats_remaining'] = $course -> course['max_users'] - $course -> course['num_students'];
				$course -> course['seats_remaining'] >= 0 OR $course -> course['seats_remaining'] = 0;
				$smarty -> assign("T_COURSE", $course);
				if ((isset($_SESSION['s_type']) && $_SESSION['s_type'] == 'administrator') || in_array($_SESSION['s_login'], array_keys($course -> getUsers()))) {
					$smarty -> assign("T_HAS_COURSE", true);
				}
				if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
					$result = eF_getTableData("module_hcd_course_to_branch mb, module_hcd_branch b", "mb.branches_ID, b.name", "mb.branches_ID=b.branch_ID and courses_ID=".$course -> course['id']);
					if (!empty($result)) {
						$course -> course['location'] = $result[0]['name'];
					}
				} #cpp#endif

                $lessons = $course -> getCourseLessons();
                foreach ($lessons as $key => $lesson) {
	                $content = new EfrontContentTree($lesson);
	                if (sizeof($content -> tree) > 0) {
	                	$contentTree[$key] = $content -> toHTML(false, 'dhtml_content_tree_'.$lesson -> lesson['id'], array('noclick' => 1));
	                }
	                $lessonInfo[$key] 	  = new LearningObjectInformation(unserialize($lesson -> lesson['info']));
	                $additionalInfo[$key] = $lesson -> getInformation();
                }
                $smarty -> assign("T_ADDITIONAL_LESSON_INFO", $additionalInfo);
                $smarty -> assign("T_COURSE_LESSON_INFO", $lessonInfo);
                $smarty -> assign("T_CONTENT_TREE", $contentTree);
                $smarty -> assign("T_LANGUAGES", EfrontSystem :: getLanguages(true));

				$smarty -> assign("T_COURSE_LESSONS", $lessons);

				$constraints = array('archive' => false, 'active' => true, 'sort' => 'name', 'condition' => 'show_catalog=1');
				if ($course -> course['instance_source']) {
					$parentCourse = new EfrontCourse($course -> course['instance_source']);
					$instances 	  = $parentCourse -> getInstances($constraints);
					$instances[$parentCourse -> course['id']] = $parentCourse;
				} else {
					$instances = $course -> getInstances($constraints);
					$instances[$course -> course['id']] = $course;
				}
				$smarty -> assign("T_COURSE_INSTANCES", $instances);

				$courseInfo = new LearningObjectInformation(unserialize($course -> course['info']));
				$smarty -> assign("T_COURSE_INFO", $courseInfo);
				$additionalInfo = $course -> getInformation();
				$smarty -> assign("T_ADDITIONAL_COURSE_INFO", $additionalInfo);
			}
		} catch (Exception $e) {
			$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
			$message      = $e -> getMessage().' &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
			$message_type = failure;
		}
	}
}

/* -------------------------------------------------------End of Lesson information part--------------------------------------------------------- */
if (isset($_GET['message'])) {
	$message ? $message  .= '<br>'.$_GET['message'] : $message = $_GET['message'];
	$message_type = $_GET['message_type'];
}
if ($_SESSION['s_message']) {
	$message 	 .= urldecode($_SESSION['s_message']);
	$message_type = $_SESSION['s_message_type'];
	unset($_SESSION['s_message']);
	unset($_SESSION['s_message_type']);
}
$smarty -> assign('T_MESSAGE', $message);
$smarty -> assign('T_MESSAGE_TYPE', $message_type);
$smarty -> assign('T_LDAPSUPPORT', $configuration['activate_ldap']);
$smarty -> assign('T_ONLY_LDAP', $configuration['only_ldap']);
$smarty -> assign('T_EXTERNALLYSIGNUP', $configuration['signup']);

$debug_timeBeforeSmarty = microtime(true) - $debug_TimeStart;
$benchmark -> set('script');



$loadScripts[] = 'includes/catalog';
if (isset($_GET['ajax']) && $_GET['ajax'] == 'cart') {
    try {
        include "catalog.php";
    } catch (Exception $e) {
        header("HTTP/1.0 500 ");
        echo rawurlencode($e -> getMessage()).' ('.$e -> getCode().')';
    }
    exit;
}
if (isset($_GET['ctg']) && $_GET['ctg'] == 'checkout' && $_GET['checkout'] && $_SESSION['s_login']) {
    try {
        /**Handles cart and catalog*/
        include "catalog.php";
    } catch (Exception $e) {
        $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
        $message      = $e -> getMessage().' &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
        $message_type = failure;
    }
} else {
    $smarty -> assign("T_CART", cart :: prepareCart());
}



if (isset ($_SESSION['s_login']) && ($GLOBALS['currentTheme'] -> options['sidebar_interface'] == 2 && $GLOBALS['currentTheme'] -> options['show_header'] == 2)) {
	try {
		//$currentUser = EfrontUserFactory :: factory($_SESSION['s_login']);
		$currentUser = EfrontUser :: checkUserAccess();
		refreshLogin();
		//$_SESSION['last_action_timestamp'] = time();		//Keep the last time something happened to the session

		if ($accounts = unserialize($currentUser -> user['additional_accounts'])) {
			$result 		= eF_getTableData("users", "login, user_type", 'login in ("'.implode('","', array_values($accounts)).'")');
			$smarty -> assign("T_MAPPED_ACCOUNTS", $result);
		}
	} catch (Exception $e) {}
}

if (isset($_GET['ctg']) && is_numeric($_GET['ctg'])) { //cheking a possible issue with search engine robots that overloads server
	if (empty($customBlocks) || in_array($_GET['ctg'], array_keys($customBlocks)) !== true) {
		 eF_redirect("HTTP/1.0 404 Not Found");
	}

}

if (isset($_SESSION['s_login']) && $_SESSION['s_login']) {    //This way, logged in users that stay on index.php are not logged out
    $loadScripts[] = 'sidebar';
}

$smarty -> assign("T_MESSAGE", $message);
$smarty -> assign("T_MESSAGE_TYPE", $message_type);
if (isset($search_message)) {
	$smarty -> assign("T_SEARCH_MESSAGE", $search_message);
}
if (!$smarty -> is_cached('index.tpl', $cacheId) || !$GLOBALS['configuration']['smarty_caching']) {
	foreach (eF_loadAllModules(true, true) as $module) {
		$module->onIndexPageLoad();
	}

	$positions = $GLOBALS['currentTheme']->layout['positions'];
	
	//Main scripts, such as prototype
	$mainScripts = getMainScripts();
	$smarty -> assign("T_HEADER_MAIN_SCRIPTS", implode(",", $mainScripts));
	//Operation/file specific scripts
	$loadScripts = array_diff($loadScripts, $mainScripts);        //Clear out duplicates
	$smarty -> assign("T_HEADER_LOAD_SCRIPTS", implode(",", array_unique($loadScripts)));                    //array_unique, so it doesn't send duplicate entries

	if (in_array('news', array_merge($positions['leftList'], $positions['rightList'], $positions['centerList']))) {
		$smarty -> assign("T_NEWS", news :: getNews(0, true));		
	}	
	
	if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
		require_once "../libraries/module_hcd_tools.php";
	} #cpp#endif
	if (EfrontUser::isOptionVisible('online_users') && in_array('online', array_merge($positions['leftList'], $positions['rightList'], $positions['centerList']))) {
		$smarty -> assign("T_ONLINE_USERS_LIST", EfrontUser :: getUsersOnline($GLOBALS['configuration']['autologout_time'] * 60));
	}
	
	
	$smarty -> assign("T_CURRENT_USER", $currentUser);
	$smarty -> load_filter('output', 'eF_template_setEditorOffset');
	$smarty -> display('index.tpl');

} else {
	$smarty -> load_filter('output', 'eF_template_setEditorOffset');
	$smarty -> display('index.tpl');
}


$benchmark -> set('smarty');
$benchmark -> stop();
$output = $benchmark -> display();
if (G_DEBUG) {
	echo $output;
}



?>
