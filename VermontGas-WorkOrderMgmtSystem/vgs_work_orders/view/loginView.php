<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen(array &$screenData) 
{
	showHeader("Login to Work Order System");
?>
<form method="post" name="form1">
<script type="text/javascript">
$('document').ready(function (){
	$('#pswd').val(''); // Ensure firefox clears the password!

	// Auto-upper case User ID
	$('#userid').blur(function(){
		var fldVal = $(this).val();
		fldVal = fldVal.toUpperCase();
		$(this).val(fldVal);
	});
})
</script>

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
<h3>Use the same user and password as you use to log in to the IBM AS/400 (iSeries/ECIS).</h3>
<table class="inquiry" width="50%">
	<tr>
		<td id="r1_c1" class="fg_column" width="50%" valign="top">

		<table id="fg_general" class="field_group fg_expanded"
			style="width: 100%; margin-top: 8px">

			<caption>Enter Credentials</caption>

			<tr>
				<td id="label_userid" class="field_label required">
					User ID
				</td>
				<td class="field_value" id="input_userid">
					<input type="text" name="userid" id="userid" value="<?= trim($screenData['userid']); ?>" size="12"> 
				</td>
			</tr>

			<tr>
				<td id="label_pswd" class="field_label  required "> 
					Password
				</td>
				<td class="field_value" id="input_pswd"> 
					<input type="password" name="pswd" id="pswd" value="<?= trim($screenData['pswd']); ?>" size="12"> 
				</td>
			</tr>

		</table>

		</td>
	</tr>
</table>

<input type="submit" class="button" value="Login" />

</form>

</div>


<?php
	showFooter();
}
