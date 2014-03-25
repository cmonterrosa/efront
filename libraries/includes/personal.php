<?php
//@todo: sidebar.js: changeStatus(), show_user_box(), tests.js (skill gap)


//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}
$loadScripts[] = 'includes/personal';

if (!isset($_GET['user'])) {
	if ($currentUser->coreAccess['dashboard'] != 'hidden') {
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$currentUser->user['login']."&op=dashboard");
	} else {
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$currentUser->user['login']."&op=profile");
	}
} else if (!isset($_GET['op'])) {
	if ($currentUser -> user['login'] == $_GET['user'] && $currentUser->coreAccess['dashboard'] != 'hidden') {
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$currentUser->user['login']."&op=dashboard");
	} else {
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$_GET['user']."&op=profile");
	}
}

if ($currentUser -> coreAccess['users'] == 'hidden' && $_GET['user'] != $currentUser->user['login']) {
 eF_redirect(basename($_SERVER['PHP_SELF']));
}

$editedUser 	= EfrontUserFactory :: factory($_GET['user']);
$editedEmployee = $editedUser -> aspects['hcd'];
$smarty -> assign("T_EDITEDUSER", $editedUser);

if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
	$currentEmployee = $currentUser -> aspects['hcd'];

	if ($currentUser->user['login'] != $editedUser->user['login'] && $currentUser->user['user_type'] != 'administrator') {
		if (!$currentEmployee -> isSupervisor() || !$currentEmployee -> supervisesEmployee($editedUser->user['login'])) {
			eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$currentUser->user['login']."&op=profile&message=".urlencode(_YOUCANNOTEDITTHISUSER)."&message_type=failure");
		}
	}
} else { #cpp#else
	if ($currentUser->user['login'] != $editedUser->user['login'] && $currentUser->user['user_type'] != 'administrator') {
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$currentUser->user['login']."&op=profile&message=".urlencode(_YOUCANNOTEDITTHISUSER)."&message_type=failure");
	}	
} #cpp#endif

$enterpriseOperations = array();
$learningOperations   = array('user_courses', 'user_lessons');
$accountOperations	  = array('profile', 'user_groups');
$fileOperations		  = array();

if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
	$enterpriseOperations = array('placements', 'skills', 'evaluations', 'history', 'org_form');
} #cpp#endif
if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	$learningOperations[] = 'certificates';
} #cpp#endif
if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
		if (EfrontUser::isOptionVisible('show_user_form') || $currentUser->user['login'] != $editedUser->user['login']) {
			$learningOperations[] = 'user_form';
		}
		if ($currentUser -> user['login'] == $editedUser -> user['login'] || $currentUser -> user['user_type'] == 'administrator' || $currentEmployee -> supervisesEmployee($editedUser->user['login'])) {
			$fileOperations[] = 'files';
		}
	} #cpp#endif
} #cpp#endif

if ($currentUser -> user['login'] == $editedUser -> user['login']) {
	if (!$GLOBALS['configuration']['mapped_accounts'] ||
		$GLOBALS['configuration']['mapped_accounts'] == 1 && $currentUser -> user['user_type'] != 'student' ||
		$GLOBALS['configuration']['mapped_accounts'] == 2 && $currentUser -> user['user_type'] == 'administrator') {
		$accountOperations[] = 'mapped_accounts';
	}	
}

if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if ($editedUser -> user['user_type'] != 'administrator' && EfrontUser::isOptionVisible('payments')) {
		$accountOperations[] = 'payments';
	}
} #cpp#endif

if (isset($_GET['add_user']) || ($_SESSION['missing_fields'] && $currentUser -> user['login'] == $editedUser -> user['login'])) {
	$accountOperations = array('profile');
}
$smarty -> assign("T_ACCOUNT_OPERATIONS", $accountOperations);
$smarty -> assign("T_LEARNING_OPERATIONS", $learningOperations);
$smarty -> assign("T_ENTERPRISE_OPERATIONS", $enterpriseOperations);
$smarty -> assign("T_FILE_OPERATIONS", $fileOperations);

