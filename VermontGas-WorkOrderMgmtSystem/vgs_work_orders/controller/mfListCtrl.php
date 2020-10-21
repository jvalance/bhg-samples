<?php 
require_once '../view/mfListView.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Mechanical_Fitting_Failure.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_Paginator.php';
require_once '../forms/VGS_Search_Filter.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../forms/VGS_Navigator.php';

$conn = new VGS_DB_Conn();
$mf = new Mechanical_Fitting_Failure($conn);
$select = new VGS_DB_Select(); 
$cvm = new Code_Values_Master($conn);
$screenData = $_REQUEST;

$filter = new VGS_Search_Filter_Group();
$filter->addFilter('MF_WONUM', 'WO#');
$filter->setInputSize('MF_WONUM', 5);
$filter->addFilter('MF_MECHANICAL_FITTING_DESCRIPTION', 'Fitting', 'LIKE');
$filter->addFilter('MF_MECHANICAL_TYPE_DESCRIPTION', 'Type', 'LIKE');
$filter->addFilter('MF_CAUSE_OF_LEAK', 'Cause');

$cvList = $cvm->getCodeValuesList('MF_CAUSE_OF_LEAK','-- All --');
$filter->setDropDownList('MF_CAUSE_OF_LEAK', $cvList);
 
$mf->getMFSearchSelect($screenData, $select, $filter);
//$select->from = 'MECH_FIT_FAIL';
$rowCount = $mf->getRowCount($select);
$paginator = new VGS_Paginator($rowCount, $screenData['pageToView']);
$paginator->activate();
$rowNumber = $paginator->getStartRow();
//pre_dump($select);

$mf->execScrollableListQuery($select);
$row_count = 0;
while ( $row = db2_fetch_assoc($mf->stmt,$rowNumber++)) {
	$row['rowNum'] = $rowNumber;

	// Retrieve descriptions for codes
	$row['fitting_desc'] = $cvm->getCodeValue('MF_MECHANICAL_FITTING', $row['MF_MECHANICAL_FITTING']);
	$row['type_desc'] = $cvm->getCodeValue('MF_MECHANICAL_TYPE', $row['MF_MECHANICAL_TYPE']);
	$row['location_desc'] = $cvm->getCodeValue('MF_LEAK_LOCATION', $row['MF_LEAK_LOCATION']);
	$row['material_desc'] = $cvm->getCodeValue('MF_FITTING_MATERIAL', $row['MF_FITTING_MATERIAL']);
	$row['cause_desc'] = $cvm->getCodeValue('MF_CAUSE_OF_LEAK', $row['MF_CAUSE_OF_LEAK']);
	$row['howoccur_desc'] = $cvm->getCodeValue('MF_HOW_OCCUR', $row['MF_HOW_OCCUR']);
	
	$screenData['rows'][] = $row;
	if (++$row_count > $paginator->getPageSize()) break;
}

$nav = new VGS_Navigator('list');
$nav->addIconButton('Download', 'doDownload();', 
						VGS_NavButton::DOWNLOAD_ICON, 'js');

if (isset($_REQUEST['filter_MF_WONUM'])) {
	$nav->addIconButton('Create Mechanical Fitting Failure', 
							'mfEditCtrl.php?mode=create&MF_WONUM='.$_REQUEST['filter_MF_WONUM'],
							VGS_NavButton::ADD_ICON);
}

showScreen($screenData, $paginator, $filter, $nav);

db2_close($conn);

