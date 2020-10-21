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
	showHeader('Project MCF Rate Estimate');
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
		<th class="ca" width="5%"></th>
		<th class="ca" width="12%">Project</th>
		<th class="ca" width="8%">Year</th>
		<th class="ca" width="10%">Rate Class</th>
		<th class="ca" width="20%">Rate Description</th>
		<th class="ca" width="10%">Est. MCF</th>
	</tr>
	</thead>
	<tbody>
<?php
foreach ($screenData['rows'] as $row) :
	$url = "pmEditCtrl.php?PM_PRJ_NUM={$row['PM_PRJ_NUM']}" . 
								"&PM_EST_YEAR={$row['PM_EST_YEAR']}" .
								"&PM_RATE_CLASS={$row['PM_RATE_CLASS']}";
								//"&popup={$screenData['popup']}";
$viewUrl = "$url&mode=inquiry"; 
	$editUrl = "$url&mode=update"; 
	$deleteUrl = "$url&mode=delete"; 
?>

<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">

	<td class="ca">
		<a href="<?= $viewUrl ?>" >
			<img src="../shared/images/view.gif" align="top" title="Display Project MCF Rate" border=0 /> 
		</a>
		<a href="<?= $editUrl ?>" >
			<img src="../shared/images/edit.gif" align="top" title="Edit Project MCF Rate" border=0 /> 
		</a>
		<a href="<?= $deleteUrl ?>" >
			<img src="../shared/images/delete7.gif" align="top" title="Delete Project MCF Rate" border=0 /> 
		</a>
	</td>

  	<td class="la">
      <?= "{$row['PM_PRJ_NUM']} - {$row['proj_desc']}" ?>
		&nbsp;
   </td>

  	<td class="ca">
      <?= $row['PM_EST_YEAR'] ?>
		&nbsp;
   </td>

	<td class="ca">
      <?=$row['PM_RATE_CLASS'] ?>
		&nbsp;
	</td>

	<td class="la">
		<?= $row['rate_desc'] ?>	
		&nbsp;
   </td>
    
   <td class="ra">
		<?= number_format($row['PM_MCF']) ?>	
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
