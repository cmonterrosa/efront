<?php

/**
 * Abstract class for any Import class - serves as an interface for subsequent
 * developed importers
 *
 * @package eFront
 * @abstract
 */
abstract class EfrontImport
{
	/**
	 * The contents of the file to be imported
	 *
	 * @since 3.6.1
	 * @var string
	 * @access private
	 */
	public $fileContents;

	/**
	 * Various options like duplicates handling are stored in the options array
	 *
	 * @since 3.6.1
	 * @var array
	 * @access private
	 */
	public $options;


	/**
	 * Log where the results of the import are stored
	 *
	 * @since 3.6.1
	 * @var array
	 * @access private
	 */
	protected $log = array();

    /**
     * Import the data from the file following the designated options
     *
     * <br/>Example:
     * <code>
     * $importer -> import(); //returns something like /var/www/efront/upload/admin/
     * $logMessages = $importer -> getLogMessages();
     * </code>
     *
     * @return void
     * @since 3.6.1
     * @access public
     */


    /**
     * Get the log of the import operations
     *
     * <br/>Example:
     * <code>
     * $importer -> import(); //returns something like /var/www/efront/upload/admin/
     * $logMessages = $importer -> getLogMessages();
     * </code>
     *
     * @return array with subarrays "success" and "failure" each with corresponding messages
     * @since 3.6.1
     * @access public
     */
	public function getLogMessages() {
		return $this -> log;
	}


	protected static $datatypes = false;
	public static function getImportTypes() {
		if (!self::$datatypes) {
			self::$datatypes = array("anything"		  => _IMPORTANYTHING,
							   "users" 			  => _USERS,
							   "users_to_courses" => _USERSTOCOURSES,
								"users_to_lessons" => _USERSTOLESSONS);

			if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
				self::$datatypes["users_to_groups"] = _USERSTOGROUPS;
			} #cpp#endif


			if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
				self::$datatypes["branches"] = _BRANCHES;
				self::$datatypes["job_descriptions"] = _JOBDESCRIPTIONS;
				self::$datatypes["skills"] = _SKILLS;
				self::$datatypes["users_to_jobs"] = _USERSTOJOBS;
				self::$datatypes["users_to_skills"] = _USERSTOSKILLS;

			} #cpp#endif
		}
		return self::$datatypes;
	}


	public function getImportTypeName($import_type) {
		if (!$datatypes) {
			$datatypes = EfrontImport::getImportTypes();
		}
		return $datatypes[$import_type];
	}

	public function __construct($filename, $_options) {
		$this -> fileContents = file_get_contents($filename);
		$this -> options  = $_options;
	}

	/*
	 * All following functions cache arrays of type "entity_name" => array("entity_ids of entities with name=entity_name")
	 */
	protected $courseNamesToIds = false;
	protected function getCourseByName($courses_name) {
		if (!$this -> courseNamesToIds) {
			$constraints = array ('return_objects' => false);
			$courses = EfrontCourse::getAllCourses($constraints);
			foreach($courses as $course) {
				if (!isset($this -> courseNamesToIds[$course['name']])) {
					$this -> courseNamesToIds[$course['name']] = array($course['id']);
				} else {
					$this -> courseNamesToIds[$course['name']][] = $course['id'];
				}
			}
		}
		return $this -> courseNamesToIds[$courses_name];
	}

	protected $lessonNamesToIds = false;
	protected function getLessonByName($lessons_name) {
		if (!$this -> lessonNamesToIds) {
			$lessons = eF_getTableData("lessons", "id,name", "archive=0");			
			foreach($lessons as $lesson) {
				if (!isset($this -> lessonNamesToIds[mb_strtolower($lesson['name'])])) {
					$this -> lessonNamesToIds[mb_strtolower($lesson['name'])] = array($lesson['id']);
				} else {
					$this -> lessonNamesToIds[mb_strtolower($lesson['name'])][] = $lesson['id'];
				}
			}
		}
		return $this -> lessonNamesToIds[mb_strtolower($lessons_name)];
	}
	
	private $groupNamesToIds = false;
	protected function getGroupByName($group_name) {
		if (!$this -> groupNamesToIds) {
			$groups = EfrontGroup::getGroups();
			foreach($groups as $group) {
				if (!isset($this -> groupNamesToIds[$group['name']])) {
					$this -> groupNamesToIds[$group['name']] = array($group['id']);
				} else {
					$this -> groupNamesToIds[$group['name']][] = $group['id'];
				}
			}
		}
		return $this -> groupNamesToIds[$group_name];
	}

	#cpp#ifdef ENTERPRISE
	private $branchNamesToIds = false;
	protected function getBranchByName($branch_name) {
		if (!$this -> branchNamesToIds) {
			$branches = EfrontBranch::getAllBranches();
			foreach($branches as $branch) {
				if (!isset($this -> branchNamesToIds[$branch['name']])) {
					$this -> branchNamesToIds[$branch['name']] = array($branch['branch_ID']);
				} else {
					$this -> branchNamesToIds[$branch['name']][] = $branch['branch_ID'];
				}
			}
		}
		return $this -> branchNamesToIds[$branch_name];
	}

	/*
	 * Add a new branch to the cached list
	 */
	protected function setBranchByName($branch_name, $branch_ID) {
		if (!isset($this -> branchNamesToIds[$branch_name])) {
			$this -> branchNamesToIds[$branch_name] = array($branch_ID);
		} else {
			$this -> branchNamesToIds[$branch_name][] = $branch_ID;
		}

	}
	/*
	 * Get id of father branch based on a branch name:
	 * - if no name defined - no father (root)
	 * - if name defined and exists - get first branch id with that name
	 * - if name defined and not exists - create a branch with that name and return its id
	 */
	protected function getFatherBranchId($father_branch_name) {
		// If no father defined - root, else we may need to create the father first
		if ($father_branch_name != "") {
			$array_of_father_branch_ids	= $this -> getBranchByName($father_branch_name);
			if (empty($array_of_father_branch_ids)) {
				 $new_father_branch = EfrontBranch::createBranch(array("name" => $father_branch_name));
				 $father_branch_ID = $new_father_branch -> branch['branch_ID'];

				 $this -> setBranchByName($new_father_branch -> branch['name'], $father_branch_ID);
			} else {
				// TODO: we implicitly select the first branch with that name as father - multiple fathers are not supported
				$father_branch_ID = $array_of_father_branch_ids[0];
			}
		} else {
			$father_branch_ID   = 0;
		}

		return $father_branch_ID;
	}

	/*
	 * This function does not only return job ids but arrays "title" => "job_description_id", "branches_name"
	 */
	private $jobNamesToIds = false;
	protected function getJobByName($job_title, $branch_name = false) {
		if (!$this -> jobNamesToIds) {
			$jobs = EfrontJob::getAllJobs();
			foreach($jobs as $job) {
				if (!isset($this -> jobNamesToIds[$job['description']])) {
					$this -> jobNamesToIds[$job['description']] = array(array("job_description_ID" => $job['job_description_ID'], "branches_name" => $job['name']));
				} else {
					$this -> jobNamesToIds[$job['description']][] = array("job_description_ID" => $job['job_description_ID'], "branches_name" => $job['name']);
				}
			}
		}

		$ids_to_return = array();
		$jobs_with_this_description = $this -> jobNamesToIds[$job_title];
		foreach ($jobs_with_this_description as $jobInfo) {
			if (!$branch_name || $jobInfo['branches_name'] == $branch_name) {
				$ids_to_return[] = $jobInfo['job_description_ID'];
			}
		}

		return $ids_to_return;
	}

	private $skillNamesToIds = false;
	protected function getSkillByName($skill_title) {
		if (!$this -> skillNamesToIds) {
			$skills = EfrontSkill::getAllSkills();
			foreach($skills as $skill) {
				if (!isset($this -> skillNamesToIds[$skill['description']])) {
					$this -> skillNamesToIds[$skill['description']] = array($skill['skill_ID']);
				} else {
					$this -> skillNamesToIds[$skill['description']][] = $skill['skill_ID'];
				}
			}
		}
		return $this -> skillNamesToIds[$skill_title];
	}

	private $skillCategoriesToIds = false;
	protected function getSkillCategoryByName($skill_category) {
		if (!$this -> skillCategoriesToIds) {
			$skillCategories = EfrontSkill::getAllSkillsCategories();
			foreach($skillCategories as $skill) {
				if (!isset($this -> skillCategoriesToIds[$skill['description']])) {
					$this -> skillCategoriesToIds[$skill['description']] = array($skill['skill_ID']);
				} else {
					$this -> skillCategoriesToIds[$skill['description']][] = $skill['skill_ID'];
				}
			}

		}
		return $this -> skillCategoriesToIds[$skill_category];
	}

	/*
	 * Add a new skill category to the cached list
	 */
	protected function setSkillCategoryByName($skill_category, $categories_ID) {
		if (!isset($this -> skillCategoriesToIds[$skill_category])) {
			$this -> skillCategoriesToIds[$skill_category] = array($categories_ID);
		} else {
			$this -> skillCategoriesToIds[$skill_category][] = $categories_ID;
		}

	}

	#cpp#endif
	/*
	 * Convert dates of the form dd/mm/yy to timestamps
	 */
    protected function createTimestampFromDate($date_field) {

        // date of event if existing, else current time
        if ($date_field != "") {
        	$date_field = trim($date_field);
        	// Assuming dd/mm/yy or dd-mm-yy
            $dateParts = explode("/", $date_field);
            if (sizeof($dateParts) == 1) {
            	$dateParts = explode("-", $date_field);
            }

            if ($this -> options['date_format'] == "MM/DD/YYYY") {
            	$timestamp = mktime(0,0,0,$dateParts[0],$dateParts[1],$dateParts[2]);
            } else if ($this -> options['date_format'] == "YYYY/MM/DD") {
            	$timestamp = mktime(0,0,0,$dateParts[2],$dateParts[0],$dateParts[1]);
            } else {
            	$timestamp = mktime(0,0,0,$dateParts[1],$dateParts[0],$dateParts[2]);
            }

            return $timestamp;
        } else {
        	return "";
        }
    }


	/*
	 * Create the mappings between csv columns and db attributes
	 */
	public static function getTypes($type) {

		switch($type) {
			case "users":
				$users_info = array("users_login"		=> "login",
								   "password"			=> "password",
								   "users_email"		=> "email",
								   "language"			=> "languages_NAME",
								   "users_name"			=> "name",
								   "users_surname"		=> "surname",
								   "active"				=> "active",
								   "user_type"			=> "user_type",
								   "registration_date"	=> "timestamp",
								   "timezone"			=> "timezone",
								   "archive" 			=> "archive");
				if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
					if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
						$user_profile = eF_getTableData("user_profile", "name", "active=1 AND type <> 'branchinfo' AND type <> 'groupinfo'");    //Get admin-defined form fields for user registration
					} else { #cpp#else
						$user_profile = eF_getTableData("user_profile", "name", "active=1");    //Get admin-defined form fields for user registration
					} #cpp#endif

					foreach($user_profile as $custom_field) {
						$users_info[$custom_field['name']] = $custom_field['name'];
					}
				} #cpp#endif

				return $users_info;
			#cpp#ifdef ENTERPRISE
			case "employees":
				$hcdArray = array('wage','hired_on','left_on' ,'address' ,'city'    ,'country' ,'father'  ,'homephone','mobilephone','sex','birthday','birthplace'              ,'birthcountry','mother_tongue'           ,'nationality' ,'company_internal_phone'  ,'office'      ,'doy'         ,'afm'         ,'police_id_number'        ,'driving_licence'         ,'work_permission_data'    ,'national_service_completed','employement_type'        ,'bank'        ,'bank_account','marital_status'          ,          'transport'   ,           'way_of_working');

				$employees_info = array_combine($hcdArray, $hcdArray);

				// Time values need to have the suffix _date to be recognized
