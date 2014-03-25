<?php
/**
 * EfrontConfiguration Class file
 *
 * @package eFront
*/

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}


/**
 * EfrontConfiguration class
 *
 * This class is used to provide a means of accessing configuration variables. It consists only of static methods.
 * @package eFront
 * @since 3.0
*/
class EfrontConfiguration
{

	const MODE_INVISIBLE = 0;
	const MODE_VISIBLE = 1;
	const MODE_VISIBLE_COMPLETE = 2;
	
    /**
     * Array containing the default configuration options
     * @since 3.0
     * @var array
     * @access private
     */
    private static $defaultOptions = array(
    	//System settings - general
        'system_email'           => 'admin@example.com',
    	'logout_redirect'	     => '',
    	'debug_mode'			 => '',
    	'updater_period'		 => 100000,
    	'last_reset_certificate' => '',
    	'cache_enabled'			 => '1',		

        //System settings - security
    	'file_black_list'        => 'php,php3,jsp,asp,cgi,pl,exe,com,bat,sh,ph3,php4,ph4,php5,ph5,phtm,phtml',
        'file_white_list'        => '',
        'ip_black_list'          => '',
        'ip_white_list'          => '*.*.*.*',
        'password_length'		 => '6',
        'force_change_password'	 => '0',
    	'password_reminder'		 => '1',
		'autologout_time'		 => '5',
    	//'inactivity_logout'		 => '',
        'encrypt_url'			 => '0',        //0: don't encrypt. 1: encrypt
        'eliminate_post_xss'	 => '1',
    	'constrain_access'		 => '1',
    	'remember_login'		 => '',
        'ban_failed_logins'		 => '0',

        //System settings - language
        'onelanguage'            => '0',
        'default_language'       => 'english',
    	'file_encoding'          => 'UTF-8',        //UTF7-IMAP for Windows servers who want to support international file names uploading

        //System settings - user activation/registration
        'activation'             => '1',
        'mail_activation'        => '0',
    	'supervisor_mail_activation' => '0',
        'signup'                 => '1',
        'show_license_note'		 => '0',
    	'insert_group_key'       => '1',                    	// 0 means 'no', 1 means 'yes'
        'lesson_enroll'			 => '1',
		'default_type'           => 'student',
    	'pm_space'				 => '',
    	'allow_direct_login' 	 => '1',

        //System settings - 3rd party tools
        'license_server'		 => 'http://keys.efrontlearning.net/list.php?version=10',
        'api'                    => '0',
    	'api_ip'				 => '127.0.0.1', 					//Set an IP to constrain the XML API
    	'virtual_keyboard'       => '1',
		'math_content'           => '0',
		'math_server'			 => 'http://www.imathas.com/cgi-bin/mimetex.cgi',
		'math_images'			 => '0',
		'phplivedocx_server'     => 'https://api.livedocx.com/1.2/mailmerge.asmx?WSDL',
		'phplivedocx_username'   => '',
		'phplivedocx_password'   => '',

        //Appearance
        'site_name'				 => 'eFront',
        'site_motto'		     => 'Refreshing eLearning',
		'motto_on_header'		 => '0',
    	'lessons_directory'      => '1',                    	//0 means 'no', 1 means 'yes', and 2 means 'only after login'
    	'collapse_catalog'		 => '0',    					// 0 means 'no', 1 means 'yes' and  2 means 'only for lessons'
        'logo'               	 => '',							// if empty, the default logo is used
    	'site_logo'              => '0',						// The actual site logo in use
    	'use_logo'               => '2',						// 0 means 'default logo', 1 means 'site logo', 2 means 'theme logo'
        'logo_max_width'		 => '200',
        'logo_max_height'		 => '150',
        'normalize_dimensions'	 => '1',
        'favicon'                => '',
        'username_format'		 => '#surname# #n#. (#login#)',    //Possible values: #name#, #n#, #surname#, #login#
        'username_format_resolve'=> '1',		//If 2 formatted usernames are the same, include the login too
        'display_empty_blocks'	 => '1',                    //0 means 'no', 1 means 'yes'
		'login_redirect_page'	 => 'lesson_catalog',  	// possilbe values 'lesson_catalog', 'user_dashboard'
		'editor_type'			 => 'tinymce',			// possible values 'tinymce', 'tinymce_new'
		'show_footer'			 => '1',
    	'load_videojs'		 	 => '0',
    	'time_reports'			 => '0',		//0 means 'count total time', 1 means 'count active time'

        //LDAP
        'activate_ldap'          => '0',
        'only_ldap'              => '0',
        'ldap_base_dn'           => '',
        'ldap_bind_dn'           => '',
        'ldap_protocol'          => '3',
        'ldap_server'            => 'ldap://localhost',
        'ldap_password'          => '',
        'ldap_port'              => '389',
        'ldap_cn'                => 'cn',
        'ldap_l'                 => 'l',
        'ldap_mail'              => 'mail',
        'ldap_postaladdress'     => 'postaladdress',
        'ldap_preferredlanguage' => 'referredlanguage',
        'ldap_telephonenumber'   => 'telephonenumber',
        'ldap_uid'               => 'uid',

        //SMTP
        'smtp_host'              => 'localhost',
        'smtp_user'              => '',
        'smtp_pass'              => '',
        'smtp_port'              => '25',
        'smtp_auth'              => '0',
        'smtp_timeout'           => '',

        //Locale
        //'decimal_point'          => '.',					//deprecated
        //'thousands_sep'          => ',',					//deprecated
        'date_format'            => 'DD/MM/YYYY',
        'location'               => 'Greece',
        'time_zone'              => '',

        //PHP
        'max_file_size'          => '50000',
        'gz_handler'			 => '1',
    	'compress_tests'		 => '0',

        //Multiple logins
        'multiple_logins'		 => '',
        'mapped_accounts'		 => '0',		//0: Enabled, 1: disabled for students, 2: disabled for students and professors, 3: disabled for all
        
        'mod_rewrite_bypass'	 => '0',
        //Simple mode
        'simple_mode' => '0',
        
        'mode_projects' => self::MODE_VISIBLE,
        'mode_bookmarks' => self::MODE_VISIBLE,
        'mode_comments' => self::MODE_VISIBLE,
        'mode_online_users' => self::MODE_VISIBLE,
        'mode_glossary' => self::MODE_VISIBLE,
        'mode_shared_glossary' => self::MODE_INVISIBLE,
        'mode_test_glossary' => self::MODE_INVISIBLE,
        'mode_calendar' => self::MODE_VISIBLE,
        'mode_surveys' => self::MODE_VISIBLE,
        'mode_news' => self::MODE_VISIBLE,
        'mode_messages' => self::MODE_VISIBLE,
        'mode_messages_student' => self::MODE_VISIBLE,
        'mode_forum' => self::MODE_VISIBLE,
        'mode_tests' => self::MODE_VISIBLE,
        'mode_tooltip' => self::MODE_VISIBLE,
        'mode_help' => self::MODE_VISIBLE,
        'mode_feedback' => self::MODE_VISIBLE,
        'mode_payments' => self::MODE_VISIBLE,
        'mode_move_blocks' => self::MODE_VISIBLE,
        'mode_change_info' => self::MODE_VISIBLE,
        'mode_change_pass' => self::MODE_VISIBLE,
        'mode_questions_pool' => self::MODE_INVISIBLE,
        'mode_curriculum' => self::MODE_VISIBLE_COMPLETE,
        'mode_skillgaptests' => self::MODE_VISIBLE_COMPLETE,
        'mode_logout_user' => self::MODE_VISIBLE_COMPLETE,
        'mode_languages' => self::MODE_VISIBLE_COMPLETE,
        'mode_archive' => self::MODE_VISIBLE_COMPLETE,
        'mode_search_user' => self::MODE_VISIBLE_COMPLETE,
        'mode_maintenance' => self::MODE_VISIBLE_COMPLETE,
        
        'mode_simple_complete' => self::MODE_VISIBLE,
        'mode_backup' => self::MODE_VISIBLE,
        'mode_themes' => self::MODE_VISIBLE,
        'mode_user_types' => self::MODE_VISIBLE,
        'mode_statistics' => self::MODE_VISIBLE,
        'mode_version_key' => self::MODE_VISIBLE,
        'mode_notifications' => self::MODE_VISIBLE,
        'mode_user_profile' => self::MODE_VISIBLE,
        'mode_configuration' => self::MODE_VISIBLE,   
        'mode_groups' => self::MODE_VISIBLE,
        'mode_course_instances' => self::MODE_VISIBLE,
        
        
        'mode_show_organization_chart' => self::MODE_VISIBLE,
        'mode_show_complete_org_chart' => self::MODE_VISIBLE,
        'mode_show_user_form' => self::MODE_INVISIBLE,
        'mode_show_unassigned_users_to_supervisors' => self::MODE_VISIBLE,
        'mode_allow_users_to_delete_supervisor_files' => self::MODE_VISIBLE,
        'mode_propagate_courses_to_branch_users' => self::MODE_VISIBLE,
        //'mode_allow_direct_login' => self::MODE_VISIBLE,
 
        
        'mode_social_events' => self::MODE_INVISIBLE,
        'mode_system_timeline' => self::MODE_INVISIBLE,
        'mode_lessons_timeline' => self::MODE_INVISIBLE,
        'mode_func_people' => self::MODE_INVISIBLE,
        'mode_func_comments' => self::MODE_INVISIBLE,
        'mode_func_userstatus' => self::MODE_INVISIBLE,

        //Social - Facebook settings
        'facebook_api_key'		 => '',
        'facebook_secret'		 => '',
        'social_modules_activated' => '63',

    	//Webserver authentication settings
    	'webserver_auth'	 	 => '0',
    	'webserver_registration' => '0',
    	'error_page' 		=> 'themes/default/external/default_error_page.html',
    	'unauthorized_page' => 'themes/default/external/default_unauthorized_page.html',
    	'username_variable' => '$_SERVER["REMOTE_USER"]',
    	'registration_file'	=> 'includes/webserver_registration.php',

        //Payments settings
        'currency'               => 'EUR',
		'currency_order'         => '1',
    	'paypalbusiness'		 => '',
        'paypalmode'			 => 'normal',
        'paypaldebug'			 => '0',
        'enable_balance'		 => '1',
    	'enable_cart'			 => '1',
    	'total_discount'		 => '0',
        'discount_period'		 => '',
        'discount_start'		 => '',

        //Invisible
    	'lock_down'				 => '0',
       //'chat_enabled'			 => '0',
        'zip_method'			 => 'php',
    	'theme'					 => '1',    //Default theme id, in a clean install this is 1
        'database_version'		 => G_VERSION_NUM,
		'help_url'				 => 'http://docs.efrontlearning.net/index.php',
    	//Version
        'version_key'			 => '',
    	'version_hosted'		 => '0',
    	'max_online_users'		 => '',
    	'max_online_users_timestamp' => '',
    	'max_online_users_threshold' => '',
    	'max_online_users_threshold_timestamp' => '',

        //Notifications
        'notifications_pageloads' => '10',
    	'notifications_maximum_inter_time' => '0',
        'notifications_messages_per_time' => '5',
        'notifications_max_sent_messages' => '100',
        'notifications_send_mode' => '0',
    	'notifications_lock' => '0',			//Lock the notifications so that multiple proccesses for send_notifications.php don't start at the same time. This is either 0 or a timestamp
    	
        //Unclassified - deprecated
        'cms_page'               => '',
        'css'                    => '',
    	'smarty_cache'			 => '1',		//Whether to cache smarty templates
    	'smarty_cache_timeout'   => '60'		//Default caching time for smarty templates
        );

