<?php
require_once 'front.php';
require_once 'loginView.php';
require_once 'Authentication_Adapter.php';

use polarbev\Authentication_Adapter;
use Zend\Authentication\AuthenticationService;

$screenData = $_REQUEST;
$auth = new AuthenticationService();

if ($auth->hasIdentity()) {
	// Identity exists; get it
	$identity = $auth->getIdentity();
	// If already logged in, go to target URL
	goToTarget();
}

$screenData['error'] = false;

if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
	// Set up the authentication adapter
	$authAdapter = new Authentication_Adapter($_POST['userid'], $_POST['pswd']);

	// Attempt authentication, saving the result
	$result = $auth->authenticate($authAdapter);
	if (!$result->isValid()) {
		// Authentication failed; print the reasons why
		foreach ($result->getMessages() as $message) {
			$screenData['messages'][] = "$message\n";
		}
		$screenData['error'] = true;
	} else {
		goToTarget();
	}

} else {
	// First time, no form submitted - clear inputs.
	$screenData ['userid'] = '';
	$screenData ['pswd'] = '';
}

showScreen ( $screenData );


//---------------------------------------------------------------------
function goToTarget() {
	$loc = '';
	//pre_dump('redirect: ' . $_SESSION['login_redirect_to']);
	// If user got bounced to login screen, attempt to retrieve intended target URL
	if (isset($_SESSION['login_redirect_to'])) {
		$loc = $_SESSION['login_redirect_to'];
		unset($_SESSION['login_redirect_to']);
	} else {
		$loc = FRONT_BASE_FOLDER . "productionsched/prodSchedSelect.php" ;
	}

// 	header ( "Location: $loc" );
	header ( "Location: ../productionsched/prodSchedSelect.php" );
	exit;
}

//---------------------------------------------------------------------
// function processInput(&$screenData, Security $sec) {
// 	$screenData ['messages'] = array ();
// 	$screenData ['error'] = false;

// 	$user = trim ( $screenData ['userid'] );
// 	$pswd = trim ( $screenData ['pswd'] );
// 	try {
// 		$result = $sec->authenticate_WOMS_User ( $user, $pswd );
// 		if ($result !== true) {
// 			$screenData ['messages'] [] = $result;
// 			$screenData ['error'] = true;
// 		}
// 	} catch ( Exception $e ) {
// 		$screenData ['messages'] [] = $e->getMessage ();
// 		$screenData ['error'] = true;
// 	}

// 	return ! $screenData ['error'];
// }
