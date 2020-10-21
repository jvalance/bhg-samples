<?php 
require_once '../view/wpeEditView.php';
require_once '../common/vgs_utilities.php';
require_once '../forms/WO_Pipe_ExposureForm.php';
require_once '../model/WO_Pipe_Exposure.php';
require_once '../model/Code_Values_Master.php';
require_once '../forms/VGS_Navigator.php';

$conn = VGS_DB_Conn_Singleton::getInstance();

try {
	$form = new WO_Pipe_ExposureForm($conn);
	$form->activate();

	//pre_dump($form);
	
	$woNum = $_GET['WPE_WO_NUM'];
	$woObj = new Workorder_Master($conn);
	$woRec = $woObj->getWorkorder($woNum);
	if (!is_array($woRec)) {
		$errorMsg = "ERROR: W/O Number $woNum does not exist.";
		?>
		<span class="error" style="background-color:yellow; font-size: 14pt">
		<?= $errorMsg; ?>
		</span><p />
		<a href="javascript:history.back()">Return</a>
		<?php
		exit; 		
	}
	
	$popup = (bool) $_REQUEST['popup'];
	
	$form->setDateOutputFormat('WPE_EXPOSURE_DATE');
	
	$nav = new VGS_Navigator($form->mode, $popup);
	$nav->addIconButton('Pipe Exposure Search', 
							"wpeListCtrl.php?filter_WPE_WO_NUM=$woNum&popup=$popup",
							VGS_NavButton::SEARCH_ICON);
	
	showScreen($form, $nav);

} catch (Exception $e) {
	echo parse_backtrace(debug_backtrace());
}
