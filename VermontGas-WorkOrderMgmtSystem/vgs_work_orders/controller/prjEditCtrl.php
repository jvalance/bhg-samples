<?php 
require_once '../view/layout.php';
require_once '../view/prjEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/ProjectForm.php';
require_once '../model/Project_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$form = new ProjectForm($conn);
$objProject = new Project_Master($conn);
$form->activate();

$projNum = $_REQUEST['PRJ_NUM'];

$nav = new VGS_Navigator($form->mode);
$nav->addIconButton('Project Search', 
							'prjListCtrl.php?filtSts=restore',
							VGS_NavButton::SEARCH_ICON);

if (!$form->isCreateMode()) {
	// If not create mode, add link to search W/Os for this project
	$woCount = Project_Master::getWOCountForProject( $conn, $projNum ); 
	$url = "woListCtrl.php?filter_WO_STATUS=*not_cnl&filter_WO_PROJECT_NUM=$projNum&popup=true";
	$nav->addIconButton("Work Orders for Project $projNum ($woCount)",
			"javascript:openSecondaryWindow(\'$url\', \'WOs_$projNum\');",
			VGS_NavButton::POPUP_ICON);
	
	// Add project estimates button
	$url = "peListCtrl.php?filter_PE_PRJ_NUM=$projNum&popup=true";
	$nav->addIconButton('Project Estimates', 
							"javascript:openSecondaryWindow(\'$url\', \'ProjEst_$projNum\');",
							VGS_NavButton::POPUP_ICON);
}

showScreen($form, $nav);
