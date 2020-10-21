<?php
require_once '../view/layout.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../model/Workorder_Master.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$woNum = $_REQUEST['WO_NUM'];

$conn = VGS_DB_Conn_Singleton::getInstance();
$select = Workorder_Master::getDollarsPostedSQLSelect($woNum);

$csv = new VGS_CSV_Builder($conn);
$csv->download_CSV($select, "WOCostDetail_$woNum");
