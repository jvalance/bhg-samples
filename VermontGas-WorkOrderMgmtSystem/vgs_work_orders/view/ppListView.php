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
	showHeader('Plastic Pipe Failure Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'ppDownloadCtrl.php';
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
		<th class="ca" width="5%"> </th>
		<th class="ca" width="5%">W/O#</th>
		<th class="ca" width="25%">W/O Description</th>
		<th class="ca" width="10%">Install Date</th>
		<th class="ca" width="10%">Failure Date</th>
		<th class="ca" width="15%">Failure Cause</th>
		<th class="ca" width="15%">Failure Loc</th>
	</tr>
<?php
foreach ($screenData['rows'] as $pp) :
//var_dump($pp)
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
	<a href="ppEditCtrl.php?mode=inquiry&PP_WONUM=<?= $pp['PP_WONUM'] ?>" >
	<img src="../shared/images/view.gif" align="top" title="Display P/P" border=0 /> 
	</a>
	<a href="ppEditCtrl.php?mode=update&PP_WONUM=<?= $pp['PP_WONUM'] ?>" >
	<img src="../shared/images/edit.gif" align="top" title="Edit P/P" border=0 /> 
	</a>
	</td>

	<td class="ca">
		<a href="ppEditCtrl.php?mode=inquiry&PP_WONUM=<?= $pp['PP_WONUM'] ?>" 
				title="Display Plastic Pipe Detail">
           <b><?= $pp['PP_WONUM']  ?>
  		</a>
		&nbsp;
	</td>

	<td class="la">
		<a href="ppEditCtrl.php?mode=inquiry&PP_WONUM=<?= $pp['PP_WONUM'] ?>" 
				title="Display Plastic Pipe Detail">
           <b><?= $pp['WO_DESCRIPTION']  ?>
		</a>
		&nbsp;
	</td>
    
	<td class="la">
    	<?= $pp['fmtd_Install_Date']; ?>
		&nbsp;
    </td>
    
	<td class="la">
    	<?= $pp['fmtd_Fail_Date']; ?>
		&nbsp;
    </td>
    
   	<td class="la">
    	<?= $pp['cause_desc']; ?>
		&nbsp;
    </td>
    
    <td class="la">
    	<?= $pp['location_desc']; ?>
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


