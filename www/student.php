<?php
/**
* Student main page
*
* This page performs all student function
* @package eFront
* @version 1.0
*/

session_cache_limiter('none');          //Initialize session
session_start();

$path = "../libraries/";                //Define default path

/** The configuration file.*/
require_once $path."configuration.php";
$benchmark = new EfrontBenchmark($debug_TimeStart);
$benchmark -> set('init');

//Set headers in order to eliminate browser cache (especially IE's)
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("cache-control: no-transform");	//To prevent 3G carriers from compressing the site, which will break all grids

//If the page is shown as a popup, make sure it remains in such mode
if (!isset($_GET['reset_popup']) && (isset($_GET['popup']) || isset($_POST['popup']) || (isset($_SERVER['HTTP_REFERER']) && strpos(strtolower($_SERVER['HTTP_REFERER']), 'popup') !== false && strpos(strtolower($_SERVER['HTTP_REFERER']), 'reset_popup') === false))) {
    output_add_rewrite_var('popup', 1);
    $smarty -> assign("T_POPUP_MODE", true);
    $popup = 1;
}

$search_message = $message = $message_type = '';                            //Initialize messages, because if register_globals is turned on, some messages will be displayed twice
$load_editor = false;
$loadScripts = array();

try {
	if ($_GET['student']) {
$currentUser = EfrontUserFactory :: factory('student',false,'student');
$currentUser->login($currentUser->user['password'], true);
} else {
	$currentUser = EfrontUser :: checkUserAccess(false, 'student');
	}
	if ($currentUser -> user['user_type'] == 'administrator') {
		throw new Exception(_ADMINISTRATORCANNOTACCESSLESSONPAGE, EfrontUserException :: RESTRICTED_USER_TYPE);
	}
	$smarty -> assign("T_CURRENT_USER", $currentUser);
} catch (Exception $e) {
pr($e);exit;
	if ($e -> getCode() == EfrontUserException :: USER_NOT_LOGGED_IN && !isset($_GET['ajax'])) {
		setcookie('c_request', htmlspecialchars_decode(basename($_SERVER['REQUEST_URI'])), time() + 300);
	}
	eF_redirect("index.php?ctg=expired");
	exit;
}

if (isset($_SESSION['s_index_comply'])) {
	eF_redirect("index.php?ctg=".$_SESSION['s_index_comply']);
	exit;
} else if (isset($_SESSION['missing_fields']) && ($_GET['ctg'] != 'personal' || $_GET['user'] != $currentUser->user['login'] || $_GET['op'] != 'profile')) {
	eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$currentUser->user['login']."&op=profile&message=".urlencode(_PLEASEFILLINMISSINGMANDATORYFIELDS)."&message_type=success");
	exit;
} 

if (!isset($_GET['ajax']) && !isset($_GET['postAjaxRequest']) && !isset($popup) && !isset($_GET['tabberajax'])) {
	$_SESSION['previousMainUrl'] = $_SERVER['REQUEST_URI'];
}

if (isset($_COOKIE['c_request']) && $_COOKIE['c_request']) {
	setcookie('c_request', '', time() - 86400);
    if (mb_strpos($_COOKIE['c_request'], '.php') !== false) {
    	$urlParts = parse_url($_COOKIE['c_request']);
    	if (basename($urlParts['path']) == 'student.php') {
	        eF_redirect($_COOKIE['c_request']);
    	}
    } else {
        eF_redirect($_SESSION['s_type'].'.php?'.$_COOKIE['c_request']);
    }
}



try {
	if (isset($_GET['view_unit']) || isset($_GET['package_ID'])) {
		if ($_GET['view_unit']) {
			$unit = new EfrontUnit($_GET['view_unit']);
		} elseif ($_GET['package_ID']) {
			$unit = new EfrontUnit($_GET['package_ID']);
		}
		if (!$unit['active'] && $_SESSION['s_lesson_user_type'] == "student") {
			$message      = _YOUCANNOTACCESSTHISPAGE;
            eF_redirect("student.php?message=".urlencode($message)."&message_type=failure");
		}	
		$currentLesson = new EfrontLesson($unit['lessons_ID']);
		$_SESSION['s_lessons_ID'] = $currentLesson -> lesson['id'];
		//$_SESSION['s_time_target'] = array($_SESSION['s_lessons_ID'] => 'lesson');
	}
} catch (Exception $e) {
	unset($_GET['view_unit']);
	$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
	$message      = $e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
	$message_type = 'failure';
}

