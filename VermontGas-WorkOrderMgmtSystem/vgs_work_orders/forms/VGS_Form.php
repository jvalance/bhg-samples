<?php
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
require_once '../forms/VGS_FormHelper.php';
require_once '../model/VGS_DB_Conn_Singleton.php';
require_once '../model/VGS_DB_Conn.php';
require_once '../common/common_errors.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Code_Values_Master.php';

abstract class VGS_Form extends Zend_Form {
	/**
	 * Form Helper object - helps construct the form using DB2 metadata.
	 * @var VGS_FormHelper
	 */
	public $fh;
	/**
	 * Specifies the page to redirect to after successfully processing form inputs
	 * @var string
	 */
	public $return_point;
	/**
	 * Mode of DB record access - ie, create, edit, inquiry, delete.
	 * @var string
	 */
	public $mode;
	/**
	 * Array containing all input values passed in from the HTML form 
	 * (combines $_GET and $_POST).
	 * @var associative array
	 */
	public $inputs;
	/**
	 * This string will be appended to the mode string (and title cased) to form 
	 * the screen's title, displayed in the standard page header.
	 * @var string
	 */
	public $screen_title;
	
	/**
	 * Boolean flag indicating whether form inputs have all passed validations. 
	 * @var boolean
	 */
	public $valid;
	
	protected $conn;
	
	//---------------------------------------------------------------------
	public function __construct( $conn ) {
		parent::__construct ( ); // call Zend_Form constructor
		 
		// Set custom properties
		$this->conn = $conn;
		// Default to input is valid
		$this->valid = true;
		// Get mode from request
		$this->mode = $_REQUEST['mode'];
		$this->fh = new VGS_FormHelper();
		$this->fh->mode = $this->mode;  
		// Set URL to return to after successful update or when cancel clicked
		if (!(bool)$_REQUEST['popup']) {
	 		if (isset($_REQUEST['return_point'])) {
				$this->return_point = $_REQUEST['return_point'];
			} else {
				$this->return_point = $_SESSION['previousPage'];
			}
		}
		// Store get and post inputs in form object
		$this->inputs = array_merge($_GET, $_POST);
	}

	abstract public function createRecord() ;
	abstract public function updateRecord() ;
	abstract public function retrieveRecord();
	/**
	 * Not all forms will have a delete capability, so don't make it abstract;
	 * Default deleteRecord() function does nothing;
	 * Must be overridden in concrete child class in order for it to do anything.
	 */
	public function deleteRecord() {
	} 

	public function activate() {
		if ($_SERVER['REQUEST_METHOD'] == 'GET') { 
			$this->loadScreen();
		} else {
			$this->processScreen();
		}
	}

	public function loadScreen() {
		if ($this->isCreateMode()) {
			$this->reset();
		} else {
			$rec = $this->retrieveRecord();
			array_map('trim', $rec);
			$this->setOutputFormatsForScreen($rec);
			$this->populate($rec);
		}
	}
	
	public function processScreen() {
		if ($this->isDeleteMode()) {
			$this->deleteRecord();
			$this->returnToCaller();
		} else {
			$this->preProcessFormInputs();
			if ($this->validate()) {
				$this->setInputFormatsForDB2(); 
				if ($this->isCreateMode()) {
					$this->createRecord();
				} elseif ($this->isUpdateMode()) {
					$this->updateRecord();
				} 
				$this->returnToCaller();
			} else {
				$this->populate($this->inputs);
			} 
		}
	}
	
