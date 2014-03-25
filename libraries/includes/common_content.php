<?php

if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

if ($GLOBALS['rtl']) {
	$arrow_previous = 'navigate_right';
	$arrow_next = 'navigate_left';
} else {
	$arrow_previous = 'navigate_left';
	$arrow_next = 'navigate_right';	
}
$smarty -> assign("T_ARROW_PREVIOUS", $arrow_previous);
$smarty -> assign("T_ARROW_NEXT", $arrow_next);

!isset($currentUser -> coreAccess['content']) || $currentUser -> coreAccess['content'] == 'change' ? $_change_ = 1 : $_change_ = 0;
$smarty -> assign("_change_", $_change_);

$loadScripts[] = 'scriptaculous/dragdrop';
$loadScripts[] = 'includes/content';
$loadScripts[] = 'includes/comments';

if (!isset($currentContent)) {
    if (!$currentLesson) {
        if ($_GET['view_unit']) {
            $unit = new EfrontUnit($_GET['view_unit']);       
            $currentLesson = new EfrontLesson($unit['lessons_ID']);
        } elseif ($_GET['package_ID']) {
            $unit = new EfrontUnit($_GET['package_ID']);
            $currentLesson = new EfrontLesson($unit['lessons_ID']);
        }
        $_SESSION['s_lessons_ID'] = $currentLesson -> lesson['id'];
    }
    $currentContent = new EfrontContentTree($currentLesson);
    if ($_student_) {
        $currentContent -> markSeenNodes($currentUser);
    }
}

//Legal values are the array of entities that the current user may actually edit or change.
foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST)) as $key => $value) {
    $legalValues[] = $key;
}

