<?php
require_once('config.php');
// If this is a production environment, then check the client.
if($act_srv_mode > 0) {
// Real clients will have an X-Nintendo-Platform-ID header, and I think that's the only definite thing.
	if(!isset($_SERVER['HTTP_X_NINTENDO_PLATFORM_ID'])) {
	// Keep crawlers, etc. away
	http_response_code(403);
	exit();
	}
} else {
ini_set('display_errors', true);
ini_set('html_errors', false);
}

// Headers
header('X-Nintendo-Date: ' . microtime(true));
// Remove Content-Type
header('Content-Type: text/html');
header_remove('Content-Type');

// Timezone, encoding, MySQL connection
mb_internal_encoding('UTF-8');
$mysql = mysqli_connect($act_mysql_host, $act_mysql_user, $act_mysql_pass, $act_mysql_db);
date_default_timezone_set('America/New_York');
// MySQL wrappers

function prepared(string $txt, $values = null) {
global $mysql;
$stmt = $mysql->prepare($txt);
if($values !== null) {
	$params = '';
		foreach($values as &$param) {
		$params .= is_int($param) ? 'i' : 's';
		}
	$funcparam = array_merge(array($params), $values);
	foreach($funcparam as $key => $value)
	$tmp[$key] = &$funcparam[$key];
	call_user_func_array([$stmt, 'bind_param'], $tmp);
	}
	if(!$stmt) {
	global $thing;
	$thing->Error(2001, 0);
	}
$stmt->execute();
	if(!$stmt || $stmt->errno) {
	global $thing;
	$thing->Error(2001, 0);
	throw new Exception($stmt->errno . ', ' . $stmt->error);
	} else {
	return $stmt->get_result();	
	}
}
function nice_ins(string $table, array $values) {
global $mysql;
$stmt = $mysql->prepare('INSERT INTO '.$table.'('.(implode(', ', array_keys($values))).')
VALUES('.rtrim(str_repeat('?, ', count($values)), ', ').')');
	$params = '';
		foreach($values as &$param) {
		$params .= is_int($param) ? 'i' : 's';
        }
	$funcparam = array_merge(array($params), array_values($values));
	foreach($funcparam as $key => $value) $tmp[$key] = &$funcparam[$key];
	call_user_func_array([$stmt, 'bind_param'], $tmp);
$stmt->execute();
	if(!$stmt || $stmt->errno) {
	global $thing;
	$thing->Error(2001, 0);
	throw new Exception($stmt->errno . ', ' . $stmt->error);
	} else {
	return $stmt->get_result();	
	}
}

// AltoRouter
require_once('router.php');
$router = new AltoRouter();

// These are routes for AltoRouter, documentation is at their site.
// Make this an array_merge and merge with some modular file at some point.
$router->addRoutes(array(
  array('GET', '/', 'Index', 'Index-root'),
  array('GET', '/v1/api/admin/time', 'GetTime', 'Admin-get-time'),
  array('GET', '/v1/api/admin/mapped_ids', 'mappedIDs', 'Mapped-IDs'),
  array('GET', '/v1/api/devices/@current/status', 'checkDeviceStatus', 'Device-check-status'),
  array('POST', '/v1/api/support/validate/email', 'checkEmail', 'Email-check'),
  array('GET', '/v1/api/content/time_zones/[:country]/[:language]', 'viewTimezones', 'Timezones-view'),
  array('GET', '/v1/api/content/agreements/[:agreement]/[:country]/[:version]', 'viewAgreement', 'Agreement-view'),
  array('GET', '/v1/api/people/[:user_id]', 'checkUserID', 'UserID-check'),
  array('GET|POST', '/v1/api/people/', 'createPerson', 'Person-create'),
array('GET|POST|PUT', '/v1/api/people[*]', 'NotImplement', 'Not-implemented-err1'),
array('GET|POST|PUT', '/v1/api/oauth20[*]', 'NotImplement', 'Not-implemented-err2'),
array('GET|POST|PUT', '/v1/api/provider[*]', 'NotImplement', 'Not-implemented-err3'),
//array('GET|POST|PUT', '/v1/api/', 'NotImplement', 'Not-implemented-err4'),
  ));
$match = $router->match();
// After matching, load a new NNAccount object.
require_once 'lib/NNAccount.php';

$thing = new NNAccount($mysql);
// If we've matched, then do the function in the target (with args)
if($match) {
$f = $match['target'];
$thing->$f($match['params']);
} else {
// If not, use the object to throw an error of code 8, which is Not Found.
$thing->Error(8);
}

// Error handler.
function handle($ex) {
global $thing;
// There's probably a $thing if there's an error.
if(isset($thing)) {
// If so, then throw the first variant of 2001.
  $thing->Error(2001, 0);
} else {
// If not, just throw a 500 code.
http_response_code(500);
}
  exit();
}
error_reporting(E_ERROR);
set_exception_handler('handle');