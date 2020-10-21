<?php 
require_once '../view/layout.php';

showHeader('Test Get Drop Down List Ajax');
?>
<form method="post" name="form1" action="<?= $_SERVER['SCRIPT_NAME']; ?>">


<div id="output" class="inquiry_output">


<table class="inquiry" width="50%">
<tr>
<td id="r1_c1" class="fg_column" width="50%" valign="top">

	<table id="fg_general" class="field_group fg_expanded"
		style="width: 100%; margin-top: 8px">
	
		<caption>Parameters</caption>
	
		<tr>
			<td class="field_label required">Drop Down List Code: </td>
			<td class="field_value">
				<input type="text" name="ddCode" id="ddCode" 
						 size="15" value="<?= $screenData['ddCode'] ?>" /> 
		</tr>
	
		<tr>
			<td class="field_label required">Returned list: </td>
			<td class="field_value">
				<select name="dropDown" id="dropDown">
				</select> 
		</tr>
		
		<tr>
			<td class="field_label required">
				Submit:
			</td>
			<td class="field_value">
				<input type="button" name="sbm" id="sbm" 
					value="Get Drop Downs" onclick="processButton()" /> 
			</td>
		</tr>
	
	</table>

</td>
</tr>
</table>

</form>

</div>
<script type="text/javascript">
	function processButton() {
		var ddcode = $('#ddCode').val();
		alert('ddcode = ' + ddcode);
		getDropDownList(ddcode, 'dropDown');
	}

	<!--
	$('document').ready(function() {

	});
	
	//-->
</script>

<?php
	showFooter();
