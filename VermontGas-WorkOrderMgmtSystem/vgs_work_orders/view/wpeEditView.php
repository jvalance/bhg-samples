<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	WO_Pipe_ExposureForm $form, 
	VGS_Navigator $nav 
) {
	showHeader($form->screen_title);
?>
<script type="text/javascript">
// Define variables to copy from W/O if this is primary pipe exposure 
var woPipeMatrl = '<?= $form->wo_record['WO_PIPE_MATERIAL'] ?>';
var woPipeSize = '<?= $form->wo_record['WO_PIPE_SIZE'] ?>';
var woPipeCoating = '<?= $form->wo_record['WO_COATING_TYPE'] ?>';
var woType = '<?= $form->wo_record['WO_TYPE'] ?>';

function lookupCrew() {
	var url = "crewSelectCtrl.php?codeField=WPE_INSPECTOR_CLOCK&descField=desc_WPE_INSPECTOR_CLOCK";
	var name = "Select_Crew";
	var jsOpen = (window.open(url,name)); 
}
$('document').ready(function (){
	// Set onChange for Primary indicator to auto-load WO pipe info
	$('#WPE_PRIMARY_WOEXP').change(changePrimaryDesignation);
	// Run trigger function on page load to determine input capability of pipe fields
	changePrimaryDesignation();
});
//--------------------------------------------------------------------------------------
// changePrimaryDesignation()
// This function is called onChange of the select list for "Exposure for WO primary pipe?"
// If primary is Yes:
//		- Populate pipe details from values on the WO record.
// 		- Make pipe fields protected and tabindex = -1 (tabover). 
// If primary is No:
//		- Just make pipe fields input capable.
function changePrimaryDesignation( ) {
	primaryPipe = $('#WPE_PRIMARY_WOEXP').val();
	if (primaryPipe == 'Y') {
		$('#WPE_PIPE_COMPOSITION').val(woPipeMatrl);
		$('#WPE_PIPE_SIZE').val(woPipeSize);
		$('#WPE_PIPE_COATING').val(woPipeCoating);
		$('#WPE_DESIGNATION').val(getDesignation(woType));

		$('#WPE_PIPE_COMPOSITION').attr("readonly", "readonly");
		$('#WPE_PIPE_SIZE').attr("readonly", "readonly");
		$('#WPE_PIPE_COATING').attr("readonly", "readonly");

		$('#WPE_PIPE_COMPOSITION').attr("tabindex", "-1");
		$('#WPE_PIPE_SIZE').attr("tabindex", "-1");
		$('#WPE_PIPE_COATING').attr("tabindex", "-1");
		
		$('#WPE_PIPE_COMPOSITION').addClass("disabled");
		$('#WPE_PIPE_SIZE').addClass("disabled");
		$('#WPE_PIPE_COATING').addClass("disabled");

	} else {
		$('#WPE_PIPE_COMPOSITION').removeAttr("readonly");
		$('#WPE_PIPE_SIZE').removeAttr("readonly");
		$('#WPE_PIPE_COATING').removeAttr("readonly");

		$('#WPE_PIPE_COMPOSITION').removeAttr("tabindex");
		$('#WPE_PIPE_SIZE').removeAttr("tabindex");
		$('#WPE_PIPE_COATING').removeAttr("tabindex");
		
		$('#WPE_PIPE_COMPOSITION').removeClass("disabled");
		$('#WPE_PIPE_SIZE').removeClass("disabled");
		$('#WPE_PIPE_COATING').removeClass("disabled");
	}
}

function getDesignation( woType ) {
	var desig = '';
	switch (woType) {
		case 'LM':;
		case 'MI':;
		case 'MM':;
		case 'MR':;
		case 'MT':
			// Distribution (i.e. Main)
			desig = 'D'; 
		break;
		
		case 'LS':;
		case 'SI':;
		case 'SM':;
		case 'SR':;
		case 'ST':
			// Service
			desig = 'S';
		break;

		case 'MN':
			// Transmission
			desig = 'T';
		break;
	}
	return desig;
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
	<td id="r1_c1" width="50%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('general');
	$form->renderFieldGroup('comments');
	if ($form->mode != 'create') : 
		$form->renderFieldGroup('maintenance');
	endif; 
	?>
	</td>
	<td id="r1_c2" width="50%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('exposure');
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
