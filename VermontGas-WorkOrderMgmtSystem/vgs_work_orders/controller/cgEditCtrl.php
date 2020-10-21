<?php 
require_once '../view/layout.php';
require_once '../view/cgEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/CodeGroupsForm.php';
require_once '../model/Code_Groups.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$cgForm = new CodeGroupsForm($conn);
$cgForm->activate();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $cgForm->isCreateMode()) {
	// Set defaults for add mode
	$cgForm->getElement('CG_STATUS')->setValue('ACT');
	$cgForm->getElement('CG_SEQUENCE')->setValue('0');
} 
// Add descriptions to fields 
//$cgForm->getElement('WO_DATE_COMPLETED')->setValue(date('M d, Y', strtotime($cg_rec['WO_DATE_COMPLETED'])));

$nav = new VGS_Navigator($cgForm->mode);
$nav->addIconButton('Drop Down Lists Search', 
							'cgListCtrl.php?filtSts=restore',
							VGS_NavButton::SEARCH_ICON);

showScreen($cgForm, $nav);
 
