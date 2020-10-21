<?php
require_once '../view/loginView.php';
require_once '../common/common_errors.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/LoginForm.php';
require_once '../forms/VGS_Navigator.php';
require_once '../model/Sec_Profiles.php';
require_once '../model/VGS_DB_Conn_Singleton.php';

$screenData = $_REQUEST;
$conn = VGS_DB_Conn_Singleton::getInstance ();

$sec = new Security();

if ($sec->isValidUserLoggedIn()) {
	// If already loggin in, go to target URL
	goToTarget();
} else {
	if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
		// Login form was submitted
		if ( processInput($screenData, $sec) ) {
			// Successful login - set user in session and go to target
			$_SESSION ['current_user'] = $_POST ['userid'];
			goToTarget();
		}
	} else {
		// First time, no form submitted - clear inputs.
		$screenData ['userid'] = '';
		$screenData ['pswd'] = '';
	}
	showScreen ( $screenData );
}


//---------------------------------------------------------------------
function goToTarget() {
	$loc = '';
	
	// If user got bounced to login screen, attempt to retrieve intended target URL 
	if (isset($_SESSION['login_redirect_to'])) {
		$loc = $_SESSION['login_redirect_to'];
		unset($_SESSION['login_redirect_to']);
	} else {
		$loc = "menuMainCtrl.php" ;
	}

	header ( "Location: $loc" ); 
	exit ();
}

//---------------------------------------------------------------------
function processInput(&$screenData, Security $sec) {
	$screenData ['messages'] = array ();
	$screenData ['error'] = false;

	$user = trim ( $screenData ['userid'] );
	$pswd = trim ( $screenData ['pswd'] );
	try {
		$result = $sec->authenticate_WOMS_User ( $user, $pswd );
		if ($result !== true) {
			$screenData ['messages'] [] = $result;
			$screenData ['error'] = true;
		}
	} catch ( Exception $e ) {
		$screenData ['messages'] [] = $e->getMessage ();
		$screenData ['error'] = true;
	}

	return ! $screenData ['error'];
}