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
	showHeader('Premise Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
$('document').ready(function() {
	$('#filter_UPSAD').focus();
})

//Retrieve name of fields to populate on the calling screen 
//with selected code and description.
var codeField = '<?= $screenData['codeField']; ?>';
var descField = '<?= $screenData['descField']; ?>';

function selectPremise( premiseNo, premAddr ) {
	var opener = window.opener;
	opener.document.getElementById(codeField).value = premiseNo;
	opener.document.getElementById(descField).innerHTML = premAddr;
	opener.focus();
	opener.document.getElementById(codeField).focus();
	window.open('','_self');
	window.close();
}

//-->
</script>
 
<form name="searchForm" method="get" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >

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
		<th class="ca" width="7%">Premise#</th>
		<th class="ca" width="7%">Status</th>
		<th class="ca" width="25%">Service Address</th>
		<th class="ca" width="10%">City</th>
		<th class="ca" width="20%">Addt'l Description</th>
		<th class="ca" width="8%">Prem Type</th>
		<th class="ca" width="8%">Dwelling</th>
	</tr>
	</thead>
	<tbody>
<?php
foreach ($screenData['rows'] as $row) :
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
		<a href="javascript:selectPremise('<?= $row['UPPRM'] ?>', '<?= $row['UPSAD'] ?>');"
				title="Select Premise # <?= $row['UPPRM'] ?>">
            <?= $row['UPPRM'] ?>
		</a>
		&nbsp;
	</td>
	<td class="ca">
		<?= $row['UPSTS'] ?>	
		&nbsp;
    </td>
	<td class="la">
		<a href="javascript:selectPremise('<?= $row['UPPRM'] ?>', '<?= $row['UPSAD'] ?>');"
				title="Select Premise # <?= $row['UPPRM'] ?>">
            <?= $row['UPSAD'] ?>
		</a>
		&nbsp;
	</td>
	<td class="la">
		<?= $row['UPCTC'] ?>	
		&nbsp;
    </td>
	<td class="la">
		<?= $row['UPADL'] ?>	
		&nbsp;
    </td>
	<td class="ca">
		<?= $row['UPTYP'] ?>	
		&nbsp;
    </td>
	<td class="ca">
		<?= $row['UPDWC'] ?>
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
