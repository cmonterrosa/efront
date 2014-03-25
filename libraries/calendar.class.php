<?php
/**
 * calendar Class file
 *
 * @package eFront
 * @version 3.6
 */

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

/**
 *
 * @author Periklis Venakis
 *
 */
class calendar extends EfrontEntity
{
	/**
	 * The calendar properties
	 *
	 * @since 3.6.7
	 * @var array
	 * @access public
	 */
	public $calendar = array();

	/**
	 * The available question types
	 *
	 * @var array
	 * @since 3.6.7
	 * @access public
	 */
	public static $calendarTypes = array('private' => _PRIVATE,
			'global'  => _GLOBAL,
			'course'  => _COURSE,
			'lesson'  => _LESSON,
			'group'   => _GROUP,
			'branch'  => _BRANCH,
			'sub_branch'  => _BRANCHWITHSUBBRANCHES);

	/**
	 * Create calendar
	 *
	 * This function is used to create calendar
	 *
	 * @param $fields An array of data
	 * @return calendar The new object
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function create($fields = array()) {
		$fields = array('data'        => $fields['data'],
				'timestamp'   => $fields['timestamp'] ? $fields['timestamp'] : time(),
				'active'      => isset($fields['active']) && $fields['active'] ? 1 : 0,
				'type'     	  => $fields['type'],
				'foreign_ID'  => $fields['foreign_ID'],
				'users_LOGIN' => $fields['users_LOGIN']);


		$newId    = eF_insertTableData("calendar", $fields);
		$result   = eF_getTableData("calendar", "*", "id=".$newId);                                            //We perform an extra step/query for retrieving data, sinve this way we make sure that the array fields will be in correct order (forst id, then name, etc)
		$calendar = new calendar($result[0]['id']);

		return $calendar;
	}


	/**
	 * (non-PHPdoc)
	 * @see libraries/EfrontEntity#getForm($form)
	 */
	public function getForm($form) {
		$GLOBALS['load_editor'] = true;
		$calendarTypes = $this -> filterCalendarTypes(EfrontUserFactory::factory($_SESSION['s_login']));

		$sidenote = '<a href = "javascript:void(0)" onclick = "Element.extend(this).up().select(\'select\').each(function (s) {if (s.name.match(/\[H\]/) || s.name.match(/\[i\]/)) {s.options.selectedIndex=0;}})">'._ALLDAY.'</a>';

		$form -> addElement('static', 'sidenote', $sidenote);
		$form -> addElement($this -> createDateElement($form, 'timestamp', _DATE, array('addEmptyOption' => array('H' => true, 'i' => true),'include_time' => true)));
		$form -> addElement('static', 'toggle_editor_code', 'toggleeditor_link');
		$form -> addElement('textarea', 'data', _EVENT, 'class = "simpleEditor inputTextarea" style = "width:98%;height:200px;"');
		if (sizeof($calendarTypes) > 1) {
			$form -> addElement('select', 'type', _EVENTTYPE, $calendarTypes, 'id = "select_type" onChange = "toggleAutoComplete(this.options[this.options.selectedIndex].value)"');
			$form -> addElement('static', 'sidenote', '<img id = "busy" src = "images/16x16/clock.png" style="display:none;" alt = "'._LOADING.'" title = "'._LOADING.'"/>');
			if ($this -> calendar['type'] || isset($_GET['course'])) {
				$form -> addElement('text', 'selection', _SELECT, 'id = "autocomplete" onkeypress = "$(\'foreign_ID\').value = \'\'" class = "autoCompleteTextBox" style = "width:400px"' );
				if ($this -> calendar['foreign_ID'] && eF_checkParameter($this -> calendar['foreign_ID'], 'id')) {
					switch($this -> calendar['type']) {
						case 'lesson': $selection = eF_getTableData("lessons", "name", "id=".$this -> calendar['foreign_ID']); break;
						case 'course': $selection = eF_getTableData("courses", "name", "id=".$this -> calendar['foreign_ID']); break;
						case 'group' : $selection = eF_getTableData("groups",  "name", "id=".$this -> calendar['foreign_ID']); break;
						case 'branch': case 'sub_branch': $selection = eF_getTableData("module_hcd_branch", "name", "branch_ID=".$this -> calendar['foreign_ID']); break;
						default:break;
					}
				}
			} else {
				$form -> addElement('text', 'selection', _SELECT, 'id = "autocomplete" onkeypress = "$(\'foreign_ID\').value = \'\'" class = "autoCompleteTextBox inactiveElement" style = "width:400px" disabled' );
			}
			$form -> addElement('static', 'autocomplete_note', _STARTTYPINGFORRELEVENTMATCHES);
			$form -> addElement('hidden', 'foreign_ID', '' , 'id="foreign_ID"');
		} else {
			$form -> addElement('select', 'type', _EVENTTYPE, $calendarTypes, 'style = "display:none"');
		}

		$form -> addElement('submit', 'submit', _SUBMIT, 'class = "flatButton"');
		if (!isset($_GET['edit'])) {
			$form -> addElement('submit', 'submit_another', _SUBMITANDADDANOTHER, 'class = "flatButton"');
		}

		$form -> setDefaults(array('data' 		=> $this -> calendar['data'],
				'type' 		=> $this -> calendar['type'],
				'foreign_ID' => $this -> calendar['foreign_ID'],
				'selection'  => $selection[0]['name']));
		if ($this -> calendar['timestamp']) {
			$form -> setDefaults(array('timestamp' 	=> $this -> calendar['timestamp']));
		} elseif (isset($_GET['view_calendar']) && $_GET['view_calendar']) {
			$form -> setDefaults(array('timestamp' 	=> $_GET['view_calendar']));
		} else {
			$form -> setDefaults(array('timestamp' 	=> time()));
		}


		if (isset($_GET['add']) && isset($_GET['course']) && eF_checkParameter($_GET['course'], 'id')) {
			$course = new EfrontCourse($_GET['course']);

			$form -> setDefaults(array('data' 		=> 'The course "'.$course -> course['name'].'" begins on '.formatTimestamp($course -> course['start_date'], 'time'),
					'type' 		=> 'course',
					'foreign_ID' => $course -> course['id'],
					'selection'  => $course -> course['name'],
					'timestamp' 	=> $course -> course['start_date']));
		}

		return $form;
	}

