<?php
if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD

		//This file cannot be called directly, only included.
		if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
			exit;
		}

		$loadScripts[] = 'includes/tests';

		if (isset($_GET['solve_test']) && eF_checkParameter($_GET['solve_test'], 'id')) {
			if (isset($_GET['confirm'])) {
				$form    = new HTML_QuickForm("test_form", "post", basename($_SERVER['PHP_SELF']).'?ctg=lessons&op=tests&solve_test='.$_GET['solve_test'].'&confirm=1', "", null, true);

				if ($form -> isSubmitted() && $form -> validate()) {

					// The skillgap test has been solved and submitted
					$result = EfrontCompletedTest::retrieveCompletedTest("completed_tests ct join completed_tests_blob ctb on ct.id=ctb.completed_tests_ID", "ct.*,ctb.test", "status != 'deleted' and tests_id = '".$_GET['solve_test']."' AND users_LOGIN = '".$currentUser -> user['login']."'");
					$testInstance = unserialize($result[0]['test']);

					$testString    = $testInstance -> toHTMLQuickForm($form);
					$testString    = $testInstance -> toHTML($testString, $remainingTime);

					$questions = $form -> exportValues('question');
					$questions= $form->getSubmitValue('question');

					$testInstance -> completedTest['status'] = 'completed';
					$testInstance -> complete($questions);

					eF_updateTableData("users_to_skillgap_tests", array("solved" => "1"), "tests_ID = '".$_GET['solve_test']."' AND users_LOGIN = '".$currentUser -> user['login']."'");

					// Check if you should automatically assign lessons and courses to the student
					if ($testInstance -> options['automatic_assignment']) {
						$analysisResults = $testInstance -> analyseSkillGapTest();

						foreach ($analysisResults['lessons'] as $lesson) {
							$currentUser -> addLessons($lesson['lesson_ID']);
						}
						foreach ($analysisResults['courses'] as $course) {
							$currentUser -> addCourses($course['courses_ID']);

						}
						eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=lessons&op=tests&message=". urlencode(_SKILLGAPTESTCOMPLETEDSUCCESSFULLYANDTHECORRESPONDING) . " " . sizeof($analysisResults['lessons'] ) . " " . _LESSONS . " " . _AND . " ". sizeof($analysisResults['courses']). " " . _COURSES . " " . _HAVEBEENASSIGNED . "&message_type=success");
					} else {
						eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=lessons&op=tests&message=". urlencode(_SKILLGAPTESTCOMPLETEDSUCCESSFULLY). ". " . urlencode(_YOURRESULTSHAVEBEENSENTTOYOURPROFESSORSWHOWILLASSIGNLESSONSACCORDINGTOYOURNEEDS) . "&message_type=success");
					}

					exit;
				}

				//HACK to remove incomplete tests
				eF_deleteTableData("completed_tests", "tests_id = '".$_GET['solve_test']."' AND users_LOGIN = '".$currentUser -> user['login']."'");
				$test   = new EfrontTest($_GET['solve_test']);
				$testInstance = $test -> start($currentUser -> user['login']);

				// Hard coded to disallow pause test
				$testInstance -> options['pause_test'] = 0;

				$testString    = $testInstance -> toHTMLQuickForm($form);
				$testString    = $testInstance -> toHTML($testString, $remainingTime);

				$form   -> addElement('hidden', 'time_start', $timeStart);                                       //This element holds the time the test started, so we know the remaining time even if the user left the system
				$form   -> addElement('submit', 'submit_test', _SUBMITTEST, 'class = "flatButton" onclick = "if (typeof(checkedQuestions) != \'undefined\' && (unfinished = checkQuestions())) return confirm(\''._YOUHAVENOTCOMPLETEDTHEFOLLOWINGQUESTIONS.': \'+unfinished+\'. '._AREYOUSUREYOUWANTTOSUBMITTEST.'\');"');
				if ($testInstance -> options['pause_test']) {
					$form -> addElement('submit', 'pause_test', _PAUSETEST, 'class = "flatButton"');
				}

				$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
				$form   -> accept($renderer);

				$smarty -> assign('T_TEST_FORM', $renderer -> toArray());
				//                        eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=lessons&op=tests&");
			} else {
				$form    = new HTML_QuickForm("test_form", "post", basename($_SERVER['PHP_SELF']).'?ctg=lessons&op=tests', "", null, true);
				$test   = new EfrontTest($_GET['solve_test']);
				$testInstance = $test;
				$test  -> getQuestions();                                    //This way the test's questions are populated, and we will be needing this information
				$testInstance -> options['random_pool'] && $testInstance -> options['random_pool'] >= sizeof($testIn) ? $questionsNumber = $testInstance -> options['random_pool'] : $questionsNumber = sizeof($testInstance -> questions);
				$smarty -> assign("T_SHOW_CONFIRMATION", true);
			}

			if (isset($_GET['ajax'])) {
				$testInstance -> handleAjaxActions();
			}

			//Calculate total questions. If it's already set, then we are visiting an unsolved test, and the questions number is already calculated (and may be different that the $testInstance -> questions size)
			if (!isset($questionsNumber)) {
				$questionsNumber = sizeof($testInstance -> questions);
			}

			//$smarty -> assign("T_REMAINING_TIME", $remainingTime);
			$smarty -> assign("T_TEST_QUESTIONS_NUM", $questionsNumber);
			$smarty -> assign("T_TEST_DATA", $testInstance);
			$smarty -> assign("T_TEST", $testString);
			$smarty -> assign("T_TEST_STATUS", $status);

		} else {
			$tests     = $currentUser -> getSkillgapTests();
			$test_array = array();
			foreach ($tests as $test) {
				if ($test['solved']) {
					$test = new EfrontTest($test['id']);
					$result = eF_getTableData("completed_tests", "id", "tests_ID=".$test->test['id']." and users_LOGIN='".$currentUser->user['login']."'");
					if ($test->options['student_results']) {
						$test_array[] = array('text' => $test->test['name'],  'image' => "32x32/success.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=tests&show_solved_test=".$result[0]['id']."&test_analysis=1&user=".$currentUser->user['login']);
					} else {
						$test_array[] = array('text' => $test->test['name'],  'image' => "32x32/success.png");						
					}
				} else {
					$test_array[] = array('text' => $test['name'],  'image' => "32x32/tests.png",   'href' => basename($_SERVER['PHP_SELF'])."?ctg=lessons&op=tests&solve_test=" . $test['id']);
				}

			}

			// Present a list of tests
			if (!empty($test_array)) {
				$smarty -> assign("T_TESTS", $test_array);

			}
		}


	} #cpp#endif
} #cpp#endif
