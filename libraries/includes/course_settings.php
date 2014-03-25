<?php
//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

if (isset($currentUser -> coreAccess['course_settings']) && $currentUser -> coreAccess['course_settings'] == 'hidden') {
	eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
}


$loadScripts[] = 'scriptaculous/controls';
$loadScripts[] = 'includes/course_settings';

$autocompleteImage = '16x16/certificate.png';
if (G_VERSIONTYPE == 'community'){ #cpp#ifdef COMMUNITY
	$autocompleteImage = '16x16/autocomplete.png';
} #cpp#endif
$options = array();
$options['information'] = array('image' => '16x16/information.png',  'title' => _INFORMATION,  'link' => basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=course_info', 			'selected' => $_GET['op'] != 'course_info'         ? false : true);
$options['completion']  = array('image' => $autocompleteImage, 		'title' => _COMPLETION,   'link' => basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=course_certificates',  'selected' => ($_GET['op'] != 'course_certificates' && $_GET['op'] != 'format_certificate' && $_GET['op'] != 'format_certificate_docx') ? false : true);
$options['rules']		= array('image' => '16x16/rules.png',     	'title' => _RULES,        'link' => basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=course_rules', 		'selected' => $_GET['op'] != 'course_rules'        ? false : true);
$options['order'] 		= array('image' => '16x16/order.png',    	'title' => _ORDER,        'link' => basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=course_order', 		'selected' => $_GET['op'] != 'course_order'        ? false : true);
$options['schedule'] 	= array('image' => '16x16/calendar.png',    	'title' => _SCHEDULING,   'link' => basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=course_scheduling', 	'selected' => $_GET['op'] != 'course_scheduling'   ? false : true);
if (!isset($currentUser -> coreAccess['course_settings']) || $currentUser -> coreAccess['course_settings'] == 'change') {
	$options['export'] = array('image' => '16x16/export.png', 'title' => _EXPORT, 'link' => basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=export_course', 'selected' => $_GET['op'] != 'export_course' ? false : true);
	$options['import'] = array('image' => '16x16/import.png', 'title' => _IMPORT, 'link' => basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=import_course', 'selected' => $_GET['op'] != 'import_course' ? false : true);
}
$moduleTabPages = array();
foreach ($currentUser -> getModules() as $module) {
	if ($moduleTabPage = $module -> getTabPageSmartyTpl('course_settings')) {
		$moduleTabPages[] = $moduleTabPage;
		$options[$moduleTabPage['tab_page']] = array('image' => $moduleTabPage['image'],
												   'title' => $moduleTabPage['title'],
												   'link'  => basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op='.$moduleTabPage['tab_page'],
												   'selected' => $_GET['op'] != $moduleTabPage['tab_page'] ? false : true,
												   'absoluteImagePath' => 1);
	}
	if ($_GET['op'] == $moduleTabPage['tab_page']) {
		$smarty -> assign("T_MODULE_TABPAGE", $moduleTabPage);
	}
}
$smarty -> assign("T_MODULE_COURSE_SETTINGS_TABPAGES", $moduleTabPages);

$smarty -> assign("T_TABLE_OPTIONS", $options);

$smarty -> assign("T_COURSE_OPTIONS", array(array('text' => _EDITCOURSE,  'image' => "16x16/edit.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=courses&edit_course=".$_GET['course'])));

if ($_GET['op'] == 'course_info') {
	$form = new HTML_QuickForm("empty_form", "post", null, null, null, true);

	$courseInformation = unserialize($currentCourse -> course['info']);
	$information       = new LearningObjectInformation($courseInformation);
	if (!isset($currentUser -> coreAccess['course_settings']) || $currentUser -> coreAccess['course_settings'] == 'change') {
		$smarty -> assign("T_COURSE_INFO_HTML", $information -> toHTML($form, false));
	} else {
		$smarty -> assign("T_COURSE_INFO_HTML", $information -> toHTML($form, false, false));
	}

	$courseMetadata = unserialize($currentCourse -> course['metadata']);
	$metadata       = new DublinCoreMetadata($courseMetadata);
	if (!isset($currentUser -> coreAccess['course_settings']) || $currentUser -> coreAccess['course_settings'] == 'change') {
		$smarty -> assign("T_COURSE_METADATA_HTML", $metadata -> toHTML($form));
	} else {
		$smarty -> assign("T_COURSE_METADATA_HTML", $metadata -> toHTML($form, true, false));
	}

	if (isset($_POST['postAjaxRequest'])) {
		if (in_array($_POST['dc'], array_keys($information -> metadataAttributes))) {
			if ($_POST['value']) {
				$courseInformation[$_POST['dc']] = urldecode($_POST['value']);
			} else {
				unset($courseInformation[$_POST['dc']]);
			}
			$currentCourse -> course['info'] = serialize($courseInformation);
		} elseif (in_array($_POST['dc'], array_keys($metadata -> metadataAttributes))) {
			if ($_POST['value']) {
				$courseMetadata[$_POST['dc']] = urldecode($_POST['value']);
			} else {
				unset($courseMetadata[$_POST['dc']]);
			}
			$currentCourse -> course['metadata'] = serialize($courseMetadata);
		}

		$currentCourse -> persist();
		$value = htmlspecialchars(rawurldecode($_POST['value']));
		$value = str_replace ("\n","<br />",  $value);
		echo $value;
		exit;
	}

} else if ($_GET['op'] == 'course_certificates') {
	$load_editor = 1;
	$defaultConstraints = array('active' => true, 'instance' => false, 'return_objects' => false);
	//$users = $currentCourse -> getCourseUsers($constraints);
	if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
		$stats_filters = array();
		$branches	  = array($_SESSION['s_current_branch']);
		$branchesTree = new EfrontBranchesTree();
		$iterator	  = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($branchesTree -> getNodeChildren($_SESSION['s_current_branch'])), RecursiveIteratorIterator :: SELF_FIRST));
		foreach($iterator as $key => $value) {
			$branches[] = $key;
		}
	
		$stats_filters[] = array("table" 		=> "module_hcd_employee_works_at_branch as filter_eb",
				"joinField"	=> "filter_eb.users_LOGIN",
				"condition" 	=> "(filter_eb.branch_ID in (" . implode(",", $branches) . ") AND filter_eb.assigned = 1)");
		$defaultConstraints['table_filters'] = $stats_filters;
	}
	$smarty->assign('T_CERTIFICATE_EXPORT_METHOD', $currentCourse->options['certificate_export_method']);
	/*
		$users = EfrontStats::getUsersCourseStatus($currentCourse);
		$users = $users[$currentCourse -> course['id']];
		*/
	$rolesBasic = EfrontLessonUser :: getLessonsRoles();
	$studentRoles = array();
	foreach ($rolesBasic as $key => $role) {
		$role != 'student' OR $studentRoles[] = $key;
	}

	//$users = $currentCourse -> getCourseUsers($defaultConstraints);
	if ($_GET['edit_user']) {
		$user_to_check = $_GET['edit_user'];
	} else if ($_GET['issue_certificate']) {
		$user_to_check = $_GET['issue_certificate'];
	} else if ($_GET['revoke_certificate']) {
		$user_to_check = $_GET['revoke_certificate'];
	} else if ($_GET['reset_keep']) {
		$user_to_check = $_GET['reset_keep'];
	} else if ($_GET['login']) {
		$user_to_check = $_GET['login'];
	}
	if (isset($user_to_check) && !eF_checkParameter($user_to_check, 'login')) {
		throw new EfrontUserException(_INVALIDLOGIN.': '.$user['login'], EfrontUserException :: INVALID_LOGIN);
	}
	$result = eF_getTableData("users_to_courses", "users_LOGIN", "archive=0 and courses_ID=".$currentCourse -> course['id']." and users_LOGIN='".$user_to_check."'");
	
	if (isset($_GET['edit_user']) && !empty($result)) {
		$user = EfrontUserFactory::factory($_GET['edit_user']);
		//pr($user -> getUserLessons());exit;
		$form = new HTML_QuickForm("edit_user_complete_course_form", "post", basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=course_certificates&edit_user='.$_GET['edit_user'].'&popup=1', "", null, true);
		$form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');                   //Register this rule for checking user input with our function, eF_checkParameter

		$form -> addElement('advcheckbox', 'completed', _COMPLETED, null, 'class = "inputCheckbox"');            //Whether the user has completed the course
		$form -> addElement('text', 'score', _SCORE, 'class = "inputTextScore"');                                                        //The user course score
		$form -> addRule('score', _THEFIELD.' "'._SCORE.'" '._ISMANDATORY, 'required', null, 'client');
		$form -> addRule('score', _THEFIELD.' "'._SCORE.'" '._MUSTBENUMERIC, 'numeric', null, 'client');                            //The score must be numeric
		$form -> addRule('score', _RATEMUSTBEBETWEEN0100, 'callback', create_function('$a', 'return ($a >= 0 && $a <= 100);'));    //The score must be between 0 and 100

		$form -> addElement('textarea', 'comments', _COMMENTS, 'class = "inputContentTextarea simpleEditor" style = "width:100%;height:5em;"');      //Comments on student's performance
		$form -> addElement('submit', 'submit_course_complete', _SUBMIT, 'class = "flatButton"');       //The submit button

		//pr($currentCourse -> getCourseLessons());exit;
		$userCourseLessonsStatus = $user -> getUserStatusInCourseLessons($currentCourse);
		$totalScore = 0;
		foreach ($userCourseLessonsStatus as $lesson) {
			$totalScore += $lesson -> lesson['score'] / sizeof($userCourseLessonsStatus);
		}
		$smarty -> assign("T_USER_COURSE_LESSON_STATUS", $userCourseLessonsStatus);
		$smarty -> assign("T_USER_COURSE", $user);

		$form -> setDefaults(array("completed" => $user -> user['completed'],
									   "score"     => $user -> user['completed'] ? $user -> user['score'] : round($totalScore),
									   "comments"  => $user -> user['comments']));
		if ($user -> user['to_timestamp']) {					
	    	$smarty -> assign("T_TO_TIMESTAMP",   $user -> user['to_timestamp']);
		} else {
    		$smarty -> assign("T_TO_TIMESTAMP",   mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));    
		}

		if ($form -> isSubmitted() && $form -> validate()) {
			$toTimestamp   = mktime($_POST['completion_Hour'], $_POST['completion_Minute'], 0, $_POST['completion_Month'],   $_POST['completion_Day'],   $_POST['completion_Year']);
			if ($form -> exportValue('completed')) {
				$courseUser = EfrontUserFactory :: factory($_GET['edit_user'], false, 'student');
				$courseUser -> completeCourse($currentCourse -> course['id'], $form -> exportValue('score'), $form -> exportValue('comments'), $toTimestamp);
			} else {
				$fields = array("completed" 			=> 0,
									"score"    				=> 0,
									"issued_certificate"	=> '',
									"to_timestamp"			=> null,
									"comments"  			=> '');

				$where  = "users_LOGIN = '".$_GET['edit_user']."' and courses_ID=".$currentCourse -> course['id'];			
				EfrontCourse::persistCourseUsers($fields, $where, $currentCourse -> course['id'], $_GET['edit_user']);	
				if ($user -> user['issued_certificate'] != "") {
					EfrontEvent::triggerEvent(array("type" => EfrontEvent::COURSE_CERTIFICATE_REVOKE, "users_LOGIN" => $_GET['edit_user'], "lessons_ID" => $currentCourse -> course['id'], "lessons_name" => $currentCourse -> course['name']));
				}
			}

			$message      = _STUDENTSTATUSCHANGED;
			$message_type = 'success';
		}
		$renderer = prepareFormRenderer($form);
		$smarty -> assign('T_COMPLETE_COURSE_FORM', $renderer -> toArray());

	} else if (isset($_GET['issue_certificate']) &&  !empty($result)) {
		try {
			$certificate = $currentCourse -> prepareCertificate($_GET['issue_certificate']);
			$currentCourse -> issueCertificate($_GET['issue_certificate'], $certificate);
			eF_redirect(''.basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=course_certificates&reset_popup=1&&message='.urlencode(_CERTIFICATEISSUEDSUCCESFULLY).'&message_type=success');
		} catch (Exception $e) {
			$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
			$message      = _PROBLEMISSUINGCERTIFICATE.': '.$e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
			$message_type = 'failure';
		}
	} else if (isset($_GET['revoke_certificate']) && !empty($result)) {
		try {
			$currentCourse -> revokeCertificate($_GET['revoke_certificate']);
			eF_redirect(''.basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=course_certificates&reset_popup=1&message='.urlencode(_CERTIFICATEREVOKED).'&message_type=success');
		} catch (Exception $e) {
			$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
			$message      = _PROBLEMREVOKINGCERTIFICATE.': '.$e -> getMessage().' &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
			$message_type = 'failure';
		}
	} else if (isset($_GET['reset_keep']) && !empty($result)) {
		try {
			$user = EfrontUserFactory :: factory($_GET['reset_keep']);
			$user -> resetProgressInCourse($currentCourse, true, true);
			eF_redirect(''.basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=course_certificates&reset_popup=1&message='.urlencode(_PROGRESSRESETSUCCESSFULLY).'&message_type=success');
		} catch (Exception $e) {
			$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
			$message      = _PROBLEMRESETINGPROGRESS.': '.$e -> getMessage().' &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
			$message_type = 'failure';
		}
	} else if (isset($_GET['change_key']) && !empty($result)) {
		try {
			$result = eF_getTableData("users_to_courses", "users_LOGIN,issued_certificate", "courses_ID = ".$currentCourse -> course['id']. " and users_LOGIN = '".$_GET['login']."'");
			$issued_certificate = unserialize($result[0]['issued_certificate']);
			if ($issued_certificate) {
				$issued_certificate['serial_number'] = $_GET['change_key']; 
				eF_updateTableData("users_to_courses", array('issued_certificate' => serialize($issued_certificate)), "courses_ID = ".$currentCourse -> course['id']. " and users_LOGIN = '".$_GET['login']."'");
				echo json_encode(array('key' => $_GET['change_key']));
			}
			exit;	
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
	} else if (isset($_GET['auto_complete'])) {
		try {
			if ($currentCourse -> options['auto_complete']) {
				$currentCourse -> options['auto_complete']    = 0;
				$currentCourse -> options['auto_certificate'] = 0;
			} else {
				$currentCourse -> options['auto_complete'] = 1;
			}
			$currentCourse -> persist();
			foreach ($currentCourse -> getCourseLessons() as $lesson) {
				$lesson -> options['auto_complete'] = $currentCourse -> options['auto_complete'];
				$lesson -> persist();
			}
			$ajaxResult = new AjaxResultObject($currentCourse -> options['auto_complete'], _OPERATIONCOMPLETEDSUCCESFULLY);
			$ajaxResult -> display();
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		exit;
	} else if (isset($_GET['auto_certificate'])) {
		try {
			if ($currentCourse -> options['auto_certificate']) {
				$currentCourse -> options['auto_certificate'] = 0;
			} else {
				$currentCourse -> options['auto_certificate'] = 1;
			}
			$currentCourse -> persist();
			echo $currentCourse -> options['auto_certificate'];
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		exit;
	} else if (isset($_GET['CertificateAll'])) {
		try {
			$users = $currentCourse -> getCourseUsers($defaultConstraints);
			foreach ($users as $key => $value) {
				if ($value['completed'] && !$value['issued_certificate']) {
					$certificate = $currentCourse -> prepareCertificate($key);
					$currentCourse -> issueCertificate($key, $certificate);
				}
			}
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		exit;
	} else if (isset($_GET['set_certificate_date'])) {
		try {
			$users = $currentCourse -> getCourseUsers($defaultConstraints);
			foreach ($users as $key => $value) {
				if ($value['issued_certificate']) {
					$unserialized_data = unserialize($value['issued_certificate']);
					$unserialized_data['date'] = $value['to_timestamp'];
					eF_updateTableData('users_to_courses', array("issued_certificate" => serialize($unserialized_data)), "courses_ID=".$currentCourse -> course['id']." and users_LOGIN='".$key."'");
				}
			}
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		exit;
	} else if (isset($_GET['revoke_all_expired'])) {
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY	
			try {				
				$users = $currentCourse -> getCourseUsers($defaultConstraints);				
				foreach ($users as $key => $value) {
					if ($currentCourse -> course['certificate_expiration'] && $value['issued_certificate']) {
						$dateTable 	= unserialize($value['issued_certificate']);
					
						$expirationArray	= convertTimeToDays($currentCourse -> course['certificate_expiration']);
						$timeExpire  		= getCertificateExpirationTimestamp($dateTable['date'], $expirationArray);	

						//Revoke certificate if it has expired, and optionally reset access to the course as well
						if ($timeExpire && $timeExpire < time() && $value['issued_certificate']) {
							$currentCourse -> revokeCertificate($key);
							if ($currentCourse -> course['reset'] == 1) {
								$currentCourse -> removeUsers($key);
								$currentCourse -> addUsers($key, $value['user_type']);
							}
							
							EfrontEvent::triggerEvent(array("type" => EfrontEvent::COURSE_CERTIFICATE_EXPIRY,
										"users_LOGIN"  => $value['login'],
										"lessons_ID"   => $currentCourse	-> course['id'],
										"lessons_name" => $currentCourse	-> course['name']));
						}
					}
				}
			} catch (Exception $e) {
				handleAjaxExceptions($e);
			}
			exit;
		} #cpp#endif
	} else if (isset($_GET['set_all_completed'])) {
		try {
			if ($_GET['only_shown']) {
				$constraints  = array('archive' => false, 'active' => true) + createConstraintsFromSortedTable();
			} else {
				$constraints  = array('archive' => false, 'active' => true);	
			}
			if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
				$stats_filters = array();
				$branches	  = array($_SESSION['s_current_branch']);
				$branchesTree = new EfrontBranchesTree();
				$iterator	  = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($branchesTree -> getNodeChildren($_SESSION['s_current_branch'])), RecursiveIteratorIterator :: SELF_FIRST));
				foreach($iterator as $key => $value) {
					$branches[] = $key;
				}

				$stats_filters[] = array("table" 		=> "module_hcd_employee_works_at_branch as filter_eb",
						"joinField"	=> "filter_eb.users_LOGIN",
						"condition" 	=> "(filter_eb.branch_ID in (" . implode(",", $branches) . ") AND filter_eb.assigned = 1)");
				$constraints['table_filters'] = $stats_filters;
			}
			$constraints['condition'] = "uc.user_type in ('".implode("','", $studentRoles)."')";
			$users 		  = $currentCourse -> getCourseUsers($constraints);
			foreach ($users as $user) {
				if (!$user -> user['completed']) {
					$user -> completeCourse($currentCourse, 100);
				}
			}
			echo json_encode(array('status' => true));
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		exit;
	} else if (isset($_GET['reset_all'])) {
		try {
			$constraints  = array('archive' => false, 'active' => true) + createConstraintsFromSortedTable();
			if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
				$stats_filters = array();
				$branches	  = array($_SESSION['s_current_branch']);
				$branchesTree = new EfrontBranchesTree();
				$iterator	  = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($branchesTree -> getNodeChildren($_SESSION['s_current_branch'])), RecursiveIteratorIterator :: SELF_FIRST));
				foreach($iterator as $key => $value) {
					$branches[] = $key;
				}

				$stats_filters[] = array("table" 		=> "module_hcd_employee_works_at_branch as filter_eb",
						"joinField"	=> "filter_eb.users_LOGIN",
						"condition" 	=> "(filter_eb.branch_ID in (" . implode(",", $branches) . ") AND filter_eb.assigned = 1)");
				$constraints['table_filters'] = $stats_filters;
			}
			$constraints['condition'] = "uc.user_type in ('".implode("','", $studentRoles)."')";
			$users 		  = $currentCourse -> getCourseUsers($constraints);
			foreach ($users as $user) {
				$user -> resetProgressInCourse($currentCourse, true);
			}
			echo json_encode(array('status' => true));
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		exit;		
	}
	
	$smarty -> assign("T_BASIC_ROLES_ARRAY", $rolesBasic);
	if (isset($_GET['ajax']) && $_GET['ajax'] == 'courseUsersTable') {

		//pr($studentRoles);
		$smarty -> assign("T_DATASOURCE_COLUMNS", array('login', 'active_in_course', 'completed', 'to_timestamp', 'score', 'issued_certificate', 'expire_certificate', 'operations'));
		$smarty -> assign("T_DATASOURCE_SORT_BY", 0);
		$constraints  = array('archive' => false, 'active' => true) + createConstraintsFromSortedTable();
		$constraints['condition'] = "uc.user_type in ('".implode("','", $studentRoles)."')";
		if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
			$stats_filters = array();
			$branches	  = array($_SESSION['s_current_branch']);
			$branchesTree = new EfrontBranchesTree();
			$iterator	  = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($branchesTree -> getNodeChildren($_SESSION['s_current_branch'])), RecursiveIteratorIterator :: SELF_FIRST));
			foreach($iterator as $key => $value) {
				$branches[] = $key;
			}
				
			$stats_filters[] = array("table" 		=> "module_hcd_employee_works_at_branch as filter_eb",
					"joinField"	=> "filter_eb.users_LOGIN",
					"condition" 	=> "(filter_eb.branch_ID in (" . implode(",", $branches) . ") AND filter_eb.assigned = 1)");
			$constraints['table_filters'] = $stats_filters;
		}		
		$users 		  = $currentCourse -> getCourseUsers($constraints);
		$totalEntries = $currentCourse -> countCourseUsers($constraints);
		$smarty -> assign("T_TABLE_SIZE", $totalEntries);


		foreach ($users as $key => $value) {
			$users[$key] -> user['issued_certificate'] 	= $value -> user['issued_certificate'];
			$expire_certificateTimestamp = "";

			if ($value -> user['issued_certificate']) {
				$issuedData 						  = unserialize($value -> user['issued_certificate']);
				$users[$key] -> user['serial_number'] = $issuedData['serial_number'];

				//$dateFormat = eF_dateFormat();
				if (eF_checkParameter($issuedData['date'], 'timestamp')) {
					//$expire_certificateTimestamp = $currentCourse -> course['certificate_expiration'] + $issuedData['date'];
					//$dateExpire = date($dateFormat, $expire_certificateTimestamp);
					$expirationArray				= convertTimeToDays($currentCourse -> course['certificate_expiration']);
					$expire_certificateTimestamp 	= getCertificateExpirationTimestamp($issuedData['date'], $expirationArray);	
					
				} else {
					$expire_certificateTimestamp = $currentCourse -> course['certificate_expiration'] + strtotime($issuedData['date']);
					//$dateExpire = date($dateFormat, $expire_certificateTimestamp);
				}

				if (isset($currentCourse -> course['certificate_expiration']) && $currentCourse -> course['certificate_expiration'] != 0) {
					$users[$key] -> user['expire_certificate'] = $expire_certificateTimestamp;
				}
			}
		}

		$users	 	   = EfrontCourse :: convertUserObjectsToArrays($users);
		$dataSource    = $users;
		$tableName     = $_GET['ajax'];
		$alreadySorted = true;
		include("sorted_table.php");
	}

	if (isset($_GET['export']) && $_GET['export'] == 'rtf' && eF_checkParameter($_GET['course'], 'id') ) {
		if (eF_checkParameter($_GET['user'], 'login')) {
			$result = eF_getTableData("users_to_courses", "*", "users_LOGIN = '".$_GET['user']."' and courses_ID = '".$_GET['course']."' limit 1");
		} else {
			$result = array();
		}
		if (sizeof($result) == 1 || isset($_GET['preview'])) {
			$course = new EfrontCourse($_GET['course']);
			if (!isset($_GET['preview'])){
				$certificate_tpl_id_rtf = $course -> options['certificate_tpl_id_rtf'];
				if ($certificate_tpl_id_rtf <= 0) {
					$cfile = new EfrontFile(G_CERTIFICATETEMPLATEPATH."certificate1.rtf");
				} else {
					$cfile = new EfrontFile($certificate_tpl_id_rtf);
				}
				$template_data = file_get_contents($cfile['path']);
				$issued_data   = unserialize($result[0]['issued_certificate']);
				if (sizeof($issued_data) > 1){
					$certificate   = $template_data;
					$certificate   = str_replace("#organization#", utf8ToUnicode($issued_data['organization']), $certificate);
					$certificate   = str_replace("#user_name#", utf8ToUnicode($issued_data['user_name']), $certificate);
					$certificate   = str_replace("#user_surname#", utf8ToUnicode($issued_data['user_surname']), $certificate);
					$certificate   = str_replace("#course_name#", utf8ToUnicode($issued_data['course_name']), $certificate);
					$certificate   = str_replace("#grade#", utf8ToUnicode($issued_data['grade']), $certificate);
					if (eF_checkParameter($issued_data['date'], 'timestamp')) {
						$issued_data['date']  = formatTimestamp($issued_data['date']);
					}
					$certificate   = str_replace("#date#", utf8ToUnicode($issued_data['date']), $certificate);

					$certificate   = str_replace("#serial_number#", utf8ToUnicode($issued_data['serial_number']), $certificate);
				}
				$filename = "certificate_".$_GET['user'].".rtf";
			} else {
				$certificateDirectory = G_CERTIFICATETEMPLATEPATH;
				$selectedCertificate  = $_GET['certificate_tpl'];
				$certificate          = file_get_contents($certificateDirectory.$selectedCertificate);
				$filename = $_GET['certificate_tpl'];
			}
			$webserver         = explode(' ',$_SERVER['SERVER_SOFTWARE']);      //GET Server information from $_SERVER
			$webserver_type    = explode('/', $webserver[0]);

			$filenameRtf = "certificate_".$_GET['user'].".rtf";

			$filenamePdf = G_ROOTPATH."www/phplivedocx/samples/mail-merge/convert/certificate_".$_GET['user'].".pdf";
			file_put_contents(G_ROOTPATH."www/phplivedocx/samples/mail-merge/convert/certificate_".$_GET['user'].".rtf", $certificate);

			if (mb_stripos($webserver_type[0], "IIS") === false) {  //because of note here http://php.net/manual/en/function.file.php
				$retValues = file(G_SERVERNAME."phplivedocx/samples/mail-merge/convert/convert-document.php?filename=certificate_".$_GET['user']);
			} else {
				$retValues[0] == "false";
			}
			if ($retValues[0] == "true") {
				header("Content-type: application/pdf");
				header("Content-disposition: inline; filename=$filenamePdf");
				$filePdf = file_get_contents($filenamePdf);
				header("Content-length: " . strlen($filePdf));
				echo $filePdf;
				exit(0);
			} else {
				header("Content-type: application/rtf");
				header("Content-disposition: inline; filename=$filenameRtf");
				if (stristr($webserver_type[0], "IIS") === false) { //for IIS 7.x
					header("Content-length: " . strlen($certificate));
				}
				echo $certificate;
				exit(0);
			}
		}
	}
	if (isset($_GET['export']) && $_GET['export'] == 'xml' && eF_checkParameter($_GET['course'], 'id') ) {
		if (eF_checkParameter($_GET['user'], 'login')) {
			$result = eF_getTableData("users_to_courses", "*", "users_LOGIN = '".$_GET['user']."' and courses_ID = '".$_GET['course']."' limit 1");
		} else {
			$result = array();
		}

		if(sizeof($result) == 1 || isset($_GET['preview'])){

			$course = new EfrontCourse($_GET['course']);

			if(!isset($_GET['preview'])){

				$certificate_tpl_id = $course->options['certificate_tpl_id'];

				if($certificate_tpl_id <= 0){

					$mainTemplate = eF_getTableData("certificate_templates", "id",
										"certificate_name='".CERTIFICATES_MAIN_TEMPLATE_NAME."'");	// XXX
					$certificate_tpl_id = $mainTemplate[0]['id'];
				}

				
				
				$issued_data = unserialize($result[0]['issued_certificate']);
				$templateData = eF_getTableData("certificate_templates", "certificate_xml", "id=".$certificate_tpl_id);				
				
				foreach (eF_loadAllModules() as $module) {
					$module -> onXMLExportCourseCertificate($issued_data, $templateData, $course, $_GET['user']);
				}
				
				$userName = $issued_data['user_name'];
				$userSurName = $issued_data['user_surname'];
				$courseName = $issued_data['course_name'];
				$courseGrade = $issued_data['grade'];
				$serialNumber = $issued_data['serial_number'];

				if (eF_checkParameter($issued_data['date'], 'timestamp'))
				$certificateDate = formatTimestamp($issued_data['date']);

				if ($course -> course['certificate_expiration'] != 0) {
					$expirationArray				= convertTimeToDays($course -> course['certificate_expiration']);
					$expire_certificateTimestamp 	= getCertificateExpirationTimestamp($issued_data['date'], $expirationArray);	
					$expireDate						= formatTimestamp($expire_certificateTimestamp);
				}
			
				$xmlExport = new XMLExport($templateData[0]['certificate_xml']);
				$creator = $xmlExport->getCreator();
				$author = $xmlExport->getAuthor();
				$subjct = $xmlExport->getSubject($userName.' '.$userSurName);
				$keywrd = $xmlExport->getKeywords();
				$orientation = $xmlExport->getOrientation();

				$pdf = new TCPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
				$pdf->SetCreator($creator);
				$pdf->SetAuthor($author);
				$pdf->SetTitle($subjct);
				$pdf->SetSubject($subjct);
				$pdf->SetKeywords($keywrd);

				$xmlExport->setBackground($pdf);

				$pdf->SetAutoPageBreak(false);
				$pdf->setFontSubsetting(false);
				
				//Line for adding a high resolution image in background as full certificate image. 
				//Change PDF_IMAGE_SCALE_RATIO with a scale factor e.g. 4.15 (#3278)
				//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
				
				$pdf->AddPage();

				//				$pdf->AddFont('Garamond','', K_PATH_FONTS.'gara.php');
				//				$pdf->AddFont('Garamond','B', K_PATH_FONTS.'garabd.php');

				$xmlExport->drawLines($pdf);
				$xmlExport->showLabels($pdf);
				if (extension_loaded('gd')) {
					$xmlExport->showImages($pdf);
					$xmlExport->showLogo($pdf);
				}
				$xmlExport->showOrganization($pdf);
				$xmlExport->showDate($pdf, $certificateDate);
				$xmlExport->showSerialNumber($pdf, $serialNumber);
				$xmlExport->showStudentName($pdf, $userName.' '.$userSurName);
				$xmlExport->showCourseName($pdf, $courseName);
				$xmlExport->showGrade($pdf, $courseGrade);
				if ($course -> course['certificate_expiration'] != 0) {
					$xmlExport->showExpireDate($pdf, $expireDate);
				}
			
				if ($course -> options['custom1'] != '') {
					$course -> options['custom1'] = replaceCustomFieldsCertificate($course -> options['custom1'], $issued_data['date'], $_GET['user'], $course -> course['ceu'], $course ->options['training_hours']);
					$xmlExport->showCustomOne($pdf, $course -> options['custom1']);
				}
				if ($course -> options['custom2'] != '') {
					$course -> options['custom2'] = replaceCustomFieldsCertificate($course -> options['custom2'], $issued_data['date'], $_GET['user'], $course -> course['ceu'], $course ->options['training_hours']);
					$xmlExport->showCustomTwo($pdf, $course -> options['custom2']);
				}
				if ($course -> options['custom3'] != '') {
					$course -> options['custom3'] = replaceCustomFieldsCertificate($course -> options['custom3'], $issued_data['date'], $_GET['user'], $course -> course['ceu'], $course ->options['training_hours']);
					$xmlExport->showCustomThree($pdf, $course -> options['custom3']);
				}
				
				
				//				$fileNamePdf = "certificate_".$_GET['user'].".pdf";
				//				$pdf->Output($fileNamePdf, 'D');

				$fileNamePdf = $course -> course['name']."_".$_GET['user'].".pdf";
			}
			else{
				$tmp = explode('-', $_GET['certificate_tpl']);
				$certificate_tpl_id = $tmp[0];
				$templateData = eF_getTableData("certificate_templates", "certificate_xml", "id=".$certificate_tpl_id);

				$xmlExport = new XMLExport($templateData[0]['certificate_xml']);
				$creator = $xmlExport->getCreator();
				$author = $xmlExport->getAuthor();
				$subjct = $xmlExport->getSubject();
				$keywrd = $xmlExport->getKeywords();
				$orientation = $xmlExport->getOrientation();

				$pdf = new TCPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
				$pdf->SetCreator($creator);
				$pdf->SetAuthor($author);
				$pdf->SetTitle($subjct);
				$pdf->SetSubject($subjct);
				$pdf->SetKeywords($keywrd);

				$xmlExport->setBackground($pdf);

				$pdf->SetAutoPageBreak(false);
				$pdf->setFontSubsetting(false);
				
				//Line for adding a high resolution image in background as full certificate image. 
				//Change PDF_IMAGE_SCALE_RATIO with a scale factor e.g. 4.15 (#3278)
				//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
				
				$pdf->AddPage();

				$xmlExport->drawLines($pdf);
				$xmlExport->showLabels($pdf);
				$xmlExport->showImages($pdf);
				$xmlExport->showLogo($pdf);
				$xmlExport->showOrganization($pdf);
				$xmlExport->showDate($pdf, formatTimestamp(time()));
				$xmlExport->showSerialNumber($pdf, 'Serial Number');
				$xmlExport->showStudentName($pdf, 'Student Name');
				$xmlExport->showCourseName($pdf, 'Course Name');
				$xmlExport->showGrade($pdf, 'Grade');
				if ($course -> options['custom1'] != '') {
					$course -> options['custom1'] = replaceCustomFieldsCertificate($course -> options['custom1'], $issued_data['date']);
					$xmlExport->showCustomOne($pdf, $course -> options['custom1']);
				}
				if ($course -> options['custom2'] != '') {
					$course -> options['custom2'] = replaceCustomFieldsCertificate($course -> options['custom2'], $issued_data['date']);
					$xmlExport->showCustomTwo($pdf, $course -> options['custom2']);
				}
				if ($course -> options['custom3'] != '') {
					$course -> options['custom3'] = replaceCustomFieldsCertificate($course -> options['custom3'], $issued_data['date']);
					$xmlExport->showCustomThree($pdf, $course -> options['custom3']);
				}

				//				$fileNamePdf = "certificate_preview.pdf";
				//				$pdf->Output($fileNamePdf, 'D');

				$fileNamePdf = "certificate_preview.pdf";

			}
			$output = $pdf->Output('', 'S');

			$pathname = $currentUser->getDirectory().str_replace(array('/', ':', '\\', '?', '&'), '_',$fileNamePdf);
			file_put_contents($pathname, $output);
			$file = new EfrontFile($pathname, $output);
			$file -> sendFile();
				
		}
	}

} else if ($_GET['op'] == 'format_certificate'){
	if($currentCourse->options['certificate_export_method'] == 'rtf' && !isset($_GET['switch']))
	eF_redirect(basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate_docx");

	if (G_VERSIONTYPE != 'community'){ #cpp#ifndef COMMUNITY

		if($currentCourse->options['certificate_tpl_id'] > 0){
			$currentTemplate = eF_getTableData("certificate_templates", "certificate_type", "id=".$currentCourse->options['certificate_tpl_id']);
			$currentTemplateType = $currentTemplate[0]['certificate_type'];
			$currentTemplate = $currentCourse->options['certificate_tpl_id'].'-'.$currentTemplateType;
			$smarty->assign('T_CURRENT_TEMPLATE_TYPE', $currentTemplateType);
		}
		else if($currentCourse->options['certificate_tpl_id'] == 0){

			$mainTemplate = eF_getTableData("certificate_templates", "id", "certificate_name='".CERTIFICATES_MAIN_TEMPLATE_NAME."'"); // XXX
			$currentTemplate = $mainTemplate[0]['id'].'-main';
			$smarty->assign('T_CURRENT_TEMPLATE_TYPE', 'main');
		}

		if(isset($_GET['tid'])){

			$currentTemplate = eF_getTableData("certificate_templates", "certificate_type", "id=".$_GET['tid']);
			$currentTemplateType = $currentTemplate[0]['certificate_type'];
			$currentTemplate = $_GET['tid'].'-'.$currentTemplateType;
			$smarty->assign('T_CURRENT_TEMPLATE_TYPE', $currentTemplateType);
		}

		if ($_SESSION['s_type'] != 'administrator') {
			$templates = eF_getTableData("certificate_templates", "id, certificate_name, certificate_type",
						"certificate_type='main' OR id='".$currentCourse->options['certificate_tpl_id']."' OR users_LOGIN='".$GLOBALS['currentUser']->user['login']."'", "certificate_name");
		} else {
			$templates = eF_getTableData("certificate_templates", "id, certificate_name, certificate_type", "", "certificate_name");					
		}
		//$existingCertificates[0] = '';  @todo
		foreach($templates as $key => $value) {
			$existingCertificates[$value['id'].'-'.$value['certificate_type']] = $value['certificate_name'];
		}
	
		$form = new HTML_QuickForm("edit_course_certificate_form", "post",
		basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=format_certificate&switch=1', "", null, true);
		$form -> registerRule('checkParameter', 'callback', 'eF_checkParameter'); // Register this rule for checking user input with eF_checkParameter
		$form -> addElement('select', 'existing_certificate', _EXISTINGCERTIFICATETEMPLATESXML, $existingCertificates,
										'id="select_certificate" onChange="showHideMainTemplatesOperations()"');

		
		if(G_VERSIONTYPE != 'standard'){ #cpp#ifndef STANDARD
			
			$form -> addElement('text', 'custom1', 	 _ADDCUSTOMTEXT, 'class = "inputText"');
			$form -> addElement('text', 'custom2', 	 _ADDANOTHERCUSTOMTEXT, 'class = "inputText"');
			$form -> addElement('text', 'custom3', 	 _ADDANOTHERCUSTOMTEXT, 'class = "inputText"');

			$form->addElement('select', 'months', _MONTHS, range(0, 120), 'onChange="displayReset()"');
			$form->addElement('select', 'days', _DAYSCAPITAL, range(0, 30), 'onChange="displayReset()"');
			$form->addElement('advcheckbox', 'reset', _RESETCOURSEWHENEXPIRE, null, 'class="inputCheckbox"', array(0, 1));
			
			$form->addElement('select', 'months_reset', _MONTHS, range(0, 60));
			$form->addElement('select', 'days_reset', _DAYSCAPITAL, range(0, 30));
			
			
			if(isset($currentCourse->course['certificate_expiration']) && $currentCourse->course['certificate_expiration'] > 0){

				$defaultMonths = floor($currentCourse->course['certificate_expiration'] / (30 * 24 * 60 * 60));
				$defaultDays = ($currentCourse->course['certificate_expiration'] % (30 * 24 * 60 * 60)) / (24 * 60 * 60);
				$form->setDefaults(array('months' => $defaultMonths, 'days' => $defaultDays));
			}
			if(isset($currentCourse->course['reset_interval']) && $currentCourse->course['reset_interval'] > 0){

				$defaultMonthsReset = floor($currentCourse->course['reset_interval'] / (30 * 24 * 60 * 60));
				$defaultDaysReset = ($currentCourse->course['reset_interval'] % (30 * 24 * 60 * 60)) / (24 * 60 * 60);
				$form->setDefaults(array('months_reset' => $defaultMonthsReset, 'days_reset' => $defaultDaysReset));
			}
		} #cpp#endif

		$form->setDefaults(array('existing_certificate' => $currentTemplate, 'reset' => $currentCourse->course['reset'], 'custom1' => $currentCourse-> options['custom1'], 'custom2' => $currentCourse-> options['custom2'], 'custom3' => $currentCourse-> options['custom3']));

		if(isset($currentUser->coreAccess['course_settings']) && $currentUser->coreAccess['course_settings'] != 'change'){
			$form->freeze();
		}
		else{
			$form->addElement('submit', 'submit_certificate', _SAVE, 'class="flatButton"');

			if($form->isSubmitted() && $form->validate()){

				try{
					if (G_VERSIONTYPE != 'standard'){ #cpp#ifndef STANDARD

						$duration = ($form->exportValue('days') * 24 * 60 * 60) + ($form->exportValue('months') * 30 * 24 * 60 * 60);
						$currentCourse->course['certificate_expiration'] = $duration;
						
						$duration_reset = ($form->exportValue('days_reset') * 24 * 60 * 60) + ($form->exportValue('months_reset') * 30 * 24 * 60 * 60);
						$currentCourse -> course['reset_interval'] = $duration_reset;
						
						//eF_deleteTableData("notifications", "id_type_entity LIKE '%_". (-1) * EfrontEvent::COURSE_CERTIFICATE_EXPIRY . "_" . $currentCourse -> course['id']. "'");
						
						if ($form->exportValue('months') != 0 || $form->exportValue('days') != 0) {
							$currentCourse->course['reset'] = $form->exportValue('reset');
							//EfrontEvent::triggerEvent(array("type" => EfrontEvent::COURSE_CERTIFICATE_EXPIRY, "timestamp" => time()+$duration,   "lessons_ID" => $currentCourse -> course['id'], "lessons_name" => $currentCourse -> course['name']));								
						} else {
							$currentCourse->course['reset'] = 0;
						}
						if ($form->exportValue('months_reset') != 0 || $form->exportValue('days_reset') != 0) {
							$currentCourse->course['reset'] = 1;
						}
						if ($form->exportValue('custom1') != '') {
							$currentCourse->options['custom1'] = $form->exportValue('custom1');
						} else {
							unset($currentCourse->options['custom1']);
						}
						if ($form->exportValue('custom2') != '') {
							$currentCourse->options['custom2'] = $form->exportValue('custom2');
						} else {
							unset($currentCourse->options['custom2']);
						}
						if ($form->exportValue('custom3') != '') {
							$currentCourse->options['custom3'] = $form->exportValue('custom3');
						} else {
							unset($currentCourse->options['custom3']);
						}
					} #cpp#endif

					$certificateID = $form->exportValue('existing_certificate');
					$certificateID = explode('-', $certificateID);
					$currentCourse->options['certificate_tpl_id'] = $certificateID[0];
					$currentCourse->options['certificate_export_method'] = 'xml';
					$currentCourse->persist();

					$message = urlencode(_SUCCESFULLYUPDATEDCERTIFICATE)."&message_type=success";
					eF_redirect(basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=course_certificates&message=".$message);
				}
				catch(Exception $e){
					handleNormalFlowExceptions($e);
				}
			}
		}

		$renderer = prepareFormRenderer($form);
		$smarty->assign('T_CERTIFICATE_FORM', $renderer->toArray());

	} #cpp#endif
} else if ($_GET['op'] == 'format_certificate_docx') {
	if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
		if ($currentCourse -> options['certificate_tpl_id_rtf'] > 0){
			try {
				$certificateFile = new EfrontFile($currentCourse -> options['certificate_tpl_id_rtf']);
			} catch (Exception $e) {
				//$certificateFile = new EfrontFile(G_CERTIFICATETEMPLATEPATH."certificate1.rtf");
				$currentCourse -> options['certificate_tpl_id_rtf'] = '';
				$currentCourse -> persist();
				$message = _CERTIFICATEFILEWASCORRUPTORMISSINGANDWASRESET;
				$certificateFile = new EfrontFile(G_CERTIFICATETEMPLATEPATH."certificate1.rtf");
			}
			$dname = $certificateFile -> offsetGet('name');
		}

		try {
			$certificateFileSystemTree = new FileSystemTree(G_CERTIFICATETEMPLATEPATH);
			foreach (new EfrontFileTypeFilterIterator(new EfrontFileOnlyFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator($certificateFileSystemTree -> tree, RecursiveIteratorIterator :: SELF_FIRST))), array('rtf')) as $key => $value) {
				$existingCertificates[basename($key)] = basename($key);
			}
		} catch (Exception $e) {
			handleNormalFlowExceptions($e);
		}


		$form = new HTML_QuickForm("edit_course_certificate_form", "post", basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=format_certificate_docx', "", null, true);
		$form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');                   //Register this rule for checking user input with our function, eF_checkParameter
		$form -> addElement('file', 'file_upload', _CERTIFICATETEMPLATE, 'class = "inputText"');
		$form -> addElement('select', 'existing_certificate', _ORSELECTONEFROMLIST, $existingCertificates, "id = 'select_certificate'");

		if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
			$form -> addElement('select', 'months' , _MONTHS, range(0,60), 'onChange="displayReset()"');
			$form -> addElement('select', 'days' , _DAYSCAPITAL, range(0,30), 'onChange="displayReset()"');
			$form -> addElement('advcheckbox', 'reset', _RESETCOURSEWHENEXPIRE, null, 'class = "inputCheckbox"', array(0, 1));

			$form->addElement('select', 'months_reset', _MONTHS, range(0, 60));
			$form->addElement('select', 'days_reset', _DAYSCAPITAL, range(0, 30));
			
			if (isset($currentCourse -> course['certificate_expiration']) && $currentCourse -> course['certificate_expiration'] > 0) {
				$defaultMonths 	= floor($currentCourse -> course['certificate_expiration']/(30*24*60*60));
				$defaultDays 	= ($currentCourse -> course['certificate_expiration']%(30*24*60*60))/(24*60*60);
				$form -> setDefaults(array('months' => $defaultMonths, 'days' => $defaultDays));
			}
			if(isset($currentCourse->course['reset_interval']) && $currentCourse->course['reset_interval'] > 0){
				$defaultMonthsReset = floor($currentCourse->course['reset_interval'] / (30 * 24 * 60 * 60));
				$defaultDaysReset = ($currentCourse->course['reset_interval'] % (30 * 24 * 60 * 60)) / (24 * 60 * 60);
				$form->setDefaults(array('months_reset' => $defaultMonthsReset, 'days_reset' => $defaultDaysReset));
			}
		} #cpp#endif

		$form -> addElement('button', 'preview', _PREVIEW, 'onclick = "location = (\''.basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=course_certificates&export=rtf&preview=1&certificate_tpl=\'+$(\'select_certificate\').value)" title = "'._VIEWCERTIFICATE.'" class = "flatButton"');


		$form -> setDefaults(array('existing_certificate' => $dname, 'reset' => $currentCourse -> course['reset']));
		$form -> setMaxFileSize(FileSystemTree :: getUploadMaxSize() * 1024);

		if (isset($currentUser -> coreAccess['course_settings']) && $currentUser -> coreAccess['course_settings'] != 'change') {
			$form -> freeze();
		} else {
			$form -> addElement('submit', 'submit_certificate', _SAVE, 'class = "flatButton"');
			if ($form -> isSubmitted() && $form -> validate()) {
				$certificateDirectory = G_CERTIFICATETEMPLATEPATH;
				if (!is_dir($certificateDirectory)) {
					mkdir($certificateDirectory, 0755);
				}
				$logoid = 0;
				try {
					if ($_FILES['file_upload']['size'] > 0) {
						$filesystem    = new FileSystemTree($certificateDirectory);
						$uploadedFile  = $filesystem -> uploadFile('file_upload', $certificateDirectory);
						$certificateid = $uploadedFile['id'];
					} else {
						$selectedCertificate = $form -> exportValue('existing_certificate');
						$certificateFile = new EfrontFile(G_CERTIFICATETEMPLATEPATH.$selectedCertificate);
						if ($certificateFile['id'] < 0) { //if the file doesn't exist, then import it
							$selectedCertificate = $certificateFileSystemTree -> seekNode(G_CERTIFICATETEMPLATEPATH.$selectedCertificate);
							$newList             = FileSystemTree :: importFiles($selectedCertificate['path']);
							$certificateid       = key($newList);
						} else {
							$certificateid = $certificateFile['id'];
						}
					}

					if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
						$duration = ($_POST['days']*24*60*60) + ($_POST['months']*30*24*60*60);
						$currentCourse -> course['certificate_expiration'] = $duration;
						$duration_reset = ($form->exportValue('days_reset') * 24 * 60 * 60) + ($form->exportValue('months_reset') * 30 * 24 * 60 * 60);
						$currentCourse -> course['reset_interval'] = $duration_reset;
						
						if ($_POST['months'] != 0 || $_POST['days'] != 0) {
							$currentCourse -> course['reset'] = $_POST['reset'];
						} else {
							$currentCourse -> course['reset'] = 0;
						}

						if ($form->exportValue('months_reset') != 0 || $form->exportValue('days_reset') != 0) {
							$currentCourse->course['reset'] = 1;
						}
				
					} #cpp#endif

					$currentCourse -> options['certificate_tpl_id_rtf'] = $certificateid;
					$currentCourse->options['certificate_export_method'] = 'rtf';
					$currentCourse -> persist();
					eF_redirect(basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=course_certificates&message=".urlencode(_SUCCESFULLYUPDATEDCERTIFICATE)."&message_type=success");
				} catch (Exception $e) {
					handleNormalFlowExceptions($e);
				}
			}
		}
		$renderer = prepareFormRenderer($form);
		$smarty -> assign('T_CERTIFICATE_FORM', $renderer -> toArray());
	} #cpp#endif
} else if($_GET['op'] == 'add_certificate_template' || $_GET['op'] == 'edit_certificate_template'){

	if(G_VERSIONTYPE != 'community'){ #cpp#ifndef COMMUNITY

		if($_GET['op'] == 'add_certificate_template') {
			$postTarget = basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=add_certificate_template';
		}
		else if($_GET['op'] == 'edit_certificate_template'){

			if ($_SESSION['s_type'] != 'administrator') {
				$templates = eF_getTableData("certificate_templates", "id, certificate_name, certificate_type",
						"certificate_type='main' OR id='".$currentCourse->options['certificate_tpl_id']."' OR users_LOGIN='".$GLOBALS['currentUser']->user['login']."'", "certificate_name");
			} else {
				$templates = eF_getTableData("certificate_templates", "id, certificate_name, certificate_type", "", "certificate_name");
			}
				
			foreach($templates as $key => $value) {
				$userTemplates[$value['id']] = $value['id'];
			}
			if(!in_array($_GET['template_id'], $userTemplates)){

				$message = _CERTIFICATETEMPLATENOACCESS;
				$message_type = 'failure';

				$redirectUrl = "".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate&message=".urlencode($message);
				$redirectUrl .= "&message_type=".$message_type."&reset_popup=1";
				$smarty->assign('T_ADD_CERTIFICATE_TEMPLATE_REDIRECT', $redirectUrl);
			}

			$tid = $_GET['template_id'];
			$postTarget = basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=edit_certificate_template&template_id='.$tid;
		}

		$form = new HTML_QuickForm("add_certificate_template_form", "post", $postTarget, "", null, true);
		$form->registerRule('checkParameter', 'callback', 'eF_checkParameter'); // Register this rule for checking user input with eF_checkParameter
		$form->addElement('text', 'certificate_name', _CERTIFICATENAME, 'class="inputText"');
		$form->addRule('certificate_name', _THEFIELD.' "'._CERTIFICATENAME.'" '._ISMANDATORY, 'required', null, 'client');
		$form->addElement('textarea', 'certificate_xml', _XML, 'style="width:99%; height:333px; font-weight:normal; font-size:11px;"');
		$form->addElement('submit', 'preview_certificate_template', _PREVIEW, 'class="flatButton"');

		if($_GET['op'] == 'add_certificate_template') {
			$form->addElement('submit', 'submit_certificate_template', _SAVE, 'class="flatButton"');
		}
		else if($_GET['op'] == 'edit_certificate_template') {
			$form->addElement('submit', 'submit_certificate_template', _UPDATE, 'class="flatButton"');
		}
		if($_GET['op'] == 'add_certificate_template'){

			$mainTemplateXMLFile = new EfrontFile(G_CERTIFICATETEMPLATEPATH."Minimum Decoration (Unicode).xml");
			$mainTemplateXMLFileContents = file_get_contents($mainTemplateXMLFile['path']);
			$form->setDefaults(array('certificate_xml' => $mainTemplateXMLFileContents));
		}
		else if($_GET['op'] == 'edit_certificate_template'){

			$editTemplate = eF_getTableData("certificate_templates", "certificate_name, certificate_xml", "id=".$tid);
			$form->setDefaults($editTemplate[0]);
		}

		if($form->isSubmitted() && $form->validate()){

			global $popup;
			(isset($popup) && $popup == 1) ? $popup_ = '&popup=1' : $popup_ = '';

			$formValues = $form->exportValues();

			if(in_array('preview_certificate_template', array_keys($formValues))){

				$xmlExport = new XMLExport($formValues['certificate_xml']);
				$creator = $xmlExport->getCreator();
				$author = $xmlExport->getAuthor();
				$subjct = $xmlExport->getSubject();
				$keywrd = $xmlExport->getKeywords();
				$orientation = $xmlExport->getOrientation();

				$pdf = new TCPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
				$pdf->SetCreator($creator);
				$pdf->SetAuthor($author);
				$pdf->SetTitle($subjct);
				$pdf->SetSubject($subjct);
				$pdf->SetKeywords($keywrd);

				$xmlExport->setBackground($pdf);

				$pdf->SetAutoPageBreak(false);
				$pdf->setFontSubsetting(false);
				
				//Line for adding a high resolution image in background as full certificate image. 
				//Change PDF_IMAGE_SCALE_RATIO with a scale factor e.g. 4.15 (#3278)
				//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
				
				$pdf->AddPage();

				$xmlExport->drawLines($pdf);
				$xmlExport->showLabels($pdf);
				$xmlExport->showImages($pdf);
				$xmlExport->showLogo($pdf);
				$xmlExport->showOrganization($pdf);
				$xmlExport->showDate($pdf, formatTimestamp(time()));
				$xmlExport->showSerialNumber($pdf, 'Serial Number');
				$xmlExport->showStudentName($pdf, 'Student Name');
				$xmlExport->showCourseName($pdf, 'Course Name');
				$xmlExport->showGrade($pdf, 'Grade');			

				$xmlExport->showCustomOne($pdf, 'custom1');
				$xmlExport->showCustomTwo($pdf, 'custom2');
				$xmlExport->showCustomThree($pdf, 'custom3');
			

				$fileNamePdf = "certificate_preview.pdf";
				header("Content-type: application/pdf");
				header("Content-disposition: attachment; filename=".$fileNamePdf);
				echo $pdf->Output('', 'S');
				exit(0);
			}
			else{
				$dbFields = array(
						"certificate_name" => $formValues['certificate_name'],
						"certificate_xml" => $formValues['certificate_xml'],
						"certificate_type" => "course",
						"users_LOGIN" => $GLOBALS['currentUser']->user['login'],
				);

				if($formValues['certificate_xml'] == ''){

					$message = urlencode(_ADDCERTIFICATETEMPLATEEMPTYXML)."&message_type=failure";

					if($_GET['op'] == 'add_certificate_template')
					eF_redirect("".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=add_certificate_template&message=".$message.$popup_);
					else if($_GET['op'] == 'edit_certificate_template')
					eF_redirect("".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=edit_certificate_template&template_id=".$tid."&message=".$message.$popup_);
				}

				if($_GET['op'] == 'add_certificate_template'){

					if(($new = eF_insertTableData("certificate_templates", $dbFields))){

						$message = _SUCCESSFULLYADDEDCERTIFICATETEMPLATE;
						$message_type = 'success';
					}
					else{
						$message = _PROBLEMADDEDCERTIFICATETEMPLATE;
						$message_type = 'failure';
					}

					$redirectUrl = "".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate&message=".urlencode($message);
					$redirectUrl .= "&message_type=".$message_type."&reset_popup=1&tid=".$new;
					$smarty->assign('T_ADD_CERTIFICATE_TEMPLATE_REDIRECT', $redirectUrl);
				}
				else if($_GET['op'] == 'edit_certificate_template'){

					if(eF_updateTableData("certificate_templates", $dbFields, "id=".$tid)){

						$message = _SUCCESSFULLYUPDATEDCERTIFICATETEMPLATE;
						$message_type = 'success';
					}
					else{
						$message = _PROBLEMUPDATEDCERTIFICATETEMPLATE;
						$message_type = 'failure';
					}

					$redirectUrl = "".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate&message=".urlencode($message);
					$redirectUrl .= "&message_type=".$message_type."&reset_popup=1&tid=".$tid;
					$smarty->assign('T_ADD_CERTIFICATE_TEMPLATE_REDIRECT', $redirectUrl);
				}
			}
		}

		$renderer = prepareFormRenderer($form);
		$smarty->assign('T_ADD_CERTIFICATE_TEMPLATE_FORM', $renderer->toArray());

	} #cpp#endif
} else if($_GET['op'] == 'rename_certificate_template'){

	if(G_VERSIONTYPE != 'community'){ #cpp#ifndef COMMUNITY

		if ($_SESSION['s_type'] != 'administrator') {
			$templates = eF_getTableData("certificate_templates", "id, certificate_name, certificate_type",
						"certificate_type='main' OR id='".$currentCourse->options['certificate_tpl_id']."' OR users_LOGIN='".$GLOBALS['currentUser']->user['login']."'", "certificate_name");
		} else {
			$templates = eF_getTableData("certificate_templates", "id, certificate_name, certificate_type", "", "certificate_name");					
		}
		

		foreach($templates as $key => $value) {
			$userTemplates[$value['id']] = $value['id'];
		}
		if(!in_array($_GET['template_id'], $userTemplates)){

			$message = _CERTIFICATETEMPLATENOACCESS;
			$message_type = 'failure';

			$redirectUrl = "".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate&message=".urlencode($message);
			$redirectUrl .= "&message_type=".$message_type."&reset_popup=1";
			$smarty->assign('T_RENAME_CERTIFICATE_TEMPLATE_REDIRECT', $redirectUrl);
		}

		$tid = $_GET['template_id'];
		$postTarget = basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=rename_certificate_template&template_id='.$tid;

		$form = new HTML_QuickForm("rename_certificate_template_form", "post", $postTarget, "", null, true);
		$form->registerRule('checkParameter', 'callback', 'eF_checkParameter'); // Register this rule for checking user input with eF_checkParameter
		$form->addElement('text', 'certificate_name', _CERTIFICATENAME, 'class="inputText"');
		$form->addRule('certificate_name', _THEFIELD.' "'._CERTIFICATENAME.'" '._ISMANDATORY, 'required', null, 'client');
		$form->addElement('submit', 'rename_certificate_template', _SAVE, 'class="flatButton"');

		$renameTemplate = eF_getTableData("certificate_templates", "certificate_name", "id=".$tid);
		$form->setDefaults(array('certificate_name' => $renameTemplate[0]['certificate_name']));

		if($form->isSubmitted() && $form->validate()){

			$formValues = $form->exportValues();
			$dbFields = array(
					"certificate_name" => $formValues['certificate_name']
			);

			if(eF_updateTableData("certificate_templates", $dbFields, "id=".$tid)){

				$message = _SUCCESSFULLYRENAMECERTIFICATETEMPLATE;
				$message_type = 'success';
			}
			else{
				$message = _PROBLEMRENAMECERTIFICATETEMPLATE;
				$message_type = 'failure';
			}

			$redirectUrl = "".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate&message=".urlencode($message);
			$redirectUrl .= "&message_type=".$message_type."&reset_popup=1&tid=".$tid;
			$smarty->assign('T_RENAME_CERTIFICATE_TEMPLATE_REDIRECT', $redirectUrl);
		}

		$renderer = prepareFormRenderer($form);
		$smarty->assign('T_RENAME_CERTIFICATE_TEMPLATE_FORM', $renderer->toArray());

	} #cpp#endif
} else if($_GET['op'] == 'clone_certificate_template'){

	if(G_VERSIONTYPE != 'community'){ #cpp#ifndef COMMUNITY

		if ($_SESSION['s_type'] != 'administrator') {
			$templates = eF_getTableData("certificate_templates", "id, certificate_name, certificate_type",
					"certificate_type='main' OR id='".$currentCourse->options['certificate_tpl_id']."' OR users_LOGIN='".$GLOBALS['currentUser']->user['login']."'", "certificate_name");
		} else {
			$templates = eF_getTableData("certificate_templates", "id, certificate_name, certificate_type", "", "certificate_name");
		}
		
		foreach($templates as $key => $value) {
			$userTemplates[$value['id']] = $value['id'];
		}
		if(!in_array($_GET['template_id'], $userTemplates)){

			$message = _CERTIFICATETEMPLATENOACCESS;
			$message_type = 'failure';

			$redirectUrl = "".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate&message=".urlencode($message);
			$redirectUrl .= "&message_type=".$message_type."&reset_popup=1";
			$smarty->assign('T_CLONE_CERTIFICATE_TEMPLATE_REDIRECT', $redirectUrl);
		}

		$tid = $_GET['template_id'];
		$postTarget = basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=clone_certificate_template&template_id='.$tid;

		$form = new HTML_QuickForm("clone_certificate_template_form", "post", $postTarget, "", null, true);
		$form->registerRule('checkParameter', 'callback', 'eF_checkParameter'); // Register this rule for checking user input with eF_checkParameter
		$form->addElement('text', 'certificate_name', _CERTIFICATENAME, 'class="inputText"');
		$form->addRule('certificate_name', _THEFIELD.' "'._CERTIFICATENAME.'" '._ISMANDATORY, 'required', null, 'client');
		$form->addElement('submit', 'clone_certificate_template', _SAVE, 'class="flatButton"');

		if($form->isSubmitted() && $form->validate()){

			$cloneTemplate = eF_getTableData("certificate_templates", "certificate_xml", "id=".$tid);

			$formValues = $form->exportValues();
			$dbFields = array(
					"certificate_name" => $formValues['certificate_name'],
					"certificate_xml" => $cloneTemplate[0]['certificate_xml'],
					"certificate_type" => "course",
					"users_LOGIN" => $GLOBALS['currentUser']->user['login'],
			);

			if(($cloned = eF_insertTableData("certificate_templates", $dbFields))){

				$message = _SUCCESSFULLYCLONECERTIFICATETEMPLATE;
				$message_type = 'success';
			}
			else{
				$message = _PROBLEMCLONECERTIFICATETEMPLATE;
				$message_type = 'failure';
			}

			$redirectUrl = "".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate&message=".urlencode($message);
			$redirectUrl .= "&message_type=".$message_type."&reset_popup=1&tid=".$cloned;
			$smarty->assign('T_CLONE_CERTIFICATE_TEMPLATE_REDIRECT', $redirectUrl);
		}

		$renderer = prepareFormRenderer($form);
		$smarty->assign('T_CLONE_CERTIFICATE_TEMPLATE_FORM', $renderer->toArray());

	} #cpp#endif
} else if($_GET['op'] == 'delete_certificate_template'){

	if(G_VERSIONTYPE != 'community'){ #cpp#ifndef COMMUNITY

		if ($_SESSION['s_type'] != 'administrator') {
			$templates = eF_getTableData("certificate_templates", "id, certificate_name, certificate_type",
						"certificate_type='main' OR id='".$currentCourse->options['certificate_tpl_id']."' OR users_LOGIN='".$GLOBALS['currentUser']->user['login']."'", "certificate_name");
		} else {
			$templates = eF_getTableData("certificate_templates", "id, certificate_name, certificate_type", "", "certificate_name");					
		}
		
		foreach($templates as $key => $value) {
			$userTemplates[$value['id']] = $value['id'];
		}
		if(!in_array($_GET['template_id'], $userTemplates)){

			$message = urlencode(_CERTIFICATETEMPLATENOACCESS)."&message_type=failure";
			eF_redirect("".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate&switch=1&message=".$message);
		}

		if($currentCourse->options['certificate_tpl_id'] == $_GET['template_id']){

			$mainTemplate = eF_getTableData("certificate_templates", "id", "certificate_name='".CERTIFICATES_MAIN_TEMPLATE_NAME."'"); // XXX
			$currentCourse->options['certificate_tpl_id'] = $mainTemplate[0]['id'];
			$currentCourse->persist();
		}

		if(eF_deleteTableData("certificate_templates", "id=".$_GET['template_id'])){

			$message = urlencode(_SUCCESSFULLYDELETECERTIFICATETEMPLATE)."&message_type=success";
			eF_redirect("".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate&switch=1&message=".$message);
		}
		else{
			$message = urlencode(_PROBLEMDELETECERTIFICATETEMPLATE)."&message_type=failure";
			eF_redirect("".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=format_certificate&switch=1&message=".$message);
		}

	} #cpp#endif
} else if ($_GET['op'] == 'course_rules') {
	$courseLessons = $currentCourse -> getCourseLessons();
	if ($currentCourse -> course['depends_on']) {
		try {
			$dependsOn = new EfrontCourse($currentCourse -> course['depends_on']);
			$smarty -> assign("T_DEPENDSON_COURSE", $dependsOn->course['name']);
		} catch (Exception $e) {}
	}

	$rules_form = new HTML_QuickForm("course_rules_form", "post", basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=course_rules", "", null, true);
	if (isset($currentUser -> coreAccess['course_settings']) && $currentUser -> coreAccess['course_settings'] != 'change') {
		$rules_form -> freeze();
	} else {
		$rules_form -> addElement('submit', 'submit_rule', _SUBMIT, 'class = "flatButton"');
		if ($rules_form -> isSubmitted() && $rules_form -> validate()) {
			foreach ($_POST['rules'] as $rule_lesson) {
				if (sizeof(array_unique($rule_lesson['lesson'])) != sizeof($rule_lesson['lesson'])) {
					$duplicate = true;
				}
			}
			if (!isset($duplicate)) {
				try {
					$currentCourse -> rules = $_POST['rules'];
					$currentCourse -> persist();
					eF_redirect("".basename($_SERVER['PHP_SELF'])."?".$baseUrl."&op=course_rules&message=".urlencode(_SUCCESFULLYSETORDER)."&message_type=success");
				} catch (Exception $e) {
					$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
					$message      = _PROBLEMSETTINGORDER.': '.$e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
					$message_type = 'failure';
				}
			} else {
				$message      = _DUPLICATESARENOTALLOWED;
				$message_type = 'failure';
			}
		}
		
		if (isset($_GET['ajax']) && isset($_GET['inter_course_rule'])) {
			try {
				if ($_GET['inter_course_rule']) {
					$dependsOn = new EfrontCourse($_GET['inter_course_rule']);
					if ($dependsOn -> course['id'] != $currentCourse -> course['id']) {
						$currentCourse -> course['depends_on'] = $dependsOn -> course['id'];
					} else {
						throw new Exception(_YOUCANNOTSETSAMECOURSEASRULE);
					}
				} else {
					$currentCourse -> course['depends_on'] = 0;					
				}
				$currentCourse -> persist();
			} catch (Exception $e) {
				handleAjaxExceptions($e);
			}
		}
	}
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

	$rules_form -> accept($renderer);
	$smarty -> assign('T_COURSE_RULES_FORM', $renderer -> toArray());
	$smarty -> assign("T_COURSE_RULES", $currentCourse -> rules);
	$smarty -> assign('T_COURSE', $currentCourse -> course);
	$smarty -> assign("T_COURSE_LESSONS", EfrontCourse::convertLessonObjectsToArrays($courseLessons));
} else if ($_GET['op'] == 'course_order') {
	$courseLessons = $currentCourse -> getCourseLessons();

	$smarty -> assign('T_COURSE', $currentCourse -> course);
	$smarty -> assign('T_COURSE_LESSONS', EfrontCourse::convertLessonObjectsToArrays($courseLessons));

	if (isset($_GET['ajax']) && isset($_GET['order'])) {
		try {
			$order    = explode(",", $_GET['order']);
			$previous = 0;
			foreach ($order as $value) {
				$result = explode("-", $value);
				if (in_array($value, array_keys($courseLessons))) {
					$fields = array("previous_lessons_ID" => $previous);
					$where  = "courses_ID=".$currentCourse -> course['id']." and lessons_ID=".$result[0];
					EfrontCourse::persistCourseLessons($fields, $where);
				}
				$previous = $result[0];
			}
			echo _TREESAVEDSUCCESSFULLY;
		} catch (Exception $e) {
			header("HTTP/1.0 500");
			echo $e -> getMessage().' ('.$e -> getCode().')';
		}
		exit;
	}
} else if ($_GET['op'] == 'course_scheduling') {
	$courseLessons = $currentCourse -> getCourseLessons();
	$smarty -> assign("T_CURRENT_COURSE", $currentCourse);

	try {
		if (isset($_GET['set_schedule']) && in_array($_GET['set_schedule'], array_keys($courseLessons))) {
			$lesson        = new EfrontLesson($_GET['set_schedule']);
			$fromTimestamp = mktime($_GET['from_Hour'], $_GET['from_Minute'], 0, $_GET['from_Month'], $_GET['from_Day'], $_GET['from_Year']);
			$toTimestamp   = mktime($_GET['to_Hour'],   $_GET['to_Minute'],   0, $_GET['to_Month'],   $_GET['to_Day'],   $_GET['to_Year']);
			if ($fromTimestamp < $toTimestamp) {
				$currentCourse -> setLessonScheduleInCourse($lesson, $fromTimestamp, $toTimestamp);
				/*
				 $lesson -> lesson['from_timestamp'] = $fromTimestamp;
				 $lesson -> lesson['to_timestamp']   = $toTimestamp;
				 $lesson -> persist();

				 eF_deleteTableData("notifications", "id_type_entity LIKE '%_". (-1) * EfrontEvent::LESSON_PROGRAMMED_START . "_" . $lesson -> lesson['id']. "'");
				 eF_deleteTableData("notifications", "id_type_entity LIKE '%_". (-1) * EfrontEvent::LESSON_PROGRAMMED_EXPIRY . "_" . $lesson -> lesson['id']. "'");

				 EfrontEvent::triggerEvent(array("type" => EfrontEvent::LESSON_PROGRAMMED_START,  "timestamp" => $lesson -> lesson['from_timestamp'], "lessons_ID" => $lesson -> lesson['id'], "lessons_name" => $lesson -> lesson['name']));
				 EfrontEvent::triggerEvent(array("type" => EfrontEvent::LESSON_PROGRAMMED_EXPIRY, "timestamp" => $lesson -> lesson['to_timestamp'],   "lessons_ID" => $lesson -> lesson['id'], "lessons_name" => $lesson -> lesson['name']));
				 */
				echo _FROM.' '.formatTimestamp($fromTimestamp, 'time_nosec').' '._TO.' '.formatTimestamp($toTimestamp, 'time_nosec').'&nbsp;';
			} else {
				header("HTTP/1.0 500");
				echo _ENDDATEMUSTBEBEFORESTARTDATE;
			}
			exit;
		} else if (isset($_GET['set_period']) && in_array($_GET['set_period'], array_keys($courseLessons))) {
			$lesson        = new EfrontLesson($_GET['set_period']);		
				
			$currentCourse -> setLessonScheduleInCourse($lesson, false, false, $_GET['start_period'], $_GET['stop_period']);
			echo _FROM.' '.$_GET['start_period'].' '._DAYSAFTERCOURSEENROLLMENT.' '._AND.' '._FOR.' '.$_GET['stop_period'].' '._DAYSCONDITIONALLOWERCASE.'&nbsp;';
			exit;
		} else if (isset($_GET['delete_schedule']) && in_array($_GET['delete_schedule'], array_keys($courseLessons))) {
			$lesson = new EfrontLesson($_GET['delete_schedule']);
			$currentCourse -> unsetLessonScheduleInCourse($lesson);
			/*
			 $lesson = new EfrontLesson($_GET['delete_schedule']);
			 $lesson -> lesson['from_timestamp'] = null;
			 $lesson -> lesson['to_timestamp']   = null;
			 $lesson -> lesson['shift']          = 0;

			 $lesson -> persist();

			 // @TODO maybe proper class internal invalidation
			 eF_deleteTableData("notifications", "id_type_entity LIKE '%_". (-1) * EfrontEvent::LESSON_PROGRAMMED_START . "_" . $lesson -> lesson['id']. "'");
			 eF_deleteTableData("notifications", "id_type_entity LIKE '%_". (-1) * EfrontEvent::LESSON_PROGRAMMED_EXPIRY . "_" . $lesson -> lesson['id']. "'");
			 */
			exit;
		} else if (isset($_GET['set_schedule']) && $_GET['set_schedule'] == 0) {	
			$previous_start_date	= $currentCourse -> course['start_date'];
			$previous_end_date		= $currentCourse -> course['end_date'];
						
			$fromTimestamp = mktime($_GET['from_Hour'], $_GET['from_Minute'], 0, $_GET['from_Month'], $_GET['from_Day'], $_GET['from_Year']);
			$toTimestamp   = mktime($_GET['to_Hour'],   $_GET['to_Minute'],   0, $_GET['to_Month'],   $_GET['to_Day'],   $_GET['to_Year']);
			if ($fromTimestamp < $toTimestamp) {
				$currentCourse -> course['start_date'] = $fromTimestamp;
				$currentCourse -> course['end_date']   = $toTimestamp;
				$currentCourse -> persist();

				$courseUsers = $currentCourse->getCourseUsers(array('archive' => false, 'active' => true, 'return_objects' => false));

				if ($previous_start_date != $currentCourse -> course['start_date']) {
					eF_deleteTableData("notifications", "id_type_entity LIKE '%_". (-1) * EfrontEvent::COURSE_PROGRAMMED_START . "_" . $currentCourse -> course['id']. "%'");
					if ($currentCourse -> course['start_date'] > time()) {
						foreach ($courseUsers as $user) {
							$event = array("type" => EfrontEvent::COURSE_PROGRAMMED_START,
									"users_LOGIN" => $user['login'],
									"users_name" => $user['name'],
									"users_surname" => $user['surname'],
									"timestamp" => $currentCourse -> course['start_date'],
									"lessons_ID" => $currentCourse -> course['id'],
									"lessons_name" => $currentCourse -> course['name'],
									"entity_ID" => $currentCourse -> course['start_date'],	//we use the entity_ID, entity_name fields for different functionality, see createSubstitutionsArray()
									"entity_name" => $currentCourse -> course['end_date']);
							EfrontEvent::triggerEvent($event);
						}
					}
				}
				if ($previous_end_date != $currentCourse -> course['end_date']) {
					eF_deleteTableData("notifications", "id_type_entity LIKE '%_". (-1) * EfrontEvent::COURSE_PROGRAMMED_EXPIRY . "_" . $currentCourse -> course['id']. "%'");
					if ($currentCourse -> course['end_date'] > time()) {
						foreach ($courseUsers as $user) {
							$event = array("type" => EfrontEvent::COURSE_PROGRAMMED_EXPIRY,
									"users_LOGIN" => $user['login'],
									"users_name" => $user['name'],
									"users_surname" => $user['surname'],
									"timestamp" => $currentCourse -> course['end_date'],
									"lessons_ID" => $currentCourse -> course['id'],
									"lessons_name" => $currentCourse -> course['name'],
									"entity_ID" => $currentCourse -> course['start_date'],
									"entity_name" => $currentCourse -> course['end_date']);
							EfrontEvent::triggerEvent($event);
						}
					}
				}
				echo _FROM.' '.formatTimestamp($fromTimestamp, 'time_nosec').' '._TO.' '.formatTimestamp($toTimestamp, 'time_nosec').'&nbsp;';
			} else {
				header("HTTP/1.0 500");
				echo _ENDDATEMUSTBEBEFORESTARTDATE;
			}
			exit;
		} else if (isset($_GET['delete_schedule']) && $_GET['delete_schedule'] == 0) {
			$currentCourse -> course['start_date'] = '';
			$currentCourse -> course['end_date']   = '';
			$currentCourse -> persist();

			eF_deleteTableData("notifications", "id_type_entity LIKE '%_". (-1) * EfrontEvent::COURSE_PROGRAMMED_START . "_" . $currentCourse -> course['id']. "'");
			eF_deleteTableData("notifications", "id_type_entity LIKE '%_". (-1) * EfrontEvent::COURSE_PROGRAMMED_EXPIRY . "_" . $currentCourse -> course['id']. "'");
		}
	} catch (Exception $e) {
		handleAjaxExceptions($e);
	}
	$days_after_enrollment = array();
	for ($k = 0; $k <= 360; $k++) {
		$days_after_enrollment[$k] = $k;		
		if ($k >= 100) {
			$k+=5;
	    } 
	}
	$smarty -> assign("T_DAYS_AFTER_ENROLLMENT", $days_after_enrollment);
	$smarty -> assign("T_COURSE_LESSONS", EfrontCourse::convertLessonObjectsToArrays($courseLessons));
	//pr($courseLessons);
} else if ($_GET['op'] == 'export_course') {
	if (isset($currentUser -> coreAccess['content']) && $currentUser -> coreAccess['content'] != 'change') {
		eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
	}

	/* Export part */
	$form = new HTML_QuickForm("export_course_form", "post", basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=export_course', "", null, true);
	$form -> addElement('submit', 'submit_export_course', _EXPORT, 'class = "flatButton"');

	try {
		$currentExportedFile = new EfrontFile($currentUser -> user['directory'].'/temp/'.EfrontFile :: encode($currentCourse -> course['name']).'.zip');
		$smarty -> assign("T_EXPORTED_FILE", $currentExportedFile);
	} catch (Exception $e) {}

	if ($form -> isSubmitted() && $form -> validate()) {
		try {
			$file   = $currentCourse -> export();
			$smarty -> assign("T_NEW_EXPORTED_FILE", $file);

			$message      = _COURSEEXPORTEDSUCCESFULLY;
			$message_type = 'success';
		} catch (Exception $e) {
			$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
			$message = $e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
		}
	}

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
	$form -> accept($renderer);
	$smarty -> assign('T_EXPORT_COURSE_FORM', $renderer -> toArray());
} else if ($_GET['op'] == 'import_course') {
	if (isset($currentUser -> coreAccess['content']) && $currentUser -> coreAccess['content'] != 'change') {
		eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
	}

	/* Import part */
	$form = new HTML_QuickForm("import_course_form", "post", basename($_SERVER['PHP_SELF']).'?'.$baseUrl.'&op=import_course', "", null, true);
	$form -> addElement('file', 'file_upload', null, 'class = "inputText"');                    //Lesson file
	$form -> setMaxFileSize(FileSystemTree :: getUploadMaxSize() * 1024);            //getUploadMaxSize returns size in KB
	$form -> addElement('submit', 'submit_import_course', _SUBMIT, 'class = "flatButton"');

	$smarty -> assign("T_MAX_FILESIZE", FileSystemTree :: getUploadMaxSize());

	if ($form -> isSubmitted() && $form -> validate()) {
		try {
			$userTempDir   = $GLOBALS['currentUser'] -> user['directory'].'/temp';
			if (!is_dir($userTempDir)) {                                                                    //If the user's temp directory does not exist, create it
				$userTempDir = EfrontDirectory :: createDirectory($userTempDir, false);
			} else {
				$userTempDir = new EfrontDirectory($userTempDir);
			}

			$filesystem     = new FileSystemTree($userTempDir);
			$uploadedFile   = $filesystem -> uploadFile('file_upload', $userTempDir);
			$currentCourse -> import($uploadedFile);

			$message      = _COURSEIMPORTEDSUCCESFULLY;
			$message_type = 'success';
		} catch (Exception $e) {
			$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
			$message      = _PROBLEMIMPORTINGFILE.': '.$e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
			$message_type = 'failure';
		}
	}

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
	$form -> accept($renderer);
	$smarty -> assign('T_IMPORT_COURSE_FORM', $renderer -> toArray());
}
