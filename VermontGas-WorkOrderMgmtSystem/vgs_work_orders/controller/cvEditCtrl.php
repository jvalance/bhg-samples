<?php 
require_once '../view/layout.php';
require_once '../view/cvEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/CodeValuesForm.php';
require_once '../model/Code_Values_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$cvForm = new CodeValuesForm($conn);
$cvForm->activate();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $cvForm->isCreateMode()) {
	// Set defaults for add mode
	$cvForm->getElement('CV_STATUS')->setValue('ACT');
	$cvForm->getElement('CV_SEQUENCE')->setValue('0');
} 

if (!isset($_GET['CV_GROUP'])) {
	exit('No Group specified - Group Code must be specified to create a new code/value record.');
}
$cvForm->populate($_GET);

$nav = new VGS_Navigator($cvForm->mode);
$nav->addIconButton(
			'Drop Down Values Search', 
			"cvListCtrl.php?filtSts=restore&filter_CV_GROUP={$_REQUEST['CV_GROUP']}",
			VGS_NavButton::SEARCH_ICON);

showScreen($cvForm, $nav);
