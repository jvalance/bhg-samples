<?php 
require_once 'layout.php';

function showScreen(LoginForm &$form) {
showHeader("Login to Work Order System");
?>
<form method="post" name="form1">

<div id="output" class="inquiry_output" >

<?php $form->renderFormTop(); ?>

<table class="inquiry" width="100%">
<tr>
	<td id="r1_c1" class="fg_column" width="50%" valign="top">
	<?php 
	$form->renderFieldGroup('login');
	?>
	</td>
	
</tr>

</table>

<?php $form->renderFormButtons(); ?>
</form>

</div>

<?php
	showFooter();
}
