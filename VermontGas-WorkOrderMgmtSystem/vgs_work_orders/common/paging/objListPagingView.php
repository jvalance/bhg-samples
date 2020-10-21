<?php 
function showObjectList( $screenData ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<style type="text/css"> 
	body { font-family: arial; color: navy; background-color: #ccc; }
	input[type=button] { margin: 5px 10px }
	h2 { border: 3px ridge silver; background-color: #EEF; font-family: times; 
		width: 50%;  color: darkred; font-size:2em; margin: 4px; padding: 3px}
	#header { margin: 0 auto; width: 90%;  }
	table.lists caption { background-color: beige; border: 1px solid navy; }
	table.lists { background-color: white; border: 1px solid navy; width: 90%; 
					padding: 5px; padding-bottom: 25px }
	table.lists th { background-color: navy; color: white; padding: .3em }
	table.lists td { border-bottom: 2px solid silver; vertical-align: top; 
					font-family: verdana; font-size: .85em; padding: 2px .3em }
</style>
<script type="text/javascript">
	function gotoPage(page) {
		document.objectList.pageToView.value = page;
		document.objectList.submit();
	}
</script>
</head>

<body>
<center>
<div id="header">
<h2>Object Listing</h2>
</div>

<form name="objectList" action="<?= $_SERVER['SCRIPT_NAME']; ?>" method="post">

<table class="lists">
	<caption>
		Displaying page <?= $screenData['currentPage'] ?> 
		of <?= $screenData['numberOfPages'] ?>. 
		&nbsp;&nbsp;&nbsp;
		Filter on Object Type: <input name="type" value="<?= $screenData['type'] ?>" />
		<br />
		  
		<input type="button" value="First Page" 
			<?= $screenData['prevButtonState'] ?>
			onclick="javascript:gotoPage(1);" />
		
		<input type="button" value="<< Previous" 
			<?= $screenData['prevButtonState'] ?>
			onclick="javascript:gotoPage(<?= $screenData['currentPage'] - 1 ?>);" />
		Go to Page: 
		<input type="text" name="pageToView" size="3" value="<?= $screenData['currentPage'] ?>" /> 
		<input type="submit" value="Go" />
		 
		<input type="button" value="Next >>" 
			<?= $screenData['nextButtonState'] ?>
			onclick="javascript:gotoPage(<?= $screenData['currentPage'] + 1 ?>);" />
		
		<input type="button" value="Last Page" 
			<?= $screenData['nextButtonState'] ?>
			onclick="javascript:gotoPage(<?= $screenData['numberOfPages'] ?>);" />
	</caption>
	<tr>
		<th width="5%">Row#</th>
		<th width="20%">Object Name</th>
		<th width="50%">Description</th>
		<th width="15%">Type</th>
		<th width="10%">Attribute</th>
	</tr>
<?php 
foreach($screenData['tableRows'] as $row) :
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