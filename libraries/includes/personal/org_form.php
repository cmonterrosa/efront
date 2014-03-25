<?php
if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE

	//This file cannot be called directly, only included.
	if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
		exit;
	}

	if ($currentUser -> coreAccess['organization'] == 'hidden') {
		eF_redirect(basename($_SERVER['PHP_SELF']));
		exit;
	}
	
	if ((isset($currentUser -> coreAccess['organization']) && $currentUser -> coreAccess['organization'] != 'change')) {
		$constrainAccess = 'all';
	} else if ($currentUser -> user['login'] == $editedUser -> user['login']) {
		$constrainAccess = array();
		if ($currentUser -> user['user_type'] != 'administrator' || $currentUser -> user['user_types_ID'] != 0) {		//administrators can change this data nevertheless
			$constrainAccess = array('work_permission_data',
									 'national_service_completed',
									 'employement_type',
									 'wage',
									 'way_of_working');
		}
	} else if ($currentUser -> user['user_type'] == 'administrator') {
		$constrainAccess = array();
	} else if (!$currentEmployee -> isSupervisor()) {
		$constrainAccess = 'all';
	} else if ($currentEmployee -> supervisesEmployee($editedUser->user['login'])) {
		$constrainAccess = array();
	} else {
		$constrainAccess = 'all';
	}

	$form = new HTML_QuickForm("user_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$editedUser -> user['login']."&op=org_form", "", null, true);
	// Permanent data of personal records of employees
	$form -> addElement('text', 'father', _FATHERNAME, 'class = "inputText"');
	$form -> addElement('select', 'sex' , _GENDER, array("0" => _MALE, "1" => _FEMALE), 'class = "inputText" ');
	$form -> addElement(EfrontEntity::createDateElement($form, 'birthday', _BIRTHDAY, array('minYear' => 1920, 'maxYear' => date("Y"), 'format' => getDateFormat())));
	$form -> addElement('text', 'birthplace',_BIRTHPLACE , 'class = "inputText" ');
	$form -> addElement('text', 'birthcountry', _BIRTHCOUNTRY, 'class = "inputText" ');
	$form -> addElement('text', 'mother_tongue', _MOTHERTONGUE, 'class = "inputText" ');
	$form -> addElement('text', 'nationality', _NATIONALITY, 'class = "inputText" ');
	$form -> addElement('text', 'address', _ADDRESS, 'class = "inputText" ');
	$form -> addElement('text', 'city', _CITY, 'class = "inputText" ');
	$form -> addElement('text', 'country', _COUNTRY, 'class = "inputText" ');
	$form -> addElement('text', 'homephone', _HOMEPHONE, 'class = "inputText" ');
	$form -> addElement('text', 'mobilephone', _MOBILEPHONE, 'class = "inputText" ');
	$form -> addElement('text', 'office', _OFFICE, 'class = "inputText" ');
	$form -> addElement('text', 'company_internal_phone', _COMPANYINTERNALPHONE, 'class = "inputText" ');
	$form -> addElement('text', 'afm', _VATREGNUMBER, 'class = "inputText" ');
	$form -> addElement('text', 'doy', _TAXOFFICE, 'class = "inputText" ');
	$form -> addElement('text', 'police_id_number', _POLICEIDNUMBER, 'class = "inputText" ');
	$form -> addElement('text', 'work_permission_data', _WORKPERMISSIONDATA, 'class = "inputText"');
	$form -> addElement('text', 'employement_type', _EMPLOYMENTTYPE, 'class = "inputText"');
	$form -> addElement(EfrontEntity::createDateElement($form, 'hired_on', _HIREDON, array('minYear' => 1960, 'maxYear' => date("Y")+1, 'addEmptyOption' =>true, 'format' => getDateFormat())));
	$form -> addElement(EfrontEntity::createDateElement($form, 'left_on', _LEFTON, array('minYear' => 1960, 'maxYear' => date("Y")+1, 'addEmptyOption' =>true, 'format' => getDateFormat())));
	$form -> addElement('text', 'wage', _WAGE, 'class = "inputText"');
	$form -> addElement('select', 'marital_status', _MARITALSTATUS, array("0" => _SINGLE, "1" => _MARRIED),'class = "inputText" ');
	$form -> addElement('text', 'bank', _BANK, 'class = "inputText" ');
	$form -> addElement('text', 'bank_account', _BANKACCOUNT, 'class = "inputText" ');
	$form -> addElement('select', 'way_of_working', _WAYOFWORKING,  array("" => "", "0" => _FULLTIME, "1" => _PARTTIME, "2" => _CASUAL),'class = "inputText" id="way_of_working"');
	$form -> addElement('advcheckbox', 'driving_licence', _DRIVINGLICENSE, null, 'class = "inputCheckbox"');
	$form -> addElement('advcheckbox', 'national_service_completed', _NATIONALSERVICECOMPLETED, null, 'class = "inputCheckbox"');
	$form -> addElement('advcheckbox', 'transport', _TRANSPORTMEANS, null, 'class = "inputCheckbox"');

	$form -> setDefaults($editedEmployee -> employee);

	if ($constrainAccess != 'all') {
		$form -> addElement('submit', 'submit_personal_details', _SUBMIT, 'class = "flatButton"');
		$form -> freeze($constrainAccess);
	} else {
		$form -> freeze();
	}

	if ($form -> isSubmitted() && $form -> validate()) {
		try {
			$values = $form -> exportValues();

			$values['birthday'] = mktime($values['birthday']['H'], $values['birthday']['i'], 0, $values['birthday']['M'], $values['birthday']['d'], $values['birthday']['Y']);
			$values['hired_on'] = mktime($values['hired_on']['H'], $values['hired_on']['i'], 0, $values['hired_on']['M'], $values['hired_on']['d'], $values['hired_on']['Y']);
			$values['left_on']  = mktime($values['left_on']['H'],  $values['left_on']['i'],  0, $values['left_on']['M'],  $values['left_on']['d'],  $values['left_on']['Y']);

			$userProperties = array('father'		    		 => $values['father'],
									'sex'		    			 => $values['sex'],
								    'birthday'					 => $values['birthday'],
									'birthplace'				 => $values['birthplace'],
									'birthcountry'		  		 => $values['birthcountry'],
									'mother_tongue'	   			 => $values['mother_tongue'],
									'nationality'	  			 => $values['nationality'],
									'address'					 => $values['address'],
									'city'	   					 => $values['city'],
					 				'country'					 => $values['country'],
									'homephone'					 => $values['homephone'],
									'mobilephone' 				 => $values['mobilephone'],
									'office'				  	 => $values['office'],
									'company_internal_phone'	 => $values['company_internal_phone'],
									'afm'	  					 => $values['afm'],
									'doy'						 => $values['doy'],
									'police_id_number'	   		 => $values['police_id_number'],
					 				'driving_licence'			 => $values['driving_licence'] ? $values['driving_licence'] : 0,
									'work_permission_data'		 => $values['work_permission_data'],
									'national_service_completed' => $values['national_service_completed'] ? $values['national_service_completed'] : 0,
									'employement_type'	  	 	 => $values['employement_type'],
									'hired_on'					 => $values['hired_on'],
									'left_on'	   				 => $values['left_on'],
					 				'wage'						 => $values['wage'] ? $values['wage'] : 0,
									'marital_status'			 => $values['marital_status'],
									'bank' 						 => $values['bank'],
									'bank_account'	  			 => $values['bank_account'],
									'way_of_working'			 => $values['way_of_working'] ? $values['way_of_working'] : 0,
									'transport'	   				 => $values['transport'] ? $values['transport'] : 0);

			foreach ($constrainAccess as $value) {
				unset($userProperties[$value]);
			}

			$editedEmployee = $editedEmployee -> updateEmployeeData($userProperties);
			eF_redirect($_SERVER['PHP_SELF']."?ctg=personal&user=".$editedUser->user['login']."&op=org_form&message=".urlencode(_OPERATIONCOMPLETEDSUCCESSFULLY)."&message_type=success");
		} catch (Exception $e) {
			handleNormalFlowExceptions($e);
		}

	}


	$smarty -> assign("T_PROFILE_FORM", $form -> toArray());
} #cpp#endif