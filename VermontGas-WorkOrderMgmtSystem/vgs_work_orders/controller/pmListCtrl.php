<?php 
require_once '../view/pmListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Project_Master.php';
require_once '../model/Project_MCF_Rates.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('PROJ', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$prj = new Project_Master($conn);
$pmr = new Project_MCF_Rates($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$filter = new VGS_Search_Filter_Group();

$screenData = $_REQUEST;
$projNum = $screenData['filter_PM_PRJ_NUM'];
$estYear = $screenData['filter_PM_EST_YEAR'];

$filter->addFilter('PM_PRJ_NUM', 'Project#');
$filter->setInputSize('PM_PRJ_NUM', 5);
$filter->setReadOnly('PM_PRJ_NUM');

$filter->addFilter('PM_EST_YEAR', 'Year');
$filter->setInputSize('PM_EST_YEAR', 5);
$filter->setReadOnly('PM_EST_YEAR');

$filter->addFilter('PRJ_DESCRIPTION', 'Description', 'LIKE');
$filter->setInputSize('PRJ_DESCRIPTION', 45);
$filter->setSpecialWhere('PRJ_DESCRIPTION');
$filter->setReadOnly('PRJ_DESCRIPTION');

$screenData['filter_PRJ_DESCRIPTION'] = $prj->getProjectDescription($projNum);

$select->from = $pmr->tableName;
$select->order = 'PM_PRJ_NUM, PM_EST_YEAR';

$filter->renderWhere($screenData, $select);

$rowCount = $pmr->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$pmr->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($pmr->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	$row['proj_desc'] = $prj->getProjectDescription($row['PM_PRJ_NUM']);
	$row['rate_desc'] = $cvm->getCodeValue('RATECLASS', $row['PM_RATE_CLASS']);
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$popup = (bool) $_REQUEST['popup']; 
$nav = new VGS_Navigator('list', $popup);
$url = "pmEditCtrl.php?mode=create&PM_PRJ_NUM=$projNum&PM_EST_YEAR=$estYear";//&popup=$popup";
$nav->addIconButton('Add MCF Estimate', $url, VGS_NavButton::ADD_ICON);

$screenData['popup'] = $popup;
showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
