<?php

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

$loadScripts[] = 'scriptaculous/slider';

if (!$_change_) {
    throw new EfrontUserException(_UNAUTHORIZEDACCESS, EfrontUserException::RESTRICTED_USER_TYPE);
}

//This page has a file manager, so bring it on with the correct options
!$skillgap_tests ? $basedir = $currentLesson -> getDirectory() : $basedir = G_EXTERNALPATH ;
//Default options for the file manager
if (!isset($currentUser -> coreAccess['files']) || $currentUser -> coreAccess['files'] == 'change') {
    $options = array('lessons_ID' => $currentLesson -> lesson['id'], 'metadata' => 0);
} else {
    $options = array('delete'        => false,
            		 'edit'          => false,
            		 'share'         => false,
            		 'upload'        => false,
            		 'create_folder' => false,
            		 'zip'           => false,
            		 'lessons_ID'    => $currentLesson -> lesson['id'],
            		 'metadata'      => 0);
}
//Default url for the file manager
$url = basename($_SERVER['PHP_SELF']).'?ctg='.$_GET['ctg'].'&'.(isset($_GET['edit_test']) ? 'edit_test='.$_GET['edit_test'] : 'add_test=1');
$extraFileTools = array(array('image' => 'images/16x16/arrow_right.png', 'title' => _INSERTEDITOR, 'action' => 'insert_editor'));
/**The file manager*/
include "file_manager.php";

//This page also needs an editor and ASCIIMathML
$load_editor = true;
if ($configuration['math_content'] && $configuration['math_images']) {
    $loadScripts[] = 'ASCIIMath2Tex';
} elseif ($configuration['math_content']) {
    $loadScripts[] = 'ASCIIMathML';
}

if (isset($_GET['edit_test'])) {
    $currentTest = new EfrontTest($_GET['edit_test']);
    $testQuestions 	= $currentTest -> getQuestions();
    //if test contains a question from another lesson, display all lessons questions
	if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
		if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD  
    		if (EfrontUser::isOptionVisible('questions_pool') && !isset($_GET['showall'])) { 
    			$showAllFlag = false;
    			foreach ($testQuestions as $key => $value) {
		    		if ($value['lessons_ID'] != $_SESSION['s_lessons_ID']) {
    					$showAllFlag = true;
    				}
    			}
    			if ($showAllFlag) {
	    			$_GET['showall'] = 1;
    			}
    		}
    	} #cpp#endif
	} #cpp#endif		
}

//These will be needed throughout the page
$smarty -> assign("T_QUESTIONTYPESTRANSLATIONS", Question :: $questionTypes);//pr($question_types_translations);
$smarty -> assign("T_QUESTIONDIFFICULTYTRANSLATIONS", Question::$questionDifficulties);//pr($question_types_translations);

//This page has a file manager, so bring it on with the correct options
$skillgap_tests ? $basedir = G_ADMINPATH : $basedir = $currentLesson -> getDirectory();
//Default options for the file manager
if (!isset($currentUser -> coreAccess['files']) || $currentUser -> coreAccess['files'] == 'change') {
    $options = array('lessons_ID' => !$skillgap_tests ? $currentLesson -> lesson['id'] : false,
            				 'metadata'   => 0);
} else {
    $options = array('delete'        => false,
	            			 'edit'          => false,
	            			 'share'         => false,
	            			 'upload'        => false,
	            			 'create_folder' => false,
	            			 'zip'           => false,
	            			 'lessons_ID'    => !$skillgap_tests ? $currentLesson -> lesson['id'] : false,
	            			 'metadata'      => 0);
}

$loadScripts[] = 'scriptaculous/slider';
$load_editor = true;

$form = new HTML_QuickForm("create_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=".$_GET['ctg'].(isset($_GET['from_unit']) && in_array($_GET['from_unit'], $legalUnits) ? '&from_unit='.$_GET['from_unit'] : '').(isset($_GET['add_test']) ? '&add_test=1' : '&edit_test='.$_GET['edit_test']), "", null, true);

$form -> addElement('text', 'name', null, 'class = "inputText"');
$form -> addElement('text', 'duration', null, 'id = "test_duration" size = "5"');
$form -> addElement('text', 'redoable', null, 'size = "5"');
$form -> addElement('text', 'maintain_history', null, 'size = "5"');
$form -> addElement('text', 'mastery_score', _MASTERYSCORE, 'size = "5"');
$form -> addElement('text', 'test_password', _TESTPASSWORD, "class = 'inputText'");
$form -> addElement('advcheckbox', 'onebyone',          null, null, null, array(0, 1));
$form -> addElement('advcheckbox', 'only_forward',      null, null, null, array(0, 1));

//$form -> addElement('advcheckbox', 'given_answers',     null, null, null, array(0, 1));
//$form -> addElement('advcheckbox', 'show_score',    	null, null, null, array(0, 1));
//$form -> addElement('advcheckbox', 'answers',           null, null, 'id = "answers_checkbox" onclick = "if (this.checked && $(\'show_answers_if_pass_checkbox\').checked) {$(\'show_answers_if_pass_checkbox\').checked = false}"', array(0, 1));
//$form -> addElement('advcheckbox', 'show_answers_if_pass', null, null, 'id = "show_answers_if_pass_checkbox" onclick = "if (this.checked && $(\'answers_checkbox\').checked) {$(\'answers_checkbox\').checked = false}"', array(0, 1));
//$form -> addElement('advcheckbox', 'redirect',          null, null, null, array(0, 1));

