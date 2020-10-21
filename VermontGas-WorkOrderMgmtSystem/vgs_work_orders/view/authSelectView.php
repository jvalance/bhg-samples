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
	showHeader('Select Authority Definition');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
//Retrieve name of fields to populate on the calling screen 
//with selected code and description.
var codeField = '<?= $screenData['codeField']; ?>';
var descField = '<?= $screenData['descField']; ?>';

function selectAuthority( authID, authName ) {
	var opener = window.opener;
	opener.document.getElementById(codeField).value = authID;
	opener.document.getElementById(descField).innerHTML = authName;
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
	<tr>
		<th class="ca" width="5%"></th>
		<th class="ca" width="15%">Authority ID</th>
		<th class="ca" width="25%">Authority Name</th>
		<th class="ca" width="35%">Description</th>
		<th class="ca" width="15%">Functional Area</th>
	</tr>
<?php
foreach ($screenData['rows'] as $row) :
	$qryStrKey = "AD_AUTH_ID={$row['AD_AUTH_ID']}";
	$viewPage = "authEditCtrl.php?mode=inquiry&$qryStrKey"; 
	$editPage = "authEditCtrl.php?mode=update&$qryStrKey"; 
	$detailScrName = 'Authority Definition';
	$editTitle = "Edit $detailScrName";
	$viewTitle = "Display $detailScrName";
	$selectTitle = "Select {$row['AD_AUTH_NAME']}";
	$selectFunction = "javascript:selectAuthority('{$row['AD_AUTH_ID']}', '{$row['AD_AUTH_NAME']}');"
?>
<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
	<td class="ca">
		<button onclick="<?= $selectFunction ?>">Select</button>
	</td>

	<td class="ca">
        <?= $row['AD_AUTH_ID'] ?>
		&nbsp;
	</td>

	<td class="la">
		<?= $row['AD_AUTH_NAME'] ?>
		&nbsp;
    </td>

	<td class="la">
		<?= $row['AD_DESCRIPTION'] ?>	
		&nbsp;
    </td>

	<td class="la">
		<?= $row['AD_FUNCTIONAL_AREA'] ?>	
		&nbsp;
    </td>
</tr>
<?php
endforeach; ?>
</table>

<!-- End of output div -->
</div>

</form>

<?php
	showFooter();
}
