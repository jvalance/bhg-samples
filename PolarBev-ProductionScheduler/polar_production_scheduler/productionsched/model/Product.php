<?php
namespace polarbev;
require_once '../common/DB_Table.php';

use polarbev\DB_Table;

class Product extends DB_Table {
	public function __construct() {
		parent::__construct ();
	}

	public function updateMRPActivityFlag( $prod, $facility ) {
		$prod = trim($prod);
		$facility = trim($facility);
		$sql = "update CIC set ICACT = 'Y' where trim(ICPROD) = ? and ICFAC = ?"; 
		return $this->execUpdate($sql, array($prod, $facility));
	}

}

?>