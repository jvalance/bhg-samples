<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	WorkOrderForm $form,
	VGS_Navigator $nav 
) {
	showHeader($form->screen_title);
	$woNum = $form->getElement('WO_NUM')->getValue();
?>

<form method="post" name="form1">
<?php 
$nav->renderNavBar();
?>
<div id="output" class="inquiry_output" >

<?php 
writeWOJavaScripts();

$form->renderFormTop(); 

$woType = $form->getElement('WO_TYPE')->getValue();

switch ($woType) {
	case 'LM':
	case 'LS':
		showLeakFields($form);
		break;
	case 'MI':
	case 'TI':
	case 'MR':
	case 'MT':
	case 'MM':
	case 'NW':
	case 'MN':
		showMainFields($form);
		break;
	default:
		showDefaultFields($form);
		break;
}

$form->renderFormButtons(); 
?>
</form>
</center>

</div>

<?php
	showFooter();
}
//-----------------------------------------------------------------------
function showDefaultFields(WorkOrderForm $form) {
?>
	<table class="inquiry" width="100%">
	<tr>
		<td id="fg_r1_c1" width="50%" class="fg_column" valign="top">
		<?php 
		$form->renderFieldGroup('general');
		$form->renderFieldGroup('project');
		$form->renderFieldGroup('cost');
	
		if ($form->isRetireWO()) : 
			$form->renderFieldGroup('retire');
		endif;
		 
		if ($form->mode != 'create') : 
			$form->renderFieldGroup('maintenance');
		endif; 
		?>
		</td>
		<td id="fg_r1_c2" width="50%" class="fg_column" valign="top">
		<?php 
		if ($form->isServiceWO()) {
			$form->renderFieldGroup('premise');
			$form->renderFieldGroup('slsapp');
		}
		$form->renderFieldGroup('pipe');
		$form->renderFieldGroup('meter');
		$form->renderFieldGroup('spclinst');
		if ($form->getElement('WO_STATUS')->getValue() == Workorder_Master::WO_STATUS_PENDING) {
			$form->renderFieldGroup('pending');
		}
		$form->renderFieldGroup('digsafe');
		?>
		</td>
	</tr>
	
	</table>
<?php 	
}
//-----------------------------------------------------------------------
function showMainFields(WorkOrderForm $form) {
?>
	<table class="inquiry" width="100%">
	<tr>
		<td id="fg_r1_c1" width="50%" class="fg_column" valign="top">
		<?php 
		$form->renderFieldGroup('general');
		$form->renderFieldGroup('project');
		$form->renderFieldGroup('cost');
	
		if ($form->isRetireWO()) : 
			$form->renderFieldGroup('retire');
		endif;
		if ($form->mode != 'create') : 
			$form->renderFieldGroup('maintenance');
		endif; 
		?>
		</td>
		<td id="fg_r1_c2" width="50%" class="fg_column" valign="top">
		<?php 
		$form->renderFieldGroup('pipe');
		$form->renderFieldGroup('spclinst');
		if ($form->getElement('WO_STATUS')->getValue() == Workorder_Master::WO_STATUS_PENDING) {
			$form->renderFieldGroup('pending');
		}
		$form->renderFieldGroup('digsafe');
		?>
		</td>
	</tr>
	
	</table>
<?php 	
}

//-----------------------------------------------------------------------
function showLeakFields(WorkOrderForm $form) {
?>
	<table class="inquiry" width="100%">
	<tr>
		<td id="fg_r1_c1" width="50%" class="fg_column" valign="top">
		<?php 
		$form->renderFieldGroup('general');
		$form->renderFieldGroup('lkPers');
		$form->renderFieldGroup('lkRepairs');
		if ($form->mode != 'create') : 
			$form->renderFieldGroup('maintenance');
		endif; 
		?>
		</td>
		<td id="fg_r1_c2" width="50%" class="fg_column" valign="top">
		<?php 
		if ($form->isServiceWO()) {
			$form->renderFieldGroup('premise');
			$form->renderFieldGroup('slsapp');
		}
		$form->renderFieldGroup('leak');
		$form->renderFieldGroup('billing');
		$form->renderFieldGroup('gaslost');
		$form->renderFieldGroup('spclinst');
		if ($form->getElement('WO_STATUS')->getValue() == Workorder_Master::WO_STATUS_PENDING) {
			$form->renderFieldGroup('pending');
		}
		$form->renderFieldGroup('digsafe');
		?>
		</td>
	</tr>
	</table>
<?php 	
}

