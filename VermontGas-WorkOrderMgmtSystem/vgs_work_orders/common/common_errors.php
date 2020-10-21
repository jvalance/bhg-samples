<?php 
//set_error_handler("errorLogger"); //, E_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
//set_exception_handler("errorLogger");
//---------------------------------------------------------------------------
function errorLogger( $errno, $errstr,  $errfile, $errline, $errcontext )
{
    $context = var_export($errcontext, TRUE);
//	array_walk(debug_backtrace(),create_function('$a,$b','print "{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']}); ";'));	
    print("errno:$errno ($errstr)<br>  file:$errfile, line:$errline<P>");
//    log_error("errno:$errno ($errstr)\n  file:$errfile, line:$errline, \n  context:$context\n");
//	pre_dump(debug_backtrace());
	$backtrace = parse_backtrace(debug_backtrace());
	echo "<pre style='text-align:left'>$backtrace</pre>";
   exit;
//    showErrorPage($errno, $errstr);
}

//--------------------------------------------------------------
function trap_sql_error($file, $function, $sql_func, 
   $sql_str, $sql_state, $sql_msg, $user_msg = '') 
{
   echo "<tt><font face=\"courier\" color=\"red\">
         SQL error occurred in PHP file: <b>$file</b><br> 
         PHP Function: <b>$function</b><br>
         SQL function: <b>$sql_func</b><br>
         SQL state: <b>$sql_state</b><br> 
         SQL message: <b>$sql_msg</b><br>
         SQL string: <br><b><pre>$sql_str</pre></b><br>
        </font>";
   if ($user_msg != '') {
      echo "<p>User message is: <b>An error occurred while trying to $user_msg</b></p>";
   }
   echo parse_backtrace(debug_backtrace());
//   echo '<p>' . getDebugBacktrace();
   echo '</tt>'; 
   exit;
}


//--------------------------------------------------------------
function parse_backtrace($backtrace, $NL = '<br>'){ 
	$output=  "-------------------------$NL" .
             "Function back trace:$NL" .
             "-------------------------$NL"; 
    foreach($backtrace as $entry){ 
	    $output .= "<b>File: ".$entry['file']."</b> (Line: ".$entry['line']."){$NL}"; 
	    $output .= "Function: ".$entry['function']."(){$NL}"; 
	    $output .= "Arguments:\n "
	            . list_args_recursive($entry['args']) . "\n{$NL}-------{$NL}"; 
    } 

    return $output . '</div>'; 
}

//---------------------------------------------------------------------------
function list_args_recursive($pieces)
// Recursive implode function - from PHP.net user notes on "implode()" page
{
	$string = "<ol class=\"bktrace\">\n";
	foreach( $pieces as $key => $piece ) {
	   if(is_array($piece)) {
			reset($piece);
			$string .= "<li>Array: '$key'\n";
			$string .= "<ol class=\"bktrace\">\n";
			$string .= list_args_recursive($piece);
			$string .= "</ol></li>\n";
	   } elseif(is_object($piece)) {
			$objArray = get_object_vars($piece);
			$string .= "<li>Object: '$key'\n";
			$string .= "<ol class=\"bktrace\">\n";
			$string .= list_args_recursive($objArray);
			$string .= "</ol></li>\n";
	   } else {
	      $string .= "<li>\n'$key' => $piece &nbsp;\n</li>\n";
	   }
   }   
   $string .= "</ol>\n";
   return trim($string);
}


//---------------------------------------------------------------------------
function showErrorPage($errno, $errstr) {
	// Display standard error page
	require_once('top.php');
	?>
	<center>
	<br>
	<h2 style="width:80%;">Error Occurred</h2>
	<div class="data_label ca" style="width:80%;">
	An error has occurred while trying to open this page. 
	This problem has been logged. Feel free to report this 
	to our customer service department by copying the message below 
	into our <a href="feedback.php">online feedback form.</a>
	<p><b>The error mesage follows:</b></p>
	<?php 
	echo "Error#: $errno - $errstr"; 
	?>   
	</div>
	<form>
	<input type="button" value="Return to Previous Page" 
			onclick="document.location='<?php echo $_SERVER["HTTP_REFERER"]; ?>'" />
	<?php
	if (isset($_SESSION['Account_Num'])) { ?>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="button" value="Logout - Restart" onclick="document.location='logout.php'" />
	<?php
	}
	?>   

	</form>	
	</center>
	<?php 
	require_once('bottom.php');
	exit();
}