$roles       = EfrontLessonUser :: getLessonsRoles();
$userLessons = $currentUser -> getLessons();
if ($_SESSION['s_lessons_ID'] && $_GET['ctg'] != 'lessons') {
	try {
		$currentLesson  = new EfrontLesson($_SESSION['s_lessons_ID']);                	//Initialize lesson
        //$_SESSION['s_time_target'] = array($_SESSION['s_lessons_ID'] => 'lesson');
	} catch (Exception $e) {
		unset($_SESSION['s_lessons_ID']);
		$smarty -> assign("T_REFRESH_SIDE", "true");
	}
}

/* This is used to allow users to enter directly internal lesson specific pages from external pages*/
if (isset($_GET['new_lessons_ID']) && eF_checkParameter($_GET['new_lessons_ID'], 'id')) {
 	if ($_GET['new_lessons_ID'] != $_SESSION['s_lessons_ID']) {
		$_SESSION['s_lessons_ID'] = $_GET['new_lessons_ID'];
		if (isset($_GET['sbctg'])) {
		//	echo "swsta";
			$smarty -> assign("T_SPECIFIC_LESSON_CTG",  $_GET['sbctg']);
		}
		$smarty -> assign("T_REFRESH_SIDE","true");
 	} else if ($_GET['new_lessons_ID'] == $_SESSION['s_lessons_ID']) {

        $smarty -> assign("T_SHOW_LOADED_LESSON_OPTIONS", 1);
    }
}

/*This is the first time the professor enters this lesson, so register the lesson id to the session*/
if (isset($_GET['lessons_ID']) && eF_checkParameter($_GET['lessons_ID'], 'id')) {
	if (!isset($_SESSION['s_lessons_ID']) || $_GET['lessons_ID'] != $_SESSION['s_lessons_ID'] || (isset($_GET['from_course']) && $_GET['from_course'] != $_SESSION['s_courses_ID'])) {		
    	if (isset($_GET['course']) || isset($_GET['from_course'])) {
            if ($_GET['course']) {
        		$course = new EfrontCourse($_GET['course']);
            } else {
            	$course = new EfrontCourse($_GET['from_course']);
            }
            $eligibility = $course -> checkRules($_SESSION['s_login']);

            if ($eligibility[$_GET['lessons_ID']] == 0){
                unset($_GET['lessons_ID']);
                $message      = _YOUCANNOTACCESSTHISLESSONBECAUSEOFCOURSERULES;
                eF_redirect("student.php?ctg=lessons&message=".urlencode($message)."&message_type=failure");
            }
            $_SESSION['s_courses_ID'] = $course -> course['id'];
        } else {
        	unset($_SESSION['s_courses_ID']);
        }

        if (in_array($_GET['lessons_ID'], array_keys($userLessons))) {
            $newLesson = new EfrontLesson($_GET['lessons_ID']);
            if (!isset($_GET['course']) && !isset($_GET['from_course']) && $roles[$userLessons[$_GET['lessons_ID']]] == 'student' && (($newLesson -> lesson['from_timestamp'] && $newLesson -> lesson['from_timestamp'] > time()) || ($newLesson -> lesson['to_timestamp'] && $newLesson -> lesson['to_timestamp'] < time()))) {
            	eF_redirect("student.php?ctg=lessons&message=".urlencode(_YOUCANNOTACCESSTHISLESSONORITDOESNOTEXIST));
            }
        	$_SESSION['s_lessons_ID'] = $_GET['lessons_ID'];
            $_SESSION['s_type']       = $roles[$userLessons[$_GET['lessons_ID']]];

            //$justVisited = 1;   // used to trigger the event when the lesson info is available
                // The justVisited flag is set to one during the first visit to this lesson
	        //if ($justVisited) {
	            //Trigger onLessonVisited event
	        EfrontEvent::triggerEvent(array("type" => EfrontEvent::LESSON_VISITED, "users_LOGIN" => $currentUser -> user['login'], "users_name" => $currentUser -> user['name'], "users_surname" => $currentUser -> user['surname'], "lessons_ID" => $_SESSION['s_lessons_ID']));
	        //}

            $smarty -> assign("T_CHANGE_LESSON", "true");
            $smarty -> assign("T_REFRESH_SIDE", "true");
        } else {
            unset($_GET['lessons_ID']);
            $message      = _YOUCANNOTACCESSTHISLESSONORITDOESNOTEXIST;
            $message_type = 'failure';
            $ctg          = 'personal';

        }
    } else if ($_GET['lessons_ID'] == $_SESSION['s_lessons_ID']) {
        $smarty -> assign("T_SHOW_LOADED_LESSON_OPTIONS", 1);
    }
}


