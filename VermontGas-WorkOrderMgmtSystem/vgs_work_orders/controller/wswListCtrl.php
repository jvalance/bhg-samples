<?php 
require_once '../view/wswListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/WO_Sewer.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$wsw = new WO_Sewer($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;

$filter = new VGS_Search_Filter_Group();
$wsw->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $wsw->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

$wsw->execScrollableListQuery($select);
$row_count = 0;

while ( $row = db2_fetch_assoc( $wsw->stmt, $rowNumber++ )) {

// 	if ($row_count < 1) pre_dump($row);
	$row['rowNum'] = $rowNumber;
	if ($row['WO_DATE_COMPLETED'] != '0001-01-01') {
		$row['dateInstalled'] = date('M d, Y', strtotime($row['WO_DATE_COMPLETED']));
	}
	$row['moc'] = formatMOC($row);
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$popup = (bool) $_REQUEST['popup'];
$nav = new VGS_Navigator('list', $popup);
if (isset($_REQUEST['filter_WSW_WO_NUM'])
&& trim($_REQUEST['filter_WSW_WO_NUM']) != '') {
	$woNum = $_REQUEST['filter_WSW_WO_NUM'];
	$nav->addPopupButton(
			'Add Sewer to WO ' . $woNum, 
			"wswEditCtrl.php?mode=create&WSW_WO_NUM=$woNum",
			VGS_NavButton::ADD_ICON);
}
$nav->addIconButton('Download', 'doDownload();', VGS_NavButton::DOWNLOAD_ICON, 'js');

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);

//-------------------------------------------------------------------
function formatMOC ($wswRec) {
	$moc = array();

	if ($wswRec['WSW_MOC_TRENCH'] == 'Y') $moc[] = 'Trench';
	if ($wswRec['WSW_MOC_HDD'] == 'Y') $moc[] = 'HDD';
	if ($wswRec['WSW_MOC_HOG'] == 'Y') $moc[] = 'Hog';
	if ($wswRec['WSW_MOC_PLOWED'] == 'Y') $moc[] = 'Plow';
	if (trim($wswRec['WSW_MOC_OTHER']) != '') $moc[] = trim($wswRec['WSW_MOC_OTHER']);

	return implode(', ', $moc);
}