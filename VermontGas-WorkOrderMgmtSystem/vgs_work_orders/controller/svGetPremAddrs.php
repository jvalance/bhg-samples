<?php
require_once '../common/vgs_utilities.php';
require_once '../model/Code_Values_Master.php';
require_once '../model/Premise.php';

$conn = VGS_DB_Conn_Singleton::getInstance();

$premise = new Premise($conn);
$premNos = $_REQUEST['premNos'];
$targetID = $_REQUEST['targetID'];
$premArray = explode(",", $premNos);
$html = '<table><tr><th>Prem#</th><th>Address</th><th>Town</th></tr>';
$count = 0;
foreach ($premArray as $prem) {
	$html .= '<tr><td>'.trim($prem).'</td>';
	$premRow = $premise->retrieve(trim($prem));

	if (is_array($premRow) && count($premRow) > 0) {
		$html .= '<td>'.trim($premRow[UPSAD]).'</td>';
		$html .= '<td>'.trim($premRow[UPCTC]).'</td>';
	} else {
		$html .= '<td>'."Unable to retrieve data for premise # {$prem}.".'</td><td></td>';
	}
	
	$html .= '</tr>';
	$count++;
}
	
$html .= "</table>";
$html .= "<div style=\"margin: 5px\"><a onclick=\"showPremAddrs('$premNos', '$targetID')\">Close</a></div>";

$outputArr = array ("html" => $html, "targetID" => $targetID);

$output = json_encode($outputArr);

echo $output;

exit;

