<?php 
require_once 'layout.php';
require_once 'form.php';
require_once 'VGS_PaginatorViewHelper.php';
require_once '../forms/VGS_Search_Filter_Group.php';

function showScreen( 
	array &$screenData, 
	VGS_Paginator $paginator,
	VGS_Search_Filter_Group $filter,
    VGS_Navigator $nav
	) 
{
	showHeader('Services Master Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script>
<!--
function doDownload() {
	var action = document.searchForm.action;
	document.searchForm.action = 'svDownloadCtrl.php';
	document.searchForm.submit();
	document.searchForm.action = action;
	return false;
}

$('document').ready(function() {
	$('#filter_SV_STREET').focus();

	$('#checkAll').change(function(){
		if ($(this).is(':checked')) {
			$("[id^=SELECTED_IDS]").each(function (i) {
				$(this).attr('checked',true);
				toggleOn(this.id);
			});
		} else {
			$("[id^=SELECTED_IDS]").each(function (i) {
				$(this).attr('checked',false);
				toggleOff(this.id);
			});
		}
	})
	
})

function doBatchEdit() {
	var url = 'svBatchEditCtrl.php';
	var savedAction = document.searchForm.action;
	document.searchForm.action = url;
	document.searchForm.target = '_blank';
	document.searchForm.submit();
	// Restore default action
	document.searchForm.action = savedAction;
	document.searchForm.target = '';
	return false;
}

function toggleSelection(checkBoxName) {
	var checkBoxId = '#' + checkBoxName;
	if ($(checkBoxId).is(':checked')) {
		toggleOn(checkBoxName);
	} else {
		toggleOff(checkBoxName);
	}
}

function toggleOn(checkBoxName) {
	//alert(checkBoxName);
	var cell_Id = '#cell_' + checkBoxName;
	$(cell_Id).addClass("prem-selected");
}

function toggleOff(checkBoxName) {
	var cell_Id = '#cell_' + checkBoxName;
	$(cell_Id).removeClass("prem-selected");
}
function toggleCellSelect( service_ID, blnToggleCB ) {
	var cbName = 'SELECTED_IDS' + service_ID;
	var cbId = '#' + cbName;
	var cb = $(cbId);
	if (blnToggleCB) cb[0].checked = !cb[0].checked;
	toggleSelection(cbName);
}

function editService( url ) {
	var currEntryFmt = $('#filter_DFT_ENTRY_FORMAT').val();
	url += '&entryFormat=' + currEntryFmt;
	document.location = url;
	return;
}

function showPDF( path ) {
	
}

//*******
//Display mini-table of service addresses
//*******
function showPremAddrs( premNos, targetID ) {
	if ($('#premTable'+targetID).is(":hidden")) {
		var script = 'svGetPremAddrs.php';
		var postData = { premNos: premNos, targetID: targetID };
		$.post(script, postData, callbackGetPremAddrs, 'json');
	} else {
		$('#premTable'+targetID).slideUp();
	}

	return false;
}

//This is called when Ajax response is received from server 
function callbackGetPremAddrs( returnJSON ) {
	var returnObj = eval(returnJSON);
	var targetID = returnObj.targetID;
	var html = returnObj.html;
	$('#premTable'+targetID).empty();
	$('#premTable'+targetID).append(html);
	$('#premTable'+targetID).slideDown();
	var target = $('#premLink'+targetID).position();
	var width = $('#premLink'+targetID).width();
	$('#premTable'+targetID).offset({ top: target.top, left: (target.left + width) });
}


//-->
</script>
<form name="searchForm" method="get" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >


<?php 
$nav->renderNavBar();
?>

<div id="output"> 
<?php 
$filter->renderView($screenData);
?>
<span class="error" style="background-color:yellow; font-size: 14pt">
<?= $screenData['errorMsg']; ?>
</span>

<?php 
// Display batch edit confirmation message
if ( $screenData['mode'] == 'batchConfirm' ) { ?>
<span class="error">
Batch edit routine completed for the services listed below.<br />
Click 'GO' button below to reset search screen.</span>
<?php 
}
?>

<table class="lists" >
<caption>
	<?php $pageHelper->renderView(); ?>
</caption>

<tr>
		<th class="ca" width="3%">
			<input type="checkbox" name="checkAll" id="checkAll">
		</th>
		<th class="ca" width="7%">&nbsp;</th>
		<th class="ca" width="25%">Service Address</th>
		<th class="ca" width="5%">Svc ID</th> 
		<th class="ca" width="7%">Svc Status</th>
		<th class="ca" width="8%">Premise #(s)</th>
		<th class="ca" width="5%">SI/WO#</th>
		<th class="ca" width="7%">Material</th>
		<th class="ca" width="7%">Size</th>
		<th class="la" width="10%">Date Completed</th>
		<th class="la" width="5%">Upd Sts</th>
		<th class="la" width="5%">Card Fmt</th>
	</tr>
	
<?php
foreach ($screenData['rows'] as $svc) :
	// Add popup flag on link URLs if passed on request
	$popup = $_REQUEST['popup'];
	$linkUrl = "svServiceEditCtrl.php?SV_SERVICE_ID={$svc['SV_SERVICE_ID']}&popup=$popup";
	$inquiryUrl = $linkUrl . '&mode=inquiry';
	$editUrl = "javascript:editService('{$linkUrl}&mode=update')";
	$PDFUrl = "svServiceShowPDFCtrl.php?path={$svc['PDFpath']}&filename={$svc['SV_SCAN_FILE_NAME']}&popup=true";
	$inquiryTitle = "Display Service Details";
	$editTitle = "Edit Service Details";
	$PDFTitle = "Display Service Record PDF";

?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca" id="cell_SELECTED_IDS<?= $svc['SV_SERVICE_ID'] ?>" onclick="toggleCellSelect(<?= $svc['SV_SERVICE_ID'] ?>, true)" >
		<!-- Create ST selection checkbox -->
		<input type="checkbox" 
				name="SELECTED_IDS[<?= $svc['SV_SERVICE_ID'] ?>]" 
				id="SELECTED_IDS<?= $svc['SV_SERVICE_ID'] ?>" 
				value="<?= $svc['SV_SERVICE_ID'] ?>"
		> 
	</td>
		
	
	<td class="ca">
		<a href="<?= $inquiryUrl ?>" > 
			<img src="../shared/images/view.gif" title="<?= $inquiryTitle ?>" align="top" border=0 /> 
		</a>
		<a href="<?= $editUrl ?>" >
			<img src="../shared/images/edit.gif" align="top" title="<?= $editTitle ?>" border=0 /> 
		</a>
<!-- IRK 20150515 PDF icon hidden until view-PDF functionality is completed
		<a href="<?= $PDFUrl ?>" >
			<img src="../shared/images/pdf.gif" align="top" title="<?= $PDFTitle ?>" border=0 /> 
		</a>
-->
	</td> 

	<td class="la">
		<a href="<?= $editUrl ?>" >
	    	<?= $svc['address']; ?>
	    </a>
		&nbsp; 
    </td>

    <td class="ca">
		<a href="<?= $inquiryUrl ?>">
			<?= $svc['SV_SERVICE_ID'] ?>
		</a>
    </td>
    
	<td class="ca">
		<?= trim($svc['SVC_STATUS_DESC']) ?>
		&nbsp;
	</td>
	
	<td class="ca">
		<?php
			// Div and link to show mini-table of premise addresses
			$premNos = trim($svc['SPX_PREMISE_NUMS']);
			// If multiple premise nums, display first one plus counter for add'l
			if ( isBlankOrZero($premNos) ) {
				$premDisp = '';
			} else {
				$premArr = explode(",", $premNos);
				$premFirst = trim(min($premArr));
				if ( count($premArr) > 1) {
					$premDisp = $premFirst . " +" . ( count($premArr)-1 );
				} else {
					$premDisp = $premFirst;
				}
			}
		?>
		<a id="premLink<?= $premFirst ?>" onclick="showPremAddrs('<?= $premNos ?>', '<?= $premFirst ?>')" >
			<?= $premDisp ?>
		</a>
		<div id="premTable<?= $premFirst ?>" class="premAddrs" >Premise Addresses</div>
		&nbsp;
	</td>
	
	<td class="ca">
		<?= isBlankOrZero($svc['SV_WO_NO']) ? '' : $svc['SV_WO_NO'] ?>
		&nbsp;
    </td>
    
    <td class="ca">
		<?= "{$svc['SVC_MATERIAL_DESC']}" ?>
		&nbsp;
    </td>
    
   	<td class="ca">
    	<?= $svc['SVC_SIZE_DESC']; ?>
		&nbsp;
    </td>
    
   	<td class="ca">
    	<?= $svc['date_completed'] ; ?>
		&nbsp;
    </td>
    
   	<td class="ca">
    	<?= $svc['SV_UPDATE_STATUS']; ?>
		&nbsp;
    </td>
    
   	<td class="ca">
    	<?= $svc['DEC_ENTRY_FORMAT'] ?>
		&nbsp;
    </td>
</tr>
<?php
endforeach; ?>
</table>

<!-- End of output div -->
</div>

</form>
<?php
	showFooter();
}
