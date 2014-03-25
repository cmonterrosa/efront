<?php

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

/*
 * Class defining the new module
* The name must match the one provided in the module.xml file
*/
class module_outlook_invitation extends EfrontModule {

	/**
	 * Get the module name, for example "Demo module"
	 *
	 * @see libraries/EfrontModule#getName()
	 */
	public function getName() {
		//This is a language tag, defined in the file lang-<your language>.php
		return _MODULE_OUTLOOK_INVITATION_OUTLOOK_INVITATION;
	}

	/**
	 * Return the array of roles that will have access to this module
	 * You can return any combination of 'administrator', 'student' or 'professor'
	 *
	 * @see libraries/EfrontModule#getPermittedRoles()
	 */
	public function getPermittedRoles() {
		return array("administrator", "professor");
	}

	/**
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getCenterLinkInfo()
	 */
	public function getCenterLinkInfo() {
		//return false;
		return array('title' => $this -> getName(),
				'image' => $this -> moduleBaseLink . 'img/outlook.png',
				'link'  => $this -> moduleBaseUrl);
	}

	public function getToolsLinkInfo() {
		$links = array('title' => $this -> getName(),
				'image' => $this -> moduleBaseLink . 'img/outlook.png',
				'link'  => $this -> moduleBaseUrl);
		if ($_SESSION['s_type'] == 'professor' || (G_VERSIONTYPE == 'enterprise' && $this->getCurrentUser()->aspects['hcd']->isSupervisor())) {
			return $links;
		} else {
			return false;
		} 
		
	}
	