//---------------------------------------------------------------------------
function log_error($message)
{

	if (file_exists(ERRLOG_FILE)) {
		$fd = fopen(ERRLOG_FILE, 'a');
	} else {
		$fd = fopen(ERRLOG_FILE, 'x');
	}
    if($fd) {
		if(!fwrite($fd, date('Y-m-d H:i:s')." ERR : \n$message\n\n"))
        fclose($fd);
    }  
}

//--------------------------------------------------------------
function getDebugBacktrace($NL = "<BR>") {
    $dbgTrace = debug_backtrace();
    $dbgMsg .= $NL."Debug backtrace begin:$NL";
    foreach($dbgTrace as $dbgIndex => $dbgInfo) {
        $dbgMsg .= "<b>Stack index $dbgIndex</b>  ".$dbgInfo['file'].
            " (line {$dbgInfo['line']}) -> <font color=red>{$dbgInfo['function']}(</font>".
            join("<font color=red>,</font>",$dbgInfo['args'])."<font color=red>)</font>$NL-----$NL";
    }
    $dbgMsg .= "Debug backtrace end".$NL;
    return $dbgMsg;
}

/* -------------------------------------------------------
 * Common input validations routines
 * ------------------------------------------------------- */
function checkLength($value, $maxLen, $label, &$msgArr) 
{
   if (strlen($value) > $maxLen) {
      $msgArr[] = "Length of $label cannot be greater than $maxLen: ($value)";
      return false;
   }
   return true;
}

/* -------------------------------------------------------
 * Common input validations routines
 * ------------------------------------------------------- */
function checkRequired($value, $label, &$msgArr) 
{
   if (trim($value) == '') {
      $msgArr[] = "$label is a required entry. Please enter a valid value for $label.";
      return false;
   }
   return true;
}

//---------------------------------------------------------------------------
function checkNumber($value, $maxWhole, $maxDec, $label, &$msgArr) 
{
	$value = trim($value);
	// Don't validate empty string - use checkRequired() for this.
	if ($value == '') return true;
	
	$errorText = 
	  "$label must be a numeric value 
	     with max $maxWhole whole digits 
	     and max $maxDec decimal places: ($value)";
	
	if (!is_numeric($value)) {
      $msgArr[] = $errorText;
      return false;
	}
	
	if ((int)$maxDec == 0) {
		// must be integer
		if (!is_integer((int)$value) || strlen($value) > $maxWhole) {
         $msgArr[] = $errorText;
         return false;
		}
	
	} else {
		// decimal number
		$decIdx = strpos($value,'.');
		if ($decIdx === false) {
			// No decimal point
			$wholeDigits = $value;
			$decDigits = '';
		} else {
			// Decimal point found
         $wholeDigits = substr($value, 0, $decIdx);
         $decDigits = substr($value, $decIdx+1);
		}
		
      if (strlen($wholeDigits) > $maxWhole 
      ||  strlen($decDigits) > $maxDec) {
         $msgArr[] = $errorText;
         return false;
      }
	}

   return true;
}

function checkEmail($Email, $label, &$msgArr) {  // written by cwilliams, pulled from php.net. Slightly modified with better regex.
   if (!ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'.
            '@'.
            '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
            '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$',
      $Email)) // Do the basic regex matching for simple validation
   {
      $msgArr[] = "The value entered for $label is not a valid email format. Please check your entry and enter a valid email address.";
   	return FALSE;
   }
   return TRUE;
}

function checkValidCardConstruction($ccCardNumber, $ccConstruction, $ccType=' ', $label, &$msgArr) 
{
	
	// good construction?
	if ($ccConstruction['goodconstruction'] == 'N') {
		$ccResult['status'] = 'Error';
		$msgArr[] = 'Credit Card ' . maskCreditCardNumber(trim($ccCardNumber),0,-4) . ' is not a valid card number';
		return FALSE;
	}
	// compare card type to card number
	if ($ccConstruction['goodconstruction'] == 'Y' && trim($ccType) != trim($ccConstruction['issabb'])) {
		$msgArr[] = "Card number doesn't match the type selected" ;
		return FALSE;
	}
		
	return TRUE;
}

