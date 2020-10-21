<?php
class Paginator {
	
	const DEFAULT_PAGE_SIZE = 25;
	private $pageSize = 0;
	private $pageToView = 0;
	private $startRowForPage = 0;
	private $prevButtonState = '';
	private $nextButtonState = '';
	// Advanced features
	private $numberOfRows = 0;
	private $numberOfPages = 0;

	public function __construct( $rowCount, $pageSize = self::DEFAULT_PAGE_SIZE ) {
		$this->numberOfRows = $rowCount;
		$this->pageSize = $pageSize;
		$this->setNumberOfPages();
		$this->setPageToView( $_REQUEST['pageToView'] );
		$this->setStartRowForPage( );
	}
	
	//------------------------------------------------
	public function getPageSize() {
		return $this->pageSize;
	}

	//------------------------------------------------
	public function setPageToView( $pageToView ) {
		$this->pageToView = (int) $pageToView;
	
		// Do some error checking on input
		if (!isset($this->pageToView) // no page requested 
		|| !is_integer($this->pageToView) // non-integer
		|| $this->pageToView < 1 // negative number
		) {
			$this->pageToView = 1;
		} 
		
		// Don't allow page request higher than total number of pages
		if ($this->pageToView > $this->numberOfPages) {
			$this->pageToView = $this->numberOfPages;
		}
		
		return $this->pageToView;
	}
	
	//------------------------------------------------
	public function getPageToView() {
		return $this->pageToView;
	}
	
	//------------------------------------------------
	private function setStartRowForPage( ) {
		$this->startRowForPage = 
			($this->pageSize * $this->pageToView) 
			- $this->pageSize 
			+ 1;

		// Ensure not requesting a negative row number
		if ($this->startRowForPage < 0) {
			$this->startRowForPage = 1;
		}
	
		return $this->startRowForPage;	
	}
	
	//------------------------------------------------
	public function getStartRowForPage() {
		return $this->startRowForPage;
	}
	
	//------------------------------------------------
	public function setButtonStates($stmt, $rowNumber) {
		$this->setPrevButtonState();
		$this->setNextButtonState($stmt, $rowNumber);
	}
	
	//------------------------------------------------
	private function setPrevButtonState(  ) {
		// If this is first page, disabled the previous button
		$this->prevButtonState = "";
		if ($this->pageToView == 1) {
			$this->prevButtonState = "disabled";
		}
	}
	
	//------------------------------------------------
	private function setNextButtonState($stmt, $rowNumber) {
		// Try to get one more row; if no more, this is last page 
		// (disable the next button)
		$this->nextButtonState = "";
		if (!$row = db2_fetch_assoc( $stmt, $rowNumber )) {
			$this->nextButtonState = "disabled";
		}
	}
	
	//------------------------------------------------
	private function setNumberOfPages( ) {
		$this->numberOfPages = 
			// Round up number of rows divided by page size
			ceil( $this->numberOfRows / $this->pageSize );
			
		return $this->numberOfPages;
	}
	
	//------------------------------------------------
	public function renderViewControls () {
		echo <<<VIEW_CONTROLS

		<script type="text/javascript">
			function gotoPage(page) {
				document.listingForm.pageToView.value = page;
				document.listingForm.submit();
			}
		</script>
		
		Displaying page {$this->pageToView} 
		of {$this->numberOfPages}. 
		  
		<input type="button" value="First Page" 
			{$this->prevButtonState} 
			onclick="javascript:gotoPage(1);" />
		
		<input type="button" value="<< Previous" 
			{$this->prevButtonState}
			onclick="javascript:gotoPage({$this->pageToView}-1 );" />
		Go to Page: 
		
		<input type="text" name="pageToView" size="3" value="{$this->pageToView}" /> 
		
		<input type="submit" value="Go" />
		 
		<input type="button" value="Next >>" 
			{$this->nextButtonState}
			onclick="javascript:gotoPage({$this->pageToView}+ 1);" />
		
		<input type="button" value="Last Page" 
			{$this->nextButtonState}
			onclick="javascript:gotoPage({$this->numberOfPages});" />
		&nbsp;&nbsp;
		{$this->numberOfRows} records found. 
			
VIEW_CONTROLS;
	}
}
