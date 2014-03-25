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
		$_change_evaluations_ = false;
	} else if ($currentUser -> user['user_type'] == 'administrator') {
		$_change_evaluations_ = true;
	} else if ($currentUser -> user['login'] == $editedUser -> user['login']) {
		$_change_evaluations_ = false;
	} else if (!$currentEmployee -> isSupervisor()) {
		$_change_evaluations_ = false;
	} else if ($currentEmployee -> supervisesEmployee($editedUser->user['login'])) {
		$_change_evaluations_ = true;
	} else {
		$_change_evaluations_ = false;
	}
	$smarty -> assign("_change_evaluations_", $_change_evaluations_);

	if (isset($_GET['delete_evaluation']) && eF_checkParameter($_GET['delete_evaluation'], 'id') && $_change_evaluations_) {
		try {
			eF_deleteTableData("module_hcd_events", "event_ID = '".$_GET['delete_evaluation']."'");
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		exit;
	} elseif (isset($_GET['add_evaluation']) || (isset($_GET['edit_evaluation']) && eF_checkParameter($_GET['edit_evaluation'], 'id')) && $_change_evaluations_) {
		$load_editor = true;

		$form = new HTML_QuickForm("evaluations_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$editedUser -> user['login']."&op=evaluations&".(isset($_GET['edit_evaluation']) ? "&edit_evaluation=".$_GET['edit_evaluation'] : "&add_evaluation=1"), "", null, true);
		$form -> addElement('textarea', 'specification', _EVALUATIONCOMMENT, 'class = "simpleEditor" style = "width:400px;"');
		$form -> addElement('submit', 'submit', _SUBMIT, 'class = "flatButton"');

		if (isset($_GET['edit_evaluation'])) {
			$evaluations = eF_getTableData("module_hcd_events", "*", "event_ID = '".$_GET['edit_evaluation']."'");
			$form -> setDefaults(array('specification'  =>  $evaluations[0]['specification']));
		}

		try {
			if ($form -> isSubmitted() && $form -> validate()) {
				$evaluation_content = array('specification'  => $form->exportValue('specification'),
	                                    'event_code'     => 10,
	                                    'users_login'    => $editedUser -> user['login'],
	                                    'author'         => $currentUser -> user['login'],
	                                    'timestamp'      => time());

				if (isset($_GET['add_evaluation'])) {
					eF_insertTableData("module_hcd_events", $evaluation_content);
				} elseif (isset($_GET['edit_evaluation'])) {
					eF_updateTableData("module_hcd_events", $evaluation_content, "event_ID = '" . $_GET['edit_evaluation']. "'");
				}
				$message      = _OPERATIONCOMPLETEDSUCCESSFULLY;
				$message_type = 'success';
			}
		} catch (Exception $e) {
			handleNormalFlowExceptions($e);
		}

		$smarty -> assign("T_EVALUATIONS_FORM", $form -> toArray());
	} else {
		$evaluations = eF_getTableData("module_hcd_events", "*", "users_login = '".$editedUser->user['login']."' AND event_code >=10", "timestamp");
		$smarty -> assign("T_EVALUATIONS", $evaluations);
	}
} #cpp#endif

/*** Evaluations are deleted either by administrators or by the users who wrote them ***/
/*
 // Check if you are changing your own data - every HCD type is allowed to do that
 if ($_GET['ctg'] != 'personal') {
 if ($currentUser -> getType() != "administrator") {      // Administrators are allowed to do anything - no need to check further

 // If you are a Supervisor...
 if ($currentEmployee -> isSupervisor() ) {
 $smarty -> assign("T_IS_SUPERVISOR", true);

 // Check if you can manage/see this employee`s data - if not, prevent access
 if (isset($_GET['edit_user']) && !$currentEmployee -> supervisesEmployee($_GET['edit_user']) ) {
 eF_redirect("".$_SERVER['HTTP_REFERER']."&message=".urlencode(_SORRYYOUDONOTHAVEPERMISSIONTOPERFORMTHISACTION)."&message_type=failure");
 }

 } else {

 // Only Employees with no supervisor rights reach this point
 // Simple employees who are professors are allowed to manage evaluations - if this is not the case, then prevent access
 if ( !($currentUser -> getType() == "professor" && (isset($_GET['add_evaluation']) || isset($_GET['edit_evaluation']) || isset($_GET['delete_evaluation'])))) {
 eF_redirect("".$_SERVER['HTTP_REFERER']."&message=".urlencode(_SORRYYOUDONOTHAVEPERMISSIONTOPERFORMTHISACTION)."&message_type=failure");
 }

 }
 }
 }
 */
