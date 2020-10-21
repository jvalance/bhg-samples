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
	if (isset($screenData['filter_WONUM'])) {
		$woNum = $screenData['filter_WONUM'];
	}
	showHeader("Link Sales Apps to W/O# $woNum");
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--
$('document').ready(function(){
	// Check all add boxes
	$('#checkAllAdd').change(function(){
		if ($(this).is(':checked')) {
			$('[id^=link_sa_]').attr('checked',true);
		} else {
			$('[id^=link_sa_]').attr('checked',false);
		}
	})
	// Check all remove boxes
	$('#checkAllRmv').change(function(){
		if ($(this).is(':checked')) {
			$('[id^=unlink_sa_]').attr('checked',true);
		} else {
			$('[id^=unlink_sa_]').attr('checked',false);
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

.woHdrLabel { background-color: white;
    color: #19589C;
    font-family: verdana;
    font-size: 0.85em;
    font-weight: normal;
    padding: 3px 5px 3px 3px;
    text-align: right;
    vertical-align: top;
}
.woHdrValue {    
    background-color: white;
    color: navy;
    font-size: 0.85em;
    font-weight: bold;
    padding: 3px 3px 3px 5px;
    text-align: left;
}        
</style>

<form name="searchForm" method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >

<!--Hidden input fields-->
<input type="hidden" name="filter_WONUM" value="<?= $screenData['filter_WONUM'] ?>">
<input type="hidden" name="update_action" value="">
<input type="hidden" name="return_point" value="<?= $screenData['return_point'] ?>" />

<!--Work Order information to display at top of page-->
<table width="100%"> <tr>
<td class="woHdrLabel">W/O Description: </td>
<td class="woHdrValue"><?= trim($screenData['woRec']['WO_DESCRIPTION']) . ', ' . trim($screenData['woRec']['WO_TOWN_NAME']) ?></td>

<td class="woHdrLabel">W/O Type: </td>
<td class="woHdrValue"><?= trim($screenData['woRec']['WO_TYPE_DESC']) ?></td>

<td class="woHdrLabel">Project: </td>
<td class="woHdrValue"><?= trim($screenData['woRec']['WO_PROJECT_NUM']) . ' - ' . trim($screenData['woRec']['PROJECT_DESC']) ?></td>
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
		<th class="ca" width="5%"> </th>
		<th class="ca" width="2%">
			Add<br>
			<input type="checkbox" name="checkAll" id="checkAllAdd">
		</th>
		<th class="ca" width="2%">
			Rmv<br>
			<input type="checkbox" name="checkAll" id="checkAllRmv">
		</th>
		<th class="ca" width="7%">SlsApp#</th>
		<th class="ca" width="7%">W/O#</th>
		<th class="ca" width="7%">Premise#</th>
		<th class="ca" width="23%">Street Address</th>
		<th class="ca" width="15%">City</th>
		<th class="ca" width="5%">App<br>Type</th>
		<th class="ca" width="5%">App<br>Status</th>
		<th class="ca" width="5%">SlsApp<br>WO Sts</th>
		<th class="ca" width="12%">Completion<br>Date</th>
		<th class="ca" width="5%">SlsMan</th>
	</tr>
<?php

foreach ($screenData['rows'] as $row) :

$class = ($row['SLSWO#'] == $woNum) ? 'linked' : '';	
$checked = ($row['SLSWO#'] == $woNum) ? 'checked="checked"' : '';	

?>
<tr class="<?= $class ?>" >
	<td class="ca">
		<a href="slsDetailCtrl.php?mode=inquiry&SLSAP#=<?= $row['SLSAP#'] ?>" >
		<img src="../shared/images/view.gif" align="top" title="Display Sales Application Detail" border=0 /> 
		</a>
	</td>

	<td class="ca">
		<!-- Link SLSAPP checkbox -->
		<?php if ($row['SLSWO#'] != $woNum) : ?>
		<input type="checkbox" 
				name="link_sa[<?= $row['SLSAP#'] ?>]" 
				id="link_sa_<?= $row['SLSAP#'] ?>" 
				value="<?= $row['SLSAP#'] ?>" >
		<?php endif; ?>
		&nbsp;
	</td>

	<td class="ca">
		<!-- Un-Link SLSAPP checkbox -->
		<?php if ($row['SLSWO#'] == $woNum) : ?>
		<input type="checkbox" 
				name="unlink_sa[<?= $row['SLSAP#'] ?>]" 
				id="unlink_sa_<?= $row['SLSAP#'] ?>" 
				value="<?= $row['SLSAP#'] ?>" >
		<?php endif; ?>
		&nbsp;
	</td>

	<td class="ca">
<!--		<a href="wcEditCtrl.php?mode=inquiry&WC_WONUM=< ?= //$row['WC_WONUM'] ?>&WC_CLEANUP_NUM=< ?= //$row['WC_CLEANUP_NUM'] ?>" -->
<!--			title="Display W/O Clean Up">-->
		<?= $row['SLSAP#'] ?>
		</a>
    </td>

	<td class="ca">
		<?= $row['SLSWO#'] ?>
		&nbsp;
    </td>

	<td class="ca">
		<?= trim($row['SLSBKF']) ?>
		&nbsp;
	</td>

	<td class="la">
		<?= trim($row['UPSAD']) ?>
		&nbsp;
    </td>

	<td class="la">
		<?= trim($row['UPCTC']) ?>
		&nbsp;
    </td>

	<td class="ca">
		<?= $row['SLSTYP'] ?>
		&nbsp;
    </td>

	<td class="ca">
		<?= $row['STATUS'] ?>
		&nbsp;
    </td>

	<td class="ca">
		<?= $row['SLSWOS'] ?>
		&nbsp;
    </td>

	<td class="ca">
		<?= $row['completionDate'] ?>
		&nbsp;
    </td>

	<td class="ca">
		<?= $row['SLSSMN'] ?>
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
