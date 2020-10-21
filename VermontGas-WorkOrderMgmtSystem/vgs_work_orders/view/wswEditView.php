<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	WO_SewerForm $form, 
	VGS_Navigator $nav 
) {
	showHeader($form->screen_title);
?>
<script type="text/javascript">
<!--
function copyAddress() {
	var address = $('#desc_WSW_WO_NUM').text(); 
	address = $.trim(address);
	$('#WSW_ADDRESS').val(address);
	$('#WSW_ADDRESS').focus(); 
	return false;
}
//-->
</script>
<form method="post" name="form1">
<?php 
$nav->renderNavBar();
?>

<div id="output" class="inquiry_output" >

<?php $form->renderFormTop(); ?>

<table class="inquiry" width="100%">
<tr>
	<td id="r1_c1" width="50%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('wo');
	$form->renderFieldGroup('sewer');
	?>
	</td>
	<td id="r1_c2" width="50%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('postinsp');
	$form->renderFieldGroup('moc');
	$form->renderFieldGroup('notes');
	$form->renderFieldGroup('maintenance');
	?>
	</td>
</tr>

</table>

<?php $form->renderFormButtons(); ?>
</form>
</center>

</div>

<?php
	showFooter();
}
