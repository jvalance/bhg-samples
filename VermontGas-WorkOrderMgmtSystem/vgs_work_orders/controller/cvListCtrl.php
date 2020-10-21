<?php 
require_once '../view/cvListView.php';
require_once '../forms/VGS_Form.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/Code_Groups.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('DD', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$select = new VGS_DB_Select(); 
$screenData = $_REQUEST;

$filter = new VGS_Search_Filter_Group();
$filter->addFilter('CV_GROUP', 'Group');
$filter->addFilter('CV_STATUS', 'Status');
$filter->addFilter('CV_VALUE', 'Value', 'LIKE');
$filter->addFilter('CV_DESCRIPTION', 'Description', 'LIKE');

$filter->setReadOnly('CV_GROUP');
$filter->setInputSize('CV_GROUP', '20');

$cv = new Code_Values_Master($conn);
$statusCodes = Code_Values_Master::$statusCodes;
$filter->setDropDownList('CV_STATUS', $statusCodes);

$cv->getCVSearchSelect($screenData, $select, $filter);

$rowCount = $cv->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$cv->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc( $cv->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	$row['status_desc'] = $statusCodes[$row['CV_STATUS']];
	$ts = VGS_Form::getTimeStampOutputFormat($row['CV_CHANGE_TIME']);
	$row['last_changed'] = $ts;
	$screenData['rows'][] = $row;
	
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');
$nav->addIconButton('Drop Down Lists',  
						"cgListCtrl.php?filtSts=restore",
						VGS_NavButton::SEARCH_ICON);
$nav->addIconButton('Create Drop Down Value', 
						"cvEditCtrl.php?mode=create&CV_GROUP={$screenData['filter_CV_GROUP']}",
						VGS_NavButton::ADD_ICON);

showScreen($screenData, $paginator, $filter, $nav);

