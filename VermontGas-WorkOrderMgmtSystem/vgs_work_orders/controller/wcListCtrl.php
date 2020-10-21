<?php 
require_once '../view/wcListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/WO_Cleanup.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$wc = new WO_Cleanup($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;

// Redirect to create/edit screen if less than 2 records found for w/o.
//if ($screenData['auto_edit'] == true) {
//	checkForExistingCleanup($wc, &$screenData ) ;
//}
	
$filter = new VGS_Search_Filter_Group();
$wc->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $wc->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$wc->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc( $wc->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
//	$row['wo_type_desc'] = $cvm->getCodeValue('WO_TYPE', $row['WO_TYPE']);
//	$row['status_desc'] = $cvm->getCodeValue('WO_STATUS', $row['WO_STATUS']);
//	$row['town'] = $cvm->getCodeValue('TOWN', $row['WPE_TAX_MUNICIPALITY']);
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$popup = (bool) $_REQUEST['popup'];
$nav = new VGS_Navigator('list', $popup);
$nav->addIconButton('Download', 'doDownload();', VGS_NavButton::DOWNLOAD_ICON, 'js');
$nav->addIconButton('Print', 'doPrintReport();', VGS_NavButton::PRINT_ICON, 'js');
$woNum = trim($screenData['filter_WC_WONUM']);
if ($woNum != '') {
	$nav->addIconButton('Create Cleanup', 
								"wcEditCtrl.php?mode=create&WC_WONUM=$woNum&popup=$popup",
								VGS_NavButton::ADD_ICON);
}

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);

//-----------------------------------------------------------------------------------
function checkForExistingCleanup( 
	WO_Cleanup $wc, 
	array &$screenData ) 
{
	$woNum = trim($screenData['filter_WC_WONUM']);
	$wcWorkOrders = $wc->getCleanupsForWO($woNum);
	
	if ($wcWorkOrders['count'] == 0) {
		// If no cleanups found for W/O, go to create cleanup screen
		$redirectPage = "wcEditCtrl.php?mode=create&WC_WONUM=$woNum";
		header("Location: $redirectPage");
		exit;
	}
	// This didn't work well: After creating/editing a record it would loop back 
	// to the edit screen, which made it look like the update didn't take place. 
//	if ($wcWorkOrders['count'] == 1) {
//		// If only one cleanup found for W/O, go to edit cleanup screen
//		$wcWoNum = $wcWorkOrders['wonums'][0]['WC_WONUM'];
//		$wcCleanupNum = $wcWorkOrders['wonums'][0]['WC_CLEANUP_NUM'];
//		$redirectPage = "wcEditCtrl.php?mode=update&WC_WONUM=$wcWoNum&WC_CLEANUP_NUM=$wcCleanupNum";
//		header("Location: $redirectPage");
//		exit;
//	}
	if ($wcWorkOrders['count'] > 0) {
		// If more than one cleanup found for W/O, show the search/list screen.
		return;
	}
	
} 