//				unset($employees_info['hired_on']);
//				unset($employees_info['left_on']);
//				$employees_info['hired_date'] = "hired_on";
//				$employees_info['left_date'] = "left_on";
				$employees_info["users_login"] = "users_login";

				return $employees_info;

			case "branches":
				return array("branch_name"		=> "name",
							"branch_address"	=> "address",
							"branch_city"		=> "city",
							"branch_country"	=> "country",
							"branch_telephone"	=> "telephone",
							"branch_email"		=> "email",
							"father_branch_name"=> "father_branch_name");

			case "job_descriptions":
				return array("job_title"			=> "description",
							"job_branch"			=> "branch_name",
							"job_description"		=> "job_role_description",
							"job_required_employees"=> "employees_needed");

			case "skills":
				return array("skill"			=> "description",
							"skill_category"	=> "skill_category");

			case "users_to_jobs":
				require_once $path."module_hcd_tools.php";
				return array("users_login"		=> "users_login",
							 "job_title"		=> "description",
							 "job_branch"		=> "branch_name",
							 "supervisor"  		=> "supervisor");

			case "users_to_skills":
				return array("users_login"			=> "users_login",
							 "skill"				=> "description",
							 "skill_specification" 	=> "specification");

			#cpp#endif
			case "users_to_courses":
				return array("users_login"		=> "users_login",
						   "courses_name"		=> "course_name",
						   "course_start_date"	=> "from_timestamp",
						   "course_user_type"	=> "user_type",
						   "course_completed"	=> "completed",
						   "course_comments"	=> "comments",
						   "course_score"		=> "score",
						   "course_active"		=> "active",
						   "course_end_date"	=> "to_timestamp");
			case "users_to_lessons":
				return array("users_login"		=> "users_login",
				"lessons_name"		=> "lesson_name",
				"lesson_user_type"	=> "user_type",
				"lesson_completed"	=> "completed",
				"lesson_comments"	=> "comments",
				"lesson_score"		=> "score",
				"lesson_active"		=> "active",
				"lesson_end_date"	=> "to_timestamp");
				
				
			case "users_to_groups":
				return array("users_login"	=> "users_login",
							 "group_name"	=> "groups.name");



		}

	}


    /*
     * Get array of fields that are mandatory to be defined for a successfull import according to the type of import
     */
	public static function getMandatoryFields($type) {
		switch($type) {
			case "users":
				return array("login" => "users_login");

			case "users_to_courses":
				return array("users_login" => "users_login",
							 "course_name"=> "courses_name");
			case "users_to_lessons":
				return array("users_login" => "users_login",
							 "lesson_name"=> "lessons_name");
			case "users_to_groups":
				return array("users_login" => "users_login",
							 "groups.name" => "group_name");

			#cpp#ifdef ENTERPRISE
			case "employees":
				return array("users_login" => "users_login");

			case "branches":
				return array("name" 				=> "branch_name",
							 "father_branch_name"	=> "father_branch_name");

			case "job_descriptions":
				return array("description"	=> "job_title");

			case "skills":
				return array("description" 		=> "skill",
							 "skill_category"	=> "skill_category");

			case "users_to_jobs":
				return array("users_login"		=> "users_login",
							 "description" 		=> "job_title",
							 "branch_name" 		=> "job_branch");

			case "users_to_skills":
				return array("users_login"	=> "users_login",
							 "description"	=> "skill");

			#cpp#endif

		}
	}

	public static function getOptionalFields($type) {
		$all = EfrontImport::getTypes($type);
		if ($type == "users" && G_VERSIONTYPE == "enterprise") { #cpp#ifdef ENTEPRRISE
			$all_employee = EfrontImport::getTypes("employees");
			$all = array_merge($all, $all_employee);
		} #cpp#endif
		$mandatory 	= EfrontImport::getMandatoryFields($type);

		foreach ($mandatory as $type_name => $column) {
			unset($all[$column]);
		}
		return array_keys($all);
	}

}



/****************************************************
 * Class used to import data from csv files
 *
 */
class EfrontImportCsv extends EfrontImport
{
	/*
	 * The separator between the file's fields
	 */
	private $separator = false;

	/*
	 * Array containing metadata about the imported data type (db attribute names, db tables, import-file accepted column names)
	 */
	protected $types = false;

	/*
	 * Number of lines of the imported file
	 */
	protected $lines = false;

	/*
	 * Array with the mappings between db fields and columns
	 */
	protected $mappings = array();

	/*
	 * Used for initialization of data (to contain all fields)
	 */
	protected $empty_data = false;
	/*
	 * Find the separator - either "," or ";"
	 */
	protected function getSeparator() {
		if (!$this -> separator) {
			$this -> separator = ",";
			$test_line = explode($this -> separator, $this -> fileContents[0]);

			if (sizeof($test_line) > 1) {
				return $this -> separator;
			}

			$this -> separator = ";";
			$test_line = explode($this -> separator, $this -> fileContents[0]);
			if (sizeof($test_line) > 1) {
				return $this -> separator;
			}
			$this -> separator = false;
		}
		return $this -> separator;
	}

	/*
	 * Get empty data - used for caching of initialization array
	 */
	protected function getEmptyData() {
		if (!$this -> empty_data) {
			$this -> empty_data = array();
			foreach ($this -> types as $key) {
				$this -> empty_data[$key]  = "";
			}
			unset($this -> empty_data['user_type']);	// the default value should never be set to ""
			unset($this -> empty_data['password']);	// the default value should never be set to ""
		}

		return $this -> empty_data;
	}

	/*
	 * Split a line to its different strings as they are determined by the separator
	 */
	protected function explodeBySeparator($line) {

		if ($this -> separator) {
			return str_getcsv($this -> fileContents[$line], $this -> separator);
		} else {
			return $this -> fileContents[$line];
		}
	}
	/*
	 * Find the header line - the first non zero line of the csv that contains at least one of the import $type's column headers
	 * @param: the line of the header
	 */
	protected function parseHeaderLine(&$headerLine) {
		$this -> mappings = array();
		$this -> separator = $this -> getSeparator();	
		$legitimate_column_names = array_keys($this -> types);
		
		//pr($legitimate_column_names);
		$found_header = false;	
		for ($line = 0; $line < $this -> lines; ++$line) {

			$candidate_header = $this -> explodeBySeparator($line);			
			$size_of_header = sizeof($candidate_header);

			for ($header_record = 0; $header_record < $size_of_header; ++$header_record) {
				$candidate_header[$header_record] = trim($candidate_header[$header_record], "\"\r\n ");
				if ($candidate_header[$header_record] != "" && in_array($candidate_header[$header_record], $legitimate_column_names)) {
					$this -> mappings[$this -> types[$candidate_header[$header_record]]] = $header_record;
					$found_header = true;
				}
			}

			if ($found_header) {
				$headerLine = $line;
				return $this -> mappings;
			}
		}
		return false;
	}

