<?php 
require_once '../view/wcEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/WO_CleanupForm.php';
require_once '../model/WO_Cleanup.php';
require_once '../model/Code_Values_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();

$form = new WO_CleanupForm($conn);
$form->activate();

$woNum = $_GET['WC_WONUM'];
$popup = (bool) $_REQUEST['popup'];

$nav = new VGS_Navigator($form->mode, $popup);

$nav->addIconButton('Cleanup Search', 
							"wcListCtrl.php?filter_WC_WONUM=$woNum&popup=$popup",
							VGS_NavButton::SEARCH_ICON);

showScreen($form, $nav);
 