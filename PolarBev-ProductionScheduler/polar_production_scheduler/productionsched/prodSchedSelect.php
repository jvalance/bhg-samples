<?php
require_once '../common/layout.php';
require_once 'model/WorkCenter.php';

$wc = new WorkCenter();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['facility'] != "") {
	// If we have redirected here after clicking "Start Over" on maint screen,
	// load our previously used value for work center as default and format dates.
	$selectedWC = $_POST["work_ctr"];
	$screenData = $_POST;
	$screenData['from_date'] = convertDateFormat($_POST['from_date'], 'Ymd', "m/d/Y");
	$screenData['to_date'] = convertDateFormat($_POST['to_date'], 'Ymd', "m/d/Y");
	$screenData['weekly_from_date'] = convertDateFormat($_POST['weekly_from_date'], 'Ymd', "m/d/Y");
	$screenData['weekly_current_date'] = convertDateFormat($_POST['weekly_current_date'], 'Y-m-d', "m/d/Y");
} else {
	$selectedWC = false;
	$screenData = $_REQUEST;
	initializeInputs($screenData);
}

$facilityOptionsList = $wc->getFacilitiesSelectOptions(' ', $screenData['facility']);
$facilityWC_JSON = json_encode($wc->getFacilityWorkCenters(), JSON_FORCE_OBJECT);

showHeader('Production Schedule Selection');
?>
<script type="text/javascript">

var datePickerOptions = {
	showOn: "button",
	buttonImage: "images/datepicker.gif",
	buttonImageOnly: true,
	dateFormat: 'mm/dd/yy',
	appendText: " (mm/dd/yyyy)"
};

var WCsByFacility = <?= $facilityWC_JSON ?>;

function updateWCOptions( fac ) {
	var wcl = WCsByFacility[fac];
	var opts = '<option value=""> </option>';
	for (wc in wcl) {
		opts += '<option ';
		// If work ctr was selected in previous screen, set it to selected now
		if (wcl[wc] == '<?= $selectedWC ?>') {opts += 'selected ';}
		opts +=	'value="' + wcl[wc] + '">' + wcl[wc] + ' - ' + wc + '</option>';
	}
	return opts;
}


$('document').ready(function() {
	$( "#weekly_from_date" ).datepicker(datePickerOptions);
	$( "#from_date" ).datepicker(datePickerOptions);
	$( "#to_date" ).datepicker(datePickerOptions);

	$( "#weekly_from_date" ).change(function(){
		// Set hidden field for which day to load in daily panel
		var fromDate = $("#from_date").val();
		var year = fromDate.substring(0, 4);
		var mm = fromDate.substring(4, 6);
		var dd = fromDate.substring(6, 8);
		var currDateVal = year + '-' + mm + '-' + dd;
		$("#weekly_current_date").val(currDateVal);
	});

	// On change of facility, update the list of work centers for the selected facility.
	$('#facility').change(function() {
		opts = updateWCOptions( $(this).val() );
		$('#work_ctr').html(opts);
	});
	// Set initial list of work centers for facility selected
	opts = updateWCOptions( $('#facility').val() );
	$('#work_ctr').html(opts);

	// Initially place cursor in facility field
	$("#facility").focus();

	// 	$("#facility").keyup(function() {
	// 		$(this).val($(this).val().toUpperCase())
	// 	});

})

function add12Days() {
	// Automatically add 12 days to from date
	var fromDate = $("#from_date").val();
	var year = fromDate.substring(0, 4);
	var mm = fromDate.substring(4, 6);
	var dd = fromDate.substring(6, 8);

	var datePlus12 = new Date(fromDate);
	datePlus12.setDate(datePlus12.getDate() + 11);
	var toDate = $.datepicker.formatDate('mm/dd/yy', datePlus12);
	$("#to_date").val(toDate);
	$("#to_date").focus();
}

function validateInputs() {
	var facility = $("#facility").val();
	var work_ctr = $("#work_ctr").val();
	var fromDate = $("#from_date").val();
	var toDate = $("#to_date").val();
	var weekly_from_date = $("#weekly_from_date").val();

	if ($.trim(facility) == '') {
		alert('Facility must be entered.');
		$("#facility").focus();
		return false;
	}

	if ($.trim(work_ctr) == '') {
		alert('Work Center must be entered.');
		$("#work_ctr").focus();
		return false;
	}

	if ($.trim(weekly_from_date) == '') {
		alert('WEEKLY START DATE must be entered.');
		$("#weekly_from_date").focus();
		return false;
	}
	if (!validateDate(fromDate)) {
		alert('FROM DATE is not a valid date.');
		$("#from_date").focus();
		return false;
	}

	if ($.trim(fromDate) == '') {
		alert('Planned Orders FROM DATE must be entered.');
		$("#from_date").focus();
		return false;
	}
	if (!validateDate(fromDate)) {
		alert('Planned Orders FROM DATE is not a valid date.');
		$("#from_date").focus();
		return false;
	}
	if ($.trim(toDate) == '') {
		alert('Planned Orders TO DATE must be entered.');
		$("#to_date").focus();
		return false;
	}
	if (!validateDate(toDate)) {
		alert('Planned Orders TO DATE is not a valid date.');
		$("#to_date").focus();
		return false;
	}

    var from = new Date(fromDate);
    var to = new Date(toDate);

	if (to < from) {
		alert('Planned Orders TO DATE must be after FROM DATE. \nPlease correct and try again');
		$("#to_date").focus();
		return false;
	}

	var script = 'createUserLock.php';
	var data = {'facility' : facility,
				'work_ctr' : work_ctr
	};

	$.get(script, data, createLockCallBack, 'json');

	return false;
}


