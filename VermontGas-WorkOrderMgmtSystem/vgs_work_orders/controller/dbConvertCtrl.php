<?php
/*
require_once '../model/VGS_i5_Conn.php';
require_once '../model/Security.php';
require_once '../view/dbConvertView.php';

$sec = new Security();
$sec->checkAuthoritiesPermission(array('DB_CONV'));

$message = submitConversion();

showScreen($message);

//--------------------------------------------------------------
function submitConversion() {
	$i5ConnObj = new VGS_i5_Conn ();
	$i5Conn = $i5ConnObj->connect_default();
	if (!$i5Conn) {
		return "Unable to call conversion script. 
				iSeries connection failed. <br>Error msg = " . 
				$i5ConnObj->getErrorMessage () ;
	}

	$ret = i5_command("call WOCONVCL");

	if (! $ret ) {
		return "Error calling program WOCONVCL. <br>Error number = " . 
				i5_errno () . " <br>Msg = " . i5_errormsg () ;
				
	}
	
	i5_close ( $i5Conn );    	
	
	return 'Data conversion job was successfully submitted to batch job queue.';
	
}
 */