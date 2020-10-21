<?php

class VGS_NavButton {
	public $label;
	public $icon = '';
	public $target;
	public $targetType;
	public $attribs;
	
	const IMG_FOLDER = '../shared/images/';
	const ADD_ICON = 'add6.gif';
	const EDIT_ICON = 'edit.gif';
	const CANCEL_ICON = 'cancel.png';
	const CLOSE_ICON = 'close.png';
	const DELETE_ICON = 'delete.gif';
	const DOWNLOAD_ICON = 'download.png';
	const HOME_ICON = 'home.jpg';
	const POPUP_ICON = 'popup.jpg';
	const PRINT_ICON = 'print.png';
	const RETURN_ICON = 'return.bmp';
	const SAVE_ICON = 'save.gif';
	const SEARCH_ICON = 'search2.png';
	const SUBMIT_ICON = 'submit8.gif';
	
	const CLEAN_UP_ICON = 'cleanup.png';
	const PIPE_EXP_ICON = 'pipe1.png';
	const SLSAPP_ICON = 'customers.gif';
	const COST_ICON = 'cost4.gif';
	const INFO_ICON = 'info.png';
	const WARNING_ICON = 'warning.png';
	const INVENTORY_ICON = 'invty8.gif';
	const SEWER_ICON = 'sewer6A.jpg';
	const PREMISES_ICON = 'prem3a.jpg';
	
	
	function __construct($label, $target, $targetType = 'uri') {
		$this->label = $label;
		$this->target = $target;
		$this->targetType = $targetType;
	}
	
	function setIcon($iconFileName) {
		$this->icon = $iconFileName;
	}
	
	static function getPopupIconURL() {
		return self::IMG_FOLDER . self::POPUP_ICON;
	}
	
	public function render($action = 'echo') {
		if ($this->targetType == 'uri') {
			$fullTarget = "document.location.href='{$this->target}';";
		} else {
			$fullTarget = $this->target;
		}
		$btnStyle = '';
		if (strtolower ( $this->label ) == 'delete') {
			$btnStyle = ' color:red; font-weight:bold; ';
		}
		if (trim ( $this->icon ) != '') {
			$iconTag = '<img src="' . self::IMG_FOLDER . $this->icon . '" height="16" width="16" align="left" style="border:0px solid silver" />&nbsp;';
		} else {
			$iconTag = '';
		}
		
		$buttonTag = <<<NAVBUTTON_RENDER
		<button type="button" onclick="{$fullTarget}" style="$btnStyle height:28px; vertical-align:top" {$this->attribs}>
			$iconTag
			{$this->label}
		</button>
NAVBUTTON_RENDER;

		if ($action == 'echo') {
			echo $buttonTag;	
		} elseif($action == 'return') {
			return $buttonTag;
		}
	}
}

?>