if ($_SESSION['s_lessons_ID'] && $roles[$userLessons[$_SESSION['s_lessons_ID']]].'.php' != basename($_SERVER['PHP_SELF'])) {
    if ($_GET['ctg'] != 'lessons') {
        eF_redirect(''.$roles[$userLessons[$_SESSION['s_lessons_ID']]].'.php');
        exit;
    }
}

if (isset($_SESSION['s_lessons_ID']) && $_SESSION['s_lessons_ID'] && $_GET['ctg'] != 'lessons') {    //Check validity of current lesson
    $userLessons = $currentUser -> getLessons();
    if (!isset($userLessons[$_SESSION['s_lessons_ID']]) || $roles[$userLessons[$_SESSION['s_lessons_ID']]] != 'student') {
        eF_redirect("student.php?ctg=lessons");    //redirect to student's lessons page

        exit;
    }
    try {
        $currentUser    -> applyRoleOptions($userLessons[$_SESSION['s_lessons_ID']]);                //Initialize user's role options for this lesson
        $currentLesson  = new EfrontLesson($_SESSION['s_lessons_ID']);                //Initialize lesson
        $smarty -> assign("T_TITLE_BAR", $currentLesson -> lesson['name']);
        //$_SESSION['s_time_target'] = array($_SESSION['s_lessons_ID'] => 'lesson');
        $_SESSION['s_lesson_user_type'] = $roles[$userLessons[$_SESSION['s_lessons_ID']]];		//needed for outputfilter.eF_template_setInnerLinks
        $currentUser -> coreAccess['content'] != 'change' ? $currentLesson -> mode = 'browse' : $currentLesson -> mode = 'normal';    //If the user type's setting is other than 'change' from content, then set lesson mode to 'browse', which means that no unit completion or ' or whatever progress is recorded
    } catch (Exception $e) {
        unset($_SESSION['s_lessons_ID']);
        $message = $e -> getMessage().' ('.$e -> getCode().')';
        eF_redirect("".basename($_SERVER['PHP_SELF'])."?message=".urlencode($message)."&message_type=failure");    //redirect to user lessons page
    }
}
//@todo: remove package_ID from $_SESSION, beware package_ID is needed in lms_commit
if (isset($_SESSION['package_ID']) && !$_GET['commit_lms']) {
    unset($_SESSION['package_ID']);
}

