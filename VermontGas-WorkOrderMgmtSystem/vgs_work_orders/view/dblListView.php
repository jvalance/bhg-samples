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
	showHeader('Database Update Log');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--
function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'dblDownloadCtrl.php';
	document.searchForm.submit();
	document.searchForm.action = action;
	return false;
}
//-->
</script>
<style>
table.subtable {
	width: 100%;
	
	color: blue;
	border:0; 
	font-size: 10pt;
}
table.subtable td {
	border:0; 
}
</style>
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
<!-- 		<th class="ca" width="2%"> </th> -->
		<th class="la" width="25%">Table/Field</th>
		<th class="la" width="25%">Key Fields</th>
		<th class="ca" width="10%">User</th>
		<th class="ca" width="15%">Date/Time</th>
		<th class="ca" width="10%">Action</th>
	</tr>
<?php


foreach ($screenData['rows'] as $dblRec) :
	// Add popup flag on link URLs if passed on request
// 	$popup = $_REQUEST['popup'];
// 	$linkUrl = "dblEditCtrl.php?DBL_TABLE_NAME={$dblRec['DBL_TABLE_NAME']}&DBL_KEY_FIELDS={$dblRec['DBL_KEY_FIELDS']}&DBL_REC_SEQ={$dblRec['DBL_REC_SEQ']}&popup=$popup";
// 	$inquiryUrl = $linkUrl . '&mode=inquiry';
// 	$inquiryTitle = "Display DB Update Log Entry";
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';" style="background-color: beige;">
<!--<td class="ca">
		<a href="<?//= $inquiryUrl ?>" > 
			<img src="../shared/images/view.gif" title="<?//= $inquiryTitle ?>" align="top" border=0 /> 
 		</a> 
	</td>-->
	
	<td class="la">
		<?= $dblRec['DBL_TABLE_NAME'] ?>: <br /> <?= $dblRec['DBL_FIELD_CHANGED'] ?>
		&nbsp;
    </td>

	<td class="la">
		<?= trim($dblRec['keys']) ?>
		&nbsp;
	</td>
 
	<td class="la"> 
		<?= $dblRec['DBL_UPD_USER'] ?>
		&nbsp;
    </td>
    
	<td class="ca">
		<?= $dblRec['DBL_DATE'] ?> at <?= $dblRec['DBL_TIME'] ?> 
		&nbsp;
    </td> 
    	
	<td class="ca">
		<?= trim($dblRec['DBL_ACTION']) ?>
		&nbsp;
    </td>
    
</tr>

<tr>
	<td colspan="7">
		<table class="subtable">
		<tr>
		<td class="la" width="10%"> 
			Value Before:
	    </td>
		<td class="la" width="90%"> 
			<b><?= $dblRec['DBL_VALUE_BEFORE'] ?>&nbsp;</b>
	    </td>
		</tr>
	    <tr>
	    <td class="la" width="10%"> 
	    	Value After:
	    </td>
		<td class="la" width="90%"> 
	    	<b><?= $dblRec['DBL_VALUE_AFTER'] ?></b>
			&nbsp;
	    </td>
	    </tr>
	    </table>
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
