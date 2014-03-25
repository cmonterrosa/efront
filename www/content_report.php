<?php
/**
* Student content report
*
* This file is the page which enables content error reporting
* @package eFront
* @version 1.0
*/

session_cache_limiter('none');
session_start();
$path = "../libraries/";

include_once $path."configuration.php";


try {
	$currentUser = EfrontUser :: checkUserAccess();
} catch (Exception $e) {
	echo "<script>parent.location = 'index.php?logout=true&message=".urlencode($e -> getMessage().' ('.$e -> getCode().')')."&message_type=failure'</script>";        //This way the frameset will revert back to single frame, and the annoying effect of 2 index.php, one in each frame, will not happen
	exit;
}


//echo $_SERVER['QUERY_STRING'];

    $load_editor = true;


    $form = new HTML_QuickForm("content_report_form", "post", "content_report.php", "", null, true);

    $form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');           //Register this rule for checking user input with our function, eF_checkParameter

    //$form -> addElement('text', 'topic', _TOPIC, 'class = "inputText"');
	//$form -> addRule('topic', _THEFIELD.' "'._TOPIC.'" '._ISMANDATORY, 'required', null, 'client');
	$form -> addElement('hidden', 'page', htmlspecialchars_decode(http_build_query($_GET)));
	$form -> addElement('textarea', 'notes', _NOTES, 'class = "inputTextArea" style="width:45em;height:15em;"');
	//$form -> addRule('notes', _THEFIELD.' "'._NOTES.'" '._ISMANDATORY, 'required', null, 'client');
    $form -> addElement('submit', 'submit_report', _REPORT, 'class = "flatButton"');

    if ($form -> isSubmitted()) {
        if ($form -> validate()) {
            $values = $form -> exportValues();
            $recipients 	= array();
            $lesson 		= new EfrontLesson($_SESSION['s_lessons_ID']);
            $lessonProfs    = $lesson -> getUsers("professor");
            foreach ($lessonProfs as $key => $value) {
                $recipients[] = $key;
            }


            if (strpos($values['page'], "glossary") !== false){
                $title 			= _ERRORREPORTFOR.'&nbsp;'.$lesson -> lesson['name'].'&nbsp;['._GLOSSARY.']';
            } elseif (strpos($values['page'], "edit_question") !== false){
                $title 			= _ERRORREPORTFOR.'&nbsp;'.$lesson -> lesson['name'].'&nbsp;['._QUESTION.']';
            } else{
                $contentId 	= mb_substr($values['page'],10);
                preg_match("/.*view_unit=(\d+).*/", $values['page'], $matches);

                $resultType = eF_getTableData("content", "ctg_type", "id=".$matches[1]);
                if ($resultType[0]['ctg_type'] == "tests"){
                    $title 			= _ERRORREPORTFOR.'&nbsp;'.$lesson -> lesson['name'].'&nbsp;['._TESTS.']';
                } else {
                    $title 			= _ERRORREPORTFOR.'&nbsp;'.$lesson -> lesson['name'].'&nbsp;['._CONTENT.']';
                }
            }
            $patterns     = array("/([&|\?])message=[^&]*(&message_type=[^&]*)?(&(.*))*/");
            $replacements = array("\$1\$4");
            $values['page'] = preg_replace($patterns, $replacements, $values['page']);

			$values['notes'] = strip_tags($values['notes']);
            $data 			= '<a href="'.G_SERVERNAME.'professor.php?lessons_ID='.$_SESSION['s_lessons_ID'].'&'.$values['page'].'">'._LINKTOTOPIC.'</a><br><br>'._NOTES.':&nbsp;'.$values['notes'];

            $pm = new eF_PersonalMessage($_SESSION['s_login'], $recipients, $title, $data);

            if ($pm -> send(true)) {
                $message      = _SUCCESFULLYSENDREPORT;
                $message_type = 'success';
            } else {
                $message      = $pm -> errorMessage;
                $message_type = 'failure';
            }
        }
    }

    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);

    $form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
    $form -> setRequiredNote(_REQUIREDNOTE);
    $form -> accept($renderer);

    $smarty -> assign('T_REPORTS_FORM', $renderer -> toArray());


$smarty -> assign("T_HEADER_LOAD_SCRIPTS", array());
$smarty -> assign("T_HEADER_EDITOR", $load_editor);
$smarty -> assign("T_MESSAGE", $message);
$smarty -> assign("T_MESSAGE_TYPE", $message_type);

//Main scripts, such as prototype
$mainScripts = getMainScripts();
$smarty -> assign("T_HEADER_MAIN_SCRIPTS", implode(",", $mainScripts));

$_GET['popup'] = 1; //In order to display correctly in horizontal themes
$smarty -> display("content_report.tpl");


?>
