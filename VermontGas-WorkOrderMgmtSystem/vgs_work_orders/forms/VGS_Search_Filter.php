<?php

class VGS_Search_Filter {
	
	public $fieldName;
	public $filterFieldName;
	public $fieldLabel;
	public $compareOperator;
	public $inputSize;
	public $defaultValue;
	public $dropDownList;
	public $attribs; 
	public $dataType; 

	/**
	 * If true, this will be rendered using a drop-down list, as a group of checkboxes allowing multiple values to be selected.
	 * @var boolean
	 */
	public $multiSelect;
	
	/**
	 * If true, the filter field will be readonly (no change allowed), 
	 * but can be initialized through the query string on a GET request.
	 * @var boolean
	 */
	private $isReadOnly;
	
	/**
	 * If true, this will not automatically be generated in the WHERE clause
	 * @var boolean 
	 */
	public $specialWhere;
	/**
	 * If true, this will not automatically be rendered in the VIEW
	 * @var boolean 
	 */
	public $specialView;
	
	function __construct($name, $label, $oper = '=') {
		$this->fieldName = trim($name);
		$this->filterFieldName = 'filter_' . trim($name);
		$this->fieldLabel = $label;
		$this->compareOperator = $oper;
		$this->dropDownList = NULL;
		$this->defaultValue = NULL;
		$this->specialWhere = false;
		$this->specialView = false;
		$this->inputSize = 10;
	}
	
	public function setReadOnly() {
		$this->isReadOnly = true;
	}
	
	public function getReadOnly() {
		return $this->isReadOnly;
	}
	
	public function setDateField( ) {
		$this->dataType = 'DATE';
		$this->attribs .= 'class="datepicker"';
	}
	
	public function buildFilterWhere(&$screenData, VGS_DB_Select $sel, $logicalOper = NULL) 
	{
		$filterValue = $screenData[$this->filterFieldName];
		
		if (is_null($logicalOper) || trim($logicalOper) == '') $logicalOper = 'and';
		
		// If default value is set and filter field is not set, use default.
		if (isset($this->defaultValue) 
		&& !isset($filterValue) ) {
			$filterValue = $this->defaultValue;
		}
		
		// Check if filter values were passed from form
		$hasFilterValues = false;
		if (is_array($filterValue)) {
			// If filterValue is an array, this is a multi checkbox filter.
			foreach ($filterValue as $testFilterValue) {
				if (trim($testFilterValue) != '') {
					$hasFilterValues = true;
					break;
				}
			}
		} else {
			if (trim($filterValue) != '') {
				$hasFilterValues = true;
			}
		}
		
		// Add WHERE condition to DB_Select object
		if (isset($filterValue) && $hasFilterValues)
		{
			// If the filter is a multi-select list, the filter form values will be in an array
			// within $screenData. Loop through the array and create a list of OR conditions  
			// for the field equal to any of these values. 
			if ($this->multiSelect === true && is_array($filterValue) && count($filterValue) > 0) {
				$multiWhere = '';
				foreach ($filterValue as $multiValue) {
					if ($multiWhere != '') $multiWhere .= ' OR ';
					$multiWhere .= "{$this->fieldName} = ?";
				}
				$sel->where($multiWhere, $logicalOper, $filterValue);
			} 
			elseif ($this->compareOperator != 'LIKE') {
				// Handle other comparison operators besides LIKE
				$sel->where("{$this->fieldName} {$this->compareOperator} ?", 
	    					$logicalOper, 
	    					trim($filterValue) 
							);
			} 
			else {
				// Special handling for LIKE operator
				$sel->where("lower(trim({$this->fieldName})) LIKE ?",
								$logicalOper,
								"%" .strtolower(trim($filterValue)). "%"
							  );					
			}
		}
	}
	
