<?php 
require_once '../view/wefEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/WO_ElectrofusionForm.php';
require_once '../model/WO_Electrofusion.php';
require_once '../model/Code_Values_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();

try {
	$woNum = $_GET['WEF_WO_NUM'];
	$form = new WO_ElectrofusionForm($conn, $woNum);
	$form->activate();

	//pre_dump($form);
	
	$popup = (bool) $_REQUEST['popup'];
	
	$nav = new VGS_Navigator($form->mode, $popup);
	$nav->addIconButton("Electrofusion List for W/O $woNum", 
							"wefListCtrl.php?filter_WEF_WO_NUM=$woNum&popup=$popup",
							VGS_NavButton::SEARCH_ICON);
	
	$nav->addIconButton("Electrofusion List (all W/Os)", 
							"wefListCtrl.php?popup=$popup",
							VGS_NavButton::SEARCH_ICON);
	
	$nav->addPopupButton("Display W/O $woNum",
			"woEditCtrl.php?WO_NUM=$woNum&mode=inquiry&popup=$popup",
			VGS_NavButton::POPUP_ICON);
	
	
	showScreen($form, $nav);

} catch (Exception $e) {
// 	pre_dump($e);
	echo $e->getMessage() . '<p>';
	echo $e->getTraceAsString() . '<p>';
	echo parse_backtrace(debug_backtrace()) . '<p>';
}
