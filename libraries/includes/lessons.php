<?php

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

$loadScripts[] = 'scriptaculous/controls';
$loadScripts[] = 'includes/lessons';

if (isset($currentUser -> coreAccess['lessons']) && $currentUser -> coreAccess['lessons'] == 'hidden') {
	eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
}

if (isset($_GET['delete_lesson']) && eF_checkParameter($_GET['delete_lesson'], 'id')) {       //The administrator asked to delete a lesson
	if (isset($currentUser -> coreAccess['lessons']) && $currentUser -> coreAccess['lessons'] != 'change') {
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
		exit;
	}
	try {
		$lesson = new EfrontLesson($_GET['delete_lesson']);
		$lesson -> delete();
	} catch (Exception $e) {
		$message      = _SOMEPROBLEMEMERGED.': '.$e -> getMessage().' ('.$e -> getCode().')';
		header("HTTP/1.0 500 ");
		echo rawurlencode($e -> getMessage()).' ('.$e -> getCode().')';
	}
	exit;
} elseif (isset($_GET['archive_lesson']) && eF_checkParameter($_GET['archive_lesson'], 'login')) {    //The administrator asked to delete a lesson
	try {
		if (isset($currentUser -> coreAccess['lessons']) && $currentUser -> coreAccess['lessons'] != 'change') {
			throw new Exception(_UNAUTHORIZEDACCESS);
		}
		$lesson = new EfrontLesson($_GET['archive_lesson']);
		$lesson -> archive();
	} catch (Exception $e) {
		header("HTTP/1.0 500 ");
		echo rawurlencode($e -> getMessage()).' ('.$e -> getCode().')';
	}
	exit;
} elseif (isset($_GET['deactivate_lesson']) && eF_checkParameter($_GET['deactivate_lesson'], 'id')) {     //The administrator asked to deactivate a lesson
	if (isset($currentUser -> coreAccess['lessons']) && $currentUser -> coreAccess['lessons'] != 'hidden') {
		echo rawurlencode(_UNAUTHORIZEDACCESS);
		exit;
	}
	try {
		$lesson = new EfrontLesson($_GET['deactivate_lesson']);
		$lesson -> deactivate();
		echo "0";
	} catch (Exception $e) {
		$message      = _SOMEPROBLEMEMERGED.': '.$e -> getMessage().' ('.$e -> getCode().')';
		header("HTTP/1.0 500 ");
		echo urlencode($e -> getMessage()).' ('.$e -> getCode().')';
	}
	exit;
} elseif (isset($_GET['activate_lesson']) && eF_checkParameter($_GET['activate_lesson'], 'id')) {                //The administrator asked to activate a lesson
	if (isset($currentUser -> coreAccess['lessons']) && $currentUser -> coreAccess['lessons'] != 'change') {
		echo urlencode(_UNAUTHORIZEDACCESS);
		exit;
	}
	try {
		$lesson = new EfrontLesson($_GET['activate_lesson']);
		$lesson -> activate();
		echo "1";
	} catch (Exception $e) {
		$message      = _SOMEPROBLEMEMERGED.': '.$e -> getMessage().' ('.$e -> getCode().')';
		header("HTTP/1.0 500 ");
		echo urlencode($e -> getMessage()).' ('.$e -> getCode().')';
	}
	exit;
} elseif (isset($_GET['unset_course_only']) && eF_checkParameter($_GET['unset_course_only'], 'id')) {     //The administrator asked to deactivate a lesson
	if (isset($currentUser -> coreAccess['lessons']) && $currentUser -> coreAccess['lessons'] != 'change') {
		echo urlencode(_UNAUTHORIZEDACCESS);
		exit;
	}
	try {
		$lesson = new EfrontLesson($_GET['unset_course_only']);
		$lessonCourses = $lesson -> getCourses();
		if (!empty($lessonCourses)) {
			throw new Exception (_THISLESSONISPARTOFCOURSESANDCANNOTCHANGEMODE);
		}

		if (G_VERSIONTYPE == 'educational' ) { #cpp#ifdef EDUCATIONAL
			$lesson -> removeCoursesInheritedSkills();
			$lesson -> insertLessonSkill();
		} #cpp#endif

		$lesson -> lesson['course_only'] = 0;
		$lesson -> persist();
		echo "0";
	} catch (Exception $e) {
		handleAjaxExceptions($e);
	}
	exit;
} elseif (isset($_GET['set_course_only']) && eF_checkParameter($_GET['set_course_only'], 'id')) {                //The administrator asked to activate a lesson
	if (isset($currentUser -> coreAccess['lessons']) && $currentUser -> coreAccess['lessons'] != 'change') {
		echo urlencode(_UNAUTHORIZEDACCESS);
		exit;
	}
	try {
		$lesson = new EfrontLesson($_GET['set_course_only']);
		$result = eF_getTableData("users_to_lessons", "count(*)", "lessons_ID={$_GET['set_course_only']} and archive=0");
		if ($result[0]['count(*)'] > 0) {
			throw new Exception (_THISLESSONHASUSERSENROLLEDPLEASEREMOVEBEFORESWITCHINGMODE);
		}

		$lesson -> lesson['course_only'] = 1;

		if (G_VERSIONTYPE == 'educational' ) { #cpp#ifdef EDUCATIONAL
			$lesson -> deleteLessonSkill();
		} #cpp#endif

		$lesson -> persist();
		echo "1";
	} catch (Exception $e) {
		handleAjaxExceptions($e);
	}
	exit;
} elseif (isset($_GET['add_lesson']) || (isset($_GET['edit_lesson']) && eF_checkParameter($_GET['edit_lesson'], 'id'))) {        //The administrator asked to add or edit a lesson

	//Set the form post target in correspondance to the current function we are performing
	if (isset($_GET['add_lesson'])) {
		$post_target = 'add_lesson=1';
	} else {
		$post_target = 'edit_lesson='.$_GET['edit_lesson'];
		$smarty -> assign("T_LESSON_OPTIONS", array(array('text' => _LESSONSETTINGS,  'image' => "16x16/generic.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=lessons&lesson_settings=".$_GET['edit_lesson'])));
	}

	$form = new HTML_QuickForm("add_lessons_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=lessons&".$post_target, "", null, true);  //Build the form
	$form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');                                                   //Register our custom input check function
	$form -> addElement('text', 'name', _LESSONNAME, 'class = "inputText"');                    //The lesson name, it is required and of type 'text'
	$form -> addRule('name', _THEFIELD.' "'._LESSONNAME.'" '._ISMANDATORY, 'required', null, 'client');
	$form -> addRule('name', _INVALIDFIELDDATA, 'checkParameter', 'noscript');
	if ($GLOBALS['configuration']['onelanguage'] != true){
		$form -> addElement('select', 'languages_NAME', _LANGUAGE, EfrontSystem :: getLanguages(true, true));  //Add a language select box to the form
	}

	try {                                                                //If there are no direction set, redirect to add direction page
		$directionsTree = new EfrontDirectionsTree();
		if (sizeof($directionsTree -> tree) == 0) {
			eF_redirect(basename($_SERVER['PHP_SELF']).'?ctg=directions&add_direction=1&message='.urlencode(_YOUMUSTFIRSTCREATEDIRECTION).'&message_type=failure');
			exit;
		}
		$form -> addElement('select', 'directions_ID', _DIRECTION, $directionsTree -> toPathString());                    //Append a directions select box to the form
	} catch (Exception $e) {
		$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
		$message      = _SOMEPROBLEMOCCURED.': '.$e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
		$message_type = 'failure';
	}

	$form -> addElement('text', 'price', _PRICE, 'class = "inputText" style = "width:50px"');                        //Add the price, active and submit button to the form
	$form -> addElement('advcheckbox', 'active', _ACTIVENEUTRAL, null, null, array(0, 1));
	$form -> addElement('advcheckbox', 'show_catalog', _SHOWLESSONINCATALOG, null, null, array(0, 1));
	$courseOnly   = $form -> addElement('radio', 'course_only', _LESSONAVAILABLE, _COURSEONLY, 1, 'onclick = "$$(\'tr.only_lesson\').each(function(s) {s.hide()})"');
	$directAccess = $form -> addElement('radio', 'course_only', _LESSONAVAILABLE, _DIRECTLY, 0, 'onclick = "$$(\'tr.only_lesson\').each(function(s) {s.show()});if ($(\'recurring\').options[$(\'recurring\').selectedIndex].value == 0) {$(\'duration_row\').hide();}"');

	$recurringOptions   = array(0 => _NO, 'D' => _DAILY, 'W' => _WEEKLY, 'M' => _MONTHLY, 'Y' => _YEARLY);
	$recurringDurations = array('D' => array_combine(range(1, 90), range(1, 90)),
                                    'W' => array_combine(range(1, 52), range(1, 52)),
                                    'M' => array_combine(range(1, 24), range(1, 24)),
                                    'Y' => array_combine(range(1, 5), range(1, 5)));        //Imposed by paypal interface
	$form -> addElement('select', 'recurring', _SUBSCRIPTION, $recurringOptions, 'id = "recurring" onchange = "$(\'duration_row\').show();$$(\'span\').each(function (s) {if (s.id.match(\'_duration\')) {s.hide();}});if (this.selectedIndex) {$(this.options[this.selectedIndex].value+\'_duration\').show();} else {$(\'duration_row\').hide();}"');
	$form -> addElement('select', 'D_duration', _DAYSCONDITIONAL, $recurringDurations['D']);
	$form -> addElement('select', 'W_duration', _WEEKSCONDITIONAL, $recurringDurations['W']);
	$form -> addElement('select', 'M_duration', _MONTHSCONDITIONAL, $recurringDurations['M']);
	$form -> addElement('select', 'Y_duration', _YEARSCONDITIONAL, $recurringDurations['Y']);

	$lessons = EfrontLesson :: getLessons();
	$lessonsList = array(0 => _SELECTLESSON, -1 => '---------------');
	foreach ($lessons as $value) {
		$lessonsList[$value['id']] = $value['name'];
	}
	unset($lessonsList[$_GET['edit_lesson']]);

	$form -> addElement('text', 'max_users', _MAXIMUMUSERS, 'class = "inputText" style = "width:50px"');
	$form -> addElement('hidden', 'copy_properties', null, 'id="copy_properties"');
	//Convert to autocomplete input fields to show categories 
	//$form -> addElement('select', 'share_folder', _SHAREFOLDERWITH, $lessonsList, 'id = "share_folder" onchange = "$(\'clone_lesson\').options.selectedIndex=0;this.options.selectedIndex ? $(\'clone_lesson\').disabled = \'disabled\' : $(\'clone_lesson\').disabled = \'\'"');
	//$form -> addElement('select', 'clone_lesson', _CLONELESSON, $lessonsList, 'id = "clone_lesson" onchange = "$(\'share_folder\').options.selectedIndex=0;this.options.selectedIndex ? $(\'share_folder\').disabled = \'disabled\' : $(\'share_folder\').disabled = \'\'"');
	$form -> addElement('hidden', 'share_folder', null, 'id = "share_folder" onchange = "$(\'clone_lesson\').options.selectedIndex=0;this.options.selectedIndex ? $(\'clone_lesson\').disabled = \'disabled\' : $(\'clone_lesson\').disabled = \'\'"');
	$form -> addElement('hidden', 'clone_lesson', null, 'id = "clone_lesson" onchange = "$(\'share_folder\').options.selectedIndex=0;this.options.selectedIndex ? $(\'share_folder\').disabled = \'disabled\' : $(\'share_folder\').disabled = \'\'"');
	
	
	$form -> addElement('text', 'duration', _AVAILABLEFOR, 'style = "width:50px;"');
	$form -> addRule('duration', _THEFIELD.' "'._AVAILABLEFOR.'" '._MUSTBENUMERIC, 'numeric', null, 'client');
	$form -> addElement('text', 'access_limit', _AVAILABLEFOR, 'style = "width:50px;"');
	$form -> addRule('access_limit', _THEFIELD.' "'._AVAILABLEFOR.'" '._MUSTBENUMERIC, 'numeric', null, 'client');

	if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
/*		
		$currentEmployee = $currentUser -> aspects['hcd'];
		$_SESSION['employee_type'] = $currentEmployee -> getType();
		require_once "../libraries/module_hcd_tools.php";

		if ($currentEmployee -> getType() == _SUPERVISOR) {
			$branches = eF_getTableData("module_hcd_branch", "branch_ID, name, father_branch_ID","branch_ID IN (" . implode(",",$currentEmployee -> supervisesBranches). ")","father_branch_ID ASC,branch_ID ASC");
			// Show only existing branches
			$only_existing = 1;
		} else {
			$branches = eF_getTableData("module_hcd_branch", "branch_ID, name, father_branch_ID","","father_branch_ID ASC,branch_ID ASC");
			// Show all branches
			$only_existing = 0;
		}

		//$form -> addElement('select', 'branches_ID', _LOCATIONBRANCH, eF_createBranchesTreeSelect($branches,$only_existing), 'class = "inputText"');
		//$smarty -> assign("T_BRANCHES_FILTER", eF_createBranchesFilterSelect());
		  
*/
		$smarty -> assign("T_JOBS_FILTER", eF_createJobFilterSelect());

	} #cpp#endif

	if (isset($_GET['edit_lesson'])) {                                                          //If we are editing a lesson, we set the default form values to the ones stored in the database
		$editLesson = new EfrontLesson($_GET['edit_lesson']);		
		$form -> setDefaults(array('name'           => $editLesson -> lesson['name'],
                                   'active'         => $editLesson -> lesson['active'],
								   'show_catalog'	=> $editLesson -> lesson['show_catalog'],
                                   'course_only'    => $editLesson -> lesson['course_only'],
                                   'directions_ID'  => $editLesson -> lesson['directions_ID'],
                                   'languages_NAME' => $editLesson -> lesson['languages_NAME'],
                                   'duration'       => $editLesson -> lesson['duration']     ? $editLesson -> lesson['duration']     : '',
								   'access_limit'   => $editLesson -> lesson['access_limit'] ? $editLesson -> lesson['access_limit'] : '',
        						   'share_folder'	=> $editLesson -> lesson['share_folder'] ? $editLesson -> lesson['share_folder'] : 0,
                                   'max_users'	    => $editLesson -> lesson['max_users']    ? $editLesson -> lesson['max_users']    : null,
                                   'price'          => $editLesson -> lesson['price'],
                                   'recurring'      => $editLesson -> options['recurring'],
		$editLesson -> options['recurring'].'_duration' => $editLesson -> options['recurring_duration']));
		if ($editLesson -> lesson['share_folder']) {
			$shareFolderLesson = new EfrontLesson($editLesson -> lesson['share_folder']);
			$smarty -> assign("T_SHARE_FOLDER_WITH", $shareFolderLesson->lesson['name']);
		}

		if (($editLesson -> lesson['course_only'] && sizeof($editLesson -> getCourses()) > 0) || (!$editLesson -> lesson['course_only'])) {
			$result = eF_getTableData("users_to_lessons", "count(*)", "lessons_ID={$editLesson->lesson['id']} and archive=0");
			if ($result[0]['count(*)'] > 0) {
				$courseOnly   -> freeze();
				$directAccess -> freeze();
			}
		}

		$smarty -> assign("T_EDIT_LESSON", $editLesson);
	} else {
		//$form -> addElement('file', 'import_content', _UPLOADLESSONFILE, 'class = "inputText"');
		$form -> setDefaults(array('active'         => 1,                                              //For a new lesson, by default active is set to 1 and price to 0
                                   'show_catalog'   => 1,
								   'price'          => 0,
                                   'course_only'    => 1,
                                   'languages_NAME' => $GLOBALS['configuration']['default_language']));
	}

	if (isset($currentUser -> coreAccess['lessons']) && $currentUser -> coreAccess['lessons'] != 'change') {
		$form -> freeze();
	} else {
		$form -> addElement('submit', 'submit_lesson', _SUBMIT, 'class = "flatButton"');

		if ($form -> isSubmitted() && $form -> validate()) {                        //If the form is submitted and validated
			$values = $form -> exportValues();	
			$localeSettings = localeconv();			
						
			if (!$values['share_folder'] || !is_numeric($values['share_folder']) || !is_dir(G_LESSONSPATH.$values['share_folder']) || $_POST['autocomplete_share'] == '') {
				unset($values['share_folder']);
			}
			$GLOBALS['configuration']['onelanguage'] == true ? $languages_NAME = $GLOBALS['configuration']['default_language']: $languages_NAME = $values['languages_NAME'];
			if (isset($_GET['add_lesson'])) {                                             //The second case is when the administrator adds a new lesson
				$fields_insert = array('name'           => $values['name'],
                                       'languages_NAME' => $languages_NAME,
                                       'directions_ID'  => $values['directions_ID'],
                                       'active'         => $values['active'],
                                       'duration'       => $values['duration']     ? $values['duration']     : 0,
									   'access_limit'   => $values['access_limit'] ? $values['access_limit'] : 0,						
                                       'share_folder'	=> $values['share_folder'] ? $values['share_folder'] : 0,
                                       'max_users'	    => $values['max_users']    ? $values['max_users']    : null,
                					   'show_catalog'   => $values['show_catalog'],
                                       'course_only'    => $values['course_only'] == '' ? 0 : $values['course_only'],
                					   'created'		=> time(),
                					   'price'          => str_replace($localeSettings['decimal_point'], '.', $values['price']));


				try {
					//If we asked to copy properties for another lesson, initialize it and get its properties (except for recurring options, which are already defined in the same page)
					if ($values['copy_properties']) {
						$copyPropertiesLesson = new EfrontLesson($values['copy_properties']);
						unset($copyPropertiesLesson -> options['recurring']);
						unset($copyPropertiesLesson -> options['recurring_duration']);
						$fields_insert['options'] = serialize($copyPropertiesLesson -> options);
					}

					//Create the new lesson
					$newLesson = EfrontLesson :: createLesson($fields_insert);
					//If a recurring payment is set, set this up to the lesson properties
					if ($values['price'] && $values['recurring'] && in_array($values['recurring'], array_keys($recurringOptions))) {
						$newLesson -> options['recurring'] = $values['recurring'];
						if ($newLesson -> options['recurring']) {
							$newLesson -> options['recurring_duration'] = $form -> exportValue($newLesson -> options['recurring'].'_duration');
						}
						$newLesson -> persist();
					}
					//Import file, if any specified
					if ($values['clone_lesson']) {
						$cloneLesson  = new EfrontLesson($values['clone_lesson']);
						$file         = $cloneLesson -> export();
						$exportedFile = $file -> copy($newLesson -> getDirectory().'/'.$exportedFile['name']);
					}									
					if (isset($exportedFile)) {
						$newLesson   -> import($exportedFile);
					} else {
						//There was no file imported, then it's safe to add a default completion condition
						$fields = array('lessons_ID' => $newLesson -> lesson['id'],
			                            'type'       => 'all_units',
			                            'relation'   => 'and');
						eF_insertTableData('lesson_conditions', $fields);
					}
					if ($newLesson -> lesson['course_only']) {                //For course-only lessons, redirect to lessons list, not to "edit lesson" page
						eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=lessons&message=".urlencode(_SUCCESSFULLYCREATEDLESSON)."&message_type=success");
					} else {
						eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=lessons&edit_lesson=".($newLesson -> lesson['id'])."&tab=users&message=".urlencode(_SUCCESSFULLYCREATEDLESSON)."&message_type=success");
					}
				} catch (Exception $e) {
					$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
					$message      = _SOMEPROBLEMOCCURED.': '.$e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
					$message_type = 'failure';
				}
			} elseif (isset($_GET['edit_lesson'])) {                                                  //The first case is when the administrator is editing a lesson
				$fields_update = array('name'           => $values['name'],
                                       'directions_ID'  => $values['directions_ID'],
                                       'languages_NAME' => $languages_NAME,
                                       'active'         => $values['active'],
                                       'duration'       => $values['duration']     ? $values['duration']     : 0,
									   'access_limit'   => $values['access_limit'] ? $values['access_limit'] : 0,
                                       'share_folder'	=> $values['share_folder'] ? $values['share_folder'] : 0,
                                       'max_users'	    => $values['max_users']    ? $values['max_users']    : null,
									   'show_catalog'   => $values['show_catalog'],
                                       'course_only'    => $values['course_only'],
                					   'price'          => str_replace($localeSettings['decimal_point'], '.', $values['price']));
				if ($values['copy_properties']) {
					$copyPropertiesLesson = new EfrontLesson($values['copy_properties']);
					unset($copyPropertiesLesson -> options['recurring']);
					unset($copyPropertiesLesson -> options['recurring_duration']);
					$editLesson -> options = $copyPropertiesLesson -> options;
				}

				$editLesson -> lesson = array_merge($editLesson -> lesson, $fields_update);

				if ($values['price'] && $values['recurring'] && in_array($values['recurring'], array_keys($recurringOptions))) {
					$editLesson -> options['recurring'] = $values['recurring'];
					if ($editLesson -> options['recurring']) {
						$editLesson -> options['recurring_duration'] = $form -> exportValue($editLesson -> options['recurring'].'_duration');
					}
				} else {
					unset($editLesson -> options['recurring']);
				}
				try {
					$editLesson -> persist();

					$lesson_forum = eF_getTableData("f_forums", "id", "lessons_ID=".$_GET['edit_lesson']);                  //update lesson's forum names as well
					if (sizeof($lesson_forum) > 0) {
						eF_updateTableData("f_forums", array('title' => $values['name']), "id=".$lesson_forum[0]['id']);
					}
					eF_redirect(basename(basename($_SERVER['PHP_SELF'])).'?ctg=lessons&message='.urlencode(_LESSONUPDATED).'&message_type=success');
				} catch (Exception $e) {
					$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
					$message      = _SOMEPROBLEMOCCURED.': '.$e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
					$message_type = 'failure';
				}
			}
		}
	}

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);                  //Create a smarty renderer
	$renderer -> setRequiredTemplate (
           '{$html}{if $required}
           		&nbsp;<span class = "formRequired">*</span>
            {/if}');
	$renderer->setErrorTemplate(
	       '{$html}{if $error}
	            <div class = "formError">{$error}</div>
	        {/if}'
	        );

	        $form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);          //Set javascript error messages
	        $form -> setRequiredNote(_REQUIREDNOTE);
	        $form -> accept($renderer);                                                     //Assign this form to the renderer, so that corresponding template code is created

	        $smarty -> assign('T_LESSON_FORM', $renderer -> toArray());                     //Assign the form to the template

	        if (G_VERSIONTYPE == 'enterprise') {	#cpp#ifdef ENTERPRISE
	        	/** MODULE HCD: Submission of skills **/
	        	/*******************************************
	        	 SUBMISSION OF SKILLS (LESSON TO SKILLS)
	        	 *******************************************/

	        	/* Ajax assignments/removals of the skill to employees */
	        	if (isset($_GET['postAjaxRequest'])) {
	        		if (isset($_GET['add_skill'])) {

	        			/* Ajax assignment of  skill */
	        			if ($_GET['insert'] == "true") {
	        				$editLesson -> assignSkill($_GET['add_skill'], $_GET['specification']);
	        			} else if ($_GET['insert'] == "false") {
	        				$editLesson -> removeSkill($_GET['add_skill']);
	        			} else if (isset($_GET['addAll'])) {
	        				$skills = $editLesson -> getSkills();
	        				isset($_GET['filter']) ? $skills = eF_filterData($skills, $_GET['filter']) : null;
	        				foreach ($skills as $skill) {
	        					if ($skill['lesson_ID'] == "") {
	        						$editLesson -> assignSkill($skill['skill_ID'], "");
	        					}
	        				}
	        			} else if (isset($_GET['removeAll'])) {
	        				$skills = $editLesson -> getSkills();
	        				isset($_GET['filter']) ? $skills = eF_filterData($skills, $_GET['filter']) : null;
	        				foreach ($skills as $skill) {
	        					if ($skill['lesson_ID'] == $editLesson -> lesson['id']) {
	        						$editLesson -> removeSkill($skill['skill_ID']);
	        					}
	        				}
	        			}
	        			exit;
	        		} else if (isset($_GET['add_branch'])) {
	        			/* Ajax assignment of  branch */
	        			if ($_GET['insert'] == "true") {
	        				$editLesson -> assignBranch($_GET['add_branch']);
	        			} else if ($_GET['insert'] == "false") {
	        				$editLesson -> removeBranch($_GET['add_branch']);
	        			} else if (isset($_GET['addAll'])) {
	        				$branches = $editLesson -> getBranches();
	        				isset($_GET['filter']) ? $branches = eF_filterData($branches, $_GET['filter']) : null;

	        				foreach ($branches as $branch) {
	        					if ($branch['lessons_ID'] == "") {
	        						$editLesson -> assignBranch($branch['branches_ID']);
	        					}
	        				}
	        			} else if (isset($_GET['removeAll'])) {
	        				$branches = $editLesson -> getBranches();
	        				isset($_GET['filter']) ? $branches = eF_filterData($branches, $_GET['filter']) : null;
	        				foreach ($branches as $branch) {
	        					if ($branch['lessons_ID'] == $editLesson -> lesson['id']) {
	        						$editLesson -> removeBranch($branch['branches_ID']);
	        					}
	        				}
	        			}
	        			exit;
	        		}

	        	}
	        } #cpp#endif

	        if (isset($_GET['edit_lesson'])) {                                          //If we are editing a lesson, get the information needed to build the users to lesson list
	        	try {
	        		if ($editLesson -> lesson['course_only']) {
	        			$smarty -> assign("T_STANDALONE_LESSON", 0);
	        		} else {
	        			$smarty -> assign("T_STANDALONE_LESSON", 1);
	        			if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
	        				/** MODULE HCD: Get all skills this lesson has to offer **/
	        				$skills = $editLesson -> getSkills();
	        				$result  = eF_getTableData("module_hcd_skill_categories", "id, description", "");
	        				foreach ($result as $value) {
	        					$skill_categories [$value['id']] = $value['description'];
	        				}
	        				
	        				foreach ($skills as $key => $value) {
	        					$skills[$key]['category'] =  $skill_categories[$value['categories_ID']];
	        				}
							$smarty -> assign("T_SKILLS_CATEGORIES", $skill_categories);  
							 			
	        				if (isset($_GET['ajax'])) {

	        					if ($_GET['ajax'] == 'skillsTable') {

	        						isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'uint') ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

	        						if (isset($_GET['sort']) && eF_checkParameter($_GET['sort'], 'text')) {
	        							$sort = $_GET['sort'];
	        							isset($_GET['order']) && $_GET['order'] == 'desc' ? $order = 'desc' : $order = 'asc';
	        						} else {
	        							$sort = 'description';
	        						}

	        						$skills = eF_multiSort($skills, $sort, $order);
	        						$smarty -> assign("T_SKILLS_SIZE", sizeof($skills));
	        						if (isset($_GET['filter'])) {
	        							$skills = eF_filterData($skills, $_GET['filter']);
	        						}
	        						if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
	        							isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
	        							$skills = array_slice($skills, $offset, $limit);
	        						}

	        						if (!empty($skills)) {
	        							$smarty -> assign("T_SKILLS", $skills);
	        						}
	        						$smarty -> display('administrator.tpl');
	        						exit;
	        					} else if ($_GET['ajax'] == 'branchesTable') {
	        						// Get branches associated with lesson
	        						$currentEmployee = $currentUser -> aspects['hcd'];
	        						$_SESSION['employee_type'] = $currentEmployee -> getType();
	        						if ($_SESSION['s_type'] == "administrator") {
	        							$permission_to_change = 1;
	        							$smarty -> assign("T_CHANGE_RIGHTS", $permission_to_change);
	        						} else if ($currentEmployee -> getType() == _SUPERVISOR) {
	        							$permission_to_change = 1;
	        							$smarty -> assign("T_CHANGE_RIGHTS", $permission_to_change);
	        						}

	        						isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'uint') ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

	        						if (isset($_GET['sort']) && eF_checkParameter($_GET['sort'], 'text')) {
	        							$sort = $_GET['sort'];
	        							isset($_GET['order']) && $_GET['order'] == 'desc' ? $order = 'desc' : $order = 'asc';
	        						} else {
	        							$sort = 'name';
	        						}

	        						if ($_SESSION['s_type'] == "administrator") {
	        							$branches = eF_getTableData("(module_hcd_branch LEFT OUTER JOIN module_hcd_employee_works_at_branch ON module_hcd_branch.branch_ID = module_hcd_employee_works_at_branch.branch_ID AND module_hcd_employee_works_at_branch.assigned = '1') LEFT OUTER JOIN module_hcd_branch as branch1 ON module_hcd_branch.father_branch_ID = branch1.branch_ID LEFT OUTER JOIN module_hcd_lesson_to_branch ON (module_hcd_lesson_to_branch.branches_ID = module_hcd_branch.branch_ID AND module_hcd_lesson_to_branch.lessons_ID = ".$_GET['edit_lesson'].") GROUP BY module_hcd_branch.branch_ID ORDER BY branch1.branch_ID", "module_hcd_branch.branch_ID, module_hcd_branch.name, module_hcd_branch.city, module_hcd_branch.address,  count(users_login) as employees,  branch1.branch_ID as father_ID, branch1.name as father, supervisor, module_hcd_lesson_to_branch.lessons_ID","");
	        						} else {
	        							$branches = eF_getTableData("(module_hcd_branch LEFT OUTER JOIN module_hcd_employee_works_at_branch ON module_hcd_branch.branch_ID = module_hcd_employee_works_at_branch.branch_ID AND module_hcd_employee_works_at_branch.assigned = '1') LEFT OUTER JOIN module_hcd_branch as branch1 ON module_hcd_branch.father_branch_ID = branch1.branch_ID LEFT OUTER JOIN module_hcd_lesson_to_branch ON (module_hcd_lesson_to_branch.branches_ID = module_hcd_branch.branch_ID AND module_hcd_lesson_to_branch.lessons_ID = ".$_GET['edit_lesson'].") WHERE module_hcd_branch.branch_ID IN (".$_SESSION['supervises_branches'].") GROUP BY module_hcd_branch.branch_ID ORDER BY branch1.branch_ID", "module_hcd_branch.name, module_hcd_branch.city, module_hcd_branch.address,  count(users_login) as employees,  module_hcd_branch.branch_ID, branch1.branch_ID as father_ID, branch1.name as father,module_hcd_lesson_to_branch.lessons_ID","");
	        						}

	        						if ($currentEmployee -> getType() == _SUPERVISOR) {
	        							$count = 0;
	        							for ($count = 0; $count < sizeof($branches); $count++) {
	        								if (in_array($branches[$count]['branch_ID'], $supervisor_at_branches['branch_ID'])) {
	        									$branches[$count]["supervisor"] = 1;
	        								} else {
	        									$branches[$count]["supervisor"] = 0;
	        								}

	        								if (in_array($branches[$count]['father_ID'], $supervisor_at_branches['branch_ID'])) {
	        									$branches[$count]["father_supervisor"] = 1;
	        								} else {
	        									$branches[$count]["father_supervisor"] = 0;
	        								}

	        							}
	        						}

	        						$branches = eF_multiSort($branches, $_GET['sort'], $order);
	        						if (isset($_GET['filter'])) {
	        							$branches = eF_filterData($branches, $_GET['filter']);
	        						}

	        						$smarty -> assign("T_BRANCHES_SIZE", sizeof($branches));
	        						if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
	        							isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
	        							$branches = array_slice($branches, $offset, $limit);
	        						}


	        						if(!empty($branches)) {
	        							$smarty -> assign("T_BRANCHES", $branches);
	        						}
	        						//pr($branches);
	        						$smarty -> display($_SESSION['s_type'].'.tpl');
	        						exit;

	        					} else if (isset($_GET['set_all_completed'])) {
	        						try {
	        							$roles = EfrontLessonUser::getLessonsRoles();
	        							
	        							$constraints   = array('archive' => false, 'active' => 1, 'return_objects' => false);
	        							$users         = $editLesson -> getLessonUsers($constraints);
	        							foreach ($users as $user) {
	        								if (EfrontLessonUser::isStudentRole($user['role'])) {
	        									$user = EfrontUserFactory :: factory($user['login'], false, $roles[$user['role']]);
	        									$user -> completeLesson($editLesson, 100);
	        								}
	        							}
	        							echo json_encode(array('status' => true));
	        						} catch (Exception $e) {
	        							handleAjaxExceptions($e);
	        						}
	        						exit;
	        					}

	        				} else {

	        					if (!empty($skills)) {
	        						$smarty -> assign("T_SKILLS", $skills);
	        						$smarty -> assign("T_SKILLS_SIZE", sizeof($skills));
	        					}
	        				}
	        			} #cpp#endif

	        		}
	        		
	        		$roles = EfrontLessonUser::getLessonsRoles(true);
	        		$smarty -> assign("T_ROLES", $roles);	       
	        		 		
	        		if (isset($_GET['ajax']) && $_GET['ajax'] == 'usersTable') {
	        			$constraints   = array('archive' => false, 'active' => 1, 'return_objects' => false) + createConstraintsFromSortedTable();
	        			$users         = $editLesson -> getLessonUsersIncludingUnassigned($constraints);
	        			$totalEntries  = $editLesson -> countLessonUsersIncludingUnassigned($constraints);
	        			
	        			foreach ($users as $key => $user) {
	        				if (!$user['has_lesson']) {
	        					$user['user_types_ID'] ? $users[$key]['role'] = $user['user_types_ID'] : $users[$key]['role'] = $user['user_type'];
	        				} 
	        			}
	        			$dataSource	   = $users;
	        			$tableName     = $_GET['ajax'];
	        			$alreadySorted = 1;
	        			$smarty -> assign("T_TABLE_SIZE", $totalEntries);
	        			
	        			include("sorted_table.php");
				        			
	        		}