	/**
	 * Changes blank values in form inputs into the proper empty value for non-character
	 * database field types - eg: numeric field with blank gets changed to 0, date field
	 * with blank gets changed to '0001-01-01'; This prevents errors of the form: 
	 * "NULL value not allowed for column xxxx"  
	 */
	protected function setInputFormatsForDB2() 
	{
		$meta = $this->fh->metaData;
		foreach ($meta as $fieldName=>$fieldMeta) {
			if (isset($fieldMeta['include']) && (bool) $fieldMeta['include'] == true) {
				$dataType = strtoupper(trim($fieldMeta['DATA_TYPE'])); 
				$elem = $this->getElement($fieldName); 
				switch ($dataType) {
					case 'DATE':
						$dateISO = $this->fixDateInput($elem->getValue());
						$this->inputs[$fieldName] = $dateISO;
						$elem->setValue($dateISO);
						break;
					case 'TIME':
					case 'TIMESTAMP':
						$timeISO = $this->fixTimeInput($elem->getValue());
						$this->inputs[$fieldName] = $timeISO ;
						$elem->setValue($timeISO);
						break;
					case 'INTEGER':
					case 'DECIMAL':
					case 'INT':
					case 'FLOAT':
					case 'DOUBLE':
						if (trim($elem->getValue()) == '') {
							$elem->setValue(0);
							$this->inputs[$fieldName] = 0;
						}
						break;
					case 'CHAR':
					case 'CHARACTER':
					case 'VARCHAR':
						if ($fieldMeta['input_type'] == 'multi-checkbox'
						||  $fieldMeta['input_type'] == 'multi-select') {
							// Multi-checkbox and multi-select fields have values passed as an array on the request.
							// Serialize the array elements using implode with separator = ','
							$serialValue = implode($elem->getValue(), ',');
							// Prevent "null value not allowed for field" exception, if no value selected.
							if ($serialValue == NULL) $serialValue = '';
							// Store the serialized value in the input field
							$this->inputs[$fieldName] = $serialValue;
						} elseif ($elem->getValue() == NULL || trim($elem->getValue()) == '') {
							// For other character fields, set nulls to blanks.
							$elem->setValue(' ');
							$this->inputs[$fieldName] = ' ';
						}
						break;
				}
				
			} else {
				// remove any lingering fields
				if (isset($this->inputs[$fieldName])) {
					unset($this->inputs[$fieldName]);
				}
			}
		}
	}
	
	/**
	 * This will perform any pre-processing on form inputs, such as converting to 
	 * upper case if requested, prior to form validation.
	 */
	protected function preProcessFormInputs() 
	{
		$meta = $this->fh->metaData;
		foreach ($meta as $fieldName=>$fieldMeta) {
			if (isset($fieldMeta['include']) && $fieldMeta['include'] == true) {
				$dataType = strtoupper(trim($fieldMeta['DATA_TYPE'])); 
				$elem = $this->getElement($fieldName); 
				if (isset($fieldMeta['upper-case']) && $fieldMeta['upper-case'] == true) {
					// Ensure input is converted to upper case if specified in form
					$upperValue = strtoupper($this->inputs[$fieldName]);
					$elem->setValue( $upperValue );
					$this->inputs[$fieldName] = $upperValue;
				}
			}
		}
	}
	
	/**
	 * Special formatting for displaying fields on detail screen
	 * eg: Converts date values from yyyy-mm-dd to mm-dd-yyyy.
	 * 
	 */
	protected function setOutputFormatsForScreen( &$data ) 
	{
		$meta = $this->fh->metaData;
		foreach ($meta as $fieldName=>$fieldMeta) {
			if (isset($data[$fieldName]) &&
				isset($fieldMeta['include']) && 
				$fieldMeta['include'] == true &&
				!in_array($fieldMeta['input_type'], array('select'))
			){
				$dataType = strtoupper(trim($fieldMeta['DATA_TYPE'])); 
				switch ($dataType) {
					case 'DATE':
						$outputOnly = (isset($fieldMeta['output_only']) && $fieldMeta['output_only'] == true);
						$viewDate = $this->fixDateOutput($data[$fieldName], $outputOnly);
						$data[$fieldName] = $viewDate;
						break;
					case 'TIME':
						$viewTime = $this->fixTimeOutput($data[$fieldName]);
						$data[$fieldName] = $viewTime;
						break;
/**
 * TODO: 2/27/2012 - JGV: Discovered that the following would work, using 
 * TIMESTMP instead of TIMESTAMP.
 * Since it wasn't working with TIMESTAMP, the use of getTimeStampOutputFormat()
 * was hard-coded into each of the model retrieve() methods to format the 
 * audit fields (CREATE_TIME and CHANGE_TIME). 
 */				
//					case 'TIMESTAMP':
//					case 'TIMESTMP':
//						pre_dump("data[$fieldName] = {$data[$fieldName]}");
//						$viewTime = $this->getTimeStampOutputFormat($data[$fieldName]);
//						pre_dump("data[$fieldName] after = {$viewTime}");
//						$data[$fieldName] = $viewTime;
//						break;
					case 'INTEGER':
					case 'INT':
						if ((int)$data[$fieldName] == 0) {
							$data[$fieldName] = '';
						}
						break;
					case 'DECIMAL':
					case 'FLOAT':
					case 'DOUBLE':
						case 'DECIMAL':
						if ((float)$data[$fieldName] == 0) {
							$data[$fieldName] = '';
						}
						break;
					case 'CHAR':
					case 'CHARACTER':
					case 'VARCHAR':
						if ($fieldMeta['input_type'] == 'multi-checkbox'
						||  $fieldMeta['input_type'] == 'multi-select') {
							$data[$fieldName] = explode(',', $data[$fieldName]);
						}
						break;
				}
				
			}
		}
	}
	
