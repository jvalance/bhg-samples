<?php
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../model/DB_Update_Log.php';
 
$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$sewer = new DB_Update_Log($conn);
$select = new VGS_DB_Select(); 
$csv = new VGS_CSV_Builder($conn);
$filter = new VGS_Search_Filter_Group();
 
$sewer->buildFilteredSelect($_REQUEST, $select, $filter);

$select->from = 'DB_UPDATE_LOG dbl ';
$select->order = 'DBL_UPD_TIMESTAMP DESC';

$csv->download_CSV($select, "DB_Update_Log");

db2_close($conn->conn);
