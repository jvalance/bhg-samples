<?php
require_once '../common/common_errors.php';
require_once '../common/vgs_utilities.php';
require_once '../model/Security.php';

error_reporting(E_RECOVERABLE_ERROR | E_ERROR);
// error_reporting(E_ALL); // uncomment this line to see all php messages

if (VGS_DB_Conn_Singleton::isDevEnvironment()
|| VGS_DB_Conn_Singleton::isTestEnvironment()) {
	// Display erros in development environment
	ini_set('display_errors', 1);
}

 
set_error_handler("errorLogger", E_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);

session_start();
$script = getScriptName();
 
$_SESSION['previousPage'] = $_SESSION['savedPage'];
$_SESSION['savedPage'] = $_SERVER['PHP_SELF'];
if ($_SERVER['QUERY_STRING'] > '') {
	$_SESSION['savedPage'] .= '?'. $_SERVER['QUERY_STRING'];  
}  
$globalSec = new Security();

if (!$globalSec->isValidUserLoggedIn()
&& ($script != 'loginCtrl.php')) {
	$_SESSION['login_redirect_to'] = $_SESSION['savedPage'];
	header("Location: ../controller/loginCtrl.php");
	exit;
}

//----------------------------------------------------------------------
function spawnNewWindow() {
	// Extract script file name from full path
	$url = urldecode($_GET['spawn']);
	if (strpos($url, '?') === false) {
		$url .= '?popup=true';
	} else {
		$url .= '&popup=true';
	}
	
	echo <<<SPAWNJS
	<script>
		openPopUp('$url', 'PopUpWin');
	</script>
SPAWNJS;
}

//----------------------------------------------------------------------
function getScriptName() {
	// Extract script file name from full path
	$script = $_SERVER['SCRIPT_NAME'];
	$script = str_replace( '\\', '/', $script);
	$script = substr( $script, strrpos($script, '/')+1);
	return $script;
}

