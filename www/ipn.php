<?php
/**
 * ipn.php: File that processes IPN requests from paypal
 *
 * Algorithm:
 * 1. Wait for an HTTP post from PayPal.
 * 2. Create a request that contains exactly the same IPN variables and values in the same order, preceded with cmd=_notify-validate.
 * 3. Post the request to paypal.com or sandbox.paypal.com, depending on whether you are going live or testing your listener in the Sandbox.
 * 4. Wait for a response from PayPal, which is either VERIFIED or INVALID.
 * 5. If the response is VERIFIED, perform the following checks:
 *	 - Confirm that the payment status is Completed.
 *	   PayPal sends IPN messages for pending and denied payments as well; do not ship until the payment has cleared.
 *	 - Use the transaction ID to verify that the transaction has not already been processed, which prevents duplicate transactions from being processed.
 *	   Typically, you store transaction IDs in a database so that you know you are only processing unique transactions.
 *	 - Validate that the receiver's email address is registered to you.
 *	   This check provides additional protection against fraud.
 *	 - Verify that the price, item description, and so on, match the transaction on your website.
 *	   This check provides additional protection against fraud.
 * 6. If the verified response passes the checks, take action based on the value of the txn_type variable if it exists; otherwise, take action based on the value of the reason_code variable.
 * 7. If the response is INVALID, save the message for further investigation.
 *
 */


$path = "../libraries/";
require_once $path."configuration.php";

$GLOBALS['configuration']['paypalmode'] == 'sandbox' ? $paypalUrl = "www.sandbox.paypal.com" : $paypalUrl = "www.paypal.com";        //Toggle sandbox/normal use

try {
    //Get the directory where the log file will be stored
    $admin     = EfrontSystem :: getAdministrator();
    $logFolder = $admin -> user['directory'].'/';
    new EfrontDirectory($logFolder);    //This way if the $logFolder is not a valid directory, we will go to the catch{} block below
} catch (Exception $e) {
    $logFolder = '';                    //Use the current directory for storing the log
}
$logFile = $logFolder.'ipn.log';

file_put_contents($logFile, "\n======================Start of communication====================\n", FILE_APPEND);

