<?php 
require_once 'layout.php';
require_once 'form.php';
require_once 'VGS_PaginatorViewHelper.php';

function showScreen( 
	array &$screenData, 
	VGS_Paginator $paginator,
    VGS_Search_Filter_Group $filter,
    VGS_Navigator $nav
	) 
{   
	showHeader('Crew/Employee Lookup');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
//Initially position cursor on description filter
$('document').ready(function() {
	$('#filter_NAME').focus();
})

// Retrieve name of fields to populate on the calling screen 
// with selected project# and description
var codeField = '<?= $screenData['codeField']; ?>';
var descField = '<?= $screenData['descField']; ?>';

// Populate caller fields with selected project values and close this screen
function select( code, desc ) {
	var opener = window.opener;
	opener.document.getElementById(codeField).value = code;
	opener.document.getElementById(descField).innerHTML = desc;
	opener.focus();
	opener.document.getElementById(codeField).focus();
	window.open('','_self');
	window.close();
}
//-->
</script>
 
<form name="searchForm" method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >

<input type="hidden" name="codeField" value="<?= $screenData['codeField']; ?>" />
<input type="hidden" name="descField" value="<?= $screenData['descField']; ?>" />

<?php 
$nav->renderNavBar();
?>

<div id="output"> 
<?php 
$filter->renderView($screenData);
?>


<table class="lists" >
<caption>
	<?php $pageHelper->renderView(); ?>
</caption>
	<thead>
	<tr>
		<th class="ca" width="15%">Crew/Employee#</th>
		<th class="ca" width="25%">Name</th>
		<th class="ca" width="25%">Type</th>
		<th class="ca" width="25%">Status</th>
		<th class="ca" width="25%">Dept.</th>
	</tr>
	</thead>
	<tbody>
<?php
foreach ($screenData['rows'] as $row) :
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
   	<td class="ca">
		<a href="javascript:select('<?= $row['ID'] ?>', '<?= addslashes ($row['NAME']) ?>');"
				title="Select: <?php echo addslashes ($row['NAME']); ?>">
            <?= $row['ID'] ?>
		</a>
		&nbsp;
    </td>
	<td class="la">
		<a href="javascript:select('<?= $row['ID'] ?>', '<?= addslashes ($row['NAME']) ?>');"
				title="Select: <?php echo addslashes ($row['NAME']); ?>">
            <?=$row['NAME'] ?>
		</a>
		&nbsp;
	</td>
	<td class="la">
		<?= $row['TYPE'] ?>	
		&nbsp;
    </td>
	<td class="la">
		<?= $row['STATUS'] ?>	
		&nbsp;
    </td>
	<td class="la">
		<?= $row['DEPT'] ?>	
		&nbsp;
    </td>
</tr>
<?php
endforeach; ?>
</tbody>
</table>

<!-- End of output div -->
</div>

</form>

<?php
	showFooter();
}