$possible_actions = array(	0 => _SHOWATHANKYOUMESSAGE,
							1 => _SHOWONLYTEST,
							2 => _SHOWGIVENANSWERS,
							3 => _SHOWSCORE,
							4 => _SHOWRIGHTANSWERSNEW,
							5 => _SHOWANSWERSIFSTUDENTPASSEDNEW
						);
$form -> addElement('select', 'action_on_submit', _SUBMITACTION, $possible_actions);

$form -> addElement('advcheckbox', 'shuffle_answers',   null, null, null, array(0, 1));
$form -> addElement('advcheckbox', 'shuffle_questions', null, null, null, array(0, 1));
$form -> addElement('advcheckbox', 'pause_test',        null, null, null, array(0, 1));
$form -> addElement('advcheckbox', 'publish',           null, null, null, array(0, 1));
$form -> addElement('advcheckbox', 'display_list',      null, null, null, array(0, 1));
$form -> addElement('advcheckbox', 'display_weights',   null, null, null, array(0, 1));
$form -> addElement('advcheckbox', 'answer_all',   		null, null, null, array(0, 1));
$form -> addElement('advcheckbox', 'keep_best',   		null, null, null, array(0, 1));
$form -> addElement('advcheckbox', 'redo_wrong',   		null, null, null, array(0, 1));
$form -> addElement('textarea',    'description',       null, 'id="editor_content_data" class = "inputTestTextarea mceEditor" style = "width:100%;height:16em;"');

if ($_GET['ctg'] != 'feedback') {
	$form -> addRule('mastery_score', _RATEMUSTBEBETWEEN0100, 'callback', create_function('$a', 'return ($a >= 0 && $a <= 100);'));    //The score must be between 0 and 100
	$form -> addRule('mastery_score', _THEFIELD.' "'._MASTERYSCORE.'" '._MUSTBENUMERIC, 'numeric', null, 'client');
	$form -> addRule('duration', _THEFIELD.' "'._DURATIONINMINUTES.'" '._MUSTBENUMERIC, 'numeric', null, 'client');
	$form -> addRule('redoable', _THEFIELD.' "'._REDOABLE.'" '._MUSTBENUMERIC, 'numeric', null, 'client');
}
$form -> addRule('name', _THEFIELD.' "'._NAME.'" '._ISMANDATORY, 'required', null, 'client');


if (!$skillgap_tests) {
    $optionsArray = $currentContent -> toHTMLSelectOptions();    //Get the units as an array of formated strings, that can be used to form an HTML select list
    $select_units = & HTML_QuickForm :: createElement('select', 'parent_content', _UNITPARENT, null, 'class = "inputSelect"');
    $select_units -> addOption(_ROOTUNIT, 0);
    $select_units -> loadArray($optionsArray);
    $form -> addElement($select_units);
//	if ($_GET['ctg'] != 'feedback') {
		$form -> addRule('parent_content', _THEFIELD.' '._UNITPARENT.' '._ISMANDATORY, 'required', null, 'client');
		$form -> addRule('parent_content', _INVALIDID, 'numeric');
//	}

    isset($_GET['from_unit']) && eF_checkParameter($_GET['from_unit'], 'id') ? $selectedUnit = $_GET['from_unit'] : $selectedUnit = 0;
    $selectedUnit ? $units = $currentContent -> getNodeChildren($selectedUnit) : $units = $currentContent -> tree;
    foreach ($iterator = new EfrontAttributeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($units)), array('id', 'name')) as $key => $value) {
        $key == 'id' ? $ids[] = $value : $names[] = $value;
    }
    $unitNames    = array_combine($ids, $names);
    $unitNames[0] = _NONEUNIT;
    if ($_GET['showall'] && EfrontUser::isOptionVisible('questions_pool')) {  
        if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD	
    			$result       = eF_getTableData("questions,lessons", "questions.*", "lessons_ID !=0 and lessons.id=questions.lessons_ID and lessons.active=1", "content_ID ASC");     
			} #cpp#endif
        } #cpp#endif
    } else {
    	$result       = eF_getTableData("questions", "*", "lessons_ID=".$currentLesson -> lesson['id'], "content_ID ASC");     //Retrieve all questions that belong to this unit or its subunits
    	if (isset($_GET['edit_test'])) {
    		//for questions already added to test from another lesson
    		//$testQuestions 	= $currentTest -> getQuestions();  		
			$result 		= array_merge(array_values($result),array_values($testQuestions));
    	}
    }
    
    if ($_GET['showall'] && EfrontUser::isOptionVisible('questions_pool')) {  
		$directionsTree  = new EfrontDirectionsTree();
		$directionsPaths = $directionsTree -> toPathString();
		$lessons = EFrontLesson :: getLessons(false, true);
		foreach ($lessons as $key => $value) {
			$lessons[$key]['lesson_path'] =  $directionsPaths[$value['directions_ID']]." --> ".$value['name'];
		}
		$smarty -> assign("T_LESSONS", $lessons);    
    }
} else {

    $form -> addElement('text', 'general_threshold', null, 'class = "inputText"');
    $form->registerRule('decimal2digits','regex','/^\d{1,2}(\.\d{1,2})?$/');
    $form->addRule('general_threshold',_INVALIDFIELDDATAFORFIELD.' "'._GENERALTHRESHOLD.'": '. _NUMBERFROM000TO9999REQUIRED,'decimal2digits');
    // Set default value and if it is defined it will be overwritten - @hardcoded value 50 - could be set by admin in general
    $form -> setDefaults(array('general_threshold'    => "50.00"));

    $form -> addElement('advcheckbox', 'assign_to_new',          null, null, null, array(0, 1));
    $form -> addElement('advcheckbox', 'automatic_assignment',          null, null, null, array(0, 1));
    $form -> addElement('advcheckbox', 'student_results',          null, null, null, array(0, 1));
    $result = eF_getTableData("questions LEFT OUTER JOIN lessons ON  lessons_ID=lessons.id", "questions.*, lessons.name" , "type <> 'raw_text' and (lessons_ID=0 or lessons.archive=0)"); 	
			
}

