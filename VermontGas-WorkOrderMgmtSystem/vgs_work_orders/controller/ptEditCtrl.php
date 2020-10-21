<?php 
require_once '../view/layout.php';
require_once '../view/ptEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/PipeTypesForm.php';
require_once '../model/Pipe_Type_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$form = new PipeTypesForm($conn);
$form->activate();

$nav = new VGS_Navigator($form->mode);
$nav->addIconButton('Pipe Type Search', 
						'ptListCtrl.php?filtSts=restore',
						VGS_NavButton::SEARCH_ICON);

showScreen($form, $nav);
