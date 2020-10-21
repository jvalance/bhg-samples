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
	showHeader('Pipe Type Lookup');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
//$('document').ready(function() {
//	$('#filter_UPSAD').focus();
//})

var codeField = '<?= $screenData['codeField']; ?>';
var descField = '<?= $screenData['descField']; ?>';
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
		<th class="ca" width="15%">Pipe Type</th>
		<th class="ca" width="12%">Cost GL</th>
		<th class="ca" width="12%">Close GL</th>
		<th class="ca" width="10%">Cap/Exp</th>
		<th class="ca" width="10%">Matrl</th>
		<th class="ca" width="10%">Diam</th>
		<th class="ca" width="10%">Categ</th>
		<th class="ca" width="10%">Coating</th>
	</tr>
	</thead>
	<tbody>
<?php
foreach ($screenData['rows'] as $pt) :
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="la">
		<a href="javascript:select('<?= $pt['PT_PIPE_TYPE'] ?>', '<?= htmlspecialchars($pt['PT_DESCRIPTION']) ?>');"
				title="Select: <?php echo htmlspecialchars($pt['PT_DESCRIPTION']); ?>">
            <?= $pt['PT_PIPE_TYPE'] ?>:  <?=$pt['PT_DESCRIPTION'] ?>
		</a>
		&nbsp;
	</td>

	<td class="la">
		<?= $pt['PT_ACCTG_UNIT_COST'] ?>-<?=$pt['PT_GL_ACCT_COST'] ?>-<?=$pt['PT_SUB_ACCT_COST'] ?>	
		&nbsp;
    </td>
    <td class="la">
		<?= $pt['PT_ACCTG_UNIT_CLOSE'] ?>-<?=$pt['PT_GL_ACCT_CLOSE'] ?>-<?=$pt['PT_SUB_ACCT_CLOSE'] ?>	
		&nbsp;
    </td>
	<td class="ca">
		<?= $pt['capexp_desc'] ?>
		&nbsp;
    </td>
    <td class="ca">
		<?= $pt['material_desc'] ?>
		&nbsp;
    </td>
	<td class="ra">
		<?= $pt['PT_DIAMETER'] ?>
		&nbsp;
    </td>
	<td class="ca">
    	<?= $pt['category_desc']; ?>
		&nbsp;
    </td>
   	<td class="ca">
    	<?= $pt['coating_desc']; ?>
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
