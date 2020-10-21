<?php 
require_once '../view/authListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Authorities.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('SEC', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$auth = new Authorities($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;
$filter = new VGS_Search_Filter_Group();

$auth->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $auth->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$auth->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($auth->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	
	// Retrieve descriptions for codes
	$row['area_desc'] = $cvm->getCodeValue('AD_FUNCTIONAL_AREA', $row['AD_FUNCTIONAL_AREA']);
	
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');
$nav->addIconButton('Create Authority Definition', 
							'authEditCtrl.php?mode=create',
							VGS_NavButton::ADD_ICON);

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
