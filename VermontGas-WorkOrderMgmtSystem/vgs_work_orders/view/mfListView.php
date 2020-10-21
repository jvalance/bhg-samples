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
	showHeader('Mechanical Fitting Failure Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'mfDownloadCtrl.php';
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
		<th class="ca" width="18%">WO#</th>
		<th class="ca" width="10%">Failure Date</th>
		<th class="ca" width="15%">Fitting</th>
		<th class="ca" width="15%">Type</th>
		<th class="ca" width="10%">Leak Loc</th>
		<th class="ca" width="10%">Material</th>
		<th class="ca" width="10%">Failure Cause</th>
		<th class="ca" width="10%">How Occur</th>
		</tr>
<?php
foreach ($screenData['rows'] as $mf) :
var_dump($mf)
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
	<a href="mfEditCtrl.php?mode=inquiry&MF_WONUM=<?= $mf['MF_WONUM'] ?>" >
	<img src="../shared/images/view.gif" align="top" title="Display M/F" border=0 /> 
	</a>
	<a href="mfEditCtrl.php?mode=update&MF_WONUM=<?= $mf['MF_WONUM'] ?>" >
	<img src="../shared/images/edit.gif" align="top" title="Edit M/F" border=0 /> 
	</a>
	</td>
	<td class="la">
		<a href="mfEditCtrl.php?mode=inquiry&MF_WONUM=<?= $mf['MF_WONUM'] ?>" 
				title="Display Mech Fit Fail Detail">
           <b>(<?= $mf['MF_WONUM'] ?>)</b>:  <?=$mf['WO_DESCRIPTION'] ?>
          		</a>
		&nbsp;
		</td>
			<td class="ca">
		<?= $mf['MF_DATE_FAIL'] ?>
		&nbsp;
    </td>
    <td class="ca">
		<?= $mf['MF_MECHANICAL_FITTING'] ?>
		&nbsp;
    </td>
	<td class="ca">
    	<?= $mf['MF_MECHANICAL_TYPE']; ?>
		&nbsp;
    </td>
     	<td class="ca">
    	<?= $mf['MF_LEAK_LOCATION']; ?>
		&nbsp;
    </td>
   	<td class="ca">
    	<?= $mf['MF_FITTING_MATERIAL']; ?>
		&nbsp;
    </td>
	<td class="ca">
    	<?= $mf['MF_CAUSE_OF_LEAK']; ?>
		&nbsp;
    </td>
	<td class="ca">
    	<?= $mf['MF_HOW_OCCUR']; ?>
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


