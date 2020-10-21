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
	showHeader('Project Pipe Footage Estimates');
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
		<th class="ca" width="10%"></th>
		<th class="ca" width="20%">Project</th>
		<th class="ca" width="5%">Year</th>
		<th class="ca" width="10%">Pipe Type</th>
		<th class="ca" width="20%">Pipe Description</th>
		<th class="ca" width="10%">Est. Footage</th>
		<td class="ca" width="30%">&nbsp;</th>
	</tr>
	</thead>
	<tbody>
<?php
foreach ($screenData['rows'] as $row) :
	$url = "pfEditCtrl.php?PF_PRJ_NUM={$row['PF_PRJ_NUM']}" . 
								"&PF_EST_YEAR={$row['PF_EST_YEAR']}" .
								"&PF_PIPE_TYPE={$row['PF_PIPE_TYPE']}";// .
								//"&popup={$screenData['popup']}";
	$viewUrl = "$url&mode=inquiry"; 
	$editUrl = "$url&mode=update"; 
	$deleteUrl = "$url&mode=delete"; 
?>

<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">

	<td class="ca">
		<a href="<?= $viewUrl ?>" >
			<img src="../shared/images/view.gif" align="top" title="Display Project Pipe Footage" border=0 /> 
		</a>
		<a href="<?= $editUrl ?>" >
			<img src="../shared/images/edit.gif" align="top" title="Edit Project Pipe Footage" border=0 /> 
		</a>
		<a href="<?= $deleteUrl ?>" >
			<img src="../shared/images/delete7.gif" align="top" title="Delete Project Pipe Footage" border=0 /> 
		</a>
	</td>

  	<td class="la">
      <?= "{$row['PF_PRJ_NUM']} - {$row['proj_desc']}" ?>
		&nbsp;
   </td>

  	<td class="ca">
      <?= $row['PF_EST_YEAR'] ?>
		&nbsp;
   </td>

	<td class="ca b">
      <?=$row['PF_PIPE_TYPE'] ?>
		&nbsp;
	</td>

	<td class="la b">
		<?= $row['pipe_desc'] ?>	
		&nbsp;
   </td>
    
   <td class="ra b">
		<?= number_format($row['PF_EST_FOOTAGE']) ?>	
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
