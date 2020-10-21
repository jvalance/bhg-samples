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
	showHeader('Project Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
// Initially position cursor on description filter
$('document').ready(function() {
	$('#filter_PRJ_DESCRIPTION').focus();
})

function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'prjDownloadCtrl.php';
	document.searchForm.submit();
	document.searchForm.action = action;
	return false;
}
//-->
</script>
 
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
		<th class="ca" width="5%">Proj#</th>
		<th class="ca" width="8%">&nbsp;</th>
		<th class="ca" width="20%">Description</th>
		<th class="ca" width="10%">Status</th>
		<th class="ca" width="15%">Contact</th>
		<th class="ca" width="10%">Feasibility #</th>
		<th class="ca" width="15%">Municipality</th>
		<th class="ca" width="5%">WO Count</th>
		<th class="ca" width="10%">Cap/Exp</th>
	</tr>
	</thead>
	<tbody>
<?php
foreach ($screenData['rows'] as $row) :
	$url = "prjEditCtrl.php?PRJ_NUM={$row['PRJ_NUM']}";
	$viewUrl = "$url&mode=inquiry"; 
	$editUrl = "$url&mode=update"; 
	$deleteUrl = "$url&mode=delete"; 
	$projEstUrl = "javascript:openPopUp('peListCtrl.php?filter_PE_PRJ_NUM={$row['PRJ_NUM']}&popup=true', 'ProjEst');";
	$popupIcon = VGS_NavButton::getPopupIconURL( );
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
		<a href="<?= $viewUrl ?>" >
			<img src="../shared/images/view.gif" align="top" title="Display Project" border=0 /> 
		</a>
		<a href="<?= $editUrl ?>" >
			<img src="../shared/images/edit.gif" align="top" title="Edit Project" border=0 /> 
		</a>
	</td>

   <td class="ca">
		<a href="<?= $viewUrl ?>" 
			title="Display Project: <?php echo htmlspecialchars($row['PRJ_DESCRIPTION']); ?>">
         <?= $row['PRJ_NUM'] ?>
		</a>
		&nbsp;
   </td>

   <td class="ca">
		<a href="#" onclick="<?= $projEstUrl ?>" title="Work with Project Yearly Estimates">
			<img src="<?= $popupIcon ?>" border="0" align="top" /> 
			Year Est.
		</a>
		&nbsp;
   </td>
	
	<td class="la">
		<a href="<?= $viewUrl ?>" 
				title="Display Project: <?php echo htmlspecialchars($row['PRJ_DESCRIPTION']); ?>">
            <?=$row['PRJ_DESCRIPTION'] ?>
		</a>
		&nbsp;
	</td>
	
	<td class="la">
		<?= $row['status_desc'] ?>	
		&nbsp;
   </td>
    
   <td class="la">
		<?= $row['PRJ_CONTACT_PERSON'] ?>	
		&nbsp;
   </td>
    
	<td class="ca">
		<?= $row['PRJ_FEASABILITY_NUM'] ?>
		&nbsp;
   </td>
   
   <td class="la">
		<?= $row['town_desc'] ?>
		&nbsp;
   </td>
	
	<td class="ca">
		<?php if ($row['wo_count'] > 0): ?>
		<a href="woListCtrl.php?filter_WO_STATUS=*not_cnl&filter_WO_PROJECT_NUM=<?=$row['PRJ_NUM']?>&popup=true" 
			target="new" style="text-decoration:underline">
		<?= $row['wo_count'] ?></a>
		<?php endif; ?>
		&nbsp;
   </td>
	
	<td class="ca">
		<?= $row['capexp_desc'] ?>
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
