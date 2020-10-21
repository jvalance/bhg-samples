<?php
/**
 * This script will allow a programmer/tester with *SYSADMIN authority to 
 * swap to a different user profile for testing purposes. 
 */
require_once '../view/layout.php';
require_once '../model/Auth_Profile_Xref.php';
require_once '../model/Sec_Profiles.php';

$menuLink = '<a href="menuMainCtrl.php">Return to main menu</a>';

// Check if logged-in user is authorized to do this  
$apx = new Auth_Profile_Xref($conn);
$sysadminPermission = trim($apx->getPermission('*SYSADMIN', $_SESSION['current_user']));
if ('ALLOW' != $sysadminPermission) {
	echo "You are not authorized to this feature. Only users with *SYSADMIN authority can run this.<br/>$menuLink";
}

$swap_user = strtoupper(trim($_REQUEST['swap_user']));

// Check that User ID is valid
$secProf = new Sec_Profiles( $conn );
$secProfRec = $secProf->retrieveByID ( $swap_user );
if ($secProfRec ['PRF_PROFILE_TYPE'] != 'USER') {
	echo "ERROR: User ID entered ($swap_user) is not a valid user profile for the Work Order System.<br/>$menuLink";
	exit;
} elseif ($secProfRec ['PRF_PROFILE_STATUS'] != 'ACT') {
	echo "ERROR: User ID entered ($swap_user) has been disabled for the Work Order System.<br/>$menuLink";
	exit;
}

// Everything OK - go ahead and swap profile
$_SESSION ['current_user'] = $swap_user;
header('Location: menuMainCtrl.php');
exit;
