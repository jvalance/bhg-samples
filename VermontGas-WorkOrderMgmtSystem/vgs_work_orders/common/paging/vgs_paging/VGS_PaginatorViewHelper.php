<?php 
require_once '../model/VGS_Paginator.php';

class VGS_PaginatorViewHelper {
	private $paginator;
	private $formName;
	
	public function __construct( VGS_Paginator $paginator, $formName = 'searchForm' ) {
		$this->paginator = $paginator;
		$this->formName = $formName;
	} 
	
	public function setFormName( $formName ) {
		$this->formName = $formName;
	}

	public function renderView() {
		echo $this->render_XofY(); 
		echo $this->render(); 
		echo $this->render_TotalRowCount(); 
	}

	public function render() {
		$pageControlString = 
			$this->render_first() .
			$this->render_previous() .
			$this->render_current() .
			$this->render_next() .
			$this->render_last();
		return $pageControlString;		
	}
	
	public function render_XofY() {
		$x = number_format($this->paginator->getPageToView());
		$y = number_format($this->paginator->getNumberOfPages());
		return 
			'<span id="page_XofY">' . 
				"Page $x of $y." .
			'</span>';
	}
	public function render_TotalRowCount() {
		return 
			'<span id="paging_rowcount">' . 
				number_format($this->paginator->getTotalResultRows()) .
			' matches.</span>';
	}
	
	public function render_first() {
		return '<input type="button" value="First Page" ' .
			'id="first_button" class="paging_button" '. 
			$this->paginator->getPrevButtonState() . 
			'onclick="javascript:gotoPage(1);" />';
	}
		
	public function render_previous() {
		return '<input type="button" value="<< Previous" ' . 
			'id="previous_button" class="paging_button" '. 
			$this->paginator->getPrevButtonState() .
			'onclick="javascript:gotoPage('.  $this->paginator->getPrevious() . ');" />';
	}
	public function render_current() {
		return 'Go to Page: <input type="text" name="pageToView" size="3" value="'
		. $this->paginator->getPageToView() . 
		'" />  <input type="submit" id="gopage_button" class="paging_button" value="Go" />';
	}
		 
	public function render_next() {
		return '<input type="button" value="Next >>"' . 
			'id="next_button" class="paging_button" '. 
			$this->paginator->getNextButtonState() .
			'onclick="javascript:gotoPage('	.  $this->paginator->getNext() . ');" />';
	}
		
	public function render_last() {
		return '<input type="button" value="Last Page"' .
			'id="last_button" class="paging_button" '. 
			$this->paginator->getNextButtonState() .
			'onclick="javascript:gotoPage('	.  $this->paginator->getNumberOfPages() . ');" />';
	}
	public function render_gotoPageJS() {
		return <<<GOTOPAGEJS
		<script type="text/javascript">
			function gotoPage(page) {
				document.{$this->formName}.pageToView.value = page;
				document.{$this->formName}.submit();
			}
		</script>
GOTOPAGEJS;
	}
	
}