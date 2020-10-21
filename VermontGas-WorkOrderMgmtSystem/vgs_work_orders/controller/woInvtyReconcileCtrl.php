<?php
require_once '../view/layout.php';
require_once '../view/woInvtyReconcileView.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$nav = new VGS_Navigator;
$nav->addMainMenuButton();
$screenData = $_REQUEST;

if (!isset($_POST['item'])) {
	showScreen($screenData, $nav);
	exit; 
} else {
	if (!validateInput($screenData)) {
		showScreen($screenData, $nav);
		exit; 
	}
}

$conn = VGS_DB_Conn_Singleton::getInstance();
$select = new VGS_DB_Select(); 
$csv = new VGS_CSV_Builder($conn);

$select->from = "dbicictv1"; 
$select->joins = " inner join dbicite on dictitem = DITEITEM ";
$select->joins .= "left join workorder_master on int(DICTACTVTY) = wo_num";

$select->columns = <<<WO_COLS
	trim(DICTACTVTY) as "W/O Num",
	dicttrnsdt as "Transx Date", 
	trim(dictitem) as "Item#",
	trim(ditedscrpt) as "Description",	
	wo_estimated_length as "WO Est Length",
	wo_actual_length as "WO Act Length",
	dictqntty as "Lawson Length", 
	(wo_actual_length - (abs(dictqntty))) as "Difference",
	round((( (abs(dictqntty)-wo_actual_length) / abs(dictqntty) ) * 100),2) as "Percent Diff",
	(substr(dictuntcst * dictqntty,1,6 )) as "Lawson Amount",
	trim(dictofacun) as "Acct Unit", 
	DICTOFFACC as "Account",
	DICTOFSBAC as "Sub Acct"
WO_COLS;
	
$item = $_REQUEST['item'];
$fromDate = $_REQUEST['fromDate'];
$toDate = $_REQUEST['toDate'];

$select->andWhere("DITEITMGRP = 'VGS  '");
$select->andWhere("trim(dictitem) = ?", $item);
$select->andWhere("dicttrnsdt >= ?", $fromDate);
$select->andWhere("dicttrnsdt <= ?", $toDate);

//pre_dump($select->toString());
//pre_dump($select->parms);

$csv->download_CSV($select, "WOInventoryReconcile_$item");

function validateInput( array &$screenData ) {
	$screenData ['messages'] = array ();
	$item = trim($screenData ['item']);
	$fromDate = trim($screenData ['fromDate']);
	$toDate = trim($screenData ['toDate']);
	
	if ($item == '') {
		$screenData ['messages'] [] = 'Item number is required.';
	} 
	if ($fromDate == '') {
		$screenData ['messages'] [] = 'From Date is required.';
	} 
	if ($toDate == '') {
		$screenData ['messages'] [] = 'To Date is required.';
	}
	
	if (!date_is_valid($fromDate)) {
		$screenData ['messages'] [] = 'From Date is not a valid date in the format yyyy-mm-dd.';
	}
	if (!date_is_valid($toDate)) {
		$screenData ['messages'] [] = 'To Date is not a valid date in the format yyyy-mm-dd.';
	}
	
	$screenData ['error'] = count ( $screenData ['messages'] ) > 0;
	return ! $screenData ['error'];
}


