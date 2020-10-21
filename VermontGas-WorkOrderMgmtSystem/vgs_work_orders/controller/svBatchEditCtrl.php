<?php 
require_once '../common/vgs_utilities.php';
require_once '../model/Premise.php';
require_once '../model/Workorder_Master.php';
require_once '../model/VGS_DB_Select.php';

session_start();

$conn = VGS_DB_Conn_Singleton::getInstance();

if (isset($_REQUEST['SELECTED_IDS'])) {
	// This is a new batch coming in from the service selection screen...
	
	// Set the list of services in session for the loop
	$_SESSION['SERVICE_SELECTED_IDS'] = $_REQUEST['SELECTED_IDS'];
	$_SESSION['BATCH_COUNTER'] = 0; 
}

if (isset($_SESSION['SERVICE_SELECTED_IDS']) && $_SESSION['BATCH_COUNTER'] < count($_SESSION['SERVICE_SELECTED_IDS']) ) {
	process_SELECTED_IDs();
} else {
	// Show completion window
	header("Location: svServiceListCtrl.php?mode=batchConfirm");

	exit;
}

function process_SELECTED_IDs () {
	$service_id_arr = array_slice($_SESSION['SERVICE_SELECTED_IDS'], $_SESSION['BATCH_COUNTER'], 1);
	$service_id = $service_id_arr[0];
	++ $_SESSION['BATCH_COUNTER'];

	$svEditURL = "svServiceEditCtrl.php?mode=update&SV_SERVICE_ID=$service_id";
	$svEditURL .= '&return_point=svBatchEditCtrl.php';

	header("Location: $svEditURL");
	exit();
}

/* IRK!!!
if (isset($_SESSION['SERVICE_SELECTED_IDS']) && count($_SESSION['SERVICE_SELECTED_IDS']) > 0 ) {
	process_SELECTED_IDs();
} else {
	// No more services to process - clear session vars  
	unset($_SESSION['SERVICE_SELECTED_IDS']);

	// close window when done with loop
	?>
	<script>
	window.self.close();
	</script>
	<?php 
	exit;
}


function process_SELECTED_IDs () {
	foreach ($_SESSION['SERVICE_SELECTED_IDS'] as $svcIdKey => $service_id) {
		// Set this service ID as "previous" so that data can be auto-filled in next record
		$_SESSION['CURRENT_SERVICE_ID'] = $_SESSION['SERVICE_SELECTED_IDS'][$svcIdKey];
		// Remove this service ID from the session array so we don't process it again
		unset($_SESSION['SERVICE_SELECTED_IDS'][$svcIdKey]);
		break; // Just get first service ID off the list
	}
	
	$svEditURL = "svServiceEditCtrl.php?mode=update&SV_SERVICE_ID=$service_id"; 
	$svEditURL .= '&return_point=svBatchEditCtrl.php';
	
	header("Location: $svEditURL");
	exit();	
}
*/
