<?php
$pagingData['pageSize'] = 25;
$pagingData['pageToView'] = 0;
$pagingData['startRowForPage'] = 0;
$pagingData['prevButtonState'] = '';
$pagingData['nextButtonState'] = '';
// Advanced features
$pagingData['numberOfPages'] = 0;

//------------------------------------------------
function getPageToView( &$pagingData ) {
	$pagingData['pageToView'] = (int)$_REQUEST['pageToView'];

	// Do some error checking on input
	if (!isset($pagingData['pageToView']) // no page requested 
	|| !is_integer($pagingData['pageToView']) // non-integer
	|| $pagingData['pageToView'] < 1 // negative number
	) {
		$pagingData['pageToView'] = 1;
	} 
	
	return $pageToView;
}

//------------------------------------------------
function getStartRowForPage( &$pagingData ) {
	$pagingData['startRowForPage'] = 
		($pagingData['pageSize'] * $pagingData['pageToView']) 
		- $pagingData['pageSize'] 
		+ 1;
	
	if ($pagingData['startRowForPage'] < 0) {
		$pagingData['startRowForPage'] = 1;
	}

	return $pagingData['startRowForPage'];	
}


//------------------------------------------------
function setPrevButtonState( &$pagingData ) {
	// If this is first page, disabled the previous button
	$pagingData['prevButtonState'] = "";
	if ($pagingData['pageToView'] == 1) {
		$pagingData['prevButtonState'] = "disabled";
	}
}

//------------------------------------------------
function setNextButtonState(&$pagingData, $stmt, $rowNumber) {
	// Try to get one more row; if no more, this is last page (disable the next button)
	$pagingData['nextButtonState'] = "";
	if (!$row = db2_fetch_assoc( $stmt, $rowNumber )) {
		$pagingData['nextButtonState'] = "disabled";
	}
}

//------------------------------------------------
function computeNumberOfPages( &$pagingData ) {
	$pagingData['numberOfPages'] = 
		// Round up number of rows divided by page size
		ceil( $pagingData['rowCount'] / $pagingData['pageSize'] );
		
	return $pagingData['numberOfPages'];
}
