<?php 
require_once '../view/userGroupListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Group_User_Xref.php';
require_once '../model/Sec_Profiles.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('SEC', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$ugx = new Group_User_Xref($conn);
$select = new VGS_DB_Select(); 
$screenData = $_REQUEST;
$filter = new VGS_Search_Filter_Group();
$secProf = new Sec_Profiles($conn);

$ugx->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $ugx->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$ugx->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($ugx->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	
	$secProfRec = $secProf->retrieveByID($row['UG_GROUP_ID']);
	$row['group_name'] = $secProfRec['PRF_DESCRIPTION'];
	
	$secProfRec = $secProf->retrieveByID($row['UG_USER_ID']);
	$row['user_name'] = $secProfRec['PRF_DESCRIPTION'];
	
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');
$nav->addIconButton('Add User to Group', 'userGroupEditCtrl.php?mode=create',
							VGS_NavButton::ADD_ICON);

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
