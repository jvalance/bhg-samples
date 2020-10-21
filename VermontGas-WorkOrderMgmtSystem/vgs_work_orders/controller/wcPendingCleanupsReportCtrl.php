<?php
require_once '../view/layout.php';
require_once '../model/JOD_Reports.php';
require_once '../model/Workorder_Master.php';
require_once '../common/vgs_utilities.php';
require_once '../model/VGS_DB_Select.php';
require_once '../forms/VGS_Search_Filter_Group.php';
require_once '../model/WO_Cleanup.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$conn = VGS_DB_Conn_Singleton::getInstance();
$cleanup = new WO_Cleanup($conn);
$select = new VGS_DB_Select(); 
$filter = new VGS_Search_Filter_Group();
$jod = new JOD_Reports();

$cleanup->buildFilteredSelect($_REQUEST, $select, $filter);
$select->from = 'WO_CLEANUP';

$select->columns = <<<WC_COLS
	WC_WONUM,
	WO_TYPE,
	WC_CLEANUP_NUM,
	cv1.CV_VALUE as "WO Type", 
	WO_DESCRIPTION "Description",
	cv3.CV_VALUE as "Cleanup Type", 
	cv4.CV_VALUE as "Town", 
	WC_CLEANUP_STATUS as "Status",
	WC_VENDOR_NUM as "Crew/Vendor",
	WC_EARLY_START_DATE	as "Early Start Date",
	WC_LATE_FINISH_DATE	as "Late Finish Date",
	WC_ESTIMATED_SIZE_1	as "Est. Size",
	WC_ESTIMATED_SIZE_2	as "Est. By",
	WC_ACTUAL_SIZE_1	as "Actual Size",
	WC_ACTUAL_SIZE_2	as "Act. By", 
	WC_COMPLETION_FOOTAGE as "Compl Footage",
	WC_DATE_COMPLETED	as "Date Completed",
	WC_COMMENTS	as "Comments",
	WO_SPECIAL_INSTRUCTION as "Special Instructions"
WC_COLS;

$select->order = 'WO_TAX_MUNICIPALITY, WC_CLEANUP_NUM, WC_WONUM';
$select->joins = 
	"LEFT JOIN WORKORDER_MASTER wo on WC_WONUM = WO_NUM
	LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE  
	LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS   
	LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'WC_CLEANUP_TYPES' and cv3.CV_CODE = WC_CLEANUP_TYPE
	LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'TOWN' and cv4.CV_CODE = WO_TAX_MUNICIPALITY 
	"; 

$db = new VGS_DB_Table($conn);
$rs = $db->execListQuery($select->toString(), $select->parms);

$report_rows = array();
while ( $row = db2_fetch_assoc( $db->stmt ) ) {
	$estimatedSize =  "{$row['Est. Size']} by {$row['Est. By']}";
	$startDate = VGS_Form::convertDateFormat($row['Early Start Date'], 'Y-m-d', 'm/d/Y');
	$finishDate = VGS_Form::convertDateFormat($row['Late Finish Date'], 'Y-m-d', 'm/d/Y');
	$pdf_row = array(
		'WONUM' => $row['WC_CLEANUP_NUM'],
		'ADDRESS' => $row['Description'],
		'CITY' => $row['Town'],
		'E_START' => $startDate,
		'L_FINISH' => $finishDate,
		'FROM' => "{$row['WO_TYPE']} {$row['WC_WONUM']}",
		'COMP_DATE' => $row['Date Completed'],
		'COMP_FTG' => $row['Compl Footage'],
		'SPCL_INST' => $row['Special Instructions'],
		'COMMENTS' => $row['Comments'],
		'CU_TYPE' => $row['Cleanup Type'],
		'EST_SIZE' => $estimatedSize,
		'CREW' => $row['Crew/Vendor']
	);
	$report_rows[] = $pdf_row; 
}

// Build list of filter parmaeters for report heading
$parmList = '';

foreach ($_REQUEST as $parmName => $parmValue) {
	// Remove the string 'filter_' form the beginning of the parm name
	if (substr($parmName, 0, 7) == 'filter_'
	&& trim($parmValue != '')) {
		$parm = substr($parmName, 7);
		// Remove the table prefix from the parm name ('WC_' or 'WO_')
		if ( strStartsWith($parm, 'WO_') || strStartsWith($parm, 'WC_') ){
			$parm = substr($parm, 3);
		}
		// Remove the underscores, convert to lower case, then upper case first letter of each word
		$parm = ucwords(strtolower(str_replace('_', ' ', $parm)));
		// Add this parm to the list of filters printed at top of report
		$parmList .= "  $parm='$parmValue';";
	}
}
if ($parmList != '') {
	$parmList = "Filters:$parmList";
}

if (is_array($report_rows) && count($report_rows) > 0) {
	$xml = "<CUS>\n";
	$xml .= "<FILTERS>$parmList</FILTERS>\n";
	foreach ($report_rows as $report_row) {
		$xml .= "<CU>\n" ;
		foreach ($report_row as $tagName => $tagValue) {
			$xml .= $jod->CreateXMLTag($tagName, htmlspecialchars($tagValue)); 
		}
		$xml .= "</CU>\n";
	}
	$xml .= "</CUS>\n";
} else {
	echo 'No records found for report.';
	exit;
}

//pre_dump($xml);
//pre_dump(htmlentities($xml));
//exit;

// Set the document template
$request =  $jod->getJODTemplateFolder() . 'PendingCleanups.pdf'; 

// Create the PDF
$pdf = $jod->retrievePDF($xml, $request);
$prefix = substr($pdf,0,5);
// pre_dump("Prefix returned = $prefix<br>");
if (substr($pdf,0,5) == '%PDF-') {
	$jod->SendPDF2Browser($pdf, "Pending_Cleanups_Rpt");
} else {
	pre_dump($pdf);
}

exit;
