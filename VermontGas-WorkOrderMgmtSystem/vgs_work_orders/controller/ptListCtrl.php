<?php 
require_once '../view/ptListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Pipe_Type_Master.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('PIPE', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$prem = new Pipe_Type_Master($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;
$filter = new VGS_Search_Filter_Group();

$prem->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $prem->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$prem->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($prem->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	
	// Retrieve descriptions for codes
	$row['material_desc'] = $cvm->getCodeValue('PT_MATERIAL', $row['PT_MATERIAL']);
	$row['category_desc'] = $cvm->getCodeValue('PT_CATEGORY', $row['PT_CATEGORY']);
	$row['coating_desc'] = $cvm->getCodeValue('PT_COATING', $row['PT_COATING']);
	$row['capexp_desc'] = $cvm->getCodeValue('PT_CAP_EXP', $row['PT_CAP_EXP']);
	
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');
$nav->addIconButton('Download', 'doDownload();', 
						VGS_NavButton::DOWNLOAD_ICON, 'js');
$nav->addIconButton('Create Pipe Type', 'ptEditCtrl.php?mode=create',
						VGS_NavButton::ADD_ICON);

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
