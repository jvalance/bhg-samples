<?php
require_once '../view/layout.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../view/wcnCancelledOrdersDownloadView.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$nav = new VGS_Navigator;
$nav->addMainMenuButton();
$screenData = $_REQUEST;

if (!isset($_POST['btnSubmit'])) {
	showScreen($screenData, $nav);
	exit;
} else {
	if (!validateInput($screenData)) {
		showScreen($screenData, $nav);
		exit;
	}
}

$conn = VGS_DB_Conn_Singleton::getInstance();

$dbProcedure = new VGS_DB_Procedure('spCancelledWOs');
$dbProcedure->parms = array(
		$_REQUEST['fromDate'],
		$_REQUEST['toDate'],
		$_REQUEST['cancel_status']
	);

$csv = new VGS_CSV_Builder($conn);

$csv->download_CSV($dbProcedure, "cancelled_wo_download");

function validateInput( array &$screenData ) {
	$screenData ['messages'] = array ();
	$cnlSts = trim($screenData ['cancel_status']);
	$fromDate = trim($screenData ['fromDate']);
	$toDate = trim($screenData ['toDate']);
	
	if ($fromDate != '' && !date_is_valid($fromDate)) {
		$screenData ['messages'] [] = 'From Date is not a valid date in the format yyyy-mm-dd.';
	}
	if ($toDate != '' && !date_is_valid($toDate)) {
		$screenData ['messages'] [] = 'To Date is not a valid date in the format yyyy-mm-dd.';
	}

	if ($cnlSts != '' && $cnlSts != 'CNLCMP' && $cnlSts != 'CNLPND' ) {
		$screenData ['messages'] [] = 'Cancel status must be blank, CNL or CNP.';
	}
	
	$screenData ['error'] = count ( $screenData ['messages'] ) > 0;
	
	return ! $screenData ['error'];
}
