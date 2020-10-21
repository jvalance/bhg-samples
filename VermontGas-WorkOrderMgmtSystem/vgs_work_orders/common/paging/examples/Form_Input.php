<?php 
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
