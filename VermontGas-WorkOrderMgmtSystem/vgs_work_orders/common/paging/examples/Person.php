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