$unitsToQuestionsDifficulties = array();
foreach ($result as $value) {
    $questions[$value['id']] = $value;
    if (!isset($unitsToQuestionsDifficulties[$value['content_ID']])){
        $unitsToQuestionsDifficulties[$value['content_ID']] = array();
    }
    if (!isset($unitsToQuestionsDifficulties[$value['content_ID']][$value['difficulty']])) {
        $unitsToQuestionsDifficulties[$value['content_ID']][$value['difficulty']] = 0;
    }
    $unitsToQuestionsDifficulties[$value['content_ID']][$value['difficulty']]++;

    if (!isset($unitsToQuestionsTypes[$value['content_ID']])) {
        $unitsToQuestionsTypes[$value['content_ID']] = array();
    }
    if (!isset($unitsToQuestionsTypes[$value['content_ID']][$value['type']])) {
        $unitsToQuestionsTypes[$value['content_ID']][$value['type']] = 0;
    }
    $unitsToQuestionsTypes[$value['content_ID']][$value['type']]++;
}

$smarty -> assign("T_UNITS_TO_QUESTIONS_DIFFICULTIES", $unitsToQuestionsDifficulties);
$smarty -> assign("T_UNITS_TO_QUESTIONS_TYPES", $unitsToQuestionsTypes);
//pr($unitsToQuestions);
if (!$skillgap_tests) {
    $smarty -> assign("T_UNITS_NAMES", $unitNames);
}
$smarty -> assign("T_QUESTION_DIFFICULTIES", Question::$questionDifficulties);
$smarty -> assign("T_QUESTION_DIFFICULTIES_ICONS", Question::$questionDifficultiesIcons);
$smarty -> assign("T_QUESTION_TYPES", Question::$questionTypes);
$smarty -> assign("T_QUESTION_TYPES_ICONS", Question::$questionTypesIcons);

if (isset($_GET['add_test'])) {
    $form -> addElement('submit', 'submit_test', _SAVETESTANDADDQUESTIONS, 'class = "flatButton"');
  /*  $form -> setDefaults(array('given_answers'    => 1,
                               'answers'          => 0,
    						   'show_answers_if_pass' => 1,
    						   'show_score'		  => 1,
                               'maintain_history' => 5,
                               'publish'          => 1,
    						   'keep_best'        => 0,
                               'mastery_score'    => $_GET['ctg'] != 'feedback' ? 50 : 0,
            				   'redoable'	      => 1));
    */
        $form -> setDefaults(array('action_on_submit'    => 5,
                               'maintain_history' => 5,
                               'publish'          => 1,
    						   'keep_best'        => 0,
                               'mastery_score'    => $_GET['ctg'] != 'feedback' ? 50 : 0,
            				   'redoable'	      => 1));
    if (isset($_GET['from_unit'])) {
        $form -> setDefaults(array('parent_content' => $_GET['from_unit']));
    }
} else if (isset($_GET['edit_test'])) {

    if (!$skillgap_tests) {
        $testUnit = new EfrontUnit($currentTest -> test['content_ID']);
    }
	if ($_GET['ctg'] != 'feedback') {
		$form -> addElement('submit', 'submit_test', _SAVETEST, 'class = "flatButton"');
		$form -> addElement('submit', 'submit_test_new', _SAVEASNEWTEST, 'class = "flatButton"');
	} else {
		$form -> addElement('submit', 'submit_test', _SAVE, 'class = "flatButton"');
	}
    $form -> freeze('parent_content');
    $form -> setDefaults($currentTest -> options); 
 	if ($currentTest -> options['redirect']) {
 		$submitaction = 0;
 	} elseif ($currentTest -> options['show_answers_if_pass']) {
 		$submitaction = 5;
 	} elseif ($currentTest -> options['answers']) {
 		$submitaction = 4;
 	} elseif ($currentTest -> options['show_score']) {
 		$submitaction = 3;
 	} elseif ($currentTest -> options['given_answers']) {
 		$submitaction = 2;
 	} else {
 		$submitaction = 1;
 	}
    $form -> setDefaults(array('name'              => $currentTest -> test['name'],
                               'duration'          => $currentTest -> options['duration'] ? round($currentTest -> options['duration'] / 60) : '',   //Duration is displayed in minutes, but is stored in seconds
                               'redoable'          => $currentTest -> options['redoable'] ? $currentTest -> options['redoable'] : '',
                               'publish'           => $currentTest -> test['publish'],
                               'keep_best'         => $currentTest -> test['keep_best'],
    						   'description'       => $currentTest -> test['description'],
    						   'action_on_submit'  => $submitaction,
                               'mastery_score'     => $currentTest -> test['mastery_score']));

    if (!$skillgap_tests) {
        $form -> setDefaults(array('parent_content'    => $testUnit['parent_content_ID']));
    }

    $smarty -> assign("T_CURRENT_TEST", $currentTest);
    $testQuestions = $currentTest -> getQuestions();

    $stats = $currentTest -> questionsInfo();
    $stats['duration'] 	  = eF_convertIntervalToTime($stats['total_duration']);
    $stats['random_pool'] = $currentTest -> options['random_pool'];
    $stats['user_configurable'] = $currentTest -> options['user_configurable'];
    $stats['show_incomplete'] = $currentTest -> options['show_incomplete'];

    $smarty -> assign("T_TEST_QUESTIONS_STATISTICS", $stats);
}