	/**
	 * (non-PHPdoc)
	 * @see libraries/EfrontEntity#handleForm($form)
	 */
	public function handleForm($form) {

		$values = $form -> exportValues();

		$timestamp = mktime($values['timestamp']['H'] ? $values['timestamp']['H'] : 0,
				$values['timestamp']['i'] ? $values['timestamp']['i'] : 0,
				0,
				$values['timestamp']['M'],
				$values['timestamp']['d'],
				$values['timestamp']['Y']);

		eF_checkParameter($values['foreign_ID'], 'id') OR $values['foreign_ID'] = 0;

		$this -> checkCalendarValues($values);

		if (isset($_GET['edit'])) {
			$this -> calendar["data"]       = $values['data'];
			$this -> calendar["timestamp"]  = $timestamp;
			$this -> calendar["type"]       = $values['type'] ? $values['type'] : 'private';
			$this -> calendar["foreign_ID"] = $values['foreign_ID'];

			$this -> persist();
			
			// clear cache
			$user = EfrontUserFactory::factory($_SESSION['s_login']);
			$cacheKey = "calendar:{$user->user['login']}";
			EfrontCache::getInstance()->deleteCache($cacheKey);	
		} else {
			$fields = array("data"        => $values['data'],
					"timestamp"   => $timestamp,
					"type"		  => $values['type'] ? $values['type'] : 'private',
					"foreign_ID"  => $values['foreign_ID'],
					"users_LOGIN" => $_SESSION['s_login']);

			$calendar = self :: create($fields);
			$this -> calendar = $calendar;
			
			// clear cache
			$user = $_SESSION['s_login'];
			$cacheKey = "calendar:{$user}";
			EfrontCache::getInstance()->deleteCache($cacheKey);
		}
	}

	public function checkCalendarValues($values) {
		try {
			switch ($values['type']) {
				case 'course': new EfrontCourse($values['foreign_ID']); break;
				case 'lesson': new EfrontLesson($values['foreign_ID']); break;
				case 'group':  new EfrontGroup($values['foreign_ID']);  break;
				case 'branch': case 'sub_branch': new EfrontBranch($values['foreign_ID']); break;
				default: break;
			}
		} catch (Exception $e) {
			throw new Exception (_INVALIDSELECTIONPLEASEPICKFROMLIST);
		}
	}

