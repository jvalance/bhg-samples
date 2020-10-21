<?php
/* 
  This script will redirect to the main menu. 
  It will allow a user to request a simple URL of the form:
  http://workorders.vermontgas.com/wo/
  or:
  http://workorders.vermontgas.com/wotest/
  without knowing the name of the menu script.
 */  
header("Location: ./controller/menuMainCtrl.php");
exit; 