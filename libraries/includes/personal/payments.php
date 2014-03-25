<?php

if (G_VERSIONTYPE != 'community') {  #cpp#ifndef COMMUNITY
	if ($currentUser -> user['user_type'] == 'administrator') {
		$smarty -> assign("T_PAYMENTS_OPTIONS", array(array('text' => _PAYMENTS,  'image' => "16x16/shopping_basket_add.png", 'href' => basename($_SERVER['PHP_SELF'])."?ctg=payments")));
	}

	if (isset($currentUser->coreAccess['payments']) && $currentUser->coreAccess['payments'] != 'change') {
		$canAddPayment = false;
	} else if ($currentUser->user['user_type'] == 'administrator') {
		$canAddPayment = true;
#cpp#ifdef ENTERPRISE	
	} else if (G_VERSIONTYPE == 'enterprise' && !$currentEmployee->isSupervisor()) {
		$canAddPayment = false;
	} else if (G_VERSIONTYPE == 'enterprise' && $currentEmployee -> supervisesEmployee($editedUser->user['login'])) {
		$canAddPayment = true;
#cpp#endif	
	} else {
		$canAddPayment = false;
	}
	$smarty -> assign("T_CAN_ADD_PAYMENT", $canAddPayment);
	
	$result  = eF_getTableData("payments", "*", "users_LOGIN='".$editedUser -> user['login']."'", "timestamp desc");
	$payments = array();
	foreach ($result as $value) {
		$payments[$value['id']] = $value;
	}
	$smarty -> assign("T_USER_PAYMENTS", sizeof($payments));
	$smarty -> assign("T_USER_TRANSACTIONS_NUM", sizeof($trans));

	if (isset($_GET['ajax']) && $canAddPayment && isset($_GET['balance'])) {
		try {
			if (is_numeric($_GET['balance']) && $_GET['balance'] > 0 && ($_GET['balance'] <= $currentUser->user['balance'] || $currentUser->user['user_type'] == 'administrator')) {
				$editedUser->user['balance'] += $_GET['balance'];
				$response = array('status' => 1, 'user_balance' => _BALANCE.': '.formatPrice($editedUser->user['balance']));
				if ($currentUser -> user['user_type'] != 'administrator') {
					$currentUser->user['balance'] -= $_GET['balance'];
					$response['supervisor_balance'] = _CURRENTBALANCEINYOURACCOUNT.': '.formatPrice($currentUser->user['balance']);
				}
				$editedUser->persist();
				$currentUser->persist();
				echo json_encode($response);
			} else {
				throw new Exception (_YOUCANTADDTHISBALANCE);
			}
		} catch (Exception $e) {
			handleAjaxExceptions($e);
		}
		exit;
	}
	
	$smarty -> assign("T_PAYMENT_METHODS", payments :: $methods);
	$tableName  = 'paymentsTable';
	$dataSource = $payments;
	/**Handle sorted table's sorting and filtering*/
	include("sorted_table.php");

} #cpp#endif
