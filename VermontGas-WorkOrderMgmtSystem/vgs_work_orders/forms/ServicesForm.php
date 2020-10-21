<?php
require_once '../forms/VGS_Form.php';
require_once '../model/Services.php';
require_once '../model/Premise.php';
require_once '../model/Pipe_Type_Master.php';
require_once '../model/Workorder_Master.php';
require_once '../common/validators/DepthFeet_Validator.php';
require_once '../common/validators/DepthInches_Validator.php';

/** 
 * @author John
 */
class ServicesForm extends VGS_Form 
{
	private $db_object;
	public  $entryFormat;

	public function __construct( $conn, $entryFormat = '') {
		parent::__construct ( $conn );
		
		$sec = new Security();
		$sec->checkPermissionByCategory('SVC', $this->mode);

		$this->db_object = new Services($conn);
		$this->setEntryFormat();
		
		$this->fh->addMetaData($this->conn, "SERVICES");
		$this->fh->addMetaData($this->conn, "WOMAST");
		$this->setDefaultElements( );
		
		$this->screen_title = 'Service Record - ' . ucfirst($this->mode);
		
	}

	public function processScreen() {
		
		if ($this->inputs['reload'] == 'Y') {
			$this->setOutputFormatsForScreen($this->inputs);
			$this->populate($this->inputs);
			$this->inputs['return_point'] = $this->return_point;
			return;
		} 
		
		if ($this->isDeleteMode()) {
			$this->deleteRecord();
			$this->returnToCaller();
		} else {
// 			$this->populate($this->inputs);
				
			$this->preProcessFormInputs();
			if ($this->validate()) {
				$this->setInputFormatsForDB2();
				
				if ($this->isCreateMode()) {
					$this->createRecord();
					// Go to premise xref table after creating new service record.
					$svnum = $this->inputs['SV_SERVICE_ID'];
					$url = $this->getXrefURL();
					$this->return_point = $url;
					$this->returnToCaller();
				} elseif ($this->isUpdateMode()) {
					// If batch edit, record data to pre-fill in next record
					if (isset($_SESSION['SERVICE_SELECTED_IDS'])) {
						// 	pre_dump($_SESSION);
						$this->recordPrefillData();
					}
					
					$this->updateRecord();
				}
				if ($this->inputs['reload'] == 'Y') {
					$this->setOutputFormatsForScreen($this->inputs);
					$this->populate($this->inputs);
				} else {
					$this->returnToCaller();
				}
			} else {
				$this->populate($this->inputs);
				$this->inputs['return_point'] = $this->return_point;
			}
		}
	}
	
	private function recordPrefillData() {
		$fieldsToPrefill = array (
			SV_MAIN_SIZE,		SV_MAIN_TYPE,		SV_MAIN_COATING,		SV_MAIN_PRESSURE,		SV_MAIN_SOIL_TYPE, 
			SV_MAIN_SOIL_OTHER, SV_TEST_PRESSURE, 	SV_TESTED_WITH_MAIN, 	SV_DURATION_HRS, 		SV_DURATION_MINS, 
			SV_DATE_COMPLETED, 	SV_INSTALLED_BY, 	SV_INSPECTED_BY
		);
		
		foreach ($fieldsToPrefill as $field) {
			if ( $this->__isset($field) ) {
				$_SESSION [PREFILL_DATA][$field] = $this->getElement($field)->getValue();
			}
		}
	}
	
	private function setEntryFormat() {
		
		$entryFormat = $defaultEntryFormat =  $_REQUEST['entryFormat'];
		
		if ($this->isUpdateMode()) {
			if (isset($this->inputs['SV_ENTRY_FORMAT'])) {
				$entryFormat = $this->inputs['SV_ENTRY_FORMAT'];
			} else {
				$svRec = $this->db_object->retrieve($_REQUEST);
				$svEntryFormat = $svRec['SV_ENTRY_FORMAT'];
				if ((int)$svEntryFormat == 0 
					&& isset($defaultEntryFormat)
					&& trim($defaultEntryFormat) != '')
				{
					$entryFormat = $defaultEntryFormat;
				} else {
					$entryFormat = $svEntryFormat;
				}
			}
		} else {
			$entryFormat = '0'; // show all fields in inquiry mode
		}
		
		// Default to 0 (all fields) if format is not valid 
		if ((int)$entryFormat < 0 || (int)$entryFormat > 5) { 
			$entryFormat = '0';
		}
		
		$this->entryFormat = $entryFormat;
	}
	
	public function createRecord() {
		return $this->db_object->create($this->inputs);
	}
	public function updateRecord() {
		return $this->db_object->update($this->inputs);
	}
	public function retrieveRecord() {
		return $this->db_object->retrieve($this->inputs);
	}
	public function deleteRecord() {
		return $this->db_object->delete($this->inputs);
	}
	
	public function setDefaultElements( ) 
	{	
		// Custom element for premise data from xref table
		// Include undisplayed div that will house premise-address table
		$premHTML = 'Premise(s)<br />(comma-sep list) :<br /><br />
			<a onclick="showPremAddrs()">Show/hide Premise Addresses</a><br />
			<div id="premAddrs" class="premAddrs">Premise Addresses</div>';
		$this->fh->addCustomMetaDatum('SPX_PREMISE_NUMS', $premHTML,'CHAR', '150');
		
		switch ((int)$this->entryFormat)
		{		
			case 1: $this->setDefaultElementsFormat1(); break;				
			
			case 2: $this->setDefaultElementsFormat2(); break;
			
			case 3: $this->setDefaultElementsFormat3(); break;
			
			case 4: $this->setDefaultElementsFormat4(); break;
			
			case 5: $this->setDefaultElementsFormat5(); break;
				
			case 0: default: $this->setDefaultElementsFormatAll(); break;
		}	

		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
//		$this->setFormElementAttributes();
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );

		// Make sure no fields are lingering in inputs from previous format
		foreach ($this->fh->metaData as $field => $meta) {
			if ($meta['include'] != '1' && isset($this->inputs[$field]))
				unset($this->inputs[$field]);
		}

		$cvm = new Code_Values_Master($this->conn);

		$cvList = $cvm->getCodeValuesList('SV_ENTRY_FORMAT', ' ');
		$this->fh->setMultiOptions('SV_ENTRY_FORMAT', $cvList);

