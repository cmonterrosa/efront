<?php
//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

if (!EfrontUser::isOptionVisible('statistics')) {
	eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
}

$loadScripts[] = 'scriptaculous/excanvas';
$loadScripts[] = 'scriptaculous/flotr';
$loadScripts[] = 'scriptaculous/controls';
$loadScripts[] = 'includes/graphs';
$loadScripts[] = 'includes/statistics';
$loadScripts[] = 'scriptaculous/canvastext';

$smarty -> assign("T_CATEGORY", 'statistics');
//$smarty -> assign("T_BASIC_TYPE", $currentUser -> user['user_type']);
$smarty -> assign("T_BASIC_TYPE", $_SESSION['s_lesson_user_type'] !="" ? $_SESSION['s_lesson_user_type'] : $currentUser -> user['user_type']);
$isProfessor = 0;
$isStudent   = 0;

//check to see if the user has any lessons as a student and any lessons as professor
$lessonRoles = EfrontLessonUser::getLessonsRoles();
if ($currentUser -> user['user_type'] != 'administrator') {
    $lessons = $currentUser -> getLessons(false);
    foreach ($lessons as $key => $type) {
        if ($lessonRoles[$type] == 'professor') {
            $isProfessor = 1;
            $professorLessons[] = $key;
        } else if ($type == 'student') {
            $isStudent = 1;
            $studentLessons[] = $key;
        }
    }
}


if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
    // Check if user is a supervisor in the HCD interface
    if (isset($currentUser ->aspects['hcd']) && $currentUser ->aspects['hcd']->isSupervisor()) {
        $isSupervisor = 1;
    }

} #cpp#endif


$smarty -> assign("T_ISPROFESSOR", $isProfessor);
$smarty -> assign("T_ISSTUDENT", $isStudent);

// Only administrators and supervisors are allowed to see user reports
if ($currentUser -> user['user_type'] != 'administrator' && !$isSupervisor) {
    if ($isProfessor) {
        if (isset($currentLesson) && !in_array($currentLesson -> lesson['id'], $professorLessons)) {
            $_GET['option'] = 'user';
        } else if (!isset($currentLesson) && $currentUser -> user['user_type'] != 'professor') {
            $_GET['option'] = 'user';
        }
    } else {
        $_GET['option'] = 'user';
        if (!$_SESSION['s_lessons_ID']) {
            $_GET['sel_user'] = $_SESSION['s_login'];

        }
    }
}
$smarty -> assign("T_OPTION", $_GET['option']);

