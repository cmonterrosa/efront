<?php
if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE

	//This file cannot be called directly, only included.
	if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
		exit;
	}

	if ($currentUser -> coreAccess['organization'] == 'hidden') {
		eF_redirect(basename($_SERVER['PHP_SELF']));
		exit;
	}
	
	if (isset($currentUser->coreAccess['organization']) && $currentUser->coreAccess['organization'] != 'change') {
		$_change_history_ = false;
	} else if ($currentUser -> user['user_type'] == 'administrator') {
		$_change_history_ = true;
	} else if ($currentUser -> user['login'] == $editedUser -> user['login']) {
		$_change_history_ = false;
	} else if (!$currentEmployee -> isSupervisor()) {
		$_change_history_ = false;
	} else if ($currentEmployee -> supervisesEmployee($editedUser->user['login'])) {
		$_change_history_ = true;
	} else {
		$_change_history_ = false;
	}
	$smarty -> assign("_change_history_", $_change_history_);

	if (eF_checkParameter($_GET['delete_event'], 'id') && $_change_history_) {
		try {
			eF_deleteTableData("events", "id = '".$_GET['delete_event']."'");
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		exit;
	} else {
		$history = array();
		// Get history from events table - 3.6 and on
		//type>300 is the HCD events
		$history_from_events = eF_getTableData("events", "*",  "users_LOGIN = '".$editedUser -> user['login']."' AND (type > 300 OR type=50 OR type=53)");
		$allModules = eF_loadAllModules();
		foreach ($history_from_events as $key => $event) {
			$eventObject = new EfrontEvent($event);
			$history[$key]['event_ID']  = $event['id'];
			$history[$key]['timestamp'] = $event['timestamp'];
			$history[$key]['message']   = $eventObject->createMessage($allModules);
		}

		// Get history from module_hcd_events table - for before 3.6
		$history_hcd_events = eF_getTableData("module_hcd_events", "*", "users_login = '".$editedUser -> user['login']."' AND event_code <10");
		foreach ($history_hcd_events as $key => $event) {
			$history[$key]['event_ID']  = $event['event_ID'];
			$history[$key]['timestamp'] = $event['timestamp'];
			$history[$key]['message']   = $event['specification'];
		}

		if (isset($_GET['ajax']) && $_GET['ajax'] == 'historyFormTable') {
			$dataSource = $history;
			$tableName  = "historyFormTable";
			include "sorted_table.php";
		}
	}
} #cpp#endif