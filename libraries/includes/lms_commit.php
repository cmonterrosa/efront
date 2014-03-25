<?php

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}


try {
	//pr($_POST);pr($_GET);
	unset($_POST['_']);

	//id and credit are not stored in any table
	$credit = true;
	if ($_POST['credit'] == 'no-credit') {
		$credit = false;
	}
	unset ($_POST['credit']);
	//unset ($_POST['session_time']);
	unset ($_POST['id']);
	unset ($_POST['popup']);

	//used only in scorm_data
	$fields['timestamp']  = time();
	foreach ($_POST as $key => $value) {													   //Store POST parameters in a variable, so that they may be inserted in a database tabl
		$fields[$key] = $value;
	}
	$fields['users_LOGIN'] = $_SESSION['s_login'];											 //The current user
	if (!isset($fields['content_ID'])) {
		exit;
	}

	if (strtolower($fields['completion_status']) == 'passed' ||
			strtolower($fields['completion_status']) == 'completed' ||
			strtolower($fields['lesson_status']) == 'passed' ||
			strtolower($fields['lesson_status']) == 'completed') {
		$seenUnit = true;
	} else {
		$seenUnit = false;
	}	
	if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
		if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD

			if ($_GET['scorm_version'] == '2004') {

				$currentContent	= new EfrontContentTree($_SESSION['s_lessons_ID']);
				$scoBranch		= array();

				//Take the branch and initialize scorm tree
				$scoBranch[$_SESSION['package_ID']] = $currentContent -> tree -> offsetGet($_SESSION['package_ID']);
				$scoContent							= new EfrontContentTreeSCORM($scoBranch, $fields['content_ID']);

				$currentNode	  = $scoContent -> flatTree[$fields['content_ID']];
				$primaryObjective = $scoContent -> objectives -> get_primary_objective($fields['content_ID']);

				if ($fields['navigation'] != 'abandonAll' && $fields['navigation'] != 'abandon') {
					//Update shared data
					foreach(json_decode($fields['shared_data'], true) as $key => $value) {
						$updateData = array (
						'target_ID' => $value['id']['value'],
						'store'	 => $value['store']['value']
						);
						$scoContent -> maps -> updateMapInfo($updateData['target_ID'], $updateData);
					}

					//Update scorm sequencing information
					eF_insertOrupdateTableData("scorm_sequencing_comments_from_learner",
					array(
						'content_ID'  => $fields['content_ID'],
						'users_LOGIN' => $_SESSION['s_login'],
						'data'		=> $fields['comments_from_learner']),
					"content_ID = '".$fields['content_ID']."' AND users_LOGIN = '".$_SESSION['s_login']."'");

					eF_insertOrupdateTableData("scorm_sequencing_comments_from_lms",
					array(
						'content_ID'  => $fields['content_ID'],
						'data'		=> $fields['comments_from_learner']),
					"content_ID = '".$fields['content_ID']."'");

					eF_insertOrupdateTableData("scorm_sequencing_interactions",
					array (
						'content_ID'  => $fields['content_ID'],
						'users_LOGIN' => $_SESSION['s_login'],
						'data'		=> $fields['interactions']),
					"content_ID = '".$fields['content_ID']."' AND users_LOGIN = '".$_SESSION['s_login']."'");

					eF_insertOrupdateTableData("scorm_sequencing_learner_preferences",
					array(
						'content_ID'  => $fields['content_ID'],
						'users_LOGIN' => $_SESSION['s_login'],
						'data'		=> $fields['learner_preferences']),
					"content_ID = '" . $fields['content_ID'] ."' AND users_LOGIN = '".$_SESSION['s_login']."'");

					//Update objectives and attempts
					$cnt = 0;
					foreach(json_decode($fields['objectives'], true) as $key => $value) {
						$updateData = array (
						'raw_score'			=> $value['score']['raw']['value'],
						'max_score'			=> $value['score']['max']['value'],
						'min_score'			=> $value['score']['min']['value'],
						'score_scaled'		=> $value['score']['scaled']['value'],
						'completion_status'	=> $value['completion_status']['value'],
						'progress_measure'	=> $value['progress_measure']['value'],
						'success_status'	=> $value['success_status']['value'],
						'description'		=> $value['description']['value']
						);

						//Deal with sequencing impacts
						//Progress Measure
						if ($updateData['progress_measure']) {
							$scoContent -> objectives -> update_objective_info($value['id']['value'], $fields['content_ID'], array('attempt_completion_amount_status' => 'true', 'attempt_completion_amount' => $updateData['progress_measure']));
						}
						//Score Scaled
						if ($updateData['score_scaled']) {
							$scoContent -> objectives -> update_objective_info($value['id']['value'], $fields['content_ID'], array('objective_measure_status' => 'true', 'objective_normalized_measure' => $updateData['score_scaled']));
						}

						$objInfo = $scoContent -> objectives -> get_objective_info($value['id']['value'], $fields['content_ID'], array('attempt_progress_status' => 'false'));
						$obj	 = $scoContent -> objectives -> get_objective($value['id']['value'], $fields['content_ID']);

						if ($updateData['success_status']) {
							switch($updateData['success_status']) {
								case 'unknown':
									$tempData = array('objective_progress_status' => 'false', 'objective_satisfied_status' => 'false', 'reported_satisfied_status' => 1);
									break;
								case 'failed':
									$tempData = array('objective_progress_status' => 'true', 'objective_satisfied_status' => 'false', 'reported_satisfied_status' => 1);
									break;
								case 'passed':
									$tempData = array('objective_progress_status' => 'true', 'objective_satisfied_status' => 'true', 'reported_satisfied_status' => 1);
									break;
								default:
									$tempData = array();
									break;
							}
							$scoContent -> objectives -> update_objective_info($value['id']['value'], $fields['content_ID'], $tempData);
						}

						//Completion Status
						if ($updateData['completion_status']) {
							switch($updateData['completion_status']) {
								case 'unknown':
									$tempData = array('attempt_progress_status' => 'false', 'attempt_completion_status' => 'false', 'reported_completion_status' => 1);
									break;
								case 'incomplete':
									$tempData = array('attempt_progress_status' => 'true', 'attempt_completion_status' => 'false', 'reported_completion_status' => 1);
									break;
								case 'completed':
									$tempData = array('attempt_progress_status' => 'true', 'attempt_completion_status' => 'true', 'reported_completion_status' => 1);
									break;
								case 'not attempted':
									$tempData = array('attempt_progress_status' => 'true', 'attempt_completion_status' => 'false', 'not_attempted' => 1, 'reported_completion_status' => 1);
									break;
								default:
									$tempData = array('');
									break;
							}
							$scoContent -> objectives -> update_objective_info($value['id']['value'], $fields['content_ID'], $tempData);
						}

						//Unset values that have already been updated
						unset($updateData['success_status']);
						unset($updateData['completion_status']);
						unset($updateData['progress_measure']);
						unset($updateData['score_scaled']);

						//Update the rest
						$scoContent -> objectives -> update_objective_info($value['id']['value'], $fields['content_ID'], $updateData);
					}

					//Deal with core properties. These take precedence over anything else, so they must be put in the end
					if (isset($fields['progress_measure']) && $fields['progress_measure']!= "" ) {
						$scoContent -> objectives -> update_objective_info($primaryObjective['objective_ID'], $fields['content_ID'], array('attempt_completion_amount_status' => 'true', 'attempt_completion_amount' => $fields['progress_measure'], 'reported_progress_measure' => 1));
					}
					if (isset($fields['score_scaled']) && $fields['score_scaled']!= "" ) {
						$scoContent -> objectives -> update_objective_info($primaryObjective['objective_ID'], $fields['content_ID'], array('objective_measure_status' => 'true', 'objective_normalized_measure' => $fields['score_scaled']));
					}
					if (isset($fields['score']) && $fields['score']!= "") {
						$scoContent -> objectives -> update_objective_info($primaryObjective['objective_ID'], $fields['content_ID'], array('raw_score'=> $fields['score']));
					}
					if (isset($fields['minscore']) && $fields['minscore']!= "" ) {
						$scoContent -> objectives -> update_objective_info($primaryObjective['objective_ID'], $fields['content_ID'], array('min_score'=> $fields['minscore']));
					}
					if (isset($fields['maxscore']) && $fields['maxscore']!= "" ) {
						$scoContent -> objectives -> update_objective_info($primaryObjective['objective_ID'], $fields['content_ID'], array('max_score'=> $fields['maxscore']));
					}

					if (isset($fields['scorm_exit']) && $fields['scorm_exit'] == 'suspend') {
						$scoContent -> activity_state_information -> update($fields['content_ID'], array('is_suspended' => 'true'));
					}

					//Core objectives
					if (isset($fields['success_status'])) {
						switch($fields['success_status']) {
							case 'unknown':
								$tempData = array('objective_progress_status' => 'false', 'objective_satisfied_status' => 'false', 'reported_satisfied_status' => 1);
								break;
							case 'failed':
								$tempData = array('objective_progress_status' => 'true', 'objective_satisfied_status' => 'false', 'reported_satisfied_status' => 1);
								break;
							case 'passed':
								$tempData = array('objective_progress_status' => 'true', 'objective_satisfied_status' => 'true', 'reported_satisfied_status' => 1);
								break;
							default:
								$tempData = array();
								break;
						}
						$scoContent -> objectives -> update_objective_info($primaryObjective['objective_ID'], $fields['content_ID'], $tempData);
					}

					//Core objectives
					if (isset($fields['completion_status'])) {
						switch($fields['completion_status']) {
							case 'unknown':
								$tempData = array('attempt_progress_status' => 'false', 'attempt_completion_status' => 'false', 'reported_completion_status' => 1);
								break;
							case 'incomplete':
								$tempData = array('attempt_progress_status' => 'true', 'attempt_completion_status' => 'false', 'reported_completion_status' => 1);
								break;
							case 'completed':
								$tempData = array('attempt_progress_status' => 'true', 'attempt_completion_status' => 'true' , 'reported_completion_status' => 1);
								break;
							case 'not attempted':
								$tempData = array('attempt_progress_status' => 'true', 'attempt_completion_status' => 'false', 'not_attempted' => 1, 'reported_completion_status' => 1);
								break;
							default:
								$tempData = array();
								break;
						}
						$scoContent -> objectives -> update_objective_info($primaryObjective['objective_ID'], $fields['content_ID'], $tempData);
					}
				}




				//Unset all the values that have been processed so far
				unset($fields['objectives']);
				unset($fields['navigation']);
				unset($fields['completion_status']);
				unset($fields['success_status']);
				unset($fields['shared_data']);
				unset($fields['comments_from_lms']);
				unset($fields['comments_from_learner']);
				unset($fields['interactions']);
				unset($fields['learner_preferences']);
				unset($fields['score_scaled']);
				unset($fields['progress_measure']);
				unset($fields['finish']);

				/*
				 echo "
				 <html>
				 </html>
				 <body>
				 <script type = \"text/javascript\" src = \"js/scriptaculous/prototype.php\"> </script>
				 <script>
				 jsonString = '".json_encode(array($newPercentage, $newConditionsPassed, $newLessonPassed))."';

				 if (parent.$('tree_image_".$scoUnit['id']."')) {
				 parent.$('tree_image_".$scoUnit['id']."').src = 'images/drag-drop-tree/".$scoUnit['ctg_type'].($seenUnit ? '_seen' : '').".png';
				 }
				 if (parent.$('progress_bar')) {
				 parent.$('progress_bar').select('span.progressNumber')[0].update(parseFloat(jsonString.evalJSON()[0]) + '%');
				 parent.$('progress_bar').select('span.progressBar')[0].setStyle({width:parseFloat(jsonString.evalJSON()[0]) + 'px'});
				 parent.$('passed_conditions').update(parseInt(jsonString.evalJSON()[1]));
				 jsonString.evalJSON()[2] == true ? parent.$('lesson_passed').setStyle({color:'green'}) : parent.$('lesson_passed').setStyle({color:'red'});

				 }
				 </script>
				 </body>";
				 */

				//Persist computed values
				$scoContent -> objectives -> commit_objectives();
				$scoContent -> maps -> commit();
				//If tracked, store values
				//@todo: credit mode check here
				if ($currentNode['tracked'] == "true") {
					$scoContent -> shared_data -> commit();
				}

				$scoContent -> activity_state_information -> commit();
				$scoContent -> comments_from_lms -> commit();
				$scoContent -> comments_from_learner -> commit();


				$scormState = array();
				$trackActivityInfo = array();

				//Set completion icons
				if ($_POST['finish'] != 'true') {

					$scormState = $scoContent -> checkControlsValidy(true);

					foreach ($scoContent->flatTree as $key => $value) {
						$objInfo = $scoContent->objectives->get_objective_info(false, $key);

						//Completion Status
						if ($objInfo['attempt_progress_status'] == 'true' && $objInfo['attempt_completion_status'] == 'true') {
							$trackActivityInfo[$key]['completion_status'] = 'completed';
						} else if ($objInfo['attempt_progress_status'] == 'true' && $objInfo['attempt_completion_status'] == 'false') {
							$trackActivityInfo[$key]['completion_status'] = 'incomplete';
						} else {
							$trackActivityInfo[$key]['completion_status'] = 'not attempted';
						}

						//Success Status
						if ($objInfo['objective_progress_status'] == 'true' && $objInfo['objective_satisfied_status'] == 'true') {
							$trackActivityInfo[$key]['success_status'] = 'passed';
						} else if ($objInfo['objective_progress_status'] == 'true' && $objInfo['objective_satisfied_status'] == 'false') {
							$trackActivityInfo[$key]['success_status'] = 'failed';
						} else {
							$trackActivityInfo[$key]['success_status'] = 'unknown';
						}

					}
				}

				//$_POST instead of $fields because the latter is unset

				if ($_POST['finish'] == 'true' && isset($fields['scorm_exit']) && ($fields['scorm_exit'] == 'time-out' || $fields['scorm_exit'] == 'logout')) {
					$redirectTo = basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=exitAll&package_ID=".$_SESSION['package_ID'];
				} else {
					if ($_POST['finish'] == 'true') {
						$matches = split('[=\{\}]', $_POST['navigation']);
						if (sizeof($matches) == 4) {
							$iterator = new EfrontNodeFilterIterator(new RecursiveIteratorIterator($scoContent -> tree, RecursiveIteratorIterator :: SELF_FIRST));
							foreach ($iterator as $key => $value) {
								if (trim($value['identifier']) == $matches[2]) {
									$redirectTo = basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=".$matches[3]."&target=".$value['content_ID']."&package_ID=".$_SESSION['package_ID'];
									break;
								}
							}
						} else if ( $_POST['navigation'] != '') {
							$redirectTo = basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=". $_POST['navigation']."&package_ID=".$_SESSION['package_ID'];
						}
					} else {
						$redirectTo = '';
					}
				}

				$result = eF_getTableData("scorm_data_2004", "total_time,id", "content_ID=".$fields['content_ID']." AND users_LOGIN='".$fields['users_LOGIN']."'");
			}
		} #cpp#endif
	} #cpp#endif
	if ($_GET['scorm_version'] != '2004') {
		if ($fields['lesson_status'] == 'browsed') {
			$fields['lesson_status'] = 'completed';
		}
		$trackActivityInfo[$fields['content_ID']]['completion_status'] = strtolower($fields['lesson_status']);
		$trackActivityInfo[$fields['content_ID']]['success_status']	   = strtolower($fields['lesson_status']);

		unset($fields['objectives']);
		unset($fields['navigation']);
		unset($fields['completion_status']);
		unset($fields['success_status']);
		unset($fields['shared_data']);
		unset($fields['comments_from_lms']);
		unset($fields['comments_from_learner']);
		unset($fields['interactions']);
		unset($fields['learner_preferences']);
		unset($fields['score_scaled']);
		unset($fields['progress_measure']);
		unset($fields['finish']);

		$result = eF_getTableData("scorm_data", "total_time,id", "content_ID=".$fields['content_ID']." AND users_LOGIN='".$fields['users_LOGIN']."'");
	}
	


	$scoUser   = EfrontUserFactory :: factory($_SESSION['s_login'], false, 'student');
	$scoLesson = new EfrontLesson($_SESSION['s_lessons_ID']);
	$scoUnit   = new EfrontUnit($fields['content_ID']);

	if (sizeof($result) > 0) {													  //This means that the students re-enters the unit
		if (isset($fields['session_time']) && $fields['session_time']) {	  //Make sure that time is properly converted, for example 35+35 minutes become 1 hour 10 minutes, instead if 70 minutes
			$time_parts1 = explode(":", $result[0]['total_time']);
			$time_parts2 = explode(":", $fields['session_time']);
			$time_parts[0] = $time_parts1[0] + $time_parts2[0];
			$time_parts[1] = $time_parts1[1] + $time_parts2[1];
			$time_parts[2] = $time_parts1[2] + $time_parts2[2];
			//print_r($time_parts1);print_r($time_parts2);print_r($time_parts);
			$time_parts[1] = $time_parts[1] + floor($time_parts[2]/60);
			$time_parts[2] = fmod($time_parts[2], 60);
			$time_parts[0] = $time_parts[0] + floor($time_parts[1]/60);
			$time_parts[1] = fmod($time_parts[1], 60);

			$fields['total_time'] = sprintf("%04d",$time_parts[0]).":".sprintf("%02d",$time_parts[1]).":".sprintf("%05.2f",$time_parts[2]);
		}

		$doneContent = eF_getTableData("users_to_lessons", "done_content", "users_LOGIN='".$scoUser->user['login']."' and lessons_ID=".$scoLesson->lesson['id']);
		$doneContent = unserialize($doneContent[0]['done_content']);
		//pr($doneContent[$scoUnit['id']]);pr($scoUnit['options']['reentry_action']);exit;
		//If the user has passed this unit and we have selected that the reentry action will leave status unchanged, then switch to no-credit mode
		if (isset($doneContent[$scoUnit['id']]) && $scoUnit['options']['reentry_action'] && !$seenUnit) {
			$credit=false;
			$trackActivityInfo[$fields['content_ID']]['completion_status'] = 'completed';
			$trackActivityInfo[$fields['content_ID']]['success_status']	   = 'passed';
		}

		unset($fields['session_time']);
		if ($_GET['scorm_version'] == '2004') {
			eF_updateTableData("scorm_data_2004", $fields, "id=".$result[0]['id']);		//Update old values with new ones
		} elseif($credit) {
			eF_updateTableData("scorm_data", $fields, "id=".$result[0]['id']);		//Update old values with new ones
		}
	} else {

		$fields['total_time'] = $fields['session_time'];
		unset($fields['session_time']);

		if ($_GET['scorm_version'] == '2004') {
			$result = eF_insertTableData("scorm_data_2004", $fields);					  //Insert a new entry that relates the current user with this SCO
		} elseif ($credit) {
			$result = eF_insertTableData("scorm_data", $fields);					  //Insert a new entry that relates the current user with this SCO
		}
	}

	if ($credit && $seenUnit) {
		$scoUser -> setSeenUnit($scoUnit, $scoLesson, true);
	}

	$newUserProgress	 = EfrontStats :: getUsersLessonStatus($scoLesson, $scoUser -> user['login']);

	$newPercentage	   	 = $newUserProgress[$scoLesson -> lesson['id']][$scoUser -> user['login']]['overall_progress'];
	$newConditionsPassed = $newUserProgress[$scoLesson -> lesson['id']][$scoUser -> user['login']]['conditions_passed'];
	$newLessonPassed	 = $newUserProgress[$scoLesson -> lesson['id']][$scoUser -> user['login']]['lesson_passed'];
	//pr($trackActivityInfo);
	if ($scoLesson -> lesson['course_only']) {
		$res = eF_getTableData("users_to_courses","issued_certificate","courses_ID=".$_SESSION['s_courses_ID']." and users_LOGIN='".$_SESSION['s_login']."'");
		if ($res[0]['issued_certificate'] != "") {
			$courseCertified = true;
		}	
	}
	
	echo json_encode(array($newPercentage, $newConditionsPassed, $newLessonPassed, $scormState, $redirectTo, $trackActivityInfo,  $courseCertified));

} catch (Exception $e) {
	echo json_encode(array('error' => $e->getMessage()));
}

exit;