		$cvList = $cvm->getCodeValuesList('TOWN', ' ');
		$this->fh->setMultiOptions('SV_CITY', $cvList);
		
		$cvList = $cvm->getCodeValuesList('SVC_STATES', ' ');
		$this->fh->setMultiOptions('SV_STATE', $cvList);
		
		if ($this->entryFormat == '0') {
			$cvList = $cvm->getCodeValuesList('REGULATOR_LOC', ' ');
			$this->fh->setMultiOptions('SV_REGULATOR_LOC', $cvList);
		}

		$cvList = $cvm->getCodeValuesList('SVC_STATUS', ' ');
		$this->fh->setMultiOptions('SV_SVC_STATUS', $cvList);
		
		$cvList = $cvm->getCodeValuesList('SVC_UPD_STS', ' ');
		$this->fh->setMultiOptions('SV_UPDATE_STATUS', $cvList);
		
		$cvList = $cvm->getCodeValuesList('SV_MATERIAL', ' ');
		$this->fh->setMultiOptions('SV_MATERIAL', $cvList);
		
		$cvList = $cvm->getCodeValuesList('PIPE_DIAM', ' ');
		$this->fh->setMultiOptions('SV_SIZE', $cvList);
		
		$cvList = $cvm->getCodeValuesList('PIPE_COATING', ' ');
		$this->fh->setMultiOptions('SV_COATING', $cvList);

		$cvList = $cvm->getCodeValuesList('PIPE_DIAM', ' ');
		$this->fh->setMultiOptions('SV_MAIN_SIZE', $cvList);
		
		$cvList = $cvm->getCodeValuesList('SV_MATERIAL', ' ');
		$this->fh->setMultiOptions('SV_MAIN_TYPE', $cvList);

		$cvList = $cvm->getCodeValuesList('PRESSURE', ' ');
		$this->fh->setMultiOptions('SV_MAIN_PRESSURE', $cvList);
		
		$cvList = $cvm->getCodeValuesList('WPE_SOILTYPE');
		$this->fh->setMultiOptions('SV_MAIN_SOIL_TYPE', $cvList);

		$cvList = $cvm->getCodeValuesList('METERLOC', ' ');
		$this->fh->setMultiOptions('SV_METER_LOC', $cvList);
		 
		$cvList = $cvm->getCodeValuesList('YES_NO_UNKNOWN', ' ');
		$this->fh->setMultiOptions('SV_CURB_STOP', $cvList);

		$cvList = $cvm->getCodeValuesList('PIPE_COATING', ' ');
		$this->fh->setMultiOptions('SV_MAIN_COATING', $cvList);

		if ($this->getElement('SV_JOINT_TYPE') != NULL) {
			$cvList = $cvm->getCodeValuesList('JOINT_TYPE');
			$this->fh->setMultiOptions('SV_JOINT_TYPE', $cvList);
		}
		
		// s/b not on fmt 4 & 5
		if ($this->entryFormat != '4' && $this->entryFormat != '5') {
			$cvList = $cvm->getCodeValuesList('YES_NO_UNKNOWN', ' ');
			$this->fh->setMultiOptions('SV_TRACER_WIRE', $cvList);

			$cvList = $cvm->getCodeValuesList('YES_NO_UNKNOWN', ' ');
			$this->fh->setMultiOptions('SV_FLOW_LIMITER', $cvList);

			$cvList = $cvm->getCodeValuesList('WO_FLOW_LIMITER_SIZE', ' ');
			$this->fh->setMultiOptions('SV_FLOW_LIMITER_SIZE', $cvList);
		}


		if ($this->isUpdateMode() || $this->isCreateMode()) {
			// add button to reload page with a different format
			$reloadFormatButton = <<<RELOADBUTTON
				<button onclick="reloadFormat('{$this->mode}'); return false;">Load Format</button>
RELOADBUTTON;
			$this->getElement('SV_ENTRY_FORMAT')->setDescription($reloadFormatButton);
		}
		
		if ($this->isCreateMode() || $this->isUpdateMode()) {
			// Add any validators
			
			$updStatus = $this->inputs['SV_UPDATE_STATUS'];
			if ($updStatus != 'INC') {
				$this->getElement('SV_DEPTH_FT')->addValidator(new DepthFeet_Validator());
				$this->getElement('SV_DEPTH_IN')->addValidator(new DepthInches_Validator());
			}
		}
		
		$this->getElement('SV_DEPTH_FT')
			->setLabel('Pipe Depth')
			->setDescription('Feet (0-15)<br/>&nbsp;&nbsp;(-1 = unknown)');
		$this->getElement('SV_DEPTH_IN')->setLabel('')->setDescription('Inches (0-11)');
		

		$this->getElement('SV_NAME')->setAttrib('size', '25');
		$this->getElement('SV_HOUSE')->setAttrib('size', '15');
		$this->getElement('SV_STREET')->setAttrib('size', '25');
		$this->getElement('SV_STATE')->setValue('VT');
		$this->getElement('SPX_PREMISE_NUMS')->setAttrib('cols', '25');
		$this->getElement('SPX_PREMISE_NUMS')->setAttrib('rows', '2');
		if (!in_array($this->entryFormat, array('2','3','4','5'))) 
		{
			$this->getElement('SV_LOT_NO')->setAttrib('size', '15');
			$this->getElement('SV_METH_OTHER')->setAttrib('size', '25');
		}
		$this->getElement('SV_JOINT_TYPE_OTHER')->setAttrib('size', '20');
		$this->getElement('SV_MAIN_SOIL_OTHER')->setAttrib('size', '20');
		if ($this->entryFormat != '4' && $this->entryFormat != '5') {
			$this->getElement('SV_INSTALLED_BY')->setAttrib('size', '30');
			$this->getElement('SV_INSPECTED_BY')->setAttrib('size', '30');
		}
		$this->getElement('SV_REMARKS')->setAttrib('cols', '25');
		$this->getElement('SV_REMARKS')->setAttrib('rows', '4');
		
		$this->getElement('SV_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('SV_CHANGE_TIME')->setAttrib('size', 30);
		
		// On edit & create, add button to populate address fields from premise number
		if( $this->isUpdateMode() || $this->isCreateMode() ) {
			$addrButton = '<button onclick="fillPremAddr(); return false">Fill Addr</button>';
			$this->getElement('SV_HOUSE')->setDescription($addrButton);
		}
	}
	
