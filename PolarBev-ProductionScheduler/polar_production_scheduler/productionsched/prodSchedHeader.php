<?php
require_once '../common/front.php';
use polarbev\Environment as Environment;
date_default_timezone_set('America/New_York');

//----------------------------------------------------------------------
function addHeadSection($title) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
    <title><?=strip_tags($title)?></title>

    <link rel="stylesheet" type="text/css" media="screen" href="css/redmond/jquery-ui-1.8.22.custom.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/site.css" />


    <script src="js/date.js" type="text/javascript"></script>
    <script src="js/utils.js" type="text/javascript"></script>
    <script src="js/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script src="js/jquery-ui-1.8.22.custom.min.js" type="text/javascript"></script>
    <script src="js/i18n/grid.locale-en.js" type="text/javascript"></script>
    <script src="js/jquery.jqGrid.min.js" type="text/javascript"></script>
    <script src="js/jquery.simpletip-1.3.1.js"></script>

    <script src="js/app_prod_sched.js"></script>
    <script src="js/app_planned.js"></script>
    <script src="js/app_weekly.js"></script>
    <script src="js/app_daily.js"></script>

    </head>
<?php
}
//----------------------------------------------------------------------

function showHeader($displayFiltersString, $formFields) {
	$environment = Environment::getEnvironment();
?>

<body>

<!-- Page banner with company logo -->
<div id="pageheader">

<table class="phdr" width="100%">
	<tr>
		<td class="left" style="width: 20%">
			<img border="0" style="background-color:white" align="left"
			src="images/logo50.png">
		</td>

		<td class="center <?= $environment ?>"  style="width: 60%">
			<h2>Polar Beverage Production Schedule Maintenance</h2>
			<h3><?= $displayFiltersString ?></h3>
	<form name="prodSchedForm" id="prodSchedForm" method="post">

		<button id="backButton" onclick="doStartOver()" type="button" style="display:none">
			Restart
		</button>

		<button id="refreshButton" onclick="doReload()" type="button">
			Reload
		</button>

		<button id="saveButton" onclick="doSave();" name="saveButton"
				type="button" class="save_button" style="display:none">
			Save Changes
		</button>

		<input type="hidden" name="jsonWeekly" value="" />
		<input type="hidden" name="jsonDaily" value="" />
		<input type="hidden" name="action" id="action" value="" />
		<input type="hidden" name="facility" value="<?= $formFields["facility"]; ?>" />
		<input type="hidden" name="work_ctr" value="<?= $formFields["work_ctr"]; ?>" />
		<input type="hidden" name="from_date" value="<?= $formFields["from_date"]; ?>" />
		<input type="hidden" name="to_date" value="<?= $formFields["to_date"]; ?>" />
		<input type="hidden" name="weekly_from_date" value="<?= $formFields["weekly_from_date"]; ?>" />
		<input type="hidden" name="weekly_current_date" id="weekly_current_date" value="<?= $formFields["weekly_current_date"]; ?>" />
		<input type="hidden" name="debug" id="debug" value="<?= $formFields['debug']; ?>" />
	</form>

		</td>

		<td id="sysinfo" class="sysinfo right" style="width: 20%">
			<span class="env_<?= $environment ?>">
				Env=<?= $environment ?>; DB User=<?= Environment::getUID() ?>
			</span><br>
			<span class="label">User:</span>
				<?php
				if (isset($_SESSION['current_user'])) :
					echo strtoupper($_SESSION['current_user']) . ' [<a href="'. FRONT_BASE_FOLDER . 'common/logoutCtrl.php">logout</a>]';
				else :
					echo 'Not logged in.';
				endif;
				?>
				<br>
			<?= date('M d, Y') . ' @ ' . date('h:i:s a')?><br>
		</td>
	</tr>
</table>


</div>

<div id="content" align="center">
<a name="topTag"></a>
<?php
}

//----------------------------------------------------------------------
function showFooter() {
?>
</div>
</div>

<div id="pagefooter">
&nbsp;<p />&nbsp;
<hr style="color:silver">
<p class="footer" style="text-align: center">
      Polar Beverages<br/> 1001 Southbridge Street, Worcester, MA 01610. Phone: 800-734-9800.
</p>
</div>

</body>
</html>
<?php
}
