<?php
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/VGS_i5_Conn.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../model/WO_Electrofusion.php';
 
$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$ef = new WO_Electrofusion($conn);
$select = new VGS_DB_Select();  
$csv = new VGS_CSV_Builder($conn);
$filter = new VGS_Search_Filter_Group();
 
$ef->buildFilteredSelect($_REQUEST, $select, $filter);

$select->from = 'WO_ELECTROFUSION';

// $i5o = new VGS_i5_Conn();
// $tko = $i5o->connect_default();
// $libl = $tko->CLInteractiveCommand ("DSPLIBL");
// pre_dump($libl);
// exit;

$select->columns = <<<WEF_COLS
	WEF_WO_NUM	"W/O#",
	cv1.CV_VALUE as "WO Type",
	cv2.CV_VALUE as "WO Status",
	WO_DESCRIPTION "WO Description",
	WEF_DESCRIPTION as "EF Description",
	char(fnFmtNullDate(WO_DATE_COMPLETED)) as "WO Date Completed",
	WEF_SEQNO as "EF Seq#",
	cv3.CV_VALUE as "Town",
		 
	char(fnFmtNullDate(WEF_FUSION_DATE)) as "Fusion Date",
	WEF_FUSER_NUM as "Fuser#",
	WEF_FUSER_NUM2 as "Fuser# 2",
	WEF_PROCESSOR_SERIAL_NUM as "Processor Ser#",
	WEF_FUSION_NUM as "Fusion#",
	cv5.CV_VALUE as "Fusion Type",
	cv4.CV_VALUE as "Junction Type",
	char(WEF_PRODUCTION_DATE) as "Production Date",
	WEF_COMPLETED_BY as "Completed By",
	WEF_LOT_NO as "Lot No.",
	WEF_NOTES as "Notes",
	char(fnFmtNullDate(date(WEF_CREATE_TIME))) as "Date Entered",
	WEF_CREATE_USER as "Entered by"
WEF_COLS;

$select->order = 'WEF_WO_NUM DESC, WEF_SEQNO';
$select->joins = 
	"LEFT JOIN WORKORDER_MASTER wo on WEF_WO_NUM = WO_NUM
	 LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE
	 LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS
	 LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'TOWN' and cv3.CV_CODE = WO_TAX_MUNICIPALITY
	 LEFT JOIN CODE_VALUES_MASTER as cv5 ON cv5.CV_GROUP = 'FUSION_TYPE' and cv5.CV_CODE = WEF_FUSION_TYPE
	 LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'WEF_JUNCTION_TYPE' and cv4.CV_CODE = WEF_JUNCTION_TYPE"; 

$csv->download_CSV($select, "Electrofusion_Download");

db2_close($conn->conn);