if ($form -> isSubmitted() && $form -> validate()) {
    $values = $form -> exportValues();
    
    switch ($values['action_on_submit']) {
            case 0 : 
            	$values['redirect'] = 1;
            	$values['given_answers'] = $values['show_score'] = $values['answers']= $values['show_answers_if_pass'] = 0;
            break;
            case 1 : 
            	$values['redirect'] = $values['given_answers'] = $values['show_score'] = $values['answers']= $values['show_answers_if_pass'] = 0;
            break;
            case 2 : 
            	$values['given_answers'] = 1;
            	$values['redirect'] = $values['show_score'] = $values['answers'] = $values['show_answers_if_pass'] = 0;
            break;
            case 3 : 
            	$values['given_answers'] = $values['show_score'] = 1;
            	$values['redirect'] =  $values['answers'] = $values['show_answers_if_pass'] = 0;
            break;
            case 4 : 
            	$values['given_answers'] = $values['show_score'] = $values['answers'] = 1;
            	$values['redirect'] =   $values['show_answers_if_pass'] = 0;
            break;
            case 5 : 
            	$values['show_answers_if_pass'] = $values['given_answers'] = $values['show_score'] = 1;
            	$values['answers'] = $values['redirect'] = 0;
            break;
            
    }
    
    if ($_GET['ctg'] != 'feedback') {
		$testOptions = array('duration'         => $values['duration'] * 60,              //Duration is displayed in minutes, but is stored in seconds
                                'redoable'          => $values['redoable'] ? $values['redoable'] : 0,
                                'onebyone'          => $values['onebyone'],
                        		'only_forward'      => $values['only_forward'],
                                'given_answers'     => $values['given_answers'],
		 						'show_score'	    => $values['show_score'],
								'show_answers_if_pass' => $values['show_answers_if_pass'],
                                'maintain_history'  => $values['maintain_history'],
                                'answers'           => $values['answers'],
								'redirect'		    => $values['redirect'],
                                'shuffle_answers'   => $values['shuffle_answers'],
                                'shuffle_questions' => $values['shuffle_questions'],
                                'pause_test'        => $values['pause_test'],
                                'display_list'      => $values['display_list'],
                                'display_weights'   => $values['display_weights'],
								'answer_all'   		=> $values['only_forward'] ? 0 : $values['answer_all'],
								'test_password'   	=> $values['test_password'],
								'redo_wrong'   		=> $values['redo_wrong'],
                        		'general_threshold' => $values['general_threshold'],        //skill-gap option
                         	    'assign_to_new'     => $values['assign_to_new'],            //skill-gap option
                        		'automatic_assignment' => $values['automatic_assignment'], //skill-gap option
                        		'student_results' => $values['student_results']); //skill-gap option
    } else {
		$testOptions = array('duration'         => 0,              //Duration is displayed in minutes, but is stored in seconds
                                'redoable'          => 1,
                                'onebyone'          => 0,
                        		'only_forward'      => 0,
                                'given_answers'     => 0,
								'show_score' 		=> 0,
								'show_answers_if_pass' => 0,
                                'maintain_history'  => 1,
                                'answers'           => 0,
								'redirect'		    => 1,
                                'shuffle_answers'   => 0,
                                'shuffle_questions' => 0,
                                'pause_test'        => 0,
                                'display_list'      => 0,
                                'display_weights'   => 0,
								'answer_all'   		=> 1,
								'test_password'		=> '',
								'redo_wrong'   		=> 0,
                        		'general_threshold' => 0,        //skill-gap option
                         	    'assign_to_new'     => 0,            //skill-gap option
                        		'automatic_assignment' => 0,
								'student_results' => 0); //skill-gap option
	}
    if (isset($_GET['edit_test']) && !isset($values['submit_test_new'])) {
        $currentTest -> test['publish']       = $values['publish'];
        $currentTest -> test['keep_best']     = $values['keep_best'];
        $currentTest -> test['description']   = applyEditorOffset( $values['description']);
        $currentTest -> test['mastery_score'] = $values['mastery_score'] ? $values['mastery_score'] : 0;
        $currentTest -> test['name']          = $values['name'];

        $currentTest -> options = array_merge($currentTest -> options, $testOptions);
        $currentTest -> persist();

        if (!$skillgap_tests) {
            $testUnit['name']              = $values['name'];
            $testUnit['parent_content_ID'] = $values['parent_content'];
            $testUnit -> persist();
        }
		if ($_GET['ctg'] != 'feedback') {
			$messageString = _SUCCESFULLYMODIFIEDTEST;
		} else {
			$messageString = _SUCCESFULLYMODIFIEDFEEDBACK;
		}
		EfrontCache::getInstance()->deleteCache("content_tree:{$_SESSION['s_lessons_ID']}");
        eF_redirect("".ltrim(basename($_SERVER['PHP_SELF']), "/")."?ctg=".$_GET['ctg']."&from_unit=".$_GET['from_unit']."&message=".urlencode($messageString)."&message_type=success");
    } else {
        $contentFields = array('data'              => '',
                                       'name'              => $values['name'],
                                       'lessons_ID'        => $currentLesson -> lesson['id'],
                                       'ctg_type'          => $_GET['ctg'],
                                       'active'            => 1,
                                       'timestamp'         => time(),
                                      // 'parent_content_ID' => $_GET['ctg'] != 'feedback' ? $values['parent_content'] : 0);
									   'parent_content_ID' => $values['parent_content']);
        $testFields = array('active'            => 1,
		                            'lessons_ID'        => (isset($currentLesson -> lesson['id']))?$currentLesson -> lesson['id']:0,
		                            'content_ID'        => $test_content_ID,
		                            'description'       => applyEditorOffset($values['description']),
		                            'options'           => serialize($testOptions),
		                            'name'              => $values['name'],
		                            'publish'           => $values['publish'],
        							'keep_best'         => $values['keep_best'],
		                            'mastery_score'     => $values['mastery_score'] ? $values['mastery_score'] : 0);

        if (!$skillgap_tests) {
            $newUnit = $currentContent -> insertNode($contentFields);
            $newTest = EfrontTest :: createTest($newUnit, $testFields);
        } else {
            $newTest = EfrontTest :: createTest(false, $testFields);
        }
        // If the new test comes from an existing one we should also copy its questions...
        if ($_GET['edit_test']) {
            $testQuestions = $currentTest -> getQuestions();
            $newTest -> addQuestions($testQuestions);
            // ... and its users if it is a skillgap test
            if ($skillgap_tests) {
                $testUsers = eF_getTableDataFlat("users_to_skillgap_tests", "users_LOGIN", "tests_ID = '".$_GET['edit_test']."'");
                $fields    = array();
                foreach ($testUsers as $entry) {
                    $fields[] = array('tests_ID' => $newTest -> test['id'], 'users_LOGIN' => $entry['useres_LOGIN']);
                }
                if (sizeof($fields) > 0) {
                    eF_insertTableDataMultiple("users_to_skillgap_tests", $fields);
                    //$insertString = "('" . $newTest->test['id'] . "', '" . implode("'),('" . $newTest -> test['id'] . "', '", $testUsers['users_LOGIN']) . "')";
                    //eF_execute("INSERT INTO users_to_skillgap_tests (tests_ID,users_LOGIN) VALUES $insertString");
                }
            }
        }
		if ($_GET['ctg'] != 'feedback') {
			$messageString = _SUCCESFULLYMODIFIEDTEST;
		} else {
			$messageString = _SUCCESFULLYMODIFIEDFEEDBACK;
		}
		EfrontCache::getInstance()->deleteCache("content_tree:{$_SESSION['s_lessons_ID']}");
        eF_redirect("".ltrim(basename($_SERVER['PHP_SELF']), "/")."?ctg=".$_GET['ctg']."&edit_test=".$newTest -> test['id']."&from_unit=".$_GET['from_unit']."&tab=questions&&message=".urlencode($messageString)."&message_type=success");
    }
}
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

