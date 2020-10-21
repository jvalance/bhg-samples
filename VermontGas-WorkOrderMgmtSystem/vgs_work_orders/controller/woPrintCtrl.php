<?php
require_once '../view/layout.php';
require_once '../common/vgs_utilities.php';
require_once '../model/JOD_Reports.php';
require_once '../model/Workorder_Master.php';
require_once '../model/Account.php';
require_once '../model/Premise.php';
require_once '../model/Pipe_Type_Master.php';
require_once '../model/SalesApp.php';
require_once '../library/PDFMerger/PDFMerger.php';

$sec = new Security();
$sec->checkPermissionByCategory('WO', 'INQUIRY');

$jod = new JOD_Reports();

ob_end_clean();

$conn = VGS_DB_Conn_Singleton::getInstance();
$wo = new Workorder_Master($conn);


// Array $documentsToMerge will hold a list of full-paths of PDFs to be merged
// into one printable PDF document
$documentsToMerge = array();

// Array $printWOs will hold the list of WO #s to be printed. 
// These are passed as an array on the request in the format: 
// woPrintCtrl.php?WO_NUM[1]=61136&WO_NUM[2]=61137&WO_NUM[3]=61138
$printWOs = $_REQUEST['WO_NUM'];
checkForTieIns($conn, $printWOs);
sort($printWOs); // Sort w/o #s ascending 

// If autoPrintRecheck = Y, then leaks with rechecks will 
// automatically print the recheck w/o as well. 
// This would be Y when printing from w/o detail screen,
// but N when printing from the w/o search screen.
$autoPrintRecheck = $_REQUEST['autoPrintRecheck'];
  
// Loop through WO#s and build printable PDFs for each one
foreach ($printWOs as $woNum) {
	$woRec = getWODetails($conn, $wo, $woNum);
			
	$documentsToMerge[] = printWO_pdf($wo, $woRec);
	
	// If leak w/o, print recheck w/o also
	if (isLeakWO($woRec) && $autoPrintRecheck == 'Y'
	&& isset($woRec['LK_RECHECK_WONUM']) 
	&& (int)$woRec['LK_RECHECK_WONUM'] > 0) 
	{
		$leakRecheck = getWODetails($conn, $wo, $woRec['LK_RECHECK_WONUM']);
		$documentsToMerge[] = printWO_pdf($wo, $leakRecheck);
	}
}

// Merge all PDFs together and send to browser
$pdfMerger = new PDFMerger;
try {
	foreach ($documentsToMerge as $pdfFile) {
		if (file_exists($pdfFile)) {
			$pdfMerger->addPDF($pdfFile, 'all');
		}
	}
	$datetime = date('Y-m-d-his');
	// Merge and send to browser
	$pdfMerger->merge('browser', "woPrint_$datetime");

	// Delete temporary PDFs
	foreach ($documentsToMerge as $pdfFile) {
		if (file_exists($pdfFile)) {
			unlink($pdfFile);  // delete temp file
		}
	}
} catch (Exception $e) {
	echo $e->getMessage();
}


//===============================================================================
function getWODetails(
	$conn, 
	Workorder_Master $wo, 
	$woNum)
{
	$woRec = $wo->getWorkorder($woNum);

	$premiseNo = $woRec['WO_PREMISE_NUM'];
	
	$account = new Account($conn);
	$acctRec = $account->retrieveByPremiseNo($premiseNo);
	$woRec['ACCT_NO'] = trim($acctRec['UMACT']);
	$woRec['OWNERS_NAME'] = trim($acctRec['UMNAM']);
	$phoneNo = Account::formatPhoneNo($acctRec['UMOPH']);
	$woRec['OWNERS_PHONE'] = $phoneNo;
	
	$premise = new Premise($conn);
	$ucsrRec = $premise->retrieve_UCSR($premiseNo);
	$woRec['METER_NO'] = trim($ucsrRec['UCMTR']);
	
	if ($woRec['WO_MAIN_PIPE_TYPE'] != 0) {
		$mainPT = new Pipe_Type_Master($conn);
		$mainData['PT_PIPE_TYPE'] = $woRec['WO_MAIN_PIPE_TYPE'];
		$mainRec = $mainPT->retrieve($mainData);
		$woRec['MAIN_PIPE_TYPE'] = '(Main: ' . trim($mainRec['PT_DESCRIPTION']) .')';
	} else {
		$woRec['MAIN_PIPE_TYPE'] = '';
	}
	
	return $woRec;
}

