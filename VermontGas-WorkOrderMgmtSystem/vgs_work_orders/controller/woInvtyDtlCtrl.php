<?php
require_once '../view/layout.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$woNum = $_REQUEST['WO_NUM'];

$conn = VGS_DB_Conn_Singleton::getInstance();
$select = new VGS_DB_Select(); 
$csv = new VGS_CSV_Builder($conn);

$select->from = "dbicictv1"; 
$select->joins = " inner join dbicite on dictitem = DITEITEM ";

$select->columns = <<<WO_COLS
	trim(DICTACTVTY) as "W/O Num",
	dicttrnsdt as "Transx Date", 
	trim(dictitem) as "Item#",
	trim(ditedscrpt) as "Description", 
	dictqntty as "Quantity", 
	(substr(dictuntcst * dictqntty,1,6 )) as "Amount",
	trim(dictofacun) as "Acct Unit", 
	DICTOFFACC as "Account",
	DICTOFSBAC as "Sub Acct"
WO_COLS;
$select->andWhere("DITEITMGRP = 'VGS  '");
$select->andWhere("DICTACTVTY = ?", $woNum);

$csv->download_CSV($select, "WOInventoryDetail_$woNum");
