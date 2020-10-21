<?php 
require_once 'layout.php';

function showScreen(&$screenData = null) {
showHeader("Main Menu");
?>
<div id="navbar">
<p>

</div>

<div id="output" class="inquiry_output" style="text-align: left; font-size: 1.2em">
<center>
<table border=0 width="75%">
<tr>
<td valign="top" width="50%">
	<h3 class="left">Work Orders</h3>
	<ul>
		<li><a href="woListCtrl.php?filter_WO_STATUS=*not_cnl">All Work Orders</a></li>
		<li><a href="woListCtrl.php?filter_WO_TYPE=L*&filter_WO_STATUS=*not_cnl">Leaks</a></li>
		<li><a href="woCreateCtrl.php">Create New Work Order</a></li>
		<li><a href="premSelect_SRST_Ctrl.php">Create Multiple SR/ST Orders</a></li>
		<li><a href="wpeListCtrl.php">Pipe Exposures</a></li>
		<li><a href="wcListCtrl.php">Cleanups</a></li>
		<li><a href="wswListCtrl.php">Sewers</a></li>
		<li><a href="wefListCtrl.php">Electrofusions</a></li>
		<li><a href="ppListCtrl.php">Plastic Pipe Failures</a></li>
		<li><a href="mfListCtrl.php">Mechanical Fitting Failures</a></li>
	</ul>
	<h3 class="left">Tables</h3>
	<ul> 
		<li><a href="svServiceListCtrl.php">Services</a></li>
		<li><a href="prjListCtrl.php">Projects</a></li>
		<li><a href="ptListCtrl.php">Pipe Types</a></li>
		<li><a href="cgListCtrl.php">Drop Down Lists</a></li>
	</ul>
</td> 

<td valign="top" width="50%">
	<h3 class="left">Security</h3>
	<ul>
		<li><a href="profListCtrl.php?filtSts=restore">Profiles</a></li>
		<li><a href="userGroupListCtrl.php?filtSts=restore">User/Group Xref</a></li>
		<li><a href="authListCtrl.php?filtSts=restore">Authorities</a></li>
		<li><a href="authProfListCtrl.php?filtSts=restore">Profile Authorities</a></li>
		<?php
		/** Only sysAdmin user in TEST or DEV environment can adopt authority of another user.
		 *  This is intended for testing of user's permissions. */ 
		if ($screenData['allowProfileSwap'] === true) : ?>
			<li>Test with profile: 
			<form method="post" action="testSec.php">
				<input name="swap_user" size="15" 
						onblur="this.value=this.value.toUpperCase();" 
						onchange="this.value=this.value.toUpperCase();" />
				<input type="submit" value="Change user" />
			</form>
		<?php
		endif;
		?>
		</ul>

	<h3 class="left">Reporting</h3>
	<ul>
		<li><a href="wcnCancelledOrdersDownloadCtrl.php">Cancelled Work Orders download</a></li>
		<li><a href="woInvtyReconcileCtrl.php">W/O Inventory Reconciliation</a></li>
	</ul>

	<h3 class="left">System</h3>
	<ul>
		<li><a href="dblListCtrl.php">DB Update Log</a></li>
		<li><a href="logoutCtrl.php">Log Out</a></li>
	</ul>
</td>

</tr>
</table>
</center>
</div>

<?php
showFooter ();
}
