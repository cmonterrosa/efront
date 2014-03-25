<?php
//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
	exit;
}

$externalMainForm = new Html_QuickForm("external_main_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=external&tab=main", "", null, true);
$externalMainForm -> registerRule('checkParameter', 'callback', 'eF_checkParameter');
$externalMainForm -> addElement("advcheckbox", "api", _ENABLEDAPI, null, 'class = "inputCheckBox"', array(0, 1));
$externalMainForm -> addElement("text", "api_ip", _CONSTRAINAPIIP, 'class = "inputText"');
$externalMainForm -> addElement("static", "", _COMMASEPARATEDLISTASTERISKEXAMPLE);
$externalMainForm -> addElement("select", "editor_type",  _EDITORTYPE, array('tinymce' => G_TINYMCE, 'tinymce_new' => G_NEWTINYMCE), 'class = "inputCheckBox"');
$externalMainForm -> addElement("advcheckbox", "virtual_keyboard", _ENABLEVIRTUALKEYBOARD, null, 'class = "inputCheckBox"', array(0, 1));
//If we are on a windows system, and the zip_method is already PHP, then don't display option to change it
if (stripos(php_uname(), 'windows') === false || $GLOBALS['configuration']['zip_method'] != "php") {
	$externalMainForm -> addElement("select", "zip_method", _ZIPHANDLING, array('php' => "PHP", 'system' => _SYSTEM), 'class = "inputSelect"');
} else {
	$externalMainForm -> addElement("select", "zip_method", _ZIPHANDLING, array('php' => "PHP"), 'class = "inputSelect"');
}
$externalMainForm -> setDefaults($GLOBALS['configuration']);
if (isset($currentUser -> coreAccess['configuration']) && $currentUser -> coreAccess['configuration'] != 'change') {
	$externalMainForm -> freeze();
} else {
	$externalMainForm -> addElement("submit", "submit", _SAVE, 'class = "flatButton"');
	if ($externalMainForm -> isSubmitted() && $externalMainForm -> validate()) {															  //If the form is submitted and validated
		$values = $externalMainForm -> exportValues();
		unset($values['submit']);
		foreach ($values as $key => $value) {
			EfrontConfiguration :: setValue($key, $value);
		}
		//delete cache when changing editor type
		$cacheTree = new FileSystemTree(G_THEMECACHE, true);
		foreach (new EfrontDirectoryOnlyFilterIterator($cacheTree -> tree) as $value) {
			$value -> delete();
		}
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=external&tab=main&message=".urlencode(_SUCCESFULLYUPDATECONFIGURATION)."&message_type=success");
	}
}
$smarty -> assign("T_EXTERNAL_MAIN_FORM", $externalMainForm -> toArray());


if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	$all_social_enabled_value = pow(2,SOCIAL_MODULES_ALL);
	if (!isset($GLOBALS['configuration']['social_modules_activated'])) {
		EfrontConfiguration :: setValue('social_modules_activated', pow(2,SOCIAL_MODULES_ALL)-1);
		$socialModulesActivated = $all_social_enabled_value-1;
	} else {
		$socialModulesActivated = intval($GLOBALS['configuration']['social_modules_activated']);
	}
	
	$externalFacebookForm	 = new HTML_QuickForm("external_fb_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=external&tab=facebook", "", null, true);
	$externalFacebookForm -> registerRule('checkParameter', 'callback', 'eF_checkParameter');
	$externalFacebookForm -> addElement("advcheckbox", "social_" . FB_FUNC_DATA_ACQUISITION,  _FACEBOOKDATAACQUISITION, null, 'class = "inputCheckBox"', array(0, 1));
	$externalFacebookForm -> addElement("advcheckbox", "social_" . FB_FUNC_LOGGING,  _FACEBOOKEXTERNALLOGGING, null, 'class = "inputCheckBox"', array(0, 1));
	$externalFacebookForm -> addElement("advcheckbox", "social_" . FB_FUNC_CONNECT,  _FACEBOOKCONNECT, null, 'class = "inputCheckBox"', array(0, 1));
	$externalFacebookForm -> addElement("text", "facebook_api_key",   _FACEBOOKAPIKEY,	 'class = "inputText"');
	$externalFacebookForm -> addElement("text", "facebook_secret",	_FACEBOOKSECRET,	 'class = "inputText"');
	// Initialize values
	
	for ($i = 1; $i < $all_social_enabled_value; $i = $i << 1) {
		if ($i & $socialModulesActivated) {
			$externalFacebookForm -> setDefaults(array('social_'.$i => 1));
		}
	}
	$externalFacebookForm -> setDefaults(array("facebook_api_key" => $GLOBALS['configuration']['facebook_api_key'], "facebook_secret"  => $GLOBALS['configuration']['facebook_secret']));

	if (isset($currentUser -> coreAccess['configuration']) && $currentUser -> coreAccess['configuration'] != 'change') {
		$externalFacebookForm -> freeze();
	} else {
		$externalFacebookForm -> addElement("submit", "submit", _SAVE, 'class = "flatButton"');

		if ($externalFacebookForm -> isSubmitted() && $externalFacebookForm -> validate()) {
			$values = $externalFacebookForm -> exportValues();
			unset($values['submit']);

			EfrontConfiguration :: setValue('facebook_api_key', $values['facebook_api_key']);
			unset($values['facebook_api_key']);
			EfrontConfiguration :: setValue('facebook_secret', $values['facebook_secret']);
			unset($values['facebook_secret']);

			// Create the new binary map
			$socialModulesToBeActivated = 0;
			foreach ($values as $key => $value) {
				if ($value == 1) {
					$socialModulesToBeActivated += intval(substr($key, 7));
				}

			}
			EfrontConfiguration :: setValue('social_modules_activated', $socialModulesToBeActivated);
			eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=external&tab=facebook&message=".urlencode(_SUCCESFULLYUPDATECONFIGURATION)."&message_type=success");
		}
	}
	$smarty -> assign("T_EXTERNAL_FACEBOOK_FORM", $externalFacebookForm -> toArray());
} #cpp#endif


