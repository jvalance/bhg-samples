<?php 
require_once 'layout.php';
require_once 'form.php';
require_once 'VGS_PaginatorViewHelper.php';

function showScreen( 
	array &$screenData, 
	VGS_Paginator $paginator,
    VGS_Search_Filter_Group $filter,
    VGS_Navigator $nav
	) 
{   
	showHeader('Authority Definition Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
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
		<th class="ca" width="15%">Authority ID</th>
		<th class="ca" width="25%">Authority Name</th>
		<th class="ca" width="35%">Description</th>
		<th class="ca" width="15%">Functional Area</th>
	</tr>
<?php
foreach ($screenData['rows'] as $row) :
	$qryStrKey = "AD_AUTH_ID={$row['AD_AUTH_ID']}";
	$viewPage = "authEditCtrl.php?mode=inquiry&$qryStrKey"; 
	$editPage = "authEditCtrl.php?mode=update&$qryStrKey"; 
	$deletePage = "authEditCtrl.php?mode=delete&$qryStrKey"; 
	$detailScrName = 'Authority';
	$editTitle = "Edit $detailScrName";
	$viewTitle = "Display $detailScrName";
	$deleteTitle = "Delete $detailScrName";
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
		<a href="<?= $viewPage ?>">
			<img src="../shared/images/view.gif" align="top" title="<?= $viewTitle ?>" border=0 /> 
		</a>
		<a href="<?= $editPage ?>">
			<img src="../shared/images/edit.gif" align="top" title="<?= $editTitle ?>" border=0 /> 
		</a>
		<a href="<?= $deletePage ?>">
			<img src="../shared/images/delete7.gif" align="top" title="<?= $deleteTitle ?>" border=0 /> 
		</a>
	</td>

	<td class="la">
		<a href="<?= $viewPage ?>" title="<?= $viewTitle ?>">
			<?= $row['AD_AUTH_ID'] ?>	
		&nbsp;
    </td>

	<td class="la">
		<a href="<?= $viewPage ?>" title="<?= $viewTitle ?>">
			<?= $row['AD_AUTH_NAME'] ?>
		</a>	
		&nbsp;
    </td>

	<td class="la">
		<?= $row['AD_DESCRIPTION'] ?>	
		&nbsp;
    </td>

	<td class="la">
		<?= $row['AD_FUNCTIONAL_AREA'] ?>	
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
