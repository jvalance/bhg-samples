<?php
require_once '../view/woCreateView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/VGS_Navigator.php';
require_once '../model/Premise.php';

$screenData = $_REQUEST;

if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
	processScreen($screenData);
	if (!$screenData['error']) {
		$woType = $screenData['WO_TYPE'];
		$url = "woEditCtrl.php?mode=create&WO_TYPE=$woType";
		$woPrem = $screenData['WO_PREMISE_NUM'];
		if (trim($woPrem) != '') {
			$url .= "&WO_PREMISE_NUM=$woPrem";
		} 
		header("Location: $url");
	}
}

$nav = new VGS_Navigator('list');
$nav->addIconButton('Submit', "document.form1.submit();", VGS_NavButton::SUBMIT_ICON, 'js');
$nav->addIconButton('W/O Search', "woListCtrl.php?filtSts=restore", VGS_NavButton::SEARCH_ICON );


showScreen($screenData, $nav);

//---------------------------------------------------------------------
function processScreen(&$screenData) {
	$conn = VGS_DB_Conn_Singleton::getInstance();
	$prem = new Premise($conn);
	$screenData['messages'] = array();
	
	$woType = trim($screenData['WO_TYPE']); 
	if ($woType == '') {
		$screenData['messages'][] = 'Work Order Type is required.';
	} else {
		if ($woType == 'LS' || substr($woType, 0,1) == 'S') {
			if (trim($screenData['WO_PREMISE_NUM']) == '') {
				$screenData['messages'][] = 'Premise number is required.';
			} else {
				$premRecord = $prem->retrieve($screenData['WO_PREMISE_NUM']);
				if ($premRecord == NULL) {
					$screenData['messages'][] = 'Invalid premise number was entered.';
				} else {
					$screenData['premAddr'] = $premRecord['UPSAD'];
				}
			}
		}
	}
	
	$screenData['error'] = count($screenData['messages']) > 0;
}