$externalMathForm = new Html_QuickForm("external_math_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=external&tab=math", "", null, true);
$externalMathForm -> registerRule('checkParameter', 'callback', 'eF_checkParameter');
$externalMathForm -> addElement("advcheckbox", "math_content",	  _ENABLEMATHCONTENT,	   null, 'class = "inputCheckBox"', array(0, 1));
$externalMathForm -> addElement("advcheckbox", "math_images",	   _LOADMATHTYPESASIMAGES,   null, 'class = "inputCheckBox"', array(0, 1));
$externalMathForm -> addElement("static", "", _MATHIMAGESINFO);
$externalMathForm -> addElement("text", "math_server",		 _MATHSERVER,			'class = "inputText"');
$externalMathForm -> addElement("static", "", _MATHSERVERINFO);
$externalMathForm -> setDefaults($GLOBALS['configuration']);
if (isset($currentUser -> coreAccess['configuration']) && $currentUser -> coreAccess['configuration'] != 'change') {
	$externalMathForm -> freeze();
} else {
	$externalMathForm -> addElement("submit", "submit", _SAVE, 'class = "flatButton"');
	if ($externalMathForm -> isSubmitted() && $externalMathForm -> validate()) {															  //If the form is submitted and validated
		$values = $externalMathForm -> exportValues();
		unset($values['submit']);
		foreach ($values as $key => $value) {
			EfrontConfiguration :: setValue($key, $value);
		}
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=external&tab=math&message=".urlencode(_SUCCESFULLYUPDATECONFIGURATION)."&message_type=success");
	}
}
$smarty -> assign("T_EXTERNAL_MATH_FORM", $externalMathForm -> toArray());

$externalLiveDocxForm = new Html_QuickForm("external_livedocx_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=external&tab=livedocx", "", null, true);
$externalLiveDocxForm -> registerRule('checkParameter', 'callback', 'eF_checkParameter');
$externalLiveDocxForm -> addElement("text", "phplivedocx_server",	 _PHPLIVEDOCXSERVER,	'class = "inputText"');
$externalLiveDocxForm -> addElement("text", "phplivedocx_username",_USERNAME);
$externalLiveDocxForm -> addElement("password", "phplivedocx_password",_PASSWORD);
$externalLiveDocxForm -> addElement("static", "", _PHPLIVEDOCXINFO);
$externalLiveDocxForm -> setDefaults($GLOBALS['configuration']);
if (isset($currentUser -> coreAccess['configuration']) && $currentUser -> coreAccess['configuration'] != 'change') {
	$externalLiveDocxForm -> freeze();
} else {
	$externalLiveDocxForm -> addElement("submit", "submit", _SAVE, 'class = "flatButton"');
	if ($externalLiveDocxForm -> isSubmitted() && $externalLiveDocxForm -> validate()) {															  //If the form is submitted and validated
		$values = $externalLiveDocxForm -> exportValues();
		unset($values['submit']);
		foreach ($values as $key => $value) {
			EfrontConfiguration :: setValue($key, $value);
		}
		$phplivedocxConfig = '<?php
define("PATH_ZF","'.G_ROOTPATH.'Zend/library/'.'");
define("USERNAME","'.$values['phplivedocx_username'].'");
define("PASSWORD","'.$values['phplivedocx_password'].'");
define("PHPLIVEDOCXAPI","'.$values['phplivedocx_server'].'");
?>';
		if (!file_exists($path."phplivedocx_config.php") || is_writable($path."phplivedocx_config.php")) {
			file_put_contents($path."phplivedocx_config.php", $phplivedocxConfig);
		} else {
			$message = _PHPLIVEDOCXCONFIGURATIONFILEISNOTWRITABLE;
		}
		eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=external&tab=livedocx&message=".urlencode(_SUCCESFULLYUPDATECONFIGURATION)."&message_type=success");
	}
}
$smarty -> assign("T_EXTERNAL_LIVEDOCX_FORM", $externalLiveDocxForm -> toArray());

