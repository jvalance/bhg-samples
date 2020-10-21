<?php
require_once '../forms/VGS_NavButton.php';

class VGS_Navigator {
	/**
	 * The general type of page, ie 'list', 'detail', 'menu', 'signon', etc.
	 * @var string
	 */
	public $pageType;
	
	/**
	 * Array of the buttons for this page.
	 * @var array of VGS_NavButton
	 */
	public $buttons;
	
	/**
	 * @var boolean Indicates if this is a pop-up. If so, navigation is different.
	 */
	public $isPopUp;
	
	function __construct($type, $popup = false) {
		$this->pageType = $type;
		$this->buttons = array();
		
		if (!$popup) {
			$popup = (bool) $_REQUEST['popup'];
		} 
		$this->isPopUp = $popup;
		
		switch ($this->pageType) {
			case 'update':
			case 'create':
				$this->addMainMenuButton();
				$this->addSaveButton();
				$this->addCancelButton();
				break; 
			case 'delete':
				$this->addMainMenuButton();
				$this->addDeleteButton();
				$this->addCancelButton();
				break; 
			case 'inquiry':
				$this->addMainMenuButton();
				$this->addCancelButton();
				break; 
			case 'list':
				$this->addMainMenuButton();
				break; 
			default:
				break;
		}
	}
	
	public function addNavButton($label, $target, $targetType = 'uri') {
		$this->buttons[] = new VGS_NavButton($label, $target, $targetType);
	} 
	
	public function addIconButton($label, $target, $icon, $targetType = 'uri') {
		$btn = new VGS_NavButton($label, $target, $targetType);
		$btn->setIcon($icon);
		$this->buttons[] = $btn;
	} 
	
	public function addPopupButton($label, $url, $icon = null, $options = null) {
		if ($icon == null) $icon = VGS_NavButton::POPUP_ICON;
		$winName = str_replace(' ', '_', $label);
		if ($options == null) {
			$target = "javascript:openPopUp('$url', '$winName');";
		} else {
			$target = "javascript:openPopUp('$url', '$winName', '$options');";
		}
		$btn = new VGS_NavButton($label, $target, 'js');
		$btn->setIcon($icon);
		$this->buttons[] = $btn;
	} 
	
	public function addModalPopupButton($label, $url, $icon = null) {
		if ($icon == null) $icon = VGS_NavButton::POPUP_ICON;
		$winName = str_replace(' ', '_', $label);
		$target = "$('#modal_popups').load('$url').dialog({modal:true})"; 
		$btn = new VGS_NavButton($label, $target, 'js');
		$btn->setIcon($icon);
		$this->buttons[] = $btn;
	} 
	
	public function addMainMenuButton() {
		if ($this->isPopUp) {
			$close = new VGS_NavButton('Close Window', "self.close()", 'js');
			$close->setIcon(VGS_NavButton::CLOSE_ICON);
			$this->buttons[] = $close;
		} else {
			$home = new VGS_NavButton('Main Menu', 'menuMainCtrl.php');
			$home->setIcon(VGS_NavButton::HOME_ICON);
			$this->buttons[] = $home;
		}
	} 
	
	public function addSaveButton() {
		$btn = new VGS_NavButton('Save', "doSubmit('update');", 'js');
		$btn->setIcon(VGS_NavButton::SAVE_ICON);
		$this->buttons[] = $btn;
	} 
	
	public function addDeleteButton() {
		$btn = new VGS_NavButton('Delete', "doSubmit('delete');", 'js');
		$btn->setIcon(VGS_NavButton::DELETE_ICON);
		$this->buttons[] = $btn;
	} 
	
	public function addCancelButton() {
		if ($this->isPopUp) {
			$btn = new VGS_NavButton('Cancel', "self.close()", 'js');
			$btn->setIcon(VGS_NavButton::CANCEL_ICON);
			$this->buttons[] = $btn;
		} else {
			$btn = new VGS_NavButton('Cancel', "doSubmit('cancel');", 'js');
			$btn->setIcon(VGS_NavButton::CANCEL_ICON);
			$this->buttons[] = $btn;
		}
	} 
	
	public function renderNavBar() {
		echo '<div id="navbar">';
		foreach ($this->buttons as $navButtonObj) {
			$navButtonObj->render();
		}
		echo '</div>';
//		echo $("#somediv").load(url).dialog({modal:true});
		echo "\n<div id='modal_popups' style='position: absolute; top:-50; right:-50; width:2000px'></div>"; 
	}
}

?>