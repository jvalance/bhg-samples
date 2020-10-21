<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	Auth_Profile_Xref_Form $form, 
	VGS_Navigator $nav 
	) 
{
	showHeader($form->screen_title);
?>

<script type="text/javascript">
function lookupAuthority() {
	var url = "authSelectCtrl.php?codeField=AP_AUTH_ID&descField=desc_AP_AUTH_ID";
	var name = "Select_Authority_Definition";
	var jsOpen = window.open(url,name); 
}
function lookupProfile() {
	var url = "profSelectCtrl.php?codeField=AP_PROFILE_ID&descField=desc_AP_PROFILE_ID";
	var name = "Select_Profile";
	var jsOpen = window.open(url,name); 
}
</script>

<form method="post" name="form1">
<?php 
$nav->renderNavBar();
?>

<div id="output" class="inquiry_output" >

<?php $form->renderFormTop(); ?>

<table class="inquiry" width="75%">
<tr>
	<td id="r1_c1" class="fg_column" width="50%" valign="top">
	<?php 
	$form->renderFieldGroup('auth_prof');
	if ( ! $form->isCreateMode() ) {
		$form->renderFieldGroup('maintenance');
	}
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