	public function filterCalendarTypes($user) {

		$user -> applyRoleOptions();
		$calendarTypes = self::$calendarTypes;

		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$user -> aspects['hcd'] -> isSupervisor() ? $supervisor = true : $supervisor = false;
		} else { #cpp#else
			unset($calendarTypes['branch']);
			unset($calendarTypes['sub_branch']);
		} #cpp#endif

		if (!$supervisor) {
			unset($calendarTypes['branch']);
			unset($calendarTypes['sub_branch']);
		}
		if (!$supervisor && ((isset($_SESSION['s_lesson_user_type']) && $_SESSION['s_lesson_user_type'] == 'student') || (!isset($_SESSION['s_lesson_user_type']) && $_SESSION['s_type'] == 'student'))) {
			unset($calendarTypes['course']);
			unset($calendarTypes['lesson']);
		}
		if ($user -> user['user_type'] != 'administrator') {
			unset($calendarTypes['global']);
		}
		if ($user -> user['user_type'] != 'administrator' && sizeof($user -> getGroups()) == 0) {
			unset($calendarTypes['group']);
		}

		if ($user -> coreAccess['course_settings'] && $user -> coreAccess['course_settings'] != "change") {
			unset($calendarTypes['course']);
		}

		if ($user -> coreAccess['settings'] && $user -> coreAccess['settings'] != "change") {
			unset($calendarTypes['lesson']);
		}

