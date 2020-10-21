<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	AuthoritiesForm $form, 
	VGS_Navigator $nav 
	) 
{
	showHeader($form->screen_title);
?>
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
	$form->renderFieldGroup('authority');
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
