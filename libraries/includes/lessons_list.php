<?php

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}
$loadScripts[] = 'includes/lessons_list';
$loadScripts[] = 'includes/catalog';
try {
	if (isset($_GET['op']) && $_GET['op'] == 'tests') {
		require_once("tests/show_skill_gap_tests.php");

	} elseif (isset($_GET['export']) && $_GET['export'] == 'rtf') {
		require_once("rtf_export.php");

	} elseif (isset($_GET['export']) && $_GET['export'] == 'xml') {
		require_once("xml_export.php");

	} elseif (isset($_GET['course'])) {		
		$currentCourse = new EfrontCourse($_GET['course']);
		$result 	   = eF_getTableData("users_to_courses", "user_type", "users_LOGIN='".$currentUser -> user['login']."' and courses_ID=".$currentCourse -> course['id']);
		
		if (empty($result) || $roles[$result[0]['user_type']] != 'professor') {
			throw new Exception(_UNAUTHORIZEDACCESS);
		}
		$currentUser -> applyRoleOptions($result[0]['user_type']);
		$baseUrl       = 'ctg=lessons&course='.$currentCourse -> course['id'];
		$smarty -> assign("T_BASE_URL", $baseUrl);
		$smarty -> assign("T_CURRENT_COURSE", $currentCourse);

		require_once 'course_settings.php';
	} elseif (isset($_GET['op']) && $_GET['op'] == 'search') {
		require_once "module_search.php";

	} elseif (isset($_GET['catalog'])) {
		require_once "catalog_page.php";

	} else {
		$myCoursesOptions = array();

		$directionsTree = new EfrontDirectionsTree();

		$options = array('noprojects' => 1, 'notests' => 1);
		
		$userLessons = $currentUser -> getUserStatusInLessons(false, true);
		
		foreach ($userLessons as $key => $lesson) {
			if (!$lesson -> lesson['active']) {
				unset($userLessons[$key]);
			}
		}

		/*
		 $userLessonProgress = EfrontStats :: getUsersLessonStatus($userLessons, $currentUser -> user['login'], $options);
		 $userLessons        = array_intersect_key($userLessons, $userLessonProgress); //Needed because EfrontStats :: getUsersLessonStatus might remove automatically lessons, based on time constraints
		 */
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$currentEmployee = $currentUser -> aspects['hcd'];
			$_SESSION['employee_type'] = $currentEmployee -> getType();

			if ($currentEmployee -> isSupervisor()) {
				$constraints    = array('archive' => false, 'active' => true);
				$pendingCourses = EfrontCourse :: getCoursesWithPendingUsersForSupervisor($constraints, $currentUser -> user['login']);
				if ($pendingCourses) {
					$myCoursesOptions[]  = array('text' => _SUPERVISORAPPROVAL, 'image' => "32x32/success.png", 'href' => "javascript:void(0)", 'onclick' => "eF_js_showDivPopup(event, '"._SUPERVISORAPPROVAL."', 2, 'supervisor_approvals_list')");
					$smarty -> assign("T_SUPERVISOR_APPROVALS", $pendingCourses);
				}
				if (isset($_GET['ajax']) && $_GET['ajax'] == 'approval') {
					try {
						$course = new EfrontCourse($_GET['course_id']);
						$course -> confirm($_GET['users_login']);
						echo json_encode(array('status' => 1));
						exit;
					} catch (Exception $e) {
						handleAjaxExceptions($e);
					}
				} elseif (isset($_GET['ajax']) && $_GET['ajax'] == 'cancel') {
					try {
						$course = new EfrontCourse($_GET['course_id']);
						$course -> removeUsers($_GET['users_login']);
						echo json_encode(array('status' => 1));
						exit;
					} catch (Exception $e) {
						handleAjaxExceptions($e);
					}
				}
				}
		} #cpp#endif

		if ($currentUser -> coreAccess['dashboard'] != 'hidden') {
			$myCoursesOptions[]  = array('text' => _DASHBOARD, 'image' => "32x32/user.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=personal");
		}

		$constraints = array('archive' => false, 'active' => true, 'sort' => 'name');
		$userCourses = $currentUser -> getUserCourses($constraints);

		foreach ($userCourses as $key => $course) {
			if ($course -> course['start_date'] && $course -> course['start_date'] > time()) {
				$course -> course['remaining'] = null;
			} elseif ($course -> course['end_date'] && $course -> course['end_date'] < time()) {
				$course -> course['remaining'] = 0;
			} else if ($course -> options['duration'] && $course -> course['active_in_course']) {
				if ($course -> course['active_in_course'] < $course -> course['start_date']) {
					$course -> course['active_in_course'] = $course -> course['start_date'];
				}
				$course -> course['remaining'] = $course -> course['active_in_course'] + $course -> options['duration']*3600*24 - time();
				if ($course -> course['end_date'] && $course -> course['end_date'] < $course -> course['active_in_course'] + $course -> options['duration']*3600*24) {
					$course -> course['remaining'] = $course -> course['end_date'] - time();
				}
			} else {
				$course -> course['remaining'] = null;
			}
			//Check whether the course registration is expired. If so, set $value['active_in_course'] to false, so that the effect is to appear disabled
			if ($course -> course['duration'] && $course -> course['active_in_course'] && $course -> course['duration'] * 3600 * 24 + $course -> course['active_in_course'] < time()) {
				$course -> archiveCourseUsers($course -> course['users_LOGIN']);
			}

			if ((!$currentUser -> user['user_types_ID'] && $course -> course['user_type'] != $currentUser -> user['user_type']) || ($currentUser -> user['user_types_ID'] && $course -> course['user_type'] != $currentUser -> user['user_types_ID'])) {
				$course -> course['different_role'] = 1;
			}
			$userCourses[$key] = $course;
		}

		$options      = array('lessons_link' 		=> '#user_type#.php?lessons_ID=',
                              'courses_link' 		=> $roles[$course -> course['user_type']] == 'professor' ? true : false,
            				  'catalog'		 		=> false,
							  'only_progress_link' 	=> true,
							  'collapse' 			=> $GLOBALS['configuration']['collapse_catalog']);

		foreach ($loadedModules as $module) {
			$module->onBeforeShowCoursesTree($userLessons, $userCourses, $userProgress);
		}
	
		if (sizeof ($userLessons) > 0 || sizeof($userCourses) > 0) {	
			$smarty -> assign("T_DIRECTIONS_TREE", $directionsTree -> toHTML(false, $userLessons, $userCourses, $userProgress, $options));
		}

		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				if (EfrontUser::isOptionVisible('skillgaptests')) {
					// Find all unsolved user skillgap tests
					$found_unsolved = 0;
					if ($_student_) {
						$userSkillgapTests = $currentUser -> getSkillgapTests();
						foreach($userSkillgapTests as $skid => $skillGap) {
							if (!$skillGap['solved']) {
								//unset($userSkillgapTests[$skid]);
								$found_unsolved = 1;
							}
						}
		
						if (!empty($userSkillgapTests)) {
							$myCoursesOptions[]  = array('text' => $found_unsolved ? _NEWSKILLGAPTESTS : _SKILLGAPTESTS, 'image' => "32x32/skill_gap.png", 'href' => "student.php?ctg=lessons&op=tests");
						}
					}
				}
			} #cpp#endif
		} #cpp#endif

		$innertable_modules = array();
		$side_innertable_modules = array();
		foreach ($loadedModules as $module) {
			unset($InnertableHTML);
			unset($side_InnertableHTML);
	 			$centerLinkInfo = $module -> getCenterLinkInfo();
				$InnertableHTML = $module -> getCatalogModule();
				$InnertableHTML ? $module_smarty_file = $module -> getCatalogSmartyTpl() : $module_smarty_file = false;
				$side_InnertableHTML = $module -> getSideCatalogModule();
				$side_InnertableHTML ? $module_side_smarty_file = $module -> getSideCatalogSmartyTpl() : $module_side_smarty_file = false;
				

			// If the module has a lesson innertable
			if ($InnertableHTML) {
				// Get module html - two ways: pure HTML or PHP+smarty
				// If no smarty file is defined then false will be returned
				if ($module_smarty_file) {
					// Execute the php code -> The code has already been executed by above (**HERE**)
					// Let smarty know to include the module smarty file
					$innertable_modules[$module->className] = array('smarty_file' => $module_smarty_file);
				} else {
					// Present the pure HTML cod
					$innertable_modules[$module->className] = array('html_code' => $InnertableHTML);
				}
			}
			
			if ($side_InnertableHTML) {
				// Get module html - two ways: pure HTML or PHP+smarty
				// If no smarty file is defined then false will be returned
				if ($module_side_smarty_file) {
					// Execute the php code -> The code has already been executed by above (**HERE**)
					// Let smarty know to include the module smarty file
					$side_innertable_modules[$module->className] = array('smarty_file' => $module_side_smarty_file);
				} else {
					// Present the pure HTML cod
					$side_innertable_modules[$module->className] = array('html_code' => $side_InnertableHTML);
				}
			}
			
		}

		if (!empty($innertable_modules)) {
			$smarty -> assign("T_INNERTABLE_MODULES", $innertable_modules);
		}
		
		if (!empty($side_innertable_modules)) {
			$smarty -> assign("T_SIDE_INNERTABLE_MODULES", $side_innertable_modules);
		}
	
		
		
		if ($GLOBALS['configuration']['insert_group_key'] && (!isset($currentUser -> coreAccess['insert_group_key']) || $currentUser -> coreAccess['insert_group_key'] != 'hidden')) {
			$myCoursesOptions[]  = array('text' => _ENTERGROUPKEY, 'image' => "32x32/key.png", 'href' => "javascript:void(0)", 'onclick' => "eF_js_showDivPopup(event, '"._ENTERGROUPKEY."', 0, 'group_key_enter')");
		}
		if ($GLOBALS['configuration']['lessons_directory']) {
			$myCoursesOptions[]  = array('text' => _COURSECATALOG, 'image' => "32x32/catalog.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=lessons&catalog=1");
		}
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			if ($currentUser->aspects['hcd']->isSupervisor() || EfrontUser::isOptionVisible('show_organization_chart')) {
				if (!isset($currentUser -> coreAccess['organization']) || $currentUser -> coreAccess['organization'] != 'hidden') {
					$myCoursesOptions[]  = array('text' => _ORGANIZATION, 'image' => "32x32/enterprise.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=module_hcd");
				}
			}
		} #cpp#endif

		if ((!isset($currentUser -> coreAccess['personal_messages']) || $currentUser -> coreAccess['personal_messages'] != 'hidden') && EfrontUser::isOptionVisible('messages')) {
			$myCoursesOptions[] = array('text' => _MESSAGES, 'image' => "32x32/mail.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=messages");
		}
		if (EfrontUser::isOptionVisible('statistics')) {
			$myCoursesOptions[] = array('text' => _STATISTICS, 'image' => "32x32/reports.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=statistics");
		}
		if (EfrontUser::isOptionVisible('forum')) {
			$myCoursesOptions[] = array('text' => _FORUM, 'image' => "32x32/forum.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=forum");
		}
		if (EfrontUser::isOptionVisible('calendar')) {
        	$myCoursesOptions[] = array('text' => _CALENDAR, 'image' => "32x32/calendar.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=calendar");
		}
		if (EfrontUser::isOptionVisible('professor_courses') && $_SESSION['s_type'] == 'professor') {
			if ($currentUser -> coreAccess['professor_courses'] != 'hidden') {	
				$myCoursesOptions[] = array('text' => _COURSES, 'image' => "32x32/courses.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=courses");
				$myCoursesOptions[] = array('text' => _LESSONS, 'image' => "32x32/lessons.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=professor_lessons");
			}
		}
		
		foreach ($loadedModules as $module) {
			if ($linkInfo = $module->getToolsLinkInfo()) {
				$myCoursesOptions[] = array('text' => $linkInfo['title'], 'image' => eF_getRelativeModuleImagePath($linkInfo['image']), 'href' => $linkInfo['link']);
			}
		}
		
		$smarty -> assign("T_LAYOUT_CLASS", $currentTheme -> options['toolbar_position'] == "left" ? "hideRight" : "hideLeft");    //Whether to show the sidemenu on the left or on the right
		$smarty -> assign("T_COURSES_LIST_OPTIONS", $myCoursesOptions);
	}
} catch (Exception $e) {
	handleNormalFlowExceptions($e);
}

