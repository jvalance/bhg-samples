<?php 
require_once '../view/layout.php';
require_once '../view/mfEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/MechanicalFittingFailureForm.php';
require_once '../model/Mechanical_Fitting_Failure.php';
require_once '../model/WorkOrder_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$mfFail = new Mechanical_Fitting_Failure($conn);
$form = new MechanicalFittingFailureForm($conn);
$wo = new Workorder_Master($conn);

$woNum = $_REQUEST['MF_WONUM'];
if (!$wo->validate_WO_NUM($woNum)) {
	die("Invalid Work Order Number passed: $woNum");
}

if ($mfFail->exists_MF_Failure($woNum)) {
	$form->mode = 'update';
} else {
	$form->mode = 'create';
} 

$form->activate();

$nav = new VGS_Navigator($form->mode);
$nav->addIconButton('Mechanical Fitting Failures', 
		'mfListCtrl.php?filtSts=restore',
		VGS_NavButton::SEARCH_ICON);

showScreen($form, $nav);

