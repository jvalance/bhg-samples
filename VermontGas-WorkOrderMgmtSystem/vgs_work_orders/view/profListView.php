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
	showHeader('Security Profile Search');
	$pageHelper = new VGS_PaginatorViewHelper($paginator);
	echo $pageHelper->render_gotoPageJS();
	writeJavaScript();
?>
 
<form name="searchForm" method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>" >

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
		<th class="ca" width="8%"> </th>
		<th class="ca" width="5%">
				<img src="../shared/images/expand.gif" align="top" 
					title="Expand All Details" border=0 
					onclick="expandAll()" /> 
				&nbsp;
				<img src="../shared/images/collapse.gif" align="top" 
					title="Collapse All Details" border=0 
					onclick="collapseAll()" /> 
		</th> 
		<th class="ca" width="10%">Profile ID</th>
		<th class="ca" width="25%">Profile Name</th>
		<th class="ca" width="12%">Profile Type</th>
		<th class="ca" width="12%">Status</th>
		<th class="ca" width="15%">Users in Group</th>
	</tr>
<?php
foreach ($screenData['rows'] as $row) :
	$qryStrKey = "PRF_PROFILE_ID={$row['PRF_PROFILE_ID']}";
	$viewPage = "profEditCtrl.php?mode=inquiry&$qryStrKey"; 
	$editPage = "profEditCtrl.php?mode=update&$qryStrKey"; 
	$deletePage = "profEditCtrl.php?mode=delete&$qryStrKey"; 
	$detailScrName = 'Profile';
	$editTitle = "Edit $detailScrName";
	$viewTitle = "Display $detailScrName";
	$deleteTitle = "Delete $detailScrName";
	?>
	<tr onmouseover="this.className='hover';"  onmouseout="this.className='';">
		<td class="la">
			<a href="<?= $viewPage ?>">
				<img src="../shared/images/view.gif" align="top" title="<?= $viewTitle ?>" border=0 /> 
			</a>
			<a href="<?= $editPage ?>">
				<img src="../shared/images/edit.gif" align="top" title="<?= $editTitle ?>" border=0 /> 
			</a>
			<?php
			// Only allow delete if no associations for this profile
			if (! $row['2NDROW']) :  
			?>	
			<a href="<?= $deletePage ?>">
				<img src="../shared/images/delete7.gif" align="top" title="<?= $deleteTitle ?>" border=0 /> 
			</a>
			<?php 
			endif; 
			?>
		</td>

		<td class="ca">
		<?php
		if ($row['2NDROW']) :  
		?>	
			<a href="javascript:expandCollapseDetails(<?= $row['rowNum'] ?>)">
				<img src="../shared/images/expand.gif" align="top" 
				id="dropIcon_<?= $row['rowNum'] ?>" title="Expand/Collapse Details" border=0 /> 
			</a>
		<?php 
		endif; 
		?>
		&nbsp;
		</td>
	
		<td class="la">
			<a href="<?= $viewPage ?>" title="<?= $viewTitle ?>">
				<?= $row['PRF_PROFILE_ID'] ?>	
			</a>
			&nbsp;
	    </td>
	
		<td class="la">
			<a href="<?= $viewPage ?>" title="<?= $viewTitle ?>">
				<?= $row['PRF_DESCRIPTION'] ?>
			</a>	
			&nbsp;
	    </td>
	
		<td class="la">
			<?= $row['PRF_PROFILE_TYPE'] ?>	
			&nbsp;
	    </td>
	
		<td class="ca">
			<?= $row['recStatus'] ?>	
			&nbsp;
	    </td>	
	    
		<td class="ca">
			<?= $row['PRF_PROFILE_TYPE'] == 'GROUP' ? $row['USER_COUNT'] : '' ?>	
			&nbsp;
	    </td>
	</tr>
	<?php
	renderRow2($row);
	
endforeach; ?>
</table>

<!-- End of output div -->
</div>

</form>

<?php
	showFooter();
}

function renderRow2 ( &$row ) {
	if ($row['2NDROW']) : ?>
		<tr	class="droprow" id="dropRow_<?= $row['rowNum'] ?>">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td colspan="2" class="details">
			<?php 
			if (count($row['AUTHORITIES']) > 0) : 
				echo '<table class="authorityDetails" border=0>
						<tr>
						<th>Authority</th>
						<th>Permission</th>
						<th>From Profile</th>
						<th>Profile Type</th>
						</tr>
					'; 
				foreach ($row['AUTHORITIES'] as $auth) :
					echo "<tr>
							<td>
								<span class='hoverText' style='padding: 3px;' 
									  title='{$auth['AD_DESCRIPTION']}'>
									{$auth['AP_AUTH_ID']}
								</span>
							</td>
							<td>{$auth['AP_PERMISSION']}</td>
							<td>{$auth['AP_PROFILE_ID']}</td>
							<td>{$auth['PRF_PROFILE_TYPE']}</td>
						  </tr>";
				endforeach;
				echo '</table>'; 
			endif;
			?>
			&nbsp;
		</td>
		<?php 
		if ('GROUP' == trim($row['PRF_PROFILE_TYPE'])) : ?>
			<td colspan="2" class="details">
				<b><u>Users in Group <?= $row['PRF_PROFILE_ID'] ?>:</u></b>
				<?php 
				if (count($row['USERS']) > 0) :
					echo '<ul>'; 
					foreach ($row['USERS'] as $user) :
						echo "<li>{$user['UG_USER_ID']}</li>";
					endforeach;
					echo '</ul>'; 
				endif;
				?>
			</td>
		<?php 
		endif;
		if ('USER' == trim($row['PRF_PROFILE_TYPE'])) : ?>
			<td colspan="2" class="details">
				<b><u>Groups this user belongs to:</u></b>
				<?php 
				if (count($row['GROUPS']) > 0) : 
					echo '<ul>'; 
					foreach ($row['GROUPS'] as $group) :
						echo "<li>$group</li>";
					endforeach;
					echo '</ul>'; 
				endif;
				?>
			</td>
		<?php 
		endif;
		?>
		</tr>	
	<?php 
	endif;

}

function writeJavaScript() {
?>
<script type="text/javascript"> 
	<!--
	// Handle toggling visibility of row details
	function expandCollapseDetails( rowNum ) {
		// set up selector for the 2nd line of this row 
		var rowSelector = '#dropRow_' + rowNum;

		// set up selector for expand/collapse icon on this row
		var iconSelector = '#dropIcon_' + rowNum;

		// toggle visibility of the 2nd row
		$(rowSelector).toggle();

		// toggle the expand/collapse icon 
		var currentIcon =  $(iconSelector).attr('src');
		if (currentIcon.endsWith('expand.gif')) {
			$(iconSelector).attr('src', '../shared/images/collapse.gif');
		} else {
			$(iconSelector).attr('src', '../shared/images/expand.gif');
		}
	}

	// After loading page, hide all details
	$('document').ready(function() {
		// Collapse all detail rows
		$('.droprow').hide();
	});

	// Handle onclick event for "expand all" icon
	function expandAll() {
		$('.droprow').show();
		$('[id^="dropIcon"]').attr('src', '../shared/images/collapse.gif');;
	}
	
	// Handle onclick event for "collapse all" icon
	function collapseAll() {
		$('.droprow').hide();
		$('[id^="dropIcon"]').attr('src', '../shared/images/expand.gif');;
	}
	//-->
</script>

<?php 
}
?>
		