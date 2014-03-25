<?php

// For ajax calls just create the filters
$stats_filters = array();
if (isset($_GET['group_filter']) && eF_checkParameter($_GET['group_filter'], 'id') && $_GET['group_filter'] != -1) {
	$stats_filters[] = array("table" 	=> "users_to_groups as filter_ug",
								"joinField"	=> "filter_ug.users_LOGIN",
								"condition" => "filter_ug.groups_ID = " . $_GET['group_filter']);
}

if (!empty($_GET['user_filter'])) {
	if ($_GET['user_filter'] != 3) {
		$stats_filters[] = array("condition" 	=> ($_GET['user_filter'] == 1)?"u.active = 1":"u.active = 0");
	}
} else {
	$stats_filters[] = array("condition" 	=> "u.active = 1");
}


if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
	$currentEmployee = $currentUser -> aspects['hcd'];

	if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
		$allowedBranches	  = array($_SESSION['s_current_branch']);
		$branchesTree = new EfrontBranchesTree();
		$iterator	  = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($branchesTree -> getNodeChildren($_SESSION['s_current_branch'])), RecursiveIteratorIterator :: SELF_FIRST));
		foreach($iterator as $key => $value) {
			$allowedBranches[] = $key;
		}
	
		if (!isset($_GET['branch_filter']) || !in_array($_GET['branch_filter'], $allowedBranches)) {
			$_GET['branch_filter'] = $_SESSION['s_current_branch'];
			$_GET['subbranches'] = 1;
		}
		//$defaultConstraints['table_filters'] = $stats_filters;
	}

	if (isset($_GET['branch_filter']) && $_GET['branch_filter'] != 0) {
		if (eF_checkParameter($_GET['branch_filter'], 'id')) {
			if (!$_GET['subbranches']) {
				$stats_filters[] = array("table" 		=> "module_hcd_employee_works_at_branch as filter_eb",
									 "joinField"	=> "filter_eb.users_LOGIN",
									 "condition" 	=> "(filter_eb.branch_ID = " . $_GET['branch_filter'] . " AND filter_eb.assigned = 1)");
			} else {
				$branches	  = array($_GET['branch_filter']);
				$branchesTree = new EfrontBranchesTree();
				$iterator	  = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($branchesTree -> getNodeChildren($_GET['branch_filter'])), RecursiveIteratorIterator :: SELF_FIRST));
				foreach($iterator as $key => $value) {
					$branches[] = $key;
				}
	
				$stats_filters[] = array("table" 		=> "module_hcd_employee_works_at_branch as filter_eb",
									 "joinField"	=> "filter_eb.users_LOGIN",
									 "condition" 	=> "(filter_eb.branch_ID in (" . implode(",", $branches) . ") AND filter_eb.assigned = 1)");
			}
		} else {
			$branches_array = explode(",", $_GET['branch_filter']);
			$flag = 1;
			foreach ($branches_array as $value) {
				$flag = $flag && eF_checkParameter($value, 'id');
			}		
			if ($flag) {
				if (!$_GET['subbranches']) {
					$stats_filters[] = array("table" 		=> "module_hcd_employee_works_at_branch as filter_eb",
										 "joinField"	=> "filter_eb.users_LOGIN",
										 "condition" 	=> "(filter_eb.branch_ID in (" . implode(",", $branches_array) . ") AND filter_eb.assigned = 1)");
				} else {
					$branches	  = $branches_array;	
					$branchesTree = new EfrontBranchesTree();
					foreach ($branches_array as $branch) {
						$iterator	  = new EfrontNodeFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator($branchesTree -> getNodeChildren($branch)), RecursiveIteratorIterator :: SELF_FIRST));
						foreach($iterator as $key => $value) {
							$branches[] = $key;
						}
					}	
					$branches = array_unique($branches);
					$stats_filters[] = array("table" 		=> "module_hcd_employee_works_at_branch as filter_eb",
										 "joinField"	=> "filter_eb.users_LOGIN",
										 "condition" 	=> "(filter_eb.branch_ID in (" . implode(",", $branches) . ") AND filter_eb.assigned = 1)");
				}
			}
		}	
	} else if ($currentEmployee -> isSupervisor()) {
		$isProfessor = false;
		if (isset($_GET['sel_course']) && $currentUser -> hasCourse($_GET['sel_course'])) {
			$roles = EfrontUser::getRoles();
			if ($roles[$currentUser -> getUserTypeInCourse($_GET['sel_course'])] == 'professor') {
				$isProfessor = true;
			}
		} else if (isset($infoLesson) && $currentUser -> hasLesson($infoLesson)) {
			$roles = EfrontUser::getRoles();
			if ($roles[$currentUser -> getUserTypeInLesson($infoLesson)] == 'professor') {
				$isProfessor = true;
			}
		}


		if (!$isProfessor) {
			if (!$_GET['subbranches']) {
				$supervisedBranches = $currentEmployee -> getSupervisedBranches();
			} else {
				$supervisedBranches = $currentEmployee -> getSupervisedBranchesRecursive();
			}
			$branches = array();
			foreach($supervisedBranches as $value) {
				$branches[] = $value['branch_ID'];
			}
			if (!empty($branches)) {
				$stats_filters[] = array("table" 		=> "module_hcd_employee_works_at_branch as filter_eb",
									 "joinField"	=> "filter_eb.users_LOGIN",
									 "condition" 	=> "(filter_eb.branch_ID in (" . implode(",", $branches) . ") AND filter_eb.assigned = 1)");
			} else {
				$stats_filters[] = array("table" 		=> "module_hcd_employee_works_at_branch as filter_eb",
									 "joinField"	=> "filter_eb.users_LOGIN",
									 "condition" 	=> "(filter_eb.branch_ID != '' AND filter_eb.assigned = 1)");

			}
		}
	}
	
	if (isset($_GET['job_filter']) && $_GET['job_filter'] != 0) {
			$jobs_array = explode(",", $_GET['job_filter']);
			$flag = 1;
			foreach ($jobs_array as $value) {
				$flag = $flag && eF_checkParameter($value, 'id');
			}		
			if ($flag) {
				$result = eF_getTableDataFlat("module_hcd_job_description", "job_description_ID,branch_ID", " description IN (SELECT description FROM module_hcd_job_description WHERE job_description_ID IN (".implode(",", $jobs_array)."))");
				$jobs_array = $result['job_description_ID'];
				
				$stats_filters[] = array("table" 		=> "module_hcd_employee_has_job_description as filter_ej",
										 "joinField"	=> "filter_ej.users_login",
										 "condition" 	=> "(filter_ej.job_description_ID in (" . implode(",", $jobs_array) . "))");
			
				
			}
	}
	
	
} #cpp#endif

