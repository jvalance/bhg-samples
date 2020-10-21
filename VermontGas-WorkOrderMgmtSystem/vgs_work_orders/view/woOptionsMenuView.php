<?php 
/**
 * This will present a list of options for additional actions
 * to perform on a workorder after creation or completion.
 */
require_once 'layout.php';

function showScreen(
	array &$woRec,
	array &$woOptions,
	VGS_Navigator $nav		
) {
	showHeader("Workorder Additional Options");
?>
<div id="navbar">
<?php 
$nav->renderNavBar();
?>
</div>

<div id="output" class="inquiry_output" style="text-align: left; font-size: 1.5em">

<form method="post">
<input type="hidden" name="WO_NUM" value="<?= $woRec['WO_NUM'] ?>">

<center>
<table border=0 width="75%">
<tr>
	<td valign="top" width="100%">
		<h3 class="left">Work Order Options: WO# <?= $woRec['WO_NUM'] ?></h3>
		<ul>
			<?php 
			foreach ($woOptions as $option) :
				$href = "openSecondaryWindow('{$option['url']}','{$option['name']}')";
				?>
				<li><a href="#" onclick="<?=$href?>"><?=$option['title']?></a></li>
				<?php 
			endforeach;
			?>
		</ul>
	</td>
</tr>

<tr>
	<td class="center">
		<input type="submit" name="doneButton" value="Done/Return" />		
	</td>
	
</tr>
</table>
</center>

</form>
</div>

<script>
function openSecondaryWindow(url, name) { 
	var opts = "status=no,toolbar=no,location=no,menubar=no,scrollbars=yes,resizable=yes,directories=no";
	window.open(url, name, opts); 
}
</script>
<?php
showFooter ();
}