	public function isCreateMode() {
		return ($this->mode == 'create');
	}

	public function isUpdateMode() {
		return ($this->mode == 'update');
	}
	
	public function isInquiryMode() {
		return ($this->mode == 'inquiry');
	}
	
	public function isDeleteMode() {
		return ($this->mode == 'delete');
	}
	
	public function returnToCaller() {
		if ( (bool) $_REQUEST['popup']) {
			header("Location: popupCloseCtrl.php");
			exit;			
		}
		// Ensure that, when returning to a fitered list, the filter values are preserved
		// This is accomplished by adding "filtSts=restore" to the query string if it's not there already.
		if (strpos($this->return_point, 'filtSts=restore') === false
		&& strpos($this->return_point, 'ListCtrl') !== false) {
			if (strpos($this->return_point, 'php?') === false) {
				$this->return_point .= '?';
			} else {
				$this->return_point .= '&';
			}
			$this->return_point .= 'filtSts=restore';
		}
		
		// Redirect to the return point
		header("Location: " . $this->return_point);
		exit;
	}

	public function setDateOutputFormat($field) {
		$value = trim($this->getElement($field)->getValue());
		if ($value != '0001-01-01' && trim($value) != '') {
			$this->getElement($field)->setValue(date('m-d-Y', strtotime($value)));
			$this->getElement($field)->setAttrib('SIZE', 15);
		} else {
			$this->getElement($field)->setValue('');
		}
	}
	
	public static function getTimeStampOutputFormat($timestamp) {
		$format = 'Y-m-d-H\.i\.s\.u';	
		if (strStartsWith($timestamp, '0001-01-01')) {
			return '';
		}
		$dateTime = DateTime::createFromFormat($format , $timestamp);
		if ($dateTime) {
			return $dateTime->format('D, M d Y \a\t h:i:s a');
		} else { 	
			return '';
		}
	}
	
	/** 
	 * This will change an empty date value from a web form, into a valid database null date value 
	 * for updating a DB2 table - '0001-01-01' 
	 * @param string $inputValue The date string value to be checked for null (i.e. '0001-01-01')
	 */
	public static function fixDateInput ( $inputValue ) {
		if (trim($inputValue) == '') {
			return '0001-01-01';
		}
		else {
			if (strpos($inputValue,'-') !== false) {
				list($mm, $dd, $yyyy) = explode('-', $inputValue);
				$ymd = "$yyyy-$mm-$dd";
				//pre_dump("inputValue = $inputValue; fmtd: $ymd");
				return $ymd;
			}
			if (strpos($inputValue,',') !== false) {
				$wkDate = strtotime($inputValue);
				$ymd = date('Y-m-d', $wkDate);
				//pre_dump("inputValue = $inputValue; fmtd: $ymd");
				return $ymd;
			}
		}
		// Default return value = empty date
		return '0001-01-01';
	}
	
	/** 
	 * This will change the format of a date value from the DB from yyyy-mm-dd to mm-dd-yyyy  
	 * @param string $dateString The date string value to be formatted
	 */
	public static function fixDateOutput ( $dateString, $blnOutputOnly = false ) {
		if (trim($dateString) == '0001-01-01') {
			return '';
		} elseif (trim($dateString) != '') {
			if ($blnOutputOnly) {
				// Output format
				$dateString = date('M d, Y', strtotime($dateString));
			} else {
				// Input format
				$dateString = date('m-d-Y', strtotime($dateString));
			}
			return $dateString;
		}
	}
	
	/** 
	 * This will change the a zero time value (00:00:00) to blanks for display on the screen.
	 * This makes it easier to check for required time values without requiring a custom validator.  
	 * @param string $timeString The time string value to be formatted
	 */
	public static function fixTimeOutput ( $timeString ) {
		if (trim($timeString) == '00.00.00') {
			return '';
		}
		else {
			return $timeString;
		}
	}
	