	/*
	 * Utility function to initialize the $log array
	 */
	protected function clearLog() {
		$this -> log = array();
		$this -> log["success"] = array();
		$this -> log["failure"] = array();
	}


	protected function clear() {
		$this -> clearLog();
		$this -> mappings = array();
		$this -> empty_data = false;
		$this -> types = array();
	}
	/*
	 * Get existence exception and compare it against the "already exists" exception of for each different import type
	 */
	protected function isAlreadyExistsException($exception_code, $type) {
		switch ($type) {
			case "users":
				if ($exception_code == EfrontUserException::USER_EXISTS) { return true; }
				break;
			#cpp#ifdef ENTERPRISE
			case "branches":
				if ($exception_code == EfrontBranchException::BRANCH_EXISTS) { return true; }
				break;
			case "job_descriptions":
				if ($exception_code == EfrontJobException :: JOB_ALREADY_EXISTS) {return true; }
				break;
			case "skills":
				if ($exception_code == EfrontSkillException::SKILL_EXISTS) { return true; }
				break;
			#cpp#endif
			default:
				return false;
		}
		return false;
	}


	protected function cleanUpEmptyValues(&$data) {
		foreach ($data as $key => $info) {
			if ($info == "") {
				unset($data[$key]);
			}
		}

	}

	/*
	 * Update the data of an existing record
	 */
	protected function updateExistingData($line, $type, $data) {
		$this -> cleanUpEmptyValues($data);
		try {
			switch($type) {
				case "users":
					if (isset($data['password']) && $data['password'] != "" && $data['password'] != "ldap") {
						$data['password'] = EfrontUser::createPassword($data['password']);
					}
					eF_updateTableData("users", $data, "login='".$data['login']."'"); $this -> log["success"][] = _LINE . " $line: " . _REPLACEDUSER . " " . $data['login'];
					EfrontCache::getInstance()->deleteCache('usernames');
					break;
				case "users_to_courses":
					$where  = "users_login='".$data['users_login']."' AND courses_ID = " . $data['courses_ID'];
					EfrontCourse::persistCourseUsers($data, $where, $data['courses_ID'], $data['users_login']);

					$this -> log["success"][] = _LINE . " $line: " . _REPLACEDEXISTINGASSIGNMENT;
					break;
				case "users_to_lessons":
					eF_updateTableData("users_to_lessons", $data, "users_login='".$data['users_login']."' AND lessons_ID = " . $data['lessons_ID']);
				
					$this -> log["success"][] = _LINE . " $line: " . _REPLACEDEXISTINGASSIGNMENT;
					break;
							
				case "users_to_groups":
					break;

				#cpp#ifdef ENTERPRISE
				case "employees":
					eF_updateTableData("module_hcd_employees", $data, "users_login='".$data['users_login']."'"); $this -> log["success"][] = _LINE . " $line: " . _REPLACEDUSER . " " . $data['users_login'];
					break;

				case "branches":
					eF_updateTableData("module_hcd_branch", $data, "branch_ID ='".$data['branch_ID']."'"); $this -> log["success"][] = _LINE . " $line: " . _REPLACEDEXISTINGBRANCH . " " . $data['name'];
					break;

				case "job_descriptions":

					if ($data['branch_ID'] != "all") {
						$branch_condition = " AND branch_ID = " . $data['branch_ID'];
					}
					eF_updateTableData("module_hcd_job_description", $data, "description ='".$data['job_description_ID']."' " . $branch_condition); $this -> log["success"][] = _LINE . " $line: " . _REPLACEDEXISTINGJOB . " " . $data['description'];
					break;

				case "skills":
					eF_updateTableData("module_hcd_skills", $data, "skill_ID ='".$data['skill_ID']."'"); $this -> log["success"][] = _LINE . " $line: " . _REPLACEDEXISTINGSKILL . " " . $data['description'];
					break;

				case "users_to_jobs":
					// Done in importData to avoid re-creating the same objects
				case "users_to_skills":
					// Done automatically in importData by $skill->assignToUser
					break;

				#cpp#endif

			}
		} catch (Exception $e) {
			$this -> log["failure"][] = _LINE . " $line: " . $e -> getMessage();
		}
	}


