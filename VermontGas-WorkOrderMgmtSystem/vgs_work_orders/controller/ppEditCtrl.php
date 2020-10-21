<?php 
require_once '../view/layout.php';
require_once '../view/ppEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/PlasticPipeFailForm.php';
require_once '../model/Plastic_Pipe_Fail.php';
require_once '../model/WorkOrder_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$ppFailure = new Plastic_Pipe_Fail($conn);
$form = new PlasticPipeFailForm($conn);
$wo = new Workorder_Master($conn);

$woNum = $_REQUEST['PP_WONUM'];
if (!$wo->validate_WO_NUM($woNum)) {
	die("Invalid Work Order Number passed: $woNum");
}

if ($ppFailure->exists_PP_Failure($woNum)) {
	$form->mode = 'update';
} else {
	$form->mode = 'create';
} 

$form->activate();

$nav = new VGS_Navigator($form->mode);
$nav->addIconButton('Plastic Pipe Fail Search', 
						'ppListCtrl.php?filtSts=restore',
						VGS_NavButton::SEARCH_ICON);

showScreen($form, $nav);
