<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	WO_CleanupForm $form, 
	VGS_Navigator $nav 
) {
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
	<td id="r1_c1" width="50%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('general');
	?>
	</td>
	<td id="r1_c2" width="50%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('comments');
	?>
	</td>
</tr>

<tr>
	<td id="r2_c1" width="50%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('estimates');
	?>
	</td>
	<td id="r2_c2" width="50%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('actuals');
	?>
	</td>
</tr>

<tr>
	<td id="r3_c1" width="50%" class="fg_column" valign="top">
	<?php 
	if ($form->mode != 'create') : 
		$form->renderFieldGroup('maintenance');
	endif; 
	?>
	</td>
	<td id="r3_c2" width="50%" class="fg_column" valign="top">
	&nbsp;
	<?php 
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
