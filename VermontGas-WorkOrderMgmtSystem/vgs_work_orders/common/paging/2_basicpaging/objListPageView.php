<?php 
function showObjectList( $tableRows, $pagingData ) 
{
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

	<!-- <NEW> -->
	<caption>
		<script type="text/javascript">
			function gotoPage(page) {
				document.listingForm.pageToView.value = page;
				document.listingForm.submit();
			}
		</script>
		Page <?= $pagingData['pageToView'] ?> 
		  
		<input type="button" value="<< Previous" 
			<?= $pagingData['prevButtonState'] ?>
			onclick="javascript:gotoPage(<?= $pagingData['pageToView'] - 1 ?>);" />
 
		Go to Page: 
		<input type="text" name="pageToView" size="3" value="<?= $pagingData['pageToView'] ?>" /> 
		<input type="submit" value="Go" />
		 
		<input type="button" value="Next >>" 
			<?= $pagingData['nextButtonState'] ?>
			onclick="javascript:gotoPage(<?= $pagingData['pageToView'] + 1 ?>);" />
	</caption>
	<!-- </NEW> -->
	
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
<?php
// end of function body 
}
?>