<?php
error_reporting(E_ERROR);
// see http://web.archive.org/web/20071211140719/http://www.w3.org/2005/MWI/BPWG/techs/CachingWithPhp
// $lastModifiedDate must be a GMT Unix Timestamp
// You can use gmmktime(...) to get such a timestamp
// getlastmod() also provides this kind of timestamp for the last
// modification date of the PHP file itself
function cacheHeaders($lastModifiedDate) {
	if ($lastModifiedDate) {
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModifiedDate) {
			if (php_sapi_name()=='CGI') {
				Header("Status: 304 Not Modified");
			} else {
				Header("HTTP/1.0 304 Not Modified");
			}
			exit;
		} else {
			$gmtDate = gmdate("D, d M Y H:i:s \G\M\T",$lastModifiedDate);
			header('Last-Modified: '.$gmtDate);
		}
	}
}

// This function uses a static variable to track the most recent
// last modification time
function lastModificationTime($time=0) {
	static $last_mod ;
	if (!isset($last_mod) || $time > $last_mod) {
		$last_mod = $time ;
	}
	return $last_mod ;
}

lastModificationTime(filemtime(__FILE__));
cacheHeaders(lastModificationTime());
header("Content-type: text/javascript; charset: UTF-8");

ob_start ("ob_gzhandler");

foreach (explode(",", $_GET['load']) as $value) {
	if (is_file("$value.js")) {
		$real_path = mb_strtolower(realpath("$value.js"));
		if (strpos($real_path, mb_strtolower(dirname(__FILE__))) !== false || strpos($real_path, mb_strtolower(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR)) !== false) {
			lastModificationTime(filemtime("$value.js"));
			include("$value.js");echo "\n";
		} 
	}
}
?>
