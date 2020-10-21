<?php
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../model/Project_Master.php';

$sec = new Security();
$sec->checkPermissionByCategory('PROJ', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$proj = new Project_Master($conn);
$select = new VGS_DB_Select(); 
$csv = new VGS_CSV_Builder($conn);
$filter = new VGS_Search_Filter_Group();

$select->columns = <<<DOWNLOAD_COLS
	PRJ_NUM	as "Project #",
	PRJ_DESCRIPTION	as "Description",
	cv1.CV_VALUE as "Proj Status",
	PRJ_CONTACT_PERSON as "Contact",
	PRJ_FEASABILITY_NUM as "Feasibility #",
	cv2.CV_VALUE as "Municipality",
	PRJ_ZONE as "Zone",
	cv3.CV_VALUE as "Cap/Exp",
	char(fnFmtNullDate(PRJ_FEASABILITY_DATE)) as "Feas. Date",
	char(fnFmtNullDate(PRJ_PROJECT_DATE)) as "Project Date",
	char(fnFmtNullDate(PRJ_COMPLETION_DATE)) as "Completion Date",
	PRJ_SALES_REP as "Sales Rep",
	PRJ_DEVELOPER as "Developer",
	PRJ_DEV_PHONE as "Devel. Phone",
	PRJ_ROW_REQUIRED as "R.O.W. Required",
	PRJ_LTR_OF_CREDIT_REQD as "LOC Req"
DOWNLOAD_COLS;

$select->joins = <<<DOWNLOAD_JOINS
	LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'PRJ_STATUS' and cv1.CV_CODE = PRJ_STATUS 
	LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'TOWN' and cv2.CV_CODE = PRJ_MUNICIPALITY_CODE  
	LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'PT_CAP_EXP' and cv3.CV_CODE = PRJ_CAP_EXP_CODE  
DOWNLOAD_JOINS;

$select->order = 'PRJ_NUM DESC';

$proj->buildFilteredSelect($_REQUEST, $select, $filter);
//pre_dump($select);

$csv->download_CSV($select, "project_download");

db2_close($conn->conn);
