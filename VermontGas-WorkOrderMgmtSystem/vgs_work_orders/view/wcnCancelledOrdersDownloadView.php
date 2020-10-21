<?php 
function showScreen( 
	array $screenData, 
	VGS_Navigator $nav 
	) 
{
	showHeader('Cancelled Work Orders Report');
?>
<form method="post" name="form1" action="wcnCancelledOrdersDownloadCtrl.php">
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
	
		<caption>Report Parameters</caption>
	
		<tr>
			<td class="field_label">
				From Date
			</td>
			<td class="field_value">
				<input type="text" name="fromDate" id="fromDate" class="datepicker"
						 size="10" value="<?= $screenData['fromDate'] ?>" /> 
				<br />
			</td>
		</tr>
	
		<tr>
			<td class="field_label">
				To Date
			</td>
			<td class="field_value">
				<input type="text" name="toDate" id="toDate"  class="datepicker"
						 size="10" value="<?= $screenData['toDate'] ?>" /> 
				<br />
			</td>
		</tr>

	
		<tr>
			<td class="field_label">Cancellation Status</td>
			<td class="field_value">
				<select name="cancel_status" id="cancel_status">
					<option value='   '> </option>
					<option value='CNLCMP'>Cancelled</option>
					<option value='CNLPND'>Cancel pending</option>
				</select>
				<script type="text/javascript">
					document.form1.cancel_status.value='<?= $screenData['cancel_status'] ?>';
				</script>
		</tr>
	</table>

</td>
</tr>
</table>

<input type="submit" class="button" name="btnSubmit" value="Download CSV" />

</form>

</div>
<script type="text/javascript">
	<!--
	$('document').ready(function() {
		// Set up defaults for date-pickers
		$.datepicker.setDefaults({
			dateFormat: 'yy-mm-dd', 
			buttonImage: '../shared/images/datepicker.gif',
			changeMonth: true,
			changeYear: true,
			showOn: 'button',
			buttonImageOnly: true, 
			buttonText: 'Calendar'});
		$('#fromDate').datepicker();
		$('#toDate').datepicker();
	});
	
	//-->
</script>

<?php
	showFooter();
}
