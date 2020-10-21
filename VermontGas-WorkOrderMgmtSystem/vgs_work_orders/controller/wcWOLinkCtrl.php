<?php 
/**
 * This script will be called when user clicks a button or link for Cleanup
 * for a given work order. 
 * It will determine if any cleanup records already 
 * exist for the work order. 
 * If so, it will redirect to the cleanup listing screen for this work order.
 * If not, it will redirect to the cleanup add screen for this work order.
 */
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/WO_Cleanup.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$wc = new WO_Cleanup($conn);
$screenData = $_REQUEST;

$woNum = trim($screenData['filter_WC_WONUM']);
$wcWorkOrders = $wc->getCleanupsForWO($woNum);

// Is this a popup window? We will pass this on the redirect url.
$popup = (bool) $_REQUEST['popup']; 

$sec->setRedirectOnDeny(false);
$blnCreateAllowed = $sec->checkPermissionByCategory('WO', 'CREATE');

if ($wcWorkOrders['count'] == 0 && $blnCreateAllowed) {
	// If no cleanups found for W/O, go to create cleanup screen
	$redirectPage = "wcEditCtrl.php?mode=create&WC_WONUM=$woNum&popup=$popup";
	header("Location: $redirectPage");
} else {
	// If cleanup recs exist for w/o, go to listing screen
	$redirectPage = "wcListCtrl.php?filter_WC_WONUM=$woNum&popup=$popup";
	header("Location: $redirectPage");
}

db2_close($conn);
exit;
