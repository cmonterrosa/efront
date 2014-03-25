<?php

if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

if (!$currentUser -> coreAccess['personal_messages'] || $currentUser -> coreAccess['personal_messages'] == 'change') {
    $_change_ = 1;
}
$smarty -> assign("_change_", $_change_);

$loadScripts[] = 'scriptaculous/controls';
$loadScripts[] = 'includes/messages';

try {
    if (!EfrontUser::isOptionVisible('messages')) {
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
    }

    formatLogin();

    $result = eF_getTableData("f_personal_messages", "*", "users_LOGIN='".$currentUser -> user['login']."'", "priority desc, viewed,timestamp desc");

    //An array of legal ids for editing entries
    $legalValues = array();
    foreach ($result as $value) {
        $messages[$value['id']] = $value;
        $legalValues[]          = $value['id'];
    }

//---------------------------------------Start of Folders-------------------------------------------

    $folders = eF_PersonalMessage :: getUserFolders($currentUser -> user['login']);
    reset($folders);
    isset($_GET['folder']) && in_array($_GET['folder'], array_keys($folders)) && eF_checkParameter($_GET['folder'], 'id') ? $currentFolder = $_GET['folder'] : $currentFolder = key($folders);    //key($folders) is the id of the first folder, which is always the Incoming

    $smarty -> assign("T_FOLDER", $currentFolder);

    $smarty -> assign("T_FOLDERS_OPTIONS", array(array('text' => _NEWFOLDER, 'image' => "16x16/folder_add.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=messages&folders=true&add=1&popup=1", 'onClick' => "eF_js_showDivPopup(event, '"._CREATEFOLDER."', 0)", 'target' => 'POPUP_FRAME')));  
    $smarty -> assign("T_FOLDERS", $folders);
    next($folders);
    $smarty -> assign("T_SENT_FOLDER", key($folders));//The 'sent' folder is always the 2nd in the list


    foreach ($folders as $folder) {
        $totalMessages += $folder['messages_num'];
        $totalSize     += $folder['filesize'];
    }
    $legalFolderValues = array_keys($folders);
	$smarty -> assign("T_TOTAL_MESSAGES", $totalMessages);
	$smarty -> assign("T_TOTAL_SIZE", $totalSize);
	if ($totalSize > $GLOBALS['configuration']['pm_space']*1024 && $GLOBALS['configuration']['pm_space'] != '') {
		$message      .= _YOUHAVETODELETEFILESFROMYOURSPACE.'<br />';
        $message_type = 'failure';
        $_change_ = 0;
        $smarty -> assign("_change_", $_change_);
	} 

//---------------------------------------End of Folders-------------------------------------------


	if (isset($_GET['folders'])) {
	    $entityForm = new HTML_QuickForm("create_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=messages&folders=true".(isset($_GET['add']) ? '&add=1' : '&edit='.$_GET['edit'])."", "", null, true);
		$legalValues = $legalFolderValues;
	    $entityName  = 'f_folders';

	    //Handle creation, deletion etc uniquely
		include("entity.php");

	} elseif (isset($_GET['delete']) && in_array($_GET['delete'], $legalValues) && eF_checkParameter($_GET['delete'], 'id')) {
	    try {
	        $result = eF_getTableData("f_personal_messages", "users_LOGIN, attachments, f_folders_ID", "id=".$_GET['delete']);

	        eF_deleteTableData("f_personal_messages", "id=".$_GET['delete']);

	        if ($result[0]['attachments'] != '') {
	            $attached_file = new EfrontFile($result[0]['attachments']);
	            $attached_file -> delete();
	        }
	    } catch (Exception $e) {
            header("HTTP/1.0 500 ");
            echo rawurlencode($e -> getMessage()).' ('.$e -> getCode().')';
	    }
        exit;
	} elseif (isset($_GET['ajax']) && isset($_GET['delete_messages'])) {
		try {
			$messages = json_decode($_GET['delete_messages']);

			foreach ($messages as $message) {
				$result = eF_getTableData("f_personal_messages", "users_LOGIN, attachments, f_folders_ID", "id=".$message);
		        eF_deleteTableData("f_personal_messages", "id=".$message);
		        if ($result[0]['attachments'] != '') {
		            $attached_file = new EfrontFile($result[0]['attachments']);
		            $attached_file -> delete();
		        }
			}
	    } catch (Exception $e) {
           handleAjaxExceptions($e);
	    }
        exit;
	} elseif (isset($_GET['ajax']) && isset($_GET['move_messages'])) {
		try {
			$messages = json_decode($_GET['move_messages']);

			foreach ($messages as $message) {
				eF_updateTableData("f_personal_messages", array("f_folders_ID" => $_GET['folder']), "id=".$message);
			}
	    } catch (Exception $e) {
           handleAjaxExceptions($e);
	    }
        exit;
	} elseif (isset($_GET['move']) && in_array($_GET['move'], $legalValues) && eF_checkParameter($_GET['move'], 'id') && isset($_GET['folder']) && in_array($_GET['folder'], $legalFolderValues) && eF_checkParameter($_GET['folder'], 'id')) {
	    try {
		    $message = $messages[$_GET['move']];
		    eF_updateTableData("f_personal_messages", array("f_folders_ID" => $_GET['folder']), "id=".$_GET['move']);
	    } catch (Exception $e) {
            header("HTTP/1.0 500 ");
            echo rawurlencode($e -> getMessage()).' ('.$e -> getCode().')';
	    }
        exit;
	} elseif (isset($_GET['flag']) && in_array($_GET['flag'], $legalValues) && eF_checkParameter($_GET['flag'], 'id')) {
	    try {
		    $message = $messages[$_GET['flag']];
		    $message['priority'] ? $priority = 0 : $priority = 1;
		    eF_updateTableData("f_personal_messages", array("priority" => $priority), "id=".$_GET['flag']);
		    echo $priority;
	    } catch (Exception $e) {
            header("HTTP/1.0 500 ");
            echo rawurlencode($e -> getMessage()).' ('.$e -> getCode().')';
	    }
        exit;
    } elseif (isset($_GET['add'])) {
		if (!$_change_ || (!EfrontUser::isOptionVisible('messages_student') && $_SESSION['s_type'] == "student")) {
			$message = _UNAUTHORIZEDACCESS;
			eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=messages&message=".urlencode($message)."&message_type=failure");
			exit;
		}

        $load_editor   = true;

        $grant_full_access = false;
        if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
            $currentEmployee = $currentUser -> aspects['hcd'];
            if ($currentEmployee -> isSupervisor()) {
                $grant_full_access = true;
            }
        } #cpp#endif

        if ($currentUser -> getType() == "administrator") {
            $grant_full_access = true;
        }

        if ($grant_full_access) {
            $smarty -> assign("T_FULL_ACCESS", 1);

            $lessons    = eF_getTableDataFlat("lessons", "id,name", "archive=0 and active=1", "name");
            $courses    = eF_getTableDataFlat("courses", "id,name", "archive=0 and active=1", "name");

            $users = EfrontUser :: getUsers(true);
            $roles = EfrontUser :: getRoles(true);
        } else {
            $smarty -> assign("T_FULL_ACCESS", 0);

            $lessons    = eF_getTableDataFlat("lessons JOIN users_to_lessons", "id,name", "users_to_lessons.archive=0 and lessons.archive=0 and lessons.active=1 and lessons.id = users_to_lessons.lessons_ID AND users_LOGIN = '".$currentUser->user['login']."'", "name");
            $courses    = eF_getTableDataFlat("courses JOIN users_to_courses", "id,name", "users_to_courses.archive=0 and courses.archive=0 and courses.active=1 and courses.id = users_to_courses.courses_ID AND users_LOGIN = '".$currentUser->user['login']."'", "name");
        }

        //If in a branch url, remove unrelated courses
        if (G_VERSIONTYPE == 'enterprise' && defined("G_BRANCH_URL") && G_BRANCH_URL) {
        	$branch = new EfrontBranch($_SESSION['s_current_branch']);
        	$result = eF_getTableDataFlat("module_hcd_course_to_branch", "courses_ID", "branches_ID=".$branch->branch['branch_ID']);
        	foreach ($courses['id'] as $key => $value) {
        		
        		if (!in_array($value, $result['courses_ID'])) {
        			unset($courses['id'][$key]);
        			unset($courses['name'][$key]);
        		}
        	}
        }

        //This code is for excluding lessons that belong to inactive courses and they do not belong to any other active course
        $lessons_excluded   = eF_getTableData("courses c,lessons l, lessons_to_courses lc", "l.id,l.name,SUM(c.active) as active", "l.id=lc.lessons_ID and c.id=lc.courses_ID  AND l.course_only=1", "", "l.id");
        foreach ($lessons_excluded as $key => $value) {
        	if($value['active'] == 0) {
        		$lessonToRemove = array_search($value['id'] , $lessons['id']);
        		if($lessonToRemove !== false) {
        			unset($lessons['id'][$lessonToRemove]);
        			unset($lessons['name'][$lessonToRemove]);
        		}
        	}
        } 

        sizeof($lessons) > 0 ? $lessons = array_combine($lessons['id'], $lessons['name']) : $lessons = array();
        sizeof($courses) > 0 ? $courses = array_combine($courses['id'], $courses['name']) : $courses = array();
        $smarty -> assign("T_LESSONS", $lessons);      
        $smarty -> assign("T_COURSES", $courses);

        $form = new HTML_QuickForm("new_message_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=messages&add=1", "", "id = 'new_message_form'", true);  //Build the form
        $form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');

        $form -> addElement('advcheckbox', 'bcc', _UNDISCLOSEDRECIPIENTS, null, 'class = "inputCheckbox"');
        $form -> addElement('radio', 'recipients', null, null, 'only_specific_users', 		'onclick = "eF_js_selectRecipients(\'only_specific_users\')" id = "only_specific_users"');
        $form -> addElement('radio', 'recipients', null, null, 'active_users', 		  		'onclick = "eF_js_selectRecipients(\'active_users\')" 	     id = "all_active_users"');
        $form -> addElement('radio', 'recipients', null, null, 'specific_course',     		'onclick = "eF_js_selectRecipients(\'specific_course\')"');
        $form -> addElement('radio', 'recipients', null, null, 'specific_lesson', 	  	    'onclick = "eF_js_selectRecipients(\'specific_lesson\')"');
        $form -> addElement('radio', 'recipients', null, null, 'specific_lesson_professor', 'onclick = "eF_js_selectRecipients(\'specific_lesson_professor\')"');
        $form -> addElement('checkbox', 'specific_type');
        $form -> addElement('select', 'user_type',       null, $roles,   'class = "inputSelectLong"');
        $form -> addElement('select', 'specific_course', null, $courses, 'id = "course_recipients" 			 class = "inputSelectLong" disabled = "disabled"');
        $form -> addElement('select', 'lesson',          null, $lessons, 'id = "lesson_recipients" 			 class = "inputSelectLong" disabled = "disabled"');
        $form -> addElement('select', 'professor',       null, $lessons, 'id = "lesson_professor_recipients" class = "inputSelectLong" disabled = "disabled"');
        $form -> addElement('advcheckbox', 'specific_course_completed', _COMPLETED, null, 'class = "inputCheckbox" id="specific_course_completed_check" style="visibility:hidden" checked=""');

        $form -> addRule('lesson', _INVALIDFIELDDATA, 'checkParameter', 'id');

        $form -> setDefaults(array('recipients' => 'only_specific_users'));

        // Hidden for maintaining the previous_url value
        $form -> addElement('hidden', 'previous_url', null, 'id="previous_url"');

        $previous_url = $_SERVER['HTTP_REFERER'];

        // Fix: Refreshing while in new_message led to the top page being set as previous_url. This led to nested [sidebar|[sidebar|mainframe]]
        // as the entire *page was loaded after the message sending into the mainframe
        if (strpos($previous_url, "administratorpage") || strpos($previous_url, "studentpage") || strpos($previous_url, "professorpage")) {
            $previous_url = "messages_index.php";
        }

        if ($position = strpos($previous_url, "&message")) {
            $previous_url = substr($previous_url, 0, $position);
        }
        if ($position2 = strpos($previous_url, "sidebar")) {

        } else if ($position3 = strpos($previous_url, "show_profile")) {
            $form -> setDefaults(array( 'previous_url'     =>  "?new_message.php"));
        } else {
            $form -> setDefaults(array( 'previous_url'     =>  $previous_url));
        }

        /* **************************************************** */
        /** MODULE HCD: Insert new radio buttons for more recipient options **/
        /* **************************************************** */
        // GGET DATA FOR CREATING THE SELECTS
        if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
        	require_once ($path."module_hcd_tools.php");
        
        	$branchesTree = new EfrontBranchesTree();
        	$branches_list = $branchesTree->toPathStringShortened();
        	 
        	if (!empty($branches_list)) {
        		$smarty -> assign("T_BRANCHES", 1);
        		if ($_SESSION['s_type'] != 'administrator') {
        			if ($currentEmployee->isSupervisor()) {
        				foreach ($currentEmployee->getSupervisedBranches() as $value) {
        					$employee_branches[] = $value['branch_ID'];
        				}        				
        			} else {
        				$employee_branches = $currentEmployee->getBranches(true);
        			}
        			
        			if (!empty($employee_branches)) {
        				foreach ($branches_list as $key => $value) {
        					if (!in_array($key, $employee_branches)) {
        						unset($branches_list[$key]);
        					}
        				}
        			} else {
        				$smarty -> assign("T_BRANCHES", 0);
        			}
        		}
        		if ($grant_full_access) {
        			
        			if ($_SESSION['s_type'] != 'administrator') {
        				$job_descriptions = eF_getTableData("module_hcd_job_description", "distinct description","branch_ID in (".implode(",", $employee_branches).")");        				
        			} else {
        				$job_descriptions = eF_getTableData("module_hcd_job_description", "distinct description","");
        			}
        			if (!empty($job_descriptions)) {
        				$job_description_list = array("0" => _ANYJOBDESCRIPTION);
        				foreach ($job_descriptions as $job_description) {
        					$log = $job_description['description'];
        					$job_description_list["$log"] = $job_description['description'];
        				}
        			} else {
        				$job_description_list["0"] = _NOJOBDESCRIPTIONSSHAVEBEENREGISTERED;
        				$disable_job_descriptions = "disabled=\"disabled\"";
        			}
        			 
        			$skills = eF_getTableData("module_hcd_skills", "skill_ID, description","");
        			$skills_list = array();
        			if (!empty($skills)) {
        				foreach ($skills as $skill) {
        					$log = $skill['skill_ID'];
        					$skills_list["$log"] = $skill['description'];
        				}
        			} else {
        				$skills_list["0"] = _NOSKILLSHAVEBEENREGISTERED;
        				$disable_skills = "disabled=\"disabled\"";
        			}
        		}
        	} else {
                $branches_list = array("0" => _NOBRANCHESHAVEBEENREGISTERED);
                //        $branches_list[0] = _NOBRANCHESHAVEBEENREGISTERED;
                $disable_branches = "disabled=\"disabled\"";
            }

            $form -> addElement('radio', 'recipients', null, null, 'to_supervisors', 		'onclick = "eF_js_selectRecipients(\'to_supervisors\')"');
            $form -> addElement('radio', 'recipients', null, null, 'to_branch_supervisors', 'onclick = "eF_js_selectRecipients(\'to_branch_supervisors\')"');

            $form -> addElement('radio', 'recipients', null, null, 'specific_branch_job_description', $disable_branches . ' onclick = "eF_js_selectRecipients(\'specific_branch_job_description\')"');
            $form -> addElement('select', 'branch_recipients', null, $branches_list, 'id = "branch_recipients" class = "inputSelectLong" disabled = "disabled"');
            $form -> addElement('checkbox', 'include_subbranches', _INCLUDESUBBRANCHES, null, 'class = "inputCheckbox" id="include_subbranches" style="visibility:hidden" checked=""');

            $form -> addElement('radio', 'recipients', null, null, 'specific_job_description', $disable_job_descriptions . ' onclick = "eF_js_selectRecipients(\'specific_job_description\')"');
            $form -> addElement('select', 'job_description_recipients',null, $job_description_list, 'id = "job_description_recipients" class = "inputSelectLong" disabled = "disabled"');

            $form -> addElement('radio', 'recipients', null, null, 'specific_skill', $disable_skills . ' onclick = "eF_js_selectRecipients(\'specific_skill\')"');
            $form -> addElement('select', 'skill_recipients', null, $skills_list, 'id = "skill_recipients" class = "inputSelectLong" disabled = "disabled"');
        } #cpp#endif
        /**************************************************/

        // User groups in any case
        if ($grant_full_access) {
            $groups = eF_getTableData("groups", "id, name", "active=1");
        } else {
            $groups = eF_getTableData("groups JOIN users_to_groups", "id, name", "active=1 AND users_to_groups.groups_ID = groups.id AND users_to_groups.users_LOGIN = '".$currentUser->user['login']."'");
        }

        $groups_list = array();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $log = $group['id'];
                $groups_list["$log"] = $group['name'];
            }
        } else {
            $groups_list["0"] = _NOGROUPSDEFINED;
            $disable_groups = "disabled=\"disabled\"";
        }

        $form -> addElement('radio', 'recipients', null, null, 'specific_group', $disable_groups . ' onclick = "eF_js_selectRecipients(\'specific_group\')"');
        $form -> addElement('select', 'group_recipients', null, $groups_list, 'id = "group_recipients" class = "inputSelectLong" disabled = "disabled"');

		$form -> addElement('text', 'recipient', _RECIPIENT, 'id = "autocomplete" class = "inputText autoCompleteTextBox" onKeyDown="if (!additional_recipients_hidden) { show_hide_additional_recipients();}" ');
		//$form -> addElement('text', 'recipient_shown', _RECIPIENT, 'id = "autocomplete" class = "inputText autoCompleteTextBox" onKeyDown="if (!additional_recipients_hidden) { show_hide_additional_recipients();}" ');
		//$form->addElement('hidden','recipient',null,'id = "recipient"');

		$form -> addElement('text', 'subject',   _SUBJECT,   'id = "msg_subject" class = "inputText" style = "width:400px"');
        $form -> addElement('file', 'attachment[0]', _ATTACHMENT, null, 'class = "inputText"');
        $form -> addElement('checkbox', 'email', _SENDASEMAILALSO, null, 'id = "send_as_email" class = "inputCheckBox"');
        if ($_SESSION['s_type'] != 'student') {
        	$form -> addElement('textarea', 'body', _BODY, 'class = "messageEditor" style = "width:100%;height:200px"');
        } else {
        	$form -> addElement('textarea', 'body', _BODY, 'style = "width:100%;height:200px"');
        }
        $form -> addElement('submit', 'submit_send_message',    _SENDMESSAGE,    'class = "flatButton"');
        $form -> addElement('submit', 'submit_preview_message', _PREVIEWMESSAGE, 'class = "flatButton"');

        if (isset($_GET['recipient'])) {

        	// Multiple recipients can be pre-defined by having their logins separated with ;
        	$predefined_recipients_array = explode(";",$_GET['recipient']);
        	$predefined_recipients = "";
        	foreach ($predefined_recipients_array as $recipient_login) {
        		if ($predefined_recipients != "") {
        			$predefined_recipients .= ";".formatLogin($recipient_login);
        		} else {
        			$predefined_recipients = formatLogin($recipient_login);
        		}

        	}
            $form -> setDefaults(array('recipient' => $predefined_recipients));
        }

        if (isset($_GET['reply']) && in_array($_GET['reply'], $legalValues) && eF_checkParameter($_GET['reply'], 'id')) {
            $recipient = eF_getTableData("f_personal_messages", "sender, title, body", "id=".$_GET['reply']);
            $form -> setDefaults(array('recipient' => formatLogin($recipient[0]['sender'])));
            $form -> setDefaults(array('subject' => "Re: " . $recipient[0]['title']));

            $previous_text = "\n\n\n------------------ " . _ORIGINALMESSAGE. " ------------------\n" . $recipient[0]['body'];
            $form -> setDefaults(array('body' => $previous_text));
        }
        if (isset($_GET['forward']) && in_array($_GET['forward'], $legalValues) && eF_checkParameter($_GET['forward'], 'id')) {
            $recipient = eF_getTableData("f_personal_messages", "sender, title, body", "id=".$_GET['forward']);
            //$form -> setDefaults(array('recipient' => $recipient[0]['sender']));
            $form -> setDefaults(array('subject' => "Fwd: " . $recipient[0]['title']));

            $previous_text = "\n\n\n------------------ " . _ORIGINALMESSAGE. " ------------------\n" . $recipient[0]['body'];
            $form -> setDefaults(array('body' => $previous_text));
        }
        if ($form -> isSubmitted() && $form -> validate()) {
            $values = $form -> exportValues();
                        
            if ($_SESSION['s_type'] == 'student') {
            	$values['subject'] = strip_tags($values['subject']);
            	$values['body'] = strip_tags($values['body']);
            }

			if ($values['recipient']) {				
				$result = eF_getTableData("users", "id,name,surname,login", "active=1 and archive=0");//@todo: change this, performance hog
				foreach ($result as $value) {
					$usernames[$value['login']] = formatLogin($value['login'], $value);
				}
				$flippedLogins = array_flip($usernames);
				if ($_admin_) {
					$flippedLogins[_ALLUSERS] =  "[*]";
				} elseif($_professor_){
					$flippedLogins[_MYSTUDENTS] =  "[*]";
				}
                //$values['recipient'] = str_replace(" ", "", $values['recipient']);
                $values['recipient'] = trim($values['recipient']);

                if (mb_substr($values['recipient'], -1) == ';') {                            //remove trailing ; character
                    $values['recipient'] = mb_substr($values['recipient'], 0, -1);
                }
                $recipientsTemp = explode(";", $values['recipient']);
				array_walk($recipientsTemp, 'trim');
				$recipients = array();
				foreach ($recipientsTemp as $key => $value) {
					if ($flippedLogins[$value] != '') {
						$recipients[] = $flippedLogins[$value];
					} else { // because of $GLOBALS['_usernames'][$key] = $value.' ('.$key.')' in formatLogin for common names
						$match = mb_substr($value , strpos($value, '(')+1, -1);
						if (in_array($match, array_keys($usernames)) === true) {
							$recipients[] = $match;
						}
					}
				}
                if (in_array("[*]", $recipients)){
                    if ($_admin_) {
                        $rec_users  = eF_getTableDataFlat("users", "login", "active=1");       // entry [*] means message for all system users
                        $recipients = array_merge($recipients, array_values($users));
                    } elseif($_professor_){
                        $rec_users   = $currentUser -> getProfessorStudents();
                        $recipients = array_merge($recipients, $rec_users);
                    }
                    unset($recipients[array_search('[*]', $recipients)]);
                }

                $recipients = array_combine(array_values($recipients),array_values($recipients));
            }

            //pr($recipients);
            switch ($form -> exportValue('recipients')) {
                // case 'all_users':
                //     $result = eF_getTableDataFlat("users", "login");
                //     break;
                case 'active_users':
                    $result = eF_getTableDataFlat("users", "login", "active=1");
                    $values['body'] = _THISPMISSENTALLUSERS.'<br />'.$values['body'];
                    break;
                case 'specific_lesson':
                    $result = eF_getTableDataFlat("users, users_to_lessons,lessons", "login", "users_to_lessons.archive=0 and lessons.archive=0 and users.active=1 AND users_to_lessons.active=1 AND users.login=users_to_lessons.users_LOGIN AND users_to_lessons.lessons_ID=lessons.id AND users_to_lessons.lessons_ID=".($form -> exportValue('lesson')));
                    $lesson = new EfrontLesson($form -> exportValue('lesson'));
                    $values['body'] = _THISPMISSENTLESSONUSERS.' <a href='.G_SERVERNAME.'##EFRONTINNERLINK##.php?lessons_ID='.$form -> exportValue('lesson').'>'.$lesson->lesson['name'].'</a><br />'.$values['body'];
                    break;
                case 'specific_course':
                    $course = new EfrontCourse($form -> exportValue('specific_course'));
                    if ($_POST['specific_course_completed']) {
                        $and_completed_criterium = " AND users_to_courses.completed = 1 ";
                        $values['body'] = _THISPMISSENTCOMPLETEDCOURSEUSERS.' '.$course->course['name'].'<br />'.$values['body'];
                    } else {
                        $and_completed_criterium = " AND users_to_courses.completed = 0 ";
                        $values['body'] = _THISPMISSENTCOURSEUSERS.' '.$course->course['name'].'<br />'.$values['body'];
                    }
                    $result = eF_getTableDataFlat("users, users_to_courses", "login", "users.active=1 AND users_to_courses.active=1 AND users_to_courses.archive=0 AND users.login=users_to_courses.users_LOGIN " . $and_completed_criterium . " AND users_to_courses.courses_ID=".($form -> exportValue('specific_course')));
                    break;
                case 'specific_lesson_professor':
                     $result = eF_getTableDataFlat("users, users_to_lessons,lessons", "login", "users_to_lessons.archive=0 and lessons.archive=0 and users.active=1 AND users_to_lessons.active=1 AND users_to_lessons.user_type = 'professor' AND users.login=users_to_lessons.users_LOGIN AND users_to_lessons.lessons_ID=lessons.id  AND users_to_lessons.lessons_ID=".($form -> exportValue('professor')));
                     $lesson = new EfrontLesson($form -> exportValue('professor'));
                    $values['body'] = _THISPMISSENTLESSONPROFESSORS.' <a href='.G_SERVERNAME.'##EFRONTINNERLINK##.php?lessons_ID='.$form -> exportValue('professor').'>'.$lesson->lesson['name'].'</a><br />'.$values['body'];
                    break;
                case 'specific_user':
                    $result = eF_getTableDataFlat("users", "login", "login = '".($form -> exportValue('user'))."'");
                    $values['body'] = _THISPMISSENTSPECIFICUSERS.'<br />'.$values['body'];
                    break;
                case 'specific_group':
                    $result = eF_getTableDataFlat("users JOIN users_to_groups ON users.login = users_to_groups.users_LOGIN","distinct login", "users_to_groups.groups_ID = '".$form -> exportValue('group_recipients') ."'");
                    $userGroup = eF_getTableData("groups","name","id=".$form -> exportValue('group_recipients'));
                    $values['body'] = _THISPMISSENTUSERGROUP.' '.$userGroup[0]['name'].'<br />'.$values['body'];                    
                    break;
                    /** MODULE HCD: Create recipients list from the HCD selects -- NO if $module... needed here !!!**/
                case 'to_supervisors':
                    // Find all branches where this employee works
                    $branches_working = eF_getTableData("module_hcd_employee_works_at_branch JOIN module_hcd_branch ON module_hcd_employee_works_at_branch.branch_ID = module_hcd_branch.branch_ID","module_hcd_branch.*", "users_login = '".$currentUser -> user['login']."' AND assigned = '1'");

                    $supervising_branches = array();
                    foreach ($branches_working as $branch) {
                        // The $branches variable is defined above
                        $this_branch_sbs = eF_getBranchAncestors($branch, $branches);

                        if ($this_branch_sbs) {
                            $supervising_branches = array_merge($supervising_branches, $this_branch_sbs);
                        }
                        $supervising_branches = array_merge($supervising_branches, array($branch['branch_ID'] => $branch['branch_ID']));

                    }

                    $result = eF_getTableDataFlat("module_hcd_employee_works_at_branch", "distinct users_login as login", "supervisor = '1' AND branch_ID IN ('". implode( $supervising_branches, "','") ."')");
                    break;
                case 'to_branch_supervisors':
                    // Find all branches where this employee works
                    $branches_working = eF_getTableDataFlat("module_hcd_employee_works_at_branch JOIN module_hcd_branch ON module_hcd_employee_works_at_branch.branch_ID = module_hcd_branch.branch_ID","module_hcd_branch.branch_ID", "users_login = '".$currentUser -> user['login']."' AND assigned = '1'");
                    $result = eF_getTableDataFlat("module_hcd_employee_works_at_branch", "distinct users_login as login", "supervisor = '1' AND assigned='1' AND branch_ID IN ('". implode( $branches_working['branch_ID'], "','") ."')");
                    break;

                case 'specific_branch_job_description':
                    $branches_list = $form -> exportValue('branch_recipients');

                    if ($_POST['include_subbranches']) {
                        // Find all subbranches - the $branches array has been defined during the creation of the list
                        $subbranches = eF_subBranches($form -> exportValue('branch_recipients'),$branches);
                        $subbranches[] = $form -> exportValue('branch_recipients');
                        $branches_list .= "','" . implode(",",$subbranches);
                    }


                    if ($form -> exportValue('job_description_recipients') != "" && $form -> exportValue('job_description_recipients') != "0") {
                        $result = eF_getTableDataFlat("users JOIN module_hcd_employee_has_job_description ON users.login = module_hcd_employee_has_job_description.users_login JOIN module_hcd_job_description ON module_hcd_job_description.job_description_ID = module_hcd_employee_has_job_description.job_description_ID","distinct login", "users.active = 1 AND module_hcd_job_description.description = '".$form -> exportValue('job_description_recipients') ."' AND module_hcd_job_description.branch_ID IN ('".$branches_list."') ");
                    } else {
                        $result = eF_getTableDataFlat("users JOIN module_hcd_employee_works_at_branch ON users.login = module_hcd_employee_works_at_branch.users_login","distinct login", "users.active = 1 AND module_hcd_employee_works_at_branch.branch_ID IN ('".$branches_list."') AND module_hcd_employee_works_at_branch.assigned = '1'");
                    }
                    break;
                case 'specific_job_description':
                    if ($form -> exportValue('job_description_recipients') != "0") {
                        $result = eF_getTableDataFlat("users JOIN module_hcd_employee_has_job_description ON users.login = module_hcd_employee_has_job_description.users_login JOIN module_hcd_job_description ON module_hcd_job_description.job_description_ID = module_hcd_employee_has_job_description.job_description_ID","distinct login", "users.active = 1 AND module_hcd_job_description.description = '".$form -> exportValue('job_description_recipients') ."'");
                    } else {
                        $result = eF_getTableDataFlat("users JOIN module_hcd_employee_has_job_description ON users.login = module_hcd_employee_has_job_description.users_login","distinct login", "users.active = 1");
                    }

                    break;
                case 'specific_skill':
                    $result = eF_getTableDataFlat("users JOIN module_hcd_employee_has_skill ON users.login = module_hcd_employee_has_skill.users_login","distinct login", "users.active = 1 AND module_hcd_employee_has_skill.skill_ID = '".$form -> exportValue('skill_recipients') ."'");
                    break;


                default:
                    break;
            }
            
            if ($values['specific_type']) {
            	if (!is_numeric($form -> exportValue('user_type'))) {
            		$filter_user_type = eF_getTableDataFlat("users", "login", "users.active=1 AND users.user_type='".($form -> exportValue('user_type'))."'");
            	} else {
            		$filter_user_type = eF_getTableDataFlat("users", "login", "users.active=1 AND users.user_types_ID='".($form -> exportValue('user_type'))."'");
            	}
            	if ($values['recipients'] == 'only_specific_users') {	//Meaning we only clicked on the checkbox for user types
            		$result = $filter_user_type;
            	} else {
            		$result['login'] = array_intersect($result['login'], $filter_user_type['login']);
            	}            	
            }

            if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
            	$currentBranch = new EfrontBranch($_SESSION['s_current_branch']);
            	$branchTreeUsers = array_keys($currentBranch->getBranchTreeUsers());
            	foreach ($result['login'] as $key => $value) {
            		if (!in_array($value, $branchTreeUsers)) {
            			unset($result['login'][$key]);
            		}
            	}
            }

            // Using this approach to enable the merging of the two arrays
            if (!empty($result)) {
                $result = array_combine(array_values($result['login']),array_values($result['login']));
            }

            // Using the array_values function to form 0=>login1,1=>login2... instead of login1=>login1, login2=>login2
            if (isset($recipients) && !empty($result)) {
                $recipients = array_values(array_merge($recipients, $result));
            } else if (!empty($result)) {
                $recipients = array_values($result);
            }
            // else the $recipients = $recipients

            // If only a massive sent selection was used and no employee was found
            if (isset($recipients)) {
                $pm = new eF_PersonalMessage($currentUser -> user['login'], $recipients, $values['subject'], $values['body'], $values['bcc']);
                if ($_FILES['attachment']['name'][0] != "") {
                	$maxFileSize = FileSystemTree :: getUploadMaxSize();
                    if ($_FILES['attachment']['size'][0] == 0 || $_FILES['attachment']['size'][0] > $maxFileSize*1024 ) { //	G_MAXFILESIZE is deprecated                                                          //If the directory could not be created, display an erro message
                        $message      = _EACHFILESIZEMUSTBESMALLERTHAN." ".$maxFileSize._KB;
                        $message_type = 'failure';
                    }
                    //Upload user avatar file
                    $pm -> sender_attachment_timestamp = time();

                    $user_dir = G_UPLOADPATH.$currentUser -> user['login'].'/message_attachments/Sent/'.$pm -> sender_attachment_timestamp.'/';
                    mkdir($user_dir, 0755);
                    $filesystem = new FileSystemTree($user_dir);
                    try {
                        $uploadedFile = $filesystem -> uploadFile('attachment', $user_dir, 0);
	                    $pm -> sender_attachment_fileId =  $uploadedFile['id'];
	                    $pm -> setAttachment($uploadedFile['path']);
                    } catch (EfrontFileException $e) {
                    	
                        //echo $e -> getMessage();
                        $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
                        $message      = $e -> getMessage()."<br />";
                        $message_type = 'failure';

                    }
                }

                if ($pm -> send($values['email'], $values)) {
                    $message      .= _MESSAGEWASSENT;
                    $message_type = 'success';
		            if (!$popup) {
		                eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=messages&message=".rawurlencode($message).'&message_type='.$message_type);
		            }
                } else {
                    $message      .= $pm -> errorMessage;
                    $message_type = 'failure';
                }
            } else {
                $message      = _NORECIPIENTSHAVEBEENFOUND;
                $message_type = 'failure';
            }

        }

        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);                  //Create a smarty renderer

        $renderer -> setRequiredTemplate (
		   '{$html}{if $required}
		        &nbsp;<span class = "formRequired">*</span>
		    {/if}');

        $form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);          //Set javascript error messages
        $form -> setRequiredNote(_REQUIREDNOTE);
        $form -> accept($renderer);                                                     //Assign this form to the renderer, so that corresponding template code is created

        $smarty -> assign('T_ADD_MESSAGE_FORM', $renderer -> toArray());                     //Assign the form to the template

    } else if (isset($_GET['view']) && in_array($_GET['view'], $legalValues) && eF_checkParameter($_GET['view'], 'id')) {

    	$smarty -> assign("T_LAYOUT_CLASS", $currentTheme -> options['toolbar_position'] == "left" ? "hideRight" : "hideLeft");    //Whether to show the sidemenu on the left or on the right
        $currentMessage = $messages[$_GET['view']];

        //With this iterator, we find the previous and next messages in the same folder
        $it = new ArrayIterator(new ArrayObject($messages));
        while ($it -> valid() && $it -> key() != $currentMessage['id']) {
            $current = $it -> current();
            if ($current['f_folders_ID'] == $currentMessage['f_folders_ID']) {
                $previousMessage = $it -> key();
            }
            $it -> next();
        }
        while ($it -> valid() && !isset($nextMessage)) {
            $it -> next();
            $current = $it -> current();
            if ($current['f_folders_ID'] == $currentMessage['f_folders_ID']) {
                $nextMessage = $it -> key();
            }
        }

        $smarty -> assign("T_PREVIOUS_MESSAGE", $previousMessage);
        $smarty -> assign("T_NEXT_MESSAGE", $nextMessage);

        $currentMessage['body'] = str_replace("&nbsp;", " ", $currentMessage['body']);
        $currentMessage['body'] = html_entity_decode($currentMessage['body'], ENT_QUOTES);

        $recipients = explode(",", $currentMessage['recipient']);
        foreach ($recipients as $k => $login) {
            $recipients[$k] = formatLogin(trim($login));
        }
        $currentMessage['recipient'] = $recipients;

        $smarty -> assign("T_PERSONALMESSAGE", $currentMessage);

        if ($currentMessage['attachments']) {
            try {
                $attachment = new EfrontFile($currentMessage['attachments']);
                $smarty -> assign("T_ATTACHMENT", $attachment);
            } catch (Exception $e) {
                $message      = _ERROROPENINGATTACHMENT;
                $message_type = 'failure';
            }
        }
        eF_updateTableData("f_personal_messages", array("viewed" => 1), "id=".$currentMessage['id']);

    } else {
		$smarty -> assign("T_LAYOUT_CLASS", $currentTheme -> options['toolbar_position'] == "left" ? "hideRight" : "hideLeft");    //Whether to show the sidemenu on the left or on the right

	    $folderMessages = eF_getTableData("f_personal_messages", "*", "users_LOGIN='".$currentUser -> user['login']."' and f_folders_ID=".$currentFolder, "priority desc, viewed,timestamp desc");

        if (isset($_GET['ajax']) && $_GET['ajax'] == 'messagesTable') {
            isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'uint') ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

            if (isset($_GET['sort']) && eF_checkParameter($_GET['sort'], 'text')) {
                $sort = $_GET['sort'];
                isset($_GET['order']) && $_GET['order'] == 'desc' ? $order = 'desc' : $order = 'asc';
            } else {
                $sort = 'priority';
            }

            $smarty -> assign("T_MESSAGES_SIZE", sizeof($folderMessages));

            $folderMessages = eF_multiSort($folderMessages, $_GET['sort'], $order);
            if (isset($_GET['filter'])) {
                $folderMessages = eF_filterData($folderMessages , $_GET['filter']);
            }

            if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
                isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
                $folderMessages = array_slice($folderMessages, $offset, $limit);
            }

			foreach ($folderMessages as $key => $value) {
			    $recipients = explode(",", $folderMessages[$key]['recipient']);
			    foreach ($recipients as $k => $login) {
			        $recipients[$k] = formatLogin(trim($login));
			    }
			    $folderMessages[$key]['recipient'] = implode(", ", $recipients);
			}
            $smarty -> assign("T_MESSAGES", $folderMessages);
            //$smarty -> assign("T_MESSAGES_SIZE", sizeof($messages));
            $smarty -> display($currentUser -> user['user_type'].'.tpl');
            exit;
        }
    }

} catch (Exception $e) {
	handleNormalFlowExceptions($e);
}

