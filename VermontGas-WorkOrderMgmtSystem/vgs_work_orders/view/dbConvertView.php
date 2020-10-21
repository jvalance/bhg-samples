<?php 
require_once 'layout.php';

function showScreen($message) {
	showHeader("Refresh Database");
	?>
	<form method="post" name="form1">
	
	<div id="navbar">
	<p>
	
	</div>
	
	<div id="output" class="inquiry_output">
	<center>

	<table id="fg_general" class="field_group fg_expanded"
		style="width: 50%; margin-top: 8px">
	
		<caption>Submit Conversion Messages</caption>
	
		<tr>
			<td class="field_label" style="width:25%">
				Message
			</td>
			<td class="field_value" style="width:75%; font-size:12pt">
				<?= $message ?>
			</td>
		</tr>

	</table>
	
	
	<input type="button" class="button" value="Main Menu" 
		onclick="document.location.href='menuMainCtrl.php';" />
	
	
	</center>
	</div>
	
	</form>
	
	<?php
	showFooter ();
}