//===============================================================================
function printWO_pdf(Workorder_Master $wo, array &$woRec)
{
	if (isLeakWO($woRec)) {
		$result = printLeakWO($woRec);
	} else {
		$result = printNonLeakWO($woRec);
	}
	
	// Create an array to increment the print count on the work order 
	$updRec['WO_NUM'] = $woRec['WO_NUM'];
	$updRec['WO_PRINT_COUNT'] = $woRec['WO_PRINT_COUNT'] + 1;
	$wo->updateWorkOrder($updRec);
	
	return $result ;
}

//--------------------------------------------------------
function printLeakWO(array &$woRec) 
{
//	echo "woRec['LK_RECHECK_WONUM'] = {$woRec['LK_RECHECK_WONUM']}<br>";
	switch ($woRec['LK_LEAKWO_TYPE']) {
		case 'ORIG':
			return getLeakOrigPDF($woRec);
			break;
		
		case 'RECHK':
			return getLeakRecheckPDF($woRec);
			break;
		
		default:
			return getLeakOrigPDF($woRec);
			break;
	}
}

//--------------------------------------------------------
function isLeakWO(array &$woRec) {
	return (substr($woRec['WO_TYPE'], 0, 1) == 'L');
}

//--------------------------------------------------------
function printNonLeakWO(array &$woRec) 
{
	global $conn;
	global $jod; // JOD_Reports instance in mainline.
	
	// Format data elements for printing
	$woGLCost = 	$woRec['PT_ACCTG_UNIT_COST'] 
			. '-' . $woRec['PT_GL_ACCT_COST'] 
			. '-' . $woRec['PT_SUB_ACCT_COST']
			. '-' . $woRec['WO_NUM'];
	
	$neededByDate = $woRec['WO_COMPLETE_BY_DATE'];
	$neededByDate = ($neededByDate == '0001-01-01') ? '' : date('D M d, Y', strtotime($neededByDate));
	
	// Merge w/o data into document template fields 
	$xml = "<WO>\n"
			 . $jod->CreateXMLTag('WO_NUM', $woRec['WO_NUM'])	
	       . $jod->CreateXMLTag('WO_ENTRY_DATE', date('D M d, Y', strtotime($woRec['WO_ENTRY_DATE'])))
	       . $jod->CreateXMLTag('NEED_BY_DATE', $neededByDate)
	       . $jod->CreateXMLTag('WO_DESCRIPTION', $woRec['WO_DESCRIPTION'])
	       . $jod->CreateXMLTag('WO_PREMISE_NUM', $woRec['WO_PREMISE_NUM'])
	       . $jod->CreateXMLTag('OWNERS_NAME', $woRec['OWNERS_NAME'])
	       . $jod->CreateXMLTag('OWNERS_PHONE', $woRec['OWNERS_PHONE'])
	       . $jod->CreateXMLTag('METER_NO', $woRec['METER_NO'])
	       . $jod->CreateXMLTag('WO_SPECIAL_INSTRUCTION', $woRec['WO_SPECIAL_INSTRUCTION'])
	       . $jod->CreateXMLTag('WO_MISC_NOTES', $woRec['WO_MISC_NOTES'])
	       . $jod->CreateXMLTag('WO_TYPE_DESC', $woRec['WO_TYPE_DESC'])
	       . $jod->CreateXMLTag('WO_GL_COST', $woGLCost)
	       . $jod->CreateXMLTag('WO_ROW_NUM', $woRec['WO_ROW_NUM'])
	       . $jod->CreateXMLTag('PT_DESCRIPTION', $woRec['PT_DESCRIPTION'])
	       . $jod->CreateXMLTag('ESTLEN', $woRec['WO_ESTIMATED_LENGTH'])
	       . $jod->CreateXMLTag('ESTHRS', $woRec['WO_CREW_HOURS'])
	       . $jod->CreateXMLTag('CURBSTOP', $woRec['WO_CURB_STOP'])
	       . $jod->CreateXMLTag('FLWLIM', $woRec['WO_FLOW_LIMITER_SIZE'])
	       . $jod->CreateXMLTag('WO_TOWN_NAME', $woRec['WO_TOWN_NAME'])
	       . $jod->CreateXMLTag('MAIN_PIPE_TYPE', $woRec['MAIN_PIPE_TYPE'])
			;
				          
	$sa = new SalesApp($conn);
	$slsApps = $sa->getSlsAppsForWO($woRec['WO_NUM']);
	if (is_array($slsApps) && count($slsApps) > 0) {
		$xml .= "<SLSAPPS>\n";
		foreach ($slsApps as $slsApp) {
			$xml .= "<SLSAPP>\n" 
				. $jod->CreateXMLTag('SLSAPPNUM', $slsApp['SLSAPP']) 
				. $jod->CreateXMLTag('PREMNO', $slsApp['PREMNO']) 
				. $jod->CreateXMLTag('ADDRESS', $slsApp['ADDRESS']) 
				. $jod->CreateXMLTag('APT', $slsApp['APT']) 
				. $jod->CreateXMLTag('SLSMN', $slsApp['SLSMN']) 
				. "</SLSAPP>\n"
			;
		} 
		$xml .= "</SLSAPPS>\n";
	}

   $xml .= "</WO>\n";

	// Determine proper print template to use, based on w/o type.
	if ( in_array($woRec['WO_TYPE'], array('SM', 'MM', 'MN')) ) {
		// Print maintenance work order
		$printTemplate = 'MaintWO.pdf';
		
	} elseif ( in_array($woRec['WO_TYPE'], array('NW', 'SB')) ) {
		// Print non-WO or barricade
		$printTemplate = 'NonWO.pdf';
		
	} elseif ( in_array($woRec['WO_TYPE'], array('MT', 'ST')) ) {
		// Print retirement WO 
		$printTemplate = 'RetireWO.pdf';
		
	} else {
		// Print installs and others
		$printTemplate = 'StdWO.pdf';
	}
	// Set the document template
	$request =  $jod->getJODTemplateFolder() . $printTemplate; 
	$fileName = "WO_{$woRec['WO_NUM']}.pdf";
	
	// Create the PDF
	$pdf = $jod->retrievePDF($xml, $request);

	// Save PDF on disk in temp folder
	$pdfFullPath = $jod->getTemporaryMergeFolder() . $fileName;
	$bytesWritten = file_put_contents($pdfFullPath, $pdf);
	
	// Return temp file name
	return $pdfFullPath;
}


