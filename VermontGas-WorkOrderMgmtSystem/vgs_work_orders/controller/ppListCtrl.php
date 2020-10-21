<?php 
require_once '../view/ppListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Plastic_Pipe_Fail.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();
$pp = new Plastic_Pipe_Fail($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;

$filter = new VGS_Search_Filter_Group();
$filter->addFilter('PP_WONUM', 'WO#');
$filter->setInputSize('PP_WONUM', 5);
$filter->addFilter('WO_DESCRIPTION', 'Description', 'LIKE');
$filter->addFilter('PP_LOCATION', 'Location');
$filter->addFilter('PP_CAUSE', 'Cause');

$cvList = $cvm->getCodeValuesList('PP_LOC', '-- All --');
$filter->setDropDownList('PP_LOCATION', $cvList);
 
$cvList = $cvm->getCodeValuesList('PP_CAUSE', '-- All --');
$filter->setDropDownList('PP_CAUSE', $cvList);

$pp->getPPSearchSelect($screenData, $select, $filter);

$rowCount = $pp->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();
//pre_dump($select);

$pp->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($pp->stmt,$rowNumber++)) {
	$row['rowNum'] = $rowNumber;
	
	// Retrieve descriptions for codes
	$row['install_desc'] = $cvm->getCodeValue('PP_INSTALL', $row['PP_INSTALL_METHOD']);
	$row['location_desc'] = $cvm->getCodeValue('PP_LOC', $row['PP_LOCATION']);
	$row['cause_desc'] = $cvm->getCodeValue('PP_CAUSE', $row['PP_CAUSE']);
	
	$row['fmtd_Install_Date'] = VGS_Form::fixDateOutput($row['PP_INSTALL_DATE'], true);
	$row['fmtd_Fail_Date'] = date('M d, Y', strtotime($row['PP_FAIL_DATE']));
	
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}


$nav = new VGS_Navigator('list');
$nav->addIconButton('Download', 'doDownload();', 
						VGS_NavButton::DOWNLOAD_ICON, 'js');

// If W/O# entered in filters, and no PP Fail found 
// for the W/O, show the create button.
$woNum = trim($screenData['filter_PP_WONUM']);
if ($woNum != '') {
	$ppRec = $pp->retrieveByWONum($woNum);
	if (!is_array($ppRec)) {
		// No PPF rec for WO - show create button
		$nav->addIconButton("Create Plastic Pipe Fail for WO $woNum", 
				"ppEditCtrl.php?mode=create&PP_WONUM=$woNum",
				VGS_NavButton::ADD_ICON);
	}
}

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);

