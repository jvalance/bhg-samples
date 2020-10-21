<?php 
require_once '../view/svServiceListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Services.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('SVC', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$svc = new Services();
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;

$filter = new VGS_Search_Filter_Group();

// Special wheres for batch edit confirmation screen
if ($screenData['mode'] == 'batchConfirm') {
	foreach ( $_SESSION['SERVICE_SELECTED_IDS'] as $svID ) {
		$select->orWhere('SV_SERVICE_ID=?', trim($svID));
	}
} else {
	// Reset batch edit session variables
	// (in case batch edit session was abandoned while in progress)
	$svc->unsetBatchSession();
}

$svc->getSearchSelect($screenData, $select, $filter);
// pre_dump($select->toString());
// pre_dump($select->parms);

$rowCount = $svc->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView'], 100);
$paginator->activate();
$rowNumber = $paginator->getStartRow();
$svc->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc( $svc->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	$row['town'] = $cvm->getCodeValue('TOWN', $row['SV_CITY']);
	$row['date_completed'] = VGS_Form::fixDateOutput($row['SV_DATE_COMPLETED'], true);
	$row['address'] = "{$row['SV_HOUSE']} {$row['SV_STREET']}, {$row['TOWN_NAME']}";
	$row['PDFpath'] = $svc->getPDFFullPath($row['SV_STREET'], $row['TOWN_NAME'], $row['SV_SCAN_FILE_NAME']);
	$svcXref = new Services($conn);
	$row['SPX_PREMISE_NUMS'] = $svcXref->getXrefPremiseNums($row['SV_SERVICE_ID']);
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$popup = (bool) $_REQUEST['popup'];
$nav = new VGS_Navigator('list', $popup);

$nav->addNavButton('Batch Edit', "javascript:doBatchEdit();", 'js');

$nav->addIconButton('Create Service Rec.',
		"svServiceEditCtrl.php?mode=create&WPE_WO_NUM=$woNum&popup=$popup",
		VGS_NavButton::ADD_ICON);

$nav->addIconButton('Download', 'doDownload();', VGS_NavButton::DOWNLOAD_ICON, 'js');


showScreen($screenData, $paginator, $filter, $nav);
