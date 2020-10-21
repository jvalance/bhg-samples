<?php 
require_once 'layout.php';
require_once 'form.php';
require_once 'VGS_PaginatorViewHelper.php';
require_once '../forms/VGS_Search_Filter_Group.php';

function showScreen( 
	array &$screenData, 
	VGS_Paginator $paginator,
	VGS_Search_Filter_Group $filter,
	VGS_Navigator $nav
	) 
{
	showHeader('Work Order Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--
function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'woDownloadCtrl.php';
	document.searchForm.submit();
	document.searchForm.action = action;
	return false;
}
function doPrint() {
	var checkedCount = $("[id^=check_wo_]:checked").length;
	if (checkedCount == 0) {
		alert('You must select at least one work order to print.');
		return false;
	}
	var action = document.searchForm.action;
	document.searchForm.action = 'woPrintCtrl.php';
	document.searchForm.target = 'new';
	document.searchForm.submit();
	document.searchForm.action = action;
	document.searchForm.target = '';
	return false;
}
$('document').ready(function(){
	$('#checkAll').change(function(){
		if ($(this).is(':checked')) {
			$('[id^=check_wo_]').attr('checked',true);
		} else {
			$('[id^=check_wo_]').attr('checked',false);
		}
	})
})
//-->
</script>
<form name="searchForm" method="get" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >
<input type="hidden" name="autoPrintRecheck" value="N">
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
		<th class="ca" width="5%"> </th>
		<th class="ca" width="2%">
			<input type="checkbox" name="checkAll" id="checkAll">
		</th>
		<th class="ca" width="7%">WO#</th>
		<th class="la" width="20%">Description</th>
		<th class="ca" width="18%">Type</th>
		<th class="ca" width="9%">Status</th>
		<th class="ca" width="13%">Municipality</th>
		<th class="ca" width="10%">Entry Date</th>
		<th class="ca" width="2%"> </th>
	</tr>
	</thead>
	<tbody class="scrollable">
<?php
foreach ($screenData['rows'] as $wo) :
//var_dump($wo);
$rowClass = '';
switch ($wo['WO_STATUS']) {
	case Workorder_Master::WO_STATUS_PENDING: 
		$rowClass = "class='wo-pending'"; 
		break;
	case Workorder_Master::WO_STATUS_COMPLETED: 
		$rowClass = "class='wo-completed'"; 
		break;
	case Workorder_Master::WO_STATUS_CLOSED: 
		$rowClass = "class='wo-closed'"; 
		break;
	case Workorder_Master::WO_STATUS_CANCELLED: 
	case Workorder_Master::WO_STATUS_CANCEL_PENDING: 
		$rowClass = "class='wo-cancelled'"; 
		break;
}
$dollars = $wo['status_desc'] > '' ? 'dollars=Y' : 'dollars=N';

$woInq = "woEditCtrl.php?mode=inquiry&WO_NUM={$wo['WO_NUM']}&$dollars";
?>
<tr <?=$rowClass?> onmouseover="$(this).addClass('hover');"  onmouseout="$(this).removeClass('hover');" >
<!--	onclick="location.href='<?= $woInq ?>'"-->
	<td class="ca">
		<a href="<?= $woInq ?>" >
		<img src="../shared/images/view.gif" align="top" title="Display W/O" border=0 /> 
		</a>
		<?php 
		if ($wo['allow_wo_update']):
			?>
			<a href="woEditCtrl.php?mode=update&WO_NUM=<?= $wo['WO_NUM'] . "&$dollars" ?>" >
			<img src="../shared/images/edit.gif" align="top" title="Edit W/O" border=0 /> 
			</a>
			<?php 
		endif;
		?>
		<a href="wcnEditCtrl.php?WCN_WO_NUM=<?= $wo['WO_NUM'] ?>" >
		<img src="../shared/images/close.png" align="top" title="Cancel W/O" border=0 /> 
		</a>
	</td>
	<td class="ca">
		<!-- Print selection checkbox -->
		<input type="checkbox" 
				name="WO_NUM[<?= $wo['WO_NUM'] ?>]" 
				id="check_wo_<?= $wo['WO_NUM'] ?>" 
				value="<?= $wo['WO_NUM'] ?>">
	</td>

	<td class="ca">
		<a href="<?= $woInq ?>" >
			<?= $wo['WO_NUM'] ?>
		</a>
   </td>

  	<td class="la">
    	<a href="<?= $woInq ?>" >
    		<?= trim($wo['WO_DESCRIPTION']); ?>
    	</a>
		&nbsp;
    </td>
    
	<td class="la">
		<?= trim($wo['WO_TYPE']) ?> - 
		<?= trim($wo['WO_TYPE_DESC']) ?>
		<?= isset($wo['LK_LEAKWO_TYPE']) ?  "({$wo['LK_LEAKWO_TYPE']})" : '' ?>
		
		&nbsp;
	</td>
	<td class="la">
		<?= trim($wo['WO_STATUS_DESC']) ?>
		<?= trim($wo['status_desc']) ?>
		&nbsp;
    </td>
	<td class="la">
		<?= trim($wo['TOWN_NAME']) ?>
		&nbsp;
    </td>
	<td class="ra">
    	<?= date('M d, Y', strtotime($wo['WO_ENTRY_DATE'])); ?>
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