	protected function importDataMultiple($type, $data) {
		try {

			switch($type) {
				case "users_to_groups":
					foreach ($data as $value) {
						$groups_ID  = current($this -> getGroupByName($value['groups.name']));
						$groups[$groups_ID][] = $value['users_login'];
					}		
					foreach ($groups as $id => $groupUsers) {
						try {
							$group = new EfrontGroup($id);
							$this -> log["success"][] = _NEWGROUPASSIGNMENT . " " . $group -> group['name'];
							$group -> addUsers($groupUsers);
						} catch (Exception $e) {
							$this -> log["failure"][] = _LINE . " ".($key+2).": " . $e -> getMessage();// ." ". str_replace("\n", "<BR>", $e->getTraceAsString());
						}
					}
					break;
				case "users":
					$existingUsers = eF_getTableDataFlat("users", "login, active, archive");
					$roles = EfrontUser::getRoles();
					foreach (EfrontUser::getRoles(true) as $key => $value) {
						$rolesTypes[$key] = mb_strtolower($value);
					}					
					$languages = EfrontSystem :: getLanguages();	
					$addedUsers    = array();		
					
					foreach ($data as $key => $value) {
						try {
							$newUser = EfrontUser::createUser($value, $existingUsers, false);
							$existingUsers['login'][]   = $newUser -> user['login'];
							$existingUsers['active'][]  = $newUser -> user['active'];
							$existingUsers['archive'][] = $newUser -> user['archive'];
							$addedUsers[] = $newUser -> user['login'];
							$this -> log["success"][] = _IMPORTEDUSER . " " . $newUser -> user['login'];
						} catch (Exception $e) {						
							if ($this -> options['replace_existing']) {		
								if ($this -> isAlreadyExistsException($e->getCode(), $type)) {					
									if (!in_array($value['login'], $existingUsers['login'], true)) {//For case-insensitive matches
										foreach ($existingUsers['login'] as $login) {
											if (mb_strtolower($value['login']) == mb_strtolower($login)) {
												$value['login'] = $login;
											}
										}
									} 

									if (!isset($value['user_type'])) {	
										$value['user_type'] = 'student';
									} else {
										if (in_array(mb_strtolower($value['user_type']), $roles)) {
											$value['user_type'] = mb_strtolower($value['user_type']);
										} else if ($k=array_search(mb_strtolower($value['user_type']), $rolesTypes)) {
											$value['user_types_ID'] = $k;
											$value['user_type'] = $roles[$k];
										} else {
											$value['user_type'] = 'student';
										}
									}
									if (!in_array($value['user_type'], EFrontUser::$basicUserTypes)) {
										$value['user_type'] = 'student';
										$value['user_types_ID'] = 0;
									}	
									if ($value['languages_NAME'] == "") {
										unset($value['languages_NAME']);
									} elseif (in_array($value['languages_NAME'], array_keys($languages)) === false) {
										$value['languages_NAME'] = $GLOBALS['configuration']['default_language'];
									}
										
									$this -> updateExistingData($key+2, $type, $value);
								} else {
									$this -> log["failure"][] = _LINE . " ".($key+2).": " . $e -> getMessage();// ." ". str_replace("\n", "<BR>", $e->getTraceAsString());
								}
							} else {
								$this -> log["failure"][] = _LINE . " ".($key+2).": " . $e -> getMessage();// ." ". str_replace("\n", "<BR>", $e->getTraceAsString());
							}
						}
					}
					$defaultGroup = eF_getTableData("groups", "id", "is_default = 1 AND active = 1");
					if (!empty($defaultGroup) && !empty($addedUsers)) {
						$defaultGroup = new EfrontGroup($defaultGroup[0]['id']);
						$defaultGroup -> addUsers($addedUsers);
					}

					break;
				case "users_to_jobs":

					$jobDescriptions = $userJobs = $userBranchesAssigned = $userBranchesUnassigned = array();
					$result = eF_getTableData("module_hcd_job_description", "job_description_ID, branch_ID, description");
					foreach ($result as $value) {
						$jobDescriptions[$value['job_description_ID']] = $value;
					}
					$result = eF_getTableData("module_hcd_employee_has_job_description", "*");
					foreach ($result as $value) {
						$userJobs[$value['users_login']][$value['job_description_ID']] = $value['job_description_ID'];
					}
					$result = eF_getTableData("module_hcd_employee_works_at_branch", "*");
					foreach ($result as $value) {
						if ($value['assigned']) {
							$userBranchesAssigned[$value['users_login']][$value['branch_ID']] = $value;
						} else {
							$userBranchesUnassigned[$value['users_login']][$value['branch_ID']] = $value;
						}
					}

					$allBranches = eF_getTableData("module_hcd_branch", "branch_ID, father_branch_ID", "");

					$addedJobs = $addedBranches = array();
					foreach ($data as $key => $value) {
						try {
							if (!$value['description']) {
								throw new EfrontJobException(_MISSING_JOB_DESCRIPTION, EfrontJobException::MISSING_JOB_DESCRIPTION);
							}

							$branchId = $this -> getBranchByName($value['branch_name']);	//Executes only once
							if ($branchId[0]) {
								if (sizeof($branchId) == 1) {
									$branchId = $branchId[0];
								} else {
									throw new EfrontBranchException(_BRANCHNAMEAMBIGUOUS.': '.$value['branch_name'], EfrontBranchException :: BRANCH_AMBIGUOUS);
								}
							} else {
								throw new EfrontBranchException(_BRANCHDOESNOTEXIST.': '.$value['branch_name'], EfrontBranchException::BRANCH_NOT_EXISTS);
							}

							$jobId = false;
							foreach ($jobDescriptions as $job) {
								if ($job['description'] == $value['description'] && $job['branch_ID'] == $branchId) {
									$jobId = $job['job_description_ID'];
								}
							}
							if (!$jobId) {
								$jobId = eF_insertTableData("module_hcd_job_description", array('description' => $value['description'], 'branch_ID' => $branchId));
								$jobDescriptions[$jobId] = array('job_description_ID' => $jobId, 'description' => $value['description'], 'branch_ID' => $branchId);
							}

							$user = EfrontUserFactory::factory($value["users_login"]);
							$value['users_login'] = $user -> user['login'];

							if (isset($userJobs[$value['users_login']]) && $this -> options['replace_assignments']) {
								$unset = false;
								foreach ($userJobs[$value['users_login']] as $key => $v) {
									if (!isset($addedJobs[$v][$value['users_login']])) {
										$user->aspects['hcd']->removeJob($v);
										unset($userJobs[$value['users_login']][$v]);
										$unset = true;
									}
								}
								if ($unset) {
									unset($userBranchesAssigned[$value['users_login']]);
								}
							}

							if (isset($userJobs[$value['users_login']][$jobId]) && $this -> options['replace_existing']) {
								eF_deleteTableData("module_hcd_employee_has_job_description", "users_login='".$value['users_login']."' AND job_description_ID ='".$jobId."'");
								unset($userJobs[$value['users_login']][$jobId]);
							}

							// Check if this job description is already assigned
							if (!isset($userJobs[$value['users_login']][$jobId])) {
								if (!isset($userBranchesAssigned[$value['users_login']][$branchId])) {
									// Write to the database the new branch assignment: employee to branch (if such an assignment is not already true)
									if (isset($userBranchesUnassigned[$value['users_login']][$branchId])) {
										eF_updateTableData("module_hcd_employee_works_at_branch", array("assigned" => 1), "users_login='".$value['users_login']."' and branch_ID=$branchId");
										unset($userBranchesUnassigned[$value['users_login']][$branchId]);
									} else {
										$fields = array('users_login' => $value['users_login'],
														'supervisor'  => $value['supervisor'],
														'assigned' 	  => '1',
														'branch_ID'   => $branchId);

										eF_insertTableData("module_hcd_employee_works_at_branch", $fields);

										if ($value['supervisor']) {
											//Iterate through sub branches
											foreach (eF_subBranches($branchId, $allBranches) as $subBranchId) {
												//If this subranch is not associated with the user, associate it
												if (!isset($userBranchesAssigned[$value['users_login']][$subBranchId]) && !isset($userBranchesUnassigned[$value['users_login']][$subBranchId])) {
													$fields = array('users_login' => $value['users_login'],
																	'supervisor'  => 1,
																	'assigned' 	  => '0',
																	'branch_ID'   => $subBranchId);
													eF_insertTableData("module_hcd_employee_works_at_branch", $fields);
													$userBranchesUnassigned[$value['users_login']][$branchId] = array('branch_ID' => $branchId, 'supervisor' => $value['supervisor'], 'assigned' => 0);
												} elseif (isset($userBranchesAssigned[$value['users_login']][$subBranchId]) && $userBranchesAssigned[$value['users_login']][$subBranchId]['supervisor'] == 0) {
													eF_updateTableData("module_hcd_employee_works_at_branch", array("supervisor" => 1), "users_login='".$value['users_login']."' and branch_ID=$subBranchId");
													$userBranchesAssigned[$value['users_login']][$subBranchId]['supervisor'] = 1;
												} elseif (isset($userBranchesUnassigned[$value['users_login']][$subBranchId]) && $userBranchesUnassigned[$value['users_login']][$subBranchId]['supervisor'] == 0) {
													eF_updateTableData("module_hcd_employee_works_at_branch", array("supervisor" => 1), "users_login='".$value['users_login']."' and branch_ID=$subBranchId");
													$userBranchesUnassigned[$value['users_login']][$subBranchId]['supervisor'] = 1;
												}
											}
										}

									}
									$userBranchesAssigned[$value['users_login']][$branchId] = array('branch_ID' => $branchId, 'supervisor' => $value['supervisor'], 'assigned' => 1);
									$addedBranches[$branchId][$value['users_login']] = $value['users_login'];
								} elseif (!$userBranchesAssigned[$value['users_login']][$branchId]['supervisor'] && $value['supervisor']) {
									eF_updateTableData("module_hcd_employee_works_at_branch", array("supervisor" => 1), "users_login='".$value['users_login']."' and branch_ID=$branchId");
									//Iterate through sub branches
									foreach (eF_subBranches($branchId, $allBranches) as $subBranchId) {
										//If this subranch is not associated with the user, associate it
										if (!isset($userBranchesAssigned[$value['users_login']][$subBranchId]) && !isset($userBranchesUnassigned[$value['users_login']][$subBranchId])) {
											$fields = array('users_login' => $value['users_login'],
															'supervisor'  => 1,
															'assigned' 	  => '0',
															'branch_ID'   => $subBranchId);
											eF_insertTableData("module_hcd_employee_works_at_branch", $fields);
											$userBranchesUnassigned[$value['users_login']][$branchId] = array('branch_ID' => $branchId, 'supervisor' => $value['supervisor'], 'assigned' => 0);
										} elseif (isset($userBranchesAssigned[$value['users_login']][$subBranchId]) && $userBranchesAssigned[$value['users_login']][$subBranchId]['supervisor'] == 0) {
											eF_updateTableData("module_hcd_employee_works_at_branch", array("supervisor" => 1), "users_login='".$value['users_login']."' and branch_ID=$subBranchId");
											$userBranchesAssigned[$value['users_login']][$subBranchId]['supervisor'] = 1;
										} elseif (isset($userBranchesUnassigned[$value['users_login']][$subBranchId]) && $userBranchesUnassigned[$value['users_login']][$subBranchId]['supervisor'] == 0) {
											eF_updateTableData("module_hcd_employee_works_at_branch", array("supervisor" => 1), "users_login='".$value['users_login']."' and branch_ID=$subBranchId");
											$userBranchesUnassigned[$value['users_login']][$subBranchId]['supervisor'] = 1;
										}
									}

								} elseif ($userBranchesAssigned[$value['users_login']][$branchId]['supervisor'] && !$value['supervisor']) {
									eF_updateTableData("module_hcd_employee_works_at_branch", array("supervisor" => 0), "users_login='".$value['users_login']."' and branch_ID=$branchId");
								}

								// Write to database the new job assignment: employee to job description
								$fields = array('users_login' 		 => $value['users_login'],
												'job_description_ID' => $jobId);
								eF_insertTableData("module_hcd_employee_has_job_description", $fields);
								$userJobs[$value['users_login']][$jobId]  = $jobId;
								$addedJobs[$jobId][$value['users_login']] = $value['users_login'];

/*
									if ($event_info) {
										EfrontEvent::triggerEvent(array("type" => EfrontEvent::HCD_NEW_JOB_ASSIGNMENT, "users_LOGIN" => $this -> login, "lessons_ID" => $branchID, "lessons_name" => $bname[0]['name'], "entity_ID" => $jobID, "entity_name" => $job_description, "timestamp" => $event_info['timestamp'], "explicitly_selected" => $event_info['manager']));
									} else {
										EfrontEvent::triggerEvent(array("type" => EfrontEvent::HCD_NEW_JOB_ASSIGNMENT, "users_LOGIN" => $this -> login, "lessons_ID" => $branchID, "lessons_name" => $bname[0]['name'], "entity_ID" => $jobID, "entity_name" => $job_description));
									}
*/

							} else {
								throw new EfrontUserException(_JOBALREADYASSIGNED . ": ".$value['users_login'], EfrontUserException :: WRONG_INPUT_TYPE);
							}

							$this -> log["success"][] = _LINE." ".($key+2)." : "._NEWJOBASSIGNMENT . " " . $value["users_login"] . " - (" .$value['branch_name'] . " - " .$value['description'] . ") ";
						} catch (Exception $e) {
							$this -> log["failure"][] = _LINE." ".($key+2)." : ".$e -> getMessage().' ('.$e -> getCode().')';
						}
					}

					$courseAssignmentsToUsers = $lessonAssignmentsToUsers = array();
					$result = eF_getTableData("module_hcd_course_to_job_description", "*");
					foreach ($result as $value) {
						foreach ($addedJobs[$value['job_description_ID']] as $user) {
							$courseAssignmentsToUsers[$value['courses_ID']][] = $user;
						}
					}
					$result = eF_getTableData("module_hcd_lesson_to_job_description", "*");
					foreach ($result as $value) {
						foreach ($addedJobs[$value['job_description_ID']] as $user) {
							$lessonAssignmentsToUsers[$value['lessons_ID']][] = $user;
						}
					}
					$result = eF_getTableData("module_hcd_course_to_branch", "*");
					foreach ($result as $value) {
						foreach ($addedBranches[$value['branches_ID']] as $user) {
							$courseAssignmentsToUsers[$value['courses_ID']][] = $user;
						}
					}
					foreach ($courseAssignmentsToUsers as $courseId => $users) {
						$course = new EfrontCourse($courseId);
						$course -> addUsers($users);
					}
					foreach ($lessonAssignmentsToUsers as $lessonId => $users) {
						$course = new EfrontLesson($lessonId);
						$course -> addUsers($users);
					}
					break;
			}
		} catch (Exception $e) {
			$this -> log["failure"][] = $e -> getMessage().' ('.$e -> getCode().')';// ." ". str_replace("\n", "<BR>", $e->getTraceAsString());
		}
	}


