<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once '../common/front.php';
require_once 'prodSchedHeader.php';
require_once 'prodSchedUpdate.php';
require_once 'firmOrdersToShop.php';
require_once 'model/WorkCenter.php';

if ($_POST['action'] == 'update' && isset($_POST['jsonWeekly'])) {
	// If posting changes to schedule, call function to parse json
	// and iterate through order updates.
	updateWeeklySchedule();
}

if ($_POST['action'] == 'firmToShop' && isset($_POST['jsonDaily'])) {
	// If posting changes to schedule, call function to parse json
	// and iterate through order updates.
	convertFirmToShop();
}

$facility = $_REQUEST['facility'];
$work_ctr = $_REQUEST['work_ctr'];
$debug = $_REQUEST['debug'];
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	// Convert date formats from prompt screen
	$from_date = convertDateFormat($_REQUEST['from_date'], 'm/d/Y', 'Ymd');
	$to_date = convertDateFormat($_REQUEST['to_date'], 'm/d/Y', 'Ymd');
	$weekly_from_date = convertDateFormat($_REQUEST['weekly_from_date'], 'm/d/Y', 'Ymd');
	$weekly_current_date = convertDateFormat($_REQUEST['weekly_from_date'], 'm/d/Y', 'Y-m-d');
} else {
	$from_date = $_REQUEST['from_date'];
	$to_date = $_REQUEST['to_date'];
	$weekly_from_date = $_REQUEST['weekly_from_date'];
	$weekly_current_date = $_REQUEST['weekly_current_date'];
}


$plannedOrdersQryStr =
    "facility=$facility&from_date=$from_date&to_date=$to_date"
    . "&work_ctr=$work_ctr&debug=$debug";

$dt_from_date = strtotime($from_date);
$from_date_fmtd = date('D m/d/Y', $dt_from_date);

$dt_to_date = strtotime($to_date);
$to_date_fmtd = date('D m/d/Y', $dt_to_date);

$plannedOrdersCaption = "Planned Orders - from $from_date_fmtd through $to_date_fmtd";

$wc = new WorkCenter();
$wcRec = $wc->getWorkCenterRec($work_ctr);
$wcDesc = trim($wcRec['WDESC']);

$facRec = $wc->getFacilityRec($facility);
$facDesc = trim($facRec['MFDESC']);

$displayFiltersString =  "<b>Facility:</b> $facility - $facDesc; <b>Work Ctr:</b> $work_ctr - $wcDesc";
if ($debug) $displayFiltersString .= "; Debug=true";
addHeadSection($displayFiltersString);

// Check to see that user is locked-in to this work center. Redirect if not.
$data = array (
	'facility' => $facility,
	'work_ctr' => $work_ctr
);

if (! $wc->isLockedIn($data)) {
	$loc = FRONT_BASE_FOLDER . "productionsched/prodSchedSelect.php" ;
	header ( "Location: $loc" );
	exit;
}

// $queryString =  'facility='.$_REQUEST['facility'] .
//     '&from_date='.$_REQUEST['from_date'].
//     '&to_date='.$_REQUEST['to_date'].
//     '&work_ctr='.$_REQUEST['work_ctr'];

$arrHiddenFormFields = array(
    "facility" => $facility,
    "work_ctr" => $work_ctr,
    "from_date" => $from_date,
    "to_date" => $to_date,
    "weekly_from_date" => $weekly_from_date,
    "weekly_current_date" => $weekly_current_date,
    "debug" => $_REQUEST['debug']
);
//================================
showHeader ( $displayFiltersString, $arrHiddenFormFields  );
//================================

?>
<script type="text/javascript">

// Load global variables from request, to be used in included javascript files
var planned_orders_query_str = '<?= $plannedOrdersQryStr; ?>';
var planned_orders_caption = '<?= $plannedOrdersCaption; ?>';
var reqFacility = '<?= $facility; ?>';
var reqFromDate = '<?= $from_date; ?>';
var	reqToDate =  '<?= $to_date; ?>';
var	reqWorkCtr =  '<?= $work_ctr; ?>';
var reqWeeklyFromDate = '<?= $weekly_from_date ?>';
var reqWeeklyCurrentDate = '<?= $weekly_current_date ?>';
var reqDebug = '<?= $debug ?>';
</script>


<div id="top_panels">
<?php
require_once 'plannedOrdersPnl.php';
?>
</div>

<div id="spacerdiv" style="height:10px"> </div>

<div id="bottom_panels">

<?php
require_once 'dailySchedule.php';
require_once 'weeklySchedule.php';?>

</div>

<div id="weekly-legend" title="Color Legend" style="display: none">
	<table class="legend-table" style="vertical-align: top">
 <!-- <caption style="background-color: navy; color:white;">Weekly Schedule Color Legend</caption>  -->
		<tr>
			<td class="legend-label">Firm Order:</td>
			<td class="orderBoxLegend firm">&nbsp;</td>
		</tr>
		<tr>
			<td class="legend-label">Firm Order, Expedite:</td>
			<td class="orderBoxLegend firm-expedite">&nbsp;</td>
		</tr>
		<tr>
			<td class="legend-label">Firm Order, De-Expedite:</td>
			<td class="orderBoxLegend firm-deexpedite">&nbsp;</td>
		</tr>
		<tr>
			<td class="legend-label">Shop Order:</td>
			<td class="orderBoxLegend shop">&nbsp; </td>
		</tr>
		<tr>
			<td class="legend-label">Shop Order, Expedite:</td>
			<td class="orderBoxLegend shop-expedite">&nbsp; </td>
		</tr>
		<tr>
			<td class="legend-label">Shop Order, De-Expedite:</td>
			<td class="orderBoxLegend shop-deexpedite">&nbsp; </td>
		</tr>
		<tr>
			<td class="legend-label">Firm-to-Shop:</td>
			<td class="orderBoxLegend firmToShop">&nbsp; </td>
		</tr>
	</table>
</div>


<?php
showFooter();
?></body>
</html>
