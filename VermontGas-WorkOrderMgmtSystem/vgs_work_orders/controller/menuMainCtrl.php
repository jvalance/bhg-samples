<?php 
require_once '../view/menuMainView.php';
require_once '../model/Auth_Profile_Xref.php';

$screenData = $_REQUEST;
$apx = new Auth_Profile_Xref($conn);

// Allow testing with a different profile is curr user has *sysadmin, but only for dev & test envs.
$screenData['allowProfileSwap'] = $apx->isUserSysAdmin() && 
								  (  VGS_DB_Conn_Singleton::isDevEnvironment() 
								  || VGS_DB_Conn_Singleton::isTestEnvironment() );

showScreen($screenData);