try {
    if (isset($_GET['view_unit']) && eF_checkParameter($_GET['view_unit'], 'id')) {
        $currentContent = new EfrontContentTree($currentLesson);           //Initialize content
        $currentContent -> markSeenNodes($currentUser);

        if ($currentLesson->lesson['access_limit']) {
	        $result = eF_getTableData("users_to_lessons", "access_counter", "users_LOGIN='".$currentUser->user['login']."' and lessons_ID='".$currentLesson->lesson['id']."'");
	        if ($result[0]['access_counter'] >= $currentLesson->lesson['access_limit']) {
	        	eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=lessons&message=".urlencode(_ACCESSDEPLETED)."&message_type=failure");
	        } else {
	        	eF_updateTableData("users_to_lessons", array('access_counter' => $result[0]['access_counter']+1), "users_LOGIN='".$currentUser->user['login']."' and lessons_ID='".$currentLesson->lesson['id']."'");
	        }
        }
        
        if ($currentUser -> coreAccess['content'] == 'hidden') {
            eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
        }
        if (!$currentLesson || !$currentContent) {
            eF_redirect("".basename($_SERVER['PHP_SELF']));
        }
        $currentUnit = $currentContent -> seekNode($_GET['view_unit']);              //Initialize current unit
        //The content tree does not hold data, so assign this unit its data
        $unitData    = new EfrontUnit($_GET['view_unit']);
        //$_SESSION['s_time_target'] = array($_GET['view_unit'] => 'unit');        
        $currentUnit['data'] = $unitData['data'];

        if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
            if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
                //If the current unit is scorm 2004, then assign its if to the 'package_ID' variable, so that the SCO initiates automatically
                if ($currentUnit['ctg_type'] == 'scorm' && in_array($currentUnit['scorm_version'], EfrontContentTreeSCORM :: $scorm2004Versions)) {
                    $_GET['package_ID'] = $_GET['view_unit'];
                }
            } #cpp#endif
        } #cpp#endif
        if (!$_GET['ctg']) {
            $_GET['ctg'] = 'content';
        }
    } elseif (isset($_GET['package_ID']) && $currentContent) {
        $_GET['ctg'] = 'content';
    }
} catch (Exception $e) {
    unset($_GET['view_unit']);
    $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
    $message      = $e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
    $message_type = 'failure';
}


$redirectPage = $GLOBALS['configuration']['login_redirect_page'];
if ($redirectPage == "user_dashboard" && $user_type != "administrator") {
	$location = "student.php?ctg=personal&user=".$_SESSION['s_login']."&op=dashboard";
} elseif (strpos($redirectPage, "module") !== false) {
	$location = "student.php?ctg=landing_page";
} else {
	$location = "student.php?ctg=lessons";
}
$smarty->assign("T_HOME_LINK", $location);


///MODULE1: Import
try {
	$loadedModules = $currentUser -> getModules();
	$module_css_array = array();
	$module_js_array = array();

	// Include module languages
	foreach ($loadedModules as $module) {
		// The $setLanguage variable is defined in globals.php
		$mod_lang_file = $module -> getLanguageFile($setLanguage);
		if (is_file ($mod_lang_file)) {
			require_once $mod_lang_file;
		}

		// Get module css
		if($mod_css_file = $module -> getModuleCSS()) {
			if (is_file ($mod_css_file)) {

				// Get the relative path
				if ($position = strpos($mod_css_file, "modules")) {
					$mod_css_file = substr($mod_css_file, $position);
				}
				$module_css_array[] = $mod_css_file;
			}
		}

		// Get module js
		if($mod_js_file = $module -> getModuleJS()) {
			if (is_file($mod_js_file)) {
				// Get the relative path
				if ($position = strpos($mod_js_file, "modules")) {
					$mod_js_file = substr($mod_js_file, $position);
				}

				$module_js_array[] = $mod_js_file;
			}
		}

		// Run onNewPageLoad code of the module (if such is defined)
		$module -> onNewPageLoad();
	}
} catch (Exception $e) {
	handleNormalFlowExceptions($e);
}

/*Ajax call to enter group and get group lessons */
if (isset($_GET['ajax']) && isset($_GET['group_key'])) {
	try {
		if (!eF_checkParameter($_GET['group_key'], 'alnum_general')) {
			throw new Exception(_INVALIDDATA.': '.$_GET['group_key']);
		}
		$result = eF_getTableData("groups", "*", "unique_key = '" . $_GET['group_key'] . "'");
		if (sizeof($result) > 0) {
			$group  = new EfrontGroup($result[0]);
			echo json_encode($group -> useKeyForUser($currentUser));
		} else {
			throw new Exception(_INVALIDKEY.': '.$_GET['group_key']);
		}
	} catch (Exception $e) {
		handleAjaxExceptions($e);
	}
	exit;
}

