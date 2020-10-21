<?php 
require_once '../view/wpeListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/WO_Pipe_Exposure.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../model/WorkOrder_Master.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$wpe = new WO_Pipe_Exposure($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;

$filter = new VGS_Search_Filter_Group();

$wpe->getWPESearchSelect($screenData, $select, $filter);
// pre_dump($select->toString());
// pre_dump($select->parms);

$rowCount = $wpe->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$wpe->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc( $wpe->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	$row['town'] = $cvm->getCodeValue('TOWN', $row['WO_TAX_MUNICIPALITY']);
	$row['designation'] = $cvm->getCodeValue('WPE_DESIGNATION', $row['WPE_DESIGNATION']);
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$popup = (bool) $_REQUEST['popup'];
$nav = new VGS_Navigator('list', $popup);
if (isset($_REQUEST['filter_WPE_WO_NUM']) && '' != trim($_REQUEST['filter_WPE_WO_NUM'])) {
	$woNum = $_REQUEST['filter_WPE_WO_NUM'];
	$woObj = new Workorder_Master($conn);
	$woRec = $woObj->getWorkorder($woNum);
	if (is_array($woRec)) {
		$nav->addIconButton('Create Pipe Exposure',
				"wpeEditCtrl.php?mode=create&WPE_WO_NUM=$woNum&popup=$popup",
				VGS_NavButton::ADD_ICON);
	} else {
		$screenData['errorMsg'] = "W/O Number $woNum does not exist.";
	}
}
$nav->addIconButton('Download', 'doDownload();', VGS_NavButton::DOWNLOAD_ICON, 'js');

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);

//-----------------------------------------------------------------------------------
function checkForExistingExposure( 
	WO_Pipe_Exposure $wpe, 
	array &$screenData ) 
{
	$woNum = trim($screenData['filter_WPE_WO_NUM']);
	$wpeWorkOrders = $wpe->getPipeExposuresForWO($woNum);
	
	if ($wpeWorkOrders['count'] == 0) {
		// If no Exposures found for W/O, go to create Exposure screen
		$redirectPage = "wpeEditCtrl.php?mode=create&WPE_WO_NUM=$woNum";
		header("Location: $redirectPage");
		exit;
	}
	// This didn't work well: After creating/editing a record it would loop back 
	// to the edit screen, which made it look like the update didn't take place. 
//	if ($wpeWorkOrders['count'] == 1) {
//		// If only one Exposure found for W/O, go to edit Exposure screen
//		$wpeWoNum = $wpeWorkOrders['wonums'][0]['WPE_WO_NUM'];
//		$wpeExposureDate = $wpeWorkOrders['wonums'][0]['WPE_EXPOSURE_DATE'];
//		$redirectPage = "wpeEditCtrl.php?mode=update&WPE_WO_NUM=$wpeWoNum&WPE_EXPOSURE_DATE=$wpeExposureDate";
//		header("Location: $redirectPage");
//		exit;
//	}
	if ($wpeWorkOrders['count'] > 0) {
		// If more than one Exposure found for W/O, show the search/list screen.
		return;
	}
	
} 