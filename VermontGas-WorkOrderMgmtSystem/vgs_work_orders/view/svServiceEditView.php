<?php 
require_once 'layout.php';
require_once 'form.php';
require_once '../forms/VGS_FormHelper.php';

function showScreen( 
	ServicesForm $form, 
	VGS_Navigator $nav 
) {
	showHeader($form->screen_title);
?>
<script type="text/javascript">
function reloadFormat() {
	$('#reload').val('Y');
	doSubmit('update');
}

//*******
//Ajax code to fill new service address from premise number
//*******
function fillPremAddr() {

	function getFirstNum(list) {
		var numArray = new Array();
		numArray = list.split(',');
		var num = numArray[0];
		return num;
	}

	var premNums = document.getElementById('SPX_PREMISE_NUMS').value;
	var premNum = getFirstNum(premNums);

	getPremAddr(premNum);

	return false;
}

//Make ajax request to server using method="post"
function getPremAddr( premNo ) {
	var script = 'svGetPremAddr.php';
	var postData = { premNo: premNo };
	//	alert(postData.premNo);
	$.post(script, postData, callbackFillPremAddr, 'json');
	return false;
}
 
//This is called when Ajax response is received from server 
function callbackFillPremAddr( returnVals ) {
	//	alert(returnVals.selectList);
	
	if (returnVals.error) {
			alert(returnVals.error);
		return false;

	} else {
		$("#SV_HOUSE").val(returnVals.HOUSE);
		$("#SV_STREET").val(returnVals.STREET);
		$("#SV_CITY").val(returnVals.CITY_CODE);
		$("#SV_STREET").focus();
	}

//	alert(JSON.stringify(returnVals));
}

//*******
//Show pop-up window with Google map of address entered
//*******

function showMap() {
	var house = document.getElementById("SV_HOUSE").value;
	var street = document.getElementById("SV_STREET").value;
	var towns = document.getElementById("SV_CITY");
	var town = towns.options[towns.selectedIndex].label;
	var state = document.getElementById("SV_STATE").value;

	var mapAddress = house + " " + street + " " + town + " " + state;
	
	var mapLink = 'http://maps.google.com/maps?hl=en&z=15&output=embed&iwd=1&q=' + escape(mapAddress.trim());

	myRef = window.open(mapLink,'mapwin', 'left=20,top=20,width=1000,height=650,toolbar=yes,resizable=yes,location=yes'); 
	myRef.focus(); 
	
	return false;
}

//*******
//Display mini-table of service addresses
//*******
function showPremAddrs() {
	if ($('#premAddrs').is(":hidden")) {
		var premNos = document.getElementById('SPX_PREMISE_NUMS').value;
		var script = 'svGetPremAddrs.php';
		var postData = { premNos: premNos, targetID: null };
		$.post(script, postData, callbackGetPremAddrs, 'json');
	} else {
		$('#premAddrs').slideUp();
	}

	return false;
}

//This is called when Ajax response is received from server 
function callbackGetPremAddrs( returnJSON ) {
	var returnObj = eval(returnJSON);
	var targetID = returnObj.targetID;
	var html = returnObj.html;
	$('#premAddrs').empty();
	$('#premAddrs').append(html);
	$('#premAddrs').slideDown();
	var target = $('#input_SPX_PREMISE_NUMS textarea').position();
	var width = $('#input_SPX_PREMISE_NUMS textarea').width();
	// Put table next to main div if textarea is disabled (inquiry view)
	if (target.top == 0 || target.left == 0) {
		target = $('#input_SPX_PREMISE_NUMS').offset();
		width  = $('#input_SPX_PREMISE_NUMS').width();
	}
	$('#premAddrs').offset({ top: target.top, left: (target.left + width) });
}



</script>

<?php 

// get the entry format
$entryFormat = $form->entryFormat;
	
?>

<form method="post" name="form1">

<input type="hidden" name="reload" id="reload" value="" />

<?php 
$nav->renderNavBar();
?>

<div id="output" class="inquiry_output" >

<?php $form->renderFormTop(); ?>

<?php
if (isset($_SESSION['SERVICE_SELECTED_IDS'])) {
	$serviceList = array_keys($_SESSION['SERVICE_SELECTED_IDS']);
	$servicesLeft = array_slice($serviceList, $_SESSION['BATCH_COUNTER']);
	echo '<div style="text-align: left; color:green">' . 
			count($servicesLeft) . ' Services left to edit: ' .
			implode(', ', $servicesLeft) .
			'</div>';
}
?>

<table class="inquiry" width="100%">
<?php

// added switch statement for format selection - 2013-12-05 - JAF
switch ($entryFormat)
{ // begin switch entry format
	case 0: // display all fields
?>
<tr> 
	<td id="r1_c1" width="30%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('keys');
	$form->renderFieldGroup('loc');
	$form->renderFieldGroup('pipe');
	?>
	</td>
	
	<td id="r1_c2" width="30%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('conn');
	$form->renderFieldGroup('method');
	?>
	</td>
	
	<td id="r1_c3" width="30%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('completion');
	$form->renderFieldGroup('remarks');
	$form->renderFieldGroup('main');
	if ($form->mode != 'create') : 
		$form->renderFieldGroup('maintenance');
	endif; 
	?>
	</td>
</tr>

<?php 
	break; // end of entry format 0
	
case 1: // display format 1
	
?>

<tr> <!-- Added table row for displaying format 1 -->
	<td id="r1_c1" width="30%" class="fg_column" valign="top">
	<?php
	$form->renderFieldGroup('fmt1Keys'); // added to test fmt1 elements - JAF
	$form->renderFieldGroup('fmt1Srv'); // added to test fmt1 elements - JAF
	?>
	</td>
	<td id="r1_c2" width="30%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('fmt1Main'); // added to test fmt1 elements - JAF
	$form->renderFieldGroup('stats'); // added to test status elements - JAF

	if ($form->mode != 'create') :
	$form->renderFieldGroup('maintenance');
	endif;
	
	?>
	</td>
</tr> <!-- End of format 1 table row -->

<?php 
	break; // end of entry format 1
	
case 2: // display format 2
?>

<tr> <!-- Added table row for displaying format 2 -->
	<td id="r1_c1" width="30%" class="fg_column" valign="top">
	<?php
	$form->renderFieldGroup('fmt2Keys'); // added to test fmt2 elements - JAF
	$form->renderFieldGroup('fmt2Srv'); // added to test fmt2 elements - JAF
	?>
	</td>
	<td id="r1_c2" width="30%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('fmt2Main'); // added to test fmt2 elements - JAF
	$form->renderFieldGroup('stats'); // added to test status elements - JAF
	
	if ($form->mode != 'create') :
	$form->renderFieldGroup('maintenance');
	endif;
	
	?>
	</td>
</tr> <!-- End of format 2 table row -->

<?php 
	break; // end of entry format 2
	
case 3: // display format 3
?>

<tr> <!-- Added table row for displaying format 3 -->
	<td id="r1_c1" width="30%" class="fg_column" valign="top">
	<?php
	$form->renderFieldGroup('fmt3Keys'); // added to test fmt3 elements - JAF
	$form->renderFieldGroup('fmt3Srv'); // added to test fmt3 elements - JAF
	?>
	</td>
	<td id="r1_c2" width="30%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('fmt3Main'); // added to test fmt3 elements - JAF
	$form->renderFieldGroup('stats'); // added to test status elements - JAF
	
	if ($form->mode != 'create') :
	
	$form->renderFieldGroup('maintenance');
	endif;
	
	?>
	</td>
</tr> <!-- End of format 3 table row -->

<?php 
	break; // end of entry format 3
	
case 4: // display format 4
?>

<tr> <!-- Added table row for displaying format 4 -->
	<td id="r1_c1" width="30%" class="fg_column" valign="top">
	<?php
	$form->renderFieldGroup('fmt4Keys'); // added to test fmt4 elements - JAF
	$form->renderFieldGroup('fmt4Srv'); // added to test fmt4 elements - JAF
	?>
	</td>
	<td id="r1_c2" width="30%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('fmt4Main'); // added to test fmt4 elements - JAF
	$form->renderFieldGroup('stats'); // added to test status elements - JAF

	if ($form->mode != 'create') :
	$form->renderFieldGroup('maintenance');
	endif;
	
	?>
	</td>
</tr> <!-- End of format 4 table row -->

<?php 
	break; // end of entry format 4
	
case 5: // display format 5
	
	?>

<tr> <!-- Added table row for displaying format 5 -->
	<td id="r1_c1" width="30%" class="fg_column" valign="top">
	<?php
	$form->renderFieldGroup('fmt5Keys'); // added to test fmt5 elements - JAF
	$form->renderFieldGroup('fmt5Srv'); // added to test fmt5 elements - JAF

	?>
	</td>
	<td id="r1_c2" width="30%" class="fg_column" valign="top">
	<?php 
	$form->renderFieldGroup('fmt5Main'); // added to test fmt5 elements - JAF
	$form->renderFieldGroup('stats'); // added to test status elements - JAF
	
	if ($form->mode != 'create') :
	$form->renderFieldGroup('maintenance');
	endif;
	
	?>
	</td>
</tr> <!-- End of format 5 table row -->

<?php 
	break; // end of entry format 5
	
	default:
?>
		<tr>
		<td id="r1_c1" width="30%" class="fg_column" valign="top">
		<?php
		$form->renderFieldGroup('keys');
		$form->renderFieldGroup('loc');
		$form->renderFieldGroup('pipe');
		?>
			</td>
			
			<td id="r1_c2" width="30%" class="fg_column" valign="top">
			<?php 
			$form->renderFieldGroup('conn');
			$form->renderFieldGroup('method');
			?>
			</td>
			
			<td id="r1_c3" width="30%" class="fg_column" valign="top">
			<?php 
			$form->renderFieldGroup('completion');
			$form->renderFieldGroup('remarks');
			$form->renderFieldGroup('main');
			if ($form->mode != 'create') : 
				$form->renderFieldGroup('maintenance');
			endif; 
			?>
			</td>
		</tr>
<?php 
} // end of switch entry format
?>

</table>

<?php $form->renderFormButtons(); ?>
</form>
</center>

</div>

<?php
	showFooter();
}
