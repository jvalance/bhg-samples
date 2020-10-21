<?php
exit; // disabled if not commented
define('PROD_FLR', '/www/zendsvr/htdocs/wo/');
define('TEST_FLR', '/www/zendsvr/htdocs/wotest/');
define('ARCH_FLR', '/usr/local/archives/WOMS/promotions/' . date('Y-m-d') . '/');

error_reporting(E_ALL);
ini_set('display_errors', true);

session_start();
echo "'{$_SESSION['current_user']}'<br>";
if (trim($_SESSION['current_user']) != 'JVALANCE') {
	echo 'You are not authorized to this function.';
	exit;
} 

promoteSource('controller/loginCtrl.php');
promoteSource('forms/VGS_FormHelper.php');
promoteSource('model/Premise.php');
promoteSource('model/Sec_Profiles.php');
promoteSource('model/Security.php');
promoteSource('model/VGS_DB_Conn_Singleton.php');	
promoteSource('model/VGS_i5_Conn.php');


function promoteSource ( $fname ) {
	archiveSource($fname);
	//copySource($fname);
}

function archiveSource( $fname ) {
	$from = PROD_FLR . $fname;
	$to = ARCH_FLR . $fname;
	if (!copy($from, $to)) {
		echo "<span style='color:red'>** ERROR ** archiving $from to $to.</span><br/>";
	} else {
		echo "<span style='color:green'>Successfully archived $from to $to.</span><br/>";
	}
}

function copySource( $fname ) {
	$from = TEST_FLR . $fname;
	$to = PROD_FLR . $fname;
	if (!copy($from, $to)) {
			echo "<span style='color:red'>** ERROR ** promoting $from to $to.</span><br/>";
	} else {
		echo "<span style='color:green'>Successfully promoted $from to $to.</span><br/>";
	}
}
