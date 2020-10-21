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
	if (isset($screenData['filter_WSW_WO_NUM'])) {
		$woNum = $screenData['filter_WSW_WO_NUM'];
	}
	showHeader('W/O Sewer Information');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--
function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'wswDownloadCtrl.php';
	document.searchForm.submit();
	document.searchForm.action = action;
	return false;
}

/*function doPrintReport() {
	var action = 'wcPendingCleanupsReportCtrl.php';
	submitFormToPopUp(document.searchForm, action, 'PendingCleanups');
	return false;
}*/
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
		<th class="ca" width="3%">Seq</th>
		<th class="ca" width="3%">WO Type</th>
		<th class="ca" width="8%">WO Sts</th>
		<th class="ca" width="8%">Sewer<br>Type</th>
		<th class="ca" width="15%">Street Addr</th>
		<th class="ca" width="15%">Town</th>
		<th class="ca" width="10%">Date<br>Installed</th>
		<th class="ca" width="5%">Insp.<br>Needed</th>
		<th class="ca" width="5%">Loc.<br>Prior</th>
		<th class="ca" width="10%">Method of<br>Construction</th>
	</tr>
<?php


foreach ($screenData['rows'] as $wsw) :
	// Add popup flag on link URLs if passed on request
	$popup = $_REQUEST['popup'];
	$linkUrl = "wswEditCtrl.php?WSW_WO_NUM={$wsw['WSW_WO_NUM']}&WSW_SEQNO={$wsw['WSW_SEQNO']}&popup=$popup";
	$inquiryUrl = $linkUrl . '&mode=inquiry';
	$editUrl = $linkUrl . '&mode=update';
	$deleteUrl = $linkUrl . "&mode=delete"; 
	$inquiryTitle = "Display Sewer Info";
	$editTitle = "Edit Sewer Info";
	$deleteTitle = "Delete Sewer Info";
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
		<a href="<?= $inquiryUrl ?>" > 
			<img src="../shared/images/view.gif" title="<?= $inquiryTitle ?>" align="top" border=0 /> 
		</a>
		<a href="<?= $editUrl ?>" >
		<img src="../shared/images/edit.gif" align="top" title="<?= $editTitle ?>" border=0 /> 
		</a>
		<a href="<?= $deleteUrl ?>" >
		<img src="../shared/images/delete7.gif" align="top" title="<?= $deleteTitle ?>" border=0 /> 
		</a>
	</td>
	
	<td class="ca">
		<a href="<?= $editUrl ?>" title="<?= $inquiryTitle ?>">
			<?= $wsw['WSW_WO_NUM'] ?>
		</a>
    </td>

	<td class="ca">
		<?= trim($wsw['WSW_SEQNO']) ?>
		&nbsp;
	</td>
    	
	<td class="la">
		<?= trim($wsw['WO_TYPE']) ?>
		&nbsp;
    </td>

	<td class="la">
		<?= $wsw['WO_STATUS_DESC'] ?>
		&nbsp;
    </td>

	<td class="la">
		<?= $wsw['WSW_SEWER_TYPE'] ?>
		&nbsp;
    </td> 

	<td class="la">
		<?= $wsw['WSW_ADDRESS'] ?>
		&nbsp;
    </td> 
 
	<td class="la"> 
		<?= $wsw['TOWN_DESC'] ?>
		&nbsp;
    </td>
 
	<td class="ca"> 
		<?= $wsw['dateInstalled'] ?>
		&nbsp;
    </td>
 
	<td class="ca"> 
		<?= $wsw['WSW_INSPECTION_NEEDED'] ?>
		&nbsp;
    </td>
 
	<td class="ca"> 
		<?= $wsw['WSW_LOCATED_PRIOR'] ?>
		&nbsp;
    </td>

	<td class="ca">
		<?= $wsw['moc'] ?>
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