if (isset($_GET['bookmarks']) && EfrontUser::isOptionVisible('bookmarks')) {
    try {
        $bookmarks = bookmarks :: getBookmarks($currentUser, $currentLesson);
        if ($_GET['bookmarks'] == 'remove' && in_array($_GET['id'], array_keys($bookmarks))) {
            $bookmark = new bookmarks($_GET['id']);
            $bookmark -> delete();
        } elseif ($_GET['bookmarks'] == 'add') {
            foreach ($bookmarks as $value) {
                $urls[] = $value['url'];
            }

            if (!in_array($_SERVER['PHP_SELF']."?view_unit=".$currentUnit['id'], $urls)) {
	            $fields = array('users_LOGIN'     => $currentUser -> user['login'],
		                        'lessons_ID'      => $currentLesson -> lesson['id'],
		                        'name'            => $currentUnit['name'],
		                        'url'             => $_SERVER['PHP_SELF']."?view_unit=".$currentUnit['id']);
	            bookmarks :: create($fields);
            }
        } else {
            echo json_encode($bookmarks);
        }
    } catch (Exception $e) {
    	handleAjaxExceptions($e);
    }
    exit;
}

/*Added Session variable for search results*/
$_SESSION['referer'] = $_SERVER['REQUEST_URI'];

//$_SESSION['last_action_timestamp'] = time();		//Keep the last time something happened to the session

/*Horizontal menus*/
$onlineUsers = EfrontUser :: getUsersOnline($GLOBALS['configuration']['autologout_time'] * 60);
if ($GLOBALS['currentTheme'] -> options['sidebar_interface']) {
	$smarty -> assign("T_ONLINE_USERS_LIST", $onlineUsers);
	if ($accounts = unserialize($currentUser -> user['additional_accounts'])) {
		$result 		= eF_getTableData("users", "login, user_type", 'login in ("'.implode('","', array_values($accounts)).'")');
	    $smarty -> assign("T_MAPPED_ACCOUNTS", $result);
	}

} else {
    $smarty -> assign("T_NO_HORIZONTAL_MENU", 1);
}

refreshLogin();//Important: It must be called AFTER EfrontUser :: getUsersOnline

!isset($_GET['ctg']) || !eF_checkParameter($_GET['ctg'], 'alnum_general') ? $ctg = "control_panel" : $ctg = $_GET['ctg'];

if (!$_SESSION['s_lessons_ID'] && $ctg != 'personal' && $ctg != 'statistics' && ($ctg == 'control_panel' && (!isset($_GET['op']) || $_GET['op'] != "search"))) {       //If there is not a lesson in the session, then the user just logged into the system. Redirect him to lessons page, except for the case he is viewing his personal information 2007/07/27 added search control. It was a problem when user had not choose a lesson.
    $ctg = 'lessons';
}

$smarty -> assign("T_CTG", $ctg);       //As soon as we derive the current ctg, assign it to smarty.
$smarty -> assign("T_OP", isset($_GET['op']) ? $_GET['op'] : false);
$smarty -> assign("T_FCT", isset($_GET['fct']) ? $_GET['fct'] : false);

//Create shorthands for user type, to avoid long variable names
$_student_ = $_professor_ = $_admin_ = 0;
if ((isset($_SESSION['s_lesson_user_type']) && $_SESSION['s_lesson_user_type'] == 'student') || (!isset($_SESSION['s_lesson_user_type']) && $_SESSION['s_type'] == 'student')) {
    $_student_ = 1;
} else if ((isset($_SESSION['s_lesson_user_type']) && $_SESSION['s_lesson_user_type'] == 'professor') || (!isset($_SESSION['s_lesson_user_type']) && $_SESSION['s_type'] == 'professor')) {
    $_professor_ = 1;
} else {
    $_admin_ = 1;
}

$smarty -> assign("_student_", $_student_);
$smarty -> assign("_professor_", $_professor_);
$smarty -> assign("_admin_", $_admin_);

