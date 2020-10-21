<?php
class Person {
	// Data elements (i.e. "properties")
	public $name;

	// Functions (i.e.: methods)
	public function __construct( $name ) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	public function showName() {
		echo "Hello. My name is {$this->name}.";
	}
}

require_once 'Person.php';

$john = new Person('John Valance');
$john->showName();  // echos: Hello. My name is John Valance


class Form_Input {
	public $name;
	public $type;
	public $value = '';
	public $label = '';
	private $isOutputOnly = false; // boolean
	
	public function __construct($name, $type='text') {
		$this->name = $name;
		$this->type = $type; 
	} 

	public function setOutputOnly() {
		$this->isOutputOnly = true;
	}
	
	public function render() {
		$html = <<<RENDER_HTML_BLOCK
			<input  type="{$this->type}" 
					name="{$this->name}"
					value="{$this->value} " 
RENDER_HTML_BLOCK;
		
		if ($this->isOutputOnly) {
			$html .= ' disabled="disabled" ';
		}
		
		$html .= ' />';
		return $html;
	}
 	
	public function renderTableRow() {
		$html = 
			"<tr>
				<td align='right'>
					{$this->label} 
				</td>
				<td align='left'>
					{$this->render()} 
				</td>
			</tr>";
		return $html;
	}

}

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

$submit = new Form_Input('submitButton', 'submit');
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
