<?php
//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

if (!eF_local_shouldDisplaySelectBox()) {
	$smarty -> assign("T_SINGLE_USER", true);							//assign this variable, so that select user panel is not available
	$_GET['sel_user'] = $currentUser -> user['login'];
}

if (isset($_GET['sel_user'])) {
	if ($currentUser -> user['user_type'] != 'administrator' && $isSupervisor) {
		if ($currentUser -> aspects['hcd'] -> supervisesEmployee($_GET['sel_user'])) {
			$validUsers[] = $_GET['sel_user'];
			$supervisesUser = 1;
		}
	}

	if (eF_local_canAccessUser()) {
		$infoUser = EfrontUserFactory :: factory($_GET['sel_user']);
	} else {
		eF_redirect(basename($_SERVER['PHP_SELF']).'?ctg=statistics&option=user&message='.urlencode(_USERISNOTVALIDORYOUCANNOTSEEUSER.": ".$_GET['sel_user']));
		exit;
	}

	if ($isSupervisor || $currentUser -> user['user_type'] == 'administrator') {
		$smarty -> assign("T_EDIT_USER_LINK", array(array('text' => _EDITUSER, 'image' => "16x16/edit.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$_GET['sel_user'])));
	}

	$directionsTree 	 = new EfrontDirectionsTree();
	$directionsTreePaths = $directionsTree -> toPathString();

	$smarty -> assign("T_USER_LOGIN", $infoUser -> user['login']);
	$smarty -> assign("T_REPORTS_USER", $infoUser);
	if ($_GET['specific_lesson_info'] && $_GET['lesson']) {
		$lessons = $infoUser -> getUserStatusInLessons($_GET['lesson']);
		$smarty -> assign ("T_USER_STATUS_IN_LESSON", $lessons[$_GET['lesson']]);

		$status	 	= EfrontStats :: getUsersLessonStatus($_GET['lesson'], $infoUser -> user['login']);
		$doneTests  = EfrontStats :: getStudentsDoneTests($_GET['lesson'], $infoUser -> user['login']);
		$feedbacks = eF_getTableDataFlat("tests t, content c", "t.id, t.content_ID", "c.id=t.content_ID and c.ctg_type='feedback'");
		foreach ($doneTests[$infoUser -> user['login']] as $contentId => $test) {
			unset($pendingTests[$test['tests_ID']]);				//remove done tests
			if (in_array($contentId, $feedbacks['content_ID'])) {
				unset($doneTests[$infoUser -> user['login']][$contentId]);				//remove done tests
			}
			
		}

		$smarty -> assign("T_USER_PENDING_TESTS", $pendingTests);
		$smarty -> assign("T_USER_DONE_TESTS", $doneTests[$infoUser -> user['login']]);
		$smarty -> assign("T_USER_STATUS", $status[$_GET['lesson']][$infoUser -> user['login']]);
	} elseif ($_GET['specific_course_info'] && $_GET['course']) {
		$lessons = $infoUser -> getUserStatusInCourseLessons(new EfrontCourse($_GET['course']), true);
		$smarty -> assign ("T_USER_STATUS_IN_COURSE_LESSONS", $lessons);
	} else {


		try {
			$roles = EfrontUser :: getRoles(true);
			$smarty -> assign("T_ROLES_ARRAY", $roles);

			$rolesBasic = EfrontLessonUser :: getLessonsRoles();
			$smarty -> assign("T_BASIC_ROLES_ARRAY", $rolesBasic);

			if (isset($_GET['ajax']) && $_GET['ajax'] == 'lessonsTable') {
				$tableName   = $_GET['ajax'];
				$smarty -> assign("T_DATASOURCE_COLUMNS", array('name', 'location', 'user_type', 'num_lessons', 'status', 'completed', 'score', 'operations', 'sort_by_column' => 4));
				$smarty -> assign("T_DATASOURCE_OPERATIONS", array('progress'));
				$lessons 	 = $infoUser -> getUserStatusInIndependentLessons();
				if ($currentUser -> user['user_type'] != 'administrator') {
					$lessons = array_intersect_key($lessons, $userLessons);
				}
				$lessons 	 = EfrontLesson :: convertLessonObjectsToArrays($lessons);
				foreach ($lessons as $key => $value) {
					$lessons[$key]['name'] = $directionsTreePaths[$value['directions_ID']].'&nbsp;&rarr;&nbsp;'.$value['name'];
				}				
				$dataSource  = $lessons;
			}
			if (isset($_GET['ajax']) && $_GET['ajax'] == 'courseLessonsTable' && eF_checkParameter($_GET['courseLessonsTable_source'], 'id')) {
				$tableName   = $_GET['ajax'];
				$smarty -> assign("T_DATASOURCE_COLUMNS", array('name', 'location', 'user_type', 'num_lessons', 'status', 'completed', 'score', 'operations', 'sort_by_column' => 4));
				$smarty -> assign("T_DATASOURCE_OPERATIONS", array('progress'));
				$lessons 	 = $infoUser -> getUserStatusInCourseLessons(new EfrontCourse($_GET['courseLessonsTable_source']), true);
				$lessons 	 = EfrontLesson :: convertLessonObjectsToArrays($lessons);
				$dataSource  = $lessons;
				if (!$_GET['sort']) {
					$_GET['sort'] = 'eliminate';		//Assign a default sort that does not exist, thus eliminating default sorting by name. This happens because $dataSource here is alread pre-sorted by course succession
				}
			}
			$smarty -> assign("T_DATASOURCE_COLUMNS", array('name', 'location', 'user_type', 'num_lessons', 'status', 'completed', 'score', 'operations', 'sort_by_column' => 4));
			$smarty -> assign("T_DATASOURCE_OPERATIONS", array('progress'));
			$smarty -> assign("T_DATASOURCE_SORT_BY", 0);
			if ($_GET['ajax'] == 'coursesTable' || $_GET['ajax'] == 'instancesTable') {
				$tableName   = $_GET['ajax'];
				if (isset($_GET['ajax']) && $_GET['ajax'] == 'coursesTable') {
					//$constraints = array('archive' => false, 'active' => true, 'instance' => false) + createConstraintsFromSortedTable();
					$constraints = array('archive' => false, 'active' => true, 'instance' => false);

					$constraints['required_fields'] = array('has_instances', 'location', 'user_type', 'completed', 'score', 'has_course', 'num_lessons');
					$constraints['return_objects']  = false;
					$courses	 = $infoUser -> getUserCoursesAggregatingResults($constraints);

					if ($currentUser -> user['user_type'] != 'administrator' && !$supervisesUser) {
						$userCourses = $currentUser -> getUserCoursesAggregatingResults($constraints);
						$courses = array_intersect_key($courses, $userCourses);
					}

				}
				if (isset($_GET['ajax']) && $_GET['ajax'] == 'instancesTable' && eF_checkParameter($_GET['instancesTable_source'], 'id')) {
					//$constraints = array('archive' => false, 'active' => true, 'instance' => $_GET['instancesTable_source']) + createConstraintsFromSortedTable();
					$constraints = array('archive' => false, 'active' => true, 'instance' => $_GET['instancesTable_source']);

					$constraints['required_fields'] = array('num_lessons', 'location');
					$constraints['return_objects']  = false;
					$courses	 = $infoUser -> getUserCourses($constraints);
					if ($currentUser -> user['user_type'] != 'administrator' && !$supervisesUser) {
						$userCourses = $currentUser -> getUserCourses($constraints);
						$courses = array_intersect_key($courses, $userCourses);
					}

				}

				$dataSource  = $courses;
				$smarty -> assign("T_SHOW_COURSE_LESSONS", true);
			}

			include("sorted_table.php");

		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}

		try {
			$userInfo = array();
			$userInfo['general']	   = $infoUser -> getInformation();
			$userInfo['communication'] = EfrontStats :: getUserCommunicationInfo($infoUser);
			
			if (sizeof($userInfo['communication']['forum_messages'])) {
				$last = current($userInfo['communication']['forum_messages']);
				$userInfo['communication']['forum_last_message'] = formatTimestamp($last['timestamp'], 'time');
			} else {
				$userInfo['communication']['forum_last_message'] = "";
			}

			$userInfo['usage'] = EfrontStats :: getUserUsageInfo($infoUser);

			$userInfo['usage']['meanDuration'] = EfrontTimes::formatTimeForReporting($userInfo['usage']['mean_duration']*60);
			$userInfo['usage']['monthmeanDuration'] = EfrontTimes::formatTimeForReporting($userInfo['usage']['month_mean_duration']*60);
			$userInfo['usage']['weekmeanDuration'] = EfrontTimes::formatTimeForReporting($userInfo['usage']['week_mean_duration']*60);

	  	try {
				$avatar = new EfrontFile($userInfo['general']['avatar']);
				$avatar['id'] != -1 ? $smarty -> assign ("T_AVATAR", $avatar['id']) : $smarty -> assign ("T_AVATAR", $avatar['path']);
			} catch (Exception $e) {
				$smarty -> assign ("T_AVATAR", G_SYSTEMAVATARSPATH."unknown_small.png");
			}

			if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
				// Check if current user supervises the user for whom we show statistics - if so, the traffic tab should appear
				if ($currentUser -> getType() != "administrator") {
					$currentEmployee = $currentUser -> aspects['hcd'];
					if ($supervisesUser) {
						$userInfo['general']['supervised_by_user'] = 1;
					}
				}
			} #cpp#endif

			$smarty -> assign("T_USER_INFO", $userInfo);

			if ($infoUser -> user['user_type'] != 'administrator') {
				$userLessons  = $infoUser -> getUserLessons();
			} else {
				$userLessons  = array();
			}
			//$allUserTimes = EfrontStats :: getUsersTimeAll(false, false, array_keys($userLessons));

			$actions = array('login'	  => _LOGIN,
							 'logout'	  => _LOGOUT,
							 'lesson'	  => _ACCESSEDLESSON,
							 'content'	  => _ACCESSEDCONTENT,
							 'tests'	  => _ACCESSEDTEST,
							 'test_begin' => _BEGUNTEST,
							 'lastmove'   => _NAVIGATEDSYSTEM);
			$smarty -> assign("T_ACTIONS", $actions);

			// Predefined periods
			$periods = array();
			$today   = time();

			$week_back	   = getdate($today - 7*24*3600);
			$week_back	   = $week_back["mon"] . "," . $week_back["mday"] . "," . $week_back["year"];

			$month_back	  = mktime(date("H"), date("i"), 0, date("m")-1, date("d"), date("Y"));
			$month_back	  = getdate($month_back);
			$month_back	   = $month_back["mon"] . "," . $month_back["mday"] . "," . $month_back["year"];

			$day_back		= getdate($today - 24*3600);
			$day_back	   = $day_back["mon"] . "," . $day_back["mday"] . "," . $day_back["year"];

			$two_days_back   = getdate($today - 48*3600);
			$two_days_back	   = $two_days_back["mon"] . "," . $two_days_back["mday"] . "," . $two_days_back["year"];

			$today = getdate(time());
			$today = $today["month"] . "," . $today["mday"] . "," . $today["year"];

			$periods[] = array("name" => _PREVIOUSWEEK, 		"value" => $week_back . "|" . $today);
			$periods[] = array("name" => _TODAY, 				"value" => $day_back . "|" . $today);
			$periods[] = array("name" => _YESTERDAY, 			"value" => $two_days_back . "|" . $day_back);
			$periods[] = array("name" => _PREVIOUSMONTH, 		"value" => $month_back . "|" . $today);
			$smarty -> assign('T_PREDEFINED_PERIODS', $periods);
			try {
				if (isset($_GET['ajax']) && $_GET['ajax'] == 'graph_access') {
					$result = eF_getTableData("logs", "timestamp", "action = 'login' and users_LOGIN = '".$infoUser -> user['login']."' order by timestamp");
					$firstDate = eF_getTableData("logs", "timestamp", "", "", "", 1);					
					//Assign the number of accesses to each week day
					foreach ($result as $value) {
						$cnt = 0;
						for ($i = $firstDate[0]['timestamp']; $i <= time(); $i += 86400) {
							$labels[$cnt] = $i;
							isset($count[$cnt]) OR $count[$cnt] = 0;
							if ($i <= $value['timestamp'] && $value['timestamp'] < $i + 86400) {
								$count[$cnt]++;
							}
							$cnt++;
						}
					}

					$graph = new EfrontGraph();
					$graph -> type = 'line';
					for ($i = 0; $i < sizeof($labels); $i++) {
						$graph -> data[]    = array($i, $count[$i]);
						$graph -> xLabels[] = array($i, '<span style = "white-space:nowrap">'.formatTimestamp($labels[$i]).'</span>');
					}

					$graph -> xTitle = _DAY;
					$graph -> yTitle = _LOGINS;
					$graph -> title  = _LOGINSPERDAY;

					echo json_encode($graph);
					exit;

				} elseif (isset($_GET['ajax']) && $_GET['ajax'] == 'graph_lesson_access') {
					$lesson		 = new EfrontLesson($_GET['entity']);
					$timesReport = new EfrontTimes(array($from, $to));

					$cnt=0;
					$result = $timesReport -> getUserSessionTimeInSingleLessonPerDay($infoUser -> user['login'], $lesson -> lesson['id']);
					foreach ($result as $key => $value) {
						$labels[$cnt]   = $key;
						$count[$cnt++]  = ceil($value/60);
					}

					$graph = new EfrontGraph();
					$graph -> type = 'line';
					for ($i = 0; $i < sizeof($labels); $i++) {
						$graph -> data[]    = array($i, $count[$i]);
						$graph -> xLabels[] = array($i, '<span style = "white-space:nowrap">'.formatTimestamp($labels[$i]).'</span>');
					}

					$graph -> xTitle = _DAY;
					$graph -> yTitle = _MINUTES;
					$graph -> title  = _MINUTESPERDAY;

					echo json_encode($graph);
					exit;
				}
			} catch (Exception $e) {
				handleAjaxExceptions($e);
			}

			//pr($infoUser -> getUserStatusInLessons());
			$timesReport = new EfrontTimes();
			if ($GLOBALS['configuration']['time_reports']) {
				if ($infoUser instanceof EfrontLessonUser) {
					$userTraffic = $infoUser->getLessonsActiveTimeForUser();
				}
			} else {
				$result = $timesReport -> getUserSessionTimeInLessons($infoUser -> user['login']);
				foreach ($result as $value) {
					$userTraffic[$value['lessons_ID']] = $value['time'];
				}
			}

			foreach ($userLessons as $id => $lesson) {
				$traffic['lessons'][$id] = $timesReport -> formatTimeForReporting($userTraffic[$id]);
				$traffic['lessons'][$id]['name']   = $lesson -> lesson['name'];
				$traffic['lessons'][$id]['active'] = $lesson -> lesson['active'];
			}

			$result = eF_getTableData("logs", "count(*)", "action = 'login' and users_LOGIN='".$infoUser -> user['login']."' order by timestamp");
			$traffic['total_logins'] = $result[0]['count(*)'];
			$result = eF_getTableData("users_to_lessons", "lessons_ID, completed, to_timestamp", "archive=0 and users_LOGIN='".$infoUser -> user['login']."'");
			$completionData = array();
			foreach ($result as $value) {
				$completionData[$value['lessons_ID']] = $value;
			}

			//$completionData = array_combine($result["lessons_ID"], $result['completed']);
			foreach ($traffic['lessons'] as $lessonId => $value) {
				$traffic['lessons'][$lessonId]['completed'] = $completionData[$lessonId]['completed'];
				$traffic['lessons'][$lessonId]['to_timestamp'] = $completionData[$lessonId]['to_timestamp'];
			}

			//pr($infoUser -> getUserLessons());pr($traffic);exit;
			$smarty -> assign("T_USER_TRAFFIC", $traffic);
		} catch (Exception $e) {
			handleNormalFlowExceptions($e);
		}
	}
}


if (isset($_GET['excel']) && $_GET['excel'] == 'user') {
	require_once 'Spreadsheet/Excel/Writer.php';
	$workBook = new Spreadsheet_Excel_Writer();
	$workBook -> setTempDir(G_UPLOADPATH);
	$workBook -> setVersion(8);

	$formatExcelHeaders = & $workBook -> addFormat(array('Size' => 14, 'Bold' => 1, 'HAlign' => 'left'));
	$headerFormat	   = & $workBook -> addFormat(array('border' => 0, 'bold' => '1', 'size'	=> '11', 'color' => 'black', 'fgcolor' => 22, 'align' => 'center'));
	$formatContent	  = & $workBook -> addFormat(array('HAlign' => 'left', 'Valign' => 'top', 'TextWrap' => 1));
	$headerBigFormat	= & $workBook -> addFormat(array('HAlign' => 'center', 'FgColor' => 22, 'Size' => 16, 'Bold' => 1));
	$titleCenterFormat  = & $workBook -> addFormat(array('HAlign' => 'center', 'Size' => 11, 'Bold' => 1));
	$titleLeftFormat	= & $workBook -> addFormat(array('HAlign' => 'left', 'Size' => 11, 'Bold' => 1));
	$fieldLeftFormat	= & $workBook -> addFormat(array('HAlign' => 'left', 'Size' => 10));
	$fieldRightFormat   = & $workBook -> addFormat(array('HAlign' => 'right', 'Size' => 10));
	$fieldCenterFormat  = & $workBook -> addFormat(array('HAlign' => 'center', 'Size' => 10));

	//first tab
	$workSheet = & $workBook -> addWorksheet("(".$infoUser -> user['login'].") General Statistics");
	$workSheet -> setInputEncoding('utf-8');

	$workSheet -> setColumn(0, 0, 5);

	//basic info
	$workSheet -> write(1, 1, _BASICINFO, $headerFormat);
	$workSheet -> mergeCells(1, 1, 1, 2);
	$workSheet -> setColumn(1, 2, 35);

	$roles = EfrontUser :: getRoles(true);
	$row = 2;
	$workSheet -> write($row, 1, _LOGIN, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $userInfo['general']['login'], $fieldRightFormat);
	$workSheet -> write($row, 1, _USERNAME, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $userInfo['general']['fullname'], $fieldRightFormat);
	$workSheet -> write($row, 1, _USERTYPE, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $roles[$userInfo['general']['user_type']], $fieldRightFormat);
	$workSheet -> write($row, 1, _USERROLE, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $roles[$userInfo['general']['user_types_ID']], $fieldRightFormat);
	if ($GLOBALS['configuration']['lesson_enroll']) {
		$workSheet -> write($row, 1, _LESSONS, $fieldLeftFormat);
		$workSheet -> write($row++, 2, $userInfo['general']['total_lessons'], $fieldRightFormat);
	}
	$workSheet -> write($row, 1, _COURSES, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $userInfo['general']['total_courses'], $fieldRightFormat);
	$workSheet -> write($row, 1, _TOTALLOGINTIME, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $userInfo['general']['total_login_time']['hours']."h ". $userInfo['general']['total_login_time']['minutes']."' ".$userInfo['general']['total_login_time']['seconds']."'' ", $fieldRightFormat);
	$workSheet -> write($row, 1, _LANGUAGE, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $userInfo['general']['language'], $fieldRightFormat);
	$workSheet -> write($row, 1, _ACTIVE, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $userInfo['general']['active_str'], $fieldRightFormat);
	$workSheet -> write($row, 1, _JOINED, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $userInfo['general']['joined_str'], $fieldRightFormat);

	//communication info
	$workSheet -> write($row, 1, _USERCOMMUNICATIONINFO, $headerFormat);
	$workSheet -> mergeCells($row, 1, $row++, 2);
	//$workSheet -> setColumn(10, 10, 35);

	if (EfrontUser::isOptionVisible('forum')) {
		$workSheet -> write($row, 1, _FORUMPOSTS, $fieldLeftFormat);
		$workSheet -> write($row++, 2, sizeof($userInfo['communication']['forum_messages']), $fieldRightFormat);
		$workSheet -> write($row, 1, _FORUMLASTMESSAGE, $fieldLeftFormat);
		$workSheet -> write($row++, 2, $userInfo['communication']['forum_last_message'], $fieldRightFormat);
	}
	if (EfrontUser::isOptionVisible('messages')) {
		$workSheet -> write($row, 1, _PERSONALMESSAGES, $fieldLeftFormat);
		$workSheet -> write($row++, 2, sizeof($userInfo['communication']['personal_messages']), $fieldRightFormat);
		$workSheet -> write($row, 1, _MESSAGESFOLDERS, $fieldLeftFormat);
		$workSheet -> write($row++, 2, sizeof($userInfo['communication']['personal_folders']), $fieldRightFormat);
	}
	$workSheet -> write($row, 1, _FILES, $fieldLeftFormat);
	$workSheet -> write($row++, 2, sizeof($userInfo['communication']['files']), $fieldRightFormat);
	$workSheet -> write($row, 1, _FOLDERS, $fieldLeftFormat);
	$workSheet -> write($row++, 2, sizeof($userInfo['communication']['personal_folders']), $fieldRightFormat);
	$workSheet -> write($row, 1, _TOTALSIZE, $fieldLeftFormat);
	$workSheet -> write($row++, 2, sizeof($userInfo['communication']['total_size'])._KB, $fieldRightFormat);

	if (EfrontUser::isOptionVisible('comments')) {
		$workSheet -> write($row, 1, _COMMENTS, $fieldLeftFormat);
		$workSheet -> write($row++, 2, sizeof($userInfo['communication']['comments']), $fieldRightFormat);
	}

	//usage info
	$workSheet -> write($row, 1, _USERUSAGEINFO, $headerFormat);
	$workSheet -> mergeCells($row, 1, $row++, 2);
	//$workSheet -> setColumn(21, 21, 35);
	$weekMeanDuration  = EfrontTimes::formatTimeForReporting($userInfo['usage']['week_mean_duration']*60);
	$monthMeanDuration = EfrontTimes::formatTimeForReporting($userInfo['usage']['month_mean_duration']*60);
	$meanDuration 	   = EfrontTimes::formatTimeForReporting($userInfo['usage']['mean_duration']*60);

	$workSheet -> write($row, 1, _LASTLOGIN, $fieldLeftFormat);
	$workSheet -> write($row++, 2, formatTimestamp($userInfo['usage']['last_login']['timestamp'], 'time'), $fieldRightFormat);
	$workSheet -> write($row, 1, _TOTALLOGINS, $fieldLeftFormat);
	$workSheet -> write($row++, 2, sizeof($userInfo['usage']['logins']), $fieldRightFormat);
	$workSheet -> write($row, 1, _MONTHLOGINS, $fieldLeftFormat);
	$workSheet -> write($row++, 2, sizeof($userInfo['usage']['month_logins']), $fieldRightFormat);
	$workSheet -> write($row, 1, _WEEKLOGINS, $fieldLeftFormat);
	$workSheet -> write($row++, 2, sizeof($userInfo['usage']['week_logins']), $fieldRightFormat);
	$workSheet -> write($row, 1, _MEANDURATION, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $meanDuration['time_string'], $fieldRightFormat);
	$workSheet -> write($row, 1, _MONTHMEANDURATION, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $monthMeanDuration['time_string'], $fieldRightFormat);
	$workSheet -> write($row, 1, _WEEKMEANDURATION, $fieldLeftFormat);
	$workSheet -> write($row++, 2, $weekMeanDuration['time_string'], $fieldRightFormat);


	$row = 1;
	if ($infoUser -> user['user_type'] != 'administrator') {
		//course users info
		$constraints = array('instance' => false, 'archive' => false, 'active' => true);
		$constraints['required_fields'] = array('has_instances', 'location', 'user_type', 'completed', 'score', 'has_course', 'num_lessons', 'to_timestamp');
		$constraints['return_objects']  = false;
		$userCourses = $infoUser -> getUserCoursesAggregatingResults($constraints);

		if (sizeof($userCourses) > 0) {
			$workSheet -> write($row, 4, _COURSESINFO, $headerFormat);
			$workSheet -> mergeCells($row, 4, $row, 11);
			$workSheet -> setColumn($row, 10, 15);

			$row++;
			$workSheet -> write($row, 4, _COURSE, $titleLeftFormat);
			//$workSheet -> write($row, 5, _LESSONS, $titleCenterFormat);
			$workSheet -> write($row, 6, _SCORE, $titleCenterFormat);
			$workSheet -> write($row, 7, _COMPLETED, $titleCenterFormat);
			$workSheet -> write($row, 8, _COMPLETEDON, $titleCenterFormat);
			foreach ($userCourses as $id => $course) {
				//$course = $course -> course;
				$row++;
				$workSheet -> write($row, 4, $course['name'], $fieldLeftFormat);
				//$workSheet -> write($row, 5, $course['lessons'], $fieldCenterFormat);
				$workSheet -> write($row, 6, formatScore($course['score'])."%", $fieldCenterFormat);
				$workSheet -> write($row, 7, $course['completed'] ? _YES : _NO, $fieldCenterFormat);
				$workSheet -> write($row, 8, formatTimestamp($course['to_timestamp']), $fieldCenterFormat);
			}
		}
		$row++;
		//lesson info
		$userLessons = $infoUser -> getUserStatusInIndependentLessons();
		if (sizeof($userLessons) > 0 && $GLOBALS['configuration']['lesson_enroll']) {
			$workSheet -> write($row, 4, _LESSONSINFO, $headerFormat);
			$workSheet -> mergeCells($row, 4, $row, 11);
			$workSheet -> setColumn(4, 10, 15);

			$row++;
			$workSheet -> write($row, 4, _LESSON, $titleLeftFormat);
			if ($GLOBALS['configuration']['time_reports']) {
				$workSheet -> write($row, 5, _ACTIVETIMEINLESSON, $titleCenterFormat);
			} else {
				$workSheet -> write($row, 5, _TIMEINLESSON, $titleCenterFormat);
			}
			$workSheet -> write($row, 6, _OVERALL, $titleCenterFormat);
			if (EfrontUser::isOptionVisible('tests')) {
				$workSheet -> write($row, 7, _TESTS, $titleCenterFormat);
			}
			if(EfrontUser::isOptionVisible('projects')) {
				$workSheet -> write($row, 8, _PROJECTS, $titleCenterFormat);
			}
			$workSheet -> write($row, 9, _COMPLETED, $titleCenterFormat);
			$workSheet -> write($row, 10, _COMPLETEDON, $titleCenterFormat);
			$workSheet -> write($row++, 11, _GRADE, $titleCenterFormat);

			foreach ($userLessons as $id => $lesson) {
				$lesson = $lesson -> lesson;
				if ($lesson['active'] && !$lesson['course_only']) {
					$workSheet -> write($row, 4, str_replace("&nbsp;&rarr;&nbsp;", " -> ", $lesson['name']), $fieldLeftFormat);
					if ($GLOBALS['configuration']['time_reports']) {
						$workSheet -> write($row, 5, $lesson['active_time_in_lesson']['time_string'], $fieldCenterFormat);
					} else {
						$workSheet -> write($row, 5, $lesson['time_in_lesson']['time_string'], $fieldCenterFormat);
					}
					$workSheet -> write($row, 6, formatScore($lesson['overall_progress']['percentage'])."%", $fieldCenterFormat);
					if(EfrontUser::isOptionVisible('tests')) {
						$workSheet -> write($row, 7, formatScore($lesson['test_status']['mean_score'])."%", $fieldCenterFormat);
					}
					if(EfrontUser::isOptionVisible('projects')) {
						$workSheet -> write($row, 8, formatScore($lesson['project_status']['mean_score'])."%", $fieldCenterFormat);
					}
					$workSheet -> write($row, 9, $lesson['completed'] ? _YES : _NO, $fieldCenterFormat);
					$workSheet -> write($row, 10, formatTimestamp($lesson['timestamp_completed']), $fieldCenterFormat);
					$workSheet -> write($row, 11, $lesson['completed'] ? formatScore($lesson['score'])."%" : '', $fieldCenterFormat);
					$row++;
				}
			}
			$row++;
		}

		$result	   	  = eF_getTableDataFlat("lessons", "id, name, active");
		$lessonNames  = array_combine($result['id'], $result['name']);

		//Done tests sheet
		$doneTests = EfrontStats :: getStudentsDoneTests(false, $infoUser -> user['login']);

		if (sizeof($doneTests[$infoUser -> user['login']]) > 0) {
			$workSheet = & $workBook -> addWorksheet('Tests Info');
			$workSheet -> setInputEncoding('utf-8');

			$workSheet -> setColumn(0, 0, 5);

			$row = 1;
			$workSheet -> write($row, 1, _TESTSINFORMATION, $headerFormat);
			$workSheet -> mergeCells($row, 1, $row, 4);
			$workSheet -> setColumn(1, 4, 25);

			$row++;
			$workSheet -> write($row, 1, _LESSON, $titleLeftFormat);
			$workSheet -> write($row, 2, _TESTNAME, $titleCenterFormat);
			$workSheet -> write($row, 3, _SCORE, $titleCenterFormat);
			$workSheet -> write($row++, 4, _DATE, $titleCenterFormat);

			$avgScore	 = 0;
			foreach ($doneTests[$infoUser -> user['login']] as $contentId => $test) {
				$workSheet -> write($row, 1, $lessonNames[$test['lessons_ID']], $fieldLeftFormat);
				$workSheet -> write($row, 2, $test['name'], $fieldCenterFormat);
				$workSheet -> write($row, 3, formatScore($test['active_score'])."%", $fieldCenterFormat);
				$workSheet -> write($row++, 4, formatTimestamp($test['timestamp'], 'time_nosec'), $fieldCenterFormat);
				$avgScore += $test['active_score'];
			}
			$row +=2;
			$workSheet -> write($row, 2, _AVERAGESCORE, $titleLeftFormat);
			$workSheet -> write($row++, 3, formatScore($avgScore / sizeof($doneTests[$infoUser -> user['login']]))."%", $fieldCenterFormat);
		}

		//Assigend projects sheet
		$assignedProjects = EfrontStats :: getStudentsAssignedProjects(false, $infoUser -> user['login']);
		if (sizeof($assignedProjects[$infoUser -> user['login']]) > 0  && EfrontUser::isOptionVisible('projects')) {
			$workSheet = & $workBook -> addWorksheet('Projects Info');
			$workSheet -> setInputEncoding('utf-8');

			$workSheet -> setColumn(0, 0, 5);

			$row = 1;
			$workSheet -> write($row, 1, _PROJECTSINFORMATION, $headerFormat);
			$workSheet -> mergeCells($row, 1, $row, 4);
			$workSheet -> setColumn(1, 4, 25);

			$row++;
			$workSheet -> write($row, 1, _LESSON, $titleLeftFormat);
			$workSheet -> write($row, 2, _PROJECTNAME, $titleLeftFormat);
			$workSheet -> write($row, 3, _SCORE, $titleCenterFormat);
			$workSheet -> write($row++, 4, _COMMENTS, $titleLeftFormat);

			$avgScore	 = 0;
			foreach ($assignedProjects[$infoUser -> user['login']] as $project) {
				$workSheet -> write($row, 1, $lessonNames[$project['lessons_ID']], $fieldLeftFormat);
				$workSheet -> write($row, 2, $project['title'], $fieldLeftFormat);
				$workSheet -> write($row, 3, formatScore($project['grade'])."%", $fieldCenterFormat);
				$workSheet -> write($row++, 4, $project['comments'], $fieldLeftFormat);
				$avgScore += $project['grade'];
			}
			$row +=2;
			$workSheet -> write($row, 2, _AVERAGESCORE, $titleLeftFormat);
			$workSheet -> write($row++, 3, formatScore($avgScore / sizeof($assignedProjects[$infoUser -> user['login']]))."%", $titleCenterFormat);
		}


		//transpose tests array, from (login => array(test id => test)) to array(lesson id => array(login => array(test id => test)))
		$temp = array();
		foreach ($doneTests as $login => $userTests) {
			foreach ($userTests as $contentId => $test) {
				$temp[$test['lessons_ID']][$login][$contentId] = $test;
			}
		}
		$doneTests = $temp;
		//transpose projects array, from (login => array(project id => project)) to array(lesson id => array(login => array(project id => project)))
		$temp = array();
		foreach ($assignedProjects as $login => $userProjects) {
			foreach ($userProjects as $projectId => $project) {
				$temp[$project['lessons_ID']][$login][$projectId] = $project;
			}
		}
		$assignedProjects = $temp;

		//add a separate sheet for each distinct course of that user
		$count = 1;
		foreach ($userCourses as $id => $course) {
			$constraints = array('instance' => $id, 'archive' => false, 'active' => true);
			$constraints['required_fields'] = array('has_instances', 'location', 'user_type', 'completed', 'score', 'has_course', 'num_lessons', 'to_timestamp');
			//$constraints['return_objects']  = false;
			$instances   = $infoUser -> getUserCourses($constraints);
			//unset($instances[$course -> course['id']]); //Remove self from instances

			//$course = $course -> course;
			$workSheet = & $workBook -> addWorksheet("Course ".$count++);
			$workSheet -> setInputEncoding('utf-8');

			$workSheet -> write(0, 0, $course['name'], $headerBigFormat);
			$workSheet -> mergeCells(0, 0, 0, 9);
			$workSheet -> write(1, 0, $infoUser -> user['name']." ".$infoUser -> user['surname'].' ('.$infoUser -> user['login'].')', $fieldCenterFormat);
			$workSheet -> mergeCells(1, 0, 1, 9);

			$workSheet -> setColumn(0, 0, 20);
			$workSheet -> setColumn(1, 1, 20);

			$row = 3;
			$workSheet -> write($row, 0, _STATUS, $headerFormat);
			$workSheet -> mergeCells($row, 0, $row++, 1);
			$workSheet -> write($row, 0, _COMPLETED, $fieldCenterFormat);
			$workSheet -> write($row++, 1, $course['completed'] ? _YES : _NO, $fieldCenterFormat);
			$workSheet -> write($row, 0, _COMPLETEDON, $fieldCenterFormat);
			$workSheet -> write($row++, 1, formatTimestamp($course['to_timestamp']), $fieldCenterFormat);
			$workSheet -> write($row, 0, _GRADE, $fieldCenterFormat);
			$workSheet -> write($row++, 1, formatScore($course['score'])."%", $fieldCenterFormat);

			if (sizeof($instances) > 1) {
				$row++;
				$workSheet -> write($row, 0, _COURSEINSTANCES, $headerFormat);
				$workSheet -> mergeCells($row, 0, $row, 2);
				$workSheet -> setColumn(0, 2, 25);

				$row++;
				$workSheet -> write($row, 0, _INSTANCE, $titleLeftFormat);
				$workSheet -> write($row, 1, _COMPLETED, $titleCenterFormat);
				$workSheet -> write($row, 3, _SCORE, $titleCenterFormat);

				foreach ($instances as $instance) {
					$row++;
					$workSheet -> write($row, 0, $instance -> course['name'], $fieldLeftFormat);
					$workSheet -> write($row, 1, $instance -> course['completed'] ? _YES.', '._ON.' '.formatTimestamp($instance -> course['to_timestamp']) : _NO, $fieldCenterFormat);
					$workSheet -> write($row, 3, formatScore($instance -> course['score'])."%", $fieldCenterFormat);
				}
			}

			$row+=2;
			foreach ($instances as $instance) {
				$row+=2;
				$workSheet -> write($row, 0, '"'.$instance -> course['name'].'" '._LESSONS, $headerFormat);
				$workSheet -> mergeCells($row, 0, $row, 2);
				$workSheet -> setColumn(0, 2, 25);

				$row++;
				$workSheet -> write($row, 0, _NAME, $titleLeftFormat);
				$workSheet -> write($row, 1, _COMPLETED, $titleCenterFormat);
				$workSheet -> write($row, 2, _SCORE, $titleCenterFormat);

				$lessons = $infoUser -> getUserStatusInCourseLessons($instance, true);
				foreach ($lessons as $lesson) {
					$row++;
					$workSheet -> write($row, 0, $lesson -> lesson['name'], $fieldLeftFormat);
					$workSheet -> write($row, 1, $lesson -> lesson['completed'] ? _YES.', '._ON.' '.formatTimestamp($lesson -> lesson['timestamp_completed']) : _NO, $fieldCenterFormat);
					$workSheet -> write($row, 2, formatScore($lesson -> lesson['score'])."%", $fieldCenterFormat);
				}
			}
			
			//add a separate sheet for each distinct lesson of that user
			$subcount = 1;			
			foreach ($infoUser -> getUserStatusInCourseLessons(new EfrontCourse($course)) as $id => $lesson) {
				$lesson = $lesson -> lesson;				
				$workSheet = & $workBook -> addWorksheet("Course ".($count-1)." lesson ".$subcount++);
				$workSheet -> setInputEncoding('utf-8');
			
				$workSheet -> write(0, 0, $lesson['name'], $headerBigFormat);
				$workSheet -> mergeCells(0, 0, 0, 9);
				$workSheet -> write(1, 0, $infoUser -> user['name']." ".$infoUser -> user['surname'].' ('.$infoUser -> user['login'].')', $fieldCenterFormat);
				$workSheet -> mergeCells(1, 0, 1, 9);
			
				$workSheet -> setColumn(0, 0, 20);
				$workSheet -> setColumn(1, 1, 20);
			
				$row = 3;
				if ($GLOBALS['configuration']['time_reports']) {
					$workSheet -> write($row, 0, _ACTIVETIMEINLESSON, $headerFormat);
				} else {
					$workSheet -> write($row, 0, _TIMEINLESSON, $headerFormat);
				}
				$workSheet -> mergeCells($row, 0, $row++, 1);
				if ($GLOBALS['configuration']['time_reports']) {
					$workSheet -> write($row, 0, $lesson['active_time_in_lesson']['time_string'], $fieldCenterFormat);
				} else {
					$workSheet -> write($row, 0, $lesson['time_in_lesson']['time_string'], $fieldCenterFormat);
				}
				$workSheet -> mergeCells($row, 0, $row++, 1);
			
				$workSheet -> write($row, 0, _STATUS, $headerFormat);
				$workSheet -> mergeCells($row, 0, $row++, 1);
				$workSheet -> write($row, 0, _COMPLETED, $fieldCenterFormat);
				$workSheet -> write($row++, 1, $lesson['completed'] ? _YES : _NO, $fieldCenterFormat);
				$workSheet -> write($row, 0, _COMPLETEDON, $fieldCenterFormat);
				$workSheet -> write($row++, 1, formatTimestamp($lesson['timestamp_completed']), $fieldCenterFormat);
				$workSheet -> write($row, 0, _GRADE, $fieldCenterFormat);
				$workSheet -> write($row++, 1, formatScore($lesson['score'])."%", $fieldCenterFormat);
			
				$workSheet -> write($row, 0, _OVERALL, $headerFormat);
				$workSheet -> mergeCells($row, 0, $row++, 1);
				$workSheet -> write($row, 0, formatScore($lesson['overall_progress']['percentage'])."%", $fieldCenterFormat);
				$workSheet -> mergeCells($row, 0, $row++, 1);
			
				if (sizeof($doneTests[$id][$infoUser -> user['login']]) > 0 && EfrontUser::isOptionVisible('tests')) {
					$workSheet -> write($row, 0, _TESTS, $headerFormat);
					$workSheet -> mergeCells($row, 0, $row++, 1);
					$avgScore = 0;
					foreach ($doneTests[$id][$infoUser -> user['login']] as $test) {
						$workSheet -> write($row, 0, $test['name'], $fieldCenterFormat);
						$workSheet -> write($row++, 1, formatScore($test['active_score'])."%", $fieldCenterFormat);
						$avgScore += $test['active_score'];
					}
					$workSheet -> write($row, 0, _AVERAGESCORE, $titleCenterFormat);
					$workSheet -> write($row++, 1, formatScore($avgScore / sizeof($doneTests[$id][$infoUser -> user['login']]))."%", $titleCenterFormat);
				}
			
				if (sizeof($assignedProjects[$id][$infoUser -> user['login']]) > 0 && EfrontUser::isOptionVisible('projects')) {
					$workSheet -> write($row, 0, _PROJECTS, $headerFormat);
					$workSheet -> mergeCells($row, 0, $row++, 1);
					$avgScore = 0;
					foreach ($assignedProjects[$id][$infoUser -> user['login']] as $project) {
						$workSheet -> write($row, 0, $project['title'], $fieldCenterFormat);
						$workSheet -> write($row++, 1, formatScore($project['grade'])."%", $fieldCenterFormat);
						$avgScore += $project['grade'];
					}
					$workSheet -> write($row, 0, _AVERAGESCORE, $titleCenterFormat);
					$workSheet -> write($row++, 1, formatScore($avgScore / sizeof($assignedProjects[$id][$infoUser -> user['login']]))."%", $titleCenterFormat);
				}

			}
		}

		//add a separate sheet for each distinct lesson of that user
		$count = 1;
		foreach ($userLessons as $id => $lesson) {
			$lesson = $lesson -> lesson;
			$workSheet = & $workBook -> addWorksheet("Lesson ".$count++);
			$workSheet -> setInputEncoding('utf-8');

			$workSheet -> write(0, 0, $lesson['name'], $headerBigFormat);
			$workSheet -> mergeCells(0, 0, 0, 9);
			$workSheet -> write(1, 0, $infoUser -> user['name']." ".$infoUser -> user['surname'].' ('.$infoUser -> user['login'].')', $fieldCenterFormat);
			$workSheet -> mergeCells(1, 0, 1, 9);

			$workSheet -> setColumn(0, 0, 20);
			$workSheet -> setColumn(1, 1, 20);

			$row = 3;
			if ($GLOBALS['configuration']['time_reports']) {
				$workSheet -> write($row, 0, _ACTIVETIMEINLESSON, $headerFormat);
			} else {
				$workSheet -> write($row, 0, _TIMEINLESSON, $headerFormat);
			}
			$workSheet -> mergeCells($row, 0, $row++, 1);
			if ($GLOBALS['configuration']['time_reports']) {
				$workSheet -> write($row, 0, $lesson['active_time_in_lesson']['time_string'], $fieldCenterFormat);
			} else {
				$workSheet -> write($row, 0, $lesson['time_in_lesson']['time_string'], $fieldCenterFormat);
			}
			$workSheet -> mergeCells($row, 0, $row++, 1);

			$workSheet -> write($row, 0, _STATUS, $headerFormat);
			$workSheet -> mergeCells($row, 0, $row++, 1);
			$workSheet -> write($row, 0, _COMPLETED, $fieldCenterFormat);
			$workSheet -> write($row++, 1, $lesson['completed'] ? _YES : _NO, $fieldCenterFormat);
			$workSheet -> write($row, 0, _COMPLETEDON, $fieldCenterFormat);
			$workSheet -> write($row++, 1, formatTimestamp($lesson['timestamp_completed']), $fieldCenterFormat);
			$workSheet -> write($row, 0, _GRADE, $fieldCenterFormat);
			$workSheet -> write($row++, 1, formatScore($lesson['score'])."%", $fieldCenterFormat);

			$workSheet -> write($row, 0, _OVERALL, $headerFormat);
			$workSheet -> mergeCells($row, 0, $row++, 1);
			$workSheet -> write($row, 0, formatScore($lesson['overall_progress']['percentage'])."%", $fieldCenterFormat);
			$workSheet -> mergeCells($row, 0, $row++, 1);

			if (sizeof($doneTests[$id][$infoUser -> user['login']]) > 0 && EfrontUser::isOptionVisible('tests')) {
				$workSheet -> write($row, 0, _TESTS, $headerFormat);
				$workSheet -> mergeCells($row, 0, $row++, 1);
				$avgScore = 0;
				foreach ($doneTests[$id][$infoUser -> user['login']] as $test) {
					$workSheet -> write($row, 0, $test['name'], $fieldCenterFormat);
					$workSheet -> write($row++, 1, formatScore($test['active_score'])."%", $fieldCenterFormat);
					$avgScore += $test['active_score'];
				}
				$workSheet -> write($row, 0, _AVERAGESCORE, $titleCenterFormat);
				$workSheet -> write($row++, 1, formatScore($avgScore / sizeof($doneTests[$id][$infoUser -> user['login']]))."%", $titleCenterFormat);
			}

			if (sizeof($assignedProjects[$id][$infoUser -> user['login']]) > 0 && EfrontUser::isOptionVisible('projects')) {
				$workSheet -> write($row, 0, _PROJECTS, $headerFormat);
				$workSheet -> mergeCells($row, 0, $row++, 1);
				$avgScore = 0;
				foreach ($assignedProjects[$id][$infoUser -> user['login']] as $project) {
					$workSheet -> write($row, 0, $project['title'], $fieldCenterFormat);
					$workSheet -> write($row++, 1, formatScore($project['grade'])."%", $fieldCenterFormat);
					$avgScore += $project['grade'];
				}
				$workSheet -> write($row, 0, _AVERAGESCORE, $titleCenterFormat);
				$workSheet -> write($row++, 1, formatScore($avgScore / sizeof($assignedProjects[$id][$infoUser -> user['login']]))."%", $titleCenterFormat);
			}
		}
	}
	$workBook -> send('export_'.$infoUser -> user['login'].'.xls');
	
	$workBook -> close();
	exit();
} else if (isset($_GET['pdf']) && $_GET['pdf'] == 'user') {

	$pdf = new EfrontPdf(_REPORT.": ".formatLogin($infoUser -> user['login']));
	
	try {
		$avatarFile = new EfrontFile($infoUser -> user['avatar']);
	} catch(Exception $e) {
		$avatarFile = new EfrontFile(G_SYSTEMAVATARSPATH."unknown_small.png");
	}

	$info = array(array(_USERNAME, $userInfo['general']['fullname']),
				  array(_USERTYPE, $userInfo['general']['user_types_ID'] ? $userInfo['general']['user_types_ID'] : $roles[$userInfo['general']['user_type']]),
				  array(_ACTIVE,   $userInfo['general']['active'] ? _YES : _NO),
				  array(_JOINED,   $userInfo['general']['joined_str']),
				  array(_TOTALLOGINTIME, $userInfo['general']['total_login_time']['time_string']));
	$pdf -> printInformationSection(_GENERALUSERINFO, $info, $avatarFile);

	$info = array(array(_FORUMPOSTS, sizeof($userInfo['communication']['forum_messages'])),
				  array(_FORUMLASTMESSAGE, formatTimestamp($userInfo['communication']['last_message']['timestamp'])),
				  array(_PERSONALMESSAGES, sizeof($userInfo['communication']['personal_messages'])),
				  array(_MESSAGESFOLDERS, sizeof($userInfo['communication']['personal_folders'])),
				  array(_FILES, sizeof($userInfo['communication']['files'])),
				  array(_FOLDERS, sizeof($userInfo['communication']['folders'])),
				  array(_TOTALSIZE, $userInfo['communication']['total_size']._KB),
				  array(_COMMENTS, sizeof($userInfo['communication']['comments'])));

	if (!EfrontUser::isOptionVisible('forum')) {
		unset($info[_FORUMPOSTS]);
		unset($info[_FORUMLASTMESSAGE]);
	}
	if (!EfrontUser::isOptionVisible('messages')) {
		unset($info[_PERSONALMESSAGES]);
		unset($info[_MESSAGESFOLDERS]);
	}	
	if (!EfrontUser::isOptionVisible('comments')) {
		unset($info[_COMMENTS]);
	}	
	$pdf -> printInformationSection(_USERCOMMUNICATIONINFO, $info);

	$meanDuration = $timesReport->formatTimeForReporting($userInfo['usage']['mean_duration']*60);
	$monthDuration = $timesReport -> formatTimeForReporting($userInfo['usage']['month_mean_duration']*60);
	$weekDuration = $timesReport -> formatTimeForReporting($userInfo['usage']['week_mean_duration']*60);

	$info = array(array(_LASTLOGIN, formatTimestamp($userInfo['usage']['last_login']['timestamp'])),
				  array(_TOTALLOGINS, sizeof($userInfo['usage']['logins'])),
				  array(_MONTHLOGINS, sizeof($userInfo['usage']['month_logins'])),
				  array(_WEEKLOGINS, sizeof($userInfo['usage']['week_logins'])),
				  array(_MEANDURATION, $meanDuration['time_string']),
				  array(_MONTHMEANDURATION, $monthDuration['time_string']),
				  array(_WEEKMEANDURATION, $weekDuration['time_string']),
				  array(_LASTIPUSED, $userInfo['usage']['last_ip']));
	$pdf -> printInformationSection(_USERUSAGEINFO, $info);

	$coursesAvgScore = $lessonsAvgScore = $projectsAvgScore = $testsAvgScore = 0;
	$data = array();
	$constraints = array('archive' => false, 'active' => true);
	$constraints['required_fields'] = array('has_instances', 'location', 'user_type', 'completed', 'score', 'has_course', 'num_lessons', 'to_timestamp');
	$constraints['return_objects']  = false;
	$userCourses = $infoUser -> getUserCourses($constraints);
	
	$dataL = $subSections = $userDoneTests = array();
	$result = EfrontStats :: getStudentsDoneTests($userLessons, $infoUser -> user['login']);
	foreach ($result[$infoUser -> user['login']] as $value) {
		$userDoneTests[$value['lessons_ID']][] = $value;
	}
	$userLessons = $infoUser -> getUserStatusInIndependentLessons();
	
	
	
	if ($infoUser -> user['user_type'] != 'administrator' && (!empty($userCourses) || !empty($userLessons))) {
		$formatting = array(_NAME			  => array('width' => '35%', 'fill' => false),
							_CATEGORY		  => array('width' => '20%','fill' => false),
							_REGISTRATIONDATE => array('width' => '13%','fill' => false),
							_COMPLETED		  => array('width' => '13%','fill' => false, 'align' => 'C'),
							_SCORE			  => array('width' => '9%','fill' => false, 'align' => 'R'));

		if ($GLOBALS['configuration']['time_reports']) {
			$formatting[_ACTIVETIME] = array('width' => '10%','fill' => false, 'align' => 'R');
		} else {
			$formatting[_TIME] = array('width' => '10%','fill' => false, 'align' => 'R');
		}

		$completedScores = array();
		foreach ($userCourses as $courseId => $value) {
			$coursesTotalSec = 0;
			//$coursesAvgScore += $value['score'];
			if ($value['completed']) {
				$completedScores[] = $value['score'];
			}

			$data[$courseId] = array(_NAME			  => $value['name'],
									_CATEGORY		  => str_replace("&nbsp;&rarr;&nbsp;", " -> ", $directionsTreePaths[$value['directions_ID']]),
									_REGISTRATIONDATE => formatTimestamp($value['active_in_course']),
									_COMPLETED		  => $value['completed'] ? _YES.($value['to_timestamp'] ? ', '._ON.' '.formatTimestamp($value['to_timestamp']) : '') : '-',
									_SCORE			  => formatScore($value['score']).'%',
									'active'		  => $value['active']);

			$courseLessons = $infoUser -> getUserStatusInCourseLessons(new EfrontCourse($value), true);

			if (!empty($courseLessons)) {
				$subsectionFormatting = array(_NAME			    => array('width' => '68%', 'fill' => true),
											  _COMPLETED		=> array('width' => '13%', 'fill' => true, 'align' => 'C'),
											  _SCORE			=> array('width' => '9%',  'fill' => true, 'align' => 'R'));
				if ($GLOBALS['configuration']['time_reports']) {
					$subsectionFormatting[_ACTIVETIMEINLESSON] = array('width' => '10%',  'fill' => true, 'align' => 'R');
				} else {
					$subsectionFormatting[_TIME] = array('width' => '10%',  'fill' => true, 'align' => 'R');
				}
											  
				$result = EfrontStats :: getStudentsDoneTests($courseLessons, $infoUser -> user['login']);

				$userDoneTests = array();
				foreach ($result[$infoUser -> user['login']] as $test) {
					$userDoneTests[$test['lessons_ID']][] = $test;
				}

				$subSectionData = array();
				foreach ($courseLessons as $lessonId => $courseLesson) {
					$courseLesson = $courseLesson->lesson;
					
					if ($GLOBALS['configuration']['time_reports']) {
						$coursesTotalSec += $courseLesson['active_time_in_lesson']['total_seconds'];
					} else {
						$coursesTotalSec += $courseLesson['time_in_lesson']['total_seconds'];
					}
					$subSectionData[$lessonId] = array(_NAME	  => $courseLesson['name'],
													   _COMPLETED => $courseLesson['completed'] ? _YES.($courseLesson['timestamp_completed'] ? ', '._ON.' '.formatTimestamp($courseLesson['timestamp_completed']): '') : '-',
													   _SCORE 	  => formatScore($courseLesson['score']).'%');
					if ($GLOBALS['configuration']['time_reports']) {
						$subSectionData[$lessonId][_ACTIVETIMEINLESSON] = $courseLesson['active_time_in_lesson']['time_string'];
					} else {
						$subSectionData[$lessonId][_TIME] = $courseLesson['time_in_lesson']['time_string'];
					}
/*
					if (isset($userDoneTests[$value['id']])) {
						$testSubsectionFormatting = array(_TESTNAME	=> array('width' => '78%', 'fill' => true),
														  _STATUS	=> array('width' => '13%', 'fill' => true, 'align' => 'C'),
														  _SCORE	=> array('width' => '9%',  'fill' => true, 'align' => 'R'));
						$testsSubSectionData = array();
						foreach ($userDoneTests[$value['id']] as $test) {
							$testsAvgScore += $test['score'];
							$testsSubSectionData[] = array(_TESTNAME => $test['name'],
														   _STATUS   => $test['status'],
														   _SCORE 	 => formatScore($test['score']).'%');
						}
						$testSubSections[$lessonId] = array('data' => $testsSubSectionData, 'formatting' => $testSubsectionFormatting, 'title' => _TESTSFORLESSON.': '.$courseLesson['name']);
					}
*/
				}
				$timeArray = $timesReport -> formatTimeForReporting($coursesTotalSec);
				$data[$courseId][_TIME] = $timeArray['time_string'];
				$subSections[$courseId] = array('data' => $subSectionData, 'formatting' => $subsectionFormatting, 'title' => _LESSONSFORCOURSE.': '.$value['name'], 'subSections' => $testSubSections);
			}
		}
		if (sizeof($completedScores) > 0) {
 			$coursesAvgScore = round(array_sum($completedScores) / sizeof($completedScores), 2);
		}

		$pdf->printDataSection(_TRAINING.': '._COURSES, $data, $formatting, $subSections);


		$completedScores = array();
		foreach ($userLessons as $lessonId => $value) {
			$value = $value -> lesson;
			//$lessonsAvgScore += $value['score'];

			if ($value['completed']) {
				$completedScores[] = $value['score'];
			}
			
			$dataL[$lessonId] = array(_NAME			   => $value['name'],
									 _CATEGORY		   => str_replace("&nbsp;&rarr;&nbsp;", " -> ", $directionsTreePaths[$value['directions_ID']]),
									 _REGISTRATIONDATE => formatTimestamp($value['active_in_lesson']),
									 _COMPLETED		   => $value['completed'] ? _YES.($value['timestamp_completed'] ? ', '._ON.' '.formatTimestamp($value['timestamp_completed']): '') : '-',
									 _SCORE			   => formatScore($value['score']).'%');
			if ($GLOBALS['configuration']['time_reports']) {
				$dataL[$lessonId][_ACTIVETIMEINLESSON] = $value['active_time_in_lesson']['time_string'];
			} else {
				$dataL[$lessonId][_TIME] = $value['time_in_lesson']['time_string'];
			}
			if (isset($userDoneTests[$value['id']])) {
				$subsectionFormatting = array(_TESTNAME	=> array('width' => '78%', 'fill' => true),
											  _STATUS	=> array('width' => '13%', 'fill' => true, 'align' => 'C'),
											  _SCORE	=> array('width' => '9%',  'fill' => true, 'align' => 'R'));
				$subSectionData = array();
				foreach ($userDoneTests[$value['id']] as $test) {
					$subSectionData[] = array(_TESTNAME	=> $test['name'],
											  _STATUS   => $test['status'],
											  _SCORE 	=> formatScore($test['active_score']).'%');
				}
				$subSections[$lessonId] = array('data' => $subSectionData, 'formatting' => $subsectionFormatting, 'title' => _TESTSFORLESSON.': '.$value['name']);
			}
		}
		$pdf->printDataSection(_TRAINING.': '._LESSONS, $dataL, $formatting, $subSections);
		if (sizeof($completedScores) > 0) {
 			$lessonsAvgScore = round(array_sum($completedScores) / sizeof($completedScores), 2);
		}
/*
		$testsAvgScoreNum = 0;
		$testsAvgScore    = 0; 
		foreach ($userDoneTests as $lessonId => $tests) {
				foreach ($tests as $test) {
					$testsAvgScore += $test['active_score'];
					$testsAvgScoreNum++;
				}
		}
*/
		$info = array(array(_COURSESAVERAGE, $coursesAvgScore.'%'),
					  array(_STANDALONELESSONSAVERAGE, $lessonsAvgScore.'%'));
		$pdf -> printInformationSection(_OVERALL, $info);
	}
	$pdf -> OutputPdf('user_form_'.$infoUser -> user['login'].'.pdf');
	exit;


}

function eF_local_shouldDisplaySelectBox() {
	global $currentUser;
	global $isSupervisor;
	
	if ($currentUser -> user['user_type'] == 'administrator' || $isSupervisor) {
		return true;
	} else if (sizeof($currentUser -> getLessons(false, 'professor')) > 0) {
		return true;
	} else {
		return false;
	}
}

function eF_local_canAccessUser() {
	global $currentUser;
	global $isSupervisor;
	$editedUser = EfrontUserFactory::factory($_GET['sel_user']);
	
	if ($currentUser -> user['user_type'] == 'administrator') { //can view any user
		return true;
	} 
	if ($editedUser->user['login'] == $currentUser->user['login']) { //can view himself
		return true;
	} 
	if ($isSupervisor) {			//can view any user he/she supervises
		if ($currentUser->aspects['hcd']-> supervisesEmployee($editedUser->user['login'])) {
			return true;
		}
	} 
	$userLessons = $currentUser -> getLessons(false, 'professor');

	if (!empty($userLessons)) {
		$result = eF_getTableData("users_to_lessons", "users_LOGIN", "archive=0 and users_LOGIN='".$editedUser->user['login']."' and lessons_ID in (".implode(",", array_keys($userLessons)).")");
	}
	if (!empty($result)) {
		return true;
	}
	return false;
}
/*		
	} else if ($_SESSION['s_lessons_ID']) {
		$statisticsLesson = new EfrontLesson($_SESSION['s_lessons_ID']);
		$lessonUsers	  = $statisticsLesson -> getUsers();
		if ($lessonRoles[$lessonUsers[$currentUser -> user['login']]['role']] == 'professor') {
			$validUsers = $lessonUsers;
		} else if ($lessonRoles[$lessonUsers[$currentUser -> user['login']]['role']] == 'student') {
			$validUsers[$currentUser -> user['login']] = $currentUser;

			if (!$isSupervisor) {
				$smarty -> assign("T_SINGLE_USER", true);							//assign this variable, so that select user panel is not available
				$_GET['sel_user'] = $currentUser -> user['login'];
			}
		} else {
			throw new EfrontUserException(_USERDOESNOTHAVETHISLESSON.": ".$statisticsLesson -> lesson['name'], EfrontUserException :: USER_NOT_HAVE_LESSON);
		}
	} else {											   //if the system user is a simple student
		if ($_student_ && !$isSupervisor) {
			$smarty -> assign("T_SINGLE_USER", true);
			$_GET['sel_user'] = $currentUser -> user['login'];
			$validUsers 	  = array($currentUser -> user['login'] => $currentUser -> user['login']);
		} else {
			$userLessons = $currentUser -> getLessons(true);
			$users	     = array();
			$result = eF_getTableDataFlat("users_to_lessons ul, lessons l, users u", "distinct u.login", "u.archive=0 and l.archive=0 and ul.lessons_ID=l.id and ul.users_LOGIN=u.login and ul.archive=0 and ul.lessons_ID in (".implode(",", array_keys($userLessons)).")");
			$validUsers = $result['login'];
			pr(array_keys($validUsers));
		}
	}
*/	

