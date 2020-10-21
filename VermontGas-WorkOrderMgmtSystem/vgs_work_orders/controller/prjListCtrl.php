<?php 
require_once '../view/prjListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Project_Master.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('PROJ', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$prj = new Project_Master($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;
$filter = new VGS_Search_Filter_Group();

$prj->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $prj->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$prj->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($prj->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
// 	ary_dump($row);
	// Retrieve descriptions for codes
	$row['town_desc'] = $cvm->getCodeValue('TOWN', $row['PRJ_MUNICIPALITY_CODE']);
	$row['status_desc'] = $cvm->getCodeValue('PRJ_STATUS', $row['PRJ_STATUS']);
	$row['capexp_desc'] = $cvm->getCodeValue('PT_CAP_EXP', $row['PRJ_CAP_EXP_CODE']);
	$row['wo_count'] = Project_Master::getWOCountForProject($conn, $row['PRJ_NUM']);
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');
$nav->addIconButton('Download', 'doDownload();', VGS_NavButton::DOWNLOAD_ICON, 'js');
$nav->addIconButton('Create Project', "prjEditCtrl.php?mode=create", VGS_NavButton::ADD_ICON);

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