	public function renderView(&$screenData) {
		if (isset($this->dropDownList)) { 
			$selected = isset($screenData[$this->filterFieldName]) 
				? $screenData[$this->filterFieldName] 
				: $this->defaultValue;
			
			if ($this->multiSelect === true) { 
				echo VGS_Search_Filter::createMultiSelectOptions(
						$this->filterFieldName, 
						$this->dropDownList, 
						$selected,
						$this);
			} else {
				$options = VGS_Search_Filter::createSelectOptions(
								$this->dropDownList, 
								$selected
							);
				?>
				<label for="<?= $this->filterFieldName ?>" class="optional"><?= $this->fieldLabel ?></label>
				<select 
					name="<?= $this->filterFieldName ?>" 
					id="<?= $this->filterFieldName ?>" 
					class="filter"
					<?= $this->attribs ?> 
					<?php if ($this->isReadOnly) echo ' readonly="readonly" class="disabled" tabindex="-1" '; ?> 
				>
					<?= $options  ?>
				</select>
				<?php 
			}
		} else { ?>
			<label for="<?= $this->filterFieldName ?>" class="optional"><?= $this->fieldLabel ?></label>
			<?php 
			if ($this->dataType == 'CHECKBOX') :
			?>
			<input type="checkbox" 
				class="filter"
				name="<?= $this->filterFieldName ?>" 
				id="<?= $this->filterFieldName ?>" 
				value="Y"
				<?php 
				$this->attribs; 
				if ('Y' == $screenData[$this->filterFieldName])
					echo ' checked="checked" ';  
				if ($this->isReadOnly) 
					echo ' readonly="readonly"  class="disabled" tabindex="-1" '; 
				?>
			> &nbsp;&nbsp;&nbsp;
			<?php 
			else : 
			?>
			<input type="text" 
				class="filter"
				size="<?= $this->inputSize ?>" 
				name="<?= $this->filterFieldName ?>" 
				id="<?= $this->filterFieldName ?>" 
				value="<?= $screenData[$this->filterFieldName] ?>"
				<?= $this->attribs ?> 
				<?php if ($this->isReadOnly) echo ' readonly="readonly"  class="disabled" tabindex="-1" '; ?>
			>
			<?php 
			endif;
			// Add date picker if this is a date field.
			if (trim($this->dataType) == 'DATE') : ?>
				<script type="text/javascript">
				<!--
				$('document').ready(function(){
					$('#<?php echo $this->filterFieldName ?>').datepicker();
				})
				//-->
				</script>
				<?php 
			endif;
		}
		
		// Add hidden field to preserve popup setting if passed via GET
		if ( $screenData['popup'] == true ) {
			?>
				<input name="popup" type="hidden" value="true" />
			<?php
		}		
	}


public static function createSelectOptions($optionsArray, $selectedValue) {
	// Removed empty option - added as an optional parameter in getCodeValuesList (Code_Values_Master.php)
	$optionsString = '';
	foreach ($optionsArray as $key => $value) {
		$optionsString .= "\t<option value=\"$key\"";
		if ((string)$key == (string)$selectedValue) {
			$optionsString .=  ' selected="selected"';
		};
		$optionsString .= ">$value</option>\n";
	}
	return $optionsString;
}


public static function createMultiSelectOptions(
		$fieldName,
		$optionsArray,
		$selectedValues,
		VGS_Search_Filter $filter)
{
	$optionsString = "<b>{$filter->fieldLabel}:</b> ";

	// 	pre_dump($selectedValues);


	foreach ($optionsArray as $key => $value) {
		$id = $fieldName . '-' . $value;

		if (in_array($key, $selectedValues)) {
			$class = 'class="filter checkBoxChecked "';
			$checked = 'checked="checked"';
		} else {
			$class = 'class="filter"';
			$checked = '';
		}

		if ($filter->getReadOnly() === true) {
			$readonly =  ' readonly="readonly" class="disabled" tabindex="-1" ';
		} else {
			$readonly = '';
		}

		$optionsString .= <<<MULTI_OPTION_STRING
			<label $class for="$id">
				<input id="$id" $class type="checkbox"
					$checked value="$key" name="{$fieldName}[]"
					{$filter->attribs} $readonly
				">
				$value
			</label>
MULTI_OPTION_STRING;
	}
	return $optionsString;
}

}

// Below: possible future enhancement for conditional readonly filter
//		if ($this->isReadOnly) :
//		<script type="text/javascript">
//		$('document').ready(function(){
//			$('#< ? = $this->filterFieldName ? >').attr('readonly', 'readonly');
//		})
//		//
//		</script>
//		<input type="hidden" name="protect_CV_GROUP" value="<= $screenData['protect_CV_GROUP'] ? >">
//		endif;
