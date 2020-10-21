<?php

require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../model/Services.php';


$sec = new Security();
$sec->checkPermissionByCategory('SVC', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$service = new Services($conn);
$select = new VGS_DB_Select();
$csv = new VGS_CSV_Builder($conn);
$filter = new VGS_Search_Filter_Group();

$service->getSearchSelect($_REQUEST, $select, $filter);

$select->columns = <<<SV_COLS
	SV_ENTRY_FORMAT as "Entry Format",
	SV_SERVICE_ID "Service ID",
	SV_WO_NO as "W/O#",
	cv02.CV_VALUE as "Service Status",
	cv03.CV_VALUE as "Update Status",
	SV_PREMISE_NO as "Premise #",
	SV_MAP_NO as "Map #",
	SV_NAME as "Service Name",
	SV_HOUSE as "House No",
	SV_STREET as "Street",
	cv01.CV_VALUE as "City",
	SV_STATE as "State",
	SV_LOT_NO as "Lot No",
	SV_PUBLIC_BLDG as "Public Building?",
	SV_ROW as "Right of Way?",
	SV_FILE_NO as "ROW Easement File#",
	cv04.CV_VALUE as "Material",
	cv05.CV_VALUE as "Size",
	cv06.CV_VALUE as "Coating",
	SV_DEPTH_FT as "Depth-ft",
	SV_DEPTH_IN as "Depth-in",
	SV_LENGTH_FT as "Length-ft",
	SV_LENGTH_IN as "Length-in",
	SV_TRACER_WIRE as "Tracer Wire?",
	SV_CAD_WELD_MAIN as "CAD Welded to Main?",
	SV_CURB_STOP as "Curb Stop?",
	cv07.CV_VALUE as "Meter Location",
	cv08.CV_VALUE as "Regulator Location",
	SV_JOINT_TYPE as "Joint Type",
	SV_JOINT_TYPE_OTHER as "Joint Type-other",
	SV_FLOW_LIMITER as "Flow Limiter",
	SV_FLOW_LIMITER_SIZE as "Flow Limiter Size",
	SV_METH_TRENCH as "MOC Trench?",
	SV_METH_HDD as "MOC HDD?",
	SV_METH_HOG as "MOC Hog?",
	SV_METH_PLOWED as "MOC Plowed?",
	SV_METH_OTHER as "MOC Other?",
	SV_DIRECT as "Direct?",
	SV_INSERT as "Insert?",
	SV_TEST_PRESSURE as "Test Pressure",
	SV_TESTED_WITH_MAIN as "Tested with Main?",
	SV_DURATION_HRS as "Test Duration-hrs",
	SV_DURATION_MINS as "Test Duration-min",
	SV_DATE_COMPLETED as "Date Completed",
	SV_INSTALLED_BY as "Installed By",
	SV_INSPECTED_BY as "Inspected By",
	SV_REMARKS as "Remarks",
	cv09.CV_VALUE as "Main Size",
	cv10.CV_VALUE as "Main Type",
	cv11.CV_VALUE as "Main Coating",
	SV_MAIN_DEPTH_FT as "Main Depth-ft",
	SV_MAIN_DEPTH_IN as "Main Depth-in",
	cv12.CV_VALUE as "Main Pressure",
	SV_MAIN_SOIL_TYPE as "Main Soil Type",
	SV_MAIN_SOIL_OTHER as "Main Soil Type-other"
SV_COLS;

$select->order = 'SV_WO_NO DESC, SV_SERVICE_ID';
$select->joins =
	"LEFT JOIN WORKORDER_MASTER wo on SV_WO_NO = WO_NUM
	 LEFT JOIN CODE_VALUES_MASTER as cv01 ON cv01.CV_GROUP = 'TOWN' and cv01.CV_CODE = SV_CITY
	 LEFT JOIN CODE_VALUES_MASTER as cv02 ON cv02.CV_GROUP = 'SVC_STATUS' and cv02.CV_CODE = SV_SVC_STATUS
	 LEFT JOIN CODE_VALUES_MASTER as cv03 ON cv03.CV_GROUP = 'SVC_UPD_STS' and cv03.CV_CODE = SV_UPDATE_STATUS 
	 LEFT JOIN CODE_VALUES_MASTER as cv04 ON cv04.CV_GROUP = 'PIPE_MTRL' and cv04.CV_CODE = SV_MATERIAL
	 LEFT JOIN CODE_VALUES_MASTER as cv05 ON cv05.CV_GROUP = 'PIPE_DIAM' and cv05.CV_CODE = SV_SIZE
	 LEFT JOIN CODE_VALUES_MASTER as cv06 ON cv06.CV_GROUP = 'PIPE_COATING' and cv06.CV_CODE = SV_COATING
	 LEFT JOIN CODE_VALUES_MASTER as cv07 ON cv07.CV_GROUP = 'METERLOC' and cv07.CV_CODE = SV_METER_LOC
	 LEFT JOIN CODE_VALUES_MASTER as cv08 ON cv08.CV_GROUP = 'REGULATOR_LOC' and cv08.CV_CODE = SV_REGULATOR_LOC
	 LEFT JOIN CODE_VALUES_MASTER as cv09 ON cv09.CV_GROUP = 'PIPE_DIAM' and cv09.CV_CODE = SV_MAIN_SIZE
	 LEFT JOIN CODE_VALUES_MASTER as cv10 ON cv10.CV_GROUP = 'PIPE_MTRL' and cv10.CV_CODE = SV_MAIN_TYPE
	 LEFT JOIN CODE_VALUES_MASTER as cv11 ON cv11.CV_GROUP = 'PIPE_COATING' and cv11.CV_CODE = SV_MAIN_COATING
	 LEFT JOIN CODE_VALUES_MASTER as cv12 ON cv12.CV_GROUP = 'PRESSURE' and cv12.CV_CODE = SV_MAIN_PRESSURE
	";


$csv->download_CSV($select, "service_download");

// pre_dump ($select);

// pre_dump ($select->toString(false));

db2_close($conn->conn);