    /**
    * Get configuration values
    *
    * This function is used to retrieve configuration values.
    * Furthermore, it compares the keys of the $defaultOptions array with
    * The name/value pairs stored in the database. If a default name/value
    * pair is not present in the database, it is created using its default
    * value (unless the whole table is empty, in which case nothing is done)
    * <br>Example:
    * <code>
    * $defaultConfig = EfrontConfiguration :: getValues();
    * </code>
    *
    * @return array The configuration options in name/value pairs
    * @access public
    * @since 3.0
    * @static
    */
    public static function getValues() {
    	$options = EfrontCache::getInstance()->getCache('configuration');
    	if (!$options) {
        	$options = eF_getTableDataFlat("configuration", "*");
        	sizeof($options) > 0 ? $options = array_combine($options['name'], $options['value']) : $options = array();
        	EfrontCache::getInstance()->setCache('configuration', $options);
    	}

        foreach (EfrontConfiguration :: $defaultOptions as $key => $value) {
            if (!isset($options[$key])) {
                EfrontConfiguration::setValue($key, $value);
                $options[$key] = $value;
            }
        }

        return $options;
    }

    /**
    * Get default configuration values
    *
    * This function is used to retrieve default configuration values.
    * <br/>Example:
    * <code>
    * $defaultConfig = EfrontConfiguration :: getDefaultValues();
    * </code>
    *
    * @return array The default configuration options
    * @access public
    * @since 3.0
    * @static
    */
    public static function getDefaultValues() {
        return EfrontConfiguration :: $defaultOptions;
    }