	/*
	 * Use eFront classes according to the type of import to store the data used
	 * @param line: the line of the imported file
	 * @param type: the import type
	 * @param type: the data of this line, formatted to be put directly into the eFront db
	 */
	//TODO: this should be moved to the EfrontImport base class - and be used by all - the $line should probably leave though
	protected function importData($line, $type, $data) {
//pr($line);exit;		
		try {
			switch($type) {
				case "users":
					$newUser = EfrontUser::createUser($data);
					$this -> log["success"][] = _LINE . " $line: " . _IMPORTEDUSER . " " . $newUser -> login;
					break;

				case "users_to_courses":
					//Check if a user exists and whether it has the same case

					$userFound = false;
					if (!in_array($data['users_login'], $this->allUserLogins)) {				//For case-insensitive matches
						
						foreach ($this->allUserLogins as $login) {
							if (mb_strtolower($data['users_login']) == mb_strtolower($login)) {
								$data['users_login'] = $login;
								$userFound = true;
							} 
						}
					} else {
						$userFound = true;
					}

					if ($userFound) {
						$courses_name = trim($data['course_name']);
						$courses_ID = $this -> getCourseByName($courses_name);
						unset($data['course_name']);
						if ($courses_ID) {

							foreach($courses_ID as $course_ID) {
								$data['courses_ID'] = $course_ID;
								$course = new EfrontCourse($course_ID);

								//$course -> addUsers($data['users_login'], (isset($data['user_type']) && $data['user_type']?$data['user_type']:"student"));
								$course -> addUsers($data['users_login'], (isset($data['user_type'])?$data['user_type']:"student"));
								$where  = "users_login = '" .$data['users_login']. "' AND courses_ID = " . $data['courses_ID'];
								$data['completed'] ? $data['completed'] = 1 : $data['completed'] = 0;

								EfrontCourse::persistCourseUsers($data, $where, $data['courses_ID'], $data['users_login']);
								if ($data['active']) {
									$course -> confirm($data['users_login']);
								} else {
									$course -> unconfirm($data['users_login']);
								}

								$this -> log["success"][] = _LINE . " $line: " . _NEWCOURSEASSIGNMENT . " " . $courses_name . " - " . $data['users_login'];
							}
		
						} else if ($courses_name != "") {
							$course = EfrontCourse::createCourse(array("name" => $courses_name));
							$this -> log["success"][] = _LINE . " $line: " . _NEWCOURSE . " " . $courses_name;
							
							$course -> addUsers($data['users_login'], (isset($data['user_type'])?$data['user_type']:"student"));
							
							$courses_ID = $course -> course['id'];
							$this -> courseNamesToIds[$courses_name] = array($courses_ID);
							$where  = "users_login = '" .$data['users_login']. "' AND courses_ID = " . $courses_ID;
							EfrontCourse::persistCourseUsers($data, $where, $courses_ID, $data['users_login']);
							if ($data['active']) {
								$course -> confirm($data['users_login']);
							} else {
								$course -> unconfirm($data['users_login']);
							}
							
							$this -> log["success"][] = _LINE . " $line: " . _NEWCOURSEASSIGNMENT . " " . $courses_name . " - " . $data['users_login'];
						} else {
							$this -> log["failure"][] = _LINE . " $line: " . _COULDNOTFINDCOURSE . " " . $courses_name;
						}
					} else {
						$this -> log["failure"][] = _LINE . " $line: " . _USERDOESNOTEXIST. ": " . $data['users_login'];
					}
					break;
				case "users_to_lessons":
					//Check if a user exists and whether it has the same case
				
					$userFound = false;
					if (!in_array($data['users_login'], $this->allUserLogins)) {				//For case-insensitive matches
				
						foreach ($this->allUserLogins as $login) {
							if (mb_strtolower($data['users_login']) == mb_strtolower($login)) {
								$data['users_login'] = $login;
								$userFound = true;
							}
						}
					} else {
						$userFound = true;
					}
				
					if ($userFound) {
						$lessons_name = trim($data['lesson_name']);
						$lessons_ID = $this -> getLessonByName($lessons_name);
						unset($data['lesson_name']);
						if ($lessons_ID) {
				
							foreach($lessons_ID as $lesson_ID) {
								$data['lessons_ID'] = $lesson_ID;
								$lesson = new EfrontLesson($lesson_ID);
				
								if (!$lesson->lesson['course_only']) {
									$lesson -> addUsers($data['users_login'], (isset($data['user_type'])?$data['user_type']:"student"));
								}
								$data['completed'] ? $data['completed'] = 1 : $data['completed'] = 0;
								eF_updateTableData("users_to_lessons", $data, "users_login = '" .$data['users_login']. "' AND lessons_ID = " . $data['lessons_ID']);
								
								if (!$lesson->lesson['course_only']) {
									if ($data['active']) {
										$lesson -> confirm($data['users_login']);
									} else {
										$lesson -> unconfirm($data['users_login']);
									}
								}
								$this -> log["success"][] = _LINE . " $line: " . _NEWLESSONASSIGNMENT . " " . $lessons_name . " - " . $data['users_login'];
							}
				
						} else if ($lessons_name != "") {
							$lesson = EfrontLesson::createLesson(array("name" => $lessons_name, 'course_only' => false));
							$this -> log["success"][] = _LINE . " $line: " . _NEWLESSON . " " . $lessons_name;
								
							$lesson -> addUsers($data['users_login'], (isset($data['user_type'])?$data['user_type']:"student"));
								
							$lessons_ID = $lesson -> lesson['id'];
							$this -> lessonNamesToIds[$lessons_name] = array($lessons_ID);							
							eF_updateTableData("users_to_lessons", $data, "users_login = '" .$data['users_login']. "' AND lessons_ID = " . $lessons_ID);
							
							if ($data['active']) {
								$lesson -> confirm($data['users_login']);
							} else {
								$lesson -> unconfirm($data['users_login']);
							}
								
							$this -> log["success"][] = _LINE . " $line: " . _NEWLESSONASSIGNMENT . " " . $lessons_name . " - " . $data['users_login'];
						} else {
							$this -> log["failure"][] = _LINE . " $line: " . _COULDNOTFINDLESSON . " " . $lessons_name;
						}
					} else {
						$this -> log["failure"][] = _LINE . " $line: " . _USERDOESNOTEXIST. ": " . $data['users_login'];
					}
					break;					
				case "users_to_groups":
					//debug();
					$groups_ID = $this -> getGroupByName($data['groups.name']);
					$group_name = $data['groups.name'];
					unset($data['groups.name']);
					foreach($groups_ID as $group_ID) {
						$data['groups_ID'] = $group_ID;
						$group = new EfrontGroup($group_ID);
						$group -> addUsers(array($data['users_login']));
						$this -> log["success"][] = _LINE . " $line: " . _NEWGROUPASSIGNMENT . " " . $group_name . " - " . $data['users_login'];
					}
					break;
					//debug(false);

				#cpp#ifdef ENTERPRISE
				case "employees":
					$this -> cleanUpEmptyValues($data);
					// a bit customized here, based on the fact that employees are always created together AFTER users (so the object should exist)
					eF_updateTableData("module_hcd_employees", $data, "users_login='".$data['users_login']."'");
					break;

				case "branches":
					// If no father defined - root, else we may need to create the father first
					$data['father_branch_ID']   = $this -> getFatherBranchId($data['father_branch_name']);
					$father_name = $data['father_branch_name'];

					$branch_ID 	 = $this -> getBranchByName($data['name']);
					$branch_name = $data['name'];

					if (sizeof($branch_ID) > 0 && $data['name'] != $data['father_branch_name']) {

						//TODO
						unset($data['father_branch_name']);
						$data['branch_ID'] = $branch_ID[0];
			            throw new EfrontBranchException(_BRANCHALREADYEXISTS, EfrontBranchException :: BRANCH_EXISTS);
					} else {
						unset($data['father_branch_name']);
						$branch = EfrontBranch::createBranch($data);
						$this -> setBranchByName($branch_name, $branch -> branch['branch_ID']);
					}

					$this -> log["success"][] = _LINE . " $line: " . _NEWBRANCH . " " . $branch_name;
					break;

				case "job_descriptions":

					if ($data['branch_name'] == "") {
						$data['branch_ID'] = "all";	// this denotes to the createJob class to put the job in all branches
					} else {
						$data['branch_ID'] = $this -> getBranchByName($data['branch_name']);

						if (sizeof($data['branch_ID']) > 0) {
							//TODO: maybe different handling when multiple branches are found
							$data['branch_ID'] = $data['branch_ID'][0];
						} else {
							throw new EfrontJobException(_BRANCHDESIGNATEDFORTHISJOBDESCRIPTIONDOESNOTEXIST, EfrontJobException::BRANCH_NOT_EXISTS);
						}
					}
					unset($data['branch_name']);

					if ($data['description'] != "") {
						$job_ID = $this -> getJobByName($data['description']);
						if (sizeof($job_ID) > 0) {
							$data['job_description_ID'] = $job_ID[0];
				            throw new EfrontJobException(_JOBDESCRIPTIONEXISTSALREADY, EfrontJobException :: JOB_ALREADY_EXISTS);
						} else {
							EfrontJob::createJob($data);
							$this -> log["success"][] = _LINE . " $line: " . _NEWJOB . " " . $data['description'];
						}
					} else {
						$this -> log["failure"][] = _LINE . " $line: " . _NOTITLEPROVIDEDFORNEWJOB;
					}

					break;
				case "skills":
					if ($data['skill_category'] == "") {
						throw new EfrontSkillException(_MISSINGSKILLCATEGORY, EfrontSkillException :: INVALID_SKILL_CATEGORY);
					} else {
						$data['categories_ID'] = $this -> getSkillCategoryByName($data['skill_category']);

						if ($data['categories_ID'][0] != "") {
							$data['categories_ID'] = $data['categories_ID'][0];
						} else {
							// create skill category
							$data['categories_ID'] = eF_insertTableData("module_hcd_skill_categories", array('description'     => $data['skill_category']));
							$this -> setSkillCategoryByName($data['skill_category'], $data['categories_ID']);
						}
					}
					unset($data['skill_category']);

					$skill_ID 	 = $this -> getSkillByName($data['description']);

					if ($skill_ID) {
						//TODO: another double issue
						$data['skill_ID'] = $skill_ID[0];
						throw new EfrontSkillException(_SKILLALREADYEXISTS, EfrontSkillException :: SKILL_EXISTS);
					} else {
						EfrontSkill::createSkill($data);
						$this -> log["success"][] = _LINE . " $line: " . _NEWSKILL . " " . $data['description'];
					}
					break;
				case "users_to_jobs":
					// Get user
					$user = EfrontUserFactory::factory($data["users_login"]);

					// Get branch id
					$branch_ID 	 = $this -> getBranchByName($data['branch_name']);
					$branch_name = $data['branch_name'];
					if ($branch_ID[0] != "") {
						if (sizeof($branch_ID) == 1) {
							$branch_ID = $branch_ID[0];
						} else {
							throw new EfrontBranchException(_BRANCHNAMEAMBIGUOUS, EfrontBranchException :: BRANCH_AMBIGUOUS);
						}
					} else {
						throw new EfrontBranchException(_BRANCHDOESNOTEXIST, EfrontBranchException::BRANCH_NOT_EXISTS);
					}

					// Get job id
					$job_name = $data['description'];
					if ($job_name != "") {
						$new_job_description_ID = eF_getJobDescriptionId($job_name, $branch_ID);
					} else {
						throw new EfrontJobException(_MISSING_JOB_DESCRIPTION, EfrontJobException::MISSING_JOB_DESCRIPTION);
					}

					// Get hcd employee object
					if ($data['supervisor']) {
						$employee = new EfrontSupervisor(array("users_login" => $data["users_login"]));
						$position = 1;
					} else {
						$employee = new EfrontEmployee(array("users_login" => $data["users_login"]));
						$position = 0;
					}
					// Assign job

					try {
						$employee -> addJob ($user, $new_job_description_ID, $branch_ID, $position);
						$this -> log["success"][] = _LINE . " $line: " . _NEWJOBASSIGNMENT . " " . $data["users_login"] . " - (" .$branch_name . " - " .$job_name . ") ";
					} catch (Exception $e) {
						if ($this -> options['replace_existing']) {
							$employee -> removeJob($new_job_description_ID);
							$employee -> addJob ($user, $new_job_description_ID, $branch_ID, $position);
							$this -> log["success"][] = _LINE . " $line: " . _NEWJOBASSIGNMENT . " " . $data["users_login"] . " - (" .$branch_name . " - " .$job_name . ") ";
						}
					}

					break;

				case "users_to_skills":
					$skill_ID = $this -> getSkillByName($data['description']);
					$skill_name = $data['description'];
					if ($skill_ID[0] != "") {
						if (sizeof($skill_ID) == 1) {
							$skill_ID = $skill_ID[0];
						} else {
							throw new EfrontSkillException(_SKILLNAMEAMBIGUOUS, EfrontSkillException :: SKILL_AMBIGUOUS);
						}
					} else {
						throw new EfrontSkillException(_SKILLDOESNOTEXIST, EfrontSkillException::SKILL_NOT_EXISTS);
					}

					$skill = new EfrontSkill($skill_ID);
					$skill -> assignToEmployee($data['users_login'], $data['module_hcd_employee_has_skill.specification']);

					$this -> log["success"][] = _LINE . " $line: " . _NEWSKILLASSIGNMENT . " " . $data["users_login"] . " - " . $skill_name;
					break;
				#cpp#endif
			}
		} catch (Exception $e) {
			if ($this -> options['replace_existing']) {
				if ($this -> isAlreadyExistsException($e->getCode(), $type)) {
					$this -> updateExistingData($line, $type, $data);
				} else {
					$this -> log["failure"][] = _LINE . " $line: " . $e -> getMessage();// ." ". str_replace("\n", "<BR>", $e->getTraceAsString());
				}
			} else {
				$this -> log["failure"][] = _LINE . " $line: " . $e -> getMessage();// ." ". str_replace("\n", "<BR>", $e->getTraceAsString());
			}

		}

	}