//--------------------------------------------------------
function getLeakOrigPDF(array &$woRec) 
{
	global $jod; // JOD_Reports instance in mainline.
	
	// Format data elements for printing
	$woGLCost = 	$woRec['PT_ACCTG_UNIT_COST'] 
			. '-' . $woRec['PT_GL_ACCT_COST'] 
			. '-' . $woRec['PT_SUB_ACCT_COST']
			. '-' . $woRec['WO_NUM'];
			
	$lkDateTime = '';
	if (isset($woRec['LK_DATE_FOUND']) 
	&& $woRec['LK_DATE_FOUND'] != '' 
	&& $woRec['LK_DATE_FOUND'] != '0001-01-01') 
	{
		$lkDateTime = date('D M d, Y', strtotime($woRec['LK_DATE_FOUND'])) . 
			' at ' . date('h:i a', strtotime($woRec['LK_TIME_FOUND']));	
	}  

	$neededByDate = $woRec['WO_COMPLETE_BY_DATE'];
	$neededByDate = ($neededByDate == '0001-01-01') ? '' : date('D M d, Y', strtotime($neededByDate));
	
	// Merge w/o data into document template fields 
	$xml = "<WO>\n"
		. $jod->CreateXMLTag('WO_NUM', $woRec['WO_NUM'])
        . $jod->CreateXMLTag('WO_ENTRY_DATE', date('D M d, Y', strtotime($woRec['WO_ENTRY_DATE'])))
        . $jod->CreateXMLTag('NEED_BY_DATE', $neededByDate)
        . $jod->CreateXMLTag('WO_DESCRIPTION', $woRec['WO_DESCRIPTION'])
        . $jod->CreateXMLTag('WO_PREMISE_NUM', $woRec['WO_PREMISE_NUM'])
        . $jod->CreateXMLTag('OWNERS_NAME', $woRec['OWNERS_NAME'])
        . $jod->CreateXMLTag('OWNERS_PHONE', $woRec['OWNERS_PHONE'])
        . $jod->CreateXMLTag('METER_NO', $woRec['METER_NO'])
        . $jod->CreateXMLTag('WO_SPECIAL_INSTRUCTION', $woRec['WO_SPECIAL_INSTRUCTION'])
        . $jod->CreateXMLTag('WO_MISC_NOTES', $woRec['WO_MISC_NOTES'])
        . $jod->CreateXMLTag('WO_TYPE_DESC', $woRec['WO_TYPE_DESC'])
        . $jod->CreateXMLTag('WO_GL_COST', $woGLCost)
        . $jod->CreateXMLTag('LK_REPORTED_BY', $woRec['LK_REPORTED_BY'])
        . $jod->CreateXMLTag('LK_REPORTED_TO', $woRec['LK_REPORTED_TO'])
        . $jod->CreateXMLTag('LK_SURVEYED_BY', $woRec['LK_SURVEYED_BY'])
        . $jod->CreateXMLTag('LK_DATE_TIME_FOUND', $lkDateTime)
        . $jod->CreateXMLTag('LK_RECHECK_WONUM', $woRec['LK_RECHECK_WONUM'])
        . $jod->CreateXMLTag('LK_LEAK_CLASS', $woRec['LK_LEAK_CLASS'])
	     . $jod->CreateXMLTag('WO_TOWN_NAME', $woRec['WO_TOWN_NAME'])
        . $jod->CreateXMLTag('LK_PAGE', $woRec['LK_PAGE'])
        . "</WO>\n";

        
	// Set the document template
	$request =  $jod->getJODTemplateFolder() . 'LeakWO.pdf'; 
	$fileName = "leakWO_{$woRec['WO_NUM']}_orig.pdf";
	
	// Create the PDF
	$pdf = $jod->retrievePDF($xml, $request);

	// Save PDF on disk in temp folder
	$pdfFullPath = $jod->getTemporaryMergeFolder() . $fileName;
	$bytesWritten = file_put_contents($pdfFullPath, $pdf);

	// Return temp file name
	return $pdfFullPath;
}

