<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	WOCancellationsForm $form, 
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

<table class="inquiry" width="65%">
<tr>
	<td id="r1_c1" class="fg_column" width="100%" valign="top">
	<?php 
	$form->renderFieldGroup('wo');
	$form->renderFieldGroup('dollars');
	$form->renderFieldGroup('reason');
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
