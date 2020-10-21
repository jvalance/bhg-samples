<?php
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../model/WO_Sewer.php';
 
$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$sewer = new WO_Sewer($conn);
$select = new VGS_DB_Select(); 
$csv = new VGS_CSV_Builder($conn);
$filter = new VGS_Search_Filter_Group();
 
$sewer->buildFilteredSelect($_REQUEST, $select, $filter);

$select->from = 'WO_SEWER';


$select->columns = <<<WSW_COLS
	WSW_WO_NUM	"W/O#",
	cv1.CV_VALUE as "WO Type",
	cv2.CV_VALUE as "WO Status",
	WO_DESCRIPTION "Description",
	char(fnFmtNullDate(WO_DATE_COMPLETED)) as "WO Date Completed", 
	WSW_SEQNO as "WO Swr Seq#",
	WSW_ADDRESS as "Street Address",
	WSW_CITY as "Town Code",
	cv3.CV_VALUE as "Town Name",
	WSW_LOCATED_PRIOR as "Sewer Located Prior?",
	WSW_SEWER_SIZE as "Size",
	WSW_SEWER_MATERIAL as "Material",
	WSW_SEWER_TYPE as "Type",
	'''' || WSW_SEPARATION_FROM_GAS as "Separation from Gas",
	WSW_DAMAGED_CONSTR as "Damaged During Contruction?",
	WSW_INSPECTION_NEEDED as "Inspection Needed?",
	WSW_INSPECT_REASON as "Inspection Reason",
	char(fnFmtNullDate(WSW_DATE_INSP_COMPLETED)) as "Date Inspection Completed",
	WSW_INSP_FINDINGS AS "Inspection Findings",
	cv4.CV_VALUE as "Inspected By",
	WSW_CREATE_USER as "Entered by",
	char(fnFmtNullDate(date(WSW_CREATE_TIME))) as "Date Entered",
	WSW_MOC_TRENCH as "MOC Trench?",
	WSW_MOC_HDD as "MOC HDD",
	WSW_MOC_HOG as "MOC Hog",
	WSW_MOC_PLOWED as "MOC Plow",
	WSW_MOC_OTHER as "MOC Other",
	fn_MOC_Combo(WSW_MOC_TRENCH, WSW_MOC_HDD, WSW_MOC_HOG,
				 WSW_MOC_PLOWED, WSW_MOC_OTHER) as "MOC Combo",
	WSW_NOTES as "General Notes"
WSW_COLS;

$select->order = 'WSW_WO_NUM DESC, WSW_SEQNO';
$select->joins = 
	"LEFT JOIN WORKORDER_MASTER wo on WSW_WO_NUM = WO_NUM
	 LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE
	 LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS
	 LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'TOWN' and cv3.CV_CODE = WSW_CITY
	 LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'CONTRACTORS' and cv4.CV_CODE = WSW_INSPECTED_BY
	"; 

$csv->download_CSV($select, "sewer_download");

db2_close($conn->conn);
