<?php
require_once 'front.php';
use polarbev\Environment as Environment;

//----------------------------------------------------------------------
function showHeader($title) {
	$environment = Environment::getEnvironment();
// 	if ($environment != Environment::ENV_PROD) :
// 		$title = "$environment $title";
// 	endif;
	$titleClass = ' ';

	$script = getScriptName();
	$fileName = $_SERVER['SCRIPT_NAME'];
	$fileName = substr($fileName, strrpos($fileName, '/')+1);
	date_default_timezone_set('America/New_York');
?>
<!DOCTYPE html>
<html>
<head>
<title><?=strip_tags($title)?></title>

<link rel="stylesheet" type="text/css" media="screen" href="<?= FRONT_BASE_FOLDER  ?>productionsched/css/redmond/jquery-ui-1.8.22.custom.css" />
<link rel="stylesheet" type="text/css" media="screen" href="<?= FRONT_BASE_FOLDER  ?>productionsched/css/ui.jqgrid.css" />
<link rel="stylesheet" type="text/css" media="screen" href="<?= FRONT_BASE_FOLDER  ?>productionsched/css/site.css" />


<script src="<?= FRONT_BASE_FOLDER  ?>productionsched/js/date.js" type="text/javascript"></script>
<script src="<?= FRONT_BASE_FOLDER  ?>productionsched/js/utils.js" type="text/javascript"></script>
<script src="<?= FRONT_BASE_FOLDER  ?>productionsched/js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="<?= FRONT_BASE_FOLDER  ?>productionsched/js/jquery-ui-1.8.22.custom.min.js" type="text/javascript"></script>
<script src="<?= FRONT_BASE_FOLDER  ?>productionsched/js/i18n/grid.locale-en.js" type="text/javascript"></script>
<script src="<?= FRONT_BASE_FOLDER  ?>productionsched/js/jquery.jqGrid.min.js" type="text/javascript"></script>
<script src="<?= FRONT_BASE_FOLDER  ?>productionsched/js/jquery.simpletip-1.3.1.js"></script>
<!-- <script src="http://jquery-simpletip.googlecode.com/files/jquery.simpletip-1.3.1.pack.js"></script> -->

</head>

<body>

<!-- Page banner with company logo -->
<div id="pageheader">

<table class="phdr" width="100%">
	<tr>
		<td class="left" style="width: 20%">
			<img border="0" style="background-color:white" align="left"
			src="<?= FRONT_BASE_FOLDER  ?>productionsched/images/logo50.png">
		</td>

		<td class="center <?= $environment ?>"  style="width: 60%">
			<h2>Polar Beverage Production Schedule Maintenance</h2>
			<h3><?= $title ?></h3>

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
