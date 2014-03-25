<?php
/**
 * EfrontLesson Class file
 *
 * @package eFront
 * @version 3.5.0
 */

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

/**
 * EfrontLessonException class
 *
 * This class extends Exception class and is used to issue errors regarding lessons
 * @author Venakis Periklis <pvenakis@efront.gr>
 * @package eFront
 * @version 1.0
 */
class EfrontLessonException extends Exception
{
	const NO_ERROR          = 0;
	const LESSON_NOT_EXISTS = 201;
	const INVALID_ID        = 202;
	const CANNOT_CREATE_DIR = 203;
	const INVALID_LOGIN     = 204;
	const DATABASE_ERROR    = 205;
	const DIR_NOT_EXISTS    = 206;
	const FILESYSTEM_ERROR  = 207;
	const DIRECTION_NOT_EXISTS = 208;
	const MAX_USERS_LIMIT   = 209;
	const CATEGORY_NOT_EXISTS   = 210;
	const INVALID_PARAMETER = 211;
	const GENERAL_ERROR     = 299;
}

/**
 * EfrontLesson class
 *
 * This class represents a lesson
 * @author Venakis Periklis <pvenakis@efront.gr>
 * @package eFront
 * @version 1.0
 */
class EfrontLesson
{
	/**
	 * The maximum length of a lesson name
	 *
	 * @var string
	 * @since 3.6.1
	 */
	const MAX_NAME_LENGTH = 150;

	/**
	 * The lesson array.
	 *
	 * @since 3.5.0
	 * @var array
	 * @access public
	 */
	public $lesson = array();

	/**
	 * The lesson users. Calling getUsers() initializes it; otherwise, it evaluates to false.
	 *
	 * @since 3.5.0
	 * @var array
	 * @access protected
	 */
	protected $users = false;

	/**
	 * The lesson conditions. Calling getConditions() initializes it; otherwise, it evaluates to false.
	 *
	 * @since 3.5.0
	 * @var array
	 * @access protected
	 * @see getConditions()
	 */
	protected $conditions = false;

	/**
	 * The lesson directory, equals G_LESSONSPATH.$lessonId
	 *
	 * @since 3.5.0
	 * @var string
	 * @access protected
	 */
	protected $directory = '';


	/**
	 * Default lesson options
	 *
	 * @since 3.5.0
	 * @var array
	 * @access public
	 */
	public $options = array('theory'          => 1,
                            'examples'        => 1,
                            'projects'        => 1,
                            'tests'           => 1,
                            'survey'          => 1,
                            'rules'           => 1,
                            'forum'           => 1,
                            'comments'        => 1,
                            'news'			  => 1,
                            'online'          => 1,
                          //'chat'            => 0,
                            'scorm'           => 1,
							'ims'             => 1,
							'tincan'          => 1,
                            'dynamic_periods' => 0,
                            'digital_library' => 1,
                            'calendar'        => 1,
                            'new_content'     => 1,
                            'glossary'        => 1,
							'reports'         => 1,
                            'tracking'        => 1,
                            'auto_complete'   => 1,
                            'content_tree'    => 1,
                            'lesson_info'     => 1,
							'bookmarking'     => 1,
							'content_report'  => 0,
    						'print_content'	  => 1,
    						'start_resume'    => 1,
                            'show_percentage' => 1,
                            'show_right_bar'  => 1,
                            'show_left_bar'   => 0,
                            'show_student_cpanel' => 1,
                            'recurring'	          => 0,
                            'recurring_duration'  => 0,
                            'show_content_tools'  => 1,
                            'show_dashboard'      => 1,
    						'show_horizontal_bar' => 1,
	//'complete_next_lesson'=> 0,
    						'default_positions'   => '',
							'feedback'   		  => 1,
							'smart_content'		  => 1,
							'timers'			  => 0);

	/**
	 * Class constructor
	 *
	 * This function is used to instantiate the class. The instatiation is done
	 * based on a lesson id or a lesson array.
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(32);                     //32 is a lesson id
	 * $result = eF_getTableData("lessons", "*", "id=32"); //Get lesson data from databasse
	 * $lesson = new EfrontLesson($result[0]);             //Instantiate using prepared data
	 * </code>
	 *
	 * @param int $lesson Either the lesson id or the lesson data
	 * @since 3.5.0
	 * @access public
	 */
	function __construct($lesson) {
		$this -> initializeDataFromSource($lesson);
		$this -> initializeDirectory();
		$this -> initializeOptions();
		$this -> buildPriceString();
	}

	/**
	 * Initialize lesson data based on the passed parameter. If the parameter is an id, then
	 * a db query takes place in order to retrive lesson values. Otherwise, if it's an array
	 * it is used for the lesson values.
	 *
	 * @param mixed $lesson A lesson id or an array with lesson values
	 * @since 3.6.1
	 * @access protected
	 */
	private function initializeDataFromSource($lesson) {
		if (is_array($lesson)) {
			$this -> lesson = $lesson;
		} elseif (!$this -> validateId($lesson)) {
			throw new EfrontLessonException(_INVALIDID, EfrontLessonException :: INVALID_ID);
		} else {
			$lesson = eF_getTableData("lessons", "*", "id = $lesson");
			if (empty($lesson)) {
				throw new EfrontLessonException(_LESSONDOESNOTEXIST, EfrontLessonException :: LESSON_NOT_EXISTS);
			}
			$this -> lesson = $lesson[0];
		}
		if (isset($this -> lesson['publish']) && $this -> lesson['publish'] == 0) {
			eF_updateTableData("lessons", array("publish" => 1), "id=".$this -> lesson['id']);
			$this -> lesson['publish'] = 1;
		}
	}

	/**
	 * Initialize lesson directory and create if it does not exist
	 *
	 * @since 3.6.1
	 * @access protected
	 */
	private function initializeDirectory() {

		if ($this -> lesson['instance_source'] && isset($this -> lesson['share_folder']) && $this -> lesson['share_folder'] != $this -> lesson['instance_source']) {
			try {			
				$sourceFolderLessonId = $this->lesson['instance_source'];
				while ($sourceFolderLessonId && $count++ < 1000) {
					$sourceLesson = new EfrontLesson($sourceFolderLessonId);
					$sourceFolderLessonId = $sourceLesson->lesson['share_folder'];
				}
								
				//$this -> persist(); We don't use persist() because the object is not fully constructed yet and it will ruin it
				eF_updateTableData("lessons", array('share_folder' => $sourceLesson->lesson['id']), "id=".$this -> lesson['id']);
				$this -> lesson['share_folder'] = $sourceLesson->lesson['id'];
			} catch (Exception $e) {
				//do nothing
			}
		}

		if (!empty($this -> lesson['share_folder'])) {
			$this -> directory = G_LESSONSPATH.$this -> lesson['share_folder'].'/';
		} else {
			$this -> directory = G_LESSONSPATH.$this -> lesson['id'].'/';
		}

		if (!is_dir($this -> directory)) {
			mkdir($this -> directory, 0755);
		}
	}

	/**
	 * Initialize lesson options, by unserializing the stored options array
	 *
	 * @since 3.6.1
	 * @access protected
	 */
	private function initializeOptions() {
		$this -> validateSerializedArray($this -> lesson['options']) OR $this -> lesson['options'] = $this -> sanitizeSerialized($this -> lesson['options']);
		$options 		 = unserialize($this -> lesson['options']);
		$newOptions      = array_diff_key($this -> options, $options);    //$newOptions are lesson options that were added to the EfrontLesson object AFTER the lesson options serialization took place
		$this -> options = $options + $newOptions;                        //Set lesson options
	}

	/**
	 * Build the price string. This function takes the lesson price and
	 * creates a human-readable version, based on whether it is a one-time
	 * or a recurring price
	 *
	 * @since 3.6.1
	 * @access protected
	 */
	private function buildPriceString() {
		if (!empty($this -> lesson['price']) && $this -> validateFloat($this -> lesson['price'])) {                                        //Create the string representing the lesson price
			$this -> options['recurring'] ? $recurring = array($this -> options['recurring'], $this -> options['recurring_duration']) : $recurring = false;
			$this -> lesson['price_string'] = formatPrice($this -> lesson['price'], $recurring);
		} else {
			$this -> lesson['price_string'] = formatPrice(0);
		}
		
		if (mb_strlen($this->lesson['name']) > 100) {
			$this->lesson['formatted_name'] = mb_substr($this->lesson['name'], 0, 100).'...';
		} else {
			$this->lesson['formatted_name'] = $this->lesson['name'];
		}
	}

	private static function validateAndSanitizeLessonFields($lessonFields) {
		$lessonFields = self :: setDefaultLessonValues($lessonFields);

		$fields = array('name'               		=> self :: validateAndSanitize($lessonFields['name'], 					'name'),
                        'active'             		=> self :: validateAndSanitize($lessonFields['active'], 				'boolean'),
        				'archive'                   => self :: validateAndSanitize($lessonFields['archive'], 				'boolean'),
						'course_only'               => self :: validateAndSanitize($lessonFields['course_only'], 			'boolean'),
						'share_folder'              => self :: validateAndSanitize($lessonFields['share_folder'], 			'integer'),
						'shift'             		=> self :: validateAndSanitize($lessonFields['shift'], 					'boolean'),
        				'created'		  			=> self :: validateAndSanitize($lessonFields['created'], 				'boolean_or_timestamp'),
						'from_timestamp'		  	=> self :: validateAndSanitize($lessonFields['from_timestamp'], 		'boolean_or_timestamp'),
						'to_timestamp'		  		=> self :: validateAndSanitize($lessonFields['to_timestamp'], 			'boolean_or_timestamp'),
        				'options'            		=> self :: validateAndSanitize($lessonFields['options'], 				'serialized'),
        				'metadata'           		=> self :: validateAndSanitize($lessonFields['metadata'], 				'serialized'),
        				'info'               		=> self :: validateAndSanitize($lessonFields['info'], 					'serialized'),
		//'description'               => self :: validateAndSanitize($lessonFields['description'], 			'text'),
        				'price'              		=> self :: validateAndSanitize($lessonFields['price'], 					'float'),
						'duration'              	=> self :: validateAndSanitize($lessonFields['duration'], 				'integer'),
						'access_limit'             	=> self :: validateAndSanitize($lessonFields['access_limit'],			'integer'),
        				'show_catalog'              => self :: validateAndSanitize($lessonFields['show_catalog'],   		'boolean'),
        				'publish'					=> self :: validateAndSanitize($lessonFields['publish'], 				'boolean'),
        				'directions_ID'      		=> self :: validateAndSanitize($lessonFields['directions_ID'],  		'directions_foreign_key'),
                        'languages_NAME'     		=> self :: validateAndSanitize($lessonFields['languages_NAME'], 		'languages_foreign_key'),
                        'max_users'                 => self :: validateAndSanitize($lessonFields['max_users'], 		 		'integer'),
						'certificate'               => self :: validateAndSanitize($lessonFields['certificate'], 			'text'),
        				'instance_source'			=> self :: validateAndSanitize($lessonFields['instance_source'], 		'lessons_foreign_key'),
						'originating_course'		=> self :: validateAndSanitize($lessonFields['originating_course'], 	'courses_foreign_key'));
		return $fields;
	}

	private static function setDefaultLessonValues($lessonFields) {
		$defaultValues = array('name'               	=> '',
	                           'active'             	=> 1,
		        			   'archive'                => 0,
							   'course_only'			=> 1,
							   'share_folder'			=> 0,
							   'shift'					=> 0,
		        			   'created'		  		=> 0,
							   'from_timestamp'			=> 0,
							   'to_timestamp'			=> 0,
		        			   'options'            	=> '',
		        			   'metadata'           	=> '',
		        			   'info'               	=> '',
		        			   'description'            => '',
		        			   'price'              	=> 0,
							   'duration'				=> 0,
		        			   'show_catalog'           => 1,
		        			   'publish'				=> 1,
		        			   'directions_ID'      	=> 0,
		                       'languages_NAME'     	=> 'english',
		                       'max_users'              => 0,
							   'certificate'			=> '',
		                       'originating_course'	    => 0,
		        			   'instance_source'		=> 0);

		return array_merge($defaultValues, $lessonFields);
	}
	/**
	 * Validate and sanitize parameters
	 *
	 * This function is used to validate and sanitize the passed parameter.
	 * It accepts the $field argument and its type and validates the field argument against the
	 * desired type.
	 * <br/>Example:
	 * <code>
	 * $lesson -> validateAndSanitize(32, 'float');	//returns 32
	 * $lesson -> validateAndSanitize('32asd', 'integer');	//returns 32
	 * </code>
	 *
	 * @param mixed $field The field to validate and optionally sanitize
	 * @param string $type The desired parameter type
	 * @return mixed The original passed parameter, sanitized
	 * @since 3.6.1
	 * @access public
	 */
	public static function validateAndSanitize($field, $type) {
		try {
			self :: validate($field, $type);
		} catch (EfrontLessonException $e) {
			if ($e -> getCode() == EfrontLessonException::INVALID_PARAMETER) {
				$field = self :: sanitize($field, $type);
			} else {
				throw $e;
			}
		}
		return $field;
	}

	/**
	 * Validate input based on the specified type
	 *
	 * This function validates the parameter value against the specified
	 * type. If it does not match, an exception is thrown.
	 * <br/>Example:
	 * <code>
	 * $lesson -> validate(32, 'float');	//returns true
	 * $lesson -> validate('32asd', 'integer');	//throws exception.
	 * </code>
	 *
	 * @param mixed $field The field to validate
	 * @param string $type The desired parameter type
	 * @return boolean Whether the passed parameter is valid
	 * @since 3.6.1
	 * @access public
	 */
	public static function validate($field, $type) {
		$validParameter = true;
		switch ($type) {
			case 'id':				   	   self :: validateId($field) 					 OR $validParameter = false; break;
			case 'name':				   self :: validateName($field) 				 OR $validParameter = false; break;
			case 'boolean':				   self :: validateBoolean($field) 			 OR $validParameter = false; break;
			case 'float':				   self :: validateFloat($field) 				 OR $validParameter = false; break;
			case 'integer': 			   self :: validateInteger($field) 			 OR $validParameter = false; break;
			case 'directions_foreign_key': self :: validateDirectionsForeignKey($field) OR $validParameter = false; break;
			case 'languages_foreign_key':  self :: validateLanguagesForeignKey($field)  OR $validParameter = false; break;
			case 'lessons_foreign_key':    self :: validateLessonsForeignKey($field) 	 OR $validParameter = false; break;
			case 'text': 				   self :: validateText($field) 				 OR $validParameter = false; break;
			case 'serialized':	   		   self :: validateSerialized($field) 			 OR $validParameter = false; break;
			case 'boolean_or_timestamp':   (self :: validateBoolean($field) || self :: validateTimestamp($field))  OR $validParameter = false; break;
			default: break;
		}

		if ($validParameter) {
			return true;
		} else {
			throw new EfrontLessonException(_INVALIDPARAMETER.' ('.$type.'): "'.$field.'"', EfrontLessonException::INVALID_PARAMETER);
		}
	}

