<?php
require_once '../view/svServicePremiseXrefView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Premise.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/Services.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('SVC', 'UPDATE');

$screenData = $_REQUEST;
$svID = trim($screenData['filter_SV_SERVICE_ID']);
if (!isset($svID) || $svID == '' || $svID == '0') {
	die('Service ID is required.');
} 

$conn = VGS_DB_Conn_Singleton::getInstance();
$service = new Services($conn);
$premise = new Premise($conn); 
$select = new VGS_DB_Select(); 
$filter = new VGS_Search_Filter_Group();

// 	// Test bad service ID.
// 	 $svID = "9999999";

$screenData['svRec'] = $service->retrieveByID($svID);
if ( !is_array( $screenData['svRec']) ) {
	die ("Service ID {$svID} is not valid.");
}

// pre_dump($screenData);

$cvm = new Code_Values_Master($conn);

$screenData['svRec']['SV_TOWN_NAME'] = $cvm->getCodeValue('TOWN', $screenData['svRec']['SV_CITY']);
$screenData['svRec']['SV_SVC_STATUS_DESC'] = $cvm->getCodeValue('SVC_STATUS', $screenData['svRec']['SV_SVC_STATUS']);

if ($_SERVER['REQUEST_METHOD'] == 'GET') { 
	initializeScreen($screenData);
}

if ($screenData['update_action'] == 'save') {
	doSave($svID);
}

// Set URL to return to  
if (!isset($screenData['return_point'])) { 
	$screenData['return_point'] = $_SESSION['previousPage'];
}

$premise->buildFilteredSelect_SvcXref($screenData, $select, $filter);

$rowCount = $premise->getRowCount($select);
//pre_dump("Row count = $rowCount");

$paginator = new VGS_Paginator($rowCount, $screenData['pageToView'], 40);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

//pre_dump($select->toString());
//ary_dump($select->parms);

$dbRes = $premise->execScrollableListQuery($select);
//pre_dump("db2 result of exec = $dbRes");

$row_count = 0;
while ( $row = db2_fetch_assoc( $premise->stmt, $rowNumber++ )) {
//	ary_dump($row);
	
	$row['rowNum'] = $rowNumber;
//	$row['completionDate'] = VGS_Form::convertDateFormat($row['SLSIDT'], 'Ymd', 'M d, Y');
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}
// pre_dump($screenData);
// exit;

$nav = new VGS_Navigator();

if ( $screenData['popup'] == true ) {
	$nav->addIconButton('Close Window', "self.close();", VGS_NavButton::CLOSE_ICON, 'js');
} else {
	$nav->addIconButton('Services List/Search', 
							"svServiceListCtrl.php?filtSts=restore",
							VGS_NavButton::SEARCH_ICON);
}
$nav->addIconButton('Save Changes', "doSave()", VGS_NavButton::SAVE_ICON, 'js');
$nav->addIconButton('Cancel Changes', "doCancel()", VGS_NavButton::CANCEL_ICON, 'js');

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);

//-----------------------------------------------------------------------
function doSave($svID) {
	// Array $link_prems will hold the list of Premise#s to be linked to the current Service. 
	// These are passed as an array on the request in the format: 
	// svServicePremiseXrefCtrl.php?link_pr[12345]=12345&link_pr[32145]=32145&link_pr[61138]=61138
	
	$service = new Services();
	
	//	Test code for capacity to catch attempts to link non-existing services or premises
			// $premNo = "9999999"; //bad prem no
			// $premNo = "54577";   //good prem no
			// $svID = "88888888";  //bad sv ID
			// $svID = "20";        //good sv ID	
			
			// $service->linkPremiseToService( $premNo, $svID );
	
	
	
	$link_prems = $_REQUEST['link_pr'];
	foreach ($link_prems as $premNo) {
		$service->linkPremiseToService( $premNo, $svID );
	}
	
	// This handles the list of Premises to be UN-linked from the Service, if any.
	$unlink_prems = $_REQUEST['unlink_pr'];
	foreach ($unlink_prems as $premNo) {
		$service->unLinkPremiseFromService( $premNo, $svID );
	}

}
 
//-----------------------------------------------------------------------
function initializeScreen( array &$screenData ) {
	$svRec = $screenData['svRec'];
}
