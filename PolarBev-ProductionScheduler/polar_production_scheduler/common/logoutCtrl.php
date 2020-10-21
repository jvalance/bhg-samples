<?php
use Zend\Authentication\AuthenticationService;
require_once 'front.php';
require_once '../productionsched/model/WorkCenter.php';
require_once 'util.php';

$auth = new AuthenticationService();

$wc = new WorkCenter();
$wc->deleteUserLock($_SESSION['current_user']);

if ($auth->hasIdentity()) {
 	$auth->clearIdentity();
 	unset($_SESSION['current_user']);
}

$loginLoc = FRONT_BASE_FOLDER . 'common/loginCtrl.php';
header ( "Location: $loginLoc" );
exit ();
