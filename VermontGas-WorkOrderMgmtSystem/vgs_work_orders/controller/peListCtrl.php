<?php 
require_once '../view/peListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Project_Master.php';
require_once '../model/Project_Estimates.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('PROJ', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$prj = new Project_Master($conn);
$pe = new Project_Estimates($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$filter = new VGS_Search_Filter_Group();

$screenData = $_REQUEST;
$projNum = $screenData['filter_PE_PRJ_NUM'];

$filter->addFilter('PE_PRJ_NUM', 'Project#');
$filter->setInputSize('PE_PRJ_NUM', 5);
$filter->setReadOnly('PE_PRJ_NUM');

$filter->addFilter('PRJ_DESCRIPTION', 'Description', 'LIKE');
$filter->setInputSize('PRJ_DESCRIPTION', 45);
$filter->setSpecialWhere('PRJ_DESCRIPTION');
$filter->setReadOnly('PRJ_DESCRIPTION');

$screenData['filter_PRJ_DESCRIPTION'] = $prj->getProjectDescription($projNum);

$select->from = $pe->tableName;
$select->order = 'PE_PRJ_NUM, PE_EST_YEAR';

$filter->renderWhere($screenData, $select);

$rowCount = $pe->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$pe->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($pe->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$popup = (bool) $_REQUEST['popup']; 
$nav = new VGS_Navigator('list', $popup);
$nav->addIconButton('Add Year Estimates', 
						"peEditCtrl.php?mode=create&PE_PRJ_NUM=$projNum&popup=$popup",
						VGS_NavButton::ADD_ICON);

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