try {
	if ($ctg == 'control_panel') {
		$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));
	    /***/
	    require_once("control_panel.php");
	}
	elseif ($ctg == 'landing_page') {
	    /***/
	    require_once ("landing_page.php");
	}
	elseif ($ctg == 'news') {
		//$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));		//Commented out so that dashboard news links are working
	    /***/
	    require_once ("news.php");
	}
	elseif ($ctg == 'progress') {
		$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));
	    /***/
	    require_once("progress.php");
	}
	elseif ($ctg == 'comments') {
		$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));
	    /***/
	    require_once ("comments.php");
	}
	elseif ($ctg== 'lesson_information') {
		$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));
	    /***/
	    require_once("lesson_information.php");
	}
	elseif ($ctg== 'digital_library' && $currentLesson -> options['digital_library']) {
		$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));
	    /***/
	    require_once("digital_library.php");
	}
	elseif ($ctg == 'projects') {
		$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));
	    /**The file that handles the projects*/
	    require_once("projects.php");
	}
	elseif ($ctg == 'content') {
		$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));
	    if (isset($_GET['commit_lms'])) {
	        /***/
	        require_once("lms_commit.php");
	        exit;
	    } else {
		    /***/
		    require_once("common_content.php");
	    }
	}
	elseif ($ctg == 'tests') {
		//$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));
	    /***/
	    require_once("module_tests.php");
	}
	elseif ($ctg == 'feedback') {
		$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));
	    require_once("module_tests.php");
	}
	elseif ($ctg == 'lessons') {
	    /***/
	    require_once("includes/lessons_list.php");
	}
	elseif ($ctg == 'forum') {
	    /***/
	    require_once("includes/forum.php");
	}
	elseif ($ctg == 'messages') {
	    /***/
	    require_once("includes/messages.php");
	}	
	elseif ($ctg == 'module') {
	    /***/
	    require_once("module.php");
	}
	elseif ($ctg == "social") {
	    /***/
	    require_once("social.php");
	}
	else if ($ctg == 'facebook') {
	    /***/
	    require_once "module_facebook.php";
	}
	elseif ($ctg == 'calendar') {
		//Changed $_SERVER['HTTP_REFERER'] because it has a buggy behavior in IE (#4588)
	    if (!EfrontUser::isOptionVisible('calendar') && strpos($_SERVER['REQUEST_URI'], 'ctg=lessons') === false && strpos($_SERVER['REQUEST_URI'], 'ctg=calendar') === false && strpos($_SERVER['REQUEST_URI'], 'op=dashboard') === false) {
		    eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
		}
		require_once "calendar.php";
	}
	elseif ($ctg == 'glossary') {
		$_SESSION['s_lessons_ID'] OR eF_redirect(basename($_SERVER['PHP_SELF']));
	    /***/
	    require_once("glossary.php");
	}
	elseif ($ctg == 'survey') {
	    if (!EfrontUser::isOptionVisible('surveys')) {
	        eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
	    }
	    $load_editor=true;
	    include_once "module_surveys.php";
	}
	elseif ($ctg == 'statistics') {
	    if (isset($_GET['show_solved_test']) && eF_checkParameter($_GET['show_solved_test'], 'id') && isset($_GET['lesson']) && eF_checkParameter($_GET['lesson'], 'id')) {
	        try {
	            //pr($_GET['lesson']);pr($currentUser -> getLessons());
	            if (in_array($_GET['lesson'], array_keys($currentUser -> getLessons()))) {
	                $result   = eF_getTableData("done_tests, tests, content", "done_tests.tests_ID, done_tests.users_LOGIN", "content.id=tests.content_ID and content.lessons_ID=".$_GET['lesson']." and tests.id = done_tests.tests_ID and done_tests.users_LOGIN = '".$currentUser -> user['login']."' and done_tests.id=".$_GET['show_solved_test']);
	                if (sizeof($result) > 0) {
	                    $showTest = new EfrontTest($result[0]['tests_ID']);
	                    //Set "show answers" and "show given answers" to true, since if it is not the student that sees the test
	                    if ($currentUser -> user['user_type'] != 'student') {
	                        $showTest -> options['answers']       = 1;
	                        $showTest -> options['given_answers'] = 1;
	                    }
	                    $showTest -> setDone($result[0]['users_LOGIN']);
	                    $smarty   -> assign("T_CURRENT_TEST", $showTest -> test);
	                    $smarty   -> assign("T_SOLVED_TEST_DATA", $showTest -> doneInfo);
	                    $smarty   -> assign("T_TEST_SOLVED", $showTest -> toHTMLQuickForm(new HTML_Quickform(), false, true));
	                } else {
	                    $message      = _USERHASNOTDONETEST;
	                    $message_type = 'failure';
	                }
	            } else {
	                $message      = _USERHASNOTTHISLESSON;
	                $message_type = 'failure';
	            }
	        } catch (Exception $e) {
	            $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
	            $message      = $e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
	            $message_type = 'failure';
	        }
	    } else {
	        /**The statistics funtions*/
	        if ($currentUser -> coreAccess['statistics'] != 'hidden') {
	            require_once "statistics.php";
	        } else {
	            eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
	        }
	    }
	}
	elseif ($ctg == 'personal') {
	    /**This part is used to display the user's personal information*/
	    include "includes/personal.php";
	}

	#cpp#ifdef ENTERPRISE
	/**************/
	/* MODULE HCD */
	/**************/
	elseif ($ctg == 'module_hcd') {
	   include "module_hcd.php";
	}
	elseif ($ctg == "emails") {
	    include "emails.php";
	}
	elseif ($ctg == 'users') {
		$_GET['op'] = "employees";
	    $currentUser -> aspects['hcd'] = EfrontEmployeeFactory :: factory($currentUser -> login);
	    include "module_hcd.php";
	}
	#cpp#endif

	/*
	At this point, we apply module functionality
	*/
	elseif (sizeof($modules) > 0 && in_array($ctg, array_keys($module_ctgs))) {
	    $module_mandatory = eF_getTableData("modules", "mandatory", "name = '".$ctg."'");
	    if ($module_mandatory[0]['mandatory'] != 'false' || ($GLOBALS['currentLesson'] -> options[$ctg])) {
	        include(G_MODULESPATH.$ctg.'/module.php');
	        $smarty -> assign("T_CTG_MODULE", $module_ctgs[$ctg]);
	    }
	}

	$fields_log = array ('users_LOGIN' => $_SESSION['s_login'],         //This is the log entry array
	                     'timestamp'   => time(),
	                     'session_ip'  => eF_encodeIP($_SERVER['REMOTE_ADDR']));

