<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	WO_ElectrofusionForm $form, 
	VGS_Navigator $nav 
) {
	showHeader($form->screen_title);
?>
<script type="text/javascript">
<!--
function copyAddress() {
	var address = $('#desc_WEF_WO_NUM').text().trim(); 
// 	address = $.trim(address);
	$('#WEF_DESCRIPTION').val(address);
	$('#WEF_DESCRIPTION').focus(); 
	return false;
}

function lookupCrew() {
	var url = "crewSelectCtrl.php?codeField=WEF_COMPLETED_BY&descField=desc_WEF_COMPLETED_BY";
	var name = "Select_Crew";
	var jsOpen = (window.open(url,name)); 
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
	$form->renderFieldGroup('electrofusion');
	?>
	</td>
	<td id="r1_c2" width="50%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('description');
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
