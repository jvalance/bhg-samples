<?php
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/Premise.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();

$sec = new Security();
$sec->checkPermissionByCategory('SVC', 'INQUIRY');

$premise = new Premise($conn);
$premNo = $_REQUEST['premNo'];
$premRow = $premise->retrieve($premNo);

// pre_dump("premNo =" . $premNo . "<br />");
// pre_dump($premRow);
// exit;

if (is_array($premRow) && count($premRow) > 0) {
	$spaceLoc = strpos($premRow[UPSAD]," ");
	$premRow[HOUSE] = trim(substr($premRow[UPSAD], 0, $spaceLoc));
	$premRow[STREET] = trim(substr($premRow[UPSAD], $spaceLoc+1));
	
	$cvm = new Code_Values_Master($conn);
	$cityList = $cvm->getCodeValuesList(SV_PREM_ADDR_XREF);

	$city = trim(strtoupper($premRow[UPCTC]));
	$premRow[CITY_CODE] = trim($cityList[$city]);
} else {
	$premRow = array('error' => "Unable to retrieve data for premise # {$premNo}.");
}

$strResponse = json_encode($premRow);
echo $strResponse;
exit;

