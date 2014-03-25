<?php
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

if (isset($currentUser -> coreAccess['progress']) && $currentUser -> coreAccess['progress'] == 'hidden') {
    eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");exit;
}

$loadScripts[] = 'includes/progress';
$loadScripts[] = 'includes/datepicker/datepicker';

if ($_student_) {
    $currentUser -> coreAccess['progress'] = 'view';
    $_GET['edit_user'] = $currentUser -> user['login'];
	$smarty -> assign("T_STUDENT_ROLE", true);
}

if (isset($_GET['edit_user']) && eF_checkParameter($_GET['edit_user'], 'login')) {
	$editedUser = EfrontUserFactory :: factory($_GET['edit_user']);
	$load_editor = true;
    //$lessonUser  = EfrontUserFactory :: factory($_GET['edit_user']);

    //Check conditions
    $currentContent = new EfrontContentTree($currentLesson);
    $seenContent = EfrontStats :: getStudentsSeenContent($currentLesson -> lesson['id'], $editedUser -> user['login']);
    $conditions  = $currentLesson -> getConditions();
    foreach ($iterator = new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST))) as $key => $value) {
        $visitableContentIds[$key] = $key;                                                    //Get the not-test unit ids for this content
    }
    foreach ($iterator = new EfrontTestsFilterIterator(new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST)))) as $key => $value) {
        $testsIds[$key] = $key;                                                    //Get the not-test unit ids for this content
    }

    $times = new EfrontTimes();
    list($conditionsStatus, $lessonPassed) = EfrontStats :: checkConditions($seenContent[$currentLesson -> lesson['id']][$editedUser -> user['login']], $conditions, $visitableContentIds, $testsIds, $usersTimesInLessonContent[$user] = EfrontLesson::getUserActiveTimeInLesson($editedUser -> user['login'], $currentLesson -> lesson['id']));
    $smarty -> assign("T_CONDITIONS", $conditions);
    $smarty -> assign("T_CONDITIONS_STATUS", $conditionsStatus);
    foreach ($iterator = new EfrontAttributeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree)), array('id', 'name')) as $key => $value) {
        $key == 'id' ? $ids[] = $value : $names[] = $value;
    }
    $smarty -> assign("T_TREE_NAMES", array_combine($ids, $names));

    $form = new HTML_QuickForm("edit_user_complete_lesson_form", "post", basename($_SERVER['PHP_SELF']).'?ctg=progress&edit_user='.$editedUser -> user['login'], "", null, true);
    $form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');                   //Register this rule for checking user input with our function, eF_checkParameter

    $form -> addElement('advcheckbox', 'completed', _COMPLETED, null, 'class = "inputCheckbox"');            //Whether the user has completed the lesson
    $form -> addElement('text', 'score', _SCORE, 'class = "inputText"');                                                        //The user lesson score
    $form -> addRule('score', _THEFIELD.' "'._SCORE.'" '._MUSTBENUMERIC, 'numeric', null, 'client');                            //The score must be numeric
    $form -> addRule('score', _RATEMUSTBEBETWEEN0100, 'callback', create_function('$a', 'return ($a >= 0 && $a <= 100);'));    //The score must be between 0 and 100
    $form -> addElement('textarea', 'comments', _COMMENTS, 'class = "inputContentTextarea simpleEditor" style = "width:100%;height:5em;"');      //Comments on student's performance

    $userStats = $editedUser -> getUserStatusInLessons($currentLesson);
    $userStats = $userStats[$currentLesson -> lesson['id']] -> lesson;

    $form -> setDefaults(array("completed" => $userStats['completed'],
                               "score"     => $userStats['score'],
                               "comments"  => $userStats['comments'] ? $userStats['comments'] : ''));

    if (isset($currentUser -> coreAccess['progress']) && $currentUser -> coreAccess['progress'] != 'change') {
        $form -> freeze();
    } else {
        $form -> addElement('submit', 'submit_lesson_complete', _SUBMIT, 'class = "flatButton"');       //The submit button
        if ($form -> isSubmitted() && $form -> validate()) {
            if ($form -> exportValue('completed')) {
                $lessonUser  = EfrontUserFactory :: factory($editedUser -> user['login'], false, 'student');
                $lessonUser -> completeLesson($currentLesson -> lesson['id'], $form -> exportValue('score'), $form -> exportValue('comments'));
            } else {
                eF_updateTableData("users_to_lessons", array('completed' => 0, 'score' => 0, 'to_timestamp' => null), "users_LOGIN = '".$editedUser -> user['login']."' and lessons_ID=".$currentLesson -> lesson['id']);
//		        $cacheKey = "user_lesson_status:lesson:".$currentLesson -> lesson['id']."user:".$editedUser -> user['login'];
//		        Cache::resetCache($cacheKey);
            }

            eF_redirect(basename($_SERVER['PHP_SELF']).'?ctg=progress&message='.urlencode(_STUDENTSTATUSCHANGED).'&message_type=success');
        }
    }

    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

    $form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
    $form -> setRequiredNote(_REQUIREDNOTE);
    $form -> accept($renderer);

    $smarty -> assign('T_COMPLETE_LESSON_FORM', $renderer -> toArray());
    $doneTests = EfrontStats :: getDoneTestsPerUser($_GET['edit_user'], false, $currentLesson -> lesson['id']);

    $result = EfrontStats :: getStudentsDoneTests($currentLesson -> lesson['id'], $_GET['edit_user']);
    foreach ($result[$_GET['edit_user']] as $key => $value) {
        if ($value['scorm']) {
            $scormDoneTests[$key] = $value;
        }
    }

    $testNames = eF_getTableDataFlat("tests t, content c", "t.id, c.name", "c.id=t.content_ID and t.active=1 and c.ctg_type='tests' and c.lessons_ID=".$currentLesson -> lesson['id']);
    $testNames = array_combine($testNames['id'], $testNames['name']);


    foreach($doneTests[$_GET['edit_user']] as $key => $value) {
        if (in_array($key, array_keys($testNames))) {
            $userStats['done_tests'][$key] = array('name'		  	=> $testNames[$key], 
            									   'score'		  	=> $value['average_score'], 
            									   'last_test_id' 	=> $value['last_test_id'], 
            									   'active_test_id' => $value['active_test_id'], 
										           'last_score'   	=> $value['scores'][$value['last_test_id']], 
            									   'active_score' 	=> $value['active_score'], 
            									   'times_done'   	=> $value['times_done'], 
            									   'content_ID'   	=> $value[$value['last_test_id']]['content_ID']);
        }
    }
    foreach($scormDoneTests as $key => $value) {
        $userStats['scorm_done_tests'][$key] = array('name' => $value['name'], 'score' => $value['score'], 'content_ID' => $key);
    }
    unset($userStats['done_tests']['average_score']);
    $smarty -> assign("T_USER_LESSONS_INFO", $userStats);

    $notDoneTests = array_diff(array_keys($testNames), array_keys($doneTests[$_GET['edit_user']]));
    $smarty -> assign("T_PENDING_TESTS", $notDoneTests);

    if ($GLOBALS['configuration']['time_reports']) {
    	$userTime = EfrontTimes::formatTimeForReporting(EfrontLesson::getUserActiveTimeInLesson($editedUser->user['login'], $currentLesson->lesson['id']));
    } else {
    	$timeReport = new EfrontTimes();
    	$userTime = $timeReport -> getUserSessionTimeInLesson($editedUser -> user['login'], $currentLesson -> lesson['id']);
    	$userTime = $timeReport -> formatTimeForReporting($userTime);
    }
    $smarty -> assign("T_USER_TIME", $userTime);

    $userProjects = EfrontStats :: getStudentsAssignedProjects($currentLesson -> lesson['id'], $editedUser -> user['login']);
    $smarty -> assign("T_USER_PROJECTS", $userProjects[$editedUser -> user['login']]);

    if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
        /** Get evaluations **/
        $evaluations = eF_getTableData("users JOIN module_hcd_events ON login = author","login, name, surname,module_hcd_events.*","module_hcd_events.users_login = '".$editedUser -> user['login']."' AND event_code = 10");
        $smarty -> assign('T_EVALUATIONS', $evaluations);
    } #cpp#endif

	$moduleFieldsets = array();
	foreach ($currentUser -> getModules() as $module) {
		if ($moduleFieldset = $module -> getFieldsetSmartyTpl('lesson_progress')) {
			$moduleFieldsets[] = $moduleFieldset;
		}
	}
	$smarty -> assign("T_MODULE_FIELDSETS", $moduleFieldsets);

}

