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
	showHeader('Authority/Profile Search');
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
		<th class="ca" width="8%"> </th>
		<th class="ca" width="10%">Profile Type</th>
		<th class="ca" width="12%">Profile ID</th>
		<th class="ca" width="20%">Profile Name</th>
		<th class="ca" width="15%">Authority ID</th>
		<th class="ca" width="20%">Authority Name</th>
		<th class="ca" width="10%">Permission</th>
	</tr>
<?php
foreach ($screenData['rows'] as $row) :
	$qryStrKey = "AP_AUTH_ID={$row['AP_AUTH_ID']}&AP_PROFILE_ID={$row['AP_PROFILE_ID']}";
	$viewPage = "authProfEditCtrl.php?mode=inquiry&$qryStrKey"; 
	$deletePage = "authProfEditCtrl.php?mode=delete&$qryStrKey"; 
	$editPage = "authProfEditCtrl.php?mode=update&$qryStrKey"; 
	$detailScrName = 'Profile Authority Xref';
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
			<?= $row['PRF_PROFILE_TYPE'] ?>	
			&nbsp;
	    </td>
	
		<td class="la">
			<?= $row['AP_PROFILE_ID'] ?>	
			&nbsp;
	    </td>
	
		<td class="la">
			<?= $row['GP_DESCRIPTION'] ?>	
			&nbsp;
	    </td>
	
		<td class="la">
			<a href="<?= $viewPage ?>" title="<?= $viewTitle ?>">
				<?= $row['AP_AUTH_ID'] ?>	
			&nbsp;
	    </td>
	
		<td class="la">
			<a href="<?= $viewPage ?>" title="<?= $viewTitle ?>">
				<?= $row['AD_AUTH_NAME'] ?>
			</a>	
			&nbsp;
	    </td>
	
		<td class="la">
			<?= $row['permission_desc'] ?>	
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