		return $calendarTypes;
	}

	/**
	 * Get global calendar events
	 *
	 * @return array A list of calendar events
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getGlobalCalendarEvents() {
		$result = eF_getTableData("calendar c", "c.*", "type = 'global' and foreign_ID=0");
		foreach ($result as $value) {
			$globalEvents[$value['id']] = $value;
		}
		return $globalEvents;
	}

	/**
	 * Get the calendar events that this user has created
	 *
	 * @param mixed $user A user login or an EfrontUser object
	 * @return array A list of calendar events
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getUserCalendarEvents($user) {
		$user   = EfrontUser::convertArgumentToUserLogin($user);
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$result = eF_getTableData("calendar ca left outer join lessons l on ca.foreign_ID=l.id
					left outer join courses c on ca.foreign_ID=c.id
					left outer join groups  g on ca.foreign_ID=g.id
			left outer join module_hcd_branch b on ca.foreign_ID=b.branch_ID",
			"ca.*, l.name as lesson_name, c.name as course_name, b.name as branch_name, g.name as group_name",
			"users_LOGIN='".$user."'");
		} else { #cpp#else
			$result = eF_getTableData("calendar ca left outer join lessons l on ca.foreign_ID=l.id
					left outer join courses c on ca.foreign_ID=c.id
			left outer join groups  g on ca.foreign_ID=g.id",
			"ca.*, l.name as lesson_name, c.name as course_name, g.name as group_name",
			"users_LOGIN='".$user."'");
		} #cpp#endif


		$userCalendarEvents = self::functionCalculateEventTypeName($result);

		return $userCalendarEvents;
	}


	/**
	 * Populate the 'name' attribute depending on whether the event is of type lesson,course,group or branch
	 *
	 * @param array $events An events array
	 * @return array The same events array, augmented with the 'name' key/value
	 * @since 3.6.7
	 * @access private
	 * @static
	 */
	private static function functionCalculateEventTypeName($events) {
		$userCalendarEvents = array();
		foreach ($events as $value) {
			$value['name'] = '';
			switch($value['type']){
				case 'lesson': $value['name'] = self::$calendarTypes[$value['type']].': '.$value['lesson_name']; break;
				case 'course': $value['name'] = self::$calendarTypes[$value['type']].': '.$value['course_name']; break;
				case 'group' : $value['name'] = self::$calendarTypes[$value['type']].': '.$value['group_name'];  break;
				case 'branch': case 'sub_branch': $value['name'] = self::$calendarTypes[$value['type']].': '.$value['branch_name']; break;
				default: break;
			}

			$userCalendarEvents[$value['id']] = $value;
		}
		return $userCalendarEvents;
	}

	/**
	 * Delete the calendar events that this user has created
	 *
	 * @param mixed $user A user login or an EfrontUser object
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function deleteUserCalendarEvents($user) {
		$user   = EfrontUser::convertArgumentToUserLogin($user);
		eF_deleteTableData("calendar", "users_LOGIN='$user'");
	}

	/**
	 * Get the calendar events that have to do with the specified lesson
	 *
	 * @param mixed $lesson A lesson id or an EfrontLesson object
	 * @return array A list of calendar events
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getLessonCalendarEvents($lesson) {
		$lesson = EfrontLesson::convertArgumentToLessonId($lesson);
		$result = eF_getTableData("calendar ca left outer join lessons l on ca.foreign_ID=l.id", "ca.*, l.name as lesson_name", "type = 'lesson' and foreign_ID=".$lesson);
		$lessonCalendarEvents = self::functionCalculateEventTypeName($result);
		return $lessonCalendarEvents;
	}

	/**
	 * Delete the calendar events related to the specified lesson
	 *
	 * @param mixed $lesson A lesson id or an EfrontLesson object
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function deleteLessonCalendarEvents($lesson) {
		$lesson = EfrontLesson::convertArgumentToLessonId($lesson);
		eF_deleteTableData("calendar", "type = 'lesson' and foreign_ID=".$lesson);
	}

	/**
	 * Get all calendar events related to lessons
	 *
	 * @return array A list of calendar events
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getCalendarEventsForAllLessons() {
		$result = eF_getTableData("calendar ca left outer join lessons l on ca.foreign_ID=l.id", "ca.*, l.name as lesson_name", "type = 'lesson'");
		$lessonCalendarEvents = self::functionCalculateEventTypeName($result);
		return $lessonCalendarEvents;
	}

	/**
	 * Get the calendar events that have to do with the specified course
	 *
	 * @param mixed $course A course id or an EfrontCourse object
	 * @return array A list of calendar events
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getCourseCalendarEvents($course) {
		$course = EfrontCourse::convertArgumentToCourseId($course);
		$result = eF_getTableData("calendar ca left outer join coursers c on ca.foreign_ID=c.id", "ca.*, c.name as course_name", "type = 'course' and foreign_ID=".$course);
		$courseCalendarEvents = self::functionCalculateEventTypeName($result);
		return $courseCalendarEvents;
	}

	/**
	 * Delete the calendar events related to the specified course
	 *
	 * @param mixed $course A course id or an EfrontCourse object
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function deleteCourseCalendarEvents($course) {
		$course = EfrontCourse::convertArgumentToCourseId($course);
		eF_deleteTableData("calendar", "type = 'course' and foreign_ID=".$course);
	}

	/**
	 * Get the calendar events that have to do with the specified group
	 *
	 * @param mixed $group A group id or an EfrontGroup object
	 * @return array A list of calendar events
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getGroupCalendarEvents($group) {
		$group = EfrontGroup::convertArgumentToGroupId($group);
		$result = eF_getTableData("calendar ca left outer join grouprs c on ca.foreign_ID=c.id", "ca.*, c.name as group_name", "type = 'group' and foreign_ID=".$group);
		$groupCalendarEvents = self::functionCalculateEventTypeName($result);
		return $groupCalendarEvents;
	}

	/**
	 * Delete the calendar events related to the specified group
	 *
	 * @param mixed $group A group id or an EfrontGroup object
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function deleteGroupCalendarEvents($group) {
		$group = EfrontGroup::convertArgumentToGroupId($group);
		eF_deleteTableData("calendar", "type = 'group' and foreign_ID=".$group);
	}

	/**
	 * Get the calendar events that have to do with the specified branch
	 *
	 * @param int $branch A branch id
	 * @return array A list of calendar events
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getBranchCalendarEvents($branch) {
		$result = eF_getTableData("left outer join module_hcd_branch b on ca.foreign_ID=b.branch_ID", "ca.*, b.name as branch_name", "(type = 'branch' or type = 'sub_branch') and foreign_ID=".$branch);
		$branchCalendarEvents = self::functionCalculateEventTypeName($result);
		return $branchCalendarEvents;
	}

	/**
	 * Delete the calendar events related to the specified branch
	 *
	 * @param mixed $lesson A branch id
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function deleteBranchCalendarEvents($branch) {
		if (eF_checkParameter($branch, 'id')) {
			eF_deleteTableData("calendar", "(type = 'branch' or type = 'sub_branch') and foreign_ID=".$branch);
		}
	}

	/**
	 * Return a list of all calendar events that should be presented to the user
	 * Administrators view all events, except for private events for other users
	 *
	 * @param mixed $user A user login or an EfrontUser object
	 * @return array A list of calendar events
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getCalendarEventsForUser($user) {
		if (!($user instanceOf EfrontUser)) {
			$user = EfrontUserFactory::factory($user);
		}

		$parameters = "calendar:{$user->user['login']}";
				
		if (($events = EfrontCache::getInstance()->getCache($parameters)) !== false) {
			$events = unserialize($events);
		} else {
			if ($user -> user['user_type'] == 'administrator') {
				$events = self :: getCalendarEventsForAdministrator($user);
			} else {
				$events = self :: getCalendarEventsForNonAdministrator($user);
			}
			EfrontCache::getInstance()->setCache($parameters, serialize($events), 86400);
		}
		return $events;
	}

	/**
	 * Return a list of all public events, plus the private events for the user
	 *
	 * @param mixed $user A user login or an EfrontUser object
	 * @return array A list of calendar events
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getCalendarEventsForAdministrator($user) {
		$user   = EfrontUser::convertArgumentToUserLogin($user);
		$personalEvents = $allEvents = array();
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$result = eF_getTableData("calendar ca left outer join lessons l on ca.foreign_ID=l.id
					left outer join courses c on ca.foreign_ID=c.id
					left outer join groups  g on ca.foreign_ID=g.id
			left outer join module_hcd_branch b on ca.foreign_ID=b.branch_ID",
			"ca.*, l.name as lesson_name, c.name as course_name, b.name as branch_name, g.name as group_name", "type != 'private'");
		} else { #cpp#else
			$result = eF_getTableData("calendar ca left outer join lessons l on ca.foreign_ID=l.id
					left outer join courses c on ca.foreign_ID=c.id
			left outer join groups  g on ca.foreign_ID=g.id",
			"ca.*, l.name as lesson_name, c.name as course_name, g.name as group_name", "type != 'private'");
		} #cpp#endif

		$allEvents = self::functionCalculateEventTypeName($result);
		$personalEvents = self :: getUserCalendarEvents($user);
		$userEvents = $personalEvents + $allEvents;

		return $userEvents;
	}

	/**
	 * Return a list of all calendar events that should be presented to a user
	 * that is not an administrator
	 *
	 * @param mixed $user A user login or an EfrontUser object
	 * @return array A list of calendar events
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function getCalendarEventsForNonAdministrator($user) {
		$user   = EfrontUser::convertArgumentToUserLogin($user);
		$personalEvents = $globalEvents = $lessonEvents = $courseEvents = $groupEvents = $branchEvents = $subbranchEvents = array();
		$result = eF_getTableData("calendar c", "c.*", "type = 'global' and foreign_ID=0");
		foreach ($result as $value) {
			$globalEvents[$value['id']] = $value;
		}
		$result = eF_getTableData("lessons l, calendar ca, users_to_lessons ul", "ca.*, l.name", "ul.users_LOGIN='$user' and ca.foreign_ID=ul.lessons_ID and ul.lessons_ID=l.id and l.archive=0 and ul.archive=0 and ca.type = 'lesson'");
		foreach ($result as $value) {
			$lessonEvents[$value['id']] = $value;
		}
		$result = eF_getTableData("courses c, calendar ca, users_to_courses uc", "ca.*, c.name", "uc.users_LOGIN='$user' and ca.foreign_ID=uc.courses_ID and uc.courses_ID=c.id and c.archive=0 and uc.archive=0 and ca.type = 'course'");
		foreach ($result as $value) {
			$courseEvents[$value['id']] = $value;
		}

		$result = eF_getTableData("groups g, calendar ca, users_to_groups ug", "ca.*, g.name", "ug.users_LOGIN='$user' and ca.foreign_ID=ug.groups_ID and ug.groups_ID=g.id and ca.type = 'group'");
		foreach ($result as $value) {
			$groupEvents[$value['id']] = $value;
		}

		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$result = eF_getTableData("module_hcd_branch b, calendar ca, module_hcd_employee_works_at_branch wb", "ca.*, b.name", "wb.users_LOGIN='$user' and ca.foreign_ID=wb.branch_ID and b.branch_ID=wb.branch_ID and ca.type = 'branch'");
			foreach ($result as $value) {
				$branchEvents[$value['id']] = $value;
			}

			$userParentBranches = array();
			$branchesTree = new EfrontBranchesTree();
			$result = eF_getTableData("module_hcd_employee_works_at_branch", "branch_ID", "users_login='$user' and assigned=1");
			foreach ($result as $value) {
				foreach ($branchesTree->getNodeAncestors($value['branch_ID']) as $node) {
					$userParentBranches[] = $node['branch_ID'];
				}
			}
			$userParentBranches = array_unique($userParentBranches);
			if (!empty($userParentBranches)) {
				$result = eF_getTableData("module_hcd_branch b, calendar ca", "ca.*, b.name", "ca.foreign_ID=b.branch_ID and b.branch_ID in (".implode(",", $userParentBranches).") and ca.type = 'sub_branch'");
				foreach ($result as $value) {
					$subbranchEvents[$value['id']] = $value;
				}
			}
		} #cpp#endif

		$personalEvents = self :: getUserCalendarEvents($user);
		$userEvents = $personalEvents + $globalEvents + $lessonEvents + $courseEvents + $groupEvents + $branchEvents + $subbranchEvents;

		return $userEvents;
	}


	/**
	 * Sort calendar events in a way suitable for calendar depiction
	 *
	 * @param array $unsortedEvents The list of events
	 * @return array A list of calendar events, sorted and structured by time
	 * @since 3.6.7
	 * @access public
	 * @static
	 */
	public static function sortCalendarEventsByTimestamp($unsortedEvents) {
		$events = array();
		foreach ($unsortedEvents as $event) {
			$events[$event['timestamp']]['id'][]  			 = $event['id'];
			$events[$event['timestamp']]['data'][] 			 = $event['data'];
		}

		return $events;
	}


	public static function filterCalendarEvents($events, $showInterval, $viewCalendar) {
		$timestampInfo = getdate($viewCalendar);                                                          //Extract date information from timestamp
		$timestampInfo['wday'] == 0 ? $timestampInfo['wday'] = 7 : '';                                    //getdate() returns week days from 0-6, with Sunday beeing 0. So, we convert Sunday to 7
		$monthStart = mktime(0,  0,  0,  $timestampInfo['mon'],     1,                                                   $timestampInfo['year']);
		$monthEnd   = mktime(23, 59, 59, $timestampInfo['mon'] + 1, 0,                                                   $timestampInfo['year']);
		$weekStart  = mktime(0,  0,  0,  $timestampInfo['mon'],     $timestampInfo['mday'] - $timestampInfo['wday'] + 1, $timestampInfo['year']);
		$weekEnd    = mktime(23, 59, 59, $timestampInfo['mon'],     $timestampInfo['mday'] - $timestampInfo['wday'] + 7, $timestampInfo['year']);
		$dayStart   = mktime(0,  0,  0,  $timestampInfo['mon'],     $timestampInfo['mday'],                              $timestampInfo['year']);
		$dayEnd     = mktime(23, 59, 59, $timestampInfo['mon'],     $timestampInfo['mday'],                              $timestampInfo['year']);

		foreach ($events as $event) {                                                         //Assign events on each interval
			$timestamp = $event['timestamp'];
			if ($timestamp >= $monthStart && $timestamp <= $monthEnd) {
				$month_events[$event['id']] = $event;
			}
			if ($timestamp >= $weekStart && $timestamp <= $weekEnd) {
				$week_events[$event['id']] = $event;
			}
			if ($timestamp >= $dayStart && $timestamp <= $dayEnd) {
				$day_events[$event['id']] = $event;
			}
		}

		switch ($showInterval) {
			case 'all':   $intervalEvents = $events; 	   break;
			case 'month': $intervalEvents = $month_events; break;
			case 'week':  $intervalEvents = $week_events;  break;
			case 'day':
			default: 	  $intervalEvents = $day_events;   break;
		}

		return $intervalEvents;
	}
}
