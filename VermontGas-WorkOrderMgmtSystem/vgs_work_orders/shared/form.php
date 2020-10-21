<?php 

function createSelectOptions($optionsArray, $selectedValue) {
	$optionsString = '';
	foreach ($optionsArray as $key => $value) {
		$optionsString .= "\t<option value=\"$key\"";
		if ($key == $selectedValue) {
			$optionsString .=  ' selected="selected"'; 
		};
		$optionsString .= ">$value</option>\n";
	}
	return $optionsString;
}

