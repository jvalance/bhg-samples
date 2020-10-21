<?php 
require_once '../view/cgListView.php';
require_once '../forms/VGS_Form.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/Code_Groups.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Navigator.php';
require_once '../forms/VGS_Search_Filter_Group.php';

$sec = new Security();
$sec->checkPermissionByCategory('DD', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$select = new VGS_DB_Select(); 
$cg = new Code_Groups($conn);
$screenData = $_REQUEST;
$statusCodes = Code_Groups::$statusCodes;
//('' => '*All', 'ACT' => 'Active', 'INA' => 'Inactive');

$filter = new VGS_Search_Filter_Group();
$cg->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $cg->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$cg->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc( $cg->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	$row['status_desc'] = $statusCodes[$row['CG_STATUS']];
	$ts = VGS_Form::getTimeStampOutputFormat($row['CG_CHANGE_TIME']);
	$row['last_changed'] = $ts;
	$screenData['rows'][] = $row;
	
	if (++$row_count > $paginator->getPageSize()) break;
}
$screenData['status_codes'] = $statusCodes;

$nav = new VGS_Navigator('list');
$nav->addIconButton('Create Drop Down List', 
							'cgEditCtrl.php?mode=create',
							VGS_NavButton::ADD_ICON);

showScreen($screenData, $paginator, $filter, $nav);

