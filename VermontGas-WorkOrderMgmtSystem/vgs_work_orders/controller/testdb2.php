<pre>
<?php
require_once '../model/WO_Sewer.php';
require_once '../model/VGS_DB_Conn_Singleton.php';
require_once '../common/vgs_utilities.php';

$conn = VGS_DB_Conn_Singleton::getInstance();

$sql = "Select WSW_WO_NUM, WSW_ADDRESS, WSW_SEQNO  from WO_SEWER";
$stmt = db2_prepare($conn, $sql) or die(db2_stmt_errormsg());
$options = array(
		'cursor'=>DB2_SCROLLABLE, 
		'i5_fetch_only'=>DB2_I5_FETCH_ON
);
db2_set_option($stmt, $options, 2);

db2_execute($stmt) or die(db2_stmt_errormsg());

for ($i=1; $i<=10; $i++) {
	$row = db2_fetch_assoc( $stmt, $i ) or die(db2_stmt_errormsg());
	echo 'WO#: ' . $row['WSW_WO_NUM'] . 
		 '; Swr Seq#: ' . $row["WSW_SEQNO"] . 
		 '; Address: ' . $row['WSW_ADDRESS'] .
		 '<br>';
}

db2_close($conn);
?>
</pre>