//----------------------------------------------------------------------
function showHeader($title) {

	$titleClass = ' ';
	// Set title colors different for certain screens (so the user knows where they are!)
	if (strpos(trim($title), 'Select') === 0) {
		$titleClass .= ' selectRecordTitle ';
	}
	if (strpos(trim($title), 'Delete') === 0) {
		$titleClass .= ' deleteTitle ';
	}
	
	$script = getScriptName();
	$fileName = $_SERVER['SCRIPT_NAME'];
	$fileName = substr($fileName, strrpos($fileName, '/')+1); 
	date_default_timezone_set('America/New_York');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">	
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head> 
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title><?=$title?></title>
<link href="../shared/css/site.css" media="screen" rel="stylesheet" type="text/css" />
<link href="../shared/css/site.css" media="print" rel="stylesheet" type="text/css" />
<link href="../shared/css/sitestyle.css" media="screen" rel="stylesheet" type="text/css" />
<link href="../shared/css/sitestyle.css" media="print" rel="stylesheet" type="text/css" />
<link href="../shared/css/jquery-ui-custom.css" media="screen" rel="stylesheet" type="text/css" />
<link href="../shared/css/jquery-ui-custom.css" media="print" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../shared/js/utilities.js"></script>
<script type="text/javascript" src="../shared/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="../shared/js/jquery-ui-1.8.11.custom.min.js"></script>

<script language="JavaScript" type="text/JavaScript">
var browserName=navigator.appName; 
if (browserName=="Microsoft Internet Explorer") {
	browserName = 'IE';
} else {
	browserName = 'Mozilla';
}

$('document').ready(function(){
	
	//Hilite selected checkbox labels in MultiOptions lists
	var modeExists = $('input[name="mode"]').length > 0;
	if (!modeExists) {
		// This is not a record detail screen - get out of here!
		return;
	}

	$('input[type="checkbox"]:checked').parent().addClass( "checkBoxChecked" );
	$('input[type="checkbox"]:checked').addClass( "checkBoxChecked" );
	
	<?php
	
	if ($_REQUEST['mode']=='inquiry' || $_REQUEST['mode']=='delete') :

		// Define img tags for output only checkboxes 
		$checkedImg = '<img src="../shared/images/cb_yes.png" style="padding-right:2px; vertical-align: middle" />';
		$uncheckedImg = '<img src="../shared/images/cb_no.png" style="padding-right:2px; vertical-align: middle" />';
		?>
		
		// In inquiry and delete modes, hide all checkbox inputs so they can't be changed. 
		// And display the corresponding image for checked or unchecked boxes
		$('input[type="checkbox"]:checked').after('<?= $checkedImg ?>');
		$('input[type="checkbox"]:checked').hide();
		$('input[type="checkbox"]:not(:checked)').after('<?= $uncheckedImg ?>');
		$('input[type="checkbox"]:not(:checked)').hide();

		// Hide text inputs and just show their values (this is to get around the inability
		// to override styling of disabled inputs in IE).
		$('input[type="text"], textarea').each(function(){
			var inpValue = $(this).val();
			if ($(this).attr('type') == 'textarea'){
				// Replace line breaks in textareas with <br> tags
				inpValue = inpValue.replace(/\n/g, '<br />');
			}
			$(this).after(inpValue + '&nbsp;'); // add the text
			$(this).hide(); // hide the input
		});
		
		// In inquiry and delete modes, hide all select drop down inputs so they can't be changed. 
		// And display the corresponding text for the selected option instead
		$("select").each(function(){
			var sel_text = $(this).find('option:selected').text();
			$(this).after(sel_text); // add the text
			$(this).hide(); // hide the dropdown
		});

	<?php
	else:
	?>
		// If not inquiry or delete mode, hilite or unhilite all checkbox labels when 
		// they are clicked, depending on their checked state.
		$('input[type="checkbox"]').click(function(){
			if ($(this).is(':checked')) {  
				$(this).parent().addClass( "checkBoxChecked" );
			} else {
				$(this).parent().removeClass( "checkBoxChecked" );
			}
		});
	<?php 
	endif;
	?>
	
});

</script>

<style type="text/css">
	td.DEV { background-color: #EAEAEA 	}
	td.TEST { background-color: #F9F0AB }
	td.PROD { background-color: white 	}
</style>

</head>
	
<body style="background-color:#e2f1fa">
<?php 
if (isset($_GET['spawn'])) {
	spawnNewWindow();
}
?>
<center>

<div id="pageheader"> <!-- Page banner with company logo -->
<?php 
$env = VGS_DB_Conn_Singleton::getEnvironment();
?>

<table class="phdr" width="100%">
	<tr>
		<td class="left">
			<img border="0" style="background-color:white" align="left"
			src="../shared/images/vtg_logo2.gif">
			<?php 
			if (isset($_SESSION['current_user'])) :
				if ($env == VGS_DB_Conn_Singleton::DB_TEST
				||  $env == VGS_DB_Conn_Singleton::DB_DEV) :
					$styleSpan = 'font-weight :bold; color: yellow; background-color: green;';
				endif;
				echo "<span style='padding-left:10px;padding-right:10px;$styleSpan'>Database is $env</span><br>";
			endif; 
			?>
		</td>
		<td class="center <?= $env; ?>" >
			<h2 class="label">VGS Work Order Management System</h2>
			<h1 class="<?= $titleClass?>" ><?=$title?></h1>			
		</td>
		<td class="sysinfo right">
			<span class="label">Script:</span> <?= $script ?> <br> 
			<span class="label">User:</span> 
				<?php 
				if (isset($_SESSION['current_user'])) :
					echo strtoupper($_SESSION['current_user']) . ' [<a href="logoutCtrl.php">logout</a>]';
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

<div id="pagefooter">
<p class="footer">&copy;2010-<?php echo date('Y')?> Vermont Gas Systems, Inc. <br />
      P.O. Box 467, Burlington VT 05402. Phone: 802.863.4511. <br />
</div>

<a name="bottomTag"></a>
</body>
</html>
<?php 
}
?>