	private static function validateId($field) {
		!eF_checkParameter($field, 'id') ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateName($field) {
		mb_strlen($field) > self::MAX_NAME_LENGTH ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateText($field) {
		return true;
	}
	private static function validateBoolean($field) {
		$field !== true && $field !== false ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateTimestamp($field) {
		!eF_checkParameter($field, 'timestamp') ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateSerialized($field) {
		unserialize($field) === false && $field !== serialize(false) ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateSerializedArray($field) {
		$unserialized = unserialize($field);
		$unserialized === false || !is_array($unserialized) ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateNull($field) {
		!is_null($field) ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateFloat($field) {
		!is_numeric($field) ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateInteger($field) {
		!is_numeric($field) ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateDirectionsForeignKey($field) {
		!eF_checkParameter($field, 'id') || sizeof(eF_getTableData("directions", "id", "id=".$field)) == 0 ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateLessonsForeignKey($field) {
		!eF_checkParameter($field, 'id') || sizeof(eF_getTableData("lessons", "id", "id=".$field)) == 0 ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateCoursesForeignKey($field) {
		!eF_checkParameter($field, 'id') || sizeof(eF_getTableData("courses", "id", "id=".$field)) == 0 ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateLanguagesForeignKey($field) {
		!eF_checkParameter($field, 'login') || sizeof(eF_getTableData("languages", "name", "name='".$field."'")) == 0 ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}
	private static function validateUsersForeignKey($field) {
		!eF_checkParameter($field, 'login') || sizeof(eF_getTableData("users", "login", "login='$field'")) == 0 ? $returnValue = false : $returnValue = true;
		return $returnValue;
	}

	/**
	 * Sanitize parameter
	 *
	 * This function is used to sanitize the passed parameter, based on the type
	 * specified
	 * <br/>Example:
	 * <code>
	 * $lesson -> sanitize(32, 'float');	//returns 32
	 * $lesson -> sanitize('32asd', 'integer');	//returns 32
	 * </code>
	 *
	 * @param mixed $field The field to sanitize
	 * @param string $type The desired parameter type
	 * @return mixed The sanitized passed parameter
	 * @since 3.6.1
	 * @access public
	 */
	public function sanitize($field, $type) {
		switch ($type) {
			case 'name':				 $field = self :: sanitizeName($field); 	   break;
			case 'boolean': 			 $field = self :: sanitizeBoolean($field);    break;
			case 'boolean_or_timestamp': $field = self :: sanitizeBoolean($field);    break;
			case 'timestamp': 			 $field = self :: sanitizeTimestamp($field);  break;
			case 'serialized': 	 		 $field = self :: sanitizeSerialized($field); break;
			case 'float':				 $field = self :: sanitizeFloat($field); 	   break;
			case 'integer':
			case 'id':   				 $field = self :: sanitizeInteger($field);    break;
			case 'directions_foreign_key':
			case 'languages_foreign_key':
			case 'lessons_foreign_key':	 $field = self :: sanitizeForeignKey($field); break;
			case 'text': default: break;
		}

		return $field;
	}
	private static function sanitizeTimestamp($field) {
		$field = time();
		return $field;
	}
	private static function sanitizeName($field) {
		$field = mb_substr($field, 0, self::MAX_NAME_LENGTH);
		return $field;
	}
	private static function sanitizeBoolean($field) {
		$field = ($field != 0);
		return $field;
	}
	private static function sanitizeSerialized($field) {
		$field = serialize(array());
		return $field;
	}
	private static function sanitizeFloat($field) {
		$field = (float)$field;
		return $field;
	}
	private static function sanitizeInteger($field) {
		$field = (int)$field;
		return $field;
	}
	private static function sanitizeForeignKey($field) {
		$field = 0;
		return $field;
	}

	/**
	 * Create lesson
	 *
	 * This function created a new lesson. This involves creating
	 * the database instance, creating the corresponding filesystem
	 * folder (read below) and finally creating corresponding forum entries
	 * as well as any other information needed.
	 * The function argument is an array with field values, corresponding to database
	 * fields. All fields are optional, and if absent they are filled with default values,
	 * but 'name', 'languages_NAME' and 'directions_ID' are strongly recommended to be defined
	 * <br/>Example:
	 * <code>
	 * $fields = array('name' => 'Test lesson', 'directions_ID' => 2, 'languages_NAME' => 'english');
	 * try {
	 *   $newLesson = EfrontLesson :: createLesson($fields);                     //$newLesson is now a new lesson object
	 * } catch (Exception $e) {
	 *   echo $e -> getMessage();
	 * }
	 * </code><br/>
	 * When a lesson is created, a folder named after the lesson id is created
	 * inside the G_LESSONSPATH folder. For example, if G_LESSONSPATH is
	 * /var/www/efront/www/content/lessons/ and the new lesson gets the id 35,
	 * a corresponding folder will be created: /var/www/efront/www/content/lessons/35/
	 *
	 * @param array $fields The new lesson characteristics
	 * @return EfrontLesson the new lesson object
	 * @since 3.5.0
	 * @access public
	 * @static
	 */
	public static function createLesson($fields) {
		is_dir(G_LESSONSPATH) OR mkdir(G_LESSONSPATH, 0755);

		$fields['metadata'] 	 = self::createLessonMetadata($fields);
		$fields['directions_ID'] = self::computeNewLessonDirectionsId($fields);
		$fields['created'] 		 = time();
		$fields['id'] 			 = self::computeNewLessonId();
		$fields 				 = self::validateAndSanitizeLessonFields($fields);
		isset($fields['creator_LOGIN']) OR $fields['creator_LOGIN'] = $_SESSION['s_login'];
		
		$lessonId 	  = eF_insertTableData("lessons", $fields);
		$newLesson    = new EfrontLesson($lessonId);

		EfrontSearch :: insertText($fields['name'], $lessonId, "lessons", "title");

		self::addNewLessonSkills($newLesson);
		self::createLessonForum($newLesson);
		self::notifyModuleListenersForLessonCreation($newLesson);

		return $newLesson;
	}

	/**
	 * Create lesson metadata
	 *
	 * @param array $fields Lesson properties
	 * @return string Serialized representation of metadata array
	 * @since 3.6.1
	 * @access private
	 */
	private static function createLessonMetadata($fields) {
		$languages      = EfrontSystem :: getLanguages(true);
		$lessonMetadata = array('title'       => $fields['name'],
                                'creator'     => isset($GLOBALS['currentUser']) ? formatLogin($GLOBALS['currentUser'] -> user['login']) : '',
                                'publisher'   => isset($GLOBALS['currentUser']) ? formatLogin($GLOBALS['currentUser'] -> user['login']) : '',
                                'contributor' => isset($GLOBALS['currentUser']) ? formatLogin($GLOBALS['currentUser'] -> user['login']) : '',
                                'date'        => date("Y/m/d", time()),
                                'language'    => $languages[$fields['languages_NAME']],
                                'type'        => 'lesson');
		$metadata = serialize($lessonMetadata);
		return $metadata;
	}

	private static function computeNewLessonId() {
		$fileSystemTree = new FileSystemTree(G_LESSONSPATH, true);
		foreach ($fileSystemTree -> tree as $key => $value) {
			if (preg_match("/\d+/", basename($key))) {
				$directories[] = basename($key);
			}
		}
		$result = eF_getTableData("lessons", "max(id) as max_id");

		$firstFreeSlot = (max($result[0]['max_id'], max($directories))) + 1;

		return $firstFreeSlot;
	}

	private static function computeNewLessonDirectionsId($fields) {
		if (!isset($fields['directions_ID'])) {
			$directions = eF_getTableData("directions", "id");
			sizeof($directions) > 0 ? $fields['directions_ID'] = $directions[0]['id'] : $fields['directions_ID'] = 1;
		}
		return $fields['directions_ID'];
	}

	private static function createLessonForum($lesson) {
		if ($lesson -> lesson['originating_course']) {
			$originatingCourse = new EfrontCourse($lesson -> lesson['originating_course']);
			$titleString = $originatingCourse -> course['name'].'&nbsp;&raquo;&nbsp;'.$lesson -> lesson['name'];
		} else {
			$titleString = $lesson -> lesson['name'];
		}
		$forumFields = array('title'       => $titleString,
                             'lessons_ID'  => $lesson -> lesson['id'],
                             'parent_id'   => 0,
                             'status'      => 1,
                             'users_LOGIN' => isset($_SESSION['s_login']) ? $_SESSION['s_login'] : '',
                             'comments'    => '');
		$forumId = eF_insertTableData("f_forums", $forumFields);
		EfrontSearch :: insertText($lesson -> lesson['name'], $forumId, "f_forums", "title");
	}

	private static function addNewLessonSkills($lesson) {
		if (G_VERSIONTYPE == 'educational') { #cpp#ifdef EDUCATIONAL
			// If insertion of a self-contained lesson add the corresponding skill - For educational version only
			if (!$lesson -> lesson['course_only']) {
				// Insert the corresponding lesson skill to the skill and lesson_offers_skill tables
				$lessonSkillId = eF_insertTableData("module_hcd_skills", array("description" => _KNOWLEDGEOFLESSON . " ". $lesson -> lesson['name'], "categories_ID" => -1));
				eF_insertTableData("module_hcd_lesson_offers_skill", array("lesson_ID" => $lesson -> lesson['id'], "skill_ID" => $lessonSkillId));
			}
		} #cpp#endif
	}

	private static function notifyModuleListenersForLessonCreation($lesson) {
		// Get all modules (NOT only the ones that have to do with the user type)
		$modules = eF_loadAllModules();
		// Trigger all necessary events. If the function has not been re-defined in the derived module class, nothing will happen
		foreach ($modules as $module) {
			$module -> onNewLesson($lesson -> lesson['id']);
		}
	}

	/**
	 * Archive lesson
	 *
	 * This function is used to archive the lesson object, by setting its active status to 0 and its
	 * archive status to 1
	 * <br/>Example:
	 * <code>
	 * $lesson -> archive();	//Archives the lesson object
	 * $lesson -> unarchive();	//Archives the lesson object and activates it as well
	 * </code>
	 *
	 * @since 3.6.0
	 * @access public
	 */
	public function archive() {
		$this -> lesson['archive'] = time();
		$this -> lesson['active']  = 0;
		$this -> persist();
	}

	/**
	 * Unarchive lesson
	 *
	 * This function is used to unarchive the lesson object, by setting its active status to 1 and its
	 * archive status to 0
	 * <br/>Example:
	 * <code>
	 * $lesson -> archive();	//Archives the lesson object
	 * $lesson -> unarchive();	//Archives the lesson object and activates it as well
	 * </code>
	 *
	 * @since 3.6.0
	 * @access public
	 */
	public function unarchive() {
		$this -> lesson['archive'] = 0;
		$this -> lesson['active']  = 1;
		//Check whether the original category exists
		$result = eF_getTableDataFlat("directions", "id");
		if (in_array($this -> lesson['directions_ID'], $result['id'])) {		// If the original category exists, no problem
			$this -> persist();
		} elseif (empty($result)) {			//If no categories exist in the system, throw exception
			throw new EfrontLessonException(_NOCATEGORIESDEFINED, EfrontLessonException::CATEGORY_NOT_EXISTS);
		} else {				//If some other category exists, assign it there
			$this -> lesson['directions_ID'] = $result['id'][0];
			$this -> persist();
		}
	}


	/**
	 * Delete lesson
	 *
	 * This function is used to delete an existing lesson. In order to do
	 * this, it caclulates all the lesson dependendant elements, deletes them
	 * and finally deletes the lesson itself.
	 *
	 * <br/>Example:
	 * <code>
	 * try {
	 *   $lesson = new EfrontLesson(32);                     //32 is the lesson id
	 *   $lesson -> delete();
	 * } catch (Exception $e) {
	 *   echo $e -> getMessage();
	 * }
	 * </code>
	 *
	 * @param boolean $removeFromCourse whether to remove the lesson from its belonging courses. Useful in case a course initiates the deletion
	 * @since 3.5.0
	 * @access public
	 */
	public function delete($removeFromCourse = true) {
		$this -> initialize('all');
		if ($removeFromCourse) {
			$this -> removeLessonFromCourses();
		}
		$this -> removeLessonForums();
		$this -> removeLessonSkills();

		calendar::deleteLessonCalendarEvents($this -> lesson['id']);

		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			eF_deleteTableData("module_hcd_lesson_to_job_description", "lessons_ID=".$this -> lesson['id']);
			eF_deleteTableData("module_hcd_lesson_to_branch", "lessons_ID=".$this -> lesson['id']);
		} #cpp#endif

		eF_deleteTableData("events", "lessons_ID=".$this -> lesson['id']);
		eF_deleteTableData("lessons_to_groups", "lessons_ID=".$this -> lesson['id']);
		eF_deleteTableData("lessons_timeline_topics", "lessons_ID=".$this -> lesson['id']);
		eF_deleteTableData("lessons", "id=".$this -> lesson['id']);
		EfrontSearch :: removeText('lessons', $this -> lesson['id'], '');
	}

	private function removeLessonSkills() {
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				eF_deleteTableData("module_hcd_lesson_offers_skill", "lesson_ID = '". $this -> lesson['id'] . "'");
			} #cpp#endif
		} #cpp#endif

		if (G_VERSIONTYPE == 'educational') { #cpp#ifdef EDUCATIONAL
			//In educational edition, skills are automatic
			$skills = $this -> getSkills(true);
			if (!empty($skills)) {
				eF_deleteTableData("module_hcd_skills", "skill_ID IN ('". implode("','", array_keys($skills)) . "')");
			}
		} #cpp#endif
	}

	private function removeLessonFromCourses() {
		foreach ($this -> getCourses(true) as $course) {
			$course -> removeLessons($this);
		}
	}

	private function removeLessonForums() {
		$lessonsForums = eF_getTableData("f_forums", "*", "lessons_ID=".$this -> lesson['id']);
		foreach($lessonsForums as $value) {
			$forum = new f_forums($value);
			$forum -> delete();
		}
	}

	/**
	 * Returns the direction of the lesson
	 *
	 * This function is used to return the direction of the lesson
	 * <br/>Example:
	 * <code>
	 * $direction_info = $lesson -> getDirection();
	 * </code>
	 *
	 * @return array The returned array has two fields: id and name of the lesson's direction
	 * @since 3.5.0
	 * @access public
	 */
	public function getDirection(){
		$result = eF_getTableData("directions", "id, name", "id=".$this -> lesson['directions_ID']);
		return array($result[0]['id'] => $result[0]['name']);
	}

	/**
	 * Activate lesson
	 *
	 * This function is used to activate a lesson
	 * <br/>Example:
	 * <code>
	 * $lesson -> activate();
	 * </code>
	 *
	 * @return boolean True if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function activate() {
		$this -> lesson['active'] = 1;
		$this -> persist();
		return true;
	}

	/**
	 * Deactivate lesson
	 *
	 * This function is used to deactivate a lesson
	 * <br/>Example:
	 * <code>
	 * $lesson -> deactivate();
	 * </code>
	 *
	 * @return boolean True if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function deactivate() {
		$this -> lesson['active'] = 0;
		$this -> persist();
		return true;
	}

	/**
	 * Get lesson directory
	 *
	 * This function returns the lesson directory, normally G_LESSONSPATH.$lesson['id']
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(43);          //Instantiate object for lesson with id 43
	 * $lesson -> getDirectory();               //Returns something like /var/sites/efront/www/content/lessons/43
	 * $lesson -> getDirectory(true);           //Returns EfrontDirectory object for lesson directory
	 * </code>
	 *
	 * @param boolean $returnObject If true, an EfrontDirectory object is returned
	 * @return mixed Either a string with the lesson directory, or the equivalent EfrontDirectory object
	 * @since 3.5.0
	 * @access public
	 */
	public function getDirectory($returnObject = false) {
		if ($returnObject) {
			return new EfrontDirectory($this -> directory);
		} else {
			return $this -> directory;
		}
	}

	/**
	 * Get url to lesson's directory
	 *
	 * This function is used to return the public url that this lesson uses
	 * Since we may have shadow lessons, this url depends on the lesson directory
	 *
	 * @return string The lesson url
	 * @since 3.6.0
	 * @access public
	 */
	public function getDirectoryUrl() {
		$url = G_RELATIVELESSONSLINK.str_replace(G_LESSONSPATH, '', $this -> getDirectory());
		return $url;
	}

	/**
	 * Get lesson users
	 *
	 * This function returns an array with the lesson users.
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(32);                      //32 is a lesson id
	 * $lessonUsers    = $lesson -> getUsers();             //Get the lesson users
	 * $nonLessonUsers = $lesson -> getNonUsers();          //Get the users that don't have the lesson, but are eligible to
	 * </code>
	 * The returned array keys match the users logins
	 *
	 * @param string $basicType The user's basic type in the lesson
	 * @param boolean $refresh Whether to explicitly refresh the object cached data set
	 * @return array A 2-dimensional array with lesson users per type, or a 1-dimensional array with lesson users of the specified type
	 * @since 3.5.0
	 * @access public
	 */
	public function getUsers($basicType = false, $refresh = false) {
		if ($this -> users === false || $refresh || $basicType) {                //Make a database query only if the variable is not initialized, or it is explicitly asked
			$this -> users = array();
			
			if ($basicType && eF_checkParameter($basicType, 'alnum_general')) {
				$users = array();
				if ($basicType == 'professor') {
					$roles = EfrontLessonUser :: getProfessorRoles();
				} else if ($basicType == 'student') {
					$roles = EfrontLessonUser :: getStudentRoles();
				} else {
					$roles = $basicType;
				}
				
				$result = eF_getTableData("users u, users_to_lessons ul", "u.*, ul.user_type as role, ul.from_timestamp, ul.completed, ul.to_timestamp as timestamp_completed", "u.user_type != 'administrator' and ul.archive = 0 and u.archive = 0 and ul.users_LOGIN = login and lessons_ID=".$this -> lesson['id']." and ul.user_type in ('".implode("','", $roles)."')");
			} else {
				$result = eF_getTableData("users u, users_to_lessons ul", "u.*, ul.user_type as role, ul.from_timestamp, ul.completed, ul.to_timestamp as timestamp_completed", "u.user_type != 'administrator' and ul.archive = 0 and u.archive = 0 and ul.users_LOGIN = login and lessons_ID=".$this -> lesson['id']);
			}

			foreach ($result as $value) {
				$users[$value['login']] = array('login'           => $value['login'],
                                                        'email'           => $value['email'],
                                                        'name'            => $value['name'],
                                                        'surname'         => $value['surname'],
                                                        'basic_user_type' => $value['user_type'],
                                                        'user_type'		  => $value['user_type'],
                                                        'user_types_ID'   => $value['user_types_ID'],
                                                        'role'            => $value['role'],
														'from_timestamp'  => $value['from_timestamp'],
                                                        'active'          => $value['active'],
                										'avatar'          => $value['avatar'],
                										'completed'		  => $value['completed'],
														'timestamp_completed' => $value['timestamp_completed'],
                                                        'partof'          => 1);
			}
			if (!$basicType) {
				$this->users = $users;
			}
		}

		if ($basicType) {
			return $users;
		} else {			
			return $this -> users;
		}

	}

	/*
	 * Append the tables that are used from the statistics filters to the FROM table list
	 */
	public static function appendTableFiltersUserConstraints($from, $constraints) {
		if (isset($constraints['table_filters'])) {
			foreach ($constraints['table_filters'] as $constraint) {
				if (isset($constraint['table']) && isset($constraint['joinField'])) {
					$from .= " JOIN " . $constraint['table'] . " ON u.login = " . $constraint['joinField'];
				}
			}
		}
		return $from;
	}

	/**
	 * Get lesson users based on the specified constraints
	 *
	 * @param array $constraints The constraints for the query
	 * @return array An array of EfrontUser objects
	 * @since 3.6.2
	 * @access public
	 */
	public function getLessonUsers($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);

		list($where, $limit, $orderby) = EfrontUser :: convertUserConstraintsToSqlParameters($constraints);

		$select  = "u.*, ul.lessons_ID,ul.completed,ul.score,ul.user_type as role,ul.from_timestamp as active_in_lesson, ul.to_timestamp as timestamp_completed, ul.comments, ul.done_content, 1 as has_lesson";
		$where[] = "u.login=ul.users_LOGIN and ul.lessons_ID='".$this -> lesson['id']."' and ul.archive=0";
		$from = EfrontLesson::appendTableFiltersUserConstraints("users u JOIN users_to_lessons ul ON u.login=ul.users_LOGIN", $constraints);
	
		$result  = eF_getTableData($from, $select, implode(" and ", $where), $orderby, 'u.login', $limit);	// Added group by because of #3943	
		
		if (!isset($constraints['return_objects']) || $constraints['return_objects'] == true) {
			return EfrontUser :: convertDatabaseResultToUserObjects($result);
		} else {
			return EfrontUser :: convertDatabaseResultToUserArray($result);
		}
	}

	/**
	 * Count lesson users based on the specified constraints
	 * @param array $constraints The constraints for the query
	 * @return array An array of EfrontUser objects
	 * @since 3.6.3
	 * @access public
	 */
	public function countLessonUsers($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);

		list($where, $limit, $orderby) = EfrontUser :: convertUserConstraintsToSqlParameters($constraints);

		$where[] = "u.login=ul.users_LOGIN and ul.lessons_ID='".$this -> lesson['id']."' and ul.archive=0";
		$from = EfrontLesson::appendTableFiltersUserConstraints("users u JOIN users_to_lessons ul ON u.login=ul.users_LOGIN", $constraints);
		$result  = eF_countTableData($from, "distinct(u.login)", implode(" and ", $where));
	
		return $result[0]['count'];
	}

	/**
	 * Get lesson users based on the specified constraints, but include unassigned users as well.
	 * Assigned users have the flag 'has_lesson' set to 1
	 *
	 * @param array $constraints The constraints for the query
	 * @return array An array of EfrontUser objects
	 * @since 3.6.2
	 * @access public
	 */
	public function getLessonUsersIncludingUnassigned($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);

		list($where, $limit, $orderby) = EfrontUser :: convertUserConstraintsToSqlParameters($constraints);
		$where[] = "user_type != 'administrator'";
		$select  = "u.*, r.lessons_ID is not null as has_lesson, r.completed,r.score, r.from_timestamp as active_in_lesson, r.role";
		$from    = "users u left outer join (select completed,score,lessons_ID,from_timestamp,users_LOGIN,user_type as role from users_to_lessons where lessons_ID='".$this -> lesson['id']."' and archive=0) r on u.login=r.users_LOGIN";
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			if (isset($constraints['branch']) && $constraints['branch']) {
				$from .= " JOIN module_hcd_employee_works_at_branch ON module_hcd_employee_works_at_branch.users_login = u.login";
			}
			if (isset($constraints['jobs']) && $constraints['jobs']) {
				$from .= " LEFT OUTER JOIN module_hcd_employee_has_job_description ON module_hcd_employee_has_job_description.users_login = u.login JOIN module_hcd_job_description ON module_hcd_job_description.job_description_ID = module_hcd_employee_has_job_description.job_description_ID";
			}
		} #cpp#endif
		$result  = eF_getTableData($from, $select,
		implode(" and ", $where), $orderby, false, $limit);

		if (!isset($constraints['return_objects']) || $constraints['return_objects'] == true) {
			return EfrontUser :: convertDatabaseResultToUserObjects($result);
		} else {
			return EfrontUser :: convertDatabaseResultToUserArray($result);
		}

	}

	/**
	 * Count lesson users based on the specified constraints, including unassigned
	 * @param array $constraints The constraints for the query
	 * @return array An array of EfrontUser objects
	 * @since 3.6.3
	 * @access public
	 */
	public function countLessonUsersIncludingUnassigned($constraints = array()) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);

		list($where, $limit, $orderby) = EfrontUser :: convertUserConstraintsToSqlParameters($constraints);
		$where[] = "user_type != 'administrator'";
		$select  = "u.login";
		$from    = "users u left outer join (select completed,score,lessons_ID,from_timestamp,users_LOGIN from users_to_lessons where lessons_ID='".$this -> lesson['id']."' and archive=0) r on u.login=r.users_LOGIN";
		if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
			if (isset($constraints['branch']) && $constraints['branch']) {
				$from .= " JOIN module_hcd_employee_works_at_branch ON module_hcd_employee_works_at_branch.users_login = u.login";
			}
			if (isset($constraints['jobs']) && $constraints['jobs']) {
				$from .= " LEFT OUTER JOIN module_hcd_employee_has_job_description ON module_hcd_employee_has_job_description.users_login = u.login JOIN module_hcd_job_description ON module_hcd_job_description.job_description_ID = module_hcd_employee_has_job_description.job_description_ID";
			}
		} #cpp#endif

		$result  = eF_countTableData($from, $select, implode(" and ", $where));

		return $result[0]['count'];
	}

	//TO REPLACE getUsers
	public function getLessonUsersOld($returnObjects = false) {
		if ($this -> users === false) {
			$this -> initializeUsers();
		}

		if ($returnObjects) {
			foreach ($this -> users as $key => $user) {
				$users[$key] = EfrontUserFactory :: factory($key);
			}
			return $users;
		} else {
			return $this -> users;
		}
	}

	private function initializeUsers() {
		$this -> lesson['total_students'] = $this -> lesson['total_professors'] = 0;
		$roles  = EfrontLessonUser :: getLessonsRoles();

		$result = eF_getTableData("users_to_lessons ul, users u", "u.*, u.user_type as basic_user_type, ul.user_type as role, ul.from_timestamp as active_in_lesson, ul.score, ul.completed", "u.archive = 0 and ul.archive = 0 and ul.users_LOGIN = u.login and ul.lessons_ID=".$this -> lesson['id']);
		foreach ($result as $value) {
			$this -> users[$value['login']] = $value;
			if ($roles[$value['role']] == 'student') {
				$this -> lesson['total_students']++;
			} elseif ($roles[$value['role']] == 'professor') {
				$this -> lesson['total_professors']++;
			}
		}
	}

	public function getStudentUsers($returnObjects = false) {
		$lessonUsers = $this -> getLessonUsersOld($returnObjects);

		foreach ($lessonUsers as $key => $value) {
			if ($value instanceOf EfrontUser) {
				$value = $value -> user;
			}
			if (!EfrontUser::isStudentRole($value['role'])) {
				unset($lessonUsers[$key]);
			}
		}
		return $lessonUsers;
	}

	public function getLessonNonUsers($returnObjects = false) {
		$subquery = "select u.*, u.user_type as basic_user_type,ul.lessons_ID as has_lesson from users u left outer join users_to_lessons ul on (ul.users_login=u.login and lessons_id=".$this -> lesson['id']." and ul.archive != 0) where u.archive = 0 and u.active=1 and u.user_type != 'administrator'";
		$result	  = eF_getTableData("($subquery) s", "s.*, s.has_lesson is null");

		$users = array();
		foreach ($result as $user) {
			$user['user_types_ID'] ? $user['role'] = $user['user_types_ID'] : $user['role'] = $user['basic_user_type'];
			$returnObjects ? $users[$user['login']] = EfrontUserFactory :: factory($user['login']) : $users[$user['login']] = $user;
		}
		return $users;
	}


	/**
	 * Check if the specified user has a 'student' role in the course
	 *
	 * @param mixed $user a login or an EfrontUser object
	 * @return boolean True if the user's role in the course is 'student'
	 * @since 3.6.1
	 * @access protected
	 */
	public function isStudentInLesson($user) {
		if ($user instanceOf EfrontUser) {
			$user = $user -> user['login'];
		}

		$roles 		 = $this -> getPossibleLessonRoles();
		$courseUsers = $this -> getUsers();
		if (in_array($user, array_keys($courseUsers)) && $roles[$courseUsers[$user]['role']] == 'student') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if the specified user has a 'professor' role in the course
	 *
	 * @param mixed $user a login or an EfrontUser object
	 * @return boolean True if the user's role in the course is 'professor'
	 * @since 3.6.1
	 * @access public
	 */
	public function isProfessorInLesson($user) {
		if ($user instanceOf EfrontUser) {
			$user = $user -> user['login'];
		}

		$roles 		 = $this -> getPossibleLessonRoles();
		$courseUsers = $this -> getUsers();
		if (in_array($user, array_keys($courseUsers)) && $roles[$courseUsers[$user]['role']] == 'professor') {
			return true;
		} else {
			return false;
		}
	}





	/**
	 * Get the possible roles for a user in a lesson. This function caches EfrontLessonUser :: getLessonsRoles() results
	 * for improved efficiency
	 *
	 * @return array the possible users' roles in the lesson
	 * @since 3.6.1
	 * @access protected
	 */
	private function getPossibleLessonRoles() {
		if (!isset($this -> roles) || !$this -> roles) {
			$this -> roles = EfrontLessonUser :: getLessonsRoles();
		}
		return $this -> roles;
	}

	/**
	 * Get lesson students according to whether they have completed the lesson or not
	 *
	 * This function returns an array with the lesson users that have completed the
	 * lesson or not
	 *
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(32);                      //32 is a lesson id
	 * $lessonUsersCompleted    = $lesson -> getUsersCompleted(true);
	 * $lessonUsersNotCompleted = $lesson -> getUsersCompleted(false);
	 * </code>
	 * The returned array keys match the users logins
	 *
	 * @param boolean $completed Whether the returned users should have completed the lesson or not
	 * @return array A 2-dimensional array with lesson users per type, or a 1-dimensional array with lesson users of the specified type
	 * @since 3.6.0
	 * @access public
	 */

	public function getUsersCompleted($completed) {
		$users = $this -> getUsers();
		foreach ($users as $key => $user) {
			if (($completed && !$user['completed']) || (!$completed && $user['completed'])) {
				unset($users[$key]);
			}
		}
		return $users;
	}


	/**
	 * Get users that don't have the lesson
	 *
	 * This function is complementary to getUsers(); it returns only those users
	 * that don't have this lesson.
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(32);                      //32 is a lesson id
	 * $lessonUsers    = $lesson -> getUsers();             //Get the lesson users
	 * $nonLessonUsers = $lesson -> getNonUsers();          //Get the users that don't have the lesson, but are eligible to
	 * </code>
	 * The returned array keys match the users logins
	 *
	 * @param boolean $type The users type
	 * @return array A list with eleigible users for this lesson
	 * @since 3.5.0
	 * @access public
	 */
	public function getNonUsers($type = false) {
		foreach ($this -> getUsers() as $key => $value) {
			$lessonUsers[$value['login']] = $value;
		}

		//$sql = "users.active = 1 and users.languages_NAME = '".$this -> lesson['languages_NAME']."' and login NOT IN ('".implode("','", array_keys($lessonUsers))."') ";
		$sql = "users.active = 1 and login NOT IN ('".implode("','", array_keys($lessonUsers))."') ";
		$type && in_array($type, EfrontUser :: basicUserTypes) ? $sql.= "and user_type == '".$type."'" : $sql.= "and user_type != 'administrator'";

		$result = eF_getTableData("users", "*", $sql);
		$nonLessonUsers = array();
		foreach ($result as $value) {
			$nonLessonUsers[$value['login']] = array('login'           => $value['login'],
                                                     'email'           => $value['email'],
                                                     'name'            => $value['name'],
                                                     'surname'         => $value['surname'],
                                                     'basic_user_type' => $value['user_type'],
                                                     'user_types_ID'   => $value['user_types_ID'],
                                                     'role'            => $value['user_types_ID'] ? $value['user_types_ID'] : $value['user_type'],
                                                     'active'          => $value['active'],
                                                     'partof'          => 0);
		}

		return $nonLessonUsers;
	}

	/**
	 * Get lesson course
	 *
	 * This function returns the courses that this lesson is part
	 * of.
	 * <br/>Example:
	 * <code>
	 * $courses = $this -> getCourses();                    //Return an array of course ids and properties
	 * $courses = $this -> getCourses(true);                //Return an array of EfrontCourse objects
	 * </code>
	 *
	 * @param boolean $returnObjects Whether to return EfrontCourse objects or a simple arrays
	 * @return array The courses list, where keys are ids and values are either arrays or EfrontCourse objects
	 * @since 3.5.0
	 * @access public
	 */
	public function getCourses($returnObjects = false) {
		$result  = eF_getTableData("courses JOIN lessons_to_courses ON courses.id = courses_ID", "courses.*", "courses.archive=0 and lessons_ID = ".$this -> lesson['id']);
		$courses = array();
		foreach ($result as $value) {
			$returnObjects ? $courses[$value['id']] = new EfrontCourse($value['id']) : $courses[$value['id']] = $value;
		}

		return $courses;
	}

	/**
	 * Verify the integrity of the lessons list and convert each one to
	 * an object, if it isn't already. The returned array has the lesson ids
	 * as keys.
	 *
	 * @param mixed $lessons An array of lesson objects or lesson ids, or a single lesson, or a single lesson object
	 * @return array The array of lesson objects, where keys are ids
	 * @since 3.6.7
	 * @access public
	 */
	public function verifyLessonsList($lessonsList) {
		is_array($lessonsList) OR $lessonsList = array($lessonsList);

		$newLessonsList = array();
		foreach ($lessonsList as $lesson) {
			($lesson instanceof EfrontLesson) OR $lesson = new EfrontLesson($lesson);
			$newLessonsList[$lesson -> lesson['id']] = $lesson;
		}
		return $newLessonsList;
	}


	/**
	 * Add users to lesson
	 *
	 * This function is used to register one or more users to the current lesson. A single login
	 * or an array of logins may be specified
	 * <br/>Example:
	 * <code>
	 * $lesson -> addUsers('joe', 'professor');         //Add the user with login 'joe' as a professor to this lesson
	 * $users = array('joe', 'mary', 'mike');
	 * $types = array('student', 'student', 'professor');
	 * $lesson -> addUsers($users, $types);             //Add the users in the array $users with roles $types
	 * </code>
	 *
	 * @param mixed $login The user login name
	 * @param mixed $role The user role for this lesson, defaults to 'student'
	 * @param boolean $confirmed If false, then the registration is set to 'pending' mode and the administration must confirm it
	 * @return boolean true if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function addUsers($users, $roles = 'student', $confirmed = true) {
		$users = EfrontUser::verifyUsersList($users);
		$users = $this -> filterOutArchivedUsers($users);
		$roles = EfrontUser::verifyRolesList($roles, sizeof($users));

		$lessonUsers   = array_keys($this -> getUsers());
		$count         = sizeof($this -> getStudentUsers());

		$usersToAddToLesson = $usersToSetRoleToLesson = array();
		
		foreach ($users as $key => $user) {
			$roleInLesson = $roles[$key];

			if ($roleInLesson != 'administrator') {
				if ($this -> lesson['max_users'] && $this -> lesson['max_users'] <= $count++ && EfrontUser::isStudentRole($roleInLesson)) {
					throw new EfrontCourseException(str_replace(array("%x", "%y", "%z"), array($this->lesson['name'], $this->lesson['max_users'], $GLOBALS['configuration']['system_email']), _MAXSEATSDEPLEATED), EfrontCourseException :: MAX_USERS_LIMIT);
				}
				if (!in_array($user, $lessonUsers)) { //added this to avoid adding existing user when admin changes his role
					$usersToAddToLesson[] 	  = array('login' => $user, 'role' => $roleInLesson, 'confirmed' => $confirmed);
				} else {
					$usersToSetRoleToLesson[] = array('login' => $user, 'role' => $roleInLesson, 'confirmed' => $confirmed);
				}
			}
		}

		$this -> addUsersToLesson($usersToAddToLesson);
		$this -> setUserRolesInLesson($usersToSetRoleToLesson);

		$this -> users = false;					//Reset users cache
		//return $this -> getUsers();
	}

	private function filterOutArchivedUsers($users) {
		$archivedUsers = eF_getTableDataFlat("users", "login", "archive != 0");
		foreach ($users as $key => $value) {
			if (in_array($value, $archivedUsers['login'])) {
				unset($users[$key]);
			}
		}
		return $users;
	}

	public static function convertLessonObjectsToArrays($lessonObjects) {
		foreach ($lessonObjects as $key => $value) {
			$lessonObjects[$key] = $value -> lesson;
		}
		return $lessonObjects;
	}

	private function getArchivedUsers() {
		$result = eF_getTableDataFlat("users_to_lessons", "users_LOGIN", "archive!=0 and lessons_ID=".$this->lesson['id']);
		if (empty($result)) {
			return array();
		} else {
			return $result['users_LOGIN'];
		}
	}
	/**
	 * Add the user in the lesson having the specified role
	 *
	 * @param string $user The user's login
	 * @param mixed $roleInLesson the user's role in the lesson
	 * @since 3.6.1
	 * @access protected
	 */
	private function addUsersToLesson($usersData) {
		$autoAssignedProjects = $this -> getAutoAssignProjects();
		$archivedLessonUsers  = $this -> getArchivedUsers();
		$newUsers			  = array();

		$options 	= unserialize($this -> lesson['options']);
		$positions	= $options['default_positions'];
		foreach ($usersData as $value) {
			if (in_array($value['login'], $archivedLessonUsers)) {
				//Update only fields not related to progress
				$updateFields = array('active'		 => 1,
										'archive'        => 0,
										'from_timestamp' => $value['confirmed'] ? time() : 0,
										'user_type'	     => $value['role'],
										'access_counter' => 0);
				eF_updateTableData("users_to_lessons", $updateFields, "users_LOGIN='".$value['login']."' and lessons_ID=".$this -> lesson['id']);
			} else {
				$newUsers[] = $value['login'];
				$fields[]   = array('users_LOGIN'	 => $value['login'],
									'lessons_ID'	 => $this -> lesson['id'],
									'active'		 => 1,
									'archive'        => 0,
									'from_timestamp' => $value['confirmed'] ? time() : 0,
									'user_type'	     => $value['role'],
									'positions'		 => '',
									'done_content'   => '',
									'current_unit'	 => 0,
									'completed'		 => 0,
									'score'			 => 0,
									'comments'		 => '',
									'positions'		 => $positions,
									'to_timestamp'	 => 0);
			}
		}

		if (!empty($newUsers)) {
			eF_insertTableDataMultiple("users_to_lessons", $fields);
			foreach ($autoAssignedProjects as $project) {
				$project -> addUsers($newUsers);
			}
		}

		if (!defined(_DISABLE_EVENTS) || _DISABLE_EVENTS !== true) {
			foreach ($usersData as $value) {
				$event = array("type" 		  => EfrontUser::isStudentRole($value['role']) ? EfrontEvent::LESSON_ACQUISITION_AS_STUDENT : EfrontEvent::LESSON_ACQUISITION_AS_PROFESSOR,
						   "users_LOGIN"  => $value['login'],
						   "lessons_ID"   => $this -> lesson['id'],
						   "lessons_name" => $this -> lesson['name']);
				EfrontEvent::triggerEvent($event);

				if (EfrontUser::isStudentRole($value['role'])) {
					$event = array("type" 		  => (-1) * EfrontEvent::LESSON_COMPLETION,
							   "users_LOGIN"  => $value['login'],
							   "lessons_ID"   => $this -> lesson['id'],
							   "lessons_name" => $this -> lesson['name'],
								"replace" => true,
								"create_negative" => false);
					EfrontEvent::triggerEvent($event);
				}				
			}
		}


	}

	/**
	 * Set the user's role in the lesson
	 *
	 * @param $user The user to set the role for
	 * @param $roleInLesson The role in the lesson
	 * @since 3.6.1
	 * @access protected
	 */
	private function setUserRolesInLesson($usersData) {
		$lessonUsers = $this -> getUsers();
		foreach ($usersData as $value) {
			if ($lessonUsers[$value['login']]['role'] != $value['role']) {
				$fields = array('archive' 		 => 0,
								'user_type' 	 => $value['role'],
								'from_timestamp' => $value['confirmed'] ? time() : 0);
				eF_updateTableData("users_to_lessons", $fields, "users_LOGIN='".$value['login']."' and lessons_ID=".$this -> lesson['id']);
			}
		}

	}

	private function getAutoAssignProjects() {
		$autoAssignProjects = array();
		foreach ($this -> getProjects() as $id => $project) {
			if ($project['auto_assign']) {
				$autoAssignProjects[$id] = new EfrontProject($id);
			}
		}
		return $autoAssignProjects;
	}

	/**
	 * Remove user from lesson
	 *
	 * This function is used to remove a user from the current lesson. A single login
	 * or an array of logins may be specified
	 * <br/>Example:
	 * <code>
	 * $lesson -> removeUsers('jdoe');          //Remove the user with login 'jdoe'
	 * </code>
	 *
	 * @param string $login The user login name
	 * @return boolean true if everything is ok
	 * @since 3.5.0
	 * @access public
	 * @todo remove him from projects list
	 */
	public function removeUsers($users) {
		$users = EfrontUser::verifyUsersList($users);
		$this -> deleteUserTests($users);
		$this -> sendNotificationsRemoveLessonUsers($users);

		foreach ($users as $user) {
			eF_deleteTableData("users_to_lessons", "users_LOGIN='$user' and lessons_ID=".$this -> lesson['id']);
			$cacheKey = "user_lesson_status:lesson:".$this -> lesson['id']."user:".$user;
			EfrontCache::getInstance()->deleteCache($cacheKey);
		}

		$this -> users = false;					//Reset users cache
		return $this -> getUsers();
	}

	private function deleteUserTests($users) {
		$lessonTests = $this -> getTests(false);
		foreach ($users as $user) {
			if (sizeof($lessonTests) > 0) {
				eF_deleteTableData("completed_tests", "users_LOGIN='$user' and tests_ID in (".implode(",", $lessonTests).")");
			}
		}
	}

	private function sendNotificationsRemoveLessonUsers($users) {
		foreach ($users as $user) {
			EfrontEvent::triggerEvent(array("type" 		   => EfrontEvent::LESSON_REMOVAL,
											"users_LOGIN"  => $user,
											"lessons_ID"   => $this -> lesson['id'],
											"lessons_name" => $this -> lesson['name']));
		}
	}

	/**
	 * Archive user in lesson
	 *
	 * This function is used to archive a user in the current lesson. It's similar to removing,
	 * only the user relation to the tracking data is not lost but retained
	 * <br/>Example:
	 * <code>
	 * $lesson -> archiveLessonUsers('jdoe');   //Archive user with login 'jdoe'
	 * </code>
	 *
	 * @param array $user the user login to archive
	 * @return array The new list of lesson users
	 * @since 3.5.0
	 * @access public
	 */
	public function archiveLessonUsers($users) {
		$users = EfrontUser::verifyUsersList($users);
		$this -> sendNotificationsRemoveLessonUsers($users);

		foreach ($users as $user) {
			eF_updateTableData("users_to_lessons", array("archive" => time()), "users_LOGIN='$user' and lessons_ID=".$this -> lesson['id']);
			$cacheKey = "user_lesson_status:lesson:".$this -> lesson['id']."user:".$user;
			EfrontCache::getInstance()->deleteCache($cacheKey);
		}

		$this -> users = false;					//Reset users cache
		return $this -> getUsers();
	}


	public static function countLessonsOccurencesInCoursesForAllUsers() {

		$result = eF_getTableData("lessons_to_courses", "lessons_ID, courses_ID");
		foreach ($result as $value) {
			$lessonsCourses[$value['lessons_ID']][] = $value['courses_ID'];
		}

		$result = eF_getTableData("users u, users_to_courses uc", "users_LOGIN, courses_ID", "u.archive=0 and uc.archive=0 and u.login=uc.users_LOGIN");
		foreach ($result as $value) {
			$usersCourses[$value['users_LOGIN']][] = $value['courses_ID'];
		}

		foreach ($lessonsCourses as $lessonId => $lessonCourses) {
			foreach ($usersCourses as $login => $userCourses) {
				if (sizeof($commonCourses = array_intersect($lessonCourses, $userCourses)) > 0) {
					$userLessonCourses[$login][$lessonId] = sizeof($commonCourses);
				}
			}
		}

		return $userLessonCourses;
	}
	public static function countLessonsOccurencesInCoursesForUser($user) {
		if ($user instanceOf EfrontUser) {
			$user = $user -> user['login'];
		}

		$result = eF_getTableData("lessons_to_courses", "lessons_ID, courses_ID");
		foreach ($result as $value) {
			$lessonsCourses[$value['lessons_ID']][] = $value['courses_ID'];
		}
		$result = eF_getTableData("users_to_courses uc", "users_LOGIN, courses_ID", "uc.archive=0 and users_LOGIN='".$user."'");
		foreach ($result as $value) {
			$usersCourses[$value['users_LOGIN']][] = $value['courses_ID'];
		}
		foreach ($lessonsCourses as $lessonId => $lessonCourses) {
			foreach ($usersCourses as $login => $userCourses) {
				if (sizeof($commonCourses = array_intersect($lessonCourses, $userCourses)) > 0) {
					$userLessonCourses[$login][$lessonId] = sizeof($commonCourses);
				}
			}
		}
		return $userLessonCourses;
	}


	/** Check if user is active in this lesson
	 *
	 * This function will return true if the user can normally access this lesson, or
	 * false if he/she needs registration confirmation by the admin or supervisor
	 *
	 * @param mixed $login Either the user login, or an EfrontLessonUser object
	 * @since 3.6.7
	 * @access public
	 */
	public function isUserActiveInLesson($login) {
		$login = EfrontUser::convertArgumentToUserLogin($login);
		$result = eF_getTableData("users_to_lessons", "from_timestamp", "archive = 0 and users_LOGIN='$login' and lessons_ID=".$this -> lesson['id']);
		if (empty($result)) {
			throw new EfrontUserException(_THEUSERDOESNOTHAVETHISLESSON.': '.$this -> lesson['id'], EfrontUserException::USER_NOT_HAVE_LESSON);
		} else if ($result[0]['from_timestamp'] > 0) {
			return true;
		} else  {
			return false;
		}
	}

	/**
	 * Confirm user registration
	 *
	 * This function is used to set the specified user's status for the current lesson
	 * to 'available', if it was previously set to 'pending'
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(15);
	 * $lesson -> confirm('jdoe');
	 * </code>
	 *
	 * @param mixed $login Either the user login, or an EfrontLessonUser object
	 * @since 3.5.2
	 * @access public
	 */
	public function confirm($login) {
		$login = $this -> convertArgumentToUserLogin($login);

		eF_updateTableData("users_to_lessons", array("from_timestamp" => time()), "users_LOGIN='".$login."' and lessons_ID=".$this -> lesson['id']." and from_timestamp=0");
		$cacheKey = "user_lesson_status:lesson:".$this -> lesson['id']."user:".$login;
		EfrontCache::getInstance()->deleteCache($cacheKey);
	}

	public function unConfirm($login) {
		$login = $this -> convertArgumentToUserLogin($login);

		eF_updateTableData("users_to_lessons", array("from_timestamp" => 0), "users_LOGIN='".$login."' and lessons_ID=".$this -> lesson['id']);
		$cacheKey = "user_lesson_status:lesson:".$this -> lesson['id']."user:".$login;
		EfrontCache::getInstance()->deleteCache($cacheKey);
	}

	private function convertArgumentToUserLogin($login) {
		if ($login instanceof EfrontLessonUser) {
			$login = $login -> user['login'];
		} else if (!eF_checkParameter($login, 'login')) {
			throw new EfrontUserException(_INVALIDLOGIN, EfrontUserException::INVALID_LOGIN);
		}

		return $login;
	}


	/**
	 * Set user roles in lesson
	 *
	 * This function sets the role for the specified user
	 * <br/>Example:
	 * <code>
	 * $lesson -> addUsers('jdoe', 'student');              //Added the user 'jdoe' in the lesson, having the role 'student'
	 * $lesson -> setRoles('jdoe', 'professor');                //Updated jdoe's role to be 'professor'
	 * </code>
	 * Multiple values can be set if arguments are arrays
	 *
	 * @param mixed $login The user login name
	 * @param mixed $role The user role for this lesson
	 * @return boolean true if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function setRoles($users, $roles) {
		$users = EfrontUser::verifyUsersList($users);
		$roles = EfrontUser::verifyRolesList($roles, sizeof($users));

		foreach ($users as $key => $value) {
			eF_updateTableData("users_to_lessons", array('archive' => 0, 'user_type' => $roles[$key]), "users_LOGIN='".$value."' and lessons_ID=".$this -> lesson['id']);
			//$cacheKey = "user_lesson_status:lesson:".$this -> lesson['id']."user:".$value;
			//EfrontCache::getInstance()->deleteCache($cacheKey);
		}
	}

	/**
	 * Get the user role in this lesson
	 *
	 * This function gets the role of the specified user in the specified lesson
	 * <br>Example:
	 * <code>
	 * $lesson -> getRole('jdoe');      //Return the user's role, e.g. 'professor', or, if it's a custom role, it's id, e.g. '5'
	 * </code>
	 *
	 * @param string $login The user's login
	 * @return mixed The user's role in lesson
	 * @since 3.5.0
	 * @access public
	 */
	public function getRole($login) {
		$lessonUsers = $this -> getUsers();
		if (in_array($login, array_keys($lessonUsers))) {
			return $lessonUsers[$login]['role'];
		} else {
			throw new EfrontUserException(_USERDOESNOTHAVETHISLESSON.": ".$lesson, EfrontUserException :: USER_NOT_HAVE_LESSON);
		}
	}

	/**
	 * Get the tests of the lesson
	 *
	 * This returns the tests of the lesson
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(12);
	 * $tests = $lesson -> getTests(true);
	 * </code>
	 * @param boolean returnObjects. Flag to indicate whether to return a list of objects or a list of ids
	 * @param boolean $onlyActive Whether to return only active tests or all
	 * @return array the lesson's tests (either an array of ids or an array of EfrontTest objects)
	 * @since 3.5.0
	 * @access public
	 */
	public function getTests($returnObjects = false, $onlyActive = false) {
		$tests = array();
		if (!$onlyActive) {
			$test_data = eF_getTableData("tests t, content c", "t.*", "t.lessons_ID = ".$this -> lesson['id']." and t.content_id = c.id and c.ctg_type='tests' and c.lessons_ID=".$this -> lesson['id']);
		} else {
			$test_data = eF_getTableData("tests t, content c", "t.*", "t.active = 1 and c.active = 1 and t.lessons_ID = ".$this -> lesson['id']." and t.content_id = c.id and c.ctg_type='tests' and c.lessons_ID=".$this -> lesson['id']);
		}
		foreach ($test_data as $t){
			if (!$returnObjects){
				$tests[] = $t['id'];
			} else{
				$test = new EfrontTest($t['id']);
				$tests[$t['id']] = $test;
			}
		}
		return $tests;
	}


	/**
	 * Get the tests and the feedbacks of the lesson
	 *
	 * This returns the tests and the feedbacks of the lesson
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(12);
	 * $tests = $lesson -> getTestsAndFeedbacks(true);
	 * </code>
	 * @param boolean returnObjects. Flag to indicate whether to return a list of objects or a list of ids
	 * @param boolean $onlyActive Whether to return only active tests/feedbacks or all
	 * @return array the lesson's tests/feedbacks (either an array of ids or an array of EfrontTest objects)
	 * @since 3.5.0
	 * @access public
	 */
	public function getTestsAndFeedbacks($returnObjects = false, $onlyActive = false) {
		$tests = array();
		if (!$onlyActive) {
			$test_data = eF_getTableData("tests t, content c", "t.*", "t.lessons_ID = ".$this -> lesson['id']." and t.content_id = c.id and (c.ctg_type='tests' or c.ctg_type='feedback') and c.lessons_ID=".$this -> lesson['id']);
		} else {
			$test_data = eF_getTableData("tests t, content c", "t.*", "t.active = 1 and c.active = 1 and t.lessons_ID = ".$this -> lesson['id']." and t.content_id = c.id and (c.ctg_type='tests' or c.ctg_type='feedback') and c.lessons_ID=".$this -> lesson['id']);
		}
		foreach ($test_data as $t){
			if (!$returnObjects){
				$tests[] = $t['id'];
			} else{
				$test = new EfrontTest($t['id']);
				$tests[$t['id']] = $test;
			}
		}
		return $tests;
	}

	/**
	 * Get the scorm tests of the lesson
	 *
	 * This returns the tests of the lesson
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(12);
	 * $tests = $lesson -> getScormTests();
	 * </code>
	 *
	 * @return array the lesson's scorm tests (an array of ids)
	 * @since 3.5.0
	 * @access public
	 * @static
	 */
	public function getScormTests() {
		$tests = array();
		$scorm_data = eF_getTableData("content", "id", "lessons_ID=".$this -> lesson['id']." and ctg_type='scorm_test'");
		foreach ($scorm_data as $data){
			$tests[] = $data['id'];
		}
		return $tests;
	}



	/**
	 * Get the questions of the lesson
	 *
	 * This returns the questions of the lesson
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(12);
	 * $questions = $lesson -> getQuestions(true);
	 * </code>
	 *
	 * @param bool returnObjects. Flag to indicate whether to return a list of objects or a list of ids
	 * @return array the lesson's questions (either an array of ids or an array of Question objects)
	 * @since 3.5.0
	 * @access public
	 * @static
	 */
	public function getQuestions($returnObjects = false){
		$questions = array();
		$result    = eF_getTableData("questions", "*", "lessons_ID=".$this -> lesson['id']);
		if (sizeof($result) > 0) {
			foreach ($result as $value) {
				$returnObjects ? $questions[$value['id']] = QuestionFactory :: factory($value) : $questions[$value['id']] = $value;
			}
		}

		return $questions;

	}

	public function getLessonStatusForUsers($constraints = array(), $onlyContent = false) {
		!empty($constraints) OR $constraints = array('archive' => false, 'active' => true);
		$constraints['return_objects'] = false;
		$lessonUsers = $this -> getLessonUsers($constraints);

		$units		 = array();
		$contentTree = new EfrontContentTree($this -> lesson['id']);
		foreach ($iterator = new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($contentTree -> tree), RecursiveIteratorIterator :: SELF_FIRST))) as $key => $value) {
			$units[] = $key;
		}

		$usersTimes = $this -> getLessonTimesForUsers();		
		foreach($this->getUsersActiveTimeInLesson() as $key => $value) {
			$activeUsersTimes[$key] = EfrontTimes::formatTimeForReporting($value);
		}		

		foreach ($lessonUsers as $key => $user) {
			if ((!$user['user_types_ID'] && $user['role'] != $user['user_type']) || ($user['user_types_ID'] && $user['role'] != $user['user_types_ID'])) {
				$user['different_role'] = 1;
			}

			$lessonUsers[$key]['overall_progress'] = $this -> getLessonOverallProgressForUser($user, $units);
			if (!$onlyContent) {
				$lessonUsers[$key]['project_status']   = $this -> getLessonProjectsStatusForUser($user);
				$lessonUsers[$key]['test_status']	   = $this -> getLessonTestsStatusForUser($user);
				$lessonUsers[$key]['time_in_lesson']   = $usersTimes[$user['login']];
				$lessonUsers[$key]['active_time_in_lesson']   = $activeUsersTimes[$user['login']];
			}
		}
	
		return $lessonUsers;

	}

