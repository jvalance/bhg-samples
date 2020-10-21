<?php
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Code_Values_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();

$sec = new Security();
$sec->checkPermissionByCategory('DD', 'INQUIRY');

$cvm = new Code_Values_Master($conn);
$ddCode = $_REQUEST['ddCode'];
$aryDDList = $cvm->getCodeValuesList($ddCode, ' ');

// pre_dump("ddCode = $ddCode");
// pre_dump($aryDDList);
// exit;

if (is_array($aryDDList) && count($aryDDList) > 0) {
	$aryDDList['selectList'] = $_REQUEST['select'];
} else {
	$aryDDList = array('error' => "Unable to retrieve drop down list for $ddCode.");
}

$strResponse = json_encode($aryDDList);
echo $strResponse;
exit;