//-----------------------------------------------------------------------
function writeWOJavaScripts() {
?>
<style type="text/css">
/* This handles highlighting of the "Completion"  
   checkbox (COMPLETE_CB) if it is checked. */ 
table.field_group td.completeChecked { color:blue; font-weight:bold; }
</style>

<script type="text/javascript">
$('document').ready(function(){
	// This handles highlighting of the "Completion"  
	// checkbox (COMPLETE_CB) if it is checked. 
	// Test initial state on page load
// 11/10/2011 - JGV: Temporarily, we will always highlight the completion label.
// Sheila requested this, so that she will remember to do it.
// In the future this checkbox may go away and we will simply test for presence of 
// a completion date which will indicate w/o completion.
	$('#label_COMPLETE_CB').addClass('completeChecked');
//	if ($('#COMPLETE_CB').is(":checked")) {
//		$('#label_COMPLETE_CB').addClass('completeChecked');
//	}
//	// Test for checkbox change
//	$('#COMPLETE_CB').change(function(){
//		if ($(this).is(":checked")) {
//			$('#label_COMPLETE_CB').addClass('completeChecked');
//		} else {
//			$('#label_COMPLETE_CB').removeClass('completeChecked');
//		}
//	});

	// If leak class changed to 9, default billable to Y
	$('#LK_LEAK_CLASS').change(function(){
		if ($(this).val() == '9') {
			$('#LK_BILLABLE').attr('checked','checked');
		} 
	});

	// Recalc total man hours if hours or crew size change
	$('#LK_PERSONNEL_HOURS').change(function(){
		var totManHrs = calcTotalManHours();
		$('#LK_TOTAL_MAN_HOURS').val(totManHrs);
	});
	$('#LK_CREW_SIZE').change(function(){
		var totManHrs = calcTotalManHours();
		$('#LK_TOTAL_MAN_HOURS').val(totManHrs);
	});
})

function calcTotalManHours() {
	var persHours = $('#LK_PERSONNEL_HOURS').val();
	var crewSize = $('#LK_CREW_SIZE').val().trim();
	// Default crew size to 1 if blank or zero
	if (crewSize == '' || parseInt(crewSize) == 0) {
		$('#LK_CREW_SIZE').val('1');
		crewSize = 1;
	}
	return persHours * crewSize; 
}

function lookupPipeType() {
	var url = "ptSelectCtrl.php?codeField=WO_PIPE_TYPE&descField=desc_WO_PIPE_TYPE";
	var woType = $('#WO_TYPE').val(); 
	if (woType != '') url += '&WO_TYPE=' + woType; 
	var projNum = $('#WO_PROJECT_NUM').val(); 
	if (projNum != '') url += '&WO_PROJECT_NUM=' + projNum; 
	var name = "Select_Pipe_Type";
	var jsOpen = (window.open(url,name)); 
}

function lookupMainPipeType() {
	var url = "ptSelectCtrl.php?codeField=WO_MAIN_PIPE_TYPE&descField=desc_WO_MAIN_PIPE_TYPE";
	url += '&WO_TYPE=MI'; 
	var name = "Select_Main_Pipe_Type";
	var jsOpen = (window.open(url,name)); 
}

function lookupProject() {
	var url = "prjSelectCtrl.php?codeField=WO_PROJECT_NUM&descField=desc_WO_PROJECT_NUM";
	var name = "Select_Project";
	var jsOpen = (window.open(url,name)); 
}

function lookupCrew() {
	var url = "crewSelectCtrl.php?codeField=WO_CREW_ID&descField=desc_WO_CREW_ID";
	var name = "Select_Crew";
	var jsOpen = (window.open(url,name)); 
}

function lookupLeakCrew() {
	var url = "crewSelectCtrl.php?codeField=LK_CREW_ID&descField=desc_LK_CREW_ID";
	var name = "Select_Crew";
	var jsOpen = (window.open(url,name)); 
}

function openSecondaryWindow(url, name) { 
	var jsOpen = (window.open(url,name)); 
}

</script>
<?php 	
}
