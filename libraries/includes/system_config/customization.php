<?php
//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

$modeForm  = new HTML_QuickForm("customization_disable_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=customization&tab=disable", "", null, true);
$modeForm -> registerRule('checkParameter', 'callback', 'eF_checkParameter');
//$modeForm -> addElement("select", "simple_mode",  _MODE, array(0 => _COMPLETEMODE, 1 => _SIMPLEMODE), 'class = "inputSelect" onchange = "document.location=\''.$_SERVER['PHP_SELF'].'?ctg=system_config&op=customization&mode=\'+this.options[this.options.selectedIndex].value"');
//$modeForm -> setDefaults()

//$modeForm -> addElement("advcheckbox", "mode_projects",  _PROJECTS, null, 'class = "inputCheckBox"', array(0,1));
$modes 					= array(EfrontConfiguration::MODE_INVISIBLE => _DISABLED, EfrontConfiguration::MODE_VISIBLE => _ALWAYSVISIBLE, EfrontConfiguration::MODE_VISIBLE_COMPLETE=>_VISIBLEONLYINCOMPLETEMODE);
$modes_not_invisible 	= array(EfrontConfiguration::MODE_VISIBLE => _ALWAYSVISIBLE, EfrontConfiguration::MODE_VISIBLE_COMPLETE=>_VISIBLEONLYINCOMPLETEMODE);
$modes_not_only 		= array(EfrontConfiguration::MODE_INVISIBLE => _DISABLED, EfrontConfiguration::MODE_VISIBLE => _ALWAYSVISIBLE);


$modeForm -> addElement("static", "separator", '<span style="font-weight:bold">'._FEATURES.'</span>');
$modeForm -> addElement("select", "mode_news",  _ANNOUNCEMENTS, $modes);
if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
		$modeForm -> addElement("select", "mode_archive",  _ARCHIVE, $modes);
	} #cpp#endif
} #cpp#endif
$modeForm -> addElement("select", "mode_backup",  _BACKUP." - "._RESTORE, $modes);
$modeForm -> addElement("select", "mode_bookmarks",  _BOOKMARKS, $modes);
$modeForm -> addElement("select", "mode_calendar",  _CALENDAR, $modes);
$modeForm -> addElement("select", "mode_comments",  _COMMENTS, $modes);
if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
		$modeForm -> addElement("select", "mode_course_instances",  _COURSEINSTANCES, $modes);
		$modeForm -> addElement("select", "mode_curriculum",  _CURRICULUM, $modes);

	} #cpp#endif
} #cpp#endif
$modeForm -> addElement("select", "mode_user_profile",  _CUSTOMIZEUSERSPROFILE, $modes);
$modeForm -> addElement("select", "mode_feedback",  _FEEDBACK, $modes);
$modeForm -> addElement("select", "mode_forum",  _FORUMS, $modes);
$modeForm -> addElement("select", "mode_glossary",  _GLOSSARY, $modes, 'onChange="checkDependency(this.options[this.options.selectedIndex].value, \'shared_glossary\')";checkDependency(this.options[this.options.selectedIndex].value, \'test_glossary\')"');
$modeForm -> addElement("select", "mode_groups",  _GROUPS, $modes);
$modeForm -> addElement("select", "mode_help",  _HELP, $modes);
$modeForm -> addElement("select", "mode_languages",  _LANGUAGES, $modes);
$modeForm -> addElement("select", "mode_maintenance",  _MAINTENANCE, $modes);
$modeForm -> addElement("select", "mode_messages",  _MESSAGES, $modes, 'onChange="checkDependency(this.options[this.options.selectedIndex].value, \'messages_student\')"');
$modeForm -> addElement("select", "mode_notifications",  _EMAILDIGESTS, $modes);
if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	$modeForm -> addElement("static", "sidenote", '<span class = "infoCell">'._WARNINGDISABLINGPAYMENTSWILLSETALLPRICESTOZERO.'</span>');
$modeForm -> addElement("select", "mode_payments",  _PAYMENTS, $modes);
} #cpp#endif
$modeForm -> addElement("select", "mode_projects",  _PROJECTS, $modes);
$modeForm -> addElement("select", "mode_version_key",  _REGISTRATIONUPDATE, $modes);
$modeForm -> addElement("select", "mode_statistics",  _STATISTICS, $modes);
$modeForm -> addElement("select", "mode_configuration",  _CONFIGURATIONVARIABLES, $modes_not_invisible);
if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
		$modeForm -> addElement("select", "mode_search_user",  _SEARCHUSER, $modes);
		$modeForm -> addElement("select", "mode_skillgaptests",  _SKILLGAPTESTS, $modes);
	} #cpp#endif
} #cpp#endif
if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	$modeForm -> addElement("select", "mode_surveys", _SURVEYS, $modes);
} #cpp#endif
$modeForm -> addElement("select", "mode_tests",  _TESTS, $modes, 'onChange="checkDependency(this.options[this.options.selectedIndex].value, \'questions_pool\')"');
$modeForm -> addElement("select", "mode_themes",  _THEMES, $modes);
$modeForm -> addElement("select", "mode_user_types",  _USERTYPES, $modes);


$modeForm -> addElement("static", "separator", '<span style="font-weight:bold">'._BEHAVIOR.'</span>');

$modeForm -> addElement("select", "mode_online_users",  _SHOWONLINEUSERS, $modes, 'onChange="checkDependency(this.options[this.options.selectedIndex].value, \'logout_user\')"');
$modeForm -> addElement("static", "sidenote", '<span class = "infoCell">'._DEPENDSON.' '._GLOSSARY.'</span>');
$modeForm -> addElement("select", "mode_shared_glossary",  _SHAREDGLOSSARY, $modes, "id='shared_glossary'");//@todo

