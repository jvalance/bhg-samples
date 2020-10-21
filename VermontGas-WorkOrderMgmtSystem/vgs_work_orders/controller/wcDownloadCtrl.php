<?php
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../forms/VGS_Search_Filter_Group.php';
//require_once '../model/Pipe_Type_Master.php';
require_once '../model/WO_Cleanup.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$cleanUp = new WO_Cleanup($conn);
$select = new VGS_DB_Select(); 
$csv = new VGS_CSV_Builder($conn);
$filter = new VGS_Search_Filter_Group();
 
$cleanUp->buildFilteredSelect($_REQUEST, $select, $filter);

$select->from = 'WO_CLEANUP';

$select->columns = <<<WC_COLS
	WC_WONUM	"W/O#",
	cv1.CV_VALUE as "WO Type", 
	WO_DESCRIPTION "Description",
	cv3.CV_VALUE as "Cleanup Type", 
	cv4.CV_VALUE as "Town", 
	WC_CLEANUP_STATUS as "Status",
	WC_VENDOR_NUM as "Crew/Vendor",
	WC_EARLY_START_DATE	as "Early Start Date",
	WC_LATE_FINISH_DATE	as "Late Finish Date",
	WC_ESTIMATED_SIZE_1	as "Est. Size",
	WC_ESTIMATED_SIZE_2	as "By",
	WC_ACTUAL_SIZE_1	as "Actual Size",
	WC_ACTUAL_SIZE_2	as "By", 
	WC_COMPLETION_FOOTAGE as "Compl Footage",
	WC_DATE_COMPLETED	as "Date Completed",
	WC_COMMENTS	as "Comments"
WC_COLS;

$select->order = 'WC_WONUM DESC, WC_CLEANUP_NUM';
$select->joins = 
	"LEFT JOIN WORKORDER_MASTER wo on WC_WONUM = WO_NUM
	LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE  
	LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS   
	LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'WC_CLEANUP_TYPES' and cv3.CV_CODE = WC_CLEANUP_TYPE
	LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'TOWN' and cv4.CV_CODE = WO_TAX_MUNICIPALITY 
	"; 

$csv->download_CSV($select, "cleanup_download");

db2_close($conn->conn);