	public function reset() {
		parent::reset();

		$this->getElement('SV_STATE')->setValue('VT');
		$this->getElement('SV_SVC_STATUS')->setValue('ACT');
		
		$this->getCodeDescriptions();
		
	}
	
	/**
	 * The populate() function is used to load the screen initially from an existing record, and to load
	 * values when the screen is redisplayed on an error condition - This function should retrieve 
	 * any ancillary values needed to display the screen completely (i.e., descriptions for coded values)
	 * 
	 * @see Zend_Form::populate()
	 */	
	public function populate(array $data) 
	{
		
		parent::populate($data);
		
		// Override the database value for entry format, if a dafault format was specified. 
		$this->getElement('SV_ENTRY_FORMAT')->setValue($this->entryFormat);
		
		$this->getCodeDescriptions();
	
		// Prefill certain data if record has not been updated since conversion
		if ($this->isUpdateMode() && $this->getElement('SV_UPDATE_STATUS')->getValue() == 'CNV') {
			$this->getElement('SV_UPDATE_STATUS')->setValue('CMP');
			if (trim($this->getElement('SV_METER_LOC')->getValue() == '')) {
				$this->getElement('SV_METER_LOC')->setValue('OS');
			}
			if (trim($this->getElement('SV_MAIN_PRESSURE')->getValue() == '')) {
				$this->getElement('SV_MAIN_PRESSURE')->setValue('M');
			}
		}
		
		// If batch edit, pre-fill data from previous record
		if ( isset($_SESSION['PREFILL_DATA']) ) {
			$this->fillPreviousBatchData();
		}
		
		$this->setFormElementAttributes();
		
	}
	
	private function fillPreviousBatchData() {
		// For each field listed in session array of prefill data,
		// check if destination field is empty (including a number of custom checks).
		// If not empty, skip to next field. If empty, prefill with session data.
 		foreach ($_SESSION['PREFILL_DATA'] as $field=>$data) {
 			$data == trim($data);
 			
 			// Only check elements that exist in this format of the form
 			if ( ! $this->__isset($field)) {
 				continue;	
 			}
 			
 			$current = $this->getElement($field)->getValue();
 				// echo "<h3>" . $field . "</h3>";
 			
 			// Special case for yes/no field: only prefill if currently set to "no"
 			if ($field == 'SV_TESTED_WITH_MAIN') {
 				if (! $current == "N") {
 					continue;
 				}		
 			}
 			
 			// Special case for date field 
 			if ($field == 'SV_DATE_COMPLETED') {
 				// Treat "01-0001-01" as zero
 				if (! ( ($current == "01-0001-01" || trim($current == '') || $current === 0 )
 						&& $data != "0001-01-01")) {
 					continue;
 				}
 				
 				// if date is in "YYYY-MM-DD" format, change to "MM-DD-YYYY"
 				if ( strlen($data) == 10 && strpos($data, '-', 0) == 4 && strpos($data, '-', 5) == 7) {
 					$data = substr($data, 5, 5) . '-' . substr($data, 0, 4);
 				}
 			}
 			
 			// Special case for "other" text field: only prefill if related checkbox field is blank or set to "other"
 			if ($field == 'SV_MAIN_SOIL_OTHER') {
 				$soilTypes = $this->getElement('SV_MAIN_SOIL_TYPE')->getValue();
  				if (! (in_array ('OTH', $soilTypes) ||
  						(count($soilTypes) == 1 && ( trim($soilTypes[0]) == '' || $soilTypes[0] === 0 )) )) {
  					continue;
  				}
 			}
 			
 		 	// Special empty-test for array field
 			if ( is_array($current) ) {
 				if ( ! (count($current) == 1 && ( trim($current[0]) == '' || $current[0] === 0 ) )) {
 					continue;
 				}
 			}
 			
 			// General case for most fields: don't prefill if field is currently not blank
 			if ( ! (trim($current) == '' || $current === 0 )) {	
 				continue;			
 			}
			
 			// Actually do the pre-fill
 				// echo $field . "<br />";
 				// pre_dump($this->getElement($field)->getValue());
 			$this->getElement($field)->setValue($data);
 			
 		}
	}
	
	private function setFormElementAttributes() {
		
		// Add button to view DB Update Log for this service
//		$jsonKeys = array("SV_SERVICE_ID" => $this->getElement('SV_SERVICE_ID')->getValue());
		$logLink = 'dblListCtrl.php?key_field=SV_SERVICE_ID&key_value=' . $this->getElement('SV_SERVICE_ID')->getValue();
		$logLink = "javascript:openPopUp('$logLink', 'DBUpdateLog');";
		$logButton = new VGS_NavButton('View Update Log', $logLink, 'js');
		$logButton->setIcon(VGS_NavButton::POPUP_ICON);
		$this->getElement('SV_CHANGE_TIME')->setDescription('<br />' . $logButton->render('return'));
	
		// Add google map button to city field
		$mapButton = "<button onclick=\"showMap(); return false;\">Map Service</button>";
		$this->getElement('SV_CITY')->setDescription($mapButton);

		// Add premise xref button to premise field, but not on create screen
		if (!$this->isCreateMode()){			
			$url = $this->getXrefURL();
			$this->addInlinePopupButton('Premise Search', $url, VGS_NavButton::PREMISES_ICON, 'SPX_PREMISE_NUMS');
		}
	}
	
	// If premise(s) already linked to this service, filter screen to show them
	// If not, show available premises and pre-fill town and first word of street
	private function getXrefURL() {
		$addr = $this->getAddrData();
		if (isBlankOrZero($this->getElement('SPX_PREMISE_NUMS')->getValue())) {
			$linkFilter = '&filter_SVID_ONLY=A';
			$townFilter = '&filter_UPCTC='.$addr['town'];
			$streetFilter = "&filter_UPSAD=".$addr['streetName'];
		} else {
			$linkFilter = '&filter_SVID_ONLY=L';
		}
		$url = "svServicePremiseXrefCtrl.php?filter_SV_SERVICE_ID=".$addr['svID'];
		$url .= $linkFilter . $streetFilter . $townFilter;
		
		if (!$this->isCreateMode()) {
			$url .= "&popup=true";
		}
	
		return $url;
	}	
	
