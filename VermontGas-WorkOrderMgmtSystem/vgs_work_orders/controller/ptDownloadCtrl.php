<?php
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_CSV_Builder.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../model/Pipe_Type_Master.php';

$sec = new Security();
$sec->checkPermissionByCategory('PIPE', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$pt = new Pipe_Type_Master($conn);
$select = new VGS_DB_Select(); 
$csv = new VGS_CSV_Builder($conn);
$filter = new VGS_Search_Filter_Group();

$select->columns = <<<PT_COLS
				PT_PIPE_TYPE as "Pipe Type Code",
				PT_DESCRIPTION "Description",
				cv1.CV_VALUE as "Material", 
				cv2.CV_VALUE as "Category", 
				cv3.CV_VALUE as "Coating", 
				cv4.CV_VALUE as "Cap/Exp", 
				PT_DIAMETER as "Diameter",
				PT_ACCTG_UNIT_COST || '-' || 
					digits(PT_GL_ACCT_COST) || '-' ||
					digits(PT_SUB_ACCT_COST) 
				as "G/L Cost",
				PT_ACCTG_UNIT_CLOSE || '-' ||
					digits(PT_GL_ACCT_CLOSE) || '-' ||
					digits(PT_SUB_ACCT_CLOSE) 
				as "G/L Close"
PT_COLS;

$select->joins = <<<PT_JOINS
	LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'PT_MATERIAL' and cv1.CV_CODE = PT_MATERIAL 
	LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'PT_CATEGORY' and cv2.CV_CODE = PT_CATEGORY  
	LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'PT_COATING' and cv3.CV_CODE = PT_COATING  
	LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'PT_CAP_EXP' and cv4.CV_CODE = PT_CAP_EXP  
PT_JOINS;

$pt->buildFilteredSelect($_REQUEST, $select, $filter);
//pre_dump($select);

$csv->download_CSV($select, "pipe_type_download");

db2_close($conn->conn);
