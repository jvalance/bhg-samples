<?php 
require_once '../view/layout.php';
require_once '../view/authProfEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/Auth_Prof_Xref_Form.php';
require_once '../model/Auth_Profile_Xref.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$form = new Auth_Profile_Xref_Form($conn);
$form->activate();

$nav = new VGS_Navigator($form->mode);
$nav->addIconButton('Profile Authority Search', 
							'authProfListCtrl.php?filtSts=restore',
							VGS_NavButton::SEARCH_ICON);

showScreen($form, $nav);