	public function getLessonTimesForUsers($interval = array()) {
		$usersTimes   = array();
		$timesReport = new EfrontTimes($interval);
		$result		 = $timesReport -> getLessonSessionTimesForUsers($this -> lesson['id']);
		foreach ($result as $value) {
			$usersTimes[$value['users_LOGIN']]   = $timesReport -> formatTimeForReporting($value['time']);
		}

		return $usersTimes;
	}
	
	private function getLessonOverallProgressForUser($user, $units) {
		$totalUnits 	= sizeof($units);
		$completedUnits = 0;
		if ($doneContent = unserialize($user['done_content'])) {
			$completedUnits = sizeof(array_intersect($units, $doneContent));
		}

		if ($totalUnits) {
			$completedUnitsPercentage = round(100 * $completedUnits/$totalUnits, 2);
			return array('total' 	  => $totalUnits,
						 'completed'  => $completedUnits,
						 'percentage' => $completedUnitsPercentage);
		} else {
			return array('total' 	  => 0,
						 'completed'  => 0,
						 'percentage' => 0);
		}

	}

	private function getLessonTestsStatusForUser($user) {
		$completedTests = $meanTestScore = 0;

		$tests 		= $this -> getTests(true, true);
		$totalTests = sizeof($tests);

		$result 	= eF_getTableData("completed_tests ct, tests t", "ct.tests_ID, ct.score, t.keep_best", "t.id=ct.tests_ID and ct.status != 'deleted' and ct.status != 'incomplete' and ct.users_LOGIN='".$user['login']."' and t.lessons_ID=".$this -> lesson['id']);
		foreach ($result as $value) {
			if (in_array($value['tests_ID'], array_keys($tests))) {
				if ($value['keep_best']) {
					isset($scores[$value['tests_ID']]) OR $scores[$value['tests_ID']] = $value['score'];
					$scores[$value['tests_ID']] = max($scores[$value['tests_ID']], $value['score']);
				} else if ($value['archive'] == 0) {
					$scores[$value['tests_ID']] = $value['score'];
				}
			}
		}
		$meanTestScore  = array_sum($scores);
		$completedTests = sizeof($scores);

		$scormTests = $this -> getLessonScormTestsStatusForUser($user);

		$result = eF_getTableData("content", "id", "ctg_type='scorm_test' and active=1 and lessons_ID=".$this -> lesson['id']);
		$totalTests += sizeof($result);

		foreach ($scormTests as $value) {
			if (is_numeric($value)) {
				$meanTestScore += $value;
				$completedTests++;
			}
		}

		if ($totalTests) {
			$completedTestsPercentage = round(100 * $completedTests/$totalTests, 2);
			$meanTestScore 			  = round($meanTestScore/$completedTests, 2);
			return array('total' 	  => $totalTests,
						 'completed'  => $completedTests,
						 'percentage' => $completedTestsPercentage,
						 'mean_score' => $meanTestScore);
		} else {
			return array();
		}
	}

	private function getLessonScormTestsStatusForUser($user) {
		$tests = array();
		$usersDoneScormTests = eF_getTableData("scorm_data sd left outer join content c on c.id=sd.content_ID",
											   "c.id, c.ctg_type, sd.masteryscore, sd.lesson_status, sd.score, sd.minscore, sd.maxscore, sd.users_LOGIN",
											   "c.ctg_type = 'scorm_test'and sd.lesson_status != 'incomplete' and sd.users_LOGIN = '".$user['login']."' and c.lessons_ID = ".$this -> lesson['id']);

		foreach ($usersDoneScormTests as $doneScormTest) {
			if (is_numeric($doneScormTest['minscore']) && is_numeric($doneScormTest['maxscore'])) {
				$doneScormTest['score'] = 100 * $doneScormTest['score'] / ($doneScormTest['minscore'] + $doneScormTest['maxscore']);
			} else {
				$doneScormTest['score'] = $doneScormTest['score'];
			}

			$tests[$doneScormTest['id']] = $doneScormTest['score'];
		}

		return $tests;
	}

	private function getLessonProjectsStatusForUser($user) {
		$completedProjects = $meanProjectScore = 0;

		$projects 	   = $this -> getProjects(true, $user['login']);
		$totalProjects = sizeof($projects);
		foreach ($projects as $project) {
			if ($project -> project['grade'] || $project -> project['grade'] === 0) {
				$completedProjects++;
				$meanProjectScore += $project -> project['grade'];
			}
		}
		if ($totalProjects) {
			$completedProjectsPercentage = round(100 * $completedProjects/$totalProjects, 2);
			$meanProjectScore 			 = round($meanProjectScore/$completedProjects, 2);
			return array('total' 	  => $totalProjects,
						 'completed'  => $completedProjects,
						 'percentage' => $completedProjectsPercentage,
						 'mean_score' => $meanProjectScore);
		} else {
			return array();
		}
	}

