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
	if (isset($screenData['filter_WEF_WO_NUM'])) {
		$woNum = $screenData['filter_WEF_WO_NUM'];
	}
	showHeader('W/O Electrofusion Information');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--
function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'wefDownloadCtrl.php';
	document.searchForm.submit();
	document.searchForm.action = action;
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
		<th class="ca" width="7%"> </th>
		<th class="ca" width="5%">WO#</th>
		<th class="ca" width="3%">Seq</th>
		<th class="ca" width="15%">WO Type</th>
		<th class="ca" width="10%">Fusion<br>Date</th>
		<th class="ca" width="12%">Fusion<br>Type</th>
		<th class="ca" width="30%">EF Descrition</th>
		<th class="ca" width="11%">Town</th>
		<th class="ca" width="5%">Completed<br>By</th>
	</tr>
<?php

foreach ($screenData['rows'] as $wef) :
	// Add popup flag on link URLs if passed on request
	$popup = $_REQUEST['popup'];
	$linkUrl = "wefEditCtrl.php?WEF_WO_NUM={$wef['WEF_WO_NUM']}&WEF_SEQNO={$wef['WEF_SEQNO']}&popup=$popup";
	$inquiryUrl = $linkUrl . '&mode=inquiry';
	$editUrl = $linkUrl . '&mode=update';
	$deleteUrl = $linkUrl . "&mode=delete"; 
	$inquiryTitle = "Display Electrofusion Info";
	$editTitle = "Edit Electrofusion Info";
	$deleteTitle = "Delete Electrofusion Info";
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
		<a href="<?= $inquiryUrl ?>" title="<?= $inquiryTitle ?>">
			<?= $wef['WEF_WO_NUM'] ?>
		</a>
    </td>

	<td class="ca">
		<?= trim($wef['WEF_SEQNO']) ?>
		&nbsp;
	</td>

	<td class="la">
		<?= trim($wef['WO_TYPE_DESC']) ?>
		&nbsp;
	</td>
    	
	<td class="ca">
		<?= trim($wef['fusionDate']) ?>
		&nbsp;
    </td>

	<td class="la">
		<?= $wef['FUSION_TYPE_DESC'] ?>
		&nbsp;
    </td>
 
	<td class="la"> 
		<?= $wef['WEF_DESCRIPTION'] ?>
		&nbsp;
    </td>
 
	<td class="la"> 
		<?= $wef['TOWN_DESC'] ?>
		&nbsp;
    </td>
 
	<td class="ca"> 
		<?= $wef['WEF_COMPLETED_BY'] ?>
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
