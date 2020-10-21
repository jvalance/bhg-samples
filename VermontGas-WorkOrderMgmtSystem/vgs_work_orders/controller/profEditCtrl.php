<?php 
require_once '../view/layout.php';
require_once '../view/profEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/Sec_ProfilesForm.php';
require_once '../model/Sec_Profiles.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$form = new Sec_ProfilesForm($conn);
$form->activate();

$nav = new VGS_Navigator($form->mode);
$nav->addIconButton('Profile Search', 'profListCtrl.php?filtSts=restore',
						VGS_NavButton::SEARCH_ICON);

showScreen($form, $nav);
