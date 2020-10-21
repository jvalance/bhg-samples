<?php 
require_once '../view/wswEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/WO_SewerForm.php';
require_once '../model/WO_Sewer.php';
require_once '../model/Code_Values_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();


try {
	$woNum = $_GET['WSW_WO_NUM'];
	$form = new WO_SewerForm($conn, $woNum);
	
	$form->activate();

	//pre_dump($form);
	
	$popup = (bool) $_REQUEST['popup'];
	
	$nav = new VGS_Navigator($form->mode, $popup);
	$nav->addIconButton("Sewer List for W/O $woNum", 
							"wswListCtrl.php?filter_WSW_WO_NUM=$woNum&popup=$popup",
							VGS_NavButton::SEARCH_ICON);
	
	$nav->addIconButton("Sewer List (all W/Os)", 
							"wswListCtrl.php?popup=$popup",
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
	exit;
}
