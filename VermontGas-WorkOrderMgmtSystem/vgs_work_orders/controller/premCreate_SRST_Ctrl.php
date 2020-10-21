<?php 
require_once '../common/vgs_utilities.php';
require_once '../model/Premise.php';
require_once '../model/Workorder_Master.php';
require_once '../model/VGS_DB_Select.php';

session_start();

$conn = VGS_DB_Conn_Singleton::getInstance();

if (isset($_REQUEST['SRST'])) {
	// This is a new batch coming in from the premise selection screen...
	
	// Clear any previous session variables in case user exited loop prematurely before 
	unset($_SESSION['premises_SR_ST']);
	unset($_SESSION['SRSTs_To_Print']);
	unset($_SESSION['SR_Cost']);
	unset($_SESSION['ST_Cost']);
	// Set the list of premises in session for the loop
	$_SESSION['premises_SR_ST'] = $_REQUEST['SRST']; 
}

if (isset($_SESSION['premises_SR_ST']) && count($_SESSION['premises_SR_ST']) > 0 ) {
	process_SRSTs();
} else {
	// No more premises to process - clear session vars & print WOs just created 
	unset($_SESSION['premises_SR_ST']);
	unset($_SESSION['SR_Cost']);
	unset($_SESSION['ST_Cost']);
	
	if (isset($_SESSION['SRSTs_To_Print']) 
	&& count($_SESSION['SRSTs_To_Print']) > 0 ) {
		printWOs();
	} else {
		header("Location: woListCtrl.php?filter_WO_STATUS=*not_cnl");
		exit;
	}	
}


function process_SRSTs () {

	if (isset($_REQUEST['lastSRST_WO'])) {
		// retrieve last WO created
		$lastWONum = $_REQUEST['lastSRST_WO'];
		$woObj = new Workorder_Master($conn);
		$lastWoRec = $woObj->getWorkorder($lastWONum); 
	}
	
	foreach ($_SESSION['premises_SR_ST'] as $premise_type => $woType) {
		// Remove this premise from the session array for SRs so we don't process it again
		unset($_SESSION['premises_SR_ST'][$premise_type]);
		$premiseAry = split('_', $premise_type);
		$premiseNo = $premiseAry[0];
 		$woType = $premiseAry[1];
		break; // Just get first premise off the list
	}
	
	$woEditURL = "woEditCtrl.php?mode=create" . 
			"&WO_TYPE=$woType" . 
			"&WO_PREMISE_NUM=$premiseNo" . 
			"&AUTOCRT=true"; 
	if (isset($_REQUEST['lastSRST_WO'])) {
		$woEditURL .= "&lastSRST_WO=" . $_REQUEST['lastSRST_WO']; 
	}
	$woEditURL .= '&return_point=premCreate_SRST_Ctrl.php';
	
	header("Location: $woEditURL");
	exit();	
}

function printWOs() {
	$printQryStr = '';	
	foreach ($_SESSION['SRSTs_To_Print'] as $wo2print) {
		if ($printQryStr != '') $printQryStr .= '&';
		$printQryStr .= "WO_NUM[$wo2print]=$wo2print";
	}
	$printURL = 'woPrintCtrl.php?' . $printQryStr;
	
	unset($_SESSION['SRSTs_To_Print']);
	header("Location: $printURL");
	exit;
}
