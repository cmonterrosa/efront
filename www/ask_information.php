<?php
session_cache_limiter('none');
session_start();

$path = "../libraries/";

include_once $path."configuration.php";

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

try {
	$languages = EfrontSystem::getLanguages(true);

	if (isset($_GET['lessons_ID']) && eF_checkParameter($_GET['lessons_ID'], 'id')) {
		$lesson            = new EfrontLesson($_GET['lessons_ID']);
		$lessonInformation = $lesson -> getInformation();


		//$lessonInformation['language'] = $languages[$lesson -> lesson['languages_NAME']];
		if ($lessonInformation['professors']) {
			foreach ($lessonInformation['professors'] as $value) {
				$professorsString[] = formatLogin($value['login']);
			}
			$lessonInformation['professors'] = implode(", ", $professorsString);
		}
		$lesson -> lesson['price'] ? $priceString = formatPrice($lesson -> lesson['price'], array($lesson -> options['recurring'], $lesson -> options['recurring_duration']), true) : $priceString = false;
		$lessonInformation['price_string'] = $priceString;
		
		if ($lesson->lesson['max_users']) {
			$lessonInformation['max_users'] = $lesson->lesson['max_users'];
			$lessonInformation['seats_remaining'] = $lesson -> lesson['max_users'] - sizeof($lesson -> getStudentUsers());
			$lessonInformation['seats_remaining'] >= 0 OR $lessonInformation['seats_remaining'] = 0;
		}
		//    if (!$lessonInformation['price']) {
		//        unset($lessonInformation['price_string']);
		//    }

		try {
			if ($_GET['from_course'] && eF_checkParameter($_GET['from_course'], 'id')) {
				$course   = new EfrontCourse($_GET['from_course']);
				$schedule = $course -> getLessonScheduleInCourse($lesson);
				$lessonInformation['from_timestamp'] = $schedule['start_date'];
				$lessonInformation['to_timestamp']   = $schedule['end_date'];
			}
		} catch (Exception $e) {};

		if ($lesson -> lesson['course_only']) {
			$lessonCourses = $lesson -> getCourses();
			if (!empty($lessonCourses)) {
				foreach ($lessonCourses as $value) {
					$lessonInformation['lesson_courses'][] = $value['name'];
				}
				$lessonInformation['lesson_courses'] = implode(", ", $lessonInformation['lesson_courses']);
			}
		}

		foreach ($lessonInformation as $key => $value) {
			if ($value) {
				$value = str_replace ("\n","<br />",  $value);
				switch ($key) {
					case 'language'			  : $GLOBALS['configuration']['onelanguage'] OR $tooltipInfo[] = '<div class = "infoEntry"><span>'._LANGUAGE."</span><span>: $languages[$value]</span></div>"; break;
					case 'professors'         : $tooltipInfo[] = '<div class = "infoEntry"><span>'._PROFESSORS."</span><span>: $value</span></div>";         break;
					case 'content'            : $tooltipInfo[] = '<div class = "infoEntry"><span>'._CONTENTUNITS."</span><span>: $value</span></div>";       break;
					case 'tests'              : EfrontUser::isOptionVisible('tests') ? $tooltipInfo[] = '<div class = "infoEntry"><span>'._TESTS."</span><span>: $value</span></div>" 	: null;           break;
					case 'projects'           : EfrontUser::isOptionVisible('projects') ? $tooltipInfo[] = '<div class = "infoEntry"><span>'._PROJECTS."</span><span>: $value</span></div>" 	: null;           break;
					case 'course_dependency'  : $tooltipInfo[] = '<div class = "infoEntry"><span>'._DEPENDSON."</span><span>: $value</span></div>";          break;
					case 'from_timestamp'     : $tooltipInfo[] = '<div class = "infoEntry"><span>'._AVAILABLEFROM."</span><span>: ".formatTimestamp($value, 'time_nosec')."</span></div>";break;
					case 'to_timestamp'       : $tooltipInfo[] = '<div class = "infoEntry"><span>'._AVAILABLEUNTIL."</span><span>: ".formatTimestamp($value, 'time_nosec')."</span></div>"; break;
					case 'general_description': $tooltipInfo[] = '<div class = "infoEntry"><span>'._DESCRIPTION."</span><span>: $value</span></div>"; break;
					case 'assessment'         : $tooltipInfo[] = '<div class = "infoEntry"><span>'._ASSESSMENT."</span><span>: $value</span></div>";         break;
					case 'objectives'         : $tooltipInfo[] = '<div class = "infoEntry"><span>'._OBJECTIVES."</span><span>: $value</span></div>";         break;
					case 'lesson_topics'      : $tooltipInfo[] = '<div class = "infoEntry"><span>'._LESSONTOPICS."</span><span>: $value</span></div>";       break;
					case 'resources'          : $tooltipInfo[] = '<div class = "infoEntry"><span>'._RESOURCES."</span><span>: $value</span></div>";          break;
					case 'other_info'         : $tooltipInfo[] = '<div class = "infoEntry"><span>'._OTHERINFO."</span><span>: $value</span></div>";          break;
					case 'price_string'       : !$lesson -> lesson['course_only'] ? $tooltipInfo[] = '<div class = "infoEntry"><span>'._PRICE."</span><span>: $value</span></div>" : null; break;
					case 'lesson_courses'     : $tooltipInfo[] = '<div class = "infoEntry"><span>'._PARTOFCOURSES."</span><span>: $value</span></div>"; break;
					case 'max_users'    	  : $tooltipInfo[] = '<div class = "infoEntry"><span>'._MAXIMUMUSERS."</span><span>: $value</span></div>";$tooltipInfo[] = '<div class = "infoEntry"><span>'._SEATSREMAINING."</span><span>: ".$lessonInformation['seats_remaining']."</span></div>";  break;
					default: break;
				}
			}
		}
		if ($string = implode("", $tooltipInfo)) {
			echo '<html '.($GLOBALS['rtl'] ? 'dir = "rtl"' : '').' >'.$string.'</html>';
		} else {
			echo _NODATAFOUND;
		}
	}
	
	if (isset($_GET['courses_ID']) && eF_checkParameter($_GET['courses_ID'], 'id') && $_GET['type'] == 'branches') {
		$result = eF_getTableDataFlat("module_hcd_course_to_branch mb, module_hcd_branch b", "mb.branches_ID, b.name", "b.branch_ID=mb.branches_ID and mb.courses_ID=".$_GET['courses_ID']);
		$tooltipInfo = '<div class = "infoEntry"><span>'.implode(", ", $result['name'])."</span><span></span></div>";
		echo $tooltipInfo;
		exit;
				
	}

	if (isset($_GET['courses_ID']) && eF_checkParameter($_GET['courses_ID'], 'id')) {
		$course            = new EfrontCourse($_GET['courses_ID']);
		$courseInformation = $course -> getInformation();

		if ($courseInformation['professors']) {
			foreach ($courseInformation['professors'] as $value) {
				$professorsString[] = formatLogin($value['login']);
			}
			$courseInformation['professors'] = implode(", ", $professorsString);
		}

		$course -> course['price'] ? $priceString = formatPrice($course -> course['price'], array($course -> options['recurring'], $course -> options['recurring_duration']), true) : $priceString = false;
		$courseInformation['price_string'] = $priceString;
		if ($course->course['max_users']) {
			$courseInformation['max_users'] = $course->course['max_users'];
			$courseInformation['seats_remaining'] = $courseInformation['max_users'] - sizeof($course -> getStudentUsers());
			$courseInformation['seats_remaining'] >= 0 OR $courseInformation['seats_remaining'] = 0;
		}
		foreach ($courseInformation as $key => $value) {
			if ($value) {
				$value = str_replace ("\n","<br />",  $value);
				switch ($key) {
					case 'language'			  : $GLOBALS['configuration']['onelanguage'] OR $tooltipInfo[] = '<div class = "infoEntry"><span>'._LANGUAGE."</span><span>: $languages[$value]</span></div>"; break;
					case 'professors'         : $tooltipInfo[] = '<div class = "infoEntry"><span>'._PROFESSORS."</span><span>: $value</span></div>";         break;
					case 'lessons_number'     : $tooltipInfo[] = '<div class = "infoEntry"><span>'._LESSONS."</span><span>: $value</span></div>";            break;
					case 'instances'     	  : $tooltipInfo[] = '<div class = "infoEntry"><span>'._COURSEINSTANCES."</span><span>: $value</span></div>";    break;
					case 'general_description': $tooltipInfo[] = '<div class = "infoEntry"><span>'._DESCRIPTION."</span><span>: $value</span></div>"; break;
					case 'assessment'         : $tooltipInfo[] = '<div class = "infoEntry"><span>'._ASSESSMENT."</span><span>: $value</span></div>";         break;
					case 'objectives'         : $tooltipInfo[] = '<div class = "infoEntry"><span>'._OBJECTIVES."</span><span>: $value</span></div>";         break;
					case 'lesson_topics'      : $tooltipInfo[] = '<div class = "infoEntry"><span>'._COURSETOPICS."</span><span>: $value</span></div>";       break;
					case 'resources'          : $tooltipInfo[] = '<div class = "infoEntry"><span>'._RESOURCES."</span><span>: $value</span></div>";          break;
					case 'other_info'         : $tooltipInfo[] = '<div class = "infoEntry"><span>'._OTHERINFO."</span><span>: $value</span></div>";          break;
					case 'price_string'       : $tooltipInfo[] = '<div class = "infoEntry"><span>'._PRICE."</span><span>: $value</span></div>";              break;
					case 'max_users'       	  : $tooltipInfo[] = '<div class = "infoEntry"><span>'._MAXIMUMUSERS."</span><span>: $value</span></div>";$tooltipInfo[] = '<div class = "infoEntry"><span>'._SEATSREMAINING."</span><span>: ".$courseInformation['seats_remaining']."</span></div>";              break;
					default: break;
				}
			}
		}
		
		if ($course -> course['depends_on']) {
			try {
				$dependsOn = new EfrontCourse($course -> course['depends_on']);
				$tooltipInfo[] = '<div class = "infoEntry"><span>'._DEPENDSON."</span><span>: ".$dependsOn->course['name']."</span></div>";
			} catch (Exception $e) {}
		}

		if ($string = implode("", $tooltipInfo)) {
			echo $string;
		} else {
			echo _NODATAFOUND;
		}

	}

	// For eFront social
	if (isset($_GET['common_lessons']) && isset($_GET['user1']) && isset($_GET['user2']) && eF_checkParameter($_GET['user1'], 'login') && eF_checkParameter($_GET['user2'], 'login')) {
		$user1 = EfrontUserFactory::factory($_GET['user1']);
		if ($user1->getType() != "administrator") {
			$common_lessons = $user1 -> getCommonLessons($_GET['user2']);
			// pr($common_lessons);
			foreach ($common_lessons as $id => $lesson) {
				if (strlen($lesson['name'])>25) {
					$lesson['name'] = substr($lesson['name'],0,22) . "...";
				}
				$tooltipInfo[] = '<div class = "infoEntry"><span>'.$lesson['name']."</span><span></span></div>";
			}

			if ($string = implode("", $tooltipInfo)) {
				echo $string;
			} else {
				echo _NODATAFOUND;
			}
		} else {
			echo _NODATAFOUND;
		}
	}


	if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
		if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD

			if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
				// For jobs

				if (isset($_GET['branch_ID']) && isset($_GET['job_description']) ) {
					if (eF_checkParameter($_GET['branch_ID'], 'id') && eF_checkParameter($_GET['job_description'], 'text')) {
						$job = new EfrontJob(array("branch_ID" => $_GET['branch_ID'], "job_description" => $_GET['job_description']));
						echo '<div class = "infoEntry"><span><b>'._JOBANALYTICALDESCRIPTION . "</b>: " . $job -> job['job_role_description']."</span><span></span></div>";
					} else {
						echo _JOBDOESNOTEXIST;
					}
				}
			} #cpp#endif

			$tooltipInfo = array();
			if (isset($_GET['users_LOGIN'])  && eF_checkParameter($_GET['users_LOGIN'], 'login')) {
				$user = EfrontUserFactory :: factory($_GET['users_LOGIN']);
				if ($_GET['type'] == 'course_status') {
					$constraints = array('archive' => false, 'active' => true, 'return_objects' => false);
					$constraints['required_fields'] = array('active_in_course', 'user_type', 'completed', 'to_timestamp', 'score');
					$courses		   = $user -> getUserCoursesAggregatingResults($constraints);

					$coursesTooltipInfo['completed'] = $coursesTooltipInfo['incomplete'] = array();
					foreach($courses as $key => $course) {
						$course['completed'] ? $idx = 'completed' : $idx = 'incomplete';
						$coursesTooltipInfo[$idx][] = '<div class = "infoEntry">
											<span>'.$course['name']." (".formatTimestamp($course['active_in_course']).")</span>
											<span>: ".($course['completed'] ? _COMPLETED.' '._ON.' '.formatTimestamp($course['to_timestamp']) : _NOTCOMPLETED)."</span></div>";

					}

					if (sizeof($coursesTooltipInfo['incomplete']) > 0) {
						$coursesTooltipInfo['incomplete'] = array_merge(array('<div class = "infoEntry"><span>'._INCOMPLETECOURSES.':</span></div>'), $coursesTooltipInfo['incomplete'], array('<br/>'));
					}
					if (sizeof($coursesTooltipInfo['completed']) > 0) {
						$coursesTooltipInfo['completed']  = array_merge(array('<div class = "infoEntry"><span>'._COMPLETEDCOURSES.':</span></div>'), $coursesTooltipInfo['completed'], array('<br/>'));
					}
					$coursesTooltipInfo = array_merge($coursesTooltipInfo['completed'], $coursesTooltipInfo['incomplete']);

					$lessons = $user -> getUserLessons();
					$lessonsTooltipInfo['completed'] = $lessonsTooltipInfo['incomplete'] = array();
					foreach($lessons as $key => $lesson) {
						if (!$lesson -> lesson['course_only'] && $lesson->lesson['active']) {
							$lesson -> lesson['completed'] ? $idx = 'completed' : $idx = 'incomplete';
							$lessonsTooltipInfo[$idx][] = '<div class = "infoEntry">
												<span>'.$lesson -> lesson['name']." (".formatTimestamp($lesson -> lesson['active_in_lesson']).")</span>
												<span>: ".($lesson -> lesson['completed'] ? _COMPLETED.' '._ON.' '.formatTimestamp($lesson -> lesson['timestamp_completed']) : _NOTCOMPLETED)."</span></div>";
						}
					}
					if (sizeof($lessonsTooltipInfo['completed']) > 0) {
						$lessonsTooltipInfo['completed']  = array_merge(array('<div class = "infoEntry"><span>'._COMPLETEDLESSONS.':</span></div>'), $lessonsTooltipInfo['completed'], array('<br/>'));
					}
					if (sizeof($lessonsTooltipInfo['incomplete']) > 0) {
						$lessonsTooltipInfo['incomplete'] = array_merge(array('<div class = "infoEntry"><span>'._INCOMPLETELESSONS.':</span></div>'), $lessonsTooltipInfo['incomplete'], array('<br/>'));
					}
					$lessonsTooltipInfo = array_merge($lessonsTooltipInfo['completed'], $lessonsTooltipInfo['incomplete']);

					$tooltipInfo = array_merge($coursesTooltipInfo, $lessonsTooltipInfo);
					echo implode("", $tooltipInfo);
				} else {
					$roles = EfrontUser :: getRoles(true);
					$user -> user['user_types_ID'] ? $userType = $roles[$user -> user['user_types_ID']] : $userType = $roles[$user -> user['user_type']];
					$tooltipInfo[] = '<div class = "infoEntry"><span>'._USER."</span><span>: ".formatLogin($user -> user['login'])."</span></div>";
					$tooltipInfo[] = '<div class = "infoEntry"><span>'._LANGUAGE."</span><span>: ".$languages[$user -> user['languages_NAME']]."</span></div>";
					$tooltipInfo[] = '<div class = "infoEntry"><span>'._USERTYPE."</span><span>: ".$userType."</span></div>";
					$tooltipInfo[] = '<div class = "infoEntry"><span>'._EMAIL."</span><span>: ".$user -> user['email']."</span></div>";
					if ($user -> user['user_type'] != 'administrator') {
						$constraints = array('archive' => false, 'active' => true, 'return_objects' => false);
						$constraints['required_fields'] = array('active_in_course', 'user_type', 'completed');
						$courses		   = $user -> getUserCoursesAggregatingResults($constraints);
						$totalCourses	   = sizeof($courses);
						$incompleteCourses = 0;
						foreach($courses as $key => $course) {
							$course['completed'] OR $incompleteCourses++;
						}
						if ($totalCourses) {
							$tooltipInfo[] = '<div class = "infoEntry">
							<span>'._COURSESINCOMPLETETOTAL."</span><span>: ".$incompleteCourses."/".$totalCourses."</span></div>";
						}
					}
					if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE

						$branchesTree = new EfrontBranchesTree();
						$pathStrings  = $branchesTree -> toPathString();
						$jobs 	  = $user -> aspects['hcd'] -> getJobs();
						$branches = $user -> aspects['hcd'] -> getBranches();

						foreach ($jobs as $value) {
							$tooltipInfo[] = '
	            		<div class = "infoEntry">
	            			<span>'._JOBDESCRIPTION.'</span><span>: '.$value['description'].' <b>('.(isset($branches['supervisor'][$value['branch_ID']]) ? _SUPERVISOR : _USER).')</b></span>
	            			<span> '._ATBRANCH.': </span><span style = "font-weight:bold">'.$pathStrings[$value['branch_ID']].'</span>
	            		</div>
	            		';
						}

						$supervisors = array();
						foreach ($user -> aspects['hcd'] -> getSupervisors() as $value) {
							$supervisors[] = formatLogin($value);
						}
						if (!empty($supervisors)) {
							$tooltipInfo[] = '<div class = "infoEntry"><span>'._SUPERVISORS."</span><span>: ".implode(", ", $supervisors)."</span></div>";
						}

						$skills = $user -> aspects['hcd'] -> getSkills();
						foreach ($skills as $value) {
							$tooltipInfo[] = '
	            		<div class = "infoEntry">
	            			<span>'._SKILL.'</span><span>: '.$value['description'].($value['specification'] ? ' <b>('.$value['specification'].')</b>' : '').'</span>
	            			<span> '._WITHSCORE.': </span><span style = "font-weight:bold">'.formatScore($value['score']).'%</span>
	            		</div>
	            		';
						}
					} #cpp#endif
					echo implode("", $tooltipInfo);
				}
			}
			
		}  #cpp#endif
	}#cpp#endif

} catch (Exception $e) {
	echo ($e -> getMessage().' ('.$e -> getCode().')');		//No ajax error handling here, since we want the info to appear in the popup
}
?>