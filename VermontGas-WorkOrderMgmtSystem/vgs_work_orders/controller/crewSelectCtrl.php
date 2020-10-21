<?php 
require_once '../view/crewSelectView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Crew.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$crew = new Crew($conn);
$selectEmployee = new VGS_DB_Select();
$selectSubContractor = new VGS_DB_Select();

$screenData = $_REQUEST;
$filter = new VGS_Search_Filter_Group();

$crew->buildFilteredSelect($screenData, $selectEmployee, $selectSubContractor, $filter);

// TODO: DUMMYZEND71 - Remove temporary code
// Build complete SQL string with UNION of two selects
$query = $selectEmployee->toString(false) 
			. ' UNION ALL ' . 
			$selectSubContractor->toString(false) . ' order by type, id';
// pre_dump($query);
// Merge bound parms for union query
$qparms = array_merge($selectEmployee->parms, $selectSubContractor->parms);

$rowCountEmp = $crew->getRowCount($selectEmployee);
$rowCountSub = $crew->getRowCount($selectSubContractor);
$rowCount = $rowCountEmp + $rowCountSub;

$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$crew->execScrollableListQuery_String($query, $qparms);
$row_count = 0;
while ( $row = db2_fetch_assoc($crew->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
