<pre>
<?php
$conn=db2_connect("*LOCAL","JVALANCE","BLUE2013");
$ret=db2_exec($conn, "DROP TABLE workordt.INTME");
$ret=db2_exec($conn, "CREATE TABLE workordt.INTME (id1 INTEGER NOT NULL, char1 char(10) not null, id2 INTEGER NOT NULL)");
$ret=db2_exec($conn, "INSERT INTO workordt.INTME (id1,char1, id2) VALUES(25,'xxxxx',15)");

$stmt = db2_prepare($conn, "SELECT id2, char1, id1 FROM workordt.INTME");
$options = array(
		'cursor'=>DB2_SCROLLABLE,
		'i5_fetch_only'=>DB2_I5_FETCH_ON
);
db2_set_option($stmt, $options, 2);
db2_execute($stmt) or die(db2_stmt_errormsg());

$row = db2_fetch_assoc($stmt,1);
var_dump($row);
?>
</pre>