if ($_GET['op'] == 'dashboard') {
	require_once 'social.php';
} else if (in_array($_GET['op'], $enterpriseOperations)) {
	if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
		foreach ($enterpriseOperations as $value) {
			require_once("personal/$value.php");			//This way we don't include unnecessary files
		}
	} #cpp#endif
} else if ($_GET['op'] == 'files') {
	foreach ($fileOperations as $value) {
		require_once("personal/$value.php");			//This way we don't include unnecessary files
	}
} else if (in_array($_GET['op'], $learningOperations)) {
	foreach ($learningOperations as $value) {
		require_once("personal/$value.php");			//This way we don't include unnecessary files
	}
} else if (in_array($_GET['op'], $accountOperations)) {
	foreach ($accountOperations as $value) {
		require_once("personal/$value.php");			//This way we don't include unnecessary files
	}
}
$smarty -> assign("T_OP", $_GET['op']);

$options = array();
//Only own access to dashboard

if ($editedUser -> user['login'] == $currentUser->user['login'] && (!isset($currentUser -> coreAccess['dashboard']) || $currentUser -> coreAccess['dashboard'] != 'hidden')) {
	$options['dashboard'] = array('image' => '16x16/social.png',  'title' => _DASHBOARD, 'link' => basename($_SERVER['PHP_SELF']).'?ctg=personal&user='.$editedUser->user['login']."&op=dashboard", 'selected' => isset($_GET['op']) && $_GET['op'] == 'dashboard' ? true : false);
}
$options['account'] = array('image' => '16x16/user.png', 'title' => _ACCOUNT, 'link' => basename($_SERVER['PHP_SELF']).'?ctg=personal&user='.$editedUser->user['login']."&op=profile", 'selected' => isset($_GET['op']) && in_array($_GET['op'], $accountOperations) ? true : false);
//administrators don't have a learning aspect
if ($editedUser->user['user_type'] != 'administrator') {
	$options['learning'] = array('image' => '16x16/courses.png', 'title' => _LEARNING, 'link' => basename($_SERVER['PHP_SELF']).'?ctg=personal&user='.$editedUser->user['login']."&op=user_courses", 'selected' => isset($_GET['op']) && in_array($_GET['op'], $learningOperations) ? true : false);
}
if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
	if ($currentUser->coreAccess['organization'] != 'hidden') {
		$options['organization'] = array('image' => '16x16/enterprise.png', 'title' => _MYROLE, 'link' => basename($_SERVER['PHP_SELF']).'?ctg=personal&user='.$editedUser->user['login']."&op=placements",   'selected' => isset($_GET['op']) && in_array($_GET['op'], $enterpriseOperations) ? true : false);
	}
	if ($currentUser -> user['login'] == $editedUser -> user['login'] || $currentUser -> user['user_type'] == 'administrator' || $currentEmployee -> supervisesEmployee($editedUser->user['login'])) {
		$options['files'] = array('image' => '16x16/folder.png',  	  'title' => _FILES,  		'link' => basename($_SERVER['PHP_SELF']).'?ctg=personal&user='.$editedUser->user['login']."&op=files", 		  'selected' => isset($_GET['op']) && $_GET['op'] == 'files' 					   ? true : false);
	}
	
} #cpp#endif
 
if (!isset($_GET['add_user']) && !$_GET['popup'] && (!isset($_SESSION['missing_fields']) || $currentUser -> user['login'] != $editedUser -> user['login'])) {	//When inside a popup, we don't want the menu
	$smarty -> assign("T_TABLE_OPTIONS", $options);
}
// Set facebook template variables
if ($GLOBALS['configuration']['social_modules_activated'] & FB_FUNC_CONNECT) {
	if (isset($_SESSION['facebook_user']) && $_SESSION['facebook_user']) {
		$smarty -> assign("T_OPEN_FACEBOOK_SESSION",1);
		$smarty -> assign("T_FACEBOOK_API_KEY", $GLOBALS['configuration']['facebook_api_key']);
		$smarty -> assign("T_FACEBOOK_SHOULD_UPDATE_STATUS", $_SESSION['facebook_can_update']);
	}
	$smarty -> assign("T_FACEBOOK_ENABLED", 1);
}


if (EfrontUser::isOptionVisible('func_userstatus')) {
	$smarty -> assign("T_SHOW_USERSTATUS",1);
}