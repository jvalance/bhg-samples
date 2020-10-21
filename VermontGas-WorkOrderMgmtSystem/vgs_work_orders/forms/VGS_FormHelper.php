<?php
require_once '../common/validators/Time_Validator.php';

/** 
 * @author John Valance
 * 
 * 
 */ 
class VGS_FormHelper {
	
	/** Contains an array of Zend_Form_Element to include on the form 
	 * @var array
	 */ 
	private $elements = array();
	
	/** The $metaData array will be used to generate labels, filters and validators 
	 * for the form elements automatically.   
	 * @var array
	 */ 
	public $metaData = array();

	/**
	 * Holds an array describing the field groupings for display
	 * @var array
	 */
	public $fieldGroups = array(); 

	/**
	 * Zend_View object for redering form and form elements 
	 * @var Zend_View
	 */
	private $view;
	
	/**
	 * Mode of accessing the form, i.e.: create, update, inqury.
	 * @var string
	 */
	public $mode;
	
	public function __construct() {
		$this->view = new Zend_View();
	}
	
	public function addCustomMetaDatum($name, $text, $type, $length, $precision = '') {
		$this->metaData[$name] = array(
			'COLUMN_NAME' => $name,
			'DATA_TYPE' => $type,
			'LENGTH' => $length,
			'NUMERIC_PRECISION' => $precision, 
			'COLUMN_TEXT' => $text,
			'CUSTOM_META' => true
		);
	}

	public static function getObjectLibrary($object, $objType) 
	{
		$i5o = new VGS_i5_Conn();
		$tko = $i5o->connect_default();

		$objd = $tko->ClCommandWithOutput("RTVOBJD OBJ($object) OBJTYPE($objType) RTNLIB(?) TEXT(?)");
		return $objd['RTNLIB'];
	}

	/**
	 * Adds metadata for a list of table names passed as an array. Much more efficient than doing each table separately.
	 * @param resource $conn
	 * @param array $tableList
	 * @throws Exception
	 */
	public function addMetaDataBatch($conn, $tableList) {
		$tableLibs = array();
		foreach ($tableList as $tableName) {
			$schema = self::getObjectLibrary($tableName, '*FILE');
			if (trim($schema)=='') {
				throw new Exception("Unable to find table metadata for $tableName. (FYI: Length of table name should not exceed 10; i.e. use system table name.)");
			} else {
				$tableLibs[$tableName] = $schema;
			}
		}
	
		$tableWhereAry = array();
		foreach ($tableLibs as $table => $lib) {
			$tableWhereAry[] = 
				"(upper(trim(table_schema)) = upper(trim('$lib')) " .
				" and upper(trim(system_table_name)) = upper(trim('$table')))";
		}
		// "OR" together all the table/schema conditions, to create the complete WHERE clause
		$whereClause = implode(' OR ', $tableWhereAry);
		
		$syscols = new VGS_DB_Table($conn);
		$query = "select * from qsys2/syscolumns where $whereClause";
		$rs = $syscols->execListQuery($query);
	
		while ($sysColumn = db2_fetch_assoc($syscols->stmt)) {
			// Add each column's metadata to the master metadata array
			$colName = $sysColumn['COLUMN_NAME'];
			$this->metaData[$colName] = $sysColumn;
		}
	}
	
	
	public function addMetaData($conn, $table) {
		
		$schema = self::getObjectLibrary($table, '*FILE');
		
		if (trim($schema)=='') {
			throw new Exception("Unable to find table metadata for $table. (FYI: Length of table name should not exceed 10; i.e. use system table name.)");
		}
		
		$syscols = new VGS_DB_Table($conn);
		$query = "select * from qsys2/syscolumns 
					where table_schema = '$schema' 
					and system_table_name = '$table' ";
		$rs = $syscols->execListQuery($query);
		
		while ($sysColumn = db2_fetch_assoc($syscols->stmt)) {
			// Add each column's metadata to the master metadata array 
			$colName = $sysColumn['COLUMN_NAME'];
			$this->metaData[$colName] = $sysColumn;
		} 
	}
	
	public function getElements( ) {
		return $this->elements;
	}
	
	public function setMultiOptions( $fieldName, $optionsList ) {
		if (is_object($this->elements[$fieldName])) {
			$this->elements[$fieldName]->setMultiOptions($optionsList);
		}
	}
	
	public function setDescription( $fieldName, $description ) {
		$this->elements[$fieldName]->setDescription($description);
	}
	
	public function getMetaData( ) {
		return $this->metaData;
	}
	
