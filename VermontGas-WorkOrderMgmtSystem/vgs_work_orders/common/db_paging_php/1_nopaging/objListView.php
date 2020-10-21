<?php 
function showObjectList( $tableRows ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="stylesheet" href="../styles.css" type="text/css" />
</head>

<body>
<center>
<div id="header">
<h2>Object Listing</h2>
</div>

<form name="listingForm" action="<?= $_SERVER['SCRIPT_NAME']; ?>" method="post">

<table class="lists">
	<tr>
		<th width="5%">Row#</th>
		<th width="20%">Object Name</th>
		<th width="50%">Description</th>
		<th width="15%">Type</th>
		<th width="10%">Attribute</th>
	</tr>
<?php 
foreach($tableRows as $row) :
?> 
	<tr> 
		<td>
			<?= $row['rowNumber']; ?>
			&nbsp;
		</td>
		<td>
			<?= $row['OBJECT_NAME']; ?>
			&nbsp;
		</td>
		<td>
			<?= $row['OBJECT_DESCRIPTION']; ?>
			&nbsp;
		</td>
		<td>
			<?= $row['OBJECT_TYPE'];?>
			&nbsp;
		</td>
		<td>
			<?= $row['OBJECT_ATTRIBUTE']; ?>
			&nbsp;
		</td>
	</tr> 
<?php 
endforeach;
?>
</table>
</center>
</form>
</div>
</body>
</html>

<?php // end of function body 
}
?>