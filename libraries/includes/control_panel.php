<?php
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

$loadScripts[] = 'scriptaculous/dragdrop';
$loadScripts[] = 'includes/control_panel';
try {
    // Insert a record into the logs table, if a lesson has been selected
    if (!$_admin_ && isset($_SESSION['s_lessons_ID'])) {
/*
        $fields_log = array ('users_LOGIN' => $_SESSION['s_login'],                                 //This is the log entry array
	                         'timestamp'   => time(),
	                         'action'      => 'lesson',
	                         'comments'    => 0,
	                         'session_ip'  => eF_encodeIP($_SERVER['REMOTE_ADDR']),
	                         'lessons_ID'  => $_SESSION['s_lessons_ID']);
        eF_deleteTableData("logs", "users_LOGIN='".$_SESSION['s_login']."' AND action='lastmove'"); //Only one lastmove action interests us, so delete any other
        eF_insertTableData("logs", $fields_log);
*/
    }

    if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
        /* Show classmates icon*/
        if ($_student_ && EfrontUser::isOptionVisible('func_people')) {
            $smarty -> assign("T_CLASSMATES", 1);
        }
    } #cpp#endif

    if (isset($_GET['op']) && $_GET['op'] == 'search') {
        /**Functions to perform searches*/
        require_once "module_search.php";
    } else if (isset($_GET['op']) && in_array($_GET['op'], array_keys($module_ctgs))) {
        $module_mandatory = eF_getTableData("modules", "mandatory", "name = '".$_GET['op']."'");
        if ($module_mandatory[0]['mandatory'] != 'false' || isset($currentLesson -> options[$_GET['op']]) || $_admin_) {
            include(G_MODULESPATH.$_GET['op'].'/module.php');
            $smarty -> assign("T_OP_MODULE", $module_ctgs[$_GET['op']]);
        }
    } else {
        $headerOptions = $controlPanelOptions = array();

        //Personal messages block (Common block)(
        if ((!isset($currentUser -> coreAccess['personal_messages']) || $currentUser -> coreAccess['personal_messages'] != 'hidden') && EfrontUser::isOptionVisible('messages')) {
            $personal_messages = eF_getTableData("f_personal_messages pm, f_folders ff", "pm.title, pm.id, pm.timestamp, pm.sender", "pm.users_LOGIN='".$currentUser -> user['login']."' and f_folders_ID=ff.id and ff.name='Incoming' and viewed='no'", "pm.timestamp desc limit 10");         //Get unseen messages in Incoming folder
            $smarty -> assign("T_PERSONAL_MESSAGES", $personal_messages);

            if ( !EfrontUser::isOptionVisible('messages_student') && $_SESSION['s_type'] == "student") {
            	$personal_message_options = array(
            	array('text' => _GOTOINBOX, 'image' => "16x16/go_into.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=messages")
            	);
            } else {
            	$personal_message_options = array(
            	array('text' => _MESSAGES,  'image' => "16x16/add.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=messages&add=1&popup=1", 'onClick' => "eF_js_showDivPopup(event, '"._NEWMESSAGE."', 2)", 'target' => 'POPUP_FRAME'),
            	array('text' => _GOTOINBOX, 'image' => "16x16/go_into.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=messages")
            	);
            }
            
            $smarty -> assign("T_PERSONAL_MESSAGES_OPTIONS", $personal_message_options);
            $smarty -> assign("T_PERSONAL_MESSAGES_LINK",    basename($_SERVER['PHP_SELF'])."?ctg=messages");
        }
        //News block (Common block)
        if (EfrontUser::isOptionVisible('news')) {
            $news = news :: getNews(0, true);
            if (!$_admin_) {
                //Get lesson news as well
            	$lessonNews = news :: getNews($currentLesson -> lesson['id'], true);
            	if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to branch urls
            		$currentBranch = new EfrontBranch($_SESSION['s_current_branch']);
            		$branchTreeUsers = array_keys($currentBranch->getBranchTreeUsers());
            		foreach ($lessonNews as $key => $value) {
            			if ($value['type'] != 'global' && !in_array($value['users_LOGIN'], $branchTreeUsers)) {
            				unset($lessonNews[$key]);
            			}
            		}
            	}            	 
                $news = array_merge($news, $lessonNews);
            }
            if (!$_student_ && (!isset($currentUser -> coreAccess['news']) || $currentUser -> coreAccess['news'] == 'change')) {
            	$newsOptions[] = array('text' => _ANNOUNCEMENTADD, 'image' => "16x16/add.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=news&add=1&popup=1", 'onClick' => "eF_js_showDivPopup(event, '"._ANNOUNCEMENTADD."', 2)", 'target' => 'POPUP_FRAME');
            }
            $newsOptions[] = array('text' => _ANNOUNCEMENTGO,  'image' => "16x16/go_into.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=news");
            $news = array_slice($news, 0, 10, true);
            $smarty -> assign("T_NEWS", $news);
            $smarty -> assign("T_NEWS_OPTIONS", $newsOptions);
            $smarty -> assign("T_NEWS_LINK", basename($_SERVER['PHP_SELF'])."?ctg=news");
        }

        //Calendar block (Common block)
        if (EfrontUser::isOptionVisible('calendar')) {
            $today = getdate(time());                                                                           //Get current time in an array
            $today = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);                            //Create a timestamp that is today, 00:00. this will be used in calendar for displaying today
            isset($_GET['view_calendar']) && eF_checkParameter($_GET['view_calendar'], 'timestamp') ? $view_calendar = $_GET['view_calendar'] : $view_calendar = $today;    //If a specific calendar date is not defined in the GET, set as the current day to be today

            $calendarOptions = array();
            if (!isset($currentUser -> coreAccess['calendar']) || $currentUser -> coreAccess['calendar'] == 'change') {
                $calendarOptions[] = array('text' => _ADDCALENDAR,  'image' => "16x16/add.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=calendar&add=1&view_calendar=".$view_calendar."&popup=1", "onClick" => "eF_js_showDivPopup(event, '"._ADDCALENDAR."', 3)", "target" => "POPUP_FRAME");
            }
            $calendarOptions[] = array('text' => _GOTOCALENDAR, 'image' => "16x16/go_into.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=calendar");

            $smarty -> assign("T_CALENDAR_OPTIONS", $calendarOptions);
            $smarty -> assign("T_CALENDAR_LINK", basename($_SERVER['PHP_SELF'])."?ctg=calendar");
            isset($_GET['add_another']) ? $smarty -> assign('T_ADD_ANOTHER', "1") : null;

            $events = calendar :: getCalendarEventsForUser($currentUser);
            if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to branch urls
            	$currentBranch = new EfrontBranch($_SESSION['s_current_branch']);
            	$branchTreeUsers = array_keys($currentBranch->getBranchTreeUsers());
            	foreach ($events as $key => $value) {
            		if ($value['type'] != 'global' && !in_array($value['users_LOGIN'], $branchTreeUsers)) {
            			unset($events[$key]);
            		}
            	}
            }
            $events = calendar :: sortCalendarEventsByTimestamp($events);
				
            $smarty -> assign("T_CALENDAR_EVENTS", $events);                                                    //Assign events and specific day timestamp to smarty, to be used from calendar
            $smarty -> assign("T_VIEW_CALENDAR", $view_calendar);
        }

        //Admin specific blocks
        if ($_admin_) {
            //New users block (Admin block)
            if (!isset($currentUser -> coreAccess['users']) || $currentUser -> coreAccess['users'] != 'hidden') {
                $users  = eF_getTableData("users", "login, surname, name, timestamp", "pending=1 and archive=0", "timestamp DESC", "", "100"); //Find every user that is not active... new way
                $smarty -> assign("T_INACTIVE_USERS", $users);                                                          //Assign them to smarty, to be displayed at the first page
                $smarty -> assign("T_INACTIVE_USERS_LINK", basename($_SERVER['PHP_SELF'])."?ctg=users");
            }
            //New lessons block (Admin block)
            if (!isset($currentUser -> coreAccess['lessons']) || $currentUser -> coreAccess['lessons'] != 'hidden') {
                $lessons = eF_getTableData("users_to_lessons ul, lessons l, users u", "DISTINCT users_LOGIN,  count(lessons_ID) AS count", "ul.users_LOGIN=u.login and u.archive=0 and ul.archive=0 and l.archive=0 and ul.lessons_ID = l.id and l.course_only = 0 and ul.from_timestamp=0", "", "users_LOGIN", "100");     //Get the new lesson registrations
                $smarty  -> assign("T_NEW_LESSONS", $lessons);                                                          //Assign the list to smarty, to be displayed at the first page

                $constraints = array('archive' => false, 'active' => true, 'limit' => 100);
                $courses = EfrontCourse :: getCoursesWithPendingUsers($constraints);
                $smarty  -> assign("T_NEW_COURSES", $courses);                                                          //Assign the list to smarty, to be displayed at the first page
            }
        }

        //Professor and student common blocks
        if ($_professor_ || $_student_) {  	
            //Projects block
            if ($currentLesson -> options['projects'] && EfrontUser::isOptionVisible('projects')) {
                if ($_professor_) {
                    $result = eF_getTableData("users_to_projects as up,projects as p", "p.title,p.id,up.users_LOGIN,up.upload_timestamp,up.last_comment", "p.lessons_ID=".$_SESSION['s_lessons_ID']." and p.id=up.projects_ID and filename!=''","up.upload_timestamp desc");
                    foreach ($result as $value) {
                        $projects[] = $value;
                    }        
                } else {
                    $projects = $currentLesson -> getProjects(false, $currentUser -> user['login']);
               
                    $projectsInControlPanel = $projects;
                    foreach ($projects as $key => $value) {
                    	if ($value['deadline'] < time()) {
                    		unset($projects[$key]);	//We unset the expired projects, instead of not retrieving them in the first place, because we want them all to determine whether to show the 'projects' icon
                    	}
                    }
                }    
         
                $smarty -> assign("T_PROJECTS", $projects);
                $projectOptions = array(array('text' => _GOTOPROJECTS, 'image' => "16x16/go_into.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=projects"));
                $smarty -> assign("T_PROJECTS_OPTIONS",$projectOptions);
                $smarty -> assign("T_PROJECTS_LINK",basename($_SERVER['PHP_SELF'])."?ctg=projects");
            }

            //New forum messages block
            if (EfrontUser::isOptionVisible('forum')) {    	
                //changed  l.name as show_lessons_name to l.name as lessons_name
            	if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
            		$forum_messages   = eF_getTableData("module_hcd_employee_works_at_branch ewb, f_messages fm JOIN f_topics ft JOIN f_forums ff LEFT OUTER JOIN lessons l ON ff.lessons_ID = l.id", "fm.title, fm.id, ft.id as topic_id, fm.users_LOGIN, fm.timestamp, l.name as lessons_name, lessons_id as show_lessons_id", "ewb.users_login = fm.users_LOGIN and ewb.branch_ID=".$_SESSION['s_current_branch']." and ft.f_forums_ID=ff.id AND fm.f_topics_ID=ft.id AND ff.lessons_ID = '".$currentLesson -> lesson['id']."'", "fm.timestamp desc LIMIT 5");
            	} else {
            		$forum_messages   = eF_getTableData("f_messages fm JOIN f_topics ft JOIN f_forums ff LEFT OUTER JOIN lessons l ON ff.lessons_ID = l.id", "fm.title, fm.id, ft.id as topic_id, fm.users_LOGIN, fm.timestamp, l.name as lessons_name, lessons_id as show_lessons_id", "ft.f_forums_ID=ff.id AND fm.f_topics_ID=ft.id AND ff.lessons_ID = '".$currentLesson -> lesson['id']."'", "fm.timestamp desc LIMIT 5");
            	}
            	$forum_lessons_ID = eF_getTableData("f_forums", "id", "lessons_ID=".$_SESSION['s_lessons_ID']);
            	 
                $smarty -> assign("T_FORUM_MESSAGES", $forum_messages);
                $smarty -> assign("T_FORUM_LESSONS_ID", $forum_lessons_ID[0]['id']);

                $forumOptions = array();
                if ($forum_lessons_ID[0]['id']) {
                    if (!isset($currentUser -> coreAccess['forum']) || $currentUser -> coreAccess['forum'] == 'change') {
                        $forumOptions[] = array('text' => _SENDMESSAGEATFORUM, 'image' => "16x16/add.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=forum&add=1&type=topic&forum_id=".$forum_lessons_ID[0]['id']."&popup=1", 'onclick' => "eF_js_showDivPopup(event, '"._NEWMESSAGE."', 2)", 'target' => 'POPUP_FRAME');
                    }
                }
                $forumOptions[] = (array('text' => _GOTOFORUM, 'image' => "16x16/go_into.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=forum"));

                $smarty -> assign("T_FORUM_OPTIONS", $forumOptions);
                $smarty -> assign("T_FORUM_LINK", basename($_SERVER['PHP_SELF'])."?ctg=forum&forum=".$forum_lessons_ID[0]['id']);
            }

            //Comments block
            if (!isset($currentUser -> coreAccess['content']) || $currentUser -> coreAccess['content'] != 'hidden') {
            	$comments = comments :: getComments(false, false, false, 5);
            	if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
            		$currentBranch = new EfrontBranch($_SESSION['s_current_branch']);
            		$branchTreeUsers = array_keys($currentBranch->getBranchTreeUsers());
            		foreach ($comments as $key => $value) {
            			if (!in_array($value['users_LOGIN'], $branchTreeUsers)) {
            				unset($comments[$key]);
            			}
            		}
            	}

                $smarty -> assign("T_COMMENTS", array_values($comments));
            }

            if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
                //Lesson timeline events block
                if (EfrontUser::isOptionVisible('lessons_timeline')) {
                    $form = new HTML_QuickForm("timeline_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=social&op=timeline&lessons_ID=".$_GET['lessons_ID'] . "&all=1", "", null, true);
                    $result = eF_getTableData("lessons_timeline_topics", "id, title", "lessons_ID = " . $currentLesson -> lesson['id']);
                    $topics = array("0" => _ANYTOPIC);
                    foreach($result as $topic) {
                        $id = $topic['id'];
                        $topics[$id]= $topic['title'];
                    }

                    $form -> addElement('select', 'topic' , _SELECTTIMELINETOPIC, $topics , 'class = "inputText"  id="timeline_topic" onchange="javascript:change_topic(\'timeline_topic\')"');

                    if (isset($_GET['topics_ID'])) {
                        $form -> setDefaults(array('topic' => $_GET['topics_ID']));
                        $smarty -> assign("T_TOPIC_TITLE", $topics[$_GET['topics_ID']]);
                    }

                    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
                    $form -> accept($renderer);
                    $smarty -> assign('T_TIMELINE_FORM', $renderer -> toArray());

                    $related_events = $currentLesson -> getEvents(false,true,0,20);
                    $timeline_options = array(
                    array('text' => _GOTOLESSONSTIMELINE, 'image' => "16x16/go_into.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=social&op=timeline&lessons_ID=" . $currentLesson -> lesson['id']."&all=1"));

                    $allModules = eF_loadAllModules(true);
                    $events = array();
                    foreach ($related_events as $key => $event) {
                        if ($related_events[$key] -> createMessage($allModules)) {
                            $events[$key] = array("avatar" => $related_events[$key] ->event['avatar'],"avatar_width" => $related_events[$key] ->event['avatar_width'], "avatar_height" => $related_events[$key] ->event['avatar_height'], "time" => $related_events[$key] ->event['time'], "message" => $related_events[$key] ->event['message']);
                        }
                    }

                    $smarty -> assign("T_TIMELINE_OPTIONS", $timeline_options);
                    $smarty -> assign("T_TIMELINE_LINK", basename($_SERVER['PHP_SELF'])."?ctg=social&op=timeline&lessons_ID=" . $currentLesson -> lesson['id']."&all=1");
                    $smarty -> assign("T_TIMELINE_EVENTS", $events);
                }
            } #cpp#endif

        }

        //Professor specific blocks
        if ($_professor_) {
        	//Completed tests list
        	if (!isset($currentUser -> coreAccess['content']) || $currentUser -> coreAccess['content'] != 'hidden') {
        		$testIds = $currentLesson -> getTests(false, true);
        		if (sizeof($testIds) > 0) {
        			$result = eF_getTableData("completed_tests ct, tests t", "ct.id, ct.users_LOGIN, ct.timestamp, ct.status, t.name", "ct.status != 'deleted' and ct.pending=1 and ct.status != 'incomplete' and ct.archive = 0 and ct.tests_ID = t.id and ct.tests_ID in (".implode(",", $testIds).")", "", "ct.timestamp DESC limit 10");

        			if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
        				$currentBranch = new EfrontBranch($_SESSION['s_current_branch']);
        				$branchTreeUsers = array_keys($currentBranch->getBranchTreeUsers());
        				foreach ($result as $key => $value) {
        					if (!in_array($value['users_LOGIN'], $branchTreeUsers)) {
        						unset($result[$key]);
        					}
        				}
        			}
        			
                    $smarty -> assign("T_COMPLETED_TESTS", array_values($result));
                }
            }
        }

        //Student specific blocks
        if ($_student_) {
            $currentContent = new EfrontContentTree($currentLesson);
            $currentContent -> markSeenNodes($currentUser);
          
            //Content tree block
            
            if (EfrontUser::isOptionVisible('tests')) {     	
                $iterator = new EfrontVisitableAndEmptyFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('active' => 1)));
                $firstNodeIterator = new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('active' => 1)));
            } else {
                $iterator 			= new EfrontNoTestsFilterIterator(new EfrontVisitableAndEmptyFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('active' => 1))));
                $firstNodeIterator 	= new EfrontNoTestsFilterIterator(new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('active' => 1))));
            
            }

		/*	$hideFeedback = false;
			foreach (new EfrontNoFeedbackFilterIterator(new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('active' => 1)))) as $key => $value) {
				if (!$value['seen']) {
					$hideFeedback = true;
				}
			} */
            if ($currentLesson -> options['content_tree']) {
                $smarty  -> assign("T_CONTENT_TREE", $currentContent -> toHTML($iterator, false, array('truncateNames' => 60, 'hideFeedback' => $hideFeedback)));
            }

            //Progress, status and start/continue block
            if (!$currentLesson -> options['tracking'] || $currentUser -> coreAccess['content'] == 'hidden') {
                $currentLesson -> options['lesson_info'] ? $controlPanelOptions[] = array('text' => _LESSONINFORMATION, 'image' => '32x32/information.png', 'href' => basename($_SERVER['PHP_SELF']).'?ctg=lesson_information', 'onClick' => "eF_js_showDivPopup(event, '"._LESSONINFORMATION."', 2)", 'target' => 'POPUP_FRAME') : null;
            } else {
                $seenContent  = EfrontStats::getStudentsSeenContent($currentLesson, $currentUser);
                $seenContent  = $seenContent[$currentLesson -> lesson['id']][$currentUser -> user['login']];
                $userProgress = EfrontStats::lightGetUserStatusInLesson($_SESSION['s_login'], $currentLesson, $seenContent, $iterator);
                $result       = eF_getTableData("users_to_lessons", "current_unit", "users_LOGIN = '".$currentUser -> user['login']."' and lessons_ID = ".$currentLesson -> lesson['id']);
                sizeof($result) > 0 ? $userProgress['current_unit']  = $result[0]['current_unit'] : $userProgress['current_unit'] = false;
                
                if ($userProgress['lesson_passed'] && !$userProgress['completed']) {
                    if (!$userProgress['completed'] && $currentLesson -> options['auto_complete']) {
                    	$userProgress = EfrontStats :: getUsersLessonStatus($currentLesson, $currentUser -> user['login']);
                    	$userProgress = $userProgress[$currentLesson -> lesson['id']][$currentUser -> user['login']];
                    	 
                        $userProgress['tests_avg_score'] ? $avgScore = $userProgress['tests_avg_score'] : $avgScore = 100;
                        $timestamp = _AUTOCOMPLETEDAT.': '.date("Y/m/d, H:i:s");
                        $currentUser -> completeLesson($currentLesson, $avgScore, $timestamp);

                        $userProgress['completed'] = 1;
                        $userProgress['score']     = $avgScore;
                        $userProgress['comments']  = $timestamp;
                    } else {
						if($currentLesson -> options['show_percentage']) {
							$headerOptions[] = array('text' => _YOUHAVEMETCONDITIONS, 'image' => '32x32/semi_success.png', 'href' => basename($_SERVER['PHP_SELF']).'?ctg=lesson_information&popup=1', 'onClick' => "eF_js_showDivPopup(event, '"._LESSONINFORMATION."', 2)", 'target' => 'POPUP_FRAME');
						}
					}
                }
                //Separate if because it might have just been set completed, from the previous if
                if ($userProgress['completed']) {
                    $smarty -> assign("T_LESSON_COMPLETED", $userProgress['completed']);
                    $headerOptions[] = array('text' => _LESSONCOMPLETE, 'image' => '32x32/success.png', 'href' => basename($_SERVER['PHP_SELF']).'?ctg=progress&popup=1', 'onclick' => "eF_js_showDivPopup(event, '"._LESSONINFORMATION."', 2)", 'target' => 'POPUP_FRAME');
                }
				
				if ($currentLesson -> lesson['course_only'] && isset($_SESSION['s_courses_ID'])) {
					$res = eF_getTableData("users_to_courses","issued_certificate","courses_ID=".$_SESSION['s_courses_ID']." and users_LOGIN='".$_SESSION['s_login']."'");
					$current_course = new EfrontCourse($_SESSION['s_courses_ID']);
					if ($res[0]['issued_certificate'] != "") {						
						$headerOptions[] = array('text' => _VIEWCOURSECERTIFICATE, 'image' => '32x32/certificate.png', 'href' => basename($_SERVER['PHP_SELF']).'?ctg=lessons&course='.$_SESSION['s_courses_ID'].'&export='.$current_course -> options['certificate_export_method'].'&user='.$_SESSION['s_login']);
					}	
				}
                if ($userProgress['current_unit']) {                                    //If there exists a value within the 'current_unit' attribute, it means that the student was in the lesson before. Seek the first unit that he hasn't seen yet
                    $firstUnseenUnit = $currentContent -> getFirstNode($firstNodeIterator);

                    //Get to the first unseen unit
                    while ($firstUnseenUnit && in_array($firstUnseenUnit['id'], array_keys($seenContent))) {
                        $firstUnseenUnit = $currentContent -> getNextNode($firstUnseenUnit, $firstNodeIterator);
                    }
                    if (!$firstUnseenUnit) {
                        $firstUnseenUnit = $currentContent -> getFirstNode($firstNodeIterator);
                    }
                    if ($currentLesson -> options['start_resume'] && $firstUnseenUnit) {
                        $headerOptions[] = array('text' => _RESUMELESSON, 'image' => '32x32/continue.png', 'href' => basename($_SERVER['PHP_SELF']).'?view_unit='.$firstUnseenUnit['id']);
                    }
                    $smarty -> assign("T_CURRENT_UNIT", $firstUnseenUnit);
                } else {
					if (EfrontUser::isOptionVisible('tests')) { 
		            	$iterator = new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST))); 
					} else { 
		            	$iterator = new EfrontNoTestsFilterIterator (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST)));    //Create a new iterator, so that the internal iterator pointer is not reset 
					}                     	
                    $iterator -> next();
                    $firstUnseenUnit = $firstUnit = $iterator -> current();

                    if ($firstUnit && $currentLesson -> options['start_resume']) {
                        $headerOptions[] = array('text' => _STARTLESSON, 'image' => '32x32/start.png', 'href' => basename($_SERVER['PHP_SELF']).'?ctg=content&view_unit='.$firstUnit['id']);
                    }
                }
                if (isset($currentLesson -> options['show_dashboard']) && !$currentLesson -> options['show_dashboard'] && $firstUnseenUnit) {
                    eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=content&view_unit=".$firstUnseenUnit['id']);
                }
                $currentLesson -> options['lesson_info'] ? $headerOptions[] = array('text' => _LESSONINFORMATION, 'image' => '32x32/information.png', 'href' => basename($_SERVER['PHP_SELF']).'?ctg=lesson_information&popup=1', 'onClick' => "eF_js_showDivPopup(event, '"._LESSONINFORMATION."', 2)", 'target' => 'POPUP_FRAME') : null;
            }

            //Digital library mini file manager block
            if ($currentLesson -> options['digital_library'] && $currentUser -> coreAccess['digital_library'] != 'hidden') {                                        //If the lesson digital library is enabled
            	$folderId = $currentLesson -> lesson['share_folder'] ? $currentLesson -> lesson['share_folder'] : $currentLesson -> lesson['id'];
            	$result = eF_getTableData("files", "*", "shared=".$folderId);
            	foreach ($result as $value) {
            		try {
            			$sharedFiles[G_ROOTPATH.$value['path']] = new EfrontFile($value['id']);
            		} catch (Exception $e) {/*Do nothing if you can't load a shared file*/}
            	}
            	if (sizeof($sharedFiles) > 0) {
            		$basedir    = $currentLesson -> getDirectory();
            		$options    = array('share' => false, 'zip' => false, 'folders' => false, 'delete' => false, 'edit' => false, 'create_folder' => false, 'upload' => false);
            		$url        = basename($_SERVER['PHP_SELF']).'?ctg=control_panel';
            		$filesystem = new FileSystemTree($basedir, true);
            		//changed to take account subfolders in efficient way
            		$filesystemIterator = new EfrontFileOnlyFilterIterator(new EfrontNodeFilterIterator(new EfrontDBOnlyFilterIterator(new EfrontFileOnlyFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($sharedFiles), RecursiveIteratorIterator :: SELF_FIRST))), array('shared' => $folderId)));

            		$smarty -> assign("T_FILES_LIST_OPTIONS", array(array('text' => _SHAREDFILES,  'image' => "16x16/go_into.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=digital_library")));
            		$smarty -> assign("T_FILE_LIST_LINK", basename($_SERVER['PHP_SELF'])."?ctg=digital_library");

            		/**The file manager*/
            		include ("file_manager.php");
            	}
            }
        }


        //This is a notifier for cookies handling the show/hide status of inner tables. It affects only control panel and is considered inside printInnerTable smarty plugin
        $innerTableIdentifier = $currentUser -> user['user_type'].'_cpanel';

        //Calculate element positions, so they can be rearreanged accordingly to the user selection
        $elementPositions = array();
        if ($_admin_) {
            $elementPositions[0]['positions'] = $GLOBALS['configuration'][$_SESSION['s_login']."_positions"];
        } else {
        	if (EfrontUser::isOptionVisible('move_blocks')) {
           		$elementPositions = eF_getTableData("users_to_lessons", "positions", "lessons_ID=".$currentLesson -> lesson['id']." AND users_LOGIN='".$currentUser -> user['login']."'");
        	}	
            if ($_student_ && sizeof($elementPositions) == 0  && $currentLesson -> options['default_positions']) {
                $elementPositions[0]['positions'] = $currentLesson -> options['default_positions'];
            }
        }

        if (sizeof($elementPositions) > 0) {
            $elementPositions = unserialize($elementPositions[0]['positions']);     //Get the inner tables positions, stored by the user.
            !is_array($elementPositions['first']) ? $elementPositions['first'] = array() : null;
            !is_array($elementPositions['second']) ? $elementPositions['second'] = array() : null;
            $smarty -> assign("T_POSITIONS_FIRST", $elementPositions['first']);     //Assign element positions to smarty
            $smarty -> assign("T_POSITIONS_SECOND", $elementPositions['second']);
            $smarty -> assign("T_POSITIONS_VISIBILITY", $elementPositions['visibility']);
            $smarty -> assign("T_POSITIONS", array_merge($elementPositions['first'], $elementPositions['second']));
            if ($_student_ && $elementPositions['update']) {
                foreach ($_COOKIE['innerTables'] as $key => $value) {
                    setcookie("innerTables[$key]", "", time()-86400, "/");
                }
                unset($elementPositions['update']);
                eF_updateTableData("users_to_lessons", array("positions" => serialize($elementPositions)), "lessons_ID=".$currentLesson -> lesson['id']." AND users_LOGIN='".$currentUser -> user['login']."'");
		        //$cacheKey = "user_lesson_status:lesson:".$currentLesson -> lesson['id']."user:".$currentUser -> user['login'];
		        //Cache::resetCache($cacheKey);
            }
        } else {
            $smarty -> assign("T_POSITIONS", array());
        }

		$controlPanelGroups = array(0 => 0);
		$controlPanelGroups[1] = _MODULES;
		$smarty -> assign("T_CONTROL_PANEL_GROUPS", $controlPanelGroups);

        $controlPanelOptions = array();
        //Set control panel elemenets for administrator
        if ($_admin_) {
            if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
                if (!isset($currentUser -> coreAccess['organization']) || $currentUser -> coreAccess['organization'] != 'hidden') {
					$controlPanelOptions[]  = array('text' => _ORGANIZATION, 'image' => "32x32/enterprise.png", 'href' => "administrator.php?ctg=module_hcd");
                }
				if (!isset($currentUser -> coreAccess['users']) || $currentUser -> coreAccess['users'] != 'hidden') {
                    $controlPanelOptions[]  = array('text' => _USERS, 'image' => "32x32/user.png", 'href' => "administrator.php?ctg=users");
                }
            } else { #cpp#else
                if (!isset($currentUser -> coreAccess['users']) || $currentUser -> coreAccess['users'] != 'hidden') {
                    $controlPanelOptions[]  = array('text' => _USERS, 'image' => "32x32/user.png", 'href' => "administrator.php?ctg=users");
                }
            } #cpp#endif
            if (!isset($currentUser -> coreAccess['lessons']) || $currentUser -> coreAccess['lessons'] != 'hidden') {
                $controlPanelOptions[] = array('text' => _LESSONS, 'image' => "32x32/lessons.png", 'href' => "administrator.php?ctg=lessons");
                $controlPanelOptions[] = array('text' => _COURSES, 'image' => "32x32/courses.png", 'href' => "administrator.php?ctg=courses");
                $controlPanelOptions[] = array('text' => _DIRECTIONS, 'image' => "32x32/categories.png", 'href' => "administrator.php?ctg=directions");
            }   
            if (EfrontUser::isOptionVisible('user_types')) {
                $controlPanelOptions[] = array('text' => _ROLES, 'image' => "32x32/user_types.png", 'href' => "administrator.php?ctg=user_types");
            }
            if (EfrontUser::isOptionVisible('groups')) {
                $controlPanelOptions[] = array('text' => _GROUPS, 'image' => "32x32/users.png", 'href' => "administrator.php?ctg=user_groups");
            }
            if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
                if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD         	
                    if (EfrontUser::isOptionVisible('lessons') && EfrontUser::isOptionVisible('curriculum')) {
                        $controlPanelOptions[] = array('text' => _CURRICULUM, 'image' => "32x32/curriculum.png", 'href' => "administrator.php?ctg=curriculums");
                    }
                	if (EfrontUser::isOptionVisible('skilulgaptests')) {
                		$controlPanelOptions[] = array('text' => _SKILLGAPTESTS, 'image' => "32x32/skill_gap.png", 'href' => "administrator.php?ctg=tests");
                	}
                } #cpp#endif
            } #cpp#endif
            if (EfrontUser::isOptionVisible('configuration')) {
                $controlPanelOptions[] = array('text' => _CONFIGURATIONVARIABLES, 'image' => "32x32/tools.png", 'href' => "administrator.php?ctg=system_config");
            }
            if (EfrontUser::isOptionVisible('themes')) {
                $controlPanelOptions[] = array('text' => _THEMES, 'image' => "32x32/themes.png", 'href' => "administrator.php?ctg=themes&theme=".$GLOBALS['currentTheme'] -> {$currentTheme -> entity}['id']);
            }
            if (EfrontUser::isOptionVisible('notifications')) {
                $controlPanelOptions[] = array('text' => _EMAILDIGESTS, 'image' => "32x32/notifications.png", 'href' => "administrator.php?ctg=digests");
            }
            if ((!isset($currentUser -> coreAccess['personal_messages']) || $currentUser -> coreAccess['personal_messages'] != 'hidden') && EfrontUser::isOptionVisible('messages')) {
                $controlPanelOptions[] = array('text' => _MESSAGES, 'image' => "32x32/mail.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=messages");
            }
            if (EfrontUser::isOptionVisible('online_users')) {
                $controlPanelOptions[] = array('text' => _CONNECTEDUSERS, 'image' => "32x32/logout.png", 'href' => "administrator.php?ctg=logout_user");
            }
            if (!isset($currentUser -> coreAccess['users']) || $currentUser -> coreAccess['users'] != 'hidden') {
                $controlPanelOptions[] = array('text' => _EXPORTIMPORTDATA, 'image' => "32x32/import_export.png", 'href' => "administrator.php?ctg=import_export");
            }
            if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
            	if (EfrontUser::isOptionVisible('user_profile')) {
	                $controlPanelOptions[] = array('text' => _CUSTOMIZEUSERSPROFILE, 'image' => "32x32/profile_add.png", 'href' => "administrator.php?ctg=user_profile");
	            }
            } #cpp#endif
            if (EfrontUser::isOptionVisible('languages')) {
                $controlPanelOptions[] = array('text' => _LANGUAGES, 'image' => "32x32/languages.png", 'href' => "administrator.php?ctg=languages");
            }
            if (EfrontUser::isOptionVisible('statistics')) {
                $controlPanelOptions[] = array('text' => _STATISTICS, 'image' => "32x32/reports.png", 'href' => "administrator.php?ctg=statistics");
            }
            if (EfrontUser::isOptionVisible('backup')) {
                $controlPanelOptions['backup'] = array('text' => _BACKUP." - "._RESTORE, 'image' => "32x32/backup_restore.png", 'href' => "administrator.php?ctg=backup");
            }
            if (EfrontUser::isOptionVisible('maintenance')) {
                $controlPanelOptions[] = array('text' => _MAINTENANCE, 'image' => "32x32/maintenance.png", 'href' => "administrator.php?ctg=maintenance");
            }
            
            if (EfrontUser::isOptionVisible('forum')) {
                $controlPanelOptions[] = array('text' => _FORUM, 'image' => "32x32/forum.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=forum");
            }
            if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
                if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD

                	if (EfrontUser::isOptionVisible('search_user')) {
                		$controlPanelOptions[] = array('text' => _SEARCHEMPLOYEE, 'image' => "32x32/search.png", 'href' => "administrator.php?ctg=search_users");
                		
                		//if (G_VERSIONTYPE == 'educational') { #cpp#ifdef EDUCATIONAL
                		//} else { #cpp#else
						//	if (!isset($currentUser -> coreAccess['organization']) || $currentUser -> coreAccess['organization'] != 'hidden') {
						//		$controlPanelOptions[] = array('text' => _SEARCHEMPLOYEE, 'image' => "32x32/search.png", 'href' => "administrator.php?ctg=module_hcd&op=reports");
						//	}
                		//} #cpp#endif
                	}

                	if (EfrontUser::isOptionVisible('archive')) {
                		$controlPanelOptions[] = array('text' => _ARCHIVE, 'image' => "32x32/generic.png", 'href' => "administrator.php?ctg=archive");
                	}
                } #cpp#endif
            } #cpp#endif

            if (!isset($currentUser -> coreAccess['modules']) || $currentUser -> coreAccess['modules'] != 'hidden') {
                $controlPanelOptions[] = array('text' => _MODULES, 'image' => "32x32/addons.png", 'href' => "administrator.php?ctg=modules", 'group' => 1);
            }
            if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
                if (EfrontUser::isOptionVisible('payments')) {
                    if (is_file('ipn.php')) {
                        $controlPanelOptions[] = array('text' => _PAYMENTS, 'image' => "32x32/shopping_basket.png", 'href' => "administrator.php?ctg=payments");
                    }
                }
                if (EfrontUser::isOptionVisible('version_key')) {
	                $controlPanelOptions[] = array('text' => _REGISTRATIONUPDATE, 'image' => "32x32/keys.png", 'href' => "administrator.php?ctg=versionkey");
	            }
	            //If the edition is not register, simply display the Registration icon
	            if (!$GLOBALS['configuration']['version_key']) {
	                $controlPanelOptions = array(array('text' => _REGISTRATIONUPDATE, 'image' => "32x32/keys.png", 'href' => "administrator.php?ctg=versionkey"), $controlPanelOptions['backup']);
	                //$smarty -> assign ("T_UNREGISTERED", 1);
	            }
            } #cpp#endif
        }
        //Set control panel elements for professor
        else if ($_professor_) {
            $currentContent = new EfrontContentTree($currentLesson);        
            if ($currentUser -> coreAccess['content'] != 'hidden') {
                $currentLesson -> options['lesson_info'] ? $controlPanelOptions[0]  = array('text' => _LESSONINFORMATION, 'image' => "32x32/information.png",       'href' => basename($_SERVER['PHP_SELF'])."?ctg=lesson_information") : null;
                
	            if (EfrontUser::isOptionVisible('tests')) {     	
	                 $firstNodeIterator = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST));  
	            } else {
	                 $firstNodeIterator = new EfrontNoTestsFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST)));
	            }
				
                if ($currentContent && $currentContent -> getFirstNode($firstNodeIterator) && !empty($firstNodeIterator)){
                    $controlPanelOptions[1]  = array('text' => _CONTENTMANAGEMENT, 'image' => "32x32/content.png",      'href' => basename($_SERVER['PHP_SELF'])."?ctg=content&view_unit=".$currentContent -> getFirstNode($firstNodeIterator) -> offsetGet('id'));
                }  else {
                    $controlPanelOptions[1]  = array('text' => _CONTENTMANAGEMENT, 'image' => "32x32/content.png",      'href' => basename($_SERVER['PHP_SELF'])."?ctg=content");
                }

                if (!isset($currentUser -> coreAccess['content']) || $currentUser -> coreAccess['content'] == 'change') {
                    $controlPanelOptions[5]  = array('text' => _CONTENTTREEMANAGEMENT,    'image' => "32x32/content_reorder.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=order");
                    $controlPanelOptions[7]  = array('text' => _COPYFROMANOTHERLESSON,    'image' => "32x32/lesson_copy.png",     'href' => basename($_SERVER['PHP_SELF'])."?ctg=copy");
                }
                (EfrontUser::isOptionVisible('projects')) ? $controlPanelOptions[2]  = array('text' => _PROJECTS,    'image' => "32x32/projects.png",     'href' => basename($_SERVER['PHP_SELF'])."?ctg=projects")  : null;

                EfrontUser::isOptionVisible('tests')? $controlPanelOptions[3]  = array('text' => _TESTS,       'image' => "32x32/tests.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=tests")     : null;
                $currentLesson -> options['rules']    ? $controlPanelOptions[10]  = array('text' => _ACCESSRULES, 'image' => "32x32/rules.png",       'href' => basename($_SERVER['PHP_SELF'])."?ctg=rules")     : null;
				$currentLesson -> options['scorm']    ? $controlPanelOptions[19] = array('text' => _SCORM,       'image' => "32x32/scorm.png",      'href' => basename($_SERVER['PHP_SELF'])."?ctg=scorm") : null;
                $currentLesson -> options['ims']	  ? $controlPanelOptions[21] = array('text' => _IMS,       'image' => "32x32/autocomplete.png",      'href' => basename($_SERVER['PHP_SELF'])."?ctg=ims") : null;
                if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
                	if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD                
                		$currentLesson -> options['tincan']   ? $controlPanelOptions[22] = array('text' => _TINCAN,    'image' => "32x32/theory.png",      'href' => basename($_SERVER['PHP_SELF'])."?ctg=tincan") : null;
	                } #cpp#endif
	            } #cpp#endif                
            }
            if (EfrontUser::isOptionVisible('glossary')) {
                $currentLesson -> options['glossary'] ? $controlPanelOptions[11]  = array('text' => _GLOSSARY,    'image' => "32x32/glossary.png",    'href' => basename($_SERVER['PHP_SELF'])."?ctg=glossary")  : null;
            }
            if ($currentUser -> coreAccess['statistics'] != 'hidden') {
                $controlPanelOptions[14]  = array('text' => _LESSONSTATISTICS,        'image' => "32x32/reports.png",       'href' => basename($_SERVER['PHP_SELF'])."?ctg=statistics&option=lesson");
            }
            if ($currentUser -> coreAccess['settings'] != 'hidden' && $currentLesson -> lesson['course_only'] != 1) {
                $controlPanelOptions[13] = array('text' => _SCHEDULING,        'image' => "32x32/schedule.png",    'href' => basename($_SERVER['PHP_SELF'])."?ctg=scheduling");
            }
            if ($currentUser -> coreAccess['files'] != 'hidden') {
                $controlPanelOptions[17] = array('text' => _FILES,       'image' => "32x32/file_explorer.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=file_manager");
            }
            if ($currentUser -> coreAccess['settings'] != 'hidden') {
                $controlPanelOptions[24] = array('text' => _LESSONSETTINGS,    'image' => "32x32/tools.png",        'href' => basename($_SERVER['PHP_SELF'])."?ctg=settings");
            }
            if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
                if (EfrontUser::isOptionVisible('func_people')) {
                    $controlPanelOptions[15] = array('text' => _LESSONPEOPLE,       'image' => "32x32/users.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=social&op=people&display=2");
                }
                if (EfrontUser::isOptionVisible('surveys')) {
                    $currentLesson -> options['survey'] ? $controlPanelOptions[8] = array('text' => _SURVEYS, 'image' => "32x32/surveys.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=survey") : null;
                }
                if ($currentLesson -> options['smart_content']) {
                	$controlPanelOptions[23] = array('text' => _SMARTCONTENT, 'image' => "32x32/import.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=import");
                }
            } #cpp#endif
			if (EfrontUser::isOptionVisible('feedback')) {
                    $currentLesson -> options['feedback'] ? $controlPanelOptions[9] = array('text' => _FEEDBACK, 'image' => "32x32/feedback.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=feedback") : null;
                }
            if ($currentUser -> coreAccess['progress'] != 'hidden') {
                $controlPanelOptions[12] = array('text' => _USERSPROGRESS, 'image' => "32x32/status.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=progress");
            }
            if (EfrontUser::isOptionVisible('forum')) {
                $resultForum = eF_getTableData("f_forums","id","lessons_ID=".$_SESSION['s_lessons_ID']);
                $currentLesson -> options['forum']    ? $controlPanelOptions[18] = array('text' => _FORUM,       'image' => "32x32/forum.png",      'href' => basename($_SERVER['PHP_SELF'])."?ctg=forum&forum=".$resultForum[0]['id']) : null;
            }

            ksort($controlPanelOptions);

        } else {
            $controlPanelOptions = $headerOptions;
            if (EfrontUser::isOptionVisible('glossary')) {
                $option = array('text' => _GLOSSARY,    'image' => "32x32/glossary.png",    'href' => basename($_SERVER['PHP_SELF'])."?ctg=glossary");
                $controlPanelOptions[] = $option;
                $headerOptions[]       = $option;
            }
            if (EfrontUser::isOptionVisible('forum')) {
                $resultForum = eF_getTableData("f_forums","id","lessons_ID=".$_SESSION['s_lessons_ID']);
                $option = array('text' => _FORUM, 'image' => "32x32/forum.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=forum&forum=".$resultForum[0]['id']);
                $controlPanelOptions[] = $option;
                $headerOptions[]       = $option;
            }
            if (EfrontUser::isOptionVisible('projects') && sizeof($projectsInControlPanel) > 0) {
            	$controlPanelOptions[]  = array('text' => _PROJECTS,    'image' => "32x32/projects.png",     'href' => basename($_SERVER['PHP_SELF'])."?ctg=projects");
            }
            if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
                if ($_student_ && EfrontUser::isOptionVisible('func_people')) {
                    $option = array('text' => _LESSONPEOPLE, 'image' => "32x32/users.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=social&op=people&display=2");
                    $controlPanelOptions[] = $option;
                    $headerOptions[]       = $option;
                }
            } #cpp#endif
            if ($currentLesson -> options['show_student_cpanel']) {
                $headerOptions = array();
            } else {
                $controlPanelOptions = array();
            }
        }

        ///Create control panel sidelinks and innertable
        $innertable_modules = array();
        foreach ($loadedModules as $module) {
            if ($_admin_ || isset($currentLesson -> options[$module -> className]) && $currentLesson -> options[$module -> className] == 1) {
                unset($InnertableHTML);
                if ($_admin_) {
                    $centerLinkInfo = $module -> getCenterLinkInfo();
                    $InnertableHTML = $module -> getControlPanelModule();
                    $InnertableHTML ? $module_smarty_file = $module -> getControlPanelSmartyTpl() : $module_smarty_file = false;
                } else {
                    $centerLinkInfo = $module -> getLessonCenterLinkInfo();
                    $InnertableHTML = $module -> getLessonModule();
                    $InnertableHTML ? $module_smarty_file = $module -> getLessonSmartyTpl() : $module_smarty_file = false;
                }
                if ($centerLinkInfo) {
                    $controlPanelOption = array('text' => $centerLinkInfo['title'],  'image' => eF_getRelativeModuleImagePath($centerLinkInfo['image']), 'href' => $centerLinkInfo['link']);
                    if ($_SESSION['s_lesson_user_type'] != 'student') {
	                    $controlPanelOption['group'] = 1;
                    }
                    $controlPanelOptions[] = $controlPanelOption;
                }
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
            }
        }

        if (!empty($innertable_modules)) {
            $smarty -> assign("T_INNERTABLE_MODULES", $innertable_modules);
        }

        $smarty -> assign("T_CONTROL_PANEL_OPTIONS", $controlPanelOptions);
        $smarty -> assign("T_HEADER_OPTIONS", $headerOptions);

    }
} catch (Exception $e) {
    $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
    $message      = _SOMEPROBLEMOCCURED.': '.$e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
    $message_type = 'failure';

}
