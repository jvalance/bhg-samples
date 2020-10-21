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
		<th class="ca" width="5%"> </th>
		<th class="ca" width="15%">Authority ID</th>
		<th class="ca" width="25%">Authority Name</th>
		<th class="ca" width="35%">Description</th>
		<th class="ca" width="15%">Functional Area</th>
	</tr>
<?php
foreach ($screenData['rows'] as $row) :
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
		<a href="authEditCtrl.php?mode=inquiry&AD_AUTH_ID=<?= $row['AD_AUTH_ID'] ?>" >
			<img src="../shared/images/view.gif" align="top" title="Display Authority Def" border=0 /> 
		</a>
		<a href="authEditCtrl.php?mode=update&AD_AUTH_ID=<?= $row['AD_AUTH_ID'] ?>" >
			<img src="../shared/images/edit.gif" align="top" title="Edit Authority Def" border=0 /> 
		</a>
	</td>

	<td class="la">
		<a href="authEditCtrl.php?mode=inquiry&AD_AUTH_ID=<?= $row['AD_AUTH_ID'] ?>" 
				title="Display Authority Def">
			<?= $row['AD_AUTH_ID'] ?>	
		&nbsp;
    </td>

	<td class="la">
		<a href="authEditCtrl.php?mode=inquiry&AD_AUTH_ID=<?= $row['AD_AUTH_ID'] ?>" 
				title="Display Authority Def">
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
