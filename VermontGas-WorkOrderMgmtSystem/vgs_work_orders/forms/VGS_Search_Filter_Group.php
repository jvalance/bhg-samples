<?php
require_once '../forms/VGS_Search_Filter.php';
require_once '../model/VGS_DB_Select.php';

class VGS_Search_Filter_Group {
	
	public $filters;
	private $logical_operator;
	
	function __construct() {
		$this->filters = array();
	}
	
	public function addFilter($name, $label, $operator = NULL) {
		if (isset($operator)) {
			$this->filters[$name] = new VGS_Search_Filter($name, $label, $operator);
		} else {
			$this->filters[$name] = new VGS_Search_Filter($name, $label);
		}
	}

	public function setLogicalOperator( $value ) {
		$value = trim($value);
		if ($value != 'and' && $value != 'or') {
			throw new Exception('Invalid logical operator passed to ' . __FUNCTION__ . ": $value. Value must be 'and' or 'or'.");
		} else {
			$this->logical_operator = $value;
		}
	}
	
	public function getLogicalOperator( ) {
		if (!isset($this->logical_operator)) 
			$this->setLogicalOperator('and');
		return $this->logical_operator;
	}

	public function setCheckbox( $name ) {
		$this->filters[$name]->dataType = 'CHECKBOX';
	}
	
	public function setDateField( $name ) {
		$this->filters[$name]->dataType = 'DATE';
		$this->filters[$name]->attribs .= 'class="datepicker"';
	}
	
	public function setUpperCase( $name ) {
		$this->filters[$name]->attribs .= ' onchange="this.value=this.value.toUpperCase();" ';
		$this->filters[$name]->attribs .= ' onblur="this.value=this.value.toUpperCase();" ';
	}
	
	public function setDefaultValue($name, $value) {
		$this->filters[$name]->defaultValue = $value;
	}
	
	public function setSpecialWhere($name) {
		$this->filters[$name]->specialWhere = true;
	}
	
	public function setSpecialView($name) {
		$this->filters[$name]->specialView = true;
	}
	
	public function setInputSize($name, $size) {
		$this->filters[$name]->inputSize = $size;
	}
	
	public function setDropDownList($name, $list, $multi = NULL) {
		$this->filters[$name]->dropDownList = $list;
		if (!is_null($multi) && $multi == 'multi-select') {
			$this->filters[$name]->multiSelect = true;
		} else {
			$this->filters[$name]->multiSelect = false;
		}
	}
	
	public function setReadOnly($name) {
		$this->filters[$name]->setReadOnly();
	}
	
	public function addAttribs($name, $attribs) {
		$this->filters[$name]->attribs .= ' ' . $attribs . ' ';
	}
	
	public function saveRestoreFilters(&$data) {
		// Restore last saved filters from $_SESSION, if requested
		if (isset($_GET['filtSts']) && $_GET['filtSts'] == 'restore') {
			if (isset($_SESSION['filters'][$_SERVER['PHP_SELF']])) {
				foreach ($_SESSION['filters'][$_SERVER['PHP_SELF']] as $filterName => $filterValue) {
// 					echo "<br>$filterName = $filterValue";
					$data[$filterName] = $filterValue;
				}
			}
		}
 
		// Always save current filters for this screen on each request
		unset($_SESSION['filters'][$_SERVER['PHP_SELF']]); // clear any previous filters
		foreach ($data as $fieldName => $fieldValue) {
			if (substr($fieldName, 0, 6) == 'filter' || ($fieldName == 'pageToView')) {
				$_SESSION['filters'][$_SERVER['PHP_SELF']][$fieldName] = $fieldValue;
			} 
		}
	}

	public function renderView(array $data) 
	{
		?>	
		<script type="text/javascript">
			<!--
			//jQuery functions
			$('document').ready(function() {
				// Set up defaults for date-pickers
				$.datepicker.setDefaults({
					dateFormat: 'yy-mm-dd', 
					buttonImage: '../shared/images/datepicker.gif',
					changeMonth: true,
					changeYear: true,
					showOn: 'button',
					buttonImageOnly: true, 
					buttonText: 'Calendar'});
			});
			function clearFilters() {
				$('.filter').val('');
				var filters = $('.filter');
				filters[0].form.submit();
			}
			//-->
		</script>

		<div id="search_filters">
		<a href="javascript:clearFilters()" >[Clear]</a> 
		<b>&nbsp;Filter on:</b>
		<?php  
		foreach ($this->filters as $filter) {
			if ( ! $filter->specialView) {
				$filter->renderView($data);
			}
		}
		?>
		</div>
		<?php 
	}
	
	public function renderWhere(array &$data, VGS_DB_Select $select) 
	{
		foreach ($this->filters as $fieldName => $filterObj) {
			if ( ! $filterObj->specialWhere ) {
				$filterObj->buildFilterWhere($data, $select, $this->logical_operator);
			}
		}
	}
}
