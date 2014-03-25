<?php
#cpp#ifndef COMMUNITY

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}
	// Permission control here
	//print_r($_SESSION);
	if($_GET['external'] == 1) {
		
		session_cache_limiter('none');
		session_start();    //This causes the double-login problem, where the user needs to login twice when already logged in with the same browser

		$path = "../libraries/";
		require_once $path . "facebook_connect.class.php";
	}

	if (isset($_SESSION['facebook_user'])) {
		//pr($_SERVER);
		//pr($_SESSION);
		//echo $_SERVER['HTTP_REFERER'];
		//unset($_SESSION['previousMainUrl']);
			
		//header(G_SERVERNAME . $_SESSION['s_type'] . "page.php");
		//header($_SERVER['HTTP_REFERER']);
		exit;
	} else {
		if($_GET['external'] == 1) {
			session_destroy();
			unset($_SESSION);
		}
	}	

	
	if (!isset($_SESSION)) {
		require_once $path."configuration.php";
		$app_callback = G_SERVERNAME . "index.php";
	} else {
		$app_callback = G_SERVERNAME . $_SESSION['s_type'] . "page.php";
	}
	
	$app_title = $GLOBALS['configuration']['site_name'] . " - Facebook Connect";	
	unset($_SESSION['previousMainUrl']);
	new EfrontFacebook(array("application_name" => $app_title, "callback_url" => $app_callback));
	        
	if (!isset($_SESSION)) {
		session_cache_limiter('none');
		session_start();    //This causes the double-login problem, where the user needs to login twice when already logged in with the same browser
	}
	
#cpp#endif              
