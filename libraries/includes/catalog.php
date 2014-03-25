<?php

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

//Add items to cart.
if (isset($_GET['fct'])) {
    $lessons = array();
    $courses = array();
    $result  = eF_getTableData("lessons", "*", "active=1 and publish=1");
    foreach ($result as $value) {
        $lessons[$value['id']] = $value;
    }
    $result = eF_getTableData("courses", "*", "active=1 and publish=1");
    foreach ($result as $value) {
        $courses[$value['id']] = $value;
    }

    $legalLessonValues = array_keys($lessons);
    $legalCourseValues = array_keys($courses);
    $legalBuyTypes = array('lesson', 'course', 'credit');

    $cart = cart :: retrieveCart();

    if ($_GET['fct'] == 'addToCart') {
        if ($_GET['type'] == 'lesson' && isset($_GET['id']) && in_array($_GET['id'], $legalLessonValues)) {
        	unset($cart['credit']);
            $lesson = new EfrontLesson($lessons[$_GET['id']]);
            //Recurring items cannot coexist with anything else in the cart!
            if ($lesson -> options['recurring']) {
                unset($cart);
            }
            $cart['lesson'][$_GET['id']] = $_GET['id'];
        } elseif ($_GET['type'] == 'course' && isset($_GET['id']) && in_array($_GET['id'], $legalCourseValues)) {
        	unset($cart['credit']);
            $course = new EfrontCourse($courses[$_GET['id']]);
            //Recurring items cannot coexist with anything else in the cart!
            if ($course -> options['recurring']) {
                unset($cart);
            }
            $cart['course'][$_GET['id']] = $_GET['id'];
        } elseif ($_GET['type'] == 'credit' && isset($_GET['id'])) {
        	$localeSettings = localeconv();
        	$_GET['id'] = str_replace($localeSettings['decimal_point'], '.', $_GET['id']);
        	if (is_numeric($_GET['id']) && $_GET['id'] > 0) {
	        	unset($cart);
	            $cart['credit'] += $_GET['id'];
        	}
        }
    } else if ($_GET['fct'] == 'removeFromCart' && in_array($_GET['type'], $legalBuyTypes)) {
        if ($_GET['type'] == 'lesson' && isset($_GET['id']) && in_array($_GET['id'], $legalLessonValues)) {
            unset($cart['lesson'][$_GET['id']]);
        } elseif ($_GET['type'] == 'course' && isset($_GET['id']) && in_array($_GET['id'], $legalCourseValues)) {
            unset($cart['course'][$_GET['id']]);
        } elseif ($_GET['type'] == 'credit') {
            unset($cart['credit']);
        }
    } else if ($_GET['fct'] == 'removeAllFromCart') {
        unset($cart);
    }

    if (isset($cart)) {
        $smarty -> assign("T_CART", cart :: prepareCart($cart));
        cart :: storeCart($cart);
    } else {
        $smarty -> assign("T_CART", false);
        cart :: storeCart();
    }

    $smarty -> display("includes/blocks/cart.tpl");
    //It's always an ajax function
    exit;
} else if (isset($_GET['return_paypal'])) {
    if (isset($_GET['cart_entry']) && isset($_GET['product_type'])) {

    } else {
	    cart :: storeCart();
	    unset($_SESSION['previousMainUrl']);
	    eF_redirect(G_SERVERNAME.'studentpage.php?message='.urlencode(_TRANSACTIONCOMPLETELESSONSWILLBEASSIGNED).'&message_type=success');
    }

} else if (isset($_GET['checkout'])) {
    $currentUser = EfrontUserFactory::factory($_SESSION['s_login']);
    if ($currentUser -> user['user_type'] != 'administrator') {
	    $lessons = $currentUser -> getEligibleNonLessons();
	    //$courses = $currentUser -> getEligibleNonCourses();
	    $constraints = array('active' => 1, 'archive' => 0, 'condition' => 'c.show_catalog=1 and c.publish=1 and r.courses_ID is null or r.archive != 0');
	    $constraints['return_objects'] = false;
	    $courses = $currentUser -> getUserCoursesIncludingUnassigned($constraints);
	    $temp = array();
	    foreach ($courses as $value) {
	    	$temp[$value['id']] = $value;
	    }
	    $courses = $temp;
    } else {
        $lessons = $courses = array();
    }

    $cart = cart :: prepareCart(false);
	if (!cart :: compactCart($cart)) {
		eF_redirect("userpage.php?ctg=lessons&catalog=1");
	}

	$cart = cart :: filterCart($cart, $lessons, $courses);
    cart :: storeCart($cart);

    if (empty($cart)) {
        eF_redirect(basename($_SESSION['s_type'])."page.php?ctg=lessons&message=".rawurlencode(_SORRYYOUALREADYHAVETHELESSONSYOUSELECTED)."&message_type=failure", true);
    }

    $cart = cart :: prepareCart(false);
    $smarty -> assign("T_CART", $cart);
    if ($currentUser -> user['balance'] && $GLOBALS['configuration']['enable_balance']) {
        $smarty->assign("T_BALANCE", formatPrice($currentUser -> user['balance']));
        //$currentUser->getUserBalance();
    }

    $totalPrice = $cart['total_price'];
    if (isset($_GET['coupon'])) {
    	try {
    		if ($_GET['coupon']) {
    			$coupon = new coupons($_GET['coupon'], true);
    			if (!$coupon -> checkEligibility()) {
    				throw new Exception(_INVALIDCOUPON);
    			}
    			$totalPrice = round($totalPrice * (1 - $coupon -> {$coupon -> entity}['discount'] / 100), 2);
    			echo json_encode(array('id' 		  => $coupon -> {$coupon -> entity}['id'],
	    							   'price' 		  => $totalPrice,
	    							   'price_string' => formatPrice($totalPrice)));
    		} else {
    			echo json_encode(array('id' 		  => '',
	    							   'price' 		  => $totalPrice,
	    							   'price_string' => formatPrice($totalPrice)));
    	    }
    	} catch (Exception $e) {
    		header("HTTP/1.0 500 ");
    		echo _INVALIDCOUPON;
    	}
    	exit;
    }

    //$form = new HTML_QuickForm("checkout_form", "post", basename($_SERVER['PHP_SELF']).'?ctg=lessons&catalog=1&checkout=1', "", null, true);
    if (basename($_SERVER['PHP_SELF']) == 'index.php') {
        $target =  basename($_SERVER['PHP_SELF']).'?ctg=checkout&checkout=1&register_lessons=1';
        $cancelReturn = G_SERVERNAME.$target."&message=".urlencode(_YOUHAVECANCELLEDTHETRANSACTION);
    } else {
        $target = basename($_SERVER['PHP_SELF']).'?ctg=lessons&catalog=1&checkout=1';
        $cancelReturn = G_SERVERNAME.'studentpage.php?message='.urlencode(_YOUHAVECANCELLEDTHETRANSACTION);
    }
    $form = new HTML_QuickForm("my_checkout_form", "post", $target, "", 'style = "display:inline"', true);
    $form -> addElement('text', 'coupon', null, 'style = "display:none" id = "coupon_code"');
    
    if (G_VERSIONTYPE != 'community') {  #cpp#ifndef COMMUNITY
        if ($totalPrice > 0 && ($GLOBALS['configuration']['paypalbusiness']  || ($GLOBALS['configuration']['enable_balance'] && $currentUser -> user['balance'] && $currentUser -> user['balance'] >= $totalPrice))) {
            if ($GLOBALS['configuration']['paypalbusiness']) {

                $GLOBALS['configuration']['paypalmode'] == 'sandbox' ? $paypalUrl = "www.sandbox.paypal.com" : $paypalUrl = "www.paypal.com";        //Toggle sandbox/normal use
                $GLOBALS['configuration']['paypaldebug'] ? $fieldType = 'text' : $fieldType = 'hidden';

                //$form -> addElement('submit', 'submit_checkout_paypal', _PAYPALPAYNOW, 'class = "flatButton"');

                //Calculate price after discount, if any
                if (($form -> isSubmitted() && $form -> validate()) && $form -> exportValue('coupon') && $coupon = new coupons($form -> exportValue('coupon'), true)) {
		    		if (!$coupon -> checkEligibility()) {
		    			throw new Exception(_INVALIDCOUPON);
		    		}

                    $totalPrice = $totalPrice * (1 - $coupon -> {$coupon -> entity}['discount'] / 100);
                    $useCoupon = 1;
                }

                $productNames = $lessonIds = $courseIds = array();
                foreach ($cart['lesson'] as $lesson) {
                    if ($lesson['recurring']) {
                        !$useCoupon OR $lesson['price'] = $lesson['price'] * (1 - $coupon -> {$coupon -> entity}['discount'] / 100);
                        //$lesson['product_type'] = 'lesson';
                        $recurring            = $lesson;
                    }
                    $productNames[] = $lesson['name'];
                    $lessonIds[]    = $lesson['id'];

                }
                foreach ($cart['course'] as $course) {
                    if ($course['recurring']) {
                        !$useCoupon OR $course['price'] = $course['price'] * (1 - $coupon -> {$coupon -> entity}['discount'] / 100);
                        //$course['product_type'] = 'course';
                        $recurring            = $course;
                    }
                    $productNames[] = $course['name'];
                    $courseIds[]    = $course['id'];
                }
				if ($cart['credit']) {
					$productNames[] = _CREDIT;
					$credit			= $cart['credit'];
				}
                //Use this key to uniquely identify the transaction
                //$transactionID = date('ymdHms') . substr(md5(G_MD5KEY . $_SESSION['s_login']), 0, 4);    //@todo: may not be needed

                //Items that are not subscription-based
                				
                if (sizeof ($productNames) > 0) {
	                //See https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_html_formbasics
	                //and https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables
	                $paypalForm             = new HTML_QuickForm("paypal_form", "post", "https://$paypalUrl/cgi-bin/webscr", "_top", 'id = "paypal_form" style = "display:inline"', true);

	                //------------Section 1: Technical HTML Variables-----------------

	                //The IPN url, required for ipn-enabled handing.
	                //Maximum length: 255 characters
	                $paypalForm -> addElement($fieldType, 'notify_url');

	                // Possible values for 'cmd':
	                // _xclick: The button that the person clicked was a Buy Now button
	                // _donations: The button that the person clicked was a Donate button
	                // _xclick-subscriptions: The button that the person clicked was a Subscribe button.
	                // _cart: For shopping cart purchases; these additional variables specify the kind of shopping cart button or command:
	                //        * add � Add to Cart buttons
	                //        * display � View Cart buttons
	                //        * upload � The Cart Upload command
	                // _oe-gift-certificate: The button that the person clicked was a Buy Gift Certificate button.
	                // _s-xclick: The button that the person clicked was protected from tampering by using encryption, or the button was saved in the merchant�s PayPal account. PayPal determines which kind of button was clicked by decoding the encrypted code or by looking up the saved button in the merchant�s account.
	                $paypalForm -> addElement($fieldType, 'cmd');

	                //An identifier of the source that built the code for the button that the payer clicked,
	                //sometimes known as the build notation. Specify a value using the following format: <Company>_<Service>_<Product>_<Country>
	                $paypalForm -> addElement($fieldType, 'bn');

	                //------------Section 2: HTML Variables for Individual Items-----------------

	                //The price or amount of the product, service, or contribution, not including shipping, handling, or tax.
	                //If omitted from Buy Now or Donate buttons, payers enter their own amount at the time of payment.
	                //NOTE: It is not used in recurring payments
	                if (!$recurring) {
	                    $paypalForm -> addElement($fieldType, 'amount');
	                }
	                //Discount amount associated with an item.
	                //$paypalForm -> addElement($fieldType, 'discount_amount');    //@todo: implement this kind of absolute discount
	                //Discount amount associated with each additional quantity of the item.
	                //$paypalForm -> addElement($fieldType, 'discount_amount2');    //@todo: implement this kind of progressive discount
	                //Discount rate (percentage) associated with an item.
	                //$paypalForm -> addElement($fieldType, 'discount_rate');    //@todo: implement discount coupon this way
	                //Discount rate (percentage) associated with each additional quantity of the item.
	                //$paypalForm -> addElement($fieldType, 'discount_rate2');    //@todo: implement this kind of progressive discount
	                //Number of additional quantities of the item to which the discount applies.
	                //$paypalForm -> addElement($fieldType, 'discount_num');    //@todo: implement this kind of discount

	                //Description of item. If omitted, payers enter their own name at the time of payment.
	                //Maximum length: 127 characters
	                $paypalForm -> addElement($fieldType, 'item_name');

	                //Number of items ordered
	                //NOTE: Not used, is here only for reference
	                //$paypalForm -> addElement($fieldType, 'quantity', '');

	                //Transaction-based tax override variable. Set this to a flat tax amount to apply to the transaction regardless of the buyer�s location
	                //NOTE: Not used, is here only for reference
	                //$paypalForm -> addElement($fieldType, 'tax', '');
	                //Transaction-based tax override variable. Set this to a percentage that will be applied to amount multiplied the quantity selected during checkout
	                //Maximum length: 6 characters
	                //NOTE: Not used, is here only for reference
	                //$paypalForm -> addElement($fieldType, 'tax', '');

	                //Passthrough variables: These variables ('custom', 'item_number', 'item_number_x', 'invoice') are set
	                //by us and returned as is, for own use only.
	                $paypalForm -> addElement($fieldType, 'custom');
	                $paypalForm -> addElement($fieldType, 'item_number');
	                //NOTE: Not used, is here only for reference
	                //$paypalForm -> addElement($fieldType, 'invoice', '');

	                //Character encoding for data sent back and forth paypal
	                $paypalForm -> addElement($fieldType, 'charset');

	                //Return customer to this url, after completing paypal transaction
	                //Maximum length: 1024 characters
	                $paypalForm -> addElement($fieldType, 'return');

	                //Return method. The FORM METHOD used to send data to the URL specified by the return variable after payment completion. '2' means 'post'
	                //Maximum length: 1
	                $paypalForm -> addElement($fieldType, 'rm');

	                //Return customer to this url, after cancelling paypal transaction
	                //Maximum length: 1024 characters
	                $paypalForm -> addElement($fieldType, 'cancel_return');

	                //This is the currency of the transaction
	                //Maximum length: 3 characters
	                $paypalForm -> addElement($fieldType, 'currency_code');

	                //Your PayPal ID or an email address associated with your PayPal account.
	                $paypalForm -> addElement($fieldType, 'business');

	                //Single discount amount to be charged cart-wide.
	                //$paypalForm -> addElement($fieldType, 'discount_amount_cart', $totalPrice);    //@todo: implement this kind of absolute discount
	                //The discount amount associated with item x.
	                //$paypalForm -> addElement($fieldType, 'discount_amount_x', $totalPrice);    //@todo: implement this kind of progressive discount
	                //Single discount rate (percentage) to be charged cart-wide.
	                //$paypalForm -> addElement($fieldType, 'discount_rate_cart', $totalPrice);    //@todo: implement this kind of progressive discount
	                //The discount rate associated with item x.
	                //$paypalForm -> addElement($fieldType, 'discount_rate_x', $totalPrice);    //@todo: implement this kind of discount

	                if ($recurring) {
		                //Regular subscription price.
		                $paypalForm -> addElement($fieldType, 'a3');
		                //Subscription duration. Specify an integer value in the allowable range for the units of duration that you specify with t3.
		                //Maximum length: 2 character
		                $paypalForm -> addElement($fieldType, 'p3');
		                //Regular subscription units of duration. Allowable values: D(1-90),W(1-52),M(1-24),Y(1-5)
		                //Maximum length: 1 character
		                $paypalForm -> addElement($fieldType, 't3');
		                //Recurring payments. Subscription payments recur unless subscribers cancel their subscriptions before the end of the current billing cycle or you limit the number of times that payments recur with the value that you specify for srt.
		                //Maximum length: 1 character
		                $paypalForm -> addElement($fieldType, 'src');
		                //Recurring times. Number of times that subscription payments recur. Specify an integer above 1. Valid only if you specify src="1".
		                //Maximum length: 1 character
		                //NOTE: Not used, is here only for reference
		                //$paypalForm -> addElement($fieldType, 'srt');
		                $paypalForm -> setDefaults(array('a3'            => $totalPrice,
					                                     'p3' 		     => $recurring['recurring_duration'],
					                                     't3' 			 => $recurring['recurring'],
		                                                 'src'           => 1));
	                }

	                $item_number = implode(",", $lessonIds).':'.implode(",", $courseIds).':'.$credit.':'.$coupon -> {$coupon -> entity}['id']; //Separate lesson ids from course ids and coupon id with ':' //@todo: Handle maximum length	                
	                $paypalForm -> setDefaults(array('notify_url'    => G_SERVERNAME."ipn.php",
	                                                 'cmd'           => $recurring ? '_xclick-subscriptions' : '_xclick',
	                                                 'bn'            => $recurring ? 'efront_Subscribe_WPS_GR' : 'efront_BuyNow_WPS_GR',
	                                                 'amount'        => str_replace(",", ".", $totalPrice),
	                                                 'item_name'     => implode(",", $productNames),    //@todo: Handle maximum length
	                                                 'custom'        => $currentUser -> user['login'],
	                                                 'item_number'   => $item_number,     
	                                                 'charset'       => 'utf-8',
	                                                 'return'        => G_SERVERNAME."index.php?ctg=checkout&checkout=1&return_paypal=1",
	                                                 'rm'            => '2',                         //Return method = POST
	                                                 'cancel_return' => $cancelReturn,
	                                                 'currency_code' => $GLOBALS['configuration']['currency'],
	                                                 'business'      => $GLOBALS['configuration']['paypalbusiness']
	                ));

		            $paypalForm -> addElement('submit', 'submit_checkout_paypal', _PAYPALPAYNOW, 'id = "submit_checkout_paypal" class = "flatButton"');

		            $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
		            $paypalForm -> accept($renderer);

		            $smarty -> assign('T_PAYPAL_FORM', $renderer -> toArray());
                }
/*
	            if ($recurring) {

	                $paypalSubscriptionForm -> setDefaults(array(//'notify_url'    => G_SERVERNAME."ipn.php",
				                                                 //'cmd'           => '_xclick-subscriptions',
				                								 //'bn'            => 'efront_Subscribe_WPS_GR',
				                                                 //'item_name'     => $recurring['name'],
				                                                 //'custom'        => $currentUser -> user['login'],
				                                                 //'item_number'   => $recurring['product_type'] == 'lesson' ? $recurring['id'].':' : ':'.$recurring['id'],    //following the pattern lesson:courses for regular payments, this time we simple put <id>: if it's a lesson, or :<id> if it's a course
				                                                 //'charset'       => 'utf-8',
				                                                 //'return'        => G_SERVERNAME."index.php?ctg=checkout&checkout=1&return_paypal=1&cart_entry=".$recurring['id']."&product_type=".$recurring['product_type'],
				                                                 //'rm'            => '2',                         //Return method = POST
				                                                 //'cancel_return' => $cancelReturn,
				                                                 //'currency_code' => $GLOBALS['configuration']['currency'],
				                                                 //'business'      => $GLOBALS['configuration']['paypalbusiness'],
				                								 'a3'            => $recurring['price'],
				                                                 'p3' 		     => $recurring['recurring'],
				                                                 't3' 			 => $recurring['recurring_duration'],
	                                                             'src'           => 1));

	                $paypalSubscriptionForm -> addElement('submit', 'submit_checkout_subscription', _PAYPALPAYNOW, 'class = "flatButton"');
		            $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
		            $paypalSubscriptionForm -> accept($renderer);

		            //$paypalSubscriptionForms[$recurring['product_type']][$recurring['id']] = $renderer -> toArray();
	                $smarty -> assign('T_PAYPAL_SUBSCRIPTION_FORM', $renderer -> toArray());
	            }
*/

            }

            if ($GLOBALS['configuration']['enable_balance'] && $totalPrice <= $currentUser -> user['balance'] && $cart['credit'] == 0) {            	
                $smarty -> assign("T_BALANCE", formatPrice($currentUser -> user['balance']));
                $form -> addElement('submit', 'submit_checkout_balance', _PAYUSINGYOURBALANCE, 'class = "flatButton"');
            }
            $form -> addElement('submit', 'submit_order', _FREEREGISTRATION, 'class = "flatButton" id="free_registration_hidden" style="display:none"');
        } else {	
            if ($totalPrice > 0) {
                $form -> addElement('submit', 'submit_enroll', _ENROLL, 'class = "flatButton"  id="submit_enroll"');
                $form -> addElement('submit', 'submit_order', _FREEREGISTRATION, 'class = "flatButton" id="free_registration_hidden" style="display:none"');
            } else {
                $form -> addElement('submit', 'submit_order', _FREEREGISTRATION, 'class = "flatButton"');
            }
        }
    } else { #cpp#else
        if ($totalPrice > 0) {
    	$form -> addElement('submit', 'submit_enroll', _ENROLL, 'class = "flatButton"  id="submit_enroll"');
    	$form -> addElement('submit', 'submit_order', _FREEREGISTRATION, 'class = "flatButton" id="free_registration_hidden" style="display:none"');
    } else {
            $form -> addElement('submit', 'submit_order', _FREEREGISTRATION, 'class = "flatButton"');
        }
    } #cpp#endif

    $nonFreeLessons = $freeLessons = array();
    foreach ($cart['lesson'] as $key => $value) {
    	//Remove the lesson from the cart if it's not eligible
    	if (!$value['show_catalog'] || !$value['active'] || !$value['publish'] || $value['course_only']) {
    		//Do nothing, simpy bypassing lesson
    	} else if (!$value['price']) {
    		$freeLessons[] = $key;
    	} else {
    		$nonFreeLessons[] = $key;
    	}
    }
    $nonFreeCourses = $freeCourses = array();
    foreach ($cart['course'] as $key => $value) {
    	//Remove the course from the cart if it's not eligible
    	if ((!$value['show_catalog'] && $course -> course['instance_source']) || !$value['active'] || !$value['publish']) {
    		//Do nothing, simpy bypassing course
    	} else if (!$value['price']) {
    		$freeCourses[] = $key;
    	} else {
    		$nonFreeCourses[] = $key;
    	}
    }
    if (!$GLOBALS['configuration']['enable_cart'] && (sizeof($freeLessons) > 0 || sizeof($freeCourses) > 0)) {
    	try {
    		if (sizeof($freeLessons) > 0) {
    			$currentUser -> addLessons($freeLessons, array_fill(0, sizeof($freeLessons), 'student'), true);
    		}
    		if (sizeof($freeCourses) > 0) {
    			$currentUser -> addCourses($freeCourses, array_fill(0, sizeof($freeCourses), 'student'), true);
    		}
    		unset($freeLessons);unset($freeCourses);
    		if (sizeof($nonFreeCourses) == 0 && sizeof($nonFreeLessons) == 0) {
    			unset($cart['lesson'][$key]);
    			unset($cart['course'][$key]);

    			cart :: storeCart($cart);

    			if (basename($_SERVER['PHP_SELF']) == 'index.php') {
    				eF_redirect($_SESSION['s_type']."page.php?ctg=lessons&message=".rawurlencode(_SUCCESSFULLYENROLLED)."&message_type=success");
    			} else {
    				eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=lessons&message=".rawurlencode(_SUCCESSFULLYENROLLED)."&message_type=success");
    			}
    		} else {
    			//$message = _FREELESSONSANDCOURSESWHEREASSIGNEDPLEASEREVIEWNONFREE;
    			if (basename($_SERVER['PHP_SELF']) == 'index.php') {
    				eF_redirect(basename($_SERVER['PHP_SELF']).'?ctg=checkout&checkout=1&register_lessons=1&message='.rawurlencode(_FREELESSONSANDCOURSESWHEREASSIGNEDPLEASEREVIEWNONFREE)."&message_type=success");
    			} else {
    				eF_redirect(basename($_SERVER['PHP_SELF']).'?ctg=lessons&catalog=1&checkout=1&message='.rawurlencode(_FREELESSONSANDCOURSESWHEREASSIGNEDPLEASEREVIEWNONFREE)."&message_type=success");
    			}
    		}
    	} catch (Exception $e) {
    		handleNormalFlowExceptions($e);
    	}
    }

    if ($form -> isSubmitted() && $form -> validate()) {

    	try {
    		unset($cart['lesson']);
    		unset($cart['course']);

            //First, assign free lessons/courses, whatever happens
            if (sizeof($freeLessons) > 0) {
                $currentUser -> addLessons($freeLessons, array_fill(0, sizeof($freeLessons), 'student'), true);
            }
            if (sizeof($freeCourses) > 0) {
                $currentUser -> addCourses($freeCourses, array_fill(0, sizeof($freeCourses), 'student'), true);
            }
            if (isset($cart)) {
                $smarty -> assign("T_CART", cart :: prepareCart($cart));
            }

            if (sizeof($nonFreeLessons) > 0 || sizeof($nonFreeCourses) > 0) {

                if (isset($_POST['submit_checkout_balance'])) {
                	$message = _SUCCESSFULLYENROLLED;
                	if ($form -> exportValue('coupon') && $coupon = new coupons($form -> exportValue('coupon'), true)) {
			    		if (!$coupon -> checkEligibility()) {
			    			throw new Exception(_INVALIDCOUPON);
			    		}
			    		if (!$GLOBALS['configuration']['paypalbusiness']) {        //If we have paypal, the reduction is already done
                		    $totalPrice = $totalPrice * (1 - $coupon -> {$coupon -> entity}['discount'] / 100);
			    		}
                    }

                    if ($currentUser -> user['balance'] < $totalPrice) {
                        throw new EfrontPaymentsException(_INADEQUATEBALANCE, EfrontPaymentsException::INADEQUATE_BALANCE);
                    }
                    if (sizeof($nonFreeLessons) > 0) {
                        $currentUser -> addLessons($nonFreeLessons, array_fill(0, sizeof($nonFreeLessons), 'student'), true);
                    }
                    if (sizeof($nonFreeCourses) > 0) {
                        $currentUser -> addCourses($nonFreeCourses, array_fill(0, sizeof($nonFreeCourses), 'student'), true);
                    }

                    $currentUser -> user['balance'] = $currentUser -> user['balance'] - $totalPrice;
                    $currentUser -> persist();

                    $fields = array("amount"      => $totalPrice,
			                        "timestamp"   => time(),
			                        "method"	  => "balance",
	                                "status"	  => "completed",
			                        "users_LOGIN" => $currentUser -> user['login'],
                    				"lessons"     => $nonFreeLessons,
                    				"courses"	  => $nonFreeCourses);
                    $payment = payments :: create($fields);

                    if ($coupon) {
                    	$coupon -> useCoupon($currentUser, $payment, array('lessons' => $nonFreeLessons, 'courses' => $nonFreeCourses));
                    }

                } else {
                   if ($form -> exportValue('coupon') && $coupon = new coupons($form -> exportValue('coupon'), true)) {
			    		if (!$coupon -> checkEligibility()) {
			    			throw new Exception(_INVALIDCOUPON);
			    		}
			    		if (!$GLOBALS['configuration']['paypalbusiness']) {        //If we have paypal, the reduction is already done
                		    $totalPrice = $totalPrice * (1 - $coupon -> {$coupon -> entity}['discount'] / 100);
			    		}
                    }
                    //in case of 100% discount
                	if ($totalPrice == 0) {
                		//Assign new lessons as inactive
                    	if (sizeof($nonFreeLessons) > 0) {
                        	$currentUser -> addLessons($nonFreeLessons, array_fill(0, sizeof($nonFreeLessons), 'student'), true);
                    	}
                    	if (sizeof($nonFreeCourses) > 0) {
	                        $currentUser -> addCourses($nonFreeCourses, array_fill(0, sizeof($nonFreeCourses), 'student'), true);
                    	}
                    	$message = _SUCCESSFULLYENROLLED;
                    	
                    	$fields = array("amount"      => $totalPrice,
                    			"timestamp"   => time(),
                    			"method"	  => "free",
                    			"status"	  => "completed",
                    			"users_LOGIN" => $currentUser -> user['login'],
                    			"lessons"     => $nonFreeLessons,
                    			"courses"	  => $nonFreeCourses);
                    	$payment = payments :: create($fields);
                	} else {
                    	//Assign new lessons as inactive
                    	if (sizeof($nonFreeLessons) > 0) {
                        	$currentUser -> addLessons($nonFreeLessons, array_fill(0, sizeof($nonFreeLessons), 'student'), false);
                    	}
                    	if (sizeof($nonFreeCourses) > 0) {
	                        $currentUser -> addCourses($nonFreeCourses, array_fill(0, sizeof($nonFreeCourses), 'student'), false);
                    	}
                    	$message = _ADMINISTRATORCONFIRMENROLLED;
                    	
                    	$fields = array("amount"      => $totalPrice,
                    			"timestamp"   => time(),
                    			"method"	  => "manual",
                    			"status"	  => "completed",
                    			"users_LOGIN" => $currentUser -> user['login'],
                    			"lessons"     => $nonFreeLessons,
                    			"courses"	  => $nonFreeCourses);
                    	$payment = payments :: create($fields);
                	}
                	
                	if ($coupon) {
                		$coupon -> useCoupon($currentUser, $payment, array('lessons' => $nonFreeLessons, 'courses' => $nonFreeCourses));
                	}                	 
                }
            }
            cart :: storeCart($cart);

            if (basename($_SERVER['PHP_SELF']) == 'index.php') {
                eF_redirect($_SESSION['s_type']."page.php?ctg=lessons&message=".rawurlencode($message)."&message_type=success");
            } else {
                eF_redirect(basename($_SERVER['PHP_SELF'])."?ctg=lessons&message=".rawurlencode($message)."&message_type=success");
            }
        } catch (Exception $e) {
        	handleNormalFlowExceptions($e);
        }
    }
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($smarty);
    $form   -> accept($renderer);

    $smarty -> assign('T_CHECKOUT_FORM', $renderer -> toArray());

} else {

    //$smarty -> display("includes/catalog.tpl");
}