	/*
	 * Check whether the file contains the columns that are necessary for this import type
	 */
	protected function checkImportEssentialField($type) {
		$mandatoryFields = EfrontImport::getMandatoryFields($type);

		$not_found = false;

		foreach ($mandatoryFields as $dbField => $columnName) {
			if (!isset($this -> mappings[$dbField])) {
				$not_found = true;
				break;
			}
		}

		if ($not_found) {
			$this -> log["failure"]["headerproblem"] = _HEADERDOESNOTINCLUDEESSENTIALCOLUMN . ": " . implode(",", $mandatoryFields);
			return false;
		} else {
			return true;
		}
	}

	/*
	 * Parse line data
	 */
	protected function parseDataLine($line) {

		$lineContents = $this -> explodeBySeparator($line);
		array_walk($lineContents, create_function('&$val', '$val = trim($val);')); 
		$data = $this -> getEmptyData();
		foreach ($this -> mappings as $dbAttribute => $fileInfo) {
			if (strpos($dbAttribute, "timestamp") === false && $dbAttribute != "hired_on" && $dbAttribute != "left_on" && !in_array($dbAttribute, $this->dateFields)) {
				$data[$dbAttribute] = trim($lineContents[$fileInfo], "\r\n");
			} else {
				$data[$dbAttribute] = $this -> createTimestampFromDate(trim($lineContents[$fileInfo], "\r\n"));
			}

		}

		//pr($data);pr(date("Y/m/d", $data['to_timestamp']));exit;

		return $data;
	}


