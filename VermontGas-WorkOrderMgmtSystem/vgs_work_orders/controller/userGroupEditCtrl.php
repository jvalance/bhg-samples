<?php 
require_once '../view/layout.php';
require_once '../view/userGroupEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/Group_User_Xref_Form.php';
require_once '../model/Group_User_Xref.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$form = new Group_User_Xref_Form($conn);
$form->activate();

$nav = new VGS_Navigator($form->mode);
$nav->addIconButton('User/Group Search', 
							'userGroupListCtrl.php?filtSts=restore',
							VGS_NavButton::SEARCH_ICON);

showScreen($form, $nav);