	// Get text address data for use in various cross-db functions
	private function getAddrData() {
		$cvm = new Code_Values_Master($this->conn);
		// Use input field for Service ID on create mode
		if ($this->isCreateMode()) {
			$addr['svID'] = $this->inputs['SV_SERVICE_ID'];
		} else {
			$addr['svID'] = $this->getElement('SV_SERVICE_ID')->getValue();
		}
		$addr['house'] = trim(ltrim($this->getElement('SV_HOUSE')->getValue(), '0'));
		$addr['street'] = trim($this->getElement('SV_STREET')->getValue());
		$addr['town'] = trim(strtoupper($cvm->getCodeValue('TOWN', $this->getElement('SV_CITY')->getValue())));
		// Default value of state is VT
		$addr['state'] = "VT";
		$addr['state'] = trim($this->getElement('SV_STATE')->getValue());
		// Get first word of street name
		$arr = explode (" ", $this->getElement('SV_STREET')->getValue());
		$addr['streetName'] = $arr[0];
	
		return $addr;
	}
	
	private function getCodeDescriptions() {
		
	}


	/**
	 * Custom validations for this form - this overrides the validate() method 
	 *    defined in VGS_Form.php, and calls the Zend_Form isValid() method. 
	 * @see VGS_Form::validate()
	 * @see Zend_Form::isValid()
	 */	
	public function validate() 
	{
		
		// Turn off required fields if update status is "incomplete",
		// or if service status is "inactive" or "retired."
		if ($this->inputs['SV_UPDATE_STATUS'] == 'INC' ||
		    $this->inputs['SV_SVC_STATUS'] == 'INA' || 
			$this->inputs['SV_SVC_STATUS'] == 'RET' ) {
			foreach ($this->getElements() as $fieldName => $elem) {
				if ($elem->isRequired()) 
					$elem->setRequired ( false );
			}
			$this->valid = parent::isValid($this->inputs);
				
			$this->valid = true;
			$this->validatePremise();
			return $this->valid;
		}
		
		$this->valid = parent::isValid($this->inputs);
		
		if ($this->isBlankOrZero($this->inputs['SV_DEPTH_FT'])
		&&  $this->isBlankOrZero($this->inputs['SV_DEPTH_IN'])) {
			$depthMsg = "Depth (feet and/or inches) must be entered.";
			$this->getElement('SV_DEPTH_FT')->addError($depthMsg);
			$this->getElement('SV_DEPTH_IN')->addError($depthMsg);
			$this->valid = false;
		}

		if ($this->isBlankOrZero($this->inputs['SV_LENGTH_FT'])
		&&  $this->isBlankOrZero($this->inputs['SV_LENGTH_IN'])) {
			$lengthMsg = "Length (feet and/or inches) must be entered.";
			$this->getElement('SV_LENGTH_FT')->addError($lengthMsg);
			$this->getElement('SV_LENGTH_IN')->addError($lengthMsg);
			$this->valid = false;
		}
		
		
		if (($this->entryFormat == 1) || ($this->entryFormat == 2) || ($this->entryFormat == 3))
		{
		// Turn off required fields if update status is "incomplete",
		// or if service status is "inactive" or "retired."
		if ($this->inputs['SV_UPDATE_STATUS'] != 'INC' ||
		    $this->inputs['SV_SVC_STATUS'] != 'INA' || 
			$this->inputs['SV_SVC_STATUS'] != 'RET' ){
				if ($this->isBlankOrZero($this->inputs['SV_TEST_PRESSURE'])
				&& trim($this->inputs['SV_TESTED_WITH_MAIN']) != 'Y') 
				{
					$errMsg = 'Either "Test Pressure" must be entered or "Tested With Main" selected.';
					$this->getElement('SV_TEST_PRESSURE')->addError($errMsg);
					$this->getElement('SV_TESTED_WITH_MAIN')->addError($errMsg);
					$this->valid = false;
				}
			}
		} // end of entry format
		
		if (in_array('OTHER',$this->inputs['SV_JOINT_TYPE']) 
		&& trim($this->inputs['SV_JOINT_TYPE_OTHER']) == '') {
			$errMsg = 'Please enter a description for Other-Conn@Main.';
			$this->getElement('SV_JOINT_TYPE_OTHER')->addError($errMsg);
			$this->valid = false;
		}
		
		if (in_array('OTH',$this->inputs['SV_MAIN_SOIL_TYPE']) 
		&& trim($this->inputs['SV_MAIN_SOIL_OTHER']) == '') {
			$errMsg = 'Please enter a description for Other Soil Condition.';
			$this->getElement('SV_MAIN_SOIL_OTHER')->addError($errMsg);
			$this->valid = false;
		}
		
		// Turn off required fields if update status is "incomplete",
		// or if service status is "inactive" or "retired."
		if ($this->inputs['SV_UPDATE_STATUS'] != 'INC' ||
		    $this->inputs['SV_SVC_STATUS'] != 'INA' || 
			$this->inputs['SV_SVC_STATUS'] != 'RET' ) {
			if (trim($this->inputs['SV_MAIN_TYPE']) == 'STEEL'
			&& trim($this->inputs['SV_MAIN_COATING']) == '') {
				$errMsg = 'Main Coating is required if Main Type is Steel.';
				$this->getElement('SV_MAIN_COATING')->addError($errMsg);
				$this->valid = false;
			}
		}
		
		if ($this->isBlankOrZero($this->inputs['SV_MAIN_DEPTH_FT'])
		&&  $this->isBlankOrZero($this->inputs['SV_MAIN_DEPTH_IN'])) {
			$depthMsg = "Main Depth (feet and/or inches) must be entered.";
			$this->getElement('SV_MAIN_DEPTH_FT')->addError($depthMsg);
			$this->getElement('SV_MAIN_DEPTH_IN')->addError($depthMsg);
			$this->valid = false;
		}
		
		$this->validatePremise();
		return $this->valid ;
	}

