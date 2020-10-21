<?php
define("PAGE_SIZE", 50);
require_once('objListPageingView.php');

$options = array('i5_naming'=> DB2_I5_NAMING_ON);
$conn = db2_connect("*LOCAL","PHPUSER","PHPUSER1", $options)
		or die("Connection failed! ". db2_conn_errormsg()); 

// Initialize screen data array from form inputs
$screenData = $_REQUEST;

$screenData['numberOfPages'] =  getNumberOfPages($conn);
$screenData['pageToView'] = getPageToView($screenData);

$stmt = getScrollableStatement($conn);
$screenData = retrieveScreenData($stmt, $screenData);
//echo '<pre>'; var_dump($screenData); echo '</pre>';

// Show the screen
showObjectList($screenData);

db2_close($conn);


//--------------------------------------------------------------------------
// End of mainline
//--------------------------------------------------------------------------
function getScrollableStatement($conn) {
	$query = 
		"SELECT 
			ODOBNM as Object_Name, 
			ODOBTP as Object_Type, 
			ODOBAT as Object_Attribute, 
			ODOBSZ as Object_Size, 
			ODOBTX as Object_Description, 
			ODOBOW as Object_Owner, 
			ODCRTU as Object_Created_By 
		FROM JOHNV/ZENDOBJS";
	
	// Demo SQL injection attack
	if (isset($_REQUEST['type']) && trim($_REQUEST['type']) != '') {
		$query .= " WHERE ODOBTP = '" . $_REQUEST['type'] . "' ";
	}
	$query .= ' ORDER BY ODOBNM';
	//echo $query;
	
	$stmt = db2_prepare ( $conn, $query ) 
		or die ( "<br>Prepare failed! <pre><br> $query </pre><br>" . db2_stmt_errormsg () );
	
	// THIS IS IMPORTANT!! Use scrollable cursor, so we can go directly to a specific record!
	db2_set_option($stmt, array('cursor'=>DB2_SCROLLABLE), 2);
	
	db2_execute($stmt)
		or die ( "<br>Execute failed! <pre><br>$query </pre><br>" . db2_stmt_errormsg () );
	
	return $stmt;
}
//------------------------------------------------
function getPageToView($screenData) {
	// Retrieve input field value
	$pageToView = $screenData['pageToView'];
	
	// Don't allow page greater than total number of pages 
	if ($pageToView > $screenData['numberOfPages']) 
		$pageToView = $screenData['numberOfPages'];
		
	// If less than 1 or non-numeric, default to page 1
	if ($pageToView < 1 || !is_int((int)$pageToView)) 
		$pageToView = 1;
		
	return $pageToView;
}
//------------------------------------------------
function retrieveScreenData($stmt, $screenData) {
	$screenData['currentPage'] = $screenData['pageToView'];
	$rowNumber = getStartRowForPage($screenData['pageToView']);
	$screenData['startRow'] = $rowNumber; 
	
	$rowsFetched = 0;
	$tableRows = array();
	while ( $row = db2_fetch_assoc( $stmt, $rowNumber )) {
		if (++$rowsFetched > PAGE_SIZE ) {
			break;
		}
		$row['rowNumber'] = $rowNumber;
		$tableRows[] = $row; //array_map("htmlentities", $row);  // add DB row to array
		$rowNumber++ ;
	}
		
	$screenData['tableRows'] = $tableRows;
	
	// Try to get one more row; if no more, this is last page (disable the next button)
	$screenData['nextButtonState'] = "";
	if (!$row = db2_fetch_assoc( $stmt, $rowNumber )) {
		$screenData['nextButtonState'] = "disabled";
	}
	// If this is first page, disabled the previous button
	$screenData['prevButtonState'] = "";
	if ($screenData['pageToView'] == 1) {
		$screenData['prevButtonState'] = "disabled";
	}
	
	return $screenData;
}

//------------------------------------------------
function getNumberOfPages($conn) {
	$query = "SELECT COUNT(*) AS ROW_COUNT FROM JOHNV/ZENDOBJS";
	if (isset($_REQUEST['type']) && trim($_REQUEST['type']) != '') {
		$query .= " WHERE ODOBTP = '" . $_REQUEST['type'] . "' ";
	}
	$stmt = db2_prepare ( $conn, $query ) 
		or die ( "<br>Prepare failed! <pre><br> $query </pre><br>" . db2_stmt_errormsg () );

	db2_execute($stmt)
		or die ( "<br>Execute failed! <pre><br>$query </pre><br>" . db2_stmt_errormsg () );
	
	if ($row = db2_fetch_assoc($stmt)) {
		// Use ceil() function to round fraction up.
		$numberOfPages = ceil( (int)$row['ROW_COUNT'] / PAGE_SIZE);
	} 
	return $numberOfPages;
}

//------------------------------------------------
function getStartRowForPage( $pageNum) {
	$rowNum = (PAGE_SIZE * $pageNum) - PAGE_SIZE + 1;
	if ($rowNum < 0) $rowNum = 1;
	return $rowNum;	
}
?>  