/*
    if (isset($log_comments)) {                                         //If there is a $log_comments variable, it indicates the current action (i.e. the unit that the user saw)
        $fields_log['action']   = $ctg;
        $fields_log['comments'] = $log_comments;
        ($_SESSION['s_lessons_ID']) ? $fields_log['lessons_ID'] = $_SESSION['s_lessons_ID'] : $fields_log['lessons_ID'] = 0;
        eF_insertTableData("logs", $fields_log);
    } else {                                                            //Any other move, that has not set the $log_comments variable, is considered a 'lastmove' action
        $fields_log['action']   = "lastmove";
        $fields_log['comments'] = "";
        ($_SESSION['s_lessons_ID']) ? $fields_log['lessons_ID'] = $_SESSION['s_lessons_ID'] : $fields_log['lessons_ID'] = 0;
        eF_deleteTableData("logs", "users_LOGIN='".$_SESSION['s_login']."' AND action='lastmove'"); //Only one lastmove action interests us, so delete any other
        eF_insertTableData("logs", $fields_log);
    }
*/
	if (detectBrowser() == 'mobile') {
		$load_editor = false;
	}
    $smarty -> assign("T_HEADER_EDITOR", $load_editor);                 //Specify whether we need to load the editor

	/*
	 * Check if you should input the JS code to
	 * trigger sending the next notificatoin emails
	 * Since 3.6.0
	 */
	if (EfrontNotification::shouldSendNextNotifications()) {
		$smarty -> assign("T_TRIGGER_NEXT_NOTIFICATIONS_SEND", 1);
		$_SESSION['send_next_notifications_now'] = 0;	// the msg that triggered the immediate send should be sent now
	}

	if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
		//Import information from Facebook
		if (isset($_SESSION['facebook_details'])) {
			EfrontFacebook::importFbInfo($_SESSION['facebook_details'], $currentUser);
		}
	} #cpp#endif

} catch (Exception $e) {
    $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
    $message      = $e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
    $message_type = 'failure';
}

if (isset($_GET['refresh'])) {
    $smarty -> assign("T_REFRESH_SIDE","true");
}




$smarty -> assign("T_MODULE_CSS", $module_css_array);
$smarty -> assign("T_MODULE_JS", $module_js_array);
foreach ($loadedModules as $module) {
    $loadScripts = array_merge($loadScripts, $module -> addScripts());
}


