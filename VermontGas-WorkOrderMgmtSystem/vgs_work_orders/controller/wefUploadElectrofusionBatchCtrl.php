<?php 
session_start();
require_once '../model/WO_Electrofusion.php';
require_once '../model/Workorder_Master.php';
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$response = array();
$response['error_count'] = 0; // no errors
$error_count = 0;

$sec = new Security();

// Authenticate valid and active user on IBM i and WOMS
$ef_auth = $_POST['ef_auth'];
$result = $sec->authenticate_WOMS_User($ef_auth['user'], $ef_auth['pswd']);
if ($result !== true) {
	$response['error_count'] = ++$error_count;
	$response['error_messages'][$error_count] = $result;
	header("Status: 500 $result");
	echo json_encode($response, JSON_FORCE_OBJECT);
	exit;
}
$_SESSION['current_user'] = $ef_auth['user'];

// Validate user has access to Electrofusiuon upload function
$blnPermitted = $sec->checkAuthoritiesPermission(array('WEF_UPLOAD'), false);
if (!$blnPermitted) {
	$msg = "User {$_SESSION['current_user']} not authorized to upload Electrofusion records.";
	$response['error_count'] = ++$error_count;
	$response['error_messages'][$error_count] = $msg; 
	header("Status: 500 $msg");
	echo json_encode($response, JSON_FORCE_OBJECT);
	exit;
}

// Get the records to process from the request.
$ef_data = $_POST['ef_data'];

$obj_EF = new WO_Electrofusion();
$obj_EF->setIsBatchRequest(true); // don't echo errors

foreach ($ef_data as $ef_recordNum => $ef_record) {
	if (array_key_exists('WEF_TOWN', $ef_record)) {
		unset($ef_record['WEF_TOWN']);
	}
	if (array_key_exists('WEF_ADDRESS', $ef_record)) {
		$ef_record['WEF_DESCRIPTION'] = $ef_record['WEF_ADDRESS'];
		unset($ef_record['WEF_ADDRESS']);
	}
	
	try {
		$date = strtotime($ef_record['WEF_FUSION_DATE']);
		if (!$date) {
			throw new Exception("Invalid fusion date: {$ef_record['WEF_FUSION_DATE']}");
		}
		$ef_record['WEF_FUSION_DATE'] = date('Y-m-d',$date);
		
		$prodDateAry = split('/', $ef_record['WEF_PRODUCTION_DATE']);
		$MM = $prodDateAry[0];
		$YY = $prodDateAry[1];
		$ef_record['WEF_PRODUCTION_DATE'] = "20{$YY}{$MM}";
		if (!is_numeric($ef_record['WEF_PRODUCTION_DATE'])) {
			throw new Exception("Invalid production date: {$ef_record['WEF_PRODUCTION_DATE']}");
		}
		
		$woNum = $ef_record['WEF_WO_NUM'];
		$wo = new Workorder_Master();
		if (!$wo->validate_WO_NUM($woNum)) {
			throw new Exception("Invalid W/O number: $woNum.");
		}
		
		$blnResult = $obj_EF->create($ef_record);
		
	} catch (Exception $e) {
		// process error
		$msg = "Unable to create Electrofusion record for WO# {$ef_record['WEF_WO_NUM']}. "
					. $e->getMessage();
		$response['error_count'] = ++$error_count;
		$response['error_messages'][$error_count] = $msg; 
		header("Status: 500 $msg");
		echo json_encode($response, JSON_FORCE_OBJECT);
		exit;
		break;
	}
}

echo json_encode($response, JSON_FORCE_OBJECT);
exit;


