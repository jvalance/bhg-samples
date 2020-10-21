<?php
class Paginator {
	
	const DEFAULT_PAGE_SIZE = 25;
	private $pageSize = 0;
	private $pageToView = 0;
	private $startRowForPage = 0;
	private $prevButtonState = '';
	private $nextButtonState = '';

	public function __construct( $pageSize = self::DEFAULT_PAGE_SIZE ) {
		$this->pageSize = $pageSize;
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
	public function renderViewControls () {
		echo <<<VIEW_CONTROLS

		<script type="text/javascript">
			function gotoPage(page) {
				document.listingForm.pageToView.value = page;
				document.listingForm.submit();
			}
		</script>
		
		<input type="button" value="<< Previous" 
			{$this->prevButtonState}
			onclick="javascript:gotoPage({$this->pageToView}-1 );" />
		Go to Page: 
		
		<input type="text" name="pageToView" size="3" value="{$this->pageToView}" /> 
		
		<input type="submit" value="Go" />
		 
		<input type="button" value="Next >>" 
			{$this->nextButtonState}
			onclick="javascript:gotoPage({$this->pageToView}+ 1);" />
		
VIEW_CONTROLS;
	}
}
