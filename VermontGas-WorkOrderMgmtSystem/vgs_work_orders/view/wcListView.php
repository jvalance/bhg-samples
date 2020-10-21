<?php 
require_once 'layout.php';
require_once 'form.php';
require_once 'VGS_PaginatorViewHelper.php';
require_once '../forms/VGS_Search_Filter_Group.php';

function showScreen( 
	array &$screenData, 
	VGS_Paginator $paginator,
	VGS_Search_Filter_Group $filter,
	VGS_Navigator $nav
	) 
{
	if (isset($screenData['filter_WC_WONUM'])) {
		$woNum = $screenData['filter_WC_WONUM'];
	}
	showHeader('W/O Cleanup Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--
function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'wcDownloadCtrl.php';
	document.searchForm.submit();
	document.searchForm.action = action;
	return false;
}
//function doPrintReport() {
//	var action = document.searchForm.action;
//	document.searchForm.action = 'wcPendingCleanupsReportCtrl.php';
//	document.searchForm.submit();
//	document.searchForm.action = action;
//	return false;
//}
function doPrintReport() {
	var action = 'wcPendingCleanupsReportCtrl.php';
	submitFormToPopUp(document.searchForm, action, 'PendingCleanups');
	return false;
}
//-->
</script>
<form name="searchForm" method="get" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >

<?php 
$nav->renderNavBar();
?>

<div id="output"> 
<?php 
$filter->renderView($screenData);
?>

<table class="lists" >
<caption>
	<?php $pageHelper->renderView(); ?>
</caption>
	<tr>
		<th class="ca" width="5%"> </th>
		<th class="ca" width="5%">WO#</th>
		<th class="ca" width="12%">WO Type</th>
		<th class="ca" width="12%">C/U Type</th>
		<th class="ca" width="9%">C/U Status</th>
		<th class="ca" width="17%">Street Addr</th>
		<th class="ca" width="17%">Town</th>
		<th class="ca" width="10%">Early Start</th>
		<th class="ca" width="10%">Vend/Crew</th>
	</tr>
<?php
foreach ($screenData['rows'] as $wc) :
	// Add popup flag on link URLs if passed on request
	$popup = $_REQUEST['popup'];
	$linkUrl = "wcEditCtrl.php?WC_WONUM={$wc['WC_WONUM']}&WC_CLEANUP_NUM={$wc['WC_CLEANUP_NUM']}&popup=$popup";
	$inquiryUrl = $linkUrl . '&mode=inquiry';
	$editUrl = $linkUrl . '&mode=update';
	$inquiryTitle = "Display W/O Clean Up";
	$editTitle = "Edit W/O Clean Up";
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
		<a href="<?= $inquiryUrl ?>" > 
			<img src="../shared/images/view.gif" title="<?= $inquiryTitle ?>" align="top" border=0 /> 
		</a>
		<?php if (trim($wc['WC_CLEANUP_STATUS']) == 'Open') : ?>
		<a href="<?= $editUrl ?>" >
		<img src="../shared/images/edit.gif" align="top" title="<?= $editTitle ?>" border=0 /> 
		</a>
		<?php endif; ?>
	</td>

	<td class="ca">
		<a href="<?= $editUrl ?>" title="<?= $inquiryTitle ?>">
			<?= $wc['WC_WONUM'] ?>
		</a>
    </td>

	<td class="la">
		<?= trim($wc['WO_TYPE_DESC']) ?>
		&nbsp;
	</td>

	<td class="la">
		<?= $wc['CLEANUP_TYPE_DESC'] ?>
		&nbsp;
    </td>

	<td class="la">
		<?= $wc['WC_CLEANUP_STATUS'] ?>
		&nbsp;
    </td>

	<td class="la">
		<?php //$wc['WC_ADDR_STREET'] ?>
		<?= $wc['WO_DESCRIPTION'] ?>
		&nbsp;
    </td> 
 
	<td class="la"> 
		<?= $wc['TOWN_DESC'] ?>
		&nbsp;
    </td>
 
	<td class="ca"> 
		<?= date('M d, Y', strtotime($wc['WC_EARLY_START_DATE'])) ?>
		&nbsp;
    </td>

	<td class="ca">
		<?= $wc['WC_VENDOR_NUM'] ?>
		&nbsp;
    </td>

</tr>
<?php
endforeach; ?>
</table>

<!-- End of output div -->
</div>

</form>
<?php
	showFooter();
}
