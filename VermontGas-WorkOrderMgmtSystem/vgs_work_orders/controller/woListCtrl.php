<?php 
require_once '../view/woListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Workorder_Master.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';
require_once '../forms/WOCancellationsForm.php';

// $startTime = new DateTime();

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$wo = new Workorder_Master($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;

$filter = new VGS_Search_Filter_Group();

$wo->buildFilteredSelect($screenData, $select, $filter);
// pre_dump($select->toString());

$rowCount = $wo->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$blnUserAllowedUpdComplete = $sec->checkAuthoritiesPermission(array('WO_UPD_COMPLETE'), false);
$blnUserAllowedAcctgCloOvr = $sec->checkAuthoritiesPermission(array('WO_ACCT_CLO_OVR'), false);
$blnUserAllowedUpdate = $sec->checkPermissionByCategory('WO', 'UPDATE', false);

$wo->execScrollableListQuery($select);
$row_count = 0;

while ( $row = db2_fetch_assoc( $wo->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	// Determine if WO can be updated
	$blnWO_CompClo = in_array($row['WO_STATUS'], Workorder_Master::$wo_sts_comp_closed);
	$blnWO_Comp = $row['WO_STATUS'] == Workorder_Master::WO_STATUS_COMPLETED;
	$blnWO_Cancel = in_array($row['WO_STATUS'], Workorder_Master::$wo_sts_cancel);
	$row['allow_wo_update'] = 
		$blnUserAllowedUpdate && 
		!$blnWO_Cancel && 
		(!$blnWO_CompClo 
				|| ($blnWO_CompClo && $blnUserAllowedUpdComplete)
				|| ($blnWO_Comp && $blnUserAllowedAcctgCloOvr)
		);
	 
// 	$woCnl = new WOCancellationsForm($conn, $row['WO_NUM']);
// 	$row['isDollarsApplied'] = $woCnl->isDollarsApplied;
// 	$row['isTransferDollarsRequired'] = $woCnl->isTransferDollarsRequired;
	
	if ($row['DOLLARS_APPLIED'] > '0') {
		// If w/o has dollars applied, show a dollar sign next to status.
		// And if a transfer w/o# is required to cancel, show two dollar signs.
		$dollarSymbol = ($row['TRANSFER_DOLLARS'] > '0') ? '$$' : '$';
		$row['status_desc'] .= " <span style='color:green; font-weight:normal'>$dollarSymbol</span>";
	} else {
		$row['status_desc'] = '';
	}
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
} 

$nav = new VGS_Navigator('list');

$nav->addIconButton('Download', 'doDownload();', VGS_NavButton::DOWNLOAD_ICON, 'js');
$nav->addIconButton('Print', 'doPrint();', VGS_NavButton::PRINT_ICON, 'js');
$nav->addIconButton('Create W/O', 'woCreateCtrl.php', VGS_NavButton::ADD_ICON);


showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);

// $now = new DateTime();
// echo "<hr>Time elapsed:";
// pre_dump($now->diff($startTime, true));