/*
	        		$lessonUsers    = $editLesson -> getUsers();                        //Get all users that have this lesson
	        		$nonLessonUsers = $editLesson -> getNonUsers();                     //Get all the users that can, but don't, have this lesson
	        		
	        		$users = array_merge($lessonUsers, $nonLessonUsers);       //Merge users to a single array, which will be useful for displaying them

	        		$roles = EfrontLessonUser :: getLessonsRoles(true);
	        		
	        		//$roles = eF_getTableDataFlat("user_types", "*", "active=1 AND basic_user_type!='administrator'");    //Get available roles
	        		//sizeof($roles) > 0 ? $roles = array_combine($roles['id'], $roles['name']) : $roles = array();                                             //Match keys with values, it's more practical this way
	        		$roles = array('student' => _STUDENT, 'professor' => _PROFESSOR) + $roles;                     //Append basic user types to the beginning of the array
	        		
	        		if (isset($_GET['ajax']) && $_GET['ajax'] == 'usersTable') {
	        			if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
	        				$constraints   = createConstraintsFromSortedTable();
	        				if (isset($constraints['branch']) && $constraints['branch'] && $constraints['branch'] != "all") {
	        					$branchUsers = eF_getTableDataFlat("module_hcd_employee_works_at_branch", "users_login", "module_hcd_employee_works_at_branch.assigned = 1 and branch_ID=".$constraints['branch']);
	        					foreach ($users as $key => $value) {
	        						if (!in_array($key, $branchUsers['users_login'])) {
	        							unset($users[$key]);
	        						}
	        					}
	        				}

	        				if (isset($constraints['jobs']) && $constraints['jobs'] && $constraints['jobs'] != _ALLJOBS) {
	        					$jobUsers = eF_getTableDataFlat("module_hcd_employee_has_job_description ej, module_hcd_job_description jd", "users_login", "ej.job_description_ID=jd.job_description_ID and jd.description='".$constraints['jobs']."'");
	        					foreach ($users as $key => $value) {
	        						if (!in_array($key, $jobUsers['users_login'])) {
	        							unset($users[$key]);
	        						}
	        					}
	        				}
	        			} #cpp#endif

	        			isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'uint') ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

	        			if (isset($_GET['sort']) && eF_checkParameter($_GET['sort'], 'text')) {
	        				$sort = $_GET['sort'];
	        				isset($_GET['order']) && $_GET['order'] == 'desc' ? $order = 'desc' : $order = 'asc';
	        			} else {
	        				$sort = 'login';
	        			}
	        			$users = eF_multiSort($users, $sort, $order);
	        			     			
	        			if (isset($_GET['filter'])) {
	        				$users = eF_filterData($users, $_GET['filter']);
	        			}
	       
	        			$smarty -> assign("T_USERS_SIZE", sizeof($users));
	        			
	        			if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
	        				isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
	        				$users = array_slice($users, $offset, $limit, true);
	        			}
        			
	        			
	        			$smarty -> assign("T_ROLES", $roles);
	        			$smarty -> assign("T_ALL_USERS", $users);
	        			$smarty -> assign("T_LESSON_USERS", array_keys($lessonUsers));                                             //We assign separately the lesson's users, to know when to display the checkboxes as "checked"
	        			$smarty -> display('administrator.tpl');
	        			exit;
	        		}
*/	        		
	        	} catch (Exception $e) {
	        		handleNormalFlowExceptions($e);
	        	}


	        	try {
	        		if (isset($_GET['ajax']) && isset($_GET['reset_user'])) {
	        			$user = EfrontUserFactory :: factory($_GET['reset_user']);
	        			$user -> resetProgressInLesson($editLesson);
	        			exit;
	        		}
	        		if (isset($_GET['postAjaxRequest'])) {
	        			if (isset($_GET['login']) && eF_checkParameter($_GET['login'], 'login')) {	        				
	        				isset($_GET['user_type']) && in_array($_GET['user_type'], array_keys($roles)) ? $userType = $_GET['user_type'] : $userType = 'student';
	        				
	        				$result = eF_getTableData("users_to_lessons", "users_LOGIN, user_type", "archive = 0 and users_LOGIN='{$_GET['login']}' and lessons_ID={$editLesson->lesson['id']}");
	        				if (sizeof($result) == 0) {	        				
	        					$editLesson -> addUsers($_GET['login'], $userType);
	        				} else {
	        					$userType != $result[0]['user_type'] ? $editLesson -> setRoles($_GET['login'], $userType) : $editLesson -> archiveLessonUsers($_GET['login']);
	        				}
	        			} else if (isset($_GET['addAll'])) {
	        				
	        				$constraints   = array('archive' => false, 'active' => true, 'condition' => 'r.lessons_ID is null', 'return_objects' => false);
	        				$users = $editLesson->getLessonUsersIncludingUnassigned($constraints);
	        				
	        				isset($_GET['filter']) ? $users = eF_filterData($users, $_GET['filter']) : null;
	        				
	        				$userTypes = array();
	        				foreach ($users as $user) {
	        					$user['user_types_ID'] ? $userTypes[] = $user['user_types_ID'] : $userTypes[] = $user['user_type'];
	        				}
	        				 
	        				$editLesson -> addUsers($users, $userTypes);
	        			} else if (isset($_GET['removeAll'])) {
	        				$constraints   = array('archive' => false, 'active' => true, 'return_objects' => false);
	        				$users = $editLesson->getLessonUsers($constraints);
	        				
	        				isset($_GET['filter']) ? $users = eF_filterData($users, $_GET['filter']) : null;
	        				$editLesson -> archiveLessonUsers(array_keys($users));
	        			}
	        			exit;
	        		}
	        	} catch (Exception $e) {
	        		handleAjaxExceptions($e);
	        	}
	        }
} else if (isset($_GET['lesson_info']) && eF_checkParameter($_GET['lesson_info'], 'id')) {
	/***/
	require_once("lesson_information.php");
} else if (isset($_GET['lesson_settings']) && eF_checkParameter($_GET['lesson_settings'], 'id')) {
	$currentLesson = new EfrontLesson($_GET['lesson_settings']);
	$smarty -> assign("T_CURRENT_LESSON", $currentLesson);

	$loadScripts[] = 'scriptaculous/scriptaculous';
	$loadScripts[] = 'scriptaculous/effects';

	$baseUrl = 'ctg=lessons&lesson_settings='.$currentLesson -> lesson['id'];
	$smarty -> assign("T_BASE_URL", $baseUrl);
	require_once "lesson_settings.php";

} else {                                            //The default action is to just print a list with the lessons defined in the system
	//    $filesystem = new FileSystemTree(G_LESSONSPATH, true);
	$form = new HTML_QuickForm("import_lesson_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=lessons", "", null, true);  //Build the form
	$form -> addElement('file', 'import_content', _UPLOADLESSONFILE, 'class = "inputText"');
	$form -> setMaxFileSize(FileSystemTree :: getUploadMaxSize() * 1024);            //getUploadMaxSize returns size in KB
	$form -> addElement('submit', 'submit_lesson', _SUBMIT, 'class = "flatButton"');
	try {
		if ($form -> isSubmitted() && $form -> validate()) {                        //If the form is submitted and validated
			$directionsTree = new EfrontDirectionsTree();
			if (sizeof($directionsTree -> tree) == 0) {
				eF_redirect(basename($_SERVER['PHP_SELF']).'?ctg=directions&add_direction=1&message='.urlencode(_YOUMUSTFIRSTCREATEDIRECTION).'&message_type=failure');
				exit;
			}
			
			//changed because of #1462				
			$newLesson    = EfrontLesson :: createLesson();
			$filesystem   = new FileSystemTree($newLesson -> getDirectory(), true);
			$file         = $filesystem -> uploadFile('import_content', $newLesson -> getDirectory());
			$newLesson   -> import($file, false, true, true);
			$message 	  = _OPERATIONCOMPLETEDSUCCESSFULLY;
			$message_type = 'success';
		}
	} catch (EfrontFileException $e) {
		handleNormalFlowExceptions($e);
	}

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);                  //Create a smarty renderer
	$renderer -> setRequiredTemplate (
           '{$html}{if $required}
           		&nbsp;<span class = "formRequired">*</span>
            {/if}');
	$renderer->setErrorTemplate(
	       '{$html}{if $error}
	            <div class = "formError">{$error}</div>
	        {/if}'
	        );

	        $form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);          //Set javascript error messages
	        $form -> setRequiredNote(_REQUIREDNOTE);
	        $form -> accept($renderer);                                                     //Assign this form to the renderer, so that corresponding template code is created

	        $smarty -> assign('T_IMPORT_LESSON_FORM', $renderer -> toArray());                     //Assign the form to the template

	        if (isset($_GET['ajax']) && $_GET['ajax'] == 'lessonsTable') {
	        	$directionsTree = new EfrontDirectionsTree();
	        	$directionPaths = $directionsTree -> toPathString();
	        	$smarty->assign("T_DIRECTIONS_PATHS", $directionPaths);
	        	
	        	$constraints   = array('archive' => false) + createConstraintsFromSortedTable();
	        	$dataSource    = EfrontLesson::getAllLessons($constraints);
	        	$totalEntries  = EfrontLesson::countAllLessons($constraints);

	        	$tableName     = $_GET['ajax'];
	        	$alreadySorted = 1;
	        	$smarty -> assign("T_TABLE_SIZE", $totalEntries);
	        	
	        	include("sorted_table.php");
	        	
/*	        	
	        	$lessons        = EFrontLesson :: getLessons();

	        	if (G_VERSIONTYPE == 'enterprise') {
	        		$result  = eF_getTableDataFlat("lessons LEFT OUTER JOIN module_hcd_lesson_offers_skill ON module_hcd_lesson_offers_skill.lesson_ID = lessons.id","lessons.id, count(skill_ID) as skills_offered","lessons.archive=0","","id");
	        		foreach ($result['id'] as $key => $lesson_id) {
	        			if (isset($lessons[$lesson_id])) {
	        				$lessons[$lesson_id]['skills_offered'] = $result['skills_offered'][$key];
	        			}
	        		}
	        	}
	        	//Perform a query to get all the 'student' and 'student-like' users of every lesson
	        	
	        	$result = eF_getTableDataFlat("lessons l,users_to_lessons ul left outer join user_types ut on ul.user_type=ut.id", "l.id,count(*)", "ul.archive=0 and l.id=ul.lessons_ID and (ul.user_type='student' or (ul.user_type = ut.id and ut.basic_user_type = 'student'))", "", "l.id" );
	        	if (sizeof($result) > 0) {
	        		$lessonUsers = array_combine($result['id'], $result['count(*)']);
	        	}
	        	foreach ($lessons as $key => $lesson) {
	        		if (isset($lessonUsers[$key]) && !$lesson['course_only']) {
	        			$lessons[$key]['students'] = $lessonUsers[$key];
	        		} else {
	        			$lessons[$key]['students'] = 0;
	        		}
	        		if (isset($_COOKIE['toggle_active'])) {
	        			if (($_COOKIE['toggle_active'] == 1 && !$lesson['active']) || ($_COOKIE['toggle_active'] == -1 && $lesson['active'])) {
	        				unset($lessons[$key]);
	        			}
	        		}
	        	}	        	
	        	isset($_GET['limit']) ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

	        	if (isset($_GET['sort'])) {
	        		isset($_GET['order']) ? $order = $_GET['order'] : $order = 'asc';
	        		$lessons = eF_multiSort($lessons, $_GET['sort'], $order);
	        	}

	        	if (isset($_GET['filter'])) {
	        		$lessons = eF_filterData($lessons, $_GET['filter']);
	        	}
	        	$smarty -> assign("T_LESSONS_SIZE", sizeof($lessons));
	        	if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
	        		isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
	        		$lessons = array_slice($lessons, $offset, $limit);
	        	}

	        	foreach ($lessons as $key => $lesson) {
	        		$obj = new EfrontLesson($lesson);
	        		//$lessons[$key]['link'] = $obj -> toHTMLTooltipLink(basename($_SERVER['PHP_SELF']).'?ctg=lessons&edit_lesson='.$lesson['id']);
	        		$lessons[$key]['direction_name'] = $directionPaths[$lesson['directions_ID']];
	        		$lessons[$key]['price_string']   = $obj -> lesson['price_string'];
	        		//$lessons[$key]['students']       = sizeof($obj -> getUsers('student'));
	        	}
	        	$smarty -> assign("T_LESSONS_DATA", $lessons);

	        	$smarty -> display('administrator.tpl');
	        	exit;
*/	        	
	        }
}
