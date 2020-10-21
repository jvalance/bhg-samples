<?php 
require_once '../view/slxMaintView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/SalesApp.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/workorder_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'UPDATE');

$screenData = $_REQUEST;
$woNum = trim($screenData['filter_WONUM']);
if (!isset($woNum) || $woNum == '' || $woNum == '0') {
	die('W/O Number is required.');
} 

$conn = VGS_DB_Conn_Singleton::getInstance();
$slsapp = new SalesApp($conn);
$objWO = new Workorder_Master($conn); 
$select = new VGS_DB_Select(); 
$filter = new VGS_Search_Filter_Group();

$screenData['woRec'] = $objWO->getWorkorder($woNum);
//pre_dump($screenData['woRec'] );

if ($_SERVER['REQUEST_METHOD'] == 'GET') { 
	initializeScreen($screenData);
}

if ($screenData['update_action'] == 'save') {
	doSave($slsapp, $woNum);
}

// Set URL to return to  
if (!isset($screenData['return_point'])) { 
	$screenData['return_point'] = $_SESSION['previousPage'];
}

$slsapp->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $slsapp->getRowCount($select);
//pre_dump("Row count = $rowCount");

$paginator = new VGS_Paginator($rowCount, $screenData['pageToView'], 40);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

//pre_dump($select->toString());
//ary_dump($select->parms);
$dbRes = $slsapp->execScrollableListQuery($select);
//pre_dump("db2 result of exec = $dbRes");

$row_count = 0;
while ( $row = db2_fetch_assoc( $slsapp->stmt, $rowNumber++ )) {
//	ary_dump($row);
	
	$row['rowNum'] = $rowNumber;
	$row['completionDate'] = VGS_Form::convertDateFormat($row['SLSIDT'], 'Ymd', 'M d, Y');
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}
//pre_dump($screenData['rows']);
//exit;

$nav = new VGS_Navigator();

$nav->addIconButton('Close Window', "self.close();", VGS_NavButton::CLOSE_ICON, 'js');
$nav->addIconButton('Save Changes', "doSave()", VGS_NavButton::SAVE_ICON, 'js');
$nav->addIconButton('Cancel Changes', "doCancel()", VGS_NavButton::CANCEL_ICON, 'js');

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);

//-----------------------------------------------------------------------
function doSave(SalesApp $slsapp, $woNum) {
	// Array $link_apps will hold the list of SlsApp#s to be linked to the current W/O. 
	// These are passed as an array on the request in the format: 
	// slxMaintCtrl.php?link_sa[12345]=12345&link_sa[32145]=32145&link_sa[61138]=61138
	$link_apps = $_REQUEST['link_sa'];
	foreach ($link_apps as $slsAppNo) {
		$slsapp->linkSlsAppToWO( $slsAppNo, $woNum );
	}
	
	// This handles the list of slsapps to be UN-linked from the w/o, if any.
	$unlink_apps = $_REQUEST['unlink_sa'];
	foreach ($unlink_apps as $slsAppNo) {
		$slsapp->unLinkSlsAppFromWO( $slsAppNo, $woNum );
	}
}
 
//-----------------------------------------------------------------------
function initializeScreen( array &$screenData ) {
	$woRec = $screenData['woRec'];
	if (is_array($woRec)) {
		$screenData['filter_WONUM_ONLY'] = '';
		$screenData['filter_FROM_SLSAPP'] = $woRec['WO_SALES_APP_NUM'];
		
		// Initialize address filter with first two "words" of w/o description
//		$addrWords = explode(' ', $woRec['WO_DESCRIPTION']);
//		if (is_array($addrWords) && count($addrWords) >= 2) {
//			$screenData['filter_UPSAD'] = $addrWords[0] . ' ' . $addrWords[1];
//		}
	}
}
