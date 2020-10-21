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
	showHeader('Select Security Profile');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
//Retrieve name of fields to populate on the calling screen 
//with selected code and description.
var codeField = '<?= $screenData['codeField']; ?>';
var descField = '<?= $screenData['descField']; ?>';

function selectProfile( grpID, grpName ) {
	var opener = window.opener;
	opener.document.getElementById(codeField).value = grpID;
	opener.document.getElementById(descField).innerHTML = grpName;
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
	<tr>
		<th class="ca" width="5%"> </th>
		<th class="ca" width="25%">Profile ID</th>
		<th class="ca" width="35%">Profile Description</th>
		<th class="ca" width="35%">Profile Type</th>
		<th class="ca" width="15%">Users in Group</th>
	</tr>
<?php
foreach ($screenData['rows'] as $row) :
	$selectTitle = "Select {$row['PRF_PROFILE_ID']}";
	$selectFunction = "javascript:selectProfile('{$row['PRF_PROFILE_ID']}', '{$row['PRF_DESCRIPTION']}');"
	
?>
	<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
		<td class="ca">
			<button onclick="<?= $selectFunction ?>">Select</button>
		</td>
	
		<td class="la">
			<?= $row['PRF_PROFILE_ID'] ?>	
			&nbsp;
	    </td>
	
		<td class="la">
			<?= $row['PRF_DESCRIPTION'] ?>
			&nbsp;
	    </td>
	
		<td class="la">
			<?= $row['PRF_PROFILE_TYPE'] ?>
			&nbsp;
	    </td>
	
		<td class="la">
			<?= $row['USER_COUNT'] ?>	
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
