<?php 
require_once '../view/layout.php';
require_once '../view/authEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/AuthoritiesForm.php';
require_once '../model/Authorities.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$form = new AuthoritiesForm($conn);
$form->activate();

$nav = new VGS_Navigator($form->mode);
$nav->addIconButton('Authorities Search', 
						'authListCtrl.php?filtSts=restore', 
						VGS_NavButton::SEARCH_ICON);

showScreen($form, $nav);