try {
    $result = eF_getTableData("payments", "*");
    $processedPayments = array();
    foreach ($result as $value) {
        if ($value['txn_id']) {
            $processedPayments[$value['txn_id']] = $value['status'];
        }
    }

    // TODO (from paypal docs):
    // Check the payment_status is Completed  - DONE
    // Check that txn_id has not been previously processed - DONE
    // Check that receiver_email is your Primary PayPal email - DONE
    // Check that payment_amount/payment_currency are correct - PENDING

    // https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_admin_IPNImplementation
    // Read the post from PayPal and add 'cmd'
    $req = 'cmd=_notify-validate';
    foreach ($_POST as $key => $value) {
        $value = urlencode($value);
        $req .= "&$key=$value";
    }
    // Post back to PayPal to validate
    $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Host: www.paypal.com\r\n";
    $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
    $fp = fsockopen ('ssl://'.$paypalUrl, "443", $errno, $errstr, 30);

    if (!$fp) {
        throw new EfrontPaymentsException('HTTP '._ERROR, EfrontPaymentsException::TRANSACTION_HTTP_ERROR);
    } else {
        fputs ($fp, $header . $req);
        while (!feof($fp)) {
            $res = fgets ($fp, 1024);
            if (strcmp (trim($res), "VERIFIED") == 0) {
                $responseText = '';
                foreach ($_POST as $key => $value){
                    $responseText .= $key." = ".$value."\n";
                }
                file_put_contents($logFile, "Live-VERIFIED IPN\n".$responseText . "\n\n" . $req, FILE_APPEND);

                if ($_POST['receiver_email'] != $GLOBALS['configuration']['paypalbusiness']) {
                    throw new EfrontPaymentsException(_CURRENTSETADDRESSDOESNOTMATCHPAYPAL.':'.$_POST['receiver_email'], EfrontPaymentsException::BUSINESS_ADDRESS_MISMATCH);
                }

                //IPN Transaction Types
                $processPayment = true;
                $paymentStatus  = '';
                switch ($_POST['txn_type']) {
                    //Supported types
                    case 'web_accept':            //Payment received; source is a Buy Now, Donation, or Auction Smart Logos button
                    case 'recurring_payment':     //Recurring payment received
                    case 'subscr_payment':        //Subscription payment received
                    	break;
                    case 'subscr_signup':         //Subscription started
                    	$paymentStatus = 'subscription signup';
                    	$processPayment = false;
                    	break;
                    case 'subscr_eot':           //Subscription expired
                    	$paymentStatus = 'subscription ended';
                    	$processPayment = false;
                    	break;
                    case 'subscr_cancel':        //Subscription canceled
                    	$paymentStatus = 'subscription cancelled';
                    	$processPayment = false;
                    	break;

                    //Unsupported types
                    case '':                  //Credit card chargeback if the case_type variable contains chargeback
                    case 'adjustment':        //A dispute has been resolved and closed
                    case 'cart':              //Payment received for multiple items; source is Express Checkout or the PayPal Shopping Cart.
                    case 'express_checkout':  //Payment received for a single item; source is Express Checkout
                    case 'masspay':           //Payment sent using MassPay
                    case 'merch_pmt':         //Monthly subscription paid for Website Payments Pro
                    case 'new_case':          //A new dispute was filed
                    case 'recurring_payment_profile_created':     //Recurring payment profile created
                    case 'send_money':           //Payment received; source is the Send Money tab on the PayPal website
                    case 'subscr_failed':        //Subscription signup failed
                    case 'subscr_modify':        //Subscription modified
                    case 'virtual_terminal':     //Payment received; source is Virtual Terminal
                    default:
                        throw new EfrontPaymentsException(_UNSUPPORTEDOPERATIONTYPE.':'.$_POST['txn_type'], EfrontPaymentsException::UNSUPPORTED_OPERATION_TYPE);
                        break;
                }

                //Valid payment statuses
                switch ($_POST['payment_status']) {
                    case 'Completed':
                        $activate = 1;    //$activate indicates whether the newly added lessons should be enabled. If the payment_status is 'pending', they are assigned but not enabled
                        if (isset($processedPayments[$_POST['txn_id']])) {
                            if ($processedPayments[$_POST['txn_id']] != 'pending') {    //Duplicate payment, should not be processed
                                throw new EfrontPaymentsException(_DUPLICATECOMPLETEDPAYMENT.':'.$_POST['txn_id'], EfrontPaymentsException::DUPLICATE_PAYMENT);
                            } else {
                                $update = 1;        //$update indicates that this completed payment follows a pending request sent. So, the new lessons should not be assigned, but rather have theur status (active flag) updated
                            }
                        }
                        $paymentStatus = 'completed';
                        break;
                    case 'Pending':
                        $activate = 0;
                        $paymentStatus = 'pending';
                        break;
                    case 'Canceled_Reversal':
                    case 'Created':
                    case 'Denied':
                    case 'Expired':
                    case 'Failed':
                    case 'Refunded':
                    case 'Reversed':
                    case 'Processed':
                    case 'Voided':
                    default:           
                    	if ($processPayment) {
                    		throw new EfrontPaymentsException(_UNSUPPORTEDPAYMENTSTATUS.':'.$_POST['payment_status'], EfrontPaymentsException::UNSUPPORTED_PAYMENT);
                    	}
                        break;
                }

                $user = EfrontUserFactory::factory($_POST['custom']);        //@todo: check user type
                $ids  = explode(":", $_POST['item_number']);
                !$ids[0] OR $lessonIds = explode(",", $ids[0]);
                !$ids[1] OR $courseIds = explode(",", $ids[1]);
                !$ids[2] OR $credit = $ids[2];
                !$ids[3] OR $couponIds = explode(",", trim($ids[3], ","));
                $couponIds = end($couponIds); //There may be multiple coupons set, but only the last is considered

                if ($_POST['txn_type'] == 'subscr_eot') {		//Subscription ended, remove lessons and courses
                	if ($lessonIds && sizeof($lessonIds) > 0 && $lessonIds[0]) {
                		$user -> removeLessons($lessonIds);
                	}
                	if ($courseIds && sizeof($courseIds) > 0 && $courseIds[0]) {
                		$user -> removeCourses($courseIds);
                	}
                } else {
                	if ($lessonIds && sizeof($lessonIds) > 0 && $lessonIds[0]) {
                		$update ? $user -> confirmLessons($lessonIds) : $user -> addLessons($lessonIds, 'student', $activate);                            //@todo: verify ids and handle errors
                	}
                	if ($courseIds && sizeof($courseIds) > 0 && $courseIds[0]) {
                		$update ? $user -> confirmCourses($courseIds) : $user -> addCourses($courseIds, 'student', $activate);
                	}
                	if ($credit) {
                		$user->user['balance'] += $credit;
                		$user->persist();
                	}
                }
                if ($_POST['payment_status'] == 'Pending') {
                    $reason = $_POST['pending_reason'];        //for notification
                }

                $fields = array("amount"      => $_POST['mc_gross'],
		                        "timestamp"   => time(),        //@todo: store paypal time
		                        "method"	  => "paypal",
                                "status"	  => $paymentStatus,
                                "txn_id"	  => $_POST['txn_id'],
                				"users_LOGIN" => $_POST['custom'],
                				"charset" => $_POST['charset'],
                                "comments"    => $responseText,
                    			"lessons"     => $lessonIds,
                    			"courses"	  => $courseIds);

                if (!isset($processedPayments[$_POST['txn_id']])) {
	                $payment = payments :: create($fields);

                    if ($couponIds) {
                    	$coupon  = new coupons($couponIds);
                    	is_array($lessonIds) OR $lessonIds = array();
                    	is_array($courseIds) OR $courseIds = array();
                    	$coupon -> useCoupon($user, $payment, array('lessons' => $lessonIds, 'courses' => $courseIds));
                    }

                } else {
                    $result  = eF_getTableData("payments", "id", "txn_id='".$_POST['txn_id']."'");
                    $payment = new payments($result[0]['id']);
                    $payment -> payments = array_merge($payment -> payments, $fields);
                    $payment -> persist();
                }

            } else if (strcmp ($res, "INVALID") == 0) {
                $responseText = '';
                foreach ($_POST as $key => $value){
                    $responseText .= $key." = ".$value."\n";
                }
                file_put_contents($logFile, "Live-INVALID IPN\n".$logText . "\n\n" . $req, FILE_APPEND);
            }
        }
        fclose ($fp);
    }
} catch (Exception $e) {
    file_put_contents($logFile, "==========System error: start===========
    								\n".$e -> getMessage()."\n".$e -> getTraceAsString()."\n
    							==========System error: end===========", FILE_APPEND);
}
file_put_contents($logFile, "\n======================End of communication====================\n", FILE_APPEND);




?>