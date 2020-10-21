<?php 
require_once '../view/layout.php';
require_once '../view/wcnEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/WOCancellationsForm.php';
require_once '../model/WO_Cancellations.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();

$sec = new Security();
$wcnForm = new WOCancellationsForm($conn, $_REQUEST['WCN_WO_NUM'], true);

if (!$wcnForm->isInquiryMode()) {
	$sec->checkPermissionByCategory('WO', 'CANCEL');
} else {
	$sec->checkPermissionByCategory('WO', $wcnForm->mode );
}

if ($wcnForm->isCreateMode()) {
	$woCnlObj = new WO_Cancellations($conn);
	$cnlRec = $woCnlObj->retrieveByID($_REQUEST['WCN_WO_NUM']);
	// Cancel request already exists?
	if (is_array($cnlRec)) {
		die("W/O {$_REQUEST['WCN_WO_NUM']} is already cancelled.");
	}
}

$wcnForm->activate();

$nav = new VGS_Navigator($wcnForm->mode);

$url = "woListCtrl.php?filtSts=restore";
$nav->addIconButton('W/O Search', $url, VGS_NavButton::SEARCH_ICON);

$woNum = $wcnForm->getWoNum();
if ($wcnForm->getIsDollarsApplied()) {
	$url = "woCostDtlCtrl.php?WO_NUM=$woNum";
	$nav->addPopupButton('Cost Detail', $url, VGS_NavButton::COST_ICON);
}

showScreen($wcnForm, $nav);
