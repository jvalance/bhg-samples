<?php 
require_once '../view/authProfListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Auth_Profile_Xref.php';
require_once '../model/Authorities.php';
require_once '../model/Sec_Profiles.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('SEC', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$apx = new Auth_Profile_Xref($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;
$filter = new VGS_Search_Filter_Group();
$auth = new Authorities($conn);
$secProf = new Sec_Profiles($conn);

$apx->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $apx->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$apx->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($apx->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	
	// Retrieve descriptions for codes
	$authRec = $auth->retrieveByID($row['AP_AUTH_ID']);
	$row['AD_AUTH_NAME'] = $authRec['AD_AUTH_NAME']; 
	$secProfRec = $secProf->retrieveByID(trim($row['AP_PROFILE_ID']));
	$row['PRF_PROFILE_TYPE'] = $secProfRec['PRF_PROFILE_TYPE']; 
	$row['GP_DESCRIPTION'] = $secProfRec['PRF_DESCRIPTION']; 
	$row['permission_desc'] = $cvm->getCodeValue('AP_PERMISSION', $row['AP_PERMISSION']);
	
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');
$nav->addIconButton('Create Auth/Profile', 
							'authProfEditCtrl.php?mode=create',
							VGS_NavButton::ADD_ICON);

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
