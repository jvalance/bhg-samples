<?php 
require_once '../view/loginView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/LoginForm.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
echo 'HELLO?';
$form = new LoginForm($conn);
$form->activate();

showScreen($form);
