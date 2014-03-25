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
	
	if (isset($currentUser->coreAccess['organization']) && $currentUser->coreAccess['organization'] != 'change') {
		$_change_placements_ = false;
	} else if ($currentUser -> user['user_type'] == 'administrator') {
		$_change_placements_ = true;
	} else if ($currentUser -> user['login'] == $editedUser -> user['login']) {
		$_change_placements_ = false;
	} else if (!$currentEmployee -> isSupervisor()) {
		$_change_placements_ = false;
	} else if ($currentEmployee -> supervisesEmployee($editedUser->user['login'])) {
		$_change_placements_ = true;
	} else {
		$_change_placements_ = false;
	}
	$smarty -> assign("_change_placements_", $_change_placements_);

	if ($currentUser -> user['user_type'] != 'administrator') {
		$smarty -> assign("T_SUPERVISES_BRANCHES", $currentEmployee -> supervisesBranches);
	}

	if (isset($_GET['ajax']) && isset($_GET['delete_job']) && eF_checkParameter($_GET['delete_job'], 'id') && $_change_placements_) {
		try {
			$editedEmployee = $editedEmployee -> removeJob($_GET['delete_job']);
			echo json_encode(array("status" => 1));
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		exit;
	} else if (isset($_GET['add_placement']) || (isset($_GET['edit_placement']) && eF_checkParameter($_GET['edit_placement'], 'id'))) {
		$branchTree = new EfrontBranchesTree();
		$branches 	= $branchTree -> toPathStringShortened();
		foreach ($branches as $key => $branch) {
			if ($currentUser -> user['user_type'] != 'administrator' && !in_array($key, $currentEmployee -> supervisesBranches)) {
				unset($branches[$key]);
			}
		}

		reset($branches);
		if ($_POST['branch']) {
			$branch = new EfrontBranch($_POST['branch']);
		} elseif (isset($_GET['add_placement'])) {
			$branch = new EfrontBranch(key($branches));
		} else {
			$currentJob = new EfrontJob($_GET['edit_placement']);
			$branch = new EfrontBranch($currentJob->job['branch_ID']);
		}
		$jobs   = eF_local_printBranchJobs($branch);

		try {
			if (isset($_GET['ajax']) && isset($_GET['jobs_for_branch'])) {
				$branch = new EfrontBranch($_GET['jobs_for_branch']);
				$branchJobs = eF_local_printBranchJobs($branch);

				$result = eF_getTableData("module_hcd_employee_works_at_branch", "assigned", "users_login='".$currentEmployee->login."' and branch_ID=".$branch->branch['branch_ID']);
				if ($result[0]['assigned'] == 1) {	//The supervisor can't assign a supervisor on the same branch
					$positions = array(_EMPLOYEE);
				} else {
					$positions = array(_EMPLOYEE, _SUPERVISOR);
				}
				echo json_encode(array("status" => 1, "jobs" => $branchJobs, 'positions' => $positions));
				exit;
			}
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		
		if ($currentEmployee -> isSupervisor()) {
			$positions = array(_EMPLOYEE);
		} else {
			$positions = array(_EMPLOYEE, _SUPERVISOR);
		}

		$form = new HTML_QuickForm("placement_form", "post", basename($_SERVER['PHP_SELF'])."?ctg=personal&user=".$editedUser -> user['login']."&op=placements".(isset($_GET['edit_placement']) ? "&edit_placement=".$_GET['edit_placement'] : "&add_placement=1"), "", null, true);
		$form -> addElement("select", "branch", _BRANCH, $branches, 'onchange = "updateBranchJobs(this)"');
		$form -> addElement("select", "job", _JOBDESCRIPTION, $jobs, 'id="jobs_for_branch"');
		$form -> addElement("select", "position", _POSITION, $positions, 'id="positions_for_branch"');

		if ($_change_placements_) {
			$form -> addElement('submit', 'submit', _SUBMIT, 'class = "flatButton"');
		} else {
			$form -> freeze();
		}
		$form -> addRule('job', _PLEASESELECTAJOB, 'callback', create_function('$a', 'return ($a && $a != "#empty#");'));    //The score must be between 0 and 100

		if  ($_GET['edit_placement']) {
			$userJobs = $editedEmployee -> getJobs();
			$job 	  = $userJobs[$_GET['edit_placement']];
			$form -> setDefaults(array('branch'   => $job['branch_ID'],
									   'job'	  => $job['description'],
									   'position' => $job['supervisor']));
		}

		try {
			if ($form -> isSubmitted() && $form -> validate() && $_change_placements_) {
				$values = $form -> exportValues();				
				if ($values['job'] && $values['branch']) {
					require_once("module_hcd_tools.php");
					$newJob = eF_getJobDescriptionId($values['job'], $values['branch']);
					//pr($values);pr($_GET['edit_placement']);pr($newJob);echo "A";exit;
					if ($_GET['edit_placement']) {
						if ($_GET['edit_placement'] != $newJob) {
							$editedEmployee -> removeJob($_GET['edit_placement']);
						} elseif (isset($userJobs[$newJob]) && $userJobs[$newJob]['supervisor'] != $_POST['position']) {
							$editedEmployee -> removeJob($_GET['edit_placement']);
						}
					}
				
					$editedEmployee -> addJob($editedUser, $newJob , $values['branch'], $_POST['position']);

					$message      = _OPERATIONCOMPLETEDSUCCESFULLY;
					$message_type = 'success';
				}
			}
		} catch (Exception $e) {
			handleNormalFlowExceptions($e);
		}
		$smarty -> assign("T_FORM", $form -> toArray());
	} else {
		$branchTree = new EfrontBranchesTree();
		$branches 	= $branchTree -> toPathString();
		$smarty -> assign("T_BRANCHES_PATH", $branches);

		$placements = $editedEmployee -> getJobs();
		$smarty -> assign("T_PLACEMENTS", $placements);
	}	

} #cpp#endif

function eF_local_printBranchJobs($branch) {
	$result = $branch -> getJobDescriptions();
	$branchJobs = array("--- {$branch->branch['name']} ---");
	foreach ($result as $value) {
		$branchJobs[$value['description']] = $value['description'];
	}
	$branchJobs['#empty#'] = "--- "._OTHERBRANCHJOBS." ---";
	$result = eF_getTableData("module_hcd_job_description", "distinct description");
	foreach ($result as $value) {
		$branchJobs[$value['description']] = $value['description'];
	}

	return $branchJobs;
}
