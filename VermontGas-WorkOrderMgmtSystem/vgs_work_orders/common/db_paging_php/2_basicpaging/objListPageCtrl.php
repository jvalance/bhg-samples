<?php
require_once('objListPageView.php');
require_once('paging.php');

// Connect to database
$conn = connectToDb2();
 
getPageToView($pagingData);

// Run the query
$stmt = execListingQuery($conn);

// Retrieve result rows
$rows = getTableRows($stmt, $pagingData);

db2_close($conn);

// Show the screen
showObjectList($rows, $pagingData);


//--------------------------------------------------------------------------
// End of mainline
//--------------------------------------------------------------------------
function connectToDb2() {
	$options = array(
		'i5_naming'=> DB2_I5_NAMING_ON
	);
	$conn = db2_connect("*LOCAL", '', '', $options)
			or die("Connection failed! ". db2_conn_errormsg()); 
	return $conn;	
}

//------------------------------------------------
function execListingQuery($conn) {
	$query = 
		"SELECT 
			ODOBNM as Object_Name, 
			ODOBTP as Object_Type, 
			ODOBAT as Object_Attribute, 
			ODOBSZ as Object_Size, 
			ODOBTX as Object_Description, 
			ODOBOW as Object_Owner, 
			ODCRTU as Object_Created_By 
		FROM JVALANCE/ZENDOBJS
		ORDER BY ODOBNM";
	
	$stmt = db2_prepare ( $conn, $query ) 
		or die ( "<br>Prepare failed! <pre><br> $query </pre><br>" . db2_stmt_errormsg () );
	
	// <NEW>
	// THIS IS IMPORTANT!! Use scrollable cursor, so we can go directly to a specific record!
	db2_set_option($stmt, array('cursor'=>DB2_SCROLLABLE), 2);
	// </NEW>
	
	db2_execute($stmt)
		or die ( "<br>Execute failed! <pre><br>$query </pre><br>" . db2_stmt_errormsg () );
	
	return $stmt;
}

//------------------------------------------------
function getTableRows($stmt, &$pagingData) {
	// <NEW>
	$rowNumber = getStartRowForPage($pagingData);
	// </NEW> 
	
	$tableRows = array();
	$rowsFetched = 0; // NEW
	while ( $row = db2_fetch_assoc( $stmt, $rowNumber )) { // CHANGED
		// <NEW>
		if (++$rowsFetched > $pagingData['pageSize'] ) {
			break;
		}
		// </NEW>
		$row['rowNumber'] = $rowNumber++;
		$tableRows[] = $row; // add DB row to array
	}
	
	// <NEW>
	setNextButtonState ( $pagingData, $stmt, $rowNumber );
	setPrevButtonState ( $pagingData );
	// </NEW>
	
	return $tableRows;
}

?>  

