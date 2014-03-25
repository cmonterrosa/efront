<?php 
/**
* 
* @package eFront
* @version 3.6.0
*/

if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

if (!EfrontUser::isOptionVisible('glossary')) {
    eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=control_panel&message=".urlencode(_UNAUTHORIZEDACCESS)."&message_type=failure");
}
//Create shorthands for user access rights, to avoid long variable names
!isset($currentUser -> coreAccess['glossary']) || $currentUser -> coreAccess['glossary'] == 'change' ? $_change_ = 1 : $_change_ = 0;

// ******************************************* Import form *******************************************
$importForm = new HTML_QuickForm("import_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=glossary&op=import", "", null, true);

$importForm -> addElement('file', 'import_file', _CSVFILE, 'class = "inputText"');
$importForm -> setMaxFileSize(FileSystemTree :: getUploadMaxSize() * 1024);
$importForm -> addElement('submit', 'submit_import', _SUBMIT, 'class = "flatButton"');

if ($importForm -> isSubmitted()) {
	try {
		if (!is_dir($currentUser -> user['directory']."/temp")) {
			mkdir($currentUser -> user['directory']."/temp", 0755);
		}
		$filesystem   = new FileSystemTree($currentUser -> user['directory']."/temp");
		$uploadedFile = $filesystem -> uploadFile('import_file');
		if ($uploadedFile['extension'] != 'csv') {
			$message = _YOUHAVETOUSEACSVFILE;
			eF_redirect("".basename($_SERVER['PHP_SELF'])."?ctg=glossary&message=".urlencode($message)."&message_type=failure");
		} else {
			if (($handle = fopen($uploadedFile['path'], "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
					$terms[] = $data;
				}
				fclose($handle);
			}
			
			$entries = array();
			foreach ($terms as $value){
				$entries[] = array("name" => $value[0], "info" => $value[1],"lessons_ID" => $_SESSION['s_lessons_ID'], "type" => 'general');
			}
			
			eF_insertTableDataMultiple("glossary", $entries);
		}
	} catch (Exception $e) {
		$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
		$message      = $e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>';
		$message_type = 'failure';
	}
}

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

$importForm -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
$importForm -> setRequiredNote(_REQUIREDNOTE);
$importForm -> accept($renderer);
$smarty -> assign('T_IMPORT_FORM', $renderer -> toArray());


$load_editor = true;

$entityName  = 'glossary';
if (EfrontUser::isOptionVisible('shared_glossary')) {
	$glossary = eF_getTableData("glossary", "id,name,info", "lessons_ID=".$currentLesson -> lesson['id']." OR lessons_ID=0");
} else {
	$glossary = eF_getTableData("glossary", "id,name,info", "lessons_ID=".$currentLesson -> lesson['id']);
}
foreach ($glossary as $value) {
    $legalValues[] = $value['id'];
}

$words = glossary :: getGlossaryWords($glossary);
$smarty -> assign("T_GLOSSARY", $words);

	    
include("entity.php");

