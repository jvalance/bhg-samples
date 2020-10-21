<?php 
require_once '../view/wefListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/WO_Electrofusion.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$wef = new WO_Electrofusion($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;

$filter = new VGS_Search_Filter_Group();
$wef->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $wef->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$wef->execScrollableListQuery($select);
$row_count = 0;

while ( $row = db2_fetch_assoc( $wef->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	if ($row['WEF_FUSION_DATE'] != '0001-01-01') {
		$row['fusionDate'] = date('M d, Y', strtotime($row['WEF_FUSION_DATE']));
	}
	
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$popup = (bool) $_REQUEST['popup'];
$nav = new VGS_Navigator('list', $popup);

if (isset($_REQUEST['filter_WEF_WO_NUM'])
&& trim($_REQUEST['filter_WEF_WO_NUM']) != '') {
	$woNum = $_REQUEST['filter_WEF_WO_NUM'];
	$nav->addPopupButton(
			'Add Electrofusion to WO ' . $woNum, 
			"wefEditCtrl.php?mode=create&WEF_WO_NUM=$woNum",
			VGS_NavButton::ADD_ICON);
}

$nav->addIconButton('Download', 'doDownload();', VGS_NavButton::DOWNLOAD_ICON, 'js');

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);

