<?php 
//require_once '../view/layout.php';
require_once 'layout.php';

function showScreen( 
	array $screenData, 
	VGS_Navigator $nav 
	) 
{
	showHeader('Create New Work Order');
?>
<form method="post" name="form1">
<input type="hidden" name="return_point" value="<?php echo $screenData['return_point'] ?>" />
<?php 
$nav->renderNavBar();
?>

<div id="output" class="inquiry_output">

<?php 
if ($screenData['error']) : ?>
<div id="form_err_msgs">
<h3>The following errors were found:</h3>
	<ul class="error">
	<?php 
	foreach ($screenData['messages'] as $message) :
		echo "<li>$message</li>\n";
	endforeach;
	?>
	</ul>
</div>
<?php 
endif;
?>

<table class="inquiry" width="50%">
<tr>
<td id="r1_c1" class="fg_column" width="50%" valign="top">

	<table id="fg_general" class="field_group fg_expanded"
		style="width: 100%; margin-top: 8px">
	
		<caption>New Work Order Parameters</caption>
	
		<tr>
			<td id="label_WO_TYPE" class="field_label required">W/O Type</td>
			<td class="field_value">
			
				<select name="WO_TYPE" id="WO_TYPE" >
				<option value="">-- Select W/O Type --</option>
					<option value="LM">Main Leak</option>
					<option value="LS">Service Leak</option>
				    <option value="MI">Main New Construction</option>
				    <option value="MM">Main Maintenance</option>
				    <option value="MN">Maintenance-Transmission</option>
				    <option value="MR">Main Replacement</option>
				    <option value="MT">Main Retirement</option>
				    <option value="NW">Non Work Order</option>
				    <option value="SB">Service Meter Barricade</option>
				    <option value="SI">Service New Construction</option>
				    <option value="SM">Service Maintenance</option>
				    <option value="SR">Service Replacement</option>
				    <option value="ST">Service Retirement</option>
				</select> 
		</tr>
	
		<tr id="tr_WO_PREMISE_NUM" class="hidden">
			<td id="label_WO_PREMISE_NUM" class="field_label required">
				Premise Num
			</td>
			<td class="field_value">
				<input type="text" name="WO_PREMISE_NUM" id="WO_PREMISE_NUM" size="15" value="<?= $screenData['WO_PREMISE_NUM'] ?>" /> 
				<a href="premSelectCtrl.php?codeField=WO_PREMISE_NUM&descField=desc_WO_PREMISE_NUM" target="new">
					<img src="../shared/images/search.gif" border="0"> Search
				</a> 
				<br />
				<span id="desc_WO_PREMISE_NUM" class="field_description"><?= $screenData['premAddr'] ?></span>
			</td>
		</tr>

	</table>

</td>
</tr>
</table>

<input type="submit" class="button" value="Create Work Order" />

</form>

</div>

<script type="text/javascript">
<!--
$('document').ready(function() {
	$('#WO_TYPE').val('<?= $screenData['WO_TYPE'] ?>');
	
	$('#WO_TYPE').change(function(){
		setPremiseVisibility($(this).val());
	})
	
	// Set initial visibility of Premise#
	setPremiseVisibility($('#WO_TYPE').val());
})
function setPremiseVisibility(woType) {
	if (woType == 'LS' || woType.substr(0,1) == 'S') {
		$('#tr_WO_PREMISE_NUM').show();
	} else {
		$('#tr_WO_PREMISE_NUM').hide();
	}
}
//-->
</script>

<?php
	showFooter();
}
