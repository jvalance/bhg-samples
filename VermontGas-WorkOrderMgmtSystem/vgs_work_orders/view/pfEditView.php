<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';
 
function showScreen( 
	ProjectPipeFtgForm $form, 
	VGS_Navigator $nav 
	) 
{
	showHeader($form->screen_title);
?>
<script type="text/javascript">
<!--

function lookupPipeType() {
	var url = "ptSelectCtrl.php?codeField=PF_PIPE_TYPE&descField=desc_PF_PIPE_TYPE";
	var woType = $('#WO_TYPE').val(); 
	if (woType != '') url += '&WO_TYPE=' + woType; 
	var projNum = $('#PF_PRJ_NUM').val(); 
	if (projNum != '') url += '&WO_PROJECT_NUM=' + projNum; 
	var name = "Select_Pipe_Type";
	var jsOpen = (window.open(url,name)); 
}

//-->
</script>
<form method="post" name="form1">
<?php 
$nav->renderNavBar();
?>

<div id="output" class="inquiry_output" >

<?php 
$form->renderFormTop();
?>

<table class="inquiry" width="100%">
<tr>
	<td id="r1_c1" class="fg_column" width="50%" valign="top">
	<?php 
	$form->renderFieldGroup('keys');
	$form->renderFieldGroup('estimates');
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