//--------------------------------------------------------
function getLeakRecheckPDF(array &$woRec) 
{
	global $jod; // JOD_Reports instance in mainline.
	
	// Format data elements for printing
	$woGLCost = 	$woRec['PT_ACCTG_UNIT_COST'] 
			. '-' . $woRec['PT_GL_ACCT_COST'] 
			. '-' . $woRec['PT_SUB_ACCT_COST']
			. '-' . $woRec['WO_NUM'];
			
	$lkDateTime = '';
	if (isset($woRec['LK_DATE_FOUND']) 
	&& $woRec['LK_DATE_FOUND'] != '' 
	&& $woRec['LK_DATE_FOUND'] != '0001-01-01') 
	{
		$lkDateTime = date('D M d, Y', strtotime($woRec['LK_DATE_FOUND'])) . 
			' at ' . date('h:i a', strtotime($woRec['LK_TIME_FOUND']));	
	}  
	
	$neededByDate = $woRec['WO_COMPLETE_BY_DATE'];
	$neededByDate = ($neededByDate == '0001-01-01') ? '' : date('D M d, Y', strtotime($neededByDate));
	
	// Merge w/o data into document template fields 
	$xml = "<WO>\n"
			. $jod->CreateXMLTag('WO_NUM', $woRec['WO_NUM'])
	      . $jod->CreateXMLTag('ENTRY_DATE', date('D M d, Y', strtotime($woRec['WO_ENTRY_DATE'])))
	      . $jod->CreateXMLTag('NEED_BY_DATE', $neededByDate)
	      . $jod->CreateXMLTag('DESCRIPTION', $woRec['WO_DESCRIPTION'])
	      . $jod->CreateXMLTag('PREMISE_NUM', $woRec['WO_PREMISE_NUM'])
	      . $jod->CreateXMLTag('OWNERS_NAME', $woRec['OWNERS_NAME'])
	      . $jod->CreateXMLTag('OWNERS_PHONE', $woRec['OWNERS_PHONE'])
	      . $jod->CreateXMLTag('METER_NO', $woRec['METER_NO'])
	      . $jod->CreateXMLTag('SPECIAL_INSTRUCTION', $woRec['WO_SPECIAL_INSTRUCTION'])
	      . $jod->CreateXMLTag('MISC_NOTES', $woRec['WO_MISC_NOTES'])
	      . $jod->CreateXMLTag('TYPE_DESC', $woRec['WO_TYPE_DESC'])
	      . $jod->CreateXMLTag('GL_COST', $woGLCost)
	      . $jod->CreateXMLTag('REPORTED_BY', $woRec['LK_REPORTED_BY'])
	      . $jod->CreateXMLTag('REPORTED_TO', $woRec['LK_REPORTED_TO'])
	      . $jod->CreateXMLTag('SURVEYED_BY', $woRec['LK_SURVEYED_BY'])
	      . $jod->CreateXMLTag('DATE_TIME_FOUND', $lkDateTime)
	      . $jod->CreateXMLTag('LK_PAGE', $woRec['LK_PAGE'])
	      . $jod->CreateXMLTag('WO_TOWN_NAME', $woRec['WO_TOWN_NAME'])
	      . $jod->CreateXMLTag('ORIG_WONUM', $woRec['LK_ORIG_WONUM'])
        . "</WO>\n";

        
	// Set the document template
	$request =  $jod->getJODTemplateFolder() . 'LeakWOrecheck.pdf'; 
	$fileName = "leakWO_{$woRec['WO_NUM']}_orig.pdf";
	
	// Create the PDF
	$pdf = $jod->retrievePDF($xml, $request);

	// Save PDF on disk in temp folder
	$pdfFullPath = $jod->getTemporaryMergeFolder() . $fileName;
	$bytesWritten = file_put_contents($pdfFullPath, $pdf);
        
	// Return temp file name
	return $pdfFullPath;
}

/**
 * This will check for tie-ins associated with any MI or MR orders. If found,
 * they should print automatically with the MI or MR order. 
 * @param array $woList The list of work orders to be printed.
 */
function checkForTieIns( $conn, &$woList ) {
	$woObj = new Workorder_Master($conn);
	foreach ($woList as $woNum) {
		$woRec = $woObj->getWorkorder($woNum);
		if ($woRec['WO_TYPE'] == 'MI' || $woRec['WO_TYPE'] == 'MR') {
			// Add 1 to w/o# and check if this is a tie in. If so, add the tie in to the print array
			$tiCheckWoNum = (int) $woNum + 1;
			$tiCheckRec = $woObj->getWorkorder($tiCheckWoNum);
			if ($tiCheckRec['WO_TYPE'] == 'TI') {
				$woList[] = $tiCheckWoNum;
			} 
		}
	}
}