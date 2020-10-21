<?php
require_once('objListAdvPageView.php');
require_once('../../vgs_utilities.php');
require_once('Paginator.php');

// Connect to database
$conn = connectToDb2();
 
$rowCount = getRowCount($conn);
// Instantiate paginator object
$paginator = new Paginator($rowCount);

// Run the query
$stmt = execListingQuery($conn);

// Retrieve result rows
$rows = getTableRows($stmt, $paginator);

db2_close($conn);

// Show the screen
showObjectList($rows, $paginator);


//--------------------------------------------------------------------------
// End of mainline
//--------------------------------------------------------------------------
function connectToDb2() {
	$options = array('i5_naming'=> DB2_I5_NAMING_ON);
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
	
	// THIS IS IMPORTANT!! Use scrollable cursor, so we can go directly to a specific record!
	db2_set_option($stmt, array('cursor'=>DB2_SCROLLABLE), 2);
	
	db2_execute($stmt)
		or die ( "<br>Execute failed! <pre><br>$query </pre><br>" . db2_stmt_errormsg () );
	
	return $stmt;
}

//------------------------------------------------
function getTableRows($stmt, Paginator $paginator) {

	$rowNumber = $paginator->getStartRowForPage();
	
	$tableRows = array();
	$rowsFetched = 0; 
	while ( $row = db2_fetch_assoc( $stmt, $rowNumber )) { 
		if (++$rowsFetched > $paginator->getPageSize() ) {
			break;
		}
		$row['rowNumber'] = $rowNumber++;
		$tableRows[] = $row; // add DB row to array
	}
	
	$paginator->setButtonStates($stmt, $rowNumber );
	
	return $tableRows;
}

//------------------------------------------------
function getRowCount($conn) {
	$query = "SELECT COUNT(*) AS ROW_COUNT FROM JVALANCE/ZENDOBJS";

	$stmt = db2_prepare ( $conn, $query ) 
		or die ( "<br>Prepare failed! <pre><br> $query </pre><br>" . db2_stmt_errormsg () );

	db2_execute($stmt)
		or die ( "<br>Execute failed! <pre><br>$query </pre><br>" . db2_stmt_errormsg () );
	
	if ($row = db2_fetch_assoc($stmt)) {
		return (int) $row['ROW_COUNT'];
	}  else {
		return 0;
	}
}

?>  

