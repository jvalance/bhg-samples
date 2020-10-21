<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	ProjectForm $form, 
	VGS_Navigator $nav 
	) 
{
	showHeader($form->screen_title);
?>
<script type="text/javascript">
<!--
function openSecondaryWindow(url, name) { 
	var jsOpen = (window.open(url,name)); 
}
<?php 
if ($form->isCreateMode()) :
?>
	var nextFeasButtonHtml = '<button id="btnGetNextFeasNum" onclick="getNextFeasNumAjax();return false;">Get Next Feas#</button>';

	$('document').ready(function(){
		// Set onchange attribute for feasibility # to check if the feas# entered already exists
		$("#PRJ_FEASABILITY_NUM").change(getFeasibilityAjax);
		// Add button next to feasibility# to retrieve next feas#
		$("#desc_PRJ_FEASABILITY_NUM").html(nextFeasButtonHtml);
	});

	// Retrieve Feasibility record for feas. number entered to see
	// if it exists. Uses Ajax call to prjGetFeasibilityAjaxCtrl.php.
	// Data retrieved is handled by function callbackGetFeasRec().  
	// This is event handler for onchange of input PRJ_FEASABILITY_NUM.
	function getFeasibilityAjax() {
		var feasNum = $("#PRJ_FEASABILITY_NUM").val();
		var script = 'prjGetFeasibilityAjaxCtrl.php';
		var postData = { 'option': 'getFeasRec', feasNum: feasNum };
		//alert('script='+script + '\npost=' + postData.feasNum);
		$.post(script, postData, callbackGetFeasRec, 'json');
		return false;
	}
	// Callback for data returned by prjGetFeasibilityAjaxCtrl.php.
	function callbackGetFeasRec( returnData ) {
		//alert(returnData);
		var warningHTML = '<span style="color: darkred">Warning: existing feasability#</span>'; 
		if (!returnData.error) {
			$("#desc_PRJ_FEASABILITY_NUM").html(warningHTML + '&nbsp;' + nextFeasButtonHtml);
		} else {
			$("#desc_PRJ_FEASABILITY_NUM").html(nextFeasButtonHtml);
		}
	}
	
	// Retrieve next feasibility number from database. 
	// Uses Ajax call to prjGetFeasibilityAjaxCtrl.php.
	// Data retrieved is handled by function callbackGetNextFeasNum().  
	// This is event handler for onclick of button btnGetNextFeasNum.
	function getNextFeasNumAjax() {
		var script = 'prjGetFeasibilityAjaxCtrl.php';
		var postData = { 'option': 'getNextFeasNum' };
		$.post(script, postData, callbackGetNextFeasNum, 'json');
		return false;
	}
	
	function callbackGetNextFeasNum( returnData ) {
		//alert(returnData);
		if (returnData.error) {
			// If server error, show the error msg
			$("#desc_PRJ_FEASABILITY_NUM").html('<span style="color: red">' + returnData.error + '</span>');
		} else {
			// No error: update feas. num input value
			$("#PRJ_FEASABILITY_NUM").val(returnData.nextFeasNum);
			// remove any messages and redisplay button next to input field
			$("#desc_PRJ_FEASABILITY_NUM").html(nextFeasButtonHtml);
		}
	}
<?php 
endif;

$currDate = date('m-d-Y');
$datePlus3Years = date('m-d-Y', strtotime('+ 3 years'));
?>

function setProjectDates( ) {
	var projSts = $("#PRJ_STATUS").val();
	if (projSts == 'P') {
		var projectDate = $("#PRJ_PROJECT_DATE").val();
		var completionDate = $("#PRJ_COMPLETION_DATE").val();
		if ($.trim(projectDate) == '')	{
			$("#PRJ_PROJECT_DATE").val('<?=$currDate?>');
			setNavConfirm();
		}
		if ($.trim(completionDate) == '') {
			$("#PRJ_COMPLETION_DATE").val('<?=$datePlus3Years?>');
			setNavConfirm();
		}
	}
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
	<td id="r1_c1" class="fg_column" width="50%" valign="top">
	<?php 
	$form->renderFieldGroup('desc');
	$form->renderFieldGroup('general');
	$form->renderFieldGroup('misc');
	?>
	</td>
	
	<td id="r1_c2" class="fg_column" width="50%" valign="top">
	<?php 
	$form->renderFieldGroup('ROR');
	$form->renderFieldGroup('docs');
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