	public function validatePremise() {
		$prems = $this->getElement('SPX_PREMISE_NUMS')->getValue();
		if (isBlankOrZero($prems)) {
			return;
		}
		$premsArray = explode(",", $prems);
		foreach ($premsArray as $premNo) {
	
			// Check that premise # is numeric
			if (!ctype_digit( trim($premNo) )) {
				$numMsg = "The value $premNo is not numeric.";
				$this->getElement('SPX_PREMISE_NUMS')->addError($numMsg);
				$this->valid = false;
				continue;
			}
		
			// Check that premise # exists in UPRM table.
			$premise = new Premise();
			$prCheck = $premise->retrieve ( trim($premNo) );
			if ( !is_array ($prCheck) ) {
				$dneMsg = "Cannot link to Premise # $premNo because it does not exist in UPRM table.";
				$this->getElement('SPX_PREMISE_NUMS')->addError($dneMsg);
				$this->valid = false;
				continue;
			}
				
			// Check that premise # is not already linked to a different service ID.
			$service = new Services();
			$linkID = trim( $service->getXrefServiceID(trim( $premNo )) );
			$svID = trim( $this->getElement('SV_SERVICE_ID')->getValue() );
			if ( !isBlankOrZero($linkID)   &&  $linkID != $svID ) {
				$dupMsg = "Cannot create link because Premise #{$premNo} is already linked to Service ID {$linkID}.";
				$this->getElement('SPX_PREMISE_NUMS')->addError($dupMsg);
				$this->valid = false;
				continue;
			}			
		}
	}