if (!isset($_GET['ajax'])) {
	$groups     = EfrontGroup :: getGroups();
	$smarty -> assign("T_GROUPS", $groups);

	if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
		// Create the branches select
		require_once $path."module_hcd_tools.php";
		eF_getRights();
		$company_branches = eF_getTableData("module_hcd_branch", "branch_ID, name, father_branch_ID","", "father_branch_ID ASC,branch_ID ASC");
		if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
			foreach ($company_branches as $key => $value) {
				if (!in_array($value['branch_ID'], $allowedBranches)) {
					unset($company_branches[$key]);
				}
			}
			$company_branches = array_values($company_branches);
		}
		
		$filter_branches = eF_createBranchesTreeSelect($company_branches,4);
		$smarty -> assign("T_BRANCHES", $filter_branches);
		
	    $job_descriptions = eF_getTableData("module_hcd_job_description", "description,job_description_ID,branch_ID","","description ASC");
	    
	    if ($_SESSION['s_type'] != 'administrator' && $_SESSION['s_current_branch']) {	//this applies to supervisors only
	    	foreach ($job_descriptions as $key => $value) {
	    		if (!in_array($value['branch_ID'], $allowedBranches)) {
	    			unset($job_descriptions[$key]);
	    		}
	    	}
	    	$job_descriptions = array_values($job_descriptions);
	    }
	    $job_des = array();
	    foreach ($job_descriptions as $key => $value) {
	    	if (empty($job_des[$key])) {
	    		$job_des[$value['description']] = $value['job_description_ID'];
	    	}
	    }
	    
	    $job_des = array_flip ($job_des);
	    $smarty -> assign("T_JOB_DES", $job_des);
		
	} #cpp#endif
}

// Create url for ajax tables
$stats_url = "";
if (isset($_GET['group_filter']) && $_GET['group_filter'] != -1) {
	$stats_url .= "&group_filter=". $_GET['group_filter'];
}
if (G_VERSIONTYPE == 'enterprise') { #cpp#ifdef ENTERPRISE
	if (isset($_GET['branch_filter']) && $_GET['branch_filter'] != 0) {
		$stats_url .= "&branch_filter=". $_GET['branch_filter'];
	}
	if (isset($_GET['job_filter']) && $_GET['job_filter'] != 0) {
		$stats_url .= "&job_filter=". $_GET['job_filter'];
	}
} #cpp#endif
if (isset($_GET['user_filter']) && $_GET['user_filter'] != 0) {
	$stats_url .= "&user_filter=". $_GET['user_filter'];
}
if (isset($_GET['subbranches'])) {
	$stats_url .= "&subbranches=". $_GET['subbranches'];
}

$smarty -> assign("T_STATS_FILTERS_URL", $stats_url);
