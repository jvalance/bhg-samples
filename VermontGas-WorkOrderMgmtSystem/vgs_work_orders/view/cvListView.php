<?php 
require_once 'layout.php';
//require_once 'form.php';
require_once '../forms/VGS_Form.php';
require_once 'VGS_PaginatorViewHelper.php';
require_once '../forms/VGS_Search_Filter_Group.php';

function showScreen( 
	array &$screenData, 
	VGS_Paginator $paginator,
	VGS_Search_Filter_Group $filter,
	VGS_Navigator $nav
	) 
{
	showHeader('Drop Down Values Search');
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
	<th class="ca" width="10%"> </th>
	<th class="ca" width="10%">List</th>
	<th class="ca" width="10%">Seq#</th>
	<th class="ca" width="10%">Code</th>
	<th class="ca" width="25%">Value</th>
	<th class="ca" width="25%">Description</th>
	<th class="ca" width="10%">Status</th>
</tr>

<?php
foreach ($screenData['rows'] as $row) :
	$qryStrKey = "CV_GROUP={$row['CV_GROUP']}&CV_CODE={$row['CV_CODE']}";
	$viewPage = "cvEditCtrl.php?mode=inquiry&$qryStrKey"; 
	$editPage = "cvEditCtrl.php?mode=update&$qryStrKey"; 
	$deletePage = "cvEditCtrl.php?mode=delete&$qryStrKey"; 
	$detailScrName = 'Drop Down Value';
	$editTitle = "Edit $detailScrName";
	$viewTitle = "Display $detailScrName";
	$deleteTitle = "Delete $detailScrName";
//var_dump($row);
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
			<a href="<?= $viewPage ?>">
				<img src="../shared/images/view.gif" align="top" title="<?= $viewTitle ?>" border=0 /> 
			</a>&nbsp;&nbsp;
			<a href="<?= $editPage ?>">
				<img src="../shared/images/edit.gif" align="top" title="<?= $editTitle ?>" border=0 /> 
			</a>&nbsp;&nbsp;
			<a href="<?= $deletePage ?>">
				<img src="../shared/images/delete7.gif" align="top" title="<?= $deleteTitle ?>" border=0 /> 
			</a>
	</td>
	
	<td class="la">
		<?= $row['CV_GROUP'] ?>
		&nbsp;
	</td>
    <td class="la">
    	<?= $row['CV_SEQUENCE'] ?>
    	&nbsp;
	 </td> 
	<td class="la">
		<a href="<?= $viewPage ?>" title="Display Drop Down Value Detail">
            <?= $row['CV_CODE'] ?>
		</a>
		&nbsp;
	</td>
	<td class="la">
		<a href="<?= $viewPage ?>" title="Display Drop Down Value Detail">
            <?=$row['CV_VALUE'] ?>
		</a>
		&nbsp;
	</td>
	<td class="la">
		<?= $row['CV_DESCRIPTION'] ?>
		&nbsp;
	</td>
	<td class="la">
		<?= $row['status_desc'] ?>
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