	private function setDefaultElementsFormat1() {
		// Begin fmt1xxx elements for Card Format 1 - JAF
		$fmt1KeyFL = "SV_ENTRY_FORMAT, SV_SERVICE_ID, SV_STREET, SV_HOUSE, SV_LOT_NO,
						      SV_CITY, SV_STATE, SV_NAME, SPX_PREMISE_NUMS";
		$this->fh->addFieldGroup($fmt1KeyFL, 'fmt1Keys', 'Format 1 Premise Information');
		$this->fh->setElementsProperties('SV_SERVICE_ID', 'output_only', true);
		$this->fh->setElementsProperties('SV_STREET, SV_HOUSE, SV_CITY, SV_STATE', 'required', true);
		$this->fh->setElementsProperties('SV_ENTRY_FORMAT, SV_CITY, SV_STATE', 'input_type', 'select');
		$this->fh->setElementsProperties('SPX_PREMISE_NUMS', 'input_type', 'textarea');
		
		$fmt1SrvFL = "SV_SIZE, SV_MATERIAL, SV_DATE_COMPLETED, SV_CURB_STOP,
							  SV_METH_TRENCH, SV_METH_HDD, SV_METH_HOG, SV_METH_PLOWED, SV_METH_OTHER, 
							  SV_ROW, SV_FILE_NO, SV_JOINT_TYPE, SV_JOINT_TYPE_OTHER, SV_METER_LOC,
							  SV_FLOW_LIMITER, SV_FLOW_LIMITER_SIZE, SV_TEST_PRESSURE, SV_TESTED_WITH_MAIN, 
							  SV_DURATION_HRS, 
							  SV_DURATION_MINS, SV_DEPTH_FT, SV_DEPTH_IN, SV_TRACER_WIRE, SV_CAD_WELD_MAIN,
							  SV_LENGTH_FT, SV_LENGTH_IN, SV_INSTALLED_BY, SV_INSPECTED_BY";
		$this->fh->addFieldGroup($fmt1SrvFL, 'fmt1Srv', 'Format 1 Service Information');
		$this->fh->setElementsProperties('SV_SIZE, SV_MATERIAL, SV_DATE_COMPLETED, SV_CURB_STOP, ' .
										 'SV_INSTALLED_BY, SV_INSPECTED_BY', 'required', true);
		$this->fh->setElementsProperties('SV_SIZE, SV_MATERIAL, SV_TRACER_WIRE, SV_CURB_STOP, SV_FLOW_LIMITER, ' .
										 'SV_FLOW_LIMITER_SIZE, SV_METER_LOC', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_ROW, SV_CAD_WELD_MAIN, ' .
										 'SV_METH_TRENCH, SV_METH_HDD, SV_METH_HOG, SV_METH_PLOWED', 'input_type', 'y/n');
		$this->fh->setElementsProperties('SV_JOINT_TYPE', 'input_type', 'multi-checkbox');
		$this->fh->setElementsProperties('SV_METH_OTHER', 'input_type', 'text');
		$this->fh->setElementsProperties('SV_TESTED_WITH_MAIN', 'input_type', 'y/n');
		
		$fmt1MainFL = "SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING, SV_MAIN_DEPTH_FT, SV_MAIN_DEPTH_IN,
							   SV_MAIN_PRESSURE, SV_MAIN_SOIL_TYPE, SV_MAIN_SOIL_OTHER,
							   SV_REMARKS";
		$this->fh->addFieldGroup($fmt1MainFL, 'fmt1Main', 'Format 1 Main Information');
		$this->fh->setElementsProperties('SV_MAIN_SIZE, SV_MAIN_TYPE', 'required', true);
		$this->fh->setElementsProperties('SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING, SV_MAIN_PRESSURE', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_MAIN_SOIL_TYPE', 'input_type', 'multi-checkbox');
		$this->fh->setElementsProperties('SV_REMARKS', 'input_type', 'textarea');
		
		$fmtSvStatsFL = "SV_WO_NO, SV_SVC_STATUS, SV_UPDATE_STATUS";
		$this->fh->addFieldGroup( $fmtSvStatsFL, 'stats', 'Other Information');
		$this->fh->setElementsProperties('SV_SVC_STATUS, SV_UPDATE_STATUS', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_SVC_STATUS, SV_UPDATE_STATUS', 'required', true);
		
		$maintFieldList = 'SV_CREATE_USER, SV_CREATE_TIME, SV_CHANGE_USER, SV_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		

	}

	private function setDefaultElementsFormat2() {
		// Begin fmt2xxx elements for Card Format 2 - JAF
		$fmt2KeyFL = "SV_ENTRY_FORMAT, SV_SERVICE_ID, SV_STREET, SV_HOUSE, SV_LOT_NO, SPX_PREMISE_NUMS,
							  SV_CITY, SV_STATE, SV_NAME, SV_MAP_NO";
		$this->fh->addFieldGroup($fmt2KeyFL, 'fmt2Keys', 'Format 2 Premise Information');
		$this->fh->setElementsProperties('SV_SERVICE_ID', 'output_only', true);
		$this->fh->setElementsProperties('SV_STREET, SV_HOUSE, SV_CITY, SV_STATE', 'required', true);
		$this->fh->setElementsProperties('SV_ENTRY_FORMAT, SV_CITY, SV_STATE', 'input_type', 'select');
		$this->fh->setElementsProperties('SPX_PREMISE_NUMS', 'input_type', 'textarea');
		
			
		$fmt2SrvFL = "SV_DATE_COMPLETED, SV_ROW, SV_FILE_NO,
							  SV_SIZE, SV_MATERIAL, SV_INSERT, SV_DIRECT, SV_CURB_STOP,
							  SV_JOINT_TYPE, SV_JOINT_TYPE_OTHER, SV_METER_LOC,
							  SV_FLOW_LIMITER, SV_FLOW_LIMITER_SIZE, SV_DEPTH_FT, SV_DEPTH_IN, SV_PUBLIC_BLDG,
							  SV_TEST_PRESSURE, SV_TESTED_WITH_MAIN, SV_TRACER_WIRE,
							  SV_LENGTH_FT, SV_LENGTH_IN, SV_INSTALLED_BY, SV_INSPECTED_BY";
		$this->fh->addFieldGroup($fmt2SrvFL, 'fmt2Srv', 'Format 2 Service Information');
		$this->fh->setElementsProperties('SV_SIZE, SV_DATE_COMPLETED, SV_CURB_STOP, SV_INSTALLED_BY, SV_INSPECTED_BY', 'required', true);
		$this->fh->setElementsProperties('SV_SIZE, SV_MATERIAL, SV_TRACER_WIRE, SV_FLOW_LIMITER, SV_FLOW_LIMITER_SIZE, SV_CURB_STOP, SV_METER_LOC', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_JOINT_TYPE', 'input_type', 'multi-checkbox');
		$this->fh->setElementsProperties('SV_ROW, SV_INSERT, SV_DIRECT', 'input_type', 'y/n');
		$this->fh->setElementsProperties('SV_TESTED_WITH_MAIN', 'input_type', 'y/n');
		
		$fmt2MainFL = "SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING, SV_MAIN_DEPTH_FT, SV_MAIN_DEPTH_IN,
							   SV_MAIN_SOIL_TYPE, SV_MAIN_SOIL_OTHER, SV_MAIN_PRESSURE,
							   SV_REMARKS";
		$this->fh->addFieldGroup($fmt2MainFL, 'fmt2Main', 'Format 2 Main Information');	
		$this->fh->setElementsProperties('SV_MAIN_SIZE, SV_MAIN_TYPE', 'required', true);
		$this->fh->setElementsProperties('SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING, SV_MAIN_PRESSURE', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_MAIN_SOIL_TYPE', 'input_type', 'multi-checkbox');
		$this->fh->setElementsProperties('SV_REMARKS', 'input_type', 'textarea');
		
		$fmtSvStatsFL = "SV_WO_NO, SV_SVC_STATUS, SV_UPDATE_STATUS";
		$this->fh->addFieldGroup( $fmtSvStatsFL, 'stats', 'Other Information');
		$this->fh->setElementsProperties('SV_SVC_STATUS, SV_UPDATE_STATUS', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_SVC_STATUS, SV_UPDATE_STATUS', 'required', true);
		
		$maintFieldList = 'SV_CREATE_USER, SV_CREATE_TIME, SV_CHANGE_USER, SV_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);

	}

	private function setDefaultElementsFormat3() {
		// Begin fmt3xxx elements for Card Format 3 - JAF
		$fmt3KeyFL = "SV_ENTRY_FORMAT, SV_SERVICE_ID, SPX_PREMISE_NUMS, SV_STREET, SV_HOUSE, SV_LOT_NO,
							  SV_CITY, SV_STATE, SV_NAME, SV_MAP_NO";
		$this->fh->addFieldGroup($fmt3KeyFL, 'fmt3Keys', 'Format 3 Premise Information');
		$this->fh->setElementsProperties('SV_SERVICE_ID', 'output_only', true);
		$this->fh->setElementsProperties('SV_STREET, SV_HOUSE, SV_CITY, SV_STATE', 'required', true);
		$this->fh->setElementsProperties('SV_ENTRY_FORMAT, SV_CITY, SV_STATE', 'input_type', 'select');
		$this->fh->setElementsProperties('SPX_PREMISE_NUMS', 'input_type', 'textarea');
		
		
		$fmt3SrvFL = "SV_DATE_COMPLETED, SV_ROW, SV_FILE_NO,
							  SV_SIZE, SV_MATERIAL, SV_INSERT, SV_DIRECT, SV_CURB_STOP,
							  SV_JOINT_TYPE, SV_JOINT_TYPE_OTHER, SV_METER_LOC,
							  SV_FLOW_LIMITER, SV_FLOW_LIMITER_SIZE, SV_DEPTH_FT, SV_DEPTH_IN, SV_PUBLIC_BLDG,
							  SV_TEST_PRESSURE, SV_TESTED_WITH_MAIN, SV_TRACER_WIRE,
							  SV_LENGTH_FT, SV_LENGTH_IN, SV_INSTALLED_BY, SV_INSPECTED_BY";
		$this->fh->addFieldGroup($fmt3SrvFL, 'fmt3Srv', 'Format 3 Service Information');
		$this->fh->setElementsProperties('SV_SIZE, SV_DATE_COMPLETED, SV_CURB_STOP, SV_INSTALLED_BY, SV_INSPECTED_BY', 'required', true);
		$this->fh->setElementsProperties('SV_SIZE, SV_MATERIAL, SV_TRACER_WIRE, SV_FLOW_LIMITER, SV_FLOW_LIMITER_SIZE, SV_CURB_STOP', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_JOINT_TYPE', 'input_type', 'multi-checkbox');
		$this->fh->setElementsProperties('SV_ROW, SV_DIRECT, SV_INSERT', 'input_type', 'y/n');
		$this->fh->setElementsProperties('SV_TESTED_WITH_MAIN', 'input_type', 'y/n');
		
		$fmt3MainFL = "SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING, SV_MAIN_DEPTH_FT, SV_MAIN_DEPTH_IN,
							   SV_MAIN_SOIL_TYPE, SV_MAIN_SOIL_OTHER, SV_MAIN_PRESSURE,
							   SV_REMARKS";
		$this->fh->addFieldGroup($fmt3MainFL, 'fmt3Main', 'Format 3 Main Information');
		$this->fh->setElementsProperties('SV_MAIN_SIZE, SV_MAIN_TYPE', 'required', true);
		$this->fh->setElementsProperties('SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING, SV_MAIN_PRESSURE, SV_METER_LOC', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_MAIN_SOIL_TYPE', 'input_type', 'multi-checkbox');
		$this->fh->setElementsProperties('SV_REMARKS', 'input_type', 'textarea');
		
		$fmtSvStatsFL = "SV_WO_NO, SV_SVC_STATUS, SV_UPDATE_STATUS";
		$this->fh->addFieldGroup( $fmtSvStatsFL, 'stats', 'Other Information');
		$this->fh->setElementsProperties('SV_SVC_STATUS, SV_UPDATE_STATUS', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_SVC_STATUS, SV_UPDATE_STATUS', 'required', true);
		
		$maintFieldList = 'SV_CREATE_USER, SV_CREATE_TIME, SV_CHANGE_USER, SV_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		
	}

	private function setDefaultElementsFormat4() {
		// Begin fmt4xxx elements for Card Format 4 - JAF
		$fmt4KeyFL = "SV_ENTRY_FORMAT, SV_SERVICE_ID, SPX_PREMISE_NUMS, SV_HOUSE, SV_STREET,
							  SV_NAME, SV_CITY, SV_STATE";
		$this->fh->addFieldGroup($fmt4KeyFL, 'fmt4Keys', 'Format 4 Premise Information');
		$this->fh->setElementsProperties('SV_SERVICE_ID', 'output_only', true);
		$this->fh->setElementsProperties('SV_STREET, SV_HOUSE, SV_CITY, SV_STATE', 'required', true);
		$this->fh->setElementsProperties('SV_ENTRY_FORMAT, SV_CITY, SV_STATE', 'input_type', 'select');
		$this->fh->setElementsProperties('SPX_PREMISE_NUMS', 'input_type', 'textarea');
		
		
		$fmt4SrvFL = "SV_SIZE, SV_MATERIAL, SV_COATING,
							  SV_CURB_STOP,
							  SV_METER_LOC, SV_LENGTH_FT, SV_LENGTH_IN, 
							  SV_DEPTH_FT, SV_DEPTH_IN, SV_JOINT_TYPE, SV_JOINT_TYPE_OTHER,
						 	  SV_INSTALLED_BY, SV_INSPECTED_BY, SV_DATE_COMPLETED";
		$this->fh->addFieldGroup($fmt4SrvFL, 'fmt4Srv', 'Format 4 Service Information');
		$this->fh->setElementsProperties('SV_SIZE, SV_DATE_COMPLETED, SV_CURB_STOP, SV_INSTALLED_BY, SV_INSPECTED_BY', 'required', true);
		$this->fh->setElementsProperties('SV_SIZE, SV_MATERIAL, SV_COATING, SV_CURB_STOP, SV_METER_LOC', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_JOINT_TYPE', 'input_type', 'multi-checkbox');
		
		$fmt4MainFL = "SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING,
							   SV_MAIN_DEPTH_FT, SV_MAIN_DEPTH_IN, SV_MAIN_SOIL_TYPE, SV_MAIN_SOIL_OTHER,
							   SV_MAIN_PRESSURE,
							   SV_REMARKS";
		$this->fh->addFieldGroup($fmt4MainFL, 'fmt4Main', 'Format 4 Main Information');
		$this->fh->setElementsProperties('SV_MAIN_SIZE, SV_MAIN_TYPE', 'required', true);
		$this->fh->setElementsProperties('SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING, SV_COATING, SV_MAIN_PRESSURE', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_MAIN_SOIL_TYPE', 'input_type', 'multi-checkbox');
		$this->fh->setElementsProperties('SV_REMARKS', 'input_type', 'textarea');
		
		$fmtSvStatsFL = "SV_WO_NO, SV_SVC_STATUS, SV_UPDATE_STATUS";
		$this->fh->addFieldGroup( $fmtSvStatsFL, 'stats', 'Other Information');
		$this->fh->setElementsProperties('SV_SVC_STATUS, SV_UPDATE_STATUS', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_SVC_STATUS, SV_UPDATE_STATUS', 'required', true);
		
		$maintFieldList = 'SV_CREATE_USER, SV_CREATE_TIME, SV_CHANGE_USER, SV_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		// End fmt4xxx elements for Card Format 4 - JAF
		
	}

	private function setDefaultElementsFormat5() {
		// Begin fmt5xxx elements for Card Format 5 - JAF
		// Removed SV_WO_NO as duplicate in fmtSvStatsFL - JAf
		$fmt5KeyFL = "SV_ENTRY_FORMAT, SV_SERVICE_ID, SPX_PREMISE_NUMS, SV_HOUSE, SV_STREET,
							  SV_NAME, SV_CITY, SV_STATE";
		$this->fh->addFieldGroup($fmt5KeyFL, 'fmt5Keys', 'Format 5 Premise Information');
		$this->fh->setElementsProperties('SV_SERVICE_ID', 'output_only', true);
		$this->fh->setElementsProperties('SV_STREET, SV_HOUSE, SV_CITY, SV_STATE', 'required', true);
		$this->fh->setElementsProperties('SV_ENTRY_FORMAT, SV_CITY, SV_STATE', 'input_type', 'select');
		$this->fh->setElementsProperties('SPX_PREMISE_NUMS', 'input_type', 'textarea');
		
		
		$fmt5SrvFL = "SV_SIZE, SV_MATERIAL, SV_COATING,
							  SV_DEPTH_FT, SV_DEPTH_IN, SV_JOINT_TYPE, SV_JOINT_TYPE_OTHER,
							  SV_LENGTH_FT, SV_LENGTH_IN";
		$this->fh->addFieldGroup($fmt5SrvFL, 'fmt5Srv', 'Format 5 Service Information');
		$this->fh->setElementsProperties('SV_DATE_COMPLETED, SV_SIZE, SV_INSTALLED_BY, SV_INSPECTED_BY', 'required', true);
		$this->fh->setElementsProperties('SV_SIZE, SV_MATERIAL, SV_COATING', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_JOINT_TYPE', 'input_type', 'multi-checkbox');
		
		$fmt5MainFL = "SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING,
							   SV_MAIN_DEPTH_FT, SV_MAIN_DEPTH_IN, SV_MAIN_SOIL_TYPE, SV_MAIN_SOIL_OTHER,
							   SV_MAIN_PRESSURE,
							   SV_DATE_COMPLETED, SV_INSPECTED_BY,
							   SV_REMARKS";
		$this->fh->addFieldGroup($fmt5MainFL, 'fmt5Main', 'Format 5 Main Information');
		$this->fh->setElementsProperties('SV_MAIN_SIZE, SV_MAIN_TYPE, SV_DATE_COMPLETED', 'required', true);
		$this->fh->setElementsProperties('SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING, SV_MAIN_PRESSURE', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_MAIN_SOIL_TYPE', 'input_type', 'multi-checkbox');
		$this->fh->setElementsProperties('SV_REMARKS', 'input_type', 'textarea');
		
		$fmtSvStatsFL = "SV_WO_NO, SV_SVC_STATUS, SV_UPDATE_STATUS";
		$this->fh->addFieldGroup( $fmtSvStatsFL, 'stats', 'Other Information');
		$this->fh->setElementsProperties('SV_SVC_STATUS, SV_UPDATE_STATUS', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_SVC_STATUS, SV_UPDATE_STATUS', 'required', true);
		
		$maintFieldList = 'SV_CREATE_USER, SV_CREATE_TIME, SV_CHANGE_USER, SV_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		// End fmt5xxx elements for Card Format 5 - JAF
		

	}

	private function setDefaultElementsFormatAll() {
	
		$keyFL = "SV_ENTRY_FORMAT, SV_SERVICE_ID, SV_WO_NO, SV_SVC_STATUS, SV_UPDATE_STATUS";
		$this->fh->addFieldGroup( $keyFL, 'keys', 'Key Fields');
		$this->fh->setElementsProperties('SV_SERVICE_ID', 'output_only', true);
		$this->fh->setElementsProperties('SV_ENTRY_FORMAT, SV_SVC_STATUS', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_UPDATE_STATUS', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_SVC_STATUS', 'required', true);
		$this->fh->setElementsProperties('SV_UPDATE_STATUS', 'required', true);
	
		$locFL = 'SPX_PREMISE_NUMS, SV_NAME, SV_HOUSE, SV_STREET, SV_CITY, SV_STATE, SV_LOT_NO, SV_PUBLIC_BLDG, SV_ROW, SV_FILE_NO';
		$this->fh->addFieldGroup( $locFL, 'loc', 'Location');
		$this->fh->setElementsProperties( 'SV_STREET, SV_HOUSE, SV_CITY, SV_STATE', 'required', true);
		$this->fh->setElementsProperties( 'SV_CITY, SV_STATE', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_PUBLIC_BLDG, SV_ROW', 'input_type', 'y/n');
		$this->fh->setElementsProperties('SPX_PREMISE_NUMS', 'input_type', 'textarea');
		
	
		$pipeFL = 'SV_MATERIAL, SV_SIZE, SV_COATING, SV_DEPTH_FT, SV_DEPTH_IN, SV_LENGTH_FT, SV_LENGTH_IN, SV_TRACER_WIRE, SV_CAD_WELD_MAIN';
		$this->fh->addFieldGroup( $pipeFL, 'pipe', 'Service Data/Pipe Information');
		$this->fh->setElementsProperties( 'SV_MATERIAL, SV_SIZE', 'required', true);
		$this->fh->setElementsProperties( 'SV_TRACER_WIRE, SV_MATERIAL, SV_SIZE, SV_COATING', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_CAD_WELD_MAIN', 'input_type', 'y/n');
	
		$connFL = 'SV_CURB_STOP, SV_METER_LOC, SV_REGULATOR_LOC, SV_JOINT_TYPE, SV_JOINT_TYPE_OTHER, SV_FLOW_LIMITER, SV_FLOW_LIMITER_SIZE';
		$this->fh->addFieldGroup( $connFL, 'conn', 'Connection Information');
		$this->fh->setElementsProperties('SV_CURB_STOP, SV_METER_LOC, SV_REGULATOR_LOC, SV_FLOW_LIMITER, SV_FLOW_LIMITER_SIZE', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_JOINT_TYPE', 'input_type', 'multi-checkbox');
		$this->fh->setElementsProperties('SV_CURB_STOP', 'required', true);
	
		$methodFL = 'SV_METH_TRENCH, SV_METH_HDD, SV_METH_HOG, SV_METH_PLOWED, SV_METH_OTHER, SV_DIRECT, SV_INSERT';
		$this->fh->addFieldGroup( $methodFL, 'method', 'Method of Construction');
		$this->fh->setElementsProperties($methodFL, 'input_type', 'y/n');
		$this->fh->setElementsProperties('SV_METH_OTHER', 'input_type', 'text');
	
		$compFL = 'SV_TEST_PRESSURE, SV_TESTED_WITH_MAIN, SV_DURATION_HRS, SV_DURATION_MINS, SV_DATE_COMPLETED, SV_INSTALLED_BY, SV_INSPECTED_BY';
		$this->fh->addFieldGroup( $compFL, 'completion', 'Testing and Completion');
		$this->fh->setElementsProperties( 'SV_DATE_COMPLETED, SV_INSTALLED_BY, SV_INSPECTED_BY', 'required', true);
		$this->fh->setElementsProperties('SV_INSTALLED_BY, SV_INSPECTED_BY', 'required', true);
		$this->fh->setElementsProperties('SV_TESTED_WITH_MAIN', 'input_type', 'y/n');
	
		$this->fh->addFieldGroup( 'SV_REMARKS', 'remarks', 'Remarks/Comments');
		$this->fh->setElementsProperties( 'SV_REMARKS', 'input_type', 'textarea');
	
		$mainFL = 'SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING, SV_MAIN_DEPTH_FT, SV_MAIN_DEPTH_IN, SV_MAIN_PRESSURE, SV_MAIN_SOIL_TYPE, SV_MAIN_SOIL_OTHER';
		$this->fh->addFieldGroup( $mainFL, 'main', 'Main Information');
		$this->fh->setElementsProperties( 'SV_MAIN_SIZE, SV_MAIN_TYPE', 'required', true);
		$this->fh->setElementsProperties( 'SV_MAIN_SIZE, SV_MAIN_TYPE, SV_MAIN_COATING, SV_MAIN_PRESSURE', 'input_type', 'select');
		$this->fh->setElementsProperties('SV_MAIN_SOIL_TYPE', 'input_type', 'multi-checkbox');
	
		$maintFieldList = 'SV_CREATE_USER, SV_CREATE_TIME, SV_CHANGE_USER, SV_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
	}
			

}

?>