	/*
	 * Main importing function
	 */
	public function import($type) {

		$this -> clear();		
		if ($this -> lines == "") {
			$this -> log["failure"]["missingheader"] = _NOHEADERROWISDEFINEDORHEADERROWNOTCOMPATIBLEWITHIMPORTTYPE;
		} else if ($this -> lines == 1) {
			$this -> log["failure"]["missingdata"] = _THEFILEAPPEARSEMPTYPERHAPSITISNOTFORMATTEDCORRECTLY;		
		} else {
			// Pairs of values <Csv column header> => <eFront DB field>

			$this -> types = EfrontImport::getTypes($type);	
			// Pairs of values <eFront DB field> => <import file column>
			$this -> mappings = $this -> parseHeaderLine($headerLine);	

			$result = eF_getTableDataFlat("user_profile", "name", "active=1 AND type ='date'");    //Get admin-defined form fields for user registration
			if (!empty($result)) {
				$this->dateFields = $result['name'];
			} else {
				$this->dateFields = array();
			}
			
			if ($this -> mappings) {
				
				if ($this -> checkImportEssentialField($type)) {
					if ($type == 'users_to_groups' || $type == 'users' || $type == 'users_to_jobs') {
						$data = array();
						for ($line = $headerLine+1; $line < $this -> lines; ++$line) {
							$data[] = $this -> parseDataLine($line);
						}
						$this -> importDataMultiple($type, $data);

					} else {
						$result = eF_getTableDataFlat("users", "login");
						$this->allUserLogins = $result['login']; 
						for ($line = $headerLine+1; $line < $this -> lines; ++$line) {
							$data = $this -> parseDataLine($line);
							$this -> importData($line+1, $type, $data);
						}
					}
				}

			} else {
				$this -> log["failure"]["missingheader"] = _NOHEADERROWISDEFINEDORHEADERROWNOTCOMPATIBLEWITHIMPORTTYPE;
			}
		}


		return $this -> log;
	}


	/*
	 * Set the memory and time limits for an import according to the number of lines to be imported
	 */
	protected function setLimits($factor = false) {
		if (!$factor) {
			$factor = $this->lines / 500;
		}

		if ($factor < 1) {
			return;
		}

		if ($factor > 20) {
			$factor = 20;
		}

		$maxmemory = 128 * $factor;
		$maxtime = 300 * $factor;

		//ini_set("memory_limit", max($maxmemory,$GLOBALS['configuration']['memory_limit'])  . "M");
        //ini_set("max_execution_time", max($maxtime, $GLOBALS['configuration']['max_execution_time']));
	}

	public function __construct($filename, $_options) {	
		$this -> fileContents = file_get_contents($filename);
		$this -> fileContents = explode("\n", trim($this -> fileContents));

		$this -> lines = sizeof($this -> fileContents);
		$this -> setLimits();
		$this -> options  = $_options;

	}

}




/**
 * Import Factory class
 *
 * This class is used as a factory for import objects
 * <br/>Example
 * <code>
 * $importer = EfrontImportFactory :: factory('csv', $file, $options);
 * $importer -> import();
 * $importer -> import('users');
 * </code>
 *
 * @package eFront
 * @version 3.6.1
 */
class EfrontImportFactory
{
    /**
     * Construct import object
     *
     * This function is used to construct an import object which can be
     * of any type: EfrontCsvImport
     *
     * <br/>Example :
     * <code>
     * $file = $filesystem -> uploadFile('upload_file');
     * $user = EfrontImportFactory :: factory('csv', $file, array("keep_duplicates" => 1);            //Use factory function to instantiate user object with login 'jdoe'
     * </code>
     *
     * @param string the type the importer: currently only 'csv' is supported $importerType
     * @param file $filename
     * @param options $various import options
     * @return EfrontImport an object of a class extending EfrontImport
     * @since 3.6.1
     * @access public
     * @static
     */
    public static function factory($importerType, $file, $options = false) {

    	if (!($file instanceof EfrontFile)) {
    		$file = new EfrontFile($file);
    	}

    	switch ($importerType) {
    		case 'csv' : $factory = new EfrontImportCsv($file['path'], $options); break;
    	}

        return $factory;
    }

}








// -----------------------------------------------------------------------------------------------------------------


/**
 * Abstract class for any Export class - serves as an interface for subsequent
 * developed exporters
 *
 * @package eFront
 * @abstract
 */
abstract class EfrontExport
{

	/**
	 * Various options like duplicates handling are stored in the options array
	 *
	 * @since 3.6.1
	 * @var array
	 * @access protected
	 */
	protected $options;

	/**
	 * The lines that should finally be exported are written in this array
	 *
	 * @since 3.6.1
	 * @var array
	 * @access protected
	 */
	protected $lines = array();

    /**
     * Export the data from the file following the designated options
     *
     * <br/>Example:
     * <code>
     * $exporter -> export(); //returns something like /var/www/efront/upload/admin/
     * $logMessages = $exporter -> getLogMessages();
     * </code>
     *
     * @return void
     * @since 3.6.1
     * @access public
     */
	public abstract function export($type);



	public static function getExportTypes() {
		$datatypes = array("users" 			  => _USERS,
						   "users_to_courses" => _USERSTOCOURSES);

		if (G_VERSIONTYPE == 'educational' || G_VERSIONTYPE == 'enterprise') { #cpp#ifndef COMMUNITY
			$datatypes["users_to_groups"] = _USERSTOGROUPS;
		} #cpp#endif


		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			$datatypes["branches"] = _BRANCHES;
			$datatypes["job_descriptions"] = _JOBDESCRIPTIONS;
			$datatypes["skills"] = _SKILLS;
			$datatypes["users_to_jobs"] = _USERSTOJOBS;
			$datatypes["users_to_skills"] = _USERSTOSKILLS;

		} #cpp#endif

		return $datatypes;
	}


	/*
	 * Create the mappings between csv columns and db attributes
	 */
	public static function getTypes($type) {

		switch($type) {
			case "users":
				$users_info = array("users_login"		=> "login",
								   "password"			=> "password",
								   "users_email"		=> "email",
								   "language"			=> "languages_NAME",
								   "users_name"			=> "name",
								   "users_surname"		=> "surname",
								   "active"				=> "active",
								   "user_type"			=> "user_type",
								   "registration_date"	=> "timestamp");
				if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
					if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
						$user_profile = eF_getTableData("user_profile", "name", "active=1 AND type <> 'branchinfo' AND type <> 'groupinfo'");    //Get admin-defined form fields for user registration
					} else { #cpp#else
						$user_profile = eF_getTableData("user_profile", "name", "active=1");    //Get admin-defined form fields for user registration
					} #cpp#endif

					foreach($user_profile as $custom_field) {
						$users_info[$custom_field['name']] = $custom_field['name'];
					}
				} #cpp#endif
				return $users_info;
			#cpp#ifdef ENTERPRISE
			case "employees":
				$hcdArray = array('wage','hired_on','left_on' ,'address' ,'city'    ,'country' ,'father'  ,'homephone','mobilephone','sex','birthday','birthplace'              ,'birthcountry','mother_tongue'           ,'nationality' ,'company_internal_phone'  ,'office'      ,'doy'         ,'afm'         ,'police_id_number'        ,'driving_licence'         ,'work_permission_data'    ,'national_service_completed','employement_type'        ,'bank'        ,'bank_account','marital_status'          ,          'transport'   ,           'way_of_working');
				$employees_info = array_combine($hcdArray, $hcdArray);

				// Time values need to have the suffix _date to be recognized
//				unset($employees_info['hired_on']);
//				unset($employees_info['left_on']);
//				$employees_info['hired_date'] = "hired_on";
//				$employees_info['left_date'] = "left_on";
//				$employees_info["users_login"] = "users_login";

				return $employees_info;

			case "branches":
				return array("branch_name"		=> "module_hcd_branch.name",
							"branch_address"	=> "module_hcd_branch.address",
							"branch_city"		=> "module_hcd_branch.city",
							"branch_country"	=> "module_hcd_branch.country",
							"branch_telephone"	=> "module_hcd_branch.telephone",
							"branch_email"		=> "module_hcd_branch.email",
							"father_branch_name"=> "father_branches.name as father_branch_name");

			case "job_descriptions":
				return array("job_title"			=> "module_hcd_job_description.description",
							"job_branch"			=> "module_hcd_branch.name",
							"job_description"		=> "module_hcd_job_description.job_role_description",
							"job_required_employees"=> "module_hcd_job_description.employees_needed");

			case "skills":
				return array("skill"			=> "module_hcd_skills.description",
							"skill_category"	=> "module_hcd_skill_categories.description as category_description");

			case "users_to_jobs":
				return array("users_login"		=> "module_hcd_employee_has_job_description.users_login",
							 "job_title"		=> "module_hcd_job_description.description",
							 "job_branch"		=> "module_hcd_branch.name",
							 "supervisor"  		=> "supervisor");
			case "users_to_skills":
				return array("users_login"			=> "users_login",
							 "skill"				=> "module_hcd_skills.description",
							 "skill_specification" 	=> "module_hcd_employee_has_skill.specification");

			#cpp#endif
			case "users_to_courses":
				return array("users_login"		=> "users_login",
						   "courses_name"		=> "courses.name",
						   "course_start_date"	=> "users_to_courses.from_timestamp",
						   "course_user_type"	=> "users_to_courses.user_type",
						   "course_completed"	=> "users_to_courses.completed",
						   "course_comments"	=> "users_to_courses.comments",
						   "course_score"		=> "users_to_courses.score",
						   "course_active"		=> "users_to_courses.active",
						   "course_end_date"	=> "users_to_courses.to_timestamp");

			case "users_to_groups":
				return array("users_login"	=> "users_login",
							 "group_name"	=> "groups.name");



		}

	}


	public function __construct($_options) {
		$this -> options  = $_options;
		if ($this -> options['date_format'] == "MM/DD/YYYY") {
			$this -> options['date_new_format'] = "m/d/Y";
		} else if ($this -> options['date_format'] == "YYYY/MM/DD") {
			$this -> options['date_new_format'] = "Y/m/d";
		} else {
			$this -> options['date_new_format'] = "d/m/Y";
		}

	}

	private $courseNamesToIds = false;
	protected function getCourseByName($courses_name) {
		if (!$courseNamesToIds) {
			$courses = EfrontCourse::getCourses();
			foreach($courses as $course) {
				if (!isset($courseNamesToIds[$course['name']])) {
					$courseNamesToIds[$course['name']] = array($course['id']);
				} else {
					$courseNamesToIds[$course['name']][] = $course['id'];
				}
			}
		}
		return $courseNamesToIds[$courses_name];
	}

	private $groupNamesToIds = false;
	protected function getGroupByName($group_name) {
		if (!$groupNamesToIds) {
			$groups = EfrontGroup::getGroups();
			foreach($groups as $group) {
				if (!isset($groupNamesToIds[$group['name']])) {
					$groupNamesToIds[$group['name']] = array($group['id']);
				} else {
					$groupNamesToIds[$group['name']][] = $group['id'];
				}
			}
		}
		return $groupNamesToIds[$group_name];
	}

	/*
	 * Convert dates of the form dd/mm/yy to timestamps
	 */
    protected function createDatesFromTimestamp($timestamp) {

        // date of event if existing, else current time
        if ($timestamp != "" && $timestamp != 0) {
			return date($this -> options['date_new_format'], $timestamp);
        } else {
        	return "";
        }
    }


}



