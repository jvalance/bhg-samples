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
	showHeader('Project Yearly Estimates');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>

<form name="searchForm" method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >

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
	<thead>
	<tr>
		<th class="ca" width="7%"></th>
		<th class="ca" width="5%">Year</th>
		<th class="ca" width="9%">&nbsp;</th>
		<th class="ca" width="9%">&nbsp;</th>
		
		<th class="ca" width="10%">Main Cost</th>
		<th class="ca" width="10%">Service Cost</th>
		<th class="ca" width="15%">Meter Cost</th>
		<th class="ca" width="10%">No. Custs</th>
		<th class="ca" width="10%">No. Svcs</th>
		<th class="ca" width="15%">Years to Build</th>
	</tr>
	</thead>
	<tbody>
<?php
foreach ($screenData['rows'] as $row) :
	$url = "peEditCtrl.php?PE_PRJ_NUM={$row['PE_PRJ_NUM']}&PE_EST_YEAR={$row['PE_EST_YEAR']}";
	$viewUrl = "$url&mode=inquiry"; 
	$editUrl = "$url&mode=update"; 
	$deleteUrl = "$url&mode=delete"; 

	$pmUrl = "javascript:openPopUp('pmListCtrl.php?filter_PM_PRJ_NUM={$row['PE_PRJ_NUM']}&filter_PM_EST_YEAR={$row['PE_EST_YEAR']}&popup=true', 'Proj_MCF_Est');";
	$pfUrl = "javascript:openPopUp('pfListCtrl.php?filter_PF_PRJ_NUM={$row['PE_PRJ_NUM']}&filter_PF_EST_YEAR={$row['PE_EST_YEAR']}&popup=true', 'Proj_Pipe_Est');";
	$popupIcon = VGS_NavButton::getPopupIconURL( );
	?>

<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">

	<td class="ca">
		<a href="<?= $viewUrl ?>" >
			<img src="../shared/images/view.gif" align="top" title="Display Project Estimates" border=0 /> 
		</a>
		<a href="<?= $editUrl ?>" >
			<img src="../shared/images/edit.gif" align="top" title="Edit Project Estimates" border=0 /> 
		</a>
		<a href="<?= $deleteUrl ?>" >
			<img src="../shared/images/delete7.gif" align="top" title="Delete Project Estimates" border=0 /> 
		</a>
	</td>

  	<td class="ca">
      <?= $row['PE_EST_YEAR'] ?>
		&nbsp;
   </td>

  	<td class="ca">
		<a href="#" onclick="<?= $pmUrl ?>">
			<img src="<?= $popupIcon ?>" border="0" align="top" /> 
			MCF/Rate
		</a>
   </td>

  	<td class="ca">
		<a href="#" onclick="<?= $pfUrl ?>">
			<img src="<?= $popupIcon ?>" border="0" align="top" /> 
			Footage/Pipe
		</a>
   </td>

	<td class="ra">
      <?=$row['PE_EST_MAIN_COST'] ?>
		&nbsp;
	</td>

	<td class="ra">
		<?= $row['PE_EST_SVC_COST'] ?>	
		&nbsp;
    </td>
    
    <td class="ra">
		<?= $row['PE_EST_METER_COST'] ?>	
		&nbsp;
    </td>
    
	<td class="ca">
		<?= $row['PE_EST_NUM_CUSTS'] ?>
		&nbsp;
    </td>
    
    <td class="ca">
		<?= $row['PE_EST_NUM_SVCS'] ?>
		&nbsp;
    </td>
    
	<td class="ca">
		<?= $row['PE_YEAR_BLD'] ?>
		&nbsp;
   </td>
    
</tr>
<?php
endforeach; ?>
</tbody>
</table>

<!-- End of output div -->
</div>

</form>

<?php
	showFooter();
}