//Main scripts, such as prototype
$mainScripts = getMainScripts();
$smarty -> assign("T_HEADER_MAIN_SCRIPTS", implode(",", $mainScripts));

//Operation/file specific scripts
$loadScripts = array_diff($loadScripts, $mainScripts);        //Clear out duplicates

$smarty -> assign("T_HEADER_LOAD_SCRIPTS", implode(",", array_unique($loadScripts)));                    //array_unique, so it doesn't send duplicate entries

$smartyClosingFiles = array();
foreach ($loadedModules as $module) {
	if ($smartyClosingFile = $module -> onPageFinishLoadingSmartyTpl()) {
		$smartyClosingFiles[] = $smartyClosingFile;
	}
}
$smarty -> assign("T_PAGE_FINISH_MODULES", $smartyClosingFiles);


$smarty -> assign("T_CURRENT_CTG", $ctg);
$smarty -> assign("T_MENUCTG", $ctg);
//$smarty -> assign("T_MENU", eF_getMenu());

//$smarty -> assign("T_QUERIES", $numberOfQueries);
if ($_SESSION['s_message']) {
	$message 	 .= urldecode($_SESSION['s_message']);
	$message_type = $_SESSION['s_message_type'];
	unset($_SESSION['s_message']);
	unset($_SESSION['s_message_type']);
}

$smarty -> assign("T_MESSAGE", $message);
$smarty -> assign("T_MESSAGE_TYPE", $message_type);
$smarty -> assign("T_SEARCH_MESSAGE", $search_message);

$smarty -> assign("T_CONFIGURATION", $configuration);       //Assign global configuration values to smarty
$smarty -> assign("T_CURRENT_USER", $currentUser);
$smarty -> assign("T_CURRENT_LESSON", isset($currentLesson) ? $currentLesson : false);

if (isset($currentLesson)) {
	$directions = new EfrontDirectionsTree();
	$categoryPath = $directions -> getPathToNode($currentLesson->lesson["directions_ID"]);
	//$categoryPath = $paths[$currentLesson->lesson["directions_ID"]];
	//pr($categoryPath);exit;
	//$categoryPath = str_replace("&rarr", "&raquo", $categoryPath);
	$smarty -> assign("T_CURRENT_CATEGORY_PATH", $categoryPath);
	if ($currentLesson -> lesson['course_only'] == 1 && $_SESSION['s_courses_ID']) {
		$currentCourse = new EfrontCourse($_SESSION['s_courses_ID']);
		$smarty -> assign("T_CURRENT_COURSE_NAME", htmlspecialchars($currentCourse->course['name'], ENT_QUOTES));
		$smarty -> assign("T_CURRENT_COURSE_ID", $currentCourse->course['id']);
	}
}

if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if ($GLOBALS['configuration']['facebook_api_key'] && $GLOBALS['configuration']['facebook_secret']) {
		$smarty -> assign("T_FACEBOOK_USER", EfrontFacebook::userConnected());
	}
} #cpp#endif

if ((!isset($currentUser -> coreAccess['personal_messages']) || $currentUser -> coreAccess['personal_messages'] != 'hidden') && EfrontUser::isOptionVisible('messages')) {
	$messages = eF_getTableData("f_personal_messages pm, f_folders ff", "count(*)", "pm.users_LOGIN='".$_SESSION['s_login']."' and viewed='no' and f_folders_ID=ff.id and ff.name='Incoming'");
	$smarty->assign("T_NUM_MESSAGES", $messages[0]['count(*)']);
}

//$smarty -> assign("T_SIMPLE_COMPLETE_MODE", EfrontUser::isOptionVisible('simple_complete'));

$smarty -> load_filter('output', 'eF_template_formatTimestamp');
$smarty -> load_filter('output', 'eF_template_formatLogins');
$smarty -> load_filter('output', 'eF_template_setInnerLinks');
$smarty -> load_filter('output', 'eF_template_setEditorOffset');


$benchmark -> set('script');
$smarty -> display('student.tpl');
$benchmark -> set('smarty');
$benchmark -> stop();
$output = $benchmark -> display();
if (G_DEBUG) {
	echo $output;
}

?>
