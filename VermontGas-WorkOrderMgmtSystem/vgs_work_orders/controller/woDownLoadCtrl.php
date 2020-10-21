<?php
require_once '../view/layout.php';
require_once '../model/Workorder_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../forms/VGS_Search_Filter_Group.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$wo = new Workorder_Master($conn);
$select = new VGS_DB_Select(); 
$csv = new VGS_CSV_Builder($conn);
$filter = new VGS_Search_Filter_Group();

$wo->buildFilteredSelect($_REQUEST, $select, $filter);

if (isLeakDownload()) {
	getSQLSelect_Leaks($select);
} else {
	getSQLSelect_Standard($select);
}
 
//pre_dump($select->toString());
//pre_dump($select->parms); 

$csv->download_CSV($select, "wo_download");

function isLeakDownload( ) {
	$leakFilters = array('LM', 'LS', 'L*', 'LO', 'LR');
	return in_array($_REQUEST['filter_WO_TYPE'], $leakFilters);	
}

function getSQLSelect_Standard( $select ) {
	$select->columns = <<<WO_COLS
		WO_NUM as "WO Number",
		cv1.CV_VALUE as "Type",
		cv2.CV_VALUE as "Status",
		char(fnFmtNullDate(fnMDYYToDate(SLSDAT))) as "Date Approved",
		WO_DESCRIPTION as "Description",
		WO_PROJECT_NUM as "Project#",
		PRJ_DESCRIPTION as "Project",
		cv3.CV_VALUE as "Town",
		PT_DESCRIPTION as "Pipe Type",
		char(fnFmtNullDate(WO_ENTRY_DATE)) as "Date Entered",
		char(fnFmtNullDate(WO_DATE_COMPLETED)) as "Date Completed",
		WO_CREW_ID as "Crew ID",
		WO_ESTIMATED_COST as "Est. Cost",
		WO_EST_COST_PER_FOOT as "Est. Cost per Foot",
		WO_ESTIMATED_LENGTH as "Est. Length",
		WO_ACTUAL_LENGTH as "Actual Length",
		WO_CONSTRUCTION_TYPE as "Constr. Type",
	    cv4.CV_VALUE as "Condition Found",
	    cv5.CV_VALUE as "Repaired Method/Repaired Equipment",
		WO_SPECIAL_INSTRUCTION as "Special Instructions",
		WO_MISC_NOTES as "Comments"
WO_COLS;
	
	$select->joins = <<<WO_JOINS
		LEFT JOIN WORKORDER_LEAK on WO_NUM = LK_WO_NUM
		LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE
		LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS
		LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'TOWN' and cv3.CV_CODE = WO_TAX_MUNICIPALITY
		LEFT JOIN PIPE_TYPE_MASTER as pt ON PT_PIPE_TYPE = WO_PIPE_TYPE
		LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'CONDITION_FOUND' and cv4.CV_CODE = WO_CONDITION_FOUND 	
		LEFT JOIN CODE_VALUES_MASTER as cv5 ON cv5.CV_GROUP = 'REPAIR_METHOD_EQUIP' and cv5.CV_CODE = WO_REPAIR_METHOD_EQUIP	
		LEFT JOIN SLSAPP as sa ON SLSBKF = WO_SALES_APP_NUM and SLSWO# = WO_Num
		LEFT JOIN PROJECT_MASTER as prj ON PRJ_NUM = WO_PROJECT_NUM
WO_JOINS;
	
}