try {
	if (isset($_GET['ajax']) && isset($_GET['reset_user'])) {
		if (isset($currentUser -> coreAccess['progress']) && $currentUser -> coreAccess['progress'] != 'change') {
    		exit;
		}
		$user = EfrontUserFactory :: factory($_GET['reset_user']);
		$user -> resetProgressInLesson($currentLesson);
		exit;
	}
	if (isset($_GET['ajax']) && isset($_GET['change_user'])) {
		if (isset($currentUser -> coreAccess['progress']) && $currentUser -> coreAccess['progress'] != 'change') {
    		exit;
		}
		if (empty($_GET['date'])){
			$timestamp = false; // it is for changing only completion date
		} else if (eF_checkParameter($_GET['date'], 'date')) {
			$date = explode ('-', $_GET['date']);
			$timestamp = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
		} else {
			$timestamp = time();
		}
		$user = EfrontUserFactory :: factory($_GET['change_user']);		
		$user -> changeProgressInLesson($currentLesson, $timestamp);
		exit;
	}
	
	if (isset($_GET['complete']) && isset($_GET['ajax'])) {
		if (isset($currentUser -> coreAccess['progress']) && $currentUser -> coreAccess['progress'] != 'change') {
    		exit;
		}
	    $completeEntities = json_decode($_GET['complete']);
	    if (!empty($completeEntities)) {
		    if (eF_checkParameter($_GET['date'], 'date')) {
				$date = explode ('-', $_GET['date']);
				$timestamp = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
			}
	    	$list = '"'.implode('","', $completeEntities).'"';
	    	$info = eF_getTableData("users_to_lessons", "users_LOGIN,lessons_ID,completed,score,to_timestamp,comments", "users_LOGIN IN (".$list.") and lessons_ID = ".$currentLesson -> lesson['id']);					
			foreach ($info as $value) {
				if ($value['completed'] == 0) {
					$user = EfrontUserFactory :: factory($value['users_LOGIN']);		
					$user -> completeLesson($currentLesson -> lesson['id'], 100, '', $timestamp);
				}
			}
	    }
	    exit;
	}
	if (isset($_GET['uncomplete']) && isset($_GET['ajax'])) {
		if (isset($currentUser -> coreAccess['progress']) && $currentUser -> coreAccess['progress'] != 'change') {
    		exit;
		}
		$uncompleteEntities = json_decode($_GET['uncomplete']);
	    if (!empty($uncompleteEntities)) {
	    	$list = '"'.implode('","', $uncompleteEntities).'"';
	    	$info = eF_getTableData("users_to_lessons", "users_LOGIN,lessons_ID,completed,score,to_timestamp,comments", "users_LOGIN IN (".$list.") and lessons_ID = ".$currentLesson -> lesson['id']);					
			foreach ($info as $value) {
				if ($value['completed'] == 1) {
					eF_updateTableData("users_to_lessons", array("completed" => 0, "to_timestamp" => null,"score" => 0,"comments" => "", "issued_certificate" => ""), "users_LOGIN='".$value['users_LOGIN']."' and lessons_ID = ".$value['lessons_ID']);
				}
			}
	    }
	    exit;
	}
	
	if (isset($_GET['ajax']) && $_GET['ajax'] == 'usersTable') {
		$constraints   = createConstraintsFromSortedTable() + array('archive' => false, 'return_objects' => false);
		foreach (EfrontLessonUser :: getLessonsRoles() as $key => $value) {
			$value != 'student' OR $studentRoles[] = $key;
		}
		
		if ($_SESSION['s_current_branch']) {
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
		$constraints['condition'] = "ul.user_type in ('".implode("','", $studentRoles)."')";
		
		$users	 	   = $currentLesson -> getLessonStatusForUsers($constraints);
		$totalEntries  = $currentLesson -> countLessonUsers($constraints);
		$dataSource    = $users;	
		
		$smarty -> assign("T_TABLE_SIZE", $totalEntries);
	}
	$tableName     = $_GET['ajax'];
	$alreadySorted = true;
	include("sorted_table.php");
} catch (Exception $e) {
	handleAjaxExceptions($e);
}

