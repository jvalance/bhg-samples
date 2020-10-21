<?php 
require_once '../view/pfListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Project_Master.php';
require_once '../model/Pipe_Type_Master.php';
require_once '../model/Project_Pipe_Ftg.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('PROJ', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$prj = new Project_Master($conn);
$ppf = new Project_Pipe_Ftg($conn);
$ptObj = new Pipe_Type_Master($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$filter = new VGS_Search_Filter_Group();

$screenData = $_REQUEST;
$projNum = $screenData['filter_PF_PRJ_NUM'];
$estYear = $screenData['filter_PF_EST_YEAR'];

$filter->addFilter('PF_PRJ_NUM', 'Project#');
$filter->setInputSize('PF_PRJ_NUM', 5);
$filter->setReadOnly('PF_PRJ_NUM');

$filter->addFilter('PF_EST_YEAR', 'Year');
$filter->setInputSize('PF_EST_YEAR', 5);
$filter->setReadOnly('PF_EST_YEAR');

$filter->addFilter('PRJ_DESCRIPTION', 'Description', 'LIKE');
$filter->setInputSize('PRJ_DESCRIPTION', 45);
$filter->setSpecialWhere('PRJ_DESCRIPTION');
$filter->setReadOnly('PRJ_DESCRIPTION');
$screenData['filter_PRJ_DESCRIPTION'] = $prj->getProjectDescription($projNum);

$select->from = $ppf->tableName;
$select->order = 'PF_PRJ_NUM, PF_EST_YEAR, PF_PIPE_TYPE';

$filter->renderWhere($screenData, $select);

$rowCount = $ppf->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$ppf->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($ppf->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	$row['proj_desc'] = $prj->getProjectDescription($row['PF_PRJ_NUM']);
	$pipeDesc = $ptObj->getPipeTypeDescription($row['PF_PIPE_TYPE']);
	$row['pipe_desc'] = $pipeDesc;
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$popup = (bool) $_REQUEST['popup']; 
$nav = new VGS_Navigator('list', $popup);
$url = "pfEditCtrl.php?mode=create&PF_PRJ_NUM=$projNum&PF_EST_YEAR=$estYear";//&popup=$popup";
$nav->addIconButton('Add Pipe Footage Est.', $url, VGS_NavButton::ADD_ICON);

$screenData['popup'] = $popup;
showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
