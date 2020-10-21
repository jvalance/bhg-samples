<?php 
require_once '../view/layout.php';
require_once '../view/pmEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/ProjectMCFRatesForm.php';
//require_once '../model/Project_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$form = new ProjectMCFRatesForm($conn);
 
$form->activate();

$popup = (bool) $_REQUEST['popup'];
$nav = new VGS_Navigator($form->mode, $popup);

showScreen($form, $nav);
