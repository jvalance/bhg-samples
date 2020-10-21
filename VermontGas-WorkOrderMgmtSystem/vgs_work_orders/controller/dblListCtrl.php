<?php 
require_once '../view/dblListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/DB_Update_Log.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$dbLog = new DB_Update_Log($conn);
$select = new VGS_DB_Select(); 
$screenData = $_REQUEST;

// On initial page load, convert key field parameter to proper format for SQL where clause 
if (isset($screenData['key_field'])) {
	$screenData['filter_DBL_KEY_FIELDS'] = 
		$screenData['key_field'] . '":"' . $screenData['key_value'] . '"';
}

// Ensure special characters are preserved in the input field
$screenData['filter_DBL_KEY_FIELDS'] = htmlspecialchars($screenData['filter_DBL_KEY_FIELDS'], ENT_QUOTES);

$filter = new VGS_Search_Filter_Group();
$dbLog->buildFilteredSelect($screenData, $select, $filter);

$rowCount = $dbLog->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->setPageSize(50);
$paginator->activate();
$rowNumber = $paginator->getStartRow();

// pre_dump($select->toString(false));
// pre_dump($select->parms);

// Set parms value so that it limits results to proper number length
// $parm = $select->parms[0];
// $parm = substr($parm, 0, (strlen($parm)-1)) . "_";
// $select->parms[0] = $parm;

// Ensure that special character encodings are decoded for the SQL where clause 
foreach ($select->parms as $pKey => $parm) {
	$parm = htmlspecialchars_decode($parm, ENT_QUOTES);
	$select->parms[$pKey] = urldecode($parm);
}

$dbLog->execScrollableListQuery($select);

$row_count = 0;

while ( $row = db2_fetch_assoc( $dbLog->stmt, $rowNumber++ )) {
	$row['rowNum'] = $rowNumber;
	$keys = json_decode($row['DBL_KEY_FIELDS'],true);
	$keys_string = '';
	foreach ($keys as $key => $value) {
		$keys_string .= "$key=$value; ";
	}
	$row['keys'] = substr($keys_string, 0, strlen($keys_string)-2); 
	$row['DBL_DATE'] = VGS_Form::fixDateOutput($row["DBL_DATE"],true);
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$popup = (bool) $_REQUEST['popup'];
$nav = new VGS_Navigator('list', $popup);
$nav->addIconButton('Download', 'doDownload();', VGS_NavButton::DOWNLOAD_ICON, 'js');

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);
