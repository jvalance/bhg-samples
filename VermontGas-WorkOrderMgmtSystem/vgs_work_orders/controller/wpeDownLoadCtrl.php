<?php
require_once '../view/layout.php';
require_once '../model/Workorder_Master.php';
require_once '../model/Wo_Pipe_Exposure.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../forms/VGS_Search_Filter_Group.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$wpe = new WO_Pipe_Exposure($conn);
$select = new VGS_DB_Select(); 
$csv = new VGS_CSV_Builder($conn);
$filter = new VGS_Search_Filter_Group();

$wpe->getWPESearchSelect($_REQUEST, $select, $filter);
// pre_dump($select->toString());
// pre_dump($select->parms);
 
$select->from = 'WO_Pipe_Exposure';
$select->columns = <<<WO_COLS
	WPE_WO_NUM as "WO Number",
	WO_TYPE as "WO Type",
	ifnull(cv1.CV_VALUE, '** WO Rec not found **')  as "WO Type Description",
	WO_STATUS as "WO Status",  
	cv2.CV_VALUE as "Status Description", 
	WO_DESCRIPTION as "WO Description",
	WO_TAX_MUNICIPALITY AS "Tax Muni", 
	cv3.CV_VALUE as "Town Description",
	WO_PIPE_TYPE as "Inst. Pipe Type", 
	PT_DESCRIPTION as "Inst. Pipe Description", 
	cv15.CV_VALUE  as "Inst. Pipe Material",           	                    	                    
	case when PT_DIAMETER = 0 then '' else trim(char(PT_DIAMETER)) end as "Inst. Pipe Diameter",         	                    	                    
	cv16.CV_VALUE as "Inst. Pipe Category",
	char(fnFmtNullDate(WO_DATE_COMPLETED)) as "Date Completed", 
	WPE_EXPOSURE_DATE as "Exposure Date",
	
	cv4.CV_VALUE as "Exp. Pipe Composition",
	case when WPE_PIPE_SIZE = 0 then '' else trim(char(dec(WPE_PIPE_SIZE,9,2))) end as "Exp. Pipe Diameter",
	cv5.CV_VALUE  as "Exp. Pipe Coating",
	cv6.CV_VALUE  as "Exp. Coating Condition",
	cv7.CV_VALUE  as "Exp. Pipe Condition",
	cv8.CV_VALUE  as "Exp. Internal Condition",
	cv9.CV_VALUE  as "Designation",
	cv10.CV_VALUE  as "Pressure",

	cv11.CV_VALUE  as "Soil Condition",
	cv12.CV_VALUE  as "Soil Packing",
	cv13.CV_VALUE as "Soil Moisture",
	cv14.CV_VALUE as "Reason",
	
	case when WPE_DEPTH_FEET = 0 then '' else trim(char(WPE_DEPTH_FEET)) end as "Exp. Depth in Feet",
	case when WPE_DEPTH_INCHES = 0 then '' else trim(char(WPE_DEPTH_INCHES)) end as "Exp. Depth in Inches",
	
	case when WPE_CP20_READING = 0 then '' else trim(char(WPE_CP20_READING)) end as "CP20 Reading",
	WPE_CREATE_USER as "Exp. Record Create User",
	date(WPE_CREATE_TIME) as "Exp. Record Create Date",
	WPE_COMMENTS as "General Comments/Notes"
	
WO_COLS;

$select->joins = <<<WO_JOINS
	LEFT JOIN WORKORDER_MASTER on WO_NUM = WPE_WO_NUM
	LEFT JOIN PIPE_TYPE_MASTER as pt ON PT_PIPE_TYPE = WO_PIPE_TYPE  
	
	LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE 
	LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS  
	LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'TOWN' and cv3.CV_CODE = WO_TAX_MUNICIPALITY  

	LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'WPE_PIPE_COMPOSITION' and cv4.CV_CODE = WPE_PIPE_COMPOSITION
	LEFT JOIN CODE_VALUES_MASTER as cv5 ON cv5.CV_GROUP = 'PIPE_COATING' and cv5.CV_CODE = WPE_PIPE_COATING
	LEFT JOIN CODE_VALUES_MASTER as cv6 ON cv6.CV_GROUP = 'WPE_COATCOND' and cv6.CV_CODE = WPE_COATING_CONDITION
	LEFT JOIN CODE_VALUES_MASTER as cv7 ON cv7.CV_GROUP = 'PIPE_CONDITION' and cv7.CV_CODE = WPE_PIPE_CONDITION
	LEFT JOIN CODE_VALUES_MASTER as cv8 ON cv8.CV_GROUP = 'WPE_INTCOND' and cv8.CV_CODE = WPE_INTERNAL_CONDITION
	LEFT JOIN CODE_VALUES_MASTER as cv9 ON cv9.CV_GROUP = 'WPE_DESIGNATION' and cv9.CV_CODE = WPE_DESIGNATION
	LEFT JOIN CODE_VALUES_MASTER as cv10 ON cv10.CV_GROUP = 'WPE_PRESSURE' and cv10.CV_CODE = WPE_PRESSURE
	LEFT JOIN CODE_VALUES_MASTER as cv11 ON cv11.CV_GROUP = 'SOIL_CONDITION' and cv11.CV_CODE = WO_SOIL_CONDITION
	LEFT JOIN CODE_VALUES_MASTER as cv12 ON cv12.CV_GROUP = 'SOIL_PACKING' and cv12.CV_CODE = WO_SOIL_PACKING
	LEFT JOIN CODE_VALUES_MASTER as cv13 ON cv13.CV_GROUP = 'SOIL_MOISTURE' and cv13.CV_CODE = WO_SOIL_MOISTURE
	LEFT JOIN CODE_VALUES_MASTER as cv14 ON cv14.CV_GROUP = 'WPE_REASON' and cv14.CV_CODE = WPE_REASON

	LEFT JOIN CODE_VALUES_MASTER as cv15 ON cv15.CV_GROUP = 'PT_MATERIAL' and cv15.CV_CODE = PT_MATERIAL
	LEFT JOIN CODE_VALUES_MASTER as cv16 ON cv16.CV_GROUP = 'PT_CATEGORY' and cv16.CV_CODE = PT_CATEGORY
	
WO_JOINS;
 
$csv->download_CSV($select, "pipe_exposure");