if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
	if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
		$extensions   = get_loaded_extensions();
		if (in_array('ldap', $extensions)) {
			$externalLDAPForm = new Html_QuickForm("external_ldap_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=external&tab=ldap", "", null, true);
			$externalLDAPForm -> registerRule('checkParameter', 'callback', 'eF_checkParameter');
			$externalLDAPForm -> addElement("advcheckbox", "activate_ldap", _ACTIVATELDAP,	null, 'class = "inputCheckBox"', array(0, 1));
			$externalLDAPForm -> addElement("advcheckbox", "only_ldap",	 _SUPPORTONLYLDAP, null, 'class = "inputCheckBox"', array(0, 1));
			$externalLDAPForm -> addElement("text", "ldap_server",	 _LDAPSERVER,   'class = "inputText"');
			$externalLDAPForm -> addElement("text", "ldap_port",	   _LDAPPORT,	 'class = "inputText"');
			$externalLDAPForm -> addElement("text", "ldap_binddn",	 _LDAPBINDDN,   'class = "inputText"');
			$externalLDAPForm -> addElement("password", "ldap_password",   _LDAPPASSWORD, 'class = "inputText"');
			$externalLDAPForm -> addElement("text", "ldap_basedn",	 _LDAPBASEDN,   'class = "inputText"');
			$externalLDAPForm -> addElement("select", "ldap_protocol", _LDAPPROTOCOLVERSION, array('2' => '2', '3' => '3'));
			$externalLDAPForm -> addElement("text", "ldap_uid",			   _LOGINATTRIBUTE,	  'class = "inputText"');
			$externalLDAPForm -> addElement("text", "ldap_cn",				_LDAPCOMMONNAME,	  'class = "inputText"');
			$externalLDAPForm -> addElement("text", "ldap_postaladdress",	 _LDAPADDRESS,		 'class = "inputText"');
			$externalLDAPForm -> addElement("text", "ldap_l",				 _LDAPLOCALITY,		'class = "inputText"');
			$externalLDAPForm -> addElement("text", "ldap_telephonenumber",   _LDAPTELEPHONENUMBER, 'class = "inputText"');
			$externalLDAPForm -> addElement("text", "ldap_mail",			  _LDAPMAIL,			'class = "inputText"');
			$externalLDAPForm -> addElement("text", "ldap_preferredlanguage", _LDAPLANGUAGE,		'class = "inputText"');
			$externalLDAPForm -> setDefaults($GLOBALS['configuration']);

			if (isset($currentUser -> coreAccess['configuration']) && $currentUser -> coreAccess['configuration'] != 'change') {
				$externalLDAPForm -> freeze();
			} else {
				$externalLDAPForm -> addElement("submit", "check_ldap", _CHECKSETTINGS, 'class = "flatButton"');
				$externalLDAPForm -> addElement("submit", "submit", _SAVE, 'class = "flatButton"');

				if ($externalLDAPForm -> isSubmitted() && $externalLDAPForm -> validate()) {															  //If the form is submitted and validated
					$values = $externalLDAPForm -> exportValues();
					if (!isset($values['check_ldap'])) {
						unset($values['submit']);
						foreach ($values as $key => $value) {
							EfrontConfiguration :: setValue($key, $value);
						}
						eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=system_config&op=external&tab=ldap&message=".urlencode(_SUCCESFULLYUPDATECONFIGURATION)."&message_type=success");
					} else {
							//debug();
							//ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
							//pr($values);
						if (!($ds = ldap_connect($values['ldap_server'], $values['ldap_port']))) {
							$message	  = _CANNOTCONNECTLDAPSERVER;
							$message_type = 'failure';
						} else {
							ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $values['ldap_protocol']);
							//ldap_set_option($ds, LDAP_, $values['ldap_protocol']);
							if (!($bind = ldap_bind($ds, $values['ldap_binddn'], $values['ldap_password']))) {
								$message	  = _CANNOTBINDLDAPSERVER;
								$message_type = 'failure';
							} else {
								$message	  = _SUCESSFULLYCONNECTEDTOLDAPSERVER;
								$message_type = 'success';
							}
							//debug(false);
						}
					}
				}
			}
			$smarty -> assign("T_EXTERNAL_LDAP_FORM", $externalLDAPForm -> toArray());
		} else {
			$smarty -> assign("T_EXTENSION_MISSING", 'ldap');
		}
	} #cpp#endif
} #cpp#endif


