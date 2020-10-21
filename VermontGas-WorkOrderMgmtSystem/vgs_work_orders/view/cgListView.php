<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_Form.php';
require_once 'VGS_PaginatorViewHelper.php';

function showScreen( 
	&$screenData, 
	VGS_Paginator $paginator,
    VGS_Search_Filter_Group $filter,
	VGS_Navigator $nav
) {
	showHeader('Drop Down Lists Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>

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
	<th class="ca" width="20%">Drop Down ID</th>
	<th class="ca" width="25%">Description</th>
	<th class="ca" width="10%">Status</th>
	<th class="ca" width="10%">Codes</th>
	<th class="ca" width="25%">Last Update</th>
</tr>

<?php
foreach ($screenData['rows'] as $cg) :
	$qryStrKey = "CG_GROUP={$cg['CG_GROUP']}";
	$viewPage = "cgEditCtrl.php?mode=inquiry&$qryStrKey"; 
	$editPage = "cgEditCtrl.php?mode=update&$qryStrKey"; 
	$deletePage = "cgEditCtrl.php?mode=delete&$qryStrKey"; 
	$detailScrName = 'Drop Down List Header';
	$editTitle = "Edit $detailScrName";
	$viewTitle = "Display $detailScrName";
	$deleteTitle = "Delete $detailScrName";
//var_dump($cg);
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="la">
			<a href="<?= $viewPage ?>">
				<img src="../shared/images/view.gif" align="top" title="<?= $viewTitle ?>" border=0 /> 
			</a>
			<a href="<?= $editPage ?>">
				<img src="../shared/images/edit.gif" align="top" title="<?= $editTitle ?>" border=0 /> 
			</a>
			<?php
			// Only allow delete if no values for this drop down list
			if ((int)$cg['VALUES_COUNT'] == 0) :  
			?>	
			<a href="<?= $deletePage ?>">
				<img src="../shared/images/delete7.gif" align="top" title="<?= $deleteTitle ?>" border=0 /> 
			</a>
			<?php 
			endif; 
			?>
		<a href="cvListCtrl.php?filter_CV_GROUP=<?= $cg['CG_GROUP'] ?>" >
		 Values 
		</a>
		
	</td>

	<td class="la">
		<a href="cvListCtrl.php?filter_CV_GROUP=<?= $cg['CG_GROUP'] ?>" >
		<?= $cg['CG_GROUP'] ?>
		</a>
		&nbsp;
    </td>
	<td class="la">
		<a href="cvListCtrl.php?filter_CV_GROUP=<?= $cg['CG_GROUP'] ?>" >
		<?= $cg['CG_DESCRIPTION'] ?>
		</a>
		&nbsp;
	</td>
	<td class="ca">
		<?= $cg['status_desc'] ?>
		&nbsp;
    </td>
	<td class="ca">
		<?= "{$cg['VALUES_COUNT']}" ?>
		&nbsp;
    </td>
	<td class="la">
    	<?= $cg['last_changed'] ?>
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
