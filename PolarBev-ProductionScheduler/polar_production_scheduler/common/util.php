<?php

//----------------------------------------------------------------------
function pre_dump($var) {
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
}

//----------------------------------------------------------------------
function getScriptName() {
	// Extract script file name from full path
	$script = $_SERVER['SCRIPT_NAME'];
	$script = str_replace( '\\', '/', $script);
	$script = substr( $script, strrpos($script, '/')+1);
	return $script;
}

/**
 * Dumps an array, showing each entry in the format KEY = VALUE on separate lines. 
 * @param array $arr Array to be dumped.
 * @param string $like Optional search string, to show only elements with key 
 * 						values starting with this string.
 */
function ary_dump(array $arr, $like = NULL) {
	echo '<pre>';
	foreach ($arr as $key => $value) {
//		echo "<br>strpos($key, $like) = " . (bool) strpos($key, $like);
		if ((isset($like) && strpos($key, $like) !== false) 
		|| !isset($like)) {
			echo "<br><b>$key</b> = $value";
		}
	}
	echo '<br></pre>';
}

/**
 * boolean strStartsWith() - returns true if $haystack starts with $needle, else false.
 * @param string $haystack
 * @param string $needle
 * @return true if $haystack starts with $needle, else false.
 */
function strStartsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

/**
 * boolean strEndsWith() - returns true if $haystack ends with $needle, else false.
 * @param string $haystack
 * @param string $needle
 * @return true if $haystack ends with $needle, else false.
 */
function strEndsWith($haystack, $needle)
{
    $length = strlen($needle);
    $start  = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
}

function isBlankOrZero( $value ) {
	$value = trim($value);
	return ($value == '' || (int) $value == 0);	
}
	
/**
 * Validate an input date in the format yyyy-mm-dd
 * @param string $inputDate 
 */
function date_is_valid($inputDate) {
	if (substr_count($inputDate, '-') == 2) {
		list($y, $m, $d ) = explode('-', $inputDate);
		return checkdate($m, $d, sprintf('%04u', $y));
	}

	return false;
}


function convertDateFormat( $dateStr, $fromFmt, $toFmt, $padLen = 8 ) {
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

function formatDate( $dateIn ) {
	return trim($dateIn == 0 ? 
					'' 
					: date('M d, Y', strtotime($dateIn))
			);
}

/**
 * Build a list of &lt;option&gt; tags from a list of DB recs passed as a multi-dim array.
 * @param array $records Multi-dimensional array of records (associative arrays) to be used to build the option tags.
 * @param string $codeFieldName Field name in each record to use as the "value" attr in the option tags. 
 * @param string $displayTextFieldName Field name in each record to use as content between the option tag pairs.
 * @param string $selectedValue [optional] - If passed, add the attr selected="selected" on the option tag whose value matches this string.
 * @param string $emptyValue [optional] - If passed, add an option as the first entry, with no value, whose displayed content is this string. 
 * @return string A set of &lt;option&gt; tags whose values = $records[n][$codeFieldName], and their displayed content = $records[n][$displayTextFieldName]. 
 */
function buildSelectOptionsFromRecord( 
		$records, 
		$codeFieldName, 
		$displayTextFieldName, 
		$selectedValue = NULL, 
		$emptyValue = NULL) 
{
	$optionsList = "\n";
	if ($emptyValue) {
		$optionsList .= "<option value=\"\">$emptyValue</option>";
	}
	
	foreach ($records as $rec) {

		if ($selectedValue && ($rec[$codeFieldName] == $selectedValue)) {
			$selectedAttr = 'selected="selected"';
		} else {
			$selectedAttr = '';
		}
		
		$dispFlds = preg_split('/-/', $displayTextFieldName);
		$sep = $displayString = '';
		foreach ($dispFlds as $dispFld) {
			$displayString .= $sep . $rec[$dispFld];
			$sep = ' - ';
		}
		
		$optionsList .= "<option value=\"{$rec[$codeFieldName]}\" $selectedAttr>" .
							$displayString .
						"</option>\n";
	}
	return $optionsList;
}