	/** 
	 * This will change an empty time value into a valid database format for unknown (i.e. zero) time. 
	 * @param string $inputValue The time string to be checked for empty or 0 
	 */
	public static function fixTimeInput ( $inputValue ) {
		$strippedValue = str_replace(':', '', $inputValue);
		$strippedValue = str_replace('.', '', $strippedValue);
		
		if (trim($inputValue) == '' || (int) $strippedValue === 0) {
			return '00.00.00';
		} else {
			return $inputValue;
		}
	}

	public static function convertDateFormat( $dateStr, $fromFmt, $toFmt, $padLen = 8 ) {
		$dateStr = trim($dateStr);
		if ($dateStr == '0' || $dateStr == '') return '';
		
		$datePad = str_pad ( $dateStr, $padLen, '0', STR_PAD_LEFT);
		//pre_dump("$datePad in format $fromFmt");
		//$newDate = strtotime($datePad);
		try {
			$dateTime = DateTime::createFromFormat($fromFmt, $datePad);
			if (!is_object($dateTime) || !is_a($dateTime, 'DateTime')) {
				$dateTime = new DateTime($datePad);
			}				
			$dateFmtd = $dateTime->format($toFmt);
		} catch (Exception $e) {
			echo "Error occurred: {$e->getMessage()} <br>in {$e->getFile()} on line {$e->getLine()}<p>";
			echo parse_backtrace(debug_backtrace());
			die;
		}
		return $dateFmtd;
	}
	
	public function renderFieldGroup( $fieldGroupName ) { 
		//echo "<P>Field group = $fieldGroupName</P>";
		if (!isset($this->fh->fieldGroups[$fieldGroupName])) {
			return;
		}
		$fg = $this->fh->fieldGroups[$fieldGroupName];
		$fgDisplayClass = 'fg_expanded';
		$fgDispIcon = 'collapse.gif';
		if (isset($fg['hidden']) && $fg['hidden'] == true) {
			$fgDisplayClass = 'fg_collapsed';
			$fgDispIcon = 'expand.gif';
		} 
		?>		
		<table id="fg_<?= $fieldGroupName ?>" 
				class="field_group <?= $fgDisplayClass ?>" 
				style="width:100%; margin-top: 8px">

		<caption>
			<img src="../shared/images/<?= $fgDispIcon ?>" 
				class="<?= $fgDisplayClass ?>" 
				id="toggle_<?= $fieldGroupName ?>"
				onclick="toggleFieldGroup('<?= $fieldGroupName ?>');"> 
			<a onclick="toggleFieldGroup('<?= $fieldGroupName ?>');"
				class="toggleFieldGroup">
				<?= $fg['caption'] ?>
			</a>
		</caption>
		
		<?php
		$fieldList = $fg['fieldlist'];
		if ($fieldList == '') return;
		$fields = $this->fh->splitNames($fieldList);
		foreach ($fields as $fieldName) {
			$elem = $this->getElement($fieldName);
			if ($elem == NULL) die("NULL field: $fieldName");
			$messages = '';
			if (count($elem->getMessages()) > 0) {
				$messages = '<ul class="error"><li>';
				$messages .= implode('</li><li>', $elem->getMessages()); 
				$messages .= '</li></ul>';
			}
			$lookup = $this->buildLookup($fieldName);
			
			echo <<<ELEM_RENDER
				<tr>
					<td id="label_{$fieldName}" class="field_label {$this->fh->metaData[$fieldName]['label-class']}"> 
						{$elem->getLabel()}
					</td>
					<td class="field_value" id="input_{$fieldName}"> 
						{$elem->render()} 
						<span class="field_lookup" id="lookup_{$fieldName}">
							$lookup
						</span>
						<span class="field_description" id="desc_{$fieldName}">
							{$elem->getDescription()}
						</span>
						$messages
					</td>
				</tr>
ELEM_RENDER;
		}
		echo '</table>';
	}
	
	private function buildLookup( $fieldName ) {
		
		// Don't add lookup link if inquiry mode or field is output only
		$outputOnly = (isset($this->fh->metaData[$fieldName]['output_only']) 
					&& $this->fh->metaData[$fieldName]['output_only'] == true);

		if ($this->isInquiryMode() || $outputOnly) {
			return '';
		}

		$lookup_tag = '';
		$lu_Action = '';
		if (isset($this->fh->metaData[$fieldName]['lookup']) ) {
			$lu_Action = $this->fh->metaData[$fieldName]['lookup'];
		}
		
		if (isset($lu_Action) && trim($lu_Action) != '') {
			$lookup_tag = <<<LU_TAG
				&nbsp;
				<a onclick="$lu_Action" class="lookup_icon">
					<img src="../shared/images/search.gif" alt="Search" title="Search" 
							style="border:1px solid white; vertical-align:middle;" 
							onmouseover="this.style.border='1px solid silver';" onmouseout="this.style.border='1px solid white'"> 
					<!-- <span style="font-size:.8em">Search</span> -->
				</a>
				&nbsp;&nbsp;
LU_TAG;
		}
		return $lookup_tag;
	}
	