$modeForm -> addElement("static", "sidenote", '<span class = "infoCell">'._DEPENDSON.' '._GLOSSARY.'</span>');
$modeForm -> addElement("select", "mode_test_glossary",  _TESTGLOSSARY, $modes, "id='test_glossary'");//@todo

$modeForm -> addElement("static", "sidenote", '<span class = "infoCell">'._DEPENDSON.' '._MESSAGES.'</span>');
$modeForm -> addElement("select", "mode_messages_student",  _MESSAGESSTUDENTS, $modes, "id='messages_student'");//@todo
if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
		$modeForm -> addElement("static", "sidenote", '<span class = "infoCell">'._DEPENDSON.' '._TESTS.'</span>');
		$modeForm -> addElement("select", "mode_questions_pool",  _QUESTIONSPOOL, $modes, "id='questions_pool'");//@todo
	} #cpp#endif
} #cpp#endif

$modeForm -> addElement("select", "mode_move_blocks",  _MOVEBLOCK, $modes);
$modeForm -> addElement("select", "mode_change_info",  _USERSCANCHANGEINFO, $modes);
$modeForm -> addElement("static", "sidenote", '<span class = "infoCell">'._YOUMAYWANTTODISABLEENABLERESETPASSWORDTOO.'</span>');
$modeForm -> addElement("select", "mode_change_pass",  _USERSCANCHANGEPASS, $modes);
$modeForm -> addElement("select", "mode_professor_courses",  _PROFESSORSCANCREATECOURSES, $modes);
$modeForm -> addElement("static", "sidenote", '<span class = "infoCell">'._DEPENDSON.' '._SHOWONLINEUSERS.'</span>');
$modeForm -> addElement("select", "mode_logout_user",  _LOGOUTUSER, $modes, "id='logout_user'");  //@todo
$modeForm -> addElement("select", "mode_tooltip",  _SHOWTOOLTIPS, $modes);

$modeForm -> addElement("select", "mode_simple_complete",  _USESIMPLECOMPLETEMODESWITCH, $modes_not_only);

if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
	$modeForm -> addElement("static", "separator", '<span style="font-weight:bold">'._ENTERPRISEOPTIONS.'</span>');
	$modeForm -> addElement("select", "mode_show_organization_chart",  _SHOWORGANIZATIONCHARTTOUSERS, $modes);
	$modeForm -> addElement("select", "mode_show_complete_org_chart",  _SHOWCOMPLETEORGCHART, $modes);
	$modeForm -> addElement("select", "mode_show_user_form",  _SHOWUSERFORMTOUSERS, $modes);
	$modeForm -> addElement("select", "mode_show_unassigned_users_to_supervisors",  _SHOWUNASSIGNEDEMPLOYEESTOSUPERVISORS, $modes);
	$modeForm -> addElement("select", "mode_allow_users_to_delete_supervisor_files",  _ALLOWUSERSTODELETEFILESSHAREDWITHSUPERVISORS, $modes);
	$modeForm -> addElement("select", "mode_propagate_courses_to_branch_users",  _AUTOMATICALLYPROPAGATEBRANCHCOURSESTOUSERS, $modes);
	//$modeForm -> addElement("select", "mode_allow_direct_login",  _ALLOWLOGINFROMDIRECTPAGE, $modes); //moved to user settings tab
} #cpp#endif

if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	$modeForm -> addElement("static", "separator", '<span style="font-weight:bold">'._SOCIALOPTIONS.'</span>');

	//$modeForm -> addElement("select", "mode_social_events",  _EVENTSLOGGING, $modes);
	$modeForm -> addElement("select", "mode_system_timeline",  _SYSTEMTIMELINES, $modes);
	$modeForm -> addElement("select", "mode_lessons_timeline",  _LESSONTIMELINES, $modes);
	$modeForm -> addElement("select", "mode_func_people",  _PEOPLECONNECTIONS, $modes);
	$modeForm -> addElement("select", "mode_func_comments",  _COMMENTSWALL, $modes);
	$modeForm -> addElement("select", "mode_func_userstatus",  _USERSTATUS, $modes);
} #cpp#endif

$modeForm -> setDefaults($GLOBALS['configuration']);
if (isset($currentUser -> coreAccess['configuration']) && $currentUser -> coreAccess['configuration'] != 'change') {
	$modeForm -> freeze();
} else {
	$modeForm -> addElement("submit", "submit", _SAVE, 'class = "flatButton"');

	if ($modeForm -> isSubmitted() && $modeForm -> validate()) {															  //If the form is submitted and validated
		$values = $modeForm -> exportValues();
		unset($values['submit']);	
		// Create the new binary map
		$socialModulesToBeActivated = 0;
		foreach ($values as $key => $value) {
			if ($value == 1 && strpos($key, 'social_') === 0) {
				$socialModulesToBeActivated += intval(substr($key, 7));
			}		
		}

		EfrontConfiguration :: setValue('social_modules_activated', $socialModulesToBeActivated);		
		foreach ($values as $key => $value) {
			EfrontConfiguration :: setValue($key, $value);
		}
		if ($values['mode_payments'] == 0) {
			eF_updateTableData("lessons", array('price' => 0), "id=id");
			eF_updateTableData("courses", array('price' => 0), "id=id");
		}
		if($values['mode_simple_complete'] == 0) {
			eF_updateTableData("users", array('simple_mode' => 0));
		}
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=customization&tab=disable&message=".urlencode(_SUCCESFULLYUPDATECONFIGURATION)."&message_type=success");
	}
	
}
$smarty -> assign("T_MODE_FORM", $modeForm -> toArray());
?>