<?php
require_once 'error.php';
require_once 'util.php';
require_once 'DB2_Adapter.php';
require_once 'Environment.php';
require_once 'autoloader.php';
use polarbev\Environment as Environment;

error_reporting(E_RECOVERABLE_ERROR | E_ERROR);
//error_reporting(E_ALL); // uncomment this line to see all php messages
ini_set('display_errors', 0);

//set_error_handler("errorLogger", E_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
define('FRONT_BASE_FOLDER', getEnvBaseURL() );

//$ini_options
$polar_ini = parse_ini_file('polar_php.ini', true);

// Session time out in seconds
define('SESSION_TIMEOUT_INTERVAL', $polar_ini['session']['SESSION_TIMEOUT_INTERVAL']);

session_start();

//=====================================================================

use Zend\Authentication\AuthenticationService;
$front_auth = new AuthenticationService();
//pre_dump("In front: getIdentity = {$front_auth->getIdentity()}");

$_SESSION['previousPage'] = $_SESSION['savedPage'];
$_SESSION['savedPage'] = $_SERVER['PHP_SELF'];
if ($_SERVER['QUERY_STRING'] > '') {
	$_SESSION['savedPage'] .= '?'. $_SERVER['QUERY_STRING'];
}
$script = getScriptName();
// pre_dump("in front.php:script = $script");
if (! $front_auth->hasIdentity() && ($script != 'loginCtrl.php')) {
	$_SESSION['login_redirect_to'] = $_SESSION['savedPage'];
	header("Location: ../common/loginCtrl.php");
	exit;
} else {
	$_SESSION['current_user'] = $front_auth->getIdentity();
}


function getEnvBaseURL() {
	if ($_SERVER['HTTPS'] === true) $base_url = 'https://';
	else $base_url = 'http://';
	$base_url .= $_SERVER['HTTP_HOST'];

	//echo ("base_url = $base_url<br>");
	//echo "_SERVER['PHP_SELF'] = {$_SERVER['PHP_SELF']}<p>";
	 
	$env_flr_name = substr($_SERVER['PHP_SELF'], 1);
	$env_flr_name_endpos = strpos($env_flr_name, '/');
	$env_flr_name = substr($env_flr_name, 0, $env_flr_name_endpos);
	
    // 	echo "env_flr_name = {$env_flr_name}<p>";
	
	return ($base_url . '/' . $env_flr_name . '/');

}