	/**
	 * Get lesson information
	 *
	 * This function returns the lesson information in an array
	 * with attributes: 'general_description', 'assessment',
	 * 'objectives', 'lesson_topics', 'resources', 'other_info',
	 * as well as other information, including professors, projects
	 * etc.
	 * If a user is specified, the information is customized on this
	 * user.
	 *
	 * <br/>Example:
	 * <code>
	 * $info = $lesson -> getInformation();         //Get lesson information
	 * $info = $lesson -> getInformation('jdoe');   //Get lesson information, customizable for user 'jdoe'
	 * </code>
	 *
	 * @param string $user The user login to customize lesson information for
	 * @return array The lesson information
	 * @since 3.5.0
	 * @access public
	 */
	public function getInformation($user = false, $information = false) {

		$lessonContent = new EFrontContentTree($this -> lesson['id'], array('id', 'previous_content_ID', 'parent_content_ID', 'active', 'publish', 'ctg_type'));
		foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('active' => 1, 'publish' => 1)) as $key => $value) {
			switch($value['ctg_type']) {
				case 'tests': case 'scorm_test': $testIds[$key]    = $key; break;
				case 'theory': case 'scorm':     $theoryIds[$key]  = $key; break;
				case 'examples':                 $exampleIds[$key] = $key; break;
				default: break;
			}
		}

		/*
		 $testIds = array();
		 foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('active' => 1, 'publish' => 1, 'ctg_type' => 'tests')) as $key => $value) {
		 $testIds[$key] = $key;            //Count tests
		 }
		 foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('active' => 1, 'ctg_type' => 'scorm_test')) as $key => $value) {
		 $testIds[$key] = $key;            //Count tests
		 }

		 $theoryIds = array();
		 foreach (new EfrontTheoryFilterIterator(new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST)))) as $key => $value) {
		 $theoryIds[$key] = $key;            //Count theory
		 }
		 //        foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('ctg_type' => 'scorm', 'active' => 1)) as $key => $value) {
		 //            $theoryIds[$key] = $key;            //Count theory
		 //        }

		 $exampleIds = array();
		 foreach (new EfrontVisitableFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('ctg_type' => 'examples'))) as $key => $value) {
		 $exampleIds[$key] = $key;            //Count examples
		 }
		 */

		$user ? $projects = $this -> getProjects(false, $user, false) : $projects = $this -> getProjects();

		$direction = $this -> getDirection();
		//$info['students']      = $this -> getUsers('student');		//commented out periklis for performance/memory reasons
		$info['professors']    = $this -> getUsers('professor');
		
		if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
			require_once 'module_hcd_tools.php';
			$currentBranch = new EfrontBranch($_SESSION['s_current_branch']);
			$branchTreeUsers = array_keys($currentBranch->getBranchTreeUsers());
			foreach ($info['students'] as $key => $value) {
				if (!in_array($value['login'], $branchTreeUsers)) {
					unset($info['students'][$key]);
				}
			}
			foreach ($info['professors'] as $key => $value) {
				if (!in_array($value['login'], $branchTreeUsers)) {
					unset($info['professors'][$key]);
				}
			}
		}

		$info['tests']         = sizeof($testIds);
		$info['theory']        = sizeof($theoryIds);
		$info['examples']      = sizeof($exampleIds);
		$info['content']       = $info['examples'] + $info['theory'];
		$info['projects']      = sizeof($projects);
		$info['direction']     = $direction['name'];
		$info['active']        = $this -> lesson['active'];
		$info['active_string'] = $this -> lesson['active'] == 1 ? _YES : _NO;
		$info['price']         = $this -> lesson['price'];
		$info['price_string']  = $this -> lesson['price_string'];
		$info['language']      = $this -> lesson['languages_NAME'];
		$info['from_timestamp']= $this -> lesson['from_timestamp'];
		$info['to_timestamp']  = $this -> lesson['to_timestamp'];
		$info['created']  	   = $this -> lesson['created'];
		//$info['shift']         = $this -> lesson['shift'];
		if ($info['professors']) {
			foreach ($info['professors'] as $value) {
				$professorsString[] = $value['name'].' '.$value['surname'];
			}
			$info['professors_string'] = implode(", ", $professorsString);
		}

		if ($this -> lesson['info']) {
			$order = array("general_description", "objectives", "assessment", "lesson_topics", "resources", "other_info", "learning_method");  // for displaying fiels sorted
			$infoSorted = array();
			$unserialized = unserialize($this -> lesson['info']);
			foreach ($order as $value) {
				if ($unserialized[$value] != "") {
					$infoSorted[$value] = $unserialized[$value];
				}
			}

			!is_array($this -> lesson['info']) && unserialize($this -> lesson['info']) !== false ? $info = array_merge($infoSorted, $info) : $info = array_merge($infoSorted, $info);
		}

		return $info;
	}

	/**
	 * Get lesson statisticsinformation
	 *
	 * This function returns the lesson information in an array
	 * with attributes: 'general_description', 'assessment',
	 * 'objectives', 'lesson_topics', 'resources', 'other_info',
	 * as well as other information, including professors, projects
	 * etc.
	 * If a user is specified, the information is customized on this
	 * user.
	 *
	 * <br/>Example:
	 * <code>
	 * $info = $lesson -> getInformation();         //Get lesson information
	 * $info = $lesson -> getInformation('jdoe');   //Get lesson information, customizable for user 'jdoe'
	 * </code>
	 *
	 * @param string $user The user login to customize lesson information for
	 * @return array The lesson information
	 * @since 3.5.0
	 * @access public
	 */
	public function getStatisticInformation($user = false) {

		$lessonContent = new EFrontContentTree($this -> lesson['id']);

		$testIds = array();
		foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('active' => 1, 'publish' => 1, 'ctg_type' => 'tests')) as $key => $value) {
			$testIds[$key] = $key;            //Count tests
		}
		foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('active' => 1, 'ctg_type' => 'scorm_test')) as $key => $value) {
			$testIds[$key] = $key;            //Count scorm tests
		}

		$theoryIds = array();
		foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('ctg_type' => 'theory', 'active' => 1)) as $key => $value) {
			$theoryIds[$key] = $key;            //Count theory
		}
		foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('ctg_type' => 'scorm', 'active' => 1)) as $key => $value) {
			$theoryIds[$key] = $key;            //Count scorm content
		}

		$exampleIds = array();
		foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST), array('ctg_type' => 'examples', 'active' => 1)) as $key => $value) {
			$exampleIds[$key] = $key;            //Count examples
		}

		if (!$user) {
			$projects = $this -> getProjects();
			$lessonComments = eF_getTableData("comments, content", "comments.*",
                                              "comments.content_ID = content.id and content.lessons_ID=".$this->lesson['id']);
			$lessonMessages = eF_getTableData("f_messages, f_topics, f_forums", "f_messages.*",
                                              "f_messages.f_topics_ID = f_topics.id and f_topics.f_forums_ID = f_forums.id and f_forums.lessons_ID=".$this->lesson['id']);
		} else {
			$projects = $this -> getProjects(false, $user, false);
			$lessonComments = eF_getTableData("comments, content", "comments.*",
                                              "comments.users_LOGIN='".$user."' and comments.content_ID = content.id and content.lessons_ID=".$this->lesson['id']);
			$lessonMessages = eF_getTableData("f_messages, f_topics, f_forums", "f_messages.*",
                                              "f_messages.users_LOGIN='".$user."' and f_messages.f_topics_ID = f_topics.id and f_topics.f_forums_ID = f_forums.id and f_forums.lessons_ID=".$this->lesson['id']);
		}

		$direction   = $this -> getDirection();
		$languages   = EfrontSystem :: getLanguages(true);
		$info['students']      = $this -> getUsers('student');
		$info['professors']    = $this -> getUsers('professor');
		$info['tests']         = sizeof($testIds);
		$info['theory']        = sizeof($theoryIds);
		$info['examples']      = sizeof($exampleIds);
		$info['content']       = $info['examples'] + $info['theory'];
		$info['projects']      = sizeof($projects);
		$info['comments']      = sizeof($lessonComments);
		$info['messages']      = sizeof($lessonMessages);
		$info['direction']     = $direction['name'];
		$info['active']        = $this -> lesson['active'];
		$info['active_string'] = $this -> lesson['active'] == 1 ? _YES : _NO;
		$info['price']         = $this -> lesson['price'];
		$info['price_string']  = $this -> lesson['price_string'];
		$info['language']      = $languages[$this -> lesson['languages_NAME']];
		$info['created']       = $languages[$this -> lesson['created']];

		if ($this -> lesson['info']) {
			if (unserialize($this -> lesson['info']) !== false) {
				$storedInfo = unserialize($this -> lesson['info']);
				unset($storedInfo['professors']);			//Due to an old bug, serialized information may contain professors as well. So, we must remove them
				$info = array_merge($info, $storedInfo);
			} else if (is_array($this -> lesson['info'])) {
				$info = array_merge($info, $this -> lesson['info']);
			}
		}
		return $info;
	}


	/**
	 * Set lesson information
	 *
	 * This function sets the lesson information to the designated
	 * value. If $info is not set, the lesson information is erased
	 * <br/>Example:
	 * <code>
	 * $info = array('general_description' => 'Hello world');               //Set the new lesson information, erasing any old information
	 * $lesson -> setInformation($info);
	 * $info = $lesson -> getInformation();                                 //Set new information, keeping old information at the same time
	 * $info['assessment'] = 'Goodbye cruel world';
	 * $lesson -> setInformation($info);
	 * $lesson -> setInformation();                                         //Erase information
	 * </code>
	 *
	 * @param array $info The lesson information
	 * @return boolean True if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function setInformation($info = false) {
		if ($info) {
			$info = serialize($info);
			$this -> lesson['info'] = $info;
			eF_updateTableData("lessons", array("info" => $info), "id=".$this -> lesson['id']);
		} else {
			$this -> lesson['info'] = '';
			eF_updateTableData("lessons", array("info" => ""), "id=".$this -> lesson['id']);
		}

		return true;
	}



	/**
	 * Get lesson units
	 *
	 * This function returns a list of the units that
	 * belong to this lesson.
	 * <br>Example:
	 * <code>
	 * $lesson = new EfrontLesson(13);			//Instantiate lesson object
	 * $units = $lesson -> getUnits();			//Return a list of ids that represent the lesson units
	 * </code>
	 *
	 * @return array The ids of the lesson content units
	 * @since 3.5.2
	 * @access public
	 */
	public function getUnits() {
		$contentTree = new EfrontContentTree($this->lesson['id']);
		foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($contentTree -> tree), RecursiveIteratorIterator :: SELF_FIRST)) as $key => $value) {
			$units[$key] = $key;
		}

		return $units;
	}

	/**
	 * Initialize lesson
	 *
	 * This function is used to initialize specific aspects of the current lesson
	 * These aspects can be the lesson content, announcements, users, glossary etc
	 * specified in the $deleteEntities array.
	 * <br/>Example:
	 * <code>
	 * try {
	 *   $lesson = new EfrontLesson(32);                     //32 is the lesson id
	 *  $lesson -> initialize(array('content', 'glossary', 'rules'));           //Erase all content, glossary terms and content rules
	 * } catch (Exception $e) {
	 *   echo $e -> getMessage();
	 * }
	 * </code><br/>
	 * If $deleteEntities is 'all', then all lesson aspects are reset
	 *
	 * @param mixed $deleteEntities Eiterh an array with lesson aspects to initialize, or 'all' which equals to 'reset everything'
	 * @return boolean True if everything is ok
	 * @since 3.5.0
	 * @access public
	 */
	public function initialize($deleteEntities) {
		$possibleEntities = array('content',
                                  'tests',
                                  'questions',
                                  'rules',
        						  'conditions',
                                  'comments',
                                  'users',
                                  'news',
        	 					  'files',
                                  'calendar',
                                  'glossary',
        	 					  'tracking',
                                  'scheduling',
                                  'surveys',
        						  'events',
        						  'modules',
        						  'projects');

		if ($deleteEntities == 'all') {
			$deleteEntities = $possibleEntities;
		}

		$content = eF_getTableDataFlat("content", "*", "lessons_ID=".$this -> lesson['id']);                    //Get the lesson units
		sizeof($content['id']) > 0 ? $content_list = implode(",", $content['id']) : $content_list = array();    //Create list of content ids, will come in handy later

		$commonFolderLessons = eF_getTableData("lessons", "id", "share_folder=".$this -> lesson['id']);

		foreach ($deleteEntities as $value) {
			switch ($value) {
				case 'tests':
					$lessonTests = $this -> getTests(true);
					foreach ($lessonTests as $id => $test) {
						$test -> delete();
					}
					break;
				case 'questions':
					$lessonQuestions = $this -> getQuestions(true);
					foreach ($lessonQuestions as $id => $question) {
						$question -> delete();
					}
					break;
				case 'rules':
					$content = new EfrontContentTree($this -> lesson['id']);
					$contentRules = $content -> getRules();
					$content -> deleteRules(array_keys($contentRules));
					break;
				case 'conditions':
					$lessonConditions = $this -> getConditions();
					$this -> deleteConditions(array_keys($lessonConditions));
					break;
				case 'comments':
					$content = new EfrontContentTree($this -> lesson['id']);
					$content -> deleteComments(array_keys($content -> getComments()));	//Delete all comments
					break;
				case 'content':
					$content = new EfrontContentTree($this -> lesson['id']);
					foreach (new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($content -> tree), RecursiveIteratorIterator :: SELF_FIRST)) as $key => $unit) {
						$unit -> delete();
					}

					break;

				case 'users':
					$lessonUsers = $this -> getUsers('student');
					$this -> removeUsers(array_keys($lessonUsers));
					break;
				case 'news':

					$lessonNews = news :: getNews($this -> lesson['id']);//$this -> getNews();
					foreach ($lessonNews as $value) {
						$value = new news($value);
						$value -> delete();
					}
					break;
				case 'files':
					//Only delete files if this lesson is not sharing its folder
					if (!$this -> lesson['share_folder'] && empty($commonFolderLessons)) {
						$directory = new EfrontDirectory($this -> directory);
						$directory -> delete();
						mkdir($this -> directory, 0755);
					}
					break;
				case 'calendar':
					!in_array('calendar', $deleteEntities) OR calendar::deleteLessonCalendarEvents($this -> lesson['id']);
					break;
				case 'glossary':
					in_array('glossary', $deleteEntities) ? eF_deleteTableData("glossary", "lessons_ID=".$this -> lesson['id']) : null;
					break;
				case 'projects':
					$lessonProjects = $this -> getProjects(true);
					foreach ($lessonProjects as $value) {
						$value -> delete();
					}
					break;
				case 'tracking':
					$tracking_info = array("done_content"       => "",
                                           "issued_certificate" => "",
                                           "comments"           => "",
                                           "completed"          => 0,
                                           "current_unit"       => 0,
                                           "score"              => 0);
					eF_updateTableData("users_to_lessons", $tracking_info, "lessons_ID = ".$this -> lesson['id']);
					/*
					 foreach ($this -> getUsers as $user => $foo) {
					 $cacheKey = "user_lesson_status:lesson:".$this -> lesson['id']."user:".$user;
					 EfrontCache::getInstance()->deleteCache($cacheKey);
					 }
					 */
					//if (!isset($lessonTests)) { //comented in order to take also feedbacks 
						$lessonTests = $this -> getTestsAndFeedbacks(true);
					//}
					foreach ($lessonTests as $id => $test) {
						eF_deleteTableData("completed_tests", "tests_ID=$id");
					}
					$units = $this -> getUnits();
					if (sizeof($units) > 0) {
						eF_deleteTableData("scorm_data", "content_ID in (".implode(",", $units).")");
					}
					break;
				case 'scheduling':
					$this -> lesson['from_timestamp'] = null;
					$this -> lesson['to_timestamp']   = null;
					$this -> persist();
					break;
				case 'surveys':
					$surveys      = eF_getTableDataFlat("surveys", "id", "lessons_ID=".$this -> lesson['id']);
					$surveys_list = implode(",", $surveys['id']);
					if (!empty($surveys_list)) {
						eF_deleteTableData("questions_to_surveys",  "surveys_ID IN ($surveys_list)");
						eF_deleteTableData("survey_questions_done", "surveys_ID IN ($surveys_list)");
						eF_deleteTableData("users_to_surveys",      "surveys_ID IN ($surveys_list)");
						eF_deleteTableData("users_to_done_surveys", "surveys_ID IN ($surveys_list)");
						eF_deleteTableData("surveys", "lessons_ID=".$this -> lesson['id']);
					}
					break;
				case 'events':
					eF_deleteTableData("events", "lessons_ID=".$this -> lesson['id']);
					break;
				case 'modules':
					$modules = eF_loadAllModules();
					foreach ($modules as $module) {
						$module -> onDeleteLesson($this -> lesson['id']);
					}
					break;
			}
		}



		/*
		 if (in_array('questions', $deleteEntities)) {                                                             //Delete lesson questions
		 $result = eF_getTableData("questions", "id", "lessons_ID=".$this -> lesson['id']);
		 foreach ($result as $value) {
		 $questionIds[] = $value['id'];
		 }
		 if (sizeof($questionIds) > 0) {
		 eF_deleteTableData("tests_to_questions", "questions_ID in (".implode(",", $questionIds).")");
		 }
		 eF_deleteTableData("questions", "lessons_ID=".$this -> lesson['id']);
		 }
		 if (in_array('content', $deleteEntities)) {                                                             //Delete lesson units
		 foreach ($content['id'] as $value) {
		 $value = new EfrontUnit($value);
		 $value -> delete();
		 }
		 $result = eF_getTableDataFlat("content", "id", "lessons_ID=".$this -> lesson['id']);
		 $list   = implode(",", $result['id']);
		 EfrontSearch :: removeText('content', $list, '', true);
		 eF_deleteTableData("content", "lessons_ID=".$this -> lesson['id']);                             //Just make sure everything is deleted
		 }
		 if (in_array('surveys', $deleteEntities)) {
		 $surveys      = eF_getTableDataFlat("surveys", "id", "lessons_ID=".$this -> lesson['id']);
		 $surveys_list = implode(",", $surveys['id']);
		 eF_deleteTableData("questions_to_surveys",  "surveys_ID IN ($surveys_list)");
		 eF_deleteTableData("survey_questions_done", "surveys_ID IN ($surveys_list)");
		 eF_deleteTableData("users_to_surveys",      "surveys_ID IN ($surveys_list)");
		 eF_deleteTableData("users_to_done_surveys", "surveys_ID IN ($surveys_list)");
		 eF_deleteTableData("surveys", "lessons_ID=".$this -> lesson['id']);
		 }
		 if (in_array('tracking', $deleteEntities)) {
		 $tracking_info = array("done_content"       => "",
		 "issued_certificate" => "",
		 "comments"           => "",
		 "completed"          => 0,
		 "current_unit"       => 0,
		 "score"              => 0);
		 eF_updateTableData("users_to_lessons", $tracking_info, "lessons_ID = ".$this -> lesson['id']);
		 }
		 if (in_array('files', $deleteEntities)) {
		 $filesystem = new FileSystemTree($this -> getDirectory());
		 foreach (new ArrayIterator($filesystem -> tree) as $key => $value) {
		 $value -> delete();
		 }
		 }
		 in_array('conditions', $deleteEntities) ? eF_deleteTableData("lesson_conditions", "lessons_ID=".$this -> lesson['id']) : null;
		 in_array('calendar',   $deleteEntities) ? eF_deleteTableData("calendar",          "lessons_ID=".$this -> lesson['id']) : null;
		 in_array('periods',    $deleteEntities) ? eF_deleteTableData("periods",           "lessons_ID=".$this -> lesson['id']) : null;
		 in_array('news',       $deleteEntities) ? eF_deleteTableData("news",              "lessons_ID=".$this -> lesson['id']) : null;
		 in_array('glossary',   $deleteEntities) ? eF_deleteTableData("glossary",    "lessons_ID=".$this -> lesson['id']) : null;
		 in_array('users',      $deleteEntities) ? eF_deleteTableData("users_to_lessons",  "lessons_ID=".$this -> lesson['id']." and user_type = 'student'") : null;

		 in_array('comments', $deleteEntities) ? eF_deleteTableData("comments", "content_ID IN (".$content_list.")") : null;
		 in_array('rules',    $deleteEntities) ? eF_deleteTableData("rules",    "content_ID IN (".$content_list.") or rule_content_ID IN (".$content_list.")") : null;

		 $done_tests = eF_getTableDataFlat("done_tests", "id", "tests_ID IN ($content_list)");
		 sizeof($done_tests) > 0 ? eF_deleteTableData("done_questions", "done_tests_ID IN (".implode(",", $done_tests['id']).")") : null;
		 eF_deleteTableData("done_tests",          "tests_ID IN ($content_list)");
		 eF_deleteTableData("users_to_done_tests", "tests_ID IN ($content_list)");
		 eF_deleteTableData("logs",                "action='test_begin' AND comments IN ($content_list)");

		 */
	}

	public function toXML($flgContent){
		$str = "";

		//write out general lesson information
		foreach ($lesson as $key => $value){
			$str .= "<$key>$value<$key>";
		}

		//write out the content tree
		$contentTree = new EfrontContentTree($this->lesson['id']);
		$str .= $contentTree->toXML(false);
		//write out the glossary
		$glossary = ef_getTableData("glossary","*","lessons_ID=".$this->lessonId);
		if (sizeof($glossary) > 0){
			$str .= '<glossary>';
			for ($i = 0; $i < sizeof($glossary); $i++){
				$str .= '<word>';
				$str .= '<name>'.$glossary[$i]['name'].'</name>';
				$str .= '<info>'.$glossary[$i]['info'].'</info>';
				$str .= '<type>'.$glossary[$i]['type'].'</type>';
				$str .= '<active>'.$glossary[$i]['active'].'</active>';
				$str .= '</word>';
			}
			$str .= '</glossary>';
		}

		//write out the lesson conditions
		$conditions = ef_getTableData("lesson_conditions", "*", "lessons_ID=".$this->lessonId);
		if (sizeof($conditions) > 0){
			$str .= '<conditions>';
			for ($i = 0; $i < sizeof($conditions); $i++){
				$str .= '<condition>';
				$str .= '<type>'.$conditions[$i]['type'].'</type>';
				$str .= '<options>'.$conditions[$i]['options'].'</options>';
				$str .= '<relation>'.$conditions[$i]['relation'].'</relation>';
				$str .= '</condition>';
			}
			$str .= '</conditions>';
		}

		//write out the rules
		$rules = ef_getTableData("rules r, content c", "r.*", "c.id = r.content_ID and c.lessons_ID=".$this->lessonId);
		if (sizeof($rules) > 0){
			$str .= '<rules>';
			for ($i = 0; $i < sizeof($rules); $i++){
				$str .= "<rule>";
				$str .= "<content_id>".$rules[$i]['content_ID']."</content_id>";
				$str .= "<rule_content_id>".$rules[$i]['rule_content_ID']."</rule_content_id>";
				$str .= "<rule_type>".$rules[$i]['rule_type']."</rule_type>";
				$str .= "<rule_option>".$rules[$i]['rule_option']."</rule_option>";
				$str .= '</rules>';
			}
			$str .= '</rules>';
		}

		if ($flgContent){
			//to do export the content as well
		}

		$xmlstr .= '<?xml version="1.0" encoding="UTF-8"?><lesson>'.$str.'</lesson>';
		return $xmlstr;
	}

	public function fromXML($xmlfile){
		$xml = simplexml_load_file($xmlfile);
		$contentTree = new EfrontContentTree($this->lesson['id']);
		$contentTree -> fromXMLNode($xml->lessonId[0]);
		$fields = array();
		$fields['name'] = (string)$xml->lesson->name;
		$fields['info'] = (string)$xml->lesson->info;
		$fields['options'] = (string)$xml->lesson->options;
		$fields['course_only'] = (string)$xml->lesson->course_only;
		//$fields['auto_certificate'] = (string)$xml->lesson->auto_certificate;
		//$fields['auto_complete'] = (string)$xml->lesson->auto_complete;
		//$fields['publish'] = (string)$xml->lesson->publish;
		$lid = ef_inserTableData("lessons", $fields);

		for ($i = 0; $i < sizeof($xml->lesson->conditions->condition); $i++){
			$condition = array();
			$condition['type'] = $xml->lessonId->conditions->condition[$i]->type;
			$condition['options'] = $xml->lessonId->conditions->condition[$i]->options;
			$condition['relation'] = $xml->lessonId->conditions->condition[$i]->relation;
			$cid = ef_insertTableData("lesson_conditions", $condition);
		}

		for ($i = 0; $i < sizeof($xml->lesson->rules->rule); $i++){
			$rule = array();
			$rule['content_id'] = $xml->lessonId->rules->rule[$i]->content_id;
			$rule['rule_content_id'] = $xml->lessonId->rules->rule[$i]->rule_content_id;
			$rule['rule_type'] = $xml->lessonId->rules->rule[$i]->rule_type;
			$rule['rule_option'] = $xml->lessonId->rules->rule[$i]->rule_option;
			$rid = ef_insertTableData("rules", $rule);
		}
	}

	public function toIMS($path){
		$lesson_entries = eF_getTableData("content", "id, name, data", "lessons_ID=" . $this->lesson['id'] . " and ctg_type = 'theory' and active=1");

		$cur_dir = getcwd();
		chdir(G_LESSONSPATH.$this->lesson['id'].'/');
		$filelist = eF_getDirContents();
		chdir($cur_dir);

		create_manifest($this->lesson['id'], $lesson_entries, $filelist, $path);


		//create the EfrontLesson.xml
		$str = '<?xml version="1.0" encoding="UTF-8"?><efrontlesson>';
		//write out the glossary
		$glossary = ef_getTableData("glossary","*","lessons_ID=".$this->lesson['id']);
		if (sizeof($glossary) > 0){
			$str .= '<glossary>';
			for ($i = 0; $i < sizeof($glossary); $i++){
				$str .= '<word>';
				$str .= '<name>'.$glossary[$i]['name'].'</name>';
				$str .= '<info>'.$glossary[$i]['info'].'</info>';
				$str .= '<type>'.$glossary[$i]['type'].'</type>';
				$str .= '<active>'.$glossary[$i]['active'].'</active>';
				$str .= '</word>';
			}
			$str .= '</glossary>';
		}

		//write out the lesson conditions
		$conditions = ef_getTableData("lesson_conditions", "*", "lessons_ID=".$this->lesson['id']);
		if (sizeof($conditions) > 0){
			$str .= '<conditions>';
			for ($i = 0; $i < sizeof($conditions); $i++){
				$str .= '<condition>';
				$str .= '<type>'.$conditions[$i]['type'].'</type>';
				$str .= '<options>'.$conditions[$i]['options'].'</options>';
				$str .= '<relation>'.$conditions[$i]['relation'].'</relation>';
				$str .= '</condition>';
			}
			$str .= '</conditions>';
		}

		//write out the rules
		$rules = ef_getTableData("rules r, content c", "r.*", "c.id = r.content_ID and c.lessons_ID=".$this->lesson['id']);
		if (sizeof($rules) > 0){
			$str .= '<rules>';
			for ($i = 0; $i < sizeof($rules); $i++){
				$str .= "<rule>";
				$str .= "<content_id>".$rules[$i]['content_ID']."</content_id>";
				$str .= "<rule_content_id>".$rules[$i]['rule_content_ID']."</rule_content_id>";
				$str .= "<rule_type>".$rules[$i]['rule_type']."</rule_type>";
				$str .= "<rule_option>".$rules[$i]['rule_option']."</rule_option>";
				$str .= '</rule>';
			}
			$str .= '</rules>';
		}

		//write out the questions
		$questions = ef_getTableData("questions q, content c", "q.*", "q.content_id = c.id and c.lessons_id=".$this->lesson['id']."");
		if (sizeof($questions) > 0){
			$str .= "<questions>";
			for ($i = 0; $i < sizeof($questions); $i++){
				$str .= "<question>";
				$str .= "<refid>q".$questions[$i]['id']."</refid>";
				$str .= "<type>".$questions[$i]['type']."</type>";
				$str .= "<difficulty>".$questions[$i]['difficulty']."</difficulty>";
				$str .= "<options>".$questions[$i]['options']."</options>";
				$str .= "<answer>".$questions[$i]['answer']."</answer>";
				$str .= "<explanation>".$questions[$i]['explanation']."</explanation>";
				$str .= "</question>";
			}
			$str .= "</questions>";
		}

		//write out the tests
		$tests = ef_getTableData("tests t, content c", "t.*", "t.content_id = c.id and c.lessons_id=".$this->lesson['id']."");
		if (sizeof($tests) > 0){
			$str .= "<tests>";
			for ($i = 0; $i < sizeof($tests); $i++){
				$str .= "<test>";
				$str .= "<refid>t".$tests[$i]['id']."</refid>";
				$str .= "<duration>".$tests[$i]['duration']."</duration>";
				$str .= "<redoable>".$tests[$i]['redoable']."</redoable>";
				$str .= "<onebyone>".$tests[$i]['onebyone']."</onebyone>";
				$str .= "<answers>".$tests[$i]['answers']."</answers>";
				$str .= "<shuffle_questions>".$tests[$i]['shuffle_questions']."</shuffle_questions>";
				$str .= "<shuffle_answers>".$tests[$i]['shuffle_answers']."</shuffle_answers>";
				$str .= "<given_answers>".$tests[$i]['given_answers']."</given_answers>";
				$questions = ef_getTableData("tests_to_questions","*","tests_ID = ".$tests[$i]['id']);
				$str .= "<questions>";
				for ($j = 0; $j < sizeof($questions); $j++){
					$str .= "<question>";
					$str .= "<refid>q".$questions[$j]['questions_ID']."</refid>";
					$str .= "<weight>".$questions[$j]['weight']."</weight>";
					$str .= "<previous>q".$questions[$j]['previous_question_ID']."</previous>";
					$str .= "</question>";
				}
				$str .= "</questions>";
				$str .= "</test>";
			}
			$str .= "</tests>";
		}

		$str .= '</efrontlesson>';
		if ($fp = fopen($path . "/lesson".$this->lesson['id']."/eFrontLesson.xml", "wb")) {
			fwrite($fp, $str);
		}
		fclose($fp);


		chdir($path."/lesson". $this->lesson['id']);
		$d = dir(".");
		$entries = array();
		while (false !== ($entry = $d->read())) {
			if ($entry != "." & $entry != ".."){
				array_push($entries, $entry);
			}
		}
		$d->close();
		$zip = new Archive_zip($path."/lesson".$this->lesson['id'].".tar.gz");
		$zip -> create( $entries );
		chdir($cur_dir);
		deldir($path."/lesson". $this->lesson['id']);
	}

	public function fromIMS($ims_file, $deleteEntities = false){

		if ($deleteEntities) {
			$this -> initialize($deleteEntities);               //Initialize the lesson aspects that the user specified
		}
		$timestamp       = time();
		$zip  = new Archive_zip($ims_file);
		$extracted_files = $zip -> extract(array('add_path' => G_LESSONSPATH.$this->lesson['id']."/IMS_".$timestamp));
		$total_fields = array();
		$resources    = array();
		$tagArray     = @eF_local_parseManifest(G_LESSONSPATH.$this->lesson['id']."/IMS_".$timestamp);
		$questions = array();
		$tests = array();
		$content = array();
		$inContent = false;
		$inTest = false;
		$inQuestion = false;
		$references = array();
		$questionsKeys = array();
		$testsKeys = array();
		foreach($tagArray as $key => $value) {
			$fields = array();
			switch ($value['tag']) {
				case 'TITLE':
					$cur = $value['parent_index'];
					if ($inContent){
						$content[$cur]['name'] = $value['value'];
					}
					if ($inTest){
						$tests[$cur]['name'] = $value['value'];
					}
					break;

				case 'ITEM':
					$cur = $key;
					if ($value['attributes']['TYPE'] != 'question' && $value['attributes']['TYPE'] != 'test')
					{
						$inContent = true;
						$inTest = false;
						$inQuestion = false;
						$content[$key]['lessons_ID'] = $this->lesson['id'];
						$content[$key]['timestamp']  = time();
						$content[$key]['ctg_type']   = 'theory';
						$content[$key]['active']     = 1;
						$references[$key] = $value['attributes']['IDENTIFIERREF'];
					}
					else if ($value['attributes']['TYPE'] == 'test')
					{
						$inTest = true;
						$inContent = false;
						$inQuestion = false;
						$tests[$key]['active'] = '1';
						$testsKeys[$value['attributes']['IDENTIFIERREF']] = $key;
						$references[$key] = $value['attributes']['IDENTIFIERREF'];
					}
					else if ($value['attributes']['TYPE'] == 'question')
					{
						$inQuestion = true;
						$inContent = false;
						$inTest = false;
						$questions[$key]['difficulty'] = 'medium'; //initial value
						$questions[$key]['type'] = 'true_false'; //initial value
						$questions[$key]['timestamp'] = time();
						$questionsKeys[$value['attributes']['IDENTIFIERREF']] = $key;
						$references[$key] = $value['attributes']['IDENTIFIERREF'];
					}
					break;

				case 'RESOURCE':
					$resources[$key] = $value['attributes']['IDENTIFIER'];
					break;

				case 'FILE':
					$files[$key] = $value['attributes']['HREF'];
					break;

				case 'ADLCP:MAXTIMEALLOWED':
					$maxtimeallowed[$key]  = $value['value'];
					break;
				case 'ADLCP:TIMELIMITACTION':
					$timelimitaction[$key] = $value['value'];
					break;
				case 'ADLCP:MASTERYSCORE':
					$masteryscore[$key]    = $value['value'];
					break;
				case 'ADLCP:DATAFROMLMS':
					$datafromlms[$key]     = $value['value'];
					break;

				case 'ADLCP:PREREQUISITES':
					$prerequisites[$key]   = $value['value'];
					break;

				default:
					break;
			}
		}

		foreach ($references as $key => $value) {

			$ref = array_search($value, $resources);
			if ($ref !== false && !is_null($ref)) {

				$data = file_get_contents(G_LESSONSPATH.$this->lesson['id']."/IMS_".$timestamp."/".$tagArray[$ref]['attributes']['HREF']);


				$primitive_hrefs[$ref] = $tagArray[$ref]['attributes']['HREF'];
				$path_part[$ref]       = dirname($primitive_hrefs[$ref]);


				foreach($tagArray[$ref]['children'] as $value2) {

					if ($tagArray[$value2]['tag'] == 'DEPENDENCY') {

						$idx = array_search($tagArray[$value2]['attributes']['IDENTIFIERREF'], $resources);


						foreach ($tagArray[$idx]['children'] as $value3) {

							if ($tagArray[$value3]['tag'] == 'FILE')  {

								$data = preg_replace("#(\.\.\/(\w+\/)*)?".$tagArray[$value3]['attributes']['HREF']."#", G_LESSONSPATH.$this->lesson['id']."/".$path_part[$ref]."/$1".$tagArray[$value3]['attributes']['HREF'], $data);
							}
						}
					}
				}

				if ($content[$key]['active'] == 1){
					$i1 = stripos($data, "<body");
					$i2 = stripos($data, ">", $i1);
					$i3 = strripos($data, "<script");
					$data = substr($data, $i2 + 1, $i3 - $i2 - 1);
					$content[$key]['data'] = $data;
				}
				else if ($tests[$key]){
					$data = $data;
					$tests[$key]['description'] = $data;
				}
				else if ($questions[$key]){
					$data = $data;
					$questions[$key]['text'] = $data;
				}
			}
		}

		$inStart = true;
		foreach ($content as $key => $value){
			$cid = ef_insertTableData("content", $value);
			/* TODO INDEX */
			if ($inStart){
				$inStart = false;
				foreach ($questions as $keyq => $valueq){
					$questions[$keyq]['content_ID'] = $cid;
				}
			}
		}


		foreach ($questions as $key => $value){
			$qid = ef_insertTableData("questions", $value);
			$questions[$key]['id'] = $qid;
		}

		foreach ($tests as $key => $value){
			$test_content['lessons_ID'] = $this->lesson['id'];
			$test_content['timestamp']  = time();
			$test_content['ctg_type']   = 'tests';
			$test_content['active']     = 1;
			$test_content['name'] = $value['name'];
			unset($value['name']);
			$cid = ef_insertTableData("content", $test_content);
			$value['content_id'] = $cid;
			$tid = ef_insertTableData("tests", $value);
			$tests[$key]['id'] = $tid;
		}


		eF_local_buildDirectories(G_LESSONSPATH.$this->lesson['id']."/", G_LESSONSPATH.$this->lesson['id']."/IMS_".$timestamp);

		foreach ($files as $key => $value) {

			//      if (!in_array($value, $primitive_hrefs))
			//      {
			$newhref = $tagArray[$tagArray[$key]['parent_index']]['attributes']['XML:BASE'];
			//copy(G_LESSONSPATH.$this->lesson['id']."/IMS_".$timestamp."/".trim($newhref,"/")."/".trim($value,"/"), G_LESSONSPATH.$this->lesson['id']."/".trim($newhref,"/")."/".trim($value,"/"));
			//      }
		}

		$cur_dir = getcwd();
		chdir(G_LESSONSPATH.$this->lesson['id']."/IMS_".$timestamp);
		$filelist = eF_getDirContents();
		foreach ($filelist as $value) {
			copy($value, G_LESSONSPATH.$this->lesson['id']."/".$value);
		}
		chdir($cur_dir);


		foreach ($prerequisites as $key => $value) {
			foreach ($tagArray as $key2 => $value2) {
				if (isset($value2['attributes']['IDENTIFIER']) && $value2['attributes']['IDENTIFIER'] == $value) {
					unset($fields_insert);
					$fields_insert['users_LOGIN'] = "*";
					$fields_insert['content_ID']  = $tagArray[$tagArray[$key]['parent_index']]['this_id'];
					$fields_insert['rule_type']   = "hasnot_seen";
					$fields_insert['rule_content_ID'] = $value2['this_id'];
					$fields_insert['rule_option'] = 0;
					//eF_insertTableData("rules", $fields_insert);
				}
			}
		}

		//read the special EfrontLesson.xml
		$xmlfile = G_LESSONSPATH.$this->lesson['id']."/IMS_".$timestamp."/EfrontLesson.xml";
		$xml = simplexml_load_file($xmlfile);
		for ($i = 0; $i < sizeof($xml->conditions->condition); $i++){
			$condition = array();
			$condition['type'] = (string)$xml->conditions->condition[$i]->type;
			$condition['options'] = (string)$xml->conditions->condition[$i]->options;
			$condition['relation'] = (string)$xml->conditions->condition[$i]->relation;
			$condition['lessons_ID'] = $this->lesson['id'];
			$cid = ef_insertTableData("lesson_conditions", $condition);
		}
		for ($i = 0; $i < sizeof($xml->glossary->word); $i++){
			$glossary = array();
			$glossary['name'] = (string)$xml->glossary->word[$i]->name;
			$glossary['type'] = (string)$xml->glossary->word[$i]->type;
			$glossary['info'] = (string)$xml->glossary->word[$i]->info;
			$glossary['active'] = (string)$xml->glossary->word[$i]->active;
			$glossary['lessons_ID'] = $this->lesson['id'];
			$cid = ef_insertTableData("glossary", $glossary);
		}

		for ($i = 0; $i < sizeof($xml->questions->question); $i++){
			$update = array();
			$refid = (string)$xml->questions->question[$i]->refid;
			$qk = $questionsKeys[$refid];
			$qid = $questions[$qk]['id'];
			$update['type'] = (string)$xml->questions->question[$i]->type;
			$update['difficulty'] = (string)$xml->questions->question[$i]->difficulty;
			$update['options'] = (string)$xml->questions->question[$i]->options;
			$update['answer'] = (string)$xml->questions->question[$i]->answer;
			$update['explanation'] = (string)$xml->questions->question[$i]->explanation;
			ef_updateTableData("questions", $update, "id = $qid");
		}

		for ($i = 0; $i < sizeof($xml->tests->test); $i++){
			$update = array();
			$refid = (string)$xml->tests->test[$i]->refid;
			$tk = $testsKeys[$refid];
			$tid = $tests[$tk]['id'];
			$update['duration'] = (string)$xml->tests->test[$i]->duration;
			$update['redoable'] = (string)$xml->tests->test[$i]->redoable;
			$update['onebyone'] = (string)$xml->tests->test[$i]->onebyone;
			$update['answers'] = (string)$xml->tests->test[$i]->answers;
			$update['shuffle_questions'] = (string)$xml->tests->test[$i]->shuffle_questions;
			$update['shuffle_answers'] = (string)$xml->tests->test[$i]->shuffle_answers;
			$update['given_answers'] = (string)$xml->tests->test[$i]->given_answers;
			ef_updateTableData("tests", $update, "id=".$tid);
			for ($j = 0; $j < sizeof($xml->tests->test[$i]->questions->question); $j++){
				$testQuestions = array();
				$refid = (string)$xml->tests->test[$i]->questions->question[$j]->refid;
				$qk = $questionsKeys[$refid];
				$qid = $questions[$qk]['id'];
				$previd = (string)$xml->tests->test[$i]->questions->question[$j]->previous;
				if ($previd != "q0"){
					$pk = $questionsKeys[$previd];
					$pid = $questions[$pk]['id'];
				}
				else
				$pid = 0;
				$testQuestions['tests_ID'] = $tid;
				$testQuestions['questions_ID'] = $qid;
				$testQuestions['previous_question_ID'] = $pid;
				$testQuestions['weight'] = (string)$xml->tests->test[$i]->questions->question[$j]->weight;
				ef_insertTableData("tests_to_questions", $testQuestions);
			}
		}
	}

	/**
	 * Import lesson
	 *
	 * This function is used to import a lesson exported to a file
	 * The first step is to optionally initialize the lesson, using initialize().
	 * It then uncompresses the given file and proceeds to importing
	 * <br/>Example:
	 * <code>
	 * try {
	 *     $lesson = new EfrontLesson(32);                                             //32 is the lesson id
	 *     $file = new EfrontFile($lesson -> getDirectory().'data.tar.gz');            //The file resides inside the lesson directory and is called 'data.tar.gz'
	 *     $lesson -> import(array('content'), $file);
	 * } catch (Exception $e) {
	 *     echo $e -> getMessage();
	 * }
	 * </code><br/>
	 *
	 * @param EfrontFile $file The compressed lesson file object
	 * @param array $deleteEntities The lesson aspects to initialize
	 * @param boolean $lessonProperties Whether to import lesson properties as well
	 * @param boolean $keepName Whether to keep the current (false) or the original name (true)
	 * @return boolean True if the importing was successful
	 * @since 3.5.0
	 * @access public
	 * @see EfrontLesson :: initialize()
	 */
	public function import($file, $deleteEntities = false, $lessonProperties = false, $keepName = false) {

		if ($deleteEntities) {
			$this -> initialize($deleteEntities);               //Initialize the lesson aspects that the user specified
		}
		if (!($file instanceof EfrontFile)) {
			$file = new EfrontFile($file);
		}

		$fileList = $file -> uncompress();
		$file -> delete();

		$fileList = array_unique(array_reverse($fileList, true));
		$dataFile = new EfrontFile($file['directory'].'/data.dat');
		$filedata = file_get_contents($dataFile['path']);
		$dataFile -> delete();

		$data              = unserialize($filedata);

		$data['content']   = self :: eF_import_fixTree($data['content'], $last_current_node);

		for ($i = 0; $i < sizeof($data['files']); $i++) {
			if (isset($data['files'][$i]['file'])) {
				$newName = str_replace(G_ROOTPATH, '', dirname($data['files'][$i]['file']).'/'.EfrontFile :: encode(basename($data['files'][$i]['file'])));
				$newName = preg_replace("#(.*)www/content/lessons/#", "www/content/lessons/", $newName);
				$newName = preg_replace("#www/content/lessons/\d+/(.*)#", "www/content/lessons/".($this -> lesson['share_folder'] ? $this -> lesson['share_folder'] : $this -> lesson['id'])."/\$1", $newName);
				if ($data['files'][$i]['original_name'] != basename($data['files'][$i]['file'])) {
					if (is_file(G_ROOTPATH.$newName)) {
						$replaceString['/\/?(view_file.php\?file=)'.$data['files'][$i]['id'].'([^0-9])/'] = '${1}'.array_search(G_ROOTPATH.$newName, $fileList).'${2}';    //Replace old ids with new ids
						//$mp[$data['files'][$i]['id']] = array_search(G_ROOTPATH.$newName, $fileList);
						$file = new EfrontFile(G_ROOTPATH.$newName);
						$file -> rename(G_ROOTPATH.dirname($newName).'/'.EfrontFile :: encode(rtrim($data['files'][$i]['original_name'], "/")));
					}
				}
			} else {
				$newName = preg_replace("#www/content/lessons/\d+/(.*)#", "www/content/lessons/".($this -> lesson['share_folder'] ? $this -> lesson['share_folder'] : $this -> lesson['id'])."/\$1", $data['files'][$i]['path']);
				if (is_file(G_ROOTPATH.$newName)) {
					$replaceString['/\/?(view_file.php\?file=)'.$data['files'][$i]['id'].'([^0-9])/'] = '${1}'.array_search(G_ROOTPATH.$newName, $fileList).'${2}';    //Replace old ids with new ids
				}
			}
		}

		for ($i = 0; $i < sizeof($data['files']); $i++) {
			if (isset($data['files'][$i]['file'])) {
				$newName = str_replace(G_ROOTPATH, '', dirname($data['files'][$i]['file']).'/'.EfrontFile :: encode(basename($data['files'][$i]['file'])));
				$newName = preg_replace("#(.*)www/content/lessons/#", "www/content/lessons/", $newName);
				$newName = preg_replace("#www/content/lessons/\d+/(.*)#", "www/content/lessons/".($this -> lesson['share_folder'] ? $this -> lesson['share_folder'] : $this -> lesson['id'])."/\$1", $newName);
				if ($data['files'][$i]['original_name'] != basename($data['files'][$i]['file'])) {
					if (is_dir(G_ROOTPATH.$newName)) {
						$file = new EfrontDirectory(G_ROOTPATH.$newName);
						$file -> rename(G_ROOTPATH.dirname($newName).'/'.EfrontFile :: encode(rtrim($data['files'][$i]['original_name'], "/")));
					}
				}
			}
		}

		unset($data['files']);

		$last_current_node = 0;
		$existing_tree     = eF_getContentTree($nouse, $this -> lesson['id'], 0, false, false);
		if (sizeof($existing_tree) > 0) {
			$last_current_node = $existing_tree[sizeof($existing_tree) - 1]['id'];
			$first_node        = self :: eF_import_getTreeFirstChild($data['content']);

			$data['content'][$first_node]['previous_content_ID'] = $last_current_node;
		}


		// MODULES - Import module data
		// Get all modules (NOT only the ones that have to do with the user type)
		$modules = eF_loadAllModules();
		foreach ($modules as $module) {
			if (isset($data[$module->className])) {
				$module -> onImportLesson($this -> lesson['id'], $data[$module->className]);
				unset($data[$module->className]);
			}
		}

		if (isset($data['glossary_words'])) {   // to avoid excluding it with the lines below
			$data['glossary'] = $data['glossary_words'];
		}
		$dbtables = eF_showTables();
		//Skip tables that don't exist in current installation, such as modules' tables
		foreach (array_diff(array_keys($data), $dbtables) as $value) {
			unset($data[$value]);
		}

		//tests_to_questions table requires special handling
		//$testsToQuestions = $data['tests_to_questions'];
		//unset($data['tests_to_questions']);
		if (!$data['questions'] && $data['tests_to_questions']) {
			unset($data['tests_to_questions']);
		}

		foreach ($data as $table => $tabledata) {
			/*if ($table == "glossary_words") {
			 $table = "glossary";

			 } */ // moved 20 lines above
			if ($table == "lessons") { //from v3 lessons parameters also imported
				if ($lessonProperties) {
					unset($data['lessons']['id']);
					unset($data['lessons']['directions_ID']);
					unset($data['lessons']['created']);
					$this -> lesson = array_merge($this -> lesson, $data['lessons']);
					$this -> persist();
				} 
					eF_updateTableData("lessons", array('info'      => $data['lessons']['info'],
	                                                    'metadata'  => $data['lessons']['metadata'],
	                                                    'options'   => $data['lessons']['options']), "id=".$this -> lesson['id']);
				
				if ($keepName) {
					eF_updateTableData("lessons", array("name" => $data['lessons']['name']), "id=".$this -> lesson['id']);
					$this->lesson['name'] = $data['lessons']['name'];
					eF_updateTableData("f_forums", array("title" => $data['lessons']['name']), "lessons_ID=".$this -> lesson['id']);
				}
			} else {
				if ($table == "questions") {
					foreach ($tabledata as $key => $value) {
						unset($tabledata[$key]['timestamp']);
						$tabledata[$key]['lessons_ID'] = $this -> lesson['id'];
						if ($tabledata[$key]['estimate'] == "") {
							unset($tabledata[$key]['estimate']);
						}
						if (isset($tabledata[$key]['code'])) { //code field removed in version 3.6
							unset($tabledata[$key]['code']);
						}
					}
				}
				if ($table == "tests") {
					for ($i = 0; $i < sizeof($tabledata); $i++) {
						if (!isset($tabledata[$i]['options'])) {
							$tabledata[$i]['options'] = serialize(array('duration' => $tabledata[$i]['duration'],
                                                                        'redoable' => $tabledata[$i]['redoable'],
                                                                        'onebyone' => $tabledata[$i]['onebyone'],
                                                                        'answers'  => $tabledata[$i]['answers'],
                                                                        'given_answers'     => $tabledata[$i]['given_answers'],
                                                                        'shuffle_questions' => $tabledata[$i]['shuffle_questions'],
                                                                        'shuffle_answers'   => $tabledata[$i]['shuffle_answers']));
							unset($tabledata[$i]['duration']);
							unset($tabledata[$i]['redoable']);
							unset($tabledata[$i]['onebyone']);
							unset($tabledata[$i]['answers']);
							unset($tabledata[$i]['given_answers']);
							unset($tabledata[$i]['shuffle_questions']);
							unset($tabledata[$i]['shuffle_answers']);
						}
					}
				}
				if ($table == 'calendar') {
					for ($i = 0; $i < sizeof($tabledata); $i++) {
						if (isset($tabledata[$i]['lessons_ID'])) {
							if ($tabledata[$i]['lessons_ID']) {
								$tabledata[$i]['foreign_ID'] = $tabledata[$i]['lessons_ID'];
								$tabledata[$i]['type'] 		= 'lesson';
							} else {
								$tabledata[$i]['foreign_ID'] = 0;
								$tabledata[$i]['type'] 		= '';
							}
							unset($tabledata[$i]['lessons_ID']);
						}
					}
				}
				for ($i = 0; $i < sizeof($tabledata); $i++) {
					if ($table == "tests") {
						if (!isset($tabledata[$i]['lessons_ID'])) {
							$tabledata[$i]['lessons_ID'] = $this -> lesson['id'];
						}
					}

					if ($tabledata[$i]) {
						$sql = "INSERT INTO ".G_DBPREFIX.$table." SET ";
						$connector = "";
						$fields = array();
						foreach ($tabledata[$i] as $key => $value) {
							if ($key == "id") {
								$old_id = $value;
							} else {
								if (($table == "content" AND $key == "data") || ($table == "questions" AND $key == "text") || ($table == "tests" AND $key == "description")) {
									$value = str_replace("##SERVERNAME##", "", $value);
									//$value = str_replace("/##LESSONSLINK##", "content/lessons/".$this -> lesson['id'], $value);
									$value = str_replace("##LESSONSLINK##", "content/lessons/".($this -> lesson['share_folder'] ? $this -> lesson['share_folder'] : $this -> lesson['id']), $value);
									$content_data = $value;
								} elseif ($key == "lessons_ID") {
									$value = $this -> lesson['id'];
								} elseif ($table == "lesson_conditions" AND $key == "options") {
									if (mb_strpos($data['lesson_conditions'][$i]['type'], "specific") === false){

									}else{
										$options = unserialize($data['lesson_conditions'][$i]['options']);
										$options[0] = $map['content'][$options[0]];
										$value = serialize($options);

									}
								}
								elseif ($table != "content" AND mb_substr($key, -3) == "_ID") {

									$from_table = mb_substr($key, 0, -3);
									if (isset($map[$from_table][$value])) {
										$value = $map[$from_table][$value];
									}
								}

								if ($table == 'scorm_sequencing_content_to_organization' && $key == 'organization_content_ID') {
									$value = $map['content'][$value];
								}
								if ($table == 'scorm_sequencing_maps_info' && $key == 'organization_content_ID') {
									$value = $map['content'][$value];
								}

								if ($table == "content" AND $key == 'previous_content_ID' AND !$value) {
									$value = 0;
								}
								if (!($table == "content" AND $key == "format")) {
									//$sql .= $connector.$key."='".str_replace("'","''",$value)."'";
									//$connector = ", ";
									$fields[$key] = $value;
								}
								if ($table == "content" AND $key == "name") {
									$content_name = $value;
								}
							}
						}

						$new_id = eF_insertTableData($table, $fields);
						if ($table == "content") {
							EfrontSearch :: insertText($content_name, $new_id, "content", "title");
							EfrontSearch :: insertText(strip_tags($content_data), $new_id, "content", "data");
						}
						$map[$table][$old_id] = $new_id;
					}
				}
			}
		}

		if ($data['content']) {
			$map['content'] = array_reverse($map['content'], true);
			foreach($map['content'] as $old_id => $new_id) {
				eF_updateTableData("content", array('parent_content_ID' => $new_id), "parent_content_ID=$old_id AND lessons_ID=".$this -> lesson['id']);
				eF_updateTableData("content", array('previous_content_ID' => $new_id), "previous_content_ID=$old_id AND lessons_ID=".$this -> lesson['id']);
				//eF_updateTableData("questions", array('content_ID' => $new_id), "content_ID=$old_id");
			}
		}

		if ($data['rules']) {
			foreach($map['content'] as $old_id => $new_id) {
				eF_updateTableData("rules", array('rule_content_ID' => $new_id), "rule_content_ID=$old_id");
			}
		}


		// Update lesson skill
		$lessonSkillId = $this -> getLessonSkill();

		// The lesson offers skill record remains the same
		if ($lessonSkillId) {
			eF_updateTableData("module_hcd_skills", array("description" => _KNOWLEDGEOFLESSON . " ". $this -> lesson['name'] , "categories_ID" => -1), "skill_ID = ". $lessonSkillId['skill_ID']);
		}

		if ($data['questions']) {
			foreach($map['questions'] as $old_id => $new_id) {

				eF_updateTableData("tests_to_questions", array('previous_question_ID' => $new_id), "previous_question_ID=$old_id and tests_ID in (select id from tests where lessons_ID=".$this -> lesson['id'].")");

				// Update all questions of not course_only lessons to offer the lessons skill
				if ($lessonSkillId) {
					eF_insertTableData("questions_to_skills", array("questions_id" => $new_id, "skills_ID" => $lessonSkillId['skill_ID'], "relevance" => 2));

				}

				//eF_insertTableData("questions_to_skills", array("q
				//$questions = eF_getTableDataFlat("questions", "id", "lessons_ID = ". $this ->lesson['id']);
				//eF_deleteTableData("questions_to_skills", "questions_id IN ('".implode("','",$questions['id'])."')");
			}
		}


		foreach ($map['content'] as $old_id => $new_id) {  //needs debugging
			$content_new_IDs[] = $new_id;
		}
		$content_new_IDs_list = implode(",",$content_new_IDs);
		if ($content_new_IDs_list) {
			$content_data = eF_getTableData("content", "data,id", "id IN ($content_new_IDs_list) AND lessons_ID=".$this -> lesson['id']);
		}

		if (isset($replaceString)) {
			for ($i = 0; $i < sizeof($content_data); $i++) {
				$replaced = preg_replace(array_keys($replaceString), array_values($replaceString), $content_data[$i]['data']);

				eF_updateTableData("content", array('data' => $replaced), "id=".$content_data[$i]['id']);
				EfrontSearch :: removeText('content', $content_data[$i]['id'], 'data');                                     //Refresh the search keywords
				EfrontSearch :: insertText($replaced, $content_data[$i]['id'], "content", "data");
			}
		}

		if ($content_new_IDs_list) {
			$content_data  = eF_getTableData("content", "data,id", "id IN ($content_new_IDs_list) AND lessons_ID=".$this -> lesson['id']." AND data like '%##EFRONTINNERLINK##%'");
		}
		for($i =0; $i < sizeof($content_data); $i++) {
			preg_match_all("/##EFRONTINNERLINK##.php\?ctg=content&amp;view_unit=(\d+)/", $content_data[$i]['data'], $regs);
			foreach ($regs[1] as $value) {
				$replaced   = str_replace("##EFRONTINNERLINK##.php?ctg=content&amp;view_unit=".$value,"##EFRONTINNERLINK##.php?ctg=content&amp;view_unit=".$map["content"][$value], $content_data[$i]['data']);
				eF_updateTableData("content", array('data' => $replaced), "id=".$content_data[$i]['id']);
				EfrontSearch :: removeText('content', $content_data[$i]['id'], 'data');                                     //Refresh the search keywords
				EfrontSearch :: insertText($replaced, $content_data[$i]['id'], "content", "data");
			}
		}

		$tests = eF_getTableData("tests t, content c", "t.id, t.name, c.name as c_name", "t.content_ID=c.id");
		foreach ($tests as $test) {
			if (!$test['name']) {
				eF_updateTableData("tests", array("name" => $test['c_name']), "id=".$test['id']);
			}
		}
		//exit;
		return true;

	}

	/**
	 * Fix efront v1.x trees
	 *
	 * This function is used for fixing lessons from efront version 1 that
	 * are beeing imported to the system
	 *
	 * @param array $tree The old content tree
	 * @param int $last_current_node The last current node
	 * @return array The fixed tree
	 * @since 3.5.0
	 * @access private
	 */
	private function eF_import_fixTree($tree, $last_current_node = 0)
	{
		for ($i = 0; $i < sizeof($tree); $i++) {
			if ($tree[$i]['parent_content_ID'] == 0) {
				$roots[$i] = $tree[$i];
				$roots[$i]['idx'] = $i;
			}
		}

		foreach ($roots as $key => $node) {
			$roots[$key]['last_child'] = self :: eF_import_getLastChild($tree, $key);
			if ($node['previous_content_ID'] == 0 && $node['parent_content_ID'] == 0) {
				$eligible[] = $roots[$key];
			}
		}

		foreach ($eligible as $key => $node) {
			$timestamps[] = $node['timestamp'];
			$found = true;
			$temp = $roots;

			$eligible[$key]['final_child'] = $node['last_child'];
			while (sizeof($temp) > 0 && $found) {
				$found = false;
				foreach ($temp as $temp_key => $temp_node) {
					if ($eligible[$key]['final_child'] == $temp_node['previous_content_ID']) {
						$eligible[$key]['final_child'] = $temp_node['last_child'];
						unset($temp[$temp_key]);

						$found = true;
					}
				}
			}
		}

		array_multisort($timestamps, SORT_ASC, $eligible);

		for ($i = 0; $i < sizeof($eligible); $i++) {
			if ($i < sizeof($eligible) - 1) {
				$eligible[$i + 1]['previous_content_ID'] = $eligible[$i]['final_child'];
			}
			unset($eligible[$i]['last_child']);
			unset($eligible[$i]['final_child']);
			$idx = $eligible[$i]['idx'];
			unset($eligible[$i]['idx']);
			$tree[$idx] = $eligible[$i];
		}

		return $tree;
	}

	/**
	 * Get the tree last child
	 *
	 * This function is used for importing lessons and returns the tree's last child
	 *
	 * @param array $tree The content tree
	 * @param int $idx The current index
	 * @return int The last children index
	 * @since 3.5.0
	 * @access private
	 */
	private function eF_import_getLastChild($tree, $idx) {
		$original_tree = $tree;

		$count = 0;
		$children[$idx] = $tree[$idx]['id'];
		$found = true;
		while (sizeof($tree) > 0 && $count++ < 1000 && $found) {
			$found = false;
			foreach ($tree as $key => $node) {
				if (in_array($node['parent_content_ID'], $children)) {
					$children[$key] = $node['id'];
					unset($tree[$key]);
					$found = true;
				}
			}
		}

		foreach ($children as $key => $child) {
			$previous[] = $original_tree[$key]['previous_content_ID'];
		}

		$last = (array_diff($children, $previous));
		$last = array_values($last);

		return $last[0];
	}

	/**
	 * Get the first child of the tree
	 *
	 * This function is used for importing lessons and returns the tree's first child
	 *
	 * @param array $tree
	 * @return int the first child index
	 * @since 3.5.0
	 * @access private
	 */
	private function eF_import_getTreeFirstChild($tree)
	{
		$count = 0;
		while ($tree[$count]['parent_content_ID'] != 0 || $tree[$count]['previous_content_ID'] != 0)
		{
			$count++;
		}

		$first_node = $count;
		return $first_node;
	}

	/**
	 * Export lesson
	 *
	 * This function is used to export the current lesson's data to
	 * a file, which can then be imported to other systems. Apart from
	 * the lesson content, the user may optinally specify additional
	 * information to export, using the $exportEntities array. If
	 * $exportEntities is 'all', everything that can be exported, is
	 * exported
	 *
	 * <br/>Example:
	 * <code>
	 * $exportedFile = $lesson -> export('all');
	 * </code>
	 *
	 * @param array $exportEntities The additional data to export
	 * @param boolean $rename Whether to rename the exported file with the same name as the lesson
	 * @param boolean $exportFiles Whether to export files as well
	 * @return EfrontFile The object of the exported data file
	 * @since 3.5.0
	 * @access public
	 */
	public function export($exportEntities, $rename = true, $exportFiles = true) {
		if (!$exportEntities) {
			$exportEntities = array('export_surveys' => 1, 'export_announcements' => 1, 'export_glossary' => 1,
                                    'export_calendar' => 1, 'export_comments' => 1, 'export_rules' => 1);
		}

		$data['lessons'] = $this -> lesson;
		unset($data['lessons']['share_folder']);
		unset($data['lessons']['instance_source']);
		unset($data['lessons']['originating_course']);

		$content         = eF_getTableData("content", "*", "lessons_ID=".$this -> lesson['id']);
		if (sizeof($content) > 0) {
			$contentIds = array();
			for ($i = 0; $i < sizeof($content); $i++) {
				$content[$i]['data'] = str_replace(G_SERVERNAME, "##SERVERNAME##", $content[$i]['data']);
				$content[$i]['data'] = str_replace("content/lessons/".($this -> lesson['share_folder'] ? $this -> lesson['share_folder'] : $this -> lesson['id']), "##LESSONSLINK##", $content[$i]['data']);
				$contentIds[] = $content[$i]['id'];
			}

			$content_list    = implode(",", array_values($contentIds));
			$data['content'] = $content;

			$questions = eF_getTableData("questions", "*", "lessons_ID=".$this -> lesson['id']);
			if (sizeof($questions) > 0) {
				for ($i = 0; $i < sizeof($questions); $i++) {
					$questions[$i]['text'] = str_replace(G_SERVERNAME, "##SERVERNAME##", $questions[$i]['text']);
					$questions[$i]['text'] = str_replace("content/lessons/".($this -> lesson['share_folder'] ? $this -> lesson['share_folder'] : $this -> lesson['id']), "##LESSONSLINK##", $questions[$i]['text']);
				}
				$data['questions'] = $questions;
			}

			$tests 		= eF_getTableData("tests", "*", "lessons_ID=".$this -> lesson['id']);
			if (sizeof($tests)) {
				$testsIds = array();
				foreach ($tests as $key => $value) {
					$testsIds[] = $value['id'];
				}
				$tests_list         = implode(",", array_values($testsIds));
				$tests_to_questions = eF_getTableData("tests_to_questions", "*", "tests_ID IN ($tests_list)");
				for ($i = 0; $i < sizeof($tests); $i++) {
					$tests[$i]['description'] = str_replace(G_SERVERNAME, "##SERVERNAME##", $tests[$i]['description']);
					$tests[$i]['description'] = str_replace("content/lessons/".($this -> lesson['share_folder'] ? $this -> lesson['share_folder'] : $this -> lesson['id']), "##LESSONSLINK##", $tests[$i]['description']);
				}

				$data['tests']      = $tests;
				$data['tests_to_questions'] = $tests_to_questions;
			}
			if (isset($exportEntities['export_rules'])) {
				$rules = eF_getTableData("rules", "*", "lessons_ID=".$this -> lesson['id']);
				if (sizeof($rules) > 0) {
					$data['rules'] = $rules;
				}
			}
			if (isset($exportEntities['export_comments'])) {
				$comments = eF_getTableData("comments", "*", "content_ID IN ($content_list)");
				if (sizeof($comments) > 0) {
					$data['comments'] = $comments;
				}
			}

		}
		if (isset($exportEntities['export_calendar'])) {
			$calendar = calendar::getLessonCalendarEvents($this);
			if (sizeof($calendar) > 0) {
				$data['calendar'] = $calendar;
			}
		}

		if (isset($exportEntities['export_glossary'])) {
			$glossary = eF_getTableData("glossary", "*", "lessons_ID = ".$this -> lesson['id']);
			if (sizeof($glossary) > 0) {
				$data['glossary'] = $glossary;
			}
		}

		if (isset($exportEntities['export_announcements'])) {
			$news = eF_getTableData("news", "*", "lessons_ID=".$this -> lesson['id']);
			if (sizeof($news) > 0) {
				$data['news'] = $news;
			}
		}

		if (isset($exportEntities['export_surveys'])) {
			$surveys = eF_getTableData("surveys", "*", "lessons_ID=".$this -> lesson['id']);   //prepei na ginei to   lesson_ID -> lessons_ID sti basi (ayto isos to parampsoyme eykola)
			if (sizeof($surveys) > 0) {
				$data['surveys'] = $surveys;
				$surveys_ = array();
				foreach ($surveys as $key => $value) {
					$surveys_[$value['id']] = $value;
				}
				$surveys_list    = implode(",", array_keys($surveys_));
				$questions_to_surveys = eF_getTableData("questions_to_surveys", "*", "surveys_ID IN ($surveys_list)");  // oposipote omos to survey_ID -> surveys_ID sti basi
				if (sizeof($questions_to_surveys) > 0) {					
					$data['questions_to_surveys'] = $questions_to_surveys;
				}
			}
		}	

		$lesson_conditions = eF_getTableData("lesson_conditions", "*", "lessons_ID=".$this -> lesson['id']);
		if (sizeof($lesson_conditions) > 0) {
			$data['lesson_conditions'] = $lesson_conditions;
		}

		$projects = eF_getTableData("projects", "*", "lessons_ID=".$this -> lesson['id']);
		if (sizeof($projects) > 0) {
			$data['projects'] = $projects;
		}

		$lesson_files = eF_getTableData("files", "*", "path like '".str_replace(G_ROOTPATH, '', EfrontDirectory :: normalize($this -> getDirectory()))."%'");
		if (sizeof($lesson_files) > 0) {
			$data['files'] = $lesson_files;
		}

		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
			if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
				//Export scorm tables from here over
				$scormLessonTables = array('scorm_sequencing_adlseq_map_info',
								   'scorm_sequencing_content_to_organization',
								   'scorm_sequencing_maps_info',
								   'scorm_sequencing_organizations');
				foreach ($scormLessonTables as $table) {
					$scorm_data = eF_getTableData($table, "*", "lessons_ID=".$this -> lesson['id']);
					if (sizeof($scorm_data) > 0) {
						$data[$table] = $scorm_data;
					}
				}
				$scormContentTables = array('scorm_sequencing_completion_threshold',
        							'scorm_sequencing_constrained_choice',
							        'scorm_sequencing_control_mode',
							        'scorm_sequencing_delivery_controls',
							        'scorm_sequencing_hide_lms_ui',
							        'scorm_sequencing_limit_conditions',
							        'scorm_sequencing_maps',
							        'scorm_sequencing_map_info',
							        'scorm_sequencing_objectives',
							        'scorm_sequencing_rollup_considerations',
							        'scorm_sequencing_rollup_controls',
							        'scorm_sequencing_rollup_rules',
							        'scorm_sequencing_rules');
				if ($content_list) {
					foreach ($scormContentTables as $table) {
						$scorm_data = eF_getTableData($table, "*", "content_ID IN ($content_list)");
						if (sizeof($scorm_data) > 0) {
							$data[$table] = $scorm_data;
						}
						if ($table == 'scorm_sequencing_rollup_rules' && sizeof($scorm_data) > 0) {
							$ids = array();
							foreach ($scorm_data as $value) {
								$ids[] = $value['id'];
							}
							$result = eF_getTableData('scorm_sequencing_rollup_rule', "*", "scorm_sequencing_rollup_rules_ID IN (".implode(",", $ids).")");
							$data['scorm_sequencing_rollup_rule'] = $result;
						}
						if ($table == 'scorm_sequencing_rules' && sizeof($scorm_data) > 0) {
							$ids = array();
							foreach ($scorm_data as $value) {
								$ids[] = $value['id'];
							}
							$result = eF_getTableData('scorm_sequencing_rule', "*", "scorm_sequencing_rules_ID IN (".implode(",", $ids).")");
							$data['scorm_sequencing_rule'] = $result;
						}
					}
				}

			} #cpp#endif
		} #cpp#endif
		//'scorm_sequencing_rollup_rule', 'scorm_sequencing_rule',

		// MODULES - Export module data
		// Get all modules (NOT only the ones that have to do with the user type)
		$modules = eF_loadAllModules();
		foreach ($modules as $module) {
			if ($moduleData = $module -> onExportLesson($this -> lesson['id'])) {
				$data[$module -> className] = $moduleData;
			}
		}

		file_put_contents($this -> directory.'/'."data.dat", serialize($data));                         //Create database dump file	
		if ($exportFiles) {
			$lessonDirectory = new EfrontDirectory($this -> directory);
			$file            = $lessonDirectory -> compress($this -> lesson['id'].'_exported.zip', false);  //Compress the lesson files
		} else {
			$dataFile = new EfrontFile($this -> directory.'/'."data.dat");
			$file 	  = $dataFile -> compress($this -> lesson['id'].'_exported.zip');
		}

		$newList         = FileSystemTree :: importFiles($file['path']);                                //Import the file to the database, so we can download it
		$file            = new EfrontFile(current($newList));

		$userTempDir     = $GLOBALS['currentUser'] -> user['directory'].'/temp';                        //The compressed file will be moved to the user's temp directory

		if (!is_dir($userTempDir)) {                                                                    //If the user's temp directory does not exist, create it
			$userTempDir = EfrontDirectory :: createDirectory($userTempDir, false);
			$userTempDir = $userTempDir['path'];
		}

		try {
			$existingFile = new EfrontFile($userTempDir.'/'.EfrontFile :: encode($this -> lesson['name']).'.zip');            //Delete any previous exported files
			$existingFile -> delete();
		} catch (Exception $e) {}

		if ($rename) {
			$newName = str_replace(array('"', '>', '<', '*', '?', ':'), array('&quot;', '&gt;', '&lt;', '&#42;', '&#63;', '&#58;'), $this -> lesson['name']);
			$file -> rename($userTempDir.'/'.EfrontFile :: encode($newName).'.zip', true);
		}
		unlink($this -> directory.'/'."data.dat");                                                      //Delete database dump file

		return $file;
	}

	public function scormExport() {
		$scormExportFolder = G_SCORMPATH.$this->lesson['id']."/";
		try {
			$dir = new EfrontDirectory($scormExportFolder);
			$dir -> delete();
		} catch (Exception $e) {}
		
		$htmlExportFolder  = $scormExportFolder.'html/';
		$filesExportFolder = $scormExportFolder.'html/files/';
		
		is_dir($filesExportFolder) OR mkdir($filesExportFolder, 0755, true);
		//is_dir($htmlExportFolder)  OR mkdir($htmlExportFolder, 0755, true);
		$filelist = array();
		
		$content = new EfrontContentTree($this, true);
		foreach (new EfrontContentFilterIterator(new EfrontNoSCORMFilterIterator(new EfrontVisitableAndEmptyFilterIterator(new EfrontNodeFilterIterator(new RecursiveIteratorIterator($content -> tree, RecursiveIteratorIterator :: SELF_FIRST))))) as $key => $unit) {
			$unitFiles = $unit->getFiles(true);
			$units[]   = $unit;
			$data = $unit['data'];
			foreach ($unitFiles as $file) {		
				$filePath = str_replace($this->getDirectory(), "/", EfrontFile :: encode($file['path']));							
				//Added this line in case of a space in path(urlencode makes it + and rawurlencode convers also slashes ) (#2143)
				$data = str_replace("content/lessons/".($this -> lesson['share_folder'] ? $this -> lesson['share_folder'] : $this -> lesson['id']).str_replace(' ','%20', $filePath), "files".str_replace(' ','%20', $filePath), $data);				
				
				$data = str_replace("content/lessons/".($this -> lesson['share_folder'] ? $this -> lesson['share_folder'] : $this -> lesson['id']).$filePath, "files".$filePath, $data);
				$data = str_replace("view_file.php?file=".$file['id'], "files".$filePath, $data);
			}					
			$unitContent = $this -> createSCORMHtmlFiles($data);
        	$unitFilename = $htmlExportFolder.$unit['name'].".html";
        	file_put_contents(EfrontFile :: encode($unitFilename), $unitContent);

        	$metadata = $this->getSCORMAssetMetadata($unit);
        	$metadataFilename = $htmlExportFolder.$unit['name'].".xml";
        	//file_put_contents($metadataFilename, $metadata);
        	file_put_contents(EfrontFile :: encode($metadataFilename), $metadata);
        	
        	$filelist = array_merge($filelist, $unitFiles);				
		}		
		foreach ($filelist as $file) {			
		    $filePath = (str_replace($this->getDirectory(), "", $file['path']));
			if (!is_dir($filesExportFolder.dirname($filePath))) {
				mkdir($filesExportFolder.dirname($filePath), 0755, true);
			}
		    $file -> copy($filesExportFolder.$filePath, true, false);
		    $metadata = $this->getSCORMAssetMetadata($file);
		    $metadataFilename = EfrontFile :: encode($filesExportFolder.$filePath.".xml");
		    file_put_contents($metadataFilename, $metadata);    			
		}

		/*Create manifest*/
		$prerequisites     = $this -> getSCORMPrerequisites();
		$organizations_str = $this -> buildSCORMManifestOrganizations($prerequisites);
		$resources_str     = $this -> buildSCORMManifestResources($units);
		$metadata_str      = $this -> buildSCORMManifestMetadata(0);
		
		$manifest          = $this -> buildSCORMManifestMain($metadata_str . $organizations_str . $resources_str);		
		
		file_put_contents($scormExportFolder."imsmanifest.xml", $manifest);

		/*Create functions files*/
		list($func1, $func2) = $this -> getAPIFunctions();
		file_put_contents($scormExportFolder."APIWrapper.js", $func1);
		file_put_contents($scormExportFolder."SCOFunctions.js", $func2);

		$scormDirectory = new EfrontDirectory($scormExportFolder);
		
		if (eF_checkParameter($this->lesson['name'], 'path')) {
			$filename = $this -> lesson['name'].'.zip';
		} else {
			$filename = 'SCO.zip';
		}
		$compressedFile = $scormDirectory -> compress($filename, false);	
		$scormDirectory -> delete();

		return $compressedFile;
	}

	private function getAPIFunctions()
	{
		$func1 = '
        var startDate;
        var exitPageStatus;

        function loadPage()
        {
           var result = doLMSInitialize();

           var status = doLMSGetValue( "cmi.core.lesson_status" );

           if (status == "not attempted")
           {
              // the student is now attempting the lesson
              doLMSSetValue( "cmi.core.lesson_status", "incomplete" );
           }

           exitPageStatus = false;
           startTimer();
        }


        function startTimer()
        {
           startDate = new Date().getTime();
        }

        function computeTime()
        {
           if ( startDate != 0 )
           {
              var currentDate = new Date().getTime();
              var elapsedSeconds = ( (currentDate - startDate) / 1000 );
              var formattedTime = convertTotalSeconds( elapsedSeconds );
           }
           else
           {
              formattedTime = "00:00:00.0";
           }

           doLMSSetValue( "cmi.core.session_time", formattedTime );
        }

        function doBack()
        {
           doLMSSetValue( "cmi.core.exit", "suspend" );

           computeTime();
           exitPageStatus = true;

           var result;

           result = doLMSCommit();

            // NOTE: LMSFinish will unload the current SCO.  All processing
            //       relative to the current page must be performed prior
            //       to calling LMSFinish.

           result = doLMSFinish();

        }

        function doContinue( status )
        {
           // Reinitialize Exit to blank
           doLMSSetValue( "cmi.core.exit", "" );

           var mode = doLMSGetValue( "cmi.core.lesson_mode" );

           if ( mode != "review"  &&  mode != "browse" )
           {
              doLMSSetValue( "cmi.core.lesson_status", status );
           }

           computeTime();
           exitPageStatus = true;

           var result;
           result = doLMSCommit();
            // NOTE: LMSFinish will unload the current SCO.  All processing
            //       relative to the current page must be performed prior
            //       to calling LMSFinish.

           result = doLMSFinish();

        }

        function doQuit( status )
        {
           computeTime();
           exitPageStatus = true;

           var result;

           result = doLMSCommit();

           result = doLMSSetValue("cmi.core.lesson_status", status);

            // NOTE: LMSFinish will unload the current SCO.  All processing
            //       relative to the current page must be performed prior
            //       to calling LMSFinish.

           result = doLMSFinish();
        }

        function unloadPage( status )
        {

            if (exitPageStatus != true)
            {
                doQuit( status );
            }


        }

        /*******************************************************************************
        ** this function will convert seconds into hours, minutes, and seconds in
        ** CMITimespan type format - HHHH:MM:SS.SS (Hours has a max of 4 digits &
        ** Min of 2 digits
        *******************************************************************************/
        function convertTotalSeconds(ts)
        {
           var sec = (ts % 60);

           ts -= sec;
           var tmp = (ts % 3600);  //# of seconds in the total # of minutes
           ts -= tmp;              //# of seconds in the total # of hours

           // convert seconds to conform to CMITimespan type (e.g. SS.00)
           sec = Math.round(sec*100)/100;

           var strSec = new String(sec);
           var strWholeSec = strSec;
           var strFractionSec = "";

           if (strSec.indexOf(".") != -1)
           {
              strWholeSec =  strSec.substring(0, strSec.indexOf("."));
              strFractionSec = strSec.substring(strSec.indexOf(".")+1, strSec.length);
           }

           if (strWholeSec.length < 2)
           {
              strWholeSec = "0" + strWholeSec;
           }
           strSec = strWholeSec;

           if (strFractionSec.length)
           {
              strSec = strSec+ "." + strFractionSec;
           }


           if ((ts % 3600) != 0 )
              var hour = 0;
           else var hour = (ts / 3600);
           if ( (tmp % 60) != 0 )
              var min = 0;
           else var min = (tmp / 60);

           if ((new String(hour)).length < 2)
              hour = "0"+hour;
           if ((new String(min)).length < 2)
              min = "0"+min;

           var rtnVal = hour+":"+min+":"+strSec;

           return rtnVal;
        }
    ';

		$func2 = '

        var _Debug = false;  // set this to false to turn debugging off
                             // and get rid of those annoying alert boxes.

        // Define exception/error codes
        var _NoError = 0;
        var _GeneralException = 101;
        var _ServerBusy = 102;
        var _InvalidArgumentError = 201;
        var _ElementCannotHaveChildren = 202;
        var _ElementIsNotAnArray = 203;
        var _NotInitialized = 301;
        var _NotImplementedError = 401;
        var _InvalidSetValue = 402;
        var _ElementIsReadOnly = 403;
        var _ElementIsWriteOnly = 404;
        var _IncorrectDataType = 405;


        // local variable definitions
        var apiHandle = null;
        var API = null;
        var findAPITries = 0;


        function doLMSInitialize()
        {
           var api = getAPIHandle();
           if (api == null)
           {
              alert("Unable to locate the LMS API Implementation.\nLMSInitialize was not successful.");
              return "false";
           }

           var result = api.LMSInitialize("");

           if (result.toString() != "true")
           {
              var err = ErrorHandler();
           }

           return result.toString();
        }

        function doLMSFinish()
        {
           var api = getAPIHandle();
           if (api == null)
           {
              alert("Unable to locate the LMS API Implementation.\nLMSFinish was not successful.");
              return "false";
           }
           else
           {
              // call the LMSFinish function that should be implemented by the API

              var result = api.LMSFinish("");
              if (result.toString() != "true")
              {
                 var err = ErrorHandler();
              }

           }

           return result.toString();
        }

        function doLMSGetValue(name)
        {
           var api = getAPIHandle();
           if (api == null)
           {
              alert("Unable to locate the LMS API Implementation.\nLMSGetValue was not successful.");
              return "";
           }
           else
           {
              var value = api.LMSGetValue(name);
              var errCode = api.LMSGetLastError().toString();
              if (errCode != _NoError)
              {
                 // an error was encountered so display the error description
                 var errDescription = api.LMSGetErrorString(errCode);
                 alert("LMSGetValue("+name+") failed. \n"+ errDescription);
                 return "";
              }
              else
              {

                 return value.toString();
              }
           }
        }

        function doLMSSetValue(name, value)
        {
           var api = getAPIHandle();
           if (api == null)
           {
              alert("Unable to locate the LMS API Implementation.\nLMSSetValue was not successful.");
              return;
           }
           else
           {
              var result = api.LMSSetValue(name, value);
              if (result.toString() != "true")
              {
                 var err = ErrorHandler();
              }
           }

           return;
        }

        function doLMSCommit()
        {
           var api = getAPIHandle();
           if (api == null)
           {
              alert("Unable to locate the LMS API Implementation.\nLMSCommit was not successful.");
              return "false";
           }
           else
           {
              var result = api.LMSCommit("");
              if (result != "true")
              {
                 var err = ErrorHandler();
              }
           }

           return result.toString();
        }

        function doLMSGetLastError()
        {
           var api = getAPIHandle();
           if (api == null)
           {
              alert("Unable to locate the LMS API Implementation.\nLMSGetLastError was not successful.");
              return _GeneralError;
           }

           return api.LMSGetLastError().toString();
        }

        function doLMSGetErrorString(errorCode)
        {
           var api = getAPIHandle();
           if (api == null)
           {
              alert("Unable to locate the LMS API Implementation.\nLMSGetErrorString was not successful.");
           }

           return api.LMSGetErrorString(errorCode).toString();
        }

        function doLMSGetDiagnostic(errorCode)
        {
           var api = getAPIHandle();
           if (api == null)
           {
              alert("Unable to locate the LMS API Implementation.\nLMSGetDiagnostic was not successful.");
           }

           return api.LMSGetDiagnostic(errorCode).toString();
        }

        function LMSIsInitialized()
        {

           var api = getAPIHandle();
           if (api == null)
           {
              alert("Unable to locate the LMS API Implementation.\nLMSIsInitialized() failed.");
              return false;
           }
           else
           {
              var value = api.LMSGetValue("cmi.core.student_name");
              var errCode = api.LMSGetLastError().toString();
              if (errCode == _NotInitialized)
              {
                 return false;
              }
              else
              {
                 return true;
              }
           }
        }

        function ErrorHandler()
        {
           var api = getAPIHandle();
           if (api == null)
           {
              alert("Unable to locate the LMS API Implementation.\nCannot determine LMS error code.");
              return;
           }

           // check for errors caused by or from the LMS
           var errCode = api.LMSGetLastError().toString();
           if (errCode != _NoError)
           {
              // an error was encountered so display the error description
              var errDescription = api.LMSGetErrorString(errCode);

              if (_Debug == true)
              {
                 errDescription += "\n";
                 errDescription += api.LMSGetDiagnostic(null);
                 // by passing null to LMSGetDiagnostic, we get any available diagnostics
                 // on the previous error.
              }

              alert(errDescription);
           }

           return errCode;
        }

        function getAPIHandle()
        {
           if (apiHandle == null)
           {
              apiHandle = getAPI();
           }

           return apiHandle;
        }


        function findAPI(win)
        {
           while ((win.API == null) && (win.parent != null) && (win.parent != win))
           {
              findAPITries++;
              // Note: 7 is an arbitrary number, but should be more than sufficient
              if (findAPITries > 7)
              {
                 alert("Error finding API -- too deeply nested.");
                 return null;
              }

              win = win.parent;

           }
           return win.API;
        }



        function getAPI()
        {
           var theAPI = findAPI(window);
           if ((theAPI == null) && (window.opener != null) && (typeof(window.opener) != "undefined"))
           {
              theAPI = findAPI(window.opener);
           }
           if (theAPI == null)
           {
              alert("Unable to find an API adapter");
           }
           return theAPI
        }
    ';

		return array($func1, $func2);
	}

	private function buildSCORMManifestMain($str)
	{
		$manifest = '<?xml version="1.0" encoding="UTF-8"?>
<manifest identifier="SingleCourseManifest" version="1.1"
		  xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2"
		  xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2"
		  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		  xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd
							  http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd
							  http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd">';
	$manifest .= $str;
	$manifest .= '
</manifest>';

		return $manifest;
	}
	
	private function  buildSCORMManifestMetadata($metadata)
	{
		$schema         = isset($metadata['schema'])         ? $metadata['schema']         : 'ADL SCORM';
		$schemaversion  = isset($metadata['schemaversion'])  ? $metadata['schemaversion']  : '1.2';
		$adlcp_location = isset($metadata['adlcp_location']) ? $metadata['adlcp_location'] : '';

		$str='
        <metadata>
            <schema>'.
		$schema
		.'</schema>
            <schemaversion>'.
		$schemaversion
		.'</schemaversion>
            <adlcp:location>'.
		$adlcp_location
		.'</adlcp:location>
        </metadata>';

		return $str;
	}
	
	private function buildSCORMManifestResources($units)
	{		

		$resource_str   = '';
		$dependency_str = '';

		for ($i = 0 ; $i < sizeof($units) ; $i++) {
			$resource_str .= '
			<resource identifier="' . $units[$i]['id'] . '" type="webcontent" adlcp:scormtype="sco" href="html/' . rawurlencode(EfrontFile :: encode($units[$i]['name'])) . '.html">
				<metadata></metadata>
				<file href="html/' .rawurlencode(EfrontFile :: encode($units[$i]['name'])) . '.html"/>
				<dependency identifierref="dep_SPECIAL"/>';

			$unitFiles = $units[$i]->getFiles(true);			
			for ($j = 0 ; $j < sizeof($unitFiles) ; $j++) {
				$file = str_replace($this->getDirectory(), "", $unitFiles[$j]['path']);
				$resource_str   .= '
				<dependency identifierref="dep_' . $i . '_' . $j . '"/>';
				$dependency_str .= '
					<resource identifier="dep_' . $i . '_' . $j . '" type="webcontent" adlcp:scormtype="asset" href="'.rawurlencode(EfrontFile :: encode($file)).'">
						<metadata></metadata>
						<file href="'.rawurlencode(EfrontFile :: encode($file)).'"/>
					</resource>';
			}
			$resource_str .= '
			</resource>';
		}

		$SPECIAL_str = '
			<resource identifier="dep_SPECIAL" adlcp:scormtype="asset" type="webcontent">
				<file href="SCOFunctions.js"/>
				<file href="APIWrapper.js"/>
			</resource>';

		$final_str = '
		<resources>' . $resource_str . $dependency_str . $SPECIAL_str. '
		</resources>';
		return $final_str;

	}
	
	private function buildSCORMManifestOrganizations($prerequisites)
	{
		
		
		$tree = eF_getContentTree($nouse, $this->lesson['id'], 0);
		//$cTree = new EfrontContentTree($lessons_id);
		for ($i = 0 ; $i < sizeof($tree) ; $i++) {
			$levels[$i] = $tree[$i]['level'];
		}

		for ($i = max($levels) ; $i>= 0 ; $i--) {
			for ($j = 0 ; $j < sizeof($tree) ; $j++) {
				if ($tree[$j]["level"] == $i && $tree[$j]["ctg_type"] != "tests" && $tree[$j]["ctg_type"] != "scorm" && $tree[$j]["ctg_type"] != "scorm_test" && $tree[$j]["ctg_type"] != "feedback") {
					$tree[$j]["string"] = "
					<item identifier=\"item" . $tree[$j]["id"] . "\" identifierref=\"" . $tree[$j]["id"] . "\">
						<title>" . htmlspecialchars($tree[$j]["name"]) . "</title>";

					/*An to antikeimeno exei prerequisites, pros8ese tis katallhles grammes*/
					if ($prerequisites[$tree[$j]["id"]]) {
						//echo "<br> A".$tree[$j]["id"];
						$tree[$j]["string"] .= "
						<adlcp:prerequisites type=\"aicc_script\">item" . $prerequisites[$tree[$j]["id"]] . "</adlcp:prerequisites>";
					}

					if (isset($tree[$j]["children"])) {
						for ($k = 1 ; $k <= sizeof($tree[$j]["children"]) ; $k++) {
							$tree[$j]["string"] .= $tree[$j]["children"][$k];
						}
					}
					$tree[$j]["string"] .= "
					</item>";
					if ($tree[$j]["parent_id"] == 0) {
						$final_str .= $tree[$j]["string"];
					} else {
						for ( $m = 0 ; $m < sizeof($tree) ; $m++) {
							if ($tree[$m]["id"] == $tree[$j]["parent_id"]) {
								$tree[$m]["children"][sizeof($tree[$m]["children"]) + 1] = "\t" . $tree[$j]["string"];
							}
						}
					}
				}
			}
		}
		$content = "
	<organizations default=\"Org\">
		<organization identifier=\"Org\" structure=\"hierarchical\">
			<title>default</title>$final_str
		</organization>
	</organizations>";

		return $content;


	}
	
	private function getSCORMPrerequisites()
	{
		$rules = eF_getTableData("rules", "content_ID,rule_content_ID", "rule_type='hasnot_seen'");
		$prerequisites = array();
		foreach ($rules as $value) {
			$prerequisites[$value['content_ID']] = $value['rule_content_ID'];
		}
		return $prerequisites;

	}

	private function createSCORMHtmlFiles($content)
	{
		$page_script = '
        var apiHandle = null;
        var API = null;
        var findAPITries = 0;

        function getAPIHandle()
        {
           if (apiHandle == null)
           {
              apiHandle = getAPI();
           }

           return apiHandle;
        }
        function findAPI(win)
        {
           while ((win.API == null) && (win.parent != null) && (win.parent != win))
           {
              findAPITries++;
              // Note: 7 is an arbitrary number, but should be more than sufficient
              if (findAPITries > 7)
              {
                 alert("Error finding API -- too deeply nested.");
                 return null;
              }

              win = win.parent;

           }
           return win.API;
        }
        function getAPI()
        {
           var theAPI = findAPI(window);
           if ((theAPI == null) && (window.opener != null) && (typeof(window.opener) != "undefined"))
           {
              theAPI = findAPI(window.opener);
           }
           if (theAPI == null)
           {
              alert("Unable to find an API adapter");
           }
           return theAPI
        }

        function my_finish()
        {
            alert(getAPIHandle().LMSGetLastError());
            getAPIHandle().LMSSetValue("cmi.core.lesson_status","completed");
            alert(getAPIHandle().LMSGetLastError());
            getAPIHandle().LMSFinish("");
            alert(getAPIHandle().LMSGetLastError());
        }

        function my_start()
        {
            alert(getAPIHandle().LMSGetLastError());
            getAPIHandle().LMSInitialize("");
            alert(getAPIHandle().LMSGetLastError());
        }


    ';
		$page_head = '<html><head><script language=javascript src="../APIWrapper.js"></script><script language=javascript src="../SCOFunctions.js"></script></head><body onunload="return unloadPage(\'incomplete\')">';
		$page_tail = '
        <script language="javascript">
        loadPage();
        </script>
    <br><br>
    <input type = "button" value = "  OK  " onClick = "doQuit(\'completed\')" id=button2 name=button2>
    ';

		$overall = $page_head . $content . $page_tail;

		return $overall;
	}

	private function getSCORMAssetMetadata($el)
	{

		$metadata = '<?xml version="1.0" encoding="ISO-8859-1"?>

        <lom xmlns="http://www.imsglobal.org/xsd/imsmd_rootv1p2p1"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:schemaLocation="http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd">

            <general>
                <identifier>
                </identifier>
                <title>
                </title>
                <catalogentry>
                    <catalog>
                    </catalog>
                    <entry>
                    </entry>
                </catalogentry>
                <language>
                </language>
                <description>
                </description>
                <keyword>
                </keyword>
                <coverage>
                </coverage>
                <structure>
                </structure>
                <aggregationlevel>
                </aggregationlevel>
            </general>

            <lifecycle>
                <version>
                </version>
                <status>
                </status>
                <contribute>
                    <role>
                    </role>
                    <centity>
                    </centity>
                    <date>
                    </date>
                </contribute>
            </lifecycle>

            <metametadata>
                <identifier>
                </identifier>
                <catalogentry>
                    <catalog>
                    </catalog>
                    <entry>
                    </entry>
                </catalogentry>
                <contribute>
                    <role>
                    </role>
                    <centity>
                    </centity>
                    <date>
                    </date>
                </contribute>
                <metadatascheme>
                </metadatascheme>
                <language>
                </language>
            </metametadata>

            <technical>
                <format>
                </format>
                <size>
                </size>
                <location>
                </location>
                <requirement>
                    <type>
                    </type>
                    <name>
                    </name>
                    <minimumversion>
                    </minimumversion>
                    <maximumversion>
                    </maximumversion>
                </requirement>
                <installationremarks>
                </installationremarks>
                <otherplatformrequirements>
                </otherplatformrequirements>
                <duration>
                </duration>
            </technical>

            <educational>
                <interactivitytype>
                </interactivitytype>
                <learningresourcetype>
                </learningresourcetype>
                <interactivitylevel>
                </interactivitylevel>
                <semanticdensity>
                </semanticdensity>
                <intendedenduserrole>
                </intendedenduserrole>
                <context>
                </context>
                <typicalagerange>
                </typicalagerange>
                <difficulty>
                </difficulty>
                <typicallearningtime>
                </typicallearningtime>
                <description>
                </description>
                <language>
                </language>
            </educational>

            <rights>
                <cost>
                </cost>
                <copyrightandotherrestrictions>
                </copyrightandotherrestrictions>
                <description>
                </description>
            </rights>

            <relation>
                <kind>
                </kind>
                <resource>
                    <identifier>
                    </identifier>
                    <description>
                    </description>
                    <catalogentry>
                        <catalog>
                        </catalog>
                        <entry>
                        </entry>
                    </catalogentry>
                </resource>
            </relation>

            <annotation>
                <person>
                </person>
                <date>
                </date>
                <description>
                </description>
            </annotation>

            <classification>
                <purpose>
                </purpose>
                <taxonpath>
                    <source>
                    </source>
                    <taxon>
                        <id>
                        </id>
                        <entry>
                        </entry>
                        <taxon>
                            <id>
                            </id>
                            <entry>
                            </entry>
                            <taxon>
                            </taxon>
                        </taxon>
                    </taxon>
                </taxonpath>
                <description>
                </description>
                <keyword>
                </keyword>
            </classification>


        </lom>

    ';

		return $metadata;
	}

	public function export2() {
		try {
			$dom  = new DomDocument();
			$id = $dom -> createAttribute('id');//
			$id -> appendChild($dom -> createTextNode($this -> lesson['id']));
			$lessonNode = $dom -> createElement("lesson");
			$lessonNode -> appendChild($id);
			$lessonNode = $dom -> appendChild($lessonNode);

			$parentNodes[0] = $lessonNode;

			$lessonContent  = new EfrontContentTree($this -> lesson['id']);

			foreach ($iterator = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($lessonContent -> tree), RecursiveIteratorIterator :: SELF_FIRST)) as $key => $properties) {
				$result = eF_getTableData("content", "*", "id=$key");

				$contentNode   = $dom -> appendChild($dom -> createElement("content"));     //<content></content>
				$parentNodes[$iterator -> getDepth() + 1] = $contentNode;

				$attribute = $contentNode -> appendChild($dom -> createAttribute('id'));    //<content id = ""></content>
				$attribute -> appendChild($dom -> createTextNode($key));    //<content id = "CONTENTID:32"></content>

				foreach ($result[0] as $element => $value) {
					if ($element == 'data') $value = htmlentities($value);
					if ($element != 'id' && $element != 'previous_content_ID' && $element != 'parent_content_ID' && $element != 'lessons_ID') {
						$element = $contentNode -> appendChild($dom -> createElement($element));
						$element -> appendChild($dom -> createTextNode($value));
					}
				}
				/*
				 if ($properties['ctg_type'] == 'tests') {
				 $result = eF_getTableData("tests", "*", "content_ID=$key");
				 foreach ($result[0] as $element => $value) {

				 }
				 }
				 */
				$parentNodes[$iterator -> getDepth()] -> appendChild($contentNode);    //<lesson><content></content></lesson>
			}
			header("content-type:text/xml");
			echo (($dom -> saveXML()));
			//$content = eF_getTableData("content", "*", "lessons_ID=".$this -> lesson['id']);
		} catch (Exception $e) {
			pr($e);
		}

	}


	/**
	 * Get system lessons
	 *
	 * This function is used used to return a list with all the system
	 * lessons.
	 * <br/>Example:
	 * <code>
	 * $lessons = EFrontLesson :: getLessons();
	 * </code>
	 *
	 * @return array The lessons list
	 * @since 3.5.0
	 * @access public
	 * @static
	 */
	public static function getLessons($returnObjects = false, $instances = false) {
		$result = eF_getTableData("lessons l, directions d", "l.*, d.name as direction_name", "l.directions_ID=d.id and l.archive=0".(!$instances ? " and l.instance_source=0" : null), "l.name");

		foreach ($result as $value) {
			if ($returnObjects){
				$lessons[$value['id']] = new EfrontLesson($value);
			} else {
				$value['info']    = unserialize($value['info']);
				$lessons[$value['id']] = $value;
			}
		}

		return $lessons;
	}
	

	public static function getAllLessons($constraints = array()) {
		list($where, $limit, $orderby) = EfrontLesson :: convertLessonConstraintsToSqlParameters($constraints);
		$result	= eF_getTableData("lessons l", "l.*", implode(" and ", $where), $orderby, false, $limit);
		return $result;
	}

	public static function countAllLessons($constraints = array()) {
		list($where, $limit, $orderby) = EfrontLesson :: convertLessonConstraintsToSqlParameters($constraints);
		$result = eF_countTableData("lessons l", "l.id", implode(" and ", $where));
		return $result[0]['count'];
	}

	public static function convertLessonConstraintsToSqlParameters($constraints) {
		$where = self::addWhereConditionToLessonConstraints($constraints);
		$limit = self::addLimitConditionToConstraints($constraints);
		$order = self::addSortOrderConditionToConstraints($constraints);
	
		return array($where, $limit, $order);
	}
	private static function addWhereConditionToLessonConstraints($constraints) {
		$where = array();
		if (isset($constraints['archive'])) {
			$constraints['archive'] ? $where[] = 'l.archive!=0' : $where[] = 'l.archive=0';
		}
		if (isset($constraints['active'])) {
			$constraints['active'] ? $where[] = 'l.active=1' : $where[] = 'l.active=0';
		}
		
	
		if (isset($constraints['filter']) && eF_checkParameter($constraints['filter'], 'text')) {
			$constraints['filter'] = trim(urldecode($constraints['filter']), "||||");
			$result 	 = eF_describeTable("lessons");
			$tableFields = array();
			foreach ($result as $value) {
				$tableFields[] = "l.".$value['Field'].' like "%'.$constraints['filter'].'%"';
			}
			$where[] = "(".implode(" OR ", $tableFields).")";
		}
		if (isset($constraints['condition'])) {
			$where[] = $constraints['condition'];
		}
		if (isset($constraints['table_filters'])) {
			foreach ($constraints['table_filters'] as $constraint) {
				$where[] = $constraint['condition'];
			}
		}
		return $where;
	}
	private static function addSortOrderConditionToConstraints($constraints) {
		$order = '';
		if (isset($constraints['sort']) && eF_checkParameter($constraints['sort'], 'alnum_with_spaces')) {
			$order = $constraints['sort'];
			if (isset($constraints['order']) && in_array($constraints['order'], array('asc', 'desc'))) {
				$order .= ' '.$constraints['order'];
			}
		}
		return $order;
	}
	private static function addLimitConditionToConstraints($constraints) {
		$limit = '';
		if (isset($constraints['limit']) && eF_checkParameter($constraints['limit'], 'int') && $constraints['limit'] > 0) {
			$limit = $constraints['limit'];
		}
		if ($limit && isset($constraints['offset']) && eF_checkParameter($constraints['offset'], 'int') && $constraints['offset'] >= 0) {
			$limit = $constraints['offset'].','.$limit;
		}
		return $limit;
	}	
	/**
	 * Get system lessons that do not currently belong to a course
	 *
	 * This function is used used to return a list with all stand-alone
	 * system lessons.
	 * <br/>Example:
	 * <code>
	 * $independent_lessons = EFrontLesson :: getStandAloneLessons();
	 * </code>
	 *
	 * @param boolean $returnObjects whether to return objects
	 * @return array The lessons list
	 * @since 3.5.0
	 * @access public
	 * @static
	 */
	public static function getStandAloneLessons($returnObjects = false) {
		$result = eF_getTableData("lessons l, directions d", "l.*, d.name as direction_name", "l.directions_ID=d.id AND l.course_only=0");
		foreach ($result as $value) {
			$value['info']    = unserialize($value['info']);
			if ($returnObjects) {
				$lessons[$value['id']] = new EfrontLesson($value);
			} else {
				$lessons[$value['id']] = $value;
			}
		}
		return $lessons;
	}

	/**
	 * Get options
	 *
	 * This function is used to get the lesson specified options
	 * <br/>Example:
	 * <code>
	 * $options = array('theory', 'tests');
	 * $lesson -> getOptions($options);             //Get the values of 'theory' and 'tests' options
	 * $lesson -> getOptions();                     //Get all options
	 * </code>
	 *
	 * @param array $options An array of lesson options
	 * @return array The values of the requested options
	 * @since 3.5.0
	 * @access public
	 */
	public function getOptions($options) {
		if ($options && !is_array($options)) {
			$options = array($options);
		}

		if (sizeof($options) > 0) {
			$requestedOptions = array();
			foreach ($options as $value) {
				if (isset($this -> options[$value])) {
					$requestedOptions[$value] = $this -> options[$value];
				}
			}
			return $requestedOptions;
		} else {
			return $this -> options;
		}

	}

	/**
	 * Set options
	 *
	 * This function sets the lesson options, based on the array
	 * specified
	 * <br/>Example:
	 * <code>
	 * $options = array('theory' => 1, 'tests' => 0);
	 * $lesson -> setOptions($options);
	 * </code>
	 *
	 * @param array $options An array of lesson options
	 * @since 3.5.0
	 * @access public
	 */
	public function setOptions($options) {
		foreach ($options as $key => $value) {
			if (isset($this -> options[$key])) {
				$this -> options[$key] = $value;
			}
		}
		$this -> lesson['options'] = serialize($this -> options);
		eF_updateTableData("lessons", array("options" => $this -> lesson['options']), "id=".$this -> lesson['id']);
	}

	/**
	 * Store database values
	 *
	 * This function is used to store changed lesson properties
	 * to the database.
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(4);           //Instantiate lesson with id 4
	 * $lesson -> lesson['name'] = 'new name';  //Change a lesson's property, for example its name
	 * $lesson -> persist();                    //Store any changed values to the database
	 * </code>
	 *
	 * @since 3.5.0
	 * @access public
	 */
	public function persist() {
		$localeSettings = localeconv();
		$fields = array('name'            => $this -> lesson['name'],
                        'directions_ID'   => $this -> lesson['directions_ID'],
                        'info'            => $this -> lesson['info'],
                        'price'           => str_replace($localeSettings['decimal_point'], '.', $this -> lesson['price']),
                        'active'          => $this -> lesson['active'],
                        'duration'        => $this -> lesson['duration']     ? $this -> lesson['duration']     : 0,
						'access_limit'    => $this -> lesson['access_limit'] ? $this -> lesson['access_limit'] : 0,
		                'share_folder'    => $this -> lesson['share_folder'] ? $this -> lesson['share_folder'] : 0,
						'show_catalog'    => $this -> lesson['course_only']	? 1 : $this -> lesson['show_catalog'],  //if lesson is available only via course, it can not be hidden from catalog
                        'options'         => serialize($this -> options),
                        'languages_NAME'  => $this -> lesson['languages_NAME'],
                        'metadata'        => $this -> lesson['metadata'],
                        'course_only'     => $this -> lesson['course_only'],
                        'certificate'     => $this -> lesson['certificate'],
		//                        'auto_certificate'=> $this -> lesson['auto_certificate'],
		//                        'auto_complete'   => $this -> lesson['auto_complete'],
		                'publish'         => $this -> lesson['publish'] ? 1 : 0,
                        'max_users'       => $this -> lesson['max_users']     ? $this -> lesson['max_users']     : null,
                        'from_timestamp'  => $this -> lesson['from_timestamp'] ? $this -> lesson['from_timestamp'] : 0,
                        'to_timestamp'    => $this -> lesson['to_timestamp'] ? $this -> lesson['to_timestamp'] : 0,
                        'shift'           => $this -> lesson['shift'],
                        'archive'         => $this -> lesson['archive'],
        				'created'		  => $this -> lesson['created']);


		if (!eF_updateTableData("lessons", $fields, "id=".$this -> lesson['id'])) {
			throw new EfrontUserException(_DATABASEERROR, EfrontUserException :: DATABASE_ERROR);
		}

		EfrontSearch :: removeText('lessons', $this -> lesson['id'], 'title');                                      //Refresh the search keywords
		EfrontSearch :: insertText($fields['name'], $this -> lesson['id'], "lessons", "title");
	}


	/**
	 * Get lesson conditions
	 *
	 * This function can be used to retrieve the conditions set
	 * for the current lesson
	 * <br/>Example:
	 * <code>
	 * $lesson -> getConditions();                  //Returns an array with the lesson conditions
	 * </code>
	 *
	 * @param array $conditions The conditions array, as a result query
	 * @return array The lesson conditions
	 * @since 3.5.0
	 * @access public
	 */
	public function getConditions($conditions = false) {
		if ($this -> conditions === false) {
			if (!$conditions) {
				$conditions = eF_getTableData("lesson_conditions", "*", "lessons_ID=".$this -> lesson['id']);
			}
			$this -> conditions = array();
			foreach ($conditions as $value) {
				$value['options'] = unserialize($value['options']);
				$this -> conditions[$value['id']] = $value;
			}
		}
		return $this -> conditions;
	}

	/**
	 * Delete lesson conditions
	 *
	 * This function is used to delete one or more lesson conditions
	 * <br/>Example:
	 * <code>
	 * $lesson -> deleteConditions(3);                                  //Delete condition with id 3
	 * $lesson -> deleteConditions(array(3, 6, 34));                    //Delete conditions with ids 3,6 and 34
	 * </code>
	 *
	 * @param mixed $conditions An id or an array of ids
	 * @return array The remaining conditions
	 * @since 3.5.0
	 * @access public
	 */
	public function deleteConditions($conditions) {
		if ($this -> conditions === false) {                            //Initialize conditions, if you haven't done so
			$this -> getConditions();
		}
		if (!is_array($conditions)) {                                    //Convert single condition to array
			$conditions = array($conditions);
		}
		foreach ($conditions as $conditionId) {
			if (eF_checkParameter($conditionId, 'id') && in_array($conditionId, array_keys($this -> conditions))) {
				eF_deleteTableData("lesson_conditions", "id=$conditionId");
				unset($this -> rules[$conditionId]);
			}
		}
		return $this -> conditions;
	}

	/**
	 * Get lesson projects
	 *
	 * This function is usd to retrieve the projects of this lesson
	 * <br/>Example:
	 * <code>
	 * $lesson = new EfrontLesson(65);                          //Create new lesson object
	 * $projectList = $lesson -> getProjects();                 //Get all projects for this lesson
	 * $projectList = $lesson -> getProjects(true);             //Get all projects for this lesson as an EfrontProject objects list
	 * $projectList = $lesson -> getProjects(true, 'jdoe');     //Get projects assigned to 'jdoe' for this lesson, as an EfrontProject objects list
	 * </code>
	 *
	 * @param boolean $returnObjects Whether to return EfrontProject objects or just an array with projects properties
	 * @param string $login If specified, return projects only assigned to this user
	 * @return array an array of lesson projects
	 * @since 3.5.0
	 * @access public
	 */
	public function getProjects($returnObjects = false, $login = false, $nonExpired = false) {
		if ($login instanceof EfrontUser) {
			$login = $login -> user['login'];
		}

		if ($login && eF_checkParameter($login, 'login')) {
			!$nonExpired ? $result = eF_getTableData("projects p, users_to_projects up", "p.*, up.grade, up.text_grade, up.comments, up.filename,up.last_comment", "up.users_LOGIN = '$login' and up.projects_ID = p.id and p.lessons_ID=".$this -> lesson['id']) : $result = eF_getTableData("projects p, users_to_projects up", "p.*, up.grade, up.text_grade, up.comments, up.filename, up.last_comment", "p.deadline > ".time()." and up.users_LOGIN = '$login' and up.projects_ID = p.id and p.lessons_ID=".$this -> lesson['id']);
		} else {
			!$nonExpired ? $result = eF_getTableData("projects", "*", "lessons_ID=".$this -> lesson['id']) : $result = eF_getTableData("projects", "*", "deadline > ".time()." and lessons_ID=".$this -> lesson['id']);
		}
		$projects = array();
		foreach ($result as $value) {
			$returnObjects ? $projects[$value['id']] = new EfrontProject($value) : $projects[$value['id']] = $value;
		}

		return $projects;
	}

	/**
	 * Print a link with tooltip
	 *
	 * This function is used to print a lesson link with a popup tooltip
	 * containing information on this lesson. The link must be provided
	 * and optionally the information.
	 * <br/>Example:
	 * <code>
	 * echo $lesson -> toHTMLTooltipLink('student.php?ctg=control_panel&lessons_ID=2');
	 * </code>
	 *
	 * @param string $link The link to print
	 * @param array $lessonInformation The information to display (According to the EfrontLesson :: getInformation() format)
	 * @since 3.5.0
	 * @access public
	 */
	public function toHTMLTooltipLink($link, $lessonInformation = false) {
		if (EfrontUser::isOptionVisible('tooltip')) {
			if (!$lessonInformation) {
				$lessonInformation = $this -> getInformation();
			}

			sizeof($lessonInformation['content']) > 0 || sizeof($lessonInformation['tests']) > 0 ? $classes[] = 'nonEmptyLesson' : $classes[] = 'emptyLesson';     //Display the link differently depending on whether it has content or not
			if (!$link) {
				$link      = 'javascript:void(0)';
				$classes[] = 'inactiveLink';
			}

			if ($lessonInformation['professors']) {
				foreach ($lessonInformation['professors'] as $value) {
					$professorsString[] = $value['name'].' '.$value['surname'];
				}
				$lessonInformation['professors'] = implode(", ", $professorsString);
			}
			foreach ($lessonInformation as $key => $value) {
				if ($value) {
					switch ($key) {
						case 'professors'         : $tooltipInfo[] = '<strong>'._PROFESSORS."</strong>: $value<br/>";         break;
						case 'content'            : $tooltipInfo[] = '<strong>'._CONTENTUNITS."</strong>: $value<br/>";       break;
						case 'tests'              : $tooltipInfo[] = '<strong>'._TESTS."</strong>: $value<br/>";              break;
						case 'projects'           : EfrontUser::isOptionVisible('projects') ? $tooltipInfo[] = '<strong>'._PROJECTS."</strong>: $value<br/>" : null; break;
						case 'course_dependency'  : $tooltipInfo[] = '<strong>'._DEPENDSON."</strong>: $value<br/>";          break;
						case 'from_timestamp'     : $tooltipInfo[] = '<strong>'._AVAILABLEFROM."</strong>: ".formatTimestamp($value, 'time_nosec')."<br/>";break;
						case 'to_timestamp'       : $tooltipInfo[] = '<strong>'._AVAILABLEUNTIL."</strong>: ".formatTimestamp($value, 'time_nosec')."<br/>"; break;
						case 'general_description': $tooltipInfo[] = '<strong>'._DESCRIPTION."</strong>: $value<br/>"; break;
						case 'assessment'         : $tooltipInfo[] = '<strong>'._ASSESSMENT."</strong>: $value<br/>";         break;
						case 'objectives'         : $tooltipInfo[] = '<strong>'._OBJECTIVES."</strong>: $value<br/>";         break;
						case 'lesson_topics'      : $tooltipInfo[] = '<strong>'._LESSONTOPICS."</strong>: $value<br/>";       break;
						case 'resources'          : $tooltipInfo[] = '<strong>'._RESOURCES."</strong>: $value<br/>";          break;
						case 'other_info'         : $tooltipInfo[] = '<strong>'._OTHERINFO."</strong>: $value<br/>";          break;
						default: break;
					}
				}
			}
			if (sizeof($tooltipInfo) > 0) {
				$classes[]     = 'info';
				$tooltipString = '
					<a href = "'.$link.'" class = "'.implode(" ", $classes).'" style = "vertical-align:middle;">
						'.$this -> lesson['name'].'
						<span class = "tooltipSpan">
						'.implode("", $tooltipInfo).'</span></a>';
			} else {
				$tooltipString = '
					<a href = "'.$link.'" class = "'.implode(" ", $classes).'" style = "vertical-align:middle;">
						'.$this -> lesson['name'].'</a>';
			}
		} else {
			$tooltipString = '
					<a href = "'.$link.'" class = "'.implode(" ", $classes).'" style = "vertical-align:middle;">
						'.$this -> lesson['name'].'</a>';
		}
		return $tooltipString;
	}



	/**
	 * Get all skills: for the skills this lesson offers the lesson_ID value will be filled
	 *
	 * <br/>Example:
	 * <code>
	 * $skillsOffered = $lesson -> getSkills();
	 * </code>
	 *
	 * @param $only_own set true if only the skills of this lesson are to be returned and not all skills
	 * @return an array with skills where each record has the form [skill_ID] => [lesson_ID, description, specification,skill_ID, categories_ID]
	 * @since 3.5.0
	 * @access public
	 */
	public function getSkills($only_own = false) {
		if (!isset($this -> skills) || !$this -> skills) {
			$this -> skills = false;        //Initialize skills to something

			$skills = eF_getTableData("module_hcd_skills LEFT OUTER JOIN module_hcd_lesson_offers_skill ON (module_hcd_lesson_offers_skill.skill_ID = module_hcd_skills.skill_ID AND module_hcd_lesson_offers_skill.lesson_ID='".$this -> lesson['id']."')", "description,specification, module_hcd_skills.skill_ID,lesson_ID,categories_ID","");

			foreach ($skills as $key => $skill) {
				if ($only_own && $skill['lesson_ID'] != $this -> lesson['id']) {
					unset($skills[$key]);
				} else {
					$skID = $skill['skill_ID'];
					$this -> skills[$skID] = $skill;
				}
			}
		}
		return $this -> skills;
	}



	/**
	 * Get all branches: for the branches this lesson offers the lesson_ID value will be filled
	 *
	 * <br/>Example:
	 * <code>
	 * $branchesOfLesson = $lesson -> getBranches();
	 * </code>
	 *
	 * @param $only_own set true if only the branches of this lesson are to be returned and not all branches
	 * @return an array with branches where each record has the form [branch_ID] => [lesson_ID]
	 * @since 3.6.0
	 * @access public
	 */
	public function getBranches($only_own = false) {
		if (!isset($this -> branches) || !$this -> branches) {
			$this -> branches = false;        //Initialize branches to something

			$branches = eF_getTableData("module_hcd_branch LEFT OUTER JOIN module_hcd_branch as branch1 ON module_hcd_branch.father_branch_ID = branch1.branch_ID LEFT OUTER JOIN module_hcd_lesson_to_branch ON (module_hcd_lesson_to_branch.branches_ID = module_hcd_branch.branch_ID AND module_hcd_lesson_to_branch.lessons_ID='".$this -> lesson['id']."')", "module_hcd_branch.*, module_hcd_branch.branch_ID as branches_ID, module_hcd_lesson_to_branch.lessons_ID, branch1.name as father","");

			foreach ($branches as $key => $branch) {
				if ($only_own && $branch['lessons_ID'] != $this -> lesson['id']) {

					unset($branches[$key]);
				} else {
					$bID = $branch['branches_ID'];
					$this -> branches[$bID] = $branch;
				}
			}
		}
		return $this -> branches;
	}


	/**
	 * Insert the skill corresponding to this lesson: Every lesson is mapped to a skill like "Knowledge of that lesson"
	 * This insertion takes place when a lesson is changed from course_only to regular lesson
	 *
	 * <br/>Example:
	 * <code>
	 * $lesson -> insertLessonSkill();
	 * </code>
	 *
	 * @return the id of the newly created record in the module_hcd_lesson_offers_skill table or false if something went wrong
	 * @since 3.5.2
	 * @access public
	 */
	public function insertLessonSkill() {
		$this->skills=false;
		// If insertion of a self-contained lesson add the corresponding skill
		// Insert the corresponding lesson skill to the skill and lesson_offers_skill tables
		$lessonSkillId = eF_insertTableData("module_hcd_skills", array("description" => _KNOWLEDGEOFLESSON . " ". $this -> lesson['name'], "categories_ID" => -1));

		// Insert question to lesson skill records for all lesson questions
		$questions = eF_getTableData("questions", "id", "lessons_ID = ". $this ->lesson['id']);
		$insert_string = "";
		foreach ($questions as $question) {
			if ($insert_string != "") {
				$insert_string .= ",('"  . $question['id'].  "','" .  $lessonSkillId . "',2)";
			} else {
				$insert_string .= "('".$question['id']."','".$lessonSkillId."',2)";
			}
		}


		if ($insert_string != "") {
			eF_executeNew("INSERT INTO questions_to_skills VALUES " . $insert_string);
		}

		return eF_insertTableData("module_hcd_lesson_offers_skill", array("lesson_ID" => $this -> lesson['id'], "skill_ID" => $lessonSkillId));
	}

	/**
	 * Function to remove all course inherited skills by all courses where this lesson belongs
	 */
	public function removeCoursesInheritedSkills() {

		$courses = $this -> getCourses(true);
		foreach ($courses as $course) {
			$courseSkill = $course -> getCourseSkill();
			if ($courseSkill) {
				eF_deleteTableData("questions_to_skills", "skills_ID = " . $courseSkill['skill_ID']);
			}
		}


	}

	/**
	 * Delete the skill corresponding to this lesson: Every lesson is mapped to a skill like "Knowledge of that lesson"
	 * This deletion takes place when a lesson is changed from regular lesson to course_only
	 *
	 * <br/>Example:
	 * <code>
	 * $lesson -> deleteLessonSkill();
	 * </code>
	 *
	 * @return the result of the table deletion
	 * @since 3.5.2
	 * @access public
	 */
	public function deleteLessonSkill() {

		// Delete the corresponding lesson skill to the skill and lesson_offers_skill tables
		$lesson_skill = eF_getTableData("module_hcd_skills JOIN module_hcd_lesson_offers_skill ON module_hcd_skills.skill_ID = module_hcd_lesson_offers_skill.skill_ID","*", "lesson_ID = ". $this -> lesson['id'] . " AND module_hcd_skills.categories_ID = -1");

		eF_deleteTableData("module_hcd_skills", "skill_ID = ". $lesson_skill[0]['skill_ID']);

		// Delete all question-to-lesson specific skill assignments
		$questions = eF_getTableDataFlat("questions", "id", "lessons_ID = ". $this ->lesson['id']);
		eF_deleteTableData("questions_to_skills", "questions_id IN ('".implode("','",$questions['id'])."') AND skills_ID = " . $lesson_skill[0]['skill_ID']);

		return eF_deleteTableData("module_hcd_lesson_offers_skill", "lesson_ID = " . $this -> lesson['id'] .  " AND skill_ID = " . $lesson_skill[0]['skill_ID']);
	}

	/**
	 * Get the skill corresponding to this lesson: Every lesson that is not course_only is
	 * mapped to a skill like "Knowledge of that lesson"
	 * <br/>Example:
	 * <code>
	 * $lesson_skill = $lesson -> getLessonSkill();
	 * </code>
	 *
	 * @return An array of the form [skill_ID] => [lesson_ID, description, specification,skill_ID, categories_ID]
	 * @since 3.5.2
	 * @access public
	 */
	public function getLessonSkill() {

		if (G_VERSIONTYPE == 'educational') { #cpp#ifdef EDUCATIONAL
			// Only the educational version has lesson specific skills
			$skills = $this -> getSkills();
			// In case of a change from enterprise to educational version, extra skills with cat_ID != -1 might be correlated with this lesson
			foreach ($skills as $skid => $skill) {
				if ($skill['lesson_ID'] == $this -> lesson['id'] && $skill['categories_ID'] == -1) {
					return $skill;
				}
			}

			// The default lesson skill was not found
			$this -> insertLessonSkill();
			$skills = $this -> getSkills();
			// In case of a change from enterprise to educational version, extra skills with cat_ID != -1 might be correlated with this lesson
			foreach ($skills as $skid => $skill) {
				if ($skill['lesson_ID'] == $this -> lesson['id'] && $skill['categories_ID'] == -1) {
					return $skill;
				}
			}
				
		} #cpp#endif
		return false;
	}


	/**
	 * Assign a skill to this lesson or update an existing skill description
	 *
	 * This function is used to correlate a skill to the lesson - if the
	 * lesson is completed then this skill is assigned to the user that completed it
	 *
	 * <br/>Example:
	 * <code>
	 * $lesson -> assignSkill(2, "Beginner PHP knowledge");   // The lesson will offer skill with id 2 and "Beginner PHP knowledge"
	 * </code>
	 *
	 * @param $skill_ID the id of the skill to be assigned
	 * @return boolean true/false
	 * @since 3.5.0
	 * @access public
	 */
	public function assignSkill($skill_ID, $specification) {

		$this -> getSkills();

		// Check if the skill is not assigned as offered by this lesson
		if ($this -> skills[$skill_ID]['lesson_ID'] == "") {
			if ($ok = eF_insertTableData("module_hcd_lesson_offers_skill", array("skill_ID" => $skill_ID, "lesson_ID" => $this -> lesson['id'], "specification" => $specification))) {
				$this -> skills[$skill_ID]['lesson_ID'] = $this -> lesson['id'];
				$this -> skills[$skill_ID]['specification'] = $specification;
			} else {
				throw new EfrontLessonException(_EMPLOYEESRECORDCOULDNOTBEUPDATED, EfrontLessonException :: DATABASE_ERROR);
			}
		} else {
			if ($ok = eF_updateTableData("module_hcd_lesson_offers_skill", array("specification" => $specification), "skill_ID = '".$skill_ID."' AND lesson_ID = '". $this -> lesson['id'] ."'") ) {
				$this -> skills[$skill_ID]['specification'] = $specification;
			} else {
				throw new EfrontLessonException(_EMPLOYEESRECORDCOULDNOTBEUPDATED, EfrontLessonException :: DATABASE_ERROR);
			}
		}

		return true;
	}

	/**
	 * Remove a skill that is offered from this lesson
	 *
	 * This function is used to stop the correlation of a skill to the lesson - if the
	 * lesson is completed then this skill is assigned to the user that completed it
	 *
	 * <br/>Example:
	 * <code>
	 * $lesson -> removeSkill(2);   // The lesson will stop offering skill with id 2
	 * </code>
	 *
	 * @param $skill_ID the id of the skill to be removed from the skills to be offered list
	 * @return boolean true/false
	 * @since 3.5.0
	 * @access public
	 */
	public function removeSkill($skill_ID) {

		$this -> getSkills();

		// Check if the skill is not assigned as offered by this lesson
		if ($this -> skills[$skill_ID]['lesson_ID'] == $this -> lesson['id']) {
			if ($ok = eF_deleteTableData("module_hcd_lesson_offers_skill", "skill_ID = '".$skill_ID."' AND lesson_ID = '". $this -> lesson['id'] ."'") ) {
				$this -> skills[$skill_ID]['specification'] = "";
				$this -> skills[$skill_ID]['lesson_ID'] = "";
			} else {
				throw new EfrontLessonException(_EMPLOYEESRECORDCOULDNOTBEUPDATED, EfrontLessonException :: DATABASE_ERROR);
			}
		}

		return true;
	}


	/**
	 * Assign a branch to this lesson
	 *
	 * This function is used to correlate a branch to the lesson
	 * All users of the branch should be assigned to this lesson
	 *
	 * <br/>Example:
	 * <code>
	 * $lesson -> assignBranch(2);   // The lesson will be assigned to branch with id 2
	 * </code>
	 *
	 * @param $branch_ID the id of the branch to be assigned
	 * @return boolean true/false
	 * @since 3.6.0
	 * @access public
	 */
	public function assignBranch($branch_ID) {

		$this -> getBranches();

		// Check if the branch is not assigned as offered by this lesson
		if ($this -> branches[$branch_ID]['lessons_ID'] == "") {
			if ($ok = eF_insertTableData("module_hcd_lesson_to_branch", array("branches_ID" => $branch_ID, "lessons_ID" => $this -> lesson['id']))) {
				$this -> branches[$branch_ID]['lessons_ID'] = $this -> lesson['id'];
				$newBranch = new EfrontBranch($branch_ID);
				$employees = $newBranch ->getEmployees(false,true);	//get data flat
				$this -> addUsers($employees['login'], $employees['user_type']);
			} else {
				throw new EfrontLessonException(_EMPLOYEESRECORDCOULDNOTBEUPDATED, EfrontLessonException :: DATABASE_ERROR);
			}
		}
		return true;
	}

	/**
	 * Remove association of a branch with this lesson
	 *
	 * This function is used to stop the correlation of a branch to the lesson
	 *
	 * <br/>Example:
	 * <code>
	 * $lesson -> removeBranch(2);   // The lesson will stop offering branch with id 2
	 * </code>
	 *
	 * @param $branch_ID the id of the branch to be removed from the lesson
	 * @return boolean true/false
	 * @since 3.6.0
	 * @access public
	 */
	public function removeBranch($branch_ID) {

		$this -> getBranches();

		// Check if the branch is not assigned as offered by this lesson
		if ($this -> branches[$branch_ID]['lessons_ID'] == $this -> lesson['id']) {
			if ($ok = eF_deleteTableData("module_hcd_lesson_to_branch", "branches_ID = '".$branch_ID."' AND lessons_ID = '". $this -> lesson['id'] ."'") ) {
				$this -> branches[$branch_ID]['lessons_ID'] = "";
			} else {
				throw new EfrontLessonException(_EMPLOYEESRECORDCOULDNOTBEUPDATED, EfrontLessonException :: DATABASE_ERROR);
			}
		}

		return true;
	}

	
	public static function getUserActiveTimeInUnit($login, $contentId) {
		if (eF_checkParameter($login, 'login') && eF_checkParameter($contentId, 'id')) {
			$result = eF_getTableData("users_to_content", "total_time", "users_LOGIN = '".$login."' and content_ID=".$contentId);
		}
		$totalTime = 0;
		if (!empty($result)) {
			$totalTime = $result[0]['total_time'];
		}
		return $totalTime;
	}

	public static function getUserActiveTimeInLesson($login, $lessonId) {
		if (eF_checkParameter($login, 'login') && eF_checkParameter($lessonId, 'id')) {
			$result = eF_getTableData("users_to_content", "sum(total_time) as total_time", "users_LOGIN = '".$login."' and lessons_ID=".$lessonId);
			$seconds = $result[0]['total_time'] ? $result[0]['total_time'] : 0;
			//Calculate SCORM times, as these are not counted by the system
			$scormResult  = eF_getTableData("scorm_data", "total_time", "users_LOGIN = '".$login."' and content_ID in (select id from content where lessons_ID=$lessonId and active=1 and (ctg_type='scorm' or ctg_type='scorm_test'))");
			$scormSeconds = 0;
			foreach($scormResult as $value) {
				$scormSeconds += convertTimeToSeconds($value['total_time']);
			}
			$result = eF_getTableData("completed_tests ct, tests t", "sum(ct.time_spent) as total_time_spent", "ct.users_LOGIN='".$login."' and ct.status!='deleted' and ct.tests_ID=t.id and t.lessons_ID=$lessonId");
			$result[0]['total_time_spent'] ? $testSeconds = $result[0]['total_time_spent'] : $testSeconds = 0;
			return $scormSeconds + $seconds + $testSeconds;
		}
	}

	public function getUsersActiveTimeInLesson() {
		$lessonUsers = array();
		foreach ($this->getLessonUsers(array('return_objects' => false)) as $key=>$value) {
			$lessonUsers[$key] = 0;
		}
		$result = eF_getTableData("users_to_content", "users_LOGIN, sum(total_time) as total_time", "lessons_ID=".$this->lesson['id'], "", "users_LOGIN");
		foreach ($result as $value) {
			if (isset($lessonUsers[$value['users_LOGIN']])) {
				$lessonUsers[$value['users_LOGIN']] = $value['total_time'];
			}
		}
		//Calculate SCORM times, as these are not counted by the system
		$result  = eF_getTableData("scorm_data", "users_LOGIN, total_time", "content_ID in (select id from content where lessons_ID=".$this->lesson['id']." and active=1 and (ctg_type='scorm' or ctg_type='scorm_test'))");
		foreach($result as $value) {
			if (isset($lessonUsers[$value['users_LOGIN']])) {
				$value['total_time'] = convertTimeToSeconds($value['total_time']);
				$lessonUsers[$value['users_LOGIN']] += $value['total_time'];
			}			
		}
		// ct.time_spent < 2*60*60 added in order to prevent too big values because of leaving test (closing browser etc)
		$result = eF_getTableData("completed_tests ct, tests t", "users_LOGIN, sum(ct.time_spent) as total_time", "ct.time_spent < 2*60*60 and ct.status!='deleted' and ct.tests_ID=t.id and t.lessons_ID=".$this->lesson['id'], "", "users_LOGIN");		
		foreach ($result as $value) {
			if (isset($lessonUsers[$value['users_LOGIN']])) {
				$lessonUsers[$value['users_LOGIN']] += $value['total_time'];
			}
		}
		
		return $lessonUsers;
	}
	
	
	/**
	 * Get all events related with this lesson
	 *
	 * This function is used to acquire all events related for this lesson,
	 * according to a topical timeline
	 *
	 * <br/>Example:
	 * <code>
	 * $lesson -> getEvents();   // Get all events of this lessons from the most recent to the oldest
	 * </code>
	 *
	 * @param $topic_ID the id of the topic to which the return events for the timeline should belong
	 * @param $returnObjects whether to return event objects or not
	 * @param $avatarSize the normalization size for the avatar images
	 * @param $limit maximum number of events to return
	 * @return boolean true/false
	 * @since 3.6.0
	 * @access public
	 */
	public function getEvents($topic_ID = false, $returnObjects = false, $avatarSize = 0, $limit = false) {
		if (!EfrontUser::isOptionVisible('lessons_timeline')) {
			return array();
		}

		if ($topic_ID) {
			// only current lesson users
			$users = $this -> getUsers();
			$users_logins = array_keys($users);
			if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
				// don't mix with course events - with courses_ID = $this->lesson['id']
				$related_events = eF_getTableData("events, module_hcd_employee_works_at_branch ewb", "events.*", "ewb.branch_ID= ".$_SESSION['s_current_branch']." and events.users_LOGIN=ewb.users_login and type = '".EfrontEvent::NEW_POST_FOR_LESSON_TIMELINE_TOPIC. "' AND entity_ID = '".$topic_ID."' AND lessons_ID = '". $this->lesson['id']."' AND events.users_LOGIN IN ('".implode("','", $users_logins)."') AND (type < 50 OR type >74)", "timestamp desc", "", $limit ? $limit*5 : null);
				
			} else {
				// don't mix with course events - with courses_ID = $this->lesson['id']
				$related_events = eF_getTableData("events, module_hcd_employee_works_at_branch ewb", "events.*", "ewb.branch_ID= ".$_SESSION['s_current_branch']." and events.users_LOGIN=ewb.users_login and type = '".EfrontEvent::NEW_POST_FOR_LESSON_TIMELINE_TOPIC. "' AND entity_ID = '".$topic_ID."' AND lessons_ID = '". $this->lesson['id']."' AND events.users_LOGIN IN ('".implode("','", $users_logins)."') AND (type < 50 OR type >74)", "timestamp desc", "", $limit ? $limit*5 : null);
			}
		} else {
			// only current lesson users
			$users = $this -> getUsers();
			$users_logins = array_keys($users);

			//    		if ($limit) {
			//    			$related_events = eF_getTableData("events", "*", "lessons_ID = '". $this->lesson['id']."' AND users_LOGIN IN ('".implode("','", $users_logins)."')", "timestamp desc LIMIT " . $limit);
			//
			//    		} else {
			if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
				$related_events = eF_getTableData("events, module_hcd_employee_works_at_branch ewb", "events.*", "ewb.branch_ID= ".$_SESSION['s_current_branch']." and events.users_LOGIN=ewb.users_login and lessons_ID = '". $this->lesson['id']."' AND events.users_LOGIN IN ('".implode("','", $users_logins)."')  AND (type < 50 OR type >74)	", "timestamp desc", "", $limit ? $limit*5 : null);
			} else {
				$related_events = eF_getTableData("events", "events.*", "lessons_ID = '". $this->lesson['id']."' AND events.users_LOGIN IN ('".implode("','", $users_logins)."')  AND (type < 50 OR type >74)	", "timestamp desc", "", $limit ? $limit*5 : null);
			}
			//    		}


		}
		
		


		if (!isset($avatarSize) || $avatarSize <= 0) {
			$avatarSize = 25;
		}

		$prev_event = false;
		$count = 0;

		$filtered_related_events = array();
		foreach($related_events as $key => $event) {

			$user = $users[$event['users_LOGIN']];
			$filtered_related_events[$key] = $event;
			try {
				$file = new EfrontFile($user['avatar']);
				$filtered_related_events[$key]['avatar'] = $user['avatar'];
				list($filtered_related_events[$key]['avatar_width'], $filtered_related_events[$key]['avatar_height']) = eF_getNormalizedDims($file['path'],$avatarSize, $avatarSize);
			} catch (EfrontfileException $e) {
				$filtered_related_events[$key]['avatar'] = G_SYSTEMAVATARSPATH."unknown_small.png";
				$filtered_related_events[$key]['avatar_width']  = $avatarSize;
				$filtered_related_events[$key]['avatar_height'] = $avatarSize;
			}
			// Logical combination of events
			if ($prev_event) {
				// since we have decreasing chronological order we now that $event['timestamp'] < $prev_event['timestamp']
				if ($event['users_LOGIN'] == $prev_event['event']['users_LOGIN'] && $event['type'] == $prev_event['event']['type'] && $prev_event['event']['timestamp'] - $event['timestamp'] < EfrontEvent::SAME_USER_INTERVAL) {
					unset($filtered_related_events[$prev_event['key']]);
					$count--;
				}
			}
			$prev_event = array("key"=>$key, "event"=>$event);
			if ($limit && ++$count == $limit) {
				break;
			}
		}

		if ($returnObjects) {
			$eventObjects = array();
			foreach ($filtered_related_events as $event) {
				$eventObjects[] = new EfrontEvent($event);
			}
			return $eventObjects;
		} else {
			return $filtered_related_events;
		}

	}


	/**
	 * Create lesson instance
	 *
	 * This function is used to create a lesson instance.
	 * <br/>Example:
	 * <code>
	 * $instance = EfrontLesson :: createInstance(43);
	 * </code>
	 *
	 * @param mixed $instanceSource Either a lesson id or an EfrontLesson object.
	 * @return EfrontLesson The new lesson instance
	 * @since 3.6.1
	 * @access public
	 * @static
	 */
	public static function createInstance($instanceSource, $originateCourse) {
		if (!($instanceSource instanceof EfrontLesson)) {
			$instanceSource = new EfrontLesson($instanceSource);
		}
		if (!($originateCourse instanceof EfrontCourse)) {
			$originateCourse = new EfrontCourse($originateCourse);
		}

		$result = eF_getTableData("lessons", "*", "id=".$instanceSource -> lesson['id']);

		unset($result[0]['id']);
		//unset($result[0]['directions_ID']);			//Instances don't belong to a category
		if (!$result[0]['share_folder']) {
			$result[0]['share_folder'] = $instanceSource -> lesson['id'];
		}
		//$result[0]['name'] .= ' ('._INSTANCE.')';
		$result[0]['originating_course'] = $originateCourse -> course['id'];
		$result[0]['instance_source'] 	 = $instanceSource -> lesson['id'];

		$file = $instanceSource -> export(false, true, false);
		$instance = EfrontLesson :: createLesson($result[0]);
		$instance -> import($file, true, true, true);

		$instance -> course['originating_course'] = $originateCourse -> course['id'];
		$instance -> course['instance_source'] 	  = $instanceSource -> lesson['id'];
		$instance -> persist();

		return $instance;
	}


	/**
	 * Convert a lesson argument to a lesson id
	 *
	 * @param mixed $lesson The lesson argument, can be an id or an EfrontLesson object
	 * @return int The lesson id
	 * @since 3.6.3
	 * @access public
	 * @static
	 */
	public static function convertArgumentToLessonId($lesson) {
		if ($lesson instanceOf EfrontLesson) {
			$lesson = $lesson -> lesson['id'];
		} else if (!eF_checkParameter($lesson, 'id')) {
			throw new EfrontLessonException(_INVALIDID, EfrontLessonException :: INVALID_ID);
		}

		return $lesson;
	}

	/**
	 * Convert argument to an EfrontLesson object
	 *
	 * @param mixed $lesson The parameter to convert
	 * @return EfrontLesson a new EfrontLesson object
	 * @since 3.6.3
	 * @access public
	 * @static
	 */
	public static function convertArgumentToLessonObject($lesson) {
		if (!($lesson instanceOf EfrontLesson)) {
			$lesson = new EfrontLesson($lesson);
		}
		return $lesson;
	}

}







