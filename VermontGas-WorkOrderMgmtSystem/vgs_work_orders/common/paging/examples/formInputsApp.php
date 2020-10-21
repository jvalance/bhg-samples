<?php 
require_once 'Form_Input.php';

$ipCustNumber = new Form_Input('CUNUM');
$ipCustNumber->label = 'Customer Number';
$ipCustNumber->value = 61325;
$ipCustNumber->setOutputOnly();

$ipCustName = new Form_Input('CUNAME');
$ipCustName->label = 'Customer Name';
$ipCustName->value = 'Acme Welding';

$ipCustInactive = new Form_Input('CUINACT', 'checkbox');
$ipCustInactive->label = 'Customer is Inactive?';

// Page to return to after form processing
$caller = new Form_Input('caller', 'hidden');
$caller->value = $_SERVER['HTTP_REFERER'];

$submit = new Form_Input('saveButton', 'submit');
$submit->value = 'Save Changes';

?>
<form>
	<?php echo $caller->render(); ?>
	
	<table border=0 width="50%">
		<caption>Customer Information</caption>
		<?php 
		echo $ipCustNumber->renderTableRow();
		echo $ipCustName->renderTableRow();
		echo $ipCustInactive->renderTableRow();
		echo $submit->renderTableRow();
		?>
	</table>
</form>