	public function onInstall() {
		$result1 = eF_executeNew("
 CREATE TABLE `module_outlook_invitation` (
  `courses_ID` int(11) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `duration` int(10) unsigned NOT NULL default 0,
  `description` text,
  `location` text,
  `subject` varchar(255) DEFAULT 'Invitation to attend training',
  `sequence` int(11) DEFAULT '0',
  PRIMARY KEY (`courses_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8				
				");

		return true;

	}
	
	public function onUpgrade() {
		try {
			eF_executeNew("alter table module_outlook_invitation add subject varchar(255) default 'Invitation to attend training'");
			eF_executeNew("alter table module_outlook_invitation add sequence int default 0");
		} catch (Exception $e) {
			
		}
		return true;
	}

	public function onUninstall() {
		return eF_executeNew("DROP TABLE module_outlook_invitation;");
	}


	/**
	 * The main functionality
	 *
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getModule()
	 */
	public function getModule() {
		$smarty = $this -> getSmartyVar();
		$currentUser = $this -> getCurrentUser();

		$directionsTree  = new EfrontDirectionsTree();
        $directionsPaths = $directionsTree -> toPathString();
        $smarty -> assign("T_MODULE_OUTLOOK_INVITATION_DIRECTION_PATHS", $directionsPaths);
		
        $temp 		= eF_getTableData("module_outlook_invitation as m,courses as c","m.*,c.name,c.directions_ID","m.courses_ID=c.id");
		$events 	= array();
		foreach ($temp as $value) {
			$events[$value['courses_ID']] = $value;
		}
		if (isset($_GET['delete_event']) && eF_checkParameter($_GET['delete_event'], 'id') && in_array($_GET['delete_event'], array_keys($events))) {
			try {
				$event = $events[$_GET['delete_event']];
				$course = new EfrontCourse($event['courses_ID']);
				$users = $course->getCourseUsers(array('active' => true, archive => false, 'return_objects' => false));
				$recipients = array();
				foreach ($users as $value) {
					$recipients[] = $value['email'];
				}
				$this->cancelInvitation($course->course['id'], $recipients);
				
				eF_deleteTableData("module_outlook_invitation", "courses_ID=".$_GET['delete_event']);			
				
			} catch (Exception $e) {
				header("HTTP/1.0 500 ");
				echo $e -> getMessage().' ('.$e -> getCode().')';
			}
			exit;
		}
		
		if ($_SESSION['s_type'] != 'administrator') {			
			$userCourses = $currentUser->getUserCourses(array('archive' => 0, 'active' => true, 'return_objects' => false));
			
			if (G_VERSIONTYPE == 'enterprise') {
				if ($_SESSION['s_current_branch']) {
					$result = eF_getTableData("module_hcd_course_to_branch", "courses_ID", "branches_ID='{$_SESSION['s_current_branch']}'");
				} else {
					if ($currentUser->aspects['hcd']->isSupervisor()) {
						$result = eF_getTableData("module_hcd_course_to_branch", "courses_ID", "branches_ID in (select branches_ID from module_hcd_employee_works_at_branch where users_login='{$currentUser->user['login']}' and supervisor=1)");
					}
				}
				$branchCourses = array();
				foreach ($result as $value) {
					$branchCourses[$value['courses_ID']] = $value['courses_ID'];
				}
				
				foreach ($events as $key=>$value) {
					if (!isset($branchCourses[$key]) && !isset($userCourses[$key])) {
						unset($events[$key]);
					}
				}
			} else {
				foreach ($events as $key=>$value) {
					if (!isset($userCourses[$key])) {
						unset($events[$key]);
					}
				}				
			}
		}
		
		if (!isset($_GET['course'])) {

			$dataSource = $events;
			$tableName  = 'outlookInvitationsTable';

			isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'uint') ? $limit = $_GET['limit'] : $limit = G_DEFAULT_TABLE_SIZE;

			if (isset($_GET['sort']) && eF_checkParameter($_GET['sort'], 'text')) {
				$sort = $_GET['sort'];
				isset($_GET['order']) && $_GET['order'] == 'desc' ? $order = 'desc' : $order = 'asc';
			} else {
				$sort = 'login';
			}
			$dataSource = eF_multiSort($dataSource, $sort, $order);
			$smarty -> assign("T_TABLE_SIZE", sizeof($dataSource));
			if (isset($_GET['filter'])) {
				$dataSource = eF_filterData($dataSource, $_GET['filter']);
			}
			if (isset($_GET['limit']) && eF_checkParameter($_GET['limit'], 'int')) {
				isset($_GET['offset']) && eF_checkParameter($_GET['offset'], 'int') ? $offset = $_GET['offset'] : $offset = 0;
				$dataSource = array_slice($dataSource, $offset, $limit);
			}

			$smarty -> assign("T_DATA_SOURCE", $dataSource);
		} else {
			$course = new EfrontCourse($_GET['course']);
			
			$form = new HTML_QuickForm("import_outlook_invitation_form", "post", $this -> moduleBaseUrl."&course={$course->course['id']}&add_event=1".(isset($_GET['popup']) ? '&popup=1' : ''), "", null, true);
			$form -> registerRule('checkParameter', 'callback', 'eF_checkParameter');           //Register this rule for checking user input with our function, eF_checkParameter
			$form -> addElement('text', 'email', _SENDER, 'class = "inputText"');
			$form -> addElement('text', 'location', _LOCATION, 'class = "inputText"');
			$form -> addElement('text', 'subject', _SUBJECT, 'class = "inputText"');
			$form -> addElement('textarea', 'description', _DESCRIPTION, 'class = "inputTestTextarea" style = "width:80%;height:6em;"');
			//$form -> addElement('checkbox', 'calendar', _MODULE_OUTLOOK_INVITATION_CREATE_CALENDAR);
			//$form -> addElement('static', 'static', _MODULE_OUTLOOK_INVITATION_INFO);
			$form -> addElement('submit', 'submit_event_all', _MODULE_OUTLOOK_INVITATION_SENDALL, 'class=flatButton');
			$form -> addElement('submit', 'submit_event_new', _MODULE_OUTLOOK_INVITATION_SENDNEW, 'class=flatButton');
							
			if (empty($events[$course->course['id']])) {	//new invitation
				$currentEvent = null;
				$form->setDefaults(array('email' => $currentUser->user['email'], 'subject' => 'Invitation to attend training: '.$course->course['name']));
			} else {			//existing invitation
				$currentEvent = $events[$course->course['id']];
				$form->setDefaults(array(
						'email' => $currentEvent['email'], 
						'description' => $currentEvent['description'],
						'subject' => $currentEvent['subject'], 
						'location' => $currentEvent['location']));
			}

			if ($form -> isSubmitted() && $form -> validate()) {
				try {
					$message = "";
					// Set info to store into database
					$permanent_info = array("courses_ID"  => $course->course['id'],//$form -> exportValue('autocomplete_course_hidden'),
							"email"       => $form -> exportValue('email') ? $form -> exportValue('email') : $GLOBALS['configuration']['system_email'],
							"location"	  => $form -> exportValue('location'),
							"subject"	  => $form -> exportValue('subject'),
							"description" => $form -> exportValue('description'));
						
					if ($currentEvent) {
						$permanent_info['sequence'] = $currentEvent['sequence']+1;
						eF_updateTableData("module_outlook_invitation", $permanent_info, "courses_ID={$course->course['id']}");
					} else {
						eF_insertTableData("module_outlook_invitation", $permanent_info);
					}

					if ($form->exportValue('submit_event_all')) {
						$users = $course->getCourseUsers(array('active' => true, archive => false, 'return_objects' => false));
						$recipients = array();
						foreach ($users as $value) {
							$recipients[] = $value['email'];
						}
						$this->sendInvitation($course->course['id'], $recipients);
					}
					
//					$smarty->assign('T_RELOAD', true);
					if (isset($_GET['popup'])) {
						$this ->setMessageVar(_OPERATIONCOMPLETEDSUCCESSFULLY, 'success');
					} else {
						eF_redirect($this -> moduleBaseUrl."&message=".urlencode(_OPERATIONCOMPLETEDSUCCESSFULLY)."&message_type=success");
					}
				} catch (Exception $e) {
					$smarty -> assign("T_EXCEPTION_TRACE", $e -> getTraceAsString());
					$this ->setMessageVar($e -> getMessage().' ('.$e -> getCode().') &nbsp;<a href = "javascript:void(0)" onclick = "eF_js_showDivPopup(event, \''._ERRORDETAILS.'\', 2, \'error_details\')">'._MOREINFO.'</a>', 'failure');
				}
			}

			$form -> setJsWarnings(_BEFOREJAVASCRIPTERROR, _AFTERJAVASCRIPTERROR);
			$form -> setRequiredNote(_REQUIREDNOTE);
			$smarty -> assign('T_MODULE_OUTLOOK_INVITATION_FORM', $form -> toArray());
		}

		$smarty -> assign("T_MODULE_BASEDIR" , $this -> moduleBaseDir);
		$smarty -> assign("T_MODULE_BASELINK" , $this -> moduleBaseLink);
		$smarty -> assign("T_MODULE_BASEURL" , $this -> moduleBaseUrl);


		return true;
	}

	public function getModuleJS() {
		return $this->moduleBaseDir."module_outlook_invitation.js";
	}

	/**
	 * Specify which file to include for template
	 *
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getSmartyTpl()
	 */
	public function getSmartyTpl() {
		$smarty = $this -> getSmartyVar();
		$smarty -> assign("T_MODULE_OUTLOOK_INVITATION_BASEDIR" , $this -> moduleBaseDir);
		$smarty -> assign("T_MODULE_OUTLOOK_INVITATION_BASEURL" , $this -> moduleBaseUrl);
		$smarty -> assign("T_MODULE_OUTLOOK_INVITATION_BASELINK", $this -> moduleBaseLink);
		return $this -> moduleBaseDir."module_outlook_invitation_page.tpl";
	}

	public function addScripts() {
		return array("scriptaculous/effects", "scriptaculous/controls");
	}


	/**
	 * (non-PHPdoc)
	 * @see libraries/EfrontModule#getNavigationLinks()
	 */
	public function getNavigationLinks() {
		$links = array (array ('title' => _HOME, 'link'  => $_SERVER['PHP_SELF']),
				array ('title' => $this -> getName(), 'link'  => $this -> moduleBaseUrl));
		if ($_GET['add_event'] && $_GET['course'] && eF_checkParameter($_GET['course'], 'id')) {
			$course = new EfrontCourse($_GET['course']);
			$links[] = array('title' => $course->course['name'], 'link'  => $this -> moduleBaseUrl.'&course='.$_GET['course'].'&add_event=1');
		}
		return $links;
	}


	public function onAddUsersToCourse($courseId, $users, $lessonUsers) {
		$result = eF_getTableDataFlat("users", "login,email");
		$emails = array_combine($result['login'], $result['email']);
		
		foreach ($users as $value) {
			$recipients[] = $emails[$value['users_LOGIN']];
		}

		$this->sendInvitation($courseId, $recipients);
	}
	
	public function onRemoveUsersFromCourse($courseId, $users) {
		$result = eF_getTableDataFlat("users", "login,email");
		$emails = array_combine($result['login'], $result['email']);
		
		foreach ($users as $value) {
			$recipients[] = $emails[$value['users_LOGIN']];
		}
		
		$this->cancelInvitation($courseId, $recipients);
		
		return true;
	}
	
	
	protected function sendInvitation($courseId, $recipients) {
		$course = new EfrontCourse($courseId);
		if (!$course->course['start_date'] || !$course->course['end_date']) {	//Only courses with a defined scheduled are allowed to send invitations
			return true;
		}
		
		$result = eF_getTableData("module_outlook_invitation", "*", "courses_ID=".$courseId);
		if (empty($result)) {
			return false;
		}
		$body =  $result[0]['description'];		//WARNING: exchange server will use the mail body for the calendar body, whereas direct clients will use the $event['description']
		$subject = $result[0]['subject'];
		$event = array('start_date' => $course->course['start_date'],
				'duration' => round(($course->course['end_date'] - $course->course['start_date'])/60),
				'description' => str_replace("\r\n", "\\n", $result[0]['description']),
				'location' => $result[0]['location'],
				'subject' =>  $result[0]['subject'],
				'email' => $result[0]['email'],
				'sequence' => $result[0]['sequence'],
				'course_id' => $course->course['id']);		
		
		$calendarbody = $this->createEventContent($event);
		file_put_contents($this -> moduleBaseDir.'calendar_'.$event['id'].'.ics', $calendarbody);
		$flag = $this->eF_mail_multipart($event['email'], implode(",", $recipients), $subject, $body, $calendarbody, false, true);
	}
	
	protected function cancelInvitation($courseId, $recipients) {
		$course = new EfrontCourse($courseId);
	
		$result = eF_getTableData("module_outlook_invitation", "*", "courses_ID=".$courseId);
		if (empty($result)) {
			return false;
		}
		$body =  $result[0]['description'];		//WARNING: exchange server will use the mail body for the calendar body, whereas direct clients will use the $event['description']
		$subject = "Cancel ".$result[0]['subject'];
		$event = array(
				'start_date' => $course->course['start_date'],
				'duration' => round(($course->course['end_date'] - $course->course['start_date'])/60),
				'description' => str_replace("\r\n", "\\n", $body),
				'location' => $result[0]['location'],
				'subject' =>  $subject,
				'email' => $result[0]['email'],
				'sequence' => $result[0]['sequence']+1,
				'course_id' => $course->course['id']);
	
		$calendarbody = $this->createEventContent($event, true);
		
		file_put_contents($this -> moduleBaseDir.'calendar_'.$event['id'].'.ics', $calendarbody);
		$flag = $this->eF_mail_multipart($event['email'], implode(",", $recipients), $subject, $body, $calendarbody, false, true);
	}	
	
	protected function createEventContent($event, $cancel = false) {
		if ($cancel) {
			$method = "CANCEL";
			$status = "CANCELLED";
		} else {
			$method = "REQUEST";
			$status = "CONFIRMED";
		}
		
		//$description = str_replace("\n","\\n",str_replace(";","\;",str_replace(",",'\,',$event['description']))) . "\n";
		$end_timestamp = $event['start_date'] + $event['duration']*60;
		
		$created_date = new DateTime('@'.time());
		$start_date = new DateTime('@'.$event['start_date']);
		$end_date = new DateTime('@'.$end_timestamp);
		
		$created_date->setTimezone(new DateTimeZone('utc'));
		$start_date->setTimezone(new DateTimeZone('utc'));
		$end_date->setTimezone(new DateTimeZone('utc'));
		
		//$uid = $created_date->format('Ymd\THis\Z').'-'.$event['course_id'].'@'.G_SERVERNAME;
		$uid = 'c'.$event['course_id'].'@'.G_SERVERNAME;

		//Based on RFC 5545, http://tools.ietf.org/html/rfc5545
		$components[] = "BEGIN:VCALENDAR";
		$components[] = "PRODID:-//".G_SERVERNAME."//eFront ".G_VERSION_NUM."//EN";
		$components[] = "VERSION:2.0";
		$components[] = "METHOD:{$method}";	//optional
		$components[] = "BEGIN:VEVENT";
		$components[] = "UID:{$uid}";
		$components[] = "CREATED:{$created_date->format('Ymd\THis\Z')}";
		$components[] = "DTSTAMP:{$start_date->format('Ymd\THis\Z')}";
		$components[] = "DTSTART:{$start_date->format('Ymd\THis\Z')}";
		$components[] = "DTEND:{$end_date->format('Ymd\THis\Z')}";
		$components[] = "DESCRIPTION:{$event['description']}";		//WARNING: exchange server will use the mail body for the calendar body, whereas direct clients will use the $event['description']
		$components[] = "SUMMARY:{$event['subject']}";
		$components[] = "LOCATION:{$event['location']}";
		$components[] = "ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;CN={$event['email']};RSVP=TRUE:mailto:{$event['email']}";		
		$components[] = "LAST-MODIFIED:{$start_date->format('Ymd\THis\Z')}";
		$components[] = "PRIORITY:5";
		$components[] = "SEQUENCE:{$event['sequence']}";
		$components[] = "STATUS:{$status}";
		$components[] = "TRANSP:TRANSPARENT";		
		$components[] = "END:VEVENT";
		$components[] = "END:VCALENDAR";
		//pr($components);exit;
		$message = implode("\r\n", $components);	

		return $message;

	}


	protected function eF_mail_multipart($sender, $recipient, $subject, $textbody, $calendarbody, $onlyText = false, $bcc = false) {

		$hdrs = array('From'    => $sender,
				'Subject' => $subject,
				//'To'  	=> $recipient,
				'Date' => date("r"));
		if ($bcc) {
			//$hdrs['To'] = '';
		}

		$params = array("text_charset" => "UTF-8",
				"html_charset" => "UTF-8",
				"head_charset" => "UTF-8",
				"head_encoding" => "base64");


		$textparams = array(
				'charset'       => 'utf-8',
				'content_type'  => 'text/plain',
				'encoding'      => 'base64',
		);

		$calendarparams = array(
				'charset'       => 'utf-8',
				'content_type'  => 'text/calendar;method=REQUEST',
				'encoding'      => 'base64',
		);


		$email = new Mail_mimePart('', array('content_type' => 'multipart/alternative'));

		$textmime = $email->addSubPart($textbody, $textparams);
		$htmlmime = $email->addSubPart($calendarbody, $calendarparams);


		$final = $email->encode();
		$final['headers'] = array_merge($final['headers'], $hdrs);

		$smtp = Mail::factory('smtp', array('auth'      => $GLOBALS['configuration']['smtp_auth'] ? true : false,
				'host'      => $GLOBALS['configuration']['smtp_host'],
				'password'  => $GLOBALS['configuration']['smtp_pass'],
				'port'      => $GLOBALS['configuration']['smtp_port'],
				'username'  => $GLOBALS['configuration']['smtp_user'],
				'timeout'   => $GLOBALS['configuration']['smtp_timeout'],
				'localhost' => $_SERVER["HTTP_HOST"]));

		$result = $smtp -> send($recipient, $final['headers'], $final['body']);

		return $result;
	}
}
