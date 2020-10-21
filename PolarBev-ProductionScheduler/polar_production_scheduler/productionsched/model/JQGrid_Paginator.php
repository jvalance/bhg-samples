<?php

class JQGrid_Paginator {
	
	const DEFAULT_PAGE_SIZE = 25;
	public $pageSize;
	public $pageToView = 1;
	public $sortidx = '';
	public $sortord = '';
	public $totalResultRows;
	public $numberOfPages;
	public $startRow;
	
	function __construct(
		$totalResultRows,
		$pageSize = self::DEFAULT_PAGE_SIZE) 
	{

		// Get the requested page. By default grid sets this to 1.
		$this->pageToView = $_GET['page'];
		
		// get how many rows we want to have into the grid - rowNum parameter in the grid
		$this->pageSize = $_GET['rows'];
		
		// get index row - i.e. user click to sort. At first time sortname parameter -
		// after that the index from colModel
		$this->sortidx = $_GET['sidx'];
		
		// sorting order - at first time sortorder
		$this->sortord = $_GET['sord'];
		
		// if we not pass at first time index use the first column for the index 
		if(!$this->sortidx) {
			$this->sortidx = 1;
		}
		
		
		// calculate the total pages for the query
		if( $totalResultRows > 0 && $this->pageSize > 0) {
			$this->numberOfPages = ceil( $totalResultRows / $this->pageSize );
		} else {
			$this->numberOfPages = 0;
		}

		// if for some reasons the requested page is greater than the total
		// set the requested page to total page
		if ($this->pageToView > $this->numberOfPages) {
			$this->pageToView = $this->numberOfPages;
		} 
		// Validate page to view
		if ($this->pageToView < 1 || ! is_int( (int)$this->pageToView )) {
			$this->pageToView = 1;
		}
		
			
		// calculate the starting position of the rows
		$this->startRow = $this->pageSize * $this->pageToView - $this->pageSize;
		
		// if for some reasons start position is negative set it to 0
		// typical case is that the user type 0 for the requested page
		if($this->startRow < 0) {
			$this->startRow = 0;
		}
		
	}
	
}

?>