$form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
$form -> setRequiredNote(_REQUIREDNOTE);
$form -> accept($renderer);

$smarty -> assign('T_TEST_FORM', $renderer -> toArray());


// Code to find users to who a skillgap tests has been assigned
if ($skillgap_tests) {
    // AJAX CODE TO RELOAD SKILL-GAP TEST USERS
	$smarty -> assign("T_JOBS_FILTER", eF_createJobFilterSelect());
    if (isset($_GET['ajax']) && $_GET['ajax'] == 'testUsersTable') {
        isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'uint') ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

        if (isset($_GET['sort']) && eF_checkParameter($_GET['sort'], 'text')) {
            $sort = $_GET['sort'];
            isset($_GET['order']) && $_GET['order'] == 'desc' ? $order = 'desc' : $order = 'asc';
        } else {
            $sort = 'login';
        }

        $testUsers = eF_getTableData("users LEFT OUTER JOIN users_to_skillgap_tests ON login = users_login AND tests_ID = '".$_GET['edit_test']."'", "distinct login, name,surname,tests_ID as partof, solved", "users.user_type = 'student' and users.archive=0 and users.active=1");
        $test_info = eF_getTableData("completed_tests", "id, users_LOGIN", "status != 'deleted' and tests_ID = " . $_GET['edit_test']);

        if (isset($_GET['sort'])) {
            isset($_GET['order']) ? $order = $_GET['order'] : $order = 'asc';
            $testUsers = eF_multiSort($testUsers, $_GET['sort'], $order);
        }

        if (isset($_GET['filter'])) {
            $testUsers = eF_filterData($testUsers, $_GET['filter']);
        }

        $smarty -> assign('T_USERS_SIZE', sizeof($testUsers));

        $smarty -> assign("T_PROPOSED_LESSONS_SIZE", sizeof($testUsers));
        if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
            isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
            $testUsers = array_slice($testUsers, $offset, $limit);
        }

        // Find the completed test for each user
        foreach ($testUsers as $uid => $user) {
            foreach($test_info as $info) {
                if ($info['users_LOGIN']  == $user['login']) {
                    $testUsers[$uid]['completed_test_id'] = $info['id'];
                }
            }
        }

        $smarty -> assign('T_ALL_USERS', $testUsers);
        $smarty -> display($_SESSION['s_type'].'.tpl');
        exit;
    }
}
if (isset($_GET['ajax']) && $_GET['ajax'] == 'questionsTable') {
    // If no lesson then define the current lesson name => _SKILLGAPTESTS (used for correct filtering)
    foreach ($questions as $qid => $question) {
        $questions[$qid]['text']           = strip_tags($question['text']);        //If we ommit this line, then the questions list is html formatted, images are displayed etc, which is *not* the intended behaviour
        $questions[$qid]['parent_name']    = $unitNames[$question['content_ID']];
        $questions[$qid]['weight']         = $testQuestions[$qid]['weight'];
        $questions[$qid]['partof']         = 0;
        $questions[$qid]['estimate_interval'] = eF_convertIntervalToTime($question['estimate']);
        if ($question['lessons_ID'] == 0) {
            $questions[$qid]['name'] = _SKILLGAPTESTS;
        } else {
            $questions[$qid]['name'] = _LESSON . ': "' . $question['name'] . '"';
            $questions[$qid]['lesson_name']        = $lessons[$question['lessons_ID']]['name'];
        }

        if ($skillgap_tests && $question['type'] == 'raw_text') {
            unset($questions[$qid]);
        }
        
        if ($_GET['ctg'] == 'feedback' && $question['type'] == 'true_false') {
        	unset($questions[$qid]);
        }
    }

    foreach ($testQuestions as $testQuestion) {                                     //Set to selected the questions that the test includes, along with their weights
        $form -> setDefaults(array('questions['.$testQuestion['id'].']'       => 1,
                                               'question_weight['.$testQuestion['id'].']' => $testQuestion['weight']));
        $questions[$testQuestion['id']]['partof'] = 1;
    }

    isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'uint') ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

    if (isset($_GET['sort']) && eF_checkParameter($_GET['sort'], 'text')) {
        $sort = $_GET['sort'];
        isset($_GET['order']) && $_GET['order'] == 'desc' ? $order = 'desc' : $order = 'asc';
    } else {
        $sort = 'text';
    }
    $questions = eF_multiSort($questions, $sort, $order);

    if (isset($_GET['filter'])) {
        $questions = eF_filterData($questions, $_GET['filter']);
    }

    $smarty -> assign("T_QUESTIONS_SIZE", sizeof($questions));
    if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
        isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
        $questions = array_slice($questions, $offset, $limit, true);
    }

    foreach ($questions as $id => $question) {
        $form -> addElement("checkbox", "questions[".$id."]", null, null, 'id = "checked_'.$id.'" onclick = "ajaxPost(\''.$id.'\', this, \'questionsTable\');"');
        $form -> addElement('select', 'question_weight['.$id.']', null, array_combine(range(1,10), range(1,10)), 'id = "weight_'.$id.'" onchange = "$(\'checked_'.$id.'\').checked=true;ajaxPost(\''.$id.'\', this);"');
    }

    $smarty -> assign('T_UNIT_QUESTIONS', $questions);
