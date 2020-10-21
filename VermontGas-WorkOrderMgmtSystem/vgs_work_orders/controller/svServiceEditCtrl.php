<?php 
require_once '../view/svServiceEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/ServicesForm.php';
require_once '../model/Services.php';
require_once '../model/Code_Values_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();

try {
// 	pre_dump($_SESSION);
	
	$form = new ServicesForm($conn);
	$form->activate();
	
// 	pre_dump($form);
	
	$popup = (bool) $_REQUEST['popup'];
	
	//$form->setDateOutputFormat('SV_DATE_COMPLETED');
	
	$nav = new VGS_Navigator($form->mode, $popup);
	$nav->addIconButton('Services List/Search', 
							"svServiceListCtrl.php?filtSts=restore&popup=$popup",
							VGS_NavButton::SEARCH_ICON);
	showScreen($form, $nav);
	
} catch (Exception $e) {
	echo parse_backtrace(debug_backtrace());
}
