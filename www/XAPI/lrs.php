<?php
session_cache_limiter('none');
session_start();

$path = "../../libraries/";

/** The configuration file.*/
require_once $path."configuration.php";

$request = $_SERVER['REQUEST_URI'];
$request = str_replace(G_OFFSET.'XAPI/', '', $request);
$parts = parse_url($request);

if ($_SERVER['HTTP_X_EXPERIENCE_API_VERSION']) {
	$version = $_SERVER['HTTP_X_EXPERIENCE_API_VERSION'];
} else {
	$version = '0.9';
}
try {
	$tincan = new Tincan($version);

	$request_body = urldecode(file_get_contents('php://input'));
	if (isset($_GET['method'])) {		//This means that we're using the POST-only method for parsing requests (see section 7.6 of the xapi-0.95)
		$request_method = $_GET['method'];
		parse_str($request_body, $params);
		$data = $params['content'];
	} else {
		$request_method = $_SERVER['REQUEST_METHOD'];
		$data = $request_body;
		$params = $_GET;
	}


	switch (trim($parts['path'], '/')) {
		case 'statements': $tincan->handle_statements($request_method, $data, $params); break;
		case 'activities/state': $tincan->handle_activities_state($request_method, $data, $params); break;
		case 'activities':
		case 'activities/profile': $tincan->handle_activities_profile($request_method, $data, $params); break;
		case 'agents':
		case 'agents/profile': $tincan->handle_agents_profile($request_method, $data, $params); break;
		default: break;
	}
} catch (Exception $e) {
	if ($e instanceOf TincanException) {
		header("HTTP/1.0 {$e->getCode()} ");
		echo $e -> getMessage();
		exit;
	} else {
		header("HTTP/1.0 500 ");
		echo $e -> getMessage();
		exit;
	}
}
/*
 //Articulate-specific
$request = $_SERVER['REQUEST_URI'];
$request = str_replace(G_OFFSET.'lrs/', '', $request);
$parts = explode("/", $request);

$request_body = file_get_contents('php://input');
foreach (explode("&", $request_body) as $value) {
list($k, $v) = explode("=", $value);
$params[] = array(urldecode($k) => urldecode($v));
}

pr($params);
pr($parts);
switch ($parts[0]) {
case 'activities': $tincan->handle_activities($parts, $params); break;
case 'statements': $tincan->handle_statements($parts, $params); break;
default : echo '[]'; break;
}
*/