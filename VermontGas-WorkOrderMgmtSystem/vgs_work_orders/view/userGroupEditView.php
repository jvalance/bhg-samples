<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	Group_User_Xref_Form $form, 
	VGS_Navigator $nav 
	) 
{
	showHeader($form->screen_title);
?>
<script type="text/javascript">
function lookupGroup() {
	var url = "profSelectCtrl.php?codeField=UG_GROUP_ID&descField=desc_UG_GROUP_ID&filter_PRF_PROFILE_TYPE=GROUP";
	var name = "Select_Group_Profile";
	var jsOpen = window.open(url,name); 
}
function lookupUser() {
	var url = "profSelectCtrl.php?codeField=UG_USER_ID&descField=desc_UG_USER_ID&filter_PRF_PROFILE_TYPE=USER";
	var name = "Select_User_Profile";
	var jsOpen = window.open(url,name); 
}
</script>

<form method="post" name="form1">
<?php 
$nav->renderNavBar();
?>

<div id="output" class="inquiry_output" >

<?php $form->renderFormTop(); ?>

<table class="inquiry" width="100%">
<tr>
	<td id="r1_c1" class="fg_column" width="50%" valign="top">
	<?php 
	$form->renderFieldGroup('user_group');
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
