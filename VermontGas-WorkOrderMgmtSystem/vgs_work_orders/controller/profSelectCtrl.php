<?php 
require_once '../view/profSelectView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Sec_Profiles.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('SEC', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$prof = new Sec_Profiles($conn);
$select = new VGS_DB_Select(); 
//$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;
$filter = new VGS_Search_Filter_Group();

$prof->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $prof->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$prof->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($prof->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	
	// Retrieve descriptions for codes
//	$row['area_desc'] = $cvm->getCodeValue('AD_FUNCTIONAL_AREA', $row['AD_FUNCTIONAL_AREA']);
	
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');
$nav->addIconButton('Create Profile', 
							'profEditCtrl.php?mode=create',
							VGS_NavButton::ADD_ICON);

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
