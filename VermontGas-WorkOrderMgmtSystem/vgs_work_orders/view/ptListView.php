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
	showHeader('Pipe Type Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'ptDownloadCtrl.php';
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
		<th class="ca" width="15%">Pipe Type</th>
		<th class="ca" width="12%">Cost GL</th>
		<th class="ca" width="12%">Close GL</th>
		<th class="ca" width="10%">Cap/Exp</th>
		<th class="ca" width="10%">Matrl</th>
		<th class="ca" width="10%">Diam</th>
		<th class="ca" width="10%">Categ</th>
		<th class="ca" width="10%">Coating</th>
	</tr>
<?php
foreach ($screenData['rows'] as $pt) :
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
		<a href="ptEditCtrl.php?mode=inquiry&PT_PIPE_TYPE=<?= $pt['PT_PIPE_TYPE'] ?>" >
			<img src="../shared/images/view.gif" align="top" title="Display P/T" border=0 /> 
		</a>
		<a href="ptEditCtrl.php?mode=update&PT_PIPE_TYPE=<?= $pt['PT_PIPE_TYPE'] ?>" >
			<img src="../shared/images/edit.gif" align="top" title="Edit P/T" border=0 /> 
		</a>
	</td>
	<td class="la">
		<a href="ptEditCtrl.php?mode=inquiry&PT_PIPE_TYPE=<?= $pt['PT_PIPE_TYPE'] ?>" 
				title="Display Pipe Type Detail">
            <b>(<?= $pt['PT_PIPE_TYPE'] ?>)</b>:  <?=$pt['PT_DESCRIPTION'] ?>
		</a>
		&nbsp;
	</td>

	<td class="la">
	<?= $pt['PT_ACCTG_UNIT_COST'] ?>-<?=$pt['PT_GL_ACCT_COST'] ?>-<?=$pt['PT_SUB_ACCT_COST'] ?>	
		&nbsp;
    </td>
    <td class="la">
	<?= $pt['PT_ACCTG_UNIT_CLOSE'] ?>-<?=$pt['PT_GL_ACCT_CLOSE'] ?>-<?=$pt['PT_SUB_ACCT_CLOSE'] ?>	
		&nbsp;
    </td>
	<td class="ca">
		<?= $pt['capexp_desc'] ?>
		&nbsp;
    </td>
    <td class="ca">
		<?= $pt['material_desc'] ?>
		&nbsp;
    </td>
	<td class="ra">
		<?= $pt['PT_DIAMETER'] ?>
		&nbsp;
    </td>
	<td class="ca">
    	<?= $pt['category_desc']; ?>
		&nbsp;
    </td>
   	<td class="ca">
    	<?= $pt['coating_desc']; ?>
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
