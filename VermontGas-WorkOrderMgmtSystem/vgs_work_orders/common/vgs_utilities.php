<?php
/**
 * $GLOBALS['DROPDOWN_COMPARE_OPERATORS']
 * This global array provides a x-ref between comparison operators in drop lists
 * to DB2 where clause operators.
 */
$GLOBALS['DROPDOWN_COMPARE_OPERATORS'] = array(
	'EQ' => '=',
	'GT' => '>',
	'GE' => '>=',
	'LT' => '<',
	'LE' => '<=',
	'NE' => '<>',
	'CT' => 'LIKE'
);

function pre_dump($var) {
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
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
 * 
 * TODO: JV, 8/29/2011 - Remove this if no runtime errors - appears to be unused testing code.
 */
//function subarray(array $arr, $prefix) {
//	$return_array = array();
//	foreach ($arr as $key => $value) {
//		if (strpos($key, $prefix, 0) !== false) {
//			echo "<br>strpos($key, \"{$prefix}CHANGE\", 0) = " . strpos($key, "{$prefix}CHANGE", 0);
//			if (strpos($key, "{$prefix}CHANGE", 0) === false
//			&&  strpos($key, "{$prefix}CREATE", 0) === false ) { 
//				$return_array[$key] = $value;
//			}
//		}
//	}
//	return $return_array;
//}

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
	return ($value == '' || (int) $value === 0);	
}

function isBlankZeroOrNull( $value ) {
	if (is_int($value)) {
		return ($value == 0);
	}
	if (is_float($value)) {
		return ($value == 0.0);
	}
	if (is_null($value)) {
		return (true);
	}
	if (is_string($value)) {
		if ($value == '0001-01-01') return true;
		if ($value == '0') return true;
		if ((int)$value == 0) return true;
		return (trim($value) == '');
	}
	
}

/**
 * Validate an input date in the format yyy-mm-dd
 * @param string $inputDate
 */
function date_is_valid($inputDate) {
	if (substr_count($inputDate, '-') == 2) {
		list($y, $m, $d ) = explode('-', $inputDate);
		return checkdate($m, $d, sprintf('%04u', $y));
	}

	return false;
}