function createLockCallBack( response ) {
	//alert('response from createUserLock.php: ' + response);
	if ( response ) {
		alert('This work center is currently locked because user '  + response.current_user + ' has an open session.\n\n' +
			'If the session is not active, this lock will expire in ' + response.min_left + ' minutes.');
		return false;
	}

	document.selectForm.action = 'prodSchedMaint.php';
	document.selectForm.submit();
}

function validateDate( value ) {
	var check = false;
	var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
	if( re.test(value)){
		var adata = value.split('/');
	    var mm = parseInt(adata[0],10);
	    var dd = parseInt(adata[1],10);
	    var yyyy = parseInt(adata[2],10);

	    var xdata = new Date(yyyy,mm-1,dd);

		if ( ( xdata.getFullYear() == yyyy )
		&& ( xdata.getMonth () == mm - 1 )
		&& ( xdata.getDate() == dd ) ) {
			check = true;
		} else {
			check = false;
		}
	} else {
		check = false;
	}
	return check;
}
</script>

<div id="promptDiv">
<form id="selectForm" name="selectForm" action="#" method="get">
<center>
<input type="hidden" name="weekly_current_date" id="weekly_current_date" value="<?= $screenData['weekly_current_date'] ?>" />

<h3 style="margin: 12px"><span style="color:red">*</span> = Required Entry</h3>

<table id="promptTbl" class="field_group" style="margin: auto; width: 75%">
	<caption>Production Schedule Selection</caption>
	<tr>
		<td class="field_label required">Facility:</td>
		<td class="field_value">
			<select id="facility" name="facility" class="required">
				<?= $facilityOptionsList ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="field_label required">Production Line:</td>
		<td class="field_value">
			<select id="work_ctr" name="work_ctr" class="required">
				<!-- Options are initialized in $(document).ready() function, based on facility selected -->
			</select>
		</td>
	</tr>
	<tr>
		<td class="field_label required">Planned Orders From Date</td>
		<td class="field_value">
			<input type="text" id="from_date" name="from_date"  size="10"
					value="<?= $screenData['from_date'] ?>" class="required"></input>
		</td>
	</tr>
	<tr>
		<td class="field_label required">Planned Orders To Date</td>
		<td class="field_value">
			<input type="text" id="to_date" name="to_date"  size="10"
					value="<?= $screenData['to_date'] ?>" class="required"></input>
			<button type="button" onclick="add12Days()">Set From+11 days</button>
		</td>
	</tr>
	<tr>
		<td class="field_label required">Weekly Schedule Start Date</td>
		<td class="field_value">
			<input type="text" id="weekly_from_date" name="weekly_from_date"  size="10"
					value="<?= $screenData['weekly_from_date'] ?>" class="required"></input>
		</td>
	</tr>
	</table>
<p>
<table id="sysInfo" class="field_group" style="margin: auto; width: 75%">
	<caption>System Options</caption>
	<tr>
		<td class="field_label">Log Debug Data?</td>
		<td class="field_value">
			<input type="checkbox" id="debug" name="debug"
					<?php echo $screenData['debug'] == '1' ? 'checked="checked"' : ''; ?>
					value="1"></input>
		</td>
	</tr>
</table>

<button type="button" style="margin-top: 25px" onclick="validateInputs();">Submit</button>

</center>
</form>
</div>

<?php
showFooter();

function initializeInputs( &$screenData ) {
	if (trim($screenData['facility']) == '')
		$screenData['facility'] = 'WO';

	if (trim($screenData['weekly_from_date']) == '')
		$screenData['weekly_from_date'] = date('m/d/Y');

	if (trim($screenData['from_date']) == '')
		$screenData['from_date'] = date('m/d/Y');

	if (trim($screenData['to_date']) == '')
		$screenData['to_date'] = date('m/d/Y', strtotime('+11 days'));

	if (trim($screenData['weekly_current_date']) == '')
		$screenData['weekly_current_date'] = date('Y-m-d', strtotime($screenData['weekly_from_date']));

	if (trim($screenData['work_ctr']) == '')
		$screenData['work_ctr'] = '10';

}