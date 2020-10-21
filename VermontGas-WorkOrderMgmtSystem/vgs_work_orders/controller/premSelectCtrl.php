<?php 
require_once '../view/premSelectView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Premise.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$prem = new Premise($conn);
$select = new VGS_DB_Select(); 
//$cvm = new Code_Values_Master($conn);
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
	$row['UPPRM'] = trim($row['UPPRM']);
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