	public function renderFormTop() {
		$this->renderFormHeaderMessage();
		$this->renderFormHiddens();
		$this->renderFormJS();
	}
		
	public function renderFormHeaderMessage() {
		if (! $this->isInquiryMode()) :
			if ($this->valid) : ?>
				<div id="form_msgs">
				<h3>
					Enter changes and press ENTER to save. 
					(<span style="color:red">*</span> = Required entry) 
				</h3></div>
			<?php else : ?>
				<div id="form_err_msgs">
					<h3>CHANGES NOT SAVED!</h3>
					<h4>Correct errors and try again, or click Cancel to return:</h4>
				</div>
				<?php 
				$formMsgs = $this->getErrorMessages();
				if (count($formMsgs) > 0) {
					echo '<ul class="error">';
					foreach ($formMsgs as $errMsg) {
						echo "\t<li class='error'>$errMsg</li>\n";
					}
					echo '</ul>';
				}
			endif; 
		endif; 
		
	}
	
	public function renderFormHiddens() { 
		?>
		<input type="hidden" name="mode" value="<?= $this->mode ?>" />
		<input type="hidden" name="a" value="" />
		<input type="hidden" name="return_point" value="<?= $this->return_point ?>" />
		<?php 
	}
	
	public function renderFormJS() {
		?>
		<script type="text/javascript">
		<!--
		// Initialize data changed flag to false
		var boolConfirmNav = false;
		
		function doSubmit(action) {
			if (action == 'update' || action == 'delete') {
				// Disable buttons after submit clicked
				$('button').attr("disabled", true);
				$('input:button').attr("disabled", true);
				$('input:submit').attr("disabled", true);
				
				if (action == 'delete') {
					if (!confirm('You are about to delete this record. \n\nAre you sure you want to continue?\n')) {
						return false;
					}
				}  
				boolConfirmNav = false; // Set off warning message about unsaved changes
				document.form1.a.value = action;
				document.form1.submit();
			} else {
				// Default = return to caller
				document.location.href = document.form1.return_point.value;
				return false;
			}
		}

		function setNavConfirm() {
			// This function will be bound to the onchange event handler 
			// for every input field on this page. 
			boolConfirmNav = true;
		}

		function checkNavOK() {
			// This function is bound to the window's onbeforeunload event handler.
			// If form inputs have been changed by the user, it will warn them to save changes first 
		//alert('In checkNavOK = ' + boolConfirmNav);
			if (boolConfirmNav == true) {
				return'Data on this page has been changed. ' +
					'If you leave this page without saving your changes will be lost.' +
					'\n\nClick OK to leave WITHOUT saving changes.' +
					'\nClick Cancel to return to editing and save your changes.';
			}
		}

		// Handle toggling visibility of field groups
		function toggleFieldGroup( groupName ) {
			// set up selector for field group table cells (caption is always visible) 
			var fgSelector = '#fg_' + groupName + ' td';

			// set up selector for expand/collapse icon
			var fgToggleIcon = '#toggle_' + groupName;

			$(fgSelector).toggle();
			var currentIcon =  $(fgToggleIcon).attr('src');
			if (currentIcon.endsWith('expand.gif')) {
				$(fgToggleIcon).attr('src', '../shared/images/collapse.gif');
			} else {
				$(fgToggleIcon).attr('src', '../shared/images/expand.gif');
			}
		}
		
		// jQuery functions
		$('document').ready(function() {
			// Collapse any field groups that default to hidden
			$('table.fg_collapsed td').hide();
			$('img.fg_collapsed').attr('src', '../shared/images/expand.gif');

			// The following will ensure that tabbing will not put focus on lookup icons.
			// Setting tabindex = -1 will prevent them from getting focus.
			$('.lookup_icon').attr("tabindex", -1);
			
			//
			//$('input.disabled, select.disabled, textarea.disabled, input[checkbox].disabled').css(
			//		'color: navy; font-weight: bold; font-size: 1.05em; background-color: white; border: 0;');
			
			// Handle key presses 
			<?php 
			$enterAction = $this->isInquiryMode() ? 'cancel' : 'update';
			?>
			$(document).keypress(function(event){
				var keycode = (event.keyCode ? event.keyCode : event.which);
				if(keycode == '13'){
					// Enter key pressed
					var tagName = $(':focus').attr('tagName');
					// Don't alter enter-key behavior on textareas (otherwise user can't add line breaks)
					if (!tagName || (tagName.toUpperCase() != 'TEXTAREA')) {
						// If not textarea, enter key will submit form
						doSubmit('<?= $enterAction ?>');
					}
				}
//				if(keycode == '27'){
//					// Escape key pressed
//					doSubmit('cancel');
//					return false;
//				}
			});
			<?php 
			if (!$this->isInquiryMode()) :
				?>
				// Set the onchange event handler for every input on the screen
				$("input").change(setNavConfirm);
				
				// Set the function to call before leaving the page. Function checkNavOK() will
				// check for any user input changes before leaving the page. 
				window.onbeforeunload = checkNavOK;
			<?php 				
			endif;
			?>
			
			// Set up defaults for date-pickers
			$.datepicker.setDefaults({
				dateFormat: 'mm-dd-yy', 
				buttonImage: '../shared/images/datepicker.gif',
				changeMonth: true,
				changeYear: true,
				showOn: 'button',
				buttonImageOnly: true, 
				buttonText: 'Calendar' });
			
		<?php
		// The following foreach handles conditional field handling javascripts. 
		// i.e., adding javascript handling for fields based on data type or other field attributes  
		foreach ($this->fh->metaData as $fieldName=>$fieldMeta) :
//			pre_dump($fieldName . '  ' . $this->fh->metaData[$fieldName]);
			if (isset($this->fh->metaData[$fieldName]['upper-case'])) {
				echo <<<UPPER_FIELDS
				// Force $fieldName to uppercase on blur
				$('#$fieldName').change(function(){
					var fldVal = $(this).val();
					fldVal = fldVal.toUpperCase();
					$(this).val(fldVal);
				});
UPPER_FIELDS;
			}
	
			$dataType = $this->fh->metaData[$fieldName]['DATA_TYPE'];
//			echo "\n//DATA_TYPE = '" . $dataType . "'";
			if (trim($dataType) == 'DATE') {
				//echo "\n//DATA_TYPE = " . $dataType; 
				if (!$this->isInquiryMode() 
				&&	!$this->fh->metaData[$fieldName]['output_only'] ) 
				{
					echo "\n" . '$(\'#' . $fieldName . '\').datepicker();';
				}
			}
		endforeach;
		?>
			// Position cursor at first text input field
			$('input:not([class="disabled"]):[type="text"]:first').focus();
			
		}) // end of $('document').ready(function() {
		
		//-->
		</script>
		<?php 		
	}
	
