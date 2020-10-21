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
	
	if (isset($screenData['filter_WPE_WO_NUM'])) {
		$woNum = $screenData['filter_WPE_WO_NUM'];
	}
	showHeader('W/O Pipe Exposure Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--
function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'wpeDownloadCtrl.php';
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
<span class="error" style="background-color:yellow; font-size: 14pt">
<?= $screenData['errorMsg']; ?>
</span>

<table class="lists" >
<caption>
	<?php $pageHelper->renderView(); ?>
</caption>
	<tr>
		<th class="ca" width="5%"> </th>
		<th class="ca" width="7%">WO#</th>
		<th class="ca" width="12%">Type</th>
		<th class="ca" width="12%">Designation</th>
		<th class="ca" width="7%">Status</th>
		<th class="ca" width="10%">Expos. Date</th>
		<th class="ca" width="15%">Pipe Type</th>
		<th class="ca" width="15%">Municipality</th>
		<th class="la" width="24%">Location</th>
	</tr>
<?php
foreach ($screenData['rows'] as $wpe) :
	// Add popup flag on link URLs if passed on request
	$popup = $_REQUEST['popup'];
	$linkUrl = "wpeEditCtrl.php?WPE_WO_NUM={$wpe['WPE_WO_NUM']}&WPE_SEQNO={$wpe['WPE_SEQNO']}&popup=$popup";
	$inquiryUrl = $linkUrl . '&mode=inquiry';
	$editUrl = $linkUrl . '&mode=update';
	$inquiryTitle = "Display W/O Pipe Exposure";
	$editTitle = "Edit W/O Pipe Exposure";

?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
		<a href="<?= $inquiryUrl ?>" > 
			<img src="../shared/images/view.gif" title="<?= $inquiryTitle ?>" align="top" border=0 /> 
		</a>
		<a href="<?= $editUrl ?>" >
			<img src="../shared/images/edit.gif" align="top" title="<?= $editTitle ?>" border=0 /> 
		</a>

	</td>

	<td class="ca">
		<a href="<?= $inquiryUrl ?>">
			<?= $wpe['WPE_WO_NUM'] ?>
		</a>
    </td>
	<td class="la">
		<?= trim($wpe['WO_TYPE_DESC']) ?>
		&nbsp;
	</td>
	<td class="la">
		<?= trim($wpe['designation'] ) ?>
		&nbsp;
	</td>
	<td class="la">
		<?= $wpe['WO_STATUS_DESC'] ?>
		&nbsp;
    </td>
	<td class="ra">
    	<?= date('M d, Y', strtotime($wpe['WPE_EXPOSURE_DATE'])); ?>
		&nbsp;
    </td>
	<td class="la">
		<?= "{$wpe['PIPE_TYPE_DESC']}" ?>
		&nbsp;
    </td>
	<td class="la">
		<?= "{$wpe['town']}" ?>
		&nbsp;
    </td>
   	<td class="la">
    	<?= $wpe['WO_DESCRIPTION']; ?>
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