//pr($questions);exit;
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
    $form -> accept($renderer);
    $smarty -> assign('T_TEST_FORM', $renderer -> toArray());
    $smarty -> display($_SESSION['s_type'].'.tpl');
    exit;
}

if (isset($_GET['postAjaxRequest'])) {
    // Ajax assignment of a skill gap test to a user
    if (isset($_GET['login'])) {
        if ($_GET['checked'] == "true") {
            eF_insertTableData("users_to_skillgap_tests", array( "users_LOGIN" => $_GET['login'], "tests_ID" => $_GET['edit_test']));
        } else if ($_GET['checked'] == "false") {
            eF_deleteTableData("users_to_skillgap_tests", "users_LOGIN = '". $_GET['login'] ."' AND tests_ID = '" .$_GET['edit_test'] . "'");

        } else if (isset($_GET['addAll'])) {

            // Different management if a users' filter is set or not
            if ($_GET['filter']) {
                $existing_test_users_r = eF_getTableData("users_to_skillgap_tests", "*", "tests_ID = '".$_GET['edit_test']."'");
                if (!empty($existing_test_users_r)) {
                    $existing_test_users_r = eF_filterData($existing_test_users_r,$_GET['filter']);
                    $existing_test_users['users_LOGIN'] = array();
                    foreach ($existing_test_users_r as $test_user) {
                        $existing_test_users['users_LOGIN'][] = $test_user['users_LOGIN'];
                    }
                } else {
                    $existing_test_users = array();
                }
                $all_users_r = eF_getTableData("users", "*", "user_type = 'student'");
                $all_users_r = eF_filterData($all_users_r,$_GET['filter']);

                $all_users['login'] = array();
                foreach ($all_users_r as $test_user) {
                    $all_users['login'][] = $test_user['login'];
                }

            } else {
                $existing_test_users = eF_getTableDataFlat("users_to_skillgap_tests", "users_LOGIN", "tests_ID = '".$_GET['edit_test']."'");
                $all_users = eF_getTableDataFlat("users", "login", "user_type = 'student'");
            }

            if (empty($existing_test_users)) {
                $non_existing_users = $all_users['login'];
            } else {
                $non_existing_users = array_diff($all_users['login'], $existing_test_users['users_LOGIN']);
            }

            foreach ($non_existing_users as $user_to_add) {
                if (!$all_users_to_add) {
                    $all_users_to_add = "('".$_GET['edit_test'] . "','". $user_to_add . "' , '0')";
                } else {
                    $all_users_to_add .= ",('".$_GET['edit_test'] . "','". $user_to_add. "' , '0')";
                }
            }

            if (isset($all_users_to_add)) {
                eF_executeNew("INSERT INTO users_to_skillgap_tests (tests_ID, users_LOGIN, solved) VALUES " . $all_users_to_add);
            }
        } else if (isset($_GET['removeAll'])) {
            // Different management if a users' filter is set or not
            if ($_GET['filter']) {
                $all_current_users = eF_getTableData("users_to_skillgap_tests JOIN users ON users_LOGIN = login", "login, name, surname", "");
                isset($_GET['filter']) ? $all_current_users = eF_filterData($all_current_users,$_GET['filter']) : null;

                foreach ($all_current_users as $test_user) {
                    eF_deleteTableData("users_to_skillgap_tests", "tests_ID = '".$_GET['edit_test'] . "' AND users_LOGIN = '". $test_user['login']."' ");
                }
            } else {
                eF_deleteTableData("users_to_skillgap_tests", "tests_ID = '".$_GET['edit_test'] . "'");
            }
        }

    } else {
        try {
            if (isset($_GET['question']) && eF_checkParameter($_GET['question'], 'id')) {
                if ($_GET['remove'] && in_array($_GET['question'], array_keys($testQuestions))) {                    //The user has the project, so remove him
                    $currentTest -> removeQuestions(array($_GET['question']));
                } else {                     //The user doesn't have the project, so add him
                    $currentTest -> addQuestions(array($_GET['question'] => $_GET['weight']));
                }
            } else if (isset($_GET['addAll'])) {

                $nonTestQuestions = $currentTest -> getNonQuestions();

                // Do not add development questions to skill gap tests
                if ($skillgap_tests) {
                    foreach($nonTestQuestions as $qid => $nonTestQuestion) {
                        if ($nonTestQuestion['type'] == 'raw_text') {
                            unset($nonTestQuestions[$qid]);
                        } else {
                            // Create a field to simulate the values appearing under the Associated with column
                            if ($nonTestQuestion['lessons_ID'] == 0) {
                                $nonTestQuestions[$qid]['name'] = _SKILLGAPTESTS;
                            } else {
                                $lesson = new EfrontLesson($nonTestQuestion['lessons_ID']);
                                $nonTestQuestions[$qid]['name'] = _LESSON . ": " . $lesson -> lesson['name'];
                            }
                        }
                    }
                }
				if ($_GET['ctg'] == 'feedback') {
					$nonTestQuestionsTemp = array();
					foreach ($nonTestQuestions as $key => $value) {
						if ($value['type'] != 'true_false') {
							$nonTestQuestionsTemp[$key] = $value;
						}
					}
					$nonTestQuestions = $nonTestQuestionsTemp;
				}
                isset($_GET['filter']) ? $nonTestQuestions = eF_filterData($nonTestQuestions,$_GET['filter']) : null;
                $currentTest -> addQuestions(array_combine(array_keys($nonTestQuestions), array_fill(0, sizeof($nonTestQuestions), 1)));
            } else if (isset($_GET['removeAll'])) {
                $testQuestions = $currentTest -> getQuestions();

                if ($skillgap_tests) {
                    // Create a field to simulate the values appearing under the Associated with column of skillgap tests
                    foreach ($testQuestions as $qid => $testQuestion) {
                        if ($testQuestion['lessons_ID'] == 0) {
                            $testQuestions[$qid]['name'] = _SKILLGAPTESTS;
                        } else {
                            $lesson = new EfrontLesson($testQuestion['lessons_ID']);
                            $testQuestions[$qid]['name'] = _LESSON . ": " . $lesson -> lesson['name'];
                        }
                    }
                }

                isset($_GET['filter']) ? $testQuestions = eF_filterData($testQuestions,$_GET['filter']) : null;
                $currentTest -> removeQuestions(array_keys($testQuestions));
            }

            //ArrayObject is required in order for json to work well with prototype
            $stats 	   = new ArrayObject($currentTest -> questionsInfo());
            $stats['difficulties']  = new ArrayObject($stats['difficulties']);
            $stats['types'] 	    = new ArrayObject($stats['types']);
            $stats['percentage']    = new ArrayObject($stats['percentage']);
            $stats['duration'] 	    = eF_convertIntervalToTime($stats['total_duration']);
            $stats['random_pool']   = $currentTest -> options['random_pool'];
            $stats['test_duration'] = $currentTest -> options['duration'];

            header("content-type:application/json");
            echo json_encode($stats);

        } catch (Exception $e) {
            header("HTTP/1.0 500 ");
            echo $e -> getMessage().' ('.$e -> getCode().')';
        }
    }
    exit;
}
if (isset($_GET['ajax']) && $_GET['ajax'] == 'randomize') {
    try {
        $params = array('multitude'  	  => is_numeric($_POST['multitude'])		  ? $_POST['multitude']  	   : '',
            					'duration' 	  	  => is_numeric($_POST['duration'])  	  ? $_POST['duration']   	   : '',
            	 				'mean_difficulty' => is_numeric($_POST['mean_difficulty']) ? $_POST['mean_difficulty']   : '',
            					'balance' 		  => is_numeric($_POST['balance'])   	  ? $_POST['balance']    	   : 50);
        $params['duration'] = $params['duration']*60;

        //Remove units and difficulties that are set to 'Off'
        if (isset($_POST['unit_to_difficulty'])) {
            foreach ($_POST['unit_to_difficulty'] as $key => $value) {
                if (!isset($_POST['unit'][$key]) || $_POST['unit'][$key] == 'off') {
                    unset($_POST['unit_to_difficulty'][$key]);
                } else {
                    foreach ($value as $k => $v) {
                        if (!isset($_POST['difficulty'][$k]) || $_POST['difficulty'][$k] == 'off') {
                            unset($_POST['unit_to_difficulty'][$key][$k]);
                        }
                    }
                }
            }
            $reqs = array('difficulty' => $_POST['unit_to_difficulty']);
            //Remove units and types that are set to 'Off'
        } else if (isset($_POST['unit_to_type'])) {
            foreach ($_POST['unit_to_type'] as $key => $value) {
                if (!isset($_POST['unit'][$key]) || $_POST['unit'][$key] == 'off') {
                    unset($_POST['unit_to_type'][$key]);
                } else {
                    foreach ($value as $k => $v) {
                        if (!isset($_POST['type'][$k]) || $_POST['type'][$k] == 'off') {
                            unset($_POST['unit_to_type'][$key][$k]);
                        }
                    }
                }
            }
            $reqs = array('type' => $_POST['unit_to_type']);
            //Adjust percentages so that the total sum is always 100
        } else if (isset($_POST['unit_to_percentage'])) {
            $sum = 0;
            //If total sum is more than 100, truncate last values so that total remains 100
            foreach ($_POST['unit_to_percentage'] as $key => $value) {
                if ($sum + $value > 100) {
                    $value = $_POST['unit_to_percentage'][$key] = 100 - $sum;
                }
                $sum += $value;
            }
            //If total sum is less than 100, augment last value so that it sums up to 100
            if ($sum < 100) {
                $_POST['unit_to_percentage'][$key] += 100 - $sum;
            }
            $reqs = array('percentage' => $_POST['unit_to_percentage']);
        }

        $questions = $currentTest -> randomize($params, $reqs);
        //ArrayObject is required in order for json to work well with prototype
        $stats 	   = new ArrayObject($currentTest -> questionsInfo($questions));
        $stats['difficulties'] = new ArrayObject($stats['difficulties']);
        $stats['types'] 	   = new ArrayObject($stats['types']);
        $stats['percentage']   = new ArrayObject($stats['percentage']);
        $stats['duration'] 	   = eF_convertIntervalToTime($stats['total_duration']);
        if ($currentTest -> options['random_pool'] > sizeof($currentTest -> getQuestions())) {
            $currentTest -> options['random_pool'] = sizeof($currentTest -> getQuestions());
            $currentTest -> persist();
        }
        $stats['random_pool']   = $currentTest -> options['random_pool'] ? $currentTest -> options['random_pool'] : '';
        $stats['test_duration'] = $currentTest -> options['duration'];

        header("content-type:application/json");
        echo json_encode($stats);
    } catch (Exception $e) {
        header("HTTP/1.0 500 ");
        echo $e -> getMessage().' ('.$e -> getCode().')';
    }
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 'random_pool' && isset($_GET['random_pool'])) {
    try {
        //Set the random pool option
        $randomPool = 0;
        if (is_numeric($_GET['random_pool']) && $_GET['random_pool'] > 0) {
            $randomPool = $_GET['random_pool'];
        }
        if (sizeof($currentTest -> getQuestions()) < $randomPool) {
            $randomPool = sizeof($currentTest -> getQuestions());
        }
        $currentTest -> options['random_pool'] = $randomPool;

        //Set the user configurable option
        isset($_GET['user_configurable']) && $_GET['user_configurable'] ? $currentTest -> options['user_configurable'] = 1 : $currentTest -> options['user_configurable'] = 0;
        isset($_GET['show_incomplete']) && $_GET['show_incomplete'] 	? $currentTest -> options['show_incomplete']   = 1 : $currentTest -> options['show_incomplete'] = 0;
        
        $currentTest -> persist();

        //ArrayObject is required in order for json to work well with prototype
        $stats 	   = new ArrayObject($currentTest -> questionsInfo());
        $stats['difficulties']  = new ArrayObject($stats['difficulties']);
        $stats['types'] 	    = new ArrayObject($stats['types']);
        $stats['percentage']    = new ArrayObject($stats['percentage']);
        $stats['duration'] 	    = eF_convertIntervalToTime($stats['total_duration']);
        $stats['random_pool']   = $currentTest -> options['random_pool'];
        //Set the test time to match questions time
        if ($_GET['update_test_time'] && $stats['total_duration'] > 0) {
            $currentTest -> options['duration'] = $stats['total_duration'];
            $currentTest -> persist();
        }
        $stats['test_duration'] = $currentTest -> options['duration'];

        header("content-type:application/json");
        echo json_encode($stats);
    } catch (Exception $e) {
        header("HTTP/1.0 500 ");
        echo $e -> getMessage().' ('.$e -> getCode().')';
    }

    exit;
}