	public function renderFormButtons() {
		if ($this->isCreateMode() || $this->isUpdateMode()) :
		// Show save button only if create or update mode
		?>
		<input type="button" onclick="doSubmit('update');" value="Save Changes" />
		<?php
		endif; 

		if ($this->isDeleteMode()) :
		// Show delete button only if this is delete mode
		?>
		<input type="button" onclick="doSubmit('delete');" value="Delete Record" style="color: red; font-weight:bold" />
		<?php
		endif; 
		?> 

		<input type="button" onclick="doSubmit('cancel');" value="Cancel" /> 
		<?php 
	}
	
	public function validate () {
		$this->valid = parent::isValid($this->inputs);
		
		if ($this->valid && $this->isCreateMode()) {
			$rec = $this->retrieveRecord($this->inputs);
			if (is_array($rec)) {
				$this->addError('Database already contains a record with this key.');
				$this->valid = false;
			}
		}
		
		return $this->valid ;
	}

	public function isBlankOrZero( $value ) {
		$value = trim($value);
		$err = ($value == '' || (int) $value == 0);
		return $err;
	}

	public function addInlinePopupButton ($label, $url, $icon = null, $location, $options = null) {
		if ($icon == null) $icon = VGS_NavButton::POPUP_ICON;
		$winName = str_replace(' ', '_', $label);
		if ($options == null) {
			$target = "javascript:openPopUp('$url', '$winName');";
		} else {
			$target = "javascript:openPopUp('$url', '$winName', '$options');";
		}
		$btn = new VGS_NavButton($label, $target, 'js');
		$btn->setIcon($icon);
		$this->getElement($location)->setDescription($btn->render('return'));
	}
	
}

?>