function getSQLSelect_Leaks( $select ) {
	$select->columns = <<<WO_COLS
		WO_NUM as "WO Number",
		cv1.CV_VALUE as "Type",
		cv2.CV_VALUE as "Status",
		WO_DESCRIPTION as "W/O Description",
		WO_SPECIAL_INSTRUCTION as "Special Instructions",
		cv3.CV_VALUE as "Town Desc",
		PT_DESCRIPTION as "Pipe Type Desc",
		char(fnFmtNullDate(WO_ENTRY_DATE)) as "Date Entered",
		char(fnFmtNullDate(WO_DATE_COMPLETED)) as "Date Completed",
		cv4.CV_VALUE as "Pipe Material",
		cv5.CV_VALUE as "Pipe Size",
		cv6.CV_VALUE as "Pipe Coating",
		LK_LEAKWO_TYPE as "Orig/Recheck",
		cv10.CV_VALUE as "Leak Class", 
		LK_REPORTED_BY as "Reported By",
		LK_REPORTED_TO as "Reported To",
		LK_SURVEYED_BY as "Surveyed By",
		char(fnFmtNullDate(LK_DATE_FOUND)) as "Date Found", 
		cv11.CV_VALUE as "Material Type",   
		cv12.CV_VALUE as "Leak Origin", 
		LK_CREW_ID as "Crew ID",
		cv13.CV_VALUE as "Survey Type", 
		cv14.CV_VALUE as "Equipment Type", 
		cv15.CV_VALUE as "Threat", 
		cv16.CV_VALUE as "Sub-Threat", 
		cv17.CV_VALUE as "Leak Repair Method", 
		LK_REPMETH_OTHER as "Repair Method-Other",
		cv18.CV_VALUE as "Leak Repair Equipment", 
		LK_REPEQUIP_OTHER as "Repair Equip-Other",
	    WO_SPECIAL_INSTRUCTION as "Special Instructions",
		WO_MISC_NOTES as "Comments"
WO_COLS;

// 		LK_PERSONNEL_COST as "Personnel Cost",
// 		LK_EQUIPMENT_COST as "Equipment Cost",
// 		LK_MATERIAL_COST as "Material Cost", 
// 	--		WO_TAX_MUNICIPALITY as "Town",
// 	--		WO_PIPE_TYPE as "Pipe Type",
// 	--		cv9.CV_VALUE as "Leak Type",
// 	--		char(fnFmtNullDate(LK_DATE_REPAIRED)) as "Date Repaired",
// 	--		LK_INSPECTOR_ID as "Inspector ID",
	
	$select->joins = <<<WO_JOINS
		LEFT JOIN WORKORDER_LEAK on WO_NUM = LK_WO_NUM
		LEFT JOIN PIPE_TYPE_MASTER as pt ON PT_PIPE_TYPE = WO_PIPE_TYPE
			
		LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE
		LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS
		LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'TOWN' and cv3.CV_CODE = WO_TAX_MUNICIPALITY
		LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'PIPE_MTRL' and cv4.CV_CODE = WO_PIPE_MATERIAL
		LEFT JOIN CODE_VALUES_MASTER as cv5 ON cv5.CV_GROUP = 'PIPE_DIAM' and cv5.CV_CODE = WO_PIPE_SIZE
		LEFT JOIN CODE_VALUES_MASTER as cv6 ON cv6.CV_GROUP = 'PIPE_COATING' and cv6.CV_CODE = WO_COATING_TYPE
			
		LEFT JOIN CODE_VALUES_MASTER as cv9 ON cv9.CV_GROUP = 'LK_TYPE' and cv9.CV_CODE = LK_TYPE
		LEFT JOIN CODE_VALUES_MASTER as cv10 ON cv10.CV_GROUP = 'LK_LEAK_CLASS' and cv10.CV_CODE = LK_LEAK_CLASS
		LEFT JOIN CODE_VALUES_MASTER as cv11 ON cv11.CV_GROUP = 'LK_MATERIAL_TYPE' and cv11.CV_CODE = LK_MATERIAL_TYPE
		LEFT JOIN CODE_VALUES_MASTER as cv12 ON cv12.CV_GROUP = 'LK_LEAK_ORIGIN' and cv12.CV_CODE = LK_LEAK_ORIGIN
		LEFT JOIN CODE_VALUES_MASTER as cv13 ON cv13.CV_GROUP = 'LK_SURVEY_TYPE' and cv13.CV_CODE = LK_SURVEY_TYPE
	
		LEFT JOIN CODE_VALUES_MASTER as cv14 ON cv14.CV_GROUP = ('LK_EQUIPTYPE_' || trim(LK_LEAK_ORIGIN)) and cv14.CV_CODE = LK_EQUIPMENT_TYPE
		LEFT JOIN CODE_VALUES_MASTER as cv15 ON cv15.CV_GROUP = 'LK_THREAT' and cv15.CV_CODE = LK_THREAT
		LEFT JOIN CODE_VALUES_MASTER as cv16 ON cv16.CV_GROUP = ('LK_SUB_THREAT_' || trim(LK_THREAT)) and cv16.CV_CODE = LK_SUB_THREAT
		LEFT JOIN CODE_VALUES_MASTER as cv17 ON cv17.CV_GROUP = 'LK_REPAIRED_METHOD' and cv17.CV_CODE = LK_REPAIRED_METHOD
		LEFT JOIN CODE_VALUES_MASTER as cv18 ON cv18.CV_GROUP = 'LK_REPAIRED_EQUIPMENT' and cv18.CV_CODE = LK_REPAIRED_EQUIPMENT
WO_JOINS;
}