/****************************************************
 * Class used to export data from csv files
 *
 */
class EfrontExportCsv extends EfrontExport
{
	/*
	 * The separator between the file's fields
	 */
	protected $separator = false;

	/*
	 * Array containing metadata about the exported data type (db attribute names, db tables, export-file accepted column names)
	 */
	protected $types = false;

	/*
	 * Find the header line - the first non zero line of the csv that contains at least one of the export $type's column headers
	 * @param: the line of the header
	 */
	protected function setHeaderLine($type) {
		$this -> types = EfrontExport::getTypes($type);
		if ($type == "users") {
			if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE

				$complete_user_info = array();
				foreach ($this -> types as $column => $field) {
					$complete_user_info[$column] = "users." . $field;
				}

				$hcd_user_info = EfrontImport::getTypes("employees");
				unset($hcd_user_info['users_login']);
				foreach ($hcd_user_info as $column => $field) {
					$complete_user_info[$column] = "module_hcd_employees." . $field;
				}
				$this -> types = $complete_user_info;

			} #cpp#endif
			unset($this -> types['password']);
		}

		$this -> lines[] = implode($this -> separator, array_keys($this -> types));
	}

	protected function clear() {
		$this -> lines = array();
	}

	/*
	 * Use eFront classes according to the type of export to store the data used
	 * @param line: the line of the exported file
	 * @param type: the export type
	 * @param type: the data of this line, formatted to be put directly into the eFront db
	 */
	protected function exportData($data) {
		$result = eF_getTableDataFlat("user_profile", "name", "active=1 AND type ='date'");    //Get admin-defined form fields for user registration
		$dateFields = array();
		if (!empty($result)) {
			$dateFields = $result['name'];
		}

		foreach ($data as $info) {
	        unset($info['password']);
	        foreach ($info as $field => $value) {
	        	if (!(strpos($field, "timestamp") === false) || !(strpos($field, "date") === false)  || $field=="hired_on" || $field=="left_on" || in_array($field, $dateFields)) {
	        		$info[$field] = $this -> createDatesFromTimestamp($value);
	        	}
	        }

	        $this -> lines[] = implode($this -> separator, $info);
	    }

	}


	/*
	 * Get data to be exported
	 */
	protected function getData($type) {
		switch($type) {
			case "users":
				if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
					return eF_getTableData("users LEFT JOIN module_hcd_employees ON users.login = module_hcd_employees.users_login", implode(",", $this -> types), "users.archive = 0");
				} else { #cpp#else
					return eF_getTableData($type, implode(",", $this -> types), "archive = 0");
				} #cpp#endif
			case "users_to_courses":
					return eF_getTableData("users_to_courses JOIN courses ON courses.id = users_to_courses.courses_ID", implode(",", $this -> types), "");
			case "users_to_groups":
					return eF_getTableData("users_to_groups JOIN groups ON groups.id = users_to_groups.groups_ID", implode(",", $this -> types), "");
			#cpp#ifdef ENTERPRISE
			case "branches":
				return eF_getTableData("module_hcd_branch LEFT OUTER JOIN module_hcd_branch as father_branches ON module_hcd_branch.father_branch_ID = father_branches.branch_ID", implode(",", $this -> types), "");
			case "job_descriptions":
				return eF_getTableData("module_hcd_job_description JOIN module_hcd_branch ON module_hcd_branch.branch_ID = module_hcd_job_description.branch_ID", implode(",", $this -> types), "");
			case "skills":
				return eF_getTableData("module_hcd_skills JOIN module_hcd_skill_categories ON module_hcd_skill_categories.id = module_hcd_skills.categories_ID", implode(",", $this -> types), "");
			case "users_to_jobs":
				return eF_getTableData("module_hcd_employee_has_job_description JOIN module_hcd_job_description ON module_hcd_employee_has_job_description.job_description_ID = module_hcd_job_description.job_description_ID JOIN module_hcd_branch ON module_hcd_job_description.branch_ID = module_hcd_branch.branch_ID JOIN module_hcd_employee_works_at_branch ON (module_hcd_job_description.branch_ID = module_hcd_employee_works_at_branch.branch_ID AND module_hcd_employee_works_at_branch.users_login = module_hcd_employee_has_job_description.users_login)", implode(",", $this -> types), "");
			case "users_to_skills":
				return eF_getTableData("module_hcd_employee_has_skill JOIN module_hcd_skills ON module_hcd_employee_has_skill.skill_ID = module_hcd_skills.skill_ID", implode(",", $this -> types), "");

			#cpp#endif
			return eF_getTableData($type, implode(",", $this -> types), "archive = 0");
		}
	}

	/*
	 * Write the exported file
	 */
	protected function writeFile($type) {
	    if (!is_dir($GLOBALS['currentUser'] -> user['directory']."/temp")) {
	        mkdir($GLOBALS['currentUser'] -> user['directory']."/temp", 0755);
	    }

	    file_put_contents($GLOBALS['currentUser'] -> user['directory']."/temp/efront_".$type.".csv", implode("\n", $this -> lines));
	    $file = new EfrontFile($GLOBALS['currentUser'] -> user['directory']."/temp/efront_".$type.".csv");

	    return $file;
	}

	/*
	 * Main exporting function
	 */
	public function export($type) {

		$this -> clear();
		$this -> setHeaderLine($type);
		$data = $this -> getData($type);
		$this -> exportData($data);

		return $this -> writeFile($type);
	}


	public function __construct($_options) {
		$this -> options  = $_options;
		if ($this -> options['date_format'] == "MM/DD/YYYY") {
			$this -> options['date_new_format'] = "m/d/Y";
		} else if ($this -> options['date_format'] == "YYYY/MM/DD") {
			$this -> options['date_new_format'] = "Y/m/d";
		} else {
			$this -> options['date_new_format'] = "d/m/Y";
		}

		if (isset($this -> options['separator'])) {
			$this -> separator = $this -> options['separator'];
		} else {
			$this -> separator = ",";
		}
	}

}




/**
 * Export Factory class
 *
 * This class is used as a factory for export objects
 * <br/>Example
 * <code>
 * $exporter = EfrontExportFactory :: factory('csv', $file, $options);
 * $exporter -> export();
 * $exporter -> export('users');
 * </code>
 *
 * @package eFront
 * @version 3.6.1
 */
class EfrontExportFactory
{
    /**
     * Construct export object
     *
     * This function is used to construct an export object which can be
     * of any type: EfrontCsvExport
     *
     * <br/>Example :
     * <code>
     * $file = $filesystem -> uploadFile('upload_file');
     * $user = EfrontExportFactory :: factory('csv', $file, array("keep_duplicates" => 1);            //Use factory function to instantiate user object with login 'jdoe'
     * </code>
     *
     * @param string the type the exporter: currently only 'csv' is supported $exporterType
     * @param file $filename
     * @param options $various export options
     * @return EfrontExport an object of a class extending EfrontExport
     * @since 3.6.1
     * @access public
     * @static
     */
    public static function factory($exporterType, $options = false) {

    	switch ($exporterType) {
    		case 'csv' : $factory = new EfrontExportCsv($options); break;
    	}

        return $factory;
    }


}


