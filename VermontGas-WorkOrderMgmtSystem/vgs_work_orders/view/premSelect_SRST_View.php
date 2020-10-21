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
	showHeader('Retire/Replace Services');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>

<script type="text/javascript">
<!--	
$('document').ready(function() {
	$('#filter_UPSAD').focus();

	$('#checkAll_ST').change(function(){
		if ($(this).is(':checked')) {
			$("[id^=ST_]").each(function (i) {
				$(this).attr('checked',true);
				toggleOn(this.id);
			});
		} else {
			$("[id^=ST_]").each(function (i) {
				$(this).attr('checked',false);
				toggleOff(this.id);
			});
		}
	})
	
	$('#checkAll_SR').change(function(){
		if ($(this).is(':checked')) {
			$("[id^=SR_]").each(function (i) {
				$(this).attr('checked',true);
				toggleOn(this.id);
			});
		} else {
			$("[id^=SR_]").each(function (i) {
				$(this).attr('checked',false);
				toggleOff(this.id);
			});
		}
	})
})

function doBatchEntry() {
	var url = 'premCreate_SRST_Ctrl.php';
	document.searchForm.action = url;
	document.searchForm.target = '_blank';
	document.searchForm.submit();
	// Restore default action
	document.searchForm.action = "<?= $_SERVER['SCRIPT_NAME'] ?>";
	return false;
}

function toggleSelection(checkBoxName) {
	var checkBoxId = '#' + checkBoxName;
	if ($(checkBoxId).is(':checked')) {
		toggleOn(checkBoxName);
	} else {
		toggleOff(checkBoxName);
	}
}

function toggleOn(checkBoxName) {
	//alert(checkBoxName);
	var cell_Id = '#cell_' + checkBoxName;
	$(cell_Id).addClass("prem-selected");
//	$(cell_Id).css({"font-weight":"bold"});
//	$(cell_Id).css({"background-color":"#FFFFAA"});
//	$(cell_Id).css({"border":"1px solid gray"});
}

function toggleOff(checkBoxName) {
	var cell_Id = '#cell_' + checkBoxName;
	$(cell_Id).removeClass("prem-selected");
//	$(cell_Id).css({"font-weight":"normal"});
//	$(cell_Id).css({"background-color":"white"});
//	$(cell_Id).css({"border":"0"});
}
function toggleCellSelect( woType, premiseNo ) {
	var cbName = woType + '_' + premiseNo;
	var cbId = '#' + cbName;
	var cb = $(cbId);
	cb[0].checked = !cb[0].checked;
	toggleSelection(cbName);
}

//-->
</script>
 
<form name="searchForm" method="get" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >

<input type="hidden" name="selections" />

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
		<th class="ca" width="6%">
			Retire <input type="checkbox" name="checkAll_ST" id="checkAll_ST">
		</th>
		<th class="ca" width="6%">
			Replace <input type="checkbox" name="checkAll_SR" id="checkAll_SR">
		</th>
		<th class="ca" width="7%">Premise#</th>
		<th class="ca" width="7%">Status</th>
		<th class="ca" width="20%">Service Address</th>
		<th class="ca" width="10%">City</th>
		<th class="ca" width="5%">SI W/O#</th>
		<th class="ca" width="15%">Pipe Type</th>
		<th class="ca" width="5%">Type</th>
	</tr>
	</thead>
	<tbody>
<?php
foreach ($screenData['rows'] as $row) :

$rowClass = '';
switch ($row['UPSTS']) {
	case 'IA':
		$rowClass = "class='premise-inactive'";
		break;
}
?>
<tr <?=$rowClass?> onmouseover="$(this).addClass('hover');"  onmouseout="$(this).removeClass('hover');" >
	<td class="ca" id="cell_ST_<?= $row['UPPRM'] ?>" onclick="toggleCellSelect('ST', <?= $row['UPPRM'] ?>)" >
		<!-- Create ST selection checkbox -->
		<input type="checkbox" 
				name="SRST[<?= $row['UPPRM'].'_ST' ?>]" 
				id="ST_<?= $row['UPPRM'] ?>" 
				value="ST"
				onclick="toggleCellSelect('ST', <?= $row['UPPRM'] ?>)" 
				onchange="toggleSelection(this.id);"
		> ST
	</td>
	
	<td class="ca" id="cell_SR_<?= $row['UPPRM'] ?>" onclick="toggleCellSelect('SR', <?= $row['UPPRM'] ?>)" >
		<!-- Create SR selection checkbox -->
		<input type="checkbox" 
				name="SRST[<?= $row['UPPRM'].'_SR' ?>]" 
				id="SR_<?= $row['UPPRM'] ?>" 
				value="SR"
				onclick="toggleCellSelect('SR', <?= $row['UPPRM'] ?>)"
				onchange="toggleSelection(this.id);"
		> SR
	</td>
	
	<td class="ca">
        <?= $row['UPPRM'] ?>
		&nbsp;
	</td>
	<td class="ca">
		<?= "{$row['UPSTS']} - {$row['PREM_STS']}" ?>	
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
	<td class="ca">
		<?php 
		$woUrl = "woEditCtrl.php?mode=inquiry&WO_NUM={$row['WO_NUM']}"; 
		?>
		<a href="#" onclick="openSecondaryWindow('<?= $woUrl ?>');" title="Display W/O" >
			<?= $row['WO_NUM'] ?>
		</a> 	
		&nbsp;
    </td>
	<td class="la">
		<?= $row['WO_PIPE_TYPE'] ?> - <?= $row['PT_DESCRIPTION'] ?>  
		&nbsp;
    </td>
	<td class="la">
		<?= $row['UPTYP'] ?>	
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

<script>
function openSecondaryWindow(url, name) { 
	var opts = "status=no,toolbar=no,location=no,menubar=no,scrollbars=yes,resizable=yes,directories=no";
	window.open(url, name, opts); 
}
</script>

<?php
	showFooter();
}