if (isset($_GET['add']) || (isset($_GET['edit']) && in_array($_GET['edit'], $legalValues) && eF_checkParameter($_GET['edit'], 'id')) && $_change_) {
	try {
	    if ($_GET['edit']) {
	        $currentUnit = $currentContent -> seekNode($_GET['edit']);
	        //The content tree does not hold data, so assign this unit its data
	        $unitData    = new EfrontUnit($_GET['edit']);
	        $currentUnit['data'] = $unitData['data'];
	        $currentUnitName 	 = $unitData['name'];
	    } else {
	        unset($currentUnit);    //Needed because we might have the &view_unit specified in the parameters
	    }

	    //This page has a file manager, so bring it on with the correct options
	    $basedir    = $currentLesson -> getDirectory();    
	    //Default options for the file manager  
        if (!isset($currentUser -> coreAccess['files']) || $currentUser -> coreAccess['files'] == 'change') {
            $options = array('lessons_ID' => $currentLesson -> lesson['id'], 'metadata' => 0);
            if(!$currentLesson -> options['digital_library']){
            	$options['share'] = false;
            }
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
        $url = basename($_SERVER['PHP_SELF']).'?ctg=content&'.(isset($_GET['edit']) ? 'edit='.$_GET['edit'] : 'add=1');
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

	    //Create form elements

	    $completeUnitSelect = array(EfrontUnit::COMPLETION_OPTIONS_DEFAULT => _DEFAULT, 
	    							EfrontUnit::COMPLETION_OPTIONS_AUTOCOMPLETE => _AUTOCOMPLETE, 
	    							EfrontUnit::COMPLETION_OPTIONS_HIDECOMPLETEUNITICON => _HIDECOMPLETEUNITICON);
	    
		$form = new HTML_QuickForm("create_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=content".(isset($_GET['add']) ? '&add=1' : '&edit='.$_GET['edit']), "", null, true);
	    $form -> addElement('text', 'name', _UNITNAME, 'class = "inputText"');
	    $form -> addElement('text', 'pdf_content', _CURRENTPDFFILE, 'class = "inputText inactive" readonly');
	    
	    if (strpos($currentUnit['ctg_type'], 'scorm') !== false || strpos($currentUnit['scorm_version'], 'tincan') !== false) {
	    	$form -> addElement('textarea', 'data', _CONTENT, 'id = "editor_content_data" class = "inputContentTextarea" style = "width:100%;height:10em;"');  //The unit content itself
	    } else {
	    	$form -> addElement('textarea', 'data', _CONTENT, 'id = "editor_content_data" class = "inputContentTextarea mceEditor" style = "width:100%;height:50em;"');  //The unit content itself
	    }
	    //For deleting data from editor when toggling pdf content in editing unit. In order to write data again (#1034)
	    $form -> addElement('hidden', 'content_toggle', null, 'id="content_toggle"');
	    $form -> addElement('advcheckbox', 'indexed', _DIRECTLYACCESSIBLE, null, 'class = "inputCheckbox"', array(0, 1));
	    $form -> addElement('advcheckbox', 'maximize_viewport', _MAXIMIZEVIEWABLEAREA, null, 'class = "inputCheckbox"', array(0, 1));
	    $form -> addElement('advcheckbox', 'scorm_asynchronous', _SCORMASYNCHROUNOUS, null, 'class = "inputCheckbox"', array(0, 1));
	    $form -> addElement('text', 'object_ids', _SPECIFYIDFORSREENMATCHING, 'class = "inputText"');
	    $form -> addElement('advcheckbox', 'no_before_unload', _NOBEFOREUPLOAD, null, 'class = "inputCheckbox"', array(0, 1));
	    $form -> addElement('advcheckbox', 'pdf_check', _UPLOADPDFFORCONTENT, null, 'class = "inputCheckbox" onclick="checkToggle=true;togglePdf()"', array(0, 1));
	    $form -> addElement('select', 'hide_navigation', _HIDENAVIGATION, array(0 => _NO, 1 => _ALLHANDLES, 2 => _UPPERHANDLES, 3 => _LOWERHANDLES));
	    $form -> addElement('select', 'ctg_type', _CONTENTTYPE, array('theory' => _THEORY, 'examples'=> _EXAMPLES), 'class = "inputSelect"');     //A select drop down for content type.... Exercises went away in version 3 (2007/07/10) makriria
	       
	    if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	    	if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
	    		$form -> addElement('text', 'complete_time', _COMPLETEAFTERSECONDS, 'id = "complete_time"  onclick = "setCompletion(this);" size = "5"');
	    		$completeUnitSelect[EfrontUnit::COMPLETION_OPTIONS_COMPLETEAFTERSECONDS] = _COMPLETEAFTERTIMEELAPSED;
	    	} #cpp#endif
	    } #cpp#endif
    	     
	    //in order to display inactive parent units (#903)
	    $iterator = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST));    //Default iterator excludes non-active units	    
	    $form -> addElement('select', 'parent_content_ID', _UNITPARENT, array(0 => _NOPARENT)+$currentContent -> toHTMLSelectOptions($iterator));
	    $form -> addElement('file', 'pdf_upload', _PDFFILE, null);	    
	    $form -> addElement('submit', 'submit_insert_content', _SAVECHANGES, 'class = "flatButton"');
	    $form -> setMaxFileSize(FileSystemTree :: getUploadMaxSize()*1024);


	    if (strpos($currentUnit['ctg_type'], 'scorm') !== false || strpos($currentUnit['scorm_version'], 'tincan') !== false) {
	        $form -> addElement('text', 'scorm_size', _EXPLICITIFRAMESIZE, 'class = "inputText" style = "width:50px"');                      //Set an explicit size for the SCORM content
	        $form -> addElement('select', 'reentry_action', _ACTIONONRENTRYCOMPLETED, array(0 => _LETCONTENTDECIDE, 1 => _DONTCHANGE), 'class = "inputText"');                      //Set what action should be performed when a user re-enters a visited content

	        if (!$currentUnit['scorm_version'] || $currentUnit['scorm_version'] == '1.2'|| $currentUnit['scorm_version'] == 'tincan') {
		        $form -> addElement('select', 'embed_type', _EMBEDTYPE, array('iframe' => _INLINEIFRAME, 'popup'=> _NEWWINDOWPOPUP), 'class = "inputSelect"');
	        	$form -> addElement('text', 'popup_parameters', _POPUPPARAMETERS, 'class = "inputText" style = "width:600px"');
				
	         	if (strpos($currentUnit['data'], 'window.open') !== false) {
					preg_match("/\"scormFrameName\".*\"\)'/U", $currentUnit['data'], $matches);
					$popupParameter = mb_substr($matches[0], mb_strpos($matches[0], '"scormFrameName", "') + mb_strlen('"scormFrameName", "'), mb_strpos($matches[0], ")'"));
					$form -> setDefaults(array('popup_parameters' => $popupParameter));
				} else {	
	        		$form -> setDefaults(array('popup_parameters' => 'width=800,height=600,scrollbars=no,resizable=yes,status=yes,toolbar=no,location=no,menubar=no,top="+(parseInt(parseInt(screen.height)/2) - 300)+",left="+(parseInt(parseInt(screen.width)/2) - 400)+"'));
				}
	        }
	
			if (strpos($currentUnit['data'], 'iframe') !== false) {
				$form -> setDefaults(array('embed_type' => 'iframe'));
			} else {
				$form -> setDefaults(array('embed_type' => 'popup'));
			}

			$form -> addRule('scorm_size', _INVALIDFIELDDATA, 'checkParameter', 'id');
	        $smarty -> assign("T_SCORM", true);
	    }
	    
	    //Set elements rules
	    $form -> addRule('name', _THEFIELD.' "'._UNITNAME.'" '._ISMANDATORY, 'required', null, 'client');           //The name is mandatory
	    //$form -> addRule('ctg_type', _THEFIELD.' '._CONTENTTYPE.' '._ISMANDATORY, 'required', null, 'client');       //The content type is mandatry
	    if (!isset($_GET['edit'])) { // changed in case parent unit is inactive
	    	$form -> addRule('parent_content_ID', _THEFIELD.' '._UNITPARENT.' '._ISMANDATORY, 'required', null, 'client');
	    	$form -> addRule('parent_content_ID', _INVALIDID, 'numeric');
	    }
	    
	    //Add the content's questions, in order to setup "complete with question" field
	    if (sizeof($currentLesson -> getQuestions()) > 0) {	    	
		    $pathStrings     = $currentContent -> toPathStrings();		    
		    foreach ($currentLesson -> getQuestions() as $key => $value) {
		        if ($value['type'] != 'raw_text' || $value['id'] == $currentUnit['options']['complete_question']) {
			        $plainText = trim(strip_tags($value['text']));
			        if (mb_strlen($plainText) > Question :: maxQuestionText) {
			            $plainText = mb_substr($plainText, 0, Question :: maxQuestionText).'...';
			        }
			        $pathStrings[$value['content_ID']]? $lessonQuestions[$value['id']] = $pathStrings[$value['content_ID']].'&nbsp;&raquo;&nbsp;'.$plainText : $lessonQuestions[$value['id']] = $plainText;
		        }
		    }	    
		    if (!empty($lessonQuestions) || $currentUnit['options']['complete_unit_setting'] == EfrontUnit::COMPLETION_OPTIONS_COMPLETEWITHQUESTION) {
			    $form -> addElement('select', 'complete_question', _COMPLETEWITHQUESTION, $lessonQuestions, 'id = "complete_question"');
			    $completeUnitSelect[EfrontUnit::COMPLETION_OPTIONS_COMPLETEWITHQUESTION] = _COMPLETEWITHQUESTION;
		    }
	    }
		ksort($completeUnitSelect);
		
	    $form -> addElement('select', 'complete_unit_setting', _COMPLETEUNITOPTIONS, $completeUnitSelect, 'onchange = "setUnitCompletionOptions(this)"');

		$smarty -> assign("T_CURRENT_CONTENT_NAME", $currentUnit['name']); // added because of #2870
	    //Set elements default values
	    $form -> setDefaults($currentUnit['options']);
	    preg_match("/eF_js_setCorrectIframeSize\((.*)\)/", $currentUnit['data'], $matches);
	    $form -> setDefaults(array('scorm_size' => isset($matches[1]) ? $matches[1] : null,
	                               'data'       => $currentUnit['data'],
							       'name'       => $currentUnitName,   // changed because of #2870
							       'ctg_type'   => $currentUnit['ctg_type'],
	        					   'complete_question' => $currentUnit['options']['complete_question'],
	    						   'complete_time'	   => $currentUnit['options']['complete_time'] ? $currentUnit['options']['complete_time'] : '',
	                               //'questions'         => $currentUnit['options']['complete_question'],
	                               'parent_content_ID' => isset($_GET['view_unit']) ? $_GET['view_unit'] : 0,
	    						   'complete_unit_setting' => $currentUnit['options']['complete_unit_setting']));
	    //If the "complete with question" option is set, show the selected question
	    //$currentUnit['options']['complete_unit_setting'] == COMPLETION_OPTIONS_COMPLETEWITHQUESTION ? $form -> updateElementAttr(array('complete_question'), array('style' => 'display:""')) : null;
	    //$currentUnit['options']['complete_unit_setting'] == COMPLETION_OPTIONS_COMPLETEAFTERSECONDS ? $form -> updateElementAttr(array('complete_time'), array('style' => 'display:""')) : null;
	    
	    //Check whether it is a pdf content and handle accordingly
	    if (mb_strpos($currentUnit['data'], "<iframe") !== false && mb_strpos($currentUnit['data'], "pdfaccept") !== false && mb_strpos($currentUnit['data'], "google.com/viewer") === false) {

	    	$fileEnd        = mb_strpos($currentUnit['data'], ".pdf");
			if ($fileEnd != "") {
	        	$contentParts   = explode("/", mb_substr($currentUnit['data'], 0, $fileEnd));
	        	try {
					$pdfFile = new EfrontFile(G_RELATIVELESSONSLINK.$_SESSION['s_lessons_ID'].'/'.EfrontFile :: decode(htmlspecialchars_decode(urldecode($contentParts[sizeof($contentParts)-1].'.pdf'))));
					if ($pdfFile['id']) {
						$form -> setDefaults(array('data'   => '<iframe src="view_file.php?file='.$pdfFile['id'].'"  name="pdfaccept" width="100%" height="600"></iframe>'));
					}

	           } catch (Exception $e) {
	           //in case file is not found in database, don't do anything
	           }
				$form -> setDefaults(array('pdf_content'   => EfrontFile :: decode(htmlspecialchars_decode(urldecode($contentParts[sizeof($contentParts)-1].'.pdf')))));
			} else {
				preg_match("/view_file.php\?file=\d+/", $currentUnit['data'], $matches);
				$pdfId = explode("=", $matches[0]);
				try {
					$pdfFile = new EfrontFile($pdfId[1]);
				} catch (Exception $e) {
	    			$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
	    			$message      = _SOMEPROBLEMOCCURED.': '.$e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
	    			$message_type = 'failure';
				}
				$form -> setDefaults(array('pdf_content'   => $pdfFile['physical_name']));

			}
	        $form -> setDefaults(array('pdf_check'     => 1));

	        $smarty -> assign("T_EDITPDFCONTENT", true);
	    }

	    //You can't change a unit's parent from this form. You must use the content tree management page
	    if ($_GET['edit']) {
	        $form -> setDefaults(array('parent_content_ID' => $currentUnit['parent_content_ID']));
	        $form -> freeze(array('parent_content_ID'));
	    }

	    //If the form was submitted with pdf content, take special care
	    if ($form -> isSubmitted() && $form -> validate()) {
	    	try {
		        $values = $form -> exportValues();

		        if ($_FILES['pdf_upload']['name'] != "") {
		            if (strpos($_FILES['pdf_upload']['name'], ".pdf") !== false) {
		                $destinationDir = new EfrontDirectory(G_LESSONSPATH.$_SESSION['s_lessons_ID']);
		                $filesystem     = new FileSystemTree(G_LESSONSPATH.$_SESSION['s_lessons_ID']);
	                    $uploadedFile   = $filesystem -> uploadFile('pdf_upload', $destinationDir);
	                    $values['data'] = '<iframe src="view_file.php?file='.$uploadedFile['id'].'"  name="pdfaccept" width="100%" height="600"></iframe>';
	                    //$values['data'] = '<iframe src="'.$currentLesson -> getDirectoryUrl().'/'.$uploadedFile["physical_name"].'"  name="pdfaccept" width="100%" height="600"></iframe>';
		            } else {
		            	throw new Exception(_YOUMUSTUPLOADAPDFFILE);
		            }
		        }

		        $options = serialize(array(//'hide_complete_unit' => $values['hide_complete_unit'],
		                                   //'auto_complete'      => $values['auto_complete'],
		                                   'complete_unit_setting' => $values['complete_unit_setting'],
		        						   'hide_navigation'    => $values['hide_navigation'],
		                                   'indexed'            => $values['indexed'],
										   'maximize_viewport'  => $values['maximize_viewport'],
										   'scorm_asynchronous' => $values['scorm_asynchronous'],
		        						   'object_ids' 		=> $values['object_ids'],
		                                   'no_before_unload'	=> $values['no_before_unload'],
		                        		   'reentry_action'	    => isset($values['reentry_action']) ? $values['reentry_action'] : false,
		            					   'complete_question'  => $values['complete_question'] ? $values['complete_question'] : 0,
		        						   'complete_time'		=> $values['complete_time'] ? $values['complete_time'] : ''));


				if (isset($_GET['edit'])) {
		            //You can't edit data in scorm units
		            if (strpos($currentUnit['ctg_type'], 'scorm') === false && strpos($currentUnit['scorm_version'], 'tincan') === false) {
		                $currentUnit['data'] = applyEditorOffset($values['data']);
		            } else {
		            	if ($values['data'] != $currentUnit['data']) {
		            		$currentUnit['data'] = $values['data'];
		            	} elseif ($values['embed_type'] == 'iframe' &&  strpos($currentUnit['data'], 'window.open') !== false) {
							preg_match("/window.open\(.*,/U", $currentUnit['data'], $matches);
							$scormValue = str_replace(array('window.open("', '",'),"",$matches[0]);
							$currentUnit['data'] = '<iframe height = "100%"  width = "100%" frameborder = "no" name = "scormFrameName" id = "scormFrameID" src = "'.$scormValue. '" onload = "if (window.eF_js_setCorrectIframeSize) {eF_js_setCorrectIframeSize();} else {setIframeSize = true;}"></iframe>';
						} elseif ($values['embed_type'] == 'popup' &&  strpos($currentUnit['data'], 'iframe') !== false) {
							preg_match("/src.*onload/U", $currentUnit['data'], $matches);
							$scormValue = str_replace(array('src = "', '" onload'),"",$matches[0]);
							$currentUnit['data'] = '
		                            	<div style = "text-align:center;height:300px">
			                            	<span>##CLICKTOSTARTUNIT##</span><br/>
			                        		<input type = "button" value = "##STARTUNIT##" class = "flatButton" onclick = \'window.open("'.$scormValue.'", "scormFrameName", "'.$values['popup_parameters'].'")\' >
		                        		</div>';
						} elseif ($values['embed_type'] == 'popup' &&  strpos($currentUnit['data'], 'window.open') !== false) {	//in case changing only popup parameters field
							preg_match("/\"scormFrameName\".*\"\)'/U", $currentUnit['data'], $matches);
							$currentUnit['data'] = preg_replace("/\"scormFrameName\".*\"\)'/U", '"scormFrameName", "'.$values['popup_parameters'].'")\'' , $currentUnit['data']);
						}
		                $currentUnit['data'] = preg_replace("/eF_js_setCorrectIframeSize\(.*\)/", "eF_js_setCorrectIframeSize(".$values['scorm_size'].")", $currentUnit['data']);
		            }
		            $values['ctg_type'] ? $currentUnit['ctg_type'] = $values['ctg_type'] : null;
		            $values['name']     ? $currentUnit['name']     = $values['name']     : null;
		            $currentUnit['options'] = $options;

		            $currentUnit -> persist();
		            $currentUnit -> setSearchKeywords();
		        } else {
		            $fields = array('name'              => $values['name'],
		                            'data'              => applyEditorOffset($values['data']),
		                            'parent_content_ID' => $values['parent_content_ID'],
		                            'lessons_ID'        => $_SESSION['s_lessons_ID'],
		                            'ctg_type'          => $values['ctg_type'],
		                            'active'            => 1,
		                            'options'           => $options);
		            $currentUnit = $currentContent -> insertNode($fields);
		        }

		        EfrontCache::getInstance()->deleteCache("content_tree:{$_SESSION['s_lessons_ID']}");
		        		        
		        $message      = _OPERATIONCOMPLETEDSUCCESSFULLY;
		        $message_type = 'success';

		        eF_redirect(basename($_SERVER['PHP_SELF']).'?ctg=content&view_unit='.$currentUnit['id'].'&message='.urlencode($message).'&message_type=success');
		    } catch (Exception $e) {
		    	handleNormalFlowExceptions($e);
		    }
	    }

	    $form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
	    $form -> setRequiredNote(_REQUIREDNOTE);

	    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
	    $renderer->setRequiredTemplate(
		       '{$html}{if $required}
		            &nbsp;<span class = "formRequired">*</span>
		        {/if}'
		        );

		        $renderer->setErrorTemplate(
		       '{$html}{if $error}
		            <span class = "formError">{$error}</span>
		        {/if}'
		        );
	    $form -> accept($renderer);

	    $smarty -> assign('T_ENTITY_FORM', $renderer -> toArray());

	    $smarty -> assign("T_EDITED_UNIT", $currentUnit);
	} catch (Exception $e) {
	    $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
	    $message      = _SOMEPROBLEMOCCURED.': '.$e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
	    $message_type = 'failure';
	}

} else if (isset($_GET['apply_all'])) {

    $completeUnitSelect = array(EfrontUnit::COMPLETION_OPTIONS_DEFAULT => _DEFAULT, 
    							EfrontUnit::COMPLETION_OPTIONS_AUTOCOMPLETE => _AUTOCOMPLETE, 
    							EfrontUnit::COMPLETION_OPTIONS_HIDECOMPLETEUNITICON => _HIDECOMPLETEUNITICON);

	$form = new HTML_QuickForm("create_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=content".(isset($_GET['add']) ? '&add=1' : '&edit='.$_GET['edit']), "", null, true);
	    
	$form -> addElement('select', 'ctg_type', _CONTENTTYPE, array('theory' => _THEORY, 'examples'=> _EXAMPLES), 'class = "inputSelect"');     //A select drop down for content type.... Exercises went away in version 3 (2007/07/10) makriria
	$form -> addElement('select', 'hide_navigation', _HIDENAVIGATION, array(0 => _NO, 1 => _ALLHANDLES, 2 => _UPPERHANDLES, 3 => _LOWERHANDLES));
	if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
		if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
			$form -> addElement('text', 'complete_time', _COMPLETEAFTERSECONDS, 'id = "complete_time"  onclick = "setCompletion(this);" size = "5"');
			$completeUnitSelect[EfrontUnit::COMPLETION_OPTIONS_COMPLETEAFTERSECONDS] = _COMPLETEAFTERTIMEELAPSED;
		} #cpp#endif
	} #cpp#endif		
	ksort($completeUnitSelect);
	$form -> addElement('select', 'complete_unit_setting', _COMPLETEUNITOPTIONS, $completeUnitSelect, 'onchange = "setUnitCompletionOptions(this)"');	
	$form -> addElement('advcheckbox', 'indexed', _DIRECTLYACCESSIBLE, null, 'class = "inputCheckbox"', array(0, 1));
	$form -> addElement('advcheckbox', 'maximize_viewport', _MAXIMIZEVIEWABLEAREA, null, 'class = "inputCheckbox"', array(0, 1));
	$form -> addElement('static', null, _SCORMSPECIFICPROPERTIES);
	$form -> addElement('text', 'object_ids', _SPECIFYIDFORSREENMATCHING, 'class = "inputText"');
	$form -> addElement('advcheckbox', 'no_before_unload', _NOBEFOREUPLOAD, null, 'class = "inputCheckbox"', array(0, 1));
	$form -> addElement('advcheckbox', 'scorm_asynchronous', _SCORMASYNCHROUNOUS, null, 'class = "inputCheckbox"', array(0, 1));		
	$form -> addElement('text', 'scorm_size', _EXPLICITIFRAMESIZE, 'class = "inputText" style = "width:50px"');                      //Set an explicit size for the SCORM content
	$form -> addElement('select', 'reentry_action', _ACTIONONRENTRYCOMPLETED, array(0 => _LETCONTENTDECIDE, 1 => _DONTCHANGE), 'class = "inputText"');                      //Set what action should be performed when a user re-enters a visited content
	$form -> addElement('select', 'embed_type', _EMBEDTYPE, array('iframe' => _INLINEIFRAME, 'popup'=> _NEWWINDOWPOPUP), 'class = "inputSelect"');
	$form -> addElement('text', 'popup_parameters', _POPUPPARAMETERS, 'class = "inputText" style = "width:600px"');

    $form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
    $form -> setRequiredNote(_REQUIREDNOTE);

    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
    $renderer->setErrorTemplate(
	       '{$html}{if $error}
	            <span class = "formError">{$error}</span>
	        {/if}'
	        );
    $form -> accept($renderer);

    $smarty -> assign('T_ENTITY_FORM', $renderer -> toArray());


    if (isset($_GET['ajax'])) {
    	try {
    		$basicIterator = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST));
    		foreach (new EfrontNoSCORMFilterIterator(new EfrontNoFeedbackFilterIterator(new EfrontNoTestsFilterIterator($basicIterator))) as $key=>$value) {
    			if (!$_GET['scorm']) {
    				if ($_GET['option'] == 'ctg_type') {
    					$value['ctg_type'] = $_GET['value'];
    				} else {
    					$value['options'][$_GET['option']] = $_GET['value'];
    				}

    				$value->persist();
    			}
    		}
    		foreach (new EfrontSCORMFilterIterator($basicIterator) as $key=>$value) {
    			if ($_GET['scorm']) {
    				if ($_GET['option'] == 'scorm_size') {
    					$currentUnit = new EfrontUnit($key);
    					$currentUnit['data'] = preg_replace("/eF_js_setCorrectIframeSize\(.*\)/", "eF_js_setCorrectIframeSize(".$_GET['value'].")", $currentUnit['data']);
    					$currentUnit->persist();
    				} else if ($_GET['option'] == 'embed_type') {
    					$currentUnit = new EfrontUnit($key);
						if ($_GET['value'] == 'iframe' &&  strpos($currentUnit['data'], 'window.open') !== false) {
							preg_match("/window.open\(.*,/U", $currentUnit['data'], $matches);
							$scormValue = str_replace(array('window.open("', '",'),"",$matches[0]);
							$currentUnit['data'] = '<iframe height = "100%"  width = "100%" frameborder = "no" name = "scormFrameName" id = "scormFrameID" src = "'.$scormValue. '" onload = "if (window.eF_js_setCorrectIframeSize) {eF_js_setCorrectIframeSize();} else {setIframeSize = true;}"></iframe>';
						} elseif ($_GET['value'] == 'popup' &&  strpos($currentUnit['data'], 'iframe') !== false) {
							preg_match("/src.*onload/U", $currentUnit['data'], $matches);
							$scormValue = str_replace(array('src = "', '" onload'),"",$matches[0]);
							$currentUnit['data'] = '
		                            	<div style = "text-align:center;height:300px">
			                            	<span>##CLICKTOSTARTUNIT##</span><br/>
			                        		<input type = "button" value = "##STARTUNIT##" class = "flatButton" onclick = \'window.open("'.$scormValue.'", "scormFrameName", "width=800,height=600,scrollbars=no,resizable=yes,status=yes,toolbar=no,location=no,menubar=no,top="+(parseInt(parseInt(screen.height)/2) - 300)+",left="+(parseInt(parseInt(screen.width)/2) - 400)+"")\' >
		                        		</div>';
						}
    					$currentUnit->persist();
    				} else if ($_GET['option'] == 'popup_parameters') {
    					$currentUnit = new EfrontUnit($key);
    					preg_match("/\"scormFrameName\".*\"\)'/U", $currentUnit['data'], $matches);
    					$currentUnit['data'] = preg_replace("/\"scormFrameName\".*\"\)'/U", '"scormFrameName", "'.$_GET['value'].'")\'' , $currentUnit['data']);
    					$currentUnit->persist();
    				} else if (isset($value['options'][$_GET['option']])) {
    					$value['options'][$_GET['option']] = $_GET['value'];
    					$value->persist();
    				}
    			}
    		}
    		exit;
    	} catch (Exception $e) {
    		handleAjaxExceptions($e);
    	}
    	//$currentUnit['options'][$_GET['option']] = eF_addSlashes($_GET['value']);
    	//$currentUnit->persist();
    }
	    
} else if (!$currentUnit && $_student_ && !isset($_GET['package_ID'])) {
    $basicIterator = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST));
    if (isset($_GET['type']) && $_GET['type'] == 'tests') {
        //if (EfrontUser::isOptionVisible('tests')) {exit;}
        $iterator = new EfrontTestsFilterIterator(new EfrontVisitableFilterIterator($basicIterator));
    } else if (isset($_GET['type']) && $_GET['type'] == 'theory') {
        $iterator = new EfrontTheoryFilterIterator(new EfrontVisitableFilterIterator($basicIterator));
    } else if (isset($_GET['type']) && $_GET['type'] == 'examples') {
        $iterator = new EfrontExampleFilterIterator(new EfrontVisitableFilterIterator($basicIterator));
    } else {
        $iterator = new EfrontVisitableAndEmptyFilterIterator($basicIterator);
    }

    //Find the parents of each of these units, so that we can keep them in the tree
    foreach ($iterator as $key => $value) {
        foreach ($currentContent -> getNodeAncestors($key) as $parent) {
            $parents[$parent['id']] = $parent['id'];
        }
    }
    //This iterator keeps the special units (for example, tests or examples) plus their parents
    $iterator = new EfrontInArrayFilterIterator(new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST))), $parents);

    $smarty  -> assign("T_THEORY_TREE", $currentContent -> toHTML($iterator, 'dhtmlContentTree'));
} else {
	if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	    if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
	        /*Check current unit*/
	        if (isset($_GET['package_ID']) && $currentContent) {

	            if ($currentUser -> coreAccess['content'] == 'hidden') {
	                eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
	            }
	            try {
	                //Needed at commit and initialize
	                $_SESSION['package_ID'] = $_GET['package_ID'];
	                //
	                //Set organization_ID and objectives_global_to_system
	                $organization = eF_getTableData("scorm_sequencing_content_to_organization", "*", "content_ID = '".$_GET['package_ID']."'");
	                $_SESSION['organization_ID'] = $organization[0]['organization_ID'];

	                //Initialize SCORM information
	                $actProgInfo = eF_getTableData("scorm_sequencing_global_state_information", "*", "lessons_ID = '".$_SESSION['s_lessons_ID']."' AND organization_content_ID = '".$_SESSION['package_ID']."' AND users_LOGIN = '".$_SESSION['s_login']."'");


	                if (empty($actProgInfo)) {
	                    EfrontContentTreeSCORM :: initialize();
	                }
	                //Cut the tree
	                $scoBranch = array();
	                $scoBranch[$_GET['package_ID']] = $currentContent->tree->offsetGet($_GET['package_ID']);

	                //SCORM Object
	                $scoContent = new EfrontContentTreeSCORM($scoBranch);

	                if(!isset($_GET['navigation'])) {

	                    //Attempt a resumeAll navigation request
	                    $navigation = new navigation('resumeAll', $scoContent->flatTree[$_GET['target']]);
	                    $navigation = $scoContent->overall_sequencing_process($navigation, true, true);
	                    if($navigation->delivery_request['content_ID'] != '' && $navigation->deliverable) {
	                        $navigation = new navigation('resumeAll');
						} else {

							//A new attempt will begin on the Activity Tree. Therefore a new set of tracking data is required.
							foreach ($scoContent->flatTree as $key2 => $value2) {
								$scoContent->objectives->initialize($value2['content_ID']);
							}

							$navigation = new navigation('start', '');
							$navigation = $scoContent->overall_sequencing_process($navigation, true, true);

	                        if($navigation->delivery_request['content_ID'] != '' && $navigation->deliverable) {
	                            $navigation = new navigation('start', '');
	                        } else {
	                            $navigation = new navigation('', '');
	                        }
	                    }
					} else {
	                    $navigation = new navigation($_GET['navigation'], $scoContent->flatTree[$_GET['target']]);
	                }

	                //Call scorm sequencing to identify next unit
	                if ($navigation->navigation_request != '') {
	                    $navigation = $scoContent->overall_sequencing_process($navigation);
	                    //Set the current unit
	                    if ($navigation->delivery_request) {
	                        $_GET['view_unit'] = $navigation -> delivery_request['content_ID'];
	                        $currentUnit       = $currentContent -> seekNode($navigation->delivery_request['content_ID']);

	                        //The content tree does not hold data, so assign this unit its data
	                        $unitData    = new EfrontUnit($currentUnit['id']);
	                        $currentUnit['data'] = $unitData['data'];
	                    } else {
	                        $currentUnit = $currentContent -> seekNode($_SESSION['package_ID']);
	                    }
	                } else {
	                    $currentUnit = $currentContent -> seekNode($_SESSION['package_ID']);
	                }

					//Set completion icons
	                $iterator = new EfrontNodeFilterIterator(new RecursiveIteratorIterator($scoContent -> tree, RecursiveIteratorIterator :: SELF_FIRST));
					foreach ($iterator as $key => $value) {

						$objInfo = $scoContent->objectives->get_objective_info(false, $value['content_ID']);

						//Completion Status
	                    if ($objInfo['attempt_progress_status'] == 'true' && $objInfo['attempt_completion_status'] == 'true') {
	                        $trackActivityInfo['completion_status'] = 'completed';
	                    } else if ($objInfo['attempt_progress_status'] == 'true' && $objInfo['attempt_completion_status'] == 'false') {
	                        $trackActivityInfo['completion_status'] = 'incomplete';
	                    } else {
	                        $trackActivityInfo['completion_status'] = 'not attempted';
	                    }

	                    //Success Status
	                    if ($objInfo['objective_progress_status'] == 'true' && $objInfo['objective_satisfied_status'] == 'true') {
	                        $trackActivityInfo['success_status'] = 'passed';
	                    } else if ($objInfo['objective_progress_status'] == 'true' && $objInfo['objective_satisfied_status'] == 'false') {
	                        $trackActivityInfo['success_status'] = 'failed';
	                    } else {
	                        $trackActivityInfo['success_status'] = 'unknown';
	                    }

						if($trackActivityInfo['completion_status'] == 'completed' || $trackActivityInfo['success_status'] == 'passed') {
							$iterator->current()->offsetset('completed', true);
						} else if($trackActivityInfo['completion_status'] == 'incomplete' && $trackActivityInfo['success_status'] == 'unknown') {
							$iterator->current()->offsetset('incomplete', true);
						} else if ($trackActivityInfo['success_status'] == 'failed') {
							$iterator->current()->offsetset('failed', true);
						}

						if ($_student_) {
							if ($iterator->current()->offsetGet('completed')) {
                                $currentUser -> setSeenUnit($iterator->current(), $currentLesson, true);
							} else {
                                $currentUser -> setSeenUnit($iterator->current(), $currentLesson, false);
							}
						}

					}

					$scormState = $scoContent -> checkControlsValidy();

	                if ($navigation->end_sequencing_session == 'true') {
	                    eF_redirect("".basename($_SERVER['PHP_SELF']).'?ctg=content&');
	                }

	                if (in_array($currentUnit['scorm_version'], EfrontContentTreeSCORM :: $scorm2004Versions)) {
	                    $scormVersion = '1.3';
	                } else {
	                    $scormVersion = '1.2';
	                }

	            } catch (Exception $e) {
	                //pr($e);
	                unset($_GET['view_unit']);
	                $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
	                $message      = $e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
	                $message_type = 'failure';
	            }
	        }
	    } #cpp#endif
	} #cpp#endif


	if ($configuration['math_content'] && $configuration['math_images']) {
		$loadScripts[] = 'ASCIIMath2Tex';
	} elseif ($configuration['math_content']) {
		$loadScripts[] = 'ASCIIMathML';
	}
    try {
		$log_comments = $currentUnit['id']; //in order to store unit into logs
        //This is the basic content iterator, including even inactive, unpublished or empty units
		if (EfrontUser::isOptionVisible('tests')) {
			$visitableIterator         = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST));
		} else {
			$visitableIterator         = new EfrontNoTestsFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($currentContent -> tree), RecursiveIteratorIterator :: SELF_FIRST)));
		}

		$treeOptions = array('truncateNames' => 25, 'selectedNode' => $currentUnit['id']);
        //$_professor_ ? $treeOptions['edit'] = 1 : $treeOptions['edit'] = 0;

        $ruleCheck = true;
        if ($_student_ && $_change_ && $currentLesson -> options['tracking']) {
            //$currentUser -> setSeenUnit($currentUnit, $currentLesson, 1);
            //$currentContent -> markSeenNodes($currentUser);
            
            //$userProgress = EfrontStats :: getUsersLessonStatus($currentLesson, $currentUser -> user['login']);
            //$userProgress = $userProgress[$currentLesson -> lesson['id']][$currentUser -> user['login']];
            $seenContent  = EfrontStats :: getStudentsSeenContent($currentLesson -> lesson['id'], $currentUser -> user['login']);
            $seenContent  = $seenContent[$currentLesson -> lesson['id']][$currentUser -> user['login']];
            $userProgress = EfrontStats::lightGetUserStatusInLesson($_SESSION['s_login'], $currentLesson, $seenContent, $visitableIterator);			         

            $smarty -> assign("T_SEEN_UNIT", in_array($currentUnit['id'], array_keys($seenContent)));    //Notify smarty whether the student has seen the current unit
            if ($currentLesson -> options['rules']) {
                $ruleCheck = $currentContent -> checkRules($currentUnit['id'], $seenContent);
            }

            if ($ruleCheck !== true) {
                $message      = $ruleCheck;
                $message_type = 'failure';
                $smarty -> assign("T_RULE_CHECK_FAILED", true);
                $ruleCheck = false;
            }

            $smarty -> assign("T_USER_PROGRESS", $userProgress);
        }

        if ($_student_) {
        	if (preg_match("#</object>#", $currentUnit['data']) || preg_match("#</applet>#", $currentUnit['data'])) {
        		$smarty -> assign("T_CONTAINS_FLASH", true);
        	}
			//$smarty -> assign("T_NEXT_LESSON", $currentLesson -> getNextLesson());
			//$userTimeInUnit = EfrontTimes::formatTimeForReporting($times->getUserSessionTimeInUnit($currentUser->user['login'], $currentUnit['id']));
			$userTimeInUnit = EfrontTimes::formatTimeForReporting(EfrontLesson::getUserActiveTimeInUnit($currentUser->user['login'], $currentUnit['id']));
			$smarty -> assign("T_USER_TIME_IN_UNIT", $userTimeInUnit);
			//$smarty -> assign("T_USER_CURRENT_TIME_IN_UNIT", $times->getUserCurrentSessionTimeInUnit($currentUser->user['login'], $currentUnit['id']));
			$userTimeInLesson = EfrontTimes::formatTimeForReporting(EfrontLesson::getUserActiveTimeInLesson($currentUser->user['login'], $currentLesson->lesson['id']));
			$smarty -> assign("T_USER_CURRENT_TIME_IN_LESSON", $userTimeInLesson['total_seconds']);
			$smarty -> assign("T_USER_TIME_IN_LESSON", $userTimeInLesson);
			foreach ($currentLesson->getConditions() as $value) {
				if ($value['type'] == 'time_in_lesson') {
					$smarty -> assign("T_REQUIRED_TIME_IN_LESSON", $value['options'][0]*60);
				}
			}
        	
        	if ($_change_ && $currentLesson -> options['tracking'] && $currentUnit['options']['complete_unit_setting'] == EfrontUnit::COMPLETION_OPTIONS_AUTOCOMPLETE && $ruleCheck && !in_array($currentUnit['id'], array_keys($seenContent))) {
                $smarty -> assign("T_AUTO_SET_SEEN_UNIT", true);
            }

            if ($_change_ && $currentLesson -> options['tracking'] && $currentUnit['options']['complete_unit_setting'] == EfrontUnit::COMPLETION_OPTIONS_COMPLETEAFTERSECONDS && $ruleCheck && !in_array($currentUnit['id'], array_keys($seenContent)) && $userTimeInUnit['total_seconds'] > $currentUnit['options']['complete_time']) {
                $smarty -> assign("T_AUTO_SET_SEEN_UNIT", true);
            }
            
			/*$hideFeedback = false;
			foreach (new EfrontNoFeedbackFilterIterator(new EfrontVisitableAndEmptyFilterIterator($visitableIterator)) as $key => $value) {
				if (!$value['seen']) {
					$treeOptions['hideFeedback'] = true;
				}
			}		*/
            //This is an iterator with only valid units plus empty units, and is used for the navigation tree
            $smarty  -> assign("T_CONTENT_TREE", $currentContent -> toHTML(new EfrontVisitableAndEmptyFilterIterator($visitableIterator), 'dhtmlContentTree', $treeOptions, $scormState));
            
            //This is an iterator with only valid units, and is used for students to navigate back and forth
            $visitableIterator     = new EfrontVisitableFilterIterator($visitableIterator);
        } else {
			if ($_change_){
				$treeOptions['edit'] = 1;
			}		
            $smarty  -> assign("T_CONTENT_TREE", $currentContent -> toHTML($visitableIterator, 'dhtmlContentTree', $treeOptions, $scormState));
        }

        if ($_professor_ && !$currentUnit && $currentContent -> getFirstNode()) { //If a unit is not specified, then consider the first content unit by default
			$currentUnit = new EfrontUnit($currentContent -> getFirstNode() -> offsetGet('id'));
			$smarty -> assign("T_CURRENTUNITID", $currentUnit['id']);
		}

        if ($currentUnit) {
            //Let the template know that it is dealing with a SCORM unit
            if (strpos($currentUnit['ctg_type'], 'scorm') !== false) {
				$smarty -> assign("T_SCORM", true);
	            $smarty -> assign("T_SCORM_VERSION", $scormVersion);

            }

            $userObj = new stdClass();
            $userObj->name=array($currentUser->user['name'].' '.$currentUser->user['surname']);
            $userObj->account = new stdClass();
            $userObj->account->accountServiceHomePage=array($_SERVER['HTTP_HOST']);			//These 2 for 0.9
            $userObj->account->accountName=array($currentUser->user['login']);
            $userObj->account->homePage=$_SERVER['HTTP_HOST'];								//These 2 for 0.95
            $userObj->account->name=$currentUser->user['login'];
            $currentUnit['data'] = str_replace(
            		array(
            				urlencode('###LRS###'),
            				urlencode('###actor###'),
            				urlencode('###auth###'),
            				urlencode('###unit_id###'),),
            		array(
            				urlencode(G_SERVERNAME.'XAPI/'),
            				urlencode(json_encode($userObj)),
            				urlencode(base64_encode('auth')),
            				urlencode($currentUnit['id']),
            				), 
            		$currentUnit['data']);
            //If glossary is activated, transform content data accordingly
            if (EfrontUser::isOptionVisible('glossary') && !isset($_GET['print'])) {
				$currentUnit['data'] = glossary :: applyGlossary($currentUnit['data'], $currentLesson -> lesson['id']);
            }
            
            //Replace inner links. Inner links are created when linking from one unit to another, so they must point either to professor.php or student.php, depending on the user viewing the content
            $currentUnit['data'] = str_replace("##EFRONTINNERLINK##", $_SESSION['s_lesson_user_type'], $currentUnit['data']);
            $currentUnit['data'] = str_replace(array("##USER_NAME##", "##USER_LAST_NAME##"), array($currentUser->user['name'], $currentUser->user['surname']), $currentUnit['data']);

            if ($currentUnit['ctg_type'] == 'tests' || $currentUnit['ctg_type'] == 'feedback') {
                $loadScripts[] = 'scriptaculous/dragdrop';
                $loadScripts[] = 'includes/tests';

                include("tests/show_unsolved_test.php");
            }

			if (isset($_GET['print'])) {
				$currentUnit['data'] = preg_replace("#<script.*?>.*?</script>#", "&lt;script removed&gt;", $currentUnit['data']);
				$currentUnit['data'] = strip_tags($currentUnit['data'],'<img><applet><iframe><div><br><p><ul><li><ol><span><sub><sup><hr><h1><h2><h3><h4><h5><h6><table><tbody><tr><th><td><font><em><i><strong><u><b><blockquote><big><center><code>');
				
			}

			//in case unit is simply an iframe,do not load the print it button
	  		$contentStripped = strip_tags($currentUnit['data'],'<img><applet><iframe><div><br><p><ul><li><ol><span><sub><sup><hr><h1><h2><h3><h4><h5><h6><table><tbody><tr><th><td><font><em><i><strong><u><b><blockquote><big><center><code>');
       		if ($contentStripped == "<p></p>" || $contentStripped == "" ) {
				$smarty -> assign("T_DISABLEPRINTUNIT", true);
			}

			foreach ($loadedModules as $module) {
				if (isset($currentLesson -> options[$module -> className])  && $currentLesson -> options[$module -> className] == 1) {
					$module -> onBeforeShowContent($currentUnit);
				}
			}
        	if ($currentUnit['ctg_type'] == 'tests') {
				$test_id =  eF_getTableData("tests","id", "content_ID=".$currentUnit['id']);
				$smarty -> assign("T_UNIT_TEST_ID", $test_id[0]['id']);
			}
			$smarty -> assign("T_UNIT", $currentUnit);
			$info = array('student_name' => $currentUser->user['name'],
						  'student_surname' => $currentUser->user['surname'],
							'student_login' => $currentUser->user['login'],
							'student_email' => $currentUser->user['email']."'",
							'student_formatted_login' => formatLogin($currentUser->user['login']),
							'lesson_name' => $currentLesson->lesson['name'],
							'lesson_id' => $currentLesson->lesson['id'],
							'course_name' => $currentCourse->course['name'],
							'course_id' => $currentCourse->course['id'],
							'timestamp' => time(),
							'date' => formatTimestamp(time()));
			array_walk($info, create_function('&$v', '$v=htmlentities($v, ENT_QUOTES);'));
			$smarty -> assign("T_INFORMATION_JSON", json_encode($info));
			
			if ($currentUnit['options']['complete_unit_setting'] == EfrontUnit::COMPLETION_OPTIONS_COMPLETEAFTERSECONDS) {
				$smarty -> assign("T_REQUIRED_TIME_IN_UNIT", $currentUnit['options']['complete_time']);
			}
			//Next and previous units are needed for navigation buttons
			//package_ID denotes that a SCORM 2004 unit is active.
			if (!isset($_GET['package_ID'])) {
				$nextUnit = $currentContent -> getNextNode($currentUnit, $visitableIterator);
				$smarty -> assign("T_NEXT_UNIT",     $nextUnit);
				$previousUnit = $currentContent -> getPreviousNode($currentUnit, $visitableIterator);
				$smarty -> assign("T_PREVIOUS_UNIT", $previousUnit);
	            //Parents are needed for printing the titles
	            $smarty -> assign("T_PARENT_LIST",   $currentContent -> getNodeAncestors($currentUnit));
			} else {
			    //SCORM 2004 content handles navigation on its own, so it's illegal to have additional navigation handles
			    $smarty -> assign("T_PARENT_LIST",   $currentContent -> getNodeAncestors($_GET['package_ID']));
			    $smarty -> assign("T_SCORM_2004_TITLE", true);
			}


            $comments = array();
            $result   = array_merge(comments::getComments($currentLesson -> lesson['id'], false, $currentUnit['id']),
                                    comments::getComments($currentLesson -> lesson['id'], $currentUser, $currentUnit['id'], false, false));
            foreach ($result as $value) {
                if (!isset($comments[$value['id']])) {
                    $comments[$value['id']] = $value;
                }
            }

			foreach($comments as $key => $value) {
			    //$user = EfrontUserFactory :: factory($value['users_LOGIN']);
			    //$comments[$key]['avatar'] = $user -> getAvatar();
			}
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
        } else {
            $smarty -> assign("T_UNIT", array());
        }


        if ($_student_ && $_change_ && $currentLesson -> options['tracking']) {
        	//if ( $userProgress['lesson_passed'] && $userProgress['completed']) {        	 
        		$nextLesson = $currentUser -> getNextLesson($currentLesson, $_SESSION['s_courses_ID'], true);
        		$smarty -> assign("T_NEXTLESSON", $nextLesson);
				if ($currentLesson -> lesson['course_only'] && isset($_SESSION['s_courses_ID'])) {
					$res = eF_getTableData("users_to_courses","issued_certificate","courses_ID=".$_SESSION['s_courses_ID']." and users_LOGIN='".$_SESSION['s_login']."'");
					$current_course = new EfrontCourse($_SESSION['s_courses_ID']);
					$smarty -> assign("T_CERTIFICATE_EXPORT_METHOD", $current_course -> options['certificate_export_method']);
					if($res[0]['issued_certificate'] != ""){
						$smarty -> assign("T_CERTIFICATE_DOWNLOAD", true);
					}
				}
        	//}
            if ($currentUnit['options']['complete_unit_setting'] == EfrontUnit::COMPLETION_OPTIONS_COMPLETEWITHQUESTION && $currentUnit['options']['complete_question']) {
                $lessonQuestions = $currentLesson -> getQuestions();
                if (in_array($currentUnit['options']['complete_question'], array_keys($lessonQuestions))) {
                    $question = QuestionFactory::factory($currentUnit['options']['complete_question']);
                    $smarty -> assign("T_QUESTION", $question -> toHTML(new HTML_QuickForm()));
                    if (sizeof($_POST) > 0) {
                        try {
                            $question -> setDone($_POST['question'][$question -> question['id']]);
                            $results  = $question -> correct();                
                            if ($results['score'] > 0.99999) {
                                $currentUser -> setSeenUnit($currentUnit, $currentLesson, true);
                                echo 'correct';
                            }
                        } catch (Exception $e) {
                        	handleAjaxExceptions($e);
                        }
                        exit;
                    } elseif(in_array($currentUnit['id'], array_keys($seenContent))) {
                    	$smarty -> assign("T_QUESTION", $question -> toHTMLSolved(new HTML_QuickForm()));
                    }
                }
            }

            if (isset($_GET['set_seen']) && $ruleCheck) {
                try {
                    $currentUser -> setSeenUnit($currentUnit, $currentLesson, $_GET['set_seen']);
                    $newUserProgress     = EfrontStats :: getUsersLessonStatus($currentLesson, $currentUser -> user['login']);
                    $newPercentage       = $newUserProgress[$currentLesson -> lesson['id']][$currentUser -> user['login']]['overall_progress'];
                    $newConditionsPassed = $newUserProgress[$currentLesson -> lesson['id']][$currentUser -> user['login']]['conditions_passed'];
                    $newLessonPassed     = $newUserProgress[$currentLesson -> lesson['id']][$currentUser -> user['login']]['lesson_passed'];

                    $nextLesson = $currentUser -> getNextLesson($currentLesson, $_SESSION['s_courses_ID']);
                    if ($currentLesson -> lesson['course_only'] && isset($_SESSION['s_courses_ID'])) {
						$res = eF_getTableData("users_to_courses","issued_certificate","courses_ID=".$_SESSION['s_courses_ID']." and users_LOGIN='".$_SESSION['s_login']."'");
						if ($res[0]['issued_certificate'] != "") {
							$courseCertified = true;
						}	
					}
					
					echo json_encode(array($newPercentage, $newConditionsPassed, $newLessonPassed, false, false, false, $courseCertified));
                } catch (Exception $e) {
                	handleAjaxExceptions($e);
                }
                exit;
            }
            if (isset($_GET['ajax']) && isset($_GET['check_conditions'])) {
                try {
                    $newUserProgress     = EfrontStats :: getUsersLessonStatus($currentLesson, $currentUser -> user['login']);
                    $newPercentage       = $newUserProgress[$currentLesson -> lesson['id']][$currentUser -> user['login']]['overall_progress'];
                    $newConditionsPassed = $newUserProgress[$currentLesson -> lesson['id']][$currentUser -> user['login']]['conditions_passed'];
                    $newLessonPassed     = $newUserProgress[$currentLesson -> lesson['id']][$currentUser -> user['login']]['lesson_passed'];

                    $nextLesson = $currentUser -> getNextLesson($currentLesson, $_SESSION['s_courses_ID']);
					if ($currentLesson -> lesson['course_only'] && isset($_SESSION['s_courses_ID'])) {
						$res = eF_getTableData("users_to_courses","issued_certificate","courses_ID=".$_SESSION['s_courses_ID']." and users_LOGIN='".$_SESSION['s_login']."'");
						if ($res[0]['issued_certificate'] != "") {
							$courseCertified = true;
						}	
					}
                    echo json_encode(array($newPercentage, $newConditionsPassed, $newLessonPassed, false, false, false, $courseCertified));
                } catch (Exception $e) {
                	handleAjaxExceptions($e);
                }
                exit;
            }
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'next_lesson') {
            	try {
            		$nextLesson = $currentUser -> getNextLesson($currentLesson, $_SESSION['s_courses_ID']);       		
            		if ($nextLesson) {
	            		$nextLessonUrl = $_SERVER['PHP_SELF'].'?lessons_ID='.$nextLesson;
	            		!$_SESSION['s_courses_ID'] OR  $nextLessonUrl .= '&from_course='.$_SESSION['s_courses_ID'];
	            		echo json_encode(array('url' => $nextLessonUrl));
            		} else {
            			echo json_encode(array('url' => ''));
            		}
            	} catch (Exception $e) {
            		handleAjaxExceptions($e);
            	}
            	exit;
            }
        }


        $options = array();
        if ($_student_ && $currentLesson -> options['content_report'] && $ruleCheck) {
            $options[] = array('text' => _CONTENTREPORT, 'image' => "16x16/warning.png", 'href' => "content_report.php?".http_build_query($_GET), 'onclick' => "eF_js_showDivPopup(event, '"._CONTENTREPORT."', 1)", "target" => "POPUP_FRAME");
        }
        if ($currentLesson -> options['bookmarking'] && EfrontUser::isOptionVisible('bookmarks') && $ruleCheck) {
	        $options[] = array('text' => _ADDTHISPAGETOYOURBOOKMARKS, 'image' => "16x16/bookmark_add.png", 'onclick' => "addBookmark(this)");
        }
        if (EfrontUser::isOptionVisible('comments') && $ruleCheck) {
            $options[] = array('text' => _ADDCOMMENT, 'image' => "16x16/comment_add.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=comments&view_unit=".$_GET['view_unit']."&add=1&popup=1", 'onclick' => "eF_js_showDivPopup(event, '"._ADDCOMMENT."', 1)", "target" => "POPUP_FRAME");
        }
        //$options[] = array('text' => "open window", 'image' => "16x16/add.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=content&view_unit=".$_GET['view_unit']."&bare=1&popup=1", 'onclick' => "window.open('about:blank', 'testme', 'width=800, height=600')", "target" => "testme");

		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
		    if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
	            $scorm2004 = in_array($currentUnit['scorm_version'], EfrontContentTreeSCORM :: $scorm2004Versions);
	            if ($scorm2004) {
	                //This means that the content will not start automatically, so a handle must appear to prompt for user selection
	                if ($scormState['start'] == 'disabled') {
	                    $smarty -> assign("T_NO_START", true);
	                    unset($treeOptions['selectedNode']);
	                    //$treeOptions['expand'] = true;
	                    $treeOptions['show_hide'] = false;
	                    $treeOptions['truncateNames'] = 100;
	                    $subTreeIterator = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator(array($currentContent -> seekNode($_GET['package_ID']))), RecursiveIteratorIterator :: SELF_FIRST));
	                    $smarty  -> assign("T_SUBTREE", $currentContent -> toHTML($subTreeIterator, 'dhtmlTree', $treeOptions, $scormState));
	                    //$smarty -> assign("T_SUBTREE", '');
	                }
	                /*
	                 if ($scormState['start'] != 'hidden') {
	                 $options[] = array('text'  => _START,
	                 'image' => '16x16/start.png',
	                 'class' => $scormState['start'] == 'disabled' ? 'inactiveImage'      : '',
	                 'href'  => $scormState['start'] == 'disabled' ? 'javascript:void(0)' : basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=start&package_ID=".$currentUnit['package_ID']);
	                 }
	                 if ($scormState['resumeAll'] != 'hidden') {
	                 $options[] = array('text'  => _RESUMEALL,
	                 'image' => '16x16/continue.png',
	                 'class' => $scormState['resumeAll'] == 'disabled' ? 'inactiveImage'      : '',
	                 'href'  => $scormState['resumeAll'] == 'disabled' ? 'javascript:void(0)' : basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=resumeAll&package_ID=".$currentUnit['package_ID']);
	                 }
	                 */
	                if ($scormState['suspendAll'] != 'hidden') {
	                    $options[] = array('text'  => _SUSPENDALL,
		            				   'image' => '16x16/pause.png',
		                               'class' => $scormState['suspendAll'] == 'disabled' ? 'inactiveImage'      : '',
		            				   'href'  => basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=suspendAll&package_ID=".$currentUnit['package_ID'],
		                               'id'    => 'navigate_suspendAll');
	                }
	                if ($scormState['exitAll'] != 'hidden') {
	                    $options[] = array('text'  => _EXITALL,
		            				   'image' => '16x16/out.png',
		                               'class' => $scormState['exitAll'] == 'disabled' ? 'inactiveImage'      : '',
									   'href'  => basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=exitAll&package_ID=".$currentUnit['package_ID'],
		            				   'onclick'  => $scormState['exitAll'] == 'disabled' ? 'return false' : '',

		                               'id'    => 'navigate_exitAll');
	                }

	                if ($scormState['abandon'] != 'hidden') {
	                    $options[] = array('text'  => _ABANDON,
		            				   'image' => '16x16/stop.png',
		                               'class' => $scormState['abandon'] == 'disabled' ? 'inactiveImage'      : '',
									   'href'  => basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=abandon&package_ID=".$currentUnit['package_ID'],
		            				   'onclick'  => $scormState['abandon'] == 'disabled' ? 'return false' : '',

		            				   'id'    => 'navigate_abandon');
	                }
	                if ($scormState['abandonAll'] != 'hidden') {
	                    $options[] = array('text'  => _ABANDONALL,
		            				   'image' => '16x16/stop_red.png',
		                               'class' => $scormState['abandonAll'] == 'disabled' ? 'inactiveImage'      : '',
									   'href'  => basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=abandonAll&package_ID=".$currentUnit['package_ID'],
		            				   'onclick'  => $scormState['abandonAll'] == 'disabled' ? 'return false' : '',

		                               'id'    => 'navigate_abandonAll');
	                }
	                if ($scormState['previous'] != 'hidden') {
	                    $options[] = array('text'  => _PREVIOUS,
		            				   'image' => '16x16/'.$arrow_previous.'.png',
		                               'class' => $scormState['previous'] == 'disabled' ? 'inactiveImage'      : '',
									   'href'  => basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=previous&package_ID=".$currentUnit['package_ID'],
		            				   'onclick'  => $scormState['previous'] == 'disabled' ? 'return false' : '',

		                               'id'    => 'navigate_previous');
	                }
	                if ($scormState['continue']) {
	                    $options[] = array('text'  => _CONTINUE,
		            				   'image' => '16x16/'.$arrow_next.'.png',
		                               'class' => $scormState['continue'] == 'disabled' ? 'inactiveImage'      : '',
									   'href'  => basename($_SERVER['PHP_SELF'])."?ctg=content&navigation=continue&package_ID=".$currentUnit['package_ID'],
		            				   'onclick'  => $scormState['continue'] == 'disabled' ? 'return false' : '',

		                               'id'    => 'navigate_continue');
	                }
	            }
	        } #cpp#endif
	    }#cpp#endif

        if (!$scorm2004) {
	        if ($currentUnit['options']['hide_navigation'] != 1 && $currentUnit['options']['hide_navigation'] != 2) {
		        if ($previousUnit) {
		            $options[] = array('text'  => strip_tags($previousUnit['name']),
		            				   'image' => "16x16/$arrow_previous.png",
		            				   'href'  => $_SERVER['PHP_SELF']."?view_unit=".$previousUnit['id'],
		                               'id'    => 'navigate_previous');
		        }
		        if ($nextUnit) {
		            $options[] = array('text'  => strip_tags($nextUnit['name']),
		            				   'image' => "16x16/$arrow_next.png",
		            				   'href'  => $_SERVER['PHP_SELF']."?view_unit=".$nextUnit['id'],
		                               'id'    => 'navigate_continue');
		        }
	        }
        }
        $smarty -> assign("T_UNIT_OPTIONS", $options);
        //$smarty -> assign("T_UNIT_SETTINGS", array('nohandle' => 1));
        if ((!$currentLesson -> options['show_right_bar'] && $_student_) || $_COOKIE['rightSideBar'] == 'hidden') {
            $smarty -> assign("T_LAYOUT_CLASS", "centerFull hideLeft");
        } else {
            $smarty -> assign("T_LAYOUT_CLASS", $currentTheme -> options['toolbar_position'] == "left" ? "hideRight" : "hideLeft");    //Whether to show the sidemenu on the left or on the right
        }
        if ((!$currentLesson -> options['show_horizontal_bar'] && $_student_) || $_COOKIE['horizontalSideBar'] == 'hidden') {
            $smarty -> assign("T_HEADER_CLASS", "headerHidden");
        } else {
            $smarty -> assign("T_HEADER_CLASS", "header"); //$currentTheme -> options['toolbar_position'] == "left" ? "hideRight" : "hideLeft");    //Whether to show the sidemenu on the left or on the right
        }

        if (isset($currentUnit['options']['maximize_viewport']) && $currentUnit['options']['maximize_viewport'] && $currentUser -> getType($currentLesson) == "student") {
            $smarty -> assign("T_MAXIMIZE_VIEWPORT", 1);
        }
        if (isset($currentUnit['options']['scorm_asynchronous']) && $currentUnit['options']['scorm_asynchronous']) {
            $smarty -> assign("T_SCORM_ASYNCHRONOUS", 1);
        } else {
        	$smarty -> assign("T_SCORM_ASYNCHRONOUS", 0);
        }

		if (isset($currentUnit['options']['object_ids']) && $currentUnit['options']['object_ids']) {
            $smarty -> assign("T_OBJECT_IDS", $currentUnit['options']['object_ids']);
        }
        
        $content_side_modules = array();
        foreach ($loadedModules as $module) {
            if (isset($currentLesson -> options[$module -> className])  && $currentLesson -> options[$module -> className] == 1) {
                unset($lessonContentSideHTML);
                $lessonContentSideHTML = $module -> getContentSideInfo();
                // If the module has a lesson innertable
                if ($lessonContentSideHTML) {
                    // Get module html - two ways: pure HTML or PHP+smarty
                    // If no smarty file is defined then false will be returned
                    if ($module_smarty_file = $module -> getContentSmartyTpl()) {	//assignment not comparison
                        // Execute the php code -> The code has already been executed by above (**HERE**)
                        // Let smarty know to include the module smarty file
                        $content_side_modules[$module->className] = array('smarty_file' => $module_smarty_file);
                    } else {
                        // Present the pure HTML cod
                        $content_side_modules[$module->className] = array('html_code' => $lessonContentSideHTML);
                    }

                    $sideContentTitle = $module -> getContentSideTitle();
                    if ($sideContentTitle) {
                        $content_side_modules[$module->className]['title'] = $sideContentTitle;
                    } else {
                        $content_side_modules[$module->className]['title'] = $module -> getName();
                    }
                }
                
                if ($link = $module->getContentToolsLink()) {
                	$moduleToolsContent[] = $link;
                }
            }
        }

        $smarty -> assign("T_MODULE_TOOLS_LINKS", $moduleToolsContent);
        $smarty -> assign("T_CONTENT_SIDE_MODULES", $content_side_modules);
    } catch (Exception $e) {
        $smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
        $message      = $e -> getMessage().' &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
        $message_type = 'failure';
    }
}




// Used for toggle horizontal sidebar
if ($GLOBALS['currentTheme'] -> options['sidebar_interface'] == 1 || $GLOBALS['currentTheme'] -> options['sidebar_interface'] == 2) {
	$smarty -> assign("T_HORIZONTAL_BAR", 1);
}
if (!isset($_GET['edit'])) {
	$smarty -> load_filter('output', 'eF_template_setStartUnit');
}