	public function addElement( $name, $element ) {
		$this->elements[$name] = $element;
	}
	
 
	public function addFieldGroup( $fieldList, $fieldGroup, $caption) {
		$unknowns = array();	// to return field names not found
		
		// Add field group
		$this->fieldGroups[$fieldGroup]['caption'] = $caption;
		$this->fieldGroups[$fieldGroup]['fieldlist'] = $fieldList;
		 
		$fields = $this->splitNames($fieldList);
		foreach ($fields as $fieldName) {
			if ( array_key_exists($fieldName, $this->metaData)) {
				$this->setElementProperty( $fieldName, 'group', $fieldGroup );
				$this->setElementProperty( $fieldName, 'include', true );
				$this->setElementProperty( $fieldName, 'label-class', '' );
			} else {
				$unknowns[] = $fieldName;
			}
		}
		return (count($unknowns) > 0) ? $unknowns : true;
	}
	
	public function setElementsProperties( $namesList, $property, $value) {
		$names = $this->splitNames($namesList);
		$unknowns = array();	// to return field names not found
		
		foreach ($names as $name) {
			if ( array_key_exists($name, $this->metaData)) {
				$this->setElementProperty( $name, $property, $value );
			} else {
				$unknowns[] = $name;
			}
		}
		return (count($unknowns) > 0) ? $unknowns : true;
	}
	
	public function setElementProperty( $name, $property, $value ) {
		$this->metaData[$name][$property] = $value;
	}
	
	/**
	 * Accepts a comma separated list of field names and parses them into an array.
	 * @param string $namesList List of field names to be converted to array,
	 * @return true if no errors, or array of field names not found in this->elementMetaData.
	 */
	public function splitNames( $namesList ) {
		$names = explode(',', $namesList);	// create array from comma separated string
		$names = array_map('trim', $names);	// trim field names
		return $names;
	}
	
	/**
	 * Generates Zend_Form_Elements from the meta data stored in this->elementMetaData;
	 * Resulting Zend_Form_Elements are stored in array this->elements;
	 * Only elements where elementMetaData['include'] is set to true will be generated 
	 * as Zend_Form_Elements, and added to the elements array.  
	 * @return Count of form elements generated and added to this->elements 
	 */
	public function addElementsFromMetaData( $mode = NULL ) {
		
		// If $mode is inquiry or delete, set all fields to output only and disabled. 
		if ( isset($mode) && ($mode == 'inquiry' || $mode == 'delete') ) {
			// Set output only for all fields in inquiry mode
			foreach ($this->fieldGroups as $fg => $fgAttrs) {
				$this->setElementsProperties($fgAttrs['fieldlist'], 'output_only', true);
				$this->setElementsProperties($fgAttrs['fieldlist'], 'attribs', array('disabled'=>'disabled'));
			}
		}
		
		// Create Zend_Form_Element_xxxxxx objects for each field included on the form.
		$count = 0;
		foreach ($this->metaData as $meta) {
			if ( isset($meta['include']) && (boolean)$meta['include'] == true) {
				$this->elements[$meta['COLUMN_NAME']] = 
					$this->buildElementFromMetaData($meta);
				$count++;
			}
		}
		return $count;
	}

	private function buildElementFromMetaData(&$elemMeta) {
		if (!isset($elemMeta['input_type'])) {
			$elemMeta['input_type'] = 'text';
		}
		switch ($elemMeta['input_type']) {
			case 'text': 
				$elem = new Zend_Form_Element_Text($elemMeta['COLUMN_NAME']);
				break; 
			case 'password': 
				$elem = new Zend_Form_Element_Password($elemMeta['COLUMN_NAME']);
				break; 
			case 'textarea': 
				$elem = new Zend_Form_Element_Textarea($elemMeta['COLUMN_NAME']);
				$elem->setOptions(array('cols'=>40, 'rows'=>5));
				break; 
			case 'select': 
				$elem = new Zend_Form_Element_Select($elemMeta['COLUMN_NAME']);
				break; 
			case 'multi-select': 
				$elem = new Zend_Form_Element_Multiselect($elemMeta['COLUMN_NAME']);
				break;  
			case 'multi-checkbox': 
				$elem = new Zend_Form_Element_MultiCheckbox($elemMeta['COLUMN_NAME']);
				break; 
			case 'y/n': 
				$elem = new Zend_Form_Element_Checkbox($elemMeta['COLUMN_NAME']);
				$elem->setCheckedValue('Y');
				$elem->setUnCheckedValue('N');
				break; 
		}
		$elem->setName($elemMeta['COLUMN_NAME']);
		// Abbreviate certain terms
		$text_label = $elemMeta['COLUMN_TEXT'];
		$text_label = str_replace('Work Order', 'W/O', $text_label);
		$elem->setLabel ( $text_label );
		$elem->clearDecorators();
		
		$elem->addDecorator('ViewHelper');
		$elem->setView($this->view);
	
		if (isset($elemMeta['attribs']) && is_array($elemMeta['attribs'])) {
			foreach ($elemMeta['attribs'] as $attrib => $attribValue) {
				$elem->setAttrib($attrib, $attribValue);
			}
		}
		
		$this->setElementDataTypeFilter($elem, $elemMeta);

		if (isset($elemMeta['output_only']) 
		&& (boolean) $elemMeta['output_only'] == true) 
		{
			$elem->setAttrib('readonly', 'readonly')
				->setAttrib('class', 'disabled')
				->setAttrib('tabindex', '-1');
			$elem->clearValidators();
		} else {
			if ($this->mode != 'inquiry'
			&& isset($elemMeta['required']) 
			&& (boolean) $elemMeta['required'] == true) 
			{
				$elem->setRequired ( true );
				$dataType = strtoupper(trim($elemMeta['DATA_TYPE']));
				if ($dataType == 'DECIMAL' || $dataType == 'NUMERIC') {
					$elem->addValidator(new Zend_Validate_GreaterThan(0));
				}
				$colName = $elemMeta['COLUMN_NAME'];
				$this->metaData[$colName]['label-class'] .= ' required ';
			} 
		}
		
		return $elem;
	}
	
