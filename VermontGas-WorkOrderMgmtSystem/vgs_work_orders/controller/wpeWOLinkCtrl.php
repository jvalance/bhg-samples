<?php 
/**
 * This script will be called when user clicks a button or link for Pipe Exposure 
 * for a given work order. 
 * It will determine if any pipe exposure records already 
 * exist for the work order. 
 * If so, it will redirect to the pipe exposure listing screen for this work order.
 * If not, it will redirect to the pipe exposure add screen for this work order.
 */
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/WO_Pipe_Exposure.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$wpe = new WO_Pipe_Exposure($conn);
$screenData = $_REQUEST;

$woNum = trim($screenData['filter_WPE_WO_NUM']);
$wpeWorkOrders = $wpe->getPipeExposuresForWO($woNum);

// Is this a popup window? We will pass this on the redirect url.
$popup = (bool) $_REQUEST['popup']; 

$sec->setRedirectOnDeny(false);
$blnCreateAllowed = $sec->checkPermissionByCategory('WO', 'CREATE');

if ($wpeWorkOrders['count'] == 0 && $blnCreateAllowed) {
	// If no pipe exposures found for W/O, go to create pipe exposure screen
	$redirectPage = "wpeEditCtrl.php?mode=create&WPE_WO_NUM=$woNum&popup=$popup";
	header("Location: $redirectPage");
} else {
	// If pipe exposure recs exist for w/o, go to listing screen
	$redirectPage = "wpeListCtrl.php?filter_WPE_WO_NUM=$woNum&popup=$popup";
	header("Location: $redirectPage");
}

db2_close($conn);
exit;
