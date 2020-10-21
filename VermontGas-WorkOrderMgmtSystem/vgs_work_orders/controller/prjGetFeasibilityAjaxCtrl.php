<?php 
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Project_Master.php';

$sec = new Security();
$sec->checkPermissionByCategory('PROJ', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$proj = new Project_Master($conn);

$option = $_REQUEST['option'];
switch ($option) {
	case 'getFeasRec':
		$returnData = getFeasibilityRec($proj);
		break;
	case 'getNextFeasNum':
		$returnData = getNextFeasibilityNum($proj);
		break;
	default:
		$returnData['error'] = "Invalid option requested: $option.";
		break;
}
	
echo json_encode($returnData);

db2_close($conn);

//---------------------------------------------------------------------
function getFeasibilityRec($proj) {
	$feasNum = $_REQUEST['feasNum'];
	$feasRec = $proj->getFeasibilityRec($feasNum);
	if ($feasRec == NULL) {
		$feasRec['error'] = "Invalid feasibility number: $feasNum.";
	}
	return $feasRec;
}

//---------------------------------------------------------------------
function getNextFeasibilityNum($proj) {
	$returnData = array();
	$feasNum = $proj->getNextFeasibilityNum();
	if ($feasNum == NULL || $feasNum <= 0) {
		$returnData['error'] = "*** Unable to retrieve next feasibility number. ***";
	} else {
		$returnData['nextFeasNum'] = $feasNum;
	}
	return $returnData;
}