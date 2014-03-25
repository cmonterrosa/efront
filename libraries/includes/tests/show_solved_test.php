<?php

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

        $result   = EfrontCompletedTest::retrieveCompletedTest("completed_tests ct join completed_tests_blob ctb on ct.id=ctb.completed_tests_ID", "ct.*, ctb.test", "ct.status != 'deleted' and ct.id=".$_GET['show_solved_test']);
        if (sizeof($result) == 0) {
            throw new EfrontTestException(_NONEXISTENTTEST.': '.$_GET['show_solved_test'], EfrontTestException :: NOT_DONE_TEST);
        }
        if (!$completedTest = unserialize($result[0]['test'])) {
            throw new EfrontTestException(_TESTCORRUPTEDORNOTACOMPLETEDTEST, EfrontTestException::CORRUPTED_TEST);
        }

        if (!isset($_GET['test_analysis'])) {
            $status        = $completedTest -> getStatus($result[0]['users_LOGIN']);

            $url     = basename($_SERVER['PHP_SELF']).'?ctg=tests&show_solved_test='.$completedTest -> completedTest['id'];
            $baseUrl = basename($_SERVER['PHP_SELF']).'?ctg=tests';

            //WARNING! This must be above the options set right below (answers, given answers) or else the test will be saved with the changed options
            if (isset($_GET['ajax'])) {
                $completedTest -> handleAjaxActions();
                exit;
            }
			if ($_GET['ctg'] != 'feedback') {
				$completedTest -> options['answers']       = true;
			}
			$completedTest -> options['given_answers'] = true;

            // We do not want all handles for test editing for skillgap tests - the students do not see the tests
            if ($skillgap_tests) {
                $testString = $completedTest -> toHTMLQuickForm(new HTML_Quickform(), false, true, false);
				$testString = $completedTest -> toHTMLSolved($testString, false);
			} elseif ($_GET['ctg'] == 'feedback') {
				$testString = $completedTest -> toHTMLQuickForm(new HTML_Quickform(), false, true, false, false, true);
				$testString = $completedTest -> toHTMLSolved($testString, false, true);
            } else {
                $testString = $completedTest -> toHTMLQuickForm(new HTML_Quickform(), false, true, true);
                $testString = $completedTest -> toHTMLSolved($testString, true);
            }

            $smarty -> assign("T_TEST_SOLVED", $testString);
            $smarty -> assign("T_TEST_DATA", $completedTest);

        } else {

            if ($skillgap_tests && isset($_GET['user']) && eF_checkParameter($_GET['user'], 'login')) {
                // Per-user analysis of the tests => skill gap analysis

                // AJAX CODE TO RELOAD SKILL-GAP ANALYSIS PROPOSED LESSONS
                if (isset($_GET['ajax']) && $_GET['ajax'] == 'proposedLessonsTable') {
                    isset($_GET['limit']) ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

                    $directionsTree = new EfrontDirectionsTree();
                    $directionsPaths = $directionsTree -> toPathString();
                    $languages       = EfrontSystem :: getLanguages(true);

                    $skills_missing = array();
                    $all_skills = "";

                    foreach ($_GET as $key => $value) {
                        // all skill-related posted values are just the skill_ID ~ a uint value
                        if (eF_checkParameter($key, 'unit')) {
                            if ($value == 1) {
                                $skills_missing[] = $key;
                                $all_skills .= "&".$skill_item['id'] . "=1";
                            } else {
                                $all_skills .= "&".$skill_item['id'] . "=0";
                            }
                        }
                    }
                    // This smarty variable will denote all missing and existing skills
                    $smarty -> assign("T_MISSING_SKILLS_URL", $all_skills);

                    // check what you GET and keep only the skills
                    $skills_missing = implode("','",  $skills_missing);

                    if ($_SESSION['s_type'] == 'administrator') {
                    	$user = EfrontUserFactory :: factory($_GET['user']);
                    } else {
                    	$user = EfrontUserFactory :: factory($_SESSION['s_login']);
                    }
                    $alredy_attending = implode("','",  array_keys($user -> getLessons()));

                    $lessons_proposed = eF_getTableData("module_hcd_skills LEFT OUTER JOIN module_hcd_lesson_offers_skill ON module_hcd_skills.skill_ID = module_hcd_lesson_offers_skill.skill_ID JOIN lessons ON lessons.id = module_hcd_lesson_offers_skill.lesson_ID","module_hcd_lesson_offers_skill.lesson_ID, lessons.*, count(module_hcd_lesson_offers_skill.skill_ID) as skills_offered", "module_hcd_lesson_offers_skill.skill_ID IN ('".$skills_missing."') AND module_hcd_lesson_offers_skill.lesson_ID NOT IN ('".$alredy_attending."')","","module_hcd_lesson_offers_skill.lesson_ID ORDER BY skills_offered DESC");

                    if (isset($_GET['sort'])) {
                        isset($_GET['order']) ? $order = $_GET['order'] : $order = 'asc';
                        $lessons_proposed = eF_multiSort($lessons_proposed, $_GET['sort'], $order);
                    }
                    if (isset($_GET['filter'])) {
                        $lessons_proposed = eF_filterData($lessons_proposed, $_GET['filter']);
                    }
                    $smarty -> assign("T_PROPOSED_LESSONS_SIZE", sizeof($lessons_proposed));
                    if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
                        isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
                        $lessons_proposed = array_slice($lessons_proposed, $offset, $limit);
                    }
                    foreach ($lessons_proposed as $key => $proposed_lesson) {
                        $obj = new EfrontLesson($proposed_lesson['lesson_ID']);
                        $lessons_proposed[$key]['link'] = $obj -> toHTMLTooltipLink(basename($_SERVER['PHP_SELF']).'?ctg=lessons&edit_lesson='.$proposed_lesson['id']);
                        $lessons_proposed[$key]['direction_name'] = $directionsPaths[$proposed_lesson['directions_ID']];
                        $lessons_proposed[$key]['languages_NAME'] = $languages[$proposed_lesson['languages_NAME']];
                    }

                    $smarty -> assign("T_PROPOSED_LESSONS_DATA", $lessons_proposed);

                    $smarty -> display('administrator.tpl');
                    exit;
                }

                // AJAX CODE TO RELOAD SKILL-GAP ANALYSIS PROPOSED COURSES
                if (isset($_GET['ajax']) && $_GET['ajax'] == 'proposedCoursesTable') {
                    isset($_GET['limit']) ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

                    $directionsTree = new EfrontDirectionsTree();
                    $directionsPaths = $directionsTree -> toPathString();
                    $languages       = EfrontSystem :: getLanguages(true);

                    $skills_missing = array();
                    $all_skills = "";

                    foreach ($_GET as $key => $value) {
                        // all skill-related posted values are just the skill_ID ~ a uint value
                        if (eF_checkParameter($key, 'unit')) {
                            if ($value == 1) {
                                $skills_missing[] = $key;
                                $all_skills .= "&".$skill_item['id'] . "=1";
                            } else {
                                $all_skills .= "&".$skill_item['id'] . "=0";
                            }
                        }
                    }
                    // This smarty variable will denote all missing and existing skills
                    $smarty -> assign("T_MISSING_SKILLS_URL", $all_skills);

                    // check what you GET and keep only the skills
                    $skills_missing = implode("','",  $skills_missing);

                    if ($_SESSION['s_type'] == 'administrator') {
                    	$user = EfrontUserFactory :: factory($_GET['user']);
                    } else {
                    	$user = EfrontUserFactory :: factory($_SESSION['s_login']);
                    }
                    
                    $alredy_attending = implode("','",  array_keys($user -> getUserCourses()));
                    $courses_proposed = eF_getTableData("module_hcd_skills LEFT OUTER JOIN module_hcd_course_offers_skill ON module_hcd_skills.skill_ID = module_hcd_course_offers_skill.skill_ID JOIN courses ON courses.id = module_hcd_course_offers_skill.courses_ID","module_hcd_course_offers_skill.courses_ID, courses.*, count(module_hcd_course_offers_skill.skill_ID) as skills_offered", "module_hcd_course_offers_skill.skill_ID IN ('".$skills_missing."') AND module_hcd_course_offers_skill.courses_ID NOT IN ('".$alredy_attending."') and courses.archive=0 and courses.active=1","","module_hcd_course_offers_skill.courses_ID ORDER BY skills_offered DESC");

                    if (isset($_GET['sort'])) {
                        isset($_GET['order']) ? $order = $_GET['order'] : $order = 'asc';
                        $courses_proposed = eF_multiSort($courses_proposed, $_GET['sort'], $order);
                    }

                    if (isset($_GET['filter'])) {
                        $courses_proposed = eF_filterData($courses_proposed, $_GET['filter']);
                    }

                    $smarty -> assign("T_PROPOSED_COURSES_SIZE", sizeof($courses_proposed));
                    if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
                        isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
                        $courses_proposed = array_slice($courses_proposed, $offset, $limit);
                    }

                    foreach ($courses_proposed as $key => $proposed_course) {
                        $obj = new EfrontCourse($proposed_course['courses_ID']);
                        $courses_proposed[$key]['link'] = $obj -> toHTMLTooltipLink(basename($_SERVER['PHP_SELF']).'?ctg=courses&edit_course='.$proposed_course['id']);
                        $courses_proposed[$key]['direction_name'] = $directionsPaths[$proposed_course['directions_ID']];
                        $courses_proposed[$key]['languages_NAME'] = $languages[$proposed_course['languages_NAME']];
                    }

                    $smarty -> assign("T_PROPOSED_COURSES_DATA", $courses_proposed);

                    $smarty -> display('administrator.tpl');
                    exit;
                }

                try {
                	if (isset($_GET['postAjaxRequest'])) {
	                    if ($_SESSION['s_type'] == 'administrator') {
	                    	$user = EfrontUserFactory :: factory($_GET['user']);
	                    } else {
	                    	$user = EfrontUserFactory :: factory($_SESSION['s_login']);
	                    }
                		
                		if (isset($_GET['add_lesson'])) {
                			$user -> addLessons($_GET['add_lesson'], $_GET['user_type'], 1);
                		} else if (isset($_GET['add_course'])) {
                			$user -> addCourses($_GET['add_course'], $_GET['user_type'], 1);
                		}
						exit;
                	}
                } catch (Exception $e) {
                	handleAjaxExceptions($e);
                }


                // AJAX CODE TO RELOAD ALREADY ASSIGNED LESSONS
                if (isset($_GET['ajax'])  && $_GET['ajax'] == 'assignedLessonsTable') {
                    $directionsTree = new EfrontDirectionsTree();
                    $directionPaths = $directionsTree -> toPathString();
                    $lessons        = EfrontLesson :: getLessons();

                    if ($_SESSION['s_type'] == 'administrator') {
                    	$editedUser = EfrontUserFactory :: factory($_GET['user']);
                    } else {
                    	$editedUser = EfrontUserFactory :: factory($_SESSION['s_login']);
                    }
                    
                    $userLessons    = $editedUser -> getLessons(true);
                    foreach ($lessons as $key => $lesson) {
                        $lessons[$key]['directions_name'] = $directionPaths[$lesson['directions_ID']];
                        $lessons[$key]['user_type']       = $editedUser -> user['user_types_ID'] ? $editedUser -> user['user_types_ID'] : $editedUser -> user['user_type'];
                        $lessons[$key]['partof']          = 0;
                        if (in_array($lesson['id'], array_keys($userLessons))) {
                            $lessons[$key]['from_timestamp']  = $userLessons[$key] -> userStatus['from_timestamp'];
                            $lessons[$key]['partof']          = 1;
                            $lessons[$key]['user_type']       = $userLessons[$key] -> userStatus['user_type'];
                            $lessons[$key]['completed']       = $userLessons[$key] -> userStatus['completed'];
                            $lessons[$key]['score']           = $userLessons[$key] -> userStatus['score'];
                        } else if ($currentUser -> user['user_type'] != 'administrator' || !$lesson['active']) {
                            unset($lessons[$key]);
                        } else if ($lesson['languages_NAME'] != $editedUser -> user['languages_NAME']) {
                            unset($lessons[$key]);
                        }
                        if ($lesson['course_only']) {
                            unset($lessons[$key]);
                        }
                    }

                    foreach ($lessons as $key => $lesson) {
                        if (!$lesson['partof']) {
                            unset($lessons[$key]);
                        } else {
                            $obj = new EfrontLesson($lesson['id']);
                            $lessons[$key]['link'] = $obj -> toHTMLTooltipLink(basename($_SERVER['PHP_SELF']).'?ctg=lessons&edit_lesson='.$lesson['id']);
                            $lessons[$key]['skills_offered'] = sizeof($obj -> getSkills(true));
                        }
                    }
                    $roles = EfrontLessonUser :: getLessonsRoles(true);
                    $smarty -> assign("T_ROLES_ARRAY", $roles);

                    isset($_GET['limit']) ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

                    if (isset($_GET['sort'])) {
                        isset($_GET['order']) ? $order = $_GET['order'] : $order = 'asc';
                        $lessons = eF_multiSort($lessons, $_GET['sort'], $order);
                    }
                    if (isset($_GET['filter'])) {
                        $lessons = eF_filterData($lessons, $_GET['filter']);
                    }
                    $smarty -> assign("T_ASSIGNED_LESSONS_SIZE", sizeof($lessons));
                    if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
                        isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
                        $lessons = array_slice($lessons, $offset, $limit);
                    }
                    //foreach ($lessons as $key => $lesson) {
                    //$lessons[$key]['languages_NAME'] = $languages[$lesson['languages_NAME']];
                    //}
                    $smarty -> assign("T_ASSIGNED_LESSONS_DATA", $lessons);
                    $smarty -> display('administrator.tpl');
                    exit;
                }

                // AJAX CODE TO RELOAD ALREADY ASSIGNED COURSES
                if (isset($_GET['ajax'])  && $_GET['ajax'] == 'assignedCoursesTable') {
                    $directionsTree = new EfrontDirectionsTree();
                    $directionPaths = $directionsTree -> toPathString();
                    if ($_SESSION['s_type'] == 'administrator') {
                    	$editedUser = EfrontUserFactory :: factory($_GET['user']);
                    } else {
                    	$editedUser = EfrontUserFactory :: factory($_SESSION['s_login']);
                    }
                    
                    $userCourses    = $editedUser -> getUserCourses();
                    $courses = array();
                    foreach ($userCourses as $userCourse) {
                    	$newCourse = $userCourse -> course;
                    	$newCourse['directions_name'] = $directionPaths[$newCourse['directions_ID']];
                    	$courses[] = $newCourse;
                    }

                    isset($_GET['limit']) ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

                    if (isset($_GET['sort'])) {
                        isset($_GET['order']) ? $order = $_GET['order'] : $order = 'asc';
                        $courses = eF_multiSort($courses, $_GET['sort'], $order);
                    }
                    if (isset($_GET['filter'])) {
                        $courses = eF_filterData($courses, $_GET['filter']);
                    }
                    $smarty -> assign("T_ASSIGNED_COURSES_SIZE", sizeof($courses));
                    if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
                        isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
                        $courses = array_slice($courses, $offset, $limit);
                    }
                    //foreach ($courses as $key => $course) {
                    //$courses[$key]['languages_NAME'] = $languages[$course['languages_NAME']];
                    //}

                    $smarty -> assign("T_ASSIGNED_COURSES_DATA", $courses);

                    $smarty -> display('administrator.tpl');
                    exit;
                }


                if (isset($_GET['ajax'])  && $_GET['ajax'] == 'coursesTable') {
                    $directionsTree = new EfrontDirectionsTree();
                    $directionPaths = $directionsTree -> toPathString();
                    $courses        = EfrontCourse :: getCourses();

                    if ($_SESSION['s_type'] == 'administrator') {
                    	$editedUser = EfrontUserFactory :: factory($_GET['user']);
                    } else {
                    	$editedUser = EfrontUserFactory :: factory($_SESSION['s_login']);
                    }
                    
                    $userCourses    = $editedUser -> getUserCourses();
                    foreach ($courses as $key => $course) {
                        $courses[$key]['partof']          = 0;
                        $courses[$key]['directions_name'] = $directionPaths[$course['directions_ID']];
                        $courses[$key]['user_type']       = $editedUser -> user['user_types_ID'] ? $editedUser -> user['user_types_ID'] : $editedUser -> user['user_type'];
                        if (in_array($course['id'], array_keys($userCourses))) {
                            $courses[$key]['from_timestamp']  = $userCourses[$key] -> course['active_in_course'];
                            $courses[$key]['partof']          = 1;
                            $courses[$key]['user_type']       = $userCourses[$key] -> course['user_type'];
                            $courses[$key]['completed']       = $userCourses[$key] -> course['completed'];
                            $courses[$key]['score']           = $userCourses[$key] -> course['score'];
                        } else if ($currentUser -> user['user_type'] != 'administrator' || !$course['active']) {
                            unset($courses[$key]);
                        } else if ($course['languages_NAME'] != $editedUser -> user['languages_NAME']) {
                            unset($courses[$key]);
                        }
                    }
                    $courses = array_values($courses); //Reindex so that sorting works

                    $roles = EfrontLessonUser :: getLessonsRoles(true);
                    $smarty -> assign("T_ROLES_ARRAY", $roles);

                    isset($_GET['limit']) ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

                    if (isset($_GET['sort'])) {
                        isset($_GET['order']) ? $order = $_GET['order'] : $order = 'asc';
                        $courses = eF_multiSort($courses, $_GET['sort'], $order);
                    }
                    if (isset($_GET['filter'])) {
                        $courses = eF_filterData($courses, $_GET['filter']);
                    }
                    $smarty -> assign("T_COURSES_SIZE", sizeof($courses));
                    if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
                        isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
                        $courses = array_slice($courses, $offset, $limit);
                    }
                    //foreach ($courses as $key => $course) {
                    //$courses[$key]['languages_NAME'] = $languages[$course['languages_NAME']];
                    //}

                    $smarty -> assign("T_COURSES_DATA", $courses);

                    $smarty -> display($_SESSION['s_type'].'.tpl');
                    exit;
                }

                // We change a bit the following typical query to acquire the latest options values for the test - in case a threshold has been changed
                $result = EfrontCompletedTest::retrieveCompletedTest("completed_tests ct JOIN completed_tests_blob ctb on ct.id=ctb.completed_tests_ID JOIN tests t ON t.id = ct.tests_ID", "ct.*, ctb.test, t.options", "ct.status != 'deleted' and ct.id = '".$_GET['show_solved_test']."'");
                $completedTest = unserialize($result[0]['test']);

                // Take the most recent set general threshold for this test
                $temp = unserialize($result[0]['options']);
                $completedTest -> options['general_threshold'] = $temp['general_threshold'];

                $smarty -> assign("T_TEST_DATA",$completedTest);

                if ($_SESSION['s_type'] == 'administrator') {
                	$user = eF_getTableData("users", "*", "login = '".$_GET['user']."'");
                } else {
                	$user = eF_getTableData("users", "*", "login = '".$_SESSION['s_login']."'");
                }
                $smarty -> assign("T_USER_INFO", $user[0]);
                $analysisResults = $completedTest -> analyseSkillGapTest();

                if (!empty($analysisResults['testSkills'])) {
                    $smarty -> assign("T_SKILLSGAP",$analysisResults['testSkills']);
                }
                $smarty -> assign("T_MISSING_SKILLS_URL", $analysisResults['missingSkills']);
                $smarty -> assign("T_USER_LINK", array(array('text' => _EDIT, 'image' => "16x16/user.png", 'href' => $_SESSION['s_type'].".php?ctg=personal&user=".$_GET['user']."&op=profile")));

                $lessons_proposed = $analysisResults['lessons'];
                $courses_proposed = $analysisResults['courses'];
            } else {
            	$loadScripts[] = 'scriptaculous/excanvas';
            	$loadScripts[] = 'scriptaculous/flotr';
            	$loadScripts[] = 'scriptaculous/controls';
            	$loadScripts[] = 'includes/graphs';

                list($parentScores, $analysisCode) = $completedTest -> analyseTest();

                $smarty -> assign("T_CONTENT_ANALYSIS", $analysisCode);
                $smarty -> assign("T_TEST_DATA", $completedTest);

                $status = $completedTest -> getStatus($result[0]['users_LOGIN']);
                $smarty -> assign("T_TEST_STATUS", $status);

                try {
                	if (isset($_GET['ajax']) && $_GET['ajax'] == 'graph_test_analysis') {
                		$graph = new EfrontGraph();
                		$graph -> type = 'line';
                		$graph -> max  = '100';
                		$graph -> min  = '0';
                		$graph -> fill = false;

                		$count = 0;

                		foreach ($parentScores as $key => $value) {
                			if (isset($value['percentage'])) {
                				if (isset($_GET['entity']) && $_GET['entity']) {
                					if ($value['name']) {
		                				$graph -> meanValue[] = array($count, $completedTest -> completedTest['score']);
                						$graph -> data[]   = array($count, $value['this_percentage']);
	                					$graph -> xLabels[] = array($count++, $value['name']);
                					}
                				} else {
                					// Only the top level chapters should appear on the basic lesson test graph
                					if ($value['top_level'] == 1) {
		                				$graph -> meanValue[] = array($count, $completedTest -> completedTest['score']);
                						$graph -> data[]   = array($count, $value['percentage']);
                						$graph -> xLabels[] = array($count++, $value['name']);
                					}
                				}
                			}
                		}
                		//The lines below are used when the graph has a single value: It creates 2 additional values, in order to appear correctly (otherwise a single point appears, rather than a line)
                		if (sizeof($graph -> data) == 1) {
                			$graph -> meanValue = array(array(0, $graph -> meanValue[0][1]), array(1, $graph -> meanValue[0][1]), array(2, $graph -> meanValue[0][1]));
                			$graph -> data   	= array(array(0, $graph -> data[0][1]), array(1, $graph -> data[0][1]), array(2, $graph -> data[0][1]));
                			$graph -> xLabels 	= array(array(0, ''), array(1, $graph -> xLabels[0][1]), array(2, ''));
                		}

                		$graph -> xTitle = _UNIT;
						$graph -> yTitle = _SCORE;
						$graph -> label  = _SCOREINUNIT;
						$graph -> meanValueLabel  = _SCOREINTEST;

                		echo json_encode($graph);
                		exit;
                	}
                } catch (Exception $e) {
                	handleAjaxExceptions($e);
                }

            }
        }
