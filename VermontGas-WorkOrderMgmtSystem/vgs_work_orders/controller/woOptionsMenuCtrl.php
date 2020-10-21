<?php 
/**
 * This will present a list of options for additional actions
 * to perform on a Work Order after creation or completion.
 */
require_once '../view/layout.php';
require_once '../view/woOptionsMenuView.php';
require_once '../model/Workorder_Master.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/WorkOrderForm.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'update');

// If user clicks done button, return to original screen
if (isset($_REQUEST['doneButton'])) {
	header("Location: woListCtrl.php?filtSts=restore");
	exit;
} 

$woNum = $_REQUEST['WO_NUM'];
$conn = VGS_DB_Conn_Singleton::getInstance();
$woObj = new Workorder_Master($conn);
$form = new WorkOrderForm($conn);

$woRec = $woObj->getWorkorder($woNum);
$woType = $woRec['WO_TYPE'];

if ($woType == 'LM' || $woType == 'LS') {
	$lkObj = new Workorder_Leak($conn);
	$lkRec = $lkObj->get_Workorder_Leak($woNum);
	$leakType = $lkRec['LK_LEAKWO_TYPE'];
} else {
	$leakType = '';
}
$woOptions = array();
$woOptions['print']['url'] = "woPrintCtrl.php?WO_NUM[1]=$woNum&autoPrintRecheck=Y";
$woOptions['print']['name'] = "Print_$woNum";
$woOptions['print']['title'] = "Print Work Order";

$woOptions['display']['url'] = "woEditCtrl.php?mode=inquiry&WO_NUM=$woNum&popup=1";
$woOptions['display']['name'] = "Display_$woNum";
$woOptions['display']['title'] = "Display Work Order Details";

$woOptions['edit']['url'] = "woEditCtrl.php?mode=update&WO_NUM=$woNum&popup=1";
$woOptions['edit']['name'] = "Edit_$woNum";
$woOptions['edit']['title'] = "Edit Work Order";


if ($leakType != 'RECHK') {
	$woOptions['cleanup']['url'] = "wcListCtrl.php?filter_WC_WONUM=$woNum&auto_edit=true";
	$woOptions['cleanup']['name'] = "Cleanup_$woNum";
	$woOptions['cleanup']['title'] = "Cleanup";
	
	$woOptions['exposure']['url'] = "wpeListCtrl.php?filter_WPE_WO_NUM=$woNum&auto_edit=true";
	$woOptions['exposure']['name'] = "PipeExp_$woNum";
	$woOptions['exposure']['title'] = "Pipe Exposure";
	
	if ($woType == 'SI') {
		$woOptions['slsapps']['url'] = "slxMaintCtrl.php?filter_WONUM=$woNum";
		$woOptions['slsapps']['name'] = "SlsApps_$woNum";
		$woOptions['slsapps']['title'] = "Sales Applications";
	}
	
	if ($woType == 'LS' || $woType == 'LM') {
		$woOptions['ppfail']['url'] = "ppEditCtrl.php?PP_WONUM=$woNum";
		$woOptions['ppfail']['name'] = "PPFail_$woNum";
		$woOptions['ppfail']['title'] = "Plastic Pipe Failure";
		
		$woOptions['mffail']['url'] = "mfEditCtrl.php?MF_WONUM=$woNum";
		$woOptions['mffail']['name'] = "MFFail_$woNum";
		$woOptions['mffail']['title'] = "Mechanical Fitting Failure";
	}
	if ($form->isSewerWO()) {
		$woOptions['sewer']['url'] = "wswlistCtrl.php?filter_WSW_WO_NUM=$woNum&popup=1";
		$woOptions['sewer']['name'] = "WOSewer_$woNum";
		$woOptions['sewer']['title'] = "Sewers";
	}
}
$nav = new VGS_Navigator();
$nav->addMainMenuButton();
$nav->addIconButton('W/O Search', 'woListCtrl.php?filtSts=restore', VGS_NavButton::SEARCH_ICON);

showScreen($woRec, $woOptions, $nav);
