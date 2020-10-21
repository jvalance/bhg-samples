<?php

class VGS_Paginator {
	const DEFAULT_PAGE_SIZE = 25;
	protected $pageSize;
	protected $totalResultRows;
	protected $numberOfPages;
	protected $pageToView = 1;
	protected $next;
	protected $previous;
	protected $nextButtonState;
	protected $prevButtonState;

	//------------------------------------------------
	public function __construct($totalResultRows, 
								$pageToView = 1, 
								$pageSize = self::DEFAULT_PAGE_SIZE)
	{
		$this->totalResultRows = $totalResultRows;
		$this->pageSize = $pageSize;
		$this->setPageToView($pageToView);
//		if ( ! is_integer($this->pageToView) ) $this->pageToView = 1; 
//		var_dump($this);
	}

	public function activate() {
		
		$this->setNumberOfPages();
		$this->setNext($this->pageToView + 1);
		$this->setPrevious($this->pageToView - 1);
		
		// Set next/previous button states
		if ($this->pageToView >= $this->numberOfPages) {
			$this->nextButtonState = ' disabled ';
		} else {
			$this->nextButtonState = '';
		}
		
		if ($this->pageToView <= 1) {
			$this->prevButtonState = ' disabled ';
		} else {
			$this->prevButtonState = '';
		}
		
	}
	public function setTotalResultRows($totalResultRows) {
		$this->totalResultRows = $totalResultRows;
	}
	
	public function setPageSize($pageSize) {
		$this->pageSize = $pageSize;
	}
	
	private function setNumberOfPages() {
		$this->numberOfPages = 
			ceil ( (int)$this->totalResultRows / $this->pageSize);
		
		// Make sure current page isn't greater than total number of pages 
		if ($this->pageToView > $this->numberOfPages) {
			$this->pageToView = $this->numberOfPages;
		}
	}
	
	public function setPageToView($pageToView) {
		if (! is_int((int)$pageToView) ) {
			$pageToView = 1;
		}
		// Don't allow page greater than total number of pages 
		elseif (isset($this->numberOfPages)  
		&& $pageToView > $this->numberOfPages) {
			$pageToView = $this->numberOfPages;
		} 
		// If less than 1 or non-numeric, default to page 1
		elseif ($pageToView < 1 || ! is_int( (int)$pageToView )) {
			$pageToView = 1;
		}
	
		$this->pageToView = $pageToView;
	}

	private function setNext($next) {
		if ($next > $this->numberOfPages) {
			$this->next = $this->numberOfPages;
		} else {
			$this->next = $next;
		}
	}

	private function setPrevious($previous) {
		if ($previous < 1) {
			$this->previous = 1;
		} else {
			$this->previous = $previous;
		}
	}
	 
	// GETTER METHODS ----------------------------------
	function getStartRow( ) {
		$rowNum = ($this->pageSize * $this->pageToView) - $this->pageSize + 1;
		if ($rowNum < 0) $rowNum = 1;
		return $rowNum;	
	}
	
	public function getPageSize() {
		return $this->pageSize;
	}

	public function getTotalResultRows() {
		return $this->totalResultRows;
	}

	public function getPageToView() {
		return $this->pageToView;
	}

	public function getNumberOfPages() {
		return $this->numberOfPages;
	}

	public function getNext() {
		return $this->next;
	}

	public function getPrevious() {
		return $this->previous;
	}

	public function getLast() {
		return $this->last;
	}

	public function getNextButtonState() {
		return $this->nextButtonState;
	}

	public function getPrevButtonState() {
		return $this->prevButtonState;
	}

}
