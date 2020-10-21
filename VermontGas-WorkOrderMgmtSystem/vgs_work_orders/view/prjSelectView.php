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
	showHeader('Project Lookup');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
//Initially position cursor on description filter
$('document').ready(function() {
	$('#filter_PRJ_DESCRIPTION').focus();
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
		<th class="ca" width="5%">Proj#</th>
		<th class="ca" width="25%">Description</th>
		<th class="ca" width="10%">Status</th>
		<th class="ca" width="15%">Contact</th>
		<th class="ca" width="10%">Feasibility #</th>
		<th class="ca" width="15%">Municipality</th>
		<th class="ca" width="5%">Zone</th>
		<th class="ca" width="10%">Cap/Exp</th>
	</tr>
	</thead>
	<tbody>
<?php
foreach ($screenData['rows'] as $row) :
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
   	<td class="ca">
		<a href="javascript:select('<?= $row['PRJ_NUM'] ?>', '<?= htmlspecialchars($row['PRJ_DESCRIPTION']) ?>');"
				title="Select: <?php echo htmlspecialchars($row['PRJ_DESCRIPTION']); ?>">
            <?= $row['PRJ_NUM'] ?>
		</a>
		&nbsp;
    </td>
	<td class="la">
		<a href="javascript:select('<?= $row['PRJ_NUM'] ?>', '<?= htmlspecialchars($row['PRJ_DESCRIPTION']) ?>');"
				title="Select: <?php echo htmlspecialchars($row['PRJ_DESCRIPTION']); ?>">
            <?=$row['PRJ_DESCRIPTION'] ?>
		</a>
		&nbsp;
	</td>
	<td class="la">
		<?= $row['status_desc'] ?>	
		&nbsp;
    </td>
    <td class="la">
		<?= $row['PRJ_CONTACT_PERSON'] ?>	
		&nbsp;
    </td>
	<td class="ca">
		<?= $row['PRJ_FEASABILITY_NUM'] ?>
		&nbsp;
    </td>
    <td class="la">
		<?= $row['town_desc'] ?>
		&nbsp;
    </td>
	<td class="ca">
		<?= $row['PRJ_ZONE'] ?>
		&nbsp;
    </td>
	<td class="ca">
		<?= $row['capexp_desc'] ?>
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