try {
    /*no option is set, so just show the available options*/
	if (!isset($_GET['option'])) {
		$reportGroups = array(0 => 0);
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				$reportGroups[1] = _CUSTOMREPORTS;
			} #cpp#endif
		} #cpp#endif
		$smarty -> assign("T_REPORTS_GROUPS", $reportGroups);
		$options = array();
		if ($currentUser -> user['user_type'] == 'administrator') {
			$options[]  = array('text' => _USERSTATISTICS,    'image' => "32x32/user.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=user");
			$options[]  = array('text' => _LESSONSTATISTICS,  'image' => "32x32/lessons.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=lesson");
            $options[]  = array('text' => _COURSESTATISTICS,  'image' => "32x32/courses.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=course");
            if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
            	if (EfrontUser::isOptionVisible('tests')) {
                	$options[]  = array('text' => _TESTSTATISTICS,    'image' => "32x32/tests.png",    'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=test");
            	}
            } #cpp#endif
			if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
				if (EfrontUser::isOptionVisible('feedback')) {
					$options[]  = array('text' => _FEEDBACKSTATISTICS,    'image' => "32x32/feedback.png",    'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=feedback");
				}
			} #cpp#endif
            $options[]  = array('text' => _SYSTEMSTATISTICS,  'image' => "32x32/reports.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=system");
            if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
                if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD

                    $options[]  = array('text' => _CUSTOMSTATISTICS,  		'image' => "32x32/custom_reports.png",    	'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=custom");
                    $options[]  = array('text' => _CERTIFICATESTATISTICS,  	'image' => "32x32/certificate.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=certificate");
                    $options[]  = array('text' => _GROUPSTATISTICS,  		'image' => "32x32/users.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=groups");
                    if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
						if (!isset($currentUser -> coreAccess['organization']) || $currentUser -> coreAccess['organization'] != 'hidden') {
							$options[]  = array('text' => _BRANCHSTATISTICS,  		'image' => "32x32/branch.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=branches");
							$options[]  = array('text' => _SKILLSSTATISTICS,  		'image' => "32x32/skills.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=skill");
						}
                    } #cpp#endif
					$options[]  = array('group' => 1, 'text' => _ADVANCEDUSERREPORTS,  		'image' => "32x32/users.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=advanced_user_reports");
                    $options[]  = array('text' => _PARTICIPATIONSTATISTICS, 'image' => "32x32/reports.png",    	'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=participation");
                    $options[]  = array('text' => _EVENTSTATISTICS,  'image' => "32x32/social.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=events");
                    $options[]  = array('text' => _TINCAN,  'image' => "32x32/theory.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=tincan");
                } #cpp#endif
            } #cpp#endif
        } else if ($isProfessor) {
            $options[]  = array('text' => _USERSTATISTICS,    'image' => "32x32/user.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=user");
            $options[]  = array('text' => _LESSONSTATISTICS,  'image' => "32x32/lessons.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=lesson");
            $options[]  = array('text' => _COURSESTATISTICS,  'image' => "32x32/courses.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=course");
            if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
				if (EfrontUser::isOptionVisible('tests')) {
					$options[]  = array('text' => _TESTSTATISTICS,    'image' => "32x32/tests.png",    'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=test");
				}
			} #cpp#endif
			if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
				if (EfrontUser::isOptionVisible('feedback')) {
					$options[]  = array('text' => _FEEDBACKSTATISTICS,    'image' => "32x32/feedback.png",    'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=feedback");
				}
				if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
					$options[]  = array('text' => _TINCAN,  'image' => "32x32/theory.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=tincan");
				} #cpp#endif
			} #cpp#endif
			if ($isSupervisor) {
				$options[]  = array('group' => 1, 'text' => _ADVANCEDUSERREPORTS,  		'image' => "32x32/users.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=advanced_user_reports");
				if (!isset($currentUser -> coreAccess['organization']) || $currentUser -> coreAccess['organization'] != 'hidden') {
            		$options[]  = array('text' => _CERTIFICATESTATISTICS,  	'image' => "32x32/certificate.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=certificate");
					$options[]  = array('text' => _BRANCHSTATISTICS,    'image' => "32x32/branch.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=branches");
	            	$options[]  = array('text' => _SKILLSSTATISTICS,  		'image' => "32x32/skills.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=skill");
				}
			}
        } else if ($isSupervisor) {
            $options[]  = array('text' => _USERSTATISTICS,    'image' => "32x32/user.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=user");
            $options[]  = array('text' => _COURSESTATISTICS,    'image' => "32x32/courses.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=course");
            $options[]  = array('text' => _CERTIFICATESTATISTICS,  	'image' => "32x32/certificate.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=certificate");
            if (!isset($currentUser -> coreAccess['organization']) || $currentUser -> coreAccess['organization'] != 'hidden') {
	            $options[]  = array('text' => _BRANCHSTATISTICS,    'image' => "32x32/branch.png",   'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=branches");
	            $options[]  = array('text' => _SKILLSSTATISTICS,  		'image' => "32x32/skills.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=skill");
            }
			$options[]  = array('group' => 1, 'text' => _ADVANCEDUSERREPORTS,  		'image' => "32x32/users.png",    		'href' => $_SERVER['PHP_SELF']."?ctg=statistics&option=advanced_user_reports");
        }
        
        foreach ($loadedModules as $module) {
			if ($linkInfo = $module->getReportsLinkInfo()) {
				$options[] = array('group' => 1, 'text' => $linkInfo['title'], 'image' => eF_getRelativeModuleImagePath($linkInfo['image']), 'href' => $linkInfo['link']);
			}
		}
		$smarty -> assign("T_STATISTICS_OPTIONS", $options);
		
    } else if ($_GET['option'] == 'user') {
        require_once("statistics/users_stats.php");

    } else if ($_GET['option'] == 'lesson') {
        require_once("statistics/lessons_stats.php");

    } else if ($_GET['option'] == 'course') {
        require_once("statistics/courses_stats.php");

    } else if ($_GET['option'] == 'test') {
        require_once("statistics/tests_stats.php");

    }  else if ($_GET['option'] == 'feedback') {
        require_once("statistics/feedback_stats.php");

    } else if ($_GET['option'] == 'system') {
        require_once("statistics/system_stats.php");

	} elseif ($_GET['option'] == 'custom') {
        require_once("statistics/custom_stats.php");

	} elseif ($_GET['option'] == 'certificate') {
        require_once("statistics/certificates_stats.php");

	} elseif ($_GET['option'] == 'events') {
        require_once("statistics/events_stats.php");

	} else if ($_GET['option'] == "groups") {
        require_once("statistics/groups_stats.php");

	} else if ($_GET['option'] == "branches") {
        require_once("statistics/branches_stats.php");

	} else if ($_GET['option'] == "participation") {
        require_once("statistics/participation_stats.php");

	} else if ($_GET['option'] == "advanced_user_reports") {
        require_once("statistics/advanced_user_reports.php");

	} else if ($_GET['option'] == "skill") {
        require_once("statistics/skills_stats.php");
        
	} else if ($_GET['option'] == "tincan") {
        require_once("statistics/tincan_stats.php");
	}
        
} catch (Exception $e) {
	handleNormalFlowExceptions($e);
}

