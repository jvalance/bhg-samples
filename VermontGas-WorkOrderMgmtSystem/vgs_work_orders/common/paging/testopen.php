<?php 
$page = $_REQUEST['page'];
?>
<html>
<body>

<script type="text/javascript">
popup = window.open('<?= $page ?>', 'ibmwin');
popup.focus();
window.open('','_self');
window.close();
</script>

</body>
</html>