	private function setElementDataTypeFilter( Zend_Form_Element $elem, &$meta ) {
		$dataType = strtoupper(trim($meta['DATA_TYPE']));
		switch ($dataType) {
			case 'VARCHAR' : 
			case 'CHAR' :
				$elem->addFilter ( 'StripTags' );
				$elem->addFilter ( 'StringTrim' );
				$elem->addValidator( 'StringLength', false, array('max' => $meta['LENGTH']));
				$length = ((int)$meta['LENGTH'] > 40 ? '40' : $meta['LENGTH'] );
				if ($meta['input_type'] == 'text') 
					$elem->setAttrib('size', "$length");
				break;    
				
			case 'DECIMAL' : 
			case 'NUMERIC' : 
			case 'FLOAT' : 
				$integ = (int) $meta['NUMERIC_PRECISION'] - (int) $meta['NUMERIC_SCALE'];
				$fract = $meta['NUMERIC_SCALE'];
				$regex = '';
				if ($fract == '0') {
					$elem->addValidator( 'Int' );
					$elem->addValidator( 'StringLength', false, array('max' => $meta['NUMERIC_PRECISION']));
				} else {
					$regex = '/^(\d{1,' . $integ . '})?(\.\d{1,' . $fract . '})?$/';	
					$regexMessage = array('regexNotMatch' => "Value must be numeric with a max of $integ whole digits and $fract decimal places.");
					$elem->addValidator('regex', false, array($regex, 'messages' => $regexMessage));
				}
				$length = $meta['NUMERIC_PRECISION'];
				if ($meta['input_type'] == 'text') {
					if ($dataType == 'FLOAT') {
						$elem->setAttrib('size', "12");
					} else {
						$elem->setAttrib('size', "$length");
					}
				}
					
				break;    
				
			case 'INTEGER' :
			case 'SMALLINT' :
			case 'INT' :
			case 'BIGINT' :
				$elem->addFilter ( 'StringTrim' );
				$elem->addValidator( 'Int' );
				$length = $meta['NUMERIC_PRECISION'];
				if ($meta['input_type'] == 'text') 
					$elem->setAttrib('size', "$length");
				break;    
				
			case 'DATE' :
				if ($meta['input_type'] == 'text') 
					$elem->setAttrib('size', '15');
				$elem->addValidator('Date',false, array('format'=>'MM-dd-yyyy'));
				break; 
				
			case 'TIME' :
			case 'TIMESTAMP' :
			case 'TIMESTMP' :
				$elem->setAttrib('size', '12');
				$elem->addValidator(new Time_Validator());
				break; 
		}
	}

	public function renderFieldGroup( $fieldGroup, Zend_Form $form ) { 
	?>		
		<table class="field_group" style="width:50%; margin-top: 8px">
		<caption><?= $this->fieldGroups[$fieldGroup]['caption'] ?></caption>
		<?php 	
		$fieldList = $this->fieldGroups[$fieldGroup]['fieldlist'];
		$fields = $this->splitNames($fieldList);
		
		foreach ($fields as $fieldName) {
			$elem = $form->getElement($fieldName);
			if ($this->metaData[$fieldName]['group'] == $fieldGroup ) {
				$messages = '';
				if (count($elem->getMessages()) > 0) {
					$messages = '<ul class="error"><li>';
					$messages .= implode('</li><li>', $elem->getMessages()); 
					$messages .= '</li></ul>';
				}
				echo <<<ELEM_RENDER
					<tr>
						<td id="label_{$this->metaData[$fieldName]}" class="field_label {$this->metaData[$fieldName]['label-class']}"> 
							{$elem->getLabel()}
						</td>
						<td class="field_value"> 
							{$elem->render()} {$elem->getDescription()}
							$messages
						</td>
					</tr>
ELEM_RENDER;
			}
		}
		echo '</table>';
	}
}

