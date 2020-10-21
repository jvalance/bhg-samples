<?php 
require_once '../view/woEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/WorkOrderForm.php';
require_once '../model/Code_Values_Master.php';
require_once '../forms/VGS_Navigator.php';
require_once '../forms/WOCancellationsForm.php';

// $startTime = new DateTime(); // debug code for time elapsed to load page

$conn = VGS_DB_Conn_Singleton::getInstance();

$form = new WorkOrderForm($conn);
// pre_dump($form->inputs);
// exit;

$form->activate();

$woNum = $form->work_order->record['WO_NUM'];


$nav = new VGS_Navigator($form->mode);
$nav->addIconButton('W/O Search', 'woListCtrl.php?filtSts=restore', VGS_NavButton::SEARCH_ICON);
if (!$form->isCreateMode()) {
	$url = "woPrintCtrl.php?WO_NUM[1]=$woNum&autoPrintRecheck=Y";
	$name = "Print_WO_$woNum";
//	$opts = "fullscreen=yes,status=no,toolbar=yes";
	$nav->addPopupButton('Print', $url, VGS_NavButton::PRINT_ICON, $opts);
}
if (!$form->isCreateMode()) {
	$url = "wcWOLinkCtrl.php?filter_WC_WONUM=$woNum&popup=true";
	$nav->addPopupButton('Cleanup', $url, VGS_NavButton::CLEAN_UP_ICON);

	$url = "wpeWOLinkCtrl.php?filter_WPE_WO_NUM=$woNum&popup=true";
	$nav->addPopupButton('Pipe Exposure', $url, VGS_NavButton::PIPE_EXP_ICON);

// 	$woCnl = new WOCancellationsForm($conn, $woNum, true);
	
	if ($_REQUEST['dollars'] == 'Y') {
		$url = "woCostDtlCtrl.php?WO_NUM=$woNum";
		$nav->addPopupButton('Cost Dtl', $url, VGS_NavButton::COST_ICON);
	}
	
	$url = "woInvtyDtlCtrl.php?WO_NUM=$woNum";
	$nav->addPopupButton('Inventory', $url, VGS_NavButton::INVENTORY_ICON);
	
	if ($form->wo_type == 'SI') {
		$url = "slxMaintCtrl.php?filter_WONUM=$woNum";
		$nav->addPopupButton('Sales Apps', $url, VGS_NavButton::SLSAPP_ICON);
	}
	
	if ($form->isSewerWO()) {
		$url = "wswlistCtrl.php?filter_WSW_WO_NUM=$woNum&popup=1";
		$nav->addPopupButton('Sewers', $url, VGS_NavButton::SEWER_ICON);
	}
	
	if ($form->isLeakWO() && !$form->isRecheckLeakWO()) {
		$url = "ppEditCtrl.php?PP_WONUM=$woNum";
		$nav->addPopupButton('Plastic_Pipe_Failure', $url);
		
		$url = "mfEditCtrl.php?MF_WONUM=$woNum";
		$nav->addPopupButton('Mech_Fitting_Failure', $url);
	}
}

showScreen($form, $nav);

// debug code for time elapsed to load page
// $now = new DateTime();
// echo "<hr>Time elapsed:";
// pre_dump($now->diff($startTime, true));
