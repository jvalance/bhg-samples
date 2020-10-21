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
	if (isset($screenData['filter_SV_SERVICE_ID'])) {
		$svID = $screenData['filter_SV_SERVICE_ID'];
	}
	showHeader("Link Premises to Service ID $svID");
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--
$('document').ready(function(){
	// Check all add boxes
	$('#checkAllAdd').change(function(){
		if ($(this).is(':checked')) {
			$('[id^=link_pr_]').attr('checked',true);
		} else {
			$('[id^=link_pr_]').attr('checked',false);
		}
	})
	// Check all remove boxes
	$('#checkAllRmv').change(function(){
		if ($(this).is(':checked')) {
			$('[id^=unlink_pr_]').attr('checked',true);
		} else {
			$('[id^=unlink_pr_]').attr('checked',false);
		}
	})
})

// Save changes button action
function doSave() {
	document.searchForm.update_action.value = 'save';
	document.searchForm.submit();
}

// Cancel changes button action
function doCancel() {
	document.searchForm.update_action.value = 'load';
	document.searchForm.submit();
}

//-->
</script>

<style>
tr.linked td { color: brown; background-color: #FFFFAA; font-weight: bold }

.svHdrLabel { background-color: white;
    color: #19589C;
    font-family: verdana;
    font-size: 0.85em;
    font-weight: normal;
    padding: 3px 5px 3px 3px;
    text-align: right;
    vertical-align: top;
}
.svHdrValue {    
    background-color: white;
    color: navy;
    font-size: 0.85em;
    font-weight: bold;
    padding: 3px 3px 3px 5px;
    text-align: left;
}        
</style>


<form name="searchForm" method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >

<!--http://workorders.vermontgas.com/wodev-ik/shared/images/vtg_logo2.gifHidden input fields-->
<input type="hidden" name="filter_SV_SERVICE_ID" value="<?= $screenData['filter_SV_SERVICE_ID'] ?>">
<input type="hidden" name="update_action" value="">
<input type="hidden" name="return_point" value="<?= $screenData['return_point'] ?>" />

<!--Service information to display at top of page-->
<table width="100%"> <tr>

<td class="svHdrLabel">Service ID: </td>
<td class="svHdrValue"><?= trim($screenData['svRec']['SV_SERVICE_ID']) . 
						   (trim($screenData['svRec']['SV_NAME']) != '' ? ' - ' : '') .
						   trim($screenData['svRec']['SV_NAME']) ?></td>

<td class="svHdrLabel">Address: </td>
<td class="svHdrValue"><?= trim($screenData['svRec']['SV_HOUSE']) . ' ' . 
						   trim($screenData['svRec']['SV_STREET']) . ', ' . 
						   trim($screenData['svRec']['SV_TOWN_NAME']) ?></td>

<td class="svHdrLabel">Status: </td>
<td class="svHdrValue"><?= trim($screenData['svRec']['SV_SVC_STATUS_DESC']) ?></td>

</tr></table>

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
		<th class="ca" width="4%"> </th>
		<th class="ca" width="3%">
			Add<br>
			<input type="checkbox" name="checkAll" id="checkAllAdd">
		</th>
		<th class="ca" width="3%">
			Rmv<br>
			<input type="checkbox" name="checkAll" id="checkAllRmv">
		</th>
		<th class="ca" width="5%">Premise#</th>
		<th class="ca" width="7%">Service ID #</th>
		<th class="ca" width="7%">Status</th>
		<th class="ca" width="20%">Service Address</th>
		<th class="ca" width="10%">City</th>
		<th class="ca" width="20%">Addt'l Description</th>
		<th class="ca" width="8%">Prem Type</th>
		<th class="ca" width="8%">Dwelling</th>
		

	</tr>
<?php


foreach ($screenData['rows'] as $row) :

$class = ($row['SPX_SERVICE_ID'] == $svID) ? 'linked' : '';	
$checked = ($row['SPX_SERVICE_ID'] == $svID) ? 'checked="checked"' : '';	

?>
<tr class="<?= $class ?>" >
	<td class="ca">
		<a href="prmDetailCtrl.php?mode=inquiry&UPPRM=<?= $row['UPPRM'] ?>" >
		<img src="../shared/images/view.gif" align="top" title="Display Premise Detail" border=0 /> 
		</a>
	</td>

	<td class="ca">
		<!-- Link PREMISE checkbox -->
		<?php if (isBlankOrZero($row['SPX_SERVICE_ID'])) : ?>
		<input type="checkbox" 
				name="link_pr[<?= $row['UPPRM'] ?>]" 
				id="link_pr_<?= $row['UPPRM'] ?>" 
				value="<?= $row['UPPRM'] ?>" >
		<?php endif; ?>
		&nbsp;
	</td>

	<td class="ca">
		<!-- Un-Link PREMISE checkbox -->
		<?php if ($row['SPX_SERVICE_ID'] == $svID) : ?>
		<input type="checkbox" 
				name="unlink_pr[<?= $row['UPPRM'] ?>]" 
				id="unlink_pr_<?= $row['UPPRM'] ?>" 
				value="<?= $row['UPPRM'] ?>" >
		<?php endif; ?>
		&nbsp;
	</td>
    
    
    <td class="ca">
        <?= $row['UPPRM'] ?>
		&nbsp;
	</td>
	<td class="ca">
		<?= $row['SPX_SERVICE_ID'] ?>
		&nbsp;
    </td>
	<td class="ca">
		<?= $row['UPSTS'] ?>	
		&nbsp;
    </td>
	<td class="la">
        <?= $row['UPSAD'] ?>
		&nbsp;
	</td>
	<td class="la">
		<?= $row['UPCTC'] ?>	
		&nbsp;
    </td>
	<td class="la">
		<?= $row['UPADL'] ?>	
		&nbsp;
    </td>
	<td class="ca">
		<?= $row['UPTYP'] ?>	
		&nbsp;
    </td>
	<td class="ca">
		<?= $row['UPDWC'] ?>
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
	
// 	pre_dump($screenData);
}