    /**
    * Set configuration value
    *
    * This function is used to set a configuration value. Given a name/value pair,
    * this function first checks if it exists in the 'configuration' database table.
    * If so, it updates the variable with the new value, otherwise it inserts a new
    * entry.
    * <br/>Example:
    * <code>
    * $defaultConfig = EfrontConfiguration :: setValue('smtp_host', 'localhost');			//Set the configuration parameter 'smtp_host' to 'localhost'
    * </code>
    *
    * @param string $name The variable name
    * @param string $value The variable value
    * @return boolean The query result
    * @access public
    * @since 3.0
    * @static
    */
    public static function setValue($name, $value) {
    	$value = trim($value);
        try {
            eF_insertTableData("configuration", array('name' => $name, 'value' => $value));
        } catch (Exception $e) {
        	//If exists, update it
        	eF_updateTableData("configuration", array('value' => $value), "name = '$name'");
        }
        $GLOBALS['configuration'][$name] = $value;

        EfrontCache::getInstance()->deleteCache('configuration');

        return true;
    }

    /**
     * Delete configuration value
     *
     * This function deletes the specified value from the configuration table and variable
     * @param string $name The variable name
     * @since 3.6.8
     * @access public
     * @static
     */
    public static function deleteValue($name) {
    	EfrontCache::getInstance()->deleteCache('configuration');

    	eF_deleteTableData("configuration", "name = '$name'");
    	unset($GLOBALS['configuration'][$name]);
    }

}
