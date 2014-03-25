<?php

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

if (isset($currentUser->coreAccess['users']) && $currentUser->coreAccess['users'] != 'change') {
	$_change_groups_ = $_self_groups_ = false;
} else if ($currentUser -> user['user_type'] == 'administrator') {
	$_change_groups_ = $_self_groups_ = true;
} else if ($currentUser -> user['login'] == $editedUser -> user['login']) {
	$_change_groups_ = false;
	$_self_groups_ = true;
#cpp#ifdef ENTERPRISE
} else if (G_VERSIONTYPE == 'enterprise' && !$currentEmployee -> isSupervisor()) {
	$_change_groups_ = $_self_groups_ = false;
} else if (G_VERSIONTYPE == 'enterprise' && $currentEmployee -> supervisesEmployee($editedUser->user['login'])) {
	$_change_groups_ = $_self_groups_ = true;
#cpp#endif		
} else {
	$_change_groups_ = $_self_groups_ = false;
}
$smarty -> assign("_change_groups_", $_change_groups_);
$smarty -> assign("_self_groups_", $_self_groups_);	//whether self-registration to "Self_enroll" groups is allowed


try {
	if (isset($_GET['ajax']) && $_GET['ajax'] == "groupsTable") {
		$userGroups = $editedUser -> getGroups();
		$groups 	= eF_getTableData("groups", "*", "active=1");
		foreach ($groups as $key => $group) {
			$groups[$key]['partof'] = 0;
			if (in_array($group['id'], array_keys($userGroups))) {
				$groups[$key]['partof'] = 1;
			} else if ((!$group['active'] || !$_change_groups_) && !($_self_groups_ && $group['self_enroll'])) {
				unset($groups[$key]);
			}
		}

		$dataSource = $groups;
		$tableName = 'groupsTable';
		include("sorted_table.php");
	}
} catch (Exception $e) {
	handleAjaxExceptions($e);
}


if (isset($_GET['postAjaxRequest']) && ($_change_groups_ || $_self_groups_)) {

	$result = eF_getTableData("groups", "*", "active=1");
	$groups = array();
	foreach ($result as $key => $value) {
		if ($value['active'] && ($_change_groups_ || ($_self_groups_ && $value['self_enroll']))) {
			$groups[$value['id']] = $value;
		}
	}
	
	try {
		if ($_GET['insert'] == "true" && in_array($_GET['add_group'], array_keys($groups))) {
			$editedUser -> addGroups($_GET['add_group']);
		} else if ($_GET['insert'] == "false" && in_array($_GET['add_group'], array_keys($groups))) {
			$editedUser -> removeGroups($_GET['add_group']);
		} else if (isset($_GET['addAll'])) {
			isset($_GET['filter']) ? $groups = eF_filterData($groups, $_GET['filter']) : null;
			$editedUser -> addGroups(array_keys($groups));
		} else if (isset($_GET['removeAll'])) {
			isset($_GET['filter']) ? $groups = eF_filterData($groups, $_GET['filter']) : null;
			$editedUser -> removeGroups(array_keys($groups));
		}
	} catch (Exception $e) {
		handleAjaxExceptions($e);
	}
	exit;
}