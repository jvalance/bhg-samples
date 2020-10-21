<?php 
require_once '../model/Security.php';

$sec = new Security();
$sec->logout();

// Redirect to login page
header("Location: loginCtrl.php");
