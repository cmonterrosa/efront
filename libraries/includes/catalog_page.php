<?php
//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

$directionsTree = new EfrontDirectionsTree();

$loadScripts[] = 'includes/catalog';
//The courses catalog
if (isset($_GET['ajax']) && $_GET['ajax'] == 'cart') {
	try {
		include "catalog.php";
	} catch (Exception $e) {
		header("HTTP/1.0 500 ");
		echo rawurlencode($e -> getMessage()).' ('.$e -> getCode().')';
	}
	exit;
}
/**Handles cart and catalog*/
include "catalog.php";

if (!isset($_GET['checkout'])) {
	//Get available languages
	$languages = array();

	foreach (EfrontSystem :: getLanguages() as $key => $value) {
		if ($value['active']) {
			$languages[$key] = $value['translation'];
		}
	}
	//ksort($languages);
	$smarty -> assign("T_LANGUAGES", $languages);

	if (isset($_GET['info_lesson'])) {
		$lesson     = new EfrontLesson($_GET['info_lesson']);
		$lesson -> lesson['price_string'] = formatPrice($lesson -> lesson['price'], array($lesson -> options['recurring'], $lesson -> options['recurring_duration']), true);
		$lesson -> lesson['num_students']    = sizeof($lesson -> getStudentUsers());
		$lesson -> lesson['seats_remaining'] = $lesson -> lesson['max_users'] - $lesson -> lesson['num_students'];
		$lesson -> lesson['seats_remaining'] >= 0 OR $lesson -> lesson['seats_remaining'] = 0;
		$smarty -> assign("T_LESSON", $lesson);

		$lessonInformation = $lesson -> getInformation();
		$content    = new EfrontContentTree($lesson);
		if (sizeof($content -> tree) > 0) {
			$smarty -> assign("T_CONTENT_TREE", $content -> toHTML(false, 'dhtml_content_tree', array('noclick' => 1, 'truncateNames' => EfrontUnit::MAXIMUM_NAME_LENGTH)));
		}
		$lessonInfo = new LearningObjectInformation(unserialize($lesson -> lesson['info']));
		$smarty -> assign("T_LESSON_INFO", $lessonInfo);
		$additionalInfo = $lesson -> getInformation();
		$smarty -> assign("T_ADDITIONAL_LESSON_INFO", $additionalInfo);

		if ($lesson -> lesson['course_only']) {
			$smarty -> assign("T_LESSON_COURSES", $lesson -> getCourses());
			if (isset($_GET['from_course']) && $_GET['from_course']) {
				$course = new EfrontCourse($_GET['from_course']);
				$smarty -> assign ("T_COURSE", $course);
				$smarty -> assign("T_HAS_COURSE", in_array($course -> course['id'], array_keys($userCourses)));
			}
		}
	} else if ($_GET['info_course']) {
		$course     = new EfrontCourse($_GET['info_course']);
		$course -> course['num_students']    = sizeof($course -> getStudentUsers());
		$course -> course['seats_remaining'] = $course -> course['max_users'] - $course -> course['num_students'];
		$course -> course['seats_remaining'] >= 0 OR $course -> course['seats_remaining'] = 0;
		$smarty -> assign("T_COURSE", $course);
		if ((isset($_SESSION['s_type']) && $_SESSION['s_type'] == 'administrator') || in_array($_SESSION['s_login'], array_keys($course -> getUsers()))) {
			$smarty -> assign("T_HAS_COURSE", true);
		}
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$result = eF_getTableData("module_hcd_course_to_branch mb, module_hcd_branch b", "mb.branches_ID, b.name", "mb.branches_ID=b.branch_ID and courses_ID=".$course -> course['id']);
			if (!empty($result)) {
				$course -> course['location'] = $result[0]['name'];
			}
		} #cpp#endif

		$lessons = $course -> getCourseLessons();	
		foreach ($lessons as $key => $lesson) {
			$content = new EfrontContentTree($lesson);
			if (sizeof($content -> tree) > 0) {
				$contentTree[$key] = $content -> toHTML(false, 'dhtml_content_tree_'.$lesson -> lesson['id'], array('noclick' => 1));
			}		
			$lessonInfo[$key] 	  					= new LearningObjectInformation(unserialize($lesson -> lesson['info']));
			$additionalInfo[$key] 					= $lesson -> getInformation();
			$additionalInfo[$key]['start_date'] 	= $lesson -> lesson['start_date'];
			$additionalInfo[$key]['end_date']   	= $lesson -> lesson['end_date'];
		}

		
		
		$smarty -> assign("T_ADDITIONAL_LESSON_INFO", $additionalInfo);
		$smarty -> assign("T_COURSE_LESSON_INFO", $lessonInfo);
		$smarty -> assign("T_CONTENT_TREE", $contentTree);
		$smarty -> assign("T_LANGUAGES", EfrontSystem :: getLanguages(true));

		$smarty -> assign("T_COURSE_LESSONS", $lessons);

		if ($course -> course['instance_source']) {
			$parentCourse = new EfrontCourse($course -> course['instance_source']);
			$instances 	  = $parentCourse -> getInstances();
			$instances[$parentCourse -> course['id']] = $parentCourse;
		} else {
			$instances = $course -> getInstances();
			$instances[$course -> course['id']] = $course;
		}
		foreach ($instances as $key => $instance) {
			if (!$instance -> course['show_catalog']) {
				unset($instances[$key]);
			}
		}

		$smarty -> assign("T_COURSE_INSTANCES", $instances);

		$courseInfo = new LearningObjectInformation(unserialize($course -> course['info']));
		$smarty -> assign("T_COURSE_INFO", $courseInfo);
		$additionalInfo = $course -> getInformation();
		$smarty -> assign("T_ADDITIONAL_COURSE_INFO", $additionalInfo);
	}

	
	if (isset($_SESSION['s_current_branch'])) {
		$branch = new EfrontBranch($_SESSION['s_current_branch']);
		$constraints = array('active' => true, 'archive' => false, 'instance' => false, 'sort' => 'name');
		$courses = $branch->getBranchCoursesIncludingParentBranches($constraints);
		//$courses = $branch->getBranchCoursesIncludingSubbranches($constraints);
		$lessons = array();
	}
	
	if ($GLOBALS['configuration']['enable_cart']) {
		$smarty -> assign("T_LAYOUT_CLASS", $currentTheme -> options['toolbar_position'] == "left" ? "hideRight" : "hideLeft");    //Whether to show the sidemenu on the left or on the right
	}

	$options = array('lessons_link' => basename($_SERVER['PHP_SELF']).'?ctg=lessons&catalog=1&info_lesson=',
	                             'courses_link' => basename($_SERVER['PHP_SELF']).'?ctg=lessons&catalog=1&info_course=',
			                     'search'       => true,
	                             'catalog'      => true,
			                     'url'          => $_SERVER['PHP_SELF'].'?ctg=lessons&catalog=1',
								 'collapse'	   => $GLOBALS['configuration']['collapse_catalog'],
								 'buy_link'	   => true,
	            				 'course_lessons' => false);
	//if (!empty($courses) || !empty($lessons)) {		//wtf
		include("directions_tree.php");
	//}
	
	

	$smarty -> assign("T_CART", cart :: prepareCart());
}


