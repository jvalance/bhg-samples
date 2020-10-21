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
	showHeader('User/Group Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
?>
<script type="text/javascript">
<!--	
//-->
</script>
 
<form name="searchForm" method="get" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >

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
		<th class="ca" width="10%"> </th>
		<th class="ca" width="15%">Group Profile ID</th>
		<th class="ca" width="30%">Group Name</th>
		<th class="ca" width="15%">User ID</th>
		<th class="ca" width="30%">User Name</th>
	</tr>
<?php
foreach ($screenData['rows'] as $row) :
	$qryStrKey = "UG_GROUP_ID={$row['UG_GROUP_ID']}&UG_USER_ID={$row['UG_USER_ID']}";
	$viewPage = "userGroupEditCtrl.php?mode=inquiry&$qryStrKey"; 
	$editPage = "userGroupEditCtrl.php?mode=update&$qryStrKey"; 
	$deletePage = "userGroupEditCtrl.php?mode=delete&$qryStrKey"; 
	$detailScrName = 'User/Group Profile Xref';
	$editTitle = "Edit $detailScrName";
	$viewTitle = "Display $detailScrName";
	$deleteTitle = "Remove User from Group";
?>
	<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
		<td class="ca">
			<a href="<?= $viewPage ?>">
				<img src="../shared/images/view.gif" align="top" title="<?= $viewTitle ?>" border=0 /> 
			</a>
			<a href="<?= $editPage ?>">
				<img src="../shared/images/edit.gif" align="top" title="<?= $editTitle ?>" border=0 /> 
			</a>
			<a href="<?= $deletePage ?>">
				<img src="../shared/images/delete7.gif" align="top" title="<?= $deleteTitle ?>" border=0 /> 
			</a>
		</td>
	
		<td class="la">
			<a href="<?= $viewPage ?>" title="<?= $viewTitle ?>">
				<?= $row['UG_GROUP_ID'] ?>	
			&nbsp;
	    </td>
	
		<td class="la">
			<a href="<?= $viewPage ?>" title="<?= $viewTitle ?>">
				<?= $row['group_name'] ?>
			</a>	
			&nbsp;
	    </td>
	
		<td class="la">
			<?= $row['UG_USER_ID'] ?>	
			&nbsp;
	    </td>
	
		<td class="la">
			<?= $row